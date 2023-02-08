<?php
$sub_menu = '400400';
include_once('./_common.php');

// error_reporting(E_ALL & ~E_NOTICE);
// ini_set("display_errors", 1);

if($auth_check = auth_check($auth[$sub_menu], "r"))
  json_response($auth_check);

$where = array();
$sel_field = get_search_string($sel_field);

// wetoz : naverpayorder - , 'od_naver_orderid' 추가
if( !in_array($sel_field, array('od_all', 'it_name', 'ct_option', 'it_admin_memo', 'it_maker', 'od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'od_naver_orderid', 'barcode', 'prodMemo', 'od_memo')) ){   //검색할 필드 대상이 아니면 값을 제거
  $sel_field = '';
}

$replace_table = array(
    'od_id' => 'c.od_id',
    'it_name' => 'c.it_name',
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
      $bacode_search .= $or." o.stoId like '%".$row_barcode['stoId']."%' ";
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
  if ($sel_field_add != "" && $sel_field_add != "od_all" && $sel_field_add != "barcode") {
    $where[] = "$sel_field_add like '%$search_add%'";
  }elseif($sel_field_add = "barcode"){
	$sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search_add."'";
      $result_barcode_search = sql_query($sql_barcode_search);
      $or = "";
      while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
        $bacode_search .= $or." o.stoId like '%".$row_barcode['stoId']."%' ";
        $or = "or";
      }
      if($bacode_search) {
        $where[] = "(".$bacode_search.")";
      } else {
        $where[] = "o.stoId like '%$search_add%'";
      }
  }
}

if ($search_add_add != "") {//사용유무 불확실
  $search_add_add = trim($search_add_add);
  if ($sel_field_add_add != "" && $sel_field_add_add != "od_all" && $sel_field_add_add != "barcode") {
    $where[] = "$sel_field_add_add like '%$search_add_add%'";
  }elseif($sel_field_add_add = "barcode"){
	$sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search_add_add."'";
      $result_barcode_search = sql_query($sql_barcode_search);
      $or = "";
      while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
        $bacode_search .= $or." o.stoId like '%".$row_barcode['stoId']."%' ";
        $or = "or";
      }
      if($bacode_search) {
        $where[] = "(".$bacode_search.")";
      } else {
        $where[] = "o.stoId like '%$search_add_add%'";
      }
  }
}

// 전체 검색
if ($sel_field == 'od_all' && $search != "") {
  $sel_arr = array('c.it_name', 'c.ct_option', 'it_admin_memo', 'it_maker', 'c.od_id', 'c.mb_id', 'mb_nick', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', /*'barcode',*/ 'prodMemo', 'od_memo');

  foreach ($sel_arr as $key => $value) {
    if ($value == "barcode") {
      $sql_barcode_search = "select `stoId` from `g5_barcode_log` where `barcode` = '" . $search . "'";
      $result_barcode_search = sql_query($sql_barcode_search);
      $or = "";
      while ($row_barcode = sql_fetch_array($result_barcode_search)) {
        $bacode_search .= $or . " o.stoId like '%" . $row_barcode['stoId'] . "%' ";
        $or = "or";
      }
      if ($bacode_search) {
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

// 전체 검색2
if ($sel_field_add == 'od_all' && $search_add != "") {
  $sel_arr = array('c.it_name', 'c.ct_option', 'it_admin_memo', 'it_maker', 'c.od_id', 'c.mb_id', 'mb_nick', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'ct_delivery_num', 'barcode','prodMemo', 'od_memo');

  foreach ($sel_arr as $key => $value) {
    if($value=="barcode") {
      $sql_barcode_search ="select `stoId` from `g5_barcode_log` where `barcode` = '".$search_add."'";
      $result_barcode_search = sql_query($sql_barcode_search);
      $or = "";
      while( $row_barcode = sql_fetch_array($result_barcode_search) ) {
        $bacode_search .= $or." o.stoId like '%".$row_barcode['stoId']."%' ";
        $or = "or";
      }
      if($bacode_search) {
        $sel_arr[$key] = $bacode_search;
      } else {
        $sel_arr[$key] = "o.stoId like '%$search_add%'";
      }
    } else {
      $sel_arr[$key] = "$value like '%$search_add%'";
    }
  }

  $where[] = "(".implode(' or ', $sel_arr).")";
}

// 출고준비 3일경과만 보기
if ($issue_1) {
  $where[] = " ( ct_status = '출고준비' and DATE(ct_move_date) <= (CURDATE() - INTERVAL 3 DAY ) ) ";
}

// 취소/반품요청 있는 주문만 보기
if ($issue_2) {
  $where[] = " ( select count(*) from g5_shop_order_cancel_request where approved = 0 and od_id = o.od_id ) > 0 ";
}

// 미재고 바코드 입력만 보기
if ($issue_3) {
  $where[] = " ( ct_barcode_insert_not_approved > 0 ) ";
}

// 바코드 입력완료, 미입력
if (gettype($ct_barcode_saved) == 'string' && $ct_barcode_saved !== '') {
  if ($ct_barcode_saved == 'saved')
    $where[] = " ( ct_barcode_insert > 0 ) ";
  else if ($ct_barcode_saved == 'none')
    $where[] = " ( ct_barcode_insert = 0 ) ";
}

// 배송정보 입력완료, 미입력
if (gettype($ct_delivery_saved) == 'string' && $ct_delivery_saved !== '') {
  if ($ct_delivery_saved == 'saved')
    $where[] = " ( CHAR_LENGTH(ct_delivery_num) > 6 ) ";
  else if ($ct_delivery_saved == 'none')
    $where[] = " ( ct_delivery_num IS NULL OR ct_delivery_num = '' ) ";
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

if ($od_partner_edit) {
  $where[] = " od_partner_edit = 1 ";
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

// 이카운트 엑셀 필터링
if (gettype($ct_is_ecount_excel_downloaded_saved) == 'string' && $ct_is_ecount_excel_downloaded_saved !== '') {
  if ($ct_is_ecount_excel_downloaded_saved == 'saved') {
    $where[] = " ct_is_ecount_excel_downloaded = '1' ";
  } else if ($ct_is_ecount_excel_downloaded_saved == 'none') {
    $where[] = " ct_is_ecount_excel_downloaded = '0' ";
  }
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

$where_count = $where;

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

$sql_count_search = '';
if ($where_count) {
  $sql_count_search = ' where '.implode(' and ', $where_count);
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

$sql_counts = "
  SELECT
    count(*) as cnt,
    ct_status,
    sum(
      case
        when io_type = 0
        then ct_price + io_price
        else ct_price
      end * ct_qty
    ) as ct_price,
    sum(ct_sendcost) as ct_sendcost,
    sum(ct_discount) as ct_discount
  {$sql_common}
  {$sql_count_search}
  GROUP BY
    ct_status
";
$result_counts = sql_query($sql_counts);
$cate_counts = [];
$total_info = [];
while($count = sql_fetch_array($result_counts)) {
  $cate_counts[$count['ct_status']] = $count['cnt'];
  $total_info[$count['ct_status']] = $count;
}

$sql_common .= $sql_search;

// 페이지네이트
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql, true);
$total_count = $row['cnt'];
//$rows = $config['cf_page_rows'];
$rows = 75;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

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
  limit $from_record, $rows
";
if ($click_status || $od_status) {
  if ($show_all == 'Y' && ($click_status == "준비" || $click_status == "출고준비" || $od_status == '준비' || $od_status == '출고준비')) {
    $sql = preg_replace('/limit (.*)/i', '', $sql);
  }
}
$result = sql_query($sql);

$orderlist = array();
while( $row = sql_fetch_array($result) ) {
  $orderlist[] = $row;
}

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
    <div class=\"table wide-table list-table-style\">
      <table>
        <thead>
          <tr>
            <th class=\"check\">선택</th>
            <th class=\"od_info\" style='width:20%'>주문정보</th>
            <th class=\"od_barNum\">바코드</th>
            <th class=\"od_name\">받는분(주문자)</th>
            <th class=\"od_content\">상품요청</th>
            <th class=\"od_content\">배송요청사항</th>
            <th class=\"od_price\">결제금액</th>
            <th class=\"od_sales_manager\">영업담당자</th>
            <th class=\"od_release_manager\">출고담당자</th>
            <!--<th class=\"od_ex_date\">출고완료일</th>-->
            <th class=\"od_content\">위탁</th>
            <th class=\"od_step\">주문상태</th>
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

// 출고담당자 목록
$ct_manager_list = [];
$result_ct_manager = sql_query("select b.`mb_name`, b.`mb_id` from `g5_auth` a left join `g5_member` b on (a.`mb_id`=b.`mb_id`) where a.`au_menu` = '400001'");
foreach($result_ct_manager as $ct_manager) {
  $ct_manager_list[] = $ct_manager;
}

// 영업담당자 목록
$sales_manager_table = [];
$result_sales_manager = sql_query("select mb_id, mb_name from g5_member where mb_level = 9");
foreach($result_sales_manager as $sales_manager) {
  $sales_manager_table[$sales_manager['mb_id']] = $sales_manager['mb_name'];
}

// 취소요청된 주문 목록
$cancel_order_table = [];
$result_cancel_order = sql_query("select od_id from g5_shop_order_cancel_request where approved = 0");
foreach($result_cancel_order as $cancel_order) {
  $cancel_order_table[$cancel_order['od_id']] = true;
}

foreach($orderlist as $order) {
  //ct_count (order 기준 - 개수 )
  $sql_ct_count = "select count(ct_id) as ct_count from `g5_shop_cart` where `od_id` ='".$order['od_id']."'";
  $result_ct_count = sql_fetch($sql_ct_count);
  if($result_ct_count['ct_count'] > 1) {
    $ct_count = $result_ct_count['ct_count']-1;
    $ct_count = '+'.$ct_count;
  } else {
    $ct_count="";
  }

  $opt_price = 0;
  if($order['io_type'])
    $opt_price = $order['io_price'];
  else
    $opt_price = $order['ct_price'] + $order['io_price'];
  $order["opt_price"] = $opt_price;

  $ct_price = number_format($opt_price*$order['ct_qty']+$order['ct_sendcost']-$order['ct_discount']); //가격
  if(in_array($order['ct_status'], ['보유재고등록', '재고소진'])){ $ct_price = $order['ct_status'];}
  $ct_it_name = $order['it_name']; //상품이름
  $ct_option = (str_replace(' ', '', $order["ct_option"]) == str_replace(' ', '', $order['it_name'])) ? "" : "(".$order['ct_option'].")"; //옵션
  $ct_it_name = $ct_it_name.$ct_option; //상품이름 + 옵션
  $ct_qty = $order['ct_qty']; //개수
  $ct_status_text = $order['ct_status']; //상태
  $ct_it_id = $order['it_id'];
  $ct_ex_date = $order['ct_ex_date']; //출고완료일
  $ct_manager = $order['ct_manager']; //출고 담당자 아이디
  $prodMemo = $order['prodMemo'];

  //출고담당자 select
  $od_release_select="";
  $od_release_select = '<select class="ct_manager" data-ct-id="'.$order['ct_id'].'" style="width:70px">';
  $od_release_select .= '<option value="미지정">미지정</option>';
  foreach($ct_manager_list as $row_m) {
    $selected="";
    if($ct_manager == $row_m['mb_id']){ $selected="selected"; }
    $od_release_select .='<option value="'.$row_m['mb_id'].'" '.$selected.'>'.$row_m['mb_name'].'('.$row_m['mb_id'].')</option>';
  }
  $od_release_select .='</select>';

  //사업소명
  if($order['mb_entNm']){
    $mb_entNm = $order['mb_entNm'];
  } else {
    $mb_entNm = $order['od_name'];
  }

  $sale_manager = $sales_manager_table[$order['mb_manager']];
  if (!$sale_manager) {
    $sale_manager = $sales_manager_table[$order['od_sales_manager']];
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

  $ct_sub_status_text = '';
  if ($order['ct_is_ecount_excel_downloaded']) {
    $ct_sub_status_text .= "<br><span id='ecount_excel_done' class='excel_done' data-ct-id='{$order['ct_id']}' style='color: #77933c'>이카운트 : 엑셀받기 완료</span>";
  }
  if ($order['refund_status']) {
    $ct_sub_status_text .= "<br><span style='color:red'>({$order['refund_status']})</span>";
  }
  $stock_insert=1;


  //보유재고등록 - > 보유재고로 표시


  // 취소 요청 체크
  $is_order_cancel_requested = "";
  if ($cancel_order_table[$order['od_id']]) {
    $is_order_cancel_requested = "cancel_requested";
  }

  $od_time = substr($order['od_time'],0,10) . ' ('. substr($order['od_time'],11,8) .')';

  if($order['ct_move_date']) {
    $ct_move_time = strtotime($order['ct_move_date']);
    $od_receipt_time = date('Y-m-d', $ct_move_time).' ('.date('H:i:s', $ct_move_time).')';
  } else {
    $od_receipt_time = '';
  }

  $mb_shorten_info = $order['od_name'] ? samhwa_get_mb_shorten_info_by_mb($order) : '';

  $od_receipt_name = $order['od_deposit_name'] ? $order['od_deposit_name'] . '<br>' : '';
  $od_receipt_name .= '(' . $order['od_settle_case'] . ')' . substr($order['od_bank_account'],0,12);

  $important_class = $order['od_important'] ? 'on' : '';

  $prodStockqty = 0;
  $prodDelivery = 0;

  if($order['prodSupYn'] == 'Y') {
    $prodStockqty = $order["ct_stock_qty"];
    $prodDelivery = $order["ct_qty"] - $order["ct_stock_qty"];
  }

  if($order["od_delivery_yn"] == "N"){
    $prodDelivery = 0;
  }

  $prodDeliveryMemo = ($prodDelivery) ? "(배송 : {$prodDelivery}개)" : "<span style='color: #DC3333;'>(배송 없음)</span>";
  $prodStockqtyMemo = ($prodStockqty) ? " (재고소진 {$prodStockqty})" : "";

  if(!$order['ct_barcode_insert']) {
    $order['ct_barcode_insert'] = 0;
  }
  $prodBarNumCntBtnStatus = '';
  $prodBarNumCntBtnWord = $order['ct_barcode_insert']."/".$order['ct_qty'];

  if ($order['ct_barcode_insert_not_approved'] > 0) {
    $prodBarNumCntBtnStatus = " approveRequired";
  } else if ($order['ct_barcode_insert'] >= $order['ct_qty']) {
    $prodBarNumCntBtnWord = '입력완료';
    $prodBarNumCntBtnStatus = " disable";
  }

  if ( $now_step != $order['ct_status'] ) {
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

    $excel_btn = "";

    $total_result = $total_info[$order['ct_status']];
    $total_result['price'] = number_format( $total_result['ct_price'] + $total_result['ct_sendcost'] - $total_result['ct_discount']);
    if($ct_status_info['name']=="재고소진"||$ct_status_info['name']=="보유재고등록"){ $status_info = "총 {$total_result['cnt']}건";}else{$status_info = "총 {$total_result['cnt']}건 / 합계: ₩ {$total_result['price']}원";}

    $ret['data'] .= "
      <tr class=\"step\">
        <td colspan=\"8\" class=\"ltr-bg-step-{$ct_status_info['step']}\">
          {$show_ct_status}
        </td>
        <td colspan=\"6\" class=\"ltr-bg-step-{$ct_status_info['step']}\" style=\"text-align:right;\">
          {$status_info} 
        </td>
      </tr>
      <tr class=\"btns\">
        <td colspan=\"16\">
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
    $now_step = $order['ct_status'];
  }

  $important2_class = $order['od_important2'] ? 'on' : '';

  $pay_status = get_pay_step($order['od_pay_state']);
  $od_pay_state = '<span class="" style="color:'. $pay_status['color'] .'">'.$pay_status['name'] .'</span>';

  $od_ex_date = $order['od_ex_date'] === '0000-00-00' ? '-' : $order['od_ex_date'];

  //상품준비, 출고준비, 값 0000-00-00 이면, 출고예정
  if($ct_ex_date === '0000-00-00'){
    $ct_ex_date = "출고예정";
  }

  $od_delivery_type = get_delivery_step($order['od_delivery_type']);

  $show_od_delivery_type = $od_delivery_type['name'];
  if ($od_delivery_type['type'] == 'delivery') {
    $show_od_delivery_type .= "<br>" . ( $order['od_edi_result'] == '1' ? '<span style="color:#236ec6">전송</span>' : '<span style="color:#c72102">미전송</span>' );
  }

  $od_release_out = '-';

  $direct_delivery_partner_text = $order['ct_direct_delivery_partner'] ? " ({$order['ct_direct_delivery_partner']})" : '(미지정)';
  switch($order['ct_is_direct_delivery']) {
    case 1:
      $direct_delivery_text = '배송'.$direct_delivery_partner_text;
      break;
    case 2:
      $direct_delivery_text = '설치'.$direct_delivery_partner_text;
      break;
    default:
      $direct_delivery_text = '';
  }
  if($order['ct_delivery_num'] && $order['ct_direct_delivery_partner']) {
    $delivery_company_name = '';
    foreach($delivery_companys as $company) {
      if($company['val'] == $order['ct_delivery_company']) {
        $delivery_company_name = $company['name'];
        break;
      }
    }
    $direct_delivery_text .= "<br>[{$delivery_company_name}] {$order['ct_delivery_num']}";

    // 위탁배송상품 출고예정일 출력
    if($order['ct_direct_delivery_date']) {
      $direct_delivery_date = date('Y-m-d (H시)', strtotime($order['ct_direct_delivery_date']));
      $ct_ex_date = "예정 : {$direct_delivery_date}<br>" . $ct_ex_date;
    }
  }
  if($order['ct_is_delivery_excel_downloaded']) {
    $direct_delivery_text .= "<br><span id='excel_done' class='excel_done' data-ct-id='{$order['ct_id']}' style='color: #FF6600'>엑셀 다운로드 완료</span>";
  }
  if($order['ct_send_direct_delivery']) {
    $send_direct_delivery = '발주전송';
    if ($order['ct_send_direct_delivery_fax'] && $order['ct_send_direct_delivery_email']) {
      $send_direct_delivery .= '(Fax,Email)';
    }
    else if ($order['ct_send_direct_delivery_fax'] && !$order['ct_send_direct_delivery_email']) {
      $send_direct_delivery .= '(Fax)';
    }
    else if (!$order['ct_send_direct_delivery_fax'] && $order['ct_send_direct_delivery_email']) {
      $send_direct_delivery .= '(Email)';
    }
    $direct_delivery_text .= "<br><span id='send_direct_delivery_done' class='send_direct_delivery_done' data-ct-id='{$order['ct_id']}' style='color: #FF6600'>{$send_direct_delivery}</span>";
  }

  // 출고준비로 변경 후 3일 지난 주문 강조
  if($order['ct_status'] === '출고준비') {
    $today = new DateTime(date('Y-m-d'));
    $target = new DateTime(date('Y-m-d', strtotime($order['ct_move_date'])));
    $daysbetween = intval($today->diff($target)->format('%a'));

    if($daysbetween >= 3) {
      $ct_status_text .= '<br><span style="color: red">3일경과</span>';
    }
  }

  // 파트너 상품수정 표시
  $partner_edit_text = '';
  if($order['od_partner_edit']) {
    $partner_edit_text = '<div style="margin-top: 5px; color: #FF6600">*파트너 상품수정</div>';
  }

  $ret['data'] .= "
    <tr class=\"{$is_order_cancel_requested} tr_{$order['od_id']} order_tr\" data-od-id=\"{$order['od_id']}\" data-href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\">
      <td align=\"center\" class=\"check\">
        <input type=\"checkbox\" name=\"od_id[]\" id=\"check_{$order['ct_id']}\" value=\"{$order['ct_id']}\" accumul_mark=\"Y\">
        <label for=\"check_{$order['ct_id']}\">&nbsp;</label>
      </td>
      <td align=\"left\" class=\"od_info\">
        <div class=\"order_info\">
          <div class=\"goods_info\">
            <div class=\"goods_name\">
              {$ct_it_name}(".($ct_qty)."개)
            </div>
            <div class=\"order_num\">
              주문일시 : {$od_time}<br>
              변경일시 : {$od_receipt_time}<br>
              <a href=\"./samhwa_orderform.php?od_id={$order['od_id']}&sub_menu={$sub_menu}\">주문번호&nbsp;<span>({$order['od_id']})</span></a>
            </div>
            {$partner_edit_text}
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
          <img src=\"/thema/eroumcare/assets/img/icon_link_orderlist.png\" class=\"icon_link\">
        </div>
      </td>
      <td align=\"center\" class=\"od_barNum\">
        <a href='#' class='prodBarNumCntBtn{$prodBarNumCntBtnStatus}' data-option='{$order["ct_option"]}'  data-it='{$ct_it_id}' data-stock='{$stock_insert}'  data-od='{$order["od_id"]}'>{$prodBarNumCntBtnWord}</a>
      </td>
      <td align=\"center\" class=\"od_name\">
        <a href='#' data-mb-id='{$order['mb_id']}' class='open_member_pop'>
          {$order['od_b_name']}
          <br/>
          {$mb_shorten_info}{$mb_entNm}
        </a>
      </td>
      <td align=\"center\" class=\"od_content\">
        {$prodMemo}
      </td>
      <td align=\"center\" class=\"od_content\">
        {$order['od_memo']}
      </td>
      <td align=\"center\" class=\"od_price\">
        <b>{$ct_price}</b>
      </td>
      <td align=\"center\" class=\"od_sales_manager\">
        {$sale_manager}
      </td>
      <td align=\"center\" class=\"od_release_manager\">
        {$od_release_select}
      </td>
      <!--
      <td align=\"center\" class=\"od_ex_date\">
        {$ct_ex_date}
      </td>
      -->
      <td align=\"center\" class=\"od_content od_direct_delivery\">
        {$direct_delivery_text}
      </td>
      <td align=\"center\" class=\"od_step\">
        {$ct_status_text}
        {$ct_sub_status_text}
      </td>
    </tr>
  ";

  $ret['last_step'] = $now_step;
}

$json = json_encode(utf8ize($ret));
header('Content-Type: application/json');
echo $json;
?>
