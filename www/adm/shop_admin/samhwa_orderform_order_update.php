<?php

	include_once("./_common.php");

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/order/update");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	curl_close($oCurl);
    // if($res["errorYN"] == "N"){
    //     sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '' where `od_id` = '{$_POST['od_id']}'");
    // }
	echo $res;

?>
