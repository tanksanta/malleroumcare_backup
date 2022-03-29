<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "r");

if (!$act || !$bc_id) {
  json_response(400, '유효하지 않은 요청입니다. CODE-1');
}

if(!in_array($act, array('rental', 'release'))) {
  json_response(400, '유효하지 않은 요청입니다. CODE-2');
}

$sql = "select * from g5_cart_barcode where bc_id = {$bc_id} and it_id = '{$it_id}' and io_id = '{$io_id}'";
$bc_row = sql_fetch($sql);

if (!$bc_row) {
  json_response(400, '유효하지 않은 요청입니다. CODE-3');
}

if ($act == 'rental') {
  $sql = "
    update g5_cart_barcode
    set
      bc_status = '대여',
      bc_memo = '{$memo}',
      rentaled_by = '{$member['mb_id']}',
      rentaled_at = NOW()
    where
      bc_id = '{$bc_id}'
  ";
  sql_query($sql);
  $bch_content = '재고관리 - 대여처리';

} else if ($act == 'release') {
  $sql = "
    update g5_cart_barcode
    set
      bc_status = '출고',
      bc_memo = '{$memo}',
      released_by = '{$member['mb_id']}',
      released_at = NOW()
    where
      bc_id = '{$bc_id}'
  ";
  sql_query($sql);
  $bch_content = '재고관리 - 출고처리';
}

// 로그
$sql = "
  insert into g5_cart_barcode_log
  set
    bc_id = '{$bc_row['bc_id']}',
    it_id = '{$it_id}',
    io_id = '{$io_id}',
    bch_barcode = '{$bc_row['bc_barcode']}',
    bch_status = '{$bc_row['bc_status']}',
    bch_content = '{$bch_content}',
    bch_memo = '{$bc_row['bc_memo']}',
    created_by = '{$member['mb_id']}',
    created_at = NOW()
";
sql_query($sql);


json_response(200, '완료되었습니다.');


