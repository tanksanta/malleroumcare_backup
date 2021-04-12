<?php

	include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	include_once('api.config.php');

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Authorization, Content-Type,Accept, Origin');
	header("Content-Type: application/json");
	$joinStatus = false;
	if($_REQUEST["mb_id"] != "admin"){
			$resInfo = $_REQUEST;
			$resInfo["usrZip01"] = substr($resInfo["usrZip"], 0, 3);
			$resInfo["usrZip02"] = substr($resInfo["usrZip"], 3, 2);
			
			$resInfo["entZip01"] = substr($resInfo["entZip"], 0, 3);
			$resInfo["entZip02"] = substr($resInfo["entZip"], 3, 2);

            $mb_password = trim($resInfo['usrPw']);
			$mb_password2 =  base64_encode ($mb_password);
			if(!$mbCheck){
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
						 mb_password = '".get_encrypt_string($mb_password)."',
						 mb_zip1 = '{$resInfo["usrZip01"]}',
						 mb_zip2 = '{$resInfo["entZip02"]}',
						 mb_addr1 = '{$resInfo["usrAddr"]}',
						 mb_addr2 = '{$resInfo["usrAddrDetail"]}',
						 mb_giup_bnum = '{$resInfo["entCrn"]}',
						 mb_giup_zip1 = '{$resInfo["entZip01"]}',
						 mb_giup_zip2 = '{$resInfo["entZip02"]}',
						 mb_giup_addr1 = '{$resInfo["entAddr"]}',
						 mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
						 mb_email = '{$resInfo["entMail"]}',
						 mb_password2 = '".$mb_password2."',
						mb_authCd = '{$resInfo["authCd"]}'
				");
			} else {
				sql_query("
					UPDATE {$g5["member_table"]} SET
						mb_name = '{$resInfo["entNm"]}',
						mb_nick = '{$resInfo["entNm"]}',
						mb_hp = '{$resInfo["entPnum"]}',
						mb_tel = '{$resInfo["entPnum"]}',
						mb_entId = '{$resInfo["entId"]}',
						mb_entNm = '{$resInfo["entNm"]}',
						mb_zip1 = '{$resInfo["usrZip01"]}',
						mb_zip2 = '{$resInfo["entZip02"]}',
						mb_addr1 = '{$resInfo["usrAddr"]}',
						mb_addr2 = '{$resInfo["usrAddrDetail"]}',
						 mb_giup_bnum = '{$resInfo["entCrn"]}',
						 mb_giup_zip1 = '{$resInfo["entZip01"]}',
						 mb_giup_zip2 = '{$resInfo["entZip02"]}',
						 mb_giup_addr1 = '{$resInfo["entAddr"]}',
						 mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
						 mb_email = '{$resInfo["entMail"]}',
						 mb_password2 = '".$mb_password2."'
					WHERE mb_id = '{$resInfo["usrId"]}'
				");
			}
        set_session('ss_mb_id', strip_tags($_REQUEST["mb_id"]));
        set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
        $result["msg"] = "success";
	}
	
	?>
	