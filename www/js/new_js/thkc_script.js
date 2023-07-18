console.log("test");



let view = true;
// 메뉴 토글
$(".toggle").click(function () {
  if (view == true) {
    $(".toggle").addClass("active");
    /*$(".asideClone").css("right", "-200%");*/

    $(".asideClone")
      .css({right: -322})
      .stop(true, true)
      .animate({right: 0}, 150);

    $(".overlay").show();
    document.body.classList.add("stop-scroll");

  } else {
    $(".toggle").removeClass("active");
    /*$(".asideClone").css("right", "-100%");*/

    $(".asideClone")
      .css({right: 0})
      .stop(true, true)
      .animate({right: -332}, 100, function() {
        $(".toggle").removeClass("active");      
        document.body.classList.remove("stop-scroll");
      });
    setTimeout(function () { $(".overlay").hide(); }, 500);

    view = true;

  }
});


$(".overlay").click(function () {
  /*$(".asideClone").css("right", "-100%");*/

  $(".asideClone")
    .css({right: 0})
    .stop(true, true)
    .animate({right: -332}, 100, function() {
      $(".toggle").removeClass("active");
      document.body.classList.remove("stop-scroll");
    });
  setTimeout(function () { $(".overlay").hide(); }, 500);

});


$(".m_toggle").click(function () {
  /*$(".asideClone").css("right", "-100%");*/

  $(".asideClone")
    .css({right: 0})
    .stop(true, true)
    .animate({right: -332}, 100, function() {
      $(".toggle").removeClass("active");
      document.body.classList.remove("stop-scroll");
    });
  setTimeout(function () { $(".overlay").hide(); }, 500);

});




// aside 메뉴를 가져와서 복제하고 appendTo 한다.
$("#thkc_asideWrap aside").clone().appendTo(".mobileAside");
//$(".asideClone, .overlay").hide();


// 패밀리사이트를 가져와서 복제하고 appendTo 한다.
$(".f_Fsns").clone().appendTo(".m_f_Fsns");

// 이로움 톡톡 배너를 가져와서 복제하고 appendTo 한다.
$(".banner_eroum").clone().appendTo(".m_banner_eroum");


// 사업소 이벤트 Swiper               
var swiper = new Swiper(".eventWrap .eventSwiper", {
  slidesPerView: "auto",
  spaceBetween: 30,
  centeredSlides: true,
  autoplay: {
    delay: 5000,
    disableOnInteraction: false,
  },
  loopAdditionalSlides: 1,
  pagination: {
    el: ".wrap_swiper_navi .swiper-pagination",
    clickable: true,
  },
  navigation: {
    nextEl: ".wrap_swiper_navi .swiper-button-next",
    prevEl: ".wrap_swiper_navi .swiper-button-prev",
  },
});


var swiper = new Swiper(".mySwiper_guide", {
  slidesPerView: "auto",
  centeredSlides: true,
  spaceBetween: 15,
  pagination: {
    // el: ".swiper-pagination",
    clickable: true,
  },
});


var swiper = new Swiper(".mySwiper_service", {
  slidesPerView: "auto",
  centeredSlides: true,
  spaceBetween: 15,
  pagination: {
    // el: ".swiper-pagination",
    clickable: true,
  },
});

//상단 띠배너 스와이퍼
var swiper = new Swiper(".mySwiper_band", {
  navigation: {
    nextEl: ".topBanner_swiper_navi .swiper-button-next",
    prevEl: ".topBanner_swiper_navi .swiper-button-prev",
  },
  loop: true,
});

//Top버튼 올라가기
$("#thkc_pageTop .btn_top").click(function () {
  $("html,body").animate({ scrollTop: 0 }, 300)
});

$(".btn_top").hide();
$(window).scroll(function () {
  let scrollY = window.pageYOffset

  if ($("body").height() / 5 < scrollY) { // 문서 반일때 스크롤 Y이 커질때 나타나라    
    $("#bannerRightClone").fadeOut();
    $(".btn_top").fadeIn();
    $(".btn_top").css({bottom: '70px'});
  } else {
    $(".btn_top").fadeOut();
    $("#bannerRightClone").fadeIn();
  }

})


// 파트너 Swiper               
var swiper = new Swiper(".top_parther .partnerSwiper", {
  slidesPerView: "auto",
  spaceBetween: 30,
  centeredSlides: true,
  autoplay: {
    delay: 5000,
    disableOnInteraction: false,
  },
  loop: true,
  loopAdditionalSlides: 1,
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
  navigation: {
    nextEl: ".wrap_swiper_navi .swiper-button-next",
    prevEl: ".wrap_swiper_navi .swiper-button-prev",
  },
});

// 아이디패스워드 Tap
$(".thkc_tableTapWrap .thkc_tableWrap").eq(1).hide();
$(".tabTitleWrap .tabTitle>div").click(function () {
  $(".tabTitleWrap .tabTitle>div").removeClass("active");
  $(this).addClass("active");

  let i = $(this).index();
  $(".thkc_tableTapWrap .thkc_tableWrap").hide();
  $(".thkc_tableTapWrap .thkc_tableWrap").eq(i).show();
});


// 회원가입 Tap
$(".thkc_JoinConent").eq(1).hide();
$(".thkc_tabJoin .thkc_tabUl>li").click(function () {
  $(".thkc_tabJoin .thkc_tabUl>li").removeClass("active");
  $(this).addClass("active")

  let i = $(this).index();
  $(".thkc_JoinConent").hide();
  $(".thkc_JoinConent").eq(i).show();

});


// 회원가입 약관 아코디언
$(".thkc_agreeWrap .thkc_menu .thkc_dfc03").click(function () {
  // e.preventDefault(); 
  $(".thkc_agreeWrap .thkc_menu .thkc_dfc03").parent().find(".thkc_iner_cont").slideUp();

  if ($(this).hasClass("active")) {
    $(".thkc_agreeWrap .thkc_menu .thkc_dfc03").removeClass("active")
  } else {
    $(this).parent().find(".thkc_iner_cont").slideDown();
    $(".thkc_agreeWrap .thkc_menu .thkc_dfc03").removeClass("active")
    $(this).addClass("active")
  }
})

// 모달 팝업창 (직원 등록)
$(".thkc_popOverlay").hide();
$(".thkc_btnWrap .btn_submit_02").click(function () {
  $(".thkc_popUpWrap").css('display', 'flex').hide().fadeIn();
  $(".thkc_popOverlay").show();
  document.body.classList.add("stop-scroll");
});
$(".thkc_popUpWrap .thkc_close").click(function () {
  $(".thkc_popUpWrap").hide();
  // $(".thkc_popUpWrap").css('display','none').show().fadeOut();
  $(".thkc_popOverlay").hide();
  document.body.classList.remove("stop-scroll");
});


// 모달 팝업창 (배송지 신규등록)
$(".thkc_popOverlay").hide();
$(".thkc_btnWrap .btn_submit_01").click(function () {
  $(".thkc_popUpWrap").css('display', 'flex').hide().fadeIn();
  $(".thkc_popOverlay").show();
  document.body.classList.add("stop-scroll");
});
$(".thkc_popUpWrap .thkc_close").click(function () {
  $(".thkc_popUpWrap").hide();
  // $(".thkc_popUpWrap").css('display','none').show().fadeOut();
  $(".thkc_popOverlay").hide();
  document.body.classList.remove("stop-scroll");
});


///* 패스워드 show,hide */
$(document).on('click', '.icon-eyes-on, .icon-eyes-off', function (e) {
  const $target = $(e.target);
  const $targetBox = $target.closest('.field, .field_01');
  const $targetInput = $targetBox.find('input');
  if
    ($targetBox.hasClass('show')) {
    $targetBox.removeClass('show');
    $targetInput.attr('type', 'text');
    $(".icon-eyes-off").css('display', 'block');
    $(".icon-eyes-on").css('display', 'none');
  } else {
    $targetBox.addClass('show');
    $targetInput.attr('type', 'password');
    $(".icon-eyes-off").css('display', 'none');
    $(".icon-eyes-on").css('display', 'block');
  }
  $targetInput.focus();
});



$("#bannerRight .banner_del").click(function () {
  $("#bannerRight").hide();
  set_cookie("bannerRight", 1, 1, g5_cookie_domain);
});

function go_url(url){
    if(url != '')   window.open(url, '_blank');
}



//Javascript
//최초 로드 시 iframe 높이값 비율에 맞게 세팅
var $videoIframe = $(".item-explan iframe").height();
var responsiveHeight = $videoIframe * 0.6625;
//$videoIframe.setAttribute('height', responsiveHeight);
$(".item-explan iframe").height(responsiveHeight);

//브라우저 리사이즈 시 iframe 높이값 비율에 맞게 세팅
window.addEventListener('resize', function(){
    responsiveHeight = $videoIframe * 0.5625;
    //$videoIframe.setAttribute('height', responsiveHeight);
    $(".item-explan iframe").height(responsiveHeight);
});