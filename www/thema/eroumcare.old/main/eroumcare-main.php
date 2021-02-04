<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<style>
.at-body {
    display:none;
}
</style>
	<div class="container_wrap_wide">
		<!-- <div class="container_wrap">
			<div class="sns_notice">
				<div class="sns_wrap">
					<div class="img_wrap">
						<img src="<?php echo THEMA_URL; ?>/assets/img/main_sns_blog.png" alt="" />
					</div>
					<div class="info">
						<p class="blog">NAVER BLOG</p>
						<p>제품 설치사례 실시간 확인<br>원하는 사이즈 주문제작 방법</p>
					</div>
					<a href="#">바로가기 <img src="<?php echo THEMA_URL; ?>/assets/img/btn_arrow_icon.png" alt="" /></a>
				</div>
				<div class="sns_wrap">
					<div class="img_wrap">
						<img src="<?php echo THEMA_URL; ?>/assets/img/main_sns_instar.png" alt="" />
					</div>
					<div class="info">
						<p class="instar">Instagram</p>
						<p>실제 제품이미지 확인<br>사용방법 확인</p>
					</div>
					<a href="#">바로가기 <img src="<?php echo THEMA_URL; ?>/assets/img/btn_arrow_icon.png" alt="" /></a>
				</div>
				<div class="notice_wrap">
					<div class="top">
						Notice
						<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice"> <img src="<?php echo THEMA_URL; ?>/assets/img/btn_more.png" alt="" /> </a>
					</div>
					<ul>
		                        <?php
						$sql = "SELECT * FROM `g5_write_notice` WHERE wr_is_comment = '0' ORDER BY wr_id DESC";
						$result = sql_query($sql);
						while($row = sql_fetch_array($result)) {
						?>
						<li><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice&wr_id=<?php echo $row['wr_id']; ?>">· <?php echo get_text($row['wr_subject']); ?> <div class="date"><?php echo substr($row['wr_datetime'], 5, 5) ;?></div></a></li>
		                        <?php } ?>
					</ul>
				</div>
			</div>
			
		</div> -->
		
		
		
		<?php
		// 배너
		$bn_md = array();
		$tb_result = sql_query("SELECT * FROM g5_shop_banner WHERE bn_device = 'both' AND ('" .G5_TIME_YMDHIS . "' between bn_begin_time and bn_end_time" . ") AND bn_position = '섹션배너' ORDER BY bn_order ASC ");
		while($tb_row = sql_fetch_array($tb_result)) {
			$bn_md[] = $tb_row;
		}
		if ( $bn_md ) {
		?>
		<div class="container_wrap">
			<div class="slick">
				<?php foreach($bn_md as $tb_row) { ?>
				<div class="item">
					<a href="<?php echo $tb_row['bn_url']; ?>"><img src="<?php echo G5_DATA_URL; ?>/banner/<?php echo $tb_row['bn_id']; ?>" alt=""></a>
				</div>
				<?php }?>
			</div>
		</div>
		<?php } ?>
		
		<?php
		$various_products_code = 'g0';
		$various_products_cnt = sql_fetch("SELECT count(*) as cnt FROM g5_shop_item WHERE 
			ca_id = '{$various_products_code}' OR 
			ca_id2 = '{$various_products_code}' OR 
			ca_id3 = '{$various_products_code}' OR
			ca_id4 = '{$various_products_code}' OR
			ca_id5 = '{$various_products_code}' OR
			ca_id6 = '{$various_products_code}' OR
			ca_id7 = '{$various_products_code}' OR
			ca_id8 = '{$various_products_code}' OR
			ca_id9 = '{$various_products_code}' OR
			ca_id10 = '{$various_products_code}' 
		");
		
		if ($various_products_cnt['cnt']) {
		?>
		<div class="container_wrap">
			<div class="main_section_tit">VARIOUS PRODUCTS</div>
			<?php
			$list = new item_list();
			$list->set_category($various_products_code, 1);
			$list->set_category($various_products_code, 2);
			$list->set_category($various_products_code, 3);
			$list->set_list_mod(4);
			$list->set_list_row(2);
			$list->set_img_size(230, 230);
			$list->set_list_skin(G5_SHOP_SKIN_PATH.'/list.10.skin.php');
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
		</div>
		<?php } ?>
		
		<div class="container_wrap">
			<div class="main_section_tit">베스트 판매 제품 추천 </div>
			<div class="tab_list_wrap">
				<div class="tab_list">
					<ul>
						<li class="active" data-id="point_tab1">포스터스탠드</li>
						<li data-id="point_tab2">벨트차단봉</li>
						<li data-id="point_tab3">카다로그,메뉴판거치대</li>
						<li data-id="point_tab4">철제POP</li>
						<li data-id="point_tab5">이젤/판넬/반제스탠드</li>
					</ul>
				</div>
			</div>
			<div class="pick_item_area point_tab1">
				<?php echo get_samhwa_content('special_pick1'); ?>
			</div>
			<div class="pick_item_area point_tab2">
				<?php echo get_samhwa_content('special_pick2'); ?>
			</div>
			<div class="pick_item_area point_tab3">
				<?php echo get_samhwa_content('special_pick3'); ?>
			</div>
			<div class="pick_item_area point_tab4">
				<?php echo get_samhwa_content('special_pick4'); ?>
			</div>
			<div class="pick_item_area point_tab5">
				<?php echo get_samhwa_content('special_pick5'); ?>
			</div>
			
		</div>
		<?php
		// 배너
		$bn_md = array();
		$tb_result = sql_query("SELECT * FROM g5_shop_banner WHERE bn_device = 'both' AND ('" .G5_TIME_YMDHIS . "' between bn_begin_time and bn_end_time" . ") AND bn_position = 'MD추천상품배너' ORDER BY bn_order ASC ");
		while($tb_row = sql_fetch_array($tb_result)) {
			$bn_md[] = $tb_row;
		}
		if ( $bn_md ) {
		?>
		<div class="container_wrap">
			<div class="main_section_tit">MD추천상품</div>
			<div class="slick">
			<?php foreach($bn_md as $tb_row) { ?>
			<div class="item">
				<a href="<?php echo $tb_row['bn_url']; ?>"><img src="<?php echo G5_DATA_URL; ?>/banner/<?php echo $tb_row['bn_id']; ?>" alt="" title="<?php echo get_text($tb_row['bn_title']); ?>" /></a>
			</div>
			<?php }?>
			</div>
		</div>
		<?php } ?>
		
		<div class="container_wrap">
			<?php @include(THEMA_PATH . '/main/samhwa-instagram.php'); ?>
		</div>

	</div>