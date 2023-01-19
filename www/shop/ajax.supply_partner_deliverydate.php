<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_POST['od_id']);
$ct_id_arr = $_POST['ct_id'];
if(!$ct_id_arr || !is_array($ct_id_arr))
  json_response(400, '저장할 상품이 없습니다.');

foreach($ct_id_arr as $ct_id) {
  $ct_id = get_search_string($ct_id);
  $ct_in_date = get_search_string($_POST["_in_dt_{$ct_id}"]);

  if(!$ct_id || !$ct_in_date)
    json_response(400, '유효하지 않은 요청입니다.');
  
  $cart = sql_fetch("
    SELECT * FROM purchase_cart
    WHERE od_id = '{$od_id}' and ct_id = '{$ct_id}'
  ");

  if($cart['ct_supply_partner'] != $member['mb_id'])
    json_response(400, '해당 상품의 배송정보를 변경할 수 있는 권한이 없습니다.');

  $ct_part_info = json_decode($cart['ct_part_info'],true)[1];

  if($ct_part_info['_in_dt'] === $ct_in_date) // 입고예정일이 이미 저장된 값과 같으면
    continue;

  $today = date('Y-m-d', time());
  $ct_part_info['_in_dt'] = $ct_in_date;
  $ct_part_info['_modify_dt'] = $today;

  $ct_part_info_json = '{ "1" : '.json_encode($ct_part_info).'}';

  $result = sql_query("
    UPDATE purchase_cart
    SET ct_part_info = '{$ct_part_info_json}'
    WHERE ct_id = '{$ct_id}'
  ");
  if(!$result)
    json_response(500, 'DB 서버 오류 발생');

  $it_name = $cart['it_name'];
  if($cart['ct_option'] && $cart['ct_option'] != $it_name)
    $it_name .= "({$cart['ct_option']})";
  set_purchase_order_admin_log($od_id, "{$it_name} - 입고예정일 변경 : $ct_in_date");
}

json_response(200, 'OK');
?>
