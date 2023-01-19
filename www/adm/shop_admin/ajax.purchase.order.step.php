<?php
include_once('./_common.php');
$_POST['ct_id']=$_POST['od_id'];

if($_POST['ct_id']&&$_POST['step']) {
  //변수지정
  $stoId="";
  $usrId="";
  $entId="";
  $state_cd_table = array();
  $flag = true;
  $stoIdList = array();
  $sql = [];
  $sql_ct = [];
  $sql_stock = [];
  $combine_orders = []; // 자동 합포적용
  $alim_orders = []; // 알림톡 보낼 주문들
  
  for($i=0;$i<count($_POST['ct_id']); $i ++) {
    // $sql_ct_s = "select a.od_id, a.it_id, a.it_name, a.ct_option, a.mb_id, a.stoId, b.mb_entId from purchase_cart a left join `g5_member` b on a.mb_id = b.mb_id where `ct_id` = '".$_POST['ct_id'][$i]."'";
    $sql_ct_s = "select
      a.od_id,
      a.it_id,
      a.it_name,
      a.ct_option,
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
    from purchase_cart a left join `g5_member` b on a.mb_id = b.mb_id where `ct_id` = '".$_POST['ct_id'][$i]."'";
    $result_ct_s = sql_fetch($sql_ct_s);
    $od_id = $result_ct_s['od_id'];

    $ct_part_info = json_decode($result_ct_s['ct_part_info'], true)[1];

    $od = sql_fetch(" select * from purchase_order where od_id = '$od_id' ");
    if($od['od_is_editing'] == 1) {
      // 사업소가 주문상품 변경 중이면 무시
      continue;
    }

    // 배송되면 재고 상태 판매완료로 바꿈
    // 재고 주문 일시 배송대기(06) -> 판매가능(01)
    // 수급자 신규 주문 일시 배송대기(06) -> 판매완료(02)
    $state_cd = '06';
    if(in_array($_POST['step'], ['배송', '완료'])) {
      $state_cd = is_pen_order($od_id) ? "02" : "01";

      // 알림톡 발송 목록에 추가
      if(!isset($alim_orders[$od_id])) {
        $alim_orders[$od_id] = true;
      }
    }
    $sto_id_list = array_filter(explode('|', $result_ct_s['stoId']));
    foreach($sto_id_list as $sto_id) {
      $state_cd_table[$sto_id] = $state_cd;
    }

    $content = $result_ct_s['it_name'];
    if($result_ct_s['it_name'] !== $result_ct_s['ct_option']){
      $content = $content."(".$result_ct_s['ct_option'].")";
    }
    $content = $content . " - " . clean_xss_tags($_POST['step']) . " 변경";

    //로그 insert
    $sql[$i]= "INSERT INTO purchase_order_admin_log SET
      od_id = '{$od_id}',
      mb_id = '{$member['mb_id']}',
      ol_content = '{$content}',
      ol_datetime = now()
    ";
    
    //상태 update
    $add_sql = '';
//    if($_POST['step'] == "배송") { $add_sql .= ", `ct_ex_date` = CURDATE()"; }
    if($_POST['step'] == "입고완료") {
      $ct_part_info['_in_dt_confirm'] = date("Y-m-d");
      $ct_part_info_f[1] = $ct_part_info;

      $add_sql .= ", `ct_delivery_complete_date` = CURDATE(), ct_part_info ='".json_encode($ct_part_info_f)."'";
    }

    $sql_ct[$i] = "update purchase_cart set `ct_status` = '".$_POST['step']."'".$add_sql.", `ct_move_date`= NOW() where `ct_id` = '".$_POST['ct_id'][$i]."'";

    // 재고관리 변경
    if($_POST['step'] == '발주완료') {
      $wh_row = sql_fetch("
        select *
        from warehouse_stock
        where
          od_id = '$od_id' AND
          ct_id = '{$_POST['ct_id'][$i]}' AND
          it_id = '{$result_ct_s['it_id']}' AND
          io_id = '{$result_ct_s['io_id']}'
      ");
      $ws_qty = $result_ct_s['ct_qty'];

      if (!$wh_row) {
        if($result_ct_s['io_type'] != 1) {
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
        }
      } else {
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
      }
    }

    if($_POST['step'] == '입고완료') {
      if($result_ct_s['io_type'] != 1) {
        $sql_stock[] = "
          update
            warehouse_stock
          set
            ws_qty = '{$ws_qty}',
            ws_scheduled_qty = 0,
            mb_id = '{$result_ct_s['mb_id']}',
            ws_memo = '주문 입고완료({$od_id})',
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
  exit;
}
?>
