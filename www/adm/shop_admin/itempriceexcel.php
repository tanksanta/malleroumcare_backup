<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

// 사업소 가져오기
$sql = "
    SELECT * FROM g5_member
    WHERE mb_manager = '{$member['mb_id']}'
    ORDER BY mb_id ASC
";
$result = sql_query($sql);

$ents = [];
while($ent = sql_fetch_array($result)) {
    $ents[] = $ent;
}

// 상품 가져오기
$sql = "
    SELECT *
    FROM g5_shop_item
    WHERE prodSupYn = 'Y'
    ORDER BY it_id ASC
";
$result = sql_query($sql);

$items = [];
while($item = sql_fetch_array($result)) {
    $entprice = sql_query("
        SELECT * FROM g5_shop_item_entprice
        WHERE it_id = '{$item['it_id']}'
    ");
    $item['entprice'] = [];
    while($ep = sql_fetch_array($entprice)) {
        $item['entprice'][$ep['mb_id']] = $ep['it_price'];
    }

    $items[] = $item;
}

include_once(G5_LIB_PATH.'/PHPExcel.php');

$excel = new PHPExcel();
$sheet = $excel->setActiveSheetIndex(0);

$styleArray = [
    'alignment' => [
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ]
];
$sheet->getStyle('A1:A4')->applyFromArray($styleArray);

// 제목
$sheet->mergeCells('A1:B1')->setCellValue('A1', '상품관리코드');
$sheet->mergeCells('A2:B2')->setCellValue('A2', '상품명');
$sheet->mergeCells('A3:B3')->setCellValue('A3', '급여가');
$sheet->mergeCells('A4:B4')->setCellValue('A4', '공급가');

// 열(사업소)
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(20);
foreach($ents as $idx => $ent) {
    $row = $idx + 5;

    $sheet->setCellValue("A{$row}", $ent['mb_id']);
    $sheet->getStyle("A{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    //$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue("B{$row}", $ent['mb_name']);
    $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
}

// 행(상품)
foreach($items as $it_idx => $it) {
    $col = PHPExcel_Cell::stringFromColumnIndex($it_idx + 2);
    $sheet->getColumnDimension($col)->setWidth(15);

    $sheet->setCellValue("{$col}1", $it['it_id']);
    $sheet->setCellValue("{$col}2", $it['it_name']);
    $sheet->setCellValue("{$col}3", $it['it_cust_price']);
    $sheet->getStyle("{$col}3")->getNumberFormat()->setFormatCode('#,##0');
    $sheet->setCellValue("{$col}4", $it['it_price']);
    $sheet->getStyle("{$col}4")->getNumberFormat()->setFormatCode('#,##0');

    foreach($ents as $ent_idx => $ent) {
        $row = $ent_idx + 5;
        $sheet->setCellValue("{$col}{$row}", $it['entprice'][$ent['mb_id']]);
        $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
    }
}

$sheet->freezePane('C5');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"상품가격관리.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
