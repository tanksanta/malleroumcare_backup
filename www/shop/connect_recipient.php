<?php

include_once('./_common.php');

if(!$is_member){
    alert('접근 권한이 없습니다.');
    exit;
}

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

$pen = $res['data'][0];

set_session('recipient', $pen);

goto_url(G5_SHOP_URL);
