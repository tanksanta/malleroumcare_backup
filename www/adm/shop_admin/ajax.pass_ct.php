<?php
include_once('./_common.php');
//auth_check($auth[$sub_menu], "r");
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

if($_POST["ct_id"] != ""){//상품관리코드 확인
	$ct_ids = $_POST["ct_id"];
	sql_query("START TRANSACTION");

	try {

		foreach($ct_ids as $ct_id) {
			sql_query(" UPDATE g5_shop_cart SET ct_barcode_insert = ct_qty WHERE ct_id = '{$ct_id}' ");
		}
		
        // 트랜잭션 커밋
		$result = sql_query("COMMIT");
		$data = $result;
		echo json_encode($data);
		exit;

    } catch (Exception $e) {
        //  트랜잭션 롤백
        sql_query("ROLLBACK");
		json_response(400, '비급여 등록 실패');
		exit;
    }
	
}else{
	json_response(400, '주문정보가 없습니다.');
	exit;
}

?>
