<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "r");
$ct_status=$od_status;
$where = array();
$od_step = $od_step ? $od_step : 5;

$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('od_id', 'od_cart_price', 'od_receipt_price', 'od_cancel_price', 'od_misu', 'od_cash', 'od_time', 'ct_status')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_field = get_search_string($sel_field);
if( !in_array($sel_field, array('od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'od_invoice', 'ct_delivery_num')) ){   //검색할 필드 대상이 아니면 값을 제거
  $sel_field = '';
}
$ct_status = get_search_string($ct_status);
$search = get_search_string($search);
$ct_manager = get_search_string($ct_manager);
$incompleted_barcode = get_search_string($incompleted_barcode) == 'true';
$unselected_only = get_search_string($unselected_only) == 'true';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$od_misu = preg_replace('/[^0-9a-z]/i', '', $od_misu);
$od_cancel_price = preg_replace('/[^0-9a-z]/i', '', $od_cancel_price);
$od_refund_price = preg_replace('/[^0-9a-z]/i', '', $od_refund_price);
$od_receipt_point = preg_replace('/[^0-9a-z]/i', '', $od_receipt_point);
$od_coupon = preg_replace('/[^0-9a-z]/i', '', $od_coupon); 

$sql_search = "";
if ($search != "") {
  if ($sel_field != "") {
    $where[] = " $sel_field like '%$search%' ";
  }
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
    $where_od_release_manager[] = " od_release_manager = '{$od_release_manager[$i]}'";
  }
  if ( count($where_od_release_manager) ) {
    $where[] = " ( " . implode(' OR ', $where_od_release_manager) . " ) ";
  }
}

if ( $od_delivery_type ) {
  if ( is_array($od_delivery_type) ) {
    $where_delivery_type = array();

    foreach($od_delivery_type as $type) {
      $where_delivery_type[] = " od_delivery_type = '$type'";
    }
    $where[] = ' ( '.implode(' or ', $where_delivery_type). ' ) ';
  } else {
    $where[] = " od_delivery_type = '$od_delivery_type' ";
  }
}

if ( $price ) {
  $where[] = " (od_cart_price + od_send_cost + od_send_cost2 - od_cart_discount - od_cart_discount2 - od_sales_discount) BETWEEN '{$price_s}' AND '{$price_e}' ";
}

if ($od_settle_case) {
  $where[] = " od_settle_case = '$od_settle_case' ";
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
    $where_od_release_manager[] = " od_release_manager = '{$od_release_manager[$i]}'";
  }
  if ( count($where_od_release_manager) ) {
    $where[] = " ( " . implode(' OR ', $where_od_release_manager) . " ) ";
  }
}

if (gettype($od_important) == 'string' && $od_important !== '') {
  $od_important = $od_important;
  $where[] = " od_important = '$od_important' ";
}

if (gettype($od_release) == 'string' && $od_release !== '') {
  if ($od_release == '0') { // 일반출고
    $where[] = " ( od_release_manager != 'no_release' AND od_release_manager != '-' ) ";
  }
  if ($od_release == '1') { // 외부출고
    $where[] = " ( od_release_manager = '-' ) ";
  }
  if ($od_release == '2') { // 출고아님
    $where[] = " ( od_release_manager = 'no_release' ) ";
  }
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

if ($manager_option) {
  $where[] = " mb_manager = '{$manager_option}' ";
}

if ($ct_status_option) {
  $ct_status = explode(',', $ct_status_option);
}

if ($fr_date && $to_date) {
  $where[] = " {$sel_date_field} between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if($od_addr1_option
  && in_array($od_addr1_option,
    ['서울', '부산', '대구', '인천',
    '광주', '대전', '울산', '세종',
    '경기', '강원', '충북', '충남',
    '전북', '전남', '경북', '경남', '제주'])) {
  $where[] = " od_addr1 like '%{$od_addr1_option}%' ";
}

if($search_text) {
  if ($search_option && strpos($search_option, ',') !== false) {
    $search_options = explode(',', $search_option);
    $s_where = [];
    foreach($search_options as $s_option) {
      // 시작 -->
      // 22.09.30 : 서원 - 송장번호 Oney 숫자 처리로 인한 검색 오류
      if( $s_option == "ct_delivery_num" ) { 
        $search_text = preg_replace("/[^0-9]*/s", "", $search_text); // 검색 데이터의 숫자만 추출하여 변수에 재저장
		if( mb_strlen( $search_text ) > 13 ) {
			$_str = mb_substr($search_text , 13, 2);
			// 23.01.11 : 서원 - 13자리 이상 뒤에 3자리중 "00"로 시작한다면...
			//                    13자리까지 자리수로 잘라서 저장 한다.
			if( $_str == "00" ) { 
			  $search_text = mb_substr($search_text , 0, 13);
			}
		  }
		if($search_text != "")$s_where[] = " $s_option like '{$search_text}%' ";
	  }else{
      // 종료 -->
		$s_where[] = " $s_option like '%{$search_text}%' ";
	  }
    }
    $where[] = " ( " . implode(' OR ', $s_where) . " ) ";
  } else {
    // 시작 -->
    // 22.09.30 : 서원 - 송장번호 Oney 숫자 처리로 인한 검색 오류
    if( $search_option == "ct_delivery_num" ) { 
      $search_text2 = $search_text;
	  $search_text = preg_replace("/[^0-9]*/s", "", $search_text); // 검색 데이터의 숫자만 추출하여 변수에 재저장
      // 23.01.11 : 서원 - 택배송장 검색 이면서 13자리 이상일 경우.
      if( mb_strlen( $search_text ) > 13 ) {
        $_str = mb_substr($search_text , 13, 2);
        // 23.01.11 : 서원 - 13자리 이상 뒤에 3자리중 "00"로 시작한다면...
        //                    13자리까지 자리수로 잘라서 저장 한다.
        if( $_str == "00" ) { 
          $search_text = mb_substr($search_text , 0, 13);
        }
      }
	  if($search_text != ""){
		$where[] = " {$search_option} LIKE '{$search_text}%' ";
	  }else{
		$where[] = " {$search_option} LIKE '{$search_text2}%' ";
	  }
      
    } else {
      $where[] = " {$search_option} LIKE '%{$search_text}%' ";  
    }
    // 종료 -->

  }
}

if($add_search_text && $add_search_option) {
  $where[] = " {$add_search_option} LIKE '%{$add_search_text}%' ";
}


$where[] = " od_del_yn = 'N' ";

if ($where) {
  $where2 = $where;
}

if ( $ct_status ) {
  if ( is_array($ct_status) ) {
    $order_steps_where = array();
    foreach($ct_status as $s) {
      $order_steps_where[] = " ct_status = '{$s}'";
    }
    $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
  } else {
    $where[] = " ct_status = '{$ct_status}'";
  }
} else {
  $order_steps_where = array();
  foreach($order_steps as $order_step) { 
    if (!$order_step['deliverylist']) continue;

    $order_steps_where[] = " ct_status = '{$order_step['val']}' ";
  }
  $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
}

// 최고관리자가 아닐때
if ( $ct_status == '작성' && $is_admin != 'super' ) {
  $where[] = " od_writer = '{$member['mb_id']}' ";
}

// 출고담당자 검색
if($ct_manager) {
  $where[] = " ct_manager = '{$ct_manager}' ";
}

// 미지정만 보기
else if($unselected_only) {
  $where[] = " (ct_manager = '' or ct_manager is null) ";
}

// 바코드등록 미완료만 보기
if($incompleted_barcode) {
  $where[] = " ct_barcode_insert < ct_qty ";
}

if ($where) {
  $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from (select ct_id as cart_ct_id, od_id as cart_od_id, it_name, ct_status, ct_move_date, ct_manager, ct_barcode_insert, ct_qty, io_type, ct_price, io_price, ct_sendcost, ct_discount, ct_delivery_num from {$g5['g5_shop_cart_table']}) B
                inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
                left join (select mb_id as mb_id_temp, mb_level, mb_manager, mb_type from {$g5['member_table']}) C
                on A.mb_id = C.mb_id_temp
                $sql_search
                group by cart_ct_id ";

foreach(array_reverse($order_steps) as $order_step) { 
  if (!$order_step['deliverylist']) continue;
  $order_by_steps[] = "'".$order_step['val']."'";
}

$order_by_step = implode(' , ', $order_by_steps);

$sql_common .= " ORDER BY FIELD(ct_status, " . $order_by_step . " ), B.ct_move_date asc, od_id asc ";

$sql = " select count(od_id) as cnt " . $sql_common;

$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 총 금액
$sql = "
  SELECT
    sum(
      case
        when io_type = 0
        then ct_price + io_price
        else ct_price
      end * ct_qty
    ) as ct_price,
    sum(ct_sendcost) as ct_sendcost,
    sum(ct_discount) as ct_discount
  FROM
    ( SELECT * {$sql_common} ) u
";
$row = sql_fetch($sql);
$total_price = $row['ct_price'] + $row['ct_sendcost'] - $row['ct_discount'];
$show_total_price = number_format($total_price);


$cate_counts = array();

if ( $where2 || $where ) {
  if ( $is_admin != 'super' ) {
    $where2[] = " if(`ct_status` = '작성', `od_writer`, '{$member['mb_id']}') = '{$member['mb_id']}' ";
  }
  if ( $where2 ) {
    $sql_search2 = ' where '.implode(' and ', $where2);
  }
}

$sql = "select count(od_id) as cnt, ct_status, ct_status from (select ct_id as cart_ct_id, od_id as cart_od_id, it_name, ct_status, ct_manager, ct_barcode_insert, ct_qty, ct_delivery_num from {$g5['g5_shop_cart_table']}) B
        inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
        left join (select mb_id as mb_id_temp, mb_level, mb_type from {$g5['member_table']}) C
        on A.mb_id = C.mb_id_temp
        $sql_search2
        group by ct_status ";


$result = sql_query($sql);
while( $row = sql_fetch_array($result) ) {
  $cate_counts[$row['ct_status']] = $row['cnt'];
}

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *,
            (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
           $sql_common
           limit $from_record, $rows ";
$result = sql_query($sql);

$orderlist = array();
while( $row = sql_fetch_array($result) ) {
  $sql = "SELECT c.*, i.it_model FROM g5_shop_cart as c LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id WHERE c.od_id = '{$row['od_id']}'";
  $cart_result = sql_query($sql);
  $row['cart'] = array();
  while ( $row2 = sql_fetch_array($cart_result) ) {
      $row['cart'][] = $row2;
  }
  $orderlist[] = $row;
}
?>
<?php

$ret = array();

$ret['counts'] = $cate_counts;

if ( !$total_count ) {
    $ret['main'] .= "
    <div class=\"samhwa_order_list_table_no_item\">
        <h1>주문내역이 없습니다.</h1>
    </div>
    ";
}

$now_step = $last_step ? $last_step : '';

$foreach_i = 0;
foreach($orderlist as $order) {
  // cart_table  기준 정렬
  $sql_ct = "select * from `g5_shop_cart` where `ct_id` ='".$order['cart_ct_id']."'";
  $result_ct = sql_fetch($sql_ct);
  
  
  $ct_price =number_format($result_ct['ct_price']*$result_ct['ct_qty']+$result_ct['ct_sendcost']-$result_ct['ct_discount']);//가격
  if($result_ct['ct_status']=="보유재고등록"||$result_ct['ct_status']=="재고소진"){ $ct_price =$result_ct['ct_status'];}
  $ct_it_name =$result_ct['it_name'];                                                                             //상품이름
  $ct_option = ($result_ct["ct_option"] == $result_ct['it_name']) ? "" : "(".$result_ct['ct_option'].")";         //옵션
  $ct_it_name=$ct_it_name.$ct_option;                                                                             //상품이름 + 옵션
  $ct_qty=$result_ct['ct_qty'];                                                                                   //개수
  $ct_status_text = $result_ct['ct_status'];                                                                           //상태
  $ct_it_id = $result_ct['it_id'];      
  $ct_ex_date = $result_ct['ct_ex_date'];      
  switch ($ct_status_text) {
    case '보유재고등록': $ct_status_text="보유재고등록"; break;
    case '재고소진': $ct_status_text="재고소진"; break;
    case '주문무효': $ct_status_text="주문무효"; break;
    case '취소': $ct_status_text="주문취소"; break;
    case '주문': $ct_status_text="상품주문"; break;
    case '입금': $ct_status_text="입금완료"; break;
    case '준비': $ct_status_text="상품준비"; break;
    case '출고준비': $ct_status_text="출고준비"; break;
    case '배송': $ct_status_text="출고완료"; break;
    case '완료': $ct_status_text="배송완료"; break;
  }
  $stock_insert=1;

  $od_time = substr($order['od_time'],2,8) . '<br>' . '('. substr($order['od_time'],11,5) .')';
  $od_time2 = substr($order['od_time'],2,8)  . '('. substr($order['od_time'],11,5) .')';

  if($order['od_receipt_time'] != '0000-00-00 00:00:00') {
      $od_receipt_time = substr($order['od_receipt_time'],2,8) . '<br>' . '('. substr($order['od_receipt_time'],11,5) .')';
  } else {
      $od_receipt_time = '';
  }
  $od_cart_count = 0;
  
  if($order['cart']) {
    foreach($order['cart'] as $cart) {
      $od_cart_count += $cart['ct_qty'];
    }

    if(!$result_ct['ct_barcode_insert']) {
      $result_ct['ct_barcode_insert']=0;
    }
    $prodBarNumCntBtnWord = "출고관리 ".$result_ct['ct_barcode_insert']."/".$result_ct['ct_qty'];
    if($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) {
      $prodBarNumCntBtnWord = "입력완료";
    }
  }

  $ct_status = get_step($order['ct_status']);

  if ( $now_step != $order['ct_status'] ) {
    if ( $where ) {
      $sql_search = ' where '.implode(' and ', $where) . " and ct_status = '{$order['ct_status']}' ";
    } else {
      $sql_search = " where ct_status = '{$order['ct_status']}' ";
    }
    $sql = " select count(od_id) as cnt, sum(od_cart_price) as od_cart_price, sum(od_send_cost) as od_send_cost, sum(od_send_cost2) as od_send_cost2, sum(od_cart_discount) as od_cart_discount, sum(od_cart_discount2) as od_cart_discount2, sum(od_sales_discount) as od_sales_discount from {$g5['g5_shop_order_table']} $sql_search ";
    $now_step = $order['ct_status'];
  }

  // 20210315성훈 필요데이터 작업
  // $sql_2 = "SELECT * FROM g5_shop_cart WHERE od_id ='{$order['od_id']}'";
  // $od_2 = sql_fetch($sql_2);
  $od_status_name="";
  $class_type1="";
  $class_type2="";
  $complate_flag="";
  $complate_flag2="";
  $edit_working = false;
    
  if($ct_status['name']=="출고준비"){
    $od_status_name="출고<br>준비"; $class_type1="type3";
    if($order['od_edit_member']){   //작업중인사람
      // $class_type2="active"; 
    } else {
      $class_type2=""; 
    }
  }

  if($ct_status['name']=="상품준비"){ $od_status_name="상품<br>준비"; $class_type1="type2"; }
  if($ct_status['name']=="출고완료"){ $od_status_name="출고<br>완료"; $class_type1="type4"; $class_type2= ($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) ? " disable" : ""; }
  if($ct_status['name']=="배송완료"){ $od_status_name="배송<br>완료"; $class_type1="type5"; }

  if(strpos($prodBarNumCntBtnWord, "입력완료") !== false) { 
    $complate_flag="cf"; 
    if($_POST['cf']==true) {
      $complate_flag2="type1";
    } else { 
      $complate_flag2="type2";
    }
  }

  # 210317 추가정보
  $moreInfo = sql_fetch("
    SELECT
      ( SELECT it_name FROM g5_shop_cart WHERE od_id = a.od_id ORDER BY it_id ASC LIMIT 0, 1 ) AS it_name,
      ( SELECT COUNT(*) FROM g5_shop_cart WHERE od_id = a.od_id ) AS totalCnt
    FROM g5_shop_order a
    WHERE od_id = '{$order["od_id"]}'
  ");
  
  $moreInfoDisplayCnt = "";
  $moreInfo["totalCnt"]--;
  if($moreInfo["totalCnt"]){
    $moreInfoDisplayCnt = "외 {$moreInfo["totalCnt"]}종";
  }
  
  # 210318 추출 데이터 배열
  $ret["data"][$foreach_i]["od_id"] = $order["od_id"];
  $ret["data"][$foreach_i]["od_b_name"] = $order["od_b_name"];

  $ret["data"][$foreach_i]["it_name"] = ($ct_status_text == "재고소진") ? "재고" : "주문";

  $ret["data"][$foreach_i]["it_name"] = "[{$ret["data"][$foreach_i]["it_name"]}] ";
  $ret["data"][$foreach_i]["it_name"] .= $ct_it_name;

  $ret["data"][$foreach_i]["delivery_cnt"] = $od_cart_count;
  $ret["data"][$foreach_i]["cnt_detail"] = $ct_qty;
  $ret["data"][$foreach_i]["date"] = $od_time2;
  
  $sql_manager = "SELECT `mb_manager`,`mb_entNm` FROM `g5_member` WHERE `mb_id` ='".$order['mb_id']."'";
  $result_manager = sql_fetch($sql_manager);
  //사업소명
  if($result_manager['mb_entNm']){
    $mb_entNm = $result_manager['mb_entNm'];
  } else {
    $mb_entNm = $order['od_name'];
  }
  if($result_ct['ct_edit_member']){
    $edit_working = true;
    $class_type2="active"; 
  }

  $ret["data"][$foreach_i]["od_name"] = $mb_entNm;
  $ret["data"][$foreach_i]["od_status_name"] = $od_status_name;
  $ret["data"][$foreach_i]["od_status_class"] = $class_type1;
  $ret["data"][$foreach_i]["od_barcode_class"] = $class_type2;
  $ret["data"][$foreach_i]["od_barcode_name"] = $prodBarNumCntBtnWord;
  $ret["data"][$foreach_i]["edit_status"] = $edit_working;
  $ret["data"][$foreach_i]["complate_flag"] = $complate_flag;
  $ret["data"][$foreach_i]["complate_flag2"] = $complate_flag2;

  $ret["data"][$foreach_i]["option"] = $complate_flag2;
  $ret["data"][$foreach_i]["complate_flag2"] = $complate_flag2;
  $ret["data"][$foreach_i]["ct_it_id"] = $ct_it_id;
  $ret["data"][$foreach_i]["ct_option"] = $result_ct["ct_option"];
  $ret["data"][$foreach_i]["ct_id"] = $order['cart_ct_id'];

  $ct_manager_sql = sql_fetch('select `mb_name` from `g5_member` where mb_id = "'.$result_ct["ct_manager"].'"');
  if($ct_manager_sql['mb_name']) {
    $ct_manage=$ct_manager_sql['mb_name'];
  } else {
    $ct_manage="미지정";
  }
  $ret["data"][$foreach_i]["ct_manager"] = $ct_manage;
  
  $foreach_i++;
}

$ret['last_step'] = $now_step;

$ret['total_price'] = $show_total_price;

header('Content-Type: application/json');
//$json = str_replace("\u0022","\\\\\"",json_encode( $ret ,JSON_HEX_QUOT)); 
//$json = json_encode( $ret, JSON_HEX_APOS|JSON_HEX_QUOT );
$json = json_encode(utf8ize($ret));
echo $json;
?>
