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
                    mb_hp = '{$resInfo["usrPnum"]}',
                    mb_tel = '{$resInfo["usrPnum"]}',
                    mb_type = '{$resInfo["type"]}',
                    mb_entId = '{$resInfo["entId"]}',
                    mb_entNm = '{$resInfo["entNm"]}',
                    mb_level = '3',
                    mb_password = '".get_encrypt_string($mb_password)."',
                    mb_zip1 = '{$resInfo["usrZip01"]}',
                    mb_zip2 = '{$resInfo["usrZip02"]}',
                    mb_addr1 = '{$resInfo["usrAddr"]}',
                    mb_addr2 = '{$resInfo["usrAddrDetail"]}',
                    mb_giup_bnum = '{$resInfo["entCrn"]}',
                    mb_giup_zip1 = '{$resInfo["entZip01"]}',
                    mb_giup_zip2 = '{$resInfo["entZip02"]}',
                    mb_giup_addr1 = '{$resInfo["entAddr"]}',
                    mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
                    mb_giup_boss_name = '{$resInfo["entCeoNm"]}',
                    mb_email = '{$resInfo["usrMail"]}',
                    mb_fax = '{$resInfo["entFax"]}',
                    mb_authCd = '{$resInfo["authCd"]}',
                    mb_giup_manager_name = '{$resInfo["entTaxCharger"]}',
                    mb_giup_buptae = '{$resInfo["entBusiCondition"]}',
                    mb_giup_bupjong = '{$resInfo["entBusiType"]}',
                    mb_sex = '{$resInfo["usrGender"]}',
                    mb_birth = '{$resInfo["usrBirth"]}',
                    mb_giup_btel = '{$resInfo["entPnum"]}',
                    mb_giup_tax_email = '{$resInfo["entMail"]}',
                    mb_giup_sbnum = '{$resInfo["entBusiNum"]}',
                    mb_giup_bname = '{$resInfo["entNm"]}',
                    mb_entConAcc01 = '{$resInfo["entConAcco1"]}',
                    mb_entConAcc02 = '{$resInfo["entConAcco2"]}',
                    mb_datetime = '".G5_TIME_YMDHIS."'
				");
			} else {
                sql_query("
                    UPDATE {$g5["member_table"]} SET
                        mb_name = '{$resInfo["entNm"]}',
                        mb_nick = '{$resInfo["entNm"]}',
                        mb_hp = '{$resInfo["usrPnum"]}',
                        mb_tel = '{$resInfo["usrPnum"]}',
                        mb_type = '{$resInfo["type"]}',
                        mb_entId = '{$resInfo["entId"]}',
                        mb_entNm = '{$resInfo["entNm"]}',
                        mb_password = '".get_encrypt_string($mb_password)."',
                        mb_zip1 = '{$resInfo["usrZip01"]}',
                        mb_zip2 = '{$resInfo["usrZip02"]}',
                        mb_addr1 = '{$resInfo["usrAddr"]}',
                        mb_addr2 = '{$resInfo["usrAddrDetail"]}',
                        mb_giup_bnum = '{$resInfo["entCrn"]}',
                        mb_giup_zip1 = '{$resInfo["entZip01"]}',
                        mb_giup_zip2 = '{$resInfo["entZip02"]}',
                        mb_giup_addr1 = '{$resInfo["entAddr"]}',
                        mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
                        mb_giup_boss_name = '{$resInfo["entCeoNm"]}',
                        mb_email = '{$resInfo["usrMail"]}',
                        mb_fax = '{$resInfo["entFax"]}',
                        mb_authCd = '{$resInfo["authCd"]}',
                        mb_giup_manager_name = '{$resInfo["entTaxCharger"]}',
                        mb_giup_buptae = '{$resInfo["entBusiCondition"]}',
                        mb_giup_bupjong = '{$resInfo["entBusiType"]}',
                        mb_sex = '{$resInfo["usrGender"]}',
                        mb_birth = '{$resInfo["usrBirth"]}',
                        mb_giup_btel = '{$resInfo["entPnum"]}',
                        mb_giup_tax_email = '{$resInfo["entMail"]}',
                        mb_giup_sbnum = '{$resInfo["entBusiNum"]}',
                        mb_entConAcc01 = '{$resInfo["entConAcco1"]}',
                        mb_entConAcc02 = '{$resInfo["entConAcco2"]}',
                        mb_giup_bname = '{$resInfo["entNm"]}'
                    WHERE mb_id = '{$resInfo["usrId"]}'
                ");
			}
            
			$sendData["usrId"] = $resInfo["usrId"];
			$oCurl = curl_init();
			curl_setopt($oCurl, CURLOPT_PORT, 9901);
			curl_setopt($oCurl, CURLOPT_URL, "https://test.eroumcare.com/api/ent/account");
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

			sql_query("
				UPDATE {$g5["member_table"]} SET
					mb_entId = '{$resInfo["entId"]}'
				WHERE mb_id = '{$resInfo["usrId"]}'
			");


        set_session('ss_mb_id', strip_tags($_REQUEST["mb_id"]));
        set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
        $result["msg"] = "success";
	}
	
	?>
	