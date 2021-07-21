<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$where = [];
$where_ledger = [];

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');
$where_time = " and (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
$where_ledger_time = " and (lc_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

# 영업담당자
if(!$mb_manager)
  $mb_manager = [];
$where_manager = [];
if(!$mb_manager_all && $mb_manager) {
  foreach($mb_manager as $man) {
    $qstr .= "mb_manager%5B%5D={$man}&amp;";
    $where_manager[] = " m.mb_manager = '$man' ";
  }
  $where[] = ' ( ' . implode(' or ', $where_manager) . ' ) ';
  $where_ledger[] = ' ( ' . implode(' or ', $where_manager) . ' ) ';
}

# 금액
$sel_price_field = in_array($sel_price_field, ['price_d', 'price_d_p', 'price_d_s', 'price_d*ct_qty']) ? $sel_price_field : '';
$where_price = '';
if($price && $sel_price_field && $price_s <= $price_e) {
  $price_s = intval($price_s);
  $price_e = intval($price_e);
  $where_price = " where ({$sel_price_field} between {$price_s} and {$price_e}) ";
}

# 검색어
$sel_field = in_array($sel_field, ['mb_entNm', 'o.od_id', 'c.it_name', 'o.od_b_name']) ? $sel_field : '';
$search = get_search_string($search);
if($search && $sel_field) {
  $where[] = " {$sel_field} LIKE '%{$search}%' ";
  if($sel_field == 'mb_entNm')
    $where_ledger[] = " {$sel_field} LIKE '%{$search}%' ";
  else
    $where_ledger[] = " 1 != 1 ";
}

$sql_search = '';
$sql_ledger_search = '';
if($where) {
  $sql_search = ' and '.implode(' and ', $where);
}
if($where_ledger) {
  $sql_ledger_search = ' and '.implode(' and ', $where_ledger);
}

# 매출
$sql_order = "
  SELECT
    o.od_time,
    o.od_id,
    m.mb_entNm,
    c.it_name,
    c.ct_option,
    (c.ct_qty - c.ct_stock_qty) as ct_qty,
    (
      (
        (c.ct_qty - c.ct_stock_qty) *
        CASE
          WHEN c.io_type = 0
          THEN c.ct_price + c.io_price
          ELSE c.io_price
        END - c.ct_discount
      ) / (c.ct_qty - c.ct_stock_qty)
    ) as price_d,
    (
      CASE
        WHEN i.it_taxInfo = '영세'
        THEN
          (
            (c.ct_qty - c.ct_stock_qty) *
            CASE
              WHEN c.io_type = 0
              THEN c.ct_price + c.io_price
              ELSE c.io_price
            END - c.ct_discount
          )
        ELSE
          ROUND(
            (
              (c.ct_qty - c.ct_stock_qty) *
              CASE
                WHEN c.io_type = 0
                THEN c.ct_price + c.io_price
                ELSE c.io_price
              END - c.ct_discount
            ) / 1.1
          )
      END
    ) as price_d_p,
    (
      CASE
        WHEN i.it_taxInfo = '영세'
        THEN 0
        ELSE
          ROUND (
            (
              (
                (c.ct_qty - c.ct_stock_qty) *
                CASE
                  WHEN c.io_type = 0
                  THEN c.ct_price + c.io_price
                  ELSE c.io_price
                END - c.ct_discount
              )
            ) / 1.1 / 10
          )
      END
    ) as price_d_s,
    0 as deposit,
    o.od_b_name
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_member m ON o.mb_id = m.mb_id
  LEFT JOIN
    g5_shop_item i ON i.it_id = c.it_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0
";

# 배송비
$sql_send_cost = "
  SELECT
    o.od_time,
    o.od_id,
    m.mb_entNm,
    '^배송비' as it_name,
    '' as ct_option,
    1 as ct_qty,
    o.od_send_cost as price_d,
    ROUND(o.od_send_cost / 1.1) as price_d_p,
    ROUND(o.od_send_cost / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_member m ON o.mb_id = m.mb_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_send_cost > 0
";

# 매출할인
$sql_sales_discount = "
  SELECT
    o.od_time,
    o.od_id,
    m.mb_entNm,
    '^매출할인' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-o.od_sales_discount) as price_d,
    ROUND(-o.od_sales_discount / 1.1) as price_d_p,
    ROUND(-o.od_sales_discount / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_member m ON o.mb_id = m.mb_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_sales_discount > 0
";

# 입금/출금
$sql_ledger = "
  SELECT
    lc_created_at as od_time,
    '' as od_id,
    m.mb_entNm,
    (
      CASE
        WHEN lc_type = 1
        THEN '입금'
        WHEN lc_type = 2
        THEN '출금'
      END
    ) as it_name,
    lc_memo as ct_option,
    1 as ct_qty,
    (
      CASE
        WHEN lc_type = 2
        THEN lc_amount
        ELSE 0
      END
    ) as price_d,
    (
      CASE
        WHEN lc_type = 2
        THEN lc_amount
        ELSE 0
      END
    ) as price_d_p,
    0 as price_d_s,
    (
      CASE
        WHEN lc_type = 1
        THEN lc_amount
        ELSE 0
      END
    ) as deposit,
    '' as od_b_name
  FROM
    ledger_content l
  LEFT JOIN
    g5_member m ON l.mb_id = m.mb_id
  WHERE
    1 = 1
";


$sql_common = "
FROM
  (
    ({$sql_order} {$sql_search} {$where_time})
    UNION ALL
    ({$sql_send_cost} {$sql_search} {$where_time} GROUP BY o.od_id)
    UNION ALL
    ({$sql_sales_discount} {$sql_search} {$where_time} GROUP BY o.od_id)
    UNION ALL
    ({$sql_ledger} {$sql_ledger_search} {$where_ledger_time})
  ) u
";

$result = sql_query("
  SELECT
    *
  {$sql_common}
  {$where_price}
  ORDER BY
    od_time asc,
    od_id asc
");

$ledgers = [];
$balance = 0;

// 누계
$total_qty = 0;
$total_price_d = 0;
$total_price_d_p = 0;
$total_price_d_s = 0;
$total_sales = 0;
$total_deposit = 0;

while($row = sql_fetch_array($result)) {
  $total_qty += $row['ct_qty'];
  $total_price_d += $row['price_d'];
  $total_price_d_p += $row['price_d_p'];
  $total_price_d_s += $row['price_d_s'];
  $total_sales += ($row['price_d'] * $row['ct_qty']);
  $total_deposit += $row['deposit'];

  $balance += ($row['price_d'] * $row['ct_qty']);
  $balance -= ($row['deposit']);
  $row['balance'] = $balance;
  $ledgers[] = $row;
}

if(! function_exists('column_char')) {
  function column_char($i) {
    return chr( 65 + $i );
  }
}

include_once(G5_LIB_PATH.'/PHPExcel.php');

$title = ["회사명 : (주)티에이치케이컴퍼니/{$fr_date} ~ {$to_date}"];
$headers = ['일자-주문번호', '사업소', '품목명[규격]', '수량', '단가(Vat포함)', '공급가액', '부가세', '판매', '수금', '잔액', '수령인'];
$widths = [25, 20, 20, 6, 12, 12, 12, 12, 12, 12, 15];
$last_char = column_char(count($headers) - 1);

$rows = [];

foreach($ledgers as $row) {
  $rows[] = [
    date('y/m/d', strtotime($row['od_time'])).($row['od_id'] ? '-'.$row['od_id'] : ''),
    $row['mb_entNm'],
    $row['it_name'].($row['ct_option'] ? " [{$row['ct_option']}]" : ''),
    $row['ct_qty'],
    $row['price_d'],
    $row['price_d_p'],
    $row['price_d_s'],
    $row['price_d'] * $row['ct_qty'],
    $row['deposit'],
    $row['balance'],
    $row['od_b_name']
  ];
}

$totals = [
  '누계',
  '',
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
$excel->getActiveSheet()->getStyle('A1:K2')->getFont()->setSize(11);
$excel->getActiveSheet()->getStyle('A1:K2')->getFont()->setBold(true);

// number format 처리
$excel->getActiveSheet()->getStyle('D3:J'.(count($rows) + 3))->getNumberFormat()->setFormatCode('#,##0_-');

// 테두리 처리
$styleArray = array(
  'borders' => array(
    'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);
$excel->getActiveSheet()->getStyle('A2:K'.(count($rows) + 3))->applyFromArray($styleArray);

foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"거래처원장.xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
