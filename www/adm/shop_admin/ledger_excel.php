<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$mb_id = get_search_string($mb_id);

if(!$mb_id)
  alert('유효하지 않은 요청입니다.');

$ent = get_member($mb_id);
if(!$ent['mb_id'])
  alert('존재하지 않는 사업소입니다.');

$mb_manager = sql_fetch("SELECT mb_name from g5_member WHERE mb_id = '{$ent['mb_manager']}'")['mb_name'];

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');

if($ent['mb_type'] == 'partner') {
  // 파트너 거래처원장
  $ledger_result = get_partner_ledger($mb_id, $fr_date, $to_date, $sel_field, $search);
  $ledgers = $ledger_result['ledger'];

  for($i = 0; $i < count($ledgers); $i++) {
    $row = $ledgers[$i];

    $ct_id = $row['ct_id'];

    //급여코드(품목코드) 가져오기
    $it = sql_fetch("
      SELECT cart.*, item.it_thezone2, o.io_thezone as io_thezone2, item.ca_id, it_standard, io_standard
      FROM g5_shop_cart as cart
      INNER JOIN g5_shop_item as item ON cart.it_id = item.it_id
      LEFT JOIN g5_shop_item_option o ON (cart.it_id = o.it_id and cart.io_id = o.io_id)
      WHERE cart.ct_id = '{$ct_id}'
      ORDER BY cart.ct_id ASC
    ");

    $thezone_code = $it['io_thezone2'] ?: $it['io_thezone'] ?: $it['it_thezone2'];
    $row['thezone_code'] = $thezone_code;

    #바코드
    $stoIdDataList = explode('|',$it['stoId']);
    $stoIdDataList = array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);

    $barcode=[];
    $oCurl = curl_init();
    $res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, [
      'stoId' => $stoIdData
    ], 443);
    $result_again = $res;
    $result_again = $result_again['data'];

    for($k=0; $k < count($result_again); $k++) {
      if($result_again[$k]['prodBarNum']) {
        array_push($barcode, $result_again[$k]['prodBarNum']);
      }
    }
    asort($barcode);
    $barcode2 = [];
    $y = 0;
    foreach($barcode as $key=>$val)  
    {  
      $new_key = $y;  
      $barcode2[$new_key] = $val;  
      $y++;  
    }
    $barcode_string="";
    if (!is_benefit_item($it)) {
      for ($y = 0; $y < count($barcode2); $y++) {
          #처음
          if ($y==0) {
              $barcode_string .= $barcode2[$y];
              continue;
          }
          #현재 바코드 -1이 전바코드와 같지않음
          if (intval($barcode2[$y])-1 !== intval($barcode2[$y-1])) {
              $barcode_string .= ",".$barcode2[$y];
          }
          #현재 바코드 -1이 전바코드와 같음
          if (intval($barcode2[$y])-1 == intval($barcode2[$y-1])) {
              //다음번이 연속되지 않을 경우
              if (intval($barcode2[$y])+1 !== intval($barcode2[$y+1])) {
                  $barcode_string .= "-".$barcode2[$y];
              }
          }
      }
      $barcode_string .= " ";
    }
    $row['barcode_string'] = $barcode_string;

    //배송정보
    $delivery = '';
    if ($it['ct_delivery_company']) {
      $delivery = '(' . get_delivery_company_step($it['ct_delivery_company'])['name'] . ') ';
    }
    if ($it['ct_delivery_num']) {
      $delivery .= $it['ct_delivery_num'];
    }
    //합포 송장번호 출력
    if ($it['ct_combine_ct_id']) {
      $sql_ct ="select `ct_delivery_company`, `ct_delivery_num` from g5_shop_cart where `ct_id` = '".$it['ct_combine_ct_id']."'";
      $result_ct = sql_fetch($sql_ct);
      $delivery = '';
      if($result_ct['ct_delivery_company'])
        $delivery = '(' . get_delivery_company_step($result_ct['ct_delivery_company'])['name'] . ') ';
      $delivery .= $result_ct['ct_delivery_num'];
    }
    $row['delivery'] = $delivery;

    $ledgers[$i] = $row;
  }

} else {
  $where_order = $where_ledger = " and m.mb_id = '$mb_id' ";

  $where_order .= " and (COALESCE(tr_date, od_time) between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  $where_ledger .= " and (lc_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

  # 매출
  $sql_order = "
    SELECT
      o.od_time,
      o.od_id,
      o.tr_date,
      m.mb_entNm,
      c.ct_id,
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
      o.tr_date,
      m.mb_entNm,
      c.ct_id,
      '^배송비' as it_name,
      '' as ct_option,
      1 as ct_qty,
      (o.od_send_cost + o.od_send_cost2) as price_d,
      ROUND( (o.od_send_cost + o.od_send_cost2) / 1.1) as price_d_p,
      ROUND( (o.od_send_cost + o.od_send_cost2) / 1.1 / 10) as price_d_s,
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
      o.tr_date,
      m.mb_entNm,
      c.ct_id,
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


  # 쿠폰할인
  $coupon_price = "(o.od_cart_coupon + o.od_coupon + o.od_send_coupon)";
  $sql_sales_coupon = "
    SELECT
      o.od_time,
      o.od_id,
      o.tr_date,
      m.mb_entNm,
      c.ct_id,
      '^쿠폰할인' as it_name,
      '' as ct_option,
      1 as ct_qty,
      (-$coupon_price) as price_d,
      ROUND(-$coupon_price / 1.1) as price_d_p,
      ROUND(-$coupon_price / 1.1 / 10) as price_d_s,
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
      $coupon_price > 0
  ";

  # 포인트결제
  $sql_sales_point = "
    SELECT
      o.od_time,
      o.od_id,
      o.tr_date,
      m.mb_entNm,
      c.ct_id,
      '^포인트결제' as it_name,
      '' as ct_option,
      1 as ct_qty,
      (-o.od_receipt_point) as price_d,
      ROUND(-o.od_receipt_point / 1.1) as price_d_p,
      ROUND(-o.od_receipt_point / 1.1 / 10) as price_d_s,
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
      o.od_receipt_point > 0
  ";


  # 입금/출금
  $sql_ledger = "
    SELECT
      lc_created_at as od_time,
      '' as od_id,
      '' as tr_date,
      m.mb_entNm,
      '' as ct_id,
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
      ({$sql_order} {$where_order})
      UNION ALL
      ({$sql_send_cost} {$where_order} GROUP BY o.od_id)
      UNION ALL
      ({$sql_sales_discount} {$where_order} GROUP BY o.od_id)
      UNION ALL
      ({$sql_sales_coupon} {$where_order} GROUP BY o.od_id)
      UNION ALL
      ({$sql_sales_point} {$where_order} GROUP BY o.od_id)
      UNION ALL
      ({$sql_ledger} {$where_ledger})
    ) u
  ";

  $result = sql_query("
    SELECT
      u.*,
      (price_d * ct_qty) as sales
    {$sql_common}
    ORDER BY
      od_time asc,
      od_id asc
  ");

  $ledgers = [];
  $carried_balance = get_outstanding_balance($mb_id, $fr_date);
  $balance = $carried_balance;

  while($row = sql_fetch_array($result)) {
    $balance += ($row['price_d'] * $row['ct_qty']);
    $balance -= ($row['deposit']);
    $row['balance'] = $balance;
    $ct_id = $row['ct_id'];
    //급여코드(품목코드) 가져오기
    $it = sql_fetch("
      SELECT cart.*, item.it_thezone2, o.io_thezone as io_thezone2, item.ca_id, it_standard, io_standard
      FROM g5_shop_cart as cart
      INNER JOIN g5_shop_item as item ON cart.it_id = item.it_id
      LEFT JOIN g5_shop_item_option o ON (cart.it_id = o.it_id and cart.io_id = o.io_id)
      WHERE cart.ct_id = '{$ct_id}'
      ORDER BY cart.ct_id ASC
    ");
    $thezone_code = $it['io_thezone2'] ?: $it['io_thezone'] ?: $it['it_thezone2'];
    $row['thezone_code'] = $thezone_code;

    #바코드
    $stoIdDataList = explode('|',$it['stoId']);
    $stoIdDataList=array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);

    $barcode=[];
    $sendData["stoId"] = $stoIdData;
    $oCurl = curl_init();
    $res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
    $result_again = $res;
    $result_again =$result_again['data'];

    for($k=0; $k < count($result_again); $k++) {
      if($result_again[$k]['prodBarNum']) {
        array_push($barcode,$result_again[$k]['prodBarNum']);
      }
    }
    asort($barcode);
    $barcode2=[];
    $y = 0;  
    foreach($barcode as $key=>$val)  
    {  
      $new_key = $y;  
      $barcode2[$new_key] = $val;  
      $y++;  
    }
    $barcode_string="";
    if (!is_benefit_item($it)) {
      for ($y=0; $y<count($barcode2); $y++) {
          #처음
          if ($y==0) {
              $barcode_string .= $barcode2[$y];
              continue;
          }
          #현재 바코드 -1이 전바코드와 같지않음
          if (intval($barcode2[$y])-1 !== intval($barcode2[$y-1])) {
              $barcode_string .= ",".$barcode2[$y];
          }
          #현재 바코드 -1이 전바코드와 같음
          if (intval($barcode2[$y])-1 == intval($barcode2[$y-1])) {
              //다음번이 연속되지 않을 경우
              if (intval($barcode2[$y])+1 !== intval($barcode2[$y+1])) {
                  $barcode_string .= "-".$barcode2[$y];
              }
          }
      }
      $barcode_string .= " ";
    }
    $row['barcode_string'] = $barcode_string;

    //배송정보
    $delivery = '';
    if ($it['ct_delivery_company']) {
      $delivery = '(' . get_delivery_company_step($it['ct_delivery_company'])['name'] . ') ';
    }
    if ($it['ct_delivery_num']) {
      $delivery .= $it['ct_delivery_num'];
    }
    //합포 송장번호 출력
    if ($it['ct_combine_ct_id']) {
      $sql_ct ="select `ct_delivery_company`, `ct_delivery_num` from g5_shop_cart where `ct_id` = '".$it['ct_combine_ct_id']."'";
      $result_ct = sql_fetch($sql_ct);
      $delivery = '';
      if($result_ct['ct_delivery_company'])
        $delivery = '(' . get_delivery_company_step($result_ct['ct_delivery_company'])['name'] . ') ';
      $delivery .= $result_ct['ct_delivery_num'];
    }
    $row['delivery'] = $delivery;

    $ledgers[] = $row;
  }

  # 금액
  $sel_price_field = in_array($sel_price_field, ['price_d', 'price_d_p', 'price_d_s', 'sales']) ? $sel_price_field : '';
  if($price && $sel_price_field && $price_s <= $price_e) {
    $price_s = intval($price_s);
    $price_e = intval($price_e);
    // 검색결과 필터링
    $ledgers = array_values(array_filter($ledgers, function($v) {
      global $sel_price_field, $price_s, $price_e;
      return $v[$sel_price_field] >= $price_s && $v[$sel_price_field] <= $price_e;
    }));
  }

  # 검색어
  if($sel_field && $search) {
    // 검색결과 필터링
    $ledgers = array_values(array_filter($ledgers, function($v) {
      global $sel_field, $search;
      $pattern = '/.*'.preg_quote($search).'.*/i';
      return preg_match($pattern, $v[$sel_field]);
    }));
  }

}

if(! function_exists('column_char')) {
  function column_char($i) {
    return chr( 65 + $i );
  }
}

include_once(G5_LIB_PATH.'/PHPExcel.php');

$headers = ['일자-주문번호', '품목명[규격]', '수량', '급여코드', '바코드', '배송정보', '단가(Vat포함)', '공급가액', '부가세', '판매', '수금', '잔액', '수령인'];
if($ent['mb_type'] === 'partner') $headers[10] = '결제';
$widths = [25, 20, 6, 12, 25, 20, 12, 12, 12, 12, 12, 12, 15];
$heights = [50, 36, 36, 36, 36, 36, 36, 20];
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
  $od_date = $row['tr_date'] ?: $row['od_time'];
  $rows[] = [
    date('y/m/d', strtotime($od_date)).($row['od_id'] ? '-'.$row['od_id'] : ''),
    $row['it_name'].($row['ct_option'] && $row['ct_option'] != $row['it_name'] ? " [{$row['ct_option']}]" : ''),
    $row['ct_qty'],
    $row['thezone_code'],
    $row['barcode_string'],
    $row['delivery'],
    $row['price_d'],
    $row['price_d_p'],
    $row['price_d_s'],
    $row['sales'],
    $row['deposit'],
    $row['balance'],
    $row['od_b_name']
  ];

  $total_qty += $row['ct_qty'];
  $total_price_d += $row['price_d'];
  $total_price_d_p += $row['price_d_p'];
  $total_price_d_s += $row['price_d_s'];
  $total_sales += ($row['price_d'] * $row['ct_qty']);
  $total_deposit += $row['deposit'];
}

$totals = [
  '누계',
  '',
  $total_qty,
  '',
  '',
  '',
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

$data = array_merge([$headers], $rows, [$totals], [$dates]);

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
$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
$excel->getActiveSheet()->getStyle('A10:M'.(count($rows) + 9))->getAlignment()->setWrapText(true);
// 폰트&볼드 처리
// $excel->getActiveSheet()->getStyle('A1:J2')->getFont()->setSize(11);

// 볼드처리
$excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('A3:A7')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('F3:F5')->getFont()->setBold(true);
$excel->getActiveSheet()->getStyle('A8:M8')->getFont()->setBold(true);

// number format 처리
$excel->getActiveSheet()->getStyle('G9:M'.(count($rows) + 9))->getNumberFormat()->setFormatCode('#,##0_-');

// 테두리 처리
$styleArray = array(
  'borders' => array(
    'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);
$excel->getActiveSheet()->getStyle('A2:M'.(count($rows) + 9))->applyFromArray($styleArray);

foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
for ($i=0; $i < count($heights); $i++) {
  $row = $i+1;
  $excel->getActiveSheet()->getRowDimension("{$row}")->setRowHeight($heights[$i]);  
}
$entNm = $ent['mb_entNm'] ?: $ent['mb_giup_bname'] ?: $ent['mb_name'];
$excel->getActiveSheet()->setCellValue("A1", "[{$entNm}] 거래원장");
$excel->getActiveSheet()->mergeCells('A1:M1');
$excel->getActiveSheet()->getStyle('A1:M1')->getFont()->setSize(18);
$excel->getActiveSheet()->getStyle('A1:M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//회사정보 영역 폰트 사이즈
$excel->getActiveSheet()->getStyle('A2:M7')->getFont()->setSize(12);

// 회사명/담당자
$excel->getActiveSheet()->setCellValue("A2", "회사명 : (주)티에이치케이컴퍼니 / 담당 : {$mb_manager}");
$excel->getActiveSheet()->mergeCells('A2:G2');

// 기간
$excel->getActiveSheet()->setCellValue("H2", "{$fr_date} ~ {$to_date}");
$excel->getActiveSheet()->mergeCells('H2:M2');
$excel->getActiveSheet()->getStyle('H2:M2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

//회사정보
$excel->getActiveSheet()->mergeCells('B3:E3');
$excel->getActiveSheet()->mergeCells('F3:G3');
$excel->getActiveSheet()->mergeCells('B4:E4');
$excel->getActiveSheet()->mergeCells('F4:G4');
$excel->getActiveSheet()->mergeCells('B5:E5');
$excel->getActiveSheet()->mergeCells('F5:G5');
$excel->getActiveSheet()->mergeCells('B6:M6');
$excel->getActiveSheet()->mergeCells('B7:M7');
$excel->getActiveSheet()->mergeCells('H3:M3');
$excel->getActiveSheet()->mergeCells('H4:M4');
$excel->getActiveSheet()->mergeCells('H5:M5');
$excel->getActiveSheet()->getStyle('B4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

$giup_info_title_cells = ['A3', 'F3', 'A4', 'F4', 'A5', 'F5', 'A6', 'A7'];
$giup_info_titles = ['사업자번호', '대표자', '여신한도', '전화번호', 'E-mail', '팩스번호', '주소', '적요'];
$giup_info_value_cells = ['B3', 'H3', 'B4', 'H4', 'B5', 'H5', 'B6', 'B7'];
$giup_info_values = [$ent['mb_giup_bnum'], $ent['mb_giup_boss_name'], '0', $ent['mb_tel'], $ent['mb_email'], $ent['mb_fax'], $ent['mb_addr1'], ''];
for ($i=0; $i < 8; $i++) { 
  $excel->getActiveSheet()->setCellValue($giup_info_title_cells[$i], $giup_info_titles[$i]);
  $excel->getActiveSheet()->setCellValue($giup_info_value_cells[$i], $giup_info_values[$i]);
}

$excel->getActiveSheet()->fromArray($data, null, 'A8');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"거래처원장.xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
