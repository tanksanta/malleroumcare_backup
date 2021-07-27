<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date || !$to_date) alert('유효하지 않은 요청입니다.');

$where_time = " and (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
$where_ledger_time = " and (lc_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

$sql_search = " and o.mb_id = '{$member['mb_id']}' ";
$sql_ledger_search = " and l.mb_id = '{$member['mb_id']}' ";

# 매출
$sql_order = "
  SELECT
    o.od_time,
    o.od_id,
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
    o.od_b_name,
    1 as custom_order,
    c.ct_id as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
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
    '^배송비' as it_name,
    '' as ct_option,
    1 as ct_qty,
    o.od_send_cost as price_d,
    ROUND(o.od_send_cost / 1.1) as price_d_p,
    ROUND(o.od_send_cost / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name,
    2 as custom_order,
    0 as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
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
    '^매출할인' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-o.od_sales_discount) as price_d,
    ROUND(-o.od_sales_discount / 1.1) as price_d_p,
    ROUND(-o.od_sales_discount / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name,
    3 as custom_order,
    0 as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_sales_discount > 0
";

# 쿠폰할인
$coupon_price = "(o.od_cart_coupon + o.od_coupon + o.od_send_coupon)";
$sql_sales_coupon = "
  SELECT
    o.od_time,
    o.od_id,
    '^쿠폰할인' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-$coupon_price) as price_d,
    ROUND(-$coupon_price / 1.1) as price_d_p,
    ROUND(-$coupon_price / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name,
    4 as custom_order,
    0 as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    $coupon_price > 0
";

# 입금/출금
$sql_ledger = "
  SELECT
    lc_created_at as od_time,
    '' as od_id,
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
    '' as od_b_name,
    0 as custom_order,
    0 as custom_sub_order
  FROM
    ledger_content l
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
    ({$sql_sales_coupon} {$sql_search} {$where_time} GROUP BY o.od_id)
    UNION ALL
    ({$sql_ledger} {$sql_ledger_search} {$where_ledger_time})
  ) u
";

$result = sql_query("
  SELECT
    *
  {$sql_common}
  ORDER BY
    od_time ASC,
    od_id ASC,
    custom_order ASC,
    custom_sub_order ASC
");

$ledgers = [];
$carried_balance = get_outstanding_balance($member['mb_id'], $fr_date);
$balance = $carried_balance;

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

$title = ["회사명 : (주)티에이치케이컴퍼니/{$member['mb_entNm']}/{$fr_date} ~ {$to_date}"];
$headers = ['일자', '품목명[규격]', '수량', '단가(Vat포함)', '공급가액', '부가세', '판매', '수금', '잔액', '수령인'];
$widths = [10, 20, 6, 12, 12, 12, 12, 12, 12, 15];
$last_char = column_char(count($headers) - 1);

$rows = [];
// 이월잔액부터 채움
if($carried_balance) {
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

foreach($ledgers as $row) {
  $rows[] = [
    date('y/m/d', strtotime($row['od_time'])),
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
