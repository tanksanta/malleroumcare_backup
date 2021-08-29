<?php

include_once("./_common.php");

# 회원검사
if(!$member["mb_id"] || !$member['mb_entId'])
  json_response(400, '사업소회원만 이용할 수 있습니다.');

if(!$_POST["penId"] || !$_POST["rs_id"])
  json_response(400, "정상적이지 않은 접근입니다.");

$rs_id = get_search_string($_POST["rs_id"]);

$result = sql_query("
  DELETE FROM recipient_rec_simple
  WHERE rs_id = '{$rs_id}' and mb_id = '{$member['mb_id']}'
");

json_response(200, 'OK');
?>
