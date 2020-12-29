<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$od['cart'] = array();
$sql = "SELECT * FROM g5_shop_cart WHERE od_id = '{$od['od_id']}'";
$cart_result = sql_query($sql);
while ( $row2 = sql_fetch_array($cart_result) ) {
    $od['cart'][] = $row2;
}


if( count($od['cart']) > 1 ) {
    $od_cart_count = ' 외 ' . (count($od['cart']) - 1) .'개';
}else{
    $od_cart_count = '';
}

$od['goodsName']		= $od['cart'][0]['it_name'] . $od_cart_count;

$delivery = get_delivery_step($od['od_delivery_type']);

// 받는 사람 정보
$receipt_member = get_member($od['mb_id']);

// 출고담당자 정보
$chulgo_member = get_member($od['od_release_manager']);

// 송하인 선택
if ( $od['od_delivery_receiptperson'] == '0' ) {
    $od['sndCustNm']		= "삼화에스앤디";

    $od['sndCustAddr1']	= "서울특별시 성동구 성수이로10길 14";
    $od['sndCustAddr2']	= "에이스하이엔드성수타워 B103호";
    $od['sndTelNo']		= "02-2267-8080";
    $od['sndHandNo']		= "02-2267-8080";
    $od['chulgo_member'] = $chulgo_member['mb_name'];
}else{
    $od['sndCustNm']		= $od['od_name'];

    $od['sndCustAddr1']	= $od['od_addr1'];
    $od['sndCustAddr2']	= $od['od_addr2'];
    $od['sndTelNo']		= $od['od_tel'] ? $od['od_tel'] : $od['od_hp'];
    $od['sndHandNo']		= $od['od_hp'] ? $od['od_hp'] : $od['od_tel'];
    $od['chulgo_member'] = $od['sndCustNm'];
}

if ($delivery['type'] == 'gdhuamul') {
    $od['od_b_addr1'] = '';
}

$filename = './pop.order.delivery.print.' . $delivery['print_page_name'] . '.php';

if ( file_exists($filename) ) {
    include_once('./pop.head.php');
    include_once($filename);
    include_once('./pop.tail.php');
    exit;
}else{
    die('잘못된 페이지입니다.');
}


?>