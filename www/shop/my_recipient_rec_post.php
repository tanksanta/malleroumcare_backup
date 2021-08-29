<?php

include_once("./_common.php");

# 회원검사
if(!$member["mb_id"] || !$member['mb_entId'])
  json_response(400, "사업소 회원만 접근할 수 있습니다.");

if(!$_POST["penId"])
  json_response(400, "정상적이지 않은 접근입니다.");

if($_POST['inmate']) {
  $_POST['inmate'] = implode(',', ($_POST['inmate']));
}

$_POST['usrId'] = $member['mb_id'];
$_POST['entId'] = $member['mb_entId'];

// 값 검증
$rec_key_cd = array(
  'pscl_state1' => '신체상태 - 옷 벗고 입기',
  'pscl_state2' => '신체상태 - 일어나 앉기',
  'pscl_state3' => '신체상태 - 식사 하기',
  'pscl_state4' => '신체상태 - 방밖으로 나오기',
  'pscl_state5' => '신체상태 - 목욕 하기',
  'pscl_state6' => '신체상태 - 화장실 사용하기',
  'helper_yn' => '가족 및 환경상태 - 주수발자',
  'helper_type' => '가족 및 환경상태 - 주수발자 관계',
  'child' => '가족 및 환경상태 - 자녀수',
  'home_env' => '가족 및 환경상태 - 거주환경',
  'home_type' => '가족 및 환경상태 - 거주형태',
);
$rec_regex = array(
  'pscl_state1' => '/(0[0-2])/',
  'pscl_state2' => '/(0[0-2])/',
  'pscl_state3' => '/(0[0-2])/',
  'pscl_state4' => '/(0[0-2])/',
  'pscl_state5' => '/(0[0-2])/',
  'pscl_state6' => '/(0[0-2])/',
  'helper_yn' => '/(Y|N)/',
  'helper_type' => '/(0[0-5])/',
  'child' => '/([0-9]+)/',
  'home_env' => '/(0[0-2])/',
  'home_type' => '/(0[0-2])/',
);
foreach($rec_regex as $key => $val) {
  $matches = [];
  if(!preg_match($rec_regex[$key], $_POST[$key], $matches)) {
    json_response(400, '입력값을 확인해주세요.\\n오류 : '.$rec_key_cd[$key]);
  }
  $_POST[$key] = $matches[1];
}

$escape_member_list = [
  'penId',
  'rs_id',
  'pscl_reason',
  'recog_reason',
  'helper_type_etc',
  'inmate',
  'total_review'
];

foreach($escape_member_list as $key) {
  $_POST[$key] = sql_real_escape_string($_POST[$key]);
}

if($_POST["rs_id"]) {
  // update
  $result = sql_query("
    UPDATE
      recipient_rec_simple
    SET
      pscl_state1 = '{$_POST['pscl_state1']}',
      pscl_state2 = '{$_POST['pscl_state2']}',
      pscl_state3 = '{$_POST['pscl_state3']}',
      pscl_state4 = '{$_POST['pscl_state4']}',
      pscl_state5 = '{$_POST['pscl_state5']}',
      pscl_state6 = '{$_POST['pscl_state6']}',
      pscl_reason = '{$_POST['pscl_reason']}',
      recog_reason = '{$_POST['recog_reason']}',
      helper_yn = '{$_POST['helper_yn']}',
      helper_type = '{$_POST['helper_type']}',
      helper_type_etc = '{$_POST['helper_type_etc']}',
      child = '{$_POST['child']}',
      home_env = '{$_POST['home_env']}',
      home_type = '{$_POST['home_type']}',
      inmate = '{$_POST['inmate']}',
      total_review = '{$_POST['total_review']}',
      updated_at = NOW()
    WHERE
      rs_id = '{$_POST['rs_id']}' and
      penId = '{$_POST['penId']}' and
      mb_id = '{$member['mb_id']}'
  ");
} else {
  // insert
  $result = sql_query("
    INSERT INTO
      recipient_rec_simple
    SET
      penId = '{$_POST['penId']}',
      mb_id = '{$member['mb_id']}',
      pscl_state1 = '{$_POST['pscl_state1']}',
      pscl_state2 = '{$_POST['pscl_state2']}',
      pscl_state3 = '{$_POST['pscl_state3']}',
      pscl_state4 = '{$_POST['pscl_state4']}',
      pscl_state5 = '{$_POST['pscl_state5']}',
      pscl_state6 = '{$_POST['pscl_state6']}',
      pscl_reason = '{$_POST['pscl_reason']}',
      recog_reason = '{$_POST['recog_reason']}',
      helper_yn = '{$_POST['helper_yn']}',
      helper_type = '{$_POST['helper_type']}',
      helper_type_etc = '{$_POST['helper_type_etc']}',
      child = '{$_POST['child']}',
      home_env = '{$_POST['home_env']}',
      home_type = '{$_POST['home_type']}',
      inmate = '{$_POST['inmate']}',
      total_review = '{$_POST['total_review']}',
      created_at = NOW(),
      updated_at = NOW()
  ");
}

if(!$result) json_response(500, 'DB 오류 발생');

json_response(200, 'OK');
?>
