<?php
  include_once ('../../common.php');

	header("Content-Type: application/json");

	$result = [];
	$data1 = [];

	if($_POST["status02"]){
		unset($_POST["status02"]);
		$status02 = true;
	}

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
	$res = json_decode($res, true);
	curl_close($oCurl);

	$dataList = $res["data"];
	if($dataList){
		foreach($dataList as $data){
			if(!$data["quantity"] && !$status02){
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

			array_push($data1, $thisData);
		}

		$result["data"] = $data1;
	}

	if($status02){
		$data2 = [];
		$_POST["stateCd"] = "02";

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
		$res = json_decode($res, true);
		curl_close($oCurl);

		$dataList = $res["data"];
		if($dataList){
			foreach($dataList as $data){
				if(!$data["quantity"]){
					continue;
				}

				$thisData = [];
				$thisData["qty"] = $data["quantity"];

				array_push($data2, $thisData);
			}
		}

		$result["data2"] = $data2;
	}

	echo json_encode($result);

?>
