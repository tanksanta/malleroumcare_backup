<?php
include_once('./_common.php');

if (!$barcodeArr || !is_array($barcodeArr) || !$ct_id) {
  json_response(400, '잘못된 요청입니다.');
}

$sql = "select * from g5_shop_cart where ct_id = '{$ct_id}' ";
$ct_row = sql_fetch($sql);

if (!$ct_row) {
  json_response(400, '카트가 존재하지 않습니다.');
}

$it_id = $ct_row['it_id'];
$io_id = $ct_row['io_id'];

$data = array(
  'ct_id' => $ct_id,
  'barcodeArr' => [],
);

for ($i = 0; $i < count($barcodeArr); $i++) {
  /*
  // 삭제 안된 바코드 갯수 검색
  $sql = "SELECT count(*) AS cnt FROM g5_cart_barcode WHERE it_id = '{$it_id}' AND bc_barcode = '{$barcode}' AND bc_del_yn = 'N'";
  $count = sql_fetch($sql)['cnt'];

  if ($count == 0) { // 삭제 안된 바코드가 0개 라면
    $sql = "SELECT * FROM g5_cart_barcode WHERE it_id = '{$it_id}' AND bc_barcode = '{$barcode}' ORDER BY bc_id DESC LIMIT 1";
    $bc_row = sql_fetch($sql);

    if ($bc_row) {
      json_response(200, '관리자삭제');
    } else {
      json_response(200, 'OK');
    }
  }

  if ($count == 1) { // 삭제 안된 바코드가 1개 라면
    $sql = "SELECT * FROM g5_cart_barcode WHERE it_id = '{$it_id}' AND bc_barcode = '{$barcode}' and bc_del_yn = 'N' ";
    $bc_row = sql_fetch($sql);

    if ($bc_row['ct_id']) {
      json_response(200, '출고');
    } else {
      json_response(200, '보유재고');
    }
  }

  if ($count > 1) { // 삭제 안된 바코드가 2개 이상이라면
    json_response(200, '보유재고');
  }
  */

  $sql = "
    SELECT * FROM g5_cart_barcode 
    WHERE 
      it_id = '{$it_id}' 
      AND io_id = '{$io_id}'
      AND pct_id > 0
      AND ct_id = 0
      AND bc_status = '정상'
      AND bc_barcode = '{$barcodeArr[$i]['barcode']}' 
      AND bc_del_yn = 'N' 
    ORDER BY bc_id DESC LIMIT 1
  ";
  $bc_row = sql_fetch($sql);

  if ($bc_row) {
    $data['barcodeArr'][$i] = array(
      'index' => $barcodeArr[$i]['index'],
      'status' => $bc_row['bc_status'],
    );
  } else {
    $data['barcodeArr'][$i] = array(
      'index' => $barcodeArr[$i]['index'],
      'status' => '미보유재고',
    );
  }
}

json_response(200, 'OK', $data);