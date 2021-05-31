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

$incompleted_eform_count = get_incompleted_eform_count();
?>

<!-- 내용 -->
<title>판매재고목록</title>
<section   class="wrap  ">
    <div class="sub_section_tit">청구/전자문서관리</div>
    <ul class="list_tab">
        <li><a href="<?=G5_SHOP_URL?>/claim_manage.php">청구관리</a></li>
        <li class="active"><a href="<?=G5_SHOP_URL?>/electronic_manage.php">전자문서관리<?php echo ($incompleted_eform_count ? '<span class="red_info">미작성: '.$incompleted_eform_count.'건</span>' : '');?></a></li>
    </ul>
    <div class="inner">
    	<div class="list_box">
 			<div class="point_box">
 				<div class="subtit">
				 	전자문서 미 작성내역
				 </div>
 				<div class="table_box">
 					
		 			<table >
						 <tr>
						 	<th>No.</th>
						 	<th>수급자 정보</th>
						 	<th>상품정보</th>
						 	<th>기준월</th>
						 	<th>전자문서</th>
						 	<th>비고</th>
						 </tr>
						 <tr>
						 	<td>3</td>
						 	<td>홍길동(L2233321333 / 3등급 /기초0%)</td>
						 	<td>상품명(11111)</td>
						 	<td>2021년 3월</td>
						 	<td class="text_c">
						 		<a href="#" class="btn_basic">다운로드</a>
						 	</td>
						 	<td class="text_c">
						 		<a href="#">공급계약서 다운로드</a>
						 		<p>2021-01-01~ 2022-01-01</p>
						 	</td>
						 </tr>
						 <tr>
						 	<td>2</td>
						 	<td>홍길동(L2233321333 / 3등급 /기초0%)</td>
						 	<td>상품명(11111)</td>
						 	<td>2021년 3월</td>
						 	<td class="text_c">
						 		<a href="#" class="btn_point">서명하기</a>
						 	</td>
						 	<td class="text_c">
						 		<a href="#">공급계약서 다운로드</a>
						 		<p>2021-01-01~ 2022-01-01</p>
						 	</td>
						 </tr>
						 <tr>
						 	<td>1</td>
						 	<td>홍길동(L2233321333 / 3등급 /기초0%)</td>
						 	<td>상품명(11111)</td>
						 	<td>2021년 3월</td>
						 	<td class="text_c">
						 		<a href="#" class="btn_basic">다운로드</a>
						 	</td>
						 	<td class="text_c">
						 		<a href="#">공급계약서 다운로드</a>
						 		<p>2021-01-01~ 2022-01-01</p>
						 	</td>
						 </tr>
						 
					 </table>
			 	</div>
				 <p>
				 	*대여제품인 경우 수급자 분류에 따라서 서명이 필요한 문서가 노출됩니다.
				 </p>
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
    	<div class="search_box">
            <select name="searchtype" id="">
                <option value="1">수급자</option>
                <option value="2">상품명</option>
            </select>
            <div class="input_search">
                <input name="searchtypeText" value="<?=$_GET["searchtypeText"]?>" type="text">
                <button  type="submit"></button>
            </div>
        </div>
 		<div class="r_btn_area">
 			<select >
                <option>작성일정렬</option>
                <option>수급자정렬</option>
                <option>상품명정렬</option>
            </select>
 		</div>
 		<div class="list_box">
 			<div class="table_box">
 			<table >
				 <tr>
				 	<th>No.</th>
				 	<th>수급자 정보</th>
				 	<th>상품정보</th>
				 	<th>분류</th>
				 	<th>작성일</th>
				 	<th>전자문서</th>
				 	<th>비고</th>
				 </tr>
				 <tr>
				 	<td>3</td>
				 	<td>홍길동(L2233321333 / 3등급 /기초0%)</td>
				 	<td>상품명(11111)</td>
				 	<td>일반계약</td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_c">
				 		<a href="#" class="btn_basic">다운로드</a>
				 	</td>
				 	<td class="text_c"> </td>
				 </tr>
				 
				 <tr>
				 	<td>2</td>
				 	<td>홍길동(L2233321333 / 3등급 /기초0%)</td>
				 	<td>상품명(11111)</td>
				 	<td>급여제공기록지(서명)</td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_c">
				 		<a href="#" class="btn_basic">다운로드</a>
				 	</td>
				 	<td class="text_c"><a href="#">공급계약서 다운로드</a></td>
				 </tr>
				 <tr>
				 	<td>1</td>
				 	<td>홍길동(L2233321333 / 3등급 /기초0%)</td>
				 	<td>상품명(11111)</td>
				 	<td>급여제공기록지</td>
				 	<td class="text_c">2021-02-02</td>
				 	<td class="text_c">
				 		<select name="" id=""  class="btn_basic">
				 			<option>::문서선택::</option>
				 			<option>계약서</option>
				 			<option>이용신청서</option>
				 			<option>이용내역서</option>
				 			<option>전체</option>
				 		</select>
				 	</td>
				 	<td class="text_c"><a href="#">공급계약서 다운로드</a></td>
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
