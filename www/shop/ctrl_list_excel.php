<?php
include_once("./_common.php");
$list_data = $_POST['list_data'];
if(!is_array($list_data))
  alert('조건과 일치하는 데이터가 없습니다.');



include_once(G5_LIB_PATH."/PHPExcel.php");
$excel = new PHPExcel();

$widths  = [10,10,20,20,30,30,30,30,15,30,25];
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);

$sheet = $excel->getActiveSheet();
$sheet->mergeCells('B2:K2')->setCellValue('B2', "제품관리대장");
$sheet->mergeCells('G4:H4')->setCellValue('G4', "제품코드");
$sheet->setCellValue('G5', "품목코드");
$sheet->setCellValue('H5', "바코드");
$sheet->mergeCells('B4:B5')->setCellValue('B4', "연번");
$sheet->mergeCells('C4:C5')->setCellValue('C4', "일자");
$sheet->mergeCells('D4:D5')->setCellValue('D4', "수급자명");
$sheet->mergeCells('E4:E5')->setCellValue('E4', "품목명");
$sheet->mergeCells('F4:F5')->setCellValue('F4', "제품명");
$sheet->mergeCells('I4:I5')->setCellValue('I4', "구분");
$sheet->mergeCells('J4:J5')->setCellValue('J4', "대여기간");
$sheet->mergeCells('K4:K5')->setCellValue('K4', "배송구분");

$sheet->mergeCells('J3:K3')->setCellValue('J3', $_POST['search_period']);
$sheet->getStyle('B:K')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //오른쪽 정렬
$sheet->getStyle('J3:K3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT); //오른쪽 정렬
$sheet
    ->getStyle( 'B:K' )
    ->getAlignment()
    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
    ->setWrapText(true);
$sheet->getStyle( 'B:K' )->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
$TITLE_STYLE = array(
    // 정렬
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
    ),
    //글자색 설정
    'font' => array(
     'bold' => 'true',
     'size' => '16',
     'color' => array('rgb'=>'000000')
    )
);

$TD_STYLE = array(
    // 정렬
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
    ),
    //글자색 설정
    'font' => array(
     'bold' => 'true',
     'size' => '11',
     'color' => array('rgb'=>'000000')
    ),

    //테두리 설정
    'borders' => array(
     'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN,
      'color' => array('argb'=>'000000')
     )
    ),

);

$sheet->getStyle('B2:K2')->applyFromArray($TITLE_STYLE);
$sheet->getStyle('B4:K5')->applyFromArray($TD_STYLE);

$sheet = $excel->getActiveSheet();

$sheet->fromArray($list_data,NULL,'B6');

// 타이틀 열 높이
$sheet->getRowDimension(2)->setRowHeight(35);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"제품관리대장.xls\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

function column_char($i) { return chr( 65 + $i ); }
?>
