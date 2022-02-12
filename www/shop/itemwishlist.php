<?php

	include_once("./_common.php");

	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["prodId"] = $_POST["it_id"];

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://test.eroumcare.com/api/prod/insertPpc");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	curl_close($oCurl);

	echo $res;

?>
