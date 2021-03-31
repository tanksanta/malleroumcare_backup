<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "r");

$where = array();
$od_step = $od_step ? $od_step : 5;

$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('od_id', 'od_cart_price', 'od_receipt_price', 'od_cancel_price', 'od_misu', 'od_cash')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_field = get_search_string($sel_field);
if( !in_array($sel_field, array('od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'od_invoice')) ){   //검색할 필드 대상이 아니면 값을 제거
    $sel_field = '';
}
$od_status = get_search_string($od_status);
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

$where[] = " od_del_yn = 'N' ";

if ($where) {
    $where2 = $where;
}

if ( $od_status ) {
    if ( is_array($od_status) ) {

        $order_steps_where = array();
        foreach($od_status as $s) {
            $order_steps_where[] = " od_status = '{$s}'";
        }
        $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
    }else{
        $where[] = " od_status = '{$od_status}'";
    }
}else{
    $order_steps_where = array();
    foreach($order_steps as $order_step) {
        if (!$order_step['deliverylist']) continue;

        $order_steps_where[] = " od_status = '{$order_step['val']}' ";
    }
    $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
}
/*
if ($od_status) {
    switch($od_status) {
        case '전체취소':
            $where[] = " od_status = '취소' ";
            break;
        case '부분취소':
            $where[] = " od_status IN('주문', '입금', '준비', '배송', '완료') and od_cancel_price > 0 ";
            break;
        default:
            $where[] = " od_status = '$od_status' ";
            break;
    }

    switch ($od_status) {
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

// $where[] = " od_status = '{$od_status}'";

// 최고관리자가 아닐때
if ( $od_status == '작성' && $is_admin != 'super' ) {
    $where[] = " od_writer = '{$member['mb_id']}' ";
}

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from {$g5['g5_shop_order_table']} as A
    left join (select mb_id as mb_id_temp, mb_level, mb_type from {$g5['member_table']}) C
    on A.mb_id = C.mb_id_temp
$sql_search ";

foreach($order_steps as $order_step) {
    if (!$order_step['deliverylist']) continue;
    $order_by_steps[] = "'".$order_step['val']."'";
}

$order_by_step = implode(' , ', $order_by_steps);

$sql_common .= " ORDER BY FIELD(od_status, " . $order_by_step . " ), od_id desc ";

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
        $where2[] = " if(`od_status` = '작성', `od_writer`, '{$member['mb_id']}') = '{$member['mb_id']}' ";
    }
    if ( $where2 ) {
        $sql_search2 = ' where '.implode(' and ', $where2);
    }
}
$sql_common2 = " from {$g5['g5_shop_order_table']} as A
    left join (select mb_id as mb_id_temp, mb_level, mb_type from {$g5['member_table']}) C
    on A.mb_id = C.mb_id_temp
$sql_search2 ";
$sql = " select count(od_id) as cnt, od_status $sql_common2 group by od_status";
$result = sql_query($sql);
while( $row = sql_fetch_array($result) ) {
    $cate_counts[$row['od_status']] = $row['cnt'];
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

$ret['main'] = "
<div id=\"samhwa_order_list_table\">
    <div class=\"table list-table-style wide-table\">
        <table>
            <thead>
                <tr>
                    <th class=\"check\">선택</th>
                    <th class=\"od_time\">주문일시</th>
                    <th class=\"od_info\">주문정보</th>
                    <th class=\"od_name\">받는분(주문자)</th>
                    <th class=\"od_type\">결제수단</th>
						<th class=\"od_barNum\">바코드</th>
                    <th class=\"od_price\">결제금액</th>
                    <th>출담</th>
                    <th class=\"od_delivery_info\">배송정보</th>
                    <th>출고예정일</th>
                    <th>상태</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
";


if ( !$total_count ) {
    $ret['main'] .= "
    <div class=\"samhwa_order_list_table_no_item\">
        <h1>자료가 없습니다.</h1>
    </div>
    ";
}

$now_step = $last_step ? $last_step : '';

foreach($orderlist as $order) {
    $od_time = substr($order['od_time'],2,8) . '<br>' . '('. substr($order['od_time'],11,5) .')';

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

    $sql_goods_name="SELECT `it_name` FROM `g5_shop_cart` WHERE `od_id` = '".$order["od_id"]."'";
    $sql_goods_result = sql_fetch($sql_goods_name);

	  $goods_name = $sql_goods_result['it_name'] ? $sql_goods_result['it_name'] : '<span class="notyet">없음(관리자 작성중)</span>';

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

		$prodBarNumCntBtnWord = "바코드 ({$order["od_prodBarNum_insert"]}/{$order["od_prodBarNum_total"]})";
		$prodBarNumCntBtnWord = ($order["od_prodBarNum_insert"] >= $order["od_prodBarNum_total"]) ? "입력완료" : $prodBarNumCntBtnWord;
		$prodBarNumCntBtnStatus = ($order["od_prodBarNum_insert"] >= $order["od_prodBarNum_total"]) ? " disable" : "";

		$deliveryCntBtnWord = "배송정보 ({$order["od_delivery_insert"]}/{$order["od_delivery_total"]})";
		$deliveryCntBtnWord = ($order["od_delivery_insert"] >= $order["od_delivery_total"]) ? "입력완료" : $deliveryCntBtnWord;
		$deliveryCntBtnStatus = ($order["od_delivery_insert"] >= $order["od_delivery_total"]) ? " disable" : "";
	}

    $important2_class = $order['od_important2'] ? 'on' : '';

    $od_status = get_step($order['od_status']);

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

    if ( $now_step != $order['od_status'] ) {

        if ( $where ) {
            $sql_search = ' where '.implode(' and ', $where) . " and od_status = '{$order['od_status']}' ";
        }else{
            $sql_search = " where od_status = '{$order['od_status']}' ";
        }
        $sql = " select count(od_id) as cnt, sum(od_cart_price) as od_cart_price, sum(od_send_cost) as od_send_cost, sum(od_send_cost2) as od_send_cost2, sum(od_cart_discount) as od_cart_discount, sum(od_cart_discount2) as od_cart_discount2 from {$g5['g5_shop_order_table']} $sql_search ";
        $total_result = sql_fetch($sql);
        $total_result['price'] = number_format($total_result['od_cart_price'] + $total_result['od_send_cost'] + $total_result['od_send_cost2'] - $total_result['od_cart_discount'] - $total_result['od_cart_discount2']);

        $od_status_info = get_step($order['od_status']);
        $show_od_status = $od_status_info['chulgo'] ? $od_status_info['name'] . '<span>(' . $od_status_info['chulgo'] . ')</span>' : $od_status_info['name'];

        $next_step = get_next_step($order['od_status']);
        $prev_step = get_prev_step($order['od_status']);

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

        $ret['data'] .= "
        <tr class=\"step\">
            <td colspan=\"8\" class=\"ltr-bg-step-{$od_status_info['step']}\">
                {$show_od_status}
            </td>
            <td colspan=\"6\" class=\"ltr-bg-step-{$od_status_info['step']}\" style=\"text-align:right;\">
                총 {$total_result['cnt']}건 / 합계: ₩ {$total_result['price']}원
            </td>
        </tr>
        <tr class=\"btns\">
            <td colspan=\"14\">
                <ul class=\"left-btns\">
                    <li class=\"order-catalog-step-btns\">
                        <span class=\"custom-select-box-btn btn drop_multi_main\" data-value=\"select\"><a href=\"javascript:;\">전체선택</a></span><span class=\"custom-select-box-btn btn drop_multi_sub\"><a href=\"javascript:;\"></a></span>
                        <ul class=\"list-select custom-select-box-multi\" name=\"select_25\" rows=\"4\" onchange=\"list_select(this)\" style=\"display: none;\">
                            <li data-value=\"select\">전체선택</li>
                            <li data-value=\"not-select\">선택안함</li>
                            <li data-value=\"important\">별표선택</li>
                            <li data-value=\"not-important\">별표없음</li>
                        </ul>
                    </li>
                    <li class=\"order-catalog-step-btns\">
                        {$show_next_status}
                        {$show_prev_status}

                        <span class=\"btn large\"><button name=\"delivery_edi_return\" class=\"delivery_edi_return\" id=\"25\" >송장리턴</button></span>


                        <!--
                        <span class=\"btn large\"><button name=\"goods_export\" id=\"25\" onclick=\"batch_goods_export(25);\">출고처리</button></span>

                        <span class=\"btn large\"><button name=\"batch_custom_ready\" id=\"25\" onclick=\"batch_custom_ready(this);\">배송완료처리</button></span>



                        <span class=\"btn large\"><button name=\"batch_custom_cancel\" id=\"25\" onclick=\"batch_custom_cancel(this);\">[수동]주문무효</button></span>



                        <span class=\"btn large\"><button name=\"goods_print\" id=\"25\" onclick=\"order_print(this);\"><img src=\"/adm/shop_admin/img/printer.png\">프린트</button></span>

                        <span class=\"btn large newred\">
                            <button class=\"hand batch_reverse\" id=\"25\" onclick=\"batch_reverse(this);\" autodepositkey=\"\">
                            '주문접수' 되돌리기
                            </button>
                        </span>
                        -->
                    </li>
                </ul>
            </td>
        </tr>
        ";
        $now_step = $order['od_status'];
    }


    $ret['data'] .= "
    <tr class=\"tr_{$order['od_id']}\">
        <td align=\"center\" class=\"check\">
            <input type=\"checkbox\" name=\"od_id[]\" id=\"check_{$order['od_id']}\" value=\"{$order['od_id']}\" accumul_mark=\"Y\">
            <label for=\"check_{$order['od_id']}\">&nbsp;</label>
        </td>
        <td align=\"center\" class=\"od_time\">
            {$od_time}
        </td>
        <td align=\"left\" class=\"od_info\">
            <div class=\"order_info\">
                <div class=\"goods_info\">
                    <div class=\"goods_name\">
                        {$goods_name}
                    </div>
                    <div class=\"goods_ea\">
                        {$od_cart_count}
                    </div>
                    <div class=\"order_num\">
                        <a href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\">NO&nbsp;<span>{$order['od_id']}</span></a>
                    </div>
                </div>
                <div class=\"buttons\">
                    <a href=\"javascript:printOrderView('{$order['od_id']}')\"><img src=\"/adm/shop_admin/img/printer.png\" align=\"absmiddle\"></a>
                    <a href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\" target=\"_blank\"><span><img src=\"/adm/shop_admin/img/window.png\" align=\"absmiddle\"></span></a>
                    <span class=\"btn-direct-open\" onclick=\"btn_direct_open(this);\"></span>
                </div>
            </div>
        </td>
        <td align=\"center\" class=\"od_name\">
            {$order['od_b_name']}
            <br/>
            {$mb_shorten_info}{$order['od_name']}
        </td>
        <td align=\"center\" class=\"od_type\">
            {$od_receipt_name}
        </td>
        <td align=\"center\" class=\"od_barNum\">
			<a href='#' class='prodBarNumCntBtn{$prodBarNumCntBtnStatus}' data-id='{$order["od_id"]}'>{$prodBarNumCntBtnWord}</a>
        </td>
        <td align=\"center\" class=\"od_price\">
            <b>{$od_price}원</b>
        </td>
        <td align=\"center\">
            <span class=\"icon-star-gray hand list-important2 important-25 {$important2_class}\" data-od_id='{$order['od_id']}'></span>
        </td>
        <td align=\"center\" class=\"delivery_info od_delivery_info\">
			<a href='#' class='deliveryCntBtn{$deliveryCntBtnStatus}' data-id='{$order["od_id"]}'>{$deliveryCntBtnWord}</a>
        </td>
        <td align=\"center\">
            <input type=\"text\" name=\"od_ex_date\" class=\"od_ex_date\" data-od-id=\"{$order['od_id']}\" value=\"{$order['od_ex_date']}\" />
        </td>
        <td align=\"center\">
            {$od_status['name']}
        </td>
    </tr>
    ";
    // <input type=\"text\" name=\"od_release_date\" class=\"od_release_date\" data-od-id=\"{$order['od_id']}\" value=\"{$order['od_release_date']}\" />
}

$ret['last_step'] = $now_step;

header('Content-Type: application/json');
//$json = str_replace("\u0022","\\\\\"",json_encode( $ret ,JSON_HEX_QUOT));
//$json = json_encode( $ret, JSON_HEX_APOS|JSON_HEX_QUOT );
$json = json_encode(utf8ize($ret));
echo $json;
?>
