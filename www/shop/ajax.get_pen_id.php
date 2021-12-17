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
  'penNm' => $keyword
));

$list = [];
if($result['errorYN'] === 'N') {
  foreach($result['data'] as $pen) {
    $penExpiDtm = explode(' ~ ', $pen["penExpiDtm"]);

    $penAge = intval(substr($pen['penBirth'], 0, 4)) ?: 0;
    if($penAge) {
      $penAge = intval(date('Y')) - $penAge + 1;
    }

    $list[] = array(
      'penId' => $pen['penId'],
      'penNm' => $pen['penNm'],
      'penLtmNum' => substr($pen['penLtmNum'], 0, 6) . '*****',
      'penLtmNumRaw' => $pen['penLtmNum'],
      'penRecGraCd' => $pen['penRecGraCd'],
      'penRecGraNm' => $pen['penRecGraNm'],
      'penTypeCd' => $pen['penTypeCd'],
      'penTypeNm' => $pen['penTypeNm'],
      'penBirth' => $pen['penBirth'],
      'penAge' => $pen['penAge'],
      'penGender' => $pen['penGender'],
      'penConNum' => $pen['penConNum'],
      'penConPnum' => $pen['penConPnum'],
      'penProConNum' => $pen['penProConNum'],
      'penExpiStDtm' => $penExpiDtm[0] ?: '',
      'penExpiEdDtm' => $penExpiDtm[1] ?: '',
      'penJumin' => substr($pen["penJumin"], 0, 6),
      'penZip' => $pen['penZip'],
      'penAddr' => $pen['penAddr'],
      'penAddrDtl' => $pen['penAddrDtl'],
    );
  }
}

echo json_encode($list);
