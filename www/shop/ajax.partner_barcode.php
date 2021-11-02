<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$ct_id = get_search_string($_POST['ct_id']);
$sto_id_arr = get_search_string($_POST['stoId']);

if(!$ct_id || !$sto_id_arr || !is_array($sto_id_arr))
  json_response(400, '유효하지 않은 요청입니다.');

$cart = sql_fetch("
  SELECT * FROM {$g5['g5_shop_cart_table']}
  WHERE ct_id = '{$ct_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
");

if(!$cart || !$cart['ct_id'])
  json_response(400, '해당 상품의 주문상태를 변경할 수 있는 권한이 없습니다.');

$cart_sto_id = [];
foreach(array_filter(explode('|', $cart['stoId'])) as $id) {
  $cart_sto_id[] = $id;
}

$count = 0;
$prods = [];
foreach($sto_id_arr as $sto_id) {
  if(!in_array($sto_id, $cart_sto_id))
    json_response(400, '해당 주문에 존재하지 않는 상품입니다.');

  $barcode = get_search_string($_POST[$sto_id]);
  if($barcode) $count++;

  $prods[] = array(
    'stoId' => $sto_id,
    'prodBarNum' => $barcode
  );
}

$ent_id = get_member($mb_id, 'mb_entId')['mb_entId'];
$result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
  'usrId' => $cart['mb_id'],
  'entId' => $ent_id,
  'prods' => $prods
));

if($result['errorYN'] != 'N')
  json_response(500, $result['message']);

// 바코드 로그
$it_name = $cart['it_name'];
if($cart['ct_option'] && $cart['ct_option'] != $cart['it_name']) $it_name .= "({$cart['ct_option']})";
foreach($sto_id_arr as $sto_id) {
  $barcode = get_search_string($_POST[$sto_id]);
  $content = "파트너 바코드입력 : {$it_name}[ {$barcode} ]";
  sql_query("
    INSERT INTO
      g5_barcode_log
    SET
      od_id = '{$cart['od_id']}',
      mb_id = '{$member['mb_id']}',
      stoId = '{$sto_id}',
      barcode = '{$barcode}',
      b_content = '{$content}',
      b_date = NOW()
  ");
}

// ct_barcode_insert update
sql_query("
  UPDATE
    {$g5['g5_shop_cart_table']}
  SET
    ct_barcode_insert = '{$count}'
  WHERE
    ct_id = '{$ct_id}'
");

json_response(200, 'OK');
?>
