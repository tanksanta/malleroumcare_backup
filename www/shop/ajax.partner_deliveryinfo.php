<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_POST['od_id']);
$check_result = sql_fetch("
  SELECT od_id FROM {$g5['g5_shop_cart_table']}
  WHERE od_id = '{$od_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
  LIMIT 1
");
if(!$check_result['od_id'])
  json_response(400, '존재하지 않는 주문입니다.');

$ct_id = $_POST['ct_id'];
if(!$ct_id || !is_array($ct_id))
  json_response(400, '유효하지 않은 요청입니다.');

foreach($ct_id as $id) {
  $id = get_search_string($id);
  $ct_delivery_company = get_search_string($_POST["ct_delivery_company_{$id}"]);
  $ct_delivery_num = get_search_string($_POST["ct_delivery_num_{$id}"]);

  if(!$ct_delivery_company)
    json_response(400, '유효하지 않은 요청입니다.');

  $ct = sql_fetch("
    SELECT * FROM {$g5['g5_shop_cart_table']}
    WHERE od_id = '{$od_id}' and ct_id = '{$id}'
  ");

  if($ct['ct_direct_delivery_partner'] != $member['mb_id'])
    json_response(400, '해당 상품의 배송정보를 변경할 수 있는 권한이 없습니다.');
  
  $result = sql_query("
    UPDATE
      {$g5['g5_shop_cart_table']}
    SET
      ct_delivery_company = '{$ct_delivery_company}',
      ct_delivery_num = '{$ct_delivery_num}'
    WHERE
      ct_id = '{$id}' and
      ct_direct_delivery_partner = '{$member['mb_id']}'
  ");

  if(!$result)
    json_response(500, 'DB 서버 오류 발생');

  // 배송기록 작성
  sql_query("
    insert into
      g5_delivery_log
    set
      od_id = '{$od_id}',
      ct_id = '{$id}',
      mb_id = '{$member['mb_id']}',
      d_content = '',
      ct_combine_ct_id = '',
      ct_delivery_company = '{$ct_delivery_company}',
      ct_delivery_num = '{$ct_delivery_num}',
      ct_delivery_cnt = '{$ct['ct_delivery_cnt']}',
      ct_delivery_price = '{$ct['ct_delivery_price']}',
      ct_edi_result = '0',
      ct_is_direct_delivery = '{$ct['ct_is_direct_delivery']}',
      d_date = NOW()
  ");
}

// 배송정보 입력 개수 업데이트
$count_result = sql_fetch("
  SELECT
    count(*) as cnt
  FROM
    {$g5['g5_shop_cart_table']}
  WHERE
    od_id = '{$od_id}' and
    (
      (ct_delivery_num <> '' and ct_delivery_num is not null) or
      (ct_combine_ct_id <> 0 and ct_combine_ct_id is not null)
    )
");
$od_delivery_insert = $count_result ? $count_result['cnt'] : 0;

sql_query("
  UPDATE
    {$g5['g5_shop_order_table']}
  SET
    od_delivery_insert = '{$od_delivery_insert}'    
  WHERE
    od_id = {$od_id}
");

json_response(200, 'OK');
?>
