<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
include_once(THEMA_PATH.'/assets/thema.php');
?>

<div id="thema_wrapper" class="wrapper <?php echo $is_thema_layout;?> <?php echo $is_thema_font;?>">

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
						<li><a href="javascript:;" onclick="sidebar_open('sidebar-user');"><b><?php echo $member['mb_nick'];?></b></a></li>
						<?php if($member['admin']) {?>
							<li><a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a></li>
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
						<li><a href="<?php echo $at_href['login'];?>" onclick="sidebar_open('sidebar-user'); return false;">로그인</a></li>
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
							<p>삼화</p>
							<p>Samhwa <span>Sign</span></p>
							<p>Best choice for promotion</p>
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
							<button type="submit" class="btn btn-sm"><i class="fa fa-search fa-lg"></i></button>
						</span>
					</div>
				</form>
				<div class="header-keyword">
					<?php echo apms_widget('basic-keyword', 'basic-keyword', 'q=베이직테마,아미나빌더,그누보드,영카트'); // 키워드 ?>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</header>

	<!-- Mobile Header -->
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
