<?php

include "../../common.php";
include G5_ADMIN_PATH."/shop_admin/admin.shop.lib.php";
include "../library.youngcart.php";
$db->query("set NAMES utf8");

//debug($g5['g5_shop_order_table']);

### 테스트용
/*
$_GET['tid]'		= "1";
$_GET['bankcode']	= "1";
$_GET['account']	= "21833704001905";
$_GET['price']		= "500";
$_GET['name']		= "최고관리자";
$_GET['paydt']		= "2014-09-17 19:30:38";
$_GET['uid']		= "test";
$_GET['charset']	= "UTF-8";
*/

### 유효성체크 ######################################################################################################################################

if (!$_GET['tid'] || !$_GET['bankcode'] || !$_GET['account'] || !$_GET['price'] || !$_GET['name'] || !$_GET['paydt'] || !$_GET['uid']  || !$_GET['charset']) exit;

### 접속허용 IP 체크 ################################################################################################################################

### 허용IP리스트
$r_arrow_ip = array(
	gethostbyname('apibox.kr'),			/* APIBOX 실서버 */
	gethostbyname('apibox.co.kr'),		/* APIBOX 백업서버 */
	gethostbyname('whenji.com'),		/* APIBOX 백업서버 */
	'121.168.248.143',									/* 이곳에 개발PC IP주소등 접속허용IP를 기록하세요 */
	);

### 허용IP가 아닌경우 차단
if (!in_array($_SERVER['REMOTE_ADDR'],$r_arrow_ip)) exit;

### 로그기록 ########################################################################################################################################

$chk = $db->fetch("show tables like 'tb_log_bank'");
if (!$chk){
	$query = "
	CREATE TABLE `tb_log_bank` (
		`no` INT(11) NOT NULL AUTO_INCREMENT,
		`tid` CHAR(50) NOT NULL,
		`bankcode` INT(11) NOT NULL,
		`account` VARCHAR(20) NOT NULL,
		`price` INT(11) NOT NULL,
		`name` VARCHAR(50) NOT NULL,
		`paydt` DATETIME NOT NULL,
		`uid` VARCHAR(20) NOT NULL,
		`charset` ENUM('UTF-8','EUC-KR') NOT NULL,
		`cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
		`step` TINYINT(4) NOT NULL DEFAULT '0',
		`result` VARCHAR(255) NULL DEFAULT NULL,
		`ip` VARCHAR(50) NOT NULL DEFAULT '0',
		`memo` text DEFAULT NULL COMMENT '메모',
		PRIMARY KEY (`no`),
		UNIQUE INDEX `Index 2` (`tid`)
	)";
	$db->query($query);
}

$query = "
insert into tb_log_bank set
	tid				= '$_GET[tid]',
	bankcode		= '$_GET[bankcode]',
	account			= '$_GET[account]',
	price			= '$_GET[price]',
	name			= '$_GET[name]',
	paydt			= '$_GET[paydt]',
	uid				= '$_GET[uid]',
	charset			= '$_GET[charset]',
	cnt				= '1',
	step			= '1',
	ip				= '$_SERVER[REMOTE_ADDR]'
on duplicate key update
	cnt				= cnt+1,
	ip				= '$_SERVER[REMOTE_ADDR]'
";
$db->query($query);

#####################################################################################################################################################

try {

	### 이미 처리되었는지 확인
	$query = "select result from tb_log_bank where tid = '$_GET[tid]' and step != 1";
	list($result) = $db->fetch($query,1);
	if ($result) throw new Exception($result);

	### 중복실행체크
	/*
	list($step) = $db->fetch("select step from tb_log_bank where tid = '$_GET[tid]'",1);
	if ($step>0) throw new Exception();
	*/

	### 주문정보조회
	$where[]	= "od_status		= '주문'";			//주문상태
	$where[]	= "od_settle_case	= '무통장'";			//결제방식
	$where[]	= "od_deposit_name	= '$_GET[name]'";	//입금자명
	$where[]	= "od_misu			= '$_GET[price]'";	//입금금액
	$where[]	= "od_time			<= '$_GET[paydt]'";	//주문시각
	$where		= implode(" and ",$where);

	$query		= "select count(*) from $g5[g5_shop_order_table] where $where";
	list($cnt) = $db->fetch($query,1);

	if (!$cnt || $cnt<=0){

		### 전송실패로그기록
		$query = "update tb_log_bank set step = '-9', result = 'FAIL' where tid = '$_GET[tid]'";
		$db->query($query);

		throw new Exception("FAIL");

	}

	if ($cnt > 1){

		### 전송실패로그기록
		$query = "update tb_log_bank set step = '-9', result = 'FAIL' where tid = '$_GET[tid]'";
		$db->query($query);

		throw new Exception("FAIL");

	}

	if ($cnt==1){

		### 전송성공로그기록
		$query = "update tb_log_bank set step = '9', result = 'OK' where tid = '$_GET[tid]'";
		$db->query($query);

		### 주문정보추출
		$data = $db->fetch("select * from $g5[g5_shop_order_table] where $where");

		### 상품 : 주문->입금처리
		$query = "
			update $g5[g5_shop_order_table]
			set
				od_status			= '입금',
				od_receipt_price	= '$_GET[price]',
				od_misu				= '0',
				od_receipt_time		= NOW()
			where
				od_id				= '$data[od_id]'
			";
		$db->query($query);

		### 아이템 : 주문->입금처리
		$ct_history = "자동입금|".date("Y-m-d H:i:s")."|$_SERVER[REMOTE_ADDR]";
		$query = "
			update $g5[g5_shop_cart_table]
			set
				ct_status		= '입금',
				ct_history		= CONCAT(ct_history,'$ct_history')
			where
				od_id			= $data[od_id]
				and ct_status	= '주문'
			";
		$db->query($query);

		### 아이코드문자발송
		if ($config['cf_sms_use']=="icode" && $default['de_sms_use4']){
			include_once(G5_LIB_PATH."/icode.sms.lib.php");
			$SMS = new SMS;
			$SMS->SMS_con($config['cf_icode_server_ip'],$config['cf_icode_id'],$config['cf_icode_pw'],$config['cf_icode_server_port']);

			$sms_contents = conv_sms_contents($data['od_id'],$default['de_sms_cont4']);
			$sms_contents = (mb_detect_encoding($sms_contents)=="UTF-8") ? mb_convert_encoding($sms_contents,"EUC-KR","UTF-8") : $str;

			if ($sms_contents){
				$receive_number	= preg_replace("/[^0-9]/","",$data['od_hp']);					// 수신자번호
				$send_number	= preg_replace("/[^0-9]/","",$default['de_admin_company_tel']);	// 발신자번호

				if ($receive_number && $send_number){
					$SMS->Add($receive_number,$send_number,$config['cf_icode_id'],$sms_contents,"");
					$SMS->Send();
				}
			}
		}
		
		throw new Exception("OK");
	}

} catch (Exception $e){

	$result = $e->getMessage();
	$result = trim(strip_tags($result));
	
	echo $result;

}

?>