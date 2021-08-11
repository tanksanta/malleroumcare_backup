<?php

  if(!defined("_GNUBOARD_")) exit;

?>

<style>
.main_slider {
  max-width: 1400px;
  margin: 30px auto 20px auto;
}

.main_top_wrap .slick-prev,
.main_top_wrap .slick-next {
    font-size: 0;
    line-height: 0;
    position: absolute;
    top: 50%;
    display: block;
    width: 20px;
    height: 20px;
    padding: 0;
    -webkit-transform: translate(0, -50%);
    -ms-transform: translate(0, -50%);
    transform: translate(0, -50%);
    cursor: pointer;
    color: transparent;
    border: none;
    outline: none;
    background: transparent;
}
.main_top_wrap .slick-prev {
  top: 150px;
  left: -50px;
}
.main_top_wrap .slick-next {
  top: 150px;
  right: -50px;
}

.main_top_wrap .slick-prev:before,
.main_top_wrap .slick-next:before {
  display: inline-block;
  font: normal normal normal 14px/1 FontAwesome;
  font-size: 80px;
  line-height: 1;
  text-rendering: auto;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #979c94;
}
.main_top_wrap .slick-prev:before {
  content: "\f104";
}
.main_top_wrap .slick-next:before {
  content: "\f105";
}

.main_top_wrap .slick-dots { display: none !important; width: 100%; padding: 0; margin: 0; list-style: none; text-align: center; }
.main_top_wrap .slick-dots li {	position: relative;	display: inline-block;	width: 20px;	height: 20px;	margin: 0 5px;	padding: 0;	cursor: pointer; }
.main_top_wrap .slick-dots li button { font-size: 0; line-height: 0; display: block; width: 20px; height: 20px; padding: 5px; cursor: pointer; color: transparent; border: 0; outline: none; background: transparent; }
.main_top_wrap .slick-dots li button:hover, .item_image_slider .slick-dots li button:focus { outline: none; }
.main_top_wrap .slick-dots li button:before { font-family: 'slick'; font-size: 6px; line-height: 20px; position: absolute; top: 0; left: 0; width: 20px; height: 20px; content: '•'; text-align: center; color: #e1e1e1; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
.main_top_wrap .slick-dots li.slick-active button:before { color: #737373; }

@media (max-width: 960px) {
  .main_top_wrap .slick-prev,
  .main_top_wrap .slick-next {
    display: none !important;
  }
  .main_top_wrap .slick-dots {
    display: block !important;
  }
}
</style>

<script type="text/javascript">
    $(function(){
        $('.main_top_service_info').css( "display", "inline-block" );
        $('.container_wrap').css( "display", "none" );
        $('.mo_top').css( "display", "none" );
        $('#headerTopQuickMenuWrap').css( "display", "none" );
        $('.btn_top_scroll').css( "display", "none" );
        $('body').css( "padding-top", "0px" );
    });
</script>


<div class="main_top_wrap">
	<div class="main_slider_tit">
		<span>복지용구급여</span> 주요기능
	</div>
  <div class="main_slider_nav">
    <div class="active" data-slide="0">온라인계약</div>
    <div data-slide="1">청구관리</div>
    <div data-slide="2">수급자관리</div>
    <div data-slide="3">재고관리</div>
  </div>
  <div class="main_slider">
    <div class="main_slide">
    <img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_service_info_01.png"  >
      <div class="main_slide_desc">
        <h2>편리한 온라인 전자계약</h2>
        <div class="desc">복지용구 사업소 업무를 쉽고 간편하게</div>
      </div>
      
    </div>
    <div class="main_slide">
      <img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_service_info_02.png" >
      <div class="main_slide_desc">
        <h2>월별 청구관리 및 간편검증</h2>
        <div class="desc">실시간 수급자 별 청구금액 확인</div>
      </div>
    </div>
    <div class="main_slide">
      <img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_service_info_03.png"  >
      <div class="main_slide_desc">
        <h2>간편관리 및 신규 수급자 추천</h2>
        <div class="desc">쉽고 간편한 수급자 통합관리 시스템</div>
      </div>
    </div>
    <div class="main_slide">
      <img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_service_info_04.png" >
      <div class="main_slide_desc">
        <h2>판매/대여(소독관리) 간편 관리</h2>
        <div class="desc">현재 보유한 재고를 일괄업로드 후 관리</div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  $('.main_slider').slick({
    autoplay: true,
    autoplaySpeed: 6000,
    dots: true
  });

  $('.main_slider_nav > div').on('click', function() {
    var index = $(this).data('slide');
    $('.main_slider').slick('slickGoTo', index);
  });

  $('.main_slider').on('beforeChange', function(event, slick, currentSlide, nextSlide) {
    $('.main_slider_nav > div').removeClass('active');
    $('.main_slider_nav > div').eq(nextSlide).addClass('active');
  });
});
</script>
