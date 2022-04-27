<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "r");

if (!$data || !is_array($data) || !$it_id || !$barcode_qty_prev) {
  json_response(400, '유효하지 않은 요청입니다.');
}

$check_qty = 0;
$add_qty = 0;
$delete_qty = 0;

for ($i = 0; $i < count($data); $i++) {
  // 신규 바코드 처리 g5_cart_barcode
  if ($data[$i]['bc_id'] == '0') {
    $sql = "
      insert into g5_cart_barcode
      set
        it_id = '{$it_id}',
        io_id = '{$io_id}',
        bc_barcode = '{$data[$i]['bc_barcode']}',
        bc_status = '정상',
        bc_is_check_yn = 'Y',
        created_by = '{$member['mb_id']}',
        created_at = NOW(),
        checked_by = '{$member['mb_id']}',
        checked_at = NOW()
    ";
    sql_query($sql);
    $bc_id = sql_insert_id();
    $bch_content = '재고확인 - 신규 바코드 추가';
    $add_qty++;

  } else {
    // 기존 바코드 업데이트 처리 g5_cart_barcode
    if ($data[$i]['bc_del_yn'] == 'Y') { // 삭제
      $sql = "
        update 
          g5_cart_barcode
        set
          bc_del_yn = 'Y',
          bc_status = '관리자삭제',
          delete_by = '{$member['mb_id']}',
          deleted_at = NOW()
        where
          bc_id = {$data[$i]['bc_id']}
      ";
      sql_query($sql);
      $bch_content = '재고확인 - 바코드 삭제';
      $delete_qty++;

    } else if ($data[$i]['checked_at'] == 'currentDate') {
      $sql = "
        update 
          g5_cart_barcode
        set
          bc_is_check_yn = 'Y',
          checked_by = '{$member['mb_id']}',
          checked_at = NOW()
        where
          bc_id = {$data[$i]['bc_id']}
      ";
      sql_query($sql);
      $bch_content = '재고확인 - 바코드 확인';
      $check_qty++;
    }

    $bc_id = $data[$i]['bc_id'];
  }

  // 로그
  $sql = "
    insert into g5_cart_barcode_log
    set
      bc_id = '{$bc_id}',
      it_id = '{$it_id}',
      io_id = '{$io_id}',
      bch_barcode = '{$data[$i]['bc_barcode']}',
      bch_content = '{$bch_content}',
      created_by = '{$member['mb_id']}',
      created_at = NOW()
  ";
  sql_query($sql);
}

$stock_info_row = get_stock_item_info($it_id, $io_id);
$option_text = convert_item_option_to_text($stock_info_row['it_option_subject'], $io_id);

// 재고 확인일 기록
$sql = "
  INSERT INTO stock_barcode_check_log
  SET 
    it_id = '{$it_id}',
    io_id = '{$io_id}',
    it_name = '{$stock_info_row['it_name']}',
    it_option = '{$option_text}',
    stock_qty = '{$stock_info_row['sum_ws_qty']}',
    barcode_qty_prev = '{$barcode_qty_prev}',
    barcode_qty = '{$stock_info_row['sum_barcode_qty']}',
    check_qty = '{$check_qty}',
    add_qty = '{$add_qty}',
    delete_qty = '{$delete_qty}',
    created_by = '{$member['mb_id']}',
    created_at = NOW()
";
sql_query($sql);


// 신규 바코드 재고로 추가
if ($add_qty > 0) {
  $wh_name = '검단창고';
  if ($stock_info_row['it_warehousing_warehouse']) {
    $wh_name = $stock_info_row['it_warehousing_warehouse'];
  }

  $sql = "
    INSERT INTO warehouse_stock
    SET 
      it_id = '{$it_id}',
      io_id = '{$io_id}',
      io_type = '0',
      it_name = '{$stock_info_row['it_name']}',
      ws_option = '{$option_text}',
      ws_qty = '{$add_qty}',
      ws_scheduled_qty = '0',
      mb_id = '{$member['mb_id']}',
      ws_memo = '신규 바코드 재고로 추가',
      wh_name = '{$wh_name}',
      od_id = '0',
      ct_id = '0',
      inserted_from = 'barcode_check',
      ws_created_at = NOW(),
      ws_updated_at = NOW()
  ";
  sql_query($sql);
}

json_response(200, '완료되었습니다.', $data);


