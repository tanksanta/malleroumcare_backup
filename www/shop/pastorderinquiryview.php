<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/orderinquiryview.php');
    return;
}

$odSeq = isset($seq) ? preg_replace('/[^A-Za-z0-9\-_]/', '', strip_tags($seq)) : 0;

if (!$is_member) alert("직접 링크로는 주문서 조회가 불가합니다.\\n\\n주문조회 화면을 통하여 조회하시기 바랍니다.", G5_SHOP_URL);

$sql = "SELECT * FROM `fm_order` AS `fo`";
if(!$is_admin)
    $sql .= " INNER JOIN `fm_member` AS `fm` 
                ON `fm`.`member_seq` = `fo`.`member_seq` AND `fm`.`userid` = '{$member['mb_id']}'";
$sql .= " WHERE `fo`.`order_seq` = '$odSeq'";

$od = sql_fetch($sql);

if (!$od['order_seq']) {
    alert("조회하실 주문서가 없습니다.", G5_SHOP_URL);
}

// 주문상품
$item = array();

$sql = "SELECT 
            `foi`.*,
            `foio`.`price`,
            `foio`.`ori_price`,
            `foio`.`ea`,
            `foio`.`coupon_sale`,
            `foio`.`member_sale`,
            `foio`.`title1`,
            `foio`.`option1`,
            `foio`.`title2`,
            `foio`.`option2`,
            `foio`.`title3`,
            `foio`.`option3`,
            `foio`.`title4`,
            `foio`.`option4`,
            `foio`.`title5`,
            `foio`.`option5`,
            `foio`.`point`
		    FROM `fm_order_item` AS `foi` 
            LEFT JOIN `fm_order_item_option` AS `foio` 
                ON `foio`.`order_seq` = `foi`.`order_seq`
                AND `foio`.`item_seq` = `foi`.`item_seq`
            WHERE `foi`.`order_seq` = '{$odSeq}'";

$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
	$item[$i] = $row;
}


// Page ID
$pid = ($pid) ? $pid : 'inquiryview';
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
$is_inquiryview_sub = false;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '주문상세내역';

if($is_inquiryview_sub) {
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

// LG 현금영수증 JS
if($od['od_pg'] == 'lg') {
    if($default['de_card_test']) {
    echo '<script language="JavaScript" src="http://pgweb.uplus.co.kr:7085/WEB_SERVER/js/receipt_link.js"></script>'.PHP_EOL;
    } else {
        echo '<script language="JavaScript" src="http://pgweb.uplus.co.kr/WEB_SERVER/js/receipt_link.js"></script>'.PHP_EOL;
    }
}

// 주문내역 스킨 불러오기
include_once($skin_path.'/pastorderinquiryview.skin.php');

if($is_inquiryview_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>