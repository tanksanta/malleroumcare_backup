<?php

	if(!defined("_GNUBOARD_")) exit;

?>
	
	<link rel="stylesheet" href="//unpkg.com/swiper/swiper-bundle.min.css">
	<script src="//unpkg.com/swiper/swiper-bundle.min.js"></script>

	<!-- 메인 상단 슬라이드 -->
	<div id="mainTopSlidePCWrap">
		<div class="listWrap">
		<?php foreach($head_category as $cate) { ?>
			<ul>
				<li class="mainMenu">
					<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo $cate['ca_name']; ?><i class="fa fa-angle-right"></i></a>
				</li>
				<?php foreach($cate['sub'] as $i=>$sub) { ?>
					<li><a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='cate_02 <?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub['ca_name']; ?></a></li>
					<?php if (!empty($sub['sub'])) { ?>
						<?php foreach($sub['sub'] as $sub2) { ?>
							<li><a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub2['ca_id']; ?>' class='cate_03 <?php echo $sub2['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub2['ca_name']; ?></a></li>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</ul>
		<?php } ?>
		</div>
		
		<div class="viewWrap swiper-container">
			<ul style="width: 300%;" class="swiper-wrapper">
				<li style="width: 33.33%;" class="swiper-slide">
					<a href="/bbs/content.php?co_id=guide">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_03.jpg" alt="">
					</a>
				</li>
				<li style="width: 33.33%;" class="swiper-slide">
					<a href="/bbs/board.php?bo_table=notice&wr_id=11">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_01.jpg" alt="">
					</a>
				</li>
				<li style="width: 33.33%;" class="swiper-slide">
					<a href="/bbs/board.php?bo_table=notice&wr_id=11">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_02.jpg" alt="">
					</a>
				</li>
			</ul>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			var swiper = new Swiper(".swiper-container", {
				slidesPerView : "auto",
				autoplay : {
					delay : 5000,
				},
			});
			
		})
	</script>
	
	<!-- 메인 추천 카테고리 -->
	<div id="mainBestCategoryWrap">
		<div class="title">
			카테고리별 추천상품
		</div>
		
		<ul class="list">
			<li><a href="#" data-no="1020" title="요실금팬티" class="active">요실금팬티</a></li>
			<li><a href="#" data-no="1030" title="자세변환용구">자세변환용구</a></li>
			<li><a href="#" data-no="1040" title="욕창예방방석">욕창예방방석</a></li>
			<li><a href="#" data-no="1050" title="지팡이">지팡이</a></li>
			<li><a href="#" data-no="1060" title="간이변기">간이변기</a></li>
			<li><a href="#" data-no="1070" title="미끄럼방지">미끄럼방지</a></li>
			<li><a href="#" data-no="1090" title="안전손잡이">안전손잡이</a></li>
			<li><a href="#" data-no="10b0" title="목욕의자">목욕의자</a></li>
			<li><a href="#" data-no="10c0" title="이동변기">이동변기</a></li>
		</ul>
		
		<div class="productListWrap">
			<ul>
			</ul>
		</div>
	</div>
	
	<!-- 메인 추천 제품 -->
	<div id="mainBestProductWrap">
		<div class="title">
			신규 추천상품
		</div>
		
		<div class="productListWrap">
			<ul>
			<?php
				$productSQL = sql_query("
					SELECT *
					FROM g5_shop_item
					WHERE ca_id2 = '30'
					ORDER BY it_id DESC
				");
				
				$optionProductList = [];
				for($i = 0; $row = sql_fetch_array($productSQL); $i++){
					$thisOptionList = [];

					# 210204 옵션
					$thisOptionSQL = sql_query("
						SELECT io_id
						FROM g5_shop_item_option
						WHERE it_id = '{$row["it_id"]}'
					");
					for($ii = 0; $subRow = sql_fetch_array($thisOptionSQL); $ii++){
						array_push($thisOptionList, $subRow["io_id"]);
					}
					
					$optionProductList[$row["it_id"]] = $thisOptionList;
				}
							 
				# 210204 재고조회
				$sendData = [];
				$sendData["usrId"] = $member["mb_id"];
				$sendData["entId"] = $member["mb_entId"];

				$prodsSendData = [];
				if($optionProductList){
					foreach($optionProductList as $it_id => $data){
						$stockQtyList[$it_id] = 0;

						if($data){
							foreach($data as $optionData){
								$prodsData = [];
								$prodsData["prodId"] = $it_id;
								$prodsData["prodColor"] = explode(chr(30), $optionData)[0];
								$prodsData["prodSize"] = explode(chr(30), $optionData)[1];

								array_push($prodsSendData, $prodsData);
							}
						} else {
							$prodsData = [];
							$prodsData["prodId"] = $it_id;
							$prodsData["prodColor"] = "";
							$prodsData["prodSize"] = "";

							array_push($prodsSendData, $prodsData);
						}
					}
				}

				$sendData["prods"] = $prodsSendData;

				$productSQL = sql_query("
					SELECT *
					FROM g5_shop_item
					WHERE ca_id2 = '30'
					ORDER BY it_id DESC
				");
							 
				for($i = 0; $row = sql_fetch_array($productSQL); $i++){
					$img = apms_it_thumbnail($row, 400, 400, false, true);

					if(!$img["src"] && $row["it_img1"]){
						$img["src"] = G5_DATA_URL."/item/{$row["it_img1"]}";
						$img["org"] = G5_DATA_URL."/item/{$row["it_img1"]}";
					}

					if(!$img["src"]){
						$img["src"] = G5_URL."/shop/img/no_image.gif";
					}
			?>
				<li class="<?=$row["it_id"]?>" data-ca="<?=substr($row["ca_id"], 0, 2)?>">
					<a href="/shop/item.php?it_id=<?=$row["it_id"]?>">
					<?php if($row["prodSupYn"] == "N"){ ?>
						<p class="sup">비유통 상품</p>
					<?php } ?>
						<p class="img">
						<?php if($img["src"]){ ?>
							<img src="<?=$img["src"]?>" alt="<?=$row["it_name"]?>_상품이미지">
							<?php if(json_decode($row["it_img_3d"], true)){ ?>
							<span class="img_3d">
								<img src="<?=G5_IMG_URL?>/item3dviewVisual.jpg">
							</span>
							<?php } ?>
						<?php } ?>
						</p>
						<p class="name"><?=$row["it_name"]?></p>
					<?php if($row["it_model"]){ ?>
						<p class="info"><?=$row["it_model"]?></p>
					<?php } ?>
					<?php if($member["mb_id"]){ ?>
						<?php if($member["mb_level"] == "3"){ ?>
							<?php if($_COOKIE["viewType"] != "basic"){ ?>
								<p class="discount"><?=number_format($row["it_cust_price"])?>원</p>
							<?php } ?>
							<p class="price"><?=($_COOKIE["viewType"] == "basic") ? number_format($row["it_cust_price"]) : number_format($row["it_price"])?>원</p>
						<?php } else { ?>
							<p class="price"><?=number_format($row["it_price"])?>원</p>
						<?php } ?>
					<?php } else { ?>
						<p class="price"><?=number_format($row["it_cust_price"])?>원</p>
					<?php } ?>
					</a>
				</li>
			<?php } ?>
			</ul>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			var sendData = <?=json_encode($sendData, JSON_UNESCAPED_UNICODE)?>;
			
			function stockCntSetting(){
			<?php if($member["mb_id"]){ ?>
				$.ajax({
					url : "/apiEroum/stock/selectList.php",
					type : "POST",
					async : false,
					data : sendData,
					success : function(result){
						$.each(result, function(it_id, cnt){
							var label = "재고 보유";
							if($("." + it_id).attr("data-ca") == "20"){
								label = "보유 대여 재고";
							}

							$("." + it_id).find("a > .cnt").remove();
							$("." + it_id).find("a").append('<p class="cnt"><span>' + label + '</span><span class="right">' + cnt + '개</span></p>');
						});
					}
				});
			<?php } ?>
			}
			
			stockCntSetting();
			
			function bestItemSetting(){
				var no = $("#mainBestCategoryWrap > .list > li > a.active").attr("data-no");
				
				$.ajax({
					url : "/shop/ajax.main.best.item.php",
					type : "POST",
					async : false,
					data : {
						no : no
					},
					success : function(result){
						result = JSON.parse(result);
						sendData = result.sendData;
						var html = "";

						$("#mainBestCategoryWrap > .productListWrap > ul > li").remove();
						
						if(result.data){
							$.each(result.data, function(it_id, row){
								html += '<li class="' + it_id + '" data-ca="' + row.ca_id + '">';
								html += '<a href="/shop/item.php?it_id=' + it_id + '">';
								if(row.prodSupYn == "N"){
									html += '<p class="sup">비유통 상품</p>';
								}
								html += '<p class="img">';
								if(row.img){
									html += '<img src="' + row.img + '" alt="' + row.it_name + '_상품이미지">';
									if(row.it_img_3d){
										html += '<span class="img_3d"><img src="<?=G5_IMG_URL?>/item3dviewVisual.jpg"></span>';
									}
								}
								html += '</p>';
								html += '<p class="name">' + row.it_name + '</p>';
								if(row.it_model){
									html += '<p class="info">' + row.it_model + '</p>';
								}
								if(row.it_price_discount){
									html += '<p class="discount">' + number_format(row.it_price_discount) + '원</p>';
								}
								html += '<p class="price">' + number_format(row.it_price) + '원</p>';
								html += '</a>';
								html += '</li>';
							});
							
							$("#mainBestCategoryWrap > .productListWrap > ul").html(html);
							stockCntSetting();
						}
					}
				});
			}
			
			$("#mainBestCategoryWrap > .list > li > a").click(function(e){
				e.preventDefault();
				if($(this).hasClass("active")){
					return false;
				}
				
				$("#mainBestCategoryWrap > .list > li > a").removeClass("active");
				$(this).addClass("active");
				bestItemSetting();
			});
			
		})
	</script>
	
	<!-- 메인 배너 -->
	<div id="mainBannerWrap">
		<div class="listWrap">
			<div class="slick">
				<div class="item">
					<a href="/bbs/content.php?co_id=guide">
						<img src="<?=THEMA_URL?>/assets/img/main_c_banner_01.png" alt="" class="pc_layout">
						<img src="<?=THEMA_URL?>/assets/img/main_c_banner_m_01.jpg" alt="" class="mo_layout">
					</a>
				</div>
			</div>
		</div>
	</div>
	
	<!-- 메인 최근게시글 -->
	<div id="mainBoardListWrap">
		<div class="customer">
			<div class="title">
				<span>이로움 고객만족센터</span>
			</div>
			
			<ul class="info">
				<li class="call">
					<img src="<?=THEMA_URL?>/assets/img/mainCallIcon.png" alt="">
					<p>
						<span class="Label">주문안내</span>
						<span class="value">032-562-6608</span>
					</p>
					<p>
						<span class="Label">시스템안내</span>
						<span class="value">02-830-1301~2</span>
					</p>
				</li>
				<li class="time">월~금 09:00~18:00 (점심시간 12시~13시)</li>
				<li class="etc">
					<p>
						<span>Email</span>
						<span class="line"></span>
						<span><?php echo $default['de_admin_info_email']; ?></span>
					</p>
					<p>
						<span>Fax</span>
						<span class="line"></span>
						<span><?php echo $default['de_admin_company_fax']; ?></span>
					</p>
				</li>
			</ul>
		</div>
		
		<div class="board">
			<div class="title">
				<span>공지사항</span> 
				<a href="/bbs/board.php?bo_table=notice" title="더보기">더보기<i class="fa fa-plus-square-o"></i></a>
			</div>
			<?php  echo latest('list_main', 'notice', 5, 30); ?>
		</div>
		
		<div class="board">
			<div class="title">
				<span>자주하는 질문</span>
				<a href="/bbs/board.php?bo_table=qa" title="더보기">더보기<i class="fa fa-plus-square-o"></i></a>
			</div>
			<?php  echo latest('list_main', 'faq', 5, 30); ?>
		</div>
	</div>