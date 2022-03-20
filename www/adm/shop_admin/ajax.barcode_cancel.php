<?php
include_once('./_common.php');

if (!$bc_id || !$pod_id || !$pct_id)
  json_response(400, '잘못된 요청입니다.');

$bc_count = 0;

// 벌크 삭제
if (is_array($bc_id)) {
  $bc_id_where = implode(', ', $bc_id);
  $bc_count = count($bc_id);
}

// 단일 삭제
if (!is_array($bc_id)) {
  $bc_id_where = $bc_id;
  $bc_count = 1;
}

// 바코드 삭제 처리
$sql = "
  update 
    g5_cart_barcode
  set
    bc_del_yn = 'Y',
    delete_by = '{$member['mb_id']}',
    deleted_at = NOW()
  where
    bc_id in ({$bc_id_where})
  ";

sql_query($sql);

// 바코드 미등록 갯수 조절
$sql = "
  update
    purchase_cart
  set
    ct_delivered_qty = ct_delivered_qty - {$bc_count}
  where
    od_id = '{$pod_id}' and
    ct_id = '{$pct_id}' 
";
sql_query($sql);

// 재고 수량 조절
$pct_row = sql_fetch("select * from purchase_cart where ct_id = '{$pct_id}' ");
$ws_scheduled_qty = $pct_row['ct_qty_for_rollback'] - $pct_row['ct_delivered_qty'];
$sql = "
      update warehouse_stock
      set
        ws_qty = '{$pct_row['ct_qty_for_rollback']}',
        ws_scheduled_qty = '{$ws_scheduled_qty}'
      where
        od_id = '{$pct_row['od_id']}' and
        ct_id = '{$pct_row['ct_id']}' and
        it_id = '{$pct_row['it_id']}' and
        io_id = '{$pct_row['io_id']}'
    ";
sql_query($sql);

json_response(200, 'OK');