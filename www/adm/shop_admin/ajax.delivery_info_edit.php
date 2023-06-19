<?php
include_once('./_common.php');
//auth_check($auth[$sub_menu], "r");
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

if($_POST["ct_id"] != ""){//상품관리코드 확인
	$ct_id = $_POST["ct_id"];
	$ct_delivery_company = $_POST["ct_delivery_company"];
	$ct_delivery_num = $_POST["ct_delivery_num"];

	$sql = "UPDATE `g5_shop_cart` 
	SET ct_delivery_company='".$ct_delivery_company."'	
	,ct_delivery_num='".$ct_delivery_num."'
	WHERE ct_id='".$ct_id."'";
	$result = sql_query($sql);
	$data = $result;
	echo json_encode($data);
	exit;
}else{
	json_response(400, '주문정보가 없습니다.');
	exit;
}

?>
