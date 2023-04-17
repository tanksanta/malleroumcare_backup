<?php
$sub_menu = '500900';
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];


// 검색처리
$select = array();
$where = array();



$sel_stat = isset($_POST['sel_stat']) ? get_search_string($_POST['sel_stat']) : '';
$od_release = isset($_POST['od_release']) ? get_search_string($_POST['od_release']) : '0';
$fr_date = isset($_POST['fr_date']) ? get_search_string($_POST['fr_date']) : '';
$to_date = isset($_POST['to_date']) ? get_search_string($_POST['to_date']) : '';

$penId = isset($_POST['penId']) ? get_search_string($_POST['penId']) : '';
$search = isset($_POST['search']) ? get_search_string($_POST['search']) : '';
$sel_field = isset($_POST['sel_field']) && in_array($_POST['sel_field'], array('penNm', 'penLtmNum','entNm','mb_id','all')) ? $_POST['sel_field'] : '';




if($sel_stat !="" && $sel_stat != "all"){
	if($sel_stat == 3){
		$where[] = " (E.dc_status='$sel_stat' || E.dc_status='2') ";
	}else{
		$where[] = " E.dc_status='$sel_stat'";
	}
}else{
	// 작성 완료된 계약서 & 마이그레이션 된 계약서만 + 간편 계약서로 생성된 계약서
	$where[] = " (E.dc_status = '2' OR E.dc_status = '3' OR E.dc_status = '11' OR E.dc_status = '4' OR E.dc_status = '5') ";
}

$od_release_text = "생성일자";
$days_text = "전체";
if($fr_date != "" || $to_date != ""){//날짜 검색 조건이 있을 경우
	if($od_release == 1){//서명요청일
		$where_od = "dc_sign_request_datetime";
		$od_release_text = "서명요청일";
	}elseif($od_release == 2){//서명완료일
		$where_od = "dc_sign_datetime";
		$od_release_text = "서명완료일";
	}elseif($od_release == "" ||  $od_release == 0){//생성일
		$where_od = "dc_datetime";	
		$od_release_text = "생성일자";
	}
		
	if($to_date == ""){//시작 날짜만 있을 경우 >=
		$where[] = " $where_od >= '$fr_date 00:00:00' ";
	}elseif($fr_date == ""){//종료 날짜만 있을 경우 <=
		$where[] = " $where_od <= '$to_date 23:59:59' ";
	}else{// 둘다 있을 경우 between
		$where[] = " $where_od between '$fr_date 00:00:00' and '$to_date 23:59:59' "; 
	}
	$days_text = $fr_date."~".$to_date;
}

// 정렬 순서
$sql_order = ' ORDER BY ';
$index_order = '';
$index_order = 'DESC';
$sql_order .= 'E.dc_datetime ' . $index_order;

//$select[] = ' m.mb_id ';
$select[] = ' I.it_name ';
$select[] = ' COUNT(E.dc_id) as it_count ';
$select[] = ' I2.t_price ';
$sql_join = ' LEFT JOIN `eform_document_item` I ON E.dc_id = I.dc_id 
LEFT JOIN (SELECT dc_id, it_name,it_qty,it_price, SUM(it_qty*it_price) AS t_price FROM `eform_document_item` GROUP BY dc_id) I2 ON E.dc_id = I2.dc_id ';
$sql_group = " GROUP BY E.dc_id";
$where2 = "";
$search_text = ($search == "")?"없음":$search;
$sel_text = "전체";
if ($search != '' && $sel_field != '') {
	if($sel_field == "all"){
		$where[] = " (E.penNm like '%{$search}%' or E.penLtmNum like '%{$search}%' or E.entNm like '%{$search}%' or E.entId = M.mb_entId) ";
		$sql_join .=" left outer join (
						SELECT mb_entId FROM g5_member WHERE mb_id = '{$search}'
					) M ON 1 = 1 ";		
	}elseif($sel_field == "mb_id"){
	  $sql_join .=" INNER JOIN (
						SELECT mb_entId FROM g5_member WHERE mb_id = '{$search}'
					) M ON M.mb_entId = E.entId ";
		$sel_text = "사업소ID";
	}else{
	  $where[] = " $sel_field like '%{$search}%' ";
	  switch($sel_field){//계약상태
		case "entNm":
			$sel_text = "사업소명";
		break;
		case "penNm":
			$sel_text = "수급자명";
		break;
		case "penLtmNum":
			$sel_text = "수급자번호";
		break;
		default:
			$sel_text = "전체";
		break;
	  }
	}
}

//검색조건
switch($sel_stat){//계약상태
	case "all":
		$stat_text = "전체";
	break;
	case "11":
		$stat_text = "계약서생성";
	break;
	case "2":case "3":
		$stat_text = "서명완료";
	break;
	case "4":
		$stat_text = "서명요청";
	break;
	case "계약서삭제":
		$stat_text = "계약서삭제";
	break;
	default:
		$stat_text = "전체";
	break;
}
$search_where = "계약상태-".$stat_text.",기간구분-".$od_release_text.",기간-".$days_text.",검색어-(".$stat_text.")".$search_text;

// select 배열 처리
$select[] = "E.*";
$select[] = "(SELECT mb_id FROM `g5_member` WHERE mb_entId=E.entId) AS mb_id";
$sql_select = "HEX(E.dc_id) as uuid, ".implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}

$sql_from = " FROM `eform_document` E";
$total_count = sql_fetch("SELECT COUNT(R.dc_id) AS cnt FROM (SELECT E.dc_id" . $sql_from . $sql_join . $sql_where . $sql_group . ') R')['cnt'];

$page_rows = isset($_POST['page_rows']) ? get_search_string($_POST['page_rows']) : $config['cf_page_rows'];
$total_page = ceil($total_count / $page_rows); // 전체 페이지 계산
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . $sql_group . $sql_order);
$i = 0;
while ($row = sql_fetch_array($result)) {
    $num = $total_count - $i ;
    $bg = 'bg'.($i%2);
	switch($row["dc_status"]){
		case "10":
		$dc_status = "작성준비";
		break;
		case "11":
		$dc_status = "계약서생성";
		break;
		case "2": case "3":
		$dc_status = "서명완료";
		break;
		case "4":
		$dc_status = "서명요청";
		break;
		case "5":
		$dc_status = "계약서삭제";
		break;
	}
	$dc_sign_send_datetime = ($row["dc_sign_send_datetime"] == "" || $row["dc_sign_send_datetime"] == "0000-00-00 00:00:00")?"-":$row["dc_sign_send_datetime"];
	$dc_sign_datetime = ($row["dc_sign_datetime"] == "0000-00-00 00:00:00")?"-":$row["dc_sign_datetime"];
  $data[] = [
    $num,
	$row["entNm"],
	$row["mb_id"],
	mb_substr($row["penNm"],0,1)."*".mb_substr($row["penNm"],-1),  
	substr($row["penLtmNum"],0,4)."***".substr($row["penLtmNum"],7,4),
    $row["penRecGraNm"],
	$row["penTypeNm"],
	$row["it_count"],
	$row["t_price"],
	$row["dc_datetime"],
	$dc_sign_send_datetime,
	$dc_sign_datetime,
	$dc_status,
  ];
  $i++;
}


$title = ['No.','사업소명','사업소ID','수급자명','수급자번호','인정등급','본인부담금율','상품수량','총계약금액','생성일자','서명요청일자','서명완료일자','상태'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:L1');
$excel->setActiveSheetIndex(0)->mergeCells('F3:M3');

// 시트 네임
$sheet->setTitle("간편계약서관리");

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:M%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:M%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle("A4:M4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle("A4:M4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$excel->setActiveSheetIndex(0)->setCellValue('A1', "간편계약서관리"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('F3', "검색 : ".$search_where);
$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(10);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(15);
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->getColumnDimension('K')->setWidth(20);
$sheet->getColumnDimension('L')->setWidth(20);
$sheet->getColumnDimension('M')->setWidth(15);


header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"계약서관리_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

?>
