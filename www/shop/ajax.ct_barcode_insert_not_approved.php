<?php
//$sub_menu = "400620";
include_once('./_common.php');

//auth_check($auth[$sub_menu], 'w');

if(!$toApproveBarcodeArr || !is_array($toApproveBarcodeArr)) {
  json_response(400, '미승인 바코드 입력 오류');
}

$approve_setting = sql_fetch("select de_barcode_approve_type from g5_shop_default")['de_barcode_approve_type'];


$ct_id = "";
foreach( $toApproveBarcodeArr as $key => $val ){

  if( $ct_id != $val['ct_id'] ) {
    $ct_row = sql_fetch("SELECT ct_id, it_id, io_id FROM g5_shop_cart WHERE `ct_id` = '{$val['ct_id']}'");
    $ct_id = $val['ct_id'];
  }

}

// == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == 
// == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == 
// 테스트용
//exit();
// == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == 
// == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == 

// 23.01.18 : 서원 - 트랜잭션 시작
sql_query("START TRANSACTION");

try {  

  $ct_ids = [];
  $log_flag = false;

  $ct_row = $_tmp_ctid = "";
  for ($i = 0; $i < count($toApproveBarcodeArr); $i++) {


    // 여러개의 ct_id에서 ct_id별 1회만 동작
    if( $_tmp_ctid != $toApproveBarcodeArr[$i]['ct_id'] ) {
      $ct_row = sql_fetch("SELECT ct_id, it_id, io_id FROM g5_shop_cart WHERE `ct_id` = '{$toApproveBarcodeArr[$i]['ct_id']}'");
      
      $ct_ids[] = $ct_row['ct_id'];
      $_tmp_ctid = $ct_row['ct_id'];


      $_sql="";
      if ($ct_row) {
        // 기존에 입력된 바코드가 있을 경우
        $_cnt = sql_fetch(" SELECT COUNT(id) as cnt 
                            FROM g5_cart_barcode_approve_request 
                            WHERE `ct_id` = '{$ct_row['ct_id']}' AND `it_id` = '{$ct_row['it_id']}' AND `io_id` = '{$ct_row['io_id']}' 
                          ");
      
        if( $_cnt['cnt'] > 0 ){
          // 기존 요청이 있으면 삭제
          $sql = (" UPDATE g5_cart_barcode_approve_request
                    SET
                      del_yn = 'Y',
                      status = '삭제',
                      deleted_by = '{$member['mb_id']}', 
                      deleted_at = NOW()
                    WHERE
                      `ct_id` = '{$ct_row['ct_id']}' 
                      AND `it_id` = '{$ct_row['it_id']}' 
                      AND `io_id` = '{$ct_row['io_id']}'
                      AND `status` = '승인요청'
                  ");
          sql_query($sql);
        }

      }

    }


    $log_flag = false;

    if( $ct_row ) {

      $sql = (" INSERT g5_cart_barcode_approve_request
                SET
                  ct_id = '{$ct_row['ct_id']}',
                  it_id = '{$ct_row['it_id']}',
                  io_id = '{$ct_row['io_id']}',
                  barcode = '{$toApproveBarcodeArr[$i]['barcode']}',
                  status = '승인요청',
                  requested_by = '{$member['mb_id']}'
              ");
      sql_query($sql);
      $insert_id = sql_insert_id();

      // 쇼핑몰 디폴트 설정에 맞춰서 자동 승인 처리
      // enum('full_auto','part_auto','no_auto')
      if ($approve_setting != 'no_auto') {
        if ($approve_setting == 'full_auto') {
          $sql = (" UPDATE g5_cart_barcode_approve_request
                    SET
                      `status` = '승인',
                      `del_yn` = 'Y',
                      `approved_at` = NOW(),
                      `approved_by` = '@full_auto'
                    WHERE
                      `id` = '{$insert_id}'
                  ");
          sql_query($sql);

          // 바코드 생성
          $sql = (" INSERT g5_cart_barcode
                    SET
                      `ct_id` = '{$ct_row['ct_id']}',
                      `it_id` = '{$ct_row['it_id']}',
                      `io_id` = '{$ct_row['io_id']}',
                      `bc_barcode` = '{$toApproveBarcodeArr[$i]['barcode']}',
                      `bc_status` = '관리자승인완료',
                      `bc_is_check_yn` = 'Y',
                      `created_by` = '{$member['mb_id']}',
                      `created_at` = NOW(),
                      `approved_by` = '@full_auto',
                      `approved_at` = NOW()
                  ");
          sql_query($sql);
          $bc_id = sql_insert_id();
          $log_flag = true;

        } else if ($approve_setting == 'part_auto') {

          $sql = (" SELECT COUNT(bc_id) as cnt 
                    FROM g5_cart_barcode
                    WHERE `it_id` = '{$ct_row['it_id']}'
                      AND `io_id` = '{$ct_row['io_id']}'
                      AND `checked_at` is not null
          ");

          if (sql_fetch($sql)['cnt'] == 0) {
            $sql = (" UPDATE g5_cart_barcode_approve_request
                      SET
                        `status` = '승인',
                        `del_yn` = 'Y',
                        `approved_at` = NOW(),
                        `approved_by` = '@part_auto'
                      WHERE
                        id = '{$insert_id}'
                    ");
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

  // 중복되는 ct_id 제거
  $ct_ids = array_unique($ct_ids);

  // 승인 요청 바코드 갯수 업데이트
  for ($i = 0; $i < count($ct_ids); $i++) {

    if( !$ct_ids[$i] ) { continue; }

    $sql = (" UPDATE g5_shop_cart 
              SET ct_barcode_insert_not_approved = (
                                                      SELECT count(id)
                                                      FROM g5_cart_barcode_approve_request 
                                                      WHERE ct_id = '{$ct_ids[$i]}' AND del_yn = 'N' AND status = '승인요청' 
                                                    )
              WHERE ct_id = '{$ct_ids[$i]}'
    ");
    sql_query($sql);
  }

  // 23.01.18 : 서원 - 트랜잭션 커밋
  sql_query("COMMIT");

} catch (Exception $e) {
  // 23.01.18 : 서원 - 트랜잭션 롤백
  sql_query("ROLLBACK");
}

json_response(200, 'OK');