<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
header('Content-type: application/json');

$query = "SHOW tables LIKE 'g5_member_leave'";//탈퇴신청 관리 테이블 확인
$wzres = sql_num_rows( sql_query($query) );
if($wzres < 1) {
	sql_query("CREATE TABLE `g5_member_leave` (
  `ml_no` int(11) NOT NULL AUTO_INCREMENT COMMENT '탈퇴 신청번호',
  `mb_id` varchar(30) NOT NULL COMMENT '탈퇴 신청인',
  `mb_leave_confirm_date` varchar(20) NOT NULL COMMENT '탈퇴 승인일',
  `mb_leave_date2` varchar(20) NOT NULL COMMENT '탈퇴 신청일',
  `mb_leave_resn` text DEFAULT NULL COMMENT '탈퇴 사유',
  `mb_leave_date3` varchar(20) DEFAULT NULL COMMENT '탈퇴 거부일',
  `mb_leave_reject_resn` text DEFAULT NULL COMMENT '탈퇴 거부 사유',
  `mb_leave_confirm` varchar(50) DEFAULT NULL COMMENT '탈퇴 승인자',
  KEY `mb_id` (`mb_id`,`mb_leave_date2`,`mb_leave_date3`),
  KEY `ml_no` (`ml_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
}
 	
if($_POST["mode"] == "check"){//탈퇴 신청 시 정보 조회 
	$data["msg"] = "현재 회원탈퇴 신청이 불가합니다.\n";
	// == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    if( $eroumon_connect_db && $member["mb_giup_matching"] =='Y') { 
		$fr_date = "2023-01-01 00:00:00";//시작일시
		$to_date = date("Y-m-d H:i:s");//오늘
		 // 페이지 진입에 따른 조건 기준으로 검색된 검색 개수.
		 // 프로시저 : CALL `PROC_EROUMCARE_CONSLT`('모드','회원사업자번호', '검색시작일', '검색종료일','페이지포인터시작','리스트수량','검색조건');
        $_Search = "AND ((MCR.CONSLT_STTUS=''CS02'') OR (MCR.CONSLT_STTUS=''CS08''))"; // 상담 접수 중
		$sql = (" CALL `PROC_EROUMCARE_CONSLT`('cnt','{$member['mb_giup_bnum']}', '{$fr_date}', '{$to_date}', NULL, NULL, '{$_Search}'); ");
        $sql_result = "";
        $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
		$total_count1 = $sql_result['cnt'];

		$_Search = "AND (MCR.CONSLT_STTUS=''CS05'')";// 상담 진행 중
		$sql = (" CALL `PROC_EROUMCARE_CONSLT`('cnt','{$member['mb_giup_bnum']}', '{$fr_date}', '{$to_date}', NULL, NULL, '{$_Search}'); ");
        $sql_result = "";
        $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
		$total_count2 = $sql_result['cnt'];
		if(($total_count1+$total_count2)>0){//상담접수,상담진행 중 하나라도 있으면 탈퇴 안됨
			$data["msg"] .= "사유 : 완료되지 않은 1:1 상담이 있습니다.";
			$data["YN"] = "N";
			
			echo json_encode($data);
			exit;
		}
	}

	$sql = "SELECT COUNT(ct_id) AS cnt2 FROM g5_shop_cart WHERE mb_id='{$member['mb_id']}' AND ct_status IN ('준비','배송','출고준비');";
	$count1 = sql_fetch($sql);// 주문 완료가 되지 않은 건수

	$sql2 = "SELECT COUNT(*) AS cnt2 FROM eform_document WHERE entId='{$member['mb_entId']}' AND dc_status IN ('11','4');";
	$count2 = sql_fetch($sql2);// 계약 완료가 되지 않은 건수


	if($count1["cnt2"] > 0){
		$data["msg"] .= "사유 : 구매가 완료되지 않은 상품이 있습니다.";
		$data["YN"] = "N";

		echo json_encode($data);
		exit;
	}elseif($count2["cnt2"] > 0){
		$data["msg"] .= "사유 : 간편계약 미완료 건이 있습니다.";
		$data["YN"] = "N";

		echo json_encode($data);
		exit;
	}else{
		$data["msg"] = "";
		$data["YN"] = "Y";
	}

	echo json_encode($data);
	exit;
}elseif($_POST["mode"] == "request"){//탈퇴 신청 시
	$tmp_password = get_encrypt_string($_POST['mb_password2']);

    if ($member['mb_password'] != $tmp_password) {
         // 비밀번호 틀릴 경우 
        $result = api_post_call(EROUMCARE_API_ACCOUNT_ENT_LOGIN, array(
            'usrId' => $member['mb_id'],
            'pw' => $_POST['mb_password2']
        ));

		if(!$result || $result['errorYN'] != 'N'){
            $data["msg"] = "비밀번호를 정확히 입력해 주세요.";
			$data["YN"] = "N";
			echo json_encode($data);
			exit;
		}
    }
	if(trim(str_replace("-","",$_POST["bnum"])) != trim(str_replace("-","",$member['mb_giup_bnum']))){
		$data["msg"] = "사업자등록번호를 정확히 입력해 주세요.";
		$data["YN"] = "N2";
		echo json_encode($data);
		exit;
	}
	
	$sql = "INSERT INTO g5_member_leave (mb_leave_date2,mb_leave_resn,mb_id) VALUES ('".date("Ymd")."','".$_POST["leave_resn"]."','".$member['mb_id']."')";//탈퇴 신청
	sql_query($sql);

	if($eroumon_connect_db){//이로움온 탈퇴 신청일, 신청사유 등록
		$sql = ("UPDATE BPLC SET LEAVE_REQUEST_DATE='".date("Ymd")."',LEAVE_RESN='".$_POST["leave_resn"]."',LEAVE_REJECT_DATE='',LEAVE_REJECT_RESN='',LEAVE_CONFIRM_NM='' WHERE BPLC_ID='".$member['mb_id']."' AND BRNO='".$member['mb_giup_bnum']."';");
        $sql_result2 = "";
        $sql_result2 = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);		
    }
	//메일 발송 시작 ==========================================================
	$content = "[탈퇴 신청 접수 안내]<br><br>
	탈퇴를 신청한 사업소가 있습니다.<br>
	확인 후 승인 및 거부 처리를 진행해 주세요.<br><br>
	■ 탈퇴 요청 사업소 : ".$member['mb_name']."<br>
	■ 신청일자 : ".date("Y-m-d")."<br><br>
	▷ 이로움 Care 관리자 바로가기<br>
	<a href='https://eroumcare.com/adm/' target='_blank'>https://eroumcare.com/adm/</a>";
	$to_mail = "thkc202205000007@thkc.co.kr";
	if(strpos($_SERVER['HTTP_HOST'],".eroumcare")){//dev,test 서버 시 발송
		mailer(mailer($config['cf_admin_email_name'], $config['cf_admin_email'], "cdcj9090@thkc.co.kr", "[탈퇴 신청 접수 안내]", $content, 1);
		mailer(mailer($config['cf_admin_email_name'], $config['cf_admin_email'], "dglee@thkc.co.kr", "[탈퇴 신청 접수 안내]", $content, 1);
	}else{//상용서버 발송
		mailer(mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $to_mail, "[탈퇴 신청 접수 안내]", $content, 1);		
	}
	//메일 발송 끝 ============================================================ 

	$data["msg"] = "";
	$data["YN"] = "Y";
	echo json_encode($data);
	exit;
}
?>
