<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_LIB_PATH.'/apms.thema.lib.php');


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


if (!($w == '' || $w == 'u')) {
  alert('w 값이 제대로 넘어오지 않았습니다.');
}

if ($w == 'u' && $is_admin == 'super') {
  if (file_exists(G5_PATH.'/DEMO'))
    alert('데모 화면에서는 하실(보실) 수 없는 작업입니다.');
}

if($w == 'u') {
  $mb_id = isset($_SESSION['ss_mb_id']) ? trim($_SESSION['ss_mb_id']) : '';
  $mb_type = $member['mb_type'];
} else if($w == '') {
  $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
  $mb_type = in_array($_POST['mb_type'], ['default', 'partner', 'normal']) ? $_POST['mb_type'] : '';
} else {
  alert('잘못된 접근입니다', G5_URL);
}

if(!$mb_id)
  alert('회원아이디 값이 없습니다. 올바른 방법으로 이용해 주십시오.');

if(!$mb_type)
  alert('분류 값이 없습니다. 올바른 방법으로 이용해 주십시오.');

$mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
$mb_password_re = isset($_POST['mb_password_re']) ? trim($_POST['mb_password_re']) : '';
$mb_email       = isset($_POST['mb_email']) ? trim($_POST['mb_email']) : '';
$mb_giup_tax_email = isset($_POST['mb_giup_tax_email']) ? trim($_POST['mb_giup_tax_email']) : '';
$mb_giup_bname  = isset($_POST['mb_giup_bname'])    ? trim($_POST['mb_giup_bname'])  : "";
$mb_giup_bnum   = isset($_POST['mb_giup_bnum']) ? trim($_POST['mb_giup_bnum']) : '';
$mb_giup_boss_name = isset($_POST['mb_giup_boss_name']) ? trim($_POST['mb_giup_boss_name']) : ''; // 사업소 대표
$mb_giup_bupjong = isset($_POST['mb_giup_bupjong']) ? trim($_POST['mb_giup_bupjong']) : ''; //사업소 업종
$mb_giup_buptae = isset($_POST['mb_giup_buptae']) ? trim($_POST['mb_giup_buptae']) : ''; //사업소 업태
$mb_giup_zip1 = isset($_POST['mb_giup_zip'])        ? substr(trim($_POST['mb_giup_zip']), 0, 3) : "";
$mb_giup_zip2 = isset($_POST['mb_giup_zip'])        ? substr(trim($_POST['mb_giup_zip']), 3)    : "";
$mb_giup_addr1 = isset($_POST['mb_giup_addr1']) ? trim($_POST['mb_giup_addr1']) : ''; // 사업소 주소
$mb_giup_addr2 = isset($_POST['mb_giup_addr2']) ? trim($_POST['mb_giup_addr2']) : '';
$mb_giup_addr3 = isset($_POST['mb_giup_addr3']) ? trim($_POST['mb_giup_addr3']) : '';
$mb_giup_manager_name = isset($_POST['mb_giup_manager_name']) ? trim($_POST['mb_giup_manager_name']) : '';
$mb_entConAcc01 = isset($_POST['mb_entConAcc01'])   ? trim($_POST['mb_entConAcc01']) : "";
$mb_entConAcc02 = isset($_POST['mb_entConAcc02'])   ? trim($_POST['mb_entConAcc02']) : "";
$mb_ent_num = isset($_POST['mb_ent_num']) ? trim($_POST['mb_ent_num']) : '';
$mb_tel         = "{$mb_tel1}-{$mb_tel2}-{$mb_tel3}";
$mb_hp          = "{$mb_hp1}-{$mb_hp2}-{$mb_hp3}";
$mb_fax         = "{$mb_fax1}-{$mb_fax2}-{$mb_fax3}";
$mb_zip1        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 0, 3) : "";
$mb_zip2        = isset($_POST['mb_zip'])           ? substr(trim($_POST['mb_zip']), 3)    : "";
$mb_addr1       = isset($_POST['mb_addr1'])         ? trim($_POST['mb_addr1'])       : "";
$mb_addr2       = isset($_POST['mb_addr2'])         ? trim($_POST['mb_addr2'])       : "";
$mb_addr3       = isset($_POST['mb_addr3'])         ? trim($_POST['mb_addr3'])       : "";
$mb_addr_jibeon = isset($_POST['mb_addr_jibeon'])   ? trim($_POST['mb_addr_jibeon']) : "";

$mb_email       = get_email_address($mb_email);
$mb_giup_tax_email = get_email_address($mb_giup_tax_email);
$mb_giup_bname  = clean_xss_tags($mb_giup_bname);
$mb_giup_bnum   = clean_xss_tags($mb_giup_bnum);
$mb_giup_boss_name = clean_xss_tags($mb_giup_boss_name);
$mb_giup_bupjong   = clean_xss_tags($mb_giup_bupjong);
$mb_giup_buptae    = clean_xss_tags($mb_giup_buptae);
$mb_giup_zip1 = preg_replace('/[^0-9]/', '', $mb_giup_zip1);
$mb_giup_zip2 = preg_replace('/[^0-9]/', '', $mb_giup_zip2);
$mb_giup_addr1 = clean_xss_tags($mb_giup_addr1);
$mb_giup_addr2 = clean_xss_tags($mb_giup_addr2);
$mb_giup_addr3 = clean_xss_tags($mb_giup_addr3);
$mb_giup_manager_name = clean_xss_tags($mb_giup_manager_name);
$mb_entConAcc01 = clean_xss_tags($mb_entConAcc01);
$mb_entConAcc02 = clean_xss_tags($mb_entConAcc02);
$mb_ent_num = clean_xss_tags($mb_ent_num);
$mb_tel         = clean_xss_tags($mb_tel);
$mb_hp          = clean_xss_tags($mb_hp);
$mb_fax         = clean_xss_tags($mb_fax);
$mb_zip1        = preg_replace('/[^0-9]/', '', $mb_zip1);
$mb_zip2        = preg_replace('/[^0-9]/', '', $mb_zip2);
$mb_addr1       = clean_xss_tags($mb_addr1);
$mb_addr2       = clean_xss_tags($mb_addr2);
$mb_addr3       = clean_xss_tags($mb_addr3);
$mb_addr_jibeon = preg_match("/^(N|R)$/", $mb_addr_jibeon) ? '' : $mb_addr_jibeon;

$crnFile_name = '';
$sealFile_name = '';

if ($w == '' || $w == 'u') {
  if ($msg = empty_mb_id($mb_id))         alert($msg, "", true, true); // alert($msg, $url, $error, $post);
  if ($msg = valid_mb_id($mb_id))         alert($msg, "", true, true);
  if ($msg = count_mb_id($mb_id))         alert($msg, "", true, true);

  if ($w == '' && !$mb_password)
    alert('비밀번호가 넘어오지 않았습니다.');
  if($w == '' && $mb_password != $mb_password_re)
    alert('비밀번호가 일치하지 않습니다.');

  if ($w == '') {
    if ($msg = exist_mb_id($mb_id))     alert($msg);
  }


  if(!validateBusinessNumber($mb_giup_bnum)) {
    alert('사업자 번호 형식이 맞지 않습니다.',G5_BBS_URL."/register.php");
  }

  // 23.03.26 : 서원 - 사업자번호 중복가입 체크 추가.
  $msg = exist_mb_giup_bnum($mb_giup_bnum);
  if($msg) alert("이미 사용 중인 사업자번호 입니다.",G5_BBS_URL."/register.php");


  //서버 최대 용량 10Mb
  $max_file_size = 1024*1024*10;

  // 사업자등록증 업로드
  if($_FILES['crnFile']['tmp_name']) {
    $uploads_dir = G5_DATA_PATH.'/file/member/license';
    $error = $_FILES['crnFile']['error'];
    $name = $_FILES['crnFile']['name'];
    $allowed_ext = ['pdf', 'jpg', 'png', 'jpeg', 'gif', 'bmp'];
    $ext = array_pop(explode('.', $name));
    $crnFile_name = $mb_id.'_'.round(microtime(true)) . '.' . $ext;
    $crnFile = "$uploads_dir/$crnFile_name";
    // 오류 확인
    if( $error != UPLOAD_ERR_OK ) {
      switch( $error ) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          alert('파일이 너무 큽니다.',G5_BBS_URL."/register.php");
          break;
        default:
          alert('파일이 제대로 업로드되지 않았습니다.',G5_BBS_URL."/register.php");
      }
    }
    if($_FILES['crnFile']['size'] >= $max_file_size) {
      alert('10Mb 까지만 업로드 가능합니다.',G5_BBS_URL."/register.php");
    }
    // 확장자 확인
    if(!in_array($ext, $allowed_ext)) {
      alert('허용되지 않는 확장자입니다',G5_BBS_URL."/register.php");
    }
    move_uploaded_file( $_FILES['crnFile']['tmp_name'], $crnFile);
  }

  // 직인 업로드
  if($_FILES['sealFile']['tmp_name']) {
    $uploads_dir = G5_DATA_PATH.'/file/member/stamp';
    $error = $_FILES['sealFile']['error'];
    $name = $_FILES['sealFile']['name'];
    $allowed_ext = ['jpg', 'png', 'jpeg', 'gif', 'bmp'];
    $ext = array_pop(explode('.', $name));
    $sealFile_name = $mb_id.'_'.round(microtime(true)) . '.' . $ext;
    $sealFile = "$uploads_dir/$sealFile_name";
    // 오류 확인
    if( $error != UPLOAD_ERR_OK ) {
      switch( $error ) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          alert('파일이 너무 큽니다.',G5_BBS_URL."/register.php");
          break;
        default:
        alert('파일이 제대로 업로드되지 않았습니다.',G5_BBS_URL."/register.php");
      }
    }
    if($_FILES['sealFile']['size'] >= $max_file_size) {
      alert('10Mb 까지만 업로드 가능합니다.',G5_BBS_URL."/register.php");
    }
    // 확장자 확인
    if(!in_array($ext, $allowed_ext)) {
      alert('허용되지 않는 확장자입니다',G5_BBS_URL."/register.php");
    }
    move_uploaded_file( $_FILES['sealFile']['tmp_name'], $sealFile);
  }
}

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

if ($w == '') { // 회원가입
  $sendData['entCrn'] = $mb_giup_bnum;
  $sendData['entConAcco1'] = $mb_entConAcc01;
  $sendData['entConAcco2'] = $mb_entConAcc02;

  $mb_level = 1;
  $mb_entId = '';

  $temp = sql_fetch("SELECT * FROM `{$g5['member_table']}` WHERE mb_giup_bnum = '{$mb_giup_bnum}' AND mb_temp = TRUE");

  if($mb_type !== 'normal' && !$temp['mb_id']) { // 일반회원이 아니거나 임시계정이 없는경우
    $mb_level = 3;
    // 시스템 먼저 회원가입
    $result = post_formdata(EROUMCARE_API_ENT_INSERT, $sendData);
    if($result['errorYN'] !== 'N')
      alert($result['message']);

    $mb_entId = $result['data']['entId'];
    if(!$mb_entId)
      alert('시스템서버 오류로 회원가입에 실패했습니다.');
  }

  $sql = "
    INSERT INTO
      {$g5["member_table"]}
    SET
      mb_id = '{$mb_id}',
      mb_password = '".get_encrypt_string($mb_password)."',
      mb_type = '{$mb_type}',
      mb_default_type = '{$mb_default_type}',
      mb_partner_type = '{$mb_partner_type}',
      mb_level = '{$mb_level}',
      mb_name = '{$mb_giup_bname}',
      mb_nick = '{$mb_giup_bname}',
      mb_giup_bname = '{$mb_giup_bname}',
      mb_hp = '{$mb_hp}',
      mb_tel = '{$mb_tel}',
      mb_giup_btel = '{$mb_tel}',
      mb_fax = '{$mb_fax}',
      mb_entId = '{$mb_entId}',
      mb_entNm = '{$mb_giup_bname}',
      mb_addr_title = '기본배송지',
      mb_addr_name = '{$mb_giup_bname}',
      mb_zip1 = '{$mb_zip1}',
      mb_zip2 = '{$mb_zip2}',
      mb_addr1 = '{$mb_addr1}',
      mb_addr2 = '{$mb_addr2}',
      mb_addr3 = '{$mb_addr3}',
      mb_giup_bnum = '{$mb_giup_bnum}',
      mb_giup_zip1 = '{$mb_giup_zip1}',
      mb_giup_zip2 = '{$mb_giup_zip2}',
      mb_giup_addr1 = '{$mb_giup_addr1}',
      mb_giup_addr2 = '{$mb_giup_addr2}',
      mb_giup_addr3 = '{$mb_giup_addr3}',
      mb_giup_boss_name = '{$mb_giup_boss_name}',
      mb_email = '{$mb_email}',
      mb_giup_manager_name = '{$mb_giup_manager_name}',
      mb_giup_buptae = '{$mb_giup_buptae}',
      mb_giup_bupjong = '{$mb_giup_bupjong}',
      mb_giup_tax_email = '{$mb_giup_tax_email}',
      mb_entConAcc01 = '{$mb_entConAcc01}',
      mb_entConAcc02 = '{$mb_entConAcc02}',
      sealFile = '{$sealFile_name}',
      crnFile = '{$crnFile_name}',
      mb_ent_num = '{$mb_ent_num}',
      mb_datetime = '".G5_TIME_YMDHIS."',
      mb_ip = '{$_SERVER['REMOTE_ADDR']}',
      mb_login_ip = '{$_SERVER['REMOTE_ADDR']}'
  ";

  sql_query($sql, true);

} else if ($w == 'u') { // 회원정보수정
  if (!trim(get_session('ss_mb_id')))
    alert('로그인 되어 있지 않습니다.');
  
  if (trim($_POST['mb_id']) != $mb_id)
    alert("로그인된 정보와 수정하려는 정보가 틀리므로 수정할 수 없습니다.\\n만약 올바르지 않은 방법을 사용하신다면 바로 중지하여 주십시오.");

  $sql_password = "";
  if($mb_password)
    $sql_password = " , mb_password = '".get_encrypt_string($mb_password)."' ";
  
  $sql_sealfile = "";
  if($sealFile_name)
    $sql_sealfile = " , sealFile = '{$sealFile_name}' ";
  
  $sql_crnfile = "";
  if($crnFile_name)
    $sql_crnfile = " , crnFile = '{$crnFile_name}' ";

  $sendData['entConAcc01'] = $mb_entConAcc01;
  $sendData['entConAcc02'] = $mb_entConAcc02;
  $sendData['entId'] = $member['mb_entId'];
  $sendData['entUsrId'] = $mb_id;

  if($mb_type !== 'normal') { // 일반회원이 아니면
    // 시스템 먼저 업데이트
    $result = post_formdata(EROUMCARE_API_ENT_UPDATE, $sendData);
    if($result['errorYN'] !== 'N')
      alert($result['message']);
  }

  $sql = "
    UPDATE
      {$g5["member_table"]}
    SET
      mb_name = '{$mb_giup_bname}',
      mb_nick = '{$mb_giup_bname}',
      mb_giup_bname = '{$mb_giup_bname}',
      mb_hp = '{$mb_hp}',
      mb_tel = '{$mb_tel}',
      mb_giup_btel = '{$mb_tel}',
      mb_fax = '{$mb_fax}',
      mb_entNm = '{$mb_giup_bname}',
      mb_zip1 = '{$mb_zip1}',
      mb_zip2 = '{$mb_zip2}',
      mb_addr1 = '{$mb_addr1}',
      mb_addr2 = '{$mb_addr2}',
      mb_addr3 = '{$mb_addr3}',
	  mb_addr_jibeon = '{$mb_addr_jibeon}',
      mb_giup_zip1 = '{$mb_giup_zip1}',
      mb_giup_zip2 = '{$mb_giup_zip2}',
      mb_giup_addr1 = '{$mb_giup_addr1}',
      mb_giup_addr2 = '{$mb_giup_addr2}',
      mb_giup_addr3 = '{$mb_giup_addr3}',
      mb_giup_boss_name = '{$mb_giup_boss_name}',
      mb_email = '{$mb_email}',
      mb_giup_manager_name = '{$mb_giup_manager_name}',
      mb_giup_buptae = '{$mb_giup_buptae}',
      mb_giup_bupjong = '{$mb_giup_bupjong}',
      mb_giup_tax_email = '{$mb_giup_tax_email}',
      mb_entConAcc01 = '{$mb_entConAcc01}',
      mb_entConAcc02 = '{$mb_entConAcc02}',
      mb_ent_num = '{$mb_ent_num}'
      {$sql_password}
      {$sql_sealfile}
      {$sql_crnfile}
    WHERE
      mb_id = '{$mb_id}'
  ";

  sql_query($sql, true);
}

if ($w == '') {
  set_session('ss_mb_id', $mb_id);

  if($mb_type === 'normal') { // 일반회원이면 메인으로 이동
    goto_url(G5_URL);
  }
  goto_url(G5_HTTP_BBS_URL.'/register_result.php');
} else if ($w == 'u') {
  alert('회원 정보 수정이 완료되었습니다.', G5_URL);
}
?>
