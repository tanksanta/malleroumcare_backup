<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_POST['od_id']);
$ct_id_arr = $_POST['ct_id'];
if(!$ct_id_arr || !is_array($ct_id_arr))
  json_response(400, '유효하지 않은 요청입니다.');

foreach($ct_id_arr as $ct_id) {
  $ct_id = get_search_string($ct_id);
  $ct_direct_delivery_date = get_search_string($_POST["ct_direct_delivery_date_{$ct_id}"]);
  $ct_direct_delivery_date = date('Y-m-d', strtotime($ct_direct_delivery_date));

  if(!$ct_id || !$ct_direct_delivery_date)
    json_response(400, '유효하지 않은 요청입니다.');
  
  $cart = sql_fetch("
    SELECT * FROM {$g5['g5_shop_cart_table']}
    WHERE od_id = '{$od_id}' and ct_id = '{$id}'
  ");

  if($cart['ct_direct_delivery_partner'] != $member['mb_id'])
    json_response(400, '해당 상품의 배송정보를 변경할 수 있는 권한이 없습니다.');
  
  $result = sql_query("
    UPDATE {$g5['g5_shop_cart_table']}
    SET ct_direct_delivery_date_ = '{$ct_direct_delivery_date}'
    WHERE ct_id = '{$ct_id}'
  ");
  if(!$result)
    json_response(500, 'DB 서버 오류 발생');
}

json_response(200, 'OK');
?>
