<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if ($member['mb_type'] === 'partner' || $member['mb_type'] === 'manager') {
  if(!$_POST['partner_mb_id'])
    json_response(400, '유효하지않은 요청입니다.');
  $res = get_partner_member_list_by_partner_mb_id($_POST['partner_mb_id']);
} else if($member['mb_type' === 'default']) {
  json_response(400, '유효하지않은 요청입니다.');
}

json_response(200, 'OK', $res);
?>