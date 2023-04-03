<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
    return;
}

define("_ORDERINQUIRY_", true);

$od_pwd = get_encrypt_string($od_pwd);

if( !in_array($sel_field, array('all', 'o.od_id', 'i.it_name', 'o.od_b_name', 'o.od_penId')) ){   //검색할 필드 대상이 아니면 값을 제거
	$sel_field = '';
}
$search = get_search_string($search);

// 회원인 경우
if ($is_member)
{
    $sql_common = " from {$g5['g5_shop_order_table']} as o
		LEFT JOIN g5_shop_cart as c ON o.od_id = c.od_id
		LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
		where o.mb_id = '{$member['mb_id']}' AND od_del_yn = 'N' ";
}
else if ($od_id && $od_pwd) // 비회원인 경우 주문서번호와 비밀번호가 넘어왔다면
{
    $sql_common = " from {$g5['g5_shop_order_table']} as o
		LEFT JOIN g5_shop_cart as c ON o.od_id = c.od_id
		LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
		where od_id = '$od_id' and od_pwd = '$od_pwd' AND od_del_yn = 'N' ";
}
else // 그렇지 않다면 로그인으로 가기
{
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderinquiry.php'));
}

# 210322 주문+재고
$order_stocks = [];
$order_stocks[0]["name"] = "주문";
$order_stocks[0]["val"] = "Y";
$order_stocks[1]["name"] = "재고";
$order_stocks[1]["val"] = "N";

# 210322 검색
# 210412 주문 hide
$sql_search = "";
$sql_search = " and `od_hide_control` != '1'";
if($_GET["s_date"]){
	$sql_search .= " AND od_time >= '{$_GET["s_date"]} 00:00:00' ";
}

if($_GET["e_date"]){
	$sql_search .= " AND od_time <= '{$_GET["e_date"]} 23:59:59' ";
}


if($_GET["od_type0"]=="0") { $where[] = " od_type = '0' ";}
if($_GET["od_type1"]=="1") { $where[] = " od_type = '1' ";}


/*
$search_od_status = "전체 상태";
if($_GET["od_status"]){
	$sql_search .= " AND od_status = '{$_GET["od_status"]}' ";
	
	for($i = 0; $i < count($order_steps); $i++){
		if($order_steps[$i]["val"] == $_GET["od_status"]){
			$search_od_status = $order_steps[$i]["name"];
		}
	}
}
*/

$search_od_stock = "주문+재고";
if($_GET["od_stock"]){
	$sql_search .= " AND recipient_yn = '{$_GET["od_stock"]}' ";
	
	for($i = 0; $i < count($order_stocks); $i++){
		if($order_stocks[$i]["val"] == $_GET["od_stock"]){
			$search_od_stock = $order_stocks[$i]["name"];
		}
	}
}

if ($_GET["ct_release"] == 'true') {
  $search_od_stock = "출고";
  $order_by = 'order by c.ct_ex_date desc, o.od_id asc';
} else {
  $order_by = 'order by o.od_id desc';
}

// 검색
if ($sel_field && $search) {
	if ($sel_field === 'all') {
		$where[] = "(
			o.od_id like '%$search%' OR
			i.it_name like '%$search%' OR
			o.od_b_name like '%$search%' OR
			o.od_penId like '%$search%'
		)";
	} else {
		$where[] = " $sel_field like '%$search%' ";
	}
}


if ($ct_status) {
	// $sql_search = $sql_search ? $sql_search : ' 1 = 1 ';
	if ( $ct_status === '주문무효') {
		$where[] = " c.ct_status IN ('주문무효', '취소') ";	
	} else {
		$where[] = " c.ct_status = '{$ct_status}' ";
	}
} else {
	// $where[] = " c.ct_status IN ('준비', '출고준비', '배송', '완료') ";
	$where[] = " c.ct_status NOT IN ('주문무효', '취소') ";
}

if ($where) {
	$where_query = $sql_search ? ' and ' : ' where ';
  $sql_search = $sql_search . $where_query . implode(' and ', $where);
}


// 테이블의 전체 레코드수만 얻음
$item_wait_count = 0;
$delivery_ing_count = 0;
$total_count = 0;
$sql = " select * " . $sql_common . " {$sql_search} ";
$sql .= " GROUP BY o.od_id ";

$result = sql_query($sql);
for($i = 0; $row = sql_fetch_array($result); $i++){
	if($row["od_status"] == "준비"){
		$item_wait_count++;
	}
	
	if($row["od_status"] == "배송"){
		$delivery_ing_count++;
	}
	
	$total_count++;
}

// 비회원 주문확인시 비회원의 모든 주문이 다 출력되는 오류 수정
// 조건에 맞는 주문서가 없다면
//if ($total_count == 0)
//{
////    goto_url(G5_SHOP_URL);	
//    if ($is_member) // 회원일 경우는 메인으로 이동
//        alert('주문이 존재하지 않습니다.', G5_SHOP_URL);
//    else // 비회원일 경우는 이전 페이지로 이동
//        alert('주문이 존재하지 않습니다.');
//}

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 비회원 주문확인의 경우 바로 주문서 상세조회로 이동
if (!$is_member)
{
    $sql = " select od_id, od_time, od_ip from {$g5['g5_shop_order_table']} where od_id = '$od_id' and od_pwd = '$od_pwd' AND od_del_yn = 'N' ";
    $row = sql_fetch($sql);
    if ($row['od_id']) {
        $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
        set_session('ss_orderview_uid', $uid);
        goto_url(G5_SHOP_URL.'/orderinquiryview.php?od_id='.$row['od_id'].'&amp;uid='.$uid);
    }
}

$list = array();


$sql = "
SELECT
  count(*) as cnt,
  ct_status
FROM
  g5_shop_cart c
LEFT JOIN
  g5_shop_order o ON c.od_id = o.od_id
WHERE
  c.mb_id = '{$member['mb_id']}' AND
	o.od_del_yn = 'N'
GROUP BY
  ct_status
";

$count_result = sql_query($sql);

$list_count = array(
	'준비' => 0,
	'출고준비' => 0,
	'배송' => 0,
	'완료' => 0
);
while($row = sql_fetch_array($count_result)) {
	$list_count[$row['ct_status']] = $row['cnt'];
}


$limit = " limit $from_record, $rows ";
$sql = " select o.*, i.it_model, i.it_name, c.ct_id, c.ct_status, c.ct_ex_date, c.ct_direct_delivery_date, c.ct_delivery_num, c.ct_delivery_company
		   from {$g5['g5_shop_order_table']} as o 
  		  LEFT JOIN g5_shop_cart as c ON o.od_id = c.od_id
		  LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
		  where o.mb_id = '{$member['mb_id']}'
		  AND o.od_del_yn = 'N'
		  {$sql_search}
		  GROUP BY o.od_id
		  {$order_by}
		  $limit ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
	$uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);

	// switch($row['od_status']) {
	// 	case '주문':
	// 		$od_status = '입금확인중';
	// 		break;
	// 	case '입금':
	// 		$od_status = '입금완료';
	// 		break;
	// 	case '준비':
	// 		$od_status = '상품준비중';
	// 		break;
	// 	case '배송':
	// 		$od_status = '상품배송';
	// 		break;
	// 	case '완료':
	// 		$od_status = '배송완료';
	// 		break;
    //     case '입고대기':
    //         $od_status = '입고대기';
    //         break;
    //     case '입고확인':
    //         $od_status = '입고확인';
    //         break;
    //     case '검수확인':
    //         $od_status = '검수확인';
    //         break;
    //     case '환불완료':
    //         $od_status = '환불완료';
    //         break;
	// 	default:
	// 		$od_status = '주문취소';
	// 		break;
	// }
	
	$od_status = get_step($row['od_status']);
	$od_status = $od_status['name'];
	
	$list[$i] = $row;
	$list[$i]['od_href'] = G5_SHOP_URL.'/orderinquiryview.php?od_id='.$row['od_id'].'&amp;uid='.$uid;
	$list[$i]['od_status'] = $od_status;
    
    $sql = "select *
            from g5_shop_order_cancel_request
            where od_id = '{$row['od_id']}' and approved = 0";
    
    $cancel_request_row = sql_fetch($sql);
    
    if ($cancel_request_row['od_id']) {
        $list[$i]['od_status'] = $cancel_request_row['request_status'];
    }

	// 합계금액 계산
	$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
					SUM(ct_qty) as qty,
					SUM(ct_discount) as discount,
					SUM(ct_send_cost) as sendcost
				from {$g5['g5_shop_cart_table']}
				where od_id = '{$row['od_id']}'";
	$sum = sql_fetch($sql);
	$list[$i]['od_total_price'] = $sum['price'] - $sum['discount'] + $row['od_send_cost'] + $row['od_send_cost2'];
}

$search_url = $_SERVER['SCRIPT_NAME'].'?'.$qstr."&amp;od_stock={$_GET["od_stock"]}&ct_release={$_GET["ct_release"]}&amp;od_status={$_GET["od_status"]}&amp;s_date={$_GET["s_date"]}&amp;e_date={$_GET["e_date"]}&amp;sel_field={$sel_field}&amp;search={$search}&amp;od_type0={$od_type0}&amp;od_type1={$od_type1}";
$write_pages = G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'];
$list_page = "{$search_url}&amp;ct_status={$_GET["ct_status"]}&amp;page=";

// Page ID
$pid = ($pid) ? $pid : 'inquiry';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

// 스킨 체크
list($order_skin_path, $order_skin_url) = apms_skin_thema('shop/order', $order_skin_path, $order_skin_url); 

// 스킨설정
$wset = array();
if($skin_row['order_'.MOBILE_.'set']) {
	$wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
}

// 데모
if($is_demo) {
	@include ($demo_setup_file);
}

// 설정값 불러오기
$is_inquiry_sub = false;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '주문내역조회';

if($is_inquiry_sub) {
	include_once(G5_PATH.'/head.sub.php');
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
	include_once('./_head.php');
}

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

// 셋업
$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
	$setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
}

include_once($skin_path.'/orderinquiry.skin.php');

if($is_inquiry_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>