<?php

	if(!defined("_GNUBOARD_")) exit;

?>

	<!-- 메인 상단 슬라이드 -->
	<div id="mainTopSlidePCWrap" class="pc_layout">
		<div class="listWrap">
			<ul>
				<li class="active">
					<p class="img">
						<img src="/data/banner/25" alt="">
					</p>
					<p class="info">
						<img src="<?=THEMA_URL?>/assets/img/mainTopSlideActive.png" alt="" class="activeIcon">
						<span class="big">신규 브랜드 입점할인</span>
						<span class="small">행복한 당신의 미래를 위한<br>오늘의 평범한 일상</span>
					</p>
				</li>
				<li>
					<p class="img">
						<img src="/data/banner/25" alt="">
					</p>
					<p class="info">
						<img src="<?=THEMA_URL?>/assets/img/mainTopSlideActive.png" alt="" class="activeIcon">
						<span class="big">신규상품 2021</span>
						<span class="small">새로운 상품을 만나보세요.<br>이벤트 진행 중</span>
					</p>
				</li>
			</ul>
		</div>
		
		<div class="viewWrap">
			<ul>
				<li class="active">
					<img src="/data/banner/25" alt="">
				</li>
				<li>
					<img src="/data/banner/25" alt="">
				</li>
			</ul>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$("#mainTopSlidePCWrap > .listWrap > ul > li").click(function(){
				var item = $("#mainTopSlidePCWrap > .viewWrap > ul > li");
				
				$("#mainTopSlidePCWrap > .listWrap > ul > li").removeClass("active");
				$("#mainTopSlidePCWrap > .viewWrap > ul > li ").removeClass("active");
				
				$(this).addClass("active");
				$(item[$(this).index()]).addClass("active");
			});
			
		})
	</script>
	
	<div id="mainTopSlideMoWrap" class="mo_layout">
		<div class="listWrap">
			<ul style="width: 300%;">
				<li style="width: 33.33%;">
					<img src="/data/banner/25" alt="">
				</li>
				<li style="width: 33.33%;">
					<img src="/data/banner/25" alt="">
				</li>
				<li style="width: 33.33%;">
					<img src="/data/banner/25" alt="">
				</li>
			</ul>
		</div>
		
		<ul class="navWrap">
			<li class="active"></li>
			<li></li>
			<li></li>
		</ul>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			var mainTopSlideNum = 0;
			
			function mainTopSlideSetting(){
				var navItem = $("#mainTopSlideMoWrap > .navWrap > li");
				
				$("#mainTopSlideMoWrap > .listWrap > ul").css("left", "-" + (mainTopSlideNum * 100) + "%");
				$(navItem).removeClass("active");
				$(navItem[mainTopSlideNum]).addClass("active");
			}
			
			$("#mainTopSlideMoWrap > .navWrap > li").click(function(){
				mainTopSlideNum = $(this).index();
				
				mainTopSlideSetting();
			});
			
		})
	</script>
	
	<!-- 메인 최근게시글 -->
	<div id="mainBoardListWrap" class="pc_layout">
		<div class="customer">
			<div class="title">
				<span>이로움 고객만족센터</span>
			</div>
			
			<ul class="info">
				<li class="call">
					<img src="<?=THEMA_URL?>/assets/img/mainCallIcon.png" alt="">
					<span>02-1234-5678</span>
				</li>
				<li class="time">월~금 09:00~18:00 (점심시간 12시~13시)</li>
				<li class="etc">
					<p>
						<span>· Email</span>
						<span class="line"></span>
						<span>abc123@abc123.com</span>
					</p>
					<p>
						<span>· Fax</span>
						<span class="line"></span>
						<span>070-222-3333</span>
					</p>
				</li>
			</ul>
		</div>
		
		<div class="board">
			<div class="title">
				<span>공지사항</span>
			</div>
			
			<ul class="list">
				<li><a href="#">· <img src="<?=THEMA_URL?>/assets/img/boardNew.png" alt="">공지사항 테스트글입니다.</a></li>
				<li><a href="#">· 공지사항 테스트글입니다.</a></li>
				<li><a href="#">· 공지사항 테스트글입니다.</a></li>
				<li><a href="#">· 공지사항 테스트글입니다.</a></li>
				<li><a href="#">· 공지사항 테스트글입니다.</a></li>
			</ul>
		</div>
		
		<div class="board">
			<div class="title">
				<span>자주하는 질문</span>
				<a href="#" title="온라인 질문하기">온라인 질문하기</a>
			</div>
			
			<ul class="list">
				<li><a href="#">· 안전손잡이 설치는 어떻게 하나요?</a></li>
				<li><a href="#">· 안전손잡이 설치는 어떻게 하나요?</a></li>
				<li><a href="#">· 안전손잡이 설치는 어떻게 하나요?</a></li>
				<li><a href="#">· 안전손잡이 설치는 어떻게 하나요?</a></li>
				<li><a href="#">· 안전손잡이 설치는 어떻게 하나요?</a></li>
			</ul>
		</div>
	</div>
	
	<!-- 메인 고객센터 -->
	<div id="mainCustomerInfoWrap" class="mo_layout">
		<div class="titleWrap">이로움 고객만족센터</div>
		
		<ul class="infoWrap">
			<li><img src="<?=THEMA_URL?>/assets/img/mainCallIcon.png" alt=""></li>
			<li>02-1234-5678</li>
			<li class="callBtn"><a href="tel: 02-1234-5678">전화연결</a></li>
		</ul>
		
		<div class="timeWrap">
			월~금 09:00~18:00 (점심시간 12시~13시)
		</div>
	</div>
	
	<!-- 메인 배너 -->
	<div id="mainBannerWrap">
		<div class="listWrap">
			<ul class="pc_layout" style="width: 300%;">
				<li style="width: 33.33%;">
					<img src="<?=THEMA_URL?>/assets/img/testBanner01.png" alt="">
				</li>
				<li style="width: 33.33%;">
					<img src="<?=THEMA_URL?>/assets/img/testBanner01.png" alt="">
				</li>
				<li style="width: 33.33%;">
					<img src="<?=THEMA_URL?>/assets/img/testBanner01.png" alt="">
				</li>
			</ul>

			<ul class="mo_layout" style="width: 300%;">
				<li style="width: 33.33%;">
					<img src="<?=THEMA_URL?>/assets/img/testBannerMo01.png" alt="">
				</li>
				<li style="width: 33.33%;">
					<img src="<?=THEMA_URL?>/assets/img/testBannerMo01.png" alt="">
				</li>
				<li style="width: 33.33%;">
					<img src="<?=THEMA_URL?>/assets/img/testBannerMo01.png" alt="">
				</li>
			</ul>
		</div>
		
		<ul class="navWrap">
			<li class="active"></li>
			<li></li>
			<li></li>
		</ul>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			var mainBannerNum = 0;
			
			function mainBannerSetting(){
				var navItem = $("#mainBannerWrap > .navWrap > li");
				
				$("#mainBannerWrap > .listWrap > ul").css("left", "-" + (mainBannerNum * 100) + "%");
				$(navItem).removeClass("active");
				$(navItem[mainBannerNum]).addClass("active");
			}
			
			$("#mainBannerWrap > .navWrap > li").click(function(){
				mainBannerNum = $(this).index();
				
				mainBannerSetting();
			});
			
		})
	</script>
	
	<!-- 메인 추천 카테고리 -->
	<div id="mainBestCategoryWrap">
		<div class="title">
			추천 카테고리
		</div>
		
		<ul class="list pc_layout">
			<li><a href="#" title="요실금팬티">요실금팬티</a></li>
			<li><a href="#" title="자세변환용구">자세변환용구</a></li>
			<li><a href="#" title="욕창예방방석">욕창예방방석</a></li>
			<li><a href="#" title="지팡이">지팡이</a></li>
			<li><a href="#" title="간이변기">간이변기</a></li>
			<li><a href="#" title="미끄럼방지">미끄럼방지</a></li>
			<li><a href="#" title="안전손잡이">안전손잡이</a></li>
			<li><a href="#" title="목욕의자">목욕의자</a></li>
			<li><a href="#" title="이동변기">이동변기</a></li>
		</ul>
		
		<ul class="mo_layout">
			<li>
				<a href="#" title="요실금팬티">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual01.png" alt="요실금팬티">
					</p>
					<p class="name">요실금팬티</p>
				</a>
			</li>
			<li>
				<a href="#" title="자세변환용구">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual02.png" alt="자세변환용구">
					</p>
					<p class="name">자세변환용구</p>
				</a>
			</li>
			<li>
				<a href="#" title="욕창예방방석">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual03.png" alt="욕창예방방석">
					</p>
					<p class="name">욕창예방방석</p>
				</a>
			</li>
			<li>
				<a href="#" title="지팡이">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual04.png" alt="지팡이">
					</p>
					<p class="name">지팡이</p>
				</a>
			</li>
			<li>
				<a href="#" title="간이변기">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual05.png" alt="간이변기">
					</p>
					<p class="name">간이변기</p>
				</a>
			</li>
			<li>
				<a href="#" title="미끄럼방지">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual06.png" alt="미끄럼방지">
					</p>
					<p class="name">미끄럼방지</p>
				</a>
			</li>
			<li>
				<a href="#" title="안전손잡이">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual07.png" alt="안전손잡이">
					</p>
					<p class="name">안전손잡이</p>
				</a>
			</li>
			<li>
				<a href="#" title="목욕의자">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual08.png" alt="목욕의자">
					</p>
					<p class="name">목욕의자</p>
				</a>
			</li>
			<li>
				<a href="#" title="이동변기">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/mainCategoryVisual09.png" alt="이동변기">
					</p>
					<p class="name">이동변기</p>
				</a>
			</li>
		</ul>
	</div>
	
	<!-- 메인 추천 제품 -->
	<div id="mainBestProductWrap">
		<div class="title">
			고객님을 위한 제품 추천
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
				$stockQtyList = [];
				if($member["mb_id"]){
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

					# 재고조회
					$oCurl = curl_init();
					curl_setopt($oCurl, CURLOPT_PORT, 9001);
					curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectList");
					curl_setopt($oCurl, CURLOPT_POST, 1);
					curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
					curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
					$res = curl_exec($oCurl);
					$stockCntList = json_decode($res, true);
					curl_close($oCurl);

					if($stockCntList["data"]){
						foreach($stockCntList["data"] as $data){
							$stockQtyList[$data["prodId"]] += $data["quantity"];
						}
					}
				}

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
				<li>
					<a href="./item.php?it_id=<?=$row["it_id"]?>">
					<?php if($row["prodSupYn"] == "N"){ ?>
						<p class="sup">비유통 상품</p>
					<?php } ?>
						<p class="img">
						<?php if($img["src"]){ ?>
							<img src="<?=$img["src"]?>" alt="<?=$list[$i]["it_name"]?>_상품이미지">
						<?php } ?>
						</p>
						<p class="name"><?=$row["it_name"]?></p>
					<?php if($row["it_model"]){ ?>
						<p class="info"><?=$row["it_model"]?></p>
					<?php } ?>
						<p class="price"><?=show_samhwa_price($row, THEMA_KEY)?></p>
					<?php if($stockQtyList[$row["it_id"]]){ ?>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right"><?=$stockQtyList[$row["it_id"]]?>개</span>
						</p>
					<?php } ?>
					</a>
				</li>
			<?php } ?>
			</ul>
		</div>
	</div>