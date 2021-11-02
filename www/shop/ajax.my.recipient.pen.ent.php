<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default' || !$member['mb_entId'])
  json_response(400, '사업소 회원만 이용가능합니다.');

$w = $_POST['w'];
$pen_mb_id = get_search_string($_POST['pen_mb_id']);
$penId = get_search_string($_POST['penId']);

if(!$pen_mb_id || !$penId)
  json_response(400, '수급자 아이디를 입력해주세요.');

if($w == 'd') {
  // 연결해제

  $result = sql_query("
    DELETE FROM
      recipient_ent
    WHERE
      ent_mb_id = '{$member['mb_id']}' and
      pen_mb_id = '{$pen_mb_id}'
  ");

  if(!$result)
    json_response(500, 'DB 오류로 연결해제에 실패했습니다.');

  json_response(200, 'OK');
} else {
  // 연결등록

  $pen_mb = get_member($pen_mb_id);
  if($pen_mb['mb_type'] !== 'normal')
    json_response(500, '해당 회원이 없습니다. 아이디를 다시 입력해주세요.');

  $pen_ent = get_pen_ent_by_pen_mb_id($pen_mb_id, $member['mb_id']);
  if($pen_ent)
    json_response(500, '이미 연결되어있는 수급자입니다.');

  $result = sql_query("
    INSERT INTO
      recipient_ent
    SET
      pen_mb_id = '{$pen_mb_id}',
      ent_mb_id = '{$member['mb_id']}',
      penId = '{$penId}',
      entId = '{$member['mb_entId']}'
  ");

  if(!$result)
    json_response(500, 'DB 오류로 등록에 실패했습니다.');

  json_response(200, 'OK');
}
?>
