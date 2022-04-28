<?php
$sub_menu = "400620";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

if(!$it_id || !$io_id)
  json_response(400, '잘못된 요청입니다.');

$use_warehouse_where_sql = get_use_warehouse_where_sql();
$sql = "
  SELECT
	  wh_name, ws_option, io_id, (SUM(ws_qty) - SUM(ws_scheduled_qty)) AS ws_qty
  FROM
    warehouse_stock ws
  WHERE
    it_id = '{$it_id}' AND io_id = '{$io_id}' AND ws_del_yn = 'N' {$use_warehouse_where_sql}
  GROUP BY wh_name, io_id
";

$result = sql_query($sql);

$data = [];
while ($row = sql_fetch_array($result)) {
  $row['stock_info'] = get_stock_item_info($it_id, $io_id);
  $data[] = $row;
}

json_response(200, 'OK', $data);
?>
