<?php
include_once('./_common.php');

if (!$member['mb_id']) {
  json_response(500, '로그인이 필요합니다.');
}

add_fcmtoken($token);

json_response(200, 'OK');