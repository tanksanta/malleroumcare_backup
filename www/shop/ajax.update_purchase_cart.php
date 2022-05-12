<?php
$sub_menu = '400480';
include_once("./_common.php");

$auth_check = check_auth($member['mb_id'], $sub_menu, "w");
if (!$auth_check) {
  json_response(400, '권한이 없습니다.');
}

if (!$od_id || !$ct_id) {
  json_response(400, '잘못된 요청입니다.');
}

// 카트 정보
$sql = "select * from purchase_cart where ct_id = '{$ct_id}'";
$ct_row = sql_fetch($sql);

$sql = "select *
        from warehouse_stock
        where
          od_id = '{$od_id}' AND
          ct_id = '{$ct_id}' AND
          it_id = '{$ct_row['it_id']}' AND
          io_id = '{$ct_row['io_id']}'
      ";
$wh_row = sql_fetch($sql);

if (!$ct_row) {
  json_response(400, '카트 정보가 없습니다.');
}

if ($ct_row['is_purchase_end'] == '1') {
  json_response(400, '발주 종료 상태입니다.');
}

if (!$barcodeArr) {
  json_response(400, '입력된 바코드 정보가 없습니다.');
}

$delivered_qty = is_array($barcodeArr) ? count($barcodeArr) : 0;

if ($delivered_qty > $ct_row['ct_qty'] || $delivered_qty == '0') {
  json_response(400, '입고수량은 0이 아니어야 하며, 발주 수량 이하 값이어야 합니다.');
}

if (intval($delivered_qty) < 0 && $delivered_qty < (-$ct_row['ct_delivered_qty'])) {
  json_response(400, '입고 취소 수량이 현재 입고 수량보다 많습니다.');
}

$barcode_memo = clean_xss_tags($barcode_memo);

//if ($delivered_qty == $ct_row['ct_delivered_qty'] && $ct_row['ct_barcode_memo'] == $barcode_memo) {
//  json_response(400, '수정된 값이 없습니다.');
//}

$prev_delivered_qty = $ct_row['ct_delivered_qty'];
$new_delivered_qty = $prev_delivered_qty + $delivered_qty;

$sql = "
  update purchase_cart
  set 
    ct_delivered_qty = '{$new_delivered_qty}',
    ct_barcode_memo = '{$barcode_memo}'
  where od_id = '{$od_id}' and ct_id = '{$ct_id}'
";

sql_query($sql);

// 5개 입고처리 / 바코드 입력한 메모내용
$log_text = "{$delivered_qty}개 입고처리";
if ($barcode_memo) {
  $log_text .= " / {$barcode_memo}";
}
set_purchase_order_admin_log($od_id, $log_text, $ct_id);

// 재고(입고) 바코드 입력
for ($i = 0; $i < count($barcodeArr); $i++) {
  $sql = "
    insert into g5_cart_barcode
    set
      pct_id = '{$ct_id}',
      it_id = '{$ct_row['it_id']}',
      io_id = '{$ct_row['io_id']}',
      bc_barcode = '{$barcodeArr[$i]['barcode']}',
      -- bc_status = '{$barcodeArr[$i]['barcodeStatus']}',
      bc_status = '정상',
      created_by = '{$member['mb_id']}',
      created_at = NOW(),
      checked_by = '{$member['mb_id']}',
      checked_at = NOW()
  ";

  sql_query($sql);
}

// 재고 입력/수정
$ws_scheduled_qty = $ct_row['ct_qty_for_rollback'] - $new_delivered_qty;
if (!$wh_row) {
  $sql = "
      insert into
        warehouse_stock
      set
        it_id = '{$ct_row['it_id']}',
        io_id = '{$ct_row['io_id']}',
        io_type = '{$ct_row['io_type']}',
        it_name = '{$ct_row['it_name']}',
        ws_option = '{$ct_row['ct_option']}',
        ws_qty = '{$new_delivered_qty}',
        ws_scheduled_qty = '{$ws_scheduled_qty}',
        mb_id = '{$ct_row['mb_id']}',
        ws_memo = '주문 발주완료({$od_id})',
        wh_name = '{$ct_row['ct_warehouse']}',
        od_id = '{$od_id}',
        ct_id = '{$ct_id}',
        inserted_from = 'purchase_cart',
        ws_created_at = NOW(),
        ws_updated_at = NOW()
    ";
} else {
  $sql = "
      UPDATE warehouse_stock
      SET
        ws_scheduled_qty = '{$ws_scheduled_qty}',
        ws_updated_at = NOW()
      WHERE
        od_id = '{$od_id}' AND
        ct_id = '{$ct_id}' AND
        it_id = '{$ct_row['it_id']}' AND
        io_id = '{$ct_row['io_id']}'
    ";
}
sql_query($sql);

// 새 카트 정보
$sql = "select * from purchase_cart where ct_id = '{$ct_id}'";
$ct_row = sql_fetch($sql);

// 입고 수량이 발주 수량 초과
if (($ct_row['ct_qty'] <= $ct_row['ct_delivered_qty']) && $ct_row['ct_status'] != '입고완료') {
  $sql = "
    update purchase_cart
    set 
      ct_status = '입고완료',
      is_purchase_end = '1'
    where od_id = '{$od_id}' and ct_id = '{$ct_id}'
  ";
  sql_query($sql);

  set_purchase_order_admin_log($od_id, '발주물량 전체입고 되어 입고완료 처리', $ct_id);
}

json_response(200, 'OK');
?>