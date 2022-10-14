<?php

	// = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -
	// = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -
	//
	// 2022.10.13 : 서원 - 세일즈 캠페인 임시용!!!!!!!!!!!!!!!!!!!!
	//
	//	해당 페이지는 2022년도 10월달의 세일즈 캠페인 이후 삭제 처리가 필요한 페이지 입니다.
	//  세일즈 캠페인을 위한 임시 사업소 가입페이지 파일 입니다.
	//
	// = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -
	// = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -

  // = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -
	// = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -
	//
	// 2022.10.13 : 서원 - 세일즈 캠페인 임시용!!!!!!!!!!!!!!!!!!!!
	//
	//	해당 페이지는 2022년도 10월달의 세일즈 캠페인 이후 삭제 처리가 필요한 페이지 입니다.
	//  세일즈 캠페인을 위한 임시 사업소 가입페이지 파일 입니다.
	//
	// = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -
	// = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = - = -

include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/register.lib.php');

// 불법접근을 막도록 토큰생성
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);
set_session("ss_cert_no",   "");
set_session("ss_cert_hash", "");
set_session("ss_cert_type", "");

$is_social_login_modify = false;

if( $provider && function_exists('social_nonce_is_valid') ){   //모바일로 소셜 연결을 했다면
  if( social_nonce_is_valid(get_session("social_link_token"), $provider) ){  //토큰값이 유효한지 체크
    $w = 'u';   //회원 수정으로 처리
    $_POST['mb_id'] = $member['mb_id'];
    $is_social_login_modify = true;
  }
}

if ($w == "") {
  // 회원 로그인을 한 경우 회원가입 할 수 없다
  // 경고창이 뜨는것을 막기위해 아래의 코드로 대체
  // alert("이미 로그인중이므로 회원 가입 하실 수 없습니다.", "./");
  // if ($is_member) {
  //     goto_url(G5_URL);
  // }

  // 리퍼러 체크
  referer_check();
  // if (!isset($_POST['agree']) || !$_POST['agree']) {
  //     alert('회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
  // }

  // if (!isset($_POST['agree2']) || !$_POST['agree2']) {
  //     alert('개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
  // }

  $agree  = preg_replace('#[^0-9]#', '', $_POST['agree']);
  $agree2 = preg_replace('#[^0-9]#', '', $_POST['agree2']);

  // $member['mb_birth'] = '';
  // $member['mb_sex']   = '';
  // $member['mb_name']  = '';
  // if (isset($_POST['birth'])) {
  //     $member['mb_birth'] = $_POST['birth'];
  // }
  // if (isset($_POST['sex'])) {
  //     $member['mb_sex']   = $_POST['sex'];
  // }
  // if (isset($_POST['mb_name'])) {
  //     $member['mb_name']  = $_POST['mb_name'];
  // }

  $g5['title'] = '회원 가입';

} else if ($w == 'u') {

  if ($is_admin == 'super')
    alert('관리자의 회원정보는 관리자 화면에서 수정해 주십시오.', G5_URL);

  if (!$is_member)
    alert('로그인 후 이용하여 주십시오.', G5_URL);

  if ($member['mb_id'] != $_POST['mb_id'])
    alert('로그인된 회원과 넘어온 정보가 서로 다릅니다.');

  /*
  if (!($member[mb_password] == sql_password($_POST[mb_password]) && $_POST[mb_password]))
      alert("비밀번호가 틀립니다.");

  // 수정 후 다시 이 폼으로 돌아오기 위해 임시로 저장해 놓음
  set_session("ss_tmp_password", $_POST[mb_password]);
  */

  if($_POST['mb_id'] && ! (isset($_POST['mb_password']) && $_POST['mb_password'])){
    if( ! $is_social_login_modify ){
      alert('비밀번호를 입력해 주세요.');
    }
  }

  if ($_POST['mb_password']) {
    // 수정된 정보를 업데이트후 되돌아 온것이라면 비밀번호가 암호화 된채로 넘어온것임
    if ($_POST['is_update'])
      $tmp_password = $_POST['mb_password'];
    else
      $tmp_password = get_encrypt_string($_POST['mb_password']);

    if ($member['mb_password'] != $tmp_password) {
      // 비밀번호 틀릴 경우 
      $result = api_post_call(EROUMCARE_API_ACCOUNT_ENT_LOGIN, array(
        'usrId' => $_POST['mb_id'],
        'pw' => $_POST['mb_password']
      ));
      if(!$result || $result['errorYN'] != 'N')
        alert('비밀번호가 틀립니다.');
    }
  }


  $g5['title'] = '회원 정보 수정';
  $member['mb_email']       = get_text($member['mb_email']);
  $member['mb_homepage']    = get_text($member['mb_homepage']);
  $member['mb_birth']       = get_text($member['mb_birth']);
  $member['mb_tel']         = get_text($member['mb_tel']);
  $member['mb_hp']          = get_text($member['mb_hp']);
  $member['mb_addr1']       = get_text($member['mb_addr1']);
  $member['mb_addr2']       = get_text($member['mb_addr2']);
  $member['mb_signature']   = get_text($member['mb_signature']);
  $member['mb_recommend']   = get_text($member['mb_recommend']);
  $member['mb_profile']     = get_text($member['mb_profile']);
  $member['mb_1']           = get_text($member['mb_1']);
  $member['mb_2']           = get_text($member['mb_2']);
  $member['mb_3']           = get_text($member['mb_3']);
  $member['mb_4']           = get_text($member['mb_4']);
  $member['mb_5']           = get_text($member['mb_5']);
  $member['mb_6']           = get_text($member['mb_6']);
  $member['mb_7']           = get_text($member['mb_7']);
  $member['mb_8']           = get_text($member['mb_8']);
  $member['mb_9']           = get_text($member['mb_9']);
  $member['mb_10']          = get_text($member['mb_10']);
} else {
  alert('w 값이 제대로 넘어오지 않았습니다.');
}

// Page ID
$pid = ($pid) ? $pid : 'regform';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

// 스킨 체크
list($member_skin_path, $member_skin_url) = apms_skin_thema('member', $member_skin_path, $member_skin_url);

// 설정값 불러오기
$is_regform_sub = false;
@include_once($member_skin_path.'/config.skin.php');

if($is_regform_sub) {
  include_once(G5_PATH.'/head.sub.php');
  if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
  include_once('./_head.php');
}

$skin_path = $member_skin_path;
$skin_url = $member_skin_url;

// 스킨설정
$wset = (G5_IS_MOBILE) ? apms_skin_set('member_mobile') : apms_skin_set('member');

$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
  $setup_href = './skin.setup.php?skin=member&amp;ts='.urlencode(THEMA);
}

$zip_href = G5_BBS_URL.'/zip.php?frm_name=fregisterform&amp;frm_zip1=mb_zip1&amp;frm_zip2=mb_zip2&amp;frm_addr1=mb_addr1&amp;frm_addr2=mb_addr2&amp;frm_addr3=mb_addr3&amp;frm_jibeon=mb_addr_jibeon';

// 회원아이콘 경로
$mb_icon_path = G5_DATA_PATH.'/member/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';
$mb_icon_url  = G5_DATA_URL.'/member/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';

// 회원이미지 경로
$mb_img_path = G5_DATA_PATH.'/member_image/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';
$mb_img_url  = G5_DATA_URL.'/member_image/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';

$register_action_url = $action_url = G5_HTTPS_BBS_URL.'/register_form_update.php';
$req_nick = !isset($member['mb_nick_date']) || (isset($member['mb_nick_date']) && $member['mb_nick_date'] <= date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400)));
$required = ($w=='') ? 'required' : '';
$readonly = ($w=='u') ? 'readonly' : '';

$agree  = preg_replace('#[^0-9]#', '', $agree);
$agree2 = preg_replace('#[^0-9]#', '', $agree2);

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
if ($config['cf_use_addr'])
  add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

include_once($skin_path.'/register_form_event.skin.php');

//추천인에 마케터 정보등록
if ($w == "" && $config['cf_use_recommend']) {
  if(defined('APMS_MKT') && APMS_MKT) {
?>
<script>
$(document).ready(function() {
  if($("#reg_mb_recommend").length > 0) {
    $('#reg_mb_recommend').val('<?php echo APMS_MKT;?>');
    //$('#reg_mb_recommend').attr('readonly',true);
  }
});
</script>
<?php
  }
}

if($is_regform_sub) {
  if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
  include_once(G5_PATH.'/tail.sub.php');
} else {
  include_once('./_tail.php');
}
?>