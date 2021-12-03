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

if(! function_exists('column_char')) {
    function column_char($i) {
        $div = intval($i / 26);
        $char = chr( 65 + ($i % 26) );

        if($div > 0) {
            return column_char($div - 1) . $char;
        } else {
            return $char;
        }
    }
}

include_once(G5_LIB_PATH.'/PHPExcel.php');
$headers = ['상품관리코드', '상품명', '급여가', '공급가'];

$excel = new PHPExcel();
$sheet = $excel->setActiveSheetIndex(0);

$styleArray = [
    'alignment' => [
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ]
];
//$excel->getDefaultStyle()->applyFromArray($styleArray);
$sheet->getStyle('A1:D1')->applyFromArray($styleArray);

// 제목
$sheet->mergeCells('A1:A2')->setCellValue('A1', '상품관리코드');;
$sheet->mergeCells('B1:B2')->setCellValue('B1', '상품명');
$sheet->mergeCells('C1:C2')->setCellValue('C1', '급여가');
$sheet->mergeCells('D1:D2')->setCellValue('D1', '공급가');

// 행(사업소)
$last_col_idx = 0;
foreach($ents as $idx => $ent) {
    $col = column_char($idx + 4);
    $last_col_idx = $idx + 4;

    $sheet->setCellValue("{$col}1", $ent['mb_id']);
    $sheet->getStyle("{$col}1")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $sheet->getStyle("{$col}1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue("{$col}2", $ent['mb_name']);
    $sheet->getStyle("{$col}2")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $sheet->getStyle("{$col}2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
}

// 행 너비
for($i = 0; $i <= $last_col_idx; $i++) {
    //if($i < 4) {
        $sheet->getColumnDimension( column_char($i) )->setWidth(20);
    //}
}

foreach($items as $it_idx => $it) {
    $row = $it_idx + 3;

    $sheet->setCellValue("A{$row}", $it['it_id']);
    $sheet->setCellValue("B{$row}", $it['it_name']);
    $sheet->setCellValue("C{$row}", $it['it_cust_price']);
    $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
    $sheet->setCellValue("D{$row}", $it['it_price']);
    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');

    foreach($ents as $ent_idx => $ent) {
        $col = column_char($ent_idx + 4);
        $sheet->setCellValue("{$col}{$row}", $it['entprice'][$ent['mb_id']]);
        $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
    }
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"상품가격관리.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
