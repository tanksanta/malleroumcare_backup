<?php
include_once("./_common.php");

ini_set( 'display_errors', '0' );

// 추후 웹 화면 쪽으로 이동 예정
//$query = "SHOW tables LIKE 'g5_shop_order_api'";

//$result = mysql_fetch_row(sql_query($query));

$query = "SHOW tables LIKE 'g5_shop_order_api'";//api 오더 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
//$query = "SHOW tables LIKE 'g5_shop_cart_api'";//api 카트 테이블 유무 확인
//$wzres = sql_fetch( $query );

if($wzres < 1) {
    sql_query("CREATE TABLE `g5_shop_order_api` (
  `od_id` varchar(50) NOT NULL DEFAULT '0',
  `order_send_id` varchar(50) NOT NULL DEFAULT '0' COMMENT '1.5주문ID',
  `mb_id` varchar(255) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 사업소 아이디',
  `od_name` varchar(20) NOT NULL DEFAULT '',
  `relation_code` varchar(20) DEFAULT NULL,
  `od_penId` varchar(20) DEFAULT NULL,
  `od_penNm` varchar(20) NOT NULL,
  `od_penRecGraNm` varchar(255) DEFAULT NULL,
  `od_penTypeNm` varchar(20) DEFAULT NULL,
  `od_penExpiDtm` varchar(255) DEFAULT NULL,
  `od_penAppEdDtm` varchar(255) DEFAULT NULL,
  `od_penGender` varchar(10) DEFAULT '' COMMENT '성별 구별 남자:남, 여자:여',
  `od_penConPnum` varchar(20) DEFAULT NULL,
  `od_penConNum` varchar(20) DEFAULT NULL,
  `od_penZip1` char(3) DEFAULT NULL COMMENT '수급자 우편번호1',
  `od_penZip2` char(3) DEFAULT NULL COMMENT '수급자 우편번호2',
  `od_penAddr` varchar(100) DEFAULT NULL COMMENT '수급자 주소',
  `od_penAddr2` varchar(100) DEFAULT NULL COMMENT '수급자 주소 상세',
  `od_penLtmNum` varchar(100) NOT NULL,
  `od_zip1` char(3) DEFAULT NULL COMMENT '신청자 우편번호',
  `od_zip2` char(3) DEFAULT NULL COMMENT '신청자 우편번호2',
  `od_addr` varchar(100) DEFAULT NULL COMMENT '신청자 주소',
  `od_addr2` varchar(100) DEFAULT NULL COMMENT '신청자 주소 상세',
  `od_birth` varchar(20) DEFAULT NULL COMMENT '신청자 생년월일',
  `od_b_id` varchar(50) NOT NULL DEFAULT '',
  `od_b_name` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 구매자 이름',
  `od_b_name2` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 수령인 이름',
  `od_b_tel` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 수령인 연락처',
  `od_b_hp` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 구매자 연락처',
  `od_b_zip1` char(3) NOT NULL DEFAULT '',
  `od_b_zip2` char(3) NOT NULL DEFAULT '',
  `od_b_addr1` varchar(100) NOT NULL DEFAULT '',
  `od_b_addr2` varchar(100) NOT NULL DEFAULT '',
  `od_b_addr3` varchar(255) NOT NULL DEFAULT '',
  `od_memo` text NOT NULL,
  `od_cart_count` int(11) NOT NULL DEFAULT 0,
  `od_status` varchar(255) NOT NULL DEFAULT '',
  `od_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `od_cancel_reason` varchar(30) DEFAULT NULL COMMENT '취소신청사유',
  `od_cancel_time` datetime DEFAULT NULL COMMENT '취소신청시간',
  `od_sync_odid` varchar(50) DEFAULT NULL COMMENT '/* 23.03.08 : 서원 - 추가 */ g5_shop_order 테이블의 연결 od_id 값',
  KEY `index2` (`mb_id`),
  KEY `order_send_id` (`order_send_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", true);
}

//$query = "SHOW tables LIKE 'g5_shop_cart_api'";

//$result = mysql_fetch_row(mysql_query($query));

$query = "SHOW tables LIKE 'g5_shop_cart_api'";//api 카트 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
if($wzres < 1) {
	sql_query("
CREATE TABLE `g5_shop_cart_api` (
  `ct_id` int(11) NOT NULL AUTO_INCREMENT,
  `od_id` varchar(50) NOT NULL DEFAULT '0',
  `order_send_id` varchar(50) NOT NULL DEFAULT '0' COMMENT '1.5주문ID',
  `order_send_id2` varchar(50) NOT NULL DEFAULT '0' COMMENT '1.5주문상세ID',
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `it_id` varchar(20) NOT NULL DEFAULT '',
  `ProdPayCode` varchar(20) NOT NULL DEFAULT '',
  `it_name` varchar(255) NOT NULL DEFAULT '',
  `ct_status` enum('','승인','반려') DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 변경 */ 1.0과 1.5사이에 발생한 상품 이벤트 처리결과 ( '''', ''승인'',''반려'' ) 3가지 항목만 입력가능',
  `ct_qty` int(11) NOT NULL DEFAULT 0,
  `ct_stock_qty` int(11) DEFAULT 0,
  `ct_barcode` text NOT NULL DEFAULT '',
  `ct_notax` tinyint(4) NOT NULL DEFAULT 0,
  `io_id` varchar(255) NOT NULL DEFAULT '',
  `io_type` tinyint(4) NOT NULL DEFAULT 0,
  `ct_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ct_memo` text NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 상품에 대한 반려 사유 저장 항목',
  `ordLendStrDtm` datetime DEFAULT NULL COMMENT '대여시작일',
  `ordLendEndDtm` datetime DEFAULT NULL COMMENT '대여종료일',
  `ct_delivery_yn` varchar(1) NOT NULL DEFAULT 'N',
  `ct_delivery_company` longtext DEFAULT NULL,
  `ct_delivery_num` longtext DEFAULT NULL,
  `ct_delivery_cnt` int(11) NOT NULL DEFAULT 1,
  `ct_sync_ctid` int(11) DEFAULT NULL COMMENT '/* 23.03.08 : 서원 - 추가 */ g5_shop_cart테이블의 ct_id 연결 값',
  PRIMARY KEY (`ct_id`),
  KEY `it_id` (`it_id`,`order_send_id`,`order_send_id2`,`ct_status`,`ct_sync_ctid`,`mb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=461178 DEFAULT CHARSET=utf8
");
}

$query = "SHOW tables LIKE 'g5_shop_api_log'";//api 로그 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
if($wzres < 1) {
	sql_query("CREATE TABLE `g5_shop_api_log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_send_id` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT '1.5주문ID',
  `mb_id` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT '사업소ID',
  `log_type` TINYINT(5) NOT NULL DEFAULT 0 COMMENT '로그구분 1:수신,2:송신,3:로그',
  `log_cont` VARCHAR(255) NULL DEFAULT ''  COMMENT '로그 내용',
  `log_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '로그 기록 시간' ,
  PRIMARY KEY (`log_id`),
  KEY `order_send_id` (`order_send_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
}
// 추후 웹 화면 쪽으로 이동 예정
/*
function api_log_write($order_send_id,$mb_id, $type, $cont){
	$sql = "insert g5_shop_api_log set order_send_id='$order_send_id',mb_id='$mb_id', log_type='$type', log_cont='$cont',log_time=now();";
	sql_query($sql);
}
*/
header('Content-Type: text/html; charset=utf-8');

$headers = apache_request_headers();

$apikey = $headers['eroumAPI_Key'];//apikey 확인

$xapikey = eroumAPI_Key;//"f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856";//SELECT SHA2('thkc1300',256);

if($apikey==$xapikey){//apikey가 맞으면
	$json_string = file_get_contents('php://input');
	$post_data = json_decode($json_string, true);
	$returnArray["API_Div"] = $API_Div = base64_decode($post_data["API_Div"]);//AIP 구분

$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
	@umask(0);
	@mkdir($log_dir,0777);
	//@chmod($upload_dir, 0777);
}
$log_file = fopen($log_dir . 'eroum_on_api_receive_log_'.date("Ymd").'.txt', 'a');
$log_txt = "====== API 받기 시작 [".$_SERVER["REMOTE_ADDR"]."] ============================================ \r\n";

$log_txt .= "[".date("Y-m-d H:i:s")."]"." 수신 - ".$API_Div." \r\n";

$log_txt .= $json_string."\r\n";
//============================================================================ 주문 시작 ============================================================================================== 	
	if($API_Div == "order_pen_first"  || $API_Div == ""){//order_pen_first - 최초 수급자 주문 접수
////데이터 검증		
		$returnArray["order_send_id"] = $order_send_id = base64_decode($post_data["order_send_id"]);//1.5 주문 id
		$returnArray["order_business_id"] = $order_business_id = base64_decode($post_data["order_business_id"]);//사업자번호
		$returnArray["order_id"] = $od_b_id = base64_decode($post_data["order_id"]);//주문자ID
		$returnArray["order_tel"] = $od_b_tel = base64_decode($post_data["order_tel"]);//주문자연락처
		$returnArray["delivery_tel"] = $od_b_hp = base64_decode($post_data["delivery_tel"]);//배송지HP
		$returnArray["delivery_zip"] = base64_decode($post_data["delivery_zip"]);
		$od_b_zip1 = substr(base64_decode($post_data["delivery_zip"]),0,3);//우편번호1 추가로 받아야함
		$od_b_zip2 = substr(base64_decode($post_data["delivery_zip"]),3,2);//우편번호2
		$returnArray["delivery_addr"] = $od_b_addr1 = base64_decode($post_data["delivery_addr"]);//주소1 세분화 해서 받아야함
		$returnArray["delivery_addr2"] = $od_b_addr2 = base64_decode($post_data["delivery_addr2"]);//주소2 세분화 해서 받아야함	
		$returnArray["delivery_addr3"] = $od_b_addr2 = base64_decode($post_data["delivery_addr3"]);//주소2 세분화 해서 받아야함	
		$returnArray["order_nm"] = $od_b_name = base64_decode($post_data["order_nm"]);//주문자명
		$returnArray["delivery_nm"] = $od_b_name2 = base64_decode($post_data["delivery_nm"]);//수령인명
		$returnArray["relation_code"] = $relation_code = base64_decode($post_data["relation_code"]);//수급자와의 관계
		$returnArray["penNm"] = $od_penNm = base64_decode($post_data["penNm"]);	//수급자명
		$returnArray["penLtmNum"] = $od_penLtmNum = base64_decode($post_data["penLtmNum"]);	//요양인증번호
		$returnArray["penTel"] = $od_penConPnum = base64_decode($post_data["penTel"]);	//수급자 휴대폰번호
		$returnArray["penGender"] = $od_penGender = base64_decode($post_data["penGender"]);	//수급자 성별 남,녀
		$returnArray["penZip"] = $od_penZip = base64_decode($post_data["penZip"]);	//수급자 우편번호
		$od_penZip1 = substr(base64_decode($post_data["penZip"]),0,3);//우편번호1 추가로 받아야함
		$od_penZip2 = substr(base64_decode($post_data["penZip"]),3,2);//우편번호2
		$returnArray["penAddr"] = $od_penAddr = base64_decode($post_data["penAddr"]);	//수급자 주소
		$returnArray["penAddr2"] = $od_penAddr2 = base64_decode($post_data["penAddr2"]);	//수급자 주소상세
		$returnArray["order_zip"] = $od_zip = base64_decode($post_data["order_zip"]);	//신청인 우편번호
		$od_zip1 = substr(base64_decode($post_data["order_zip"]),0,3);//우편번호1 추가로 받아야함
		$od_zip2 = substr(base64_decode($post_data["order_zip"]),3,2);//우편번호2
		$returnArray["order_addr"] = $od_addr = base64_decode($post_data["order_addr"]);	//신청인 주소
		$returnArray["order_addr2"] = $od_addr2 = base64_decode($post_data["order_addr2"]);	//신청인 주소상세
		$returnArray["order_birth"] = $od_birth = base64_decode($post_data["order_birth"]);	//신청인 생년월일

		$returnArray["delivery_memo"] = $od_memo = base64_decode($post_data["delivery_memo"]);//주문 메모
		/*$od_penTypeCd = base64_decode($post_data["penTypeCd"]);	//본인부담율
		switch($od_penTypeCd){
			case "00": $od_penTypeNm = "일반15%"; break;
			case "01": $od_penTypeNm = "감경9%"; break;
			case "02": $od_penTypeNm = "감경6%"; break;
			case "03": $od_penTypeNm = "의료6%"; break;
			case "04": $od_penTypeNm = "기초0%"; break;	
			default: $od_penTypeNm = ""; break;
		}
		$returnArray["penTypeCd"] = $od_penTypeCd;
		*/
	if($post_data["order_send_id"] != "" && $post_data["order_business_id"] != "" && $post_data["order_nm"] != "" && $post_data["order_tel"] != "" && $post_data["order_id"] != "" && $post_data["delivery_tel"] != "" && $post_data["delivery_addr"] != "" && $post_data["delivery_addr2"] != "" && $post_data["delivery_zip"] != "" && $post_data["delivery_nm"] != "" && $post_data["penNm"] != "" && $post_data["penLtmNum"] != "" && $post_data["penTel"] != "" && $post_data["penGender"] != "" && $post_data["penZip"] != "" && $post_data["penAddr"] != "" && $post_data["penAddr2"] != "" && is_array($post_data["_array_item"]) && $post_data["_array_item"][0]["ProdPayCode"] != ""  && $post_data["_array_item"][0]["item_qty"] != "" && $post_data["_array_item"][0]["order_send_dtl_id"] != "" ){//누락 데이터가 없을 경우
		
		$error_stat = false;
		
		if($post_data["relation_code"] != "" && $post_data["relation_code"] != "0" && ($post_data["order_zip"] != "" || $post_data["order_addr"] != "" || $post_data["order_addr2"] != ""  || $post_data["order_birth"] != "")){
			$empty_data .=($post_data["order_zip"] == "")?"order_zip,":"";
			$empty_data .=($post_data["order_addr"] == "")?"order_addr,":"";
			$empty_data .=($post_data["order_addr2"] == "")?"order_addr2,":"";
			$empty_data .=($post_data["order_birth"] == "")?"order_birth,":"";
			$code = "411";
			$msg = $empty_data."누락 데이터가 있습니다.(계약서생성 필수 데이터)";
			$data = $returnArray;
		}
		//사업소정보 확인
		$sql_b = "select * from g5_member where mb_giup_bnum='".$order_business_id."' and (mb_level='3' or mb_level='4') " ;
		$row_b = sql_fetch($sql_b);
		if($row_b["mb_id"] == ""){//사업소 조회 실패
			$error_stat = true;
			$code = "412";
			$msg = "해당 사업소 정보가 없습니다.";
			$data = $returnArray;
		}

		if($error_stat == false){//수급자정보 확인 
			$od_penLtmNum = str_replace("L","",$od_penLtmNum);  //수급자 요양인정번호
			$od_penNm; //수급자 이름
			//wmds 에서 조회 후 없을 경우 틸코 API를 통해 공단 조회
			//wmds 에 있을 경우 정상 처리, 없을 경우 공단 조회 - 1.5에서 이미 조회 했을 것으로 간주 공단 조회 안함
			//공단 조회 시 있을 경우 등록 안내, 없을 경우 에러 처리
			$page = 1;
			$send_data = [];
			$send_data["penNm"] = $od_penNm;
			$send_data["penLtmNum"] = "L".$od_penLtmNum;//수급자번호에 L 제거 저장
			$send_data["usrId"] = $row_b["mb_id"];
			$send_data["entId"] = $row_b["mb_entId"];
			$send_data["pageNum"] = $page;
			$send_data["pageSize"] = 1;
			$send_data["appCd"] = "01";

			$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
			$list = [];
			foreach($res['data'] as $data) {
			  $checklist = ['penRecGraCd', 'penTypeCd', 'penExpiDtm', 'penBirth'];
			  $is_incomplete = false;
			  foreach($checklist as $check) {
				if(!$data[$check])
				  $is_incomplete = true;
			  }
			  if(!in_array($data['penGender'], ['남', '여']))
				$is_incomplete = true;
			  if($data['penTypeCd'] == '04' && !$data['penJumin'])
				$is_incomplete = true;
			  if($data['penExpiDtm']) {
				// 유효기간 만료일 지난 수급자는 유효기간 입력 후 주문하게 함
				$expired_dtm = substr($data['penExpiDtm'], -10);
				if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
				  $data['penExpiDtm'] = '';
				  $is_incomplete = true;
				}
			  }
			  $data['incomplete'] = $is_incomplete;
			  $list[] = $data;
			  $od_penId = $data["penId"];
			  $od_penRecGraNm = $data["penRecGraNm"];
			  $od_penTypeNm = $data["penTypeNm"];
			  $od_penExpiDtm = $data["penExpiDtm"];
			  $od_penAppEdDtm = $data["penAppEdDtm"];
			  $od_penGender = ($data["penGender"] != "")?$data["penGender"] : $od_penGender;
			}
			$save_pen_info = "Y";
			if(count($list) < 1){//등록된 수급자가 없을 경우 등록 권유 안내 처리
				$save_pen_info = "N";
			}
			$returnArray["save_pen_info"] = $save_pen_info;
		}
		//상품정보 확인
		//$returnArray["_array_item"] = $post_data["_array_item"];
		if($error_stat == false){
			for($i = 0; $i < count($post_data["_array_item"]); $i++ ){
				$returnArray["_array_item"][$i]["item_id"] = $item_id[$i] = base64_decode($post_data["_array_item"][$i]["item_id"]);//상품 아이디
				$returnArray["_array_item"][$i]["ProdPayCode"] = $ProdPayCode[$i] = base64_decode($post_data["_array_item"][$i]["ProdPayCode"]);//상품 급여코드
				$returnArray["_array_item"][$i]["order_send_dtl_id"] = $order_send_dtl_id[$i] = base64_decode($post_data["_array_item"][$i]["order_send_dtl_id"]);//상세주문코드
				$returnArray["_array_item"][$i]["item_opt_id"] = $item_opt_id[$i] = str_replace("u001e",chr(30),base64_decode($post_data["_array_item"][$i]["item_opt_id"]));//상품 옵션 아이디
				//$returnArray["_array_item"][$i]["item_opt_nm"] = $item_opt_nm[$i] = str_replace("*",chr(30),base64_decode($post_data["_array_item"][$i]["item_opt_nm"]));//상품 옵션 이름
				$returnArray["_array_item"][$i]["item_qty"] = $item_qty[$i] = base64_decode($post_data["_array_item"][$i]["item_qty"]);//상품 수량
				//상품 정보 일치 시 정상 처리, 상품 조회 실패 시 $error_stat = true 처리
				$sql_i = "select * from g5_shop_item where ProdPayCode='".$ProdPayCode[$i]."' and it_use='1'";
				$row_i = sql_fetch($sql_i);
				if($row_i["it_id"] == ""){//상품 조회 실패
					$error_stat = true;
					$code = "413";
					$msg = $ProdPayCode[$i]." 해당 제품 정보가 없습니다.";
					$data = $returnArray;
				}elseif($item_opt_id[$i] != ""){//옵션상품 조회
					$sql_o = "select * from g5_shop_item_option where it_id='".$row_i["it_id"]."' and (io_id='".$item_opt_id[$i]."' or io_id='".$item_opt_nm[$i]."')";
					$row_o = sql_fetch($sql_o);
					if($row_o["io_id"] == ""){
						$error_stat = true;
						$code = "414";
						$msg = $ProdPayCode[$i]."/".$item_opt_id[$i]." 해당 제품 옵션 정보가 없습니다.";
						$data = $returnArray;
					}
				}				
			}
		}
		
	
	//정상 진행 완료 시
		if($error_stat == false){
			$sql = "select count(*) as cnt from g5_shop_order_api where order_send_id ='{$order_send_id}' and mb_id='{$row_b['mb_id']}'";
			$result = sql_fetch($sql);
			if($result["cnt"]>0){//중복 주문 데이터
				$code = "415";
				$msg = "중복된 주문 입니다.";
				$data = $returnArray;
			}else{
			//1.5 주문 내역 DB에 등록 
			// 주문 DB는 cart(상품),order(주문내용) 테이블 copy, 필드 추가로 생성
			$od_id = "";//order_id
			$od_time = G5_TIME_YMDHIS;
			$sql_o = " insert g5_shop_order_api
			  set 
			  #od_id             = '{$order_send_id}',
				  order_send_id= '{$order_send_id}',
				  mb_id             = '{$row_b['mb_id']}',
				  od_name           = '{$row_b['mb_name']}',         
				  relation_code      = '$relation_code',
				  od_penId      = '$od_penId',
				  od_penNm      = '$od_penNm',
				  od_penLtmNum  = '$od_penLtmNum',
				  od_penRecGraNm  = '$od_penRecGraNm',
				  od_penTypeNm  = '$od_penTypeNm',
				  od_penExpiDtm  = '$od_penExpiDtm',
				  od_penAppEdDtm  = '$od_penAppEdDtm',
				  od_penGender  = '$od_penGender',
				  od_penZip1  = '$od_penZip1',
				  od_penZip2  = '$od_penZip2',
				  od_penAddr  = '$od_penAddr',
				  od_penAddr2  = '$od_penAddr2',
				  od_penConPnum = '$od_penConPnum',
				  od_zip1 = '$od_zip1',
				  od_zip2 = '$od_zip2',
				  od_addr = '$od_addr',
				  od_addr2 = '$od_addr2',
				  od_birth = '$od_birth',
				  od_b_id = '$od_b_id',
				  od_b_name         = '$od_b_name',
				  od_b_name2         = '$od_b_name2',
				  od_b_tel          = '$od_b_tel',
				  od_b_hp           = '$od_b_hp',
				  od_b_zip1         = '$od_b_zip1',
				  od_b_zip2         = '$od_b_zip2',
				  od_b_addr1        = '$od_b_addr1',
				  od_b_addr2        = '$od_b_addr2',
				  od_b_addr3        = '$od_b_addr3',
				  od_memo           = '$od_memo',              
				  od_time           = '$od_time',
				  od_status			= '승인대기',
				  od_cart_count     = '".count($post_data["_array_item"])."'
";//order insert 주문 내용
//$returnArray["insert_sql"] = $sql_o;
			sql_query($sql_o);
	//cart 등록		
			$comma = '';
			$sql_c = " INSERT INTO g5_shop_cart_api
					( od_id,order_send_id,order_send_id2, mb_id, it_id,ProdPayCode, it_name, io_id,ct_qty,ct_status,ct_time)
				  VALUES ";//cart insert 주문 상품
			for($i = 0; $i < count($post_data["_array_item"]); $i++ ){
				$sql_i = "select * from g5_shop_item where ProdPayCode='".$ProdPayCode[$i]."' and it_use='1'";
				$row_i = sql_fetch($sql_i);
				$it_id = $row_i["it_id"];//or $post_data["_array_item"][$i]["item_id"]
				$it_name = $row_i["it_name"];				
				$sql_c .= $comma."( '$order_send_id','$order_send_id','{$order_send_dtl_id[$i]}', '{$row_b['mb_id']}', '{$it_id}','{$ProdPayCode[$i]}' , '".addslashes($it_name)."','{$item_opt_id[$i]}','{$item_qty[$i]}','',now()  )";
				$comma = ' , ';		
			}
			sql_query($sql_c);
	


			//알림 DB 등록 - 알림창 생성 시 read 값 1로 변경 
			$sql2 = "select MAX(me_id) as max_id
				  from {$g5['memo_table']}";
			$result2 = sql_fetch($sql2);
			$max = $result2['max_id']+1;
			$sql_m = " insert into {$g5['memo_table']} 
			(me_id,me_recv_mb_id,me_send_mb_id,me_send_datetime,me_memo) 
			values ('{$max}','{$row_b['mb_id']}','admin_api',now(),'{$order_send_id}')";
			sql_query($sql_m);
			//전체 정보 정상 일 경우 상품 주문 과정 실행(보류)

			//주문 완료 후 계약서 자동 생성 실행(보류)

			api_log_write($order_send_id,$row_b['mb_id'], '1', "주문서 생성");
			$code = "200";
			$msg = "주문이 정상 등록 되었습니다.";
			$data = $returnArray;	
			}
		}
	}else{//누락 데이터가 있을 경우
		$empty_data .=($post_data["order_send_id"] == "")?"order_send_id,":"";
			$empty_data .=($post_data["order_business_id"] == "")?"order_business_id,":"";
			$empty_data .=($post_data["order_nm"] == "")?"order_nm,":"";
			$empty_data .=($post_data["order_tel"] == "")?"order_tel,":"";
			$empty_data .=($post_data["order_id"] == "")?"order_id,":"";
			$empty_data .=($post_data["delivery_tel"] == "")?"delivery_tel,":"";
			$empty_data .=($post_data["delivery_addr"] == "")?"delivery_addr,":"";
			$empty_data .=($post_data["delivery_addr2"] == "")?"delivery_addr2,":"";
			$empty_data .=($post_data["delivery_zip"] == "")?"ordedelivery_zipr_send_id,":"";
			$empty_data .=($post_data["delivery_nm"] == "")?"delivery_nm,":"";
			$empty_data .=($post_data["penNm"] == "")?"penNm,":"";
			$empty_data .=($post_data["penLtmNum"] == "")?"penLtmNum,":"";
			$empty_data .=($post_data["penGender"] == "")?"penGender,":"";
			$empty_data .=($post_data["_array_item"][0]["ProdPayCode"] == "")?"ProdPayCode,":"";
			$empty_data .=($post_data["_array_item"][0]["item_qty"] == "")?"item_qty,":"";
			$empty_data .=($post_data["_array_item"][0]["order_send_dtl_id"] == "")?"order_send_dtl_id,":"";
			$empty_data .=($post_data["_array_item"][0]["ProdPayCode"] == "")?"ProdPayCode,":"";
		$code = "411";
		$msg = $empty_data." 누락 데이터가 있습니다.";
		$data = $returnArray;
	}
		$result = array("success" => "true"
            , "code" => $code
            , "message" => $msg
            , "data" => $returnArray

            );
//============================================================================ 결제, 최소 시작 ============================================================================================== 
	}elseif($API_Div == "order_pen_confirm" ){//order_pen_confirm - 수급자 결제, 취소 확인 사업소 정보 필수로 들어와야함
		$returnArray["order_send_id"] = $order_send_id = base64_decode($post_data["order_send_id"]);//1.5 주문 id
		$returnArray["order_state"] = $order_state = base64_decode($post_data["order_state"]);//주문 상태
		$returnArray["order_business_id"] = $order_business_id = base64_decode($post_data["order_business_id"]);//사업자번호
		if($post_data["order_send_id"] != "" && $post_data["order_state"] != "" && $post_data["order_business_id"] != "" && is_array($post_data["_array_item"]) && $post_data["_array_item"][0]["order_send_dtl_id"] != ""){//누락 데이터가 없을 경우 - 주문ID,주문상태값, 상품 디테일 ID  
			$error_stat = false;

			//사업소정보 확인
			if($error_stat == false){
				$sql_b = "select * from g5_member where mb_giup_bnum='".$order_business_id."' and (mb_level='3' or mb_level='4') " ;
				$row_b = sql_fetch($sql_b);
				if($row_b["mb_id"] == ""){//사업소 조회 실패
					$error_stat = true;
					$code = "412";
					$msg = "해당 사업소 정보가 없습니다.";
					$data = $returnArray;
				}
			}
			$sql = "select od_status from g5_shop_order_api where order_send_id ='".$order_send_id."' and mb_id='{$row_b['mb_id']}'";
			$result = sql_fetch($sql);
			if($result["od_status"] == "주문취소" || $result["od_status"] == "주문완료" || $result["od_status"] == "출고완료" ){//주문처리정보가 있을 경우
				$error_stat = true;
				$code = "415";
				$msg = "이미 주문완료 또는 주문취소 된 주문입니다.";
				$data = $returnArray;
			}
			if($error_stat == false){
			//주문정보 조회
			$sql = "select count(*) as cnt from g5_shop_order_api where order_send_id ='".$order_send_id."' and mb_id='{$row_b['mb_id']}'";// and mb_id='{$row_b["mb_id"]}'
			$result = sql_fetch($sql);
			if($result["cnt"]>0){//주문정보가 있을 경우
				$sql0 = "select od_status from g5_shop_order_api where order_send_id ='".$order_send_id."' and mb_id='{$row_b['mb_id']}'";//주문완료 체크
				$row0 = sql_fetch($sql0);
				if($row0["od_status"] != "주문처리"){
					$error_stat = true;
					$code = "416";
					$msg = " 승인대기,결제완료,주문완료,출고완료 시에는 처리가 불가능 합니다.";
					$data = $returnArray;
				}else{
				//update 처리 결제 완료 시 g5_shop_cart_api 테이블 ct_status 필드 결제, 취소로 변경, 취소 시 전체 취소로 변경 g5_shop_order_api 테이블 od_cancel_time 필드 시간 등록 
				if($order_state == "Y"){//주문 결제
					if($row0["od_status"] == "결제완료"){
						$error_stat = true;
						$code = "416";
						$msg = " 결제완료 처리 된 주문입니다. ";
						$data = $returnArray;
					}else{
					
					//결제를 제외한 나머지 상품 반려/[수급자취소] 기능 필요
					$confirm_items = array();
					//$log_txt .= count($post_data["_array_item"])."\r\n";
					//$log_txt .= is_array($post_data["_array_item"])."\r\n";
					if(is_array($post_data["_array_item"]) && $post_data["_array_item"][0]["order_send_dtl_id"] != ""){//주문 상품별로 결제, 취소 일경우 && $post_data["_array_item"][0]["ProdPayCode"] != "" && $post_data["_array_item"][0]["order_send_dtl_id"] != ""
						
						for($i = 0; $i < count($post_data["_array_item"]); $i++ ){
							$returnArray["_array_item"][$i]["item_memo"] = $item_memo[$i] = base64_decode($post_data["_array_item"][$i]["item_memo"]);//상품 아이디
							$returnArray["_array_item"][$i]["ProdPayCode"] = $ProdPayCode[$i] = base64_decode($post_data["_array_item"][$i]["ProdPayCode"]);//상품 급여코드
							$returnArray["_array_item"][$i]["order_send_dtl_id"] = $order_send_dtl_id[$i] = base64_decode($post_data["_array_item"][$i]["order_send_dtl_id"]);//상세주문코드
							$returnArray["_array_item"][$i]["item_state"] = $item_state[$i] = base64_decode($post_data["_array_item"][$i]["item_state"]);//주문상태
							//if($item_state[$i] == "Y"){
								$confirm_items[$i] = $order_send_dtl_id[$i];
							//}
						}
						
					}
					$sql01 = "select order_send_id2,ProdPayCode from g5_shop_cart_api where mb_id='{$row_b['mb_id']}' and order_send_id ='".$order_send_id."' and ct_status='승인'";
					$result = sql_query($sql01);
					while($row01 = sql_fetch_array($result)){
						$up_sql = "";
						if(in_array($row01["order_send_id2"], $confirm_items)){
							//그대로 승인
						}else{
							$up_sql = "update g5_shop_cart_api set ct_status='반려',ct_memo='수급자 취소"."' where order_send_id2='".$row01["order_send_id2"]."' and ProdPayCode ='".$row01["ProdPayCode"]."'"; 
							sql_query($up_sql);
							api_log_write($order_send_id,$row_b['mb_id'], '3', "수급자 상품 취소[급여코드:".$row01["ProdPayCode"]."]");
						}
						//$log_txt .= stripslashes(json_encode($confirm_items, JSON_UNESCAPED_UNICODE))."|".$row01["order_send_id2"]."|".in_array($row01["order_send_id2"], $confirm_items)." \r\n";
						//$log_txt .= $sql01." \r\n";
					}					
					$od_status = "결제완료";
					$sql1 = "update g5_shop_order_api set od_status='결제완료' where order_send_id ='".$order_send_id."' and mb_id='{$row_b['mb_id']}'";//g5_shop_order_api 취소 처리 and mb_id='{$row_b["mb_id"]}'
					sql_query($sql1);					
					
					api_log_write($order_send_id,$row_b['mb_id'], '1', "수급자 결제 완료");
					}
				}else{//주문 취소
					
					if($row0["od_status"] == "주문처리" || $row0["od_status"] == "결제완료"){//주문 취소는 여기까지만 가능
						$cancle_cnt = 0;
						if(is_array($post_data["_array_item"]) && $post_data["_array_item"][0]["ProdPayCode"] != "" && $post_data["_array_item"][0]["order_send_dtl_id"] != ""){//주문 상품별로 결제, 취소 일경우
							
							for($i = 0; $i < count($post_data["_array_item"]); $i++ ){
								$returnArray["_array_item"][$i]["item_memo"] = $item_memo[$i] = base64_decode($post_data["_array_item"][$i]["item_memo"]);//상품 아이디
								$returnArray["_array_item"][$i]["ProdPayCode"] = $ProdPayCode[$i] = base64_decode($post_data["_array_item"][$i]["ProdPayCode"]);//상품 급여코드
								$returnArray["_array_item"][$i]["order_send_dtl_id"] = $order_send_dtl_id[$i] = base64_decode($post_data["_array_item"][$i]["order_send_dtl_id"]);//상세주문코드
								$returnArray["_array_item"][$i]["item_state"] = $item_state[$i] = base64_decode($post_data["_array_item"][$i]["item_state"]);//주문상태
								//if($item_state[$i] == "N"){
									$cancle_cnt++;
									$up_sql = "update g5_shop_cart_api set ct_status='반려',ct_memo='[수급자취소]".$item_memo[$i]."' where order_send_id2='".$order_send_dtl_id[$i]."' and ProdPayCode='".$ProdPayCode[$i]."'"; 
									sql_query($up_sql);
									api_log_write($order_send_id,$row_b['mb_id'], '3', "수급자 상품 취소[급여코드:".$ProdPayCode[$i]."][사유:".$item_memo[$i]."]");
								//}
							}
						}
						//$sql2 = "select count(*) as ct_cnt from g5_shop_cart_api where order_send_id ='".$order_send_id."' and mb_id='{$row_b['mb_id']}'";
						//$row2 = sql_fetch($sql2);
						//if($row2["ct_cnt"] == $cancle_cnt){//주문수량과 취소 수량이 같을 경우
							$od_status = "주문취소";
							$sql1 = "update g5_shop_order_api set od_cancel_time=now(),od_status='주문취소',od_cancel_reason='수급자 주문 취소' where order_send_id ='".$order_send_id."' and mb_id='{$row_b['mb_id']}'";//g5_shop_order_api 취소 처리 and mb_id='{$row_b["mb_id"]}'
							sql_query($sql1);
						
							api_log_write($order_send_id, $row_b['mb_id'], '1', "수급자 주문 취소");
						//}
					}else{// 여기부터는 주문 취소 불가능
						$error_stat = true;
						$code = "416";
						$msg = " 주문 취소가 불가능 합니다. 관리자에 문의 하세요.";
						$data = $returnArray;
					}
				}
				}
				//sql_query($sql);
				//알림 DB 등록 - 알림창 생성 시 read 값 1로 변경 내용 검토 필요
				if($error_stat == false){
					$sql2 = "select MAX(me_id) as max_id
						  from {$g5['memo_table']}";
					$result2 = sql_fetch($sql2);
					$max = $result2['max_id']+1;
					$sql_m = " insert into {$g5['memo_table']} 
					(me_id,me_recv_mb_id,me_send_mb_id,me_send_datetime,me_memo) 
					values ('{$max}','{$row_b['mb_id']}','admin_api',now(),'{$order_send_id}')";
					sql_query($sql_m);
					$code = "200";
					$msg = $od_status." 주문 상태가 정상 접수 되었습니다.";
					$data = $returnArray;	
				}
			}else{//주문정보가 없을 경우
				$code = "412";
				$msg = "해당 주문 정보가 없습니다.";
				$data = $returnArray;
			}
			}
		}else{//누락 데이터가 있을 경우
			$empty_data .=($post_data["order_send_id"] == "")?"order_send_id,":"";
			$empty_data .=($post_data["order_business_id"] == "")?"order_business_id,":"";
			$empty_data .=($post_data["_array_item"][0]["order_send_dtl_id"] == "")?"order_send_dtl_id,":"";

			$code = "411";
			$msg = $empty_data." 누락 데이터가 있습니다.";
			$data = $returnArray;
		}
		$result = array("success" => "true"
            , "code" => $code
            , "message" => $msg
            , "data" => $returnArray

            );
	//============================================================================ 상품 조회 시작 ============================================================================================== 
	}elseif($API_Div == "item_info" ){//item_info - 상품 조회
		if(is_array($post_data["_array_item"]) && $post_data["_array_item"][0]["ProdPayCode"] != ""){//누락 데이터가 없을 경우
			//상품 조회
			$error_stat = false;
			for($i = 0; $i < count($post_data["_array_item"]); $i++ ){
				$returnArray["_array_item"][$i]["item_id"] = $item_id[$i] = base64_decode($post_data["_array_item"][$i]["item_id"]);//상품 아이디
				$returnArray["_array_item"][$i]["ProdPayCode"] = $ProdPayCode[$i] = base64_decode($post_data["_array_item"][$i]["ProdPayCode"]);//상품 급여코드
				$returnArray["_array_item"][$i]["item_opt_id"] = $item_opt_id[$i] = base64_decode($post_data["_array_item"][$i]["item_opt_id"]);//상품 옵션 아이디
				$returnArray["_array_item"][$i]["item_opt_nm"] = $item_opt_nm[$i] = base64_decode($post_data["_array_item"][$i]["item_opt_nm"]);//상품 옵션 명
				//상품 정보 일치 시 정상 처리, 상품 조회 실패 시 $error_stat = true 처리
				$sql_i = "select * from g5_shop_item where ProdPayCode='".$ProdPayCode[$i]."'";
				$row_i = sql_fetch($sql_i);//상품 기본 정보
				if($row_i["it_id"] == ""){//상품 조회 실패
					$error_stat = true;
					$code = "413";
					$msg = $ProdPayCode[$i]." 해당 제품 정보가 없습니다.";
					$data = $returnArray;
				}else{
					//if($item_opt_id[$i] != ""){//옵션상품 조회
						$returnArray["_array_item"][$i]["item_opt_id"] = array();
						$_array_item[$i]["item_opt_id"] = array(); 
						$sql_o = "select * from g5_shop_item_option where it_id='".$row_i["it_id"]."'";
						$result = sql_query($sql_o);
						$j = 0;
						while($row_o = sql_fetch_array($result)){
						/*if($row_o["io_id"] == ""){
							$error_stat = true;
							$code = "414";
							$msg = $ProdPayCode[$i]."/".$item_opt_id[$i]." 해당 제품 옵션 정보가 없습니다.";
							$data = $returnArray;
						}else{//옵션별로 상품 수량, 품절, 판매 유무 체크 하는지 재 확인 필요

						}*/
							$returnArray["_array_item"][$i]["item_opt_id"][$j]["io_type"] = $row_o["io_type"];
								$_array_item[$i]["item_opt_id"][$j]["io_type"] = base64_encode($row_o["io_type"]);//상품옵션type 선택옵션:0,추가옵션:1
							$returnArray["_array_item"][$i]["item_opt_id"][$j]["io_id"] = $row_o["io_id"];
								$_array_item[$i]["item_opt_id"][$j]["io_id"] = base64_encode($row_o["io_id"]);//상품옵션ID
							$returnArray["_array_item"][$i]["item_opt_id"][$j]["io_qty"] = $row_o["io_stock_qty"];
								$_array_item[$i]["item_opt_id"][$j]["io_qty"] = base64_encode($row_o["io_stock_qty"]);//상품옵션ID
							$j++;
						}
					//}
					$it_type = "";
					if($row_i["it_type1"] == 1){//일시품절
						$it_type = "일시품절";
					}elseif($row_i["it_type2"] == 1){//일부옵션품절
						$it_type = "일부옵션품절";
					}elseif($row_i["it_type10"] == 1){//품절
						$it_type = "품절";
					}
					$returnArray["_array_item"][$i]["item_id"] = $row_i["it_id"];
						$_array_item[$i]["item_id"] = base64_encode($row_i["it_id"]);//상품 아이디
					$returnArray["_array_item"][$i]["item_nm"] = $row_i["it_name"];
						$_array_item[$i]["item_nm"] = base64_encode($row_i["it_name"]);//상품 이름
					$returnArray["_array_item"][$i]["ProdPayCode"] = base64_decode($post_data["_array_item"][$i]["ProdPayCode"]);
						$_array_item[$i]["ProdPayCode"] = ($post_data["_array_item"][$i]["ProdPayCode"]);//상품 급여코드
					//$_array_item[$i]["item_opt_id"] = $sql_o;//상품옵션ID
					//$_array_item[$i]["item_opt_nm"] = ($post_data["_array_item"][$i]["item_opt_nm"]);//상품 옵션 이름?
					$returnArray["_array_item"][$i]["item_qty"] = $row_i["it_stock_qty"];
						$_array_item[$i]["item_qty"] = base64_encode($row_i["it_stock_qty"]);//상품수량
					$returnArray["_array_item"][$i]["item_soldout"] = $row_i["it_soldout"];
						$_array_item[$i]["item_soldout"] = base64_encode($row_i["it_soldout"]);//상품수량
					$returnArray["_array_item"][$i]["item_use"] = $row_i["it_use"];
						$_array_item[$i]["item_use"] = base64_encode($row_i["it_use"]);//상품수량
					$returnArray["_array_item"][$i]["item_opt_tag"] = $it_type;
						$_array_item[$i]["item_opt_tag"] = base64_encode($it_type);//상품태그
				}				
			}
			if($error_stat == false){
				$code = "200";
				$msg = "상품 정보 조회가 완료 되었습니다.";
			}
			
		}else{//누락 데이터가 있을 경우
			$code = "411";
			$msg = "누락 데이터가 있습니다.";
			$data = $returnArray;
		}
		
		$result = array("success" => "true"
            , "code" => $code
            , "message" => $msg
			, "_array_item" => $_array_item
            , "data" => $returnArray			
            );
	}

}else{//apikey가 틀릴경우
	 $result=array("success" => "true"
              , "code" => "401"
              , "message" => "인증키를 확인하세요."
              , "data" => array ()
              , "count" => 0
              );
}
$log_txt .= "[".date("Y-m-d H:i:s")."]"." 응답 - ".$API_Div." \r\n";

$log_txt .= stripslashes(json_encode($result, JSON_UNESCAPED_UNICODE))."\r\n";
$log_txt .= "====== API 받기 끝 ============================================================= \r\n";
fwrite($log_file, $log_txt . "\r\n");
fclose($log_file);

echo stripslashes(json_encode($result, JSON_UNESCAPED_UNICODE));

?>