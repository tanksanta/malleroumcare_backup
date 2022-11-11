<?php
include_once('./_common.php');

if(!$member['mb_entId'])
  json_response(400, '사업소 회원만 이용할 수 있습니다.');

$prodId = $_POST['prodId'];
$stoId = $_POST['stoId'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

if(!$stoId || !$start_date || !$end_date)
  json_response(400, '유효하지않은 요청입니다.');

$start_time = strtotime($start_date);
$end_time = strtotime($end_date);
if(!$start_time || !$end_time)
  json_response(400, '선택한 날짜가 유효하지 않습니다.');
$start_date = date('Y-m-d', $start_time);
$end_date = date('Y-m-d', $end_time);

# 대여중인 재고 조회
$stock_result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, array(
  'entId' => $member['mb_entId'],
  'usrId' => $member['mb_id'],
  'prodId' => $prodId,
  'stoId' => $stoId,
  'stateCd' => ['02']
));

if($stock_result['errorYN'] != 'N')
  json_response(500, '시스템 서버 오류 발생 : '.$stock_result['message']);

$stock = $stock_result['data'] ? $stock_result['data'][0] : null;
if(!$stock || $stock['stoId'] != $stoId)
  json_response(400, '대여기간변경이 가능한 재고가 아닙니다.');

$penOrdId = $stock['penOrdId'];

if(!$penOrdId)
  //json_response(400, '대여기간을 변경할 수 없는 재고입니다. 대여기간을 수동으로 종료해주세요.');

if($penOrdId) {
  # 시스템 주문 변경
  $result = api_post_call(EROUMCARE_API_ORDER_UPDATE, array(
    'usrId' => $member['mb_id'],
    'penOrdId' => $penOrdId,
    'ordLendStrDtm' => $start_date,
    'ordLendEndDtm' => $end_date
  ));

  if($result['errorYN'] != 'N')
    json_response(500, '시스템 서버 오류 발생 : '.$stock_result['message']);

  # 쇼핑몰 주문 변경
  $result = sql_query("
    UPDATE
      {$g5['g5_shop_cart_table']}
    SET
      ordLendStrDtm = '{$start_date}',
      ordLendEndDtm	= '{$end_date}'
    WHERE
      stoId like '%{$stoId}%'
  ");

  if(!$result)
    json_response(500, 'DB 서버 오류 발생');

	# 대여 로그 변경
	$result = sql_query("
	  UPDATE
		g5_rental_log
	  SET
		strdate = '{$start_date}',
		enddate	= '{$end_date}'
	  WHERE
		stoId = '{$stoId}' and
		ordId = '{$penOrdId}'
	");
	if(!$result)
	  json_response(500, 'DB 서버 오류 발생');
}else{
	# 대여 로그 변경
	$row = sql_fetch("select dis_total_date from g5_rental_log where stoId = '{$stoId}' and
	rental_log_division ='2' order by rental_log_Id DESC limit 1");
	
	$result = sql_query("
	  UPDATE
		g5_rental_log
	  SET
		strdate = '{$start_date}',
		enddate	= '{$end_date}'
	  WHERE
		stoId = '{$stoId}' and
		dis_total_date = '".$row["dis_total_date"]."'
	");
	if(!$result)
	  json_response(500, 'DB 서버 오류 발생');

	$result = sql_query("
	  update
		stock_custom_order
	  SET
		sc_sale_date = '{$start_date}',
		sc_rent_date = '{$end_date}',
		sc_updated_at = NOW()
	  where sc_stoId = '{$stoId}' and 
		sc_rent_state = 'rent' and
		sc_gubun = '01'
	");

	if(!$result)
		 json_response(500, 'DB 서버 오류 발생');

}


json_response(200, 'OK');
?>
