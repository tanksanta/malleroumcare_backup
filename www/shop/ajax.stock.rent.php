<?php
include_once('./_common.php');

// 대여보유재고 -> 대여완료 처리

if(!$member['mb_entId'])
  json_response(400, '사업소 회원만 이용 가능합니다.');

$prod_id = sql_real_escape_string($_POST['prodId']);
$sto_id = sql_real_escape_string($_POST['stoId']);
$prod_bar_num = sql_real_escape_string($_POST['prodBarNum']);
$pen_nm = sql_real_escape_string($_POST['penNm']);
$str_date = $_POST['strDate'] ? strtotime($_POST['strDate']) : '';
$end_date = $_POST['endDate'] ? strtotime($_POST['endDate']) : '';

if(!$prod_id || !$sto_id)
  json_response(400, '유효하지 않은 요청입니다.');

if(!$str_date || !$end_date)
  json_response(400, '대여기간을 입력해주세요.');

$str_date = date('Y-m-d', $str_date);
$end_date = date('Y-m-d', $end_date);

// 재고 조회
$api_result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, array(
  'entId' => $member['mb_entId'],
  'usrId' => $member['mb_id'],
  'prodId' => $prod_id,
  'stoId' => $sto_id,
  'stateCd' => ['01']
));

if($api_result['errorYN'] != 'N')
  json_response(500, $api_result['message']);

if(!in_array($sto_id, array_column($api_result['data'], 'stoId')))
  json_response(400, '대여완료처리가 가능한 재고가 아닙니다.');

$prods = array(
  'stoId' => $sto_id,
  'prodBarNum' => $prod_bar_num,
  'prodId' => $prod_id,
  'stateCd' => '02'
);

$api_result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
  'entId' => $member['mb_entId'],
  'usrId' => $member['mb_id'],
  'prods' => [$prods]
));

if($api_result['errorYN'] != 'N')
  json_response(500, $api_result['message']);

sql_query("
  INSERT INTO
    stock_custom_order
  SET
    sc_stoId = '{$sto_id}',
    sc_prodId = '{$prod_id}',
    sc_gubun = '01',
    sc_penNm = '{$pen_nm}',
    sc_rent_state = 'rent',
    sc_sale_date = '{$str_date}',
    sc_rent_date = '{$end_date}',
    sc_created_at = NOW(),
    sc_updated_at = NOW()
");

# 대여 로그 입력
$rental_log_id="rental_log".round(microtime(true)).rand();
$result = sql_query("
  INSERT INTO
    g5_rental_log
  SET
    rental_log_Id = '{$rental_log_id}',
    stoId = '{$sto_id}',
    strdate = '{$str_date}',
    enddate = '{$end_date}',
    dis_total_date = NOW(),
    ren_person = '{$pen_nm}',
    rental_log_division = '2'
");

json_response(200, 'OK');
?>
