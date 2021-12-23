<?php
$sub_menu = '200400';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check)
    json_response(400, $auth_check);

$file = $_FILES['datafile']['tmp_name'];
if(!$file)
  json_response(400, '파일을 선택해주세요.');

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel5');
$excel = $reader->load($file);
$sheet = $excel->getActiveSheet();

$num_rows = $sheet->getHighestDataRow();

$data = [];
for($row = 2; $row <= $num_rows; $row++) {
    $mb_id = trim($sheet->getCell("A{$row}")->getValue());
    $mb_id = get_search_string($mb_id);

    if(!$mb_id)
        continue;
    
    $mb_name = trim($sheet->getCell("B{$row}")->getValue());
    $mb_name = clean_xss_tags($mb_name);

    $data[] = [
        'mb_id' => $mb_id,
        'mb_name' => $mb_name
    ];
}

json_response(200, 'OK', $data);
