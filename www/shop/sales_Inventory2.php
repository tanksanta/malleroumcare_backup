<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
    return;
}

define("_ORDERINQUIRY_", true);

$od_pwd = get_encrypt_string($od_pwd);

// 회원인 경우
if ($is_member)
{
    $sql_common = " from {$g5['g5_shop_order_table']} where mb_id = '{$member['mb_id']}' AND od_del_yn = 'N' ";
}
else if ($od_id && $od_pwd) // 비회원인 경우 주문서번호와 비밀번호가 넘어왔다면
{
    $sql_common = " from {$g5['g5_shop_order_table']} where od_id = '$od_id' and od_pwd = '$od_pwd' AND od_del_yn = 'N' ";
}
else // 그렇지 않다면 로그인으로 가기
{
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderinquiry.php'));
}

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

?>
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css">
    <section id="stock" class="wrap stock-list">
        <h2>보유재고관리</h2>
        <ul class="stock-tab">
            <li><a href="<?=G5_SHOP_URL?>/sales_Inventory.php">판매재고<i class="num">(1022)</i></a></li>
            <li class="active"><a href="<?=G5_SHOP_URL?>/sales_Inventory2.php">대여재고<i class="num">(111)</i></a></li>
        </ul>
        <div class="inner">
            <div class="search-box">
                <select name="" id="">
                    <option value="">상품명</option>
                    <option value="">제품코드</option>
                </select>
                <div class="input-search">
                    <input type="text">
                    <button type="submit"></button>
                </div>
            </div>
            <div class="table-wrap">
                <ul>
                    <li class="head cb">
                        <span class="num">No.</span>
                        <span class="product">상품정보</span>
                        <span class="pro-num">제품코드</span>
                        <span class="stock">대여가능</span>
                        <span class="order">대여중</span>
                        <span class="price">급여가</span>
                    </li>
                    <!--반복-->
                    <a href="<?=G5_SHOP_URL?>/sales_Inventory_datail2.php">
                        <li class="list cb">
                            <span class="num">1</span>
                            <span class="product">
                                <div class="info">
                                    <div class="img">
                                        <img src="<?=G5_IMG_URL?>/ex01.png" alt="">
                                    </div>
                                    <div class="text">
                                        <div class="info-01">
                                            <i>[수동휠체어]</i>
                                            <p>HAL48(22D) </p>
                                            <p>유통/과세</p>
                                        </div>
                                        <!--mobile 용-->
                                        <div class="info-02">
                                            <span class="pro-num">A465465464</span>
                                            <span class="stock">5개</span>
                                            <span class="price">10,500원</span>
                                        </div>
                                    </div>
                                </div>
                            </span>
                            <!--pc 용-->
                            <span class="pro-num m_off">A5465465464</span>
                            <span class="stock m_off">5개</span>
                            <span class="order">판매완료</span>
                            <span class="price m_off">10,500원</span>
                        </li>
                    </a>
                    <a href="<?=G5_SHOP_URL?>/sales_Inventory_datail2.php">
                        <!--반복-->
                        <li class="list cb">
                            <span class="num">2</span>
                            <span class="product">
                                <div class="info">
                                    <div class="img">
                                        <img src="<?=G5_IMG_URL?>/ex01.png" alt="">
                                    </div>
                                    <div class="text">
                                        <div class="info-01">
                                            <i>[수동휠체어]</i>
                                            <p>HAL48(22D) </p>
                                            <p>유통/과세</p>
                                        </div>
                                        <div class="info-02">
                                            <span class="pro-num">M0465465464</span>
                                            <span class="stock">5개</span>
                                            <span class="price">10,500원</span>
                                        </div>
                                    </div>
                                </div>
                            </span>
                            <span class="pro-num m_off">M5465465464</span>
                            <span class="stock m_off">5개</span>
                            <span class="order">판매완료</span>
                            <span class="price m_off">10,500원</span>
                        </li>
                    </a>
                </ul>
            </div>
            <div class="pg-wrap">
                <div>
                    <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a>
                    <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a>
                    <a href="javascript:;" class="on">1</a>
                    <a href="javascript:;">2</a>
                    <a href="javascript:;">3</a>
                    <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a>
                    <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a>
                </div>
            </div>
        </div>
    </section>

<?php
if($is_inquiry_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>
  
  
  