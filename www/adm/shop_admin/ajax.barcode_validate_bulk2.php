<?php
include_once('./_common.php');

if (!$barcodeArr || !is_array($barcodeArr) || !$ct_id) {
  json_response(400, '잘못된 요청입니다.');
}

$ct_row = sql_fetch("SELECT it_id, io_id FROM `g5_shop_cart` WHERE `ct_id` = '" . $ct_id ."' ");

if (!$ct_row) {
  json_response(400, '카트가 존재하지 않습니다.');
}

$it_id = $ct_row['it_id'];
$io_id = $ct_row['io_id'];

$data = array(
  'ct_id' => $ct_id,
  'barcodeArr' => [],
);

  // 23.02.14 : 서원 - for문에서 foreach로 변경.
  foreach( $barcodeArr as $key => $val ) {
    
    $bc_row = sql_fetch(" SELECT bc_status FROM `g5_cart_barcode` 
                          WHERE `it_id` = '" . $it_id . "' AND
                                `io_id` = '" . $io_id . "' AND
                                (bc_status = '정상' OR bc_status = '관리자승인완료') AND
                                bc_barcode = '" . $val['barcode'] . "' AND
                                bc_del_yn = 'N' 
                          ORDER BY bc_id DESC 
                          LIMIT 1
    ");

    if ($bc_row) {
      $data['barcodeArr'][$key] = array(
        'index' => $val['index'], 
        'status' => $bc_row['bc_status']
      );
    } else {

      // 23.04.11 : 서원 - 바코드 미등록시 출고 제한을 위한 코드 삽입. 
      //if( $default['de_barcode_approve_type'] == "part_auto" || $default['de_barcode_approve_type'] == "full_auto" ) {
        $data['barcodeArr'][$key] = array( 
          'index' => $val['index'], 
          'status' => '미보유재고'
        );
      

    }
  }





json_response(200, 'OK', $data);