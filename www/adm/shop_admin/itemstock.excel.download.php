<?php
$sub_menu = '400620';
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];

$use_warehouse_where_sql = get_use_warehouse_where_sql(false);
$sql = "
  SELECT it.it_id, it.it_name, io.io_id, io.io_standard, it.it_option_subject, ws.ws_option, IFNULL(ws.ws_qty, '0') AS ws_qty
  FROM g5_shop_item it
  LEFT JOIN (SELECT * FROM g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS io ON it.it_id = io.it_id
  LEFT JOIN (SELECT it_id, io_id, ws_option, (SUM(ws_qty) - SUM(ws_scheduled_qty)) AS ws_qty FROM warehouse_stock WHERE {$use_warehouse_where_sql} GROUP BY it_id, io_id) AS ws ON (io.it_id = ws.it_id AND io.io_id = ws.io_id)
  ORDER BY it.it_name ASC
";

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

  $data[] = [
    $row['it_id'],
    $row['io_id'],
    $row['it_name'],
    $row['io_value'],
    $row['ws_qty'],
  ];
}


// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load(G5_DATA_PATH.'/itemstock_list_form.xlsx');
$sheet = $excel->getActiveSheet();

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;

// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}

// 가운데 정렬
$sheet->getStyle('A:E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$sheet->fromArray($data,NULL,'A2');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"상품재고.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
?>
