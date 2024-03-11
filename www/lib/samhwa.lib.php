<?php

$pay_types = array(
    '0' => array(
        'name' => '미결제',
        'fullname' => '미결제',
        'val' => '0',
        'color' => '#c72102',
        'next' => '1',
    ),
    '1' => array(
        'name' => '결제',
        'fullname' => '결제완료',
        'val' => '1',
        'color' => '#236ec6',
        'next' => '2',
    ),
    '2' => array(
        'name' => '결제 후 출고',
        'fullname' => '결제 후 출고',
        'val' => '2',
        'color' => '#c72102',
        'next' => '0',
    ),
);

function get_pay_step($val) {

    global $pay_types;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($pay_types); $i++) {
        if ( $val == $pay_types[$i]['val'] ) {
            $k = $i;
        }
    }

    if ( $k > -1 ) {
        return $pay_types[$k];
    }else{
        return false;
    }
}

function samhwa_price($it, $thema_key='default') {
    global $member;

    $it_price = 0;

    if ( $thema_key == 'partner' || $member['mb_type'] == 'partner' ) {
        $it_price = $it['it_price_partner'];
    }
    //if ( $thema_key != 'partner' || !$it_price ) {
    if ( !$it_price ) {
        $it_price = $it['it_price'];
    }

    if ( $member['mb_level'] == '3' && $it['it_price_dealer'] ) {
        $it_price = $it['it_price_dealer'];
    }

    if ( $member['mb_level'] == '4' && $it['it_price_dealer2'] ) {
        $it_price = $it['it_price_dealer2'];
    }

    return $it_price;
}

function samhwa_opt_price($opt, $thema_key='default') {
    global $member;

    $io_price = 0;

    if ( $thema_key == 'partner' ) {
        $io_price = $opt['io_price_partner'];
    }
    
    if ( $thema_key != 'partner' || !$io_price ) {
        $io_price = $opt['io_price'];
    }

    if ( $member['mb_level'] == '3' && $opt['io_price_dealer'] ) {
        $io_price = $opt['io_price_dealer'];
    }

    if ( $member['mb_level'] == '4' && $opt['io_price_dealer2'] ) {
        $io_price = $opt['io_price_dealer2'];
    }

    return $io_price;
}

function show_samhwa_price($it, $thema_key='default', $vat=0) {
    global $member;

    $ret = '';

    $it_price = samhwa_price($it, $thema_key);

    $ret = '<b>' . number_format($it_price) . '원</b>';
    
    if ($vat) {
        $ret .= '<span>(VAT 포함)</span>';
    }

    if ( !$member['mb_id'] && $thema_key == 'partner' ) {
        $ret = '<span class="no_login_price">로그인 후 가격 공개</span>';
    }

    return $ret;
}

function show_samhwa_it_tags($it) {
    global $g5;
    if ( !$it['pt_tag'] ) return '';

    $tags = explode(',', $it['pt_tag']);
    // print_r($tags);

    $ret = '';
    foreach($tags as $tag) {
        $str .= '<a href="' .G5_BBS_URL .'/tag.php?q='. $tag .'">#'. $tag .'</a>';
    }

    return $str;
}

function partner_daegi() {
    global $member, $is_admin;

    $partner_daegi = false;
    if ( $member['mb_type'] == 'partner' ) {
        $partner_daegi = true;
        if ( $member['mb_partner_auth'] == 1 ) {
            if ( strtotime(G5_TIME_YMDHIS) <= strtotime($member['mb_partner_date'] . " 23:59:59") ) {
                $partner_daegi = false;
            } else {
                /* 자동연장 삭제 (2022-04-20)
                // 자동연장인지 확인하기
                if ( $member['mb_partner_date_auto'] == 1 ) {
                    $sql = "SELECT count(*) as cnt, SUM(od_cart_price + od_send_cost) as price FROM g5_shop_order WHERE mb_id = '{$member['mb_id']}'";
                    $member_auto = sql_fetch($sql);

                    if ( $member['mb_partner_date_auto_buy_price'] <= $member_auto['price'] && $member['mb_partner_date_auto_buy_cnt'] <= $member_auto['cnt'] ) {
                        if ( $member['mb_partner_date_auto_extend_date'] > 0 ) {
                            $sql = "UPDATE g5_member SET mb_partner_date = DATE_ADD( mb_partner_date, INTERVAL ". $member['mb_partner_date_auto_extend_date'] ." month ) WHERE mb_id = '{$member['mb_id']}'";
                            sql_query($sql);
                            $partner_daegi = false;
                        }
                    }
                }
                */
                $partner_daegi = true;
            }
        }
    }
    
    if ( $is_admin ) {
        $partner_daegi = false;
    }
    if ( !$member['mb_id'] ) {
        $partner_daegi = false;
    }
    if ( $member['mb_type'] == 'default' ) {
        $partner_daegi = false;
    }

    return $partner_daegi;
}

$order_steps = array(
    '0' => array(
        'name' => '주문접수',
        'val' => '주문',
        'next' => 1,
        // 'prev' => 10, // 작성
        'orderlist' => true,
        'step' => 5,
        'chulgo' => '출고전',
        'cart' => true,
        'orderlist_complete' => false,
        'cart_editable' => true,
        'cart_deletable' => true,
        'cancelable' => true,
		"statusN" => "06",
		"statusY" => "01",
    ),
    '1' => array(
        'name' => '입금완료',
        'val' => '입금',
        'next' => 2,
        'prev' => 0,
        'orderlist' => true,
        'step' => 10,
        'chulgo' => '출고전',
        'cart' => true,
        'orderlist_complete' => false,
        'cart_editable' => true,
        'cart_deletable' => true,
        'cancelable' => true,
		"statusN" => "06",
		"statusY" => "01",
    ),
    '2' => array(
        'name' => '상품준비',
        'val' => '준비',
        'next' => 3,
        'prev' => 1,
        'orderlist' => true,
        'step' => 15,
        'chulgo' => '출고전',
        'cart' => true,
        'orderlist_complete' => false,
        'cart_editable' => true,
        'cart_deletable' => true,
        'cancelable' => true,
		"statusN" => "06",
		"statusY" => "01",
    ),
    '3' => array(
        'name' => '출고준비',
        'val' => '출고준비',
        'next' => 4,
        'prev' => 2,
        'orderlist' => true,
        'step' => 20,
        'chulgo' => '출고전',
        'cart' => true,
        'orderlist_complete' => false,
        'deliverylist' => true,
        'cart_editable' => true,
        'cart_deletable' => true,
        'direct_cancel' => true,
		"statusN" => "06",
		"statusY" => "01",
    ),
    '4' => array(
        'name' => '출고완료',
        'val' => '배송',
        'next' => 5,
        'prev' => 3,
        'orderlist' => true,
        'step' => 25,
        'chulgo' => '출고후',
        'cart' => true,
        'orderlist_complete' => false,
        'deliverylist' => true,
        'cart_editable' => false,
        'cart_deletable' => false,
		"statusN" => "06",
		"statusY" => "01",
    ),
    '5' => array(
        'name' => '배송완료',
        'val' => '완료',
        'prev' => 3,
        'orderlist' => true,
        'step' => 30,
        'chulgo' => '출고후',
        'cart' => true,
        'orderlist_complete' => true,
        'deliverylist' => true,
        'cart_editable' => false,
        'cart_deletable' => false,
		"statusN" => "01",
		"statusY" => "03",
    ),
    '6' => array(
        'name' => '주문취소',
        'val' => '취소',
        'orderlist' => true,
        'step' => 70,
//        'next' => 0,
        'chulgo' => '',
        'cart' => false,
        'orderlist_complete' => false,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '16' => array(
        'name' => '주문무효',
        'val' => '주문무효',
        'orderlist' => true,
        'step' => 35,
        'next' => 0,
        'chulgo' => '',
        'cart' => false,
        'orderlist_complete' => false,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '7' => array(
        'name' => '부분취소',
        'val' => '부분취소',
        'orderlist' => false,
        'step' => 40,
        'chulgo' => '',
        'cart' => false,
        'orderlist_complete' => false,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '8' => array(
        'name' => '반품',
        'val' => '반품',
        'orderlist' => false,
        'step' => 45,
        'chulgo' => '',
        'cart' => false,
        'orderlist_complete' => false,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '9' => array(
        'name' => '품절',
        'val' => '품절',
        'orderlist' => false,
        'step' => 50,
        'chulgo' => '',
        'cart' => false,
        'orderlist_complete' => false,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '10' => array(
        'name' => '작성',
        'val' => '작성',
        'next' => 0,
        'orderlist' => true,
        'step' => 55,
        'chulgo' => '',
        'cart' => true,
        'orderlist_complete' => false,
        'cart_deletable' => true,
        'cart_editable' => true,
		"statusN" => "06",
		"statusY" => "01",
    ),
    '11' => array(
        'name' => '입고대기',
        'val' => '입고대기',
        'next' => 12,
        'orderlist' => false,
        'step' => 60,
        'chulgo' => '',
        'cart' => true,
        'orderlist_complete' => false,
        'cancellist' => true,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '12' => array(
        'name' => '입고확인',
        'val' => '입고확인',
        'prev' => 11,
        'next' => 13,
        'orderlist' => false,
        'step' => 65,
        'chulgo' => '',
        'cart' => true,
        'orderlist_complete' => false,
        'cancellist' => true,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '13' => array(
        'name' => '검수확인',
        'val' => '검수확인',
        'prev' => 12,
        'next' => 14,
        'orderlist' => false,
        'step' => 70,
        'chulgo' => '',
        'cart' => true,
        'orderlist_complete' => false,
        'cancellist' => true,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
    ),
    '14' => array(
        'name' => '재고소진',
        'val' => '재고소진',
        'prev' => 13,
        'orderlist' => true,
        'step' => 70,
        'chulgo' => '출고후',
        'cart' => true,
        'orderlist_complete' => false,
        'cancellist' => true,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
        "statusN" => "06",
		"statusY" => "01",
    ),
    '15' => array(
        'name' => '보유재고등록',
        'val' => '보유재고등록',
        'next' => 1,
        'prev' => 10, // 작성
        'orderlist' => true,
        'step' => 5,
        'chulgo' => '출고후',
        'cart' => true,
        'orderlist_complete' => false,
		"statusN" => "06",
		"statusY" => "01",
        'cart_editable' => false,
        'cart_deletable' => false,
        "statusN" => "06",
		"statusY" => "01",
    )
);

$purchase_order_steps = array(
  '0' => array(
    'name' => '발주대기',
    'val' => '발주대기',
    'next' => 1,
    'orderlist' => true,
    'step' => 5,
    'chulgo' => '출고전',
    'cart' => true,
    'orderlist_complete' => false,
    'cart_editable' => true,
    'cart_deletable' => true,
    'cancelable' => true,
    "statusN" => "06",
    "statusY" => "01",
  ),
  '1' => array(
    'name' => '발주완료',
    'val' => '발주완료',
    'next' => 2,
    'prev' => 0,
    'orderlist' => true,
    'step' => 10,
    'chulgo' => '출고전',
    'cart' => true,
    'orderlist_complete' => false,
    'cart_editable' => true,
    'cart_deletable' => true,
    'cancelable' => true,
    "statusN" => "06",
    "statusY" => "01",
  ),
  '2' => array(
    'name' => '출고완료',
    'val' => '출고완료',
    'next' => 3,
    'prev' => 1,
    'orderlist' => true,
    'step' => 15,
    'chulgo' => '출고후',
    'cart' => true,
    'orderlist_complete' => false,
    'cart_editable' => true,
    'cart_deletable' => true,
    'cancelable' => true,
    "statusN" => "06",
    "statusY" => "01",
  ),
  '3' => array(
    'name' => '입고완료',
    'val' => '입고완료',
    'prev' => 2,
    'orderlist' => true,
    'step' => 20,
    'chulgo' => '출고후',
    'cart' => true,
    'orderlist_complete' => false,
    'deliverylist' => true,
    'cart_editable' => true,
    'cart_deletable' => true,
    'direct_cancel' => true,
    "statusN" => "06",
    "statusY" => "01",
  ),
  '4' => array(
    'name' => '마감완료',
    'val' => '마감완료',
    'orderlist' => true,
    'step' => 35,
    'cart' => true,
    'orderlist_complete' => false,
    'deliverylist' => true,
    'cart_editable' => true,
    'cart_deletable' => true,
    'direct_cancel' => true,
    "statusN" => "06",
    "statusY" => "01",
  ),
  '5' => array(
    'name' => '발주취소',
    'val' => '발주취소',
    'orderlist' => true,
    'step' => 70,
    'chulgo' => '',
    'cart' => false,
    'orderlist_complete' => false,
    "statusN" => "06",
    "statusY" => "01",
    'cart_editable' => false,
    'cart_deletable' => false,
  ),
);

$cancel_request_types = array(
        '0' => array(
                'name' => '주문취소요청',
                'val' => 'cancel',
        ),
        '1' => array(
                'name' => '주문반품요청',
                'val' => 'return',
        ),
);

function get_typereceipt_step($od_id) {
    global $typereceipt_types;
    
    $sql = "SELECT * FROM g5_shop_order_typereceipt WHERE od_id = '{$od_id}'";
    $result = sql_fetch($sql);
    if ( !$result ) return $typereceipt_types[1];

    $type = $result['ot_typereceipt'];

    $ret = array();

    $k = -1;

    for($i=0;$i<count($typereceipt_types); $i++) {
        if ( $type == $typereceipt_types[$i]['val'] ) {
            $k = $i;
        }
    }
    $ret_type = $typereceipt_types[$k];

    if ( $ret_type['cuse'] ) {
        $ret_type['cuse'] = $ret_type['cuse'][$result['ot_typereceipt_cuse']];
    }

    if ( $k > -1 ) {
        return array_merge($result, $ret_type);
    }else{
        return array_merge($result, $typereceipt_types[0]);
    }
}


function get_typereceipt_cate($od_id) {
    global $typereceipt_cates;
    
    $sql = "SELECT * FROM g5_shop_order_typereceipt WHERE od_id = '{$od_id}'";
    $result = sql_fetch($sql);
    if ( !$result ) return $typereceipt_cates[1];

    $type = $result['ot_typereceipt_cate'];

    $ret = array();

    $k = -1;

    for($i=0;$i<count($typereceipt_cates); $i++) {
        if ( $type == $typereceipt_cates[$i]['val'] ) {
            $k = $i;
        }
    }
    $ret_type = $typereceipt_cates[$k];

    if ( $k > -1 ) {
        return array_merge($result, $ret_type);
    }else{
        return array_merge($result, $typereceipt_cates[0]);
    }
}

function get_delivery_step($type) {

    global $delivery_types;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($delivery_types); $i++) {
        if ( $type == $delivery_types[$i]['val'] ) {
            $k = $i;
        }
    }

    if ( $k > -1 ) {
        return $delivery_types[$k];
    }else{
        return false;
    }
}

function get_delivery_company_step($type) {

    global $delivery_companys;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($delivery_companys); $i++) {
        if ( $type == $delivery_companys[$i]['val'] ) {
            $k = $i;
        }
    }

    if ( $k > -1 ) {
        return $delivery_companys[$k];
    }else{
        return false;
    }
}

function get_step($od_status, $partner = '') {

    global $order_steps;

    $ret = array();

    $k = -1;

    if($partner == 'partner'){
        for ($i = 0; $i < count($order_steps); $i++) {
            if ($od_status == $order_steps[$i]['name']) {
                $k = $i;
            }
        }
    } else {
        for ($i = 0; $i < count($order_steps); $i++) {
            if ($od_status == $order_steps[$i]['val']) {
                $k = $i;
            }
        }
    }

    if ( $k > -1 ) {
        return $order_steps[$k];
    }else{
        return false;
    }
}

function get_purchase_step($od_status) {

  global $purchase_order_steps;

  $ret = array();

  $k = -1;

  if($od_status == '관리자발주취소' || $od_status == '취소') $od_status = '발주취소';

  for($i=0;$i<count($purchase_order_steps); $i++) {
    if ( $od_status == $purchase_order_steps[$i]['val'] ) {
      $k = $i;
    }
  }

  if ( $k > -1 ) {
    return $purchase_order_steps[$k];
  }else{
    return false;
  }
}

function get_canel_request($reqeust_type) {
    global $cancel_request_types;
    
    $ret = array();
    
    $k = -1;
    
    for ($i = 0; $i < count($cancel_request_types); $i++) {
        if ($reqeust_type == $cancel_request_types[$i]['val']) {
            $k = $i;
        }
    }
    
    if ($k > -1) {
        return $cancel_request_types[$k];
    } else {
        return false;
    }
}

// 다음 스탭 정보 가져오기
function get_next_step($od_status) {
    global $order_steps;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($order_steps); $i++) {
        if ( $od_status == $order_steps[$i]['val'] ) {
            $k = $i;
        }
    }

    if ( $k > -1 && $order_steps[$k]['next'] !== NULL ) {
        return $order_steps[$order_steps[$k]['next']];
    }else{
        return false;
    }
}

// 이전 스탭 정보 가져오기
function get_prev_step($od_status) {
    global $order_steps;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($order_steps); $i++) {
        if ( $od_status == $order_steps[$i]['val'] ) {
            $k = $i;
        }
    }

    if ( $k > -1 && $order_steps[$k]['prev'] !== NULL ) {
        return $order_steps[$order_steps[$k]['prev']];
    }else{
        return false;
    }
}

// 다음 스탭 정보 가져오기
function get_next_purchase_step($od_status) {
  global $purchase_order_steps;

  $ret = array();

  $k = -1;

  for($i=0;$i<count($purchase_order_steps); $i++) {
    if ( $od_status == $purchase_order_steps[$i]['val'] ) {
      $k = $i;
    }
  }

  if ( $k > -1 && $purchase_order_steps[$k]['next'] !== NULL ) {
    return $purchase_order_steps[$purchase_order_steps[$k]['next']];
  }else{
    return false;
  }
}

// 이전 스탭 정보 가져오기
function get_prev_purchase_step($od_status) {
  global $purchase_order_steps;

  $ret = array();

  $k = -1;

  for($i=0;$i<count($purchase_order_steps); $i++) {
    if ( $od_status == $purchase_order_steps[$i]['val'] ) {
      $k = $i;
    }
  }

  if ( $k > -1 && $purchase_order_steps[$k]['prev'] !== NULL ) {
    return $purchase_order_steps[$purchase_order_steps[$k]['prev']];
  }else{
    return false;
  }
}

function get_order_admin_log($od_id) {

    $sql = "SELECT * FROM g5_shop_order_admin_log WHERE od_id = '{$od_id}' ORDER BY ol_no DESC";
    $result = sql_query($sql);

    $ret = array();
    while($row = sql_fetch_array($result)) {
        $ret[] = $row;
    }

    return $ret;
}

function get_purchase_order_admin_log($od_id, $ct_id = null) {
  $sql = "SELECT * FROM purchase_order_admin_log WHERE od_id = '{$od_id}' AND (ct_id IS NULL OR ct_id = '') ORDER BY ol_no DESC";

  if ($ct_id) {
    $sql = "SELECT * FROM purchase_order_admin_log WHERE od_id = '{$od_id}' AND ct_id = '{$ct_id}' ORDER BY ol_no DESC";
  }

  if ($ct_id == 'not_null') {
    $sql = "SELECT * FROM purchase_order_admin_log WHERE od_id = '{$od_id}' AND (ct_id IS NOT NULL OR ct_id != '') ORDER BY ol_no DESC";
  }

  $result = sql_query($sql);

  $ret = array();
  while($row = sql_fetch_array($result)) {
    $ret[] = $row;
  }

  return $ret;
}

function get_barcode_log($od_id) {

    $sql = "SELECT * FROM g5_barcode_log WHERE od_id = '{$od_id}' ORDER BY b_date DESC, stoId ASC;";
    $result = sql_query($sql);

    $ret = array();
    while($row = sql_fetch_array($result)) {
        $ret[] = $row;
    }

    return $ret;
}

function get_delivery_log($od_id) {

    $sql = "SELECT * FROM g5_delivery_log WHERE od_id = '{$od_id}' ORDER BY d_date DESC;";
    $result = sql_query($sql);

    $ret = array();
    while($row = sql_fetch_array($result)) {
        $ret[] = $row;
    }

    return $ret;
}

function get_delivery_log_re($od_id) { // 아예 같은 내용인

    $sql = "SELECT * FROM g5_delivery_log WHERE od_id = '{$od_id}' 
    group by ct_id, d_content, ct_combine_ct_id, ct_delivery_company, ct_delivery_num, 
    ct_edi_result, ct_is_direct_delivery, ct_direct_delivery_partner, was_combined, was_direct_delivery, set_warehouse ORDER BY d_date DESC;";
    $result = sql_query($sql);

    $ret = array();
    while($row = sql_fetch_array($result)) {
        $ret[] = $row;
    }

    return $ret;
}

function set_order_admin_log($od_id, $content) {
    global $member;
    
    $mb_id = $member['mb_id'];

    $manager_mb_id = get_session('ss_manager_mb_id');
    if($manager_mb_id) {
        $mb_id = $manager_mb_id;
    }

    $sql = "INSERT INTO g5_shop_order_admin_log SET
                od_id = '{$od_id}',
                mb_id = '{$mb_id}',
                ol_content = '{$content}',
                ol_datetime = now()
                ";

    return sql_query($sql);
}

function set_purchase_order_admin_log($od_id, $content, $ct_id = null, $type = null) {
  global $member;

  $mb_id = $member['mb_id'];

  $manager_mb_id = get_session('ss_manager_mb_id');
  if($manager_mb_id) {
    $mb_id = $manager_mb_id;
  }

  $set_ct_id = '';
  if ($ct_id != null) {
    $set_ct_id = "ct_id = '{$ct_id}',";
  }

  if( $type != null ) {
    $set_type = "ol_type = '{$type}',";
  }

  $sql = "INSERT INTO purchase_order_admin_log SET
                od_id = '{$od_id}',
                {$set_ct_id}
                mb_id = '{$mb_id}',
                ol_content = '{$content}',
                {$set_type}
                ol_datetime = now()
                ";

  return sql_query($sql);
}

function set_send_ledger_log($mb_id, $send_type, $receiver = "") {

    $sql = "INSERT INTO g5_send_ledger_history SET
                mb_id = '{$mb_id}',
                send_type = '{$send_type}',
                receiver = '{$receiver}',
                send_date = now()
                ";

    return sql_query($sql);
}

$typereceipt_types = array(
    '0' => array(
        'name' => '발급안함',
        'val' => '',
    ),
    '1' => array(
        'name' => '세금계산서',
        'val' => '11',
    ),
    '2' => array(
        'name' => '현금영수증',
        'val' => '31',
        'cuse' => array(
            '0' => array(
                'name' => '없음',
                'val' => '',
            ),
            '1' => array(
                'name' => '개인소득공제',
                'val' => '1',
            ),
            '2' => array(
                'name' => '사업자지출증빙',
                'val' => '2',
            )
        )
    ),
);

$typereceipt_cates = array(
    '0' => array(
        'name' => '없음',
        'val' => '',
    ),
    '1' => array(
        'name' => '세금계산서',
        'val' => '11',
    ),
    '2' => array(
        'name' => '현금',
        'val' => '31',
    ),
    '3' => array(
        'name' => '일괄',
        'val' => '25',
    ),
    '4' => array(
        'name' => '카드',
        'val' => '17',
    ),
    '5' => array(
        'name' => '수출',
        'val' => '16',
    ),
    '6' => array(
        'name' => '포인트',
        'val' => '14',
    ),
    '7' => array(
        'name' => '오픈마켓',
        'val' => '26',
    ),
    '8' => array(
        'name' => '기타',
        'val' => '99',
    ),
);

$delivery_types = array(
    '0' => array(
        'name' => '택배(선불)',
        'val' => 'delivery1',
        'type' => 'delivery',
        'user-order' => 'true',
        'required' => 'true',
    ),
    '1' => array(
        'name' => '택배(착불)',
        'val' => 'delivery2',
        'type' => 'delivery',
        'user-order' => 'true',
    ),
    '2' => array(
        'name' => '퀵서비스(선불)',
        'val' => 'quick1',
        'type' => 'quick',
        'print_page_name' => 'damas',
    ),
    '3' => array(
        'name' => '퀵서비스(착불)',
        'val' => 'quick2',
        'type' => 'quick',
        'user-order' => 'true',
        'print_page_name' => 'damas',
    ),
    '4' => array(
        'name' => '매장수령',
        'val' => 'store',
        'type' => 'store',
        'user-order' => 'true',
    ),
    '5' => array(
        'name' => '오토바이퀵(선불)',
        'val' => 'autobike1',
        'type' => 'autobike',
        'print_page_name' => 'damas',
    ),
    '6' => array(
        'name' => '오토바이퀵(착불)',
        'val' => 'autobike2',
        'type' => 'autobike',
        'print_page_name' => 'damas',
    ),
    '7' => array(
        'name' => '다마스퀵(선불)',
        'val' => 'damas1',
        'type' => 'damas',
        'freight' => '선불',
        'print_page_name' => 'damas',
    ),
    '8' => array(
        'name' => '다마스퀵(착불)',
        'val' => 'damas2',
        'type' => 'damas',
        'freight' => '착불',
        'print_page_name' => 'damas',
    ),
    '9' => array(
        'name' => '화물택배(선불)',
        'val' => 'huamul1',
        'type' => 'huamul',
        'freight' => '선불',
        'print_page_name' => 'huamul',
    ),
    '10' => array(
        'name' => '화물택배(착불)',
        'val' => 'huamul2',
        'type' => 'huamul',
        'freight' => '착불',
        'print_page_name' => 'huamul',
        'user-order' => 'true',
    ),
    '11' => array(
        'name' => '경동화물 영업소(선불)',
        'val' => 'gdhuamul1',
        'type' => 'gdhuamul',
        'freight' => '선불',
        'print_page_name' => 'huamul',
    ),
    '12' => array(
        'name' => '경동화물 영업소(착불)',
        'val' => 'gdhuamul2',
        'type' => 'gdhuamul',
        'freight' => '착불',
        'print_page_name' => 'huamul',
    ),
    '13' => array(
        'name' => '전국화물(선불)',
        'val' => 'nationwidehuamul1',
        'type' => 'nationwidehuamul',
        'freight' => '선불',
        'print_page_name' => 'huamul',
    ),
    '14' => array(
        'name' => '전국화물(착불)',
        'val' => 'nationwidehuamul2',
        'type' => 'nationwidehuamul',
        'freight' => '착불',
        'print_page_name' => 'huamul',
        'user-order' => 'true',
    ),
    '15' => array(
        'name' => '고속버스(선불)',
        'val' => 'bus1',
        'type' => 'bus',
    ),
    '16' => array(
        'name' => '고속버스(착불)',
        'val' => 'bus2',
        'type' => 'bus',
    ),
);

$delivery_companys = array(
  array(
    'name' => '로젠택배',
    'val' => 'ilogen'
  ),
  array(
    'name' => '롯데택배',
    'val' => 'lotteglogis'
  ),
  array(
    'name' => '한진택배',
    'val' => 'hanjin'
  ),
  array(
    'name' => '건영택배',
    'val' => 'kunyoung'
  ),
  array(
    'name' => '대한통운',
    'val' => 'cjlogistics'
  ),
  array(
    'name' => '경동택배',
    'val' => 'kdexp'
  ),
  array(
    'name' => '일양택배',
    'val' => 'ilyanglogis'
  ),
  array(
    'name' => '대신택배',
    'val' => 'ds3211'
  ),
  array(
    'name' => '합동택배',
    'val' => 'hdexp'
  ),
  array(
    'name' => '천일택배',
    'val' => 'chunilps'
  ),
  array(
    'name' => '우체국택배',
    'val' => 'epost'
  ),
  array(
    'name' => '우리택배(구 호남택배)',
    'val' => 'honam'
  ),
  array(
    'name' => '화물배송',
    'val' => 'hwamul'
  ),
  array(
    'name' => '퀵배송',
    'val' => 'quick'
  ),
  array(
    'name' => '기타배송',
    'val' => 'etc'
  ),
  array(
    'name' => '설치배송',
    'val' => 'install'
  )
);

function get_warehouses() {
  $sql = " select wh_name from warehouse where wh_use_yn = 'Y' order by wh_id asc ";
  $result = sql_query($sql);

  $list = [];
  $list[] = '미지정';
  while($row = sql_fetch_array($result)) {
    $list[] = $row['wh_name'];
  }

  return $list;
}

$refund_types = array(
    '0' => array(
        'name' => '계좌입금',
        'val' => '0'
    ),
    '1' => array(
        'name' => '카드취소',
        'val' => '1'
    ),
    '2' => array(
        'name' => '기타',
        'val' => '2'
    ),
);

function get_refund_type($type) {

    global $refund_types;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($refund_types); $i++) {
        if ( $type == $refund_types[$i]['val'] ) {
            $k = $i;
        }
    }

    if ( $k > -1 ) {
        return $refund_types[$k];
    }else{
        return false;
    }
}

// 상품 선택옵션
function samhwa_get_item_options($it_id, $subject, $is_div='', $sb = '')
{
    global $g5, $aslang;

    if(!$it_id || !$subject)
        return '';

    $sb = $sb ? $sb : $aslang['io_select'];

    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '0' and it_id = '$it_id' and io_use = '1' order by io_no asc ";
    $result = sql_query($sql);
    if(!sql_num_rows($result))
        return '';

    $str = '';
    $subj = explode(',', $subject);
    $subj_count = count($subj);

    if($subj_count > 1) {
        $options = array();

        // 옵션항목 배열에 저장
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $opt_id = explode(chr(30), $row['io_id']);

            for($k=0; $k<$subj_count; $k++) {
                if(!is_array($options[$k]))
                    $options[$k] = array();

                if($opt_id[$k] && !in_array($opt_id[$k], $options[$k]))
                    $options[$k][] = $opt_id[$k];
            }
        }

        // 옵션선택목록 만들기
        for($i=0; $i<$subj_count; $i++) {
            $opt = $options[$i];
            $opt_count = count($opt);
            $disabled = '';
            if($opt_count) {
                $seq = $i + 1;
                if($i > 0)
                    $disabled = ' disabled="disabled"';

                if($is_div === 'div') {
                    $str .= '<div class="get_item_options">'.PHP_EOL;
                    $str .= '<label for="it_option_'.$seq.'">'.$subj[$i].'</label>'.PHP_EOL;
                } else {
                    $str .= '<tr>'.PHP_EOL;
                    $str .= '<th><label for="it_option_'.$seq.'">'.$subj[$i].'</label></th>'.PHP_EOL;
                }

                $select = '<select id="it_option_'.$seq.'" class="it_option"'.$disabled.'>'.PHP_EOL;
                $select .= '<option value="">'.$subj[$i].' 선택하세요.</option>'.PHP_EOL;
                for($k=0; $k<$opt_count; $k++) {
                    $opt_val = $opt[$k];
                    if(strlen($opt_val)) {
                        $select .= '<option value="'.$opt_val.'">'.$opt_val.'</option>'.PHP_EOL;
                    }
                }
                $select .= '</select>'.PHP_EOL;

                if($is_div === 'div') {
                    $str .= '<span>'.$select.'</span>'.PHP_EOL;
                    $str .= '</div>'.PHP_EOL;
                } else {
                    $str .= '<td>'.$select.'<div class="select-option-img"></div></td>'.PHP_EOL;
                    $str .= '</tr>'.PHP_EOL;
                }
            }
        }
    } else {
        if($is_div === 'div') {
            $str .= '<div class="get_item_options">'.PHP_EOL;
            $str .= '<label for="it_option_1">'.$subj[0].' 선택하세요</label>'.PHP_EOL;
        } else {
            $str .= '<tr>'.PHP_EOL;
            $str .= '<th><label for="it_option_1">'.$subj[0].'</label></th>'.PHP_EOL;
        }

        $select = '<select id="it_option_1" class="it_option">'.PHP_EOL;
        $select .= '<option value="">'.$subj[$i].' 선택하세요.</option>'.PHP_EOL;
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($row['io_price'] >= 0)
                $price = '&nbsp;&nbsp;+ '.astxt($aslang['io_price'], array(number_format($row['io_price']))); //원
            else
                $price = '&nbsp;&nbsp; '.astxt($aslang['io_price'], array(number_format($row['io_price']))); //원

            if($row['io_stock_qty'] < 1)
                $soldout = '&nbsp;&nbsp;'.$aslang['io_soldout']; //[품절]
            else
                $soldout = '';

			if($row['io_sold_out'] == 1)
				$soldout2 = '&nbsp;&nbsp;[일시품절]'; //[일시품절]
			else
				$soldout2 = '';

            $select .= '<option value="'.$row['io_id'].','.$row['io_price'].','.$row['io_stock_qty'].','.$row['io_price_partner'].','.$row['io_price_dealer'].'">'.$row['io_id'].$price.$soldout.$soldout2.'</option>'.PHP_EOL;
        }
        $select .= '</select>'.PHP_EOL;

        if($is_div === 'div') {
            $str .= '<span>'.$select.'</span>'.PHP_EOL;
            $str .= '</div>'.PHP_EOL;
        } else {
            $str .= '<td>'.$select.'<div class="select-option-img"></div></td>'.PHP_EOL;
            $str .= '</tr>'.PHP_EOL;
        }
	}

    return $str;
}


// 상품 추가옵션
function samhwa_get_item_supply($it_id, $subject, $is_div='', $sb = '')
{
    global $g5, $aslang;

    if(!$it_id || !$subject)
        return '';

    $sb = $sb ? $sb : $aslang['io_select'];

    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '1' and it_id = '$it_id' and io_use = '1' order by io_no asc ";
    $result = sql_query($sql);
    if(!sql_num_rows($result))
        return '';

    $str = '';

    $subj = explode(',', $subject);
    $subj_count = count($subj);
    $options = array();

    // 옵션항목 배열에 저장
    for($i=0; $row=sql_fetch_array($result); $i++) {
        $opt_id = explode(chr(30), $row['io_id']);

        if($opt_id[0] && !array_key_exists($opt_id[0], $options))
            $options[$opt_id[0]] = array();

        if(strlen($opt_id[1])) {
            if($row['io_price'] >= 0)
                $price = '&nbsp;&nbsp;+ '.astxt($aslang['io_price'], array(number_format($row['io_price']))); //원
            else
                $price = '&nbsp;&nbsp; '.astxt($aslang['io_price'], array(number_format($row['io_price']))); //원
            $io_stock_qty = get_option_stock_qty($it_id, $row['io_id'], $row['io_type']);

            if($io_stock_qty < 1)
                $soldout = '&nbsp;&nbsp;'.$aslang['io_soldout']; //품절
            else
                $soldout = '';

			if($row['io_sold_out'] == 1)
				$soldout2 = '&nbsp;&nbsp;[일시품절]'; //[일시품절]
			else
				$soldout2 = '';

            $options[$opt_id[0]][] = '<option value="'.$opt_id[1].','.$row['io_price'].','.$io_stock_qty.','.$row['io_price_partner'].','.$row['io_price_dealer'].'">'.$opt_id[1].$price.$soldout.$soldout2.'</option>';
        }
    }

    // 옵션항목 만들기
    for($i=0; $i<$subj_count; $i++) {
        $opt = $options[$subj[$i]];
        $opt_count = $opt ? count($opt) : 0;
        if($opt_count) {
            $seq = $i + 1;
            if($is_div === 'div') {
                $str .= '<div class="get_item_supply">'.PHP_EOL;
                $str .= '<label for="it_supply_'.$seq.'">'.$subj[$i].'</label>'.PHP_EOL;
            } else {
                $str .= '<tr>'.PHP_EOL;
                $str .= '<th><label for="it_supply_'.$seq.'">'.$subj[$i].'</label></th>'.PHP_EOL;
            }

            $select = '<select id="it_supply_'.$seq.'" class="it_supply">'.PHP_EOL;
            $select .= '<option value="">'.$subj[$i].'</option>'.PHP_EOL;
            for($k=0; $k<$opt_count; $k++) {
                $opt_val = $opt[$k];
                if($opt_val) {
                    $select .= $opt_val.PHP_EOL;
                }
            }
            $select .= '</select>'.PHP_EOL;

            if($is_div === 'div') {
                $str .= '<span class="td_sit_sel">'.$select.'<div class="select-supply-img"></div></span>'.PHP_EOL;
                $str .= '</div>'.PHP_EOL;
            } else {
                $str .= '<td class="td_sit_sel">'.$select.'<div class="select-supply-img"></div></td>'.PHP_EOL;
                $str .= '</tr>'.PHP_EOL;
            }
		}
    }

    return $str;
}

// cart데이터를 기반으로 order테이블 계산 다시하기
function samhwa_order_calc($od_id) {

    global $g5;

    // 주문페이지 계산하기
    $sql = "SELECT SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as od_price,
                    sum(ct_discount) as ct_discount
                FROM {$g5['g5_shop_cart_table']} WHERE od_id = '{$od_id}'";
    $result = sql_fetch($sql);

    $sendcost = get_sendcost_new($od_id, 1); // 배송비 계산

    $sql = "UPDATE {$g5['g5_shop_order_table']} SET 
                od_cart_price = '{$result['od_price']}', 
                od_cart_discount = '{$result['ct_discount']}',
                od_misu = {$result['od_price']} + od_send_cost + od_send_cost2 - {$result['ct_discount']} - od_cancel_price - od_cart_discount2 - od_sales_discount,
                od_send_cost = '{$sendcost}'
            WHERE od_id = '{$od_id}'";
    sql_query($sql);

}

// 금액 한글로 변환
function samhwa_price_to_hangul($price) {
    $trans_kor=array("","일","이","삼","사","오","육","칠","팔","구"); 
    $price_unit=array("","십","백","천","만","십","백","천","억","십","백","천","조","십","백","천"); 
    $valuecode=array("","만","억","조"); 
    $value=strlen($price); 
    $k=0; 

    for($i=$value;$i>0;$i--){ 
        $vv=""; 
        $vc=substr($price,$k,1); 
        $vt=$trans_kor[$vc]; 
        $k++; 

        if($i%5 ==0){ 
            $vv=$valuecode[$i/5];
        }else{ 
            if($vc){ 
                $vv=$price_unit[$i-1];
            } 
        }
        $vr = $vr . $vt . $vv; 
    }

    return $vr;
}

function shorturl($url) {
    global $default, $config;
    $client_id = $config['cf_naver_clientid'];
    $client_secret = $config['cf_naver_secret'];
    $encText = urlencode($url);
    $postvars = "url=".$encText;
    $url = "https://openapi.naver.com/v1/util/shorturl";
    $is_post = true;
    //$url = "https://openapi.naver.com/v1/util/shorturl?url=" + $encText ;
    //$is_post = false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $postvars);
    $headers = array();
    $headers[] = "X-Naver-Client-Id: ".$client_id;
    $headers[] = "X-Naver-Client-Secret: ".$client_secret;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //echo "status_code:".$status_code."<br>";
    curl_close ($ch);
    if($status_code == 200) {
        //echo $response;
        $data = json_decode($response);
        //print_r2($data);
        return $data->result->url;
    } else {
        //echo "Error 내용:".$response;
        return;
    }
}

function samhwa_get_misu($mb_id) {

    $ptmb = get_member($mb_id);

    // 미수금액
    // $sql = "SELECT * FROM `g5_shop_order` WHERE mb_id = '{$mb_id}' and od_misu > 0";
    // $misu_result = sql_query($sql);
    // $misus = array();
    // $misu_price = 0;
    // $misu_gang_price = 0;
    // while($misu_row = sql_fetch_array($misu_result)) {
    //     $misus[] = $misu_row;
    //     $misu_price += $misu_row['od_misu'];
    // }

    $sql = "select sum(od_cart_price) as od_cart_price, sum(od_send_cost) as od_send_cost, sum(od_send_cost2) as od_send_cost2, sum(od_cart_discount) as od_cart_discount
    from g5_shop_order
    where mb_id = '{$mb_id}' and od_pay_state = '0'";
    $total_result = sql_fetch($sql);

    // 강력미수금액
    if ( $ptmb['mb_partner_date_pay_date'] > 0 ) {
        $sql = "SELECT sum(od_misu) as gang_misu FROM `g5_shop_order` WHERE mb_id = '{$mb_id}' and od_misu > 0 and od_time <= date_add(now(), interval -{$ptmb['mb_partner_date_pay_date']} month)";
        $misu_gang_result = sql_fetch($sql);
    }

    return array(
        'misu' => ($total_result['od_cart_price'] + $total_result['od_send_cost'] + $total_result['od_send_cost2'] - $total_result['od_cart_discount'] - $total_result['od_cart_discount2'] - $total_result['od_sales_discount']),
        'misu_gang' => $misu_gang_result['gang_misu'],
    );

}

function samhwa_get_mb_shorten_info_by_mb($mb) {
  if ( !$mb ) {
    $ret = '<span class="mb_shorten_info no_login">비</span>';
    return $ret;
  }

  $ret = '';

  if ($mb['mb_type'] == 'partner') { // 파트너
    $ret .= '<span class="mb_shorten_info partner">파</span>';
  }
  if ($mb['mb_level'] == 3) { // 딜러
    $ret .= '<span class="mb_shorten_info dealer dealer_1">사</span>';
  }
  if ($mb['mb_level'] == 4) { // 우수딜러
    $ret .= '<span class="mb_shorten_info dealer dealer_2">우</span>';
  }
  if ($mb['mb_giup_type'] > 0) { // 기업
    $ret .= '<span class="mb_shorten_info giup">기</span>';
  }
  /*
  if ($mb['mb_giup_type'] == 1) { // 구매목적
    $ret .= '<span class="mb_shorten_info giup giup_buy">구</span>';
  }
  if ($mb['mb_giup_type'] == 1) { // 납품/판매목적
    $ret .= '<span class="mb_shorten_info giup giup_sell">납</span>';
  }
  */

  return $ret;
}

function samhwa_get_mb_shorten_info($mb_id) {
  $mb = get_member($mb_id);
  return samhwa_get_mb_shorten_info_by_mb($mb);
}

// XP Level Icon
function samhwa_xp_icon() {
	global $g5, $xp, $member, $is_admin;

	if ($member['mb_id']) {
		$mb_level_name = $member['mb_level'];
		$mb_level = $member['mb_level'];

		if ( $member['mb_type'] == 'partner') {
			$mb_level_name = '파트너';
			$mb_level = 'partner';
		}

		if ( $member['mb_level'] == 3) {
			$mb_level_name = '사업소';
			$mb_level = 'dealer1';
		}
		if ( $member['mb_level'] == 4) {
			$mb_level_name = '우수사업소';
			$mb_level = 'dealer2';
		}

		$xp_icon = '<span class="lv-icon lv-'.$mb_level.'">'.$mb_level_name.'</span>';
	}else{
		$xp_icon = '<span class="lv-icon lv-1">1</span>';
	}
	if ($is_admin) {
		$xp_icon = '<span class="lv-icon lv-admin">'.$xp['xp_icon_admin'].'</span>';
	}

    return $xp_icon;
}

function curl_exec_instagram_api($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);

    $json = curl_exec($curl);
    curl_close($curl);

    return json_decode($json);
}

// 캐시
function samhwa_cache($c_name, $seconds=300, $c_code) {

    global $g5;

    $result = sql_fetch(" select c_name, c_text, c_datetime from {$g5['apms_cache']} where c_name = '$c_name' ", false);
    if (!$result) {
        // 시간을 offset 해서 입력 (-1을 해줘야 처음 call에 캐쉬를 만듭니다)
        $new_time = date("Y-m-d H:i:s", G5_SERVER_TIME - $seconds - 1);
        $result['c_datetime'] = $new_time;
        sql_query(" insert into {$g5['apms_cache']} set c_name='$c_name', c_datetime='$new_time' ", false);
	}
	

    $sec_diff = G5_SERVER_TIME - strtotime($result['c_datetime']);
    if ($sec_diff > $seconds) {

        // $c_code () 안에 내용만 살림
        $pattern = "/[()]/";
        $tmp_c_code = preg_split($pattern, $c_code);

        // 수행할 함수의 이름
        $func_name = $tmp_c_code[0];

        // 수행할 함수의 인자
        $tmp_array = explode(",", $tmp_c_code[1]);

        if ($func_name == "include_once" || $func_name == "include") {

            ob_start();
            @include($tmp_array[0]);
            $c_text = ob_get_contents();
            ob_end_clean();

        } else {

            // 수행할 함수의 인자를 담아둘 변수
            $func_args = array();

            for($i=0;$i < count($tmp_array); $i++) {
                // 기본 trim은 여백 등을 없앤다. $charlist = " \t\n\r\0\x0B"
                $tmp_args = $tmp_array[$i];
                // urldecode
                $tmp_args = trim(urldecode($tmp_args));
                // 추가 trim으로 인자를 넘길 때 쓰는 '를 없앤다
                $tmp_args = trim($tmp_args, "'");
                // 추가 trim으로 인자를 넘길 때 쓰는 "를 없앤다
                $func_args[$i] = trim($tmp_args, '"');
            }
            // 새로운 캐쉬값을 만들고
            $c_text = call_user_func_array($func_name, $func_args);
        }

		if ( is_object($c_text) ) { // object 이면 object to array
			$c_text = json_decode(json_encode($c_text), True);
		}
		
		if ( is_array($c_text) ) {
			$c_text = json_encode($c_text);
		}

		// 값이 없으면 그냥 return
        if (trim($c_text) == "")
			return;

		// db에 넣기전에 slashes들을 앞에 싹 붙여 주시고
		$c_text1 = addslashes($c_text);

        // 새로운 캐쉬값을 업데이트 하고
		sql_query(" update {$g5['apms_cache']} set c_text = '$c_text1', c_datetime='".G5_TIME_YMDHIS."' where c_name = '$c_name' ", false);

        // 새로운 캐쉬값을 return (slashes가 없는거를 return 해야합니다)
        return $c_text;

    } else {

        // 캐쉬한 데이터를 그대로 return
        return $result['c_text'];

    }
}

function get_samhwa_content($co_id = '', $type='co_content') {

    global $g5;

    if (!$co_id) return '';
    if ($type != 'co_content' && $type != 'co_mobile_content') return '';

    $result = sql_fetch(" select {$type} from `g5_content` where co_id = '{$co_id}' ", false);

    return $result[$type];

}

$albank_bank_codes = array(
    '0' => array(
        'name' => '없음',
        'val' => '',
    ),
    '1' => array(
        'name' => '국민은행',
        'val' => '4',
    ),
    '2' => array(
        'name' => '신한은행',
        'val' => '88',
    ),
    '3' => array(
        'name' => '기업은행',
        'val' => '3',
    ),
    '4' => array(
        'name' => '우리은행',
        'val' => '20',
    ),
    '5' => array(
        'name' => '농협',
        'val' => '11',
    ),
    '6' => array(
        'name' => '스탠다드차타드',
        'val' => '23',
    ),
    '7' => array(
        'name' => '하나은행',
        'val' => '81',
    ),
);

function get_albank_bank_step($val) {

    global $albank_bank_codes;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($albank_bank_codes); $i++) {
        if ( $val == $albank_bank_codes[$i]['val'] ) {
            $k = $i;
        }
    }

    if ( $k > -1 ) {
        return $albank_bank_codes[$k];
    }else{
        return false;
    }
}


// 회원권한을 SELECT 형식으로 얻음
function samhwa_get_member_level_select($name, $start_id=0, $end_id=10, $selected="", $event="", $all = false)
{
    global $g5, $is_admin;

	//최고관리자면 무조건 10 까지
	if($is_admin == 'super') {
		$end_id = 10;
	}

    $str = "\n<select id=\"{$name}\" name=\"{$name}\"";
    if ($event) $str .= " $event";
    $str .= ">\n";
    if ($all) {
        $str .= '<option value=""';

        if (!$selected)
        $str .= ' selected="selected"';

        $str .= ">전체</option>\n";
    }
    for ($i=$start_id; $i<=$end_id; $i++) {
        $str .= '<option value="'.$i.'"';
        if ($i == $selected)
            $str .= ' selected="selected"';
        $text = '';
        if ($i == 3) {
            $text = ' (딜러)';
        }
        if ($i == 4) {
            $text = ' (우수딜러)';
        }
        $str .= ">{$i}{$text}</option>\n";
    }
    $str .= "</select>\n";
    return $str;
}

$receipt_bank_codes = array(
    '0' => array(
        'name' => '국민카드',
        'val' => '99600',
    ),
    '1' => array(
        'name' => '하나카드(외환)',
        'val' => '99601',
    ),
    '2' => array(
        'name' => '엘지카드',
        'val' => '99602',
    ),
    '3' => array(
        'name' => '현대카드',
        'val' => '99603',
    ),
    '4' => array(
        'name' => '롯데카드',
        'val' => '99604',
    ),
    '5' => array(
        'name' => '신한카드',
        'val' => '99605',
    ),
    '6' => array(
        'name' => '비씨카드',
        'val' => '99606',
    ),
    '7' => array(
        'name' => '비자카드',
        'val' => '99607',
    ),
    '8' => array(
        'name' => '삼성카드',
        'val' => '99608',
    ),
    '9' => array(
        'name' => 'PG카드결제 (내 사이트)',
        'val' => '99609',
    ),
    '10' => array(
        'name' => '농협카드',
        'val' => '99611',
    ),
    '11' => array(
        'name' => '스마트스토어카드',
        'val' => '99612',
    ),
    '12' => array(
        'name' => '네이버페이카드',
        'val' => '99616',
    ),
    '13' => array(
        'name' => '인터파크카드',
        'val' => '99617',
    ),
);

function get_receipt_bank_name_by_value($val) {
    global $receipt_bank_codes;
    
    $ret = array();
    
    $k = -1;
    
    for ($i = 0; $i < count($receipt_bank_codes); $i++) {
        if ($val == $receipt_bank_codes[$i]['val']) {
            $k = $i;
        }
    }
    
    if ($k > -1) {
        return $receipt_bank_codes[$k]['name'];
    } else {
        return false;
    }
}


// 50101 이상의 유니크 키를 발급
function get_uniqid_member() {
    $sql = 'select max(uq_id) as max_key from g5_uniqid_member';
    $row = sql_fetch($sql);
    
    $key = $row['max_key'];
    $min_key = '50101';
    
    if (!$key || $key < $min_key) {
        $key = $min_key;
    }
    
    sql_query(" LOCK TABLE g5_uniqid_member WRITE ");
    while (1) {
        $key = (int)$key + 1;
        $result = sql_query(" insert into g5_uniqid_member set uq_id = '$key', uq_ip = '{$_SERVER['REMOTE_ADDR']}' ", false);
        if ($result) break;
        usleep(100000);
    }
    sql_query(" UNLOCK TABLES ");

    return $key;
}



// 영문 N + 숫자 10자리 SO_NB 발급
function get_uniqid_so_nb() {
    $sql = 'select max(uq_id) as max_key from g5_uniqid_so_nb';
    $row = sql_fetch($sql);
    
    $key = $row['max_key'];
    
    if (!$key) {
        $key = 'N0000000001';
        sql_query("insert into g5_uniqid_so_nb set uq_id = '$key', uq_ip = '{$_SERVER['REMOTE_ADDR']}' ", false);
    } else {
        sql_query(" LOCK TABLE g5_uniqid_so_nb WRITE ");
        while (1) {
            $key = (int)only_num($key) + 1;
            $key = str_pad($key, 10, "0", STR_PAD_LEFT);
            $key = "N".$key;
            $result = sql_query(" insert into g5_uniqid_so_nb set uq_id = '$key', uq_ip = '{$_SERVER['REMOTE_ADDR']}' ", false);
            if ($result) break;
            usleep(100000);
        }
        sql_query(" UNLOCK TABLES ");
    }
    
    return $key;
}

function only_num($c) {
    return preg_replace('/\D/', '', $c);
}

function uuidv4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function is_dealer() {
    global $member;

    if ($member['mb_level'] == 3 || $member['mb_level'] == 4) {
        return true;
    }
    return false;
}

function utf8ize( $mixed ) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

function show_delivery_info($order) {
    $od_status = get_step($order['od_status']);

    $od_delivery = get_delivery_step($order['od_delivery_type']);

    // 배송정보
    $delivery_info = $od_delivery['name'];
    if ( $od_delivery['type'] == 'delivery' ) {
        $delivery_info .= " / 송장번호: {$order['od_delivery_text']}";
    }
    if ( $od_delivery['type'] == 'quick' ) {
        $delivery_info .= " / 연락처: {$order['od_delivery_text']}";
    }
    if ( $od_delivery['type'] == 'store' ) {
        $delivery_info .= " / 메모: {$order['od_delivery_text']}";
    }
    if ( $od_delivery['type'] == 'autobike' ) {
        $delivery_info .= " / 연락처: {$order['od_delivery_tel']}";
    }
    if ( $od_delivery['type'] == 'damas' ) {
        $delivery_info .= " / 연락처: {$order['od_delivery_tel']}";
    }
    if ( $od_delivery['type'] == 'huamul' ) {
        $delivery_info .= " / box: {$order['od_delivery_qty']}";
    }
    if ( $od_delivery['type'] == 'gdhuamul' ) {
        $delivery_info .= " / 영업소: {$order['od_delivery_place']}";
        $delivery_info .= " / box: {$order['od_delivery_qty']}";
    }
    if ( $od_delivery['type'] == 'nationwidehuamul' ) {
        $delivery_info .= " / 송장번호: {$order['od_delivery_text']}";
        $delivery_info .= " / box: {$order['od_delivery_qty']}";
    }
    if ( $od_delivery['type'] == 'bus' ) {
        $delivery_info .= " / 정류장: {$order['od_delivery_place']}";
        $delivery_info .= " / box: {$order['od_delivery_qty']}";
    }
    if ( $od_delivery['type'] == 'delivery' || $od_delivery['type'] == 'quick' || $od_delivery['type'] == 'autobike' || $od_delivery['type'] == 'damas' || $od_delivery['type'] == 'huamul' || $od_delivery['type'] == 'gdhuamul' || $od_delivery['type'] == 'nationwidehuamul' || $od_delivery['type'] == 'bus'  ) {
        if ( $order['od_delivery_receiptperson'] == 0 ) {
            $delivery_info .= " / 송하인: 삼화";
        }else{
            $delivery_info .= " / 송하인: {$order['od_b_name']}";
        }
    }

    return $delivery_info;
}

$customer_codes = array(
    '0' =>  array(
        'name' => '비회원',
        'val' => '06012',
    ),
    '1' =>  array(
        'name' => '네이버 포인트결제',
        'val' => '10027',
    ),
    '2' =>  array(
        'name' => '네이버 PG결제',
        'val' => '13327',
    ),
    '3' =>  array(
        'name' => '오픈마켓 ESM지마켓',
        'val' => '08145',
    ),
    '4' =>  array(
        'name' => '오픈마켓 ESM옥션',
        'val' => '08144',
    ),
    '5' =>  array(
        'name' => '오픈마켓 11번가',
        'val' => '10204',
    ),
    '6' =>  array(
        'name' => '스마트스토어',
        'val' => '07979',
    ),
    '7' =>  array(
        'name' => '오너클랜',
        'val' => '08405',
    ),
);

function get_customer_step($code) {

    global $customer_codes;

    $ret = array();

    $k = -1;

    for($i=0;$i<count($customer_codes); $i++) {
        if ( $code == $customer_codes[$i]['val'] ) {
            return $customer_codes[$i]['name'];
        }
    }
    return '회원';
}

function get_customer_code($od_id) {

    global $g5;

    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $od = sql_fetch($sql);
    if (!$od['od_id']) {
        return false;
    }
    $mb = get_member($od['mb_id']);

    $customer_code_ck = $mb['mb_thezone'];

    if ($mb == null) {
        $customer_code_ck = '06012';
    }
    
    if ($od['customer_code'] != null) {
        $customer_code_ck = $od['customer_code'];
    }

    if ($od['od_settle_case'] == '네이버페이') {
        if ($od['od_naver_PaymentCoreType'] == 'PG결제') {
            $customer_code_ck = '13327';
        }
        else {
            $customer_code_ck = '10027';
        }
    }

    return $customer_code_ck;
}

function get_category_where($ca_id) {
    return " (
        ca_id = '{$ca_id}' OR
        ca_id2 = '{$ca_id}' OR
        ca_id3 = '{$ca_id}' OR
        ca_id4 = '{$ca_id}' OR
        ca_id5 = '{$ca_id}' OR
        ca_id6 = '{$ca_id}' OR
        ca_id7 = '{$ca_id}' OR
        ca_id8 = '{$ca_id}' OR
        ca_id9 = '{$ca_id}' OR 
        ca_id10 = '{$ca_id}'
    ) ";
}

?>
