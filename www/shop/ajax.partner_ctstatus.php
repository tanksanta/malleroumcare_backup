<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$manager_mb_id = get_session('ss_manager_mb_id');
if($manager_mb_id) {
  $manager = get_member($manager_mb_id);
}

$ct_status = in_array($_POST['ct_status'], ['출고준비', '배송', '취소']) ? $_POST['ct_status'] : '';
$ct_id_arr = get_search_string($_POST['ct_id']);

if(!$ct_status || !$ct_id_arr || !is_array($ct_id_arr))
  json_response(400, '주문상태를 변경할 상품을 선택해주세요.');

$sto_id = [];
$sql = [];
$mb_id;
$sto_id_od_id_table = [];
foreach($ct_id_arr as $ct_id) {
  $cart = sql_fetch("
    SELECT * FROM {$g5['g5_shop_cart_table']}
    WHERE ct_id = '{$ct_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
  ");
  
  update_partner_install_schedule_status_by_ct_id($ct_id, $ct_status);

  if($cart['ct_status'] == $ct_status) // 변경하려는 상태가 기존 상태랑 똑같은경우
    continue;

  if(!$cart || !$cart['ct_id'])
    json_response(400, '해당 상품의 주문상태를 변경할 수 있는 권한이 없습니다.');
  
  if(!in_array($cart['ct_status'], ['출고준비', '배송']))
    json_response(400, '해당 상품의 주문상태를 변경할 수 없습니다.');

  $od_id = $cart['od_id'];
  $mb_id = $cart['mb_id'];

  // 배송(출고완료) 상태는 배송정보가 입력되어야 변경할 수 있음
  if($ct_status == '배송' && !$cart['ct_delivery_num'])
    json_response(400, '배송정보를 입력해주세요.');

  $set_sql = ' , ct_ex_date = NULL ';
  if($ct_status == '배송') {
    $set_sql = ' , ct_ex_date = CURDATE() ';

    if($cart['io_type'] != 1) {
      $ws_qty = $cart['ct_qty'] - $cart['ct_stock_qty'];
      $sql[] = "
        insert into
          warehouse_stock
        set
          it_id = '{$cart['it_id']}',
          io_id = '{$cart['io_id']}',
          io_type = '{$cart['io_type']}',
          it_name = '{$cart['it_name']}',
          ws_option = '{$cart['ct_option']}',
          ws_qty = '-{$ws_qty}',
          mb_id = '{$cart['mb_id']}',
          ws_memo = '주문 출고완료({$od_id})',
          wh_name = '{$cart['ct_warehouse']}',
          od_id = '$od_id',
          ct_id = '$ct_id',
          ws_created_at = NOW(),
          ws_updated_at = NOW()
      ";
    }
  }

  if($ct_status == '취소') {
    $sql[] = "
      delete from
        warehouse_stock
      where
        od_id = '$od_id' and
        ct_id = '$ct_id'
    ";
  }

  $sql[] = "
    UPDATE
      {$g5['g5_shop_cart_table']}
    SET
      ct_status = '{$ct_status}',
      ct_move_date = NOW()
      {$set_sql}
    WHERE
      ct_id = '{$ct_id}'
  ";

  $it_name = $cart['it_name'];
  if($cart['ct_option'] && $cart['ct_option'] != $cart['it_name']) $it_name .= "({$cart['ct_option']})";
  switch ($ct_status) {
    case '출고준비': $ct_status_text="출고준비"; break;
    case '배송': $ct_status_text="출고완료"; break;
    case '취소': $ct_status_text="주문취소"; break;
  }
  $log_content = '';
  $mb_id = $member['mb_id'];
  if($manager) {
    $mb_id = $manager_mb_id;
  }
  $log_content .= "{$it_name}-{$ct_status_text} 변경";

  $sql[] = "
    INSERT INTO
      g5_shop_order_admin_log
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

// 바코드 정보 조회
if($sto_id) {
  $stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
    'stoId' => implode('|', $sto_id)
  ));
  if(!$stock_result['data'])
    json_response(500, '시스템 서버 오류', $stock_result);

  $prods = array_map(function($data) {
    global $ct_status, $sto_id_od_id_table;

    $stateCd = '06'; // 재고대기
    if($ct_status == '배송')
      $stateCd = is_pen_order($sto_id_od_id_table[$data['stoId']]) ? "02" : "01";

    return array(
      'stoId' => $data['stoId'],
      'prodBarNum' => $data['prodBarNum'],
      'prodId' => $data['prodId'],
      'stateCd' => $stateCd
    );
  }, $stock_result['data']);

  if($ct_status == '배송') {
    foreach($prods as $prod) {
      $prodSupYn = sql_fetch(" SELECT prodSupYn FROM g5_shop_item WHERE it_id = '{$prod['prodId']}' ")['prodSupYn'];
      if($prodSupYn == 'Y' && !$prod['prodBarNum'])
        json_response(400, '모든 유통상품의 바코드 정보가 입력되어야 출고가 가능합니다.');
    }
  }

  $api_result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'prods' => $prods
  ));

  if($api_result['errorYN'] != 'N')
    json_response(500, $api_result['message']);
}

foreach($sql as $query) {
  $result = sql_query($query);
  if(!$result)
    json_response(500, 'DB 서버 오류 발생');
}

json_response(200, 'OK');
?>