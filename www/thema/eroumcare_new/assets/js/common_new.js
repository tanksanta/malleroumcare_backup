var winFixST = 0;
$(function () {
  eventToggle();
  //slickSlide();
});

//프로젝트 동영상 리사이징
$(window).load(function () {
  // video size
  var _video = $('.video_wrap').find('iframe');
  var video_w = _video.width();
  var video_h = video_w * (9 / 16);
  _video.css('height', video_h);
  // var
});
$(window).resize(function () {
  // video size
  var _video = $('.video_wrap').find('iframe');
  var video_w = _video.width();
  var video_h = video_w * (9 / 16);
  _video.css('height', video_h);
  // var
});

function scrollToTop() {
  $('html, body').animate({ scrollTop: 0 }, 300);
}

function scrollToBack() {
  window.history.back();
}

function eventToggle() {
  $('.right_menu_toggle').on('click', function () {
    if (!$(this).closest('.right_menu_area').hasClass('on')) {
      $(this).closest('.right_menu_area').addClass('on');
      $(this)
        .closest('.right_menu_area')
        .stop()
        .animate({ right: '-180px' }, 300);
      $('.right_menu_toggle').html('◀');
      //$('body').addClass('scroll-fixed');
      $.cookie('right_menu_area', 'on', { expires: 365, path: '/' });
    } else {
      $(this).closest('.right_menu_area').removeClass('on');
      $(this).closest('.right_menu_area').stop().animate({ right: '0px' }, 300);
      $('.right_menu_toggle').html('▶');
      //$('body').removeClass('scroll-fixed');
      //$.removeCookie('right_menu_area');
      $.cookie('right_menu_area', 'off', { expires: 365, path: '/' });
    }
  });
  $('.top_menu_all').on('click', function () {
    if (!$('.all_menu_wrap').hasClass('on')) {
      $('.all_menu_wrap').addClass('on');
      $('.all_menu_wrap').stop().animate({ 'z-index': '5', opacity: '1' }, 10);
      $('.top_menu_all').html(
        '<img src="' +
          thema_url +
          '/assets/img/btn_top_menu_x.png" ><span>전체 상품 카테고리</span>'
      );
      //$('body').addClass('scroll-fixed');
    } else {
      $('.all_menu_wrap').removeClass('on');
      $('.all_menu_wrap').stop().animate({ 'z-index': '-1', opacity: '0' }, 10);
      $('.top_menu_all').html(
        '<img src="' +
          thema_url +
          '/assets/img/btn_top_menu2.png" ><span>전체 상품 카테고리</span>'
      );
      //$('body').removeClass('scroll-fixed');
    }
  });
  $('.icon_vod').on('click', function () {
    var vod_area = $(this).closest('li').find('.vod_area');

    if (!$(vod_area).hasClass('on')) {
      $(vod_area).addClass('on');
      var _video = $(vod_area).find('iframe');
      var video_w = _video.width();
      var video_h = video_w * (9 / 16);
      _video.css('height', video_h);
    } else {
      $(vod_area).removeClass('on');
    }
  });
  $('.vod_area .btn_close').on('click', function () {
    $('.vod_area').removeClass('on');
  });
}

var currentScrollTop = 0;
window.onload = function () {
  scrollController();
  $(window).on('scroll', function () {
    scrollController();
  });
};
function scrollController() {
  currentScrollTop = $(window).scrollTop();
  if (currentScrollTop > 110) {
    $('.scroll_top')
      .stop()
      .css({ top: '0px' }, 300)
      .promise()
      .then(function () {});
    $('.btn_top_scroll').stop().animate({ opacity: '1' }, 300);
    //$('.all_menu_wrap').addClass('fixed');
  } else {
    $('.scroll_top').stop().css({ top: '-110px' }, 300);
    $('.btn_top_scroll').stop().animate({ opacity: '0' }, 300);
  }
}

//프로젝트 동영상 리사이징
/*
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
*/

$(window).on('scroll', function () {
  var winST = $(window).scrollTop();
  if ($('.store-detail').length > 0) {
    var titOT = $('.store-title').offset().top;
    if (winST > titOT) {
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
function popOpen(target) {
  winFixST = $(window).scrollTop();
  $('body').css({ top: -winFixST });
  $('body').addClass('scroll-fixed');
  $(target).stop().fadeIn(300);
}

//팝업 닫기
function popClose(target) {
  $('body').removeClass('scroll-fixed');
  $(target).closest('.popup').stop().fadeOut(300);
  $(window).scrollTop(winFixST);
}

/*
function slickSlide() {
  $('.slick').slick({
    adaptiveHeight: true,
    arrows: false,
    dots: false,
    autoplay: true,
    autoplaySpeed: 5000,
    prevArrow: "<button type='button' class='slick-prev'>Previous</button>", // 이전 화살표 모양 설정
    nextArrow: "<button type='button' class='slick-next'>Next</button>",
  });
}
*/

$(document).ready(function () {
  // 즐겨찾기
  $('.favorite').on('click', function (e) {
    var bookmarkURL = window.location.href;
    var bookmarkTitle = document.title;
    var triggerDefault = false;

    if (window.sidebar && window.sidebar.addPanel) {
      // Firefox version < 23
      window.sidebar.addPanel(bookmarkTitle, bookmarkURL, '');
    } else if (
      (window.sidebar &&
        navigator.userAgent.toLowerCase().indexOf('firefox') > -1) ||
      (window.opera && window.print)
    ) {
      // Firefox version >= 23 and Opera Hotlist
      var $this = $(this);
      $this.attr('href', bookmarkURL);
      $this.attr('title', bookmarkTitle);
      $this.attr('rel', 'sidebar');
      $this.off(e);
      triggerDefault = true;
    } else if (window.external && 'AddFavorite' in window.external) {
      // IE Favorite
      window.external.AddFavorite(bookmarkURL, bookmarkTitle);
    } else {
      // WebKit - Safari/Chrome
      alert(
        (navigator.userAgent.toLowerCase().indexOf('mac') != -1
          ? 'Cmd'
          : 'Ctrl') + '+D 키를 눌러 즐겨찾기에 등록하실 수 있습니다.'
      );
    }

    return triggerDefault;
  });

  //POINT PICK, SPECIAL PICK
  $('.tab_list ul li').on('click', function (e) {
    var parent = $(this).closest('.container_wrap');
    var className = $(this).data('id');

    parent.find('.tab_list ul li').removeClass('active');
    $(this).addClass('active');

    parent.find('.pick_item_area').hide();
    parent.find('.' + className).show();
  });
  $('.tab_list ul li:first-child').click();

  $('.tab_list2 ul li').on('click', function (e) {
    var parent = $(this).closest('.container_wrap');
    var className = $(this).data('id');

    parent.find('.tab_list2 ul li').removeClass('active');
    $(this).addClass('active');

    parent.find('.pick_item_area').hide();
    parent.find('.' + className).show();
  });
  $('.tab_list2 ul li:first-child').click();

  $('#top_banner_nomore_close').on('click', function (e) {
    $(this).closest('.container_wrap_wide').hide();
  });

  $('#top_banner_nomore').on('click', function (e) {
    $.cookie('top_banner_nomore', 'on', { expires: 3, path: '/' });
    $('#top_banner_nomore_close').click();
  });

  // 상단 헤더 메뉴 마우스오버
  $('.top_menu_wrap .main_menu table td a.title').on('hover', function (e) {
    var parent = $(this).closest('td');
    var modal = $(this).next();

    // console.log(modal.offset());
    if (modal.offset().left > 800) {
      modal.css('right', '0px');
    }

    //$('.top_menu_wrap .main_menu table td .select_menu').hide();
    //modal.show();

    //$('.top_menu_wrap .main_menu table td .select_menu').css('visibility','hidden');
    //modal.css('visibility', 'visible');
  });

  $('.select_menu').on('mouseleave', function (e) {
    //$(this).css('visibility','hidden');
  });
});
