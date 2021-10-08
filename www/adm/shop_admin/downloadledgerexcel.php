<?php

	include_once("./_common.php");

	auth_check($auth["400400"], "r");
	include_once(G5_LIB_PATH."/PHPExcel.php");
	function column_char($i) { return chr( 65 + $i ); }

    $headers = array("사업소명", "아이디", "영업담당자", "총구매액", "총미수금", "수금금액(숫자만 입력하세요)", "기준일(ex.20210101)");
    $data = array($headers);
    
    $widths  = array(30, 30, 30, 30, 30, 30, 30);
    $header_bgcolor = 'FFABCDEF';
    $last_char = column_char(count($headers) - 1);

    $excel = new PHPExcel();
    $excel->setActiveSheetIndex(0)
        ->getStyle( "A1:${last_char}1" )
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB($header_bgcolor);

    $excel->setActiveSheetIndex(0)
        ->getStyle( "A:$last_char" )
        ->getAlignment()
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
        ->setWrapText(true);

    foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    $excel->getActiveSheet()->fromArray($data,NULL,'A1');

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"uploadledgerexcel.xls\"");
    header("Cache-Control: max-age=0");

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');

?>