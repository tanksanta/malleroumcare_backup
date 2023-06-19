<?php
include_once('./_common.php');
//auth_check($auth[$sub_menu], "r");
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

if($_POST["it_id"] != ""){//상품관리코드 확인
	$query = "SHOW COLUMNS FROM g5_shop_item WHERE `Field` = 'it_update_mb';";//업데이트멤버 없을 시 추가
	$wzres = sql_fetch( $query );
	if(!$wzres['Field']) {
		sql_query("ALTER TABLE `g5_shop_item`
		ADD `it_update_mb` varchar(50) NULL DEFAULT '' COMMENT '업데이트멤버' AFTER it_update_time", true);
	}
	$it_type11 = ($_POST["it_deadline"] != "" && $_POST["it_deadline"] != "00:00:00")? "1" : "0";
	$warehouse = ($_POST["it_default_warehouse"] == "미지정")?"":$_POST["it_default_warehouse"];
	$direct_delivery_partner = ($_POST["it_direct_delivery_partner"] == "no_reg")? "":$_POST["it_direct_delivery_partner"];

	$sql = "UPDATE `g5_shop_item` 
	SET it_type11='".$it_type11."'	
	,it_is_direct_delivery='".$_POST["it_is_direct_delivery"]."'
	,it_direct_delivery_partner='".$direct_delivery_partner."'
	,it_deadline='".$_POST["it_deadline"]."'
	,it_default_warehouse='".$warehouse."'
	,it_admin_memo='".$_POST["it_admin_memo"]."' 
	,it_update_time=now()
	,it_update_mb = '".$member["mb_id"]."'
	WHERE it_id='".$_POST["it_id"]."'";
	$result = sql_query($sql);
	$data = $result;
	echo json_encode($data);
	exit;
}else{
	json_response(400, '상품관리코드가 없습니다.');
	exit;
}

?>
