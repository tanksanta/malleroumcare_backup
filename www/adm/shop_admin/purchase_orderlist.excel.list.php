<?php

include_once("./_common.php");

auth_check($auth["400400"], "r");
include_once(G5_LIB_PATH . "/PHPExcel.php");
function column_char($i)
{
  return chr(65 + $i);
}

$ct_ids = $od_id;
// 합포 상품들 검색
$combine_ct_items = [];
foreach ($ct_ids as $ct_id) {
  $it = sql_fetch("
        SELECT cart.*
        FROM purchase_cart as cart
        WHERE cart.ct_id = '{$ct_id}'
      ");
  if ($it['ct_combine_ct_id']) {
    array_push($combine_ct_items, $it['ct_combine_ct_id']);
    $result = sql_query("
              SELECT cart.* 
              FROM purchase_cart as cart 
              WHERE cart.ct_combine_ct_id = '{$it['ct_combine_ct_id']}'
          ");
    while ($row = sql_fetch_array($result)) {
      array_push($combine_ct_items, $row['ct_id']);
    }
  }
}
$ct_ids = array_merge($ct_ids, $combine_ct_items);
$ct_ids = array_values(array_unique($ct_ids));

if ($_POST['ref'] == 'orderform') {
  $ct_ids = [];
  $sql = "SELECT ct_id FROM purchase_cart WHERE od_id = '{$od_id[0]}' ";
  $result = sql_query($sql);

  while ($row = sql_fetch_array($result)) {
    $ct_ids[] = $row['ct_id'];
  }
}

if (!$ct_ids) {
  $ct_ids = [];
  $where = [];

  $sel_field = get_search_string($sel_field);

  // wetoz : naverpayorder - , 'od_naver_orderid' 추가
  if (!in_array($sel_field, array('od_all', 'it_name', 'it_admin_memo', 'it_maker', 'od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'od_naver_orderid', 'barcode', 'prodMemo', 'od_memo'))) {   //검색할 필드 대상이 아니면 값을 제거
    $sel_field = '';
  }
  $replace_table = array(
    'od_id' => 'o.od_id',
    'it_name' => 'i.it_name',
    'mb_id' => 'c.mb_id'
  );
  $sel_field = $replace_table[$sel_field] ?: $sel_field;
  $sel_field_add = $replace_table[$sel_field_add] ?: $sel_field_add;

  $ct_status = $od_status;
  $ct_status = get_search_string($ct_status);
  $search = get_search_string($search);
  if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date)) $fr_date = '';
  if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date)) $to_date = '';

  $od_misu = preg_replace('/[^0-9a-z]/i', '', $od_misu);
  $od_cancel_price = preg_replace('/[^0-9a-z]/i', '', $od_cancel_price);
  $od_refund_price = preg_replace('/[^0-9a-z]/i', '', $od_refund_price);
  $od_receipt_point = preg_replace('/[^0-9a-z]/i', '', $od_receipt_point);
  $od_coupon = preg_replace('/[^0-9a-z]/i', '', $od_coupon);

  if ($search != "") {
    $search = trim($search);
    if ($sel_field == "barcode") {
      $sql_barcode_search = "select `stoId` from `g5_barcode_log` where `barcode` = '" . $search . "'";
      $result_barcode_search = sql_query($sql_barcode_search);
      $or = "";
      while ($row_barcode = sql_fetch_array($result_barcode_search)) {
        $bacode_search .= $or . " `o.stoId` like '%" . $row_barcode['stoId'] . "%' ";
        $or = "or";
      }
      $where[] = $bacode_search;
    } else {
      if ($sel_field != "" && $sel_field != "od_all") {
        $where[] = " $sel_field like '%$search%' ";
      }
    }
  }

  if ($search_add != "") {
    $search_add = trim($search_add);
    if ($sel_field_add != "" && $sel_field_add != "od_all") {
      $where[] = "$sel_field_add like '%$search_add%'";
    }
  }

  // 전체 검색
  if ($sel_field == 'od_all' && $search != "") {
    $sel_arr = array('i.it_name', 'it_admin_memo', 'it_maker', 'o.od_id', 'c.mb_id', 'mb_nick', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'barcode', 'prodMemo', 'od_memo');

    foreach ($sel_arr as $key => $value) {
      if ($value == "barcode") {
        $sql_barcode_search = "select `stoId` from `g5_barcode_log` where `barcode` = '" . $search . "'";
        $result_barcode_search = sql_query($sql_barcode_search);
        $or = "";
        while ($row_barcode = sql_fetch_array($result_barcode_search)) {
          $bacode_search .= $or . " `o.stoId` like '%" . $row_barcode['stoId'] . "%' ";
          $or = "or";
        }
        if ($bacode_search) {
          $sel_arr[$key] = $bacode_search;
        } else {
          $sel_arr[$key] = "o.stoId like '%$search%'";
        }
      } else {
        $sel_arr[$key] = "$value like '%$search%'";
      }
    }

    $where[] = "(" . implode(' or ', $sel_arr) . ")";
  }

  // 출고준비 3일경과만 보기
  if ($issue_1) {
    $where[] = " ( ct_status = '발주대기' and DATE(ct_move_date) <= (CURDATE() - INTERVAL 3 DAY ) ) ";
  }

  // 취소/반품요청 있는 주문만 보기
  if ($issue_2) {
    $where[] = " ( select count(*) from g5_shop_order_cancel_request where approved = 0 and od_id = o.od_id ) > 0 ";
  }

  if ($od_sales_manager) {
    $where_od_sales_manager = array();
    for ($i = 0; $i < count($od_sales_manager); $i++) {
      $where_od_sales_manager[] = " mb_manager = '{$od_sales_manager[$i]}'";
    }
    if (count($where_od_sales_manager)) {
      $where[] = " ( " . implode(' OR ', $where_od_sales_manager) . " ) ";
    }
  }

  if ($od_release_manager) {
    $where_od_release_manager = array();
    for ($i = 0; $i < count($od_release_manager); $i++) {
      if ($od_release_manager[$i] == 'yet_release') {
        $od_release_manager[$i] = '';
      }
      $where_od_release_manager[] = " od_release_manager = '{$od_release_manager[$i]}'";
    }
    if (count($where_od_release_manager)) {
      $where[] = " ( " . implode(' OR ', $where_od_release_manager) . " ) ";
    }
  }

  if ($partner_issue) {
    $where_partner_issue = array();
    for ($i = 0; $i < count($partner_issue); $i++) {
      $where_partner_issue[] = " pir.ir_is_issue_{$partner_issue[$i]} = TRUE ";
    }
    if (count($where_partner_issue)) {
      $where[] = " ( " . implode(' OR ', $where_partner_issue) . " ) ";
    }
  }

  if ($od_pay_state && is_array($od_pay_state)) {
    foreach ($od_pay_state as $s) {
      $s = (int)$s;
      $od_pay_state_where[] = " od_pay_state = '{$s}'";
    }
    $where[] = ' ( ' . implode(' OR ', $od_pay_state_where) . ' ) ';
  }
  if (gettype($add_admin) == 'string' && $add_admin !== '') {
    $od_add_admin = $add_admin;
    $where[] = " od_add_admin = '$od_add_admin' ";
  }

  if (gettype($od_important) == 'string' && $od_important !== '') {
    $od_important = $od_important;
    $where[] = " od_important = '$od_important' ";
  }

  if (gettype($ct_is_direct_delivery) == 'string' && $ct_is_direct_delivery !== '') {
    $where[] = " ct_is_direct_delivery = '$ct_is_direct_delivery' ";
  }

  if (($ct_direct_delivery_partner = get_search_string($ct_direct_delivery_partner)) && $ct_is_direct_delivery !== '0') {
    $where[] = " ct_direct_delivery_partner = '$ct_direct_delivery_partner' ";
  }

  if (gettype($od_release) == 'string' && $od_release !== '') {
    if ($od_release == '0') { // 일반출고
      $where[] = " ( od_release_manager != 'no_release' AND od_release_manager != '-' ) ";
    }
    if ($od_release == '1') { // 외부출고
      $where[] = " ( od_release_manager = '-' ) ";
    }
    if ($od_release == '2') { // 출고대기
      $where[] = " ( od_release_manager = 'no_release' ) ";
    }
  }

  if ($price) {
    $where[] = " (od_cart_price + od_send_cost + od_send_cost2 - od_cart_discount) BETWEEN '{$price_s}' AND '{$price_e}' ";
  }

  if ($od_settle_case) {
    if (is_array($od_settle_case)) {

      $od_settle_case_where = array();
      foreach ($od_settle_case as $s) {
        $od_settle_case_where[] = " od_settle_case = '{$s}'";
      }
      $where[] = ' ( ' . implode(' OR ', $od_settle_case_where) . ' ) ';
    } else {
      $where[] = " od_settle_case = '{$od_settle_case}'";
    }
  }

  //// 등급 검색 ////
  if ($member_level_s) {
    if (is_array($member_level_s)) {
      $member_level_s_where = array();
      foreach ($member_level_s as $s) {
        $member_level_s_where[] = " mb_level = '{$s}'";
      }
      $temp_where[] = ' ( ' . implode(' OR ', $member_level_s_where) . ' ) ';
    } else {
      $temp_where[] = " ( mb_level = '{$member_level_s}' )";
    }
  }

  if ($member_type_s) {
    if (is_array($member_type_s)) {
      $member_type_s_where = array();
      foreach ($member_type_s as $s) {
        $member_type_s_where[] = " mb_type = '{$s}'";
      }
      $temp_where[] = ' ( ' . implode(' OR ', $member_type_s_where) . ' ) ';
    } else {
      $temp_where[] = " ( mb_type = '{$member_type_s}' )";
    }
  }

  if ($is_member_s) {
    if (is_array($is_member_s)) {
      $is_member_s_where = array();
      foreach ($is_member_s as $s) {
        $is_member_s_where[] = " mb_level is {$s}";
      }
      $temp_where[] = ' ( ' . implode(' OR ', $is_member_s_where) . ' ) ';
    } else {
      $temp_where[] = " mb_level is {$is_member_s}";
    }
  }

  if ($temp_where) {
    foreach ($temp_where as $s) {
      $where[] = ' ( ' . implode(' OR ', $temp_where) . ' ) ';
    }
  }
  //////////////////

  if ($_POST["od_recipient"]) {
    $where[] = " recipient_yn = '{$_POST["od_recipient"]}'";
  }

  if ($od_misu) {
    $where[] = " od_misu != 0 ";
  }

  if ($od_cancel_price) {
    $where[] = " od_cancel_price != 0 ";
  }

  if ($od_refund_price) {
    $where[] = " od_refund_price != 0 ";
  }

  if ($od_receipt_point) {
    $where[] = " od_receipt_point != 0 ";
  }

  if ($od_coupon) {
    $where[] = " ( od_cart_coupon > 0 or od_coupon > 0 or od_send_coupon > 0 ) ";
  }

  if ($od_escrow) {
    $where[] = " od_escrow = 1 ";
  }

  if ($fr_date && $to_date) {
    $where[] = " ({$sel_date_field} between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  }

  $where[] = " od_del_yn = 'N' ";

  // 최고관리자가 아닐때
  if ($ct_status == '작성' && $is_admin != 'super') {
    $where[] = " od_writer = '{$member['mb_id']}' ";
  }

  if ($click_status) {
    $where[] = " ct_status = '{$click_status}'";
  } else {
    if ($ct_status) {
      if (is_array($ct_status)) {

        $order_steps_where = array();
        foreach ($ct_status as $s) {
          $order_steps_where[] = " ct_status = '{$s}'";
        }
        $where[] = ' ( ' . implode(' OR ', $order_steps_where) . ' ) ';
      } else {
        $where[] = " ct_status = '{$ct_status}'";
      }
    } else {
      $order_steps_where = array();
      foreach ($order_steps as $order_step) {
        if (!$order_step['orderlist']) continue;

        $order_steps_where[] = " ct_status = '{$order_step['val']}' ";
      }
      $where[] = ' ( ' . implode(' OR ', $order_steps_where) . ' ) ';
    }
  }

  $where[] = " (m.mb_intercept_date = '' OR m.mb_intercept_date IS NULL) ";

  $sql_search = '';
  if ($where) {
    $sql_search = ' where ' . implode(' and ', $where);
  }

  // shop_cart 조인으로 수정
  // member 테이블 조인
  $sql_common = "
      FROM
        purchase_cart c
      LEFT JOIN
        {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
      LEFT JOIN
        purchase_order o ON c.od_id = o.od_id
      LEFT JOIN
        {$g5['member_table']} m ON c.mb_id = m.mb_id
      LEFT JOIN
        partner_install_report pir ON c.od_id = pir.od_id
      LEFT JOIN
        g5_shop_order_cancel_request ocr ON c.od_id = ocr.od_id
    ";

  $sql_common .= $sql_search;

  // 정렬
  foreach ($order_steps as $order_step) {
    if (!$order_step['orderlist']) continue;
    $order_by_steps[] = "'" . $order_step['val'] . "'";
  }
  $order_by_step = implode(' , ', $order_by_steps);
  $sql_common .= " ORDER BY FIELD(ct_status, " . $order_by_step . " ), ct_move_date desc, o.od_id desc ";

  $sql = "
      select *, o.od_id as od_id, c.ct_id as ct_id, c.mb_id as mb_id, (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
      $sql_common
    ";
  $result = sql_query($sql);

  while ($row = sql_fetch_array($result)) {
    $ct_ids[] = $row['ct_id'];
  }
}


$ct_items = [];
$combine_ct_items = [];
for ($ii = 0; $ii < count($ct_ids); $ii++) {

  $it = sql_fetch("
      SELECT cart.*, item.it_thezone2
      FROM purchase_cart as cart
      INNER JOIN g5_shop_item as item ON cart.it_id = item.it_id
      WHERE cart.ct_id = '{$ct_ids[$ii]}'
      ORDER BY cart.ct_id ASC
    ");

  $od = sql_fetch(" 
      SELECT * FROM purchase_order WHERE od_id = '" . $it['od_id'] . "'
    ");
  $it['od_info'] = $od;

  //영업담당자
  $sql_manager = "SELECT `mb_manager`,`mb_entNm` FROM `g5_member` WHERE `mb_id` ='" . $od['mb_id'] . "'";
  $result_manager = sql_fetch($sql_manager);
  if (!$result_manager['mb_manager']) {
    $result_manager['mb_manager'] = $od['od_sales_manager'];
  }

  $sql_manager = "SELECT `mb_name` FROM `g5_member` WHERE `mb_id` ='" . $result_manager['mb_manager'] . "'";
  $result_manager = sql_fetch($sql_manager);
  $it['sale_manager'] = $result_manager['mb_name'];

  $it_name = $it["it_name"];

  if ($it_name != $it["ct_option"]) {
    $it_name .= " [{$it["ct_option"]}]";
  }
  $it["it_name"] = $it_name;
  $it["it_name_qty"] = $it_name . "*" . $it["ct_qty"] . "개";

  $addr = "";
  if ($od_b_zip1) {
    $addr = "(" . $od_b_zip1 . $od_b_zip2 . ")";
  }
  $it['addr'] = $addr . $od["od_b_addr1"] . ' ' . $od["od_b_addr2"] . ' ' . $od["od_b_addr3"];

  $ct_delivery_company = $it['ct_delivery_company'];
  foreach ($delivery_companys as $companyInfo) {
    if ($companyInfo["val"] == $ct_delivery_company) {
      $ct_delivery_company = $companyInfo["name"];
    }
  }

  if ($it['ct_combine_ct_id'] == NULL) {
    array_push($ct_items, $it);
  } else {
    array_push($combine_ct_items, $it);
  }
}

if (count($combine_ct_items) > 0) {
  foreach ($combine_ct_items as $combine_item) {
    foreach ($ct_items as $key => $item) {
      if ($combine_item['ct_combine_ct_id'] == $item['ct_id']) {
        $it_name = $item['it_name'] . " / " . $combine_item['it_name'];
        $ct_items[$key]['it_name'] = $it_name;

        $it_name_qty = $item['it_name_qty'] . " / " . $combine_item['it_name_qty'];
        $ct_items[$key]['it_name_qty'] = $it_name_qty;

        $ct_qty = intval($item['ct_qty']);
        $ct_items[$key]['ct_qty'] = $ct_qty + intval($combine_item['ct_qty']);

        $box_cnt = intval($item['ct_delivery_cnt']);
        $ct_items[$key]['ct_delivery_cnt'] = $box_cnt + intval($combine_item['ct_delivery_cnt']);
      }
    }
  }
}

$i = 1;
$rows = [];
foreach ($ct_items as $it) {
  $od = $it['od_info'];

  $ct_part_info = json_decode($it['ct_part_info'],true)[1];

  $ct_delivery_complete_date = $ct_part_info['_in_dt_confirm']?date("Y-m-d", strtotime($ct_part_info['_in_dt_confirm'])) :"-" ;
  $ct_delivery_expect_date = $ct_part_info['_in_dt']?date("Y-m-d", strtotime($ct_part_info['_in_dt'])) :"-" ;
  $ct_delivered_qty = $ct_part_info['_in_qty']?:"0" ;
  $item_price = number_format($it['ct_price'])?:"-";
  $basic_price = $it['ct_price']?number_format(round(($it['ct_price']*$it["ct_qty"])/1.1)):"-";
  $tax_price = $it['ct_price']?number_format(round(($it['ct_price']*$it["ct_qty"])/11)):"-";
  $total_price = $it['ct_price']?number_format($it['ct_price']*$it["ct_qty"]):"-";

  $rows[] = [
    ' ' . $od['od_id'],
    $od["od_name"],
    date("Y-m-d", strtotime($od["od_time"])),
    $it['it_name'],
    $it["ct_qty"],
    $ct_delivered_qty,
    $item_price,
    $basic_price,
    $tax_price,
    $total_price,
    $it['ct_status'],
    $it['ct_warehouse'],
    $ct_delivery_expect_date,
    $ct_delivery_complete_date
  ];
}

$headers = array("발주번호", "거래처 상호", "발주일", "품목[상품]", "발주수량", "입고수량", "단가", "공급가액", "부가세", "합계", "발주상태", "입고창고", "입고예정일", "입고완료일");
$data = array_merge(array($headers), $rows);

$widths = array(25, 30, 30, 50, 10, 10, 15, 15, 15, 15, 20, 25, 25, 30);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)
  ->getStyle("A1:${last_char}1")
  ->getFill()
  ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
  ->getStartColor()
  ->setARGB($header_bgcolor);

$excel->setActiveSheetIndex(0)
  ->getStyle("A:$last_char")
  ->getAlignment()
  ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
  ->setWrapText(true);

foreach ($widths as $i => $w) {
  $excel->setActiveSheetIndex(0)->getColumnDimension(column_char($i))->setWidth($w);
  $excel->setActiveSheetIndex(0)->getStyle(column_char($i)."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  if(in_array(column_char($i), array("G","H","I","J"))) { $excel->setActiveSheetIndex(0)->getStyle(column_char($i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT); }
  else { $excel->setActiveSheetIndex(0)->getStyle(column_char($i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); }
}
$excel->getActiveSheet()->fromArray($data, NULL, 'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"orderexcel-" . date("ymd", time()) . ".xls\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
