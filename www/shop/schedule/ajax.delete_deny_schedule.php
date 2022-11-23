<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if(!$_POST['partner_mb_id'] || !$_POST['partner_manager_mb_id'] || !$_POST['deny_date'])
  json_response(400, '유효하지않은 요청입니다.');

$res = delete_partner_deny_schedule($_POST['partner_mb_id'], $_POST['partner_manager_mb_id'], $_POST['deny_date']);
if ($res != false) json_response(200, 'OK', $res);
else json_response(400, '에러');
?>