<?php

	include_once("./_common.php");

	$sendData = [];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["prodId"] = $_GET["it_id"];
	$sendData["ppcId"] = $_GET["ppc_id"];

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
<<<<<<< HEAD
	curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/prod/deletePpc");
=======
	curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_DELETE_PPC);
>>>>>>> dev
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	curl_close($oCurl);

	goto_url("./wishlist.php");

?>
