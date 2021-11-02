<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if(!$_POST['od_id'] || !$_POST['penId'])
  json_response(400, '유효하지않은 요청입니다.');

$res = get_pen_order_limit($penId, $od_id);

json_response(200, 'OK', $res);
?>
