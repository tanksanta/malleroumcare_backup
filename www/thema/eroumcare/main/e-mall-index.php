<?php

  if(!defined("_GNUBOARD_")) exit;

?>
<div class="main_content" style="width: 100%; height: 100%;">
    <div class="service_content">
        <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_content.png" style="width: 100%;" >
    </div>

    <div class="main_top_wrap" style="width: 100%; background-color: #F5F5F5;">
        <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_code1.png" style="width: 60%;" >
        <div class="main_slider">
            <div class="content_slide">
                <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_01.png" style="width: 100%;" >
            </div>
            <div class="content_slide">
                <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_02.png" style="width: 100%;" >
            </div>
            <div class="content_slide">
                <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_03.png" style="width: 100%;" >
            </div>
            <div class="content_slide">
                <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_04.png" style="width: 100%;" >
            </div>
        </div>
    </div>

    <div class="service_offer">
        <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_offer.png" style="width: 100%;" >
    </div>

    <div class="service_infomation" style="width: 100%;">
        <img src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_info.png" style="width: 100%;" >
    </div>
</div>


<style>
.main_slider {
  max-width: 1400px;
  margin: auto;
}

.main_top_wrap .slick-prev {
    font-size: 30px;
    line-height: 1;
    position: absolute;
    top: 60%;
    display: block;
    width: 30px;
    height: 30px;
    padding: 0;
    -webkit-transform: translate(0, -50%);
    -ms-transform: translate(0, -50%);
    transform: translate(0, -50%);
    cursor: pointer;
    color: transparent;
    border: none;
    outline: none;
    background: transparent;
    z-index: 20;
}

.main_top_wrap .slick-next {
    font-size: 30px;
    line-height: 1;
    position: absolute;
    top: 60%;
    display: block;
    width: 30px;
    height: 30px;
    padding: 0;
    -webkit-transform: translate(0, -50%);
    -ms-transform: translate(0, -50%);
    transform: translate(0, -50%);
    cursor: pointer;
    color: transparent;
    border: none;
    outline: none;
    background: transparent;
    z-index: 20;
}
.main_top_wrap .slick-prev {
  top: 250px;
  left: 15px;
}
.main_top_wrap .slick-next {
  top: 250px;
  right: 15px;
}

.main_top_wrap .slick-prev:before {
  display: inline-block;
  font: normal normal normal 14px/1 FontAwesome;
  font-size: 50px;
  line-height: 1.5;
  text-rendering: auto;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: rgba(255,255,255,0);
}

.main_top_wrap .slick-next:before {
  display: inline-block;
  font: normal normal normal 14px/1 FontAwesome;
  font-size: 50px;
  line-height: 1.5;
  text-rendering: auto;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: rgba(255,255,255,0);
}
.main_top_wrap .slick-prev:before {
  content: " \f104 ";
}
.main_top_wrap .slick-next:before {
  content: " \f105 ";
}

@media (max-width: 960px) {
  .main_top_wrap .slick-prev,
  .main_top_wrap .slick-next {
    display: none !important;
  }
}
</style>

<script type="text/javascript">
    $(function(){
        $('.top_fixed_wrap').css('display', 'none');
        $('.top_common_area').css('display', 'none');
        $('.main_top_service_info').css( "display", "inline-block" );
        $('.container_wrap').css( "display", "none" );
        $('.mo_top').css( "display", "none" );
        $('#headerTopQuickMenuWrap').css( "display", "none" );
        $('.btn_top_scroll').css( "display", "none" );
        $('body').css( "padding-top", "0px" );
    });

$(function() {
  $('.main_slider').slick({
    autoplay: true,
    autoplaySpeed: 6000
  });

  $('.main_slider_nav > div').on('click', function() {
    var index = $(this).data('slide');
    $('.main_slider').slick('slickGoTo', index);
  });

  $('.main_slider').on('beforeChange', function(event, slick, currentSlide, nextSlide) {
    $('.main_slider_nav > div').removeClass('active');
    $('.main_slider_nav > div').eq(nextSlide).addClass('active');
  });

  $('.main_top_service_info .service_wrap li').click(function() {
    if(window.innerWidth > 960) return;

    $(this).find('.desc_area').addClass('on');
    $('body').addClass('desc-open');
    $('#btn_close_service_desc').show();
  });

  $('#btn_close_service_desc').click(function() {
    $('.main_top_service_info .service_wrap .desc_area').removeClass('on');
    $('body').removeClass('desc-open');
    $(this).hide();
  });
});
</script>
