<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

if ($w == 'u' && $is_admin == 'super') {
    if (file_exists(G5_PATH.'/DEMO'))
        alert('데모 화면에서는 하실(보실) 수 없는 작업입니다.');
}

$mb_id = $_SESSION['ss_mb_reg'];

if($w != "u"){
	$sendData = [];
	$sendData["usrId"] = $mb_id;

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/account/entInfo");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	curl_close($oCurl);
	$resInfo = json_decode($res, true);
	$resInfo = $resInfo["data"];

	$mb_password    = "1234";
	$mb_password_re = "1234";
	//$mb_name        = "hsy";
	//$mb_level       = "2";
	//$mb_nick        = trim($_POST['mb_nick']);
	//$mb_nick        = "hsy";
	//$mb_email       = "";
	//$mb_sex         = "";
	//$mb_birth       = "";
	//$mb_homepage    = "";
	//$mb_tel         = "";
	//$mb_hp          = "";
	//$mb_fax         = "";
	//$mb_zip1        = "";
	//$mb_zip2        = "";
	//$mb_addr1       = "";
	//$mb_addr2       = "";
	//$mb_addr3       = "";
	//$mb_addr_jibeon = "";
	//$mb_signature   = "";
	//$mb_profile     = "";
	//$mb_recommend   = "";
	//$mb_mailling    = "";
	//$mb_sms         = "";
	//$mb_1           = "";
	//$mb_2           = "";
	//$mb_3           = "";
	//$mb_4           = "";
	//$mb_5           = "";
	//$mb_6           = "";
	//$mb_7           = "";
	//$mb_8           = "";
	//$mb_9           = "";
	//$mb_10          = "";
	//$mb_type        = "";

	// $mb_name        = clean_xss_tags($mb_name);
	// $mb_email       = get_email_address($mb_email);
	// $mb_homepage    = clean_xss_tags($mb_homepage);
	// $mb_tel         = clean_xss_tags($mb_tel);
	// $mb_fax         = clean_xss_tags($mb_fax);
	// $mb_zip1        = preg_replace('/[^0-9]/', '', $mb_zip1);
	// $mb_zip2        = preg_replace('/[^0-9]/', '', $mb_zip2);
	// $mb_addr1       = clean_xss_tags($mb_addr1);
	// $mb_addr2       = clean_xss_tags($mb_addr2);
	// $mb_addr3       = clean_xss_tags($mb_addr3);
	// $mb_addr_jibeon = preg_match("/^(N|R)$/", $mb_addr_jibeon) ? $mb_addr_jibeon : '';


	$sql_certify = '';
	$md5_cert_no = $_SESSION['ss_cert_no'];
	$cert_type = $_SESSION['ss_cert_type'];

	$sql_certify .= " , mb_hp = '{$mb_hp}' ";
	$sql_certify .= " , mb_certify = '' ";
	$sql_certify .= " , mb_adult = 0 ";
	$sql_certify .= " , mb_birth = '' ";
	$sql_certify .= " , mb_sex = '' ";


	$mb_thezone_code = get_uniqid_member();

	//$sql = "insert into {$g5['member_table']}
	//        set mb_id = '{$mb_id}',
	//        mb_password = '".get_encrypt_string($mb_password)."',
	//        mb_name = '{$mb_name}'";

	$resInfo["entZip01"] = substr($resInfo["entZip"], 0, 3);
	$resInfo["entZip02"] = substr($resInfo["entZip"], 3, 2);

	$sql = "insert into {$g5['member_table']}
			set mb_id = '{$resInfo["usrId"]}',
			mb_name = '{$resInfo["entNm"]}',
			mb_nick = '{$resInfo["entNm"]}',
			mb_hp = '{$resInfo["entPnum"]}',
			mb_tel = '{$resInfo["entPnum"]}',
			mb_type = '{$resInfo["type"]}',
			mb_entId = '{$resInfo["entId"]}',
			mb_entNm = '{$resInfo["entNm"]}',
			mb_level = '3',
			 mb_password = '".get_encrypt_string($mb_password)."',
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
			mb_authCd = '{$resInfo["authCd"]}'";

	sql_query($sql);
	if ($member != get_member($_SESSION['ss_mb_reg'])) {

	}
} else {
	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["entNm"] = $_POST["mb_name"];
	$sendData["entCrn"] = $_POST["mb_giup_bnum"];
	$sendData["entZip"] = $_POST["mb_zip"];
	$sendData["entAddr"] = $_POST["mb_addr1"];
	$sendData["entAddrDetail"] = $_POST["mb_addr2"];
	$sendData["entPnum"] = $_POST["mb_hp"];
	$sendData["entMail"] = $_POST["mb_email"];
	$sendData["pw"] = $_POST["mb_password"];
		
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/account/entUpdate");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);
	
	if($res["errorYN"] == "Y"){
		alert($res["message"]);
	} else {
		$sendData = [];
		$sendData["usrId"] = $member["mb_id"];
		
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, 9001);
		curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/account/entInfo");
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$res = curl_exec($oCurl);
		curl_close($oCurl);
		$resInfo = json_decode($res, true);
		$resInfo = $resInfo["data"];
		
		$resInfo["entZip01"] = substr($resInfo["entZip"], 0, 3);
		$resInfo["entZip02"] = substr($resInfo["entZip"], 3, 2);
		
		sql_query("
			UPDATE {$g5["member_table"]} SET
				mb_name = '{$resInfo["entNm"]}',
				mb_nick = '{$resInfo["entNm"]}',
				mb_hp = '{$resInfo["entPnum"]}',
				mb_tel = '{$resInfo["entPnum"]}',
				mb_entId = '{$resInfo["entId"]}',
				mb_entNm = '{$resInfo["entNm"]}',
				 mb_zip1 = '{$resInfo["entZip01"]}',
				 mb_zip2 = '{$resInfo["entZip02"]}',
				 mb_addr1 = '{$resInfo["entAddr"]}',
				 mb_addr2 = '{$resInfo["entAddrDetail"]}',
				 mb_giup_bnum = '{$resInfo["entCrn"]}',
				 mb_giup_zip1 = '{$resInfo["entZip01"]}',
				 mb_giup_zip2 = '{$resInfo["entZip02"]}',
				 mb_giup_addr1 = '{$resInfo["entAddr"]}',
				 mb_giup_addr2 = '{$resInfo["entAddrDetail"]}',
				 mb_email = '{$resInfo["entMail"]}'
			WHERE mb_id = '{$resInfo["usrId"]}'
		");
		
		alert("수정이 완료되었습니다.");
	}
	
	goto_url("/bbs/member_confirm.php?url=register_form.php");
}   

// 사용자 코드 실행
@include_once ($member_skin_path.'/register_form_update.tail.skin.php');

unset($_SESSION['ss_cert_type']);
unset($_SESSION['ss_cert_no']);
unset($_SESSION['ss_cert_hash']);
unset($_SESSION['ss_cert_birth']);
unset($_SESSION['ss_cert_adult']);

if ($w == '') {
	if($pim) {
		goto_url(G5_HTTP_BBS_URL.'/register_result.php?pim='.$pim);
	} else {
		goto_url(G5_HTTP_BBS_URL.'/register_result.php');
	}
}
?>
