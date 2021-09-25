<?php
$sub_menu = "200100";
include_once("./_common.php");

if($auth_check = auth_check($auth[$sub_menu], 'w', true)) {
  json_response(400, $auth_check);
}

if (!$mb_id) {
  json_response(400, '유효하지않은 요청입니다.');
}

$mb = get_member($mb_id);

$temp = sql_fetch("SELECT * FROM `{$g5['member_table']}` WHERE mb_giup_bnum = '{$mb['mb_giup_bnum']}' AND mb_temp = TRUE");
if (!$temp['mb_id'] || !$temp['mb_entId'] ) {
  json_response(500, '유효하지않은 임시계정입니다.');
}

$change_id_result = api_post_call(EROUMCARE_API_ENT_UPDATE_USRID, array(
  'entId' => $temp['mb_entId'],
  'usrId' => $temp['mb_id'],
  'toUsrId' => $mb['mb_id'],
));

if ($change_id_result['errorYN'] != 'N') {
  json_response(500, $change_id_result['message']);
}

// entId 연결 및 level 3로 변경
sql_query("UPDATE `{$g5['member_table']}` SET 
  mb_entId = '{$temp['mb_entId']}',
  mb_level = 3
  WHERE mb_id = '{$mb_id}'
");

// 주문정보 이전
sql_query("UPDATE `{$g5['g5_shop_order_table']}` SET 
  mb_id = '{$mb_id}'
  WHERE mb_id = '{$temp['mb_id']}'
");
// 장바구니 정보 이전
sql_query("UPDATE `{$g5['g5_shop_cart_table']}` SET 
  mb_id = '{$mb_id}'
  WHERE mb_id = '{$temp['mb_id']}'
");

// 청구내역 이전
sql_query("UPDATE claim_management SET 
  mb_id = '{$mb_id}'
  WHERE mb_id = '{$temp['mb_id']}'
");
sql_query("UPDATE claim_nhis_upload SET 
  mb_id = '{$mb_id}'
  WHERE mb_id = '{$temp['mb_id']}'
");

// 과거공단자료
sql_query("UPDATE stock_data_upload SET 
  mb_id = '{$mb_id}'
  WHERE mb_id = '{$temp['mb_id']}'
");

// 임시계정 삭제
sql_query("DELETE FROM `{$g5['member_table']}`
  WHERE mb_id = '{$temp['mb_id']}'
");

json_response(200, 'OK');
