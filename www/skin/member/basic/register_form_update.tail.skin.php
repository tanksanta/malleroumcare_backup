<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//----------------------------------------------------------
// SMS 문자전송 시작
//----------------------------------------------------------

$sms_contents = $default['de_sms_cont1'];
$sms_contents = str_replace("{이름}", $mb_name, $sms_contents);
$sms_contents = str_replace("{회원아이디}", $mb_id, $sms_contents);
$sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);

// 핸드폰번호에서 숫자만 취한다
$receive_number = preg_replace("/[^0-9]/", "", $mb_hp);  // 수신자번호 (회원님의 핸드폰번호)
$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

if ($w == "" && $default['de_sms_use1'] && $receive_number)
{
	if ($config['cf_sms_use'] == 'icode')
	{
		if($config['cf_sms_type'] == 'LMS') {
            include_once(G5_LIB_PATH.'/icode.lms.lib.php');

            $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

            // SMS 모듈 클래스 생성
            if($port_setting !== false) {
                $SMS = new LMS;
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                $strDest     = array();
                $strDest[]   = $receive_number;
                $strCallBack = $send_number;
                $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                $strSubject  = '';
                $strURL      = '';
                $strData     = iconv_euckr($sms_contents);
                $strDate     = '';
                $nCount      = count($strDest);

                $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }
        } else {
            include_once(G5_LIB_PATH.'/icode.sms.lib.php');

            $SMS = new SMS; // SMS 연결
            $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
            $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
            $SMS->Send();
            $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
        }
	}
}
//----------------------------------------------------------
// SMS 문자전송 끝
//----------------------------------------------------------

$mb_id = trim($_POST['mb_id']) ? trim($_POST['mb_id']) : $member['mb_id'];

$mm_nos = array();
for($i=0;$i<count($_POST['mm_name']);$i++) {
    if ( !$_POST['mm_name'][$i] ) continue;
    if ( $_POST['mm_no'][$i] ) {
        // $sql = "UPDATE g5_member_giup_manager SET mm_name = '{$_POST['mm_name'][$i]}', mm_tel = '{$_POST['mm_tel'][$i]}' WHERE mm_no = '{$_POST['mm_no'][$i]}'";
        // $sql = "UPDATE g5_member_giup_manager SET mm_name = '{$_POST['mm_name'][$i]}', mm_tel = '{$_POST['mm_tel'][$i]}', mm_hp = '{$_POST['mm_hp'][$i]}', mm_hp_extension = '{$_POST['mm_hp_extension'][$i]}', mm_thezone = '{$_POST['mm_thezone'][$i]}', mm_email = '{$_POST['mm_email'][$i]}' WHERE mm_no = '{$_POST['mm_no'][$i]}'";
        $sql = "UPDATE g5_member_giup_manager SET 
                    mm_name = '{$_POST['mm_name'][$i]}', 
                    mm_part = '{$_POST['mm_part'][$i]}', 
                    mm_rank = '{$_POST['mm_rank'][$i]}', 
                    mm_work = '{$_POST['mm_work'][$i]}', 
                    mm_tel = '{$_POST['mm_tel'][$i]}', 
                    mm_hp = '{$_POST['mm_hp'][$i]}', 
                    mm_hp_extension = '{$_POST['mm_hp_extension'][$i]}', 
                    mm_thezone = '{$_POST['mm_thezone'][$i]}', 
                    mm_email = '{$_POST['mm_email'][$i]}' 
                WHERE mm_no = '{$_POST['mm_no'][$i]}'";
        $mm_nos[] = " mm_no != '{$_POST['mm_no'][$i]}' ";
        sql_query($sql);
    }else{
        // $sql = "INSERT g5_member_giup_manager SET mb_id = '{$mb_id}', mm_name = '{$_POST['mm_name'][$i]}', mm_tel = '{$_POST['mm_tel'][$i]}'";
        // $sql = "INSERT g5_member_giup_manager SET mb_id = '{$mb_id}', mm_name = '{$_POST['mm_name'][$i]}', mm_tel = '{$_POST['mm_tel'][$i]}', mm_hp = '{$_POST['mm_hp'][$i]}', mm_hp_extension = '{$_POST['mm_hp_extension'][$i]}', mm_thezone = '{$_POST['mm_thezone'][$i]}', mm_email = '{$_POST['mm_email'][$i]}'";
        $sql = "INSERT g5_member_giup_manager SET 
                    mb_id = '{$mb_id}', 
                    mm_name = '{$_POST['mm_name'][$i]}', 
                    mm_part = '{$_POST['mm_part'][$i]}', 
                    mm_rank = '{$_POST['mm_rank'][$i]}', 
                    mm_work = '{$_POST['mm_work'][$i]}', 
                    mm_tel = '{$_POST['mm_tel'][$i]}', 
                    mm_hp = '{$_POST['mm_hp'][$i]}', 
                    mm_hp_extension = '{$_POST['mm_hp_extension'][$i]}', 
                    mm_thezone = '{$_POST['mm_thezone'][$i]}', 
                    mm_email = '{$_POST['mm_email'][$i]}'";

        sql_query($sql);
        $mm_no = sql_insert_id();
        $mm_nos[] = " mm_no != '{$mm_no}' ";
    }
}

$mm_nos_query = implode(' AND ', $mm_nos);
sql_query("DELETE FROM g5_member_giup_manager WHERE mb_id = '{$mb_id}' AND ( {$mm_nos_query} )");

?>
