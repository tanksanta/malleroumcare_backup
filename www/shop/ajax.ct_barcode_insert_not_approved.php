<?php
//$sub_menu = "400620";
include_once('./_common.php');

//auth_check($auth[$sub_menu], 'w');

if(!$toApproveBarcodeArr || !is_array($toApproveBarcodeArr)) {
  json_response(400, '미승인 바코드 입력 오류');
}


$ct_ids = [];

for ($i = 0; $i < count($toApproveBarcodeArr); $i++) {
  $ct_row = sql_fetch("select * from g5_shop_cart where ct_id = '{$toApproveBarcodeArr[$i]['ct_id']}'");

  if ($ct_row) {
    // 기존 요청이 있으면 삭제
    $sql = "
      update g5_cart_barcode_approve_request
      set
        del_yn = 'Y',
        deleted_by = '{$member['mb_id']}', 
        deleted_at = NOW()
      where
        ct_id = '{$ct_row['ct_id']}' and 
        it_id = '{$ct_row['it_id']}' and 
        io_id = '{$ct_row['io_id']}' and 
        barcode = '{$toApproveBarcodeArr[$i]['barcode']}' and 
        status = '승인요청'
    ";
    sql_query($sql);

    $sql = "
      insert into g5_cart_barcode_approve_request
      set
        ct_id = '{$ct_row['ct_id']}',
        it_id = '{$ct_row['it_id']}',
        io_id = '{$ct_row['io_id']}',
        barcode = '{$toApproveBarcodeArr[$i]['barcode']}',
        status = '승인요청',
        requested_by = '{$member['mb_id']}'
    ";
    sql_query($sql);

    $ct_ids[] = $ct_row['ct_id'];
  }
}

$ct_ids = array_unique($ct_ids);

// 승인 요청 바코드 갯수 업데이트
for ($i = 0; $i < count($ct_ids); $i++) {
  $sql = "
    select count(*) as cnt from g5_cart_barcode_approve_request 
    where 
      ct_id = '{$ct_ids[$i]}' 
      and del_yn = 'N' 
      and status = '승인요청' 
  ";
  $count = sql_fetch($sql)['cnt'];

  $sql = "
    update g5_shop_cart 
    set ct_barcode_insert_not_approved = '{$count}'
    where ct_id = '{$ct_ids[$i]}'
  ";
  sql_query($sql);
}

json_response(200, 'OK');