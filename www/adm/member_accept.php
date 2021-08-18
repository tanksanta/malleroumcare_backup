<?php
$sub_menu = "200100";
include_once("./_common.php");

if($auth_check = auth_check($auth[$sub_menu], 'w', true)) {
  json_response(400, $auth_check);
}

if (!$usrId) {
  json_response(400, '유효하지않은 요청입니다.');
}

// 시스템 승인
$result = post_formdata(EROUMCARE_API_ENT_UPDATE, array(
  'usrId' => $usrId,
  'entId' => $entId,
  'entConfirmCd' => '01'
));

if(!$result)
  json_response(500, '시스템 서버 오류로 처리하지 못했습니다.');

add_notification(
  array(),
  $usrId,
  '[이로움] 회원가입 승인 완료',
  '서비스 이용이 가능합니다.',
  G5_URL,
);

json_response(200, 'OK');
