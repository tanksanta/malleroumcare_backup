<?php

	header("Content-Type: application/json");

	$stockQtyList = [];

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/stock/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$stockCntList = json_decode($res, true);
	curl_close($oCurl);

	if($stockCntList["data"]){
		foreach($stockCntList["data"] as $data){
			$stockQtyList[$data["prodId"]] += $data["quantity"];
		}
	}

	echo json_encode($stockQtyList);

?>
