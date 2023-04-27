<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

$mb_id = trim($_POST['reg_mb_id']);

set_session('ss_check_mb_id', '');

if ($msg = empty_mb_id($mb_id))     die($msg);
if ($msg = valid_mb_id($mb_id))     die($msg);
if ($msg = count_mb_id($mb_id))     die($msg);
if ($msg = exist_mb_id($mb_id))     die($msg);
if ($msg = reserve_mb_id($mb_id))   die($msg);



// 23.04.25 : 서원 -	WMDS기존 가입회원 정보 확인(추가!!)
//					WMDS에 등록되었다가 g5_member 테이블에서 삭제 처리 될 경우 중복 아이디 사용 방지.
$sendData = [];
$sendData["usrId"] = $mb_id;
$result = get_eroumcare(EROUMCARE_API_ENT_ACCOUNT, $sendData);

if($result['errorYN'] == 'N'){
	if( is_array($result['data']) && count($result['data'])>1 ) {
  		die($result['message']);
	}
}


set_session('ss_check_mb_id', $mb_id);
?>