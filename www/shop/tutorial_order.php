<?php
include_once('./_common.php');

$t_recipient_add = get_tutorial('recipient_add');

if (!$t_recipient_add['t_data']) {
  alert('먼저 이전 튜토리얼을 완료해주세요.');
}


$t_recipient_order = get_tutorial('recipient_order');

if ($t_recipient_order['t_state'] == '1') {
  alert('이미 완료한 튜토리얼입니다.\r\n다음단계를 진행하세요.', '/');
}

$tmp_cart_id = get_session('ss_cart_id');

$uid = uuidv4();


// 수급자 연결
$send_data = [];
$send_data['usrId'] = $member['mb_id'];
$send_data['entId'] = $member['mb_entId'];
$send_data['penId'] = $pen_id;
$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);

$_SESSION['recipient']['penId'] = $t_recipient_add['t_data'];

$pen = $res['data'][0];
set_session('recipient', $pen);

$sql = "REPLACE INTO
  g5_shop_cart
  ( 
    od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select, ct_select_time, pt_it, pt_msg1, pt_msg2, pt_msg3, ct_uid, ct_discount, prodSupYn, io_thezone, ct_delivery_cnt, ct_delivery_price, ct_delivery_company, ct_is_direct_delivery, ct_pen_id
  ) VALUES ( 
    '{$tmp_cart_id}', '{$member['mb_id']}', 'PRO2021072200012', 'HSA-8D (체험상품)', '2', '0', '3000', '100000', '0', '쇼핑', '580000', '0', '0', '0', '사이즈:400 / 색상:#01', '1', '0', '400#01', '0', '1', '2021-07-23 14:57:03', '192.168.1.1', '', '0', '1', '0000-00-00 00:00:00', '1', '', '', '', '{$uid}', '0', 'Y', '', '0', '0', 'ilogen', '1', '{$t_recipient_add['t_data']}' 
  ), (
    '{$tmp_cart_id}', '{$member['mb_id']}', 'PRO2021072200013', 'ASC-102 (체험상품)', '2', '0', '3000', '100000', '0', '쇼핑', '129400', '0', '0', '0', 'ASC-102 (체험상품)', '1', '0', '', '0', '', '2021-07-23 14:58:57', '192.168.1.1', '', '0', '1', '0000-00-00 00:00:00', '1', '', '', '', '{$uid}', '0', 'Y', '', '1', '3000', 'ilogen', '1', '{$t_recipient_add['t_data']}' 
  )
";
sql_query($sql);

goto_url(G5_SHOP_URL . '/orderform.php');