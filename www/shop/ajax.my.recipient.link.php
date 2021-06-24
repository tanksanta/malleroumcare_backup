<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$rl_id = $_POST['rl_id'];

$link = get_recipient_link($rl_id, $member['mb_id']);
if(!$link || $link['status'] == 'wait')
  json_response(400, '유효하지 않은 요청입니다.');

$rl = sql_fetch("
  SELECT * FROM `recipient_link`
  WHERE rl_id = {$link['rl_id']}
");
if(!$rl['rl_id'])
  json_response(500, '수급자를 정보를 불러올 수 없습니다.');

$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);
if($w == 'd') {
  // 연결취소 요청
  if($link['status'] == 'link') {
    // 연결상태에서 취소요청시
    sql_query("
      UPDATE `recipient_link` SET
      rl_state = 'wait',
      rl_ent_mb_id = '',
      rl_updated_at = '$datetime'
      WHERE rl_id = '$rl_id'
    ");
    sql_query("
      UPDATE `recipient_link_rel` SET
      status = 'wait',
      updated_at = '$datetime'
      WHERE mb_id = '{$member['mb_id']}'
      AND rl_id = '$rl_id'
    ");
  } else {
    // 연결요청만 된 상태에서 취소요청시
    sql_query("
      DELETE FROM `recipient_link_rel`
      WHERE mb_id = '{$member['mb_id']}'
      AND rl_id = '$rl_id'
    ");
  }
  json_response(200, 'OK');
}

if($w == 's') {
  // 활동시작 요청
  if($link['status'] != 'request')
    json_response(500, '연결요청 상태가 아닙니다.');
  if($rl['rl_state'] != 'wait')
    json_response(500, '이미 사업소와 연결된 수급자입니다.');

  sql_query("
    UPDATE `recipient_link` SET
    rl_state = 'link',
    rl_ent_mb_id = '{$member['mb_id']}',
    rl_updated_at = '$datetime'
    WHERE rl_id = '$rl_id'
  ");
  sql_query("
    UPDATE `recipient_link_rel` SET
    status = 'link',
    updated_at = '$datetime'
    WHERE mb_id = '{$member['mb_id']}'
    AND rl_id = '$rl_id'
  ");
  json_response(200, 'OK');
}

if($w == 'r') {
  // 수급자등록 요청
  if($link['status'] != 'link')
    json_response(500, '수급자와 연결상태가 아닙니다.');

  $data = array(
    'penNm' => get_search_string($rl['rl_pen_name']),
    'penAddr' => get_search_string($rl['rl_pen_addr1']),
    'penAddrDtl' => get_search_string($rl['rl_pen_addr1'].$rl['rl_pen_addr2']),
    'penZip' => get_search_string($rl['rl_pen_zip1'].$rl['rl_pen_zip2']),
    'penBirth' => $_POST['penBirth'],
    'penLtmNum' => $_POST['penLtmNum'] ?  get_search_string($_POST['penLtmNum']) : get_search_string($rl['rl_pen_ltm_num']),
    'penRecGraCd' => $_POST['penRecGraCd'],
    'penTypeCd' => $_POST['penTypeCd'],
    'penGender' => $_POST['penGender'],
    'penConNum' => get_search_string($rl['rl_pen_hp']),
    'entId' => $member["mb_entId"],
    'entUsrId' => $member['mb_giup_boss_name'],
    'appCd' => '01',
    'usrId' => $member["mb_id"]
  );

  $proData = array(
    'penProNm' => get_search_string($rl['rl_pen_pro_name']),
    'penProRel' => $rl['rl_pen_pro_type'],
    'penProRelEtc' => get_search_string($rl['rl_pen_pro_type_etc']),
    'penProConNum' => get_search_string($rl['rl_pen_pro_hp'])
  );

  // 보호자가 있으면
  if($proData['penProNm'])
    $data = array_merge($data, $proData);

  // 필드 무결성 체크
  $valid = valid_recipient_input($data);
  if($valid)
    json_response(400, $valid);

  // 필드 정규화
  $data = normalize_recipient_input($data);

  $result = api_post_call(EROUMCARE_API_RECIPIENT_INSERT, $data);
  if(!$result || $result['errorYN'] == 'Y')
    json_response(500, '오류 발생: '.$result['message']);
  
  sql_query("
    UPDATE `recipient_link` SET
    rl_state = 'done',
    rl_updated_at = '$datetime'
    WHERE rl_id = '$rl_id'
  ");
  sql_query("
    UPDATE `recipient_link_rel` SET
    status = 'done',
    updated_at = '$datetime'
    WHERE mb_id = '{$member['mb_id']}'
    AND rl_id = '$rl_id'
  ");

  json_response(200, 'OK');
}
?>
