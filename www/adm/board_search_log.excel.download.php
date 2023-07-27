<?php
$sub_menu = '300100';
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];

$g5['title'] = '게시판 사용자 조회로그';

////////////////////////////////////////////////////////////////////////////////////////////////////
// 초기 3개월 범위 적용
$fr_date = $_REQUEST["fr_date"];
$to_date = $_REQUEST["to_date"];

$where = array();
//$where[] = "";

$search_tag = get_search_string($search_tag);//검색어
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$search_select_text = "전체";
$search_text = "없음";
if ($search_tag != "") {//검색태그 검색
  $search_tag = trim($search_tag);
  if($search_select == ""){//전체 조회 시
	$where[] = " (a.mb_id like '%$search_tag%' or a.wr_id like '%$search_tag%' or a.mb_id in (select mb_id from g5_member where mb_name like '%$search_tag%') or a.mb_id in (select mb_id from g5_member where mb_giup_bnum like '%$search_tag%')) ";
	$search_select_text = "회원명";
  }elseif($search_select == "mb_name"){//닉네임 조회 시
	$where[] = " a.mb_id in (select mb_id from g5_member where mb_name like '%$search_tag%') ";
	$search_select_text = "전체";
  }elseif($search_select == "mb_giup_bnum"){//사업소코드 조회 시
	$where[] = " a.mb_id in (select mb_id from g5_member where mb_giup_bnum like '%$search_tag%') ";
	$search_select_text = "사업소코드";
  }else{
	$where[] = " a.".$search_select." like '%$search_tag%' ";
	$search_select_text = ($search_select == "mb_id")?"회원아이디":"wr_id";
  }
  $search_text = $_REQUEST["search_tag"];
}

$frto_text = "전체";
if ($fr_date && $to_date) {
  $where[] = " (a.create_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  $frto_text = $fr_date." ~ ".$to_date;
}

$where_count = $where;

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

$table_text = "전체";
if($_REQUEST["bo_table"] != ""){
	$where[] .= " a.bo_table='".$_REQUEST["bo_table"]."'";
	$table_text = $_REQUEST["bo_table"];
}
// 페이지네이트
$sql_common = "
  FROM
    g5_board_log a 
	left join g5_member b on a.mb_id=b.mb_id
";

$sql_common .= $sql_search;

// 페이지네이트
$sql = " select count(*) as cnt " . $sql_common;

$row = sql_fetch($sql, true);
$total_count = $row['cnt'];
$page_rows = (int)$page_rows ? (int)$page_rows : $config['cf_page_rows'];
$rows = $page_rows;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql_order = " ORDER BY create_time DESC ";//기본 정렬

//echo $sql;
$sql  = "
  select *, b.mb_name,b.mb_giup_bnum
  $sql_common  
  $sql_order
  limit $from_record, $rows
";
$result = sql_query($sql);

$board_log_list = array();
while( $row = sql_fetch_array($result) ) {
  $board_log_list[] = $row;
}
$i = 0;
foreach($board_log_list as $row) {
	$num = $total_count - $i ;
	$data[] = [
		$num." ",
		$row["bo_table"],
		$row['wr_id']." ",
		$row['mb_id'],
		$row["mb_name"],  
		$row["mb_giup_bnum"],
		$row['create_time'],	
	];
	$i++;
}
$search_where = "검색조건-".$table_text.", 기간조건-".$frto_text.", 키워드 검색 : (".$search_select_text.")".$search_text;

$title = ['No','Table','wr_id','회원아이디','회원명','사업소코드','조회일'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
$excel->setActiveSheetIndex(0)->mergeCells('E3:G3');

// 시트 네임
$sheet->setTitle("게시판 사용자 조회로그");

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:G%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:G%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 금액 우측 정렬
//$sheet -> getStyle(sprintf("K5:N%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle("A4:G4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle("A4:G4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$excel->setActiveSheetIndex(0)->setCellValue('A1', "게시판 사용자 조회로그"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('E3',$search_where);
$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(30);
$sheet->getColumnDimension('E')->setWidth(50);
$sheet->getColumnDimension('F')->setWidth(30);
$sheet->getColumnDimension('G')->setWidth(30);

$excel->setActiveSheetIndex(0)->getStyle(sprintf("C5:C%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
$excel->setActiveSheetIndex(0)->getStyle(sprintf("F5:F%s", ($last_row+3)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"게시판_사용자_조회로그_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

?>
