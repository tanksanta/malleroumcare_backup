<?php
include_once ('./_common.php');

$status = $_GET['status'];
$insert_id = $_GET['insert_id'];
if(!$status || !$insert_id)
  alert('유효하지 않은 요청입니다.');

sql_query("UPDATE device_security SET status = '{$status}', updated_at = CURRENT_TIMESTAMP WHERE id = '{$insert_id}' LIMIT 1;");
alert_close('처리되었습니다.');
?>
