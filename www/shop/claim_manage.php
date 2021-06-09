<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
    return;
}

define("_ORDERINQUIRY_", true);

// 회원이 아닌 경우
if (!$is_member) goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/claim_manage.php'));

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

$g5['title'] = '청구관리';


include_once('./_head.php');

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

// 셋업
$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
	$setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
}

$selected_month = '2021-06-01';

$entId = $member['mb_entId'];
if(!$entId) {
	alert('사업소 회원만 접근 가능합니다.');
}

$where = "";
$search = get_search_string($search);
if(in_array($searchtype, ['penNm', 'penLtmNum']) && $search) {
	$where = " AND $searchtype LIKE '%$search%' ";
}

$eform_query = sql_query("
SELECT
	MIN(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '3'), '%Y-%m-%d')) as start_date,
	SUM(`it_price`) as total_price,
	SUM(`it_price_pen`) as total_price_pen,
	(SUM(`it_price`) - SUM(`it_price_pen`)) as total_price_ent,
	penId, penNm, penLtmNum, penRecGraCd, penRecGraNm, penTypeCd, penTypeNm, penBirth
FROM `eform_document` a
LEFT JOIN `eform_document_item` b
ON a.dc_id = b.dc_id
WHERE
	entId = '$entId'
	AND dc_status = '2'
	$where
	AND
	(
		(
			gubun = '00' AND
			STR_TO_DATE(`it_date`, '%Y-%m-%d') BETWEEN '$selected_month' AND LAST_DAY('$selected_month')
		)
		OR
		(
			gubun = '01' AND
			(
				(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '3'), '%Y-%m-%d') <= '$selected_month')
				AND
				(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '-3'), '%Y-%m-%d') >= '$selected_month')
			)
		)
	)
GROUP BY `penId`
ORDER BY `penNm` ASC
");

$total_count = sql_num_rows($eform_query);
$page_rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $page_rows); // 전체 페이지 계산
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$cl_query = sql_query("SELECT * FROM `claim_management` WHERE selected_month = '$selected_month' AND mb_id = '{$member['mb_id']}'");
$cl = [];
while($row = sql_fetch_array($cl_query)) {
	$cl[] = $row;
}
?>

<!-- 내용 -->
<title>판매재고목록</title>
<section   class="wrap  ">
    <div class="sub_section_tit">청구/전자문서관리</div>
    <ul class="list_tab">
        <li class="active"><a href="<?=G5_SHOP_URL?>/claim_manage.php">청구관리</a></li>
        <li ><a href="<?=G5_SHOP_URL?>/electronic_manage.php">전자문서관리<!--<span class="red_info">미작성: 1건</span>--></a></li>
    </ul>
     <div class="inner">
     	<div class="date_wrap">
     		<div class="date_this">
     			<a href="#">이번달</a>
     		</div>
     		<div class="date_selected">
     			<a href="#" class="disabled">◀ 지난달</a>
     			<select name="" id="">
     				<option selected>2021년 6월</option>
     			</select>
     			<a href="#" class="disabled">다음달 ▶</a>
     		</div>
     		
     	</div>
     	
     	<div class="search_box">
				 <form action="/shop/claim_manage.php" method="get">
            <select name="searchtype" id="">
                <option value="penNm">수급자명</option>
                <option value="penLtmNum">요양인정번호</option>
            </select>
            <div class="input_search">
                <input name="search" value="<?=$_GET["search"]?>" type="text">
                <button  type="submit"></button>
            </div>
					</form>
        </div>
 		<div class="r_btn_area">
 			<!--<span>최신검증일 : 2021-03-01 13:35</span>
 			<a href="#" class="btn_nhis">건보 자료 업로드</a>-->
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
				<?php 
				for($i = 0; $row = sql_fetch_array($eform_query); $i++) {
					$index = $from_record + $i + 1;
					$row['selected_month'] = $selected_month;
					if(strtotime($row['start_date']) < strtotime($selected_month))
						$row['start_date'] = $selected_month;
					
					$row['cl_status'] = '0';
					foreach($cl as $val) {
						if
						(
							$val['penId'] == $row['penId'] &&
							$val['penNm'] == $row['penNm'] &&
							$val['penLtmNum'] == $row['penLtmNum'] &&
							$val['penRecGraCd'] == $row['penRecGraCd'] &&
							$val['penTypeCd'] == $row['penTypeCd']
						) {
							$row['cl_status'] = $val['cl_status'];
							$row['start_date'] = $val['start_date'];
							$row['total_price'] = $val['total_price'];
							$row['total_price_pen'] = $val['total_price_pen'];
							$row['total_price_ent'] = $val['total_price_ent'];
						}
					}
				?>
				<tr>
					<td><?=$index?></td>
				 	<td><a href="#"><?="{$row['penNm']}({$row['penLtmNum']} / {$row['penRecGraNm']} / {$row['penTypeNm']})"?></a></td>
				 	<td class="text_c start_date"><?=$row['start_date']?></td>
				 	<td class="text_r total_price"><?=number_format($row['total_price'])?>원</td>
				 	<td class="text_r total_price_pen"><?=number_format($row['total_price_pen'])?>원</td>
				 	<td class="text_r total_price_ent"><?=number_format($row['total_price_ent'])?>원</td>
				 	<td class="text_c status" data-status="<?=$row['cl_status']?>"><?=($row['cl_status'] == '0' ? '대기' : '변경')?></td>
				 	<td class="text_c">
						 <a href="#" class="btn_edit w_100" data-json="<?=htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8')?>">변경</a>
					</td>
				 </tr>
				<?php } ?>
				 <!--<tr>
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
				 </tr>-->
			 </table>
			 </div>
			 
			 <div class="list-paging">
			 </div>
			 <!--<div class="subtit">
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
     </div>-->
    
</section>

<div id="popupEdit">
	<div></div>
</div>
<style>
td.status {color: #333;}
td.status[data-status="0"] {color: #bbb;}

#popupEdit { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popupEdit > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popupEdit iframe { position: relative; width: 700px; height: 300px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; }

@media (max-width : 800px){
	#popupEdit iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}

body.modal-open {
	overflow: hidden;
}
</style>

<script>
function updateClaim(cl_id, data) {
	var $tr = $('.btn_edit[data-id="'+cl_id+'"]').closest('tr');
	$tr.find('.start_date').text(data['start_date']);
	$tr.find('.total_price').text(data['total_price']);
	$tr.find('.total_price_pen').text(data['total_price_pen']);
	$tr.find('.total_price_ent').text(data['total_price_ent']);
	$tr.find('.status').attr('data-status', '1').text('변경');
}

$(function() {
  $("#popupEdit").hide();
	$("#popupEdit").css("opacity", 1);

	// 내용변경 버튼
	$(document).on('click', '.btn_edit', function(e) {
		e.preventDefault();

		var $this = $(this);
		var data = $this.data('json');
		$.post('./ajax.claim_manage.write.php', JSON.stringify(data), 'json')
		.done(function(data) {
			var cl_id = data.message;
			$this.attr('data-id', cl_id);
			$("#popupEdit > div").html("<iframe src='./claim_manage_edit.php?cl_id="+cl_id+"'>");
			$("#popupEdit iframe").load(function(){
				$("body").addClass('modal-open');
				$("#popupEdit").show();
			});
		})
		.fail(function($xhr) {
			var data = $xhr.responseJSON;
      alert(data && data.message);
		});
	});
});
</script>


<?php
include_once('./_tail.php');
?>
