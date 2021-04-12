<?php

	include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	include_once('api.config.php');

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Authorization, Content-Type,Accept, Origin');
	header("Content-Type: application/json");
	// $joinStatus = false;
	// $result=[];
	// if(!$_REQUEST["mb_id"]){$result["msg"] = "mb_id is null. mb_id is a required value."; echo json_encode($result); return false;}
	// if(!$_REQUEST["mb_password"]){$result["msg"] = "mb_password is null. mb_password is a required value."; echo json_encode($result); return false;}

	// if($_POST["mb_id"] != "admin"){
	// 	$sendData = [];
	// 	$sendData["usrId"] = $_REQUEST["mb_id"];
	// 	$sendData["pw"] = $_REQUEST["mb_password"];
	// 	$oCurl = curl_init();
	// 	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	// 	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/account/entLogin");
	// 	curl_setopt($oCurl, CURLOPT_POST, 1);
	// 	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	// 	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	// 	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	// 	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	// 	$res = curl_exec($oCurl);
	// 	$res = json_decode($res, true);
	// 	curl_close($oCurl);
	// 	if($res["errorYN"] == "Y"){
	// 		$result["msg"] = "This is not system information.";
	// 		echo json_encode($result);
	// 	} else {
	// 		unset($sendData["pw"]);
	// 		$mbCheck = sql_fetch("SELECT mb_id FROM {$g5["member_table"]} WHERE mb_id = '{$_POST["mb_id"]}'")["mb_id"];
	
	// 		$oCurl = curl_init();
	// 		curl_setopt($oCurl, CURLOPT_PORT, 9901);
	// 		curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/ent/account");
	// 		curl_setopt($oCurl, CURLOPT_POST, 1);
	// 		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	// 		curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	// 		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	// 		curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	// 		curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 15);
	// 		$res = curl_exec($oCurl);
	// 		curl_close($oCurl);
	// 		$resInfo = json_decode($res, true);
	// 		$resInfo = $resInfo["data"];
	
	// 		$resInfo["usrZip01"] = substr($resInfo["entZip"], 0, 3);
	// 		$resInfo["usrZip02"] = substr($resInfo["entZip"], 3, 2);
			
	// 		$resInfo["entZip01"] = substr($resInfo["entZip"], 0, 3);
	// 		$resInfo["entZip02"] = substr($resInfo["entZip"], 3, 2);
	// 		$mb_password2 =  base64_encode ($mb_password) ;
	// 		if(!$mbCheck){
	// 			sql_query("
	// 				INSERT INTO {$g5["member_table"]} SET
	// 					mb_id = '{$resInfo["usrId"]}',
	// 					mb_name = '{$resInfo["entNm"]}',
	// 					mb_nick = '{$resInfo["entNm"]}',
	// 					mb_hp = '{$resInfo["entPnum"]}',
	// 					mb_tel = '{$resInfo["entPnum"]}',
	// 					mb_type = '{$resInfo["type"]}',
	// 					mb_entId = '{$resInfo["entId"]}',
	// 					mb_entNm = '{$resInfo["entNm"]}',
	// 					mb_level = '3',
	// 					 mb_password = '".get_encrypt_string($mb_password)."',
	// 					 mb_zip1 = '{$resInfo["usrZip01"]}',
	// 					 mb_zip2 = '{$resInfo["entZip02"]}',
	// 					 mb_addr1 = '{$resInfo["usrAddr"]}',
	// 					 mb_addr2 = '{$resInfo["usrAddrDetail"]}',
	// 					 mb_giup_bnum = '{$resInfo["entCrn"]}',
	// 					 mb_giup_zip1 = '{$resInfo["entZip01"]}',
	// 					 mb_giup_zip2 = '{$resInfo["entZip02"]}',
	// 					 mb_giup_addr1 = '{$resInfo["entAddr"]}',
	// 					 mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
	// 					 mb_email = '{$resInfo["entMail"]}',
	// 					 mb_password2 = '".$mb_password2."',
	// 					mb_authCd = '{$resInfo["authCd"]}'
	// 			");
	// 		} else {
	// 			sql_query("
	// 				UPDATE {$g5["member_table"]} SET
	// 					mb_name = '{$resInfo["entNm"]}',
	// 					mb_nick = '{$resInfo["entNm"]}',
	// 					mb_hp = '{$resInfo["entPnum"]}',
	// 					mb_tel = '{$resInfo["entPnum"]}',
	// 					mb_entId = '{$resInfo["entId"]}',
	// 					mb_entNm = '{$resInfo["entNm"]}',
	// 					mb_zip1 = '{$resInfo["usrZip01"]}',
	// 					mb_zip2 = '{$resInfo["entZip02"]}',
	// 					mb_addr1 = '{$resInfo["usrAddr"]}',
	// 					mb_addr2 = '{$resInfo["usrAddrDetail"]}',
	// 					 mb_giup_bnum = '{$resInfo["entCrn"]}',
	// 					 mb_giup_zip1 = '{$resInfo["entZip01"]}',
	// 					 mb_giup_zip2 = '{$resInfo["entZip02"]}',
	// 					 mb_giup_addr1 = '{$resInfo["entAddr"]}',
	// 					 mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
	// 					 mb_email = '{$resInfo["entMail"]}',
	// 					 mb_password2 = '".$mb_password2."'
	// 				WHERE mb_id = '{$resInfo["usrId"]}'
	// 			");
	// 		}
			
	// 		$result["msg"] = "success";
    //         // 회원아이디 세션 생성
    //         set_session('ss_mb_id', strip_tags($_REQUEST["mb_id"]));
    //         // FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
    //         set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
	// 		echo json_encode($result);
	// 	}
	// }
	echo json_encode($_REQUEST);
	
	?>
	