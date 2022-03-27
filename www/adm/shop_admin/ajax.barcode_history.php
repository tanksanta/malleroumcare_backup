<?php
include_once('./_common.php');

if (!$barcode)
  json_response(400, '잘못된 요청입니다.');

$sql = "
  select cbl.*, m.mb_name
  from g5_cart_barcode_log cbl
  left join g5_member m on cbl.created_by = m.mb_id
  where bch_barcode = '{$barcode}'
  order by bcl_id desc
";

$result = sql_query($sql);

$data = [];
while ($row = sql_fetch_array($result)) {
  $data[] = $row;
}

json_response(200, 'OK', $data);