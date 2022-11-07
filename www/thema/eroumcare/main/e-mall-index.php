<?php

  if(!defined("_GNUBOARD_")) exit;

?>
<div class="main_content" style="width: 100%; height: 100%;position: relative;">
    <div class="mobile_service_top" style="position: relative; text-align: center; padding-top: 30px;">
        <img id="service_top_img" src="<?=G5_URL?>/thema/eroumcare/assets/img/hd_logo.png" style="width: 40%; margin: 15px auto; display: block;" >
        <p>장기요양기관</p>
        <p>통합관리시스템</p>
        <p> </p>
        <p>이로움만의 장기요양기관 통합관리시스템으로<br>모든 것을 쉽고 편하게 관리해보세요</p>
        <p><a href="/bbs/login.php">로그인 <span>⇀</span></a></p>
        <p><a href="/bbs/register.php">회원가입 <span>⇀</span></a></p>
        <img id="main_mobile_wrap_img" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_wrap.png" style="width: 100%; z-index: 2;" >
    </div>

    <div class="service_content"style="position: relative;">
        <img id="service_content_img_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_content.png" style="width: 100%;" >
        <img id="service_content_img_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_content.png" style="width: 100%;" >
    </div>

    <div class="main_top_wrap" style="width: 100%; background-color: #F5F5F5;position: relative;">
        <img id="main_mobile_wrap_img" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_wrap.png" style="width: 100%;" >
        <img id="main_top_wrap_img_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_code1.png" style="width: 60%;" >
        <img id="main_top_wrap_img_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_code1.png" style="width: 80%; margin:auto;" >
        <div class="main_slider">
            <div class="content_slide">
                <img class="content_slide_img1_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_01.png" style="width: 100%;" >
                <img class="content_slide_img1_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_01.png" style="margin: auto; width: 80%; " >
            </div>
            <div class="content_slide">
                <img class="content_slide_img2_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_02.png" style="width: 100%;" >
                <img class="content_slide_img2_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_02.png" style="margin: auto; width: 80%;" >
            </div>
            <div class="content_slide">
                <img class="content_slide_img3_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_03.png" style="width: 100%;" >
                <img class="content_slide_img3_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_03.png" style="margin: auto; width: 80%;" >
            </div>
            <div class="content_slide">
                <img class="content_slide_img4_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_04.png" style="width: 100%;" >
                <img class="content_slide_img4_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_04.png" style="margin: auto; width: 80%;" >
            </div>
        </div>
        <img id="main_mobile_wrap_img" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_wrap.png" style="width: 100%;" >
    </div>

    <div class="service_offer">
        <img id="service_offer_img_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_offer.png" style="width: 100%;" >
        <img id="service_offer_img_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_offer.png" style="width: 100%;" >
    </div>

    <div class="service_infomation" style="width: 100%;">
        <img id="service_infomation_img_large" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_info.png" style="width: 100%;" >
        <img id="service_infomation_img_small" src="<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_info.png" style="width: 100%;" >
    </div>
</div>


<style>
.mobile_service_top {
    background-image: url("<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_mobile_top.png");
    background-repeat: no-repeat;
    background-size: 100% 100%;
}
.mobile_service_top p:nth-child(2) {font-size: 40px; font-weight:550; display: inline-block; color: #000; line-height:1;}
.mobile_service_top p:nth-child(3) {font-size: 45px; font-weight:550; display: inline-block; color: #EF8207; line-height:1;}
.mobile_service_top p:nth-child(4) {height:200px;}
.mobile_service_top p:nth-child(6) {width:90%; margin-top: 10px; font-size: 20px; background-color: #0c0c0c; font-weight:550; display: inline-block; padding: 15px 0 15px 0; border: 1px solid #0c0c0c; color: #F5F5F5;}
.mobile_service_top p:nth-child(6) span{color: #e86b19; font-weight:1000;}
.mobile_service_top p:nth-child(7) {width:90%; margin: 10px 0; font-size: 20px; background-color: #F5F5F5; font-weight:550; display: inline-block; padding: 15px 0 15px 0; border: 1px solid #0c0c0c; color: #0c0c0c;}
.mobile_service_top p:nth-child(7) span{font-weight:1000;}

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

@media (max-width: 1200px) {
  .main_top_wrap .slick-prev,
  .main_top_wrap .slick-next {
    top: 100px;
  }
}

@media (min-width: 420px) {
  .content_slide_img1_large,
  .content_slide_img2_large,
  .content_slide_img3_large,
  .content_slide_img4_large,
  #service_infomation_img_large,
  #service_offer_img_large,
  #service_content_img_large,
  #main_top_wrap_img_large {
    display:block !important;
  }

  .content_slide_img1_small,
  .content_slide_img2_small,
  .content_slide_img3_small,
  .content_slide_img4_small,
  #service_infomation_img_small,
  #service_offer_img_small,
  #service_content_img_small,
  #main_mobile_wrap_img,
  .mobile_service_top,
  #main_top_wrap_img_small {
    display:none !important;
  }
}

@media (max-width: 420px) {
  .main_top_wrap .slick-prev {
    top: 300px;
    left: 60px;
    height:70px;
  }
  .main_top_wrap .slick-next {
    top: 300px;
    right: 60px;
    height:70px;
  }
  .main_top_wrap_img {
    width: 100% !important;
  }

  .content_slide_img1_large,
  .content_slide_img2_large,
  .content_slide_img3_large,
  .content_slide_img4_large,
  #service_infomation_img_large,
  #service_offer_img_large,
  #service_content_img_large,
  #main_top_wrap_img_large {
    display:none !important;
  }

  .content_slide_img1_small,
  .content_slide_img2_small,
  .content_slide_img3_small,
  .content_slide_img4_small,
  #service_infomation_img_small,
  #service_offer_img_small,
  #service_content_img_small,
  #main_mobile_wrap_img,
  .mobile_service_top,
  #main_top_wrap_img_small {
    display:block !important;
  }
  .main_top_wrap{
    margin-top:0px;
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
