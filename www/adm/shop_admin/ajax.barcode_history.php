<?php
include_once('./_common.php');

if (!$barcode || !$ct_id)
  json_response(400, '잘못된 요청입니다.');

$ct_row = sql_fetch("select * from g5_shop_cart where ct_id = '{$ct_id}'");

if (!$ct_row)
  json_response(400, '존재하지 않는 카트입니다. 관리자에게 문의하세요.');

$sql = "
  select cbl.*, m.mb_name
  from g5_cart_barcode_log cbl
  left join g5_member m on cbl.created_by = m.mb_id
  where 
    bch_barcode = '{$barcode}'
    and it_id = '{$ct_row['it_id']}'
    and io_id = '{$ct_row['it_id']}'
  order by bcl_id desc
";

$result = sql_query($sql);

$data = [];
while ($row = sql_fetch_array($result)) {
  $data[] = $row;
}

json_response(200, 'OK', $data);