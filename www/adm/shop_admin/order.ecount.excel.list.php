<?php

  include_once("./_common.php");

  auth_check($auth["400400"], "r");
  include_once(G5_LIB_PATH."/PHPExcel.php");
  function column_char($i) { return chr( 65 + $i ); }

  $ct_id = $od_id;
  $count_number = 0;
  $count_od_id = "";

  if(!$ct_id) {
    $ct_id = [];
    $where = [];

    $sel_field = get_search_string($sel_field);

    // wetoz : naverpayorder - , 'od_naver_orderid' 추가
    if( !in_array($sel_field, array('od_all', 'it_name', 'it_admin_memo', 'it_maker', 'od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'od_naver_orderid', 'barcode', 'prodMemo', 'od_memo')) ){   //검색할 필드 대상이 아니면 값을 제거
        $sel_field = '';
    }
    $replace_table = array(
      'od_id' => 'o.od_id',
      'it_name' => 'i.it_name',
      'mb_id' => 'c.mb_id'
    );
    $sel_field = $replace_table[$sel_field] ?: $sel_field;
    $sel_field_add = $replace_table[$sel_field_add] ?: $sel_field_add;
    
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
    
    if ($search != "") {
      $search = trim($search);
      if($sel_field=="barcode") {
        $sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search."'";
        $result_barcode_search = sql_query($sql_barcode_search);
        $or = "";
        while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
          $bacode_search .= $or." `o.stoId` like '%".$row_barcode['stoId']."%' ";
          $or = "or";
        }
        $where[] = $bacode_search;
      } else {
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
      $sel_arr = array('i.it_name', 'it_admin_memo', 'it_maker', 'o.od_id', 'c.mb_id', 'mb_nick', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'barcode', 'prodMemo', 'od_memo');
    
      foreach ($sel_arr as $key => $value) {
        if($value=="barcode") { 
          $sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search."'";
          $result_barcode_search = sql_query($sql_barcode_search);
          $or = "";
          while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
            $bacode_search .= $or." `o.stoId` like '%".$row_barcode['stoId']."%' ";
            $or = "or";
          }
          if($bacode_search) {
            $sel_arr[$key] = $bacode_search;
          } else {
            $sel_arr[$key] = "o.stoId like '%$search%'";
          }
        } else {
          $sel_arr[$key] = "$value like '%$search%'";
        }
      }
    
      $where[] = "(".implode(' or ', $sel_arr).")";
    }
    
    // 출고준비 3일경과만 보기
    if($issue_1) {
      $where[] = " ( ct_status = '출고준비' and DATE(ct_move_date) <= (CURDATE() - INTERVAL 3 DAY ) ) ";
    }
    
    // 취소/반품요청 있는 주문만 보기
    if($issue_2) {
      $where[] = " ( select count(*) from g5_shop_order_cancel_request where approved = 0 and od_id = o.od_id ) > 0 ";
    }
    
    if ( $od_sales_manager ) {
      $where_od_sales_manager = array();
      for($i=0;$i<count($od_sales_manager);$i++) {
        $where_od_sales_manager[] = " od_sales_manager = '{$od_sales_manager[$i]}'";
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
      $where[] = " ct_is_direct_delivery = '$ct_is_direct_delivery' ";
    }
    
    if(($ct_direct_delivery_partner = get_search_string($ct_direct_delivery_partner)) && $ct_is_direct_delivery !== '0') {
      $where[] = " ct_direct_delivery_partner = '$ct_direct_delivery_partner' ";
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
    //////////////////
    
    if($_POST["od_recipient"]){
      $where[] = " recipient_yn = '{$_POST["od_recipient"]}'";
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
    
    // 최고관리자가 아닐때
    if ( $ct_status == '작성' && $is_admin != 'super' ) {
      $where[] = " od_writer = '{$member['mb_id']}' ";
    }
    
    if ($click_status) {
      $where[] = " ct_status = '{$click_status}'";
    } else {
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
      } else {
        $order_steps_where = array();
        foreach($order_steps as $order_step) {
          if (!$order_step['orderlist']) continue;
    
          $order_steps_where[] = " ct_status = '{$order_step['val']}' ";
        }
        $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
      }
    }
    
    $where[] = " (m.mb_intercept_date = '' OR m.mb_intercept_date IS NULL) ";
    
    $sql_search = '';
    if ($where) {
      $sql_search = ' where '.implode(' and ', $where);
    }
    
    // shop_cart 조인으로 수정
    // member 테이블 조인
    $sql_common = "
      FROM
        {$g5['g5_shop_cart_table']} c
      LEFT JOIN
        {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
      LEFT JOIN
        {$g5['g5_shop_order_table']} o ON c.od_id = o.od_id
      LEFT JOIN
        {$g5['member_table']} m ON c.mb_id = m.mb_id
      LEFT JOIN
        partner_install_report pir ON c.od_id = pir.od_id
      LEFT JOIN
        g5_shop_order_cancel_request ocr ON c.od_id = ocr.od_id
    ";
    
    $sql_common .= $sql_search;
    
    // 정렬
    foreach($order_steps as $order_step) {
      if (!$order_step['orderlist']) continue;
      $order_by_steps[] = "'".$order_step['val']."'";
    }
    $order_by_step = implode(' , ', $order_by_steps);
    $sql_common .= " ORDER BY FIELD(ct_status, " . $order_by_step . " ), ct_move_date desc, o.od_id desc ";
    
    $sql  = "
      select *, o.od_id as od_id, c.ct_id as ct_id, c.mb_id as mb_id, (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
      $sql_common
    ";
    $result = sql_query($sql);

    while( $row = sql_fetch_array($result) ) {
      $ct_id[] = $row['ct_id'];
    }
  }

  $rows = [];
  $checked = [];
  for($ii = 0; $ii < count($ct_id); $ii++) {
    $this_ct_id = $ct_id[$ii];

    if(isset($checked[$this_ct_id])) continue;
    $checked[$this_ct_id] = true;

    $it = sql_fetch("
      SELECT cart.*, item.it_thezone2, o.io_thezone, o.io_thezone as io_thezone2, item.ca_id, it_standard, io_standard
      FROM g5_shop_cart as cart
      INNER JOIN g5_shop_item as item ON cart.it_id = item.it_id
      LEFT JOIN g5_shop_item_option o ON (cart.it_id = o.it_id and cart.io_id = o.io_id)
      WHERE cart.ct_id = '{$this_ct_id}'
      ORDER BY cart.ct_id ASC
    ");

    if($new_only && $it['ct_is_ecount_excel_downloaded']) {
      continue;
    }
    
    $od = sql_fetch(" 
      SELECT o.*, cp.cp_subject
      FROM g5_shop_order o
        LEFT JOIN g5_shop_coupon_log cp_log ON o.od_id = cp_log.od_id
        LEFT JOIN g5_shop_coupon cp ON cp_log.cp_id = cp.cp_id
      WHERE o.od_id = '".$it['od_id']."'
    ");

    if ($count_od_id !== $it['od_id']) {
        $count_number++;
        $count_od_id = $it['od_id'];
    }
    #바코드
    $stoIdDataList = explode('|',$it['stoId']);
    $stoIdDataList=array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);

    $barcode=[];
    $sendData["stoId"] = $stoIdData;
    $oCurl = curl_init();
    $res = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
    $result_again = $res;
    $result_again =$result_again['data'];
    
    if( is_array($result_again) && count($result_again) ) {
      for($k=0; $k < count($result_again); $k++) {
        if($result_again[$k]['prodBarNum']) {
          array_push($barcode,$result_again[$k]['prodBarNum']);
        }
      }
    }
    asort($barcode);
    $barcode2=[];
    $y = 0;  
    foreach($barcode as $key=>$val)  
    {  
      $new_key = $y;  
      $barcode2[$new_key] = $val;  
      $y++;  
    }
    $barcode_string="";
    if (!is_benefit_item($it)) {
      for ($y=0; $y<count($barcode2); $y++) {
          #처음
          if ($y==0) {
              $barcode_string .= $barcode2[$y];
              continue;
          }
          #현재 바코드 -1이 전바코드와 같지않음
          if (intval($barcode2[$y])-1 !== intval($barcode2[$y-1])) {
              $barcode_string .= ",".$barcode2[$y];
          }
          #현재 바코드 -1이 전바코드와 같음
          if (intval($barcode2[$y])-1 == intval($barcode2[$y-1])) {
              //다음번이 연속되지 않을 경우
              if (intval($barcode2[$y])+1 !== intval($barcode2[$y+1])) {
                  $barcode_string .= "-".$barcode2[$y];
              }
          }
      }
      $barcode_string .= " ";
    }

    
    //할인적용 단가
    if($it['io_type'])
      $opt_price = $it['io_price'];
    else
      $opt_price = $it['ct_price'] + $it['io_price'];

    if($opt_price)
      $price_d = ($opt_price*$it["ct_qty"]-$it['ct_discount'])/$it["ct_qty"];
    else
      $price_d = 0;

    //영세 과세 구분
    $sql_taxInfo = 'select `it_taxInfo` from `g5_shop_item` where `it_id` = "'.$it['it_id'].'"';
    $it_taxInfo = sql_fetch($sql_taxInfo);
    $price_d_p ="";
    $price_d_s ="";
    if($it_taxInfo['it_taxInfo']=="영세"){
      $price_d_p = $price_d*$it['ct_qty'];
      $price_d_s = "0";
    }else{
      $price_d_p = round(($price_d ? $price_d : 0) / 1.1) * $it['ct_qty']; // 공급가액
      $price_d_s = round(($price_d ? $price_d : 0) / 1.1 / 10) * $it['ct_qty']; // 부가세
    }

    $it_name = $it["it_name"];
      
    if($it_name != $it["ct_option"]){
      $it_name .= " [{$it["ct_option"]}]";
    }

    $addr="";
    if($od_b_zip1){ $addr= "(".$od_b_zip1.$od_b_zip2.")"; }
    $addr = $addr.$od["od_b_addr1"].' '.$od["od_b_addr2"].' '.$od["od_b_addr3"];

    $mb = get_member($it['mb_id']);
  
    //영업담당자
    // $od_sales_manager = get_member($od['od_sales_manager']);
    if($od['od_sales_manager'] == ""){
		$sql_manager = "SELECT `mb_manager` FROM `g5_member` WHERE `mb_id` ='".$od['mb_id']."'";
		$result_manager = sql_fetch($sql_manager);
		if (!$result_manager['mb_manager']) {
		  $result_manager['mb_manager'] = $od['od_sales_manager'];
		}
	}else{
		$result_manager['mb_manager'] = $od['od_sales_manager'];
	}
    $od_sales_manager = get_member($result_manager['mb_manager']);

    $thezone_code = $it['io_thezone2'] ?: $it['io_thezone'] ?: $it['it_thezone2'];
    $standard = $it['io_standard'] ?: $it['it_standard']; // 규격

    $delivery = '';
    //송장번호 출력
    if ($it['ct_delivery_company']) {
      $delivery = '(' . get_delivery_company_step($it['ct_delivery_company'])['name'] . ') ';
    }
    if ($it['ct_delivery_num']) {
      $delivery .= $it['ct_delivery_num'];
    }
    //합포 송장번호 출력
    if ($it['ct_combine_ct_id']) {
      $sql_ct ="select `ct_delivery_company`, `ct_delivery_num` from g5_shop_cart where `ct_id` = '".$it['ct_combine_ct_id']."'";
      $result_ct = sql_fetch($sql_ct);
      $delivery = '';
      if($result_ct['ct_delivery_company'])
        $delivery = '(' . get_delivery_company_step($result_ct['ct_delivery_company'])['name'] . ') ';
      $delivery .= $result_ct['ct_delivery_num'];
    }
    $date = "출고전";
    if($it["ct_ex_date"] && $it["ct_ex_date"] !== "0000-00-00") {
      $date =date("Ymd", strtotime($it["ct_ex_date"]));
    }

    $abstract = '통합관리플랫폼'; // 적요
    if($it['prodMemo']) {
      $abstract .= " ({$it['prodMemo']})";
    }

    $rows[] = [
      'od_id' => $od['od_id'],
      'value' => [
        $date,  //날짜
        $count_number,
        $mb['mb_thezone'],
        '',
        $od_sales_manager['mb_name'],
        $it['ct_warehouse'], // 출하창고
        '',
        '',
        '',
        $od["od_b_name"],
        $addr,
        '',
        '',
        ' '.$it['prodMemo'],
        '',
        '10000', //부서
        $thezone_code, // 품목코드
        '',
        $standard,
        $it["ct_qty"],
        $price_d ?: '0', // 단가(판매가)
        '',
        $price_d_p ?: '0', //공급가액
        $price_d_s ?: '0', //부가세
        $barcode_string, // 바코드
        $delivery, // 로젠송장번호,
        $abstract, //적요
        '',
      ]
    ];

    $ct = sql_fetch("select *
                          from g5_shop_cart
                          where od_id = {$it['od_id']}
                          order by ct_id asc ");

    if ($it['ct_id'] == $ct['ct_id']) {
      // 배송비
      if ($od['od_send_cost'] > 0) {
        $rows[] = [
          'od_id' => $od['od_id'],
          'value' => [
            $date,  //날짜
            $count_number,
            $mb['mb_thezone'],
            '',
            $od_sales_manager['mb_name'],
            $it['ct_warehouse'], // 출하창고
            '',
            '',
            '',
            $od["od_b_name"],
            $addr,
            '',
            '',
            ' '.$it['prodMemo'],
            '',
            '10000', //부서
            '00043', // 품목코드(배송비)
            '',
            '',
            '1',
            $od['od_send_cost'], // 단가(vat포함)
            '',
            round($od['od_send_cost'] / 1.1), //공급가액
            ($od['od_send_cost'] - round($od['od_send_cost'] / 1.1)), //부가세
            '', // 바코드
            $delivery, // 로젠송장번호,
            $abstract, //적요
            '',
          ]
        ];
      }

      // 추가배송비
      if ($od['od_send_cost2'] > 0) {
        $rows[] = [
          'od_id' => $od['od_id'],
          'value' => [
            $date,  //날짜
            $count_number,
            $mb['mb_thezone'],
            '',
            $od_sales_manager['mb_name'],
            $it['ct_warehouse'], // 출하창고
            '',
            '',
            '',
            $od["od_b_name"],
            $addr,
            '',
            '',
            ' '.$it['prodMemo'],
            '',
            '10000', //부서
            '00043', // 품목코드(배송비)
            '',
            '추가배송비',
            '1',
            $od['od_send_cost2'], // 단가(vat포함)
            '',
            round($od['od_send_cost2'] / 1.1), //공급가액
            ($od['od_send_cost2'] - round($od['od_send_cost2'] / 1.1)), //부가세
            '', // 바코드
            $delivery, // 로젠송장번호,
            $abstract, //적요
            '',
          ]
        ];
      }

      // 매출할인
      if ($od['od_sales_discount'] > 0) {
        $rows[] = [
          'od_id' => $od['od_id'],
          'value' => [
            $date,  //날짜
            $count_number,
            $mb['mb_thezone'],
            '',
            $od_sales_manager['mb_name'],
            '',
            '',
            '',
            '',
            $od["od_b_name"],
            $addr,
            '',
            '',
            ' '.$it['prodMemo'],
            '',
            '10000', //부서
            '03245', // 품목코드(매출할인)
            '',
            '',
            '1',
            -($od['od_sales_discount']), // 단가(vat포함)
            '',
            -(round($od['od_sales_discount'] / 1.1)), //공급가액
            -($od['od_sales_discount'] - round($od['od_sales_discount'] / 1.1)), //부가세
            '', // 바코드
            '', // 로젠송장번호,
            $abstract, //적요
            '',
          ]
        ];
      }
      
      // 쿠폰할인
      $coupon_price = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];
      if ($coupon_price > 0) {

        // 22.12.19 : 서원 - 주문내역 이카운트 엑셀다운로드 쿠폰정보에 쿠폰이름 매핑입력요청
        // 창고명 하드코딩 요청.
        $it['ct_warehouse'] = "검단창고";

        // 22.12.19 : 서원 - 주문내역 이카운트 엑셀다운로드 쿠폰정보에 쿠폰이름 매핑입력요청
        if( $od['cp_subject'] ) {
          // 쿠폰명칭 추가
          $abstract .= " ({$od['cp_subject']})";
          //쿠폰이 존재할 경우 쿠폰명으로 대체
          $standard = $od['cp_subject'];
        }

        $rows[] = [
          'od_id' => $od['od_id'],
          'value' => [
            $date,  //날짜
            $count_number,
            $mb['mb_thezone'],
            '',
            $od_sales_manager['mb_name'],
            $it['ct_warehouse'], // 출하창고
            '',
            '',
            '',
            $od["od_b_name"],
            $addr,
            '',
            '',
            ' '.$it['prodMemo'],
            '',
            '10000', //부서
            '04378', // 품목코드(쿠폰할인)
            '',
            $standard,
            '1',
            -($coupon_price), // 단가(vat포함)
            '',
            -(round($coupon_price / 1.1)), //공급가액
            -($coupon_price - round($coupon_price / 1.1)), //부가세
            '', // 바코드
            '', // 로젠송장번호,
            $abstract, //적요
            '',
          ]
        ];
      }

      // 포인트결제
      if ($od['od_receipt_point'] > 0) {
        $rows[] = [
          'od_id' => $od['od_id'],
          'value' => [
            $date,  //날짜
            $count_number,
            $mb['mb_thezone'],
            '',
            $od_sales_manager['mb_name'],
            '',
            '',
            '',
            '',
            $od["od_b_name"],
            $addr,
            '',
            '',
            ' '.$it['prodMemo'],
            '',
            '10000', //부서
            '12345', // 품목코드(포인트결제)
            '',
            '',
            '1',
            -($od['od_receipt_point']), // 단가(vat포함)
            '',
            -(round($od['od_receipt_point'] / 1.1)), //공급가액
            -($od['od_receipt_point'] - round($od['od_receipt_point'] / 1.1)), //부가세
            '', // 바코드
            '', // 로젠송장번호,
            $abstract, //적요
            '',
          ]
        ];
      }
    }

    // 이카운트 엑셀 다운로드 표시
    sql_query("
      update g5_shop_cart
      set ct_is_ecount_excel_downloaded = 1
      where ct_id = '{$this_ct_id}'
    ");
    set_order_admin_log($od['od_id'], '이카운트 엑셀 다운로드 : ' . $it_name);
  }
  $headers = array(
    "일자",
    "순서",
    "거래처코드",
    "거래처명",
    "담당자",
    "출하창고",
    "거래유형",
    "통화",
    "환율",
    "성명(상호명)",
    "배송처",
    "전잔액",
    "후잔액",
    "특이사항",
    "참고사항",
    "부서",
    "품목코드",
    "품목명",
    "규격",
    "수량",
    "단가(vat포함)",
    "외화금액",
    "공급가액",
    "부가세",
    "바코드",
    "로젠 송장번호",
    "적요",
    "생산전표생성
  ");

  usort($rows, function($item1, $item2) {
    return $item1['od_id'] <=> $item2['od_id'];
  });

  $i = 0;
  $last_od_id;
  $rows = array_map(function($row) {
    global $i, $last_od_id;

    if($last_od_id != $row['od_id']) {
      $i++;
      $last_od_id = $row['od_id'];
    }

    $value = $row['value'];

    $value[1] = $i;

    return $value;
  }, $rows);
  /*
  $rows = array_map(function($row) {
    return $row['value'];
  }, $rows);
  */

  $data = array_merge(array($headers), $rows);
  
  $widths  = array(20, 50, 10, 30, 50, 30, 50);
  $header_bgcolor = 'FFABCDEF';
  $last_char = column_char(count($headers) - 1);

  $excel = new PHPExcel();
  // $excel->setActiveSheetIndex(0)
  //     ->getStyle( "A1:${last_char}1" )
  //     ->getFill()
  //     ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
  //     ->getStartColor()
  //     ->setARGB($header_bgcolor);

  // $excel->setActiveSheetIndex(0)
  //     ->getStyle( "A:$last_char" )
  //     ->getAlignment()
  //     ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
  //     ->setWrapText(true);

  foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
  $excel->getActiveSheet()->fromArray($data,NULL,'A1');

  header("Content-Type: application/octet-stream");
  header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
  header("Cache-Control: max-age=0");
  header('Set-Cookie: fileDownload=true; path=/');

  $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
  $writer->save('php://output');

?>
