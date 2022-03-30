<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "r");

if (!$data || !is_array($data) || !$it_id) {
  json_response(400, '유효하지 않은 요청입니다.');
}

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

json_response(200, '완료되었습니다.', $data);


