<?php
include_once('./_common.php');
if ($_POST['ct_id'] && $_POST['step']) {
  //변수지정
  $stoId = "";
  $usrId = "";
  $entId = "";
  $flag = true;
  $stoIdList = array();

  $sql = [];
  $sql_ct = [];
  $sql_cp = [];
  $sql_stock = [];
  $combine_orders = []; // 자동 합포적용

  for ($i = 0; $i < count($_POST['ct_id']); $i++) {
    $sql_ct_s = "select
      a.od_id,
      a.it_id,
      a.it_name,
      a.ct_option,
      a.ct_status,
      a.mb_id,
      a.stoId,
      b.mb_entId,
      a.io_id,
      a.io_type,
      a.ct_price,
      a.io_price,
      a.ct_qty,
      a.ct_discount,
      a.prodSupYn,
      a.ct_stock_qty,
      a.ct_id,
      a.ct_combine_ct_id,
      a.ct_warehouse
    from `purchase_orderpurchase_cart` a left join `g5_member` b on a.mb_id = b.mb_id where `ct_id` = '" . $_POST['ct_id'][$i] . "'";
    $result_ct_s = sql_fetch($sql_ct_s);
    $od_id = $result_ct_s['od_id'];

    $od = sql_fetch(" select * from g5_shop_order where od_id = '$od_id' ");
    if ($od['od_is_editing'] == 1) {
      echo '해당 주문은 사업소에서 수정 중이므로 주문단계가 변경되지 않았습니다.';
      exit;
    }

    if (in_array($result_ct_s['ct_status'], ['취소', '주문무효'])) {
      echo '취소된 주문은 상태변경이 불가능합니다.';
      exit;
    }

    $content = $result_ct_s['it_name'];
    if ($result_ct_s['it_name'] !== $result_ct_s['ct_option']) {
      $content = $content . "(" . $result_ct_s['ct_option'] . ")";
    }
    $it_name = $content;
    $content = $content . "-" . clean_xss_tags($_POST['step']) . " 변경";
    //로그 insert
    $sql[$i] = "INSERT INTO g5_shop_order_admin_log SET
      od_id = '{$od_id}',
      mb_id = '{$member['mb_id']}',
      ol_content = '{$content}',
      ol_datetime = now()
    ";
    //상태 update
    $add_sql = '';
    if ($_POST['step'] == "배송") {
      $add_sql .= ", `ct_ex_date` = CURDATE()";
    }
    if ($_POST['step'] == "출고준비") {
      $add_sql .= ", `ct_rdy_date` = NOW()";
    }

    $sql_ct[$i] = "update `purchase_cartpurchase_cart` set `ct_status` = '" . $_POST['step'] . "'" . $add_sql . ", `ct_move_date`= NOW() where `ct_id` = '" . $_POST['ct_id'][$i] . "'";

    // 재고관리 변경
    if ($_POST['step'] == '배송') {
      $ws_qty = $result_ct_s['ct_qty'] - $result_ct_s['ct_stock_qty'];
      if ($result_ct_s['io_type'] != 1) {
        $sql_stock[] = "
          insert into
            warehouse_stock
          set
            it_id = '{$result_ct_s['it_id']}',
            io_id = '{$result_ct_s['io_id']}',
            io_type = '{$result_ct_s['io_type']}',
            it_name = '{$result_ct_s['it_name']}',
            ws_option = '{$result_ct_s['ct_option']}',
            ws_qty = '-{$ws_qty}',
            mb_id = '{$result_ct_s['mb_id']}',
            ws_memo = '주문 출고완료({$od_id})',
            wh_name = '{$result_ct_s['ct_warehouse']}',
            od_id = '$od_id',
            ct_id = '{$_POST['ct_id'][$i]}',
            ws_created_at = NOW(),
            ws_updated_at = NOW()
        ";
      }
    }
    if ($_POST['step'] == '취소' || $_POST['step'] == '주문무효') {
      $sql_stock[] = "
        delete from
          warehouse_stock
        where
          od_id = '$od_id' and
          ct_id = '{$_POST['ct_id'][$i]}'
      ";
    }

    // 쿠폰 취소
    if ($_POST['step'] == '취소' || $_POST['step'] == '주문무효') {
      $sql_cp[] = "
        DELETE FROM
          g5_shop_coupon_log
        WHERE
          od_id = '{$od_id}'
      ";
    }

    if ($_POST['step'] === '배송') {
      add_notification(
        array(),
        $result_ct_s['mb_id'],
        '[이로움] 주문상품 배송 시작',
        $it_name . ' 배송이 시작되었습니다.',
        G5_URL . '/shop/orderinquiryview.php?od_id=' . $result_ct_s['od_id'],
      );
    }

    if ($_POST['step'] === '출고준비') {
      if (!isset($combine_orders[$od_id]))
        $combine_orders[$od_id] = true;

      // 이미 수동으로 합포적용된 상품이 있으면 자동 합포적용 하지 않음
      if ($result_ct_s['ct_combine_ct_id'])
        $combine_orders[$od_id] = false;
    }

    if ($_POST['step'] === '완료') {

      if ($result_ct_s['io_type'])
        $opt_price = $result_ct_s['io_price'];
      else
        $opt_price = $result_ct_s['ct_price'] + $result_ct_s['io_price'];

      $result_ct_s["opt_price"] = $opt_price;

      // 소계
      $result_ct_s['ct_price_stotal'] = $opt_price * $result_ct_s['ct_qty'] - $result_ct_s['ct_discount'];
      if ($result_ct_s["prodSupYn"] == "Y") {
        $result_ct_s["ct_price_stotal"] -= ($result_ct_s["ct_stock_qty"] * $opt_price);
      }

      $point_receiver = get_member($result_ct_s['mb_id']);
      $point = (int)($result_ct_s["ct_price_stotal"] / 100 * $default['de_it_grade' . $point_receiver['mb_grade'] . '_discount']);
      $point_content = "주문({$od_id}) {$it_name} 상품 배송완료 포인트 적립 ({$default['de_it_grade' . $point_receiver['mb_grade'] . '_name']} / {$default['de_it_grade' . $point_receiver['mb_grade'] . '_discount']}%)";

      insert_point($point_receiver['mb_id'], $point, $point_content, 'order_completed', $_POST['ct_id'][$i], $point_receiver['mb_id']);
    } else {
      $po_cancel_sql = " select * from {$g5['point_table']}
      where mb_id = '{$result_ct_s['mb_id']}'
        and po_rel_table = 'order_completed'
        and po_rel_id = '{$_POST['ct_id'][$i]}'
        and po_rel_action = '{$result_ct_s['mb_id']}' ";
      $po_cancel_row = sql_fetch($po_cancel_sql);
      if ($po_cancel_row['po_id']) {
        insert_point($po_cancel_row['mb_id'], $po_cancel_row['po_point'] * -1, $po_cancel_row['po_content'] . ' 취소 (포인트환수)', 'order_completed_cancel', $po_cancel_row['po_rel_id'], $po_cancel_row['po_rel_action']);
      }
    }

    //시스템 상태값 변경
    $stoId = $stoId . $result_ct_s['stoId'];
    $usrId = $result_ct_s['mb_id'];
    $entId = $result_ct_s['mb_entId'];

    foreach (explode('|', $result_ct_s['stoId']) as $temp_sto_id) {
      if ($temp_sto_id) {
        $stoIdList[$temp_sto_id] = $result_ct_s['ct_id'];
      }
    }
  }

  //완료 판매완료로 바꿈
  // 재고 주문 일시 배송 완료 -> 01
  // 수급자 신규 주문 일시 배송 완료 -> 02
  $stateCd = "06";
  switch ($_POST['step']) {
    case '배송':
    case '완료':
      $stateCd = is_pen_order($od_id) ? "02" : "01";
      break;
  }
  $stoIdDataList = explode('|', $stoId);
  $stoIdDataList = array_filter($stoIdDataList);
  $stoIdData = implode("|", $stoIdDataList);
  $sendData["stoId"] = $stoIdData;
  $res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
  $result_again = $res['data'];
  $new_sto_ids = array_map(function ($data) {
    global $stateCd;
    return array(
      'stoId' => $data['stoId'],
      'prodBarNum' => $data['prodBarNum'],
      'prodId' => $data['prodId'],
      'stateCd' => $stateCd
    );
  }, $result_again);

  if ($stateCd == "01") {
    for ($k = 0; $k < count($new_sto_ids); $k++) {
      $result_confirm = sql_fetch("select `prodSupYn` from `g5_shop_item` where `it_id` ='" . $new_sto_ids[$k]['prodId'] . "'");

      // 비급여 바코드 미입력 체크된 경우 패스
      $ct_result = sql_fetch("SELECT * FROM purchase_cart WHERE ct_id = '{$stoIdList[$new_sto_ids[$k]['stoId']]}'");
      if ($ct_result['ct_barcode_insert'] === $ct_result['ct_qty']) {
        continue;
      }

      if ($result_confirm['prodSupYn'] == "Y" && !$new_sto_ids[$k]['prodBarNum']) {
        echo "유통상품의 모든 바코드가 입력되어야 출고가 가능합니다";
        $flag = false;
        return false;
      }
    }
  }

  if ($flag) {

    for ($i = 0; $i < count($sql); $i++) {
      sql_query($sql[$i]);
      sql_query($sql_ct[$i]);
    }

    foreach ($sql_stock as $sql) {
      sql_query($sql);
    }

    foreach ($sql_cp as $sql) {
      sql_query($sql);
    }

    $api_data = array(
      'usrId' => $usrId,
      'entId' => $entId,
      'prods' => $new_sto_ids,
    );
    $api_result = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $api_data);
    if ($api_result['errorYN'] === 'N') {
      // 자동 합포적용

      echo "success";
    } else {
      echo "fail";
    }
  }
} else {
  echo "fail";
}
?>