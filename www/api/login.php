<?php

	include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	include_once('api.config.php');

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Authorization, Content-Type,Accept, Origin');

	$returnURL = ($_POST["url"]) ? $_POST["url"] : "/";

	$member = sql_fetch("
		SELECT *
		FROM g5_member
		WHERE mb_id = '{$_POST["mb_id"]}'
	");

	if($member){
		set_session("ss_mb_id", $member["mb_id"]);
		goto_url($returnURL);
	} else {
		$sendData = [];
		$sendData["usrId"] = $_POST["mb_id"];

		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, 9901);
		curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_ENT_INFO);
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 15);
		$res = curl_exec($oCurl);
		curl_close($oCurl);
		$resInfo = json_decode($res, true);
		$resInfo = $resInfo["data"];

		$resInfo["entZip01"] = substr($resInfo["entZip"], 0, 3);
		$resInfo["entZip02"] = substr($resInfo["entZip"], 3, 2);

		sql_query("
			INSERT INTO {$g5["member_table"]} SET
				mb_id = '{$resInfo["usrId"]}',
				mb_name = '{$resInfo["entNm"]}',
				mb_nick = '{$resInfo["entNm"]}',
				mb_hp = '{$resInfo["entPnum"]}',
				mb_tel = '{$resInfo["entPnum"]}',
				mb_type = '{$resInfo["type"]}',
				mb_entId = '{$resInfo["entId"]}',
				mb_entNm = '{$resInfo["entNm"]}',
				mb_level = '3',
				 mb_password = '".get_encrypt_string("1234")."',
				 mb_zip1 = '{$resInfo["entZip01"]}',
				 mb_zip2 = '{$resInfo["entZip02"]}',
				 mb_addr1 = '{$resInfo["entAddr"]}',
				 mb_addr2 = '{$resInfo["entAddrDetail"]}',
				 mb_giup_bnum = '{$resInfo["entCrn"]}',
				 mb_giup_zip1 = '{$resInfo["entZip01"]}',
				 mb_giup_zip2 = '{$resInfo["entZip02"]}',
				 mb_giup_addr1 = '{$resInfo["entAddr"]}',
				 mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
				 mb_email = '{$resInfo["entMail"]}',
				mb_authCd = '{$resInfo["authCd"]}'
		");

		$member = sql_fetch("
			SELECT *
			FROM g5_member
			WHERE mb_id = '{$resInfo["usrId"]}'
		");

		set_session("ss_mb_id", $member["mb_id"]);
		goto_url($returnURL);
	}

?>
