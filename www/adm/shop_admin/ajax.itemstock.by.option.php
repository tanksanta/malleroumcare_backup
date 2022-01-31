<?php
$sub_menu = "400620";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

if(!$it_id || !$ws_option)
  json_response(400, '잘못된 요청입니다.');

$sql = "
  SELECT
	  wh_name, ws_option, (SUM(ws_qty) - SUM(ws_scheduled_qty)) AS ws_qty
  FROM
    warehouse_stock ws
  WHERE
    it_id = '{$it_id}' AND ws_option = '{$ws_option}' AND ws_del_yn = 'N'
  GROUP BY wh_name, ws_option
";

$result = sql_query($sql);

$data = [];
while ($row = sql_fetch_array($result)) {
  $data[] = $row;
}

json_response(200, 'OK', $data);
?>
