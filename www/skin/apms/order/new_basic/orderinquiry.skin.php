<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['ihead']) && $wset['ihead']) {
	add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ihead'].'.css" media="screen">', 0);
	$head_class = 'list-head';
} else {
	$head_class = (isset($wset['icolor']) && $wset['icolor']) ? 'tr-head border-'.$wset['icolor'] : 'tr-head border-black';
}

// 헤더 출력
if($header_skin)
	include_once('./header.php');

	# 스킨경로	
	$SKIN_URL = G5_SKIN_URL.'/apms/order/'.$skin_name;

?>

<link rel="stylesheet" href="<?=$SKIN_URL?>/css/jquery-ui.min.css">
<link rel="stylesheet" href="<?=$SKIN_URL?>/css/product_order.css">
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
   
<script>
	$( function() {
		//캘린더
		$("#date1").datepicker({
			dateFormat : 'yy-mm-dd',
			prevText: '이전달',
			nextText: '다음달',
			monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			dayNames: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
			showMonthAfterYear: true,
			changeMonth: true,
			changeYear: true,
			showOn: "both",
			buttonImage: "<?=$SKIN_URL?>/image/icon_17.png",
			buttonImageOnly: true,
			buttonText: "Select date"
		});
		$("#date2").datepicker({
			dateFormat : 'yy-mm-dd',
			prevText: '이전달',
			nextText: '다음달',
			monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			dayNames: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
			showMonthAfterYear: true,
			changeMonth: true,
			changeYear: true,
			showOn: "both",
			buttonImage: "<?=$SKIN_URL?>/image/icon_17.png",
			buttonImageOnly: true,
			buttonText: "Select date"
		});

		//셀렉트(주문+재고, 전체 상태)
		$('.order-date .list-select .select').find('p').each(function(){
			$(this).on('click',function(){
				$(this).siblings('ul').stop().slideToggle();
				$(this).parent('.select').siblings('.select').find('ul').stop().slideUp();
				$(this).siblings('ul').find('li a ').on('click',function(){
					let textVal = $(this).text();
					$(this).parents('ul').siblings('p').text(textVal);
					$(this).parents('ul').stop().slideUp();

				});
			});
		});
	} );
</script>
    
<section id="pro-order" class="wrap order-list">
	<h2 class="tti">주문내역</h2>
	<div class="order-date">
		<div class="list-text">
			<div>
				<span><img src="<?=$SKIN_URL?>/image/icon_13.png" alt="">상품준비 <b><?=$item_wait_count?>건</b></span>
			</div>
			<div>
				<span><img src="<?=$SKIN_URL?>/image/icon_14.png" alt="">배송중 <b><?=$delivery_ing_count?>건</b></span>
			</div>
		</div>
		<form class="date-box cb" style="width: 100%;" method="get">
			<div class="list-date">
				<input type="text" name="s_date" value="<?=$_GET["s_date"]?>" id="date1" /> 
				~ 
				<input type="text" name="e_date" value="<?=$_GET["e_date"]?>" id="date2" /> 
			</div>
			<div class="list-tab">
				<a href="javascript:;" onclick="searchDateSetting('1week');">일주일</a>
				<a href="javascript:;" onclick="searchDateSetting('1month');">이번달</a>
				<a href="javascript:;" onclick="searchDateSetting('3month');">3개월</a>
			</div>
			<div class="list-select">
				<div class="select">
					<input type="hidden" name="od_stock" value="<?=$_GET["od_stock"]?>">
					<p><?=$search_od_stock?></p>
					<ul>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_stock" data-val="">주문+재고</a></li>
					<?php for($i = 0; $i < count($order_stocks); $i++){ ?>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_stock" data-val="<?=$order_stocks[$i]["val"]?>"><?=$order_stocks[$i]["name"]?></a></li>
					<?php } ?>
					</ul>
				</div>
				<div class="select">
					<input type="hidden" name="od_status" value="<?=$_GET["od_status"]?>">
					<p><?=$search_od_status?></p>
					<ul>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_status" data-val="">전체 상태</a></li>
					<?php for($i = 0; $i < count($order_steps); $i++){ ?>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_status" data-val="<?=$order_steps[$i]["val"]?>"><?=$order_steps[$i]["name"]?></a></li>
					<?php } ?>
					</ul>
				</div>
				<button type="submit">검색</button>
			</div>
		</form>
	</div>

	<div class="list-wrap">
	<?php for ($i = 0; $i < count($list); $i++){ $row = $list[$i]; ?>
	<?php
		$itemList = [];
		$itemSQL = sql_query("
			SELECT a.*
				, ( SELECT it_img1 FROM {$g5["g5_shop_item_table"]} WHERE it_id = a.it_id ) AS it_img
			FROM {$g5["g5_shop_cart_table"]} a
			WHERE od_id = '{$row["od_id"]}'
		");
											  
		for($ii = 0; $item = sql_fetch_array($itemSQL); $ii++){
			array_push($itemList, $item);
		}
	?>
		<div class="table-list<?=($i) ? " table-list2" : ""?>">
			<div class="top">
				<span> <i class="m_none">주문번호 :</i> <a href="<?=$row["od_href"]?>"><?=$row["od_id"]?></a> </span>
				<span> <?=display_price($row["od_total_price"])?> </span>
				<span class="m_none"> <?=date("Y.m.d(H:i)", strtotime($row["od_time"]))?></span>
			<?php if($row["recipient_yn"] == "Y"){ ?>
				<span class="btn-pro"> <img src="<?=$SKIN_URL?>/image/icon_15.png" alt=""> 수급자 주문 </span>
			<?php }else if($row["od_stock_insert_yn"] == "Y"){ ?>
				<span class="btn-pro on"> 보유재고등록 </span>
			<?php } else { ?>
				<span class="btn-pro on"> <img src="<?=$SKIN_URL?>/image/icon_16.png" alt=""> 상품 주문 </span>
			<?php } ?>
			</div>

			<div class="info-wrap">
			<?php if($row["recipient_yn"] == "Y"){ ?>
				<div class="info-top">
					<h5>수급자 정보 : <?=$row["od_penNm"]?> (<?=$row["od_penTypeNm"]?>)</h5>
					<a href="javascript:;">계약서</a>
				</div>
			<?php } ?>
			</div>
			
			<?php foreach($itemList as $item){ ?>
				<div class="list">
					<ul class="cb">
						<li class="pro">
							<div class="img">
							<?php if($item["it_img"]){ ?>
								<img src="/data/item/<?=$item["it_img"]?>" onerror="this.src='/img/no_img.png';">
							<?php } ?>
							</div>
							<div class="pro-info">
							<?php if($row["recipient_yn"] == "Y"){ ?>
								<div class="day">
								<?php if($item["ordLendStrDtm"] && $item["ordLendStrDtm"] != "0000-00-00 00:00:00"){ ?>
									<i>대여</i>
									<?=date("Y.m.d", strtotime($item["ordLendStrDtm"]))?> ~ <?=date("Y.m.d", strtotime($item["ordLendEndDtm"]))?>
								<?php } else { ?>
									<i class="on-order">주문</i>
								<?php } ?>
								</div>
							<?php } ?>
								<div class="name"><?=$item["it_name"]?> <?=($item["ct_option"] && $item["ct_option"] != $item["it_name"]) ? "({$item["ct_option"]})" : ""?></div>
								<div>
									<em>수량 : <?=$item["ct_qty"]?></em>
								<?php if($item["ct_stock_qty"]){ ?>
									<em>, 재고소진 : <?=$item["ct_stock_qty"]?></em>
								<?php } ?>
								</div>
								<div class="pc_none">
									<?=($row["od_stock_insert_yn"] == "Y") ? "재고등록완료" : $row["od_status"]?>
								</div>
							</div>
						</li>
						<li class="delivery m_none">
							<p><?=($row["od_stock_insert_yn"] == "Y") ? "재고등록완료" : $row["od_status"]?></p>
						</li>
						<li class="info-btn">
							<div>
<!--								<a href="javascirpt:;" class="btn-01 btn-0"><img src="<?=$SKIN_URL?>/image/icon_02.png" alt=""> 바코드</a>-->
							<a href="javascirpt:;" class="btn-03 btn-0"><?=($row["od_prodBarNum_insert"] < $row["od_prodBarNum_total"]) ? "바코드 ({$row["od_prodBarNum_insert"]}/{$row["od_prodBarNum_total"]})" : "바코드 확인"?></a>
							
							<?php if($row["od_delivery_insert"]){ ?>
								<a href="javascirpt:;" class="btn-02 btn-0">배송정보</a>
							<?php } ?>
							
							<?php if($row["od_status"] == "배송완료"){ ?>
								<a href="javascirpt:;" class="btn-02 btn-0">재고확인</a>
							<?php } ?>
							</div>
						</li>
					</ul>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	</div>

</section>

<div class="text-center">
	<ul class="pagination pagination-sm en">
		<?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
	</ul>
</div>

<?php if($setup_href) { ?>
	<p class="text-center">
		<a class="btn btn-color btn-sm win_memo" href="<?php echo $setup_href;?>">
			<i class="fa fa-cogs"></i> 스킨설정
		</a>
	</p>
<?php } ?>

<script type="text/javascript">
	function searchDateSetting(type){
		switch(type){
			case "1week" :
				$("#date1").val("<?=date("Y-m-d", strtotime("- 7 days"))?>");
				break;
			case "1month" :
				$("#date1").val("<?=date("Y-m-01")?>");
				break;
			case "3month" :
				$("#date1").val("<?=date("Y-m-d", strtotime("- 3 month"))?>");
				break;
		}

		$("#date2").val("<?=date("Y-m-d")?>");
	}
	
	$(function(){
		
		$(".hiddenChange").click(function(){
			var target = $(this).attr("data-target");
			var val = $(this).attr("data-val");
			
			$(this).closest("form").find("input[name='" + target + "']").val(val);
		});
		
	})
</script>