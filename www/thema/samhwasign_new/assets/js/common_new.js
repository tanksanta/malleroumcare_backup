var winFixST = 0;
$(function(){
    eventToggle();
    slickSlide();
});


function scrollToTop() {
    $('html, body').animate({scrollTop: 0 }, 300);
}


$(document).ready(function() {

    // 즐겨찾기
    $('.favorite').on('click', function(e) {
        var bookmarkURL = window.location.href;
        var bookmarkTitle = document.title;
        var triggerDefault = false;
 
        if (window.sidebar && window.sidebar.addPanel) {
            // Firefox version < 23
            window.sidebar.addPanel(bookmarkTitle, bookmarkURL, '');
        } else if ((window.sidebar && (navigator.userAgent.toLowerCase().indexOf('firefox') > -1)) || (window.opera && window.print)) {
            // Firefox version >= 23 and Opera Hotlist
            var $this = $(this);
            $this.attr('href', bookmarkURL);
            $this.attr('title', bookmarkTitle);
            $this.attr('rel', 'sidebar');
            $this.off(e);
            triggerDefault = true;
        } else if (window.external && ('AddFavorite' in window.external)) {
            // IE Favorite
            window.external.AddFavorite(bookmarkURL, bookmarkTitle);
        } else {
            // WebKit - Safari/Chrome
            alert((navigator.userAgent.toLowerCase().indexOf('mac') != -1 ? 'Cmd' : 'Ctrl') + '+D 키를 눌러 즐겨찾기에 등록하실 수 있습니다.');
        }
 
        return triggerDefault;
    });

    
});


function eventToggle(){
    $('.right_menu_toggle').on('click', function(){
        if($(this).closest('.right_menu_area').hasClass('on'))
        {
            $(this).closest('.right_menu_area').removeClass('on');
            $(this).closest('.right_menu_area').stop().animate({'right': '-200px'},300);
            $('.right_menu_toggle').html('◀');
            $.cookie('right_menu_area', 'off', { expires: 365, path: '/' });
        } 
        else 
        {
            $(this).closest('.right_menu_area').addClass('on');
            $(this).closest('.right_menu_area').stop().animate({'right': '0px'},300);
            $('.right_menu_toggle').html('▶');
            $.cookie('right_menu_area', 'on', { expires: 365, path: '/' });
        }
    });

    var $category = $("#category");

    $("#menu_open").on("click", function() {
        if( $(this).hasClass("on"))
        {
            hideTopMenu()
        }
        else
        {
            $(this).addClass('on');
            $(this).html('<button type="button" class="close_btn2"><img src="' + g5_url + '/thema/samhwasign_new/assets/img/btn_top_menu_x.png" class="icon_menu">전체카테고리 <i><img src="'  + g5_url + '/thema/samhwasign_new/assets/img/icon_arrow_up.png" class="icon"></button');
            $('.close_btn').addClass('on');
            $category.show()
            $("#category_all_bg").show()
        }
    });
    
    $(document).mouseup(function (e){
        var container = $("#category");
        if( container.has(e.target).length === 0)
            hideTopMenu()
    });

    var $category2 = $("#category");

    $("#menu_open2").on("click", function() {
        if( $(this).hasClass("on"))
        {
            hideTopMenu2()
        }
        else
        {
            $(this).addClass('on');
            $(this).html('<button type="button" class="close_btn2"><img src="' + g5_url + '/thema/samhwasign_new/assets/img/btn_top_menu_x.png" class="icon_menu">전체카테고리 <i><img src="'  + g5_url + '/thema/samhwasign_new/assets/img/icon_arrow_up.png" class="icon"></button');
            $('.close_btn').addClass('on');
            $category2.show()
            $("#category_all_bg").show()
        }
    });
}

function hideTopMenu()
{
    $("#menu_open").removeClass('on');
    $("#menu_open").html('<img src="'  + g5_url + '/thema/samhwasign_new/assets/img/btn_top_menu.png" class="icon_menu">전체카테고리 <i><img src="' + g5_url + '/thema/samhwasign_new/assets/img/icon_arrow_down.png" class="icon">');
    $("#category").hide()
    $("#category_all_bg").hide()
}

function hideTopMenu2()
{
    $("#menu_open2").removeClass('on');
    $("#menu_open2").html('<img src="'  + g5_url + '/thema/samhwasign_new/assets/img/btn_top_menu.png" class="icon_menu">전체카테고리 <i><img src="' + g5_url + '/thema/samhwasign_new/assets/img/icon_arrow_down.png" class="icon">');
    $("#category").hide()
    $("#category_all_bg").hide()
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
	if (currentScrollTop > 90) {
		$('.scroll_top').stop().css({'top': '0px'},300).promise().then(function() {
            $('.menu-content').addClass('fixed');
            $('#category').addClass('fixed');
        });
         
        $('.btn_top_scroll').stop().animate({'opacity': '1'},300);
		$('.right_menu_area').stop().animate({'padding': '110px 20px 0'},300);
		
	}else {
		$('.scroll_top').stop().css({'top': '-110px'},300);
        $('.btn_top_scroll').stop().animate({'opacity': '0'},300);
		$('.right_menu_area').stop().animate({'padding': '190px 20px 0'},300);
        $('#category').removeClass('fixed');
        $('.menu-content').removeClass('fixed');
	}
}


//프로젝트 동영상 리사이징
$(window).on(function(){
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
    if ($('.slick').slick) {
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
}