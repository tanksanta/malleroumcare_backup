var winFixST = 0;
$(function(){
    eventToggle();
    slickSlide();
});


function scrollToTop() {
    $('html, body').animate({scrollTop: 0 }, 300);
}

function eventToggle(){
    $('.right_menu_toggle').on('click', function(){
       if(!$(this).closest('.right_menu_area').hasClass('on')){
           $(this).closest('.right_menu_area').addClass('on');
           $(this).closest('.right_menu_area').stop().animate({'right': '-180px'},300);
           $('.right_menu_toggle').html('◀');
           //$('body').addClass('scroll-fixed');
       } else {
           $(this).closest('.right_menu_area').removeClass('on');
           $(this).closest('.right_menu_area').stop().animate({'right': '0px'},300);
           $('.right_menu_toggle').html('▶');
           //$('body').removeClass('scroll-fixed');
       }
    });
    $('.top_menu_all').on('click', function(){
       if(!$('.all_menu_wrap').hasClass('on')){
           $('.all_menu_wrap').addClass('on');
           $('.all_menu_wrap').stop().animate({'z-index':'5','opacity': '1'},10);
           $('.top_menu_all').html('<img src="assets/img/btn_top_menu_x.png" class="icon_menu">전체카테고리 <i><img src="assets/img/icon_arrow_up.png" class="icon">');
           //$('body').addClass('scroll-fixed');
       } else {
           $('.all_menu_wrap').removeClass('on');
           $('.all_menu_wrap').stop().animate({'z-index':'-1','opacity': '0'},10);
           $('.top_menu_all').html('<img src="assets/img/btn_top_menu.png" class="icon_menu">전체카테고리 <i><img src="assets/img/icon_arrow_down.png" class="icon">');
           //$('body').removeClass('scroll-fixed');
       }
    });


}



var currentScrollTop = 0;             
window.onload = function() {
	scrollController();
	$(window).on("scroll", function() {
		scrollController();
	});
}             
function scrollController() {
	currentScrollTop = $(window).scrollTop();
	if (currentScrollTop > 250) {
		$('.scroll_top').stop().animate({'top': '0px'},300);
		$('.btn_top_scroll').stop().animate({'opacity': '1'},300);
		$('.right_menu_area').stop().animate({'padding': '110px 20px 0'},300);
	}else {
		$('.scroll_top').stop().animate({'top': '-100px'},300);
		$('.btn_top_scroll').stop().animate({'opacity': '0'},300);
		$('.right_menu_area').stop().animate({'padding': '190px 20px 0'},300);
	}
}


//프로젝트 동영상 리사이징
$(window).load(function(){
	// video size
	var _video = $('.vod_area').find('iframe');
	var video_w = _video.width();
	var video_h = video_w*(9/16);
	_video.css('height', video_h);
	// var 
});
$(window).resize(function(){
	// video size
	var _video = $('.vod_area').find('iframe');
	var video_w = _video.width();
	var video_h = video_w*(9/16);
	_video.css('height', video_h);
	// var 
});


$(window).on('scroll', function(){
    var winST = $(window).scrollTop();
    if($('.store-detail').length > 0){
        var titOT = $('.store-title').offset().top;
        if(winST > titOT){
            $('.store-detail').addClass('fixed-tit');
            $('.desc_detail').addClass('hidden');
            $('.desc_simple').removeClass('hidden');
        } else {
            $('.store-detail').removeClass('fixed-tit');
            $('.desc_detail').removeClass('hidden');
            $('.desc_simple').addClass('hidden');
        }
    }
});



//팝업 오픈
function popOpen(target){
    winFixST = $(window).scrollTop();
    $('body').css({'top': -winFixST});
    $('body').addClass('scroll-fixed');
    $(target).stop().fadeIn(300);
}

//팝업 닫기
function popClose(target){
    $('body').removeClass('scroll-fixed');
    $(target).closest('.popup').stop().fadeOut(300);
    $(window).scrollTop(winFixST);
}

function slickSlide(){
    $('.slick').slick({
        adaptiveHeight: true,
        arrows: false,
        dots: true,
        autoplay : true,
        autoplaySpeed : 5000,
        prevArrow : "<button type='button' class='slick-prev'>Previous</button>",		// 이전 화살표 모양 설정
		nextArrow : "<button type='button' class='slick-next'>Next</button>",
    });
}