<?php
//require_once('../vendor/autoload.php');
include_once('./_common.php');
$API_Key64 = base64_encode(G5_MDS_ID.":".G5_MDS_KEY); //API 접속 base64 인코딩 키
//$client = new \GuzzleHttp\Client();

$templateId1_1 = "f70a6140-6bfb-11ee-9526-335ec08fd824";//급여제공기록지 템플릿 5칸 
$templateId1_2 = "5ff7dea0-6bfa-11ee-9526-335ec08fd824";//급여제공기록지 템플릿 10칸
$templateId1_3 = "75c50490-6bb6-11ee-bb6a-fd3a1f54a209";//급여제공기록지 템플릿 14칸

ini_set("allow_url_fopen", true);
header('Content-type: application/json');

$response2 = array();
$response2["api_stat"] = "0";

if($_POST["div"] == "sign_stat" || $_POST["div"] == "view_doc" || $_POST["div"] == "rejection_view"){// 서명 상황, 계약서 보기,거절사유보기
	$api_url = 'https://api.modusign.co.kr/documents/'.strtolower($_POST["doc_id"]);
	$type = "GET";
	$data = "";
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);
	if($_POST["div"] == "sign_stat"){//서명 상황
		$url = $arrResponse["file"]["downloadUrl"];
		$participants_count = count($arrResponse["participants"]);
		$gubun1 = "-";
		$sign_date1 = "-";
		$stat1 = "대상아님";
		for($j=0;$j<count($arrResponse["signings"]);$j++ ){
			$p_signedAts[$arrResponse["signings"][$j]["participantId"]] = $arrResponse["signings"][$j]["signedAt"];
		}
		
		for($i=0;$i<$participants_count;$i++){
			if($arrResponse["participants"][$i]["name"] == "수급자(보호자)"){//수급자(보호자)로 바꿔야함
				$gubun1 = ($arrResponse["participants"][$i]["signingMethod"]["type"] == "SECURE_LINK")?"웹페이지":"카카오톡";			
				$sign_date1 = ($p_signedAts[$arrResponse["participants"][$i]["id"]] != "")? date("Y-m-d H:i:s",strtotime($p_signedAts[$arrResponse["participants"][$i]["id"]])):"-"; 
				$signingDue = ($arrResponse["participants"][$i]["signingDue"]["datetime"] != "")? date("Y-m-d H:i:s",strtotime($arrResponse["participants"][$i]["signingDue"]["datetime"])):"-"; 
				$stat1 = ($sign_date1 == "-")?"진행중":"완료";
				if($sign_date1 != "-"){//서명완료 시 
					$sql = "update `eform_rent_hist` set dc_sign_datetime='".$sign_date1."',rh_status='3' WHERE doc_id='".$_POST["doc_id"]."'";
					sql_query($sql);
					//서명완료 로그 =====================================================================================================================
					// 계약서 로그 작성
					$ip = $_SERVER['REMOTE_ADDR'];
					$browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
					$datetime = date('Y-m-d H:i:s');
					$log = '전자계약서에 서명했습니다.';

					sql_query("INSERT INTO `eform_document_log2` SET
					`dc_id` = '".$_POST["dc_id"]."',
					`dl_log` = '$log',
					`dl_ip` = '$ip',
					`dl_browser` = '$browser',
					`dl_datetime` = '$datetime'
					");
				}
				$part_id1 = $arrResponse["participants"][$i]["id"];
			}
		}
		$sql = "SELECT * FROM `eform_rent_hist` WHERE doc_id='".$_POST["doc_id"]."'";
		$row=sql_fetch($sql);
		
		if($url != ""){
			$response2["url"] = $url;
			$response2["contract_sign_name"] = $row['contract_sign_name'];
			$response2["contract_sign_type"] = $row['contract_sign_type'];
			$response2["applicantRelation"] = $row['applicantRelation'];
			$response2["gubun1"] = $gubun1;
			$response2["stat1"] = $stat1;
			$response2["sign_date1"] = $signingDue;
			$response2["part_id1"] = $part_id1;
			$response2["api_stat"] = "1";
		}else{
			$response2["url"] = "url생성실패";
		}
	}elseif($_POST["div"] == "view_doc"){//계약서 보기
		$url = $arrResponse["file"]["downloadUrl"];
		if($url != ""){
			$response2["url"] = $url;
			$response2["api_stat"] = "1";
		}else{
			$response2["url"] = "url생성실패";
		}
	}elseif($_POST["div"] == "rejection_view"){// 거절사유 보기
		$url = $arrResponse["file"]["downloadUrl"];
		$participants_count = count($arrResponse["participants"]);		
		for($i=0;$i<$participants_count;$i++){
			if($arrResponse["participants"][$i]["id"] == $arrResponse["abort"]["participantId"]){
				$rejection_member = $arrResponse["participants"][$i]["name"];
			}
		}
		$rejection_date = date("Y-m-d H:i:s",strtotime($arrResponse["abort"]["abortedAt"]));
		$rejection_msg = $arrResponse["abort"]["message"];
		if($url != ""){
			$response2["url"] = $url;
			$response2["date"] = $rejection_date;
			$response2["msg"] = $rejection_msg;
			$response2["member"] = $rejection_member;
			$response2["api_stat"] = "1";
		}else{
			$response2["url"] = "url생성실패";
		}
	}

}elseif($_POST["div"] == "sign_doc"){//계약서 서명	
	$api_url = 'https://api.modusign.co.kr/documents/'.$_POST["doc_id"].'/participants/'.$_POST["part_id"].'/embedded-view?redirectUrl=https%3A%2F%2F'.$_SERVER[ "HTTP_HOST" ].'%2Fshop%2Feform_sign_completed.php%3F'.$_POST["dc_id"];
	$type = "GET";
	$data = "";
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);
	$url = $arrResponse["embeddedUrl"];
	if($url != ""){
		$response2["url"] = $url;
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
}elseif($_POST["div"] == "new_doc"){//계약서 생성
	
	// 계약서 조회	
	$uuid = isset($_POST["dc_id1"]) ? get_search_string($_POST["dc_id1"]) : '';

	$sql = "select * from eform_rent_hist where rh_id='{$uuid}'";
	$row = sql_fetch($sql);
	$_POST['penId'] = $row["penId"];
	$_POST['confirm_date'] = $row["confirm_date"];
	$_POST['create_month'] = $row["create_month"];
	$_POST['entConAcc'] = $row["entConAcc"];
	$_POST['penRecTypeCd'] = $row["penRecTypeCd"];
	$_POST['it_ids'] = $row["it_ids"];
	$_POST['it_dates'] = $row["it_dates"];
	$_POST['contract_sign_relation'] = $row["contract_sign_relation"];//관계코드
	$_POST['contract_sign_relation_nm'] = $row["contract_sign_relation_nm"];//기타관계명
	$_POST['pen_guardian_nm'] = $row["pen_guardian_nm"];//서명자명

	//수급자 정보
	$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
	  'usrId' => $member['mb_id'],
	  'entId' => $member['mb_entId'],
	  'penId' => $_POST['penId']
	));

	$pen = $res['data'][0];
	$pen_nm = $pen['penNm'];

	switch($_POST['contract_sign_relation']){
		case '1':
		$contract_sign_relation_con = " [     ] 본인   [  V  ] 가족   [     ] 친족   [     ] 기타 (      )";
		break;
		case '2':
		$contract_sign_relation_con = " [     ] 본인   [     ] 가족   [  V  ] 친족   [     ] 기타 (      )";
		break;
		case '3':
		$contract_sign_relation_con = " [     ] 본인   [     ] 가족   [     ] 친족   [  V  ] 기타 (".$_POST['contract_sign_relation_nm'].")";
		break;
		default:
		$contract_sign_relation_con = " [  V  ] 본인   [     ] 가족   [     ] 친족   [     ] 기타 (      )";
		$_POST['pen_guardian_nm'] = $pen_nm;
		break;
	}

 
$it_id = explode(",",$_POST['it_ids']);
$row_count = count($it_id);
$it_date = explode(",",$_POST['it_dates']);

for($ii = 0; $ii < $row_count; $ii++ ){
	$it_dates2[$it_id[$ii]] = $it_date[$ii];
}

$title_text = $row['entNm']."_".$row['entNum']."_".$pen['penNm']."_".substr($pen['penLtmNum'],0,6)."_".date("Ymd");

$sql1 = "SELECT * FROM eform_document_item WHERE it_id IN (".$_POST['it_ids'].")";
$resutl = sql_query($sql1);
while($row2 = sql_fetch_array($resutl)){
	$ca_name[$row2["it_id"]] = $row2["ca_name"];//품목명
	$it_name[$row2["it_id"]] = $row2["it_name"];//제품명
	$it_code[$row2["it_id"]] = $row2["it_code"];//품목코드
	$it_barcode[$row2["it_id"]] = $row2["it_barcode"];//바코드
	$it_rental_price[$row2["it_id"]] = $row2["it_rental_price"];//기준급여비용
}


function calc_rental_price($str_date, $end_date, $price,$penTypeCd) {
    $rental_price = 0;
	$price22 = array();
    $str_time = strtotime($str_date);
    $end_time = strtotime($end_date);

    $year1 = date('Y', $str_time);
    $year2 = date('Y', $end_time);

    $month1 = date('m', $str_time);
    $month2 = date('m', $end_time);

	$day1 = date('d', $str_time);
    $day2 = date('d', $end_time);

    $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

    // 중간달 계산
    if($diff > 1) {
        $rental_price1 += ( $price * ($diff - 1) );
    }

    
    if($diff == 0){ //년,월 차이 없이 일만 차이 있을 경우
		$rental_price2 += (int) (round(
			$price * (
				($end_date-$str_date+1)
				/
				( date('t', $end_time)*10 )
			))*10
		) ;
	}else{// 마지막 달 계산 
		$rental_price2 += (int) (round(
			$price * (
				date('j', $end_time)
				/
				( date('t', $end_time) * 10 )
			)
		)) * 10;
	}

    if($diff > 0) {
        // 첫째 달 계산
        $rental_price3 += (int) (round(
            $price * (
                ( date('t', $str_time) - date('j', $str_time) + 1 )
                /
                ( date('t', $str_time) * 10 )
            )
        )) * 10;
    }

	$rental_price = $rental_price1+$rental_price2+$rental_price3;
    $price22["calc_rental_price"] = $rental_price;//$rental_price;
	if($diff == 0){//단기계약
		$price22["calc_pen_price"] = calc_pen_price(($penTypeCd), ($rental_price2),2);
	}else{//일반계약
		$price22["calc_pen_price"] = calc_pen_price(($penTypeCd), $rental_price1+$rental_price2+$rental_price3,1);
	}

	return $price22;
}

function calc_pen_price($penTypeCd, $price,$round_floor) {
	switch($penTypeCd) {
        case '00':
            $rate = 15;
            break;
        case '01':
            $rate = 9;
            break;
        case '02':
        case '03':
            $rate = 6;
            break;
        case '04':
            return 0;
        default:
            $rate = 15;
            break;
    }
	
	if($round_floor == 2){
		$pen_price =  (int)round(
			$price * ($rate / 100)/10
		) * 10;
	}else{
		$pen_price =  (int)floor(
			$price * ($rate/ 100)/10
		)* 10 ;
	}

    return $pen_price;
}

	//excluded : 해당 서명자 제외 유무 표시 (false-제외안함,true-제외함)
	//signingDuration : 서명 유효 기간 (20160 - 14일)유효기간 수정 기능 있음, 최대 525600qns(365일) 
	//role : 서명자 역할 지정 name : 서명자 이름
	//requesterMessage : 서명자에게 보낼 메세지
	//서명자가 여러명일 때 참여자 서명수단의 value 값이 다 달라야함
	//requesterInputMappings 은 값이 있을때만 맞는 값만 전송해야함 없는 dataLabel 에 데이터를 전송 하면 에러남
	//수급자 발송정보
	$pen_sign_info = '{"excluded":false,"signingMethod":{"type":"'.$_POST["pen_send"].'","value":"'.$_POST["pen_send_tel"].'"},"signingDuration":525600,"locale":"ko","role":"수급자(보호자)","name":"수급자(보호자)","requesterMessage":"['.$row["entNm"].'] 에서 장기요양급여 제공기록지 서명을 요청하였습니다."}';
	if($row_count < 6){
		$temp_doc_id = $templateId1_1;//5개 이하
	}elseif($row_count < 11){
		$temp_doc_id = $templateId1_2;//10개 이하
	}elseif($row_count < 15){
		$temp_doc_id = $templateId1_3;//14개 이하
	}

	//수급자 기본 정보
	$pen_name_3  = $pen['penNm'];//수급자 이름
	$pen_birthday_1 =  $pen['penBirth'];//수급자생일
	$pen_grade_2 =  $pen['penRecGraNm'];//수급자인정등급명
	$pen_ltmnum_2 =  $pen['penLtmNum'];//수급자장기요양인정번호
	$ent_name_3 =  $row['entNm'];//사업소이름
	$ent_entnum_2 = $member["mb_ent_num"];//사업소기관번호

	//상품 정보
	for($i = 0;$i < $row_count; $i++){
		$price = calc_rental_price(str_replace("-","",substr($it_dates2[$it_id[$i]],0,10)), str_replace("-","",substr($it_dates2[$it_id[$i]],11,10)), $it_rental_price[$it_id[$i]],$pen['penTypeCd']);
		$it_price = $price["calc_rental_price"];//대여가(추가)
		$it_price_pen = $price["calc_pen_price"];//본인부담금(추가)
		$it_price_ent = $it_price - $it_price_pen;//공단부담금

		$it_rent_categoryN_2	.= ',{"dataLabel":"it_rent_category'.($i+1).'_2","value":"'.$ca_name[$it_id[$i]].'"}'; //아이템대여_품목명
		$it_rent_nameN_2		.=',{"dataLabel":"it_rent_name'.($i+1).'_2","value":"'.$it_name[$it_id[$i]].'"}'; //대여이름
		$it_rent_codeN_2		.=',{"dataLabel":"it_rent_code'.($i+1).'_2","value":"'.$it_code[$it_id[$i]]."-\\n".$it_barcode[$it_id[$i]].'"}'; //대여품목코드
		$it_rent_priceN_2		.=',{"dataLabel":"it_rent_price'.($i+1).'_2","value":"'.number_format($it_price).'"}'; //대여품목급여가
		$it_rent_dateN_2		.=',{"dataLabel":"it_rent_date'.($i+1).'_2","value":"'.substr($it_dates2[$it_id[$i]],0,11)."\\n".substr($it_dates2[$it_id[$i]],11,10).'"}'; //대여품목계약일
		$it_rent_sumpriceN_1	.=',{"dataLabel":"it_rent_sumprice'.($i+1).'_1","value":"'.number_format($it_price).'"}'; //대여품목_총액1
		$it_rent_price_penN_2	.=',{"dataLabel":"it_rent_price_pen'.($i+1).'_2","value":"'.number_format($it_price_pen).'"}'; //대여품목본인부담금
		$it_rent_price_entN_1	.=',{"dataLabel":"it_rent_price_ent'.($i+1).'_1","value":"'.number_format($it_price_ent).'"}'; //대여품목_공단부담금1
	}

	$ent_ConAcc_1 = nl2br($row["entConAcc"]);//특이사항
	$ent_ConAcc_1 = preg_replace('/\r\n|\r|\n/','',$ent_ConAcc_1);
	$ent_ConAcc_1 = str_replace("<br />","\\n",$ent_ConAcc_1);
	$ent_ConAcc_1 = str_replace('"','\\"',$ent_ConAcc_1);

	$ent_ceoname_3 = $member["mb_entNm"];//사업소대표이름
	$pen_contract_name_1 = $_POST['pen_guardian_nm'];//대리인&수급자이름
	$pen_contract_relation_1 = $contract_sign_relation_con;//대리인&수급자관계
	$dc_date_2 = substr($_POST["confirm_date"],0,4)." 년    ".substr($_POST["confirm_date"],5,2)." 월    ".substr($_POST["confirm_date"],8,2)." 일";//확인일시
	$dc_rectype_1 = "[ ".(($_POST["penRecTypeCd"]=="02")?"V":"  ")." ] 방문   [ ".(($_POST["penRecTypeCd"]=="02")?"  ":"V")." ] 유선";//확인방법

	$str = file_get_contents($_SERVER['DOCUMENT_ROOT']."/data/file/member/stamp/".$member["sealFile"]);
	$stamp = base64_encode($str);

	$api_url = 'https://api.modusign.co.kr/documents/request-with-template';
	$type = "POST";
	$data = '{"document":{
					"participantMappings":[
						'.$pen_sign_info.'
					]
					,"requesterInputMappings":[
					{"dataLabel":"pen_name_3","value":"'.$pen_name_3.'"}
					,{"dataLabel":"pen_birthday_1","value":"'.$pen_birthday_1.'"}
					,{"dataLabel":"pen_grade_2","value":"'.$pen_grade_2.'"}
					,{"dataLabel":"pen_ltmnum_2","value":"'.$pen_ltmnum_2.'"}
					,{"dataLabel":"ent_name_3","value":"'.$ent_name_3.'"}
					,{"dataLabel":"ent_entnum_2","value":"'.$ent_entnum_2.'"}
					'.$it_rent_categoryN_2.'
					'.$it_rent_nameN_2.'
					'.$it_rent_codeN_2.'
					'.$it_rent_priceN_2.'
					'.$it_rent_dateN_2.'
					'.$it_rent_sumpriceN_1.'				
					'.$it_rent_price_penN_2.'
					'.$it_rent_price_entN_1.'
					,{"dataLabel":"ent_ConAcc_1","value":"'.$ent_ConAcc_1.'"}
					,{"dataLabel":"ent_ceoname_3","value":"'.$ent_ceoname_3.'"}
					,{"dataLabel":"pen_contract_name_1","value":"'.$pen_contract_name_1.'"}
					,{"dataLabel":"pen_contract_relation_1","value":"'.$pen_contract_relation_1.'"}
					,{"dataLabel":"dc_date_2","value":"'.$dc_date_2.'"}
					,{"dataLabel":"dc_rectype_1","value":"'.$dc_rectype_1.'"}
					,{"dataLabel":"ent_signimage_2","value":{"base64":"'.$stamp.'"}}
					]
					,"metadatas":[{"key":"entId","value":"'.$member['mb_entId'].'"},{"key":"dc_id","value":"rent_'.strtolower($_POST["dc_id1"]).'"}]
					,"title":"'.$title_text.'"
				}
				,"templateId":"'.$temp_doc_id.'"}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//메타데이터 확인
$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
	@umask(0);
	@mkdir($log_dir,0777);
	//@chmod($upload_dir, 0777);
}
$log_file = fopen($log_dir . 'rent_eform_api_log_'.date("Ymd").'.txt', 'a');
$log_txt = "====== 모두싸인 계약서 시작(급여제공기록지) [".$_SERVER["REMOTE_ADDR"]."] ============================================ \r\n";
$log_txt .= "[".date("Y-m-d H:i:s")."]"."\r\n".$data." \r\n";
$log_txt .= stripslashes(json_encode($arrResponse, JSON_UNESCAPED_UNICODE))." \r\n";
$log_txt .= "====== 모두싸인 계약서(급여제공기록지) 끝 ============================================================= \r\n";
fwrite($log_file, $log_txt . "\r\n");
fclose($log_file);

	$url = $arrResponse["file"]["downloadUrl"];
	
	if($url != ""){
		$response2["url"] = $url;
		$response2["d_id"] = $arrResponse["id"];
		$response2["p_id"] = $arrResponse["participants"][0]["id"];
		$sql = "update eform_rent_hist set dc_sign_send_datetime=now(),rh_status='4',doc_id='".$arrResponse["id"]."' where rh_id='".$_POST["dc_id1"]."'";
		sql_query($sql);
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
}elseif($_POST["div"] == "resend_doc"){//계약서 재 전송
	$api_url = 'https://api.modusign.co.kr/documents/'.$_POST["doc_id"].'/forward';
	$type = "POST";
	$data = '{"contacts":['.$mo_nums.']}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);// 계약서 재 전송

	$response2["url"] = $arrResponse;
	if($url != ""){
		$response2["url"] = $url;
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
}elseif($_POST["div"] == "sign_cancel"){//서명 요청 취소
	$api_url = 'https://api.modusign.co.kr/documents/'.$_POST["doc_id"].'/cancel';
	$type = "POST";
	$data = '{"accessibleByParticipant":false,"message":"서명요청 취소"}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//서명요청 취소

	$url = $arrResponse["file"]["downloadUrl"];
	if($url != ""){
		$response2["url"] = $url;
		$sql = "update eform_rent_hist set dc_sign_send_datetime='0000-00-00 00:00:00',rh_status='11',doc_id='' where doc_id='".$_POST["doc_id"]."'";
		sql_query($sql);
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
}elseif($_POST["div"] == "dc_del"){//거절 계약서 초기화 ******** 검토 필요
	$api_url = 'https://api.modusign.co.kr/documents/'.$_POST["doc_id"].'/metadatas';
	$type = "PUT";
	$data = '{"metadatas":[{"key":"dc_id","value":"rent_dc_reset"}]}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//메타데이터 변경

	$url = $arrResponse["metadatas"][0]["key"];
	if($url != ""){
		$response2["url"] = $url;
		$sql = "update eform_rent_hist set rh_status='0' where doc_id='".$_POST["doc_id"]."'";
		sql_query($sql);
		$response2["api_stat"] = "1";
		$response2["url"] = $sql;
	}else{
		$response2["url"] = "url생성실패";
	}
}elseif($_POST["div"] == "sign_resend"){//서명요청 재알림
	$api_url = 'https://api.modusign.co.kr/documents/'.$_POST["doc_id"].'/remind-signing';
	$type = "POST";
	$data = '';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//서명요청 재알림
	$url = $arrResponse["file"]["downloadUrl"];
	if($url != ""){
		$response2["url"] = $url;
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
}

echo json_encode($response2);


?>
