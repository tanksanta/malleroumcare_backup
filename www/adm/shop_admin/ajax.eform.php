<?php
include_once('./_common.php');

header('Content-type: application/json');

if($_POST["mb_entId"] != ""){//이벤트 상품 구매 조회
	$sql = "SELECT mb_id FROM `g5_member` 	
	WHERE mb_entId = '".$_POST["mb_entId"]."'";
	$row = sql_fetch($sql);
	$data = $row["mb_id"];
	echo json_encode($data);
	exit;
}

?>