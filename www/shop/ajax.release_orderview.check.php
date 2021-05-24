<?php

	include_once("./_common.php");
	header("Content-Type: application/json");
	$result = [];

	$od_edit_member = sql_fetch("SELECT `ct_edit_member` FROM `g5_shop_cart` WHERE ct_id = '{$ct_id}'")["ct_edit_member"];
	if($od_edit_member && $od_edit_member != $member["mb_id"]){
		$result["error"] = "Y";
	} else {
		$result["error"] = "N";
	}

	echo json_encode($result);

?>