<?php
include_once('./_common.php');

if($member['mb_type'] !== 'partner')
  json_response(400, '파트너 회원만 접근가능합니다.');

$manager_mb_id = get_session('ss_manager_mb_id');
if($manager_mb_id) {
  json_response(400, '담당자회원은 담당자를 변경할 수 없습니다.');
}

$manager = clean_xss_tags($_POST['manager']);
$ct_id_arr = $_POST['ct_id'];

if(!$ct_id_arr || !is_array($ct_id_arr))
  json_response(400, '담당자를 지정할 상품을 선택해주세요.');

if($manager) {
  $mb = get_member($manager);
}

$sql = [];
$od_ids = [];

foreach($ct_id_arr as $ct_id) {
  $ct_id = get_search_string($ct_id);

  $cart = sql_fetch("
    SELECT * FROM purchase_cart
    WHERE ct_id = '{$ct_id}' and ct_supply_partner = '{$member['mb_id']}'
  ");

  if(!$cart || !$cart['ct_id'])
    json_response(400, '해당 상품의 담당자를 변경할 수 있는 권한이 없습니다.');
  
  if(!in_array($cart['od_id'], $od_ids)) {
    $od_ids[] = $cart['od_id'];
  }
}

foreach($od_ids as $od_id) {
  $sql = "
    UPDATE
        purchase_order o
    LEFT JOIN
        purchase_cart c ON c.od_id = o.od_id
    SET
        o.od_partner_manager = '$manager'
    WHERE
        o.od_id = '$od_id' and
        o.mb_id = '{$member['mb_id']}'
  ";

  $result = sql_query($sql);
  if(!$result)
    json_response(400, 'DB 오류가 발생하여 담당자를 지정하지 못했습니다.');

  if($mb)
    set_purchase_order_admin_log($od_id, "발주 담당자 지정 : [직원] {$mb['mb_name']}");
  else
    set_purchase_order_admin_log($od_id, "발주 담당자 해제");
}

json_response(200, 'OK');
?>