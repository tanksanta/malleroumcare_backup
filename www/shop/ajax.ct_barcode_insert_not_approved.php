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
if($orgBarcodeArr && count($orgBarcodeArr)>0){
	for ($i = 0; $i < count($orgBarcodeArr); $i++) {
		$sql_u = "update g5_cart_barcode
					set 
					ct_id = '0',
					bc_status = '정상',
					checked_by = '{$member['mb_id']}',
					checked_at=now()
					where ct_id = '{$ct_row['ct_id']}'
					and it_id = '{$ct_row['it_id']}'
					and io_id = '{$ct_row['io_id']}'
					and bc_barcode = '{$orgBarcodeArr[$i]}'";
					sql_query($sql_u);
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
	  if($toApproveBarcodeArr[$i]['barcode'] != ""){
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
			  }else{//사용가능한 바코드가 있을 경우
					$sql_c = (" SELECT COUNT(bc_id) as cnt, bc_id 
						FROM g5_cart_barcode
						WHERE `it_id` = '{$ct_row['it_id']}'
						  AND `io_id` = '{$ct_row['io_id']}'
						  AND bc_barcode = '{$toApproveBarcodeArr[$i]['barcode']}'
						  AND `checked_at` is not null
					");
					if (sql_fetch($sql_c)['cnt'] == 0) {
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
					}else{
						$sql = "update g5_cart_barcode 
						set 
						ct_id = '{$ct_row['ct_id']}',						
						bc_status = '관리자승인완료',
						bc_is_check_yn = 'Y',
						approved_by = '@part_auto',
						approved_at = NOW(),
						checked_at = null,
						checked_by = ''
						where bc_barcode = '{$toApproveBarcodeArr[$i]['barcode']}'
						and it_id = '{$ct_row['it_id']}'
						and io_id = '{$ct_row['io_id']}'";
						sql_query($sql);
						$bc_id = sql_fetch($sql_c)['bc_id'];
						if (in_array($toApproveBarcodeArr[$i]['barcode'], $orgBarcodeArr)) {
							 $log_flag = false;
						}else{
							$log_flag = true;
						}
					}
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
		}else{//바코드를 삭제했을 경우 처리
			$sql ="select bc_id,bc_barcode 
			from g5_cart_barcode
			where ct_id = '{$ct_row['ct_id']}'
			and it_id = '{$ct_row['it_id']}'
			and io_id = '{$ct_row['io_id']}'";
			$result = sql_query($sql);
			if(sql_num_rows($result)>0){			
				$sql_u = "update g5_cart_barcode
				set 
				ct_id = '0',
				bc_status = '정상',
				checked_by = '{$member['mb_id']}',
				checked_at=now()
				where ct_id = '{$ct_row['ct_id']}'
				and it_id = '{$ct_row['it_id']}'
				and io_id = '{$ct_row['io_id']}'";
				sql_query($sql_u);
			}

			while($row = sql_fetch_array($result)){
				$bch_content = '바코드 등록 취소 - 직배송주문관리에서 바코드 카트 정보 삭제';
				$sql = "
					insert into g5_cart_barcode_log
					set
					  bc_id = '{$row['bc_id']}',
					  ct_id = '{$ct_row['ct_id']}',
					  it_id = '{$ct_row['it_id']}',
					  io_id = '{$ct_row['io_id']}',
					  bch_barcode = '{$row['bc_barcode']}',
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
