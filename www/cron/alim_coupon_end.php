<?php
include_once('./_common.php');

/*
 * 쿠폰마감 알림톡 보내기
 * 매일 오후 12시 한번 보냄
 * ct_alim: 0 = 알림톡 미전송, 1 = 알림톡 전송완료
 */

$data = json_decode(file_get_contents('php://input'), true);

$key_check = "2xBkK#4fKR9hPp=x+J9dDWr9fxR5Nt*2^e@D-!AL";
$key = $data['key'];

$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
	@umask(0);
	@mkdir($log_dir,0777);
	//@chmod($upload_dir, 0777);
}
$log_file = fopen($log_dir . 'coupon_log_'.date("Ymd").'.txt', 'a');
$log_txt = "====== 쿠폰 소멸안내 시작 [".$_SERVER["REMOTE_ADDR"]."] ============================================ \r\n";



// 키 인증
if($key !== $key_check){
	$log_txt .= "[".date("Y-m-d H:i:s")."]"."인증에 실패했습니다. \r\n";
	$log_txt .= "====== 쿠폰 소멸안내 끝 ============================================================= \r\n";
	fwrite($log_file, $log_txt . "\r\n");
	fclose($log_file);
    json_response(400, '인증에 실패했습니다.');
}
	
$token = get_biztalk_token();//알림톡 필수
if(!$token){//알림톡 필수
	$log_txt .= "[".date("Y-m-d H:i:s")."]"."비즈톡 토큰 발급 오류 \r\n";
	$log_txt .= "====== 쿠폰 소멸안내 끝 ============================================================= \r\n";
	fwrite($log_file, $log_txt . "\r\n");
	fclose($log_file);
    json_response(500, '비즈톡 토큰 발급 오류');//알림톡 필수
}
$log_txt_con = "쿠폰소멸 알림 내역이 없습니다. \r\n"; 
$sql = "SELECT a.*
,b.mb_id AS mb_id2 
FROM `g5_shop_coupon` a
LEFT JOIN `g5_shop_coupon_member` b ON a.cp_no = b.cp_no
WHERE a.cp_end = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 6 DAY),'%Y-%m-%d') 
AND b.mb_id NOT IN (SELECT mb_id FROM `g5_shop_coupon_log` WHERE cp_id = a.cp_id)";//마감 7일전 사용안한 쿠폰 조회
$result = sql_query($sql);

while($cp = sql_fetch_array($result)) {
    $mb = get_member($cp['mb_id2']);
	if($mb["mb_hp"] !=""){
		$alimtalk_contents = "[이로움]\n".get_text($mb['mb_name'])."님, 보유한 쿠폰이 소멸 예정되어 안내드립니다.\n■ 쿠폰명 : ".$cp["cp_subject"]."\n■ 유효기간 : ".$cp['cp_end']." 23시 59분까지\n\n상기 쿠폰은 유효기간 내 미 사용 시 소멸됩니다.\n\n* 이 메시지는 고객님의 동의에 의해 지급된 쿠폰의 소멸 안내 메시지입니다.";
		$result2 = send_alim_talk('COUPONDEL_'.str_replace("-","",$cp['cp_id']).'_'.str_replace("-","",$mb["mb_hp"]), $mb["mb_hp"], 'ent_coupon_delete', $alimtalk_contents, array(
    'button' => [
      array(
        'name' => '쿠폰확인',
        'type' => 'WL',
        'url_mobile' => 'https://eroumcare.com/',
		'url_pc' => 'https://eroumcare.com/'
      )
    ]
  ));//내용은 템플릿과 동일 해야 함
		$log_txt_con = "[".date("Y-m-d H:i:s")."] 비즈톡 발송 결과 : (".$mb['mb_name'].")".stripslashes(json_encode($result2, JSON_UNESCAPED_UNICODE))." \r\n";
	}else{
		$log_txt_con = "[".date("Y-m-d H:i:s")."] 비즈톡 발송 결과 : ".$mb['mb_name']."님의 휴대폰 정보가 없습니다. \r\n";
	}
	
}
$log_txt .= $log_txt_con;
$log_txt .= "====== 쿠폰 소멸안내 끝 ============================================================= \r\n";
fwrite($log_file, $log_txt . "\r\n");
fclose($log_file);

json_response(200, 'OK');
