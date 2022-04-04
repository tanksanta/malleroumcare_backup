<?php
include_once('./_common.php');

if (!$request_id) {
  json_response(400, '잘못된 요청입니다.');
}

if ($is_admin != 'super')
  json_response(400, '최고관리자만 접근 가능합니다.');

$sql = "select * from g5_cart_barcode_approve_request where id = '{$request_id}' ";
$request_row = sql_fetch($sql);

if (!$request_row) {
  json_response(400, '바코드 승인요청이 존재하지 않습니다.');
}

// 승인처리
$sql = "
  update g5_cart_barcode_approve_request
  set
    status = '승인',
    del_yn = 'Y',
    approved_at = NOW(),
    approved_by = '{$member['mb_id']}'
  where
    id = '{$request_id}'
";
sql_query($sql);

// 바코드 생성
$sql = "
  insert into g5_cart_barcode
  set
    ct_id = '{$request_row['ct_id']}',
    it_id = '{$request_row['it_id']}',
    io_id = '{$request_row['io_id']}',
    bc_barcode = '{$request_row['barcode']}',
    bc_status = '관리자승인완료',
    bc_is_check_yn = 'Y',
    created_by = '{$member['mb_id']}',
    created_at = NOW(),
    approved_by = '{$member['mb_id']}',
    approved_at = NOW()
";
sql_query($sql);
$bc_id = sql_insert_id();
$bch_content = '바코드입력 - 관리자 권한으로 출고 승인';

// 바코드 로그
$sql = "
  insert into g5_cart_barcode_log
  set
    bc_id = '{$bc_id}',
    ct_id = '{$request_row['ct_id']}',
    it_id = '{$request_row['it_id']}',
    io_id = '{$request_row['io_id']}',
    bch_barcode = '{$request_row['barcode']}',
    bch_content = '{$bch_content}',
    created_by = '{$member['mb_id']}',
    created_at = NOW()
";
sql_query($sql);

// 승인 요청 바코드 갯수 업데이트
$sql = "
    select count(*) as cnt from g5_cart_barcode_approve_request 
    where 
      ct_id = '{$request_row['ct_id']}' 
      and del_yn = 'N' 
      and status = '승인요청' 
  ";
$count = sql_fetch($sql)['cnt'];

$sql = "
    update g5_shop_cart 
    set ct_barcode_insert_not_approved = '{$count}'
    where ct_id = '{$request_row['ct_id']}'
  ";
sql_query($sql);

json_response(200, '완료되었습니다.');