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

if (!$ct_row) {
  json_response(400, '카트 정보가 없습니다.');
}

$sql = "select *
        from warehouse_stock
        where
          od_id = '{$od_id}' AND
          ct_id = '{$ct_id}' AND
          it_id = '{$ct_row['it_id']}' AND
          io_id = '{$ct_row['io_id']}'
      ";
$wh_row = sql_fetch($sql);

// 상태 업데이트
sql_query("UPDATE purchase_cart 
              SET is_purchase_end = '{$is_purchase_end}' 
              WHERE od_id = '{$od_id}' AND ct_id = '{$ct_id}'
              ");

// 발주 종료 처리 (입고완료)
if ($is_purchase_end == '1') {
  // 카트 업데이트
  $sql = "
    UPDATE purchase_cart
    SET
      ct_status = '입고완료',
      ct_qty = ct_delivered_qty
    WHERE od_id = '{$od_id}' and ct_id = '{$ct_id}'
  ";

  sql_query($sql);

  set_purchase_order_admin_log($od_id, '발주 종료', $ct_id);

  // 재고 입력/수정
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
        ws_qty = '{$ct_row['ct_delivered_qty']}',
        ws_scheduled_qty = '0',
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
        ws_qty = '{$ct_row['ct_delivered_qty']}',
        ws_scheduled_qty = '0'
      WHERE
        od_id = '{$od_id}' AND
        ct_id = '{$ct_id}' AND
        it_id = '{$ct_row['it_id']}' AND
        io_id = '{$ct_row['io_id']}'
    ";
  }
  sql_query($sql);

} else if ($is_purchase_end == '0') { // 발주 종료 취소 (출고완료)
  // 카트 업데이트
  $sql = "
    UPDATE purchase_cart
    SET
      ct_status = '출고완료',
      ct_qty = ct_qty_for_rollback
    WHERE od_id = '{$od_id}' and ct_id = '{$ct_id}'
  ";

  sql_query($sql);

  set_purchase_order_admin_log($od_id, '발주 종료 취소', $ct_id);

  $ws_scheduled_qty = $ct_row['ct_qty_for_rollback'] - $ct_row['ct_delivered_qty'];
  $sql = "
      UPDATE warehouse_stock
      SET
        ws_qty = '{$ct_row['ct_qty_for_rollback']}',
        ws_scheduled_qty = '{$ws_scheduled_qty}'
      WHERE
        od_id = '{$od_id}' AND
        ct_id = '{$ct_id}' AND
        it_id = '{$ct_row['it_id']}' AND
        io_id = '{$ct_row['io_id']}'
    ";

  sql_query($sql);
}

json_response(200, 'OK');
?>