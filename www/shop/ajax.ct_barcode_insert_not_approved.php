<?php
//$sub_menu = "400620";
include_once('./_common.php');

//auth_check($auth[$sub_menu], 'w');

if(!$toApproveBarcodeArr || !is_array($toApproveBarcodeArr)) {
  json_response(400, '미승인 바코드 입력 오류');
}

$approve_setting = sql_fetch("select * from g5_shop_default")['de_barcode_approve_type'];

$ct_ids = [];
$log_flag = false;
for ($i = 0; $i < count($toApproveBarcodeArr); $i++) {
  $log_flag = false;
  $ct_row = sql_fetch("select * from g5_shop_cart where ct_id = '{$toApproveBarcodeArr[$i]['ct_id']}'");

  if ($ct_row) {
    // 기존 요청이 있으면 삭제
    $sql = "
      update g5_cart_barcode_approve_request
      set
        del_yn = 'Y',
        status = '삭제',
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
    $insert_id = sql_insert_id();

    $ct_ids[] = $ct_row['ct_id'];

    // 쇼핑몰 디폴트 설정에 맞춰서 자동 승인 처리
    // enum('full_auto','part_auto','no_auto')
    if ($approve_setting != 'no_auto') {
      if ($approve_setting == 'full_auto') {
        $sql = "
          update g5_cart_barcode_approve_request
          set
            status = '승인',
            del_yn = 'Y',
            approved_at = NOW(),
            approved_by = '@full_auto'
          where
            id = '{$insert_id}'
        ";
        sql_query($sql);

        // 바코드 생성
        $sql = "
          insert into g5_cart_barcode
          set
            ct_id = '{$ct_row['ct_id']}',
            it_id = '{$ct_row['it_id']}',
            io_id = '{$ct_row['io_id']}',
            bc_barcode = '{$toApproveBarcodeArr[$i]['barcode']}',
            bc_status = '관리자승인완료',
            bc_is_check_yn = 'Y',
            created_by = '{$member['mb_id']}',
            created_at = NOW(),
            approved_by = '@full_auto',
            approved_at = NOW()
        ";
        sql_query($sql);
        $bc_id = sql_insert_id();
        $log_flag = true;

      } else if ($approve_setting == 'part_auto') {
        $sql = "
          select count(*) as cnt 
          from g5_cart_barcode
          where it_id = '{$ct_row['it_id']}'
              and io_id = '{$ct_row['io_id']}'
              and checked_at is not null
        ";
        if (sql_fetch($sql)['cnt'] == 0) {
          $sql = "
            update g5_cart_barcode_approve_request
            set
              status = '승인',
              del_yn = 'Y',
              approved_at = NOW(),
              approved_by = '@part_auto'
            where
              id = '{$insert_id}'
          ";
          sql_query($sql);

          // 바코드 생성
          $sql = "
            insert into g5_cart_barcode
            set
              ct_id = '{$ct_row['ct_id']}',
              it_id = '{$ct_row['it_id']}',
              io_id = '{$ct_row['io_id']}',
              bc_barcode = '{$toApproveBarcodeArr[$i]['barcode']}',
              bc_status = '관리자승인완료',
              bc_is_check_yn = 'Y',
              created_by = '{$member['mb_id']}',
              created_at = NOW(),
              approved_by = '@part_auto',
              approved_at = NOW()
          ";
          sql_query($sql);
          $bc_id = sql_insert_id();
          $log_flag = true;
        }
      }

      // 바코드 로그
      if ($log_flag) {
        $bch_content = '바코드입력 - 쇼핑몰 설정으로 자동 출고 승인';
        $sql = "
          insert into g5_cart_barcode_log
          set
            bc_id = '{$bc_id}',
            ct_id = '{$ct_row['ct_id']}',
            it_id = '{$ct_row['it_id']}',
            io_id = '{$ct_row['io_id']}',
            bch_barcode = '{$toApproveBarcodeArr[$i]['barcode']}',
            bch_content = '{$bch_content}',
            created_by = '{$member['mb_id']}',
            created_at = NOW()
        ";
        sql_query($sql);
      }
    }
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