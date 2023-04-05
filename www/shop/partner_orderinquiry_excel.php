<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch("
  SELECT
    o.*,
    m.mb_temp,
    m.mb_name,
    m.mb_manager,
    mb_entNm,
    mb_giup_btel
  FROM
    {$g5['g5_shop_order_table']} o
  LEFT JOIN
    {$g5['member_table']} m ON o.mb_id = m.mb_id
  WHERE
    od_id = '{$od_id}'
");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

// 임시회원의경우 mb_entNm 대신 mb_name 출력
if($od['mb_temp']) {
  $od['mb_entNm'] = $od['mb_name'];
}

// 담당자
$manager = get_member($od['mb_manager']);

$cart_result = sql_query("
  SELECT
    *
  FROM
    {$g5['g5_shop_cart_table']}
  WHERE
    od_id = '{$od_id}' and
    ct_direct_delivery_partner = '{$member['mb_id']}' and
    ct_is_direct_delivery = '2' and
    ct_status IN('준비', '출고준비', '배송', '완료')
  ORDER BY
    ct_id ASC
");

$carts = [];
while($row = sql_fetch_array($cart_result)) {
  $row['it_name'] .= $row['ct_option'] && $row['ct_option'] != $row['it_name'] ? " ({$row['ct_option']})" : '';

  // 바코드 정보 가져오기
  $sto_id = [];

  foreach(array_filter(explode('|', $row['stoId'])) as $id) {
    $sto_id[] = $id;
  }

  $stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
    'stoId' => implode('|', $sto_id)
  ));
  
  $barcodes = [];
  if($stock_result['data']) {
    foreach($stock_result['data'] as $data) {
      $barcodes[] = $data['prodBarNum'];
    }
  }

  $row['barcode'] = $barcodes;

  $carts[] = $row;
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load(G5_DATA_PATH.'/installation_report.xlsx');
$sheet = $excel->getActiveSheet();

$sheet->setCellValue('U4', '주문접수일 : '.date('   Y년   m월   d일', strtotime($od['od_time'])));

$sheet->setCellValue('G6', $manager ? $manager['mb_name'] . ' 담당자' : '없음');
$sheet->setCellValue('AE6', $manager['mb_hp']);

$sheet->setCellValue('G7', $od['mb_entNm']);
$sheet->setCellValue('AE7', $od['mb_giup_btel']);

$sheet->setCellValue('L8', $od['od_b_name']);
$sheet->setCellValue('AE9', $od['od_b_hp'] ?: $od['od_b_tel']);
$sheet->setCellValue('L10', sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon']));

$total_qty = 0;
$data_index = 0;
foreach($carts as $cart) {
  if($data_index > 5) break;

  // 데이터 입력
  $sheet->setCellValue('A'.(14 + $data_index), $cart['it_name']);
  $sheet->setCellValue('G'.(14 + $data_index), $cart['ct_qty']);
  $sheet->setCellValue('L'.(14 + $data_index), ' '.implode(', ', $cart['barcode']));
  $sheet->setCellValue('AK'.(14 + $data_index), $cart['prodMemo']);

  $total_qty += $cart['ct_qty'];
  $data_index++;
}
$sheet->setCellValue('L11', $od['od_memo']);
$sheet->setCellValue('G20', $total_qty.'개');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"설치확인서.xlsx\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
?>
