<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

?>
</div>
</div>
</div>


<div class="footer_info container_wrap">
	<ul>
		<li>
			<div class="subtit">고객센터</div>
			<div class="point_txt">
				<?php if ( $member['mb_type'] == 'partner' ) { ?>
					02-2273-8011
				<?php }else{ ?>
					<?php echo $default['de_admin_company_tel']; ?>
				<?php } ?>
			</div>
			월~금 오전 9시 ~ 오후 6시 <br>
			<span class="txt_s">
			(점심시간 12시~1시)<br>
			주말 /  공휴일 휴무</span>
		</li>
		<li class="bank_info">
			
			<div class="subtit">입금계좌</div>
			· <?php echo $banks[0]; ?><br>
			<span class="txt_s">
			*예금주 : <?php echo $default['de_admin_company_name']; ?>
			</span>
			<div class="company_info">
				<a href="<?php echo THEMA_URL; ?>/assets/files/삼화에스앤디고양지점_사업자등록증사본.pdf" target="_blank">사업자등록증 사본 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_print.png" alt="" /></a>
				<a href="<?php echo THEMA_URL; ?>/assets/files/삼화에스앤디고양지점_입금통장사본.pdf" target="_blank">입금통장 사본 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_print.png" alt="" /></a>
			</div>
			
		</li>
		
		<li>
			<div class="subtit">배송조회</div>
			<a href="https://www.ilogen.com/m/personal/tkSearch" target="_blank">로젠택배 배송위치 조회 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_car.png" alt="" /></a>
			<a href="https://kdexp.com/main.kd" target="_blank">경동택배 배송위치 조회 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_car.png" alt="" /></a>
		</li>
		<li>
			<div class="subtit">위치정보</div>
			<span class="txt_s map_addr"><?php echo $default['de_admin_company_addr']; ?></span>
			<a href="http://kko.to/99M6OQrY0" target="_blank">지도보기 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_pin.png" alt="" /></a>
		</li>
	</ul>
</div>

<div class="footer_area">
	<div class="logo"><img src="<?php echo THEMA_URL; ?>/assets/img/footer_logo.png" alt="" /></div>
	<div class="info">
		<p class="link">
			<a href="/bbs/content.php?co_id=new_company">회사소개 </a>   |   <a href="/bbs/content.php?co_id=provision">이용약관</a>    |   <a href="/bbs/content.php?co_id=privacy"><strong>개인정보취급방침</strong></a>    |   <a href="/shop/orderinquiry.php">주문조회(비회원)</a>
		</p>
		<p>
			<?php echo $default['de_admin_company_name']; ?> | 대표 : <?php echo $default['de_admin_company_owner']; ?> ㅣ 사업자등록번호 : <?php echo $default['de_admin_company_saupja_no']; ?> <a href="javascript:;" onclick="window.open('https://www.ftc.go.kr/bizCommPop.do?wrkr_no=3588501550','communicationViewPopup','width=750,height=700,scrollbars=yes')">[사업자정보확인]</a> <span class="pc_only"> ㅣ </span> <span class="mo_br"></span> 통신판매신고번호 : <?php echo $default['de_admin_tongsin_no']; ?><br>
			개인정보보호관리자 : <?php echo $default['de_admin_info_name']; ?> ㅣ 주소 : <?php echo $default['de_admin_company_addr']; ?> <span class="pc_only"> ㅣ </span> <span class="mo_br"></span> Email : <?php echo $default['de_admin_info_email']; ?> | Tel : <?php if ( $member['mb_type'] == 'partner' ) { ?>
				02-2273-8011
			<?php }else{ ?>
				<?php echo $default['de_admin_company_tel']; ?>
			<?php } ?>
		</p>
		<div class="desc">
			본, 쇼핑몰의 모든 정보, 콘텐츠 및 UI, 저작물 등의 저작권은 삼화에스앤디(주)에 있으며, 어떠한 이유에서도<br>
			전시, 전송, 스크래핑, 무단복제, 도용 등은 저작권법(제97조5항)에 의거 금지되어 있으므로 이를 위반 시 법적처벌을 받을 수 있습니다.<br><br>
			Copyright ⓒ2013 삼화에스앤디(주) All righs reserved.
		</div>
	</div>
	<div class="sign"><img src="<?php echo THEMA_URL; ?>/assets/img/footer_info.png" alt="" /></div>
	
</div>  
<!--mobile fixed menu -->
<div class="mobile-fixed">
	<div class="content mo_menu">
		<ul>
			<li>
				<a href="/">
					<img src="<?php echo THEMA_URL; ?>/assets/img/m_menu_01_off.png" alt="" />
					<p>홈</p>
				</a>
			</li>
			<li>
				<a href="/bbs/board.php?bo_table=notice">
					<img src="<?php echo THEMA_URL; ?>/assets/img/m_menu_02_off.png" alt="" />
					<p>공지사항</p>
				</a>
			</li>
			<li>
				<a href="/bbs/mypage.php">
					<img src="<?php echo THEMA_URL; ?>/assets/img/m_menu_03_off.png" alt="" />
					<p>마이페이지</p>
				</a>
			</li>
			<li>
				<?php if($is_member) { // 로그인 상태 ?>
                    <a href="<?php echo $at_href['logout'];?>">
						<img src="<?php echo THEMA_URL; ?>/assets/img/m_menu_04_off.png" alt="" />
						<p>로그아웃</p>
					</a>
                <?php }else{ ?>
                    <a href="<?php echo $at_href['login'];?>">
						<img src="<?php echo THEMA_URL; ?>/assets/img/m_menu_04_on.png" alt="" />
						<p>로그인</p>
					</a>
                <?php } ?>
			</li>
		</ul>
	</div>
</div>
<!--mobile fixed menu -->
<!--[if lt IE 9]>
<script type="text/javascript" src="<?php echo THEMA_URL;?>/<?php echo THEMA_URL; ?>/assets/js/respond.js"></script>
<![endif]-->

<!-- JavaScript -->
<script>
var sub_show = "<?php echo $at_set['subv'];?>";
var sub_hide = "<?php echo $at_set['subh'];?>";
var menu_startAt = "<?php echo ($m_sat) ? $m_sat : 0;?>";
var menu_sub = "<?php echo $m_sub;?>";
var menu_subAt = "<?php echo ($m_subsat) ? $m_subsat : 0;?>";
</script>
<script src="<?php echo THEMA_URL;?>/assets/bs3/js/bootstrap.min.js"></script>
<script src="<?php echo THEMA_URL;?>/assets/js/sly.min.js"></script>
<script src="<?php echo THEMA_URL;?>/assets/js/custom.js"></script>
<script src="<?php echo THEMA_URL;?>/assets/js/jquery.cookie.js"></script>
<?php if($is_sticky_nav) { ?>
<script src="<?php echo THEMA_URL;?>/assets/js/sticky.js"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo THEMA_URL;?>/assets/js/slick.min.js"></script>
<script type="text/javascript" src="<?php echo THEMA_URL;?>/assets/js/common_new.js"></script>
<script>
	$(function(){
        var tabCarousel = setInterval(function() {
            var tabs = $('.top-menu > li'),
                active = tabs.filter('.active'),
                next = active.next('li'),
                toClick = next.length ? next.find('a') : tabs.eq(0).find('a');

            toClick.trigger('hover');
        }, 4000);
        $('.top-menu > li > a').hover(function() {
            $(this).tab('show');
        });
    });
</script>
<script>
    jQuery(function($){
        $(".smenu >a").hover(function(e){
            e.preventDefault();
            $(".smenu.open").removeClass("open")
            $(this).parent().addClass('open');
        })
        if( $(".smenu.open").length == 0 )
            $('.menu-content ul li:first').addClass('open');
    })
</script>
<script>
    $(document).ready(function() {
        $('.header-hamburger-btn').click(function() {
            // $('#samhwa-m-menu').toggle();
            $('#samhwa-m-menu').show(10);
            $('#samhwa-m-menu .wrap').addClass('active');
        });

        $('#samhwa-m-menu .wrap .closer').click(function() {
            $('#samhwa-m-menu').hide(0);
            $('#samhwa-m-menu .wrap').removeClass('active');

        });

        $('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li').click(function() {
			$('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li').removeClass('on');
			$(this).addClass('on');
			//return false;
		});
		$('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li>a').dblclick(function() {
			console.log('aaa');
			window.location = this.href;
			//return false;
		});
    });
</script>
<script>
$(document).ready(function(){
    $(".top_menu_wrap").sticky({topSpacing:50});
  });
</script>
<?php echo apms_widget('basic-sidebar'); //사이드바 및 모바일 메뉴(UI) ?>

<?php if($is_designer || $is_demo) include_once(THEMA_PATH.'/assets/switcher.php'); //Style Switcher ?>

<script type="text/javascript">
wcs_do(); // wetoz : 2020-09-04
</script>