<?php

  include_once("./_common.php");

  auth_check($auth["400400"], "r");
  include_once(G5_LIB_PATH."/PHPExcel.php");
  function column_char($i) { return chr( 65 + $i ); }

  $ct_id = $od_id;

  if(!$ct_id) {
    $ct_id = [];
    $where = [];

    $sel_field = get_search_string($sel_field);
    
    // wetoz : naverpayorder - , 'od_naver_orderid' 추가
    if( !in_array($sel_field, array('od_all', 'it_name', 'it_admin_memo', 'it_maker', 'od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'od_naver_orderid','barcode')) ){   //검색할 필드 대상이 아니면 값을 제거
        $sel_field = '';
    }
    $ct_status = $od_status;
    $ct_status = get_search_string($ct_status);

    $search = get_search_string($search);
    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

    $od_misu = preg_replace('/[^0-9a-z]/i', '', $od_misu);
    $od_cancel_price = preg_replace('/[^0-9a-z]/i', '', $od_cancel_price);
    $od_refund_price = preg_replace('/[^0-9a-z]/i', '', $od_refund_price);
    $od_receipt_point = preg_replace('/[^0-9a-z]/i', '', $od_receipt_point);
    $od_coupon = preg_replace('/[^0-9a-z]/i', '', $od_coupon);
    
    $sql_search = "";
    if ($search != "") {
      $search = trim($search);
      if($sel_field=="barcode"){
        $sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search."'";
        $result_barcode_search = sql_query($sql_barcode_search);
        $or="";
        while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
          $bacode_search .= $or." `stoId` like '%".$row_barcode['stoId']."%' ";
          $or="or";
        }
        $where[] = $bacode_search;
      }else{
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
      $sel_arr = array('it_name', 'it_admin_memo', 'it_maker', 'od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num','barcode');
  
      foreach ($sel_arr as $key => $value) {
        if($value=="barcode"){
          $sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search."'";
          $result_barcode_search = sql_query($sql_barcode_search);
          $or="";
          while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
            $bacode_search .= $or." `stoId` like '%".$row_barcode['stoId']."%' ";
            $or="or";
          }
          if($bacode_search){
            $sel_arr[$key] = $bacode_search;
          }else{
            $sel_arr[$key] = "stoId like '%$search%'";
          }
        }else{
          $sel_arr[$key] = "$value like '%$search%'";
        }
      }
  
      $where[] = "(".implode(' or ', $sel_arr).")";
    }
    
    if ( $od_sales_manager ) {
      $where_od_sales_manager = array();
      for($i=0;$i<count($od_sales_manager);$i++) {
        $where_od_sales_manager[] = " mb_manager = '{$od_sales_manager[$i]}'";
      }
      if ( count($where_od_sales_manager) ) {
        $where[] = " ( " . implode(' OR ', $where_od_sales_manager) . " ) ";
      }
    }
    
    if ( $od_release_manager ) {
      $where_od_release_manager = array();
      for($i=0;$i<count($od_release_manager);$i++) {
        if ($od_release_manager[$i] == 'yet_release') {
          $od_release_manager[$i] = '';
        }
        $where_od_release_manager[] = " od_release_manager = '{$od_release_manager[$i]}'";
      }
      if ( count($where_od_release_manager) ) {
        $where[] = " ( " . implode(' OR ', $where_od_release_manager) . " ) ";
      }
    }

    if ($partner_issue) {
      $where_partner_issue = array();
      for($i=0;$i<count($partner_issue);$i++) {
        $where_partner_issue[] = " pir.ir_is_issue_{$partner_issue[$i]} = TRUE ";
      }
      if ( count($where_partner_issue) ) {
        $where[] = " ( " . implode(' OR ', $where_partner_issue) . " ) ";
      }
    }
    
    if ( $od_pay_state && is_array($od_pay_state) ) {
      foreach($od_pay_state as $s) {
        $s = (int)$s;
        $od_pay_state_where[] = " od_pay_state = '{$s}'";
      }
      $where[] = ' ( '.implode(' OR ', $od_pay_state_where).' ) ';
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
      if($ct_is_direct_delivery == '1')
        $where[] = " (ct_is_direct_delivery = '1' or ct_is_direct_delivery = '2') ";
      else
        $where[] = " ct_is_direct_delivery = '$ct_is_direct_delivery' ";
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
    
    if ( $price ) {
      $where[] = " (od_cart_price + od_send_cost + od_send_cost2 - od_cart_discount) BETWEEN '{$price_s}' AND '{$price_e}' ";
    }
    
    if ($od_settle_case) {
      if ( is_array($od_settle_case) ) {
        $od_settle_case_where = array();
        foreach($od_settle_case as $s) {
          $od_settle_case_where[] = " od_settle_case = '{$s}'";
        }
        $where[] = ' ( '.implode(' OR ', $od_settle_case_where).' ) ';
      } else {
        $where[] = " od_settle_case = '{$od_settle_case}'";
      }
    }
    
    if ($od_openmarket) {
      if ( is_array($od_openmarket) ) {
        $od_openmarket_where = array();
        foreach($od_openmarket as $s) {
          if($s=="my"){
            $od_openmarket_where[] = " od_writer != 'openmarket'";
          }else{
            $od_openmarket_where[] = " sabang_market = '{$s}'";
          }
        }
        $where[] = ' ( '.implode(' OR ', $od_openmarket_where).' ) ';
      } else {
        if($od_openmarket=="my"){
          $where[] = " od_writer != 'openmarket'";
        }else{
          $where[] = " sabang_market = '{$od_openmarket}'";
        }
      }
    }

    //// 등급 검색 ////
    if ($member_level_s) {
      if ( is_array($member_level_s) ) {
        $member_level_s_where = array();
        foreach($member_level_s as $s) {
          $member_level_s_where[] = " mb_level = '{$s}'";
        }
        $temp_where[] = ' ( '.implode(' OR ', $member_level_s_where).' ) ';
      } else {
        $temp_where[] = " ( mb_level = '{$member_level_s}' )";
      }
    }

    if ($member_type_s) {
      if ( is_array($member_type_s) ) {
        $member_type_s_where = array();
        foreach($member_type_s as $s) {
          $member_type_s_where[] = " mb_type = '{$s}'";
        }
        $temp_where[] = ' ( '.implode(' OR ', $member_type_s_where).' ) ';
      } else {
        $temp_where[] = " ( mb_type = '{$member_type_s}' )";
      }
    }

    if ($is_member_s) {
      if ( is_array($is_member_s) ) {
        $is_member_s_where = array();
        foreach($is_member_s as $s) {
          $is_member_s_where[] = " mb_level is {$s}";
        }
        $temp_where[] = ' ( '.implode(' OR ', $is_member_s_where).' ) ';
      } else {
        $temp_where[] = " mb_level is {$is_member_s}";
      }
    }

    if ($temp_where) {
      foreach($temp_where as $s) {
        $where[] = ' ( '.implode(' OR ', $temp_where).' ) ';
      }
    }

    if($od_recipient){
      $where[] = " recipient_yn = '{$od_recipient}'";
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
    
    if ($click_status) {
      $where[] = " ct_status = '{$click_status}'";
    }else{
      if ( $ct_status ) {
        if ( is_array($ct_status) ) {
          $order_steps_where = array();
          foreach($ct_status as $s) {
            $order_steps_where[] = " ct_status = '{$s}'";
          }
          $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
        }else{
          $where[] = " ct_status = '{$ct_status}'";
        }
      }else{
        $order_steps_where = array();
        foreach($order_steps as $order_step) {
          if (!$order_step['orderlist']) continue;

          $order_steps_where[] = " ct_status = '{$order_step['val']}' ";
        }
        $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
      }
    }
    
    // 최고관리자가 아닐때
    if ( $ct_status == '작성' && $is_admin != 'super' ) {
      $where[] = " od_writer = '{$member['mb_id']}' ";
    }

    $where[] = " (C.mb_intercept_date = '' OR C.mb_intercept_date IS NULL) ";
    
    if ($where) {
      $sql_search = ' where '.implode(' and ', $where);
    }

    // shop_cart 조인으로 수정
    // member 테이블 조인
    $sql_common = " from (select ct_id as cart_ct_id, od_id as cart_od_id, X.it_name, ct_delivery_num, it_admin_memo, it_maker, ct_status ,ct_move_date, ct_ex_date, ct_is_direct_delivery from {$g5['g5_shop_cart_table']} X left join {$g5['g5_shop_item_table']} I on I.it_id = X.it_id ) B
                    inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
                    left join (select mb_id as mb_id_temp, mb_level, mb_manager, mb_type, mb_intercept_date from {$g5['member_table']}) C
                    on A.mb_id = C.mb_id_temp
                    LEFT JOIN partner_install_report pir ON B.cart_ct_id = pir.ct_id
                    $sql_search
                    group by cart_ct_id ";

    foreach($order_steps as $order_step) {
      if (!$order_step['orderlist']) continue;
      $order_by_steps[] = "'".$order_step['val']."'";
    }

    $order_by_step = implode(' , ', $order_by_steps);

    $sql_common .= " ORDER BY FIELD(B.ct_status, " . $order_by_step . " ), B.ct_move_date desc, od_id desc ";

    $sql  = " select *,
                (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
               $sql_common ";
    $result = sql_query($sql);

    while( $row = sql_fetch_array($result) ) {
      $ct_id[] = $row['cart_ct_id'];
    }
  }

  $rows = [];
  for($ii = 0; $ii < count($ct_id); $ii++) {

    $it = sql_fetch("
      SELECT cart.*, item.it_thezone2
      FROM g5_shop_cart as cart
      INNER JOIN g5_shop_item as item ON cart.it_id = item.it_id
      WHERE cart.ct_id = '{$ct_id[$ii]}'
      ORDER BY cart.ct_id ASC
    ");

    $od = sql_fetch(" 
      SELECT * FROM g5_shop_order WHERE od_id = '".$it['od_id']."'
    ");

      
    //영업담당자
    $sql_manager = "SELECT `mb_manager`,`mb_entNm` FROM `g5_member` WHERE `mb_id` ='".$od['mb_id']."'";
    $result_manager = sql_fetch($sql_manager);

    $sql_manager = "SELECT `mb_name` FROM `g5_member` WHERE `mb_id` ='".$result_manager['mb_manager']."'";
    $result_manager = sql_fetch($sql_manager);
    $sale_manager=$result_manager['mb_name'];

    $it_name = $it["it_name"];
    
    if($it_name != $it["ct_option"]){
      $it_name .= " [{$it["ct_option"]}]";
    }
    $addr="";
    if($od_b_zip1){$addr= "(".$od_b_zip1.$od_b_zip2.")";}
    $addr = $addr.$od["od_b_addr1"].' '.$od["od_b_addr2"].' '.$od["od_b_addr3"];
    $ct_delivery_company = $it['ct_delivery_company'];
		foreach($delivery_companys as $companyInfo) {
			if($companyInfo["val"] == $ct_delivery_company){
				$ct_delivery_company = $companyInfo["name"];
			}
		}
    $rows[] = [ 
      ' '.$it['od_id'],
      date("Y-m-d", strtotime($od["od_time"]))."-".($i),
      $it_name,
      $it["ct_qty"],
              $it_name." / ".$it["ct_qty"].' EA',
      $od["od_b_name"],
              $sale_manager,
      $addr,
      $od["od_b_tel"],
      $od["od_b_hp"],
      $it["prodMemo"],
      $od["od_memo"],
      $it['ct_id'],
      $ct_delivery_company,
      $it['ct_delivery_num']
    ];
  }

  $headers = array("주문번호", "일자-No.", "품목명[규격]", "수량", "품목&수량","성함(상호명)", "영업담당자", "배송처", "연락처","휴대폰", "적요", "배송지요청사항", "카트ID", "택배사", "송장번호");
  $data = array_merge(array($headers), $rows);
    
  $widths  = array(20, 20, 50, 10, 30, 50, 30, 50, 20, 20, 10, 20, 10, 15, 20);
  $header_bgcolor = 'FFABCDEF';
  $last_char = column_char(count($headers) - 1);

  $excel = new PHPExcel();
  $excel->setActiveSheetIndex(0)
    ->getStyle( "A1:${last_char}1" )
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB($header_bgcolor);

  $excel->setActiveSheetIndex(0)
    ->getStyle( "A:$last_char" )
    ->getAlignment()
    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
    ->setWrapText(true);

  foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
  $excel->getActiveSheet()->fromArray($data,NULL,'A1');

  header("Content-Type: application/octet-stream");
  header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
  header("Cache-Control: max-age=0");
  header('Set-Cookie: fileDownload=true; path=/');

  $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
  $writer->save('php://output');
?>
