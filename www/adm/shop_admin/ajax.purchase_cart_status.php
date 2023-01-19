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
  $sql_stock = [];

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
      a.ct_warehouse,
      a.ct_part_info
    from `purchase_cart` a left join `g5_member` b on a.mb_id = b.mb_id where `ct_id` = '" . $_POST['ct_id'][$i] . "'";
    $result_ct_s = sql_fetch($sql_ct_s);
    $od_id = $result_ct_s['od_id'];

    $ct_part_info = json_decode($result_ct_s['ct_part_info'], true)[1];

    $od = sql_fetch(" select * from purchase_order where od_id = '$od_id' ");
    if ($od['od_is_editing'] == 1) {
      echo '해당 주문은 사업소에서 수정 중이므로 주문단계가 변경되지 않았습니다.';
      exit;
    }

    if (in_array($result_ct_s['ct_status'], ['파트너발주취소','관리자발주취소','발주취소','취소'])) {
      echo '취소된 주문은 상태변경이 불가능합니다.';
      exit;
    }

    $content = $result_ct_s['it_name'];
    if ($result_ct_s['it_name'] !== $result_ct_s['ct_option']) {
      $content = $content . "(" . $result_ct_s['ct_option'] . ")";
    }
    $it_name = $content;
    $content = $content . " - " . clean_xss_tags($_POST['step']) . " 변경";

    //로그 insert
    $sql[$i] = "INSERT INTO purchase_order_admin_log SET
      od_id = '{$od_id}',
      ct_id = '{$ct_id}',
      mb_id = '{$member['mb_id']}',
      ol_content = '{$content}',
      ol_datetime = now()
    ";

    //상태 update
    $add_sql = '';
    if ($_POST['step'] == "입고완료") {
      $ct_part_info['_in_dt_confirm'] = date("Y-m-d");
      $ct_part_info_f[1] = $ct_part_info;

      $add_sql .= ", `ct_delivery_complete_date` = CURDATE(), ct_part_info ='".json_encode($ct_part_info_f)."'";
    }

    $sql_ct[$i] = "update `purchase_cart` set `ct_status` = '" . $_POST['step'] . "'" . $add_sql . ", `ct_move_date`= NOW() where `ct_id` = '" . $_POST['ct_id'][$i] . "'";

    // 재고관리 변경
    $wh_row = sql_fetch("
        select *
        from warehouse_stock
        where
          od_id = '$od_id' AND
          ct_id = '{$ct_id}' AND
          it_id = '{$result_ct_s['it_id']}' AND
          io_id = '{$result_ct_s['io_id']}'
      ");
    $ws_qty = $result_ct_s['ct_qty'];

    if (!$wh_row) {
      $sql_stock[] = "
        insert into
          warehouse_stock
        set
          it_id = '{$result_ct_s['it_id']}',
          io_id = '{$result_ct_s['io_id']}',
          io_type = '{$result_ct_s['io_type']}',
          it_name = '{$result_ct_s['it_name']}',
          ws_option = '{$result_ct_s['ct_option']}',
          ws_qty = '0',
          ws_scheduled_qty = '{$ws_qty}',
          mb_id = '{$result_ct_s['mb_id']}',
          ws_memo = '주문 발주완료({$od_id})',
          wh_name = '{$result_ct_s['ct_warehouse']}',
          od_id = '$od_id',
          ct_id = '{$_POST['ct_id'][$i]}',
          inserted_from = 'purchase_cart',
          ws_created_at = NOW(),
          ws_updated_at = NOW()
      ";
    } else {
      if ($_POST['step'] == '발주대기' || $_POST['step'] == '발주완료' || $_POST['step'] == '출고완료') {
        $sql_stock[] = "
          update
            warehouse_stock
          set
            ws_qty = '0',
            ws_scheduled_qty = '{$ws_qty}',
            mb_id = '{$result_ct_s['mb_id']}',
            ws_memo = '주문 발주완료({$od_id})',
            ws_updated_at = NOW()
          where
            od_id = '$od_id' AND
            ct_id = '{$_POST['ct_id'][$i]}' AND
            it_id = '{$result_ct_s['it_id']}' AND
            io_id = '{$result_ct_s['io_id']}'
        ";
      } else if ($_POST['step'] == '발주완료') {
        $sql_stock[] = "
          update
            warehouse_stock
          set
            ws_qty = '{$ws_qty}',
            ws_scheduled_qty = '0',
            mb_id = '{$result_ct_s['mb_id']}',
            ws_memo = '주문 발주완료({$od_id})',
            ws_updated_at = NOW()
          where
            od_id = '$od_id' AND
            ct_id = '{$_POST['ct_id'][$i]}' AND
            it_id = '{$result_ct_s['it_id']}' AND
            io_id = '{$result_ct_s['io_id']}'
        ";
      }
    }

    if ($_POST['step'] == '관리자발주취소' or $_POST['step'] == '파트너발주취소' or $_POST['step'] == '발주취소' or $_POST['step'] == '취소') {
      $sql_stock[] = "
        delete from
          warehouse_stock
        where
          od_id = '$od_id' and
          ct_id = '{$_POST['ct_id'][$i]}'
      ";
    }
  }

  for ($i = 0; $i < count($sql); $i++) {
    sql_query($sql[$i]);
    sql_query($sql_ct[$i]);
  }

  foreach ($sql_stock as $sql) {
    sql_query($sql);
  }

  echo "success";
} else {
  echo "fail";
}
?>