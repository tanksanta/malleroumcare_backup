<?php
// $sub_menu = '400400';
include_once('./_common.php');

// error_reporting(E_ALL & ~E_NOTICE);
// ini_set("display_errors", 1);

// auth_check($auth[$sub_menu], "r");

$where = array();
// $od_step = $od_step ? $od_step : 5;

$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('od_id', 'od_cart_price', 'od_receipt_price', 'od_cancel_price', 'od_misu', 'od_cash')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_field = get_search_string($sel_field);

// wetoz : naverpayorder - , 'od_naver_orderid' 추가
if( !in_array($sel_field, array('od_all', 'it_name', 'it_admin_memo', 'it_maker', 'od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'od_naver_orderid','barcode')) ){   //검색할 필드 대상이 아니면 값을 제거
    $sel_field = '';
}
$ct_status=$od_status;
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
    if ($save_search != $search) {
        // $page = 1;
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


// 결제 수단 라디오 -> 다중 체크박스로 변경

/*
if ($od_settle_case) {
    $where[] = " od_settle_case = '$od_settle_case' ";
}
*/

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

if ($where) {
    $where2 = $where;
}

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

// shop_cart 조인으로 수정
// member 테이블 조인
$sql_common = " from (select ct_id as cart_ct_id, od_id as cart_od_id, X.it_name, ct_delivery_num, it_admin_memo, it_maker, ct_status ,ct_move_date, ct_ex_date, ct_is_direct_delivery from {$g5['g5_shop_cart_table']} X left join {$g5['g5_shop_item_table']} I on I.it_id = X.it_id ) B
                inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
                left join (select mb_id as mb_id_temp, mb_level, mb_manager, mb_type from {$g5['member_table']}) C
                on A.mb_id = C.mb_id_temp
                $sql_search
                group by cart_ct_id ";

foreach($order_steps as $order_step) {
    if (!$order_step['orderlist']) continue;
    $order_by_steps[] = "'".$order_step['val']."'";
}

$order_by_step = implode(' , ', $order_by_steps);

// $sql_common .= " ORDER BY FIELD(ct_status, " . $order_by_step . " ), od_id desc ";
// echo $order_by_step;
// return false;
$sql_common .= " ORDER BY FIELD(B.ct_status, " . $order_by_step . " ), B.ct_move_date desc, od_id desc ";
// echo $order_by_step;
// return false;


$sql = " select count(od_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 총 금액
$sql = " select sum(od_cart_price) as od_cart_price, sum(od_send_cost) as od_send_cost, sum(od_send_cost2) as od_send_cost2, sum(od_cart_discount) as od_cart_discount, sum(od_cart_discount2) as od_cart_discount2, sum(od_sales_discount) as od_sales_discount" . $sql_common;
$row = sql_fetch($sql);
$total_price = $row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2'] - $row['od_cart_discount'] - $row['od_cart_discount2'] - $row['od_sales_discount'];
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

$sql = "select count(od_id) as cnt, ct_status, ct_status from (select ct_id as cart_ct_id, od_id as cart_od_id,ct_delivery_num, X.it_name, it_admin_memo, it_maker, ct_status, ct_ex_date, ct_is_direct_delivery from {$g5['g5_shop_cart_table']} X left join {$g5['g5_shop_item_table']} I on I.it_id = X.it_id ) B
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
// return false;
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
$ct_status_info = get_step($ct_status);
$show_ct_status = $ct_status_info['chulgo'] ? $ct_status_info['name'] . '<span>(' . $ct_status_info['chulgo'] . ')</span>' : $ct_status_info['name'];

$next_step = get_next_step($ct_status);
$prev_step = get_prev_step($ct_status);

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

$ret['counts'] = $cate_counts;


//분류
$ret['main'] = "
<div id=\"samhwa_order_list_table\">
    <div class=\"table list-table-style\">
        <table>
            <thead>
                <tr>
                    <th class=\"check\">선택</th>
                    <th class=\"balhaeng\">발행</th>
                    <th class=\"od_time\">주문일시</th>
                    <th class=\"od_info\">주문정보</th>
                    <th class=\"od_barNum\">바코드</th>
                    <th class=\"od_name\">받는분(주문자)</th>
                    <th class=\"od_receipt_time\">변경일시</th>
                    <th class=\"od_type\">결제수단</th>
                    <th class=\"od_price\">결제금액</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div class=\"table list-table-style\">
        <table class=\"scrollable\">
            <thead>
                <tr>
                    <th class=\"od_sales_manager\">영업담당자</th>
                    <th class=\"od_release_manager\">출고담당자</th>
                    <!-- <th class=\"od_release_manager_star\">출담</th>
                    
                    <th class=\"od_delivery_type\">출고방법</th> -->
                    <th class=\"od_ex_date\">출고완료일</th>
                    <th class=\"od_step\">주문상태</th>
                    <th class=\"od_matching\">매칭여부</th>
                    <th class=\"od_list_memo\">메모</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
";

//자료가 없을 때,
if ( !$total_count ) {
    $ret['main'] .= "
    <div class=\"samhwa_order_list_table_no_item\">
        <h1>자료가 없습니다.</h1>
    </div>
    ";
}

$now_step = $last_step ? $last_step : '';



foreach($orderlist as $order) {


    // cart_table  기준 정렬
    $sql_ct = "select c.*, i.ca_id from `g5_shop_cart` as c 
    INNER JOIN {$g5['g5_shop_item_table']} as i ON c.it_id = i.it_id
    WHERE c.ct_id ='".$order['cart_ct_id']."'";
    $result_ct = sql_fetch($sql_ct);


    //ct_count (order 기준 - 개수 )
    $sql_ct_count = "select count(ct_id) as ct_count from `g5_shop_cart` where `od_id` ='".$order['od_id']."'";
    $result_ct_count = sql_fetch($sql_ct_count);
    if($result_ct_count['ct_count'] > 1){
        $ct_count =$result_ct_count['ct_count']-1;
        $ct_count='+'.$ct_count;
    }else{
        $ct_count="";
    }

    $opt_price = 0;
    if($result_ct['io_type'])
      $opt_price = $result_ct['io_price'];
    else
      $opt_price = $result_ct['ct_price'] + $result_ct['io_price'];
    $result_ct["opt_price"] = $opt_price;

    $ct_price = number_format($opt_price*$result_ct['ct_qty']+$result_ct['ct_sendcost']-$result_ct['ct_discount']);//가격
    if($result_ct['ct_status']=="보유재고등록"||$result_ct['ct_status']=="재고소진"){ $ct_price = $result_ct['ct_status'];}
    $ct_it_name =$result_ct['it_name'];                                                                             //상품이름
    $ct_option = ($result_ct["ct_option"] == $result_ct['it_name']) ? "" : "(".$result_ct['ct_option'].")";         //옵션
    $ct_it_name=$ct_it_name.$ct_option;                                                                             //상품이름 + 옵션
    $ct_qty=$result_ct['ct_qty'];                                                                                   //개수
    $ct_status_text = $result_ct['ct_status'];                                                                           //상태
    $ct_it_id = $result_ct['it_id'];      
    $ct_ex_date = $result_ct['ct_ex_date'];                                                                          //출고완료일
    $ct_manager = $result_ct['ct_manager'];                                                                          //출고 담당자 아이디
    
    //출고담당자 select
    $od_release_select="";
    $od_release_select = '<select class="ct_manager" data-ct-id="'.$order['cart_ct_id'].'">';
        $sql_m="select b.`mb_name`, b.`mb_id` from `g5_auth` a left join `g5_member` b on (a.`mb_id`=b.`mb_id`) where a.`au_menu` = '400001'";
        $result_m = sql_query($sql_m);
        $od_release_select .= '<option value="미지정">미지정</option>';
        for ($q=0; $row_m=sql_fetch_array($result_m); $q++){
            $selected="";
            if($ct_manager == $row_m['mb_id']){ $selected="selected"; }
            $od_release_select .='<option value="'.$row_m['mb_id'].'" '.$selected.'>'.$row_m['mb_name'].'('.$row_m['mb_id'].')</option>';
        }
    $od_release_select .='</select>';
    

    //영업담당자
    $sql_manager = "SELECT `mb_manager`,`mb_entNm` FROM `g5_member` WHERE `mb_id` ='".$order['mb_id']."'";
    $result_manager = sql_fetch($sql_manager);

    //사업소명
    if($result_manager['mb_entNm']){
        $mb_entNm = $result_manager['mb_entNm'];
    }else{
        $mb_entNm = $order['od_name'];
    }

    $sql_manager = "SELECT `mb_name` FROM `g5_member` WHERE `mb_id` ='".$result_manager['mb_manager']."'";
    $result_manager = sql_fetch($sql_manager);
    $sale_manager=$result_manager['mb_name'];


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
    

    //보유재고등록 - > 보유재고로 표시
    

    // 취소 요청 체크
    $sql = "select *
            from g5_shop_order_cancel_request
            where od_id = '{$order['od_id']}' and approved = 0";

    $cancel_request_row = sql_fetch($sql);

    $is_order_cancel_requested = "";
    if ($cancel_request_row['od_id']) {
        $is_order_cancel_requested = "cancel_requested";
    }

    $od_time = substr($order['od_time'],2,8) . '<br>' . '('. substr($order['od_time'],11,5) .')';

    /*if($order['od_receipt_time'] != '0000-00-00 00:00:00') {
        $od_receipt_time = substr($order['od_receipt_time'],2,8) . '<br>' . '('. substr($order['od_receipt_time'],11,5) .')';
    }else{
        $od_receipt_time = '';
    }*/
    if($order['ct_move_date']) {
      $ct_move_time = strtotime($order['ct_move_date']);
      $od_receipt_time = date('Y-m-d', $ct_move_time).'<br>('.date('H:i:s', $ct_move_time).')';
    } else {
      $od_receipt_time = '';
    }

    // if( count($order['cart']) > 1 ) {
    //     $od_cart_count = '    |&nbsp;' . count($order['cart']);
    // }else{
    //     $od_cart_count = '';
    // }

    $od_price = number_format($order['od_cart_price'] + $order['od_send_cost'] + $order['od_send_cost2'] - $order['od_cart_discount'] - $order['od_cart_discount2'] - $order['od_sales_discount'])."원";
    $od_price = $order['cart']['ct_price'];
    




    $mb_shorten_info = $order['od_name'] ? samhwa_get_mb_shorten_info($order['mb_id']) : '';

    $od_receipt_name = $order['od_deposit_name'] ? $order['od_deposit_name'] . '<br>' : '';
    $od_receipt_name .= '(' . $order['od_settle_case'] . ')' . substr($order['od_bank_account'],0,12);

    $important_class = $order['od_important'] ? 'on' : '';

    if ( strpos($order['od_send_admin_memo'], '오픈마켓') ) {
        $goods_name = '<span class="openmarket">오</span>';
        $goods_name_alt = $order['od_send_admin_memo'];
    }else{
        $goods_name = '';
        $goods_name_alt = '';
    }

    $is_naverapi = false;
    if ($order['od_naver_orderid']) {
        $is_naverapi = true;
    }

    if ( $is_naverapi ) {
        $goods_name .= '<span class="naverpay">N</span>';
    }

    // $goods_name .= $order['cart'][0]['it_name'] ? $order['cart'][0]['it_name'] : '<span class="notyet">없음(관리자 작성중)</span>';
	$goods_name .= "[".($order["recipient_yn"] == "Y" ? "주문" : "재고")."] ";
    $goods_name .= $order['cart'][0]['it_name'] ? $order['cart'][0]['it_name'] : '<span class="notyet">없음(관리자 작성중)</span>';
	
//     $goods_ct = $order['cart'][0]['ct_qty']  ? $order['cart'][0]['ct_qty']  : '0';
//     $goods_ct = count((array)$order['cart']);

    $od_cart_count = 0;
    $saved_uid = '';
    $goods_ct = 0;
	
	$prodSupYqty = 0;
	$prodSupNqty = 0;
	$prodStockqty = 0;
	$prodDelivery = 0;
	
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
	
    if(!$result_ct['ct_barcode_insert']){
        $result_ct['ct_barcode_insert']=0;
    }
    $prodBarNumCntBtnWord = $result_ct['ct_barcode_insert']."/".$result_ct['ct_qty'];
	$prodBarNumCntBtnWord = ($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) ? "입력완료" : $prodBarNumCntBtnWord;
	$prodBarNumCntBtnStatus = ($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) ? " disable" : "";

    $barcode_html = '';
    if (!is_benefit_item($result_ct)) {
        $barcode_html = "<a href='#' class='prodBarNumCntBtn{$prodBarNumCntBtnStatus}' data-option='{$result_ct["ct_option"]}'  data-it='{$ct_it_id}' data-stock='{$stock_insert}'  data-od='{$order["od_id"]}'>{$prodBarNumCntBtnWord}</a>";
    }


    if ($od_cart_count > 0) {
        $show_od_cart_count = '| ' . $od_cart_count;
    }
    $show_goods_ct = $goods_ct > 1 ? '외 ' . ($goods_ct - 1) . '종' : '';

	$goods_data = get_goods($order['od_id']);

    if ( $result_ct['ct_status'] == '오픈마켓' ) {
        $goods_maching = ($goods_data['it_id'])?"매칭":"비매칭";
    }else{
        $goods_maching = '-';
    }

    $od_send_admin_memo = strpos($order['od_send_admin_memo'], '오픈마켓') > -1 ? get_text($order['od_send_admin_memo']) : '';


    if ( $now_step != $result_ct['ct_status'] ) {
        $ct_status_info = get_step($result_ct['ct_status']);
        $show_ct_status = $ct_status_info['chulgo'] ? $ct_status_info['name'] . '<span>(' . $ct_status_info['chulgo'] . ')</span>' : $ct_status_info['name'];

        $next_step = get_next_step($result_ct['ct_status']);
        $prev_step = get_prev_step($result_ct['ct_status']);

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

        $excel_btn = "";



        $ret['left'] .= "
        <tr class=\"step\">
            <td colspan=\"10\" class=\"ltr-bg-step-{$ct_status_info['step']}\">
                {$show_ct_status}
            </td>
        </tr>
        <tr class=\"btns\">
            <td colspan=\"10\">
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
                        <!-- <span class=\"btn large\"><button id=\"list_order_prints\">선택 작업지시서 출력</button></span> -->
                        <span class=\"btn large\"><button id=\"change_to_invalid_step\" >주문무효</button></span>
                    </li>
                </ul>
            </td>
        </tr>
        ";

        if ( $where ) {
            $sql_search = ' where '.implode(' and ', $where) . " and ct_status = '{$result_ct['ct_status']}' ";
        }else{
            $sql_search = " where ct_status = '{$result_ct['ct_status']}' ";
        }
        $sql = "
          select
            count(ct_id) as cnt,
            sum(
              case
                when io_type = 0
                then ct_price + io_price
                else ct_price
              end * ct_qty
            ) as ct_price,
            sum(ct_sendcost) as ct_sendcost,
            sum(ct_discount) as ct_discount
          from
            (select B.*, C.*, od_name, od_tel, od_hp, od_b_name, od_b_tel, od_b_hp, od_deposit_name, od_invoice, od_del_yn, od_time, od_receipt_time, od_ex_date, it_admin_memo, it_maker 
            from {$g5['g5_shop_cart_table']} B
            left join {$g5['g5_shop_item_table']} I on I.it_id = B.it_id
            inner join {$g5['g5_shop_order_table']} A on B.od_id = A.od_id
            left join (select mb_id as mb_id_temp, mb_level, mb_type from {$g5['member_table']}) C on B.mb_id = C.mb_id_temp
            group by B.ct_id ) as ct_id
          $sql_search
        ";
        $total_result = sql_fetch($sql);
        $total_result['price'] = number_format( $total_result['ct_price'] + $total_result['ct_sendcost'] - $total_result['ct_discount']);
        // print_r($sql);
        // return false;
        if($ct_status_info['name']=="재고소진"||$ct_status_info['name']=="보유재고등록"){ $status_info = "총 {$total_result['cnt']}건";}else{$status_info = "총 {$total_result['cnt']}건 / 합계: ₩ {$total_result['price']}원";}
        $ret['right'] .= "
        <tr class=\"step\">
            <td colspan=\"10\" class=\"ltr-bg-step-{$ct_status_info['step']}\">
                {$status_info}
            </td>
        </tr>
        <tr class=\"btns\">
            <td colspan=\"10\">
                <!--
                <span class=\"btn large\"><button name=\"excel_down\" onclick=\"excel_down('25')\"><img src=\"/adm/shop_admin/img/btn_img_ex.gif\" align=\"absmiddle\"> 엑셀다운로드</button></span>
                -->
            </td>
        </tr>
        ";
        $now_step = $result_ct['ct_status'];
    }

    $ret['left'] .= "
    <tr class=\"{$is_order_cancel_requested} tr_{$order['od_id']}\">
        <td align=\"center\" class=\"check\">
            <input type=\"checkbox\" name=\"od_id[]\" id=\"check_{$order['cart_ct_id']}\" value=\"{$order['cart_ct_id']}\" accumul_mark=\"Y\">
            <label for=\"check_{$order['cart_ct_id']}\">&nbsp;</label>
        </td>
        <td align=\"center\" class=\"balhaeng\">
            <span class=\"icon-star-gray hand list-important important-25 {$important_class}\" data-od_id='{$order['od_id']}'></span>
        </td>
        <td align=\"center\" class=\"od_time\">
            {$od_time}
        </td>
        <td align=\"left\" class=\"od_info\">
            <div class=\"order_info\">
                <div class=\"goods_info\">
                    <div class=\"goods_name\" title=\"{$goods_name_alt}\">
                        {$ct_it_name}(".($ct_qty)."개)
                    </div>
                    <!-- <div class=\"order_num\">
                        
                    </div> -->
                    <div class=\"order_num\">
                    <!-- 상품 주문번호 ({$order['cart_ct_id']})<br> -->
                        <a href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\">주문번호&nbsp;<span>({$order['od_id']})</span></a>
                    </div>
                    {$od_send_admin_memo}
                </div>


                <div class=\"buttons\">
	                <div class=\"ct_count\">
	                    {$ct_count}
	                </div>
                    <a href=\"javascript:printOrderView('{$order['od_id']}')\"><img src=\"/adm/shop_admin/img/printer.png\" align=\"absmiddle\"></a>
                    <a href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\" target=\"_blank\"><span><img src=\"/adm/shop_admin/img/window.png\" align=\"absmiddle\"></span></a>
                    <span class=\"btn-direct-open\" onclick=\"btn_direct_open(this);\"></span>
                </div>
            </div>
        </td>
		<td align=\"center\" class=\"od_barNum\">
            {$barcode_html}
        </td>
        <td align=\"center\" class=\"od_name\">
            <a href='#' data-mb-id='{$order['mb_id']}' class='open_member_pop'>
                {$order['od_b_name']}
                <br/>
                {$mb_shorten_info}{$mb_entNm}
            </a>
        </td>
        <td align=\"center\" class=\"od_receipt_time\">{$od_receipt_time}</td>
        <td align=\"center\" class=\"od_type\">
            {$od_receipt_name}
        </td>
        <td align=\"center\" class=\"od_price\">
            <b>{$ct_price}</b>
        </td>
    </tr>
    ";

    $important2_class = $order['od_important2'] ? 'on' : '';

    $pay_status = get_pay_step($order['od_pay_state']);
    $od_pay_state = '<span class="" style="color:'. $pay_status['color'] .'">'.$pay_status['name'] .'</span>';

    $od_ex_date = $order['od_ex_date'] === '0000-00-00' ? '-' : $order['od_ex_date'];

    //상품준비, 출고준비, 값 0000-00-00 이면, 출고예정
    if($ct_ex_date === '0000-00-00'){
        $ct_ex_date = "출고예정";
    }
    // if($ct_status_info['name']=="상품준비"||$ct_status_info['name']=="출고준비"){
    //     $od_ex_date = "출고예정";
    // }
    // //주문취소, 주문무효이면 상태값표시
    // if($ct_status_info['name']=="주문취소"||$ct_status_info['name']=="주문무효"){
    //     $ct_ex_date = $ct_status_info['name'];
    // }
    // $od_ex_date=$result_ct['ct_status'];
    // if ($cancel_request_row['od_id']) {
    //     $ct_status = get_canel_request($cancel_request_row['request_type']);
    // } else {
    //     $ct_status = get_step($result_ct['ct_status']);
    // }

    $od_delivery_type = get_delivery_step($order['od_delivery_type']);

    $show_od_delivery_type = $od_delivery_type['name'];
    if ($od_delivery_type['type'] == 'delivery') {
        $show_od_delivery_type .= "<br>" . ( $order['od_edi_result'] == '1' ? '<span style="color:#236ec6">전송</span>' : '<span style="color:#c72102">미전송</span>' );
    }

    $od_release_out = '-';

    $od_list_memo = $order['od_list_memo'] ? htmlspecialchars($order['od_list_memo']) : '없음';
    if($order['ct_is_direct_delivery']) $od_list_memo = '직배송';

    $ret['right'] .= "
    <tr class=\"{$is_order_cancel_requested} tr_{$order['od_id']}\">
        <td align=\"center\" class=\"od_sales_manager\">
            {$sale_manager}
        </td>
        <td align=\"center\" class=\"od_release_manager\">
            {$od_release_select}
        </td>
        <!-- <td align=\"center\" class=\"od_release_manager_star\">
            <span class=\"icon-star-gray hand list-important2 important-25 {$important2_class}\" data-od_id='{$order['od_id']}'></span>
        </td>
        <td align=\"center\" class=\"od_pay_state\">
            {$od_pay_state}
        </td>
        <td align=\"center\" class=\"od_delivery_type\">
            {$show_od_delivery_type}
        </td> -->
        <td align=\"center\" class=\"od_ex_date\">
            {$ct_ex_date}
        </td>
        <td align=\"center\" class=\"od_step\">
            {$ct_status_text}
        </td>
        <td align=\"center\" class=\"od_matching\"><b>{$goods_maching}</b></td>
        <td align=\"center\" class=\"od_list_memo\">
            <span class=\"open_list_memo_layer_popup list_memo_{$order['od_id']} \" data-od-id=\"{$order['od_id']}\">
                {$od_list_memo}
            </span>
        </td>
    </tr>
    ";

    $ret['last_step'] = $now_step;
}

$json = json_encode(utf8ize($ret));
// $jsonString = mb_convert_encoding($data['name'], 'UTF-8', 'UTF-8');

// if (json_last_error() != JSON_ERROR_NONE) {
//     printf("JSON Error: %s", json_last_error_msg());
// }

header('Content-Type: application/json');
// $json = str_replace("\u0022","\\\\\"",json_encode( $ret ,JSON_HEX_QUOT));
// $json = json_encode( $ret, JSON_HEX_APOS|JSON_HEX_QUOT );
// $json = json_encode($ret);
echo $json;
?>