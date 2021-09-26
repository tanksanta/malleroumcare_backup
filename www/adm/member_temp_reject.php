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
  json_response(500, '유효하지 않는 요청입니다.');
}

// 요청 계정 삭제
sql_query("DELETE FROM `{$g5['member_table']}`
  WHERE mb_id = '{$mb['mb_id']}'
");

json_response(200, 'OK');
