<?php
include_once('./_common.php');
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

if($_POST["ltm"] != ""){//수급자정보 확인
	$ltm = $_POST["ltm"];
	$nm = $_POST["nm"];
	$item_nm = $_POST["item_nm"];
	$bar_num = $_POST["bar_num"];

	$sql = "select count('past_id') as cnt from pen_purchase_hist where ENT_ID = '".$member['mb_entId']."' and PEN_NM = '".$nm."' and PEN_LTM_NUM  = '".$ltm."' and (CURRENT_TIMESTAMP between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) and replace(ITEM_NM,' ','')='".$item_nm."' and CNCL_YN='정상'";
	if($bar_num != ""){
		$sql .= " and PROD_BAR_NUM='".$bar_num."'";
	}

	$row = sql_fetch($sql);
	$data = $row["cnt"];
	echo json_encode($data);
	exit;
}else{
	json_response(400, '주문정보가 없습니다.');
	exit;
}

?>