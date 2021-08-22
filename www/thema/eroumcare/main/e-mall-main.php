<?php
if(!defined("_GNUBOARD_")) exit;

if(!$is_approved) {
  goto_url(G5_BBS_URL."/register_result.php");
}

// 메인 배너가져오기
$banner_result = sql_query("
  SELECT * FROM {$g5['g5_shop_banner_table']}
  ORDER BY bn_order, bn_id DESC
");

$banners = [];
while($row = sql_fetch_array($banner_result)) {
  //$row['img'] = G5_DATA_URL.'/banner/'.$row['bn_id'];
  $row['img'] = 'https://eroumcare.com/data/banner/'.$row['bn_id'];

  $banners[] = $row;
}
?>

<link rel="stylesheet" href="<?php echo G5_URL; ?>/css/swiper.min.css">
<script src="<?php echo G5_URL; ?>/js/swiper.min.js"></script>
<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/main.css">

<!-- 메인 상단 슬라이드 -->
<div id="mainTopSlidePCWrap">
  <div class="viewWrap swiper-container">
    <ul style="width: 100%;" class="swiper-wrapper">
      <?php foreach($banners as $banner) { ?>
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
        <div class="swiper-slide">
          <span>목욕의자(PN-L41621DK 뉴클리어)</span>
        </div>
        <div class="swiper-slide">
          <span>요실금팬티(LP-021 보나수 50cc)</span>
        </div>
    </div>
    <div class="btn_nav_next">▶</div>
    <div class="btn_nav_prev">◀</div>
  </div>
  <div class="best_item_content">
    <div class="best_item_list" style="display: block;">
      <div class="flex">
        <div class="video_wrap">
          <iframe width="100%" src="https://www.youtube.com/embed/6-0W0oPmo70" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
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

    <div class="best_item_list">
      <div class="flex">
        <div class="video_wrap">
          <iframe width="100%" src="https://www.youtube.com/embed/8_7jTlvt2g8" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
        </div>
        <div class="info_wrap">
          <div class="flex">
            <img class="img_product" src="/data/item/PRO2021022500256/7I2464Sk7J28_PNL41621DK_1.jpg">
            <div class="grow">
              <p class="name">PN-L41621DK 뉴클리어</p>
              <p class="code">급여코드 : B03180081602</p>
              <p class="size">
                - 재질 : 알루미늄,EVA
                <br>
                - 사이즈 : 50.5(폭)X50~58.5(길이)X68~78(높이)㎝
                <br>
                - 중량 : 4.7kg
                <br>
              </p>
            </div>
          </div>
          <div class="btn_wrap">
            <a href="/shop/item.php?it_id=PRO2021022500256">상품 자세히보기</a>
          </div>
        </div>
      </div>
    </div>

    <div class="best_item_list">
      <div class="flex">
        <div class="video_wrap">
          <iframe width="100%" src="https://www.youtube.com/embed/Fje8iKlvCaQ" title="YouTube video player" frameborder="0" allowfullscreen=""></iframe>
        </div>
        <div class="info_wrap">
          <div class="flex">
            <img class="img_product" src="/data/item/PRO2021022500716/7I2464Sk7J28_LP02167O064KY7IiY50cc_1.jpg">
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
      slideChange: function() {
        var current_slide = best_item_nav.realIndex;
        var _video = $('.best_item_list').hide().eq(current_slide).show().find('.video_wrap iframe');
        var video_w = _video.width();
        var video_h = video_w * (9 / 16);
        _video.css('height', video_h);
      }
    }
  });
});
</script>

<!-- 메인 최근 주문내역 -->
<div class="latest_order_area" style="display: none;">
  <div class="flex">
    <h3>최근 주문내역</h3>
    <div class="order_desc grow">총 9건 <span class="grey">(최근 3개월간의 내역을 조회합니다.)</span></div>
    <div class="link_wrap">
      <a href="/shop/orderinquiry.php" class="btn_default">전체주문 보기</a>
    </div>
  </div>
  <ul class="list_tab">
    <li class="active"><a href="javascript:void(0);">주문내역</a></li>
    <li><a href="javascript:void(0);">취소/환불</a></li>
  </ul>
  <div class="latest_order">
    <div class="latest_order_head flex">
      <a href="javascript:void(0);" class="step active">
        <div class="num">1</div>
        <div class="desc">상품준비</div>
      </a>
      <div class="next">></div>
      <a href="javascript:void(0);" class="step">
        <div class="num">0</div>
        <div class="desc">출고준비</div>
      </a>
      <div class="next">></div>    
      <a href="javascript:void(0);" class="step">
        <div class="num">12</div>
        <div class="desc">출고완료</div>
      </a>
      <div class="next">></div>
      <a href="javascript:void(0);" class="step">
        <div class="num">93</div>
        <div class="desc">배송완료</div>
      </a>
    </div>
  </div>
</div>

<!-- 메인 진행중인 이벤트 -->
<?php  echo latest('event_main', 'event', 2); ?>

<?php 
$tutorials = get_tutorials();
?>

<?php 
if ($member['mb_id'] && $member['mb_type'] === 'default' && !$tutorials) { 
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
if ($member['mb_id'] && $member['mb_type'] === 'default' && $tutorials) { 
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
<?php } ?>