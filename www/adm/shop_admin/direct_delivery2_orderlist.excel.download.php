<?php
$sub_menu = '400406';
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];


$g5['title'] = '설치배송 주문관리';

////////////////////////////////////////////////////////////////////////////////////////////////////
//if($auth_check = auth_check($auth[$sub_menu], "r"))
// 초기 3개월 범위 적용
$fr_date = $_REQUEST["fr_date"];
$to_date = $_REQUEST["to_date"];
if ($fr_date == "" && $to_date == "") {
	$select_date_text = "전체";
	//$fr_date = date("Y-m-d", strtotime("-30 day"));
    //$to_date = date("Y-m-d");
}else{
	$select_date_text = $fr_date."~".$to_date;
}

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
FROM g5_shop_cart c
LEFT JOIN g5_shop_order o ON c.od_id = o.od_id
WHERE ct_is_direct_delivery = '2'
AND od_del_yn = 'N'";
$row = sql_fetch($sql,true);

$count1 = $row["count1"];//상품준비count
$count2 = $row["count2"];//출고준비count
$count3 = $row["count3"];//출고완료(배송완료포함)count



$where = array();
$where[] = "ct_is_direct_delivery = '2'";//직배항목만

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

$search_mb_name_text = "없음";
if ($search_mb_name != "") {//사업소명 검색
  $search_mb_name = trim($search_mb_name);
  $where[] = " m.mb_name like '%$search_mb_name%' ";
  $search_mb_name_text = $_REQUEST["search_mb_name"];
}

$search_memo_text = "없음";
if ($search_memo != "") {//요청사항 검색
  $search_memo = trim($search_memo);
  $where[] = " (o.od_memo like '%$search_memo%' or c.prodMemo like '%$search_memo%') ";
  $search_memo_text = $_REQUEST["search_memo"];
}



// 품목구분
$ca_id_text = "전체";
if (gettype($ca_id) == 'string' && $ca_id !== '') {
    $where[] = " ( substring(ca_id,1,4) = '$ca_id') ";
	switch($ca_id){
		case "1090": $ca_id_text = "안전손잡이"; break;
		case "2060": $ca_id_text = "수동침대"; break;
		case "2070": $ca_id_text = "전동침대"; break;
		case "2080": $ca_id_text = "수동휠체어"; break;
		default: $ca_id_text = "-"; break;
	}	
}

// 설치파트너
$partner_text = "전체";
if (gettype($partner_id) == 'string' && $partner_id !== '') {
    $where[] = " ( m2.mb_id = '$partner_id' ) ";
	$mb = get_member($partner_id);
	$partner_text = $mb["mb_name"];
}

// 설치결과보고
$pip_text= "전체";
if (gettype($pip) == 'string' && $pip !== '') {
  if ($pip == '등록')
    $where[] = " ( img_cnt1 > 0 && img_cnt2 > 0 && img_cnt3 > 0 ) ";
  else
    $where[] = " ( img_cnt1 < 1 || img_cnt2 < 1 || img_cnt3 < 1 ) ";
  $pip_text= $pip;
}

// 설치이슈여부
$pir_issue_text = "전체";
if (gettype($pir_issue) == 'string' && $pir_issue !== '') {
	$where[] = " ( $pir_issue = '1' ) ";
	switch($pir_issue){
		case "ir_is_issue_1": $pir_issue_text = "상품변경"; break;
		case "ir_is_issue_2": $pir_issue_text = "상품추가"; break;
		case "ir_is_issue_3": $pir_issue_text = "미설치"; break;
	}	
}

//////////////////
$search_date = ($search_date == "")?"od_time":$search_date;//검색기간 구분

if ($fr_date && $to_date) {
  $where[] = " ($search_date between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  $qstr .= "&amp;search_date=".$search_date."&amp;fr_date=".$fr_date."&amp;to_date=".$to_date;
}

$where[] = " od_del_yn = 'N' ";

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
	LEFT JOIN ( SELECT od_id, COUNT(CASE WHEN img_type = '설치사진' THEN 1 END) AS img_cnt1
,COUNT(CASE WHEN img_type = '실물바코드사진' THEN 1 END) AS img_cnt2
,COUNT(CASE WHEN img_type = '설치ㆍ회수ㆍ소독확인서' THEN 1 END) AS img_cnt3 
FROM partner_install_photo WHERE 1=1 GROUP BY od_id) AS pip ON c.od_id = pip.od_id
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

$sql_order = " ORDER BY o.od_id DESC ";//기본 정렬

$sql_common .= $sql_order;

$sql  = "
  select *, o.od_id as od_id, c.ct_id as ct_id, c.mb_id as mb_id,m.mb_name as mb_name2,m2.mb_name AS partner_name,m2.mb_id AS partner_id
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

$sales_manager_table = [];
$result_sales_manager = sql_query("select mb_id, mb_name from g5_member where mb_level = 9");
foreach($result_sales_manager as $sales_manager) {
  $sales_manager_table[$sales_manager['mb_id']] = $sales_manager['mb_name'];
}

$i = 0;
foreach($orderlist as $order) {
	switch(substr($order['ca_id'],0,4)){
		case "1090": $ca_nm = "안전손잡이"; break;
		case "2060": $ca_nm = "수동침대"; break;
		case "2070": $ca_nm = "전동침대"; break;
		case "2080": $ca_nm = "수동휠체어"; break;
		default: $ca_nm = "-"; break;
	}

	$ct_direct_delivery_partner_name = ($order['partner_name'] == "")?"미등록": $order['partner_name'];//파트너
	$ct_delivery_company = "";
	foreach($delivery_companys as $data2){ 
		if($order["ct_delivery_company"] == $data2["val"]){
			$ct_delivery_company = $data2["name"];
		}
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
	$sale_manager = $sales_manager_table[$order['od_sales_manager']];
	if (!$sale_manager) {
		$sale_manager = $sales_manager_table[$order['mb_manager']];
	}
//바코드 ==========================================
	$od = sql_fetch(" 
      SELECT o.*, cp.cp_subject
      FROM g5_shop_order o
        LEFT JOIN g5_shop_coupon_log cp_log ON o.od_id = cp_log.od_id
        LEFT JOIN g5_shop_coupon cp ON cp_log.cp_id = cp.cp_id
      WHERE o.od_id = '".$order['od_id']."'
    ");

    if ($count_od_id !== $order['od_id']) {
        $count_number++;
        $count_od_id = $order['od_id'];
    }

    // 23.04.27 : 서원 - 바코드 누락건으로 인한 프로세스 간략화.
    //                    WMDS에 등록된 주문건의 경우 cart테이블을 확인하고, ct_barcode 컬럼에 바코드 데이터가 있을 경우 해당 데이터를 WMDS거치지 않고, 컬럼 데이터로 대체한다.
    $barcode=[];
    if( $order['ct_barcode'] && ( mb_strlen($order['ct_barcode'] ) > 20 ) ) {
      $_ct_barcode = json_decode( $order['ct_barcode'], TRUE );

      if( is_array($_ct_barcode) && count($_ct_barcode) ) {
        foreach ($_ct_barcode as $key => $val) {          
          array_push( $barcode, $val );
        }
      }

    } else {
      // 23.04.27 : 서원 - ct_barcode 컬럼에 데이터가 없을 경우 기존 로직 활성화.
      #바코드
      $stoIdDataList = explode('|',$order['stoId']);
      $stoIdDataList=array_filter($stoIdDataList);
      $stoIdData = implode("|", $stoIdDataList);


      $sendData["stoId"] = $stoIdData;
      $oCurl = curl_init();
      $res = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
      $result_again = $res;
      $result_again =$result_again['data'];
      
      if( is_array($result_again) && count($result_again) ) {
        for($k=0; $k < count($result_again); $k++) {
          if($result_again[$k]['prodBarNum']) {
            array_push($barcode,$result_again[$k]['prodBarNum']);
          }
        }
      }

    }

    asort($barcode);
    $barcode2=[];
    $y = 0;  
    foreach($barcode as $key=>$val)  
    {  
      $new_key = $y;  
      $barcode2[$new_key] = $val;  
      $y++;  
    }
    $barcode_string="";
    if (!is_benefit_item($order)) {
      for ($y=0; $y<count($barcode2); $y++) {
          #처음
          if ($y==0) {
              $barcode_string .= $barcode2[$y];
              continue;
          }
          #현재 바코드 -1이 전바코드와 같지않음
          if (intval($barcode2[$y])-1 !== intval($barcode2[$y-1])) {
              $barcode_string .= ",".$barcode2[$y];
          }
          #현재 바코드 -1이 전바코드와 같음
          if (intval($barcode2[$y])-1 == intval($barcode2[$y-1])) {
              //다음번이 연속되지 않을 경우
              if (intval($barcode2[$y])+1 !== intval($barcode2[$y+1])) {
                  $barcode_string .= "-".$barcode2[$y];
              }
          }
      }
      $barcode_string .= " ";
    }


  $data[] = [
	$order["od_id"],//주문번호
	substr($order['od_time'],0,16),//주문일시
	($order["ct_direct_delivery_date"] == "")?"-":substr($order["ct_direct_delivery_date"],0,16),//설치예정일
	($order["ct_ex_date"]=="" || $order["ct_ex_date"]=="0000-00-00")?"-":$order["ct_ex_date"],//출고완료일
	$order["it_name"],//상품명
	(($order["ct_option"] != $order["it_name"])?$order["ct_option"]:"-"),//옵션명
	$ca_nm,//품목구분
	$order["it_thezone2"],//품목코드
	$order['ct_qty'],//수량
	number_format($order["opt_price"]),//단가
	number_format($order["tax_price"]),//부가세
	number_format($order["basic_price"]),//공급가액
	number_format($order["ct_price_stotal"]),//합계금액
	$order['mb_name2'],//사업소명
	$order['mb_giup_bnum'],//사업자번호
	$sale_manager,//영업사원
	$order["od_b_name"],//수령인
	$order["od_b_addr1"],//주소
	$order["od_b_addr2"]." ".$order["od_b_addr3"],//상세주소
	$order["od_b_tel"],//연락처
	$order["od_b_hp"],//휴대전화
	$ct_direct_delivery_partner_name,//설치파트너
	$order['prodMemo'],//상품요청사항
	$order['od_memo'],//배송요청사항
	$barcode_string,//바코드 이카운트 엑셀 다운 참고
	$ct_delivery_company,//택배사
	$order["ct_delivery_num"],//송장번호
  ];
  $i++;
}
$search_where = "품목구분-".$ca_id_text.", 설치파트너-".$partner_text.", 설치결과보고-".$pip_text.", 설치이슈여부-".$pir_issue_text.", 검색기간-".$select_date_text.", 검색어 : 상품명(".$search_it_name_text."), 사업소(".$search_mb_name_text.") 수령인명(".$search_b_name_text."), 요청사항(".$search_memo_text.")";

$title = ['주문번호','주문일시','설치예정일','출고완료일','상품명','옵션명','품목구분','품목코드','수량','단가(원)','부가세(원)','공급가액(원)','합계금액(원)','사업소명','사업자번호','영업담당자','수령인','주소','상세주소','연락처','휴대전화','설치파트너','상품요청사항','배송요청사항','바코드','택배사','송장번호'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:AA1');
$excel->setActiveSheetIndex(0)->mergeCells('J3:AA3');

// 시트 네임
$sheet->setTitle("설치배송 주문관리(".$title_text.")");

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:AA%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:AA%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 금액 우측 정렬
$sheet -> getStyle(sprintf("J5:M%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle("A4:AA4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle("A4:AA4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$excel->setActiveSheetIndex(0)->setCellValue('A1', "설치배송 주문관리(".$title_text.")"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('J3', "검색 : ".$search_where);
$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');


//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(17);
$sheet->getColumnDimension('C')->setWidth(17);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(40);
$sheet->getColumnDimension('F')->setWidth(45);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(10);
$sheet->getColumnDimension('J')->setWidth(10);
$sheet->getColumnDimension('K')->setWidth(10);
$sheet->getColumnDimension('L')->setWidth(15);
$sheet->getColumnDimension('M')->setWidth(15);
$sheet->getColumnDimension('N')->setWidth(25);
$sheet->getColumnDimension('O')->setWidth(15);
$sheet->getColumnDimension('P')->setWidth(15);
$sheet->getColumnDimension('Q')->setWidth(15);
$sheet->getColumnDimension('R')->setWidth(45);
$sheet->getColumnDimension('S')->setWidth(45);
$sheet->getColumnDimension('T')->setWidth(20);
$sheet->getColumnDimension('U')->setWidth(20);
$sheet->getColumnDimension('V')->setWidth(25);
$sheet->getColumnDimension('W')->setWidth(50);
$sheet->getColumnDimension('X')->setWidth(50);
$sheet->getColumnDimension('Y')->setWidth(20);
$sheet->getColumnDimension('Z')->setWidth(13);
$sheet->getColumnDimension('AA')->setWidth(25);
$excel->setActiveSheetIndex(0)->getStyle(sprintf("A5:A%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
$excel->setActiveSheetIndex(0)->getStyle(sprintf("H5:H%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
$excel->setActiveSheetIndex(0)->getStyle(sprintf("AA5:AA%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
$excel->setActiveSheetIndex(0)->getStyle(sprintf("T5:T%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
$excel->setActiveSheetIndex(0)->getStyle(sprintf("U5:U%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
$excel->setActiveSheetIndex(0)->getStyle(sprintf("Y5:Y%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"설치배송_주문관리(".$title_text.")_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

?>
