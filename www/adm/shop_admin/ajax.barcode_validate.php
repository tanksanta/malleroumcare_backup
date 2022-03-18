<?php
include_once('./_common.php');

if(!$barcode)
  json_response(400, '잘못된 요청입니다.');

$sql = "SELECT count(*) as cnt FROM g5_cart_barcode WHERE bc_barcode = '{$barcode}' and bc_del_yn = 'N' ";
$count = sql_fetch($sql)['cnt'];

if ($count == 0) {
  $sql = "SELECT * FROM g5_cart_barcode WHERE bc_barcode = '{$barcode}' and bc_del_yn = 'Y' ";
  $bc_row = sql_fetch($sql);

  if ($bc_row) {
    json_response(200, '관리자삭제');
  } else {
    json_response(200, 'OK');
  }
}

if ($count == 1) {
  $sql = "SELECT * FROM g5_cart_barcode WHERE bc_barcode = '{$barcode}' and bc_del_yn = 'N' ";
  $bc_row = sql_fetch($sql);

  if ($bc_row['ct_id']) {
    json_response(200, '이미출고');
  } else {
    json_response(200, '보유재고');
  }
}

if ($count > 1) {
  json_response(200, '보유재고');
}

json_response(500, 'error');