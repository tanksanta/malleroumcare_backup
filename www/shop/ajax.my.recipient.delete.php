<?php

	include_once("./_common.php");

	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["pageNum"] = 1;
	$sendData["pageSize"] = 1;
	$sendData["penId"] = $_POST["id"];

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/recipient/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$data = $res["data"][0];
	if(!$data){
		$res = [];
		$res["errorYN"] = "Y";
		$res["message"] = "존재하지 않는 데이터입니다.";
		
		echo $res;
		return false;
	}

	$data["penExpiDtm"] = explode(" ~ ", $data["penExpiDtm"]);

	$sendData = [];
	$sendData["penId"] = $data["penId"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["delYn"] = "Y";

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/recipient/update");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	curl_close($oCurl);

	echo $res;

?>