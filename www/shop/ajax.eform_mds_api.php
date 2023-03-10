<?php
//require_once('../vendor/autoload.php');
include_once('./_common.php');
$API_Key64 = base64_encode(G5_MDS_ID.":".G5_MDS_KEY); //API 접속 base64 인코딩 키
//$client = new \GuzzleHttp\Client();

$templateId1_1 = "932d23a0-a37a-11ed-aeef-1bb14ef4354c";//기본 템플릿 6p(15/5)
$templateId1_2 = "ff3f3790-a38e-11ed-a8f1-9fe09be5e9a1";//기본 템플릿 6p(10/5)
$templateId1_3 = "524b22a0-a38f-11ed-a8f1-9fe09be5e9a1";//기본 템플릿 6p(5/5)
$templateId2_1 = "8176f0e0-a38f-11ed-a8f1-9fe09be5e9a1";//기본 템플릿 4p(15/5)
$templateId2_2 = "bb70b510-a38f-11ed-a8f1-9fe09be5e9a1";//기본 템플릿 4p(10/5)
$templateId2_3 = "eef248e0-a38f-11ed-9f87-3f9656f47c97";//기본 템플릿 4p(5/5)

//if($_POST["div"] == ""){
//서명 WebHook 시작
ini_set("allow_url_fopen", true);
header('Content-type: application/json');
	// Webhook Request Body
	$json_string = file_get_contents('php://input');

	// Webhook 메시지 Json parse
	$arrResponse = json_decode($json_string, true);

	// 추가적인 Webhook 메시지 항목은 하단의 [Webhook 메시지 구성] 참조
	if($arrResponse["event"]["type"] != ""){
		/*$response = $client->request('GET', 'https://api.modusign.co.kr/documents/'.$arrResponse["document"]["id"], [
		  'headers' => [
			'accept' => 'application/json',
			'authorization' => 'Basic '.$API_Key64,
		  ],
		]);
		$arrResponse2 = json_decode($response->getBody(),true);*/
		$api_url = 'https://api.modusign.co.kr/documents/'.$arrResponse["document"]["id"];
		$type = "GET";
		$data = "";
		$arrResponse2 = get_modusign($API_Key64,$api_url,$type,$data);
		$dc_id2 = strtoupper($arrResponse2["metadatas"][0]["value"]);
		$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
		//$log_dir = "/home/root...등의 절대경로 ";
	    $log_txt = "\r\n";
		$log_txt .= '(' . date("Y-m-d H:i:s") . ')' .$arrResponse["event"]["type"]. "\r\n";
		if($arrResponse["event"]["type"] == "document_all_signed"){// 서명완료
			$sql = "update `eform_document` set dc_sign_datetime=now(),dc_status='3' WHERE dc_id=UNHEX('".$dc_id2."')";
			$log_txt .= "-- 계약서 ".$dc_id2." 서명 완료\r\n".$sql;
			sql_query($sql);
			//if($is_simple_efrom) {
			  $uuid = $dc_id2; 
			  $dc_status = '3';
			  $eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
			  
			  // 간편 계약서 작성 시 바코드 입력한 상품 '재고소진' 상태로 재고 등록
			  $sql = "
				SELECT
				  i.*,
				  x.it_id as id
				FROM
				  eform_document_item i
				LEFT JOIN
				  g5_shop_item x ON x.it_id = (
					select it_id
					from g5_shop_item
					where
					  ProdPayCode = i.it_code and
					  (
						( i.gubun = '00' and ca_id like '10%' ) or
						( i.gubun = '01' and ca_id like '20%' )
					  )
					limit 1
				  )
				WHERE
				  dc_id = UNHEX('$uuid')
				ORDER BY
				  i.it_id ASC
			  ";
			  $result = sql_query($sql);

			  $stock_insert = [];
			  $stock_update = [];
			  $rental_data_table = [];
			  while($row = sql_fetch_array($result)) {
				if(strlen($row['it_barcode']) == 12) { // 바코드 12자리 정상적으로 입력한 경우
				  if($row['gubun'] == '00') {
					// 판매

					// 재고에 있는지 조회
					$stock_result = get_stock($row['id'], $row['it_barcode']);
					if($stock_result) {
					  // 재고에 있으면
					  $stock = $stock_result[0];
					  // 재고가 판매완료 상태가 아니면 판매완료로 업데이트
					  $stock['stateCd'] != '02';
					  $stock_update[] = array(
						'stoId' => $stock['stoId'],
						'prodBarNum' => $row['it_barcode'],
						'stateCd' => '02'
					  );
					} else {
					  // 재고에 없으면
					  // 보유재고로 판매완료로 등록
					  $stock_insert[] = array(
						'prodId' => $row['id'],
						'prodBarNum' => $row['it_barcode'],
						'stateCd' => '02'
					  );
					}
				  } else {
					// 대여

					$str_date = substr($row['it_date'], 0, 10);
					$end_date = substr($row['it_date'], 11, 10);

					// rental_data_table에 입력해둠 (나중에 재고 업데이트/등록 후 대여로그 작성하기 위해)
					$rental_data_table["{$row['id']}-{$row['it_barcode']}"] = array(
					  'strdate' => $str_date,
					  'enddate' => $end_date
					);

					// 재고에 있는지 조회
					$stock_result = get_stock($row['id'], $row['it_barcode']);
					if($stock_result) {
					  // 재고가 있으면
					  $stock = $stock_result[0];
					  // 재고 대여완료로 업데이트
					  $stock_update[] = array(
						'stoId' => $stock['stoId'],
						'prodId' => $row['id'],
						'prodBarNum' => $row['it_barcode'],
						'stateCd' => '02'
					  );
					} else {
					  // 재고에 없으면
					  // 보유재고에 등록
					  $stock_insert[] = array(
						'prodId' => $row['id'],
						'prodBarNum' => $row['it_barcode'],
						'stateCd' => '02',
						'initialContractDate' => date('Y-m-d H:i:s', strtotime($str_date))
					  );
					}
				  }
				}
			  }

			  // 재고 insert
			  if($stock_insert) {
				$insert_result = api_post_call(EROUMCARE_API_STOCK_INSERT, array(
				  'usrId' => $member["mb_id"],
				  'entId' => $member["mb_entId"],
				  'prods' => $stock_insert
				));

				// 대여로그 작성
				foreach($insert_result['data'] as $row) {
				  $rental_data = $rental_data_table["{$row['prodId']}-{$row['prodBarNum']}"];
				  if(!$rental_data) continue;

				  $rental_log_id = "rental_log".round(microtime(true)).rand();
				  $dis_total_date = G5_TIME_YMDHIS;

				  sql_query("
					INSERT INTO
					  g5_rental_log
					SET
					  rental_log_Id = '{$rental_log_id}',
					  stoId = '{$row['stoId']}',
					  ordId = '',
					  strdate = '{$rental_data['strdate']}',
					  enddate = '{$rental_data['enddate']}',
					  dis_total_date = '{$dis_total_date}',
					  ren_person = '{$eform['penNm']}',
					  rental_log_division = '2'
				  ");
				}
			  }

			  // 재고 update
			  if($stock_update) {
				$update_result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
				  'usrId' => $member["mb_id"],
				  'entId' => $member["mb_entId"],
				  'prods' => $stock_update
				));

				// 대여로그 작성
				foreach($stock_update as $row) {
				  $rental_data = $rental_data_table["{$row['prodId']}-{$row['prodBarNum']}"];
				  if(!$rental_data) continue;

				  // 이미 같은 기간동안의 대여로그가 작성되어있는지 검색
				  $check_result = sql_fetch("
					SELECT
					  rental_log_Id
					FROM
					  g5_rental_log
					WHERE
					  stoId = '{$row['stoId']}' and
					  strdate = '{$rental_data['strdate']}' and
					  enddate = '{$rental_data['enddate']}' and
					  rental_log_division = '2'
				  ");

				  // 이미 작성된 로그면 건너뜀
				  if($check_result['rental_log_Id']) continue;

				  $rental_log_id = "rental_log".round(microtime(true)).rand();
				  $dis_total_date = G5_TIME_YMDHIS;

				  sql_query("
					INSERT INTO
					  g5_rental_log
					SET
					  rental_log_Id = '{$rental_log_id}',
					  stoId = '{$row['stoId']}',
					  ordId = '',
					  strdate = '{$rental_data['strdate']}',
					  enddate = '{$rental_data['enddate']}',
					  dis_total_date = '{$dis_total_date}',
					  ren_person = '{$eform['penNm']}',
					  rental_log_division = '2'
				  ");
				}
			  }
			//}
		}elseif($arrResponse["event"]["type"] == "document_rejected"){//서명거절 document_rejected
			$sql = "update `eform_document` set dc_sign_datetime=now(),dc_status='5' WHERE dc_id=UNHEX('".$dc_id2."')";
			$log_txt .= "-- 계약서 ".$dc_id2." 서명 거절\r\n".$sql;
			sql_query($sql);
		}elseif($arrResponse["event"]["type"] == "document_POST_canceled"){//서명요청취소
			$log_txt .= "-- 계약서 ".$dc_id2." 서명 요청취소";
		}elseif($arrResponse["event"]["type"] == "document_signing_canceled"){//서명취소
			$log_txt .= "-- 계약서 ".$dc_id2." 서명 취소";
		}
	
		$log_file = fopen($log_dir . 'log.txt', 'a');
		fwrite($log_file, $log_txt . "\r\n\r\n");
		fclose($log_file);
	}
//서명 완료 WebHook 끝
//}
$response2 = array();
$response2["api_stat"] = "0";

if($_REQUEST["signed"] == "ok"){?>
	<script>
	setTimeout(completed(), 5000);	
	function completed(){
		opener.location.reload();
		window.close();
	}
	</script>
<?php }elseif($_POST["div"] == "sign_stat" || $_POST["div"] == "view_doc" || $_POST["div"] == "rejection_view"){// 서명 상황, 계약서 보기,거절사유보기
	$api_url = 'https://api.modusign.co.kr/documents?offset=0&limit=1&metadatas=%7B%22dc_id%22%3A%22'.strtolower($_POST["dc_id"]).'%22%7D';
	$type = "GET";
	$data = "";
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);
	if($_POST["div"] == "sign_stat"){//서명 상황
		$url = $arrResponse["documents"][0]["file"]["downloadUrl"];
		$participants_count = count($arrResponse["documents"][0]["participants"]);
		$gubun1 = $gubun2 = $gubun3 = "-";
		$sign_date1 = $sign_date2 = $sign_date3 = "-";
		$stat1 = $stat2 = $stat3 = "대상아님";
		for($i=0;$i<$participants_count;$i++){
			if($arrResponse["documents"][0]["participants"][$i]["name"] == "수급자"){
				$gubun1 = ($arrResponse["documents"][0]["participants"][$i]["signingMethod"]["type"] == "SECURE_LINK")?"웹페이지":"카카오톡";			
				$sign_date1 = ($arrResponse["documents"][0]["signings"][$i]["signedAt"] != "")? date("Y-m-d H:i:s",strtotime($arrResponse["documents"][0]["signings"][$i]["signedAt"])):"-"; 
				$stat1 = ($sign_date1 == "-")?"진행중":"완료";
				$part_id1 = $arrResponse["documents"][0]["participants"][$i]["id"];
			}
			if($arrResponse["documents"][0]["participants"][$i]["name"] == "대리인"){
				$gubun2 = ($arrResponse["documents"][0]["participants"][$i]["signingMethod"]["type"] == "SECURE_LINK")?"웹페이지":"카카오톡";			
				$sign_date2 = ($arrResponse["documents"][0]["signings"][$i]["signedAt"] != "")? date("Y-m-d H:i:s",strtotime($arrResponse["documents"][0]["signings"][$i]["signedAt"])):"-"; 
				$stat2 = ($sign_date2 == "-")?"진행중":"완료";
				$part_id2 = $arrResponse["documents"][0]["participants"][$i]["id"];
			}
			if($arrResponse["documents"][0]["participants"][$i]["name"] == "신청자"){
				$gubun3 = ($arrResponse["documents"][0]["participants"][$i]["signingMethod"]["type"] == "SECURE_LINK")?"웹페이지":"카카오톡";			
				$sign_date3 = ($arrResponse["documents"][0]["signings"][$i]["signedAt"] != "")? date("Y-m-d H:i:s",strtotime($arrResponse["documents"][0]["signings"][$i]["signedAt"])):"-"; 
				$stat3 = ($sign_date2 == "-")?"진행중":"완료";
				$part_id3 = $arrResponse["documents"][0]["participants"][$i]["id"];
			}
		}
		$sql = "SELECT * FROM `eform_document` WHERE dc_id=UNHEX('".$_POST["dc_id"]."')";
		$row=sql_fetch($sql);
		
		if($url != ""){
			$response2["url"] = $url;
			$response2["contract_sign_name"] = $row['contract_sign_name'];
			$response2["contract_sign_type"] = $row['contract_sign_type'];
			$response2["applicantRelation"] = $row['applicantRelation'];
			$response2["gubun1"] = $gubun1;
			$response2["gubun2"] = $gubun2;
			$response2["gubun3"] = $gubun3;
			$response2["stat1"] = $stat1;
			$response2["stat2"] = $stat2;
			$response2["stat3"] = $stat3;
			$response2["sign_date1"] = $sign_date1;
			$response2["sign_date2"] = $sign_date2;
			$response2["sign_date3"] = $sign_date3;
			$response2["part_id1"] = $part_id1;
			$response2["part_id2"] = $part_id2;
			$response2["part_id3"] = $part_id3;
			$response2["doc_id"] = $arrResponse["documents"][0]["id"];
			$response2["api_stat"] = "1";
		}else{
			$response2["url"] = "url생성실패";
		}
	}elseif($_POST["div"] == "view_doc"){//계약서 보기
		$url = ($_POST["gubun"] == 1)?$arrResponse["documents"][0]["file"]["downloadUrl"]:$arrResponse["documents"][0]["auditTrail"]["downloadUrl"];
		if($url != ""){
			$response2["url"] = $url;
			$response2["api_stat"] = "1";
		}else{
			$response2["url"] = "url생성실패";
		}
	}elseif($_POST["div"] == "rejection_view"){// 거절사유 보기
		$url = $arrResponse["documents"][0]["file"]["downloadUrl"];
		$participants_count = count($arrResponse["documents"][0]["participants"]);
		
		for($i=0;$i<$participants_count;$i++){
			if($arrResponse["documents"][0]["participants"][$i]["id"] == $arrResponse["documents"][0]["abort"]["participantId"]){
				$rejection_member = $arrResponse["documents"][0]["participants"][$i]["name"];
			}
		}
		$rejection_date = date("Y-m-d H:i:s",strtotime($arrResponse["documents"][0]["abort"]["abortedAt"]));
		$rejection_msg = $arrResponse["documents"][0]["abort"]["message"];
				
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
	$select = array();
	$where = array();

	$uuid = isset($_POST["dc_id1"]) ? get_search_string($_POST["dc_id1"]) : '';

	//$select[] = ' m.mb_id ';
	$select[] = ' I.it_name ';
	$select[] = ' COUNT(E.dc_id) as it_count ';
	$select[] = ' I2.t_price ';
	$sql_join = ' LEFT JOIN `eform_document_item` I ON E.dc_id = I.dc_id 
	LEFT JOIN (SELECT dc_id, it_name,it_qty,it_price, SUM(it_qty*it_price) AS t_price FROM `eform_document_item` GROUP BY dc_id) I2 ON E.dc_id = I2.dc_id ';

	// select 배열 처리
	$select[] = "E.*";
	$sql_select = "HEX(E.dc_id) as uuid, ".implode(', ', $select);

	// where 배열 처리
	$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
	if($where) {
	  $sql_where .= ' AND '.implode(' AND ', $where);
	}

	$sql_from = " FROM `eform_document` E";
	$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . " and HEX(E.dc_id) = '".$uuid."'");
	$row=sql_fetch_array($result);
	//excluded : 해당 서명자 제외 유무 표시 (false-제외안함,true-제외함)
	//signingDuration : 서명 유효 기간 (20160 - 14일)유효기간 수정 기능 있음 
	//role : 서명자 역할 지정 name : 서명자 이름
	//requesterMessage : 서명자에게 보낼 메세지
	//서명자가 여러명일 때 참여자 서명수단의 value 값이 다 달라야함
	//requesterInputMappings 은 값이 있을때만 맞는 값만 전송해야함 없는 dataLabel 에 데이터를 전송 하면 에러남
	//수급자 발송정보
	$pen_sign_info = ($_POST["pen_sign"] == "1")? '{"excluded":false,"signingMethod":{"type":"'.$_POST["pen_send"].'","value":"'.$_POST["pen_send_tel"].'"},"signingDuration":20160,"locale":"ko","role":"수급자","name":"수급자","requesterMessage":"['.$row["entNm"].'] 에서 복지용구 공급계약을 요청하였습니다."}': '{"excluded":true,"signingMethod":{"type":"SECURE_LINK","value":"01010000000"},"signingDuration":20160,"locale":"ko","role":"수급자","name":"수급자"}' ;
	//대리인 발송정보
	$contract_sign_info = ($_POST["contract_sign"] == "1")? '{"excluded":false,"signingMethod":{"type":"'.$_POST["contract_send"].'","value":"'.$_POST["contract_send_tel"].'"},"signingDuration":20160,"locale":"ko","role":"대리인","name":"대리인","requesterMessage":"['.$row["entNm"].'] 에서 복지용구 공급계약을 요청하였습니다."}': '{"excluded":true,"signingMethod":{"type":"SECURE_LINK","value":"01020000000"},"signingDuration":20160,"locale":"ko","role":"대리인","name":"대리인"}' ;
	//신청자 발송정보
	$applicant_sign_info = ($_POST["applicant_sign"] == "1")? '{"excluded":false,"signingMethod":{"type":"'.$_POST["applicant_send"].'","value":"'.$_POST["applicant_send_tel"].'"},"signingDuration":20160,"locale":"ko","role":"신청자","name":"신청자","requesterMessage":"['.$row["entNm"].'] 에서 복지용구 공급계약을 요청하였습니다."}': '{"excluded":true,"signingMethod":{"type":"SECURE_LINK","value":"01030000000"},"signingDuration":20160,"locale":"ko","role":"신청자","name":"신청자"}' ;

	$str = file_get_contents($_SERVER['DOCUMENT_ROOT'].$row["dc_signUrl"]);
	$stamp = base64_encode($str);
	$penTypeCd = $row["penTypeCd"];
	//수급자 기본 정보
	$pen_name_1 = $pen_name_2 = $pen_name_3 = $pen_name_4 = $pen_name_5 = $pen_name_6 = $pen_name_7 = $pen_name_8 = $row["penNm"];//수급자 이름
	$pen_ltmnum_1 = $pen_ltmnum_2 = $pen_ltmnum_3 = $pen_ltmnum_4 = $pen_ltmnum_5 = $row["penLtmNum"];//수급자장기요양인정번호
	$pen_grade_1 = $pen_grade_2 = $pen_grade_3 = $pen_grade_4 = $row["penRecGraNm"];//수급자인정등급명
	$pen_type_1 = $pen_type_2 = $pen_type_3 = $pen_type_4 = $pen_type_5 = $row["penTypeNm"];//수급자본인부담금율
	$pen_birthday_1 =  $row["penBirth"];//수급자생일
	$pen_Jumin_1 = $pen_Jumin_2 = str_replace(".","",$row["penBirth"]);//수급자생일
	$pen_tel_1 = $row["penConNum"];//수급자전화번호
	$pen_addr_1 = $pen_addr_2 = ($row["penAddr"] != "")?"(".$row["penZip"].")".$row["penAddr"].' '.$row["penAddrDtl"]:"";//수급자주소
	$ent_name_1 = $ent_name_2 = $ent_name_3 = $row["entNm"];//사업소이름
	$ent_entnum_1 = $ent_entnum_2 = $row["entNum"];//사업소기관번호
	$ent_ceoname_1 = $ent_ceoname_2 = $ent_ceoname_3 = $row["entCeoNm"];//사업소대표이름
	$contract_name_1 = $contract_name_2 = $contract_name_3 = $contract_name_4 = $row["contract_sign_name"];//대리자명 $contract_name_1:대리자가 있을 경우만
	$contract_tel_1 = $contract_tel_2 = $row["contract_tel"];//대리자전화번호 $contract_tel_1:대리자가 있을 경우만
	$pen_contract_name_1 = "";//대리인&수급자이름
	$pen_contract_relation_1 = "";//대리인&수급자관계
	$dc_date_1 = $dc_date_2 = $dc_date_3 = substr($row["do_date"],0,10);
	$dc_rectype_1 = ($row["penRecTypeCd"] == 01)?"유선":"방문";

	if($row["contract_sign_type"] == "1"){//대리자가 있을 경우
		$pen_contract_name_1 = $contract_name_1;
		if($row["contract_sign_relation"] == 1){
			$pen_contract_relation_1 = "[   ]본인 [ ✓ ]가족 [   ]친족 [   ]기타 (   )";
			$contract_sign_relation = "가족";
		}elseif($row["contract_sign_relation"] == 2){
			$pen_contract_relation_1 = "[   ]본인 [   ]가족 [ ✓ ]친족 [   ]기타 (   )";
			$contract_sign_relation = "친족";
		}elseif($row["contract_sign_relation"] == 3){
			$pen_contract_relation_1 = "[   ]본인 [   ]가족 [   ]친족 [ ✓ ]기타 (   )";
			$contract_sign_relation = "기타";
		}
		$contract_relation_1 = $contract_relation_2 = $contract_sign_relation;//대리자 관계 $contract_relation_1:대리자가 있을 경우만
		$contract_addr_1 =  $row["contract_addr"];
	}else{//수급자
		$pen_contract_name_1 = $pen_name_1;
		$pen_contract_relation_1 = "[ ✓ ]본인 [   ]가족 [   ]친족 [   ]기타 (   )";
		$pen_addr_1 = $pen_ltmnum_3 = $pen_name_5 = $contract_addr_1 = $contract_tel_2 = $contract_relation_1 = $contract_relation_2 = "";
	}
	if($row["applicantRelation"] != "0" && $row["applicantRelation"] != ""){//신청자가 본인이 아닐경우
		if($row["applicantRelation"] == 4){//신청인이 대리인 일경우
			$app_relation_1 = $contract_sign_relation; 
			$app_birthday_1 = "";
			$app_addr_1 = $row["contract_addr"];
			$app_tel_1 = $contract_tel_1;
			$app_name_1 = $app_name_2 = $app_name_3 =  $row["contract_sign_name"];
		}else{
			if($row["applicantRelation"] == 1){
				$app_relation_1 = "가족"; 
			}elseif($row["applicantRelation"] == 2){
				$app_relation_1 = "친족"; 
			}elseif($row["applicantRelation"] == 3){
				$app_relation_1 = "기타"; 
			}elseif($row["applicantRelation"] == 5){
				$app_relation_1 = ""; 
			}
			$app_birthday_1 = $row["applicantBirth"];
			$app_addr_1 = $row["applicantAddr"];
			$app_tel_1 = $row["applicantTel"];
			$app_name_1 = $app_name_2 = $app_name_3 =  $row["applicantNm"];
		}
	}else{
		$app_relation_1 = "본인";
		$app_birthday_1 = $row["penBirth"];
		$app_addr_1 = $pen_addr_2;
		$app_tel_1 = $row["penConNum"];
		$app_name_1 = $app_name_2 = $app_name_3 = $pen_name_1;
	}

	$it_purchase_categoryN_1 = "";
	$it_purchase_nameN_1 = "";
	$it_purchase_codeN_1 = "";
	$it_purchase_barcodeN_1 = "";
	$it_purchase_countN_1 = "";
	$it_purchase_dateN_1 = "";
	$it_purchase_priceN_1 = "";
	$it_purchase_price_penN_1 = "";
	$it_purchase_price_sum_1 = "";
	$it_purchase_price_pen_sum_1 = "";
	$it_rent_category1_1 = "";
	$it_rent_nameN_1 = "";
	$it_rent_codeN_1 = "";
	$it_rent_barcodeN_1 = "";
	$it_rent_countN_1 = "";
	$it_rent_dateN_1 = "";
	$it_rent_priceN_1 = "";
	$it_rent_price_penN_1 = "";
	$it_rent_price_sum_1 = "";
	$it_rent_price_pen_sum_1 = "";
	$sd = "";
	$sad = "";
	$ent_ConAcc_1 = "";
	$count_total = 0;
	$count_sale = 0;
	$count_rant = 0;
	$total_price["sale"] = 0;
	$total_price_pen["sale"] = 0;
	$total_price_ent["sale"] = 0;
	$total_price["rant"] = 0;
	$total_price_pen["rant"] = 0;
	$total_price_ent["rant"] = 0;
	$total_price["all"] = 0;
	$total_price_pen["all"] = 0;
	$total_price_ent["all"] = 0;

	$sql = "SELECT * FROM `eform_document_item` WHERE HEX(dc_id)='".$uuid."'";
	$result = sql_query($sql);

	while($row=sql_fetch_array($result)){
		if($row["gubun"] == "00"){
			$count_sale++;
			$ca_name["sale"][$count_sale] = $row["ca_name"];
			$it_name["sale"][$count_sale] = $row["it_name"];
			$it_code["sale"][$count_sale] = $row["it_code"];
			$it_barcode["sale"][$count_sale] = $row["it_barcode"];
			$it_date["sale"][$count_sale] = $row["it_date"];
			$it_price["sale"][$count_sale] = $row["it_price"];
			$it_price_pen["sale"][$count_sale] = $row["it_price_pen"];
			$it_price_ent["sale"][$count_sale] = $row["it_price_ent"];
			$total_price["sale"] += $it_price["sale"][$count_sale];
			$total_price_pen["sale"] += $it_price_pen["sale"][$count_sale];
			$total_price_ent["sale"] += $it_price_ent["sale"][$count_sale];
		}else{
			$count_rant++;
			$ca_name["rant"][$count_rant] = $row["ca_name"];
			$it_name["rant"][$count_rant] = $row["it_name"];
			$it_code["rant"][$count_rant] = $row["it_code"];
			$it_barcode["rant"][$count_rant] = $row["it_barcode"];
			$it_date["rant"][$count_rant] = $row["it_date"];
			$it_price["rant"][$count_rant] = $row["it_price"];
			$it_price_pen["rant"][$count_rant] = $row["it_price_pen"];
			$it_price_ent["rant"][$count_rant] = $row["it_price_ent"];
			$total_price["rant"] += $it_price["rant"][$count_rant];
			$total_price_pen["rant"] += $it_price_pen["rant"][$count_rant];
			$total_price_ent["rant"] += $it_price_ent["rant"][$count_rant];
		}
			$count_total++;
			$ca_name["all"][$count_total] = $row["ca_name"];
			$it_name["all"][$count_total] = $row["it_name"];
			$it_code["all"][$count_total] = $row["it_code"];
			$it_barcode["all"][$count_total] = $row["it_barcode"];
			$sale_gubun["all"][$count_total] = ($row["gubun"] == "00")?"V(1)":"";
			$rant_gubun["all"][$count_total] = ($row["gubun"] == "00")?"":"V(1)";
			$it_date["all"][$count_total] = $row["it_date"];
			$it_price["all"][$count_total] = $row["it_price"];
			$it_price_pen["all"][$count_total] = $row["it_price_pen"];
			$it_price_ent["all"][$count_total] = $row["it_price_ent"];
			$total_price["all"] += $it_price["all"][$count_total];
			$total_price_pen["all"] += $it_price_pen["all"][$count_total];
			$total_price_ent["all"] += $it_price_ent["all"][$count_total];

	}


	for($i=1;$i<($count_sale+1);$i++){//판매물품-최대 15개
		$it_purchase_categoryN_1	.= ',{"dataLabel":"it_purchase_category'.$i.'_1","value":"'.$ca_name["sale"][$i].'"}'; //구매품목명
		$it_purchase_nameN_1		.=',{"dataLabel":"it_purchase_name'.$i.'_1","value":"'.$it_name["sale"][$i].'"}'; //구매이름
		$it_purchase_codeN_1		.=',{"dataLabel":"it_purchase_code'.$i.'_1","value":"'.$it_code["sale"][$i].'"}'; //구매품목코드
		$it_purchase_barcodeN_1		.=',{"dataLabel":"it_purchase_barcode'.$i.'_1","value":"'.$it_barcode["sale"][$i].'"}'; //구매품목바코드
		$it_purchase_countN_1		.=',{"dataLabel":"it_purchase_count'.$i.'_1","value":"1"}'; //구매품목개수
		$it_purchase_dateN_1		.=',{"dataLabel":"it_purchase_date'.$i.'_1","value":"'.$it_date["sale"][$i].'"}'; //구매품목계약일
		$it_purchase_priceN_1		.=',{"dataLabel":"it_purchase_price'.$i.'_1","value":"'.number_format($it_price["sale"][$i]).'"}'; //구매품목급여가
		$it_purchase_price_penN_1	.=',{"dataLabel":"it_purchase_price_pen'.$i.'_1","value":"'.number_format($it_price_pen["sale"][$i]).'"}'; //구매품목본인부담금
		
		$it_purchase_categoryN_2	.= ',{"dataLabel":"it_purchase_category'.$i.'_2","value":"'.$ca_name["sale"][$i].'"}'; //구매품목명
		$it_purchase_nameN_2		.=',{"dataLabel":"it_purchase_name'.$i.'_2","value":"'.$it_name["sale"][$i].'"}'; //구매이름
		$it_purchase_codeN_2		.=',{"dataLabel":"it_purchase_code'.$i.'_2","value":"'.$it_code["sale"][$i].' - '.$it_barcode["sale"][$i].'"}'; //구매품목코드
		$it_purchase_priceN_2		.=',{"dataLabel":"it_purchase_price'.$i.'_2","value":"'.number_format($it_price["sale"][$i]).'"}'; //구매품목급여가
		$it_purchase_dateN_2		.=',{"dataLabel":"it_purchase_date'.$i.'_2","value":"'.$it_date["sale"][$i].'"}'; //구매품목계약일
		$it_purchase_sumpriceN_1	.=',{"dataLabel":"it_purchase_sumprice'.$i.'_1","value":"'.number_format($it_price["sale"][$i]).'"}'; //아이템구매_총액1
		$it_purchase_price_penN_2	.=',{"dataLabel":"it_purchase_price_pen'.$i.'_2","value":"'.number_format($it_price_pen["sale"][$i]).'"}'; //구매품목본인부담금
		$it_purchase_price_entN_1	.=',{"dataLabel":"it_purchase_price_ent'.$i.'_1","value":"'.number_format($it_price_ent["sale"][$i]).'"}'; //아이템구매_공단부담금1
	}
	$it_purchase_price_sum_1 = ',{"dataLabel":"it_purchase_price_sum_1","value":"'.number_format($total_price["sale"]).'"}';//아이템구매_품목급여가합계
	$it_purchase_price_pen_sum_1 = ',{"dataLabel":"it_purchase_price_pen_sum_1","value":"'.number_format($total_price_pen["sale"]).'"}';//아이템구매_품목본인부담금합계


	for($i=1;$i<($count_rant+1);$i++){//대여물품-최대 5개
		$it_rent_categoryN_1	.= ',{"dataLabel":"it_rent_category'.$i.'_1","value":"'.$ca_name["rant"][$i].'"}'; //아이템대여_품목명
		$it_rent_nameN_1		.=',{"dataLabel":"it_rent_name'.$i.'_1","value":"'.$it_name["rant"][$i].'"}'; //대여이름
		$it_rent_codeN_1		.=',{"dataLabel":"it_rent_code'.$i.'_1","value":"'.$it_code["rant"][$i].'"}'; //대여품목코드
		$it_rent_barcodeN_1		.=',{"dataLabel":"it_rent_barcode'.$i.'_1","value":"'.$it_barcode["rant"][$i].'"}'; //대여품목바코드
		$it_rent_dateN_1		.=',{"dataLabel":"it_rent_date'.$i.'_1","value":"'.$it_date["rant"][$i].'"}'; //대여품목계약일
		$it_rent_priceN_1		.=',{"dataLabel":"it_rent_price'.$i.'_1","value":"'.number_format($it_price["rant"][$i]).'"}'; //대여품목급여가
		$it_rent_price_penN_1	.=',{"dataLabel":"it_rent_price_pen'.$i.'_1","value":"'.number_format($it_price_pen["rant"][$i]).'"}'; //대여품목본인부담금

		$it_rent_categoryN_2	.= ',{"dataLabel":"it_rent_category'.$i.'_2","value":"'.$ca_name["rant"][$i].'"}'; //아이템대여_품목명
		$it_rent_nameN_2		.=',{"dataLabel":"it_rent_name'.$i.'_2","value":"'.$it_name["rant"][$i].'"}'; //대여이름
		$it_rent_codeN_2		.=',{"dataLabel":"it_rent_code'.$i.'_2","value":"'.$it_code["rant"][$i].' - '.$it_barcode["rant"][$i].'"}'; //대여품목코드
		$it_rent_priceN_2		.=',{"dataLabel":"it_rent_price'.$i.'_2","value":"'.number_format($it_price["rant"][$i]).'"}'; //대여품목급여가
		$it_rent_dateN_2		.=',{"dataLabel":"it_rent_date'.$i.'_2","value":"'.$it_date["rant"][$i].'"}'; //대여품목계약일
		$it_rent_sumpriceN_1	.=',{"dataLabel":"it_rent_sumprice'.$i.'_1","value":"'.number_format($it_price["rant"][$i]).'"}'; //대여품목_총액1
		$it_rent_price_penN_2	.=',{"dataLabel":"it_rent_price_pen'.$i.'_2","value":"'.number_format($it_price_pen["rant"][$i]).'"}'; //대여품목본인부담금
		$it_rent_price_entN_1	.=',{"dataLabel":"it_rent_price_ent'.$i.'_1","value":"'.number_format($it_price_ent["rant"][$i]).'"}'; //대여품목_공단부담금1
	}
	$it_rent_price_sum_1 = ',{"dataLabel":"it_rent_price_sum_1","value":"'.number_format($total_price["rant"]).'"}';//아이템대여_품목급여가합계
	$it_rent_price_pen_sum_1 = ',{"dataLabel":"it_rent_price_pen_sum_1","value":"'.number_format($total_price_pen["rant"]).'"}';//아이템대여_품목본인부담금합계

	$sd = ',{"dataLabel":"sd","value":"'.number_format($total_price["all"]).'"}';//아이템총급여가합계 
	$sad = ',{"dataLabel":"sad","value":"'.number_format($total_price_pen["all"]).'"}';//아이템총본인부담급합계 
	$dc_sumprice_sum_1 = number_format($total_price["all"]);// 

	for($i=1;$i<($count_total+1);$i++){//전체물품-최대 20개
		$it_categoryN_1	.= ',{"dataLabel":"it_category'.$i.'_1","value":"'.$ca_name["all"][$i].'"}'; //아이템품목명1
		$it_codeN_1		.=',{"dataLabel":"it_code'.$i.'_1","value":"'.$it_code["all"][$i].'"}'; //아이템제품코드1
		$it_purchaseynN_1		.=',{"dataLabel":"it_purchaseyn'.$i.'_1","value":"'.$sale_gubun["all"][$i].'"}'; //아이템구매여부1
		$it_rentynN_1		.=',{"dataLabel":"it_rentyn'.$i.'_1","value":"'.$rant_gubun["all"][$i].'"}'; //아이템대여여부1
		$it_dateN_1		.=',{"dataLabel":"it_date'.$i.'_1","value":"'.$it_date["all"][$i].'"}'; //아이템계약일1
		$it_sumprice1_1		.=',{"dataLabel":"it_sumprice'.$i.'_1","value":"'.number_format($it_price["all"][$i]).'"}'; //아이템총액1
		$ent_nameN_1	.=',{"dataLabel":"ent_name'.$i.'_1","value":"'.$ent_name_1.'"}'; //사업소이름1
		$ent_entnumN_1	.=',{"dataLabel":"ent_entnum'.$i.'_1","value":"'.$ent_entnum_1.'"}'; //사업소번호1
	}
	if($penTypeCd == "03" || $penTypeCd == "04" ){//의료6%,기초0%
		$applicant_sign_info2 = ','.$applicant_sign_info;
		if($count_sale < 6){
			$temp_doc_id = $templateId1_3;//6p(5/5)
		}elseif($count_sale < 11){
			$temp_doc_id = $templateId1_2;//6p(10/5)
		}else{
			$temp_doc_id = $templateId1_1;//6p(15/5)
		}
		$gicho_con = ',{"dataLabel":"app_name_1","value":"'.$app_name_1.'"}
					,{"dataLabel":"app_relation_1","value":"'.$app_relation_1.'"}
					,{"dataLabel":"app_birthday_1","value":"'.$app_birthday_1.'"}
					,{"dataLabel":"app_addr_1","value":"'.$app_addr_1.'"}
					,{"dataLabel":"app_tel_1","value":"'.$app_tel_1.'"}
					,{"dataLabel":"pen_name_6","value":"'.$pen_name_6.'"}
					,{"dataLabel":"pen_Jumin_1","value":"'.$pen_Jumin_1.'-"}
					,{"dataLabel":"pen_grade_3","value":"'.$pen_grade_3.'"}
					,{"dataLabel":"pen_ltmnum_4","value":"'.$pen_ltmnum_4.'"}
					,{"dataLabel":"pen_addr_2","value":"'.$pen_addr_2.'"}
					,{"dataLabel":"pen_tel_1","value":"'.$pen_tel_1.'"}
					,{"dataLabel":"app_app_date_1","value":"'.substr($row["applicantDate"],0,10).'"}
					,{"dataLabel":"app_name_2","value":"'.$app_name_2.'"}
					,{"dataLabel":"pen_name_7","value":"'.$pen_contract_name_1.'"}
					,{"dataLabel":"app_name_3","value":"'.$app_name_3.'"}
					,{"dataLabel":"pen_name_8","value":"'.$pen_name_8.'"}
					,{"dataLabel":"pen_Jumin_2","value":"'.$pen_Jumin_2.'-"}
					,{"dataLabel":"pen_grade_4","value":"'.$pen_grade_4.'"}
					,{"dataLabel":"pen_ltmnum_5","value":"'.$pen_ltmnum_5.'"}
					'.$it_categoryN_1.'
					'.$it_codeN_1.'
					'.$it_purchaseynN_1.'
					'.$it_rentynN_1.'
					'.$it_dateN_1.'
					'.$it_sumprice1_1.'
					'.$ent_nameN_1.'
					'.$ent_entnumN_1.'
					,{"dataLabel":"dc_sumprice_sum_1","value":"'.$dc_sumprice_sum_1.'"}';
	}else{
		$applicant_sign_info2 = '';
		if($count_sale < 6){
			$temp_doc_id = $templateId2_3;//4p(5/5)
		}elseif($count_sale < 11){
			$temp_doc_id = $templateId2_2;//4p(10/5)
		}else{
			$temp_doc_id = $templateId2_1;//4p(15/5)
		}
		$gicho_con = "";
	}
	$api_url = 'https://api.modusign.co.kr/documents/request-with-template';
	$type = "POST";
	$data = '{"document":{
					"participantMappings":[
						'.$pen_sign_info.'
						,'.$contract_sign_info.'
						'.$applicant_sign_info2.'
					]
					,"requesterInputMappings":[
					{"dataLabel":"pen_name_1","value":"'.$pen_name_1.'"}
					,{"dataLabel":"pen_ltmnum_1","value":"'.$pen_ltmnum_1.'"}
					,{"dataLabel":"pen_grade_1","value":"'.$pen_grade_1.'"}
					,{"dataLabel":"pen_type_1","value":"'.$pen_type_1.'"}
					,{"dataLabel":"ent_name_1","value":"'.$ent_name_1.'"}	
					,{"dataLabel":"ent_entnum_1","value":"'.$ent_entnum_1.'"}
					,{"dataLabel":"ent_ceoname_1","value":"'.$ent_ceoname_1.'"}
					,{"dataLabel":"contract_name_1","value":"'.$contract_name_1.'"}
					,{"dataLabel":"contract_relation_1","value":"'.$contract_relation_1.'"}
					,{"dataLabel":"contract_tel_1","value":"'.$contract_tel_1.'"}
					'.$it_purchase_categoryN_1.'
					'.$it_purchase_nameN_1.'
					'.$it_purchase_codeN_1.'
					'.$it_purchase_barcodeN_1.'
					'.$it_purchase_countN_1.'
					'.$it_purchase_dateN_1.'
					'.$it_purchase_priceN_1.'
					'.$it_purchase_price_penN_1.'
					'.$it_purchase_price_sum_1.'
					'.$it_purchase_price_pen_sum_1.'
					'.$it_rent_categoryN_1.'
					'.$it_rent_nameN_1.'
					'.$it_rent_codeN_1.'
					'.$it_rent_barcodeN_1.'
					'.$it_rent_dateN_1.'
					'.$it_rent_priceN_1.'
					'.$it_rent_price_penN_1.'
					'.$it_rent_price_sum_1.'
					'.$it_rent_price_pen_sum_1.'
					'.$sd.'
					'.$sad.'
					,{"dataLabel":"ent_ConAcc_1","value":"'.$row["ent_ConAcc_1"].'"}
					,{"dataLabel":"dc_date_1","value":"'.$dc_date_1.'"}
					,{"dataLabel":"pen_name_2","value":"'.$pen_name_2.'"}
					,{"dataLabel":"contract_name_2","value":"'.$contract_name_2.'"}
					,{"dataLabel":"ent_name_2","value":"'.$ent_name_2.'"}	
					,{"dataLabel":"ent_ceoname_2","value":"'.$ent_ceoname_2.'"}
					,{"dataLabel":"ent_signimage_1","value":{"base64":"'.$stamp.'"}}
					,{"dataLabel":"pen_name_3","value":"'.$pen_name_3.'"}
					,{"dataLabel":"pen_birthday_1","value":"'.$pen_birthday_1.'"}
					,{"dataLabel":"pen_grade_2","value":"'.$pen_grade_2.'"}
					,{"dataLabel":"pen_ltmnum_2","value":"'.$pen_ltmnum_2.'"}
					,{"dataLabel":"ent_name_3","value":"'.$ent_name_3.'"}
					,{"dataLabel":"ent_entnum_2","value":"'.$ent_entnum_2.'"}
					'.$it_purchase_categoryN_2.'
					'.$it_purchase_nameN_2.'
					'.$it_purchase_codeN_2.'
					'.$it_purchase_priceN_2.'
					'.$it_purchase_dateN_2.'
					'.$it_purchase_sumpriceN_1.'
					'.$it_purchase_price_penN_2.'
					'.$it_purchase_price_entN_1.'
					'.$it_rent_categoryN_2.'
					'.$it_rent_nameN_2.'
					'.$it_rent_codeN_2.'
					'.$it_rent_priceN_2.'
					'.$it_rent_dateN_2.'
					'.$it_rent_sumpriceN_1.'				
					'.$it_rent_price_penN_2.'
					'.$it_rent_price_entN_1.'
					,{"dataLabel":"ent_ceoname_3","value":"'.$ent_ceoname_3.'"}
					,{"dataLabel":"pen_contract_name_1","value":"'.$pen_contract_name_1.'"}
					,{"dataLabel":"pen_contract_relation_1","value":"'.$pen_contract_relation_1.'"}
					,{"dataLabel":"dc_date_2","value":"'.$dc_date_2.'"}
					,{"dataLabel":"dc_rectype_1","value":"'.$dc_rectype_1.'"}
					,{"dataLabel":"ent_signimage_2","value":{"base64":"'.$stamp.'"}}
					,{"dataLabel":"dc_date_3","value":"'.$dc_date_3.'"}
					,{"dataLabel":"pen_name_4","value":"'.$pen_name_4.'"}
					,{"dataLabel":"contract_name_3","value":"'.$contract_name_3.'"}
					,{"dataLabel":"contract_name_4","value":"'.$contract_name_4.'"}
					,{"dataLabel":"contract_addr_1","value":"'.$contract_addr_1.'"}
					,{"dataLabel":"contract_relation_2","value":"'.$contract_relation_2.'"}
					,{"dataLabel":"contract_tel_2","value":"'.$contract_tel_2.'"}
					,{"dataLabel":"pen_name_5","value":"'.$pen_name_5.'"}
					,{"dataLabel":"pen_ltmnum_3","value":"'.$pen_ltmnum_3.'"}
					,{"dataLabel":"pen_addr_1","value":"'.$pen_addr_1.'"}
					'.$gicho_con.'				
					]
					,"metadatas":[{"key":"entId","value":"'.$_POST["mb_entId1"].'"},{"key":"dc_id","value":"'.strtolower($_POST["dc_id1"]).'"}]
					,"title":"'.$_POST["title"].'"
				}
				,"templateId":"'.$temp_doc_id.'"}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//메타데이터 확인

	$url = $arrResponse["file"]["downloadUrl"];
	
	if($url != ""){
		$response2["url"] = $url;
		$response2["d_id"] = $arrResponse["id"];
		$response2["p_id"] = $arrResponse["participants"][0]["id"];
		$sql = "update eform_document set dc_sign_send_datetime=now(),dc_status='4' where dc_id=UNHEX('".$_POST["dc_id1"]."')";
		sql_query($sql);
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}

}elseif($_POST["div"] == "resend_doc"){//계약서 재 전송
	$api_url = 'https://api.modusign.co.kr/documents?offset=0&limit=1&metadatas=%7B%22dc_id%22%3A%22'.strtolower($_POST["dc_id"]).'%22%7D';
	$type = "GET";
	$data = "";
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//문서 ID 확인
	$url = $arrResponse["documents"][0]["file"]["downloadUrl"];
	$participants_count = count($arrResponse["documents"][0]["participants"]);
	
	for($i=0;$i<$participants_count;$i++){
		$com = ($i != 0)?",":"";
		$mo_nums .= $com.'"'.$arrResponse["documents"][0]["participants"][$i]["signingMethod"]["value"].'"';
	}
	$doc_id = $arrResponse["documents"][0]["id"];
	$api_url = 'https://api.modusign.co.kr/documents/'.$doc_id.'/forward';
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
	$api_url = 'https://api.modusign.co.kr/documents?offset=0&limit=1&metadatas=%7B%22dc_id%22%3A%22'.strtolower($_POST["dc_id"]).'%22%7D';
	$type = "GET";
	$data = "";
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);
	$doc_id = $arrResponse["documents"][0]["id"];//문서 ID 확인

	$api_url = 'https://api.modusign.co.kr/documents/'.$doc_id.'/metadatas';
	$type = "PUT";
	$data = '{"metadatas":[{"key":"dc_id","value":"sign_cancel'.strtolower($_POST["dc_id"]).'"}]}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//메타데이터 변경

	$api_url = 'https://api.modusign.co.kr/documents/'.$doc_id.'/cancel';
	$type = "POST";
	$data = '{"accessibleByParticipant":false,"message":"서명요청 취소"}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//서명요청 취소

	$url = $arrResponse["file"]["downloadUrl"];
	if($url != ""){
		$response2["url"] = $url;
		$sql = "update eform_document set dc_sign_send_datetime='0000-00-00 00:00:00',dc_status='11' where dc_id=UNHEX('".$_POST["dc_id"]."')";
		sql_query($sql);
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
}elseif($_POST["div"] == "dc_reset"){//거절 계약서 초기화
	$api_url = 'https://api.modusign.co.kr/documents?offset=0&limit=1&metadatas=%7B%22dc_id%22%3A%22'.strtolower($_POST["dc_id"]).'%22%7D';
	$type = "GET";
	$data = "";
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);
	$doc_id = $arrResponse["documents"][0]["id"];
	$api_url = 'https://api.modusign.co.kr/documents/'.$doc_id.'/metadatas';
	$type = "PUT";
	$data = '{"metadatas":[{"key":"dc_id","value":"dc_reset'.strtolower($_POST["dc_id"]).'"}]}';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//메타데이터 변경

	$url = $arrResponse["metadatas"][0]["key"];
	if($url != ""){
		$response2["url"] = $url;
		$sql = "update eform_document set dc_sign_datetime='0000-00-00 00:00:00',dc_sign_send_datetime='0000-00-00 00:00:00',dc_status='11' where dc_id=UNHEX('".$_POST["dc_id"]."')";
		sql_query($sql);
		$response2["api_stat"] = "1";
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
}elseif($_REQUEST["div"] == "completed_doc"){//서명완료,서명거절 시 대기 화면 
	$api_url = 'https://api.modusign.co.kr/documents/'.$_REQUEST["dc_id"];
	$type = "GET";
	$data = '';
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);//메타데이터 확인
	$dc_id =  strtoupper($arrResponse["metadatas"][0]["value"]);
	$url = "";
	$sql = "SELECT dc_status FROM `eform_document` WHERE dc_id=UNHEX('".$dc_id."')";
	$row=sql_fetch($sql);
	if($row["dc_status"] == "3" || $row["dc_status"] == "5"){
		$url = $row["dc_status"];
	}else{
		for($i=0;$i<16;$i++){
			$sql = "SELECT dc_status FROM `eform_document` WHERE dc_id=UNHEX('".$dc_id."')";
			$row=sql_fetch($sql);
			if($row["dc_status"] == "3" || $row["dc_status"] == "5"){
				$url = $i;
				break;
			}elseif($i == 15){
				$url = $i;
				break;
			}
			sleep(1);
		}
	}
	//$url = $dc_id;

	if($url != ""){
		$response2["url"] = $url;
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
	
}

echo json_encode($response2);


?>