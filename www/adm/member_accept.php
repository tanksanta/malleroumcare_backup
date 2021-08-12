<?php 

include_once("./_common.php");

if (!$usrId) {
    json_response(400, '유저아이디를 입력해주세요.');
}

add_notification(
    array(),
    $usrId,
    '[이로움] 회원가입 승인 완료',
    '서비스 이용이 가능합니다.',
    G5_URL,
);

json_response(200, 'OK');