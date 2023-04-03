<?php
include_once('./_common.php');
$xapikey = substr(eroumAPI_Key,0,32);//"f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856";

if($_POST["business_id"] == ""){
	session_unset(); // 모든 세션변수를 언레지스터 시켜줌
    session_destroy(); // 세션해제함
	alert("비정상 접근입니다.","/bbs/login.php");
}
$business_id = $_POST["business_id"];
//$business_id = $_REQUEST["business_id"];//테스트용
$aesIv      = str_repeat(chr(0), 16);
$order_business_id = openssl_decrypt(base64_decode($business_id), 'aes-256-cbc', $xapikey, OPENSSL_RAW_DATA, $aesIv);
//$order_business_id2 = openssl_encrypt("321-64-51984", 'aes-256-cbc', $xapikey, OPENSSL_RAW_DATA, $aesIv);


//echo $order_business_id." <br>".base64_encode($order_business_id2);
//exit;
$url = $_POST["url"];
//$eroumAPI_Key = $_REQUEST["eroumAPI_Key"];//테스트용
//$order_business_id = $_REQUEST["order_business_id"];//테스트용

if(isset($order_business_id)){

		$sql_b = "select * from g5_member where mb_giup_bnum='".$order_business_id."' and (mb_level='3' or mb_level='4') " ;
		$mb = sql_fetch($sql_b);

		if($order_business_id == $mb["mb_giup_bnum"]){
			//사업소 정보 있음 정상 로그인 처리 시작
			// 차단된 아이디인가?
			  if ($mb['mb_intercept_date'] && $mb['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME)) {
				$date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_intercept_date']);
				session_unset(); // 모든 세션변수를 언레지스터 시켜줌
                session_destroy(); // 세션해제함
				// alert('관리자 승인이 대기중입니다.',G5_BBS_URL."/register_result.php");
				alert_close('회원님의 아이디는 접근이 금지되어 있습니다.\n처리일 : '.$date, G5_BBS_URL."/member_intercept.php");
			  }
			  // 탈퇴한 아이디인가?
			  if ($mb['mb_leave_date'] && $mb['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME)) {
				$date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_leave_date']);
				session_unset(); // 모든 세션변수를 언레지스터 시켜줌
                session_destroy(); // 세션해제함
				alert_close('탈퇴한 아이디이므로 접근하실 수 없습니다.\n탈퇴일 : '.$date);
			  }
			  echo $mb['mb_id'];
			//exit;
			
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
			print_r($_SESSION);
			//exit;

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
				$link = "/shop/eroumon_order_list.php";
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
			@setcookie('viewType', 'adm', time() + 86400 * 3650, "/");
			if($mb_10 == 1){//급여안내모드로 설정
				@setcookie('viewType', 'basic', time() + 86400 * 3650, "/");
			}
			// 통계등록
			insert_statistics("LOGIN", $member['mb_id'], $member['mb_level'], "로그인", $_SERVER['REMOTE_ADDR']);
			$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
			if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
				@umask(0);
				@mkdir($log_dir,0777);
				//@chmod($upload_dir, 0777);
			}
			
			$log_file = fopen($log_dir . 'eroum_on_sso_log_'.date("Ymd").'.txt', 'a');
			$log_txt = "[".date("Y-m-d H:i:s")."]".$member["mb_name"]."/".$member["mb_id"]." 1.5->1.0 SSO 로그인 (".$_SERVER["REMOTE_ADDR"].")\r\n";
			fwrite($log_file, $log_txt . "\r\n");
			fclose($log_file);

			goto_url($link);



			exit;
		}else{
			session_unset(); // 모든 세션변수를 언레지스터 시켜줌
            session_destroy(); // 세션해제함
			alert("사업소 정보가 없습니다.","/bbs/login.php");
		} 
	
	//alert('파일을 읽을 수 없습니다.');
}else{
	$msg = "비정상 접근입니다. 로그인 후 이용하세요.";
}
session_unset(); // 모든 세션변수를 언레지스터 시켜줌
session_destroy(); // 세션해제함
alert($msg,"/bbs/login.php");
?>