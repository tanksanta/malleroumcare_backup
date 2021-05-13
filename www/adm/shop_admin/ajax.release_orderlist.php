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
if( !in_array($sel_field, array('od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'od_invoice')) ){   //검색할 필드 대상이 아니면 값을 제거
    $sel_field = '';
}
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
    if ($sel_field != "") {
        $where[] = " $sel_field like '%$search%' ";
    }

    if ($save_search != $search) {
        // $page = 1;
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
    }else{
        $where[] = " od_delivery_type = '$od_delivery_type' ";
    }
}

//print_r($where);
//exit;

// $where[] = " od_release_manager != '-' ";

if ( $price ) {
    $where[] = " (od_cart_price + od_send_cost + od_send_cost2 - od_cart_discount - od_cart_discount2) BETWEEN '{$price_s}' AND '{$price_e}' ";
}

if ($od_settle_case) {
    $where[] = " od_settle_case = '$od_settle_case' ";
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

if ($temp_where) {
    foreach($temp_where as $s) {
        $where[] = ' ( '.implode(' OR ', $temp_where).' ) ';
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

if ($fr_date && $to_date) {
    $where[] = " {$sel_date_field} between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if ($search_option) {
        $where[] = " {$search_option} LIKE '%{$search_text}%' ";
}


$where[] = " od_del_yn = 'N' ";
$where[] = " ct_status != '완료' ";

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
    }else{
        $where[] = " ct_status = '{$ct_status}'";
    }
}else{
    $order_steps_where = array();
    foreach($order_steps as $order_step) { 
        if (!$order_step['deliverylist']) continue;

        $order_steps_where[] = " ct_status = '{$order_step['val']}' ";
    }
    $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
}
/*
if ($ct_status) {
    switch($ct_status) {
        case '전체취소':
            $where[] = " ct_status = '취소' ";
            break;
        case '부분취소':
            $where[] = " ct_status IN('주문', '입금', '준비', '배송', '완료') and od_cancel_price > 0 ";
            break;
        default:
            $where[] = " ct_status = '$ct_status' ";
            break;
    }

    switch ($ct_status) {
        case '주문' :
            $sort1 = "od_id";
            $sort2 = "desc";
            break;
        case '입금' :   // 결제완료
            $sort1 = "od_receipt_time";
            $sort2 = "desc";
            break;
        case '배송' :   // 배송중
            $sort1 = "od_invoice_time";
            $sort2 = "desc";
            break;
    }
}
*/

// $where[] = " ct_status = '{$ct_status}'";

// 최고관리자가 아닐때
if ( $ct_status == '작성' && $is_admin != 'super' ) {
    $where[] = " od_writer = '{$member['mb_id']}' ";
}

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from (select ct_id as cart_ct_id, od_id as cart_od_id, it_name, ct_status from {$g5['g5_shop_cart_table']}) B
                inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
                left join (select mb_id as mb_id_temp, mb_level, mb_manager, mb_type from {$g5['member_table']}) C
                on A.mb_id = C.mb_id_temp
                $sql_search
                group by cart_ct_id ";

foreach($order_steps as $order_step) { 
    if (!$order_step['deliverylist']) continue;
    $order_by_steps[] = "'".$order_step['val']."'";
}

$order_by_step = implode(' , ', $order_by_steps);

//$sql_common .= " ORDER BY FIELD(ct_status, " . $order_by_step . " ), od_id desc ";
$sql_common .= " ORDER BY ";
switch($cust_sort){
	case "od_time" :
		$sql_common .= " od_time DESC ";
		break;
	case "ct_status" :
		$sql_common .= " FIELD ( ct_status, '출고준비', '완료' ) DESC ";
		break;
}

$sql = " select count(od_id) as cnt " . $sql_common;

$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 총 금액
$sql = " select sum(od_cart_price) as od_cart_price, sum(od_send_cost) as od_send_cost, sum(od_send_cost2) as od_send_cost2, sum(od_cart_discount) as od_cart_discount, sum(od_cart_discount2) as od_cart_discount2 " . $sql_common;
$row = sql_fetch($sql);
$total_price = $row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2'] - $row['od_cart_discount'] - $row['od_cart_discount2'];
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
$sql_common2 = " from {$g5['g5_shop_order_table']} $sql_search2 ";

//$sql = " select count(od_id) as cnt, ct_status $sql_common2 group by ct_status";

$sql = "select count(od_id) as cnt, ct_status, ct_status from (select ct_id as cart_ct_id, od_id as cart_od_id, it_name, ct_status from {$g5['g5_shop_cart_table']}) B
        inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
        left join (select mb_id as mb_id_temp, mb_level, mb_type from {$g5['member_table']}) C
        on A.mb_id = C.mb_id_temp
        $sql_search2
        group by ct_status ";


$result = sql_query($sql);
while( $row = sql_fetch_array($result) ) {
    $cate_counts[$row['ct_status']] = $row['cnt'];
}

// print_r($cate_counts);

$rows = $config['cf_page_rows'];
//$rows = 2;
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
    // $sql = "SELECT * FROM g5_shop_cart WHERE od_id = '{$row['od_id']}'";
    $sql = "SELECT c.*, i.it_model FROM g5_shop_cart as c LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id WHERE c.od_id = '{$row['od_id']}'";
    $cart_result = sql_query($sql);
    $row['cart'] = array();
    while ( $row2 = sql_fetch_array($cart_result) ) {
        $row['cart'][] = $row2;
    }
    $orderlist[] = $row;
}

/*
$qstr1 = "od_settle_case=".urlencode($od_settle_case)."&amp;od_misu=$od_misu&amp;od_cancel_price=$od_cancel_price&amp;od_refund_price=$od_refund_price&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if($default['de_escrow_use'])
    $qstr1 .= "&amp;od_escrow=$od_escrow";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";
*/

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';


?>
<?php

$ret = array();

$ret['counts'] = $cate_counts;


if ( !$total_count ) {
    $ret['main'] .= "
    <div class=\"samhwa_order_list_table_no_item\">
        <h1>자료가 없습니다.</h1>
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
    }else{
        $od_receipt_time = '';
    }

    if( count($order['cart']) > 1 ) {
        $od_cart_count = '    |&nbsp;' . count($order['cart']);
    }else{
        $od_cart_count = '';
    }

    $od_price = number_format($order['od_cart_price'] + $order['od_send_cost'] + $order['od_send_cost2'] - $order['od_cart_discount'] - $order['od_cart_discount2']);

    $mb_shorten_info = samhwa_get_mb_shorten_info($order['mb_id']);
    
    $od_receipt_name = $order['od_deposit_name'] ? $order['od_deposit_name'] . '<br>' : '';
    $od_receipt_name .= '(' . $order['od_settle_case'] . ')' . substr($order['od_bank_account'],0,12);

    $important_class = $order['od_important'] ? 'on' : '';

    //$goods_name .= $order['cart'][0]['it_name'] ? $order['cart'][0]['it_name'] : '<span class="notyet">없음(관리자 작성중)</span>';
	$goods_name = $order['cart'][0]['it_model'] ? $order['cart'][0]['it_model'] : '<span class="notyet">없음(관리자 작성중)</span>';
    
    $sale_manager = '';
    $sale_manager = get_member($order['od_sales_manager']);
    $sale_manager = get_sideview($sale_manager['mb_id'], get_text($sale_manager['mb_name']), $sale_manager['mb_email'], '');

    $release_manager = '';
    $release_manager = get_member($order['od_release_manager']);
    $release_manager = get_sideview($release_manager['mb_id'], get_text($release_manager['mb_name']), $release_manager['mb_email'], '');
	
	$od_cart_count = 0;
	
	$prodSupYqty = 0;
	$prodSupNqty = 0;
	$prodStockqty = 0;
	$prodDelivery = 0;
	
	if($order['cart']){
		foreach($order['cart'] as $cart) {
			$od_cart_count += $cart['ct_qty'];
			if ($saved_uid != $cart['ct_uid']) {
				$goods_ct++;
				$saved_uid = $cart['ct_uid'];
			}

			if($cart["prodSupYn"] == "Y"){
				$prodSupYqty += $cart["ct_qty"];
				$prodStockqty += $cart["ct_stock_qty"];
				$prodDelivery += $cart["ct_qty"];
				$prodDelivery -= $cart["ct_stock_qty"];
			}

			if($cart["prodSupYn"] == "N"){
				$prodSupNqty += $cart["ct_qty"];
			}
		}

		if($order["od_delivery_yn"] == "N"){
			$prodDelivery = 0;
		}

		$prodDeliveryMemo = ($prodDelivery) ? "(배송 : {$prodDelivery}개)" : "<span style='color: #DC3333;'>(배송 없음)</span>";
		$prodStockqtyMemo = ($prodStockqty) ? " (재고소진 {$prodStockqty})" : "";

        $prodBarNumCntBtnWord = "출고관리 ".$result_ct['ct_barcode_insert']."/".$result_ct['ct_qty'];
        $prodBarNumCntBtnWord = ($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) ? "입력완료" : $prodBarNumCntBtnWord;
        $prodBarNumCntBtnStatus = ($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) ? " disable" : "";


		$deliveryCntBtnWord = "배송정보 ({$order["od_delivery_insert"]}/{$order["od_delivery_total"]})";
		$deliveryCntBtnWord = ($order["od_delivery_insert"] >= $order["od_delivery_total"]) ? "입력완료" : $deliveryCntBtnWord;
		$deliveryCntBtnStatus = ($order["od_delivery_insert"] >= $order["od_delivery_total"]) ? " disable" : "";
	}

    $important2_class = $order['od_important2'] ? 'on' : '';

    $ct_status = get_step($order['ct_status']);

    $od_delivery = get_delivery_step($order['od_delivery_type']);
    // print_r($od_delivery);

    // 배송정보
    $delivery_info = $od_delivery['name'];
    if ( $od_delivery['type'] == 'delivery' ) {
        // $delivery_info .= " / 송장번호: {$order['od_delivery_text']}";
        $delivery_info .= "<br>";
        $delivery_info .= '<select class="od_delivery_company_select" name="od_delivery_company" onchange="changeDeliverySelect(this)">';
        foreach($delivery_companys as $company) {
            $is_selected = $company['val'] == $order['od_delivery_company'] ? 'selected' : '';
            $delivery_info .= "<option value='{$company['val']}' {$is_selected}>{$company['name']}</option>";
        }
        $delivery_info .= '</select>';
        // $readonly_when_ilogen = $order['od_delivery_company'] == 'ilogen' ? 'readonly' : '';
        $delivery_info .= "<input name='od_delivery_text' type='text' value='{$order['od_delivery_text']}' $readonly_when_ilogen/>";
        $delivery_info .= "<button class='changeDeliveryBtn' type='button' onclick='changeDeliveryInfo(\"{$order['od_id']}\", this)'>수정</button>";
        $delivery_info .= "<br>";

        $delivery_info_edit = true;
    }
    if ( $od_delivery['type'] == 'quick' ) {
        $delivery_info .= " / 연락처: {$order['od_delivery_tel']}";
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
        // $delivery_info .= " / box: {$order['od_delivery_qty']}";
        $delivery_info .= "<br>";
        $delivery_info .= '<select class="od_delivery_company_select" name="od_delivery_company" onchange="changeDeliverySelect(this)">';
        foreach($delivery_companys as $company) {
            $is_selected = $company['val'] == $order['od_delivery_company'] ? 'selected' : '';
            $delivery_info .= "<option value='{$company['val']}' {$is_selected}>{$company['name']}</option>";
        }
        $delivery_info .= '</select>';
        $delivery_info .= "<input name='od_delivery_text' type='text' value='{$order['od_delivery_text']}' />";
        $delivery_info .= "<button class='changeDeliveryBtn' type='button' onclick='changeDeliveryInfo(\"{$order['od_id']}\", this)'>수정</button>";
        $delivery_info .= "<br>";

        $delivery_info_edit = true;
    }
    if ( $od_delivery['type'] == 'gdhuamul' ) {
        // $delivery_info .= " / 영업소: {$order['od_delivery_text']}";
        // $delivery_info .= " / box: {$order['od_delivery_qty']}";
        $delivery_info .= "<br>";
        $delivery_info .= '<select class="od_delivery_company_select" name="od_delivery_company" onchange="changeDeliverySelect(this)">';
        foreach($delivery_companys as $company) {
            $is_selected = $company['val'] == $order['od_delivery_company'] ? 'selected' : '';
            $delivery_info .= "<option value='{$company['val']}' {$is_selected}>{$company['name']}</option>";
        }
        $delivery_info .= '</select>';
        $delivery_info .= "<input name='od_delivery_text' type='text' value='{$order['od_delivery_text']}' />";
        $delivery_info .= "<button class='changeDeliveryBtn' type='button' onclick='changeDeliveryInfo(\"{$order['od_id']}\", this)'>수정</button>";
        $delivery_info .= "<br>";

        $delivery_info_edit = true;
    }
    if ( $od_delivery['type'] == 'nationwidehuamul' ) {
        // $delivery_info .= " / 메모: {$order['od_delivery_text']}";
        // $delivery_info .= " / box: {$order['od_delivery_qty']}";
        $delivery_info .= "<br>";
        $delivery_info .= '<select class="od_delivery_company_select" name="od_delivery_company" onchange="changeDeliverySelect(this)">';
        foreach($delivery_companys as $company) {
            $is_selected = $company['val'] == $order['od_delivery_company'] ? 'selected' : '';
            $delivery_info .= "<option value='{$company['val']}' {$is_selected}>{$company['name']}</option>";
        }
        $delivery_info .= '</select>';
        $delivery_info .= "<input name='od_delivery_text' type='text' value='{$order['od_delivery_text']}' />";
        $delivery_info .= "<button class='changeDeliveryBtn' type='button' onclick='changeDeliveryInfo(\"{$order['od_id']}\", this)'>수정</button>";
        $delivery_info .= "<br>";

        $delivery_info_edit = true;
    }
    if ( $od_delivery['type'] == 'bus' ) {
        $delivery_info .= " / 정류장: {$order['od_delivery_place']}";
        $delivery_info .= " / box: {$order['od_delivery_qty']}";
    }
    if ( $od_delivery['type'] == 'delivery' || $od_delivery['type'] == 'quick' || $od_delivery['type'] == 'autobike' || $od_delivery['type'] == 'damas' || $od_delivery['type'] == 'huamul' || $od_delivery['type'] == 'gdhuamul' || $od_delivery['type'] == 'nationwidehuamul' || $od_delivery['type'] == 'bus'  ) {
        if (!$delivery_info_edit) {
            $delivery_info .= " / ";
        }
        if ( $order['od_delivery_receiptperson'] == 0 ) {
            $delivery_info .= "송하인: 삼화";
        }else{
            $delivery_info .= "송하인: {$order['od_b_name']}";
        }
    }

    // 배송정보 버튼
    $delivery_btn = '';
    if ( $od_delivery['print_page_name'] == 'huamul' || $od_delivery['print_page_name'] == 'damas' ) {
        $delivery_btn = '<a onclick="window.open(\'./pop.order.delivery.print.php?od_id='. $order['od_id'] .'\', \'delivery_print_pop\', \'width=835, height=900, resizable = no, scrollbars = no\')"><img class="printer" src="'. G5_ADMIN_URL .'/shop_admin/img/printer.png"/></a>';
    }
    if ( $od_delivery['type'] == 'delivery' ) {
        if ( $order['od_delivery_company'] == 'ilogen' ) {
            $delivery_btn = '<a href="https://www.ilogen.com/web/personal/trace/'. $order['od_delivery_text'] .'" target="_blank"><img src="'. G5_ADMIN_URL .'/shop_admin/img/btn_delivery.png"/></a>';
        }
    }

    if ( $now_step != $order['ct_status'] ) {
        
        if ( $where ) {
            $sql_search = ' where '.implode(' and ', $where) . " and ct_status = '{$order['ct_status']}' ";
        }else{
            $sql_search = " where ct_status = '{$order['ct_status']}' ";
        }
        $sql = " select count(od_id) as cnt, sum(od_cart_price) as od_cart_price, sum(od_send_cost) as od_send_cost, sum(od_send_cost2) as od_send_cost2, sum(od_cart_discount) as od_cart_discount, sum(od_cart_discount2) as od_cart_discount2 from {$g5['g5_shop_order_table']} $sql_search ";
        $total_result = sql_fetch($sql);
        $total_result['price'] = number_format($total_result['od_cart_price'] + $total_result['od_send_cost'] + $total_result['od_send_cost2'] - $total_result['od_cart_discount'] - $total_result['od_cart_discount2']);
            
        $od_status_info = get_step($order['ct_status']);
        $show_od_status = $od_status_info['chulgo'] ? $od_status_info['name'] . '<span>(' . $od_status_info['chulgo'] . ')</span>' : $od_status_info['name'];

        $next_step = get_next_step($order['ct_status']);
        $prev_step = get_prev_step($order['ct_status']);
        
        if ( $next_step ) {
            $show_next_status = '<span class="btn large"><button id="change_next_step" data-next-step-val="'. $next_step['val'] .'">선택 '. $next_step['name'] .'단계로 변경</button></span>';
        }else{
            $show_next_status = '';
        }
        
        if ( $prev_step ) {
            $show_prev_status = '<span class="btn large"><button id="change_prev_step" data-prev-step-val="'. $prev_step['val'] .'">선택 '. $prev_step['name'] .'단계로 되돌리기</button></span>';
        }else{
            $show_prev_status = '';
        }
        $now_step = $order['ct_status'];
    }
    // <input type=\"text\" name=\"od_release_date\" class=\"od_release_date\" data-od-id=\"{$order['od_id']}\" value=\"{$order['od_release_date']}\" />

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
        }else{
            $class_type2=""; 
        }
    }
    if($order['od_edit_member']){
		$edit_working = true;
	}
    if($ct_status['name']=="출고완료"){$od_status_name="출고<br>완료"; $class_type1="type4"; $class_type2= ($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) ? " disable" : ""; }
    if($ct_status['name']=="배송완료"){$od_status_name="배송<br>완료"; $class_type1="type5"; }
    
    

    $od_detail="총 ".($prodSupYqty + $prodSupNqty)." / 유통 {$prodSupYqty}{$prodStockqtyMemo} / 비 유통 {$prodSupNqty}";

    if(strpos($prodBarNumCntBtnWord, "입력완료") !== false) { 
        $complate_flag="cf"; 
        if($_POST['cf']==true){
            $complate_flag2="type1";
        }else{ 
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
	$ret["data"][$foreach_i]["od_name"] = $order["od_name"];
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
    if($ct_manager_sql['mb_name']){
        $ct_manage=$ct_manager_sql['mb_name'];
    }else{
        $ct_manage="미지정";
    }
	$ret["data"][$foreach_i]["ct_manager"] = $ct_manage;
	
	$foreach_i++;
}

$ret['last_step'] = $now_step;

header('Content-Type: application/json');
//$json = str_replace("\u0022","\\\\\"",json_encode( $ret ,JSON_HEX_QUOT)); 
//$json = json_encode( $ret, JSON_HEX_APOS|JSON_HEX_QUOT );
$json = json_encode(utf8ize($ret));
echo $json;
?>