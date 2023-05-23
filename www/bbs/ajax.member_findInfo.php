<?php 
    /* // */
    /* // */
    /* // */
    /* // */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* //  *  */
    /* //  *  */
    /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! = Renewal Ver:1.0 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
    /* //  *  */
    /* //  *  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // */
    /* // */
    /* // */
    /* // */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 : \www\bbs\ajax.member_findInfo.php */
    /* // 파일 설명 : 신규파일 - 회원 아이디 / 비밀번호 찾기(ajax파일) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');


    function validateBusinessNumber($number) {
        // 사업자 번호 정규식 패턴
        $pattern = "/^\d{3}-\d{2}-\d{5}$/";
      
        // 정규식과 매치되는지 확인
        if (preg_match($pattern, $number)) {
            return true; // 형식이 맞는 경우
        } else {
            return false; // 형식이 맞지 않는 경우
        }
      }    


    $_referer = false;
    if( strpos($_SERVER["HTTP_REFERER"],"member_find_id.php") || strpos($_SERVER["HTTP_REFERER"],"member_find_pw.php") ){
        $_referer = true;
    };


    // POST값으로 mode에 값이 없을 경우 더 이상 처리 하지 않고, 에러 처리 한다.
    if( !$_POST['mode']  ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "오류가 발생하였습니다.";
        echo json_encode($result); exit();
    }

    if( !$_referer ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "잘못된 접근방식으로 실행하였습니다.";
        echo json_encode($result); exit();
    }



    if( $_POST['mode'] == "findID_SMS" ) {
        // --- --- --- --- --- --- --- --- --- ---
        // 아이디 찾기 - 인증번호 전송 부분
        // --- --- --- --- --- --- --- --- --- ---
        $tCertNum = mt_rand(1000, 9999);
              
        $tBNUM = $_POST['bnum'];
        $tHP = $_POST['hp'] ;

        if( !$tBNUM ) {
            $result["YN"] = "N";
            $result["YN_msg"] = "입려된 사업자번호를 확인할 수 없습니다.";    
            echo json_encode($result); exit();
        } else if( !$tHP ){ 
            $result["YN"] = "N";
            $result["YN_msg"] = "입력된 휴대폰번호를 확인할 수 없습니다.";    
            echo json_encode($result); exit();
        }

        
        // 23.05.23 - 서원 : 사업자번호 형식 체크
        if(!validateBusinessNumber($tBNUM)) {
            alert('사업자 번호 형식이 맞지 않습니다.',G5_BBS_URL."/register.php");
        }
        

        $temp = sql_fetch("SELECT mb_id FROM `g5_member` WHERE `mb_giup_bnum` = '{$tBNUM}' AND `mb_hp` = '{$tHP}' " );
        if( !$temp && !$temp['mb_id'] ) {
            $result["YN"] = "N";
            $result["YN_msg"] = "가입된 정보가 없습니다. \n사업자등록번호와 휴대전화번호를 확인 해 주세요.";    
            echo json_encode($result); exit();
        } else {

        }


        $sms_contents = '[이로움] 인증번호는 {0000} 입니다. '. $url;
        $sms_contents = str_replace("{0000}", $tCertNum, $sms_contents);

        // 핸드폰번호에서 숫자만 취한다
        $receive_number = preg_replace("/[^0-9]/", "", $tHP);  // 수신자번호 (회원님의 핸드폰번호)
        $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

        $strDest = array();
        $strDest[0] = $receive_number;

        include_once(G5_LIB_PATH.'/icode.sms.lib.php');

        $SMS = new SMS; // SMS 연결
        $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);        
        $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
        $SMS->Send();
        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.

        $result["YN"] = "Y";
        $result["YN_msg"] = "";
        $result["CertNum"] = $tCertNum;
        $result["FindID"] = $temp['mb_id'];

        echo json_encode($result); exit();





    } else if( $_POST['mode'] == "findID_SMSck" ) {
    // --- --- --- --- --- --- --- --- --- ---
    // 아이디 찾기 - 전송된 인증번호 확인 후 ID 프론트로 전달
    // --- --- --- --- --- --- --- --- --- ---





    } else if( $_POST['mode'] == "findID_MAIL" ) {
    // --- --- --- --- --- --- --- --- --- ---
    // 아이디 찾기 - 메일전송 ( 정보가 정확화면 해당 계정 아이디 정보 메일로 전달. )
    // --- --- --- --- --- --- --- --- --- ---

        $tBNUM = $_POST['bnum'];
        $tMAIL = $_POST['mail'] ;

        $email = get_email_address(trim($tMAIL));
        if (!$email) {
            $result["YN"] = "N";
            $result["YN_msg"] = "메일주소 오류입니다.";
    
            echo json_encode($result); exit();
        }


        // 23.05.23 - 서원 : 사업자번호 형식 체크
        if(!validateBusinessNumber($tBNUM)) {
            $result["YN"] = "N";
            $result["YN_msg"] = "사업자 번호 형식이 맞지 않습니다.";
            
            echo json_encode($result); exit();
        }


        $row = sql_fetch(" SELECT count(*) as cnt FROM `g5_member` WHERE `mb_email` = '{$tMAIL}' ");
        if ($row['cnt'] > 1) { 
            $result["YN"] = "N";
            $result["YN_msg"] = "동일한 메일주소가 2개 이상 존재합니다. \n관리자에게 문의하여 주십시오.";    
            echo json_encode($result); exit();
        }
        
        $mb = sql_fetch(" SELECT mb_no, mb_id, mb_name, mb_nick, mb_email, mb_datetime, mb_leave_date FROM `g5_member` WHERE `mb_giup_bnum` = '{$tBNUM}' AND `mb_email` = '{$tMAIL}' ");
        if (!$mb['mb_id'] || $mb['mb_leave_date']) { 
            $result["YN"] = "N";
            $result["YN_msg"] = "존재하지 않는 회원입니다.";    
            echo json_encode($result); exit();

        } else if (is_admin($mb['mb_id'])) { 
            $result["YN"] = "N";
            $result["YN_msg"] = "관리자 아이디는 접근 불가합니다.";    
            echo json_encode($result); exit();
         }
        
        // 링크 생성
        $href = G5_URL;
        // 제목 생성
        $subject = "[".$config['cf_title']."] 아이디 찾기 결과 안내 메일.";

        include_once(G5_LIB_PATH.'/mailer.lib.php');
        ob_start();
        include_once ($misc_skin_path.'/member_find_id_mailform.skin.php');
        $content = ob_get_contents();
        ob_end_clean();
        
        mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb['mb_email'], $subject, $content, 1);
        
        $result["YN"] = "Y";
        $result["YN_msg"] = "";

        echo json_encode($result); exit();





    } else if( $_POST['mode'] == "findPW_SMS" ) {
    // --- --- --- --- --- --- --- --- --- ---
    // 비밀번호 찾기 - 인증번호 전송 부분
    // --- --- --- --- --- --- --- --- --- ---
        $tCertNum = mt_rand(1000, 9999);
                
        $tID = $_POST['id'];
        $tBNUM = $_POST['bnum'];
        $tHP = $_POST['hp'] ;

        if( !$tBNUM ) {
            $result["YN"] = "N";
            $result["YN_msg"] = "입려된 사업자번호를 확인할 수 없습니다.";

            echo json_encode($result); exit();
        } else if( !$tHP ){ 
            $result["YN"] = "N";
            $result["YN_msg"] = "입력된 휴대폰번호를 확인할 수 없습니다.";

            echo json_encode($result); exit();
        }


        // 23.05.23 - 서원 : 사업자번호 형식 체크
        if(!validateBusinessNumber($tBNUM)) {
            $result["YN"] = "N";
            $result["YN_msg"] = "사업자 번호 형식이 맞지 않습니다.";
            
            echo json_encode($result); exit();
        }


        $temp = sql_fetch("SELECT mb_id FROM `g5_member` WHERE `mb_id` = '{$tID}' AND `mb_giup_bnum` = '{$tBNUM}' AND `mb_hp` = '{$tHP}' " );
        if( !$temp && !$temp['mb_id'] ) {
            $result["YN"] = "N";
            $result["YN_msg"] = "가입된 정보가 없습니다. \n사업자등록번호와 휴대전화번호를 확인 해 주세요.";

            echo json_encode($result); exit();
        } else {

        }


        $sms_contents = '[이로움] 인증번호는 {0000} 입니다. '. $url;
        $sms_contents = str_replace("{0000}", $tCertNum, $sms_contents);

        // 핸드폰번호에서 숫자만 취한다
        $receive_number = preg_replace("/[^0-9]/", "", $tHP);  // 수신자번호 (회원님의 핸드폰번호)
        $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

        $strDest = array();
        $strDest[0] = $receive_number;

        include_once(G5_LIB_PATH.'/icode.sms.lib.php');

        $SMS = new SMS; // SMS 연결
        $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);        
        $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
        $SMS->Send();
        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.

        $result["YN"] = "Y";
        $result["YN_msg"] = "";
        $result["CertNum"] = $tCertNum;

        echo json_encode($result); exit();





    } else if( $_POST['mode'] == "findPW_SMSck" ) {
    // --- --- --- --- --- --- --- --- --- ---
    // 비밀번호 찾기 - 전송된 인증 번호 확인 후 기존 비밀번호 리셋 & 초기화 UI 오픈 명령 전달
    // --- --- --- --- --- --- --- --- --- ---




    
    } else if( $_POST['mode'] == "findPW_PWRESET" ) {
    // --- --- --- --- --- --- --- --- --- ---
    // 비밀번호 찾기 - 초기화로 인한 비밀번호 재입력 부분 조건확인 후 회원 비밀번호 변경 처리
    // --- --- --- --- --- --- --- --- --- ---
        $tID = $_POST['id'];
        $tBNUM = $_POST['bnum'];
        $tHP = $_POST['hp'] ;
        $tPW1 = $_POST['pw1'] ;
        $tPW2 = $_POST['pw2'] ;
        
        if( !$tID ) {
            $result["YN"] = "N";
            $result["YN_msg"] = "입려된 아이디를 확인할 수 없습니다.";
            echo json_encode($result); exit();
        } else if( !$tBNUM ) {
            $result["YN"] = "N";
            $result["YN_msg"] = "입려된 사업자번호를 확인할 수 없습니다.";
            echo json_encode($result); exit();
        } else if( !$tHP ){ 
            $result["YN"] = "N";
            $result["YN_msg"] = "입력된 휴대폰번호를 확인할 수 없습니다.";
            echo json_encode($result); exit();
        } else if( !$tPW1 || !$tPW2 ){ 
            $result["YN"] = "N";
            $result["YN_msg"] = "입력된 비밀번호를 확인할 수 없습니다.";
            echo json_encode($result); exit();
        }


        // 23.05.23 - 서원 : 사업자번호 형식 체크
        if(!validateBusinessNumber($tBNUM)) {
            $result["YN"] = "N";
            $result["YN_msg"] = "사업자 번호 형식이 맞지 않습니다.";
            
            echo json_encode($result); exit();
        }


        $temp = sql_fetch("SELECT mb_id FROM `g5_member` WHERE `mb_id` = '{$tID}' AND `mb_giup_bnum` = '{$tBNUM}' AND `mb_hp` = '{$tHP}' " );
        if( !$temp && !$temp['mb_id'] ) {
            $result["YN"] = "N";
            $result["YN_msg"] = "가입된 정보가 없습니다. \n아이디, 사업자등록번호, 휴대전화번호를 확인 해 주세요.";
            echo json_encode($result); exit();
        } 


        if( $tPW1 == $tPW2 ){
            sql_query(" UPDATE `g5_member` SET `mb_password` = '" . get_encrypt_string($tPW1) . "' WHERE `mb_id` = '{$tID}' AND `mb_giup_bnum` = '{$tBNUM}' AND `mb_hp` = '{$tHP}' ");

            $result["YN"] = "Y";
            $result["YN_msg"] = "";
            echo json_encode($result); exit();
        } else {
            $result["YN"] = "N";
            $result["YN_msg"] = "입력된 비밀번호가 정확하지 않습니다. \n다시 확인해 주세요.";
            echo json_encode($result); exit();
        }





    } else if( $_POST['mode'] == "findPW_MAIL" ) {
    // --- --- --- --- --- --- --- --- --- ---
    // 비밀번호 찾기 - 초기화 후 해당 비밀번호 메일로 전달
    // --- --- --- --- --- --- --- --- --- ---
        $tID = $_POST['id'];
        $tBNUM = $_POST['bnum'];
        $tMAIL = $_POST['mail'] ;

        $email = get_email_address(trim($tMAIL));
        if (!$email) {
            $result["YN"] = "N";
            $result["YN_msg"] = "메일주소 오류입니다.";
    
            echo json_encode($result); exit();
        }


        // 23.05.23 - 서원 : 사업자번호 형식 체크
        if(!validateBusinessNumber($tBNUM)) {
            $result["YN"] = "N";
            $result["YN_msg"] = "사업자 번호 형식이 맞지 않습니다.";

            echo json_encode($result); exit();
        }


        $row = sql_fetch(" SELECT count(*) as cnt FROM `g5_member` WHERE `mb_email` = '{$tMAIL}' ");
        if ($row['cnt'] > 1) { 
            $result["YN"] = "N";
            $result["YN_msg"] = "동일한 메일주소가 2개 이상 존재합니다. \n관리자에게 문의하여 주십시오.";
    
            echo json_encode($result); exit();
        }
        
        $mb = sql_fetch(" SELECT mb_no, mb_id, mb_name, mb_nick, mb_email, mb_datetime, mb_leave_date FROM `g5_member` WHERE `mb_id` = '{$tID}' AND  `mb_giup_bnum` = '{$tBNUM}' AND `mb_email` = '{$tMAIL}' ");
        if (!$mb['mb_id'] || $mb['mb_leave_date']) { 
            $result["YN"] = "N";
            $result["YN_msg"] = "존재하지 않는 회원입니다.";
    
            echo json_encode($result); exit();

        } else if (is_admin($mb['mb_id'])) { 
            $result["YN"] = "N";
            $result["YN_msg"] = "관리자 아이디는 접근 불가합니다.";
    
            echo json_encode($result); exit();
        }
        

        // 임시비밀번호 발급
        $change_password = rand(100000, 999999);
        $mb_lost_certify = get_encrypt_string($change_password);

        // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
        $mb_nonce = md5(pack('V*', rand(), rand(), rand(), rand()));

        // 임시비밀번호와 난수를 mb_lost_certify 필드에 저장
        sql_query(" UPDATE `g5_member` set `mb_lost_certify` = '$mb_nonce $mb_lost_certify' WHERE mb_id = '{$mb['mb_id']}' ");

        // 인증 링크 생성
        $href = G5_BBS_URL.'/password_lost_certify.php?mb_no='.$mb['mb_no'].'&amp;mb_nonce='.$mb_nonce;
        $subject = "[".$config['cf_title']."] 요청하신 회원정보 찾기 안내 메일입니다.";

        include_once(G5_LIB_PATH.'/mailer.lib.php');
        ob_start();
        include_once ($misc_skin_path.'/member_find_pw_mailform.skin.php');
        $content = ob_get_contents();
        ob_end_clean();
        
        mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb['mb_email'], $subject, $content, 1);
        
        $result["YN"] = "Y";
        $result["YN_msg"] = "";

        echo json_encode($result); exit();





    } else {

    }
  
  ?>