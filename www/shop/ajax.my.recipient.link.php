<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$rl_id = $_POST['rl_id'];

$link = get_recipient_link($rl_id, $member['mb_id']);
if(!$link || $link['status'] == 'wait')
  alert('유효하지 않은 요청입니다.');

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
?>
