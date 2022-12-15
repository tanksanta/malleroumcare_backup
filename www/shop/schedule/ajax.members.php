<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if ($_SESSION['ss_manager_mb_id']) {
  $member = get_member($_SESSION['ss_manager_mb_id']);
} else {
  $member = get_member($_SESSION['ss_mb_id']);
}
if ($member['mb_type'] === 'partner' || $member['mb_type'] === 'manager') {
  $res = get_partner_member_list_by_partner_mb_id($_POST['partner_mb_id'], $member['mb_type']);
} else if ($member['mb_type'] === 'default') {
  if ($member['mb_level'] >= 9) {
    $res = get_partner_list($member['mb_type']);
  } else {
    $res = get_partner_member_list_by_ent_mb_id_and_partner_mb_id($member['mb_id']);
  }
} else {
  json_response(400, '유효하지않은 요청입니다.');
}

json_response(200, 'OK', $res);
?>