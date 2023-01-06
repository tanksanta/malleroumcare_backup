<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$manager_mb_id = get_session('ss_manager_mb_id');
if($manager_mb_id) {
  $manager = get_member($manager_mb_id);
}

// '출고완료', '발주취소' 중 파트너는 출고완료 취소만 가능
$ct_status = in_array($_POST['ct_status'], ['출고완료', '발주취소']) ? $_POST['ct_status'] : '';
$ct_id_arr = get_search_string($_POST['ct_id']);

if(!$ct_status || !$ct_id_arr || !is_array($ct_id_arr))
  json_response(400, '주문상태를 변경할 상품을 선택해주세요.');

$sto_id = [];
$sql = [];
$mb_id;
$sto_id_od_id_table = [];
foreach($ct_id_arr as $ct_id) {
  $cart = sql_fetch("
    SELECT * FROM purchase_cart
    WHERE ct_id = '{$ct_id}' and ct_supply_partner = '{$member['mb_id']}'
  ");

  if($cart['ct_status'] == $ct_status) // 변경하려는 상태가 기존 상태랑 똑같은경우
    continue;

  if(!$cart || !$cart['ct_id'])
    json_response(400, '해당 상품의 주문상태를 변경할 수 있는 권한이 없습니다.');

  //출고완료 상태에서 발주취소 적용 불가
  if($cart['ct_status'] == '출고완료' && $ct_status == '발주취소')
    json_response(400, '출고완료 내역이 확인되어, 변경 할 수 없습니다.');

  // 마감완료/출고완료/입고완료 상태의 발주건은 상태 변경 불가
  if(in_array($cart['ct_status'], ['마감완료', '출고완료', '입고완료']))
    json_response(400, '해당 상품의 주문상태를 변경할 수 없습니다.');

  $od_id = $cart['od_id'];
  $mb_id = $cart['mb_id'];

  // 배송(출고완료) 상태는 배송정보가 입력되어야 변경할 수 있음
  if($ct_status == '출고완료' && !$cart['ct_delivery_num'])
    json_response(400, '배송정보를 입력해주세요.');

  $set_sql = ' , ct_ex_date = NULL ';
  if($ct_status == '입고완료') {
    $set_sql = ' , ct_ex_date = CURDATE() ';

    if($cart['io_type'] != 1) {
      $ws_qty = $cart['ct_qty'];
      $sql[] = "
        insert into
          warehouse_stock
        set
          it_id = '{$cart['it_id']}',
          io_id = '{$cart['io_id']}',
          io_type = '{$cart['io_type']}',
          it_name = '{$cart['it_name']}',
          ws_option = '{$cart['ct_option']}',
          ws_qty = '{$ws_qty}',
          mb_id = '{$cart['mb_id']}',
          ws_memo = '발주 주문 입고완료({$od_id})',
          wh_name = '{$cart['ct_warehouse']}',
          od_id = '$od_id',
          ct_id = '$ct_id',
          inserted_from = 'purchase_cart',
          ws_created_at = NOW(),
          ws_updated_at = NOW()
      ";
    }
  }

  if($ct_status == '발주취소') {
    $sql[] = "
      delete from
        warehouse_stock
      where
        od_id = '$od_id' and
        ct_id = '$ct_id'
    ";
  }

  if($ct_status == '출고완료') {
    $_part_info = json_decode($cart['ct_part_info'],true);
    $_part_info[1]['_out_dt'] = date("Y-m-d");
    $ct_part_info = json_encode($_part_info);
    $set_sql .= " , ct_part_info = '{$ct_part_info}' ";
  }

  $sql[] = "
    UPDATE
      purchase_cart
    SET
      ct_status = '{$ct_status}',
      ct_move_date = NOW()
      {$set_sql}
    WHERE
      ct_id = '{$ct_id}'
  ";

  $it_name = $cart['it_name'];
  if($cart['ct_option'] && $cart['ct_option'] != $cart['it_name']) $it_name .= "({$cart['ct_option']})";

  $log_content = '';
  $mb_id = $member['mb_id'];
  if($manager) {
    $mb_id = $manager_mb_id;
  }
  $log_content .= "{$it_name}-{$ct_status} 변경";

  $sql[] = "
    INSERT INTO
      purchase_order_admin_log
    SET
      od_id = '$od_id',
      mb_id = '{$mb_id}',
      ol_content = '{$log_content}',
      ol_datetime = NOW()
  ";

  foreach(array_filter(explode('|', $cart['stoId'])) as $id) {
    $sto_id[] = $id;
    $sto_id_od_id_table[$id] = $od_id;
  }
}

foreach($sql as $query) {
  $result = sql_query($query);
  if(!$result)
    json_response(500, 'DB 서버 오류 발생');
}

json_response(200, 'OK');
?>
