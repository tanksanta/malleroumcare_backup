<?php
if(!defined("_GNUBOARD_")) exit;

if(!$is_approved) {
  goto_url(G5_BBS_URL."/register_result.php");
}

// 메인 배너가져오기
$banner_result = sql_query("
  SELECT * FROM {$g5['g5_shop_banner_table']} where (bn_begin_time<=now() and bn_end_time>=now()) or (bn_end_time = '' or bn_end_time = '0000-00-00 00:00:00')
  ORDER BY bn_order, bn_id DESC
");

$banners = [];
while($row = sql_fetch_array($banner_result)) {
  $row['img'] = G5_DATA_URL.'/banner/'.$row['bn_id'];
  //$row['img'] = 'https://eroumcare.com/data/banner/'.$row['bn_id'];

  $banners[] = $row;
}

// 최근 주문내역 건수
$result = sql_fetch("
  SELECT
    count(*) as cnt
  FROM
    g5_shop_cart c
  LEFT JOIN
    g5_shop_order o ON c.od_id = o.od_id
  WHERE
    c.mb_id = '{$member['mb_id']}' and
    c.ct_status IN ('준비', '출고준비', '배송', '완료') and
    o.od_del_yn = 'N' and
    o.od_time >= DATE(NOW() - INTERVAL 3 MONTH)
");
$latest_order_count = $result['cnt'] ?: 0;

$type = chk_itType_deadline();
echo "<script>console.log('updated default : ".$type."');</script>";
?>

<link rel="stylesheet" href="<?php echo G5_URL; ?>/css/swiper.min.css">
<script src="<?php echo G5_URL; ?>/js/swiper.min.js"></script>
<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/main.css?v=08231805">

<?php if ($member['mb_type'] !== 'normal') { ?>
<!-- 메인 상단 슬라이드 -->
<div id="mainTopSlidePCWrap">
  <div class="viewWrap swiper-container">
    <ul style="width: 100%;" class="swiper-wrapper">
      <?php foreach ($banners as $banner) { ?>
      <li style="width: 100%;" class="swiper-slide">
        <a href="<?=$banner['bn_url']?>">
          <img src="<?=$banner['img']?>">
        </a>
      </li>
      <?php } ?>
    </ul>
    <div class="sw-button-prev sw-button pc_only">
      <i class="fa fa-angle-left" aria-hidden="true"></i>
    </div>
    <div class="sw-button-next sw-button pc_only">
      <i class="fa fa-angle-right" aria-hidden="true"></i>
    </div>
  </div>
</div>

<script type="text/javascript">
$(function(){
  var swiper = new Swiper("#mainTopSlidePCWrap .viewWrap", {
    slidesPerView : "auto",
    autoplay : {
      delay : 5000,
      disableOnInteraction: false
    },
    navigation: {
      nextEl: '.sw-button-next',
      prevEl: '.sw-button-prev',
    },
    loop: true
  });
});
</script>
<?php } ?>

<!-- 메인 베스트 상품소개 -->
<div class="best_item_wrap">
    <div class="flex">
        <h3 class="grow">베스트 상품소개</h3>
        <div class="link_wrap">
            <a href="/shop/list.php?ca_id=10" class="btn_default">전체상품 보기</a>
        </div>
    </div>
    <div class="nav_container swiper-container">
        <div class="best_item_nav swiper-wrapper">
            <div class="swiper-slide">
                <span>이동변기(APT-101)</span>
            </div>
            <!-- div class="swiper-slide">
                <span>요실금팬티(LP-021 보나수 50cc)</span>
            </div>
            <div class="swiper-slide">
                <span>안전손잡이(라이팅핸들1)</span>
            </div -->
            <div class="swiper-slide">
                <span>자세변환용구(MPG-06)</span>
            </div>
            <!-- div class="swiper-slide">
                <span>욕창예방방석(트리니티쿠션)</span>
            </div -->
            <div class="swiper-slide">
                <span>실내경사로(TRA-H20)</span>
            </div>
        </div>
        <div class="btn_nav_next">▶</div>
        <div class="btn_nav_prev">◀</div>
    </div>
    <div class="best_item_content">
        <div class="best_item_list" style="display: block;">
            <div class="flex">
                <div class="video_wrap">
                    <iframe width="100%" src="https://www.youtube.com/embed/6-0W0oPmo70" data-src="https://www.youtube.com/embed/6-0W0oPmo70" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
                </div>
                <div class="info_wrap">
                    <div class="flex">
                        <img class="img_product" src="/data/item/PRO2021022500111/7I2464Sk7J28_APT101.jpg">
                        <div class="grow">
                            <p class="name">APT-101</p>
                            <p class="code">급여코드 : T03030060001</p>
                            <p class="size">
                                - 재질 : 목재,우레탄,PP
                                <br>
                                - 사이즈 : 47(폭)X55(길이)X85/88/91(높이)㎝
                                <br>
                                - 중량 : 16.2kg
                                <br>
                            </p>
                        </div>
                    </div>
                    <div class="btn_wrap">
                        <a href="/shop/item.php?it_id=PRO2021022500111">상품 자세히보기</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- div class="best_item_list">
            <div class="flex">
                <div class="video_wrap">
                    <iframe width="100%" src="https://www.youtube.com/embed/Fje8iKlvCaQ" data-src="https://www.youtube.com/embed/Fje8iKlvCaQ" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
                </div>
                <div class="info_wrap">
                    <div class="flex">
                        <img class="img_product" src="/data/item/PRO2021022500716/LP021_7I2464Sk7J28.jpg">
                        <div class="grow">
                            <p class="name">LP-021 보나수 50cc</p>
                            <p class="code">급여코드 : T09061123101</p>
                            <p class="size">
                                - 재질 : 면
                                <br>
                                - 사이즈 : M,L,XL,XXL
                                <br>
                                - 중량 : 0.056kg
                                <br>
                            </p>
                        </div>
                    </div>
                    <div class="btn_wrap">
                        <a href="/shop/item.php?it_id=PRO2021022500716">상품 자세히보기</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="best_item_list">
            <div class="flex">
                <div class="video_wrap">
                    <iframe width="100%" src="https://www.youtube.com/embed/EZmeyQIBgN4" data-src="https://www.youtube.com/embed/EZmeyQIBgN4" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
                </div>
                <div class="info_wrap">
                    <div class="flex">
                        <img class="img_product" src="/data/item/PRO2021022500661/7I2464Sk7J28_65287J207YyF7ZW465Ok167Cw7Iah.jpg">
                        <div class="grow">
                            <p class="name">라이팅핸들1</p>
                            <p class="code">급여코드 : F18031068106</p>
                            <p class="size">
                                - 재질 : 원목, 아연합금
                                <br>
                                - 사이즈 : 40cm, 손잡이둘레 35㎝
                                <br>
                                - 중량 : 0.7kg
                                <br>
                            </p>
                        </div>
                    </div>
                    <div class="btn_wrap">
                        <a href="/shop/item.php?it_id=PRO2021022500661">상품 자세히보기</a>
                    </div>
                </div>
            </div>
        </div -->

        <div class="best_item_list">
            <div class="flex">
                <div class="video_wrap">
                    <iframe width="100%" src="https://www.youtube.com/embed/XfVS_ThJXrA" data-src="https://www.youtube.com/embed/XfVS_ThJXrA" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
                </div>
                <div class="info_wrap">
                    <div class="flex">
                        <img class="img_product" src="/data/item/PRO2021022500229/7I2464Sk7J28_MPG06.jpg">
                        <div class="grow">
                            <p class="name">MPG-06</p>
                            <p class="code">급여코드 : M30030078105</p>
                            <p class="size">
                                - 재질 : 메쉬(외피), 폴리우레탄폼(내장재)
                                <br>
                                - 사이즈 : 40(폭)x104(길이)x2.5/11.5(높이)㎝
                                <br>
                                - 중량 : 2.3kg
                                <br>
                            </p>
                        </div>
                    </div>
                    <div class="btn_wrap">
                        <a href="/shop/item.php?it_id=PRO2021022500229">상품 자세히보기</a>
                    </div>
                </div>
            </div>
        </div>

        <!--div class="best_item_list">
            <div class="flex">
                <div class="video_wrap">
                    <iframe width="100%" src="https://www.youtube.com/embed/a9VcoklA2MU" data-src="https://www.youtube.com/embed/a9VcoklA2MU" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
                </div>
                <div class="info_wrap">
                    <div class="flex">
                        <img class="img_product" src="/data/item/PRO2021022500306/7I2464Sk7J28_7Yq466as64uI7Yuw7Lg7IWY.jpg">
                        <div class="grow">
                            <p class="name">트리니티쿠션</p>
                            <p class="code">급여코드 : H12030130105</p>
                            <p class="size">
                                - 재질 : TPU
                                <br>
                                - 사이즈 : 40(폭)x46(길이)x7(높이)㎝
                                <br>
                                - 중량 : 1.75kg
                                <br>
                            </p>
                        </div>
                    </div>
                    <div class="btn_wrap">
                        <a href="/shop/item.php?it_id=PRO2021022500306">상품 자세히보기</a>
                    </div>
                </div>
            </div>
        </div-->

        <div class="best_item_list">
            <div class="flex">
                <div class="video_wrap">
                    <iframe width="100%" src="https://www.youtube.com/embed/sCE2B5q-wKg" data-src="https://www.youtube.com/embed/sCE2B5q-wKg" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
                </div>
                <div class="info_wrap">
                    <div class="flex">
                        <img class="img_product" src="/data/item/PRO2021022500722/7I2464Sk7J28_TRAH20.jpg">
                        <div class="grow">
                            <p class="name">TRA-H20</p>
                            <p class="code">급여코드 : F24011052102</p>
                            <p class="size">
                                - 재질 : 우레탄
                                <br>
                                - 사이즈 : 77.5(폭)x9.5(깊이)x2(높이)cm
                                <br>
                                - 중량 : 0.82kg
                                <br>
                            </p>
                        </div>
                    </div>
                    <div class="btn_wrap">
                        <a href="/shop/item.php?it_id=PRO2021022500722">상품 자세히보기</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
$(function() {
  var best_item_nav = new Swiper('.best_item_wrap .nav_container', {
    slidesPerView: 'auto',
    autoplay : false,
    centeredSlides: true,
    slideToClickedSlide: true,
    navigation: {
      nextEl: '.btn_nav_next',
      prevEl: '.btn_nav_prev'
    },
    loop: true,
    on: {
      slideChange: onSlideChange
    }
  });

  function onSlideChange() {
    $('.best_item_list .video_wrap iframe').each(function() {
      $(this).attr("src", $(this).data("src"));
    });

    var current_slide = best_item_nav.realIndex;
    var _video = $('.best_item_list').hide().eq(current_slide).show().find('.video_wrap iframe');
    var video_w = _video.width();
    var video_h = video_w * (9 / 16);
    _video.css('height', video_h);
    _video.attr("src", _video.data("src") + '?autoplay=1&amp;mute=1');
  }

  onSlideChange();
});
</script>

<!-- 메인 최근 주문내역 -->
<?php if($latest_order_count > 0) { ?>
<div class="latest_order_area">
  <div class="flex">
    <h3>최근 주문내역</h3>
    <div class="order_desc grow">총 <?=$latest_order_count?>건 <span class="grey">(최근 3개월간의 내역을 조회합니다.)</span></div>
    <div class="link_wrap">
      <a href="/shop/orderinquiry.php" class="btn_default">전체주문 보기</a>
    </div>
  </div>
  <ul class="list_tab">
    <li class="active" data-tab="0"><a href="javascript:void(0);">주문내역</a></li>
    <li data-tab="1"><a href="javascript:void(0);">취소/환불</a></li>
  </ul>
  <div class="latest_order">
    <div class="latest_order_head flex">
    </div>
  </div>
  <ul class="latest_order_list">
  </ul>
</div>

<div id="popupProdBarNumInfoBox" class="listPopupBoxWrap"><div></div></div>
<!-- 210326 재고조회팝업 -->
   
<!-- 210326 배송정보팝업 -->
<div id="popupProdDeliveryInfoBox" class="listPopupBoxWrap"><div></div></div>

<script>
var step_list = ['준비', '출고준비', '배송', '완료'];
var step_name = {
  '준비': '상품준비',
  '출고준비' : '출고준비',
  '배송': '출고완료',
  '완료': '배송완료'
}
var step = '준비';
var tab = 0; // 0: 주문내역 / 1: 취소/환불
function update_latest_order(page) {
  page = page || 1;
  $('.latest_order_head .step').removeClass('active');
  $('.latest_order_head .step[data-step="'+step+'"]').addClass('active');
  $('.latest_order_list').html('<li style="padding: 50px 0; text-align:center;"><img src="/shop/img/loading.gif"></li>');
  $.post('/shop/ajax.order.latest.list.php', {
    ct_status: step,
    page: page,
  }, 'json')
  .done(function(result) {
    $('.latest_order_list').html(result.data);
  });
}
$(function() {
  get_latest_order_count();

  function get_latest_order_count() {
    step = '';
    $('.latest_order_head').hide();
    $('.latest_order_list').html('<li style="padding: 50px 0; text-align:center;"><img src="/shop/img/loading.gif"></li>');

    $.get('/shop/ajax.order.latest.count.php', {}, 'json')
    .done(function(result) {
      var data = result.data;

      $('.latest_order_head').show().empty();

      for(var i = 0; i < step_list.length; i++) {
        var cur_step = step_list[i];
        var count = data[cur_step] ? data[cur_step] : 0;

        if(step === '' && count > 0)
          step = cur_step;

        var next_html = i === (step_list.length - 1) ? '' : '<div class="next">></div>';
        $('.latest_order_head')
        .append('\
          <a href="javascript:void(0);" class="step" data-step="'+cur_step+'">\
            <div class="num">' + count + '</div>\
            <div class="desc">' + step_name[cur_step] + '</div>\
          </a>\
        ')
        .append(next_html);
      }

      update_latest_order();
    });
  }

  $(document).on('click', '.latest_order_head .step', function() {
    if($(this).find('.num').text() > 0) {
      step = $(this).data('step');
      update_latest_order();
    }
  });

  $(document).on('click', '.latest_order_area .list_tab li', function() {
    $('.latest_order_area .list_tab li').removeClass('active');
    $(this).addClass('active');
    tab = parseInt($(this).data('tab'));

    if(tab === 0) {
      // 주문내역
      $('.latest_order_head').show();
      get_latest_order_count();
    }
    else if(tab === 1) {
      // 취소/환불
      $('.latest_order_head').hide();
      step = '취소';
      update_latest_order();
    }
  });

  $('.listPopupBoxWrap').hide().css('opacity', 1);
  $(document).on('click', '.popupDeliveryInfoBtn', function(e) {
    e.preventDefault();
        
    var od = $(this).attr("data-od");
    $("#popupProdDeliveryInfoBox > div").html("<iframe src='/shop/popup.prodDeliveryInfo.php?od_id=" + od + "'>");
    $("#popupProdDeliveryInfoBox iframe").load(function() {
      $("#popupProdDeliveryInfoBox").show();
    });
  });

  $(document).on('click', '.popupProdBarNumInfoBtn', function(e) {
    e.preventDefault();
    var od_id = $(this).attr("data-id");
    var ct_id = $(this).attr("data-ct-id");
    $("#popupProdBarNumInfoBox > div").html("<iframe src='<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form_4.php?od_id=" + od_id +  "&ct_id=" + ct_id +"'>");
    $("#popupProdBarNumInfoBox iframe").load(function(){
      $("#popupProdBarNumInfoBox").show();
    });
  });
});
</script>
<?php } ?>

<!-- 메인 진행중인 이벤트 -->
<?php if ($member['mb_type'] !== 'normal') { ?>
  <?php  echo latest('event_main', 'event', 2); ?>
<?php } ?>

<?php 
$tutorials = get_tutorials();
?>

<?php 
/* if ($member['mb_id'] && $member['mb_type'] === 'default' && !$tutorials && !$is_admin) { 
?>
	<script>
		show_eroumcare_popup({
			id: 'tutorial_start',
			title: '서비스 체험',
			content: '이로움 통합관리시스템을<br/>한번에 체험할 수 있습니다.',
			activeBtn: {
				href: '/shop/tutorial_start.php',
				text: '체험시작하기',
			},
			hideBtn: {
				text: '다음에',
			},
			hideOneWeekBtn: true,
		});
	</script>
<?php } ?>

<?php 
if ($member['mb_id'] && $member['mb_type'] === 'default' && $tutorials && !$is_admin) {
?>
	<?php 
	$t_recipient_add = get_tutorial('recipient_add');
	if ($t_recipient_add['t_state'] == '0') { 
	?>
		<script>
			show_eroumcare_popup({
				title: '수급자 신규등록',
				content: '수급자 등록 체험을 위해<br/>체험용 수급자를 등록해보세요.',
				activeBtn: {
					href: '/shop/my_recipient_write.php?tutorial=true',
					text: '수급자 등록하기',
				},
				hideBtn: {
					text: '다음에',
				}
			});
		</script>
	<?php } ?>


	<?php
	$t_recipient_order = get_tutorial('recipient_order');
	if ($t_recipient_order['t_state'] == '0') {
	?>
	<script>
	show_eroumcare_popup({
	title: '수급자 주문하기',
	content: '수급자 주문을 체험하시겠습니까?<br/>판매품목 1개, 대여품목1개<br/>선택되어 주문을 체험할 수 있습니다.',
	activeBtn: {
		text: '주문체험하기',
		href: '/shop/tutorial_order.php'
	},
	hideBtn: {
		text: '다음에',
	}
	});

	</script>
	<?php
	} 
	?>

	<?php
	$t_document = get_tutorial('document');
	if ($t_document['t_state'] == '0') {
		
		$t_sql = "SELECT e.dc_status FROM tutorial as t INNER JOIN eform_document as e ON t.t_data = e.od_id
		WHERE 
			t.mb_id = '{$member['mb_id']}' AND
			t.t_type = 'recipient_order'
		";
		$t_result = sql_fetch($t_sql);

		if ($t_result['dc_status'] == '2' || $t_result['dc_status'] == '3') {
	?>
		<script>
		show_eroumcare_popup({
			title: '전자문서 확인',
			content: '작성한 전자 계약서를<br/>확인하시겠습니까?',
			activeBtn: {
				text: '전자계약서확인',
				href: '/shop/electronic_manage.php'
			},
			hideBtn: {
				text: '다음에',
			}
		});
		</script>
		<?php } ?>
	<?php } ?>

	<?php
	$t_claim = get_tutorial('claim');
	if ($t_claim['t_state'] == '0') {
	?>
	<script>
	show_eroumcare_popup({
		title: '청구내역 확인',
		content: '수급자 주문 후 누적된 청구내역을<br/>확인 하시겠습니까?',
		activeBtn: {
		text: '청구내역 확인',
		href: '/shop/claim_manage.php'
		},
		hideBtn: {
		text: '다음에',
		}
	});
	</script>
	<?php } 
	?>
<?php } */ ?>
