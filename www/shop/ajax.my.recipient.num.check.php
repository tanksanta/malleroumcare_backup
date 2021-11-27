<?php
include_once('./_common.php');

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');


$penLtmNum = $_POST['penLtmNum'];

// 수급자 정보
$ent_pen = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penLtmNum' => $penLtmNum,
  ));
$ent_pen = $ent_pen['data'][0];

json_response(200, 'OK', array(
  'ent_pen' => $ent_pen
));
?>
