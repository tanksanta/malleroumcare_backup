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
if( !in_array($sel_field, array('od_all', 'od_id', 'it_name', 'it_admin_memo', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num','barcode')) ){   //검색할 필드 대상이 아니면 값을 제거
  $sel_field = '';
}

$ct_status=$od_status;
$ct_status = get_search_string($ct_status);
$search = get_search_string(trim($search));
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
      $where[] = '('.$bacode_search.')';
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

if ($search_add_add != "") {
  $search_add_add = trim($search_add_add);
  if ($sel_field_add_add != "" && $sel_field_add_add != "od_all") {
    $where[] = "$sel_field_add_add like '%$search_add_add%'";
  }
}

// 작업상태 검색
if ($complete1) {
  // 바코드 미완료만 검색
  $where[] = " ( ct_barcode_insert < ct_qty and io_type = '0' ) ";
}
else if ($not_complete1) {
  // 바코드 완료만 검색
  $where[] = " ( ct_barcode_insert >= ct_qty or io_type = '1' ) ";
}
if ($complete2) {
  // 배송정보 미완료만 검색
  $where[] = " ( ( ct_delivery_num is null or ct_delivery_num = '' ) and ct_is_direct_delivery = 0 and ( ct_combine_ct_id is null or ct_combine_ct_id = 0 ) ) ";
}
else if ($not_complete2) {
  // 배송정보 완료만 검색
  $where[] = " ( ( ct_delivery_num is not null and ct_delivery_num <> '' ) or ct_is_direct_delivery > 0 or ( ct_combine_ct_id is not null and ct_combine_ct_id <> 0 ) ) ";
}
if ($not_complete3) {
  // 합포 미적용 내역만 보기
  $where[] = " ( ct_is_auto_combined = 0 ) ";
}
if ($not_approved) {
  $where[] = " ( ct_barcode_insert_not_approved > 0 ) ";
}

// 전체 검색
if ($sel_field == 'od_all' && $search != "") {
  $sel_arr = array('it_name', 'it_admin_memo', 'od_id', 'mb_id', 'mb_nick', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num','barcode');

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

// 전체 검색2
if ($sel_field_add == 'od_all' && $search_add != "") {
  $sel_arr = array('it_name', 'it_admin_memo', 'od_id', 'mb_id', 'mb_nick', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num','barcode');

  foreach ($sel_arr as $key => $value) {
      if($value=="barcode"){
          $sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search_add."'";
          $result_barcode_search = sql_query($sql_barcode_search);
          $or="";
          while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
              $bacode_search .= $or." `stoId` like '%".$row_barcode['stoId']."%' ";
              $or="or";
          }
          if($bacode_search){
              $sel_arr[$key] = $bacode_search;
          }else{
              $sel_arr[$key] = "stoId like '%$search_add%'";
          }
      }else{
          $sel_arr[$key] = "$value like '%$search_add%'";
      }
  }

  $where[] = "(".implode(' or ', $sel_arr).")";
}

// 전체 검색3
if ($sel_field_add_add == 'od_all' && $search_add_add != "") {
  $sel_arr = array('it_name', 'it_admin_memo', 'od_id', 'mb_id', 'mb_nick', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num','barcode');

  foreach ($sel_arr as $key => $value) {
      if($value=="barcode"){
          $sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search_add_add."'";
          $result_barcode_search = sql_query($sql_barcode_search);
          $or="";
          while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
              $bacode_search .= $or." `stoId` like '%".$row_barcode['stoId']."%' ";
              $or="or";
          }
          if($bacode_search){
              $sel_arr[$key] = $bacode_search;
          }else{
              $sel_arr[$key] = "stoId like '%$search_add_add%'";
          }
      }else{
          $sel_arr[$key] = "$value like '%$search_add_add%'";
      }
  }

  $where[] = "(".implode(' or ', $sel_arr).")";
}

if ( $price ) {
  $where[] = " (od_cart_price + od_send_cost + od_send_cost2 - od_cart_discount - od_cart_discount2 - od_sales_discount) BETWEEN '{$price_s}' AND '{$price_e}' ";
}

if ($od_settle_case) {
  $where[] = " od_settle_case = '$od_settle_case' ";
}

if ($od_openmarket) {
  if ( is_array($od_openmarket) ) {
    $od_openmarket_where = array();
    foreach($od_openmarket as $s) {
      if($s=="my") {
        $od_openmarket_where[] = " od_writer != 'openmarket'";
      } else {
        $od_openmarket_where[] = " sabang_market = '{$s}'";
      }
    }
    $where[] = ' ( '.implode(' OR ', $od_openmarket_where).' ) ';
  } else {
    if($od_openmarket=="my") {
        $where[] = " od_writer != 'openmarket'";
    } else {
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
    $where_od_sales_manager[] = " mb_manager = '{$od_sales_manager[$i]}'";
  }
  if ( count($where_od_sales_manager) ) {
    $where[] = " ( " . implode(' OR ', $where_od_sales_manager) . " ) ";
  }
}

if ( $od_release_manager ) {
  $where_od_release_manager = array();
  for($i=0; $i<count($od_release_manager); $i++) {
    $where_od_release_manager[] = " ct_manager = '{$od_release_manager[$i]}' ";
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

if ($where) {
  $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from (
                  select
                    ct_id as cart_ct_id,
                    od_id as cart_od_id,
                    X.it_name,
                    it_admin_memo,
                    ct_status,
                    ct_move_date,
                    ct_delivery_num,
                    ct_manager,
                    ct_is_direct_delivery,
                    ct_direct_delivery_partner,
                    ct_barcode_insert,
                    ct_barcode_insert_not_approved,
                    ct_qty,
                    X.io_type,
                    ct_combine_ct_id,
                    ct_is_auto_combined,
                    Y.it_soldout,
                    Y.it_type1,
                    O.io_sold_out
                  from g5_shop_cart X 
                    left join g5_shop_item Y ON Y.it_id = X.it_id
                    left join g5_shop_item_option O ON O.it_id = X.it_id AND O.io_id = X.io_id

                ) B
                inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
                left join (select mb_id as mb_id_temp, mb_nick, mb_level, mb_manager, mb_type from {$g5['member_table']}) C
                on A.mb_id = C.mb_id_temp
                $sql_search
                group by cart_ct_id ";

foreach($order_steps as $order_step) {
  if (!$order_step['deliverylist']) continue;
  $order_by_steps[] = "'".$order_step['val']."'";
}

$order_by_step = implode(' , ', $order_by_steps);

$sql_common .= " ORDER BY FIELD(ct_status, " . $order_by_step . " ), B.ct_move_date desc, od_id desc ";

$sql = " select count(od_id) as cnt " . $sql_common;

$row = sql_fetch($sql);
$total_count = $row['cnt'];

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

$sql = "select count(od_id) as cnt, ct_status 
        from (
          select 
            ct_id as cart_ct_id,
            od_id as cart_od_id,
            ct_delivery_num,
            X.it_name,
            it_admin_memo,
            ct_status,
            ct_manager,
            ct_is_direct_delivery,
            ct_direct_delivery_partner,
            ct_barcode_insert,
            ct_barcode_insert_not_approved,
            ct_qty,
            io_type,
            ct_combine_ct_id,
            ct_is_auto_combined
          from {$g5['g5_shop_cart_table']} X 
          left join {$g5['g5_shop_item_table']} Y ON Y.it_id = X.it_id 
        ) B
        inner join {$g5['g5_shop_order_table']} A ON B.cart_od_id = A.od_id
        left join (select mb_id as mb_id_temp, mb_nick, mb_level, mb_manager, mb_type from {$g5['member_table']}) C
        on A.mb_id = C.mb_id_temp
        $sql_search2
        group by ct_status ";
$result = sql_query($sql);
while( $row = sql_fetch_array($result) ) {
  $cate_counts[$row['ct_status']] = $row['cnt'];
}

// $rows = $config['cf_page_rows'];
$rows = 50;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *,
            (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
           $sql_common
           limit $from_record, $rows ";
if ($od_status) {
  if ($show_all == 'Y' && $od_status == '출고준비') {
    $sql = preg_replace('/limit (.*)/i', '', $sql);
  }
}

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
          <th class=\"od_content\">상품요청</th>
          <th class=\"od_content\">배송요청사항</th>
          <th class=\"od_barNum\">바코드</th>
          <th class=\"od_price\">결제금액</th>
          <th>출고담당자</th>
          <th class=\"od_delivery_info\">배송정보</th>
          <th>출고완료일</th>
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

  // cart_table  기준 정렬
  $sql_ct = "select * from `g5_shop_cart` where `ct_id` ='".$order['cart_ct_id']."'";
  $result_ct = sql_fetch($sql_ct);
  
  //ct_count (order 기준 - 개수 )
  $sql_ct_count = "select count(ct_id) as ct_count from `g5_shop_cart` where `od_id` ='".$order['od_id']."'";
  $result_ct_count = sql_fetch($sql_ct_count);
  if($result_ct_count['ct_count'] > 1) {
    $ct_count =$result_ct_count['ct_count']-1;
    $ct_count='+'.$ct_count;
  } else {
    $ct_count="";
  }

  $opt_price = 0;
  if($result_ct['io_type'])
    $opt_price = $result_ct['io_price'];
  else
    $opt_price = $result_ct['ct_price'] + $result_ct['io_price'];
  $result_ct["opt_price"] = $opt_price;

  $ct_price = number_format($opt_price*$result_ct['ct_qty']+$result_ct['ct_sendcost']-$result_ct['ct_discount']);//가격
  if($result_ct['ct_status']=="보유재고등록"||$result_ct['ct_status']=="재고소진") { $ct_price = $result_ct['ct_status']; }
  $ct_it_name = $result_ct['it_name'];                                                                             //상품이름
  $ct_option = ($result_ct["ct_option"] == $result_ct['it_name']) ? "" : "(".$result_ct['ct_option'].")";         //옵션
  $ct_it_name = $ct_it_name.$ct_option;                                                                             //상품이름 + 옵션
  $ct_qty = $result_ct['ct_qty'];                                                                                   //개수
  $ct_status_text = $result_ct['ct_status'];                                                                           //상태
  $ct_it_id = $result_ct['it_id'];      
  $ct_ex_date = $result_ct['ct_ex_date'];      
  $ct_manager = $result_ct['ct_manager'];                                                                          //출고 담당자 아이디
  $prodMemo = $result_ct['prodMemo'];                                                                          //출고 담당자 아이디
  $ct_it_soldout = $order['it_soldout'];                                                                          //출고 담당자 아이디



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
  if($result_manager['mb_entNm']) {
    $mb_entNm = $result_manager['mb_entNm'];
  } else {
    $mb_entNm = $order['od_name'];
  }
    
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

  $od_price = number_format($order['od_cart_price'] + $order['od_send_cost'] + $order['od_send_cost2'] - $order['od_cart_discount'] - $order['od_cart_discount2'] - $order['od_sales_discount']);

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

  $cart_cnt = 0;
  $delivery_input_cnt = 0; // 입력
  $edi_success_cnt = 0; // 전송
  $edi_return_cnt = 0; // 송장

  if($order['cart']) {
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

      // 합포가 아니면
      if (!$cart['ct_combine_ct_id']) {
        $cart_cnt++;

        if($cart['ct_delivery_company'] === 'ilogen' && $cart['ct_delivery_cnt'] > 0) {
          $delivery_input_cnt++;
        }

        if($cart['ct_edi_result'] == '1') {
          $edi_success_cnt++;
        }

        if($cart['ct_delivery_num']) {
          $edi_return_cnt++;
        }
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

    $class_c1 = $prodBarNumCntBtnStatus = '';
    $prodBarNumCntBtnWord = $result_ct['ct_barcode_insert']."/".$result_ct['ct_qty'];
    if ($result_ct['ct_barcode_insert_not_approved'] > 0) {
      $prodBarNumCntBtnStatus = " approveRequired";
    } else if ($result_ct['ct_barcode_insert'] >= $result_ct['ct_qty']) {
      $prodBarNumCntBtnWord = "입력완료";
      $class_c1 = 'complete1';
      $prodBarNumCntBtnStatus = 'disable';
    }

    $deliveryCntBtnWord = " 입력 ({$delivery_input_cnt}/". $cart_cnt .")";
    $deliveryCntBtnWord .= ", 전송 ({$edi_success_cnt}/". $cart_cnt .")";
    $deliveryCntBtnWord .= ", 송장 ({$edi_return_cnt}/". $cart_cnt .")";

    //배송정보
    $deliveryCntBtnStatus = '';
    $deliveryCntBtnWord ="배송정보";
    $delivery_insert=0;
    $delivery_all_insert=0;
    $sql_od_ct = " select * from {$g5['g5_shop_cart_table']} where od_id = '".$result_ct['od_id']."' ";
    $result_od_ct = sql_query($sql_od_ct);
    while($row_od_ct = sql_fetch_array($result_od_ct)) {
      $delivery_all_insert++;
      if($row_od_ct['ct_combine_ct_id']||$row_od_ct['ct_delivery_num']) {
        $delivery_insert++;
      }
    }

    $class_c2="";
    $delivery_company="";
    $ct_delivery_num="";

    foreach($delivery_companys as $data){ 
        if($result_ct['ct_delivery_company'] == $data["val"] ){
            $delivery_company2=$data["name"];
        }
	}
	//직배송
    if($result_ct['ct_is_direct_delivery']){
        $deliveryCntBtnWord = '입력완료(직배송)';
        $class_c2 = 'complete2 ';
        $deliveryCntBtnStatus = ' disable ';
    }

    //합포
    if($result_ct['ct_combine_ct_id']){

        $sql_ctd ="select `ct_delivery_company`,`ct_delivery_num` from `g5_shop_cart` where `ct_id` = '".$result_ct['ct_combine_ct_id']."'";
        $result_ctd = sql_fetch($sql_ctd);

        foreach($delivery_companys as $data){ 
            if($result_ctd['ct_delivery_company'] == $data["val"] ){
                $delivery_company=$data["name"];
				$delivery_company2=$data["name"];
            }
        }

        if($result_ctd['ct_delivery_num']){
            $ct_delivery_num=$result_ctd['ct_delivery_num'];
        }else{
            $ct_delivery_num="합포-미입력";
        }

        $deliveryCntBtnWord = '입력완료('.$delivery_company.' '.$ct_delivery_num.')';
        $class_c2 = 'complete2 ';
        $deliveryCntBtnStatus = ' disable ';
    }

    //입력
    if($result_ct['ct_delivery_num']){

        foreach($delivery_companys as $data){ 
            if($result_ct['ct_delivery_company'] == $data["val"] ){
                $delivery_company=$data["name"];
				$delivery_company2=$data["name"];
            }
        }
        $ct_delivery_num=$result_ct['ct_delivery_num'];

        $deliveryCntBtnWord = '입력완료('.$delivery_company.' '.$ct_delivery_num.')';
        $class_c2 = 'complete2 ';
        $deliveryCntBtnStatus = ' disable ';
    }

  }  

  $important2_class = $order['od_important2'] ? 'on' : '';

  $ct_status = get_step($order['ct_status']);

  $od_delivery = get_delivery_step($order['od_delivery_type']);

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
    } else {
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
    } else {
        $sql_search = " where ct_status = '{$order['ct_status']}' ";
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
      (select *
      from {$g5['g5_shop_cart_table']} B
          inner join (select od_id as order_od_id ,od_del_yn from {$g5['g5_shop_order_table']}) A
          on B.od_id = A.order_od_id
          left join (select mb_id as mb_id_temp, mb_nick, mb_level, mb_type from {$g5['member_table']}) C
          on B.mb_id = C.mb_id_temp
          group by B.ct_id ) as ct_id
      $sql_search
    ";
    
    $total_result = sql_fetch($sql);
    $total_result['price'] = number_format( $total_result['ct_price'] + $total_result['ct_sendcost'] - $total_result['ct_discount']);

    $ct_status_info = get_step($order['ct_status']);
    $show_ct_status = $ct_status_info['chulgo'] ? $ct_status_info['name'] . '<span>(' . $ct_status_info['chulgo'] . ')</span>' : $ct_status_info['name'];

    $next_step = get_next_step($order['ct_status']);
    $prev_step = get_prev_step($order['ct_status']);

    if ( $next_step ) {
      $show_next_status = '<span class="btn large"><button id="change_next_step" data-next-step-val="'. $next_step['val'] .'">선택 '. $next_step['name'] .'단계로 변경</button></span>';
    } else {
      $show_next_status = '';
    }

    if ( $prev_step ) {
      $show_prev_status = '<span class="btn large"><button id="change_prev_step" data-prev-step-val="'. $prev_step['val'] .'">선택 '. $prev_step['name'] .'단계로 되돌리기</button></span>';
    } else {
      $show_prev_status = '';
    }

    $ret['data'] .= "
      <tr class=\"step\">
        <td colspan=\"8\" class=\"ltr-bg-step-{$ct_status_info['step']}\">
          {$show_ct_status}
        </td>
        <td colspan=\"6\" class=\"ltr-bg-step-{$ct_status_info['step']}\" style=\"text-align:right;\">
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

              <!--
              <span class=\"btn large\"><button name=\"delivery_edi_return\" class=\"delivery_edi_return\" id=\"25\" >송장리턴</button></span>
              -->

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
    $now_step = $order['ct_status'];
  }
  
  $auto_combined_text = '';
  if($order['ct_is_auto_combined']) {
    $auto_combined_text = '<span class="combine_done" style="color: #ff6600;">(합포완료)</span>';
  }


  $ret['data'] .= "
    <tr class=\"tr_{$order['cart_ct_id']} {$class_c1} {$class_c2} order_tr\" data-od-id=\"{$order['od_id']}\" data-href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\">
      <td align=\"left\" class=\"check_SoldOut\">
        <input type=\"checkbox\" name=\"od_id[]\" id=\"check_{$order['cart_ct_id']}\" value=\"{$order['cart_ct_id']}\" accumul_mark=\"Y\" data-delivery-company=\"{$delivery_company2}\">
        <label for=\"check_{$order['cart_ct_id']}\">
        ".(($now_step=="출고준비")&&($order['it_soldout']||$order['it_type1']||$order['io_sold_out'])?"<span style='font-weight: bold; color:#FF0000;'>품절</span>":"")."
        </label>
      </td>
      <td align=\"center\" class=\"od_time\">
        {$od_time}
      </td>
      <td align=\"left\" class=\"od_info\">
        <div class=\"order_info\">
          <div class=\"goods_info\">
            <div class=\"goods_name\">
              ".(($now_step=="출고준비")&&($order['it_soldout']||$order['it_type1']||$order['io_sold_out'])?"<span style='color:#FF0000;'>":"")."
              {$ct_it_name}
              ".(($now_step=="출고준비")&&($order['it_soldout']||$order['it_type1']||$order['io_sold_out'])?"</span>":"")."
            </div>
            <div class=\"goods_ea\">
              {$ct_qty}
            </div>
            <div class=\"order_num\">
              <a href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\">NO&nbsp;<span>{$order['od_id']}</span></a>
            </div>
          </div>
          <div class=\"buttons\">
            <div class=\"ct_count\">
              {$ct_count}
            </div>
            <!--
            <a href=\"javascript:printOrderView('{$order['od_id']}')\"><img src=\"/adm/shop_admin/img/printer.png\" align=\"absmiddle\"></a>
            <a href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\" target=\"_blank\"><span><img src=\"/adm/shop_admin/img/window.png\" align=\"absmiddle\"></span></a>
            -->
            <span class=\"btn-direct-open\" onclick=\"btn_direct_open(this);\"></span>
          </div>
        </div>
        <img src=\"/thema/eroumcare/assets/img/icon_link_orderlist.png\" class=\"icon_link\">
      </td>
      <td align=\"center\" class=\"od_name\">
        {$order['od_b_name']}
        <br/>
        {$mb_shorten_info}{$mb_entNm}
      </td>
      <td align=\"center\" class=\"od_content\">
        {$prodMemo}
      </td>
      <td align=\"center\" class=\"od_content\">
        {$order['od_memo']}
      </td>
      <td align=\"center\" class=\"od_barNum\">
        <a href='#' class='prodBarNumCntBtn{$prodBarNumCntBtnStatus}' data-option='{$result_ct["ct_option"]}'  data-it='{$ct_it_id}' data-stock='{$stock_insert}'  data-od='{$order["od_id"]}'>{$prodBarNumCntBtnWord}</a>
      </td>
      <td align=\"center\" class=\"od_price\">
        <b>{$ct_price}원</b>
      </td>
      <td align=\"center\">
        {$od_release_select}
      </td>
      <td align=\"center\" class=\"delivery_info od_delivery_info\">
        <a href='#' class='deliveryCntBtn{$deliveryCntBtnStatus} wide' data-id='{$order["od_id"]}'  data-ct='{$order["cart_ct_id"]}' >{$deliveryCntBtnWord}</a>
      </td>
      <td align=\"center\">
        {$ct_ex_date}
      </td>
      <td align=\"center\" class=\"od_step\">
        {$ct_status['name']}
        {$auto_combined_text}
      </td>
    </tr>
  ";
  // <input type=\"text\" name=\"od_release_date\" class=\"od_release_date\" data-od-id=\"{$order['od_id']}\" value=\"{$order['od_release_date']}\" />
}

$ret['last_step'] = $now_step;

header('Content-Type: application/json');
$json = json_encode(utf8ize($ret));
echo $json;
?>
