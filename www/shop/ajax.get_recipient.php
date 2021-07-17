<?php

include_once('./_common.php');

if(!$is_member){
    json_response(400, '권한이 없습니다.');
    exit;
}

if (!$pen_id) {
    json_response(400, '잘못된 요청입니다.');
    exit;
}

if(!$redirect)
    $redirect = G5_URL;

$send_data = [];
$send_data['usrId'] = $member['mb_id'];
$send_data['entId'] = $member['mb_entId'];
$send_data['penId'] = $pen_id;

$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);

if (!$pen_id || $res['errorYN'] === 'Y' || count($res['data']) < 1) {
    json_response(500, '존재하지 않는 수급자입니다.');
}

$ret = $res['data'][0];
$ret['grade'] = get_recipient_grade_per_year($ret['penId']);


json_response(200, 'OK', $ret);
