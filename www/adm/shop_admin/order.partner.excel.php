<?php
include_once("./_common.php");

auth_check($auth["400400"], "r");

$ct_id_arr = $_POST['od_id'];
if(!is_array($ct_id_arr))
  alert('선택한 주문이 없습니다.');

$data = [];
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
      o.od_b_tel
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
  
  $data[] = [
    $ct['it_name'],
    $ct['ct_qty'],
    $ct['od_b_name'],
    sprintf("(%s%s)", $ct['od_b_zip1'], $ct['od_b_zip2']).' '.print_address($ct['od_b_addr1'], $ct['od_b_addr2'], $ct['od_b_addr3'], $ct['od_b_addr_jibeon']),
    $ct['od_b_hp'] ?: $ct['od_b_tel'],
    $ct['prodMemo']
  ];

  sql_query("
    UPDATE g5_shop_cart
    SET ct_is_delivery_excel_downloaded = 1
    WHERE ct_id = '{$ct_id}'
  ");
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load(G5_DATA_PATH.'/purchase_order_form.xlsx');
$sheet = $excel->getActiveSheet();

$sheet->fromArray($data,NULL,'C12');
$sheet->setCellValue('B9', date('Y년 m월 d일'));

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"구매발주서.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
?>
