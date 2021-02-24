<?php

	header("Content-Type: application/json");

	$result = [];

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$dataList = $res["data"];
	if($dataList){
		foreach($dataList as $data){
			if(!$data["quantity"]){
				continue;
			}
			
			$thisData = [];
			
			if($data["prodColor"] || $data["prodSize"]){
				$thisData["name"] = "{$data["prodColor"]}";
				$thisData["name"] .= ($data["prodSize"]) ? "/{$data["prodSize"]}" : "";
			} else {
				$thisData["name"] = $data["prodNm"];
			}
			
			$thisData["qty"] = $data["quantity"];
			
			array_push($result, $thisData);
		}
	}

	echo json_encode($result);

?>