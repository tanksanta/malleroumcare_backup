<?php
    include_once('./_common.php');

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/ent/account");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res_send = curl_exec($oCurl);
	curl_close($oCurl);
    $resInfo = json_decode($res, true);
    $resInfo = $resInfo["data"];
    $resInfo["usrZip01"] = substr($resInfo["usrZip"], 0, 3);
    $resInfo["usrZip02"] = substr($resInfo["usrZip"], 3, 2);
    $resInfo["entZip01"] = substr($resInfo["entZip"], 0, 3);
    $resInfo["entZip02"] = substr($resInfo["entZip"], 3, 2);

    $mb_password = trim($_POST['usrPw']);
    $mb_password2 =  base64_encode ($mb_password) ;
    $crnFile_name ="";
    $sealFile_name ="";
    $mbCheck = sql_fetch("SELECT * FROM {$g5["member_table"]} WHERE mb_id = '".$_POST["usrId"]."'");

    if($mbCheck['sealFile']){
        $sealFile_name = $mbCheck['sealFile'];
    }
    if($mbCheck['crnFile']){
        $crnFile_name = $mbCheck['crnFile'];
    }
    
    //사업자등록증
    if($_FILES['crnFile']['tmp_name']){
        $max_file_size = 2097152;
        // 변수 정리
        $uploads_dir = G5_DATA_PATH.'/file/member/license';
        $error = $_FILES['crnFile']['error'];
        $name = $_FILES['crnFile']['name'];
        $allowed_ext = array('exe');
        $ext = array_pop(explode('.', $name));
        $temp = explode(".", $_FILES["crnFile"]["name"]);
        $crnFile_name = $_POST['usrId'].'_'.round(microtime(true)) . '.' . end($temp);
        $crnFile = "$uploads_dir/$crnFile_name";
        // 오류 확인
        if( $error != UPLOAD_ERR_OK ) {
            switch( $error ) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo 'N';
                    break;
                exit;
                default:
                echo 'N';;
                exit;
            }
            exit;
        }
        if($file['size'] >= $max_file_size) {
            echo 'N';;
            exit;
        }
        // 확장자 확인
        if( in_array($ext, $allowed_ext) ) {
            echo 'N';;
            exit;
        }
    }


    //직인
    if($_FILES['sealFile']['tmp_name']){
        $max_file_size = 2097152;
        // 변수 정리
        $uploads_dir = G5_DATA_PATH.'/file/member/stamp';
        $error = $_FILES['sealFile']['error'];
        $name = $_FILES['sealFile']['name'];
        $allowed_ext = array('exe');
        $ext = array_pop(explode('.', $name));
        $temp = explode(".", $_FILES["sealFile"]["name"]);
        $sealFile_name = $_POST['usrId'].'_'.round(microtime(true)) . '.' . end($temp);
        $sealFile = "$uploads_dir/$sealFile_name";
        // 오류 확인
        if( $error != UPLOAD_ERR_OK ) {
            switch( $error ) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo 'N';
                    break;
                exit;
                default:
                echo 'N';
                exit;
            }
            exit;
        }
        if($file['size'] >= $max_file_size) {
            echo 'N';
            exit;
        }
        // 확장자 확인
        if( in_array($ext, $allowed_ext) ) {
            echo 'N';
            exit;
        }
    }

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
                mb_entConAcc01 = '{$resInfo["entConAcco1"]}',
                mb_entConAcc02 = '{$resInfo["entConAcco2"]}',
                mb_giup_bname = '{$resInfo["entNm"]}',
                sealFile = '".$sealFile_name."',
                crnFile = '".$crnFile_name."',
                mb_datetime = '".G5_TIME_YMDHIS."',
                mb_password2 = '".$mb_password2."'
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
                mb_giup_bname = '{$resInfo["entNm"]}',
                sealFile = '".$sealFile_name."',
                crnFile = '".$crnFile_name."',
                mb_password2 = '".$mb_password2."'
            WHERE mb_id = '{$resInfo["usrId"]}'
        ");
    }
    //파일저장
    if($_FILES['crnFile']['tmp_name']){
        move_uploaded_file( $_FILES['crnFile']['tmp_name'], $crnFile);
    }
    if($_FILES['sealFile']['tmp_name']){
        move_uploaded_file( $_FILES['sealFile']['tmp_name'], $sealFile);
    }



	echo $res_send;

?>
