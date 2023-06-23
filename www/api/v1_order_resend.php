<?php
include_once("./_common.php");
	ini_set( 'display_errors', '0' );
   
	//error_reporting(E_ALL);
	$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
	if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
		@umask(0);
		@mkdir($log_dir,0777);
		//@chmod($upload_dir, 0777);
	}
	
	$log_file = fopen($log_dir . 'eroum_on_api_send_log_'.date("Ymd").'.txt', 'a');
	$log_txt = "====== API 보내기 시작 [".$_SERVER["REMOTE_ADDR"]."] ============================================ \r\n";
    fwrite($log_file, $log_txt . "\r\n");
	$apiKey = eroumAPI_Key;//"f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856";f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856
    $url = eroumAPI_url;//"https://eroum.icubesystems.co.kr/eroumcareApi/bplcRecv/callback.json";
	
	$API_Div = $_REQUEST["API_Div"];//API 구분
	$order_send_id = $_REQUEST["order_send_id"];//주문ID
	
	$ct_id = $_REQUEST["ct_id"];//카트ID
	if($API_Div == "order_ent_response"){//주문 승인 반려
		$sql = "SELECT o.* FROM `g5_shop_cart_api` o
		WHERE o.mb_id='".$member["mb_id"]."' and o.order_send_id='".$order_send_id."'";
		$result = sql_query($sql);
		$comma = "";
		$log_text = "수급자 주문 취소(사업소 취소)";
		$count = 0;
		$c_count = 0;
		while($row2=sql_fetch_array($result)){
			$STTS_TY = ($row2["ct_status"] == "승인")? "1": "0";//주문 승인,반려 상태
			$log_text = ($row2["ct_status"] == "승인")? "수급자 주문 승인": $log_text;
			$ORDR_DTL_CD = $row2["order_send_id2"];//주문상세ID(?)
			$ProdPayCode[$row2["order_send_id2"]] = $row2["ProdPayCode"];
			$RESN = $row2["ct_memo"];
			$_array_item .= $comma.'{"order_send_dtl_id":"'.base64_encode($ORDR_DTL_CD).'"
							,"item_state":"'.base64_encode($STTS_TY).'"
							,"item_memo":"'.base64_encode($RESN).'"}';
			$comma = ",";
			if($row2["ct_status"] == "반려"){
				$c_count++;
			}
			$count++;			
		}
		$od_status = ($count == $c_count && $count != 0)? "주문취소": "주문처리";
		$postdata = '{"api_div":"'.base64_encode("order_ent_response").'"
		,"order_send_id":"'.base64_encode($order_send_id).'"
		,"_array_item":['.$_array_item.']}
		';//상품 전체로
		$log_txt ="";
		$log_txt .= "[".date("Y-m-d H:i:s")."]"." 송신용 데이터 - ".$API_Div." \r\n";
		fwrite($log_file, $log_txt . "\r\n");
		$log_txt ="";
		$log_txt .= "order_send_id:".$order_send_id."\r\n";
		fwrite($log_file, $log_txt . "\r\n");

		api_log_write($order_send_id,$member["mb_id"], '2', $log_text);
	}elseif($API_Div == "order_status"){//주문 상태 상품 건별로 전달, 주문상품 전체 출고 상태 체크
		$sql = "SELECT m.mb_id,m.mb_giup_bnum FROM g5_member AS m 
		INNER JOIN g5_shop_cart_api c ON c.mb_id = m.mb_id AND c.order_send_id='{$order_send_id}' AND c.ct_sync_ctid = '{$ct_id}'
		where 1";
		$row_b = sql_fetch($sql);
		
		$sql = "SELECT o.order_send_id,o.order_send_id2,m.mb_name as item_delivery_num_name,o.ProdPayCode, c.* FROM `g5_shop_cart_api` o
		left outer join g5_shop_cart c on o.ct_sync_ctid = c.ct_id
		left outer join g5_member m on c.ct_direct_delivery_partner = m.mb_id
		WHERE o.order_send_id='{$order_send_id}' and o.ct_sync_ctid = '".$ct_id."'";
		$row2 = sql_fetch($sql);
		$ORDR_DTL_CD = $row2["order_send_id2"];//주문상세ID(?)
		$ct_delivery_company = $row2["ct_delivery_company"];
		$ct_delivery_num = $row2["ct_delivery_num"];
		$item_delivery_num_name = $row2["item_delivery_num_name"];
		$ct_memo = $row2["ct_memo"];
		
		$sql333 = "select od_status from g5_shop_order_api where order_send_id='".$row["order_send_id"]."' and mb_id='".$row_b['mb_id']."'"; 
		$row333 = sql_fetch($sql33);
		if($row333 != "출고완료"){
			api_log_write($order_send_id,$row_b['mb_id'], '3', "이로운 주문 출고완료[급여코드:".$row2['ProdPayCode']."]");
			$log_txt ="";			
			$log_txt .= "[".date("Y-m-d H:i:s")."]"." 송신용 데이터 - ".$API_Div." \r\n";
			$log_txt .= "order_send_id:".$order_send_id." | ct_id:".$ct_id."\r\n";
			fwrite($log_file, $log_txt . "\r\n");
		}
		$sql = "SELECT COUNT(a.ct_id) AS a_cnt, COUNT(c.ct_id) AS c_cnt, api.order_send_id 
		FROM g5_shop_cart b
		LEFT OUTER JOIN g5_shop_cart_api api ON api.ct_sync_ctid = b.ct_id
		LEFT OUTER JOIN g5_shop_cart_api a ON a.order_send_id = api.order_send_id AND a.ct_status='승인'
		LEFT OUTER JOIN g5_shop_cart c ON c.ct_id IN (a.ct_sync_ctid) AND c.ct_status IN ('배송', '완료')
		WHERE b.ct_id ='".$ct_id."'";//카트ID 로 수급자 주문 카트 승인 카운트 조회, 카트 출과완료,배송완료 건 카우트 조회 
		//$log_txt .= $sql."\r\n";		
		//api_log_write($order_send_id,$row_b['mb_id'], '3', $ORDR_DTL_CD);
		$row = sql_fetch($sql);
		
		//$log_txt .= stripslashes(json_encode($row, JSON_UNESCAPED_UNICODE))."\r\n";
		//fwrite($log_file, $log_txt . "\r\n");

		if($row["a_cnt"] == $row["c_cnt"] && $row["a_cnt"] != 0){//수급자 주문 승인건과 주문출고완료건이 같을 경우
			
			if($row333 != "출고완료"){
				$sql2 = "update g5_shop_order_api set od_status='출고완료' where order_send_id='".$row["order_send_id"]."' and mb_id='".$row_b['mb_id']."'";
				sql_query($sql2);//수급자주문 전체 출고완료 처리
				api_log_write($order_send_id,$row_b['mb_id'], '3', "수급자 주문 상태값 변경(출고완료)");
			}
		}

		

		$postdata = '{"api_div":"'.base64_encode("order_status").'"
		,"order_business_id":"'.base64_encode($row_b["mb_giup_bnum"]).'"
		,"order_send_id":"'.base64_encode($row2["order_send_id"]).'"
		,"order_send_dtl_id":"'.base64_encode($ORDR_DTL_CD).'"
		,"item_status":"'.base64_encode($row2["ct_status"]).'"
		,"item_delivery_company":"'.base64_encode($ct_delivery_company).'"
		,"item_delivery_num_name":"'.base64_encode($item_delivery_num_name).'"
		,"item_delivery_num":"'.base64_encode($ct_delivery_num).'"
		,"item_memo":"'.base64_encode($ct_memo).'"}
		';
		if($row333 != "출고완료"){
			api_log_write($order_send_id,$row_b['mb_id'], '2', "송장정보 전달[급여코드:".$row2['ProdPayCode']."]");
		}
	}if($API_Div == "eform_status"){//계약서 상태
		$sql = "SELECT o.* FROM `g5_shop_order_api` o
		WHERE o.mb_id='".$member["mb_id"]."' and o.order_send_id='".$order_send_id."'";
		$row = sql_fetch($sql);
		if($row["od_status"] == "서명요청"){//계약서 생성
			$log_text = "계약서 생성,요청";
			$eform_status = "22";//서명요청?
		}elseif($row["od_status"] == "서명완료"){
			$eform_status = "3";
			$log_text = "계약서 서명완료";
		}
		$postdata = '{"ORDR_CD":"'.base64_encode($order_send_id).'"
		,"order_business_id":"'.base64_encode($member["mb_giup_bnum"]).'"
		,"eform_status":"'.base64_encode($eform_status).'"}';//상품 전체로
		api_log_write($order_send_id,$member['mb_id'], '3', $log_text);
	}

	$log_txt ="";
	$log_txt .= "[".date("Y-m-d H:i:s")."]"." 송신 - ".$API_Div." \r\n";
	$log_txt .= stripslashes(json_encode($postdata, JSON_UNESCAPED_UNICODE))."\r\n";

	fwrite($log_file, $log_txt . "\r\n");

    $ch = curl_init(); // 리소스 초기화
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ssl 접근시 필요
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//ssl 접근시 필요
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 최초 연결 시도 2초 이내 불가시 연결 취소
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'eroumAPI_Key:'.$apiKey,
	  'eroum_api_key: '.$apiKey,
      'Content-Type: application/json'
    ));
 
    $output = curl_exec($ch); // 데이터 요청 후 수신
    //echo $output;
    $rs=json_decode($output);
    curl_close($ch);  // 리소스 해제
	
    $log_txt ="";
	$log_txt .= "[".date("Y-m-d H:i:s")."]"." 응답 - ".$API_Div." \r\n";

	$log_txt .= stripslashes(json_encode($rs, JSON_UNESCAPED_UNICODE))."\r\n";
	$rs2 = json_encode($rs);
	$rs = json_decode($rs2,true);
	if($rs["resultCode"] == "0"){//성공응답시
		if($API_Div == "order_ent_response"){//승인반려 주문처리 성공시
			if(is_array($rs["_array_item"]) && $od_status != "주문취소"){// 취소 상품이 있을 경우 처리
				for($i=0; $i<count($rs["_array_item"]); $i++){
					$sql1 = "update `g5_shop_cart_api` set ct_status='반려',ct_memo='수급자취소[".$rs["_array_item"][$i]["item_memo"]."]' where order_send_id2='".$rs["_array_item"][$i]["order_send_dtl_id"]."' AND mb_id='".$member["mb_id"]."'";
					sql_query($sql1);//취소 상품 반려 처리
					api_log_write($order_send_id,$member['mb_id'], '3', "수급자 승인 전 취소[급여코드:".$ProdPayCode[$rs["_array_item"][$i]["order_send_dtl_id"]]."]");
				}
				$sql2 = "SELECT COUNT(ct_id) AS all_cnt,COUNT(CASE WHEN ct_status='반려' THEN 1 END) AS c_cnt 
						FROM `g5_shop_cart_api` 
						WHERE order_send_id='".$order_send_id."' AND mb_id='".$member["mb_id"]."'";
				$row2 = sql_fetch($sql2);//전체 상품수, 반려 상품수 비교
				if($row2["all_cnt"] == $row2["c_cnt"]){// 전체 상품수와 반려 상품수가 동일할 경우 전체 취소 처리 
					$od_status = "주문취소";
					api_log_write($order_send_id,$member['mb_id'], '3', "수급자 승인 전 취소로 전체 취소 처리");
				}
			}

			$sql = "update `g5_shop_order_api` o
			set o.od_status = '".$od_status."'
			WHERE o.mb_id='".$member["mb_id"]."' and o.order_send_id='".$order_send_id."'";
			sql_query($sql);
		}
	}
	
	
//======================= 내부 테스트용 ===============================================================
if(strpos($_SERVER['HTTP_HOST'],"test.eroumcare")){
	$url2 = "https://eroum.icubesystems.co.kr/eroumcareApi/bplcRecv/callback.json";
	$ch = curl_init(); // 리소스 초기화
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ssl 접근시 필요
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//ssl 접근시 필요
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 최초 연결 시도 2초 이내 불가시 연결 취소
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'eroumAPI_Key:'.$apiKey,
	  'eroum_api_key: '.$apiKey,
      'Content-Type: application/json'
    ));
 
    $output = curl_exec($ch); // 데이터 요청 후 수신
    //echo $output;
    $rs=json_decode($output);
    curl_close($ch);  // 리소스 해제


	$log_txt .= "[".date("Y-m-d H:i:s")."]"." 응답 - ".$API_Div." \r\n";

	$log_txt .= stripslashes(json_encode($rs, JSON_UNESCAPED_UNICODE))."\r\n";
	$rs2 = json_encode($rs);
	$rs = json_decode($rs2,true);
	if($rs["resultCode"] == "0"){//성공응답시
		if($API_Div == "order_ent_response"){//승인반려 주문처리 성공시
			if(is_array($rs["_array_item"]) && $od_status != "주문취소"){// 취소 상품이 있을 경우 처리
				for($i=0; $i<count($rs["_array_item"]); $i++){
					$sql1 = "update `g5_shop_cart_api` set ct_status='반려',ct_memo='수급자취소[".$rs["_array_item"][$i]["item_memo"]."]' where order_send_id2='".$rs["_array_item"][$i]["order_send_dtl_id"]."' AND mb_id='".$member["mb_id"]."'";
					sql_query($sql1);//취소 상품 반려 처리
					api_log_write($order_send_id,$member['mb_id'], '3', "수급자 승인 전 취소[급여코드:".$ProdPayCode[$rs["_array_item"][$i]["order_send_dtl_id"]]."]");
				}
				$sql2 = "SELECT COUNT(ct_id) AS all_cnt,COUNT(CASE WHEN ct_status='반려' THEN 1 END) AS c_cnt 
						FROM `g5_shop_cart_api` 
						WHERE order_send_id='".$order_send_id."' AND mb_id='".$member["mb_id"]."'";
				$row2 = sql_fetch($sql2);//전체 상품수, 반려 상품수 비교
				if($row2["all_cnt"] == $row2["c_cnt"]){// 전체 상품수와 반려 상품수가 동일할 경우 전체 취소 처리 
					$od_status = "주문취소";
					api_log_write($order_send_id,$member['mb_id'], '3', "수급자 승인 전 취소로 전체 취소 처리");
				}
			}

			$sql = "update `g5_shop_order_api` o
			set o.od_status = '".$od_status."'
			WHERE o.mb_id='".$member["mb_id"]."' and o.order_send_id='".$order_send_id."'";
			sql_query($sql);
		}
	}
	//header('Content-type: application/json');
	//echo json_encode($rows);
//======================= 내부 테스트용 ===============================================================
}

$log_txt .= "====== API 보내기 끝 ============================================================= \r\n";
	fwrite($log_file, $log_txt . "\r\n");
	fclose($log_file);
	
	echo "<pre>";
    print_r($rs);
?>
