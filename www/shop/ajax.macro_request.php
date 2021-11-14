<?php
include_once("./_common.php");

if(!$member["mb_id"])
  json_response(400, '접근 권한이 없습니다.');

$mb_id = $_POST['mb_id'];
$name = $_POST['name'];
$num = $_POST['num'];
sql_query("INSERT INTO `macro_request` SET
    mb_id = '{$mb_id}',
    status = 'W',
    recipient_name = '{$name}',
    recipient_num = '{$num}'
");

json_response(200, 'OK');
?>
