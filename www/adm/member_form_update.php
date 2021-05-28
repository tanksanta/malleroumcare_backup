<?php
$sub_menu = "200100";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
$w = 'u';
if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');

// check_admin_token();

$mb_id = trim($_POST['mb_id']);

$mm_nos = array();
// for($i=0;$i<count($_POST['mm_name']);$i++) {
//     if ( !$_POST['mm_name'][$i] ) continue;
//     if ( $_POST['mm_no'][$i] ) {
//         $sql = "UPDATE g5_member_giup_manager SET
//             mm_name = '{$_POST['mm_name'][$i]}',
//             mm_part = '{$_POST['mm_part'][$i]}',
//             mm_rank = '{$_POST['mm_rank'][$i]}',
//             mm_work = '{$_POST['mm_work'][$i]}',
//             mm_tel = '{$_POST['mm_tel'][$i]}',
//             mm_hp = '{$_POST['mm_hp'][$i]}',
//             mm_hp_extension = '{$_POST['mm_hp_extension'][$i]}',
//             mm_thezone = '{$_POST['mm_thezone'][$i]}',
//             mm_email = '{$_POST['mm_email'][$i]}'
//             WHERE mm_no = '{$_POST['mm_no'][$i]}'";
//         $mm_nos[] = " mm_no != '{$_POST['mm_no'][$i]}' ";
//         sql_query($sql);
//     }else{
//         $sql = "INSERT g5_member_giup_manager SET
//                     mb_id = '{$mb_id}',
//                     mm_name = '{$_POST['mm_name'][$i]}',
//                     mm_part = '{$_POST['mm_part'][$i]}',
//                     mm_rank = '{$_POST['mm_rank'][$i]}',
//                     mm_work = '{$_POST['mm_work'][$i]}',
//                     mm_tel = '{$_POST['mm_tel'][$i]}',
//                     mm_hp = '{$_POST['mm_hp'][$i]}',
//                     mm_hp_extension = '{$_POST['mm_hp_extension'][$i]}',
//                     mm_thezone = '{$_POST['mm_thezone'][$i]}',
//                     mm_email = '{$_POST['mm_email'][$i]}'";
//         sql_query($sql);
//         $mm_no = sql_insert_id();
//         $mm_nos[] = " mm_no != '{$mm_no}' ";
//     }
// }

$mm_nos_query = implode(' AND ', $mm_nos);
sql_query("DELETE FROM g5_member_giup_manager WHERE mb_id = '{$mb_id}' AND ( {$mm_nos_query} )");

// 휴대폰번호 체크
$mb_hp = hyphen_hp_number($_POST['mb_hp']);
//if($mb_hp) {
//    $result = exist_mb_hp($mb_hp, $mb_id);
//    if ($result)
//        alert($result);
//}

// 인증정보처리
if($_POST['mb_certify_case'] && $_POST['mb_certify']) {
    $mb_certify = $_POST['mb_certify_case'];
    $mb_adult = $_POST['mb_adult'];
} else {
    $mb_certify = '';
    $mb_adult = 0;
}

@mkdir(G5_DATA_PATH."/member_partner", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/member_partner", G5_DIR_PERMISSION);

$sql = "SELECT * FROM {$g5['member_table']} where mb_id = '{$mb_id}'";
$result = sql_fetch($sql);

// 계약서 파일1
$mb_partner_file1      = $_FILES['mb_partner_file1']['tmp_name'];
$mb_partner_file1_name = $_FILES['mb_partner_file1']['name'] ? $mb_id . '_' . $_FILES['mb_partner_file1']['name'] : $result['mb_partner_file1'];

if ($_POST['mb_partner_file1_del']) {
    @unlink(G5_DATA_PATH."/member_partner/" . $result['member_partner_file1']);
    $mb_partner_file1_name = '';
}

if ($_FILES['mb_partner_file1']['name']) {
    upload_file($_FILES['mb_partner_file1']['tmp_name'], $mb_partner_file1_name, G5_DATA_PATH."/member_partner");
}

// 계약서 파일2
$mb_partner_file2      = $_FILES['mb_partner_file2']['tmp_name'];
$mb_partner_file2_name = $_FILES['mb_partner_file2']['name'] ? $mb_id . '_' . $_FILES['mb_partner_file2']['name'] : $result['mb_partner_file2'];

if ($_POST['mb_partner_file2_del']) {
    @unlink(G5_DATA_PATH."/member_partner/" . $result['member_partner_file2']);
    $mb_partner_file2_name = '';
}

if ($_FILES['mb_partner_file2']['name']) {
    upload_file($_FILES['mb_partner_file2']['tmp_name'], $mb_partner_file2_name, G5_DATA_PATH."/member_partner");
}

// 계약서 파일3
$mb_partner_file3      = $_FILES['mb_partner_file3']['tmp_name'];
$mb_partner_file3_name = $_FILES['mb_partner_file3']['name'] ? $mb_id . '_' . $_FILES['mb_partner_file3']['name'] : $result['mb_partner_file3'];

if ($_POST['mb_partner_file3_del']) {
    @unlink(G5_DATA_PATH."/member_partner/" . $result['member_partner_file1']);
    $mb_partner_file3_name = '';
}

if ($_FILES['mb_partner_file3']['name']) {
    upload_file($_FILES['mb_partner_file3']['tmp_name'], $mb_partner_file3_name, G5_DATA_PATH."/member_partner");
}

$mb_zip1 = substr($_POST['mb_zip'], 0, 3);
$mb_zip2 = substr($_POST['mb_zip'], 3);

$mb_email = isset($_POST['mb_email']) ? get_email_address(trim($_POST['mb_email'])) : '';
$mb_nick = isset($_POST['mb_nick']) ? trim(strip_tags($_POST['mb_nick'])) : '';

//기업
$mb_giup_type           = isset($_POST['mb_giup_type'])             ? trim($_POST['mb_giup_type'])           : "";
$mb_giup_bname           = isset($_POST['mb_giup_bname'])             ? trim($_POST['mb_giup_bname'])           : "";
$mb_giup_boss_name           = isset($_POST['mb_giup_boss_name'])             ? trim($_POST['mb_giup_boss_name'])           : "";
$mb_giup_btel           = isset($_POST['mb_giup_btel'])             ? trim($_POST['mb_giup_btel'])           : "";
$mb_giup_bnum           = isset($_POST['mb_giup_bnum'])             ? trim($_POST['mb_giup_bnum'])           : "";
$mb_giup_sbnum           = isset($_POST['mb_giup_sbnum'])             ? trim($_POST['mb_giup_sbnum'])           : "";
$mb_giup_sbnum_explain    = isset($_POST['mb_giup_sbnum_explain'])      ? trim($_POST['mb_giup_sbnum_explain'])           : "";
$mb_giup_buptae           = isset($_POST['mb_giup_buptae'])             ? trim($_POST['mb_giup_buptae'])           : "";
$mb_giup_bupjong           = isset($_POST['mb_giup_bupjong'])             ? trim($_POST['mb_giup_bupjong'])           : "";
$mb_giup_tax_email           = isset($_POST['mb_giup_tax_email'])             ? trim($_POST['mb_giup_tax_email'])           : "";
$mb_giup_manager_name           = isset($_POST['mb_giup_manager_name'])             ? trim($_POST['mb_giup_manager_name'])           : "";
$mb_giup_manager_tel           = isset($_POST['mb_giup_manager_tel'])             ? trim($_POST['mb_giup_manager_tel'])           : "";

$mb_giup_zip1        = isset($_POST['mb_giup_zip'])           ? substr(trim($_POST['mb_giup_zip']), 0, 3) : "";
$mb_giup_zip2        = isset($_POST['mb_giup_zip'])           ? substr(trim($_POST['mb_giup_zip']), 3)    : "";
$mb_giup_addr1       = isset($_POST['mb_giup_addr1'])         ? trim($_POST['mb_giup_addr1'])       : "";
$mb_giup_addr2       = isset($_POST['mb_giup_addr2'])         ? trim($_POST['mb_giup_addr2'])       : "";
$mb_giup_addr3       = isset($_POST['mb_giup_addr3'])         ? trim($_POST['mb_giup_addr3'])       : "";
$mb_giup_addr_jibeon = isset($_POST['mb_giup_addr_jibeon'])   ? trim($_POST['mb_giup_addr_jibeon']) : "";

$mb_giup_zip1        = preg_replace('/[^0-9]/', '', $mb_giup_zip1);
$mb_giup_zip2        = preg_replace('/[^0-9]/', '', $mb_giup_zip2);
$mb_giup_addr1       = clean_xss_tags($mb_giup_addr1);
$mb_giup_addr2       = clean_xss_tags($mb_giup_addr2);
$mb_giup_addr3       = clean_xss_tags($mb_giup_addr3);
$mb_giup_addr_jibeon = preg_match("/^(N|R)$/", $mb_giup_addr_jibeon) ? $mb_giup_addr_jibeon : '';

$mb_thezone = isset($_POST['mb_thezone'])             ? trim($_POST['mb_thezone'])           : "";
if ($w == '') {
    $mb_thezone = get_uniqid_member();
}
$mb_partner_date_pay_date = isset($_POST['mb_partner_date_pay_date'])             ? trim($_POST['mb_partner_date_pay_date'])           : "";

$_POST['mb_dealer'] = $_POST['mb_dealer'] ? (int)$_POST['mb_dealer'] : 0;

if ($msg = valid_mb_nick($mb_nick))     alert($msg, "", true, true);




$mb_hp = $_POST['mb_hp1']."-".$_POST['mb_hp2']."-".$_POST['mb_hp3'];
$mb_fax = $_POST['mb_fax1']."-".$_POST['mb_fax2']."-".$_POST['mb_fax3'];
$mb_giup_btel = $_POST['mb_tel1']."-".$_POST['mb_tel2']."-".$_POST['mb_tel3'];
$mb_tel = $_POST['mb_tel1']."-".$_POST['mb_tel2']."-".$_POST['mb_tel3'];

$sql_common = "  mb_name = '{$_POST['mb_name']}',
                 mb_nick = '{$mb_nick}',
                 mb_email = '{$mb_email}',
                 mb_homepage = '{$_POST['mb_homepage']}',
                 mb_tel = '{$mb_tel}',
                 mb_hp = '{$mb_hp}',
                 mb_fax = '{$mb_fax}',
                 mb_certify = '{$mb_certify}',
                 mb_adult = '{$mb_adult}',
                 mb_zip1 = '$mb_zip1',
                 mb_zip2 = '$mb_zip2',
                 mb_addr1 = '{$_POST['mb_addr1']}',
                 mb_addr2 = '{$_POST['mb_addr2']}',
                 mb_addr3 = '{$_POST['mb_addr3']}',
                 mb_addr_jibeon = '{$_POST['mb_addr_jibeon']}',
                 mb_signature = '{$_POST['mb_signature']}',
                 mb_leave_date = '{$_POST['mb_leave_date']}',
                 mb_intercept_date='{$_POST['mb_intercept_date']}',
                 mb_memo = '{$_POST['mb_memo']}',
                 mb_mailling = '{$_POST['mb_mailling']}',
                 mb_sms = '{$_POST['mb_sms']}',
                 mb_open = '{$_POST['mb_open']}',
                 mb_profile = '{$_POST['mb_profile']}',
                 mb_level = '{$_POST['mb_level']}',
                 mb_1 = '{$_POST['mb_1']}',
                 mb_2 = '{$_POST['mb_2']}',
                 mb_3 = '{$_POST['mb_3']}',
                 mb_4 = '{$_POST['mb_4']}',
                 mb_5 = '{$_POST['mb_5']}',
                 mb_6 = '{$_POST['mb_6']}',
                 mb_7 = '{$_POST['mb_7']}',
                 mb_8 = '{$_POST['mb_8']}',
                 mb_9 = '{$_POST['mb_9']}',
                 mb_10 = '{$_POST['mb_10']}',
                 mb_giup_type = '{$mb_giup_type}',
                 mb_giup_bname = '{$mb_giup_bname}',
                 mb_giup_boss_name = '{$mb_giup_boss_name}',
                 mb_giup_btel = '{$mb_giup_btel}',
                 mb_giup_bnum = '{$mb_giup_bnum}',
                 mb_giup_sbnum = '{$mb_giup_sbnum}',
                 mb_giup_sbnum_explain = '{$mb_giup_sbnum_explain}',
                 mb_giup_buptae = '{$mb_giup_buptae}',
                 mb_giup_bupjong = '{$mb_giup_bupjong}',
                 mb_giup_addr1 = '{$mb_giup_addr1}',
                 mb_giup_addr2 = '{$mb_giup_addr2}',
                 mb_giup_addr3 = '{$mb_giup_addr3}',
                 mb_giup_addr_jibeon = '{$mb_giup_addr_jibeon}',
                 mb_giup_zip1 = '{$mb_giup_zip1}',
                 mb_giup_zip2 = '{$mb_giup_zip2}',
                 mb_giup_tax_email = '{$mb_giup_tax_email}',
                 mb_giup_manager_name = '{$mb_giup_manager_name}',
                 mb_giup_manager_tel = '{$mb_giup_manager_tel}',
                 mb_partner_auth = '{$_POST['mb_partner_auth']}',
                 mb_partner_date = '{$_POST['mb_partner_date']}',
                 mb_partner_date_auto = '{$_POST['mb_partner_date_auto']}',
                 mb_partner_date_auto_extend_date = '{$_POST['mb_partner_date_auto_extend_date']}',
                 mb_partner_date_auto_buy_price = '{$_POST['mb_partner_date_auto_buy_price']}',
                 mb_partner_date_auto_buy_cnt = '{$_POST['mb_partner_date_auto_buy_cnt']}',
                 mb_type = '{$_POST['mb_type']}',
                 mb_partner_file1 = '$mb_partner_file1_name',
                 mb_partner_file2 = '$mb_partner_file2_name',
                 mb_partner_file3 = '$mb_partner_file3_name',
                 mb_thezone = '$mb_thezone',
                 mb_partner_date_pay_date = '$mb_partner_date_pay_date',
                 mb_dealer = '{$_POST['mb_dealer']}',
                 mb_partner_pay_type = '{$_POST['mb_partner_pay_type']}',
                 mb_partner_remark = '{$_POST['mb_partner_remark']}',
                 mb_manager = '{$_POST['mb_manager']}',
                 mb_update_date = now()
                  ";

if ($w == '')
{
    $mb = get_member($mb_id);
    if ($mb['mb_id'])
        alert('이미 존재하는 회원아이디입니다.\\nＩＤ : '.$mb['mb_id'].'\\n이름 : '.$mb['mb_name'].'\\n닉네임 : '.$mb['mb_nick'].'\\n메일 : '.$mb['mb_email']);

    // 닉네임중복체크
    //$sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_nick = '{$mb_nick}' ";
	//$row = sql_fetch($sql);
    //if ($row['mb_id'])
    //    alert('이미 존재하는 닉네임입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);

    // 이메일중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_email = '{$mb_email}' ";
    $row = sql_fetch($sql);
    if ($row['mb_id'])
        alert('이미 존재하는 이메일입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);

    sql_query("insert into {$g5['member_table']}
                    set
                        mb_id = '{$mb_id}',
                        mb_password = '".get_encrypt_string($mb_password)."',
                        mb_datetime = '".G5_TIME_YMDHIS."',
                        mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                        mb_email_certify = '".G5_TIME_YMDHIS."',
                        {$sql_common}
                ");
}
else if ($w == 'u')
{
    $mb = get_member($mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'])
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');

    if ($is_admin !== 'super' && is_admin($mb['mb_id']) === 'super' ) {
        alert('최고관리자의 비밀번호를 수정할수 없습니다.');
    }

    if ($_POST['mb_id'] == $member['mb_id'] && $_POST['mb_level'] != $mb['mb_level'])
        alert($mb['mb_id'].' : 로그인 중인 관리자 레벨은 수정 할 수 없습니다.');

    // 닉네임중복체크
    //$sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_nick = '{$mb_nick}' and mb_id <> '$mb_id' ";
    //$row = sql_fetch($sql);
    //if ($row['mb_id'])
    //    alert('이미 존재하는 닉네임입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);

    // 이메일중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_email = '{$mb_email}' and mb_id <> '$mb_id' ";
    $row = sql_fetch($sql);
    if ($row['mb_id'])
        alert('이미 존재하는 이메일입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);

	//이용기간 체크
	$sql_as_date = '';
	if(isset($_POST['as_leave']) && $_POST['as_leave']) {
		$sql_as_date = " , as_date = ''";
	} else if($mb['as_date']) {
		$as_date = '';
		if($_POST['as_date_plus'] > 0) {
			$as_date = $mb['as_date'] + (abs($_POST['as_date_plus']) * 86400);
		} else if($_POST['as_date_plus'] < 0) {
			$as_date = $mb['as_date'] - (abs($_POST['as_date_plus']) * 86400);
		}

		if($as_date) {
			$sql_as_date = " , as_date = '{$as_date}'";
		}
	}

    if ($mb_password)
        $sql_password = " , mb_password = '".get_encrypt_string($mb_password)."' ";
    else
        $sql_password = "";

    if ($passive_certify)
        $sql_certify = " , mb_email_certify = '".G5_TIME_YMDHIS."' ";
    else
        $sql_certify = "";

    $sql = " update {$g5['member_table']}
                set {$sql_common}
                     {$sql_password}
                     {$sql_certify}
                     {$sql_as_date}
				where mb_id = '{$mb_id}' ";
    sql_query($sql);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

if( $w == '' || $w == 'u' ){

    $mb_dir = substr($mb_id,0,2);

    // 회원 아이콘 삭제
    if ($del_mb_icon)
        @unlink(G5_DATA_PATH.'/member/'.$mb_dir.'/'.$mb_id.'.gif');

    $image_regex = "/(\.(gif|jpe?g|png))$/i";
    $mb_icon_img = $mb_id.'.gif';

    // 아이콘 업로드
    if (isset($_FILES['mb_icon']) && is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
        if (!preg_match($image_regex, $_FILES['mb_icon']['name'])) {
            alert($_FILES['mb_icon']['name'] . '은(는) 이미지 파일이 아닙니다.');
        }

        if (preg_match($image_regex, $_FILES['mb_icon']['name'])) {
            $mb_icon_dir = G5_DATA_PATH.'/member/'.$mb_dir;
            @mkdir($mb_icon_dir, G5_DIR_PERMISSION);
            @chmod($mb_icon_dir, G5_DIR_PERMISSION);

            $dest_path = $mb_icon_dir.'/'.$mb_icon_img;

            move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
            chmod($dest_path, G5_FILE_PERMISSION);

            if (file_exists($dest_path)) {
                $size = @getimagesize($dest_path);
                if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
                    $thumb = null;
                    if($size[2] === 2 || $size[2] === 3) {
                        //jpg 또는 png 파일 적용
                        $thumb = thumbnail($mb_icon_img, $mb_icon_dir, $mb_icon_dir, $config['cf_member_icon_width'], $config['cf_member_icon_height'], true, true);
                        if($thumb) {
                            @unlink($dest_path);
                            rename($mb_icon_dir.'/'.$thumb, $dest_path);
                        }
                    }
                    if( !$thumb ){
                        // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                        @unlink($dest_path);
                    }
                }
            }
        }
    }

    $mb_img_dir = G5_DATA_PATH.'/member_image/';
    if( !is_dir($mb_img_dir) ){
        @mkdir($mb_img_dir, G5_DIR_PERMISSION);
        @chmod($mb_img_dir, G5_DIR_PERMISSION);
    }
    $mb_img_dir .= substr($mb_id,0,2);

    // 회원 이미지 삭제
    if ($del_mb_img)
        @unlink($mb_img_dir.'/'.$mb_icon_img);

    // 아이콘 업로드
    if (isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name'])) {
        if (!preg_match($image_regex, $_FILES['mb_img']['name'])) {
            alert($_FILES['mb_img']['name'] . '은(는) 이미지 파일이 아닙니다.');
        }

        if (preg_match($image_regex, $_FILES['mb_img']['name'])) {
            @mkdir($mb_img_dir, G5_DIR_PERMISSION);
            @chmod($mb_img_dir, G5_DIR_PERMISSION);

            $dest_path = $mb_img_dir.'/'.$mb_icon_img;

            move_uploaded_file($_FILES['mb_img']['tmp_name'], $dest_path);
            chmod($dest_path, G5_FILE_PERMISSION);

            if (file_exists($dest_path)) {
                $size = @getimagesize($dest_path);
                if ($size[0] > $config['cf_member_img_width'] || $size[1] > $config['cf_member_img_height']) {
                    $thumb = null;
                    if($size[2] === 2 || $size[2] === 3) {
                        //jpg 또는 png 파일 적용
                        $thumb = thumbnail($mb_icon_img, $mb_img_dir, $mb_img_dir, $config['cf_member_img_width'], $config['cf_member_img_height'], true, true);
                        if($thumb) {
                            @unlink($dest_path);
                            rename($mb_img_dir.'/'.$thumb, $dest_path);
                        }
                    }
                    if( !$thumb ){
                        // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                        @unlink($dest_path);
                    }
                }
            }
        }
    }
}


$sendData=[];
$sendData['usrId']=$_POST['mb_id'];

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/ent/account");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
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

if($_POST['usrPw']){
    $mb_password = trim($_POST['usrPw']);
    $mb_password2 =  base64_encode ($mb_password) ;
    $password = "mb_password = '".get_encrypt_string($mb_password)."',
                mb_password2 = '".$mb_password2."',";
}else{
    $password="";
}

$crnFile_name ="";
$sealFile_name ="";
$mbCheck = sql_fetch("SELECT * FROM {$g5["member_table"]} WHERE mb_id = '".$_POST["usrId"]."'");

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
            mb_password2 = '".$mb_password2."',
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
            {$password}
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

alert('저장되었습니다.');

goto_url('./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$mb_id, false);

?>
