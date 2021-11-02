<?php
include_once("./_common.php");

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$m = $_POST['m'];

if($m == 'a') {
  # 알림 모두확인
  $result = sql_query("
    UPDATE
      recipient_noti
    SET
      rn_checked_yn = 'Y'
    WHERE
      mb_id = '{$member['mb_id']}' and
      rn_checked_yn = 'N'
  ");
} else {
  $rn_id = get_search_string($_POST['rn_id']);
  if(!$rn_id)
    json_response(400, '유효하지 않은 요청입니다.');

  if($m == 'd') {
    # 알림 확인취소
    $result = sql_query("
      UPDATE
        recipient_noti
      SET
        rn_checked_yn = 'N'
      WHERE
        mb_id = '{$member['mb_id']}' and
        rn_id = '{$rn_id}' and
        rn_checked_yn = 'Y'
    ");
  } else {
    # 알림 확인
    $result = sql_query("
      UPDATE
        recipient_noti
      SET
        rn_checked_yn = 'Y'
      WHERE
        mb_id = '{$member['mb_id']}' and
        rn_id = '{$rn_id}' and
        rn_checked_yn = 'N'
    ");
  }

  if(!$result) {
    json_response(500, 'DB 서버에 오류가 발생했습니다.');
  }
}

json_response(200, 'OK');
?>
