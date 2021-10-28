<?php
include_once('./_common.php');

if (!$od_id) {
  json_response(400, '주문번호 입력하세요.');
}

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
  json_response(400, '존재하지 않는 주문서입니다.');
}

$sql = "SELECT *
from g5_shop_order_cancel_request
where od_id = '{$od['od_id']}'";
$cancel_request = sql_fetch($sql);

$request_type = $cancel_request['request_type'] ?: $to;
$request_type_text = $request_type === 'cancel' ? '환불' : '반품';

if (!$cancel_request['od_id']) {
  
  if (!in_array($to, ['cancel', 'return'])) {
    json_response(400, '정상적인 접근이 아닙니다.');
  }
  
  if ($to == "cancel")
    $status = "취소 대기중";
  if ($to == "return")
    $status = "반품 대기중";

  $time =  date('Y-m-d H:i:s', time());
  $sql = "INSERT INTO g5_shop_order_cancel_request
          SET
              od_id = '{$od['od_id']}',
              mb_id = '{$member['mb_id']}',
              request_type = '{$to}',
              request_status = '{$status}',
              request_reason_type = '{$request_reason_type}',
              request_reason = '{$cancel_memo}',
              requested_at = '{$time}'
          ";
  sql_query($sql);

  $sql = "UPDATE {$g5['g5_shop_order_table']} SET
    od_cancel_reason = '{$request_reason_type}',
    od_cancel_memo = '{$cancel_memo}',
    od_cancel_time = now()
  WHERE od_id = '$od_id' 
  ";

  sql_query($sql);
  
  set_order_admin_log($od_id, "{$request_type_text}신청 선택");

  json_response(200, 'OK');
}

$refund_price = (int)$refund_price ?: 0;
$refund_status = htmlspecialchars($refund_status);
$refund_memo = htmlspecialchars($refund_memo);

$ledger_id_sql = ", lc_id = NULL ";

if ($cancel_request['refund_price'] != $refund_price) {

  $ledger_refund_price = $refund_price * -1;

  if ($cancel_request['lc_id']) {

    $result = sql_query("UPDATE
        ledger_content
      SET
        lc_amount = '{$ledger_refund_price}'
      WHERE lc_id = '{$cancel_request['lc_id']}'
    ");
  } else {
      $sql = "SELECT * FROM g5_shop_cart WHERE od_id = '{$od['od_id']}'";
      $cart_result = sql_query($sql);
      while ($row2 = sql_fetch_array($cart_result)) {
          $od['cart'][] = $row2;
      }

      if (count($od['cart']) > 1) {
          $od_cart_count = ' 외 ' . (count($od['cart']) - 1) .'개';
      } else {
          $od_cart_count = '';
      }

      $it_name		= $od['cart'][0]['it_name'] . $od_cart_count;
      $ledger_content_memo = '환불금액 (' . $it_name . ')';
      
      $result = sql_query("INSERT INTO
          ledger_content
        SET
          mb_id = '{$od['mb_id']}',
          lc_type = '2',
          lc_amount = '{$ledger_refund_price}',
          lc_memo = '{$ledger_content_memo}',
          lc_created_at = now(),
          lc_created_by = '{$member['mb_id']}',
          lc_base_date = now()
      ");

      $lc_id = sql_insert_id();

      $ledger_id_sql = ", lc_id = '{$lc_id}'";
  }
}

$sql = "UPDATE g5_shop_order_cancel_request SET
  refund_price = '{$refund_price}',
  refund_status = '{$refund_status}',
  refund_memo = '{$refund_memo}',
  refund_at = now()
  {$ledger_id_sql}
WHERE od_id = '{$od_id}'
";
sql_query($sql);

set_order_admin_log($od_id, "{$request_type_text}진행 수정 ({$refund_status}, " . number_format($refund_price) . "원)");

json_response(200, 'OK', $rows);