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

//print_r( $_POST );

$_od_id = $_POST['od_id'];
$_it_id = $_POST['it_id'];
$_ct_id = $_POST['ct_id'];

try {

  $_od = sql_fetch("SELECT * FROM purchase_order WHERE od_id='{$_od_id}'");
  if( !$_od ) { json_response(400, "발주서 정보가 존재 하지 않습니다."); }


  $od_member = get_member($mb_id);
  if (!$od_member) { alert('존재하지 않는 물품공급 파트너입니다.'); }


  // 22.11.08 : 서원 - 할인/반품 정보 리스트 json 처리
	$od_discount_info = [];
  $type_key = ['r', 'd'];
  $index = 0;
  for($i = 0; $i <2; $i++) {
    $_discount_it_name = $_POST[$type_key[$i].'_'.'discount_it_name'];
    if ($_discount_it_name) {
      foreach ($_discount_it_name as $key => $val) {
        if ($val) {
          $od_discount_info[$index] = array(
            "discount_type" => $type_key[$i],
            "discount_it_name" => $_POST[$type_key[$i] . '_' . 'discount_it_name'][$key],
            "discount_qty" => (int)preg_replace("/[^\d]/", "", $_POST[$type_key[$i] . '_' . 'discount_qty'][$key]),
            "discount_it_price" => (int)preg_replace("/[^\d]/", "", $_POST[$type_key[$i] . '_' . 'discount_it_price'][$key]),
            "discount_memo" => $_POST[$type_key[$i] . '_' . 'discount_memo'][$key]
          );
          $index++;
        }
      }
    }
  }
  $od_discount_info = json_encode( $od_discount_info, JSON_UNESCAPED_UNICODE );

  $_ct = ""; 
  $_ct = sql_query("SELECT * FROM purchase_cart WHERE od_id='{$_od_id}'");

  $ct_old = []; $ct_del_list = []; 
  foreach($_ct as $key => $val) {
    $ct_old[$val['ct_id']] = $val;

    // 22.11.10 : 서원 - 변경된 발주서에서 기존  상품이 빠지는 경우 삭제 리스트 체크
    $_mode = true;
    foreach($_ct_id as $key2 => $val2) {
      if( $val['ct_id'] == $val2 ) { $_mode = false; break; } 
    }

    // 22.11.10 : 서원 - 기존 발주서 상품 삭제 리스트 ct_id값 저장
    if( $_mode ) { $ct_del_list[] = $val['ct_id']; }
  }
  //print_r($ct_del_list);


  // 입고창고
  $warehouse_name = '검단창고';
  if ($_POST['wh_name']) { $warehouse_name = $_POST['wh_name']; }
  $wh_row = sql_fetch("SELECT * FROM warehouse WHERE wh_name = '{$warehouse_name}' ");
  $ct_warehouse = $wh_row['wh_name'];
  $ct_warehouse_address = $wh_row['wh_address'];
  $ct_warehouse_phone = $wh_row['wh_phone'];

  
  // 22.11.10 : 서원 - 발주 정보 리스트 체크
  if( $_it_id ) {

    $_ct_update_sql = []; // 기종 발주서 업뎃용
    $_ct_insert_sql = []; // 신규 발주서 입력용

    //관리자가 등록한 코드
    $ct_admin_new = [];

    set_purchase_order_admin_log($od_id, '발주서 변경 시작');

    foreach($_it_id as $key => $val) {

      if (!$val) { continue; }

      $sql = " SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = '$val' ";
      $it = sql_fetch($sql);

      if($it['it_sc_type'] == 1){
        $ct_send_cost = 2; // 무료
      }
      else if( ($it['it_sc_type'] > 1) && ($it['it_sc_method'] == 1) ) {
        $ct_send_cost = 1; // 착불
      } else {
        $ct_send_cost = 0;
      }

      // 옵션정보를 얻어서 배열에 저장
      $sql = " SELECT * FROM {$g5['g5_shop_item_option_table']} WHERE it_id = '$val' AND io_use = 1 ORDER BY io_no ASC ";
      $it_opt = sql_query($sql);

      $lst_count = 0;
      $opt_list = array();
      for ($k = 0; $row = sql_fetch_array($it_opt); $k++) {
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

      $uid = uuidv4();

      $comma = '';
      $sql = " purchase_cart
      SET ( 
        od_id,
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
        ct_part_info,
        ct_modify_date
      ) VALUES ";


      $ct_select = 1;
      $ct_select_time = G5_TIME_YMDHIS;
      $sw_direct = 0;

      $io_id = preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$key]);
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


      $pt_msg1 = get_text($_POST['pt_msg1'][$it_id][$key]);
      $pt_msg2 = get_text($_POST['pt_msg2'][$it_id][$key]);
      $pt_msg3 = get_text($_POST['pt_msg3'][$it_id][$key]);


      $io_price = $chk_dealer_price && $opt_list[$io_type][$io_id]['price_dealer'] ? $opt_list[$io_type][$io_id]['price_dealer'] : $opt_list[$io_type][$io_id]['price'];
      $io_price = $chk_dealer2_price && $opt_list[$io_type][$io_id]['price_dealer2'] ? $opt_list[$io_type][$io_id]['price_dealer2'] : $opt_list[$io_type][$io_id]['price'];
      $io_price = $chk_partner_price && $opt_list[$io_type][$io_id]['price_partner'] ? $opt_list[$io_type][$io_id]['price_partner'] : $io_price;
      // 임의 상품 옵션 가격 적용
      // $io_price = $chk_custom_price ? $_POST['io_price'][$it_id][$k] : $opt_list[$io_type][$io_id]['price'];
      // $io_price = (int)$_POST['it_price'][$i];
      $io_price = 0;
      $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];

      $ct_qty = $_POST['qty'][$key];
      $ct_qty = (int)preg_replace("/[^\d]/", "", $ct_qty);
      // $it_price = $it['it_price'];

      $it_price = $_POST['it_price'][$key];
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

      $ct_delivery_company = '';

      $io_value = $io_value ? $io_value : addslashes($it['it_name']);
      $ct_admin_new_v = GenerateString(15);
      array_push($ct_admin_new, $ct_admin_new_v);

      // 비유통상품 가격
      if ($it['prodSupYn'] == 'N') {
        $it_price = 0;
      }

      // 입고예정일 (2022-01-25 08:26:21)
      $od_datetime_date = "{$_POST['od_datetime_date']} 00:00:00";
      $ct_delivery_expect_date = "{$od_datetime_date} 00:00:00";

      // 22.11.10 : 서원 - 변경된 발주서의 상품이 기존에 입력되어 있는 상품과 동일 여부 체크
      if( $_ct_id[$key] ) {
        //echo("기존");
        $_ct = "";
        $_ct = $ct_old[ $_ct_id[$key] ];

        // ct_part_info
        $ct_part_info = json_decode( $_ct['ct_part_info'], true )[1]; // 차수 없을때
        $ct_part_info['_in_dt'] = $_POST['od_datetime_date'];
        $enc_ct_part_info = '{ "1" : '.json_encode($ct_part_info).'}';
        
        $sql = "";
        $sql = ("
          UPDATE
            purchase_cart
          SET
            `od_id` = '$od_id',
            `mb_id` = '{$_od['mb_id']}',
            `it_id` = '{$it['it_id']}',
            `it_name` = '" . addslashes($it['it_name']) . "',
            `it_sc_type` = '{$it['it_sc_type']}',
            `it_sc_method` = '{$it['it_sc_method']}',
            `it_sc_price` = '{$it['it_sc_price']}',
            `it_sc_minimum` = '{$it['it_sc_minimum']}',
            `it_sc_qty` = '{$it['it_sc_qty']}',
            `ct_status` = '{$_ct['ct_status']}',
            `ct_price` = '{$it_price}',
            `ct_point` = '$point',
            `ct_point_use` = '0',
            `ct_stock_use` = '0',
            `ct_option` = '$io_value',
            `ct_qty` = '$ct_qty',
            `ct_qty_for_rollback` = '$ct_qty',
            `ct_notax` = '{$it['it_notax']}',
            `io_id` = '$io_id',
            `io_type` = '$io_type',
            `io_price` = '$io_price',
            `ct_time` = '{$_ct['ct_time']}',
            `ct_ip` = '$remote_addr',
            `ct_send_cost` = '$ct_send_cost',
            `ct_direct` = '$sw_direct',
            `ct_select` = '$ct_select',
            `ct_select_time` = '{$_ct['ct_select_time']}',
            `pt_it` = '{$it['pt_it']}',
            `pt_msg1` = '$pt_msg1',
            `pt_msg2` = '$pt_msg2',
            `pt_msg3` = '$pt_msg3',
            `ct_history` = '',
            `ct_discount` = '$add_ct_discount',
            `ct_price_type` = '0',
            `ct_uid` = '$uid',
            `io_thezone` = '{$_ct['io_thezone']}',
            `ct_admin_new` = '$ct_admin_new_v',
            `ct_delivery_cnt` = '{$_ct['ct_delivery_cnt']}',
            `ct_delivery_price` = '$ct_delivery_price',
            `ct_delivery_company` = '{$_ct['ct_delivery_company']}',
            `ct_is_direct_delivery` = '{$it['it_is_direct_delivery']}',
            `ct_direct_delivery_partner` = '{$it['it_direct_delivery_partner']}',
            `ct_direct_delivery_price` = '{$it['it_direct_delivery_price']}',
            `prodMemo` = '{$_POST['memo'][$key]}',
            `prodSupYn` = '{$it['prodSupYn']}',
            `ct_warehouse` = '$ct_warehouse',
            `ct_warehouse_address` = '$ct_warehouse_address',
            `ct_warehouse_phone` = '$ct_warehouse_phone',
            `ct_supply_partner` = '{$od_member['mb_id']}',
            `ct_delivery_expect_date` = '{$od_datetime_date}',
            `ct_part_info` = '{$enc_ct_part_info}',
            `ct_modify_date` = '" . G5_TIME_YMD . "'
          WHERE 
            `od_id` = '{$_ct['od_id']}' 
            AND `ct_id` = '{$_ct['ct_id']}'
        ");

        $_ct_update_sql[] = $sql;

        $result = sql_query($_ct_update_sql);
        set_purchase_order_admin_log($_od_id, '상품: ' . addslashes( $_ct['it_name'] ) . ', ' . $_ct['ct_option'] . ' 상품 [발주변경]');
      } else {

        // 22.11.10 : 서원 - 상품 it_id가 존재하는 입력 데이터만 처리
        if( $val ) {
          //echo("신규");

          // 22.11.08 : 서원 - 부분입고에 따른 데이터 기본셋팅
          $ct_part_info = array(
            1 => array(
              '_out_qty'=>'', '_out_dt'=>'', '_out_delivery_company'=>'', '_out_delivery_num'=>'', '_out_member'=>'',
              '_in_qty'=>'', '_in_dt'=>$od_datetime_date, '_in_dt_confirm'=>'', '_in_member'=>'',
              '_modify_dt'=>date("Y-m-d")
            )
          );
          $ct_part_info = json_encode( $ct_part_info, JSON_UNESCAPED_UNICODE );

          $sql = "";
          $sql = ("
            INSERT
              purchase_cart
            SET
              `od_id` = '$od_id',
              `mb_id` = '{$_od['mb_id']}',
              `it_id` = '{$it['it_id']}',
              `it_name` = '" . addslashes($it['it_name']) . "',
              `it_sc_type` = '{$it['it_sc_type']}',
              `it_sc_method` = '{$it['it_sc_method']}',
              `it_sc_price` = '{$it['it_sc_price']}',
              `it_sc_minimum` = '{$it['it_sc_minimum']}',
              `it_sc_qty` = '{$it['it_sc_qty']}',
              `ct_status` = '발주대기',
              `ct_price` = '{$it_price}',
              `ct_point` = '$point',
              `ct_point_use` = '0',
              `ct_stock_use` = '0',
              `ct_option` = '$io_value',
              `ct_qty` = '$ct_qty',
              `ct_qty_for_rollback` = '$ct_qty',
              `ct_notax` = '{$it['it_notax']}',
              `io_id` = '$io_id',
              `io_type` = '$io_type',
              `io_price` = '$io_price',
              `ct_time` = '" . G5_TIME_YMDHIS . "',
              `ct_ip` = '$remote_addr',
              `ct_send_cost` = '$ct_send_cost',
              `ct_direct` = '$sw_direct',
              `ct_select` = '$ct_select',
              `ct_select_time` = '$ct_select_time',
              `pt_it` = '{$it['pt_it']}',
              `pt_msg1` = '$pt_msg1',
              `pt_msg2` = '$pt_msg2',
              `pt_msg3` = '$pt_msg3',
              `ct_history` = '',
              `ct_discount` = '$add_ct_discount',
              `ct_price_type` = '0',
              `ct_uid` = '$uid',
              `io_thezone` = '$io_thezone',
              `ct_admin_new` = '$ct_admin_new_v',
              `ct_delivery_cnt` = '$ct_delivery_cnt',
              `ct_delivery_price` = '$ct_delivery_price',
              `ct_delivery_company` = '$ct_delivery_company',
              `ct_is_direct_delivery` = '{$it['it_is_direct_delivery']}',
              `ct_direct_delivery_partner` = '{$it['it_direct_delivery_partner']}',
              `ct_direct_delivery_price` = '{$it['it_direct_delivery_price']}',
              `prodMemo` = '{$_POST['memo'][$key]}',
              `prodSupYn` = '{$it['prodSupYn']}',
              `ct_warehouse` = '$ct_warehouse',
              `ct_warehouse_address` = '$ct_warehouse_address',
              `ct_warehouse_phone` = '$ct_warehouse_phone',
              `ct_supply_partner` = '{$od_member['mb_id']}',
              `ct_delivery_expect_date` = NULL,
              `ct_part_info` = '{$ct_part_info}',
              `ct_modify_date` = NULL
          ");

          $_ct_insert_sql[] = $sql;

          $result = sql_query($_ct_insert_sql);
          set_purchase_order_admin_log($_od_id, '상품: ' . addslashes($_POST['it_name'][$key]) . ', ' . $_POST['io_id'][$key] . ' 상품 [발주추가]');

        }

      }

    }
  }


  // 22.11.10 : 서원 - 상품 삭제 리스트가 있을 경우 해당 ct **관리자 취소 처리**
  $_ct_delete_sql = [];
  if( is_array($ct_del_list) && count($ct_del_list) ) {
    //print_r($ct_del_list);
    foreach ($ct_del_list as $key => $val) {
      //print_r($val);
      //echo("삭제");
      $_ct = "";
      $_ct = sql_fetch("SELECT od_id, ct_id, it_name, ct_option FROM purchase_cart WHERE ct_id='{$val}'");

      if( $_ct ) {
        $_ct_delete_sql[] = ("
          UPDATE 
            purchase_cart 
          SET
            `ct_status` = '관리자발주취소',
            `is_purchase_end` = '1'
          WHERE od_id = '{$_ct['od_id']}' AND ct_id='{$_ct['ct_id']}'
        ");
      }

      $result = sql_query($_ct_delete_sql);
      set_purchase_order_admin_log($_od_id, '상품: ' . addslashes($_ct['it_name']) . ', ' . $_ct['ct_option'] . ' 상품 [발주취소]');
    }
  }
  // print_r( $_ct_delete_sql );

  // 22.11.11 : 서원 - 발주서 테이블 변경
  $order_sql = '';
  if( count($_ct_insert_sql)>0 || count($_ct_delete_sql)>0 || $od_discount_info ) {
    $order_sql = ("
      UPDATE 
        purchase_order
      SET 
        -- `od_send_yn` = '0',
        -- `od_send_mail_yn` = '0',
        -- `od_send_hp_yn` = '0',
        -- `od_send_fax_yn` = '0',
        `od_discount_info` = '{$od_discount_info}'
      WHERE
        od_id = '{$_od['od_id']}'
    ");
  }


/*
  print_r($_ct_update_sql);
  print_r($_ct_insert_sql);
  print_r($_ct_delete_sql);
  echo($order_sql);
*/
  

  // 22.11.11 : 서원 - SQL 데이터 처리부분 시작 ( 롤백 포인트 지정 )
  sql_query("savepoint sql_rollback");
  if( is_array($_ct_update_sql) && (count($_ct_update_sql)>0) ) {
    foreach ($_ct_update_sql as $key => $_sql) { sql_query($_sql); }
  }
  
  if( is_array($_ct_insert_sql) && (count($_ct_insert_sql)>0) ) {
    foreach ($_ct_insert_sql as $key => $_sql) { sql_query($_sql); }
  }
  
  if( is_array($_ct_delete_sql) && (count($_ct_delete_sql)>0) ) {
    foreach ($_ct_delete_sql as $key => $_sql) { sql_query($_sql); }
  }

  if( $order_sql ) {
    sql_query($order_sql);
  }

  set_purchase_order_admin_log($od_id, '발주서 변경 완료');
  
  // 22.11.11 : 서원 - SQL 데이터 처리 완료 ( 롤백 커밋 )
  sql_query("commit");

  json_response(200, 'OK');
} catch (Exception $e) {
  // 22.11.11 : 서원 - 데이터 처리 과정중 오류 발생에 따른 SQL 롤백 포인트 지점으로 복원. )
  sql_query("rollback to sql_rollback");
  json_response(500, $e->getMessage());
}

?>