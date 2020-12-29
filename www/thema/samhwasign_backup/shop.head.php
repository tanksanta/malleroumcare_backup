<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
include_once(THEMA_PATH.'/assets/thema.php');
?>
<script src="<?php echo THEMA_URL; ?>/assets/js/ofi.js" type="text/javascript" charset="utf-8"></script>
<script>
$(document).ready(function() {
	objectFitImages();
});
</script>
<div id="thema_wrapper" class="wrapper <?php echo $is_thema_layout;?> <?php echo $is_thema_font;?>">
	<div class="header-bar">
		<div class="header-bar-container">
			<ul>
				<li>
					<a href="<?php echo G5_BBS_URL;?>/content.php?co_id=company">회사소개</a>
				</li>
				<li class="yellow">
					<a href="<?php echo G5_SHOP_URL; ?>/personalpay.php">개인결제창</a>
				</li>
				<li>
					<a href="#">제품상세페이지 다운로드</a>
				</li>
			</ul>
		</div>
	</div>
	<!-- LNB -->
	<aside class="at-lnb">
		<div class="at-container">
			<!-- LNB Left -->
			<!-- <div class="pull-left">
				<ul>
					<li><a href="javascript:;" id="favorite">즐겨찾기</a></li>
					<li><a href="<?php echo $at_href['rss'];?>" target="_blank">RSS 구독</a></li>
					<?php
					  $tweek = array("일", "월", "화", "수", "목", "금", "토");
					?>	
					<li><a><?php echo date('m월 d일');?>(<?php echo $tweek[date("w")];?>)</a></li>
				</ul>
			</div> -->
			<!-- LNB Right -->
			<div class="pull-right">
				<ul>
					<?php if($is_member) { // 로그인 상태 ?>
						<li>
							<a style="margin-right:15px;cursor:text;"><b style="color:#009944;"><?php echo $member['mb_nick'];?></b>님 반갑습니다. </a>
							<a href="<?php echo G5_SHOP_URL; ?>/cart.php" >장바구니</a>
						</li>
						<li>
							<a href="<?php echo G5_BBS_URL; ?>/mypage.php" >마이페이지</a>
						</li>
						<li>
							<a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" >주문/배송</a>
						</li>
						<?php if($member['admin']) {?>
							<li><a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a></li>
						<?php } ?>
						<?php if($is_samhwa_admin && !$member['admin']) {?>
							<li><a href="<?php echo G5_ADMIN_URL;?>">관리</a></li>
						<?php } ?>
						<?php if($member['partner']) { ?>
							<li><a href="<?php echo $at_href['myshop'];?>">마이샵</a></li>
						<?php } ?>
						<li class="sidebarLabel"<?php echo ($member['response'] || $member['memo']) ? '' : ' style="display:none;"';?>>
							<a href="javascript:;" onclick="sidebar_open('sidebar-response');"> 
								알림 <b class="orangered sidebarCount"><?php echo $member['response'] + $member['memo'];?></b>
							</a>
						</li>
					<?php } else { // 로그아웃 상태 ?>
						<li><a href="<?php echo $at_href['login'];?>">로그인</a></li>
						<li><a href="<?php echo $at_href['reg'];?>">회원가입</a></li>
						<li><a href="<?php echo $at_href['lost'];?>" class="win_password_lost">정보찾기	</a></li>
					<?php } ?>
					<!-- <?php if(IS_YC) { // 영카트 사용하면 ?>
						<?php if($member['cart'] || $member['today']) { ?>
							<li>
								<a href="<?php echo $at_href['cart'];?>" onclick="sidebar_open('sidebar-cart'); return false;"> 
									쇼핑 <b class="blue"><?php echo number_format($member['cart'] + $member['today']);?></b>
								</a>
							</li>
						<?php } ?>
						<li><a href="<?php echo $at_href['change'];?>"><?php echo (IS_SHOP) ? '커뮤니티' : '쇼핑몰';?></a></li>
					<?php } ?> -->
					<!-- <li><a href="<?php echo $at_href['connect'];?>">접속 <?php echo number_format($stats['now_total']); ?><?php echo ($stats['now_mb']) ? ' (<b class="orangered">'.number_format($stats['now_mb']).'</b>)' : ''; ?></a></li> -->
					<?php if($is_member) { ?>
						<li><a href="<?php echo $at_href['logout'];?>">로그아웃	</a></li>
					<?php } ?>
				</ul>
			</div>
			<div class="clearfix"></div>
		</div>
	</aside>
	
	<!-- PC Header -->
	<header class="pc-header">
		<div class="at-container">
			<!-- PC Logo -->
			<div class="header_logo">
				<a href="<?php echo $at_href['home'];?>">
					<div class="top_logo">
						<div class="logo_icon">
							<img src="<?php echo THEMA_URL;?>/assets/img/top_logo_icon.png" alt="" />
						</div>
						<div class="logo_txt">
							<!-- <p><?php echo THEMA_KEY == 'default' ? '삼화' : '삼화 파트너'; ?></p> -->
							<p>삼화  <span>S&#38;D</span></p>
							<p>Sign & Display</p>
						</div>
					</div>
				</a>
			</div>
			<!-- PC Search -->
			<div class="header-search">
				<form name="tsearch" method="get" onsubmit="return tsearch_submit(this);" role="form" class="form">
				<input type="hidden" name="url"	value="<?php echo (IS_YC) ? $at_href['isearch'] : $at_href['search'];?>">
					<div class="input-group input-group-sm">
						<input type="text" name="stx" class="form-control input-sm" value="<?php echo $stx;?>">
						<span class="input-group-btn">
							<button type="submit" class="btn btn-sm">
								<!--<i class="fa fa-search fa-lg"></i>-->
								<img src="<?php echo THEMA_URL; ?>/assets/img/icon_top_search.png" class="search_img" />
							</button>
						</span>
					</div>
				</form>
				<div class="header-keyword">
					<?php echo apms_widget('basic-keyword', 'basic-keyword', 'q=베이직테마,아미나빌더,그누보드,영카트'); // 키워드 ?>
				</div>
			</div>
			<div class="header-hamburger mobile">
				<img src="<?php echo THEMA_URL; ?>/assets/img/btn_mo_menu.png" />
			</div>
			<div class="clearfix"></div>
		</div>
	</header>
	
	<header class="samhwa-m-scroll-header">
		<div class="header_logo">
			<a href="<?php echo $at_href['home'];?>">
				<div class="top_logo">
					<div class="logo_icon">
						<img src="<?php echo THEMA_URL;?>/assets/img/top_logo_icon.png" alt="" class="logo-img" />
					</div>
					<div class="logo_txt">
						<!-- <p><?php echo THEMA_KEY == 'default' ? '삼화' : '삼화 파트너'; ?></p> -->
						<p>삼화  <span>S&#38;D</span></p>
						<p>Sign & Display</p>
					</div>
				</div>
			</a>
			<div class="header-hamburger mobile">
				<img src="<?php echo THEMA_URL; ?>/assets/img/btn_mo_menu.png" />
			</div>
			<div class="clearfix"></div>
		</div>
	</header>

	<script>
	$(document).ready(function() {
		$(window).scroll(function() {
			if (jQuery(document).scrollTop() > 100) {
				$('.samhwa-m-scroll-header').addClass('scroll')
			} else {
				$('.samhwa-m-scroll-header').removeClass('scroll')
			}
		});
	});
	</script>
	</script>

	<!-- Mobile Header -->
	<?php /*
	<header class="m-header">
		<div class="at-container">
			<div class="header-wrap">
				<div class="header-icon">
					<a href="javascript:;" onclick="sidebar_open('sidebar-user');">
						<i class="fa fa-user"></i>
					</a>
				</div>
				<div class="header-logo en">
					<!-- Mobile Logo -->
					<a href="<?php echo $at_href['home'];?>">
						<b>아미나</b>
					</a>
				</div>
				<div class="header-icon">
					<a href="javascript:;" onclick="sidebar_open('sidebar-search');">
						<i class="fa fa-search"></i>
					</a>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</header>
	*/ ?>

	<div class="clearfix"></div>
	
	<?php include_once(THEMA_PATH . '/shop.cate.php'); ?>

	<div class="clearfix"></div>
	
	<?php if($page_title) { // 페이지 타이틀 ?>
		<div class="at-title">
			<div class="at-container">
				<div class="page-title en">
					<strong<?php echo ($bo_table) ? " class=\"cursor\" onclick=\"go_page('".G5_BBS_URL."/board.php?bo_table=".$bo_table."');\"" : "";?>>
						<?php echo $page_title;?>
					</strong>
				</div>
				<?php if($page_desc) { // 페이지 설명글 ?>
					<div class="page-desc hidden-xs">
						<?php echo $page_desc;?>
					</div>
				<?php } ?>
				<div class="clearfix"></div>
			</div>
		</div>
	<?php } ?>

	<div class="at-body">
		<?php if($col_name) { ?>
			<div class="at-container">
			<?php if($col_name == "two") { ?>
				<div class="row at-row">
					<div class="col-md-<?php echo $col_content;?><?php echo ($at_set['side']) ? ' pull-right' : '';?> at-col at-main">		
			<?php } else { ?>
				<div class="at-content">
			<?php } ?>
		<?php } ?>
