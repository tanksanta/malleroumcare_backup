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
echo "<script> alert({$mb_id}) </script>";

$mb_password    = "1234";
$mb_password_re = "1234";
$mb_name        = "hsy";
$mb_level       = "2";
//$mb_nick        = trim($_POST['mb_nick']);
$mb_nick        = "hsy";
$mb_email       = "";
$mb_sex         = "";
$mb_birth       = "";
$mb_homepage    = "";
$mb_tel         = "";
$mb_hp          = "";
$mb_fax         = "";
$mb_zip1        = "";
$mb_zip2        = "";
$mb_addr1       = "";
$mb_addr2       = "";
$mb_addr3       = "";
$mb_addr_jibeon = "";
$mb_signature   = "";
$mb_profile     = "";
$mb_recommend   = "";
$mb_mailling    = "";
$mb_sms         = "";
$mb_1           = "";
$mb_2           = "";
$mb_3           = "";
$mb_4           = "";
$mb_5           = "";
$mb_6           = "";
$mb_7           = "";
$mb_8           = "";
$mb_9           = "";
$mb_10          = "";
$mb_type        = "";

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

$sql = "insert into {$g5['member_table']}
        set mb_id = '{$mb_id}',
        mb_password = '".get_encrypt_string($mb_password)."',
        mb_name = '{$mb_name}'";

sql_query($sql);
if ($member != get_member($_SESSION['ss_mb_reg'])) {
    
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
