<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$file = $_FILES['datafile']['tmp_name'];
if(!$file)
  json_response(400, '파일을 선택해주세요.');

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load($file);
$sheet = $excel->getActiveSheet();

$num_rows = $sheet->getHighestRow();

$data = [];
for($i = 3; $i < $num_rows; $i += 2) {
  $cell_name = $sheet->getCell('B'.$i)->getValue();
  $cell_name = explode("\n ", $cell_name);
  $pen_nm = $cell_name[0];
  $pen_type = $cell_name[1];

  $pen_jumin = $sheet->getCell('C'.$i)->getValue();
  $pen_ltm_num = $sheet->getCell('C'.($i + 1))->getValue();

  $cell_item = $sheet->getCell('D'.$i)->getValue();
  $cell_item = explode("/", $cell_item);
  $ca_name = $cell_item[0];
  $it_name = $cell_item[1];

  $cell_code = $sheet->getCell('D'.($i + 1))->getValue();
  $cell_code = explode("-", $cell_code);
  $it_code = $cell_code[0];
  $it_barcode = $cell_code[1];

  $cell_gubun = $sheet->getCell('E'.$i)->getValue();
  if($cell_gubun == '판매')
    $gubun = '00';
  else if($cell_gubun == '대여')
    $gubun = '01'; 
  
  $contract_date = $sheet->getCell('F'.$i)->getValue();

  $cell_date = $sheet->getCell('F'.($i + 1))->getValue();
  if($cell_gubun == '대여') {
    $cell_date = explode('~', $cell_date);
    $sale_date = date('Y-m-d', strtotime($cell_date[0]));
    $rent_date = date('Y-m-d', strtotime($cell_date[1]));
  } else {
    $sale_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($cell_date));
    $rent_date = '0000-00-00';
  }

  $data[] = array(
    'pen_nm' => $pen_nm,
    'pen_type' => $pen_type,
    'pen_jumin' => $pen_jumin,
    'pen_ltm_num' => $pen_ltm_num,

    'ca_name' => $ca_name,
    'it_name' => $it_name,
    'it_code' => $it_code,
    'it_barcode' => $it_barcode,

    'gubun' => $gubun,

    'contract_date' => $contract_date,
    'sale_date' => $sale_date,
    'rent_date' => $rent_date
  );
}

json_response(200, 'OK', $data);
?>
