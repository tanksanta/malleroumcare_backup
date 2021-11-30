<?php
include_once('./_common.php');

$penId = clean_xss_tags($_POST['penId']);
$penNm = clean_xss_tags($_POST['penNm']);
$penConNum = clean_xss_tags($_POST['penConNum']);
$penRecGraCd = clean_xss_tags($_POST['penRecGraCd']);
$penTypeCd = clean_xss_tags($_POST['penTypeCd']);
$penExpiStDtm = clean_xss_tags($_POST['penExpiStDtm']);
$penExpiEdDtm = clean_xss_tags($_POST['penExpiEdDtm']);
$penJumin = clean_xss_tags($_POST['penJumin']);

$data = [
    'entId' => $member["mb_entId"],
    'usrId' => $member['mb_id'],
    'penId' => $penId,
    'penNm' => $penNm,
    'penConNum' => $penConNum,
    'penRecGraCd' => $penRecGraCd,
    'penTypeCd' => $penTypeCd,
    'penExpiStDtm' => $penExpiStDtm,
    'penExpiEdDtm' => $penExpiEdDtm,
    'penJumin' => $penJumin
];

$valid = valid_recipient_input($data, true);
if(!$valid) {
    // 입력값 검증 통과된 경우에만 업데이트시킴
    $data = normalize_recipient_input($data);
    $res = api_post_call(EROUMCARE_API_RECIPIENT_UPDATE, $data);

    if($res['errorYN'] != 'N')
        json_response(400, $res['message'] ?: '시스템 서버에서 오류가 발생하여 수급자 정보를 업데이트하지 못했습니다.');
} else {
    json_response(400, $valid);
}

json_response(200, 'OK');
