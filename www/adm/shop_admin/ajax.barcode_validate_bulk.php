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



// 23.04.11 : 서원 - 바코드 미등록시 출고 제한을 위한 코드 삽입. 
if( $default['de_barcode_approve_type'] == "part_auto" || $default['de_barcode_approve_type'] == "full_auto" ) {

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
      if( $default['de_barcode_approve_type'] == "part_auto" || $default['de_barcode_approve_type'] == "full_auto" ) {
        $data['barcodeArr'][$key] = array( 
          'index' => $val['index'], 
          'status' => '미보유재고'
        );
      } else {
        $data['barcodeArr'][$key] = array( 
          'index' => $val['index']
        );
        json_response(400, "재고로 등록되지 않은 바코드 입니다.\n입력된 바코드는 초기화 됩니다.",$data);
      }

    }
  }

} else {
  
  // 23.05.22 : 서원
  // 쇼핑몰설정 > 기타설정 > 바코드 출고 설정 > "미등록 PDA 바코드 스캔 / 입력 제한 (관리자 제외)"
  // 특정 설정 값에 따른 바코드 검색 후 회신데이터 변경.

  $_setCheck = 0;
  // 23.02.14 : 서원 - for문에서 foreach로 변경.
  foreach( $barcodeArr as $key => $val ) {
    
    $bc_row = sql_fetch(" SELECT bc_status FROM `g5_cart_barcode` 
                          WHERE `it_id` = '" . $it_id . "' 
                                AND `io_id` = '" . $io_id . "'
                                AND (bc_status = '정상' OR bc_status = '관리자승인완료')
                                AND bc_barcode = '" . $val['barcode'] . "'
                                AND bc_del_yn = 'N' 
                          ORDER BY bc_id DESC 
                          LIMIT 1
    ");

    if ($bc_row) {
      $data['barcodeArr'][$key] = array(
        'index' => $val['index'], 
        'status' => $bc_row['bc_status']
      );
    } else {
      $data['barcodeArr'][$key] = array(
        'index' => $val['index'], 
        'status' => '미등록재고'
      );
      $_setCheck = 1;
    }

  }

  if( !$_setCheck ) {
    json_response(200, 'OK', $data);
  } else {
    json_response(400, "[미재고 바코드 확인] 재고로 등록된 바코드만 정렬하여 입력됩니다.",$data);
  }

}


 /*
for ($i = 0; $i < count($barcodeArr); $i++) {
 
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
 

  $sql = "
    SELECT * FROM g5_cart_barcode 
    WHERE 
      it_id = '{$it_id}' 
      AND io_id = '{$io_id}'
      -- AND ct_id = 0
      AND (bc_status = '정상' OR bc_status = '관리자승인완료')
      AND bc_barcode = '{$barcodeArr[$i]['barcode']}' 
      AND bc_del_yn = 'N' 
    ORDER BY bc_id DESC 
    LIMIT 1
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
 */

json_response(200, 'OK', $data);