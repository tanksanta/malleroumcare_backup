<?php
include_once('./_common.php');

header('Content-Type: application/json');

function getExcelFile($mb_id) {
    $ent = get_member($mb_id);
    if(!$ent['mb_id'])
      alert('존재하지 않는 사업소입니다.');

    $where_order = $where_ledger = " and m.mb_id = '$mb_id' ";

    # 기간
    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
    if(!$fr_date)
      $fr_date = date('Y-m-01');
    if(!$to_date)
      $to_date = date('Y-m-d');
    $where_order .= " and (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
    $where_ledger .= " and (lc_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

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


    # 쿠폰할인
    $coupon_price = "(o.od_cart_coupon + o.od_coupon + o.od_send_coupon)";
    $sql_sales_coupon = "
      SELECT
        o.od_time,
        o.od_id,
        m.mb_entNm,
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
        m.mb_entNm,
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

    if(! function_exists('column_char')) {
      function column_char($i) {
        return chr( 65 + $i );
      }
    }

    include_once(G5_LIB_PATH.'/PHPExcel.php');

    $title = ["회사명 : (주)티에이치케이컴퍼니/{$ent['mb_entNm']}/{$fr_date} ~ {$to_date}"];
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

    // header("Content-Type: application/octet-stream");
    // header("Content-Disposition: attachment; filename=\"ledger.xls\"");
    // header("Cache-Control: max-age=0");

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    ob_start();
    $writer->save('php://output');
    $excelOutput = ob_get_clean();

    return $excelOutput;
}

$send_data = $_POST['send_data'];
if (!$send_data) {
    $ret = array(
        'result' => 'fail',
        'msg' => '잘못된 요청입니다.',
    );
    echo json_encode($ret);
    exit;
}

$send_mail_arr = array();
$send_fax_arr = array();

foreach($send_data as $data) {
    $send_type = $data['send_type'];
    $ent_id = $data['ent_id'];
    $ent_name = $data['ent_name'];
    $mb_manager = $data['mb_manager'];
    $receiver = $data['receiver'];

    if ($send_type == 'E') {
        $fr_date = date('Y-m-01');
        $to_date = date('Y-m-d');
        $mail_contents = '
            <div style="background-color:#f9f9f9;width:100%;max-width:800px;padding:30px;">
            <div style="padding-bottom:30px;border-bottom:1px solid #cfcfcf;">
                <div style="color:#333333;position:relative;width:70%;float:left;">
                    <p style="font-size:42px;padding:0;margin:0;"><b style="font-size:30px;">' . date('m월') . ' 거래처원장</p>
                </div>
                <div style="clear:both;"></div>
            </div>
            <div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
                <p style="margin:0;text-align:center;padding-bottom:30px;">안녕하세요. 이로움 ' . $mb_manager . ' 담당자입니다.<br>항상 저희 이로움 플랫폼을 이용해 주셔서 진심으로 감사드립니다.<br><br>' . $ent_name . ' 사업소에서 거래하신 내역을 송부하였으니 확인 바랍니다.<br>더욱더 노력하는 이로움플랫폼이 되겠습니다.<br><br></p>
                <a href="' . G5_ADMIN_URL . '/shop_admin/ledger_excel.php?mb_id=' . $ent_id . '&fr_date=' . $fr_date . '&to_date=' . $to_date . '" target="_blank" style="background-color:#0aa2cd;display:inline-block;text-align:center;padding: 12px 60px;color:white;text-decoration:none;margin:20px auto;font-size:18px;">거래처원장 다운로드</a>
            </div>
            <p style="font-size:12px;color:#656565;margin:30px auto;text-align:center;">
                대표자: ' . $default['de_admin_company_owner'] . ' | 사업자등록번호: ' . $default['de_admin_company_saupja_no'] . ' | 통신판매신고번호: ' . $default['de_admin_tongsin_no'] . ' <br/>
                개인정보보호관리자: ' . $default['de_admin_info_name'] . ' | 주소: ' . $default['de_admin_company_addr'] . '
                <br/><br/>
                Copyright © ' . $default['de_admin_company_name'] . ' All rights reserved.
            </p>
            </div>
        ';

        array_push($send_mail_arr, array(
            'subject' => '[이로움 장기요양기관 통합관리플랫폼] ' . date('m월') . ' 거래처원장 송부드립니다.',
            'content' => $mail_contents,
            'receiver' => trim($receiver)
        ));

        set_send_ledger_log($ent_id, $send_type, $receiver);
    }
    else if ($send_type == 'F') {
        // 팩스번호에서 숫자만 취한다
        $receive_number = preg_replace("/[^0-9]/", "", $receiver);  // 수신자번호 (회원님의 핸드폰번호)

        $Receivers[] = array(
            'rcv' => $receive_number,
            'rcvnm' => $ent_name
        );

        $excelData = getExcelFile($ent_id);
        array_push($send_fax_arr, array(
            'excel' => $excelData,
            'rcvnm' => $ent_name,
            'rcv' => $receive_number
        ));
        $FileDatas[] = array(
            //파일명
            'fileName' => 'ledger.xls',
            //fileData - BLOB 데이터 입력
            'fileData' => $excelData //file_get_contenst-바이너리데이터 추출
        );

/*        if ($response) {
            $ret = array(
                'result' => 'fail',
                'msg' => $response,
            );
            echo json_encode($ret);
            exit;   
        }
*/
        set_send_ledger_log($ent_id, $send_type, $receive_number);
    }
}

if (count($send_mail_arr) > 0) {
    // echo 'console.log("' . var_dump($send_mail_arr) . '")';
    include_once(G5_LIB_PATH.'/mailer.lib.php');
    mailer_multiple($config['cf_admin_email_name'], $config['cf_admin_email'], $send_mail_arr);
}

if (count($send_fax_arr) > 0) {
    // echo 'console.log("' . var_dump($send_mail_arr) . '")';
    include_once(G5_LIB_PATH.'/fax.lib.php');
    $response = sendFax($send_fax_arr);
    if ($response) {
        $ret = array(
            'result' => 'fail',
            'msg' => $response,
        );
        echo json_encode($ret);
        exit;   
    }
}



$ret = array(
    'result' => 'success',
    'msg' => '발송하였습니다.',
);

echo json_encode($ret);
?>