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
// 바코드 미등록 갯수 조절(입고 차수 없음)
$pct_info_row = sql_fetch("select ct_part_info from purchase_cart where ct_id = '{$pct_id}' ");

$_part_info = json_decode( $pct_info_row['ct_part_info'], true )[1]; // 차수 없을때
if($_part_info['_in_qty'] == $bc_count) $_part_info['_in_dt_confirm'] = ''; // 전부 바코드 삭제이면 입고완료일 삭제
$_part_info['_in_qty'] = $_part_info['_in_qty'] - $bc_count; // 삭제된 바코드 수만큼 입고 수량 조절
$_part_info_enc[1] = $_part_info;
$_tmp_sql_ct_part_info = "`ct_part_info` ='" . json_encode($_part_info_enc) . "',";

$sql = "
  update
    purchase_cart
  set
    {$_tmp_sql_ct_part_info}
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