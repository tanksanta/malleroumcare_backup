<?php
include_once('./_common.php');

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// 22.11.08 : 서원
// 부분 입출고에 따른 발주서 내용 변경시 부분출고된 데이터와 변경하려는 데이터 체크
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

if( !$_POST['od_id'] || !$_POST['it_id'] )
  json_response(400, '오류!!');

$_od_id = $_POST['od_id'];
$_it_id = $_POST['it_id'];
$_mode = $_POST['mode'];

$ct = sql_fetch("
  SELECT 
    ct_qty, ct_part_info, ct_status, od_send_yn
  FROM 
    purchase_cart ct
  LEFT JOIN
    purchase_order od
  ON
    ct.od_id = od.od_id
  WHERE 
    ct.od_id = '{$_od_id}'
    AND ct.it_id ='{$_it_id}'
");

  $_qty = $_POST['qty'];
  $_part_info = json_decode( $ct['ct_part_info'], true );

  $_in_qty = 0;
  $tmp_qty = 0;
  foreach ($_part_info as $key => $val) {
    $tmp_qty += (int)$val['_out_qty'];
  }
  foreach ($_part_info as $key => $val) {
    $_in_qty += (int)$val['_in_qty'];
  }

  $result_yn = $result_qty = '';
  if( $_mode == "check_qty" ) {
    if( $_qty < $_in_qty ) {
      $result_yn = 'N';
      $result_qty = $_in_qty;
    }
  }
  else if( $_mode == "check_del" ) {
    if($ct['od_send_yn'] == '1') {
      $_tmp = array(
        "yn" => 'S',
        "qty" => $_qty
      );
      json_response(200, $_tmp);
    }

    if($ct['ct_status'] == '입고완료') {
      $_tmp = array(
        "yn" => 'N',
        "qty" => $_qty
      );
      json_response(200, $_tmp);
    }

    if( $tmp_qty > 0 ) {
      $result_yn = 'N';
      $result_qty = $tmp_qty;
    }
  } else { }


  if(empty($result_yn)&&empty($result_qty)) {
    json_response(400, 'OK');
  } else {
    $_tmp = array(
      "yn" => $result_yn,
      "qty" => $result_qty
    );

    json_response(200, $_tmp);
  }

?>