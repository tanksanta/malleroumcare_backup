<?php
// $sub_menu = '400480';
include_once('./_common.php');
//랜덤값 생성
function GenerateString($length)
{
  $characters = "0123456789";
  $characters .= "abcdefghijklmnopqrstuvwxyz";
  $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $characters .= "_";
  $string_generated = "";
  $nmr_loops = $length;
  while ($nmr_loops--) {
    $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
  }
  return $string_generated;
}

// auth_check($auth[$sub_menu], "w");

try {
  $od_member = get_member($mb_id);
  if (!$od_member) {
    alert('존재하지 않는 물품공급 파트너입니다.');
  }

  // 22.11.08 : 서원 - 할인/반품 정보에 대한 데이터 저장
  $od_discount_info = [];
  $type_key = ['r', 'd'];
  $index = 0;
  for($i = 0; $i <2; $i++) {
    $_discount_it_name = $_POST[$type_key[$i].'_'.'discount_it_name'];
    if ($_discount_it_name) {
      foreach ($_discount_it_name as $key => $val) {
        $od_discount_info[$index] = array(
          "discount_type" => $type_key[$i],
          "discount_it_name" => $_POST[$type_key[$i].'_'.'discount_it_name'][$key],
          "discount_qty" => (int)preg_replace("/[^\d]/", "", $_POST[$type_key[$i].'_'.'discount_qty'][$key]),
          "discount_it_price" => (int)preg_replace("/[^\d]/", "", $_POST[$type_key[$i].'_'.'discount_it_price'][$key]),
          "discount_memo" => $_POST[$type_key[$i].'_'.'discount_memo'][$key]
        );
        $index++;
      }
    }
  }
  $od_discount_info = json_encode( $od_discount_info, JSON_UNESCAPED_UNICODE );

  $od_id = get_uniqid();
  $so_nb = get_uniqid_so_nb();
  $od_pwd = $member['mb_password'];
  $od_status = '발주대기';

  $sql = " insert purchase_order
            set od_id             = '$od_id',
                mb_id             = '$mb_id',
                od_name = '{$od_member['mb_name']}',
                od_email = '{$od_member['mb_email']}',
                od_tel = '{$od_member['mb_tel']}',
                od_hp = '{$od_member['mb_hp']}',
                od_fax = '{$od_member['mb_fax']}',
                od_zip1 = '{$od_member['mb_zip1']}',
                od_zip2 = '{$od_member['mb_zip2']}',
                od_addr1 = '{$od_member['mb_addr1']}',
                od_addr2 = '{$od_member['mb_addr2']}',
                od_addr3 = '{$od_member['mb_addr3']}',
                od_addr_jibeon = '{$od_member['mb_addr_jibeon']}',
                od_b_name = '{$od_b_name}',
                od_b_tel = '{$od_b_tel}',
                od_b_hp = '',
                od_b_zip1 = '',
                od_b_zip2 = '',
                od_b_addr1 = '{$od_b_addr1}',
                od_b_addr2 = '',
                od_b_addr3 = '',
                od_b_addr_jibeon = '{$mb['mb_addr_jibeon']}',
                od_pwd            = '',
                od_time           = '" . G5_TIME_YMDHIS . "',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '월 마감 정산',
                od_status         = '{$od_status}',
                od_memo           = '',
                od_shop_memo      = '',
                od_mod_history    = '',
                od_cash           = '0',
                od_cash_no        = '',
                od_cash_info      = '',
                od_writer         = '{$member['mb_id']}',
                od_add_admin      = '1',
                so_nb             = '{$so_nb}',
                od_purchase_manager  = '{$member['mb_id']}',
                od_discount_info  = '{$od_discount_info}'
                ";
  sql_query($sql);

  set_purchase_order_admin_log($od_id, '주문서 관리자 등록');

  $sql = " select * from purchase_order where od_id = '$od_id' ";
  $od = sql_fetch($sql);
  $od_member = get_member($od['mb_id']);

  $insert_ids = array();
  $ct_discount = (int)$ct_discount ?: 0;

  $it_ids = $_POST['it_id'];

//관리자가 등록한 코드
  $ct_admin_new = [];
  for ($i = 0; $i < count($it_ids); $i++) {
    $it_id = $it_ids[$i];

    if (!$it_id) {
      continue;
    }

    // 상품정보
    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    if ($it['it_sc_type'] == 1)
      $ct_send_cost = 2; // 무료
    else if ($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
      $ct_send_cost = 1; // 착불
    else
      $ct_send_cost = 0;

    // 옵션정보를 얻어서 배열에 저장
    $opt_list = array();
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
    $result = sql_query($sql);
    $lst_count = 0;

    for ($k = 0; $row = sql_fetch_array($result); $k++) {
      $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
      $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
      $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
      $opt_list[$row['io_type']][$row['io_id']]['price_partner'] = $row['io_price_partner'];
      $opt_list[$row['io_type']][$row['io_id']]['price_dealer'] = $row['io_price_dealer'];
      $opt_list[$row['io_type']][$row['io_id']]['price_dealer2'] = $row['io_price_dealer2'];
      $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
      $opt_list[$row['io_type']][$row['io_id']]['io_thezone'] = $row['io_thezone'];

      // 선택옵션 개수
      if (!$row['io_type'])
        $lst_count++;
    }

    // if (!$uid) {
    $uid = uuidv4();
    // }

    $comma = '';
    $sql = " INSERT INTO purchase_cart
    ( od_id,
      mb_id,
      it_id,
      it_name,
      it_sc_type,
      it_sc_method,
      it_sc_price,
      it_sc_minimum,
      it_sc_qty,
      ct_status,
      ct_price,
      ct_point,
      ct_point_use,
      ct_stock_use,
      ct_option,
      ct_qty,
      ct_qty_for_rollback,
      ct_notax,
      io_id,
      io_type,
      io_price,
      ct_time,
      ct_ip,
      ct_send_cost,
      ct_direct,
      ct_select,
      ct_select_time,
      pt_it,
      pt_msg1,
      pt_msg2,
      pt_msg3,
      ct_history,
      ct_discount,
      ct_price_type,
      ct_uid,
      io_thezone,
      ct_admin_new,
      ct_delivery_cnt,
      ct_delivery_price,
      ct_delivery_company,
      ct_is_direct_delivery,
      ct_direct_delivery_partner,
      ct_direct_delivery_price,
      prodMemo,
      prodSupYn,
      ct_warehouse,
      ct_warehouse_address,
      ct_warehouse_phone,
      ct_supply_partner,
      ct_delivery_expect_date,
      ct_part_info
    )
  VALUES ";

    $ct_select = 1;
    $ct_select_time = G5_TIME_YMDHIS;
    $sw_direct = 0;

    for ($k = 0; $k < 1; $k++) {
      $io_id = preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$i]);
      $io_type = preg_replace('#[^01]#', '', 0);
      // $io_value = $_POST['io_value'][$it_id][$k];

      $io_value = '';
      if ($io_id) {
        $it_option_subjects = explode(',', $it['it_option_subject']);
        $io_ids = explode(chr(30), $io_id);
        for ($g = 0; $g < count($io_ids); $g++) {
          if ($g > 0) {
            $io_value .= ' / ';
          }
          $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
        }
      }

      $pt_msg1 = get_text($_POST['pt_msg1'][$it_id][$k]);
      $pt_msg2 = get_text($_POST['pt_msg2'][$it_id][$k]);
      $pt_msg3 = get_text($_POST['pt_msg3'][$it_id][$k]);

      $io_price = $chk_dealer_price && $opt_list[$io_type][$io_id]['price_dealer'] ? $opt_list[$io_type][$io_id]['price_dealer'] : $opt_list[$io_type][$io_id]['price'];
      $io_price = $chk_dealer2_price && $opt_list[$io_type][$io_id]['price_dealer2'] ? $opt_list[$io_type][$io_id]['price_dealer2'] : $opt_list[$io_type][$io_id]['price'];
      $io_price = $chk_partner_price && $opt_list[$io_type][$io_id]['price_partner'] ? $opt_list[$io_type][$io_id]['price_partner'] : $io_price;
      // 임의 상품 옵션 가격 적용
      // $io_price = $chk_custom_price ? $_POST['io_price'][$it_id][$k] : $opt_list[$io_type][$io_id]['price'];
      // $io_price = (int)$_POST['it_price'][$i];
      $io_price = 0;
      $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];

      $ct_qty = $_POST['qty'][$i];
      $ct_qty = (int)preg_replace("/[^\d]/", "", $ct_qty);
      // $it_price = $it['it_price'];
      $it_price = $_POST['it_price'][$i];
      $it_price = (int)preg_replace("/[^\d]/", "", $it_price);

      $io_value = sql_real_escape_string(strip_tags($io_value));
      $remote_addr = get_real_client_ip();

      $add_ct_discount = $i == 0 && $k == 0 ? $ct_discount : 0;

      $point = 0;

      if ($it['it_delivery_min_cnt']) {
        //박스 개수 큰것 +작은것 - >ceil
        $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($ct_qty / $it['it_delivery_cnt']) : 0;
        //큰박스 floor 한 가격을 담음
        $ct_delivery_bigbox = $it['it_delivery_cnt'] ? floor($ct_qty / $it['it_delivery_cnt']) : 0;
        $ct_delivery_price = $it['it_delivery_cnt'] ? ($ct_delivery_bigbox * $it['it_delivery_price']) : 0;
        //나머지
        $remainder = $ct_qty % $it['it_delivery_cnt'];
        //나머지가 있으면
        if ($remainder) {
          //나머지가 최소수량보다 작으면
          if ($remainder <= $it['it_delivery_min_cnt']) {
            //작은 박스 가격 더해줌
            $ct_delivery_price = $ct_delivery_price + $it['it_delivery_min_price'];
          } else {
            //큰 박스 가격 더해줌
            $ct_delivery_price = $ct_delivery_price + $it['it_delivery_price'];
          }
        }
      } else {
        //없으면 큰박스로만 진행
        $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($ct_qty / $it['it_delivery_cnt']) : 0;
        $ct_delivery_price = $ct_delivery_cnt * $it['it_delivery_price'];
      }

      $ct_delivery_company = 'ilogen';

      $io_value = $io_value ? $io_value : addslashes($it['it_name']);
      $ct_admin_new_v = GenerateString(15);
      array_push($ct_admin_new, $ct_admin_new_v);

      // 입고창고
      $warehouse_name = '검단창고';
      if ($_POST['wh_name']) {
        $warehouse_name = $_POST['wh_name'];
      }
      $wh_row = sql_fetch(" select * from warehouse where wh_name = '{$warehouse_name}' ");
      $ct_warehouse = $wh_row['wh_name'];
      $ct_warehouse_address = $wh_row['wh_address'];
      $ct_warehouse_phone = $wh_row['wh_phone'];

      // 비유통상품 가격
      if ($it['prodSupYn'] == 'N') {
        $it_price = 0;
      }

      // 입고예정일 (2022-01-25 08:26:21)
      $ct_delivery_expect_date = "{$od_datetime_date} {$od_datetime_time}:00:00";

      // 22.11.08 : 서원 - 부분입고에 따른 데이터 기본셋팅
      $ct_part_info = array(
        1 => array(
          '_out_qty'=>'', '_out_dt'=>'', '_out_delivery_company'=>'', '_out_delivery_num'=>'', '_out_member'=>'',
          '_in_qty'=>'', '_in_dt'=>$od_datetime_date, '_in_dt_confirm'=>'', '_in_member'=>'',
          '_modify_dt'=>date("Y-m-d")
        )
      );
      $ct_part_info = json_encode( $ct_part_info, JSON_UNESCAPED_UNICODE );

      $insert_sql = $sql . "
    (
      '$od_id',
      '{$od['mb_id']}',
      '{$it['it_id']}',
      '" . addslashes($it['it_name']) . "',
      '{$it['it_sc_type']}',
      '{$it['it_sc_method']}',
      '{$it['it_sc_price']}',
      '{$it['it_sc_minimum']}',
      '{$it['it_sc_qty']}',
      '발주대기',
      '{$it_price}',
      '$point',
      '0',
      '0',
      '$io_value',
      '$ct_qty',
      '$ct_qty',
      '{$it['it_notax']}',
      '$io_id',
      '$io_type',
      '$io_price',
      '" . G5_TIME_YMDHIS . "',
      '$remote_addr',
      '$ct_send_cost',
      '$sw_direct',
      '$ct_select',
      '$ct_select_time',
      '{$it['pt_it']}',
      '$pt_msg1',
      '$pt_msg2',
      '$pt_msg3',
      '',
      '$add_ct_discount',
      '0',
      '$uid',
      '$io_thezone',
      '$ct_admin_new_v',
      '$ct_delivery_cnt',
      '$ct_delivery_price',
      '$ct_delivery_company',
      '{$it['it_is_direct_delivery']}',
      '{$it['it_direct_delivery_partner']}',
      '{$it['it_direct_delivery_price']}',
      '$memo[$i]',
      '{$it['prodSupYn']}',
      '$ct_warehouse',
      '$ct_warehouse_address',
      '$ct_warehouse_phone',
      '{$od_member['mb_id']}',
      '{$ct_delivery_expect_date}',
      '{$ct_part_info}'
    )";

      $result = sql_query($insert_sql);

      $insert_ids[] = sql_insert_id();
      $ct_count++;

      set_purchase_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ', ' . $io_id . ' 상품 추가');
    }
  }

// 주문 금액 계산

  $sql = "INSERT INTO purchase_cart_memo SET
            od_id = '{$od_id}' ,
            ctm_uid = '{$uid}',
            ctm_memo = '{$memo[$i]}'
        ";
  sql_query($sql);

  json_response(200, $od_id);
} catch (Exception $e) {
  json_response(500, $e->getMessage());
}