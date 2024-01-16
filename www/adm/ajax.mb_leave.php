<?php
include_once('./_common.php');
$sub_menu = '200110';

$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check)
  json_response(400, $auth_check);

header('Content-type: application/json');

if($_POST["mode"] == "reject"){//탈퇴 거부
	$sql = "UPDATE g5_member_leave 
	SET mb_leave_reject_resn='".$_POST['leave_reject_resn']."',
	mb_leave_date3='".date("Ymd")."',
	mb_leave_confirm='".$member['mb_id']."'
	WHERE mb_id='".$_POST['mb_id']."'
	and ml_no='".$_POST['ml_no']."'";
	$result = sql_query($sql);

	if($eroumon_connect_db){//이로움온 탈퇴 거부일, 거부사유,거부자 등록
		$sql = ("UPDATE BPLC SET LEAVE_REJECT_DATE='".date("Ymd")."',LEAVE_REJECT_RESN='".$_POST["leave_reject_resn"]."',LEAVE_CONFIRM_NM='".$member['mb_name']."' WHERE BPLC_ID='".$_POST['mb_id']."';");
        $sql_result2 = "";
        $sql_result2 = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);		
    }
	if(!$result){
		json_response(500, 'DB 오류로 탈퇴 거부에 실패했습니다.');
		exit;
	}
					// 알림톡 발송 : 탈퇴 거부 시작 = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                    //$alimtalk_contents = $RGTR."님, 요청하신 1:1 상담이 취소되었습니다.\n\n◼︎ 상담 취소일 : ".date("Y-m-d")."\n\n상담을 원하시는 경우 이로움ON에서 다시 상담을 요청해 주세요.";
                    $member2 = get_member($_POST['mb_id']);
					$MBR_TELNO = $member2["mb_hp"];//회원휴대폰번호
					
					$alimtalk_contents = "[탈퇴 불가 안내]\n\n안녕하세요. ".$member2["mb_name"]."님\n탈퇴 승인여부 안내드립니다.\n\n탈퇴 가능 여부 : 불가\n탈퇴 불가 사유 : ".$_POST['leave_reject_resn']."\n\n자세한 상담 필요 시 아래 고객센터로 연락 부탁드립니다.\n\n-\n고객센터 : 1533-5088\n운영시간 : 평일 08:30~17:30 (점심시간 12:20~13:20)\n주말/공휴일은 휴무입니다.";
                    $result2 = send_alim_talk('LEAVE_REJECT_'.$MBR_TELNO, $MBR_TELNO, 'Care_0006', $alimtalk_contents, array(
                        'button' => [
                            array(
									'name' => '이로움 바로가기',
									'type' => 'WL',
									'url_mobile' => 'https://eroumcare.com/',
									'url_pc' => 'https://eroumcare.com/'
								  )
                        ]
                    ),'');//내용은 템플릿과 동일 해야 함 
                    // 알림톡 발송 : 탈퇴 거부 종료 = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

		//메일 발송 시작 ==========================================================
		$content = "[탈퇴 요청 거부 안내]<br><br>
		탈퇴 요청이 거부되었습니다.<br><br>
		탈퇴 요청 시 1:1매칭이 되지 않도록 설정되었기 때문에<br>
		진행 여부를 확인하여 관리자 > 멤버스 관리에서 등록정보를 수정해 주세요.<br><br>
		◼︎ 사업소 : ".$member2["mb_id"]."<br>◼︎ 사업자번호 : ".$member2["mb_giup_bnum"]."<br><br>
		▷ 이로움 ON 관리자 바로가기<br> 
		<a href='https://eroum.co.kr/_mng/consult/recipter/list' target='_blank'>https://eroum.co.kr/_mng/consult/recipter/list</a>";
		$to_mail = "thkc_cx@thkc.co.kr";//thkc_cx@thkc.co.kr
		mailer('이로움', 'no-reply@eroumcare.com', $to_mail, "[탈퇴 요청 거부 안내]", $content, 1);
		//메일 발송 끝 ============================================================ 

}else{//탈퇴 승인
	$i = 0 ;
	foreach($_POST['mb_id'] as $mb_id) {
		$comma = ($i == 0)?"":",";
		$mb_ids = explode("|",$mb_id);
		$mb_id2 .= $comma."'".$mb_ids[0]."'";
		$ml_no2 .= $comma."'".$mb_ids[1]."'";
		$i = 1;
	}
	
	$sql1 = "UPDATE g5_member 
	SET mb_leave_date='".date("Ymd")."'
	WHERE mb_id in (".$mb_id2.")";//멤버 테이블 등록
	$result = sql_query($sql1);

	$sql2 = "UPDATE g5_member_leave 
	SET mb_leave_confirm='".$member['mb_id']."'
	WHERE ml_no in (".$ml_no2.")";//탈퇴관리 테이블 등록
	$result = sql_query($sql2);

	if($eroumon_connect_db){//이로움온 탈퇴 승인일, 승인자 등록, 거부일,거부사유 제거
		foreach($_POST['mb_id'] as $mb_id) {
			$mb_ids = explode("|",$mb_id);
			$sql = ("UPDATE BPLC SET LEAVE_CONFIRM_DATE='".date("Ymd")."',LEAVE_CONFIRM_NM='".$member['mb_name']."',LEAVE_REJECT_DATE='',LEAVE_REJECT_RESN='',USE_YN = 'N',RCMDTN_YN = 'N',mb_giup_matching = 'N' WHERE BPLC_ID='".$mb_ids[0]."';");
			$sql_result2 = "";
			$sql_result2 = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);	
		}	
    }
	if(!$result){ 
		json_response(500, 'DB 오류로 탈퇴 승인에 실패했습니다.');
		exit;
	}
	//알림톡 발송
	foreach($_POST['mb_id'] as $mb_id) {
		$mb_ids = explode("|",$mb_id);
					// 알림톡 발송 : 탈퇴 거부 시작 = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                    //$alimtalk_contents = $RGTR."님, 요청하신 1:1 상담이 취소되었습니다.\n\n◼︎ 상담 취소일 : ".date("Y-m-d")."\n\n상담을 원하시는 경우 이로움ON에서 다시 상담을 요청해 주세요.";
                    $member2 = get_member($mb_ids[0]);
					$MBR_TELNO = $member2["mb_hp"];//회원휴대폰번호
					
					$alimtalk_contents = "[탈퇴 승인 완료]\n\n안녕하세요. ".$member2["mb_name"]."님\n탈퇴가 정상적으로 처리되었습니다.\n\n그동안 이로움을 이용해 주셔서 감사합니다.\n더 나은 서비스로 찾아 뵙겠습니다.";
                    $result2 = send_alim_talk('LEAVE_CONFRIM_'.$MBR_TELNO, $MBR_TELNO, 'Care_0005', $alimtalk_contents, '','');//내용은 템플릿과 동일 해야 함 
                    // 알림톡 발송 : 탈퇴 거부 종료 = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	}
}

json_response(200, 'OK');
exit;