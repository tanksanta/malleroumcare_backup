<?php
// $sub_menu = '400400';
include_once('./_common.php');

# 회원검사
if(!$member["mb_id"])
  json_response(500, '접근 권한이 없습니다.');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$file = $_FILES['files']['tmp_name'];
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$sheetData = $spreadsheet->getSheet(0)->toArray(null, true, true, true);

function parse_birth($ymd) {
  $date = DateTime::createFromFormat('Ymd', $ymd);

  if(!$date) return '';

  return $date->format('Y-m-d');
}
if($sheetData){// 엑셀 필드 확인용으로 사용
	if(trim(addslashes($sheetData[1]['F'])) != "요청사항" || trim(addslashes($sheetData[1]['G'])) != "수령인 이름" || trim(addslashes($sheetData[1]['H'])) != "연락처" || trim(addslashes($sheetData[1]['I'])) != "주소"){
		json_response(500, '일괄등록 업로드 파일 입력 형식이 다릅니다.');
		exit;
	}

	
}

if($sheetData) {
	$od_b_name = "";//수령인
	$od_b_tel = "";//연락처
	$od_b_addr1 = "";//배송지 주소
	$_POST['it_id'] = array();//상품ID
	$_POST['io_id'] = array();//상품옵션
	$_POST['qty'] = array();//상품수량
	$_POST['it_price'] = array();//상품가격
	$memo	= array();//요청 사항

    $num_rows = $spreadsheet->getSheet(0)->getHighestDataRow('A');

	$msg = "";

	for ($ii = 2; $ii <= ($num_rows); $ii++) {
		$mb_id =  trim(addslashes($sheetData[$ii]['A'])); 
		$od_member = get_member($mb_id);
		if (!$od_member) {
		  $msg .= $ii."열 ".$mb_id."는 없는 사업소입니다.\n";
		  //json_response(500, $ii."열 ".$mb_id.'는 없는 사업소입니다.');
		}
		// 상품정보
		if($msg == ""){
			if(trim(addslashes($sheetData[$ii]['B'])) == ""){
				$msg .= $ii."열 상품 정보를 입력해 주세요.\n";
			}
		}
		if($msg == ""){  
			$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '".trim(addslashes($sheetData[$ii]['B']))."' and it_use='1' and it_soldout='0' and prodSupYn='Y'";//상품판매,품절아님,유통 상품만 주문 가능
			$it = sql_fetch($sql);
			if($it["it_name"] == ""){
				$msg .= $ii."열 ".trim(addslashes($sheetData[$ii]['B']))."의 상품이 없습니다.\n";
			}
		}

		// 상품옵션정보
		if($msg == ""){
			if(trim(addslashes($sheetData[$ii]['C'])) != ""){
				$sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '".trim(addslashes($sheetData[$ii]['B']))."' and io_use = 1 and io_sold_out='0' and io_id='".str_replace('>',chr(30),preg_replace(G5_OPTION_ID_FILTER, '', trim(addslashes($sheetData[$ii]['C']))))."' ";//상품사용, 품절아닌 옵션상품만 주문 가능
				$io = sql_fetch($sql);
				if($io["io_id"] == ""){
					$msg .= $ii."열 ".preg_replace(G5_OPTION_ID_FILTER, '', trim(addslashes($sheetData[$ii]['C'])))."의 상품옵션이 없습니다.\n";			
				}
			}else{//상품 옵션을 선택해야 하는 상품일 경우
				$sql = " select count(it_id) as cnt from {$g5['g5_shop_item_option_table']} where it_id = '".trim(addslashes($sheetData[$ii]['B']))."'";
				$io = sql_fetch($sql);
				if($io["cnt"] > 0 ){
					$msg .= $ii."열 ".trim(addslashes($sheetData[$ii]['B']))."의 옵션 정보를 입력해 주세요.\n";
				}				
			}			
		}

		//수량
		if($msg == ""){
			if(trim(addslashes($sheetData[$ii]['D'])) == 0 || trim(addslashes($sheetData[$ii]['D'])) ==""){
				$msg .= $ii."열 ".$io_value."의 수량을 입력해 주세요.\n";
			}
		}

		//재고
		if($msg == ""){
			$tmp_ct_qty = trim(addslashes($sheetData[$ii]['D']));
			if(!trim(addslashes($sheetData[$ii]['C'])))
				$tmp_it_stock_qty = get_it_stock_qty(trim(addslashes($sheetData[$ii]['B'])));
			else
				$tmp_it_stock_qty = get_option_stock_qty(trim(addslashes($sheetData[$ii]['B'])), str_replace('>',chr(30),preg_replace(G5_OPTION_ID_FILTER, '', trim(addslashes($sheetData[$ii]['C'])))), '0');

			if ($tmp_ct_qty > $tmp_it_stock_qty){
				$msg .= $ii."열 ".$io_value."의 재고수량이 부족합니다.현재 재고수량 : " . number_format($tmp_it_stock_qty) . " 개\n";
			}
		}

		//단가
		if($msg == ""){
			if(trim(addslashes($sheetData[$ii]['E'])) == 0 || trim(addslashes($sheetData[$ii]['E'])) ==""){
				$msg .= $ii."열 의 단가를 입력해 주세요.\n";
			}
		}
		
		//배송정보
		if($msg == ""){
			if(trim(addslashes($sheetData[$ii]['G'])) == "" || trim(addslashes($sheetData[$ii]['H'])) == "" || trim(addslashes($sheetData[$ii]['I'])) == ""){
				$msg .= $ii."열 배송정보를 빠짐 없이 입력해 주세요.\n";
			}
		}
	}
	
	if($msg != ""){
		json_response(500, $msg);
		exit;
	}

	for ($i2 = 2; $i2 <= ($num_rows); $i2++) {
		$mb_id =  trim(addslashes($sheetData[$i2]['A'])); 
		$od_member = get_member($mb_id);
		
		$_POST['it_id'][] =  trim(addslashes($sheetData[$i2]['B']));//상품ID
		$_POST['io_id'][] = str_replace('>',chr(30),preg_replace(G5_OPTION_ID_FILTER, '', trim(addslashes($sheetData[$i2]['C']))));//상품옵션
		$_POST['qty'][] = trim(addslashes($sheetData[$i2]['D']));//상품수량
		$_POST['it_price'][] = trim(addslashes(str_replace(",","",$sheetData[$i2]['E'])));//상품가격
		$memo[]	= trim(addslashes($sheetData[$i2]['F']));//요청 사항

	if(($od_b_name != trim(addslashes($sheetData[$i2]['G'])) && $i2>2) || $i2 == $num_rows){//수령인 정보가 다를 때만 오더 생성
	
		if($i2 != $num_rows){
		//마지막 배열값 저장 =========================
			$it_id_last = end($_POST['it_id']);
			$io_id_last = end($_POST['io_id']);
			$qty_last = end($_POST['qty']);
			$it_price_last = end($_POST['it_price']);	
			$memo_last = end($memo);
		
		//마지막 배열 제거 ===========================
			array_pop($_POST['it_id']);
			array_pop($_POST['io_id']);
			array_pop($_POST['qty']);
			array_pop($_POST['it_price']);	
			array_pop($memo);	
		//=========================================	
		}
		$od_id = get_uniqid();
		$so_nb = get_uniqid_so_nb();
		$od_pwd = $member['mb_password'];
		$od_status = '작성';
		
		$sql = " insert {$g5['g5_shop_order_table']}
            set od_id             = '$od_id',
                mb_id             = '$mb_id',
                od_name = '{$od_member['mb_name']}',
                od_email = '{$od_member['mb_email']}',
                od_tel = '{$od_member['mb_tel']}',
                od_hp = '{$od_member['mb_hp']}',
                od_zip1 = '{$od_member['mb_zip1']}',
                od_zip2 = '{$od_member['mb_zip2']}',
                od_addr1 = '{$od_member['mb_addr1']}',
                od_addr2 = '{$od_member['mb_addr2']}',
                od_addr3 = '{$od_member['mb_addr3']}',
                od_addr_jibeon = '{$od_member['mb_addr_jibeon']}',
                od_b_name = '{$od_b_name}',
                od_b_tel = '{$od_b_tel}',
                od_b_hp = '',
                od_b_zip1 = '',
                od_b_zip2 = '',
                od_b_addr1 = '{$od_b_addr1}',
                od_b_addr2 = '',
                od_b_addr3 = '',
                od_b_addr_jibeon = '{$mb['mb_addr_jibeon']}',
                od_pwd            = '',
                od_time           = '".G5_TIME_YMDHIS."',
                od_ip             = '$REMOTE_ADDR',
                od_send_cost      = '" . $_sum_delivery_cost . "',
                od_settle_case    = '월 마감 정산',
                od_status         = '{$od_status}',
                od_memo           = '',
                od_shop_memo      = '',
                od_mod_history    = '',
                od_cash           = '0',
                od_cash_no        = '',
                od_cash_info      = '',
                od_writer         = '{$member['mb_id']}',
                od_add_admin      = '1',
                so_nb             = '{$so_nb}'
                ";
		sql_query($sql);

		set_order_admin_log($od_id, '주문서 관리자 등록');

		$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
		$od = sql_fetch($sql);
		$od_member = get_member($od['mb_id']);
    
		
		
		$insert_ids = array();
		$ct_discount = (int)$ct_discount ?: 0;

		$it_ids = $_POST['it_id'];

		//관리자가 등록한 코드
		$ct_admin_new=[];
		for($i=0; $i<count($it_ids); $i++) {
		  $it_id = $it_ids[$i];

		  if (!$it_id) {
			continue;
		  }

		  // 상품정보
		  $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
		  $it = sql_fetch($sql);
			//json_response(500, $sql);//디버깅용
		  if($it['it_sc_type'] == 1)
			$ct_send_cost = 2; // 무료
		  else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
			$ct_send_cost = 1; // 착불
		  else
			$ct_send_cost = 0;

		  // 옵션정보를 얻어서 배열에 저장
		  $opt_list = array();
		  $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
		  $result = sql_query($sql);
		  $lst_count = 0;
		  for($k=0; $row=sql_fetch_array($result); $k++) {
			$opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
			$opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
			$opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
			$opt_list[$row['io_type']][$row['io_id']]['price_partner'] = $row['io_price_partner'];
			$opt_list[$row['io_type']][$row['io_id']]['price_dealer'] = $row['io_price_dealer'];
			$opt_list[$row['io_type']][$row['io_id']]['price_dealer2'] = $row['io_price_dealer2'];
			$opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
			$opt_list[$row['io_type']][$row['io_id']]['io_thezone'] = $row['io_thezone'];

			// 선택옵션 개수
			if(!$row['io_type'])
			  $lst_count++;
		  }

		  // if (!$uid) {
			$uid = uuidv4();
		  // }

		  $comma = '';
		  $sql = " INSERT INTO {$g5['g5_shop_cart_table']}
			( od_id,
			  mb_id,
			  it_id,
			  it_name,
			  it_sc_type,
			  it_sc_method,
			  it_sc_price,
			  it_sc_minimum,
			  it_sc_qty,
			  ct_status,
			  ct_price,
			  ct_point,
			  ct_point_use,
			  ct_stock_use,
			  ct_option,
			  ct_qty,
			  ct_notax,
			  io_id,
			  io_type,
			  io_price,
			  ct_time,
			  ct_ip,
			  ct_send_cost,
			  ct_direct,
			  ct_select,
			  ct_select_time,
			  pt_it,
			  pt_msg1,
			  pt_msg2,
			  pt_msg3,
			  ct_history,
			  ct_discount,
			  ct_price_type,
			  ct_uid,
			  io_thezone,
			  ct_admin_new,
			  ct_delivery_cnt,
			  ct_delivery_price,
			  ct_delivery_company,
			  ct_is_direct_delivery,
			  ct_direct_delivery_partner,
			  ct_direct_delivery_price,
			  prodMemo,
			  ordLendStrDtm,
			  ordLendEndDtm,
			  prodSupYn,
			  ct_pen_id,
			  ct_warehouse
			)
		  VALUES ";

		  $ct_select = 1;
		  $ct_select_time = G5_TIME_YMDHIS;
		  $sw_direct = 0;
		  
		  for($k=0;$k< 1;$k++) {
			$io_id = preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$i]);
			$io_type = preg_replace('#[^01]#', '', 0);
			// $io_value = $_POST['io_value'][$it_id][$k];

			$io_value = '';
			if ($io_id) {
			  $it_option_subjects = explode(',', $it['it_option_subject']);
			  $io_ids = explode(chr(30), $io_id);
			  for($g = 0; $g< count($io_ids); $g++) {
				if ($g > 0) {
				  $io_value .= ' / ';
				}
				$io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
			  }
			}

			$pt_msg1 = get_text($_POST['pt_msg1'][$it_id][$k]);
			$pt_msg2 = get_text($_POST['pt_msg2'][$it_id][$k]);
			$pt_msg3 = get_text($_POST['pt_msg3'][$it_id][$k]);

			$io_price = $chk_dealer_price && $opt_list[$io_type][$io_id]['price_dealer'] ? $opt_list[$io_type][$io_id]['price_dealer'] : $opt_list[$io_type][$io_id]['price'];
			$io_price = $chk_dealer2_price && $opt_list[$io_type][$io_id]['price_dealer2'] ? $opt_list[$io_type][$io_id]['price_dealer2'] : $opt_list[$io_type][$io_id]['price'];
			$io_price = $chk_partner_price && $opt_list[$io_type][$io_id]['price_partner'] ? $opt_list[$io_type][$io_id]['price_partner'] : $io_price;
			// 임의 상품 옵션 가격 적용
			// $io_price = $chk_custom_price ? $_POST['io_price'][$it_id][$k] : $opt_list[$io_type][$io_id]['price'];
			// $io_price = (int)$_POST['it_price'][$i];
			$io_price = 0;
			$io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];
			
			$ct_qty = $_POST['qty'][$i];
			$ct_qty = (int)preg_replace("/[^\d]/","", $ct_qty);
			// $it_price = $it['it_price'];
			$it_price = $_POST['it_price'][$i];
			$it_price = (int)preg_replace("/[^\d]/","", $it_price);


			$sql2 = " select ct_id, io_type, ct_qty
					  from {$g5['g5_shop_cart_table']}
					  where od_id = '$od_id'
						and it_id = '$it_id'
						and io_id = '$io_id'
						and pt_msg1 = '{$pt_msg1}'
						and pt_msg2 = '{$pt_msg2}'
						and pt_msg3 = '{$pt_msg3}'
						and ct_status = '쇼핑' ";
			$row2 = sql_fetch($sql2);
			if($row2['ct_id']) {
			  
			  $sql3 = " update {$g5['g5_shop_cart_table']}
						  set ct_qty = ct_qty + '$ct_qty',
						  ct_uid = '$uid'
						  where ct_id = '{$row2['ct_id']}' ";
			  sql_query($sql3);
			  continue;
			}

			$io_value = sql_real_escape_string(strip_tags($io_value));
			$remote_addr = get_real_client_ip();

			$add_ct_discount = $i == 0 && $k == 0 ? $ct_discount : 0;

			$point = 0;

			if($it['it_delivery_min_cnt']) {
			  //박스 개수 큰것 +작은것 - >ceil
			  $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($ct_qty / $it['it_delivery_cnt']) : 0;
			  //큰박스 floor 한 가격을 담음
			  $ct_delivery_bigbox = $it['it_delivery_cnt'] ? floor($ct_qty / $it['it_delivery_cnt']) : 0;
			  $ct_delivery_price = $it['it_delivery_cnt'] ? ($ct_delivery_bigbox * $it['it_delivery_price']) : 0;
			  //나머지
			  $remainder = $ct_qty % $it['it_delivery_cnt'];
			  //나머지가 있으면
			  if($remainder) {
				//나머지가 최소수량보다 작으면
				if($remainder <= $it['it_delivery_min_cnt']) {
				  //작은 박스 가격 더해줌
				  $ct_delivery_price = $ct_delivery_price + $it['it_delivery_min_price'];
				} else {
				  //큰 박스 가격 더해줌
				  $ct_delivery_price = $ct_delivery_price + $it['it_delivery_price'];
				}
			  }
			} else {
			  //없으면 큰박스로만 진행
			  $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($ct_qty / $it['it_delivery_cnt']) : 0;
			  $ct_delivery_price = $ct_delivery_cnt * $it['it_delivery_price'];
			}

			$ct_delivery_company = 'ilogen';

			$io_value = $io_value ? $io_value : addslashes($it['it_name']);
			$ct_admin_new_v = GenerateString(15);
			array_push($ct_admin_new,$ct_admin_new_v);

			// 대여기간
			$sqlOrdLendStrDtm = 'NULL';
			$sqlOrdLendEndDtm = 'NULL';
			if ($ordLendStartDtm && $ordLendEndDtm) {
			  $sqlOrdLendStrDtm = "'{$ordLendStartDtm}'";
			  $sqlOrdLendEndDtm = "'{$ordLendEndDtm}'";
			}

			// 수급자 여부
			$sql_ct_pen_id = 'NULL';
			if($od['od_penId']) {
			  $sql_ct_pen_id = "'{$od['od_penId']}'";
			}

			// 출하창고
			$ct_warehouse = '검단창고';
			if($it['it_default_warehouse']) {
			  $ct_warehouse = $it['it_default_warehouse'];
			}
			if($it['it_direct_delivery_partner'] != ""){//직배송 파트너가 있을 경우 파트너 계정에 설정되어 있는 출하창고 등록
				$partner = get_member($it['it_direct_delivery_partner']);
				$ct_warehouse = ($partner["mb_partner_default_warehouse"] != "" )? $partner["mb_partner_default_warehouse"] : $ct_warehouse;
			}

			// 비유통상품 가격
			if($it['prodSupYn'] == 'N') {
			  $it_price = 0;
			}

			$insert_sql = $sql . "
			(
			  '$od_id',
			  '{$od['mb_id']}',
			  '{$it['it_id']}',
			  '".addslashes($it['it_name'])."',
			  '{$it['it_sc_type']}',
			  '{$it['it_sc_method']}',
			  '{$it['it_sc_price']}',
			  '{$it['it_sc_minimum']}',
			  '{$it['it_sc_qty']}',
			  '작성',
			  '{$it_price}',
			  '$point',
			  '0',
			  '0',
			  '$io_value',
			  '$ct_qty',
			  '{$it['it_notax']}',
			  '$io_id',
			  '$io_type',
			  '$io_price',
			  '".G5_TIME_YMDHIS."',
			  '$remote_addr',
			  '$ct_send_cost',
			  '$sw_direct',
			  '$ct_select',
			  '$ct_select_time',
			  '{$it['pt_it']}',
			  '$pt_msg1',
			  '$pt_msg2',
			  '$pt_msg3',
			  '',
			  '$add_ct_discount',
			  '0',
			  '$uid',
			  '$io_thezone',
			  '$ct_admin_new_v',
			  '$ct_delivery_cnt',
			  '$ct_delivery_price',
			  '$ct_delivery_company',
			  '{$it['it_is_direct_delivery']}',
			  '{$it['it_direct_delivery_partner']}',
			  '{$it['it_direct_delivery_price']}',
			  '$memo[$i]',
			  $sqlOrdLendStrDtm,
			  $sqlOrdLendEndDtm,
			  '{$it['prodSupYn']}',
			  $sql_ct_pen_id,
			  '$ct_warehouse'
			)";

			sql_query($insert_sql);

			$insert_ids[] = sql_insert_id();
			$ct_count++;

			set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ', ' . $io_id .' 상품 추가');
		  }
		}

		// 주문 금액 계산
		samhwa_order_calc($od_id);

		$sql = "INSERT INTO g5_shop_order_cart_memo SET
					od_id = '{$od_id}' ,
					ctm_uid = '{$uid}',
					ctm_memo = '{$memo[$i]}'
				";
		sql_query($sql);

		// 상품수 수정
		$sql = " select COUNT(distinct it_id, ct_uid) as cart_count, count(*) as delivery_count
					from {$g5['g5_shop_cart_table']} where od_id = '$od_id'  ";
		$row = sql_fetch($sql);



		// ================================================================================================
		// 23.02.02 : 서원 - 관리자 주문에 대한 배송비 정책 적용 부분 시작
		// ================================================================================================

		// 배송비 합계
		$_sum_delivery_cost = 0;
		// 상품 가격 합계
		$_sum_it_price = 0;
		// 배송정책 타입0 수량
		$_sum_sc_type0 = 0;


		// 23.02.02 : 서원 - POST받은 상품 정보에서 배송비 합산을 위한 계산 Loop
		foreach ( $_POST['it_id'] as $key => $val ) {


		  if( !$val ) continue; // 상품 아이디 값이 없을 경우 continue

		  
		  // 배송비 정책 라이브러리 조회
		  $_result = "";
		  $_result = get_item_delivery_cost( $val, $_POST['qty'][$key], (int)preg_replace("/[^\d]/","", $_POST['it_price'][$key]) );
		  

		  // 23.02.02 : 서원 - 배송비 타입이 0,1,2,3에 속할 경우 전체 금액의 무료 배송을 결정 하기 위해 별도 계산.
		  if( $_result['sc_type'] == 0 || $_result['sc_type'] == 1 || $_result['sc_type'] == 2 || $_result['sc_type'] == 3 ) {
			// 배송비 정책 타입이 '0'0일 경우 해당 상품 카운트 합산
			if( $_result['sc_type'] == 0 ) { $_sum_sc_type0 += 1; }
			// 쇼핑몰 기본 배송비정책에 의한 금액 산정을 위한 합산
			$_sum_it_price += ( $_POST['qty'][$key] * (int)preg_replace("/[^\d]/","", $_POST['it_price'][$key]) );
		  } else {
			// 위 4가지 조건 배송비 정책 타입 이외 모두 배송비 합산.
			$_sum_delivery_cost += $_result['cost'];
		  }

		// Loop 종료
		}


		// 쇼핑몰 기본 배송비 정책 가져와서 Array 처리
		$send_cost_limit =  explode(';', $default['de_send_cost_limit'] );
		$send_cost_list = explode(';', $default['de_send_cost_list'] );

		// 상품중 배송비 정책 타입이 '0'이상이고, 금액이 발생되었을 경우 기본 정책 정책 루틴 적용. 
		if( ($_sum_sc_type0 > 0) && ($_sum_it_price > 0) ) {
		  for( $i=0; $i < COUNT($send_cost_limit); $i++) {
			if($_sum_it_price < $send_cost_limit[$i]) { 
			  $_sum_delivery_cost += $send_cost_list[$i]; break;
			}
		  }
		}

		// ================================================================================================
		// 23.02.02 : 서원 - 관리자 주문에 대한 배송비 정책 적용 부분 종료
		// ================================================================================================


		$od_send_cost = $_sum_delivery_cost;
		sql_query(" UPDATE {$g5['g5_shop_order_table']} 
					SET od_cart_count = '{$row['cart_count']}', 
						od_delivery_total = '{$row['delivery_count']}',
						od_send_cost = '{$od_send_cost}'
					WHERE od_id = '$od_id' ");


		if (!$od['od_penId']) {
		  $where_ct_admin_new = 'ct_id IN (' . implode(',', $insert_ids) . ')';
		} else {
		  $where_ct_admin_new = '1=1';
		}
		$sql = " select MT.it_id,
						MT.ct_qty,
						MT.it_name,
						MT.io_id,
						MT.io_type,
						MT.ct_option,
						MT.ct_qty,
						MT.ct_id,
						( SELECT it_time FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_time,
						( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn,
						( SELECT ProdPayCode FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodPayCode,
						( SELECT it_delivery_cnt FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_cnt,
						( SELECT it_delivery_price FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_price,
						( SELECT it_option_subject FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_option_subject,
						MT.ordLendStrDtm,
						MT.ordLendEndDtm
				from {$g5['g5_shop_cart_table']} MT
				where od_id = '$od_id'
					and ct_select = '1'  and ($where_ct_admin_new)";
		$result = sql_query($sql);
		$productList = [];
		$od_prodBarNum_total = 0;

		for ($i=0; $row=sql_fetch_array($result); $i++) {
		  # 옵션값 가져오기
		  $prodColor = $prodSize = $prodOption = '';
		  $prodOptions = [];

		  if ($row["io_id"]) { // 옵션값이 있으면
			$io_subjects = explode(',', $row['it_option_subject']);
			$io_ids = explode(chr(30), $row["io_id"]);

			for ($io_idx = 0; $io_idx < count($io_subjects); $io_idx++) {
			  switch ($io_subjects[$io_idx]) {
				case '색상':
				  $prodColor = $io_ids[$io_idx];
				  break;
				case '사이즈':
				  $prodSize = $io_ids[$io_idx];
				  break;
				default:
				  $prodOptions[] = $io_ids[$io_idx];
				  break;
			  }
			}
		  }

		  if ($prodOptions && count($prodOptions)) {
			$prodOption = implode('|', $prodOptions);
		  }

		  # 상품목록
		  for ($ii = 0; $ii < $row["ct_qty"]; $ii++) {
			$thisProductData = [];
			$thisProductData["prodId"] = $row["it_id"];
			$thisProductData["prodColor"] = $prodColor;
			$thisProductData["prodSize"] = $prodSize;
			$thisProductData["prodOption"] = $prodOption;
			$thisProductData["prodBarNum"] = "";
			$thisProductData["prodManuDate"] = date("Y-m-d");
			$thisProductData["stoMemo"] = $memo[$i];
			$thisProductData["ct_id"] = $row["ct_id"];

			$it_name = $row['it_name'];
			if($row['it_name'] !== $row['ct_option']){
			  $it_name = $it_name."(".$row['ct_option'].")";
			}
			$thisProductData["itemNm"] = $it_name;
			if ($row['ordLendStrDtm'] && $row['ordLendEndDtm']) {
			  $thisProductData["ordLendStrDtm"] = date("Y-m-d", strtotime($row['ordLendStrDtm']));
			  $thisProductData["ordLendEndDtm"] = date("Y-m-d", strtotime($row['ordLendEndDtm']));
			}
			array_push($productList, $thisProductData);
			$od_prodBarNum_total++;
		  }
		}

		$stoIdList = [];
		$sendData = [];
		$sendData["usrId"] = $od_member["mb_id"];
		$sendData["entId"] = $od_member["mb_entId"];
		$prodsSendData = [];
		$prodsData = [];
		foreach ($productList as $key => $value) {
		  $prodsData["prodId"] = $value["prodId"];
		  $prodsData["prodColor"] = $value["prodColor"];
		  $prodsData["prodSize"] = $value["prodSize"];
		  $prodsData["prodOption"] = $value["prodOption"];
		  $prodsData["prodManuDate"] = $value["prodManuDate"];
		  $prodsData["prodBarNum"] = $value["prodBarNum"];
		  $prodsData["stoMemo"] = $value["stoMemo"];
		  $prodsData["ct_id"] = $value["ct_id"];
		  $prodsData["itemNm"] = $value["itemNm"];
		  // var_dump(strlen($value['ordLendStrDtm']));
		  if (strlen($value['ordLendStrDtm']) === 10) {
			$prodsData["ordLendStrDtm"] = $value['ordLendStrDtm'];
			$prodsData["ordLendEndDtm"] = $value['ordLendEndDtm'];
		  }
		  array_push($prodsSendData, $prodsData);
		}
		if ($od['od_penId']) {
			$sendData["penId"] = $od['od_penId'];
		}
		$sendData["prods"] = $prodsSendData;

		if ($od['od_penId']) {

		  $ent_pen = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
			'usrId' => $od_member['mb_id'],
			'entId' => $od_member['mb_entId'],
			'penId' => $od['od_penId'],
		  ));
		  $ent_pen = $ent_pen['data'][0];

		  // print_r2($od);

		  // 기존 주문 삭제
		  $delete = api_post_call(EROUMCARE_API_ORDER_DELETE, array(
			'usrId' => $od_member['mb_id'],
			'penOrdId' => $od["ordId"],
		  ));
		  
		  // 새 주문 생성
		  $sendData["penOrdId"] = $od["ordId"];
		  $sendData["uuid"] = $od["uuid"];
		  $sendData["penId"] = $od["od_penId"];
		  $sendData["delGbnCd"] = "";
		  $sendData["ordWayNum"] = "";
		  $sendData["delSerCd"] = "";
		  $sendData["ordNm"] = $od["od_b_name"];
		  $sendData["ordCont"] = ($od["od_b_hp"]) ? $od["od_b_hp"] : $od["od_b_tel"];
		  $sendData["ordMeno"] = $od["od_memo"];
		  $sendData["ordZip"] = $od["od_b_zip1"] . $od["od_b_zip2"];
		  $sendData["ordAddr"] = $od["od_b_addr1"];
		  $sendData["ordAddrDtl"] = $od["od_b_addr2"];
		  $sendData["finPayment"] = strval(calc_order_price($od['od_id']));
		  $sendData["payMehCd"] = "0";
		  $sendData["regUsrId"] = $member["mb_id"];
		  $sendData["regUsrIp"] = $_SERVER["REMOTE_ADDR"];
		  $sendData["prods"] = $prodsSendData;
		  $sendData["documentId"] = ($ent_pen["penTypeCd"] == "04") ? "THK101_THK102_THK001_THK002_THK003" : "THK001_THK002_THK003";
		  $sendData["eformType"] = ($ent_pen["penTypeCd"] == "04") ? "21" : "00";
		  $sendData["conAcco1"] = $od_member["entConAcc01"];
		  $sendData["conAcco2"] = $od_member["entConAcc02"];
		  $sendData["returnUrl"] = "NULL";

		  $res = api_post_call(EROUMCARE_API_ORDER_INSERT, $sendData);

		  // 새로운 시스템 주문 아이디 등록
		  sql_query("
			UPDATE g5_shop_order SET
			  ordId = '{$res["data"]["penOrdId"]}',
			  uuid = '{$res["data"]["uuid"]}'
			WHERE od_id = '{$od_id}'
		  ");
		} else {
		  $oCurl = curl_init();
		  curl_setopt($oCurl, CURLOPT_PORT, 9901);
		  curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_INSERT);
		  curl_setopt($oCurl, CURLOPT_POST, 1);
		  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
		  curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		  $res = curl_exec($oCurl);
		  $res = json_decode($res, true);
		  curl_close($oCurl);
		}

				
		//결과 값
		if ($res["errorYN"] == "N") {
		  //성공시 ct_id에 업로드
		  if($od['od_penId'])
			$data = $res['data']['stockList'];
		  else
			$data = $res['data'];
		  for ($k=0; $k<count($data);$k++) {
			// ct_id에 업로드
			if ($w) {
			  $ct_status = $ct_status_w;
			} else {
			  $ct_status = "준비";
			}
			array_push($stoIdList, $data[$k]["stoId"]);
			$sql_ct = "update `g5_shop_cart` set ct_status='".$ct_status."', `stoId` = CONCAT(`stoId`,'".$data[$k]["stoId"]."|') where `ct_id` ='".$data[$k]["ct_id"]."'";
			sql_query($sql_ct);
		  }
		} else {

		    sql_query("
			DELETE FROM `g5_shop_order`
			WHERE `od_id` = '".$od_id."'
			");//오더 삭제
			
			sql_query("
			DELETE FROM `g5_shop_cart`
			WHERE `od_id` = '".$od_id."'
			");//카트 삭제
		  // 22.11.21 : 서원 - 상품 정보가 WMDS에 등록되어 있지 않을 경우 에러 처리
		  //                   [관리자]잘못된 상품DB로 주문서 생성 시 에러발생
		  if( !is_array($res['data']) ){
			json_response(500, $od_b_name."|".$od_b_tel."|".$od_b_addr1." 배송정보에 잘못된 상품정보가 있습니다. 주문내역 확인 후 다시 업로드 바랍니다.");
		  }

		  //실패시 ct_id 삭제
		  for ($k=0; $k<count($res['data']);$k++) {
			//실패하면 ct_id 삭제
			sql_query("
			DELETE FROM `g5_shop_cart`
			WHERE `ct_id` = '".$res['data'][$k]["ct_id"]."'
			");
		  }

		  json_response(500, $res["message"]);
		  return false;
		}
		//통신 성공시 order table 에 stoId 추가, total stoId 개수 갱신
		$stoIdList = implode(",", $stoIdList);

		//수정시 불필요한 , 정리
		$od['stoId'] = explode(',', $od['stoId']);
		$od['stoId'] = array_filter($od['stoId']);
		$od['stoId'] = implode(',', $od['stoId']);
		if ($od['stoId']) {
		  $stoIdList = $od['stoId'].','.$stoIdList;
		}

		sql_query("
		  UPDATE `g5_shop_order` SET
			  `stoId` = '".$stoIdList."'
		  WHERE od_id = '{$od_id}'
		");

		//들어있는 바코드수 구하기
		$sto_imsi="";
		$sql_ct = " select `stoId` from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
		$result_ct = sql_query($sql_ct);
		while($row_ct = sql_fetch_array($result_ct)) {
		  $sto_imsi .=$row_ct['stoId'];
		}

		$stoIdDataList = explode('|',$sto_imsi);
		$stoIdDataList=array_filter($stoIdDataList);
		$stoIdData = implode("|", $stoIdDataList);

		$count_b=0;
		$sendData["stoId"] = $stoIdData;
		$res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
		$result_again = $res['data'];
		for($k=0; $k < count($result_again); $k++){
		  if($result_again[$k]['prodBarNum']){
			$count_b ++;
		  }
		}

		//바코드 od_prodBarNum_insert, order total 조정
		$sql = "UPDATE `g5_shop_order` SET 
			`od_prodBarNum_insert` = ".$count_b.",
			`od_prodBarNum_total` = ".count($result_again)."
		WHERE `od_id` = '".$od_id."'";
		sql_query($sql);
	
		// 배열 초기화 & 마지막 배열 추가 ======================
		$_POST['it_id'] = array();//상품ID
		$_POST['io_id'] = array();//상품옵션
		$_POST['qty'] = array();//상품수량
		$_POST['it_price'] = array();//상품가격
		$memo	= array();//요청 사항	

		$_POST['it_id'][] = $it_id_last;
		$_POST['io_id'][] = $io_id_last;
		$_POST['qty'][] = $qty_last;
		$_POST['it_price'][] = $it_price_last;	
		$memo[] = $memo_last;
		//=================================================
		}
		$od_b_name = trim(addslashes($sheetData[$i2]['G']));//수령인
		$od_b_tel = trim(addslashes($sheetData[$i2]['H']));//연락처
		$od_b_addr1 = trim(addslashes($sheetData[$i2]['I']));//배송지 주소
	}
	sql_query("COMMIT");

    $response["msg"] = "ok";
	
	header('Content-type: application/json');
	echo json_encode($response); 
	
	
} else {
    json_response(500, '파일을 읽을 수 없습니다.');
	exit;
}

//랜덤값 생성
function GenerateString($length)  
{  
    $characters  = "0123456789";  
    $characters .= "abcdefghijklmnopqrstuvwxyz";  
    $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";  
    $characters .= "_";  
    $string_generated = "";  
    $nmr_loops = $length;  
    while ($nmr_loops--)  
    {  
      $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
    }  
    return $string_generated;  
}  

?>


