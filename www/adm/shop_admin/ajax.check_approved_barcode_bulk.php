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
  $sql = "
    SELECT * FROM g5_cart_barcode_approve_request 
    WHERE 
      it_id = '{$it_id}' 
      AND io_id = '{$io_id}' 
      AND barcode = '{$barcodeArr[$i]['barcode']}' 
      AND ((del_yn = 'N' AND status = '승인요청') OR (del_yn = 'Y' AND status = '승인'))
    ORDER BY id DESC LIMIT 1
  ";
  $row = sql_fetch($sql);

  if ($row) {
    $data['barcodeArr'][] = array(
      'request_id' => $row['id'],
      'index' => $barcodeArr[$i]['index'],
      'status' => $row['status'],
    );
  }
}

json_response(200, 'OK', $data);