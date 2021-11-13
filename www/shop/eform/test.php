<?php
include_once("./_common.php");

if($is_admin != 'super') alert('접근 ㄴㄴ');

$result = get_biztalk_result();
print_r2($result);

/*
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel5');
$excel = $reader->load('./boxsize.xls');
$sheet = $excel->getActiveSheet();

$num_rows = $sheet->getHighestRow();

for($i = 2; $i <= $num_rows; $i++) {
  $it_id = sql_real_escape_string(trim($sheet->getCell('C'.$i)->getValue()));
  $it_box_size_width = sql_real_escape_string(trim($sheet->getCell('AH'.$i)->getValue()));
  $it_box_size_length = sql_real_escape_string(trim($sheet->getCell('AI'.$i)->getValue()));
  $it_box_size_height = sql_real_escape_string(trim($sheet->getCell('AJ'.$i)->getValue()));

  if(!$it_id) continue;

  // 박스 규격
  $it_box_size = [
    $it_box_size_width,
    $it_box_size_length,
    $it_box_size_height
  ];
  $it_box_size = implode(chr(30), $it_box_size);

  sql_query(" UPDATE g5_shop_item SET it_box_size = '$it_box_size' WHERE it_id = '$it_id' ");
}

echo 'OK';*/

?>
