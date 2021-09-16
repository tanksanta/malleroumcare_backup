<?php
include_once("./_common.php");

if(!$is_samhwa_partner)
  alert("파트너 회원만 접근 가능한 페이지입니다.");

$ct_id_arr = $_POST['ct_id'];
if(!is_array($ct_id_arr))
  alert('선택한 주문이 없습니다.');

$widths  = [20, 15, 25, 10, 30, 20, 20, 30, 20, 20, 30, 30];
$headers = [
  '주문번호',
  '일자',
  '품목명[규격]',
  '수량',
  '품목&수량',
  '주문사업소',
  '배송지명',
  '배송처',
  '연락처',
  '휴대폰',
  '상품요청사항',
  '배송요청사항'
];
$data = [];

foreach($ct_id_arr as $ct_id) {
  $ct = sql_fetch("
    SELECT
      c.*,
      o.od_time,
      o.od_name,
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
      ct_id = '{$ct_id}' and
      ct_is_direct_delivery IN(1, 2) and
      ct_direct_delivery_partner = '{$member['mb_id']}'
  ");

  if(!$ct['ct_id'])
    continue;

  $ct['it_name'] .= $ct['ct_option'] && $ct['ct_option'] != $ct['it_name'] ? " ({$ct['ct_option']})" : '';
  
  $data[] = [
    ' '.$ct['od_id'],
    date('Y-m-d', strtotime($ct['od_time'])),
    $ct['it_name'],
    $ct['ct_qty'],
    "{$ct['it_name']} / {$ct['ct_qty']} EA",
    $ct['od_name'],
    $ct['od_b_name'],
    print_address($ct['od_b_addr1'], $ct['od_b_addr2'], $ct['od_b_addr3'], $ct['od_b_addr_jibeon']),
    $ct['od_b_tel'],
    $ct['od_b_hp'],
    $ct['prodMemo'],
    $ct['od_memo']
  ];
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$excel = new PHPExcel();
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$sheet = $excel->getActiveSheet();

$data = array_merge(array($headers), $data);
$sheet->fromArray($data,NULL,'A1');

$last_col = count($headers);
$last_char = column_char($last_col - 1);
$last_row = count($data);
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
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
  )
);
$sheet->getStyle('A1:'.$last_char.$last_row)->applyFromArray($styleArray);

// 헤더 배경
$header_bgcolor = 'FFD3D3D3';
$sheet
  ->getStyle( "A1:${last_char}1" )
  ->getFill()
  ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
  ->getStartColor()
  ->setARGB($header_bgcolor);

// 헤더 폰트 굵기
$sheet
  ->getStyle( "A1:${last_char}1" )
  ->getFont()
  ->setBold(true);

// 열 높이
for($i = 1; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(35);
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"주문내역.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

function column_char($i) { return chr( 65 + $i ); }
?>
