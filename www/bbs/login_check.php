<?php
include_once('./_common.php');
$g5['title'] = "로그인 검사";
# 210131 이로움 계정검사

$check_member = get_member($_POST["mb_id"]);

set_session('ss_manager_mb_id', '');
if($check_member['mb_type'] === 'manager') {
  // 각 사업소의 담당자 회원일 경우 해당 아이디/비밀번호 일치시 해당 사업소로 로그인해야함

  $mb_id       = trim($_POST['mb_id']);
  $mb_password = trim($_POST['mb_password']);

  // 23.04.04 : 서원 - 리뉴얼로 인한 'manager' 권한 로그인 일자 저장하기 위한 임시 저장용.
  $tid        = trim($_POST['mb_id']);

  //계정정보 불러오기
  $mb = get_member($mb_id);

  //쇼핑몰에 등록이 되어 있지 않으면, 메세지출력
  if (!check_password($mb_password, $mb['mb_password'])) {
    alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
  }

  set_session('ss_manager_mb_id', $mb_id);

  $mb_id = $_POST['mb_id'] = $mb['mb_manager'];

  //계정정보 불러오기
  $mb = get_member($mb_id);

  if(!$mb['mb_id']) {
    alert('사업소 계정이 존재하지 않습니다.');
  }

  // 23.04.04 : 서원 - 리뉴얼로 인한 'manager' 권한 로그인 일자 저장 요청.
  sql_query(" update {$g5['member_table']} set mb_today_login = NOW() where mb_id = '{$tid}' ");

} else if($_POST["mb_id"] != "admin" && $check_member['mb_type'] !== 'normal') {

  $sendData = [];
  $sendData["usrId"] = $_POST["mb_id"];
  $sendData["pw"] = $_POST["mb_password"];
  $res = get_eroumcare(EROUMCARE_API_ACCOUNT_ENT_LOGIN, $sendData);
  if($res["errorYN"] == "Y") {
    //시스템 회원 정보가 아닐시
    $mb_id       = trim($_POST['mb_id']);
    $mb_password = trim($_POST['mb_password']);
    //공백이 포함된 경우 메세지 출력
    if (!$mb_id || !$mb_password)
    alert('회원아이디나 비밀번호가 공백이면 안됩니다.');
    
    //계정정보 불러오기
    $mb = get_member($mb_id);

    //쇼핑몰에 등록이 되어 있지 않으면, 메세지출력
    if (!check_password($mb_password, $mb['mb_password'])) {
      alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
    }

    //관리자 승인 대기인 경우 : 로그인 시키고 - 결과페이지로 이동, 상품보기 불가능
    if($mb['mb_level']<5&&$res['message']=="미승인된 사업소 입니다. 관리자에게 문의하세요.") {
      #로그인 체크
      if ($_POST["mb_id"] == "admin" && !$is_social_password_check && (!$mb['mb_id'] || !login_password_check($mb, $mb_password, $mb['mb_password'])) ) {
        alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
      }
      // 차단된 아이디인가?
      if ($mb['mb_intercept_date'] && $mb['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME)) {
        $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_intercept_date']);
        // alert('관리자 승인이 대기중입니다.',G5_BBS_URL."/register_result.php");
        alert('회원님의 아이디는 접근이 금지되어 있습니다.\n처리일 : '.$date, G5_BBS_URL."/member_intercept.php");
      }
      // 탈퇴한 아이디인가?
      if ($mb['mb_leave_date'] && $mb['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME)) {
        $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_leave_date']);
        alert('탈퇴한 아이디이므로 접근하실 수 없습니다.\n탈퇴일 : '.$date);
      }
      // 회원아이디 세션 생성
      set_session('ss_mb_id', $mb['mb_id']);
      //이로움 통합시스템 이동
      alert('관리자 승인이 대기중입니다.',G5_BBS_URL."/register_result.php");
    }
    
    // 임시계정이 있을때
    $temp = sql_fetch("SELECT * FROM `{$g5['member_table']}` WHERE mb_giup_bnum = '{$mb['mb_giup_bnum']}' AND mb_temp = TRUE");
    if ($temp['mb_id']) {
      //이로움 통합시스템 이동
      alert('관리자 승인이 대기중입니다.',G5_BBS_URL."/register_result.php");
    }

    if($mb['mb_level'] < 5) {
      if($res['message'] == '계정 정보가 잘못되었습니다.' && check_password($mb_password, $mb['mb_password'])) {
        // 쇼핑몰 비밀번호는 맞는데 시스템 비밀번호가 틀린 경우
        // -> 비밀번호찾기로 비밀번호 변경된 경우임. 시스템 비밀번호 변경시킴
        $change_pw_result = post_formdata(EROUMCARE_API_ENT_UPDATE, array(
          'entId' => $mb['mb_entId'],
          'usrId' => $mb['mb_id'],
          'entUsrId' => $mb['mb_id'],
          'usrPw' => $mb_password
        ));
        if($change_pw_result['errorYN'] != 'N')
          alert($change_pw_result['message']);
      } else {
        alert($res['message']);
      }
    }

  } else if($res["errorYN"] == "N") {
    //계정정보 불러오기
    $mb = get_member($mb_id);

    if(!$mb['mb_id'])
      alert('존재하지 않는 계정입니다.');

    //쇼핑몰에 등록이 되어 있지 않으면, 메세지출력
    if (!check_password($mb_password, $mb['mb_password'])) {
      alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
    }
  } else {
    alert("시스템 서버가 응답하지 않습니다.");
  }
} else {
  $mb_id = trim($_POST['mb_id']);
  $mb = get_member($mb_id);
  if(!login_password_check($mb, $mb_password, $mb['mb_password'])) {
    alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
  }
}
// 아미나빌더 소셜로그인
$is_apms_social_check = false;
if(isset($_POST['apms_social']) && $_POST['apms_social']) {

  // 기존 소셜계정정보
  $mb_sn = get_session('mb_sn');

  if (!$mb_sn)
    alert('정상적인 접근이 아닙니다.');

  $mb = sql_fetch(" select * from {$g5['member_table']} where mb_sn = '{$mb_sn}' ", false);

  if (!$mb['mb_id'])
    alert('가입된 소셜계정 정보가 없습니다.');

  $mb_id = $mb['mb_id'];
  $is_apms_social_check = true;

} else {

  $mb_id       = trim($_POST['mb_id']);
  $mb_password = trim($_POST['mb_password']);

  if (!$mb_id || !$mb_password)
    alert('회원아이디나 비밀번호가 공백이면 안됩니다.');

  $mb = get_member($mb_id);
}

//소셜 로그인추가 체크
$is_social_login = false;
$is_social_password_check = false;

// 소셜 로그인이 맞는지 체크하고 해당 값이 맞는지 체크합니다.
if(function_exists('social_is_login_check')){
  $is_social_login = social_is_login_check();

  //패스워드를 체크할건지 결정합니다.
  //소셜로그인일때는 체크하지 않고, 계정을 연결할때는 체크합니다.
  $is_social_password_check = social_is_login_password_check($mb_id);
}

$is_social_password_check = ($is_apms_social_check) ? true : $is_social_password_check;

// 소셜 로그인이 맞다면 패스워드를 체크하지 않습니다.
// 가입된 회원이 아니다. 비밀번호가 틀리다. 라는 메세지를 따로 보여주지 않는 이유는
// 회원아이디를 입력해 보고 맞으면 또 비밀번호를 입력해보는 경우를 방지하기 위해서입니다.
// 불법사용자의 경우 회원아이디가 틀린지, 비밀번호가 틀린지를 알기까지는 많은 시간이 소요되기 때문입니다.
if ($_POST["mb_id"] == "admin" && !$is_social_password_check && (!$mb['mb_id'] || !login_password_check($mb, $mb_password, $mb['mb_password'])) ) {
  alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
}

// 차단된 아이디인가?
if ($mb['mb_intercept_date'] && $mb['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME)) {
  $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_intercept_date']);
  // alert('회원님의 아이디는 접근이 금지되어 있습니다.\n처리일 : '.$date);
  alert('회원님의 아이디는 접근이 금지되어 있습니다.\n처리일 : '.$date, G5_BBS_URL."/member_intercept.php");
}

// 탈퇴한 아이디인가?
if ($mb['mb_leave_date'] && $mb['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME)) {
  $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_leave_date']);
  alert('탈퇴한 아이디이므로 접근하실 수 없습니다.\n탈퇴일 : '.$date);
}

// 파트너몰 일반몰 분리
/*
if ( $mb['mb_level'] < 10 && $mb['mb_type'] != THEMA_KEY ) {
    alert('삼화스탠드몰과 파트너몰에서 동일한 아이디를 사용하실 수 없습니다.');
}
if ( $mb['mb_level'] < 10 && THEMA_KEY == 'default' && $mb['mb_type'] != 'default' ) {
    alert('일반회원은 파트너몰에 로그인 할 수 없습니다.');
}
*/

// 아미나 소셜계정 이메일 정보 업데이트
if($is_apms_social_check) {
  include_once(G5_LIB_PATH.'/register.lib.php');

  $mb_email = get_email_address(trim($_POST['mb_email']));

  if ($msg = empty_mb_email($mb_email)) alert($msg, "", true, true);
  if ($msg = valid_mb_email($mb_email)) alert($msg, "", true, true);
  if ($msg = prohibit_mb_email($mb_email)) alert($msg, "", true, true);

  if ($mb_email != $mb['mb_email']) {
    if ($msg = exist_mb_email($mb_email, $mb_id)) alert($msg, "", true, true);

    //회원정보에 이메일 업데이트
    $mb_sql = "mb_email = '{$mb_email}'";
    if(is_use_email_certify()) {
      $mb_sql .= ", mb_email_certify = ''";

      //값 재설정
      $mb['mb_email_certify'] = '';
      $mb['mb_email'] = $mb_email;
    }
    sql_fetch(" update {$g5['member_table']} set $mb_sql where mb_id = '{$mb_id}' ", false);

  }
}

// 메일인증 설정이 되어 있다면
if ( is_use_email_certify() && !preg_match("/[1-9]/", $mb['mb_email_certify'])) {
  $ckey = md5($mb['mb_ip'].$mb['mb_datetime']);
  confirm("{$mb['mb_email']} 메일로 메일인증을 받으셔야 로그인 가능합니다. 다른 메일주소로 변경하여 인증하시려면 취소를 클릭하시기 바랍니다.", G5_URL, G5_BBS_URL.'/register_email.php?mb_id='.$mb_id.'&ckey='.$ckey);
}

@include_once($member_skin_path.'/login_check.skin.php');

// 회원아이디 세션 생성
set_session('ss_mb_id', $mb['mb_id']);
// FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
// set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
set_session('ss_mb_key', md5($mb['mb_datetime'] . $_SERVER['HTTP_USER_AGENT']));

// 포인트 체크
if($config['cf_use_point']) {
  $sum_point = get_point_sum($mb['mb_id']);

  $sql= " update {$g5['member_table']} set mb_point = '$sum_point' where mb_id = '{$mb['mb_id']}' ";
  sql_query($sql);
}

// 3.26
// 아이디 쿠키에 한달간 저장
if ($auto_login) {
  // 3.27
  // 자동로그인 ---------------------------
  // 쿠키 한달간 저장->일년
  $key = hash('sha256', md5($mb['mb_id'].$_SERVER['HTTP_USER_AGENT'].$mb['mb_password']));
  set_cookie('ck_mb_id', $mb['mb_id'], 86400 * 31 * 12);
  set_cookie('ck_auto', $key, 86400 * 31  * 12);
  // 자동로그인 end ---------------------------
} else {
  set_cookie('ck_mb_id', '', 0);
  set_cookie('ck_auto', '', 0);
}

if ($url) {
    // url 체크
    check_url_host($url, '', G5_URL, true);

    $link = urldecode($url);
    // 2003-06-14 추가 (다른 변수들을 넘겨주기 위함)
    if (preg_match("/\?/", $link))
      $split= "&amp;";
    else
      $split= "?";

    // $_POST 배열변수에서 아래의 이름을 가지지 않은 것만 넘김
    $post_check_keys = array('mb_id', 'mb_password', 'x', 'y', 'url', 'slr_url');

    //소셜 로그인 추가
    if($is_social_login){
      $post_check_keys[] = 'provider';
    }

    foreach($_POST as $key=>$value) {
      if ($key && !in_array($key, $post_check_keys)) {
        $link .= "$split$key=$value";
        $split = "&amp;";
      }
    }

  // 도메인 붙이기
  $p = @parse_url($link);
  if(!isset($p['host']) || !$p['host']) {
    if(G5_URL) {
      $pu = @parse_url(G5_URL);
      $host_url = (isset($pu['path']) && $pu['path']) ? str_replace($pu['path'], '', G5_URL) : G5_URL;
      $link = $host_url.$link;
    }
  }

} else  {
    $link = G5_URL;
}

// 내글반응 체크
if(isset($mb['as_response'])) {
  $row = sql_fetch(" select count(*) as cnt from {$g5['apms_response']} where mb_id = '{$mb['mb_id']}' and confirm <> '1' ", false);
  if($mb['as_response'] != $row['cnt']) {
    sql_query(" update {$g5['member_table']} set as_response = '{$row['cnt']}' where mb_id = '{$mb['mb_id']}' ", false);
  }
}

// 쪽지체크
if(isset($mb['as_memo'])) {
  $row = sql_fetch(" select count(*) as cnt from {$g5['memo_table']} where me_recv_mb_id = '{$mb['mb_id']}' and me_read_datetime = '0000-00-00 00:00:00' ");
  if($mb['as_memo'] != $row['cnt']) {
    sql_query(" update {$g5['member_table']} set as_memo = '{$row['cnt']}' where mb_id = '{$mb['mb_id']}' ", false);
  }
}

//소셜 로그인 추가
if(function_exists('social_login_success_after')){
  // 로그인 성공시 소셜 데이터를 기존의 데이터와 비교하여 바뀐 부분이 있으면 업데이트 합니다.
  $link = social_login_success_after($mb, $link);
  social_login_session_clear(1);
}

//영카트 회원 장바구니 처리
if(defined('G5_USE_SHOP') && G5_USE_SHOP && function_exists('set_cart_id')){
  $member = $mb;

  // 보관기간이 지난 상품 삭제
  //cart_item_clean();
  set_cart_id('');
  $s_cart_id = get_session('ss_cart_id');
  // 선택필드 초기화
  $sql = " update {$g5['g5_shop_cart_table']} set ct_select = '0' where od_id = '$s_cart_id' ";
  sql_query($sql);
}

// 연결기간(3일) 지난 수급자 연결해제
recipient_link_clean();

// 로그인시 구매모드로 설정
//@setcookie('viewType', 'adm', time() + 86400 * 3650, "/");

// 통계등록
insert_statistics("LOGIN", $member['mb_id'], $member['mb_level'], "로그인", $_SERVER['REMOTE_ADDR']);

goto_url(G5_URL);
?>
