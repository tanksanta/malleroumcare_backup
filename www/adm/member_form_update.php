<?php
$sub_menu = "200100";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
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

$send_transaction = "A";
$send_transaction_e = $_POST["mb_transaction_e"] ? "E" : "";
$send_transaction_f = $_POST["mb_transaction_f"] ? "F" : "";
if ($send_transaction_e == "E" && $send_transaction_f == "") {
    $send_transaction = "E";
}
else if ($send_transaction_e == "" && $send_transaction_f == "F") {
    $send_transaction = "F";
}
else if ($send_transaction_e == "" && $send_transaction_f == "") {
    $send_transaction = "N";
}

$send_transaction_e = $_POST["send_transaction_e"];
$send_transaction_f = $_POST["send_transaction_f"];

$mb_thezone = isset($_POST['mb_thezone'])             ? trim($_POST['mb_thezone'])           : "";
if ($w == '') {
    // $mb_thezone = get_uniqid_member();
}
$mb_partner_date_pay_date = isset($_POST['mb_partner_date_pay_date'])             ? trim($_POST['mb_partner_date_pay_date'])           : "";

$_POST['mb_order_approve'] = isset($_POST['mb_order_approve']) ? (int)$_POST['mb_order_approve'] : 1;
$_POST['mb_giup_matching'] = $_POST['mb_giup_matching'] ? $_POST['mb_giup_matching'] : 'N';
$_POST['mb_dealer'] = $_POST['mb_dealer'] ? (int)$_POST['mb_dealer'] : 0;

if ($msg = valid_mb_nick($mb_nick))     alert($msg, "", true, true);

$mb_temp = $mb_temp ? 1 : 0;

$mb_hp = $_POST['mb_hp1']."-".$_POST['mb_hp2']."-".$_POST['mb_hp3'];
$mb_fax = $_POST['mb_fax1']."-".$_POST['mb_fax2']."-".$_POST['mb_fax3'];
$mb_giup_btel = $_POST['mb_tel1']."-".$_POST['mb_tel2']."-".$_POST['mb_tel3'];
$mb_tel = $_POST['mb_tel1']."-".$_POST['mb_tel2']."-".$_POST['mb_tel3'];

// 파트너 유형 (직배송, 설치, 물품공급)

$mb_partner_type_text = '';
if ($_POST['mb_partner_type'] != null && $_POST['mb_type'] == 'partner') {
    $mb_partner_type_text = implode('|', $_POST['mb_partner_type']);
}

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
                 mb_addr_name = '{$_POST['mb_addr_name']}',
                 mb_addr_tel = '{$_POST['mb_addr_tel']}',
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
                 mb_grade = '{$_POST['mb_grade']}',
                 mb_order_approve = '{$_POST['mb_order_approve']}',
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
                 mb_giup_matching = '{$_POST['mb_giup_matching']}',
                 mb_partner_type = '{$mb_partner_type_text}',
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
				 mb_partner_default_warehouse = '{$_POST['mb_partner_default_warehouse']}',
                 mb_thezone = '$mb_thezone',
                 mb_partner_date_pay_date = '$mb_partner_date_pay_date',
                 mb_dealer = '{$_POST['mb_dealer']}',
                 mb_partner_pay_type = '{$_POST['mb_partner_pay_type']}',
                 mb_partner_remark = '{$_POST['mb_partner_remark']}',
                 mb_manager = '{$_POST['mb_manager']}',
                 mb_update_date = now(),
                 mb_ent_num = '{$mb_ent_num}',
                 mb_temp = '{$mb_temp}',
                 send_transaction = '{$send_transaction}',
                 send_transaction_e = '{$send_transaction_e}',
                 send_transaction_f = '{$send_transaction_f}',
                 mb_matching_manager_mail = '{$_POST['mb_matching_manager_mail']}',
                 mb_matching_manager_tel = '{$_POST['mb_matching_manager_tel']}',
                 mb_matching_manager_nm = '{$_POST['mb_matching_manager_nm']}'
                ";

$sendData = array(
    'usrId' => $mb_id,
    'usrPw' => $mb_password,
    'entNm' => $mb_giup_bname,
    'usrPnum' => $mb_hp,
    'entPnum' => $mb_tel,
    'entFax' => $mb_fax,
    'usrMail' => $mb_email,
    'entMail' => $mb_giup_tax_email,
    'mbType' => $mb_type,
    'entCeoNm' => $mb_giup_boss_name,
    'entBusiType' => $mb_giup_bupjong,
    'entBusiCondition' => $mb_giup_buptae,
    'entZip' => $mb_giup_zip1 . $mb_giup_zip2,
    'entAddr' => $mb_giup_addr1,
    'entAddrDetail' => $mb_giup_addr2 . $mb_giup_addr3,
    'entTaxCharger' => $mb_giup_manager_name,
    'usrZip' => $mb_zip1 . $mb_zip2,
    'usrAddr' => $mb_addr1,
    'usrAddrDetail' => $mb_addr2 . $mb_addr3
);

$sql = " select count(*) as cnt from `{$g5['member_table']}` where mb_giup_bnum = '{$mb_giup_bnum}' and mb_temp = 0 and mb_id != '{$mb_id}'";
$row = sql_fetch($sql);
if ($row['cnt']) {
    alert('이미 존재하는 사업자 번호 입니다.');
}


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

    $sendData['entCrn'] = $mb_giup_bnum;
    $sendData['entConAcco1'] = "1. 본 계약은 국민건강보험 노인장기요양보험 급여상품의 공급계약을 체결함에 목적이 있다.\n2. 본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.";
    $sendData['entConAcco2'] = '본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.';
    
    if($mb_type !== 'normal') { // 일반회원이 아니면
        // $mb_level = 3;
        // 시스템 먼저 회원가입
        $result = post_formdata(EROUMCARE_API_ENT_INSERT, $sendData);
        if($result['errorYN'] !== 'N')
            alert($result['message']);
    
        $mb_entId = $result['data']['entId'];
        if(!$mb_entId)
            alert('시스템서버 오류로 회원가입에 실패했습니다.');

        $sql_common .= "
            , mb_entId = '{$mb_entId}'
            , mb_entConAcc01 = '{$sendData['entConAcco1']}'
            , mb_entConAcc02 = '{$sendData['entConAcco2']}'
        ";
    }
    

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

    
    $sendData['entConAcc01'] = $mb['mb_entConAcc01'];
    $sendData['entConAcc02'] = $mb['mb_entConAcc02'];
    $sendData['entId'] = $mb['mb_entId'];
    $sendData['entUsrId'] = $mb['mb_id'];
    if($mb_type !== 'normal' && $mb['mb_entId']) { // 일반회원이 아니고 전용아이디가 아닌경우
        // 시스템 먼저 업데이트
        $result = post_formdata(EROUMCARE_API_ENT_UPDATE, $sendData);
        if($result['errorYN'] !== 'N')
        alert($result['message']);
        
        $sql_common .= "
            , mb_entConAcc01 = '{$sendData['entConAcco1']}'
            , mb_entConAcc02 = '{$sendData['entConAcco2']}'
        ";
    }

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
    
    // 23.11.22 : 서원 - 이로움ON과 사업소정보인 BPLC 테이블 정보중 매칭 담당자 정보를 연동 하기위한 Array 생성.
    $_matchingINFO = [
        "mb_giup_matching" => $_POST['mb_giup_matching']
        ,"mb_matching_manager_nm" => $_POST['mb_matching_manager_nm']
        ,"mb_matching_manager_tel" => $_POST['mb_matching_manager_tel']
        ,"mb_matching_manager_mail" => $_POST['mb_matching_manager_mail']
        ,"mb_id" => $mb_id
        ,"mb_giup_bnum" => $mb_giup_bnum
    ];

    // 23.11.20 - 서원 : 프로시저 CALL `PROC_EROUMCARE_BPLC`('모드','이로움ON 회원 데이터');
    //                    사업소의 매칭 담당자 정보는 사업소ID와 사업자번호가 이로움Care와 이로움ON이 동일해야 변경됨.
    $sql = (" CALL `PROC_EROUMCARE_BPLC`('UPDATE_matching','".json_encode($_matchingINFO, JSON_UNESCAPED_UNICODE)."'); ");
    $sql_result = "";
    $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
	if($_POST['mb_leave_date'] != ""){//이로움온 탈퇴일,탈퇴 승인자 등록
		$sql = ("UPDATE BPLC SET LEAVE_CONFIRM_DATE='".$_POST['mb_leave_date']."',LEAVE_CONFIRM_NM='".$member['mb_name']."',LEAVE_REJECT_DATE='',LEAVE_REJECT_RESN='',USE_YN = 'N',RCMDTN_YN = 'N',mb_giup_matching = 'N' WHERE BPLC_ID='{$mb_id}' AND BRNO='{$mb_giup_bnum}';");
        $sql_result2 = "";
        $sql_result2 = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);		
    }
	if($_POST['mb_leave_date'] == "" && $_POST['mb_leave_date2'] != ""){//탈퇴 회원 복원
		
		$sql = ("UPDATE BPLC SET LEAVE_CONFIRM_DATE='',LEAVE_REQUEST_DATE='',LEAVE_RESN='',LEAVE_CONFIRM_NM='',LEAVE_REJECT_DATE='',LEAVE_REJECT_RESN='' WHERE BPLC_ID='{$mb_id}' AND BRNO='{$mb_giup_bnum}';");
        $sql_result2 = "";
        $sql_result2 = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);		

		
		//메일 발송 시작 ==========================================================
		$content = "[탈퇴 계정 복구 안내]<br><br>
		탈퇴 계정이 복구되었습니다.<br>
		1:1 매칭 상담 진행 여부를 확인하여 관리자 > 멤버스 관리에서 등록정보를 수정해 주세요.<br><br>
		◼︎ 사업소 : ".$mb_id."<br>◼︎ 사업자번호 : ".$mb_giup_bnum."<br><br>
		▷ 이로움 ON 관리자 바로가기<br> 
		<a href='https://eroum.co.kr/_mng/consult/recipter/list' target='_blank'>https://eroum.co.kr/_mng/consult/recipter/list</a>";
		$to_mail = "thkc_cx@thkc.co.kr";//thkc_cx@thkc.co.kr
		if(strpos($_SERVER['HTTP_HOST'],".eroumcare")){//dev,test 서버 시 발송
			mailer($config['cf_admin_email_name'], $config['cf_admin_email'], "cdcj9090@thkc.co.kr", "[탈퇴 계정 복구 안내]", $content, 1);
			mailer($config['cf_admin_email_name'], $config['cf_admin_email'], "dglee@thkc.co.kr", "[탈퇴 계정 복구 안내]", $content, 1);
		}else{//상용 서버 발송
			mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $to_mail, "[탈퇴 계정 복구 안내]", $content, 1);			
		}
		//메일 발송 끝 ============================================================ 
	}
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

    #시스템 정보와 맞춤
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
                    mb_name = '{$_POST['mb_name']}',
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
                    mb_giup_matching = '{$resInfo["mb_giup_matching"]}',
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
                    mb_matching_manager_mail = '{$resInfo["mb_matching_manager_mail"]}',
                    mb_matching_manager_tel = '{$resInfo["mb_matching_manager_tel"]}',
                    mb_matching_manager_nm = '{$resInfo["mb_matching_manager_nm"]}'

            ");
        } else {
            sql_query("
                UPDATE {$g5["member_table"]} SET
                  sealFile = '".$sealFile_name."',
                  crnFile = '".$crnFile_name."'
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
        alert('저장되었습니다.');
        goto_url('./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$mb_id, false);
    }else{
        //성공 결과 페이지 이동
        alert('회원정보 저장에 실패하였습니다..',G5_URL."/member_list.php");
    }


?>
