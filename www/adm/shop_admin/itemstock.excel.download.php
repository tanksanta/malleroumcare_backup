<?php
$sub_menu = '400620';
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];

$use_warehouse_where_sql = get_use_warehouse_where_sql(false);
$sql = "
  SELECT it.it_id, it.it_name, io.io_id, io.io_standard, it.it_option_subject,it_use,it_soldout,pt_it, ws.ws_option, IFNULL(ws.ws_qty, '0') AS ws_qty
  FROM g5_shop_item it
  LEFT JOIN (SELECT * FROM g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS io ON it.it_id = io.it_id
  LEFT JOIN (SELECT it_id, io_id, ws_option, (SUM(ws_qty) - SUM(ws_scheduled_qty)) AS ws_qty FROM warehouse_stock WHERE {$use_warehouse_where_sql} AND ws_del_yn = 'N' 
  GROUP BY it_id, io_id) AS ws ON (it.it_id = ws.it_id AND IFNULL(io.io_id, '') = ws.io_id)";
if($_POST["wh_name"] != ""){
	$sql .= " WHERE ( SELECT (SUM(ws_qty) - SUM(ws_scheduled_qty)) FROM warehouse_stock s WHERE it.it_id = s.it_id 
   AND it.it_warehousing_warehouse = '".$_POST["wh_name"]."' AND 
   ws_del_yn = 'N'  AND {$use_warehouse_where_sql}  ) <> 0";
}

$sql .= " ORDER BY it.it_name ASC";

$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {
  $io_value = '';
  if ($row['io_id'] && $row['it_option_subject']) {
    $it_option_subjects = explode(',', $row['it_option_subject']);
    $io_ids = explode(chr(30), $row['io_id']);

    for ($g = 0; $g < count($io_ids); $g++) {
      if ($g > 0) {
        $io_value .= ' / ';
      }
      $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
    }
  }

  $row['io_value'] = $io_value;
	if($_POST['wh_name'] != ""){
		$sql = " select (sum(ws_qty) - sum(ws_scheduled_qty)) as stock from warehouse_stock where it_id = '{$row['it_id']}' and io_id = '{$row['io_id']}' and wh_name = '{$_POST['wh_name']}' and ws_del_yn = 'N' and {$use_warehouse_where_sql} ";
		$stock = (sql_fetch($sql)['stock']!="")?sql_fetch($sql)['stock']:0;
		$wh_name = $_POST['wh_name'];
	}else{
		$stock = $row['ws_qty'];
		$wh_name = "전체창고";
	}
	if($row['pt_it'] == "1"){
		$pt_it = "일반상품";
	}elseif($row['pt_it'] == "2"){
		$pt_it = "컨텐츠상품";
	}
	if($row['it_use'] == "1"){
		$it_use = "O";
	}else{
		$it_use = "X";
	}
	if($row['it_soldout'] == "1"){
		$it_soldout = "O";
	}else{
		$it_soldout = "X";
	}
  $data[] = [
    $pt_it,
	$row['it_id'],
	$row['it_name'],  
	$row['io_id'],
    $row['io_value'],
	$stock,
	$it_use,
	$it_soldout,
  ];
}

$title = ['구분','상품코드','상품명','옵션코드','옵션명','수량','판매','품절'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:H1');
$excel->setActiveSheetIndex(0)->mergeCells('F3:H3');

// 시트 네임
$sheet->setTitle("상품재고현황_".$wh_name);

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:H%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:H%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
if(count($data)>0){
	$sheet->getColumnDimension('A')->setAutoSize(true);
	$sheet->getColumnDimension('B')->setAutoSize(true);
	$sheet->getColumnDimension('C')->setAutoSize(true);
	$sheet->getColumnDimension('D')->setAutoSize(true);
	$sheet->getColumnDimension('E')->setAutoSize(true);
}else{
	$sheet->getColumnDimension('A')->setWidth(10);
	$sheet->getColumnDimension('B')->setWidth(20);
	$sheet->getColumnDimension('C')->setWidth(40);
	$sheet->getColumnDimension('D')->setWidth(20);
	$sheet->getColumnDimension('E')->setWidth(30);
}
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(8);
$sheet->getColumnDimension('H')->setWidth(8);

$sheet->getStyle("A4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle("B4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle("C4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle("D4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle("E4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle("F4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle("G4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle("H4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');


$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('F3')->getFont()->setBold(true);
$sheet->getStyle("A4")->getFont()->setBold(true);
$sheet->getStyle("B4")->getFont()->setBold(true);
$sheet->getStyle("C4")->getFont()->setBold(true);
$sheet->getStyle("D4")->getFont()->setBold(true);
$sheet->getStyle("E4")->getFont()->setBold(true);
$sheet->getStyle("F4")->getFont()->setBold(true);
$sheet->getStyle("G4")->getFont()->setBold(true);
$sheet->getStyle("H4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$excel->setActiveSheetIndex(0)->setCellValue('A1', "상품 재고 현황"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('F3', "창고명 - ".$wh_name);

$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"상품재고현황_".$wh_name."_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
?>
