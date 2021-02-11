<script type="text/javascript">
	
	// wetoz : 2020-09-04
	if(!wcs_add) var wcs_add = {};
	wcs_add["wa"] = "s_4372b22f12c2";
	wcs.inflow("samhwasnd.com");
	
	/* 210115 */
	document.addEventListener("message", function(e){
		switch(e.data){
			case "nowPage" :
				history.go(-1);
				break;
		}
	});
	
</script>

<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
include_once(THEMA_PATH.'/assets/thema.php');

// 카테고리
$category = array();
$head_category = array();
$sql = "SELECT * FROM g5_shop_category where length(ca_id) = '2' and ca_use = '1' and ca_main_use = '1' ORDER BY ca_order, ca_id ASC";
$res = sql_query($sql);
while( $row = sql_fetch_array($res) ) {
    $sql = "SELECT * FROM g5_shop_category where  length(ca_id) = '4' and ca_id like '{$row['ca_id']}%' and ca_use = '1' and ca_main_use = '1'  ORDER BY ca_order, ca_id ASC";
    $res2 = sql_query($sql);
    while( $row2 = sql_fetch_array($res2) ) {
        $sql = "SELECT * FROM g5_shop_category where  length(ca_id) = '6' and ca_id like '{$row2['ca_id']}%' and ca_use = '1' and ca_main_use = '1' ORDER BY ca_order, ca_id ASC";
        $res3 = sql_query($sql);
        while( $row3 = sql_fetch_array($res3) ) {
            $row2['sub'][] = $row3;
        }
        $row['sub'][] = $row2;
    }
	$category[] = $row;
	if ( $row['ca_head_use'] ) {
		$head_category[] = $row;
	}
}

$banks = explode(PHP_EOL, $default['de_bank_account']); 

?>
<script src="<?php echo THEMA_URL; ?>/assets/js/ofi.js" type="text/javascript" charset="utf-8"></script>
<script>
$(document).ready(function() {
	objectFitImages();
});
</script>

<?php if ( $_COOKIE['right_menu_area'] == 'on' ) { ?>
<!-- 오른쪽 메뉴 닫기 열기 -->
<style>
.right_menu_area {
	right:-180px;
}
</style>
<?php } ?>

<div class="btn_top_scroll">
	<a onclick="scrollToTop()"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_scroll.png" alt=""></a>
</div>

<div class="mo_top">
	<div class="logoWrap">
		<a href="<?=G5_URL?>"><img src="<?=THEMA_URL?>/assets/img/top_logo.jpg" alt=""></a>
	</div>

	<div class="searchWrap">
		<form name="tsearch" method="get" onsubmit="return tsearch_submit(this);" role="form" class="form">
			<input type="hidden" name="url"	value="<?php echo (IS_YC) ? $at_href['isearch'] : $at_href['search'];?>">
			<input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="search" placeholder="상품명으로 검색하세요."/>
			<button type="submit" id="sch_submit"><img src="<?php echo THEMA_URL; ?>/assets/img//btn_search.jpg"></button>
		</form>
	</div>
	
	<img src="<?=THEMA_URL?>/assets/img/btn_mo_menu.png" alt="" class="header-hamburger-btn">
</div>

<div id="thema_wrapper" class="wrapper <?php echo $is_thema_layout;?> <?php echo $is_thema_font;?>">
	
	<div id="samhwa-m-menu">
		<div class="wrap">
			<div class="closer">
				<img src="<?php echo THEMA_URL; ?>/assets/img/btn_close.png" />
			</div>
			<div class="logo_area">
				<img src="<?php echo THEMA_URL; ?>/assets/img/top_logo_s.png">
			</div>
			<div class="scrollable-wrap">
				<ul class="mobile-cate">   
					<?php foreach($category as $cate) { ?>
						<li class="<?php echo (substr($ca_id, 0, strlen($cate['ca_id'])) === $cate['ca_id']) ? 'on default_on ': ''; ?>" data-id="<?php echo $cate['ca_id']; ?>">
							<a class='title'><?php echo $cate['ca_name']; ?></a> <?php /*href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' data-id="<?php echo $cate['ca_id']; ?>"*/?>
							<?php if ( $cate['sub'] ) { ?>
								<ul class='sub'>
									<?php foreach($cate['sub'] as $sub) { ?>
										<li class="<?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?> ">
											<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='sub-title'><?php echo $sub['ca_name']; ?></a>
										</li>
									<?php } ?>
								</ul>
							<?php } ?>
						</li>
					<?php } ?>
				</ul>
				<?php if($is_member) { // 로그인 상태 ?>
					<a href="<?php echo $at_href['logout'];?>">로그아웃</a>
				<?php }else{ ?>
					<a href="<?php echo $at_href['login'];?>" class="green">로그인</a>
				<?php } ?>
			</div>
		</div>
	</div>
	<script>
	$(document).ready(function() {

		$('.header-system-move-btn').click(function() {
			location.href = "https://eroumcare.com/cmm/cmm2000/cmm2000/selectCmm2003View.do";
		});

		$('.header-hamburger-btn').click(function() {
			// $('#samhwa-m-menu').toggle();
			$('#samhwa-m-menu').show(10);
			$('#samhwa-m-menu .wrap').addClass('active');
		});

		$('#samhwa-m-menu .wrap .closer').click(function() {
			$('#samhwa-m-menu').hide(100);
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

	<div id="wrap">
		<?php
		// 배너
		$bn_md = array();
		$tb_result = sql_query("SELECT * FROM g5_shop_banner WHERE bn_device = 'both' AND ('" .G5_TIME_YMDHIS . "' between bn_begin_time and bn_end_time" . ") AND bn_position = '상단배너' ORDER BY bn_order ASC ");
		while($tb_row = sql_fetch_array($tb_result)) {
			$bn_md[] = $tb_row;
		}
		if ( $bn_md && $_COOKIE['top_banner_nomore'] != 'on' ) {
		?>
		<div class="container_wrap_wide">
			<div class="top_banner_wide">
				<div class="slick">
					<?php foreach($bn_md as $tb_row) { ?>
					<div class="item" style="background-color:<?php echo get_text($tb_row['bn_bgcolor']); ?>;">
						<img src="<?php echo G5_DATA_URL; ?>/banner/<?php echo $tb_row['bn_id']; ?>" title="<?php echo get_text($tb_row['bn_title']); ?>">
					</div>
					<?php }?>
				</div>
				<div class="top_banner_nomore">
					<input type="checkbox" name="top_banner_nomore" value="3" id="top_banner_nomore"><label for="top_banner_nomore">3일동안 보지 않기</label><span id="top_banner_nomore_close">닫기</span>
				</div>
			</div>
		</div>
		<?php } ?>
		<div class="container_wrap txt_center top_common_area">
			<div class="logoWrap">
				<a href="<?=G5_URL?>"><img src="<?=THEMA_URL?>/assets/img/top_logo.jpg" alt=""></a>
			</div>
			
			<div class="searchWrap">
				<form name="tsearch" method="get" onsubmit="return tsearch_submit(this);" role="form" class="form">
					<input type="hidden" name="url"	value="<?php echo (IS_YC) ? $at_href['isearch'] : $at_href['search'];?>">
					<input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="search" placeholder="상품명으로 검색하세요."/>
					<button type="submit" id="sch_submit"><img src="<?php echo THEMA_URL; ?>/assets/img//btn_search.jpg"></button>
				</form>
			</div>
			
			<div class="top_right_area">
				<div class="link_area">
				
					<?php if($is_member) { // 로그인 상태 ?>
						<a href="<?php echo G5_SHOP_URL; ?>/cart.php" >장바구니</a>
						<a href="<?php echo G5_BBS_URL; ?>/mypage.php" >마이페이지</a>
						<a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" >주문/배송</a>
						<a href="<?php echo G5_SHOP_URL; ?>/my.recipient.list.php" >수급자</a>
						<?php if($member['admin']) {?>
							<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
						<?php } ?>
						<?php if($is_samhwa_admin && !$member['admin']) {?>
							<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
						<?php } ?>
						<a href="<?php echo G5_BBS_URL; ?>/logout.php" >로그아웃</a>
					<?php }else{ ?>
						<a href="<?php echo $at_href['login'];?>" class="green">로그인</a>
						<a href="<?php echo $at_href['reg'];?>">회원가입</a>
						<a href="<?php echo $at_href['lost'];?>" class="win_password_lost">정보찾기</a>
					<?php } ?>
					<!-- <?php if ( $member['mb_type'] == 'partner' ) { ?>
						<a href="https://signstand.co.kr/shop/list.php?ca_id=10">파트너전용</a>
					<?php }else{ ?>
						<a href="https://signstand.co.kr/shop/list.php?ca_id=10">기업전용</a>
					<?php } ?> -->
				</div>
				<!-- <div class="search">
					<form name="tsearch" method="get" onsubmit="return tsearch_submit(this);" role="form" class="form">
						<input type="hidden" name="url"	value="<?php echo (IS_YC) ? $at_href['isearch'] : $at_href['search'];?>">
						<input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="search" placeholder="Search"/>
						<a href="#"><img src="<?php echo THEMA_URL; ?>/assets/img//btn_search.png" ></a>
					</form>
				</div> -->
			</div>
			
			<script type="text/javascript" charset="utf-8">
					function pagePrintPreview(){
 
          var browser = navigator.userAgent.toLowerCase();
          if ( -1 != browser.indexOf('chrome') ){
                     window.print();
          }else if ( -1 != browser.indexOf('trident') ){
                     try{
                              //참고로 IE 5.5 이상에서만 동작함
 
                              //웹 브라우저 컨트롤 생성
                              var webBrowser = '<OBJECT ID="previewWeb" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
 
                              //웹 페이지에 객체 삽입
                              document.body.insertAdjacentHTML('beforeEnd', webBrowser);
 
                              //ExexWB 메쏘드 실행 (7 : 미리보기 , 8 : 페이지 설정 , 6 : 인쇄하기(대화상자))
                              previewWeb.ExecWB(7, 1);
 
                              //객체 해제
                              previewWeb.outerHTML = "";
                     }catch (e) {
                              alert("- 도구 > 인터넷 옵션 > 보안 탭 > 신뢰할 수 있는 사이트 선택\n   1. 사이트 버튼 클릭 > 사이트 추가\n   2. 사용자 지정 수준 클릭 > 스크립팅하기 안전하지 않은 것으로 표시된 ActiveX 컨트롤 (사용)으로 체크\n\n※ 위 설정은 프린트 기능을 사용하기 위함임");
                     }
                    
          }
          
}
					
			</script>
			
		</div>
		
		<?php if($member['mb_level'] >= 3){ ?>
			<div id="headerTopQuickMenuWrap">
				<div>
					<div>

						<div class="titleWrap">복지용구 통합관리 시스템</div>
						
						<ul class="listWrap">
							<li>
								<a href="/shop/my.recipient.list.php" title="수급자관리">
									<img src="<?=THEMA_URL?>/assets/img/headerTopIcon01.png" alt="수급자관리_아이콘" class="pc_layout">
									<p class="mo_layout imgWrap">
										<img src="<?=THEMA_URL?>/assets/img/headerTopIcon01.png" alt="수급자관리_아이콘">
									</p>
									<span>수급자관리</span>
								</a>
							</li>
							<li>
								<a href="/shop/my.recipientSpare.list.php" title="예비수급자관리">
									<img src="<?=THEMA_URL?>/assets/img/headerTopIcon02.png" alt="예비수급자관리_아이콘" class="pc_layout">
									<p class="mo_layout imgWrap">
										<img src="<?=THEMA_URL?>/assets/img/headerTopIcon02.png" alt="예비수급자관리_아이콘">
									</p>
									<span>예비수급자관리</span>
								</a>
							</li>
							<li>
								<a href="#" title="보유재고관리">
									<img src="<?=THEMA_URL?>/assets/img/headerTopIcon03.png" alt="보유재고관리_아이콘" class="pc_layout">
									<p class="mo_layout imgWrap">
										<img src="<?=THEMA_URL?>/assets/img/headerTopIcon03.png" alt="보유재고관리_아이콘">
									</p>
									<span>보유재고관리</span>
								</a>
							</li>
							<li>
								<a href="#" title="주문내역관리">
									<img src="<?=THEMA_URL?>/assets/img/headerTopIcon04.png" alt="주문내역관리_아이콘" class="pc_layout">
									<p class="mo_layout imgWrap">
										<img src="<?=THEMA_URL?>/assets/img/headerTopIcon04.png" alt="주문내역관리_아이콘">
									</p>
									<span>주문내역관리</span>
								</a>
							</li>
							<li>
								<a href="#" title="시스템이용안내">
									<img src="<?=THEMA_URL?>/assets/img/headerTopIcon05.png" alt="시스템이용안내_아이콘">
									<span>시스템이용안내</span>
								</a>
							</li>
						</ul>

					</div>
				</div>
			</div>
		<?php } ?>
		
		<div class="mo_menu" style="<?=($member["mb_id"]) ? "margin-top: 20px;" : ""?>">
			<a href="/shop/list.php?ca_id=10">판매품목</a>
			<a href="/shop/list.php?ca_id=20">대여품목</a>
		</div>
	
		<div class="top_menu_wrap">
			<div class="menu_wrap" style="<?=($member["mb_id"]) ? "border-top: 0;" : ""?>">
				<div class="menu"><button class="top_menu_all"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu.jpg" ><span>전체 상품 카테고리</span></button></div>
				<div class="main_menu">
					<table >
						<tr>
						<?php foreach($head_category as $cate) { ?>
							<td>
								<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo $cate['ca_name']; ?></a>
								<div class="select_menu">
									<table class="menu_area">
										<?php foreach($cate['sub'] as $i=>$sub) { ?>
											<?php if ( $i == 0 ) echo '<tr>'; ?>
												<td <?php echo $i == count($cate['sub'])-1 && count($cate['sub']) % 3 ? 'colspan="'.(4-count($cate['sub'])%3).'"' : ''; ?>>
													<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='cate_02 <?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub['ca_name']; ?></a>
													<?php if (!empty($sub['sub'])) { ?>
														<?php foreach($sub['sub'] as $sub2) { ?>
															<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub2['ca_id']; ?>' class='cate_03 <?php echo $sub2['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub2['ca_name']; ?></a>
														<?php } ?>
													<?php } ?>
												</td>
											<?php if ( $i != 0 && $i % 3 == 2 ) echo '</tr><tr>'; ?>
											<?php if ( $i == count($cate['sub'])-1 ) echo '</tr>'; ?>
										<?php } ?>
									</table>
									
									<img src="<?php echo G5_DATA_URL; ?>/category/<?php echo $cate['ca_id']; ?>" alt="" />
								</div>
							</td>
						<?php } ?>
							<td><a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=30">추천상품</a></td>
							<td><a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=60">신상품</a></td>
						</tr>
					</table>
				</div>
				<div class="all_menu_wrap">
					<div class="all_menu">
						<table>
							<?php for($i=0;$i<count($category);$i++) { ?>
								<?php if ( $i == 0 ) echo '<tr>'; ?>
								<td>
									<div class="tit"><a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$category[$i]['ca_id']; ?>' class='sub-title'><?php echo $category[$i]['ca_name']; ?></a></div>
									<?php if ( $category[$i]['sub'] ) { ?>
										<?php foreach($category[$i]['sub'] as $sub) { ?>
											<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='sub-title'><?php echo $sub['ca_name']; ?></a>
										<?php } ?>
									<?php } ?>
								</td>
								<?php if ( $i != 0 && $i % 5 == 4 ) echo '</tr><tr>'; ?>
								<?php if ( $i == count($category)-1 ) echo '</tr>'; ?>
							<?php } ?>
						</table>
					</div>
				</div>
			</div>
			
			
		</div>
		
		<div class="scroll_top">
			<div class="scroll_top_menu">
				<div class="scroll_top_menu_wrap">
					<div class="scroll_top_menu">
						<a href="<?php echo G5_URL; ?>"><img src="<?php echo THEMA_URL; ?>/assets/img//top_logo_s.png"></a>
						<div class="menu_area">
							<?php if($is_member) { // 로그인 상태 ?>
								<a href="<?php echo G5_SHOP_URL; ?>/cart.php" >장바구니</a>
								<a href="<?php echo G5_BBS_URL; ?>/mypage.php" >마이페이지</a>
								<a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" >주문/배송</a>
								<?php if($member['admin']) {?>
									<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
								<?php } ?>
								<?php if($is_samhwa_admin && !$member['admin']) {?>
									<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
								<?php } ?>
								<a href="<?php echo G5_BBS_URL; ?>/logout.php" >로그아웃</a>
							<?php }else{ ?>
								<a href="<?php echo $at_href['login'];?>" class="green">로그인</a>
								<a href="<?php echo $at_href['reg'];?>">회원가입</a>
								<a href="<?php echo $at_href['lost'];?>" class="win_password_lost">정보찾기</a>
							<?php } ?>
							<?php if ( $member['mb_type'] == 'partner' ) { ?>
							<a href="#">파트너전용</a>
							<?php }else{ ?>
							<a href="#">기업전용</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="top_menu_wrap ">
				<div class="menu_wrap">
					<div class="menu"><button class="top_menu_all"><span>전체카테고리</span> <img src="<?php echo THEMA_URL; ?>/assets/img//btn_top_menu.png" ></button></div>
					<div class="main_menu">
						<table >
							<tr>
								<?php foreach($head_category as $cate) { ?>
									<td>
										<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo $cate['ca_name']; ?></a>
										<div class="select_menu">
											<table class="menu_area">
												<?php foreach($cate['sub'] as $i=>$sub) { ?>
													<?php if ( $i == 0 ) echo '<tr>'; ?>
														<td <?php echo $i == count($cate['sub'])-1 && count($cate['sub']) % 3 ? 'colspan="'.(4-count($cate['sub'])%3).'"' : ''; ?>>
															<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='cate_02 <?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub['ca_name']; ?></a>
															<?php if (!empty($sub['sub'])) { ?>
																<?php foreach($sub['sub'] as $sub2) { ?>
																	<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub2['ca_id']; ?>' class='cate_03 <?php echo $sub2['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub2['ca_name']; ?></a>
																<?php } ?>
															<?php } ?>
														</td>
													<?php if ( $i != 0 && $i % 3 == 2 ) echo '</tr><tr>'; ?>
													<?php if ( $i == count($cate['sub'])-1 ) echo '</tr>'; ?>
												<?php } ?>
											</table>
											
											<img src="<?php echo G5_DATA_URL; ?>/category/<?php echo $cate['ca_id']; ?>" alt="" />
										</div>
									</td>
								<?php } ?>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			
		</div>
		
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
		
		<?php if ( $is_main ) { ?>
		<?php @include(THEMA_PATH . '/main/samhwa-main.php'); ?>
		<?php } ?>

		<div class="at-body">
			<?php if($col_name) { ?>
				<div class="at-container">
					<div class="scrollBannerListWrap left" style="<?=($member['mb_level'] >= 3) ? "top: -290px;" : ""?>">
						<ul>
							<li><a href=""><img src="<?php echo THEMA_URL; ?>/assets/img/wing_banner_02.png" alt="" /></a></li>
							<li><a href=""><img src="<?php echo THEMA_URL; ?>/assets/img/wing_banner_03.png" alt="" /></a></li>
						</ul>
					</div>
					
					<div class="scrollBannerListWrap right" style="<?=($member['mb_level'] >= 3) ? "top: -290px;" : ""?>">
						<ul>
							<li><a target="_blank" href="<?php echo THEMA_URL; ?>/assets/카달로그(이로움)_1호.pdf" ><img src="<?php echo THEMA_URL; ?>/assets/img/wing_banner_01.png" alt="" /></a></li>
						</ul>
						
						<div class="todayViewWrap">
							<div class="title">최근 본 상품</div>
							<?php include(THEMA_PATH."/side/boxtodayview.skin.php"); ?>
						</div>
					</div>
				<?php if($col_name == "two") { ?>
					<div class="row at-row">
						<div class="col-md-<?php echo $col_content;?><?php echo ($at_set['side']) ? ' pull-right' : '';?> at-col at-main">		
				<?php } else { ?>
					<div class="at-content">
				<?php } ?>
			<?php } ?>
