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

<section id="stock" class="wrap" >
        <div class="list-more"><a href="<?=G5_SHOP_URL?>/sales_Inventory2.php">목록</a></div>
        <h2>판매 재고 상세</h2>
        <div class="stock-view">
            <div class="product-view">
                <div class="pro-image">
                    <img src="<?=G5_IMG_URL?>/big_ex01.png" alt="">
                </div>
                <div class="info-list">
                    <ul>
                        <li>
                            <span>유통</span>
                            <span>이로움</span>
                        </li>
                        <li>
                            <span>세금</span>
                            <span>과세</span>
                        </li>
                        <li>
                            <span>제품코드</span>
                            <span>M201481402</span>
                        </li>
                        <li>
                            <span>가격</span>
                            <span>15,000원</span>
                        </li>
                    </ul>
                    <div class="info-btn">
                        <div>
                            <a href="javascript:;" class="btn-01">신규재고등록</a>
                            <a href="javascript:;" class="btn-02">상세정보</a>
                        </div>
                        <p>*보유 재고 등록 가능</p>
                    </div>
                </div>
            </div>
            <div class="inner">
                <div class="table-wrap">
                    <h3>보유 재고</h3>
                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="date">입고일</span>
                            <span class="order">판매</span>
                        </li>

                        <!--반복-->
                        <li class="list cb">
                            <!--pc용-->
                            <span class="num">1</span>
                            <span class="product m_off">미끄럼방지양말(흰색)</span>
                            <span class="pro-num m_off"><b>123456789</b></span>
                            <span class="date m_off">2021-03-03</span>
                            <span class="order m_off">
                                <a href="javascript:;">수급자선택</a>
                            </span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product">미끄럼방지양말(흰색)</span>
                                    <span class="pro-num"><b>123456789</b></span>
                                </div>
                                <div class="info-m">
                                    <span class="date">2021-03-03</span>
                                    <span class="order">
                                        <a href="javascript:;">수급자선택</a>
                                    </span>
                                </div>
                            </div>
                        </li>
                        <!--반복-->
                        <li class="list cb">
                            <!--pc용-->
                            <span class="num">2</span>
                            <span class="product m_off">미끄럼방지양말(흰색)</span>
                            <span class="pro-num m_off"><b>123456789</b></span>
                            <span class="date m_off">2021-03-03</span>
                            <span class="order m_off">
                                <a href="javascript:;">수급자선택</a>
                            </span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product">미끄럼방지양말(흰색)</span>
                                    <span class="pro-num"><b>123456789</b></span>
                                </div>
                                <div class="info-m">
                                    <span class="date">2021-03-03</span>
                                    <span class="order">
                                        <a href="javascript:;">수급자선택</a>
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="table-wrap table-wrap2">
                    <h3>판매 완료</h3>
                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="name">수급자</span>
                            <span class="date">종료일</span>
                            <span class="check">계약서</span>
                        </li>
                        <!--반복-->
                        <li class="list cb">
                             <!--pc용-->
                            <span class="num">1</span>
                            <span class="product m_off">미끄럼방지양말(흰색)</span>
                            <span class="pro-num m_off"><b>123456789</b></span>
                            <span class="name m_off">홍길동</span>
                            <span class="date m_off">2021-03-03</span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product">미끄럼방지양말(흰색)</span>
                                    <span class="pro-num"><b>123456789</b></span>
                                </div>
                                <div class="info-m">
                                    <span class="name">홍길동</span>
                                    <span class="date">2021-03-03</span>
                                </div>
                            </div>
                            <span class="check">
                                <a href="javascript:;">확인</a>
                            </span>
                        </li>
                        <!--반복-->
                        <li class="list cb">
                            <!--pc용-->
                           <span class="num">2</span>
                           <span class="product m_off">미끄럼방지양말(흰색)</span>
                           <span class="pro-num m_off"><b>123456789</b></span>
                           <span class="name m_off">홍길동</span>
                           <span class="date m_off">2021-03-03</span>
                           <!--mobile용-->
                           <div class="list-m">
                               <div class="info-m">
                                   <span class="product">미끄럼방지양말(흰색)</span>
                                   <span class="pro-num"><b>123456789</b></span>
                               </div>
                               <div class="info-m">
                                   <span class="name">홍길동</span>
                                   <span class="date">2021-03-03</span>
                               </div>
                           </div>
                           <span class="check">
                               <a href="javascript:;">확인</a>
                           </span>
                       </li>
                       
                    </ul>
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
  
  
  