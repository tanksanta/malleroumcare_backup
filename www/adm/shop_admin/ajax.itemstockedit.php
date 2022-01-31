<?php
$sub_menu = "400620";
include_once('./_common.php');

$mb_id = $member['mb_id'];

if (!$it_id || !$edit_type || !$ws_memo) {
  json_response(400, '잘못된 요청입니다. code:1');
}

// 상품 검사
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);
if (!$it['it_id']) {
  json_response(400, '존재하지 않는 상품입니다.');
}

// 상품 옵션 검사
$is_item_option_exist = (count_item_option($it_id) == 0 ? 'N' : 'Y');
$io_id = '';
$ws_option = '';

if ($is_item_option_exist == 'Y') {
  if (!$item_option) {
    json_response(400, '잘못된 요청입니다. code:2');
  }

  $item_option_arr = explode("|", $item_option);
  if (count($item_option_arr) != 2) {
    json_response(400, '올바르지 않은 옵션 요청입니다.');
  } else {
    $io_id = $item_option_arr[0];
    $ws_option = $item_option_arr[1];
  }
}

// 수정 타입
if ($edit_type == 'stock') {
  if (!$stock_qty || $stock_qty <= 0 || !$wh_name ) {
    json_response(400, '잘못된 요청입니다. code:3');
  }

  if ($is_item_option_exist == 'N') {
    $io_id = '';
    $ws_option = $it['it_name'];
  }

  if ($stock_abs == 'minus') {
    $stock_qty = $stock_qty * -1;
  }

  $sql = "
    INSERT INTO warehouse_stock
    SET
      it_id = '{$it_id}',
      io_id = '{$io_id}',
      io_type = '0',
      it_name = '{$it['it_name']}',
      ws_option = '{$ws_option}',
      ws_qty = '{$stock_qty}',
      ws_scheduled_qty = '0',
      mb_id = '{$mb_id}',
      ws_memo = '{$ws_memo}',
      wh_name = '{$wh_name}',
      od_id = '0',
      ct_id = '0',
      inserted_from = 'stock_edit',
      ws_created_at = NOW(),
      ws_updated_at = NOW()
  ";
  sql_query($sql);

} else if ($edit_type == 'move') {
  if (!$move_qty || $move_qty <= 0 || !$wh_name_from || !$wh_name_to) {
    json_response(400, '잘못된 요청입니다. code:4');
  }

  // 출고창고 갯수 검사
  $where = '';
  $group = '';
  if ($is_item_option_exist == 'Y') {
    $where = "AND ws_option = '{$ws_option}'";
    $group = ', ws_option';
  }

  $sql = "
    SELECT
      wh_name, ws_option, (sum(ws_qty) - sum(ws_scheduled_qty)) AS ws_qty
    FROM
      warehouse_stock ws
    WHERE
      it_id = '{$it_id}' AND ws_del_yn = 'N' AND wh_name = '{$wh_name_from}' {$where}
    GROUP BY wh_name {$group}
  ";

  $current_warehouse_stock = sql_fetch($sql)['ws_qty'];

  if ($move_qty > $current_warehouse_stock) {
    json_response(400, "출고창고의 재고가 부족합니다.\n({$wh_name_from} 재고: {$current_warehouse_stock}개, 요청 이동수량 : {$move_qty}개)");
  }

  // 출고처리
  $sql = "
    INSERT INTO warehouse_stock
    SET
      it_id = '{$it_id}',
      io_id = '{$io_id}',
      io_type = '0',
      it_name = '{$it['it_name']}',
      ws_option = '{$ws_option}',
      ws_qty = '-{$move_qty}',
      ws_scheduled_qty = '0',
      mb_id = '{$mb_id}',
      ws_memo = '{$ws_memo}',
      wh_name = '{$wh_name_from}',
      od_id = '0',
      ct_id = '0',
      inserted_from = 'stock_move',
      ws_created_at = NOW(),
      ws_updated_at = NOW()
  ";

  sql_query($sql);

  // 입고처리
  $sql = "
    INSERT INTO warehouse_stock
    SET
      it_id = '{$it_id}',
      io_id = '{$io_id}',
      io_type = '0',
      it_name = '{$it['it_name']}',
      ws_option = '{$ws_option}',
      ws_qty = '{$move_qty}',
      ws_scheduled_qty = '0',
      mb_id = '{$mb_id}',
      ws_memo = '{$ws_memo}',
      wh_name = '{$wh_name_to}',
      od_id = '0',
      ct_id = '0',
      inserted_from = 'stock_move',
      ws_created_at = NOW(),
      ws_updated_at = NOW()
  ";
  sql_query($sql);

} else {
  json_response(400, '잘못된 요청입니다. code:5');
}

json_response(200, '완료되었습니다.');
?>
