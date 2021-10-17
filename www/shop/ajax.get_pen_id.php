<?php
include_once('./_common.php');

header('Content-type: application/json');

if($member['mb_type'] !== 'default') {
  echo json_encode([]);
  exit;
}

$keyword = str_replace(' ', '', trim($keyword));

$result = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId'],
  'appCd' => '01',
  'penNm' => $keyword
));

$list = [];
if($result['errorYN'] === 'N') {
  foreach($result['data'] as $pen) {
    $list[] = array(
      'penId' => $pen['penId'],
      'penNm' => $pen['penNm'],
      'penLtmNum' => substr($pen['penLtmNum'], 0, 6) . '*****',
      'penRecGraNm' => $pen['penRecGraNm'],
      'penTypeNm' => $pen['penTypeNm'],
      'penBirth' => $pen['penBirth'],
      'penGender' => $pen['penGender'],
      'penConNum' => $pen['penConNum'],
      'penProConNum' => $pen['penProConNum'],
    );
  }
}

echo json_encode($list);
