<?php
$sub_menu = "400480";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

if(!$od_id || !$ct_ids || !$wh_ids)
  json_response(400, '잘못된 요청입니다.');

foreach ($wh_ids as $key => $wh_id) {
  if ($wh_id) {
    $sql = "select * from warehouse where wh_use_yn = 'Y' and wh_id = '{$wh_id}' ";

    $wh_row = sql_fetch($sql);

    $sql = "
      update purchase_cart
      set
        ct_warehouse = '{$wh_row['wh_name']}',
        ct_warehouse_address = '{$wh_row['wh_address']}',
        ct_warehouse_phone = '{$wh_row['wh_phone']}'
      where
        od_id = '{$od_id}' and
        ct_id = '{$ct_ids[$key]}'
    ";

    sql_query($sql);
  }
}

json_response(200, 'OK');