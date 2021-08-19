<?php
    include_once('./_common.php');
    $sendData=[];
    $sendData['usrId'] =$_POST['mb_id'];
    $resInfo = get_eroumcare(EROUMCARE_API_ENT_ACCOUNT, $sendData);
    
    //데이터 성공시
    if($resInfo['message']=="SUCCESS"){
        $resInfo = $resInfo["data"];
        $resInfo["usrZip01"] = substr($resInfo["usrZip"], 0, 3);
        $resInfo["usrZip02"] = substr($resInfo["usrZip"], 3, 2);
        $resInfo["entZip01"] = substr($resInfo["entZip"], 0, 3);
        $resInfo["entZip02"] = substr($resInfo["entZip"], 3, 2);
        $mb_password = trim($_POST['mb_password']);
        $crnFile_name ="";
        $sealFile_name ="";
        $mbCheck = sql_fetch("SELECT * FROM {$g5["member_table"]} WHERE mb_id = '".$_POST["mb_id"]."'");
        if($mbCheck['sealFile']){
            $sealFile_name = $mbCheck['sealFile'];
        }
        if($mbCheck['crnFile']){
            $crnFile_name = $mbCheck['crnFile'];
        }
        //서버 최대 용량 10Mb
        $max_file_size = 1024*1024*10;
        //사업자등록증
        if($_FILES['crnFile']['tmp_name']){
            // 변수 정리
            $uploads_dir = G5_DATA_PATH.'/file/member/license';
            $error = $_FILES['crnFile']['error'];
            $name = $_FILES['crnFile']['name'];
            $allowed_ext = array('exe');
            $ext = array_pop(explode('.', $name));
            $temp = explode(".", $_FILES["crnFile"]["name"]);
            $crnFile_name = $_POST['mb_id'].'_'.round(microtime(true)) . '.' . end($temp);
            $crnFile = "$uploads_dir/$crnFile_name";
            // 오류 확인
            if( $error != UPLOAD_ERR_OK ) {
                switch( $error ) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        alert('파일이 너무 큽니다.',G5_BBS_URL."/register.php");
                        break;
                    exit;
                    default:
                        alert('파일이 제대로 업로드되지 않았습니다.',G5_BBS_URL."/register.php");
                    exit;
                }
                exit;
            }
            if($file['size'] >= $max_file_size) {
                alert('10Mb 까지만 업로드 가능합니다.',G5_BBS_URL."/register.php");
                exit;
            }
            // 확장자 확인
            if( in_array($ext, $allowed_ext) ) {
                alert('허용되지 않는 확장자입니다',G5_BBS_URL."/register.php");
                exit;
            }
        }
        //직인
        if($_FILES['sealFile']['tmp_name']){
            // 변수 정리
            $uploads_dir = G5_DATA_PATH.'/file/member/stamp';
            $error = $_FILES['sealFile']['error'];
            $name = $_FILES['sealFile']['name'];
            $allowed_ext = array('exe');
            $ext = array_pop(explode('.', $name));
            $temp = explode(".", $_FILES["sealFile"]["name"]);
            $sealFile_name = $_POST['mb_id'].'_'.round(microtime(true)) . '.' . end($temp);
            $sealFile = "$uploads_dir/$sealFile_name";
            // 오류 확인
            if( $error != UPLOAD_ERR_OK ) {
                switch( $error ) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        alert('파일이 너무 큽니다.',G5_BBS_URL."/register.php");
                        break;
                    exit;
                    default:
                    alert('파일이 제대로 업로드되지 않았습니다.',G5_BBS_URL."/register.php");
                    exit;
                }
                exit;
            }
            if($file['size'] >= $max_file_size) {
                alert('10Mb 까지만 업로드 가능합니다.',G5_BBS_URL."/register.php");
                exit;
            }
            // 확장자 확인
            if( in_array($ext, $allowed_ext) ) {
                alert('허용되지 않는 확장자입니다',G5_BBS_URL."/register.php");
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
                    mb_entConAcc01 = '{$_POST["mb_entConAcc01"]}',
                    mb_entConAcc02 = '{$_POST["mb_entConAcc02"]}',
                    mb_giup_bname = '{$resInfo["entNm"]}',
                    sealFile = '".$sealFile_name."',
                    crnFile = '".$crnFile_name."',
			        mb_ent_num = '{$mb_ent_num}',
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
                    mb_entConAcc01 = '{$_POST["mb_entConAcc01"]}',
                    mb_entConAcc02 = '{$_POST["mb_entConAcc02"]}',
                    mb_giup_bname = '{$resInfo["entNm"]}',
                    sealFile = '".$sealFile_name."',
                    crnFile = '".$crnFile_name."',
                    mb_ent_num = '{$mb_ent_num}'
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
        //성공 결과 페이지 이동
        if($w){
            alert('회원 정보 수정이 완료되었습니다.',G5_URL);
        }else{
            $mb = get_member($_POST['mb_id']);
            #로그인 체크
            if ($_POST["mb_id"] == "admin" && !$is_social_password_check && (!$mb['mb_id'] || !login_password_check($mb, $mb_password, $mb['mb_password'])) ) {
                alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
            }
            // 차단된 아이디인가?
            if ($mb['mb_intercept_date'] && $mb['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME)) {
                $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_intercept_date']);
                alert('회원님의 아이디는 접근이 금지되어 있습니다.\n처리일 : '.$date);
            }
            // 탈퇴한 아이디인가?
            if ($mb['mb_leave_date'] && $mb['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME)) {
                $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_leave_date']);
                alert('탈퇴한 아이디이므로 접근하실 수 없습니다.\n탈퇴일 : '.$date);
            }
            // 회원아이디 세션 생성
            set_session('ss_mb_id', $mb['mb_id']);
            // FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
            set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
            //이로움 통합시스템 이동
            $_SESSION[$mb_id]=$mb_password;
            alert('회원가입이 완료되었습니다.',G5_BBS_URL."/register_result.php");
            }
    }else{
        //실패 이동
        if($w){
            alert('회원 정보 수정에 실패하였습니다.',G5_BBS_URL."/register.php");
        }else{
            alert('회원가입에 실패하였습니다.',G5_BBS_URL."/register.php");
        }
    }
?>
