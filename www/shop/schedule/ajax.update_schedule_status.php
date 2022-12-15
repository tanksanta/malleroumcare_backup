<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if((!$_POST['ct_id'] && !$_POST['status']) || (!$_POST['od_id'] && !$_POST['status']))
  json_response(400, '유효하지않은 요청입니다.');

if (gettype($_POST['od_id']) == "string") {
  $res = update_partner_install_schedule_status_by_od_id($_POST['od_id'], $_POST['status']);
    if ($res) $code = 200;
    else json_response(400, '유효하지않은 요청입니다.');
} else if (gettype($_POST['ct_id']) == "string") {
    $res = update_partner_install_schedule_status_by_ct_id($_POST['ct_id'], $_POST['status']);
    if ($res) $code = 200;
    else json_response(400, '유효하지않은 요청입니다.');
} else if(gettype($_POST['ct_id']) == "array") {
    $res = update_partner_install_schedule_status_by_ct_id_array($_POST['ct_id'], $_POST['status']);
    if ($res) $code = 200;
    else json_response(400, '유효하지않은 요청입니다.');
} else {
    json_response(400, '유효하지않은 요청입니다.');
}
?>