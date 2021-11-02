<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$sd_id = get_search_string($_POST['sd_id']);
if(!$sd_id)
  json_response(400, '잘못된 요청입니다.');

$result = sql_query(" DELETE FROM stock_data_upload WHERE sd_id = '{$sd_id}' AND mb_id = '{$member['mb_id']}' AND sd_status = 0 ");
if(!$result)
  json_response(500, 'DB 서버 오류가 발생했습니다.');

json_response(200, 'OK');
?>
