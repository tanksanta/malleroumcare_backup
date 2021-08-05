<?php
include_once('./_common.php');

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');

$ledger_result = get_partner_ledger($member['mb_id'], $fr_date, $to_date, $sel_field, $search);
$total_price = $ledger_result['total_price'];
$total_price_p = @round(($total_price ?: 0) / 1.1);
$total_price_s = @round(($total_price ?: 0) / 1.1 / 10);
$carried_balance = $ledger_result['carried_balance'];
$ledgers = $ledger_result['ledger'];

if(! function_exists('column_char')) {
  function column_char($i) {
    return chr( 65 + $i );
  }
}

include_once(G5_LIB_PATH.'/PHPExcel.php');

$title = ["회사명 : (주)티에이치케이컴퍼니/{$member['mb_entNm']}/{$fr_date} ~ {$to_date}"];
$headers = ['일자-주문번호', '품목명[규격]', '수량', '단가(Vat포함)', '공급가액', '부가세', '판매', '수금', '잔액', '수령인'];
$widths = [25, 20, 6, 12, 12, 12, 12, 12, 12, 15];
$last_char = column_char(count($headers) - 1);

$rows = [];
// 이월잔액부터 채움
if($carried_balance && !($sel_field && $search) && !$price) {
  $rows[] = [
    '',
    '이월잔액',
    '',
    '',
    '',
    '',
    '',
    '',
    $carried_balance,
    ''
  ];
}

// 누계
$total_qty = 0;
$total_price_d = 0;
$total_price_d_p = 0;
$total_price_d_s = 0;
$total_sales = 0;
$total_deposit = 0;

foreach($ledgers as $row) {
  $rows[] = [
    date('y/m/d', strtotime($row['od_time'])).($row['od_id'] ? '-'.$row['od_id'] : ''),
    $row['it_name'].($row['ct_option'] && $row['ct_option'] != $row['it_name'] ? " [{$row['ct_option']}]" : ''),
    $row['ct_qty'],
    $row['price_d'],
    @round(($row['sales'] ?: 0) / 1.1),
    @round(($row['sales'] ?: 0) / 1.1 / 10),
    $row['sales'],
    $row['deposit'],
    $row['balance'],
    $row['od_b_name']
  ];

  $total_qty += $row['ct_qty'];
  $total_price_d += $row['price_d'];
  $total_sales += $row['sales'];
  $total_deposit += $row['deposit'];
}
$total_price_d_p = @round(($total_sales ?: 0) / 1.1);
$total_price_d_s = @round(($total_sales ?: 0) / 1.1 / 10);

$totals = [
  '누계',
  '',
  $total_qty,
  $total_price_d,
  $total_price_d_p,
  $total_price_d_s,
  $total_sales,
  $total_deposit
];

// 날짜 표시
$now = time();
$am_or_pm = date('a', $now);
if($am == 'am')
  $date = date('Y/m/d', $now).'  오전 '.date('h:i:s', $now);
else
  $date = date('Y/m/d', $now).'  오후 '.date('h:i:s', $now);
$dates = [$date];

$data = array_merge([$title], [$headers], $rows, [$totals], [$dates]);

$excel = new PHPExcel();
$styleArray = array(
  'font' => array(
    'size' => 10,
    'name' => 'Arial'
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  )
);
$excel->getDefaultStyle()->applyFromArray($styleArray);
$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);

// 폰트&볼드 처리
$excel->getActiveSheet()->getStyle('A1:J2')->getFont()->setSize(11);
$excel->getActiveSheet()->getStyle('A1:J2')->getFont()->setBold(true);

// number format 처리
$excel->getActiveSheet()->getStyle('C3:I'.(count($rows) + 3))->getNumberFormat()->setFormatCode('#,##0_-');

// 테두리 처리
$styleArray = array(
  'borders' => array(
    'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);
$excel->getActiveSheet()->getStyle('A2:J'.(count($rows) + 3))->applyFromArray($styleArray);

foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"거래처원장.xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
