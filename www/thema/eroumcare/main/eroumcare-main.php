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
	</div>
	
	<!-- 메인 추천 제품 -->
	<div id="mainBestProductWrap">
		<div class="title">
			고객님을 위한 제품 추천
		</div>
		
		<div class="productListWrap">
			<ul>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
				<li>
					<a href="#">
						<p class="sup">비유통 상품</p>
						<p class="img">
						</p>
						<p class="name">미끄럼방지양말</p>
						<p class="info">자세한 설명이 보여집니다.</p>
						<p class="price">999,000원</p>
						<p class="cnt">
							<span>재고 보유</span>
							<span class="right">2개</span>
						</p>	
					</a>
				</li>
			</ul>
		</div>
	</div>