<?php
$sub_menu = "500050";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$rl_id = $_POST['rl_id'];
$mb_id = $_POST['mb_id'];

if(!$rl_id || !$mb_id)
  json_response(400, '잘못된 요청입니다.');

$rl = sql_fetch("SELECT * FROM recipient_link WHERE rl_id = '{$rl_id}'");
if(!$rl['rl_id'])
  json_response(500, '존재하지 않는 수급자입니다.');

$ent = get_member($mb_id);
if(!$ent)
  json_response(500, '존재하지 않는 사업소 회원입니다.');

if($rl['rl_state'] != 'wait')
  json_response(500, '연결할 수 없는 상태의 수급자입니다.');

$link = get_recipient_link($rl_id, $mb_id);
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

if($w == 'd') { // 요청취소
  if(!$link || $link['status'] == 'wait')
    json_response(500, '취소할 연결요청이 없습니다.');
  
  if($link['status'] != 'request')
    json_response(500, '사업소와 연결이 된 수급자는 취소할 수 없습니다.');

  sql_query("
    DELETE FROM `recipient_link_rel`
    WHERE rl_id = '$rl_id'
    AND mb_id = '$mb_id'
  ");

  json_response(200, 'OK');
}

if(!$link) {
  // 수급자-사업소 관계가 없을 때 (처음 요청할 때)
  sql_query("
    INSERT INTO `recipient_link_rel` SET
    rl_id = '$rl_id',
    mb_id = '$mb_id',
    status = 'request',
    created_at = '$datetime',
    updated_at = '$datetime'
  ");
  
  add_notification(
    array(),
    $mb_id,
    '[이로움] 신규 수급자 추천',
    '추천된 수급자를 확인하세요.',
    G5_URL . '/shop/my_recipient_list.php',
  );
} else {
  // 수급자-사업소 관계가 있을 때 (이미 요청한 적이 있을때)
  if($link['status'] != 'wait')
    json_response(500, '이미 연결요청 진행 중인 사업자입니다.');
  sql_query("
    UPDATE `recipient_link_rel` SET
    status = 'request',
    updated_at = '$datetime'
    WHERE rl_id = '$rl_id'
    AND mb_id = '$mb_id'
  ");
}

json_response(200, 'OK');
?>
