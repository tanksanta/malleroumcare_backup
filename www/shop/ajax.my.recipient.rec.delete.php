<?php

include_once("./_common.php");
include_once('./shop/eform/lib/eform.lib.php');

# 회원검사
if(!$member["mb_id"] || !$member['mb_entId'])
  json_response(400, '사업소회원만 이용할 수 있습니다.');

if(!$_POST["penId"] || !$_POST["recId"])
  json_response(400, "정상적이지 않은 접근입니다.");

  $result = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_REC_LIST, array(
    'recId' => $_POST["recId"],
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penId' => $_POST["penId"]
  ));
  if($result['errorYN'] == 'N' && $result['data']) {
    $result = get_eroumcare(EROUMCARE_API_RECIPIENT_DELETE_REC, array(
      'recId' => $_POST["recId"],
      'usrId' => $member['mb_id'],
      'entId' => $member['mb_entId'],
      'penId' => $_POST["penId"]
    ));
  } else {
    json_response(400, '욕구사정기록지가 존재하지 않습니다.');
  }

  json_response(200, 'OK');
?>
