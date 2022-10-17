<?php

include_once('./_common.php');

if(!$is_member){
    alert('접근 권한이 없습니다.');
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
    // die('정상적인 pen_id가 아닙니다.');
    unset($_SESSION['recipient']);
    goto_url(G5_SHOP_URL . '/my_recipient_list.php');
    exit;
}

// 유효기간 만료일 체크
$expired_dtm = substr($res['data'][0]['penExpiDtm'], -10);

if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
    alert('유효기간이 만료된 수급자입니다.');
}

$pen = $res['data'][0];

// 2022.10.05 blocked by JAKE for the mean time by 10.12~
set_session('recipient', $pen);

goto_url($redirect);
