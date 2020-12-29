<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<?php
$main_ca_1_result = sql_query(get_mshop_category('', 2));
$i = 0;
while ($main_ca_1_row = sql_fetch_array($main_ca_1_result)) {

	$main_ca_1_row['index'] = sprintf('%02d', $i + 1);

	// 하위 카테고리 가져오기
	$main_ca_1_row['child'] = array();
	$main_ca_2_result = sql_query(get_mshop_category($main_ca_1_row['ca_id'], 4));
	while ($main_ca_2_row = sql_fetch_array($main_ca_2_result)) {
		$main_ca_1_row['child'][] = $main_ca_2_row;
	}
	
	$main_ca_1[] = $main_ca_1_row;
	$i++;
}

$sql = " select * from g5_write_notice where wr_datetime >= '". date('Y-m-d 00:00:00') ."' and wr_id = wr_parent"; 
$new_notice = sql_fetch($sql);

?>
<!--index-->
<!-- menu section -->
<div class="main_manu_wrap">
	<div class="main_manu remove-add">
		<!-- <div class="notice">
			<a href="/bbs/board.php?bo_table=notice">
				<img src="<?php echo THEMA_URL; ?>/assets/img/icon_notice.png">
				<p>
					공지사항
				</p>
			</a>
			<div class="new"><?php echo $new_notice['wr_id'] ? 'N' : ''; ?></div>
		</div> -->
		<div class="container_wrap" id="top-menu-container"> 
				<!-- tabs -->
					<div class="tabbable tabs-left">
						<ul class="nav nav-tabs top-menu">
							<?php for($i=0; $i<count($main_ca_1); $i++) { ?>
								<li class="<?php echo !$i ? 'active' : ''; ?>" onClick="location.href='<?php echo G5_URL; ?>/shop/list.php?ca_id=<?php echo $main_ca_1[$i]['ca_id']; ?>'" style="height:<?php echo 463 / count($main_ca_1) + 1; ?>px;">
									<a data-target="#new-tab-<?php echo $main_ca_1[$i]['index']; ?>" data-hover="tab">
									<?php echo $main_ca_1[$i]['index']; ?>. <?php echo $main_ca_1[$i]['ca_name']; ?>
									</a>
								</li>
							<?php } ?>
						</ul>
						<div class="tab-content top-menu-sub">
							<?php for($i=0; $i<count($main_ca_1); $i++) { ?>
								<div class="tab-pane <?php echo !$i ? 'active' : ''; ?>" id="new-tab-<?php echo $main_ca_1[$i]['index']; ?>">                
									<div class="left-menu">
										<a href="<?php echo G5_URL; ?>/shop/list.php?ca_id=<?php echo $main_ca_1[$i]['ca_id']; ?>"><h3><?php echo $main_ca_1[$i]['ca_name']; ?></h3></a>
										<ul>
											<?php foreach($main_ca_1[$i]['child'] as $child) { ?>
												<li>
													<a href="<?php echo G5_URL; ?>/shop/list.php?ca_id=<?php echo $child['ca_id']; ?>"><?php echo $child['ca_name']; ?></a>
												</li>
											<?php } ?>
										</ul>
									</div>
									<!-- Right Content -->
									<div class="right-content">
										<div class="product-menu">
											<ul>
												<?php for($j=1; $j<=4; $j++) { ?>
													<?php
													if (!$main_ca_1[$i]['ca_main_item_' . $j]) continue;
													$sql = "SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = '{$main_ca_1[$i]['ca_main_item_' . $j]}'";
													$ca_main_item = sql_fetch($sql);
													if (!$ca_main_item['it_id']) continue;

													// 파트너몰 가격 구분
        											$ca_main_item['it_price'] = samhwa_price($ca_main_item, THEMA_KEY);
													?>
													<li onclick="location.href='<?php echo G5_URL; ?>/shop/item.php?it_id=<?php echo $main_ca_1[$i]['ca_main_item_' . $j]; ?>'">
														<?php echo get_it_image($ca_main_item['it_id'], 140, 140); ?>
														<p style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width:140px;"><?php echo $ca_main_item['it_name']; ?></p>
														<p><?php echo $ca_main_item['it_model']; ?></p>
														<p class="price"><?php echo number_format($ca_main_item['it_price']); ?>원</p>
													</li>
												<?php } ?>
											</ul>
										</div>
										<div class="right-image">										
											<div class="img-content">
												<img src="<?php echo G5_DATA_URL.'/category/'.$main_ca_1[$i]['ca_id']; ?>" alt="" class="main_img" style="object-fit:cover;height:416px;" />
												<p>
													<a href="/shop/list.php?ca_id=<?php echo $main_ca_1[$i]['ca_id']; ?>">
														<?php echo $main_ca_1[$i]['ca_name']; ?>
														<span>
															<img src="/thema/samhwasign_new/assets/img/arrow.png" alt="" />
														</span>
													</a>
												</p>
											</div>
										</div>
									</div>
									<!-- Right Content -->
								</div> 
							<?php } ?>
						</div>
					</div>
				<!-- /tabs -->
		</div>

	</div>
</div>
<!-- menu section -->

<div class="mo_main_section">
	<div class="banner_area">
		<img src="/thema/samhwasign_new/assets/img/main_top_banner.png" alt="" />
	</div>
	<div class="menu_area">
		<div class="list_one">
			<a href="/shop/list.php?ca_id=j9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_01.png" alt="" />
				<p>단면삽입식<br>포스터스탠드</p>
			</a>
			<a href="/shop/list.php?ca_id=k9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_02.png" alt="" />
				<p>양면삽입식<br>포스터스탠드</p>
			</a>
			<a href="/shop/list.php?ca_id=l9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_03.png" alt="" />
				<p>실외전용<br>포스터스탠드</p>
			</a>
			<a href="/shop/list.php?ca_id=m9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_04.png" alt="" />
				<p>벨트<br>차단봉</p>
			</a>
		</div>
		<div class="list_one">
			<a href="/shop/list.php?ca_id=o9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_05.png" alt="" />
				<p>POP</p>
			</a>
			<a href="/shop/list.php?ca_id=p9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_06.png" alt="" />
				<p>철제배너</p>
			</a>
			<a href="/shop/list.php?ca_id=q9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_07.png" alt="" />
				<p>LED배너</p>
			</a>
			<a href="/shop/list.php?ca_id=r9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_08.png" alt="" />
				<p>액자</p>
			</a>
		</div>
		<div class="list_one">
			<a href="/shop/list.php?ca_id=s9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_09.png" alt="" />
				<p>이젤</p>
			</a>
			<a href="/shop/list.php?ca_id=t9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_10.png" alt="" />
				<p>메뉴판거치대</p>
			</a>
			<a href="/shop/list.php?ca_id=u9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_11.png" alt="" />
				<p>카다로그거치대</p>
			</a>
			<a href="/shop/list.php?ca_id=v9">
				<img src="<?php echo THEMA_URL; ?>/assets/img/main_menu_12.png" alt="" />
				<p>네온·블랙보드</p>
			</a>
		</div>
	</div>
</div>



<!-- row section -->
<?php echo get_samhwa_content('main_sw_info'); ?>
<!-- row section -->
<!-- best pick -->
<div class="best container_wrap">
	<div class="best-pick">
		<div class="title">
			<h2>인기상품</h2>
		</div>
		<ul class="nav nav-tabs custom-tab">
		    <li class="active"><a data-toggle="tab" href="#tab01"><?php echo get_samhwa_content('best_txt_01'); ?></a></li>
		    <li><a data-toggle="tab" href="#tab02"><?php echo get_samhwa_content('best_txt_02'); ?></a></li>
		    <li><a data-toggle="tab" href="#tab03"><?php echo get_samhwa_content('best_txt_03'); ?></a></li>
		    <li><a data-toggle="tab" href="#tab04"><?php echo get_samhwa_content('best_txt_04'); ?></a></li>
		</ul>
	
	  <div class="tab-content m-tab pt30">
	    <div id="tab01" class="tab-pane p30 fade in active">
	      <!--MD BEST -->
	      <?php
				$list = new item_list();
				$list->set_category('g9', 1);
				$list->set_category('g9', 2);
				$list->set_category('g9', 3);
				$list->set_list_mod(6);
				$list->set_list_row(1);
				$list->set_img_size(155, 155);
				$list->set_list_skin(G5_SHOP_SKIN_PATH.'/list.100.skin.php');
				$list->set_view('it_img', true);
				$list->set_view('it_id', false);
				$list->set_view('it_name', true);
				$list->set_view('it_basic', false);
				$list->set_view('it_cust_price', true);
				$list->set_view('it_price', true);
				$list->set_view('it_price_partner', true);
				$list->set_view('it_price_dealer', true);
				$list->set_view('it_price_dealer2', true);
				$list->set_view('it_icon', true);
				$list->set_view('it_youtube_link', true);
				$list->set_view('it_model', true);
				$list->set_view('sns', false);
				echo $list->run();
				?>
	      <!--MD BEST -->
	    </div>
	    <div id="tab02" class="tab-pane p30 fade">
	      <!--단면 포스터스탠드 -->
	      <?php
				$list = new item_list();
				$list->set_category('b2', 1);
				$list->set_category('b2', 2);
				$list->set_category('b2', 3);
				$list->set_list_mod(6);
				$list->set_list_row(1);
				$list->set_img_size(230, 230);
				$list->set_list_skin(G5_SHOP_SKIN_PATH.'/list.100.skin.php');
				$list->set_view('it_img', true);
				$list->set_view('it_id', false);
				$list->set_view('it_name', true);
				$list->set_view('it_basic', false);
				$list->set_view('it_cust_price', true);
				$list->set_view('it_price', true);
				$list->set_view('it_price_partner', true);
				$list->set_view('it_price_dealer', true);
				$list->set_view('it_price_dealer2', true);
				$list->set_view('it_icon', true);
				$list->set_view('it_youtube_link', true);
				$list->set_view('it_model', true);
				$list->set_view('sns', false);
				echo $list->run();
				?>
	      <!--단면 포스터스탠드 -->
	    </div>
	    <div id="tab03" class="tab-pane p30 fade">
	       <!-- 양면 포스터스탠드 -->
	      <?php
				$list = new item_list();
				$list->set_category('b3', 1);
				$list->set_category('b3', 2);
				$list->set_category('b3', 3);
				$list->set_list_mod(6);
				$list->set_list_row(1);
				$list->set_img_size(155, 155);
				$list->set_list_skin(G5_SHOP_SKIN_PATH.'/list.100.skin.php');
				$list->set_view('it_img', true);
				$list->set_view('it_id', false);
				$list->set_view('it_name', true);
				$list->set_view('it_basic', false);
				$list->set_view('it_cust_price', true);
				$list->set_view('it_price', true);
				$list->set_view('it_price_partner', true);
				$list->set_view('it_price_dealer', true);
				$list->set_view('it_price_dealer2', true);
				$list->set_view('it_icon', true);
				$list->set_view('it_youtube_link', true);
				$list->set_view('it_model', true);
				$list->set_view('sns', false);
				echo $list->run();
				?>
	      <!--양면 포스터스탠드 -->
	    </div>
	    <div id="tab04" class="tab-pane p30 fade">
	       <!--벨트 차단봉-->
	      <?php
				$list = new item_list();
				$list->set_category('b4', 1);
				$list->set_category('b4', 2);
				$list->set_category('b4', 3);
				$list->set_list_mod(6);
				$list->set_list_row(1);
				$list->set_img_size(155, 155);
				$list->set_list_skin(G5_SHOP_SKIN_PATH.'/list.100.skin.php');
				$list->set_view('it_img', true);
				$list->set_view('it_id', false);
				$list->set_view('it_name', true);
				$list->set_view('it_basic', false);
				$list->set_view('it_cust_price', true);
				$list->set_view('it_price', true);
				$list->set_view('it_price_partner', true);
				$list->set_view('it_price_dealer', true);
				$list->set_view('it_price_dealer2', true);
				$list->set_view('it_icon', true);
				$list->set_view('it_youtube_link', true);
				$list->set_view('it_model', true);
				$list->set_view('sns', false);
				echo $list->run();
				?>
	      <!--벨트 차단봉 -->
	    </div>
	  </div>
	</div>
</div>
<!-- best pick -->
<!-- 추천상품 -->
<?php echo get_samhwa_content('main_best'); ?>
<!-- 추천상품 -->
<!--index-->