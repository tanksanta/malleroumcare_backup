<?php
include_once('./_common.php');

// 수급자의 보호자 목록을 가져옴
$penId = get_search_string($_POST['penId']);
$pen = get_recipient($penId);

if(!$pen)
    json_response(400, '존재하지 않는 수급자입니다.');   

$pros = get_pros_by_recipient($penId);

if($pen['penProNm']) {
    array_unshift($pros, [
        'pro_name' => $pen['penProNm'],
        'pro_type' => $pen['penProTypeCd'],
        'pro_rel_type' => $pen['penProRel'],
        'pro_rel' => $pen['penProRelEtc'],
        'pro_birth' => $pen['penProBirth'],
        'pro_hp' => $pen['penProConNum'],
        'pro_tel' => $pen['penProConPnum'],
        'pro_zip' => $pen['penProZip'],
        'pro_addr1' => $pen['penProAddr'],
        'pro_addr2' => $pen['penProAddrDtl']
    ]);
}

json_response(200, 'OK', $pros);
