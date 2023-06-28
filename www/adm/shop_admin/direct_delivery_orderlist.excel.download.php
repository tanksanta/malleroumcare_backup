<?php
$sub_menu = '400310';
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];


$g5['title'] = '직배송 주문관리';

////////////////////////////////////////////////////////////////////////////////////////////////////
if($auth_check = auth_check($auth[$sub_menu], "r"))
// 초기 3개월 범위 적용
$fr_date = $_REQUEST["fr_date"];
$to_date = $_REQUEST["to_date"];
if ($fr_date == "" && $to_date == "") {
    $fr_date = date("Y-m-d", strtotime("-60 day"));
    $to_date = date("Y-m-d");
}
$qstr .= '&amp;page_rows='.$page_rows;
$click_status = ($click_status == "")?"준비": $click_status;
switch($click_status){
	case "준비": $title_text = "상품준비"; break;
	case "출고준비": $title_text = "출고준비"; break;
	case "완료": $title_text = "출고완료"; break;
	default: $title_text = "상품준비"; break;
}

$sql = "SELECT COUNT(CASE WHEN ct_status='준비' THEN 1 END) AS count1, 
COUNT(CASE WHEN ct_status='출고준비' THEN 1 END) AS count2,
COUNT(CASE WHEN ct_status='배송' THEN 1  END) AS count3 
FROM g5_shop_cart
WHERE ct_is_direct_delivery = '1'";
$row = sql_fetch($sql,true);

$count1 = $row["count1"];//상품준비count
$count2 = $row["count2"];//출고준비count
$count3 = $row["count3"];//출고완료(배송완료포함)count



$where = array();
$where[] = "ct_is_direct_delivery = '1'";//직배항목만

$replace_table = array(
    'od_id' => 'c.od_id',
    'it_name' => 'c.it_name',
    'mb_id' => 'c.mb_id'
);

$search_it_name = get_search_string($search_it_name);//상품명 검색
$search_b_name = get_search_string($search_b_name);//수령인명 검색
$search_b_addr = get_search_string($search_b_addr);//배송주소 검색
$search_partner = get_search_string($search_partner);//파트너 ID 검색
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$search_it_name_text = "없음";
if ($search_it_name != "") {//상품명 검색
  $search_it_name = trim($search_it_name);
  $where[] = " i.it_name like '%$search_it_name%' ";
  $search_it_name_text = $_REQUEST["search_it_name"];
}
$search_b_name_text = "없음";
if ($search_b_name != "") {//수령인명 검색
  $search_b_name = trim($search_b_name);
  $where[] = " od_b_name like '%$search_b_name%' ";
  $search_b_name_text = $_REQUEST["search_b_name"];
}
$search_b_addr_text = "없음";
if ($search_b_addr != "") {//배송주소 검색
  $search_b_addr = trim($search_b_addr);
  $where[] = " (od_b_addr1 like '%$search_b_addr%' or od_b_addr2 like '%$search_b_addr%' or od_b_addr3 like '%$search_b_addr%') ";
  $search_b_addr_text = $_REQUEST["search_b_addr"];
}
$search_b_memo_text = "없음";
if ($search_b_memo != "") {//관리자메모 검색
  $search_b_memo = trim($search_b_memo);
  $where[] = " (i.it_admin_memo like '%$search_b_memo%') ";
  $search_b_memo_text = $_REQUEST["search_b_memo"];
}

$search_partner_text = "없음";
if ($search_partner != "") {//파트너 ID 검색
  $search_partner = trim($search_partner);
  if($search_partner == "미등록"){
	$where[] = " ct_direct_delivery_partner = '' ";	
  }else{
	$where[] = " (ct_direct_delivery_partner like '%$search_partner%' or ct_direct_delivery_partner in (select mb_id from g5_member where mb_name like '%$search_partner%' and mb_type = 'partner' AND mb_partner_auth = 1 AND mb_level='5' AND mb_partner_type LIKE '%직배송%' AND (mb_intercept_date = '' OR mb_intercept_date IS NULL))) ";	
  }
  $search_partner_text = $_REQUEST["search_partner"];
}
$it_deadline_text ="전체";
if($_REQUEST["it_deadline"] != ""){//마감시간	
	switch($_REQUEST["it_deadline"]){
		case 1: $where[] .= " i.it_deadline between '09:00:00' and '09:59:59' ";$it_deadline_text ="09:00~10:00"; break;
		case 2: $where[] .= " i.it_deadline between '10:00:00' and '10:59:59' ";$it_deadline_text ="10:00~11:00"; break;
		case 3: $where[] .= " i.it_deadline between '11:00:00' and '11:59:59' ";$it_deadline_text ="11:00~12:00"; break;
		case 4: $where[] .= " i.it_deadline between '12:00:00' and '12:59:59' ";$it_deadline_text ="12:00~13:00"; break;
		case 5: $where[] .= " i.it_deadline between '13:00:00' and '13:59:59' ";$it_deadline_text ="13:00~14:00"; break;
		case 6: $where[] .= " i.it_deadline between '14:00:00' and '14:59:59' ";$it_deadline_text ="14:00~15:00"; break;
		case 7: $where[] .= " i.it_deadline between '15:00:00' and '15:59:59' ";$it_deadline_text ="15:00~16:00"; break;
		case 8: $where[] .= " i.it_deadline between '16:00:00' and '16:59:59' ";$it_deadline_text ="16:00~17:00"; break;
		case 9: $where[] .= " i.it_deadline between '17:00:00' and '17:59:59' ";$it_deadline_text ="17:00~18:00"; break;
		case 10: $where[] .= " (i.it_deadline between '18:00:00' and '23:59:59' or i.it_deadline between '00:00:00' and '08:59:59') ";$it_deadline_text ="기타/시간미등록"; break;
		default: $where[] .= " i.it_deadline between '09:00:00' and '09:59:59' ";$it_deadline_text ="09:00~10:00"; break;
	}
}




// 바코드 입력완료, 미입력
$barcode_text = "전체";
if (gettype($ct_barcode_saved) == 'string' && $ct_barcode_saved !== '') {  
  if ($ct_barcode_saved == 'saved'){
    $where[] = " ( ct_barcode_insert = ct_qty or ct_barcode_insert > ct_qty or substring(ca_id,1,2) = '70') ";
  	$barcode_text = "입력완료";
  }else if ($ct_barcode_saved == 'none'){
    $where[] = " ( ct_barcode_insert = 0 OR ct_barcode_insert ='') and substring(ca_id,1,2) != '70'";
  	$barcode_text = "미입력";
  }

}

// 배송정보 입력완료, 미입력
$delivery_text = "전체";
if (gettype($ct_delivery_saved) == 'string' && $ct_delivery_saved !== '') {  
  if ($ct_delivery_saved == 'saved'){
    $where[] = " ( CHAR_LENGTH(ct_delivery_num) > 6 ) ";
	$delivery_text = "입력완료";
  }else if ($ct_delivery_saved == 'none'){
    $where[] = " ( ct_delivery_num IS NULL OR ct_delivery_num = '' ) ";
	$delivery_text = "미입력";
  }
}

// 급여, 비급여
$gubun_text = "전체";
if (gettype($gubun) == 'string' && $gubun !== '') {  
  if ($gubun == '10'){
    $where[] = " ( substring(ca_id,1,2) = '10' or substring(ca_id,1,2) = '20' ) ";
	$gubun_text = "급여";
  }else if ($gubun == '70'){
    $where[] = " ( substring(ca_id,1,2) = '70' ) ";
	$gubun_text = "비급여";
  }
}

// 위탁엑셀 다운, 미다운
$delivery_excel_text = "전체";
if (gettype($ct_is_delivery_excel_downloaded) == 'string' && $ct_is_delivery_excel_downloaded !== '') {  
  if ($ct_is_delivery_excel_downloaded == 'saved'){
    $where[] = " ( ct_is_delivery_excel_downloaded = '1' ) ";
	$delivery_excel_text = "다운완료";
  }else if ($ct_is_delivery_excel_downloaded == 'none'){
    $where[] = " ( ct_is_delivery_excel_downloaded = '0' ) ";
	$delivery_excel_text = "미다운";
  }
}

//////////////////

if ($fr_date && $to_date) {
  $where[] = " (ct_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  $qstr .= "&amp;fr_date=".$fr_date."&amp;to_date=".$to_date;
}

$where[] = " od_del_yn = 'N' ";

// 최고관리자가 아닐때
//if ( $ct_status == '작성' && $is_admin != 'super' ) {
  //$where[] = " od_writer = '{$member['mb_id']}' ";
//}

$where_count = $where;

if ($click_status) {//상품상태
  $where[] = " ct_status = '{$click_status}'";  
  $qstr .= "&amp;click_status=".$click_status;
} 

$where[] = " (m.mb_intercept_date = '' OR m.mb_intercept_date IS NULL) ";

$sql_search = '';
if ($where) {
  $sql_search = ' where '.implode(' and ', $where);
}

$sql_count_search = '';
if ($where_count) {
  $sql_count_search = ' where '.implode(' and ', $where_count);
}

// shop_cart 조인으로 수정
// member 테이블 조인
$sql_common = "
  FROM
    {$g5['g5_shop_cart_table']} c
  LEFT JOIN
    {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
  LEFT JOIN
    {$g5['g5_shop_order_table']} o ON c.od_id = o.od_id
  LEFT JOIN
    {$g5['member_table']} m ON c.mb_id = m.mb_id
  LEFT JOIN 
    {$g5['member_table']} m2 ON c.ct_direct_delivery_partner = m2.mb_id
  LEFT JOIN
    partner_install_report pir ON c.od_id = pir.od_id
  LEFT JOIN
    g5_shop_order_cancel_request ocr ON c.od_id = ocr.od_id
";

$sql_counts = "
  SELECT
    count(*) as cnt,
    ct_status,
    sum(
      case
        when io_type = 0
        then ct_price + io_price
        else ct_price
      end * ct_qty
    ) as ct_price,
    sum(ct_sendcost) as ct_sendcost,
    sum(ct_discount) as ct_discount
  {$sql_common}
  {$sql_count_search}
  GROUP BY
    ct_status
";
$result_counts = sql_query($sql_counts);
$cate_counts = [];
$total_info = [];
while($count = sql_fetch_array($result_counts)) {
  $cate_counts[$count['ct_status']] = $count['cnt'];
  $total_info[$count['ct_status']] = $count;
}

$sql_common .= $sql_search;

// 페이지네이트
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql, true);
$total_count = $row['cnt'];
$page_rows = (int)$page_rows ? (int)$page_rows : "100";
$rows = $page_rows;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if($_REQUEST["orb"] == ""){
	if($click_status == "준비"){
		$_REQUEST["orb"] = "deadline_it";//마감-상품명
	}elseif($click_status == "출고준비"){
		$_REQUEST["orb"] = "od_id";//주문번호
	}elseif($click_status == "배송"){
		$_REQUEST["orb"] = "out_time_partner";//출고일-파트너명
	}
}

if($_REQUEST["orb"] == "od_id"){//주문번호
	$sql_order = " ORDER BY o.od_id DESC ";
}elseif($_REQUEST["orb"] == "deadline_partner"){//마감-파트너명
	$sql_order = " ORDER BY IF(time_dead>0, 1, 2) ASC, time_dead ASC,
	CASE
    WHEN partner_name IS NULL THEN '2'
    WHEN partner_name = '' THEN '1'
    ELSE '0'	
	END,partner_name ASC ";
}elseif($_REQUEST["orb"] == "deadline_it"){//마감-상품명
	$sql_order = " ORDER BY IF(time_dead>0, 1, 2) ASC, time_dead ASC,
	i.it_name ASC ";
}elseif($_REQUEST["orb"] == "partner_it"){//파트너명-상품명
	$sql_order = " ORDER BY CASE
    WHEN partner_name IS NULL THEN '2'
    WHEN partner_name = '' THEN '1'
    ELSE '0'	
END,partner_name ASC, i.it_name ASC ";
}elseif($_REQUEST["orb"] == "out_time_partner"){//출고일-파트너명
	$sql_order = " ORDER BY ct_ex_date DESC,CASE
    WHEN partner_name IS NULL THEN '2'
    WHEN partner_name = '' THEN '1'
    ELSE '0'	
END,partner_name ASC ";
}
$qstr .= "&amp;orb=".$_REQUEST["orb"];
$sql_common .= $sql_order;

$sql  = "
  select *, o.od_id as od_id, c.ct_id as ct_id, c.mb_id as mb_id,m2.mb_name AS partner_name, (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
  $sql_common 
";
if ($click_status || $od_status) {
  if ($show_all == 'Y' && ($click_status == "준비" || $click_status == "출고준비" || $od_status == '준비' || $od_status == '출고준비')) {
    $sql = preg_replace('/limit (.*)/i', '', $sql);
  }
}
$result = sql_query($sql);
//echo $sql;
$orderlist = array();
while( $row = sql_fetch_array($result) ) {
  $orderlist[] = $row;
}
$i = 0;
foreach($orderlist as $order) {
    $num = $total_count - $i ;
    $bg = 'bg'.($i%2);
	//$mb = get_member($order['ct_direct_delivery_partner']);
		$ct_direct_delivery_partner_name = ($order['partner_name'] == "")?"미등록": $order['partner_name'];//파트너
		if(!$order['ct_barcode_insert']) {//등록 바코드 수량
			$order['ct_barcode_insert'] = 0;
		}
		$opt_price = 0;

		if($order['io_type'])
		  $opt_price = $order['io_price'];
		else
		  $opt_price = $order['ct_price'] + $order['io_price'];

		$order["opt_price"] = $opt_price;

		// 소계
		$order['ct_price_stotal'] = $opt_price * $order['ct_qty'] - $order['ct_discount'];
		if($order["prodSupYn"] == "Y") {
		  $order["ct_price_stotal"] -= ($order["ct_stock_qty"] * $opt_price);
		}
		// 단가 역산
		$order["opt_price"] = $order['ct_price_stotal'] ? @round($order['ct_price_stotal'] / ($order["ct_qty"] - $order["ct_stock_qty"])) : 0;

		// 공급가액
		$order["basic_price"] = $order['ct_price_stotal'];
		// 부가세
		$order["tax_price"] = 0;
		if($order['it_taxInfo'] != "영세" ) {
		  // 공급가액
		  $order["basic_price"] = round($order['ct_price_stotal'] / 1.1);
		  // 부가세
		  $order["tax_price"] = round($order['ct_price_stotal'] / 11);
		}
		$direct_delivery_text = ($order['ct_is_delivery_excel_downloaded'] == 1)?"다운완료":"-";//위탁엑셀다운로드완료
		$order['od_memo'];
		$order['prodMemo'];
		$memo = ($order['od_memo'] !="" || $order['prodMemo'] != "")?"<a href=\"javascript:;\" onClick=\"go_view('".$order["od_id"]."','".$order["it_name"]."','".$order['od_memo']."','".$order['prodMemo']."')\">보기</a>":"-";//요청사항보기
		if ($cancel_order_table[$order['od_id']]) {
			$is_order_cancel_requested = "cancel_requested";
		  }
	
  $data[] = [
    $order["ct_id"],
	$order["od_id"],
	$ct_direct_delivery_partner_name,
	$order['ct_direct_delivery_partner'],
    $order["it_name"].(($order["ct_option"] != $order["it_name"])?" [".$order["ct_option"]."]":""),  
	$order["it_thezone2"],
    $order["od_b_name"],
	$order["od_b_addr1"],
	(($order["od_b_addr2"]!="")?" ".$order["od_b_addr2"]:"").(($order["od_b_addr3"]!="")?" ".$order["od_b_addr3"]:""),
	$order['ct_qty'],
	number_format($order["opt_price"]),
	number_format($order["basic_price"]),
	number_format($order["tax_price"]),
	number_format($order["ct_price_stotal"]),
	$order['od_memo'],
	$order['prodMemo'],
	$order['it_admin_memo'],
	$order['od_time'],
	($order["ct_ex_date"]=="" || $order["ct_ex_date"]=="0000-00-00")?"-":$order["ct_ex_date"],
	($order["it_deadline"] == "00:00:00" || $order["it_type11"] == "0")?"-":$order["it_deadline"],
  ];
  $i++;
}
$search_where = "바코드 입력여부-".$barcode_text.", 배송정보입력-".$delivery_text.", 급여구분-".$gubun_text.", 위탁엑셀다운로드-".$delivery_excel_text.", 마감시간-".$it_deadline_text.", 검색어 : 파트너ID 또는 이름(".$search_partner_text."), 상품명(".$search_it_name_text."), 수령인명(".$search_b_name_text."), 배송주소(".$search_b_addr_text."), 관리자메모(".$search_b_memo_text.")";

$title = ['카트번호','주문번호','파트너','파트너ID','상품명(옵션)','상품코드','수령인','배송주소','상세주소','수량(개)','단가(원)','공급가액(원)','부가세(원)','합계금액(원)','배송요청사항','상품요청사항','관리자메모','주문일자','출고준비변경일','출고일','마감시간'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:U1');
$excel->setActiveSheetIndex(0)->mergeCells('J3:U3');

// 시트 네임
$sheet->setTitle("직배송 주문관리(".$title_text.")");

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:U%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:U%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 금액 우측 정렬
$sheet -> getStyle(sprintf("K5:N%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle("A4:U4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle("A4:U4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$excel->setActiveSheetIndex(0)->setCellValue('A1', "직배송 주문관리(".$title_text.")"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('J3', "검색 : ".$search_where);
$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(50);
$sheet->getColumnDimension('F')->setWidth(10);
$sheet->getColumnDimension('G')->setWidth(30);
$sheet->getColumnDimension('H')->setWidth(60);
$sheet->getColumnDimension('I')->setWidth(50);
$sheet->getColumnDimension('J')->setWidth(10);
$sheet->getColumnDimension('K')->setWidth(10);
$sheet->getColumnDimension('L')->setWidth(15);
$sheet->getColumnDimension('M')->setWidth(10);
$sheet->getColumnDimension('N')->setWidth(15);
$sheet->getColumnDimension('O')->setWidth(50);
$sheet->getColumnDimension('P')->setWidth(50);
$sheet->getColumnDimension('Q')->setWidth(50);
$sheet->getColumnDimension('R')->setWidth(15);
$sheet->getColumnDimension('S')->setWidth(15);
$sheet->getColumnDimension('T')->setWidth(15);
$sheet->getColumnDimension('U')->setWidth(15);


header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"직배송_주문관리(".$title_text.")_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

?>
