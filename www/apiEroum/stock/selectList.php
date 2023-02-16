<?php
  include_once ('../../common.php');

	header("Content-Type: application/json");

	$stockQtyList = [];

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_SELECT_LIST);
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 2); // curl이 첫 응답 시간에 대한 timeout
	curl_setopt($oCurl, CURLOPT_TIMEOUT, 5); // curl 전체 실행 시간에 대한 timeout
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
