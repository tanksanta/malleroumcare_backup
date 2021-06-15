<?php

include_once("./_common.php");

# 회원검사
if(!$member["mb_id"] || !$member['mb_entId'])
  alert("사업소 회원만 접근할 수 있습니다.");

if(!$_POST["penId"])
  alert("정상적이지 않은 접근입니다.");

if($_POST['inmate']) {
  $_POST['inmate'] = implode(',', $_POST['inmate']);
}

$_POST['usrId'] = $member['mb_id'];
$_POST['entId'] = $member['mb_entId'];

// 값 검증
$rec_key_cd = array(
  'psclState1' => '신체상태 - 옷 벗고 입기',
  'psclState2' => '신체상태 - 일어나 앉기',
  'psclState3' => '신체상태 - 식사 하기',
  'psclState4' => '신체상태 - 방밖으로 나오기',
  'psclState5' => '신체상태 - 목욕 하기',
  'psclState6' => '신체상태 - 화장실 사용하기',
  'helperYn' => '가족 및 환경상태 - 주수발자',
  'helperType' => '가족 및 환경상태 - 주수발자 관계',
  'child' => '가족 및 환경상태 - 자녀수',
  'homeEnv' => '가족 및 환경상태 - 거주환경',
  'homeType' => '가족 및 환경상태 - 거주형태',
);
$rec_regex = array(
  'psclState1' => '/(0[0-2])/',
  'psclState2' => '/(0[0-2])/',
  'psclState3' => '/(0[0-2])/',
  'psclState4' => '/(0[0-2])/',
  'psclState5' => '/(0[0-2])/',
  'psclState6' => '/(0[0-2])/',
  'helperYn' => '/(Y|N)/',
  'helperType' => '/(0[0-5])/',
  'child' => '/([0-9]+)/',
  'homeEnv' => '/(0[0-2])/',
  'homeType' => '/(0[0-2])/',
);
foreach($rec_regex as $key => $val) {
  $matches = [];
  if(!preg_match($rec_regex[$key], $_POST[$key], $matches)) {
    alert('입력값을 확인해주세요.\\n오류 : '.$rec_key_cd[$key]);
  }
  $_POST[$key] = $matches[1];
}

if($_POST["recId"]) {
  // update
  $result = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_REC_LIST, array(
    'recId' => $_POST["recId"],
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penId' => $_POST["penId"]
  ));
  if($result['errorYN'] == 'N' && $result['data']) {
    $result = get_eroumcare(EROUMCARE_API_RECIPIENT_UPDATE_REC, $_POST);
  } else {
    alert('욕구사정기록지가 존재하지 않습니다.');
  }
} else {
  // insert
  $result = get_eroumcare(EROUMCARE_API_RECIPIENT_INSERT_REC, $_POST);
}

if($result['errorYN'] != 'N') alert('오류 : '.$result['message']);

header('Location: '.G5_SHOP_URL.'/my_recipient_view.php?id='.$_POST['penId']);
?>
