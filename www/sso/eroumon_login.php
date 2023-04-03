<?php
include_once('./_common.php');
$xapikey = substr(eroumAPI_Key,0,32);//"f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856";
//echo $member["mb_giup_bnum"];
$url = eroumon_login_url;//http://192.168.0.229/partners/login
$aesIv      = str_repeat(chr(0), 16);
$order_business_id2 = base64_encode(openssl_encrypt($member["mb_giup_bnum"], 'aes-256-cbc', $xapikey, OPENSSL_RAW_DATA, $aesIv));
//echo $order_business_id2;
$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
			if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
				@umask(0);
				@mkdir($log_dir,0777);
				//@chmod($upload_dir, 0777);
			}
			
			$log_file = fopen($log_dir . 'eroum_on_sso_log_'.date("Ymd").'.txt', 'a');
			$log_txt = "[".date("Y-m-d H:i:s")."]".$member["mb_name"]."/".$member["mb_id"]." 1.0->1.5 SSO 로그인 (".$_SERVER["REMOTE_ADDR"].")\r\n";
			fwrite($log_file, $log_txt . "\r\n");
			fclose($log_file);

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>
	$(document).ready(function(){
	var new_form = $('<form></form>'); 
        new_form.attr("name", "eroumon_login");
        new_form.attr("method", "post");
        new_form.attr("action", "<?=$url?>");
        new_form.attr("target", "_blank"); 
        new_form.append($('<input/>', {type: 'hidden', name: 'business_id', value: '<?=$order_business_id2?>'}));
        new_form.appendTo('body');
 
        new_form.submit();
});
</script>


