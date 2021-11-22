<?php
include_once("./_common.php");

auth_check($auth["400400"], "r");

$ct_id_arr = $_POST['od_id'];
if(!is_array($ct_id_arr))
  alert('선택한 주문이 없습니다.');

$data = [];
$index = 1;
foreach($ct_id_arr as $ct_id) {
  $ct = sql_fetch("
    SELECT
      c.*,
      o.od_b_name,
      o.od_b_zip1,
      o.od_b_zip2,
      o.od_b_addr1,
      o.od_b_addr2,
      o.od_b_addr3,
      o.od_b_addr_jibeon,
      o.od_b_hp,
      o.od_b_tel,
      o.od_memo
    FROM
      g5_shop_cart c
    LEFT JOIN
      g5_shop_order o ON c.od_id = o.od_id
    WHERE
      c.ct_id = '{$ct_id}'
  ");

  if(!$ct['ct_id'])
    continue;
  
  $ct['it_name'] .= $ct['ct_option'] && $ct['ct_option'] != $ct['it_name'] ? " ({$ct['ct_option']})" : '';
  if ($ct['prodMemo']) {
    $memo = $ct['prodMemo'] . "\r\r[배송요청사항]\r" . $ct['od_memo'];
  }
  else {
    $memo = "[배송요청사항]\r" . $ct['od_memo'];
  }
    
  
  $data[] = [
    $index,
    $ct['it_name'],
    $ct['ct_qty'],
    $ct['od_b_name'],
    print_address($ct['od_b_addr1'], $ct['od_b_addr2'], $ct['od_b_addr3'], $ct['od_b_addr_jibeon']),
    $ct['od_b_hp'] ?: $ct['od_b_tel'],
    $memo
  ];

  sql_query("
    UPDATE g5_shop_cart
    SET ct_is_delivery_excel_downloaded = 1
    WHERE ct_id = '{$ct_id}'
  ");

  $index++;
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load(G5_DATA_PATH.'/purchase_order_form.xlsx');
$sheet = $excel->getActiveSheet();

$last_row = count($data) + 11;
if($last_row < 21) $last_row = 21;

// 테두리 처리
$styleArray = array(
  'font' => array(
    'size' => 10,
    'name' => 'Malgun Gothic'
  ),
  'borders' => array(
    'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  )
);
$sheet->getStyle('B11:H'.$last_row)->applyFromArray($styleArray);

// 열 높이
for($i = 11; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle('H12:H'.$last_row)->getAlignment()->setWrapText(true);

// 가운데 정렬
$sheet->getStyle('B11:B'.$last_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('D11:D'.$last_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// 겉 굵은 테두리
$sheet->getStyle('B11:B'.$last_row)->applyFromArray(array(
  'borders' => array(
    'left' => array(
      'style' => PHPExcel_Style_Border::BORDER_MEDIUM
    )
  )
));
$sheet->getStyle('H11:H'.$last_row)->applyFromArray(array(
  'borders' => array(
    'right' => array(
      'style' => PHPExcel_Style_Border::BORDER_MEDIUM
    )
  )
));
$sheet->getStyle('B'.$last_row.':H'.$last_row)->applyFromArray(array(
  'borders' => array(
    'bottom' => array(
      'style' => PHPExcel_Style_Border::BORDER_MEDIUM
    )
  )
));

$sheet->fromArray($data,NULL,'B12');
$sheet->setCellValue('B9', date('Y년 m월 d일'));

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"구매발주서.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
?>
