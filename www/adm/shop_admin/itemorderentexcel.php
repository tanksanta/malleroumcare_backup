<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$it_id = get_search_string($_GET['it_id']);

if(!$it_id)
    alert('유효하지 않은 요청입니다.');

$sql = "
    select
        mb_id
    from
        g5_shop_cart
    where
        it_id = '$it_id' and
        ct_status in ('준비', '출고준비')
    group by
        mb_id
    order by
        mb_id asc
";

$result = sql_query($sql, true);

$ents = [];
while($row = sql_fetch_array($result)) {
    $ent = get_member($row['mb_id']);

    $ents[] = [ $ent['mb_id'], $ent['mb_name'] ];
}

include_once(G5_LIB_PATH.'/PHPExcel.php');

$headers = ['아이디', '사업소명'];
$widths = [25, 25];

$data = array_merge([$headers], $ents);

$excel = new PHPExcel();
$sheet = $excel->setActiveSheetIndex(0);

$styleArray = [
    
];

$excel->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

foreach($widths as $i => $w) $sheet->getColumnDimension( PHPExcel_Cell::stringFromColumnIndex($i) )->setWidth($w);
$sheet->fromArray($data);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"사업소목록.xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
