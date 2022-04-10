<?php
include_once('./_common.php');

$sent_od_id_arr = [];

$result = [];
$result['od_id'] = '2022040815433455';
$ct_ids = [
  '219364',
  '219365',
  '219366',
  '219367',
  '219368',
  '219369',
  '219370',
  '219371',
  '219372',
  '219373',
  '219374',
  '219375',
  '219376',
  '219377',
  '219378',
  '219379',
  '219380',
  '219381',
];

foreach ($ct_ids as $ct_id) {
  if (!in_array($result['od_id'], $sent_od_id_arr)) {
    $od_sql = "select * from g5_shop_order where od_id = '{$result['od_id']}' ";
    $order_row = sql_fetch($od_sql);

    $items_text = '';
    $carts_sql = "select * from g5_shop_cart where od_id = '{$result['od_id']}' and ct_id in (". implode(',', $ct_ids) .") ";
    $carts_result = sql_query($carts_sql);
    while ($cart_row = sql_fetch_array($carts_result)) {
      $ct_it_name = $cart_row['it_name']; //상품이름
      $ct_option = ($cart_row["ct_option"] == $cart_row['it_name']) ? "" : "(" . $cart_row['ct_option'] . ")"; //옵션
      $ct_it_name = $ct_it_name.$ct_option;

      $items_text .= "　- 품명 : {$ct_it_name} / 수량 : {$cart_row['ct_qty']}개\n";
    }

    $talk_msg = "[구매발주안내]\n";
    $talk_msg .= "안녕하세요 테스터님 이로움입니다.\n";
    $talk_msg .= "항상 저희 이로움 플랫폼을 이용해주셔서 진심으로 감사드립니다.\n";
    $talk_msg .= "\n";
    $talk_msg .= "구매발주내역이 있으니 확인바랍니다.\n";
    $talk_msg .= "더욱더 노력하는 이로움플랫폼이 되겠습니다.\n";
    $talk_msg .= "\n";
    $talk_msg .= "■ 주문일시 : " . date('Y/m/d H:i', strtotime($order_row['od_time'])) . "\n";
    $talk_msg .= "■ 주문번호 : {$order_row['od_id']}\n";
    $talk_msg .= "■ 주문내역 :\n";
    $talk_msg .= "{$items_text}";
    $talk_msg .= "■ 배송지명 : {$order_row['od_b_name']}\n";
    $talk_msg .= "■ 배송주소 : {$order_row['od_b_addr1']} {$order_row['od_b_addr2']} {$order_row['od_b_addr3']} {$order_row['od_b_addr_jibeon']}\n";
    $talk_msg .= "■ 배송지연락처 : {$order_row['od_b_tel']} ({$order_row['od_b_hp']})\n";
    $talk_msg .= "■ 배송요청사항 : {$order_row['od_memo']}";

    $talk_result =  send_alim_talk('OD_RESULT_'.$result['od_id'], '010-5134-3622', 'ent_order_result2', $talk_msg);
    if ($talk_result) {
      print_r2($talk_result);
    } else {
      echo 'return null';
    }

    $sent_od_id_arr[] = $result['od_id'];
  }
}