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

<!-- 내용 -->
<title>판매재고목록</title>
<section   class="wrap  ">
    <div class="sub_section_tit">청구/전자문서관리</div>
    <ul class="list_tab">
        <li class="active"><a href="<?=G5_SHOP_URL?>/claim_manage.php">청구관리</a></li>
        <li ><a href="<?=G5_SHOP_URL?>/electronic_manage.php">전자문서관리<span class="red_info">미작성: 1건</span></a></li>
    </ul>
     <div class="inner">
     	<div class="date_wrap">
     		<div class="date_this">
     			<a href="#">이번달</a>
     		</div>
     		<div class="date_selected">
     			<a href="#">◀ 지난달</a>
     			<select name="" id="">
     				<option selected>2021년 3월</option>
     				<option>2021년 2월</option>
     				<option>2021년 1월</option>
     			</select>
     			<a href="#" class="disabled">다음달 ▶</a>
     		</div>
     		
     	</div>
     	
     	<div class="search_box">
            <select name="searchtype" id="">
                <option value="1">수급자명</option>
                <option value="2">요양인정번호</option>
            </select>
            <div class="input_search">
                <input name="searchtypeText" value="<?=$_GET["searchtypeText"]?>" type="text">
                <button  type="submit"></button>
            </div>
        </div>
 		<div class="r_btn_area">
 			<span>최신검증일 : 2021-03-01 13:35</span>
 			<a href="#" class="btn_nhis">건보 자료 업로드</a>
 			<a href="#">엑셀다운로드</a>
 		</div>
 		<div class="list_box">
 			<div class="table_box">
 			<table >
				 <tr>
				 	<th>No.</th>
				 	<th>수급자 정보</th>
				 	<th>급여시작일</th>
				 	<th>급여비용총액</th>
				 	<th>본인부담금</th>
				 	<th>청구액</th>
				 	<th>검증상태</th>
				 	<th>금액변경</th>
				 </tr>
				 <tr>
				 	<td>5</td>
				 	<td><a href="#">홍길동(L2233321333 / 3등급 /기초0%)</a></td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_r">200,000원</td>
				 	<td class="text_r">10,000원</td>
				 	<td class="text_r">210,000원</td>
				 	<td class="text_c">정상</td>
				 	<td class="text_c"><a href="#" class="w_100">변경</a></td>
				 </tr>
				 <tr class="bg_red">
				 	<td>4</td>
				 	<td>
				 		<a href="#">홍길동(L2233321333 / 3등급 /기초0%)</a>
				 		<p class="text_red">홍**(L22333**** / 2등급 /기초0%)</p>
				 	</td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_r">
				 		200,000원
				 		<p class="text_red">150,000원</p>
				 	</td>
				 	<td class="text_r">
				 		100,000원
				 	</td>
				 	<td class="text_r">
				 		300,000원
				 		<p class="text_red">250,000원</p>
				 	</td>
				 	<td class="text_c text_red">오류</td>
				 	<td class="text_c"><a href="#" class="w_100">변경</a></td>
				 </tr>
				 <tr>
				 	<td>3</td>
				 	<td class="text_point"><a href="#">홍길동(L2233321333 / 3등급 /기초0%)</a>
				 	</td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_r">200,000원</td>
				 	<td class="text_r">10,000원</td>
				 	<td class="text_r text_point">210,000원</td>
				 	<td class="text_c text_point">변경완료</td>
				 	<td class="text_c"><a href="#" class="w_100">변경</a></td>
				 </tr>
				 <tr>
				 	<td>2</td>
				 	<td><a href="#">홍길동(L2233321333 / 3등급 /기초0%)</a></td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_r">200,000원</td>
				 	<td class="text_r">10,000원</td>
				 	<td class="text_r">210,000원</td>
				 	<td class="text_c text_gray">대기</td>
				 	<td class="text_c"><a href="#" class="w_100">변경</a></td>
				 </tr>
				 <tr>
				 	<td>1</td>
				 	<td><a href="#">홍길동(L2233321333 / 3등급 /기초0%)</a></td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_r">200,000원</td>
				 	<td class="text_r">10,000원</td>
				 	<td class="text_r">210,000원</td>
				 	<td class="text_c text_gray">대기</td>
				 	<td class="text_c"><a href="#" class="w_100">변경</a></td>
				 </tr>
			 </table>
			 </div>
			 
			 <div class="list-paging">
			 	<ul class="pagination ">
			 		<li> </li>
			 		<li><a href="#"> &lt;</a></li>
			 		<li class="active"><a href="#">1</a></li>
			 		<li><a href="#">2</a></li>
			 		<li><a href="#">3</a></li>
			 		<li><a href="#">&gt;</a></li>
			 		<li> </li>
			 	</ul>
			 </div>
			 <div class="subtit">
			 	건강관리공단 미 매칭 자료 
			 </div>
 			<div class="table_box">
 			<table >
				 <tr>
				 	<th>No.</th>
				 	<th>수급자 정보</th>
				 	<th>급여시작일</th>
				 	<th>급여비용총액</th>
				 	<th>본인부담금</th>
				 	<th>청구액</th>
				 	<th>검증상태</th>
				 </tr>
				 <tr>
				 	<td>2</td>
				 	<td>홍길동(L2233***** / 3등급 /기초0%)</td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_r">200,000원</td>
				 	<td class="text_r">10,000원</td>
				 	<td class="text_r">210,000원</td>
				 	<td class="text_c text_gray">미매칭</td>
				 </tr>
				 <tr>
				 	<td>1</td>
				 	<td>홍길동(L2233***** / 3등급 /기초0%)</td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_r">200,000원</td>
				 	<td class="text_r">10,000원</td>
				 	<td class="text_r">210,000원</td>
				 	<td class="text_c text_gray">미매칭</td>
				 </tr>
			 </table>
			 </div>
			 
			 <div class="list-paging">
			 	<ul class="pagination ">
			 		<li> </li>
			 		<li><a href="#"> &lt;</a></li>
			 		<li class="active"><a href="#">1</a></li>
			 		<li><a href="#">2</a></li>
			 		<li><a href="#">3</a></li>
			 		<li><a href="#">&gt;</a></li>
			 		<li> </li>
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
