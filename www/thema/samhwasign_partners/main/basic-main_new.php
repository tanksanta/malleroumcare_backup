<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 위젯 대표아이디 설정
$wid = 'CMB';

// 게시판 제목 폰트 설정
$font = 'font-16 en';

// 게시판 제목 하단라인컬러 설정 - red, blue, green, orangered, black, orange, yellow, navy, violet, deepblue, crimson..
$line = 'navy';

// 사이드 위치 설정 - left, right
$side = ($at_set['side']) ? 'left' : 'right';

?>
<style>
.widget-index .at-main,
.widget-index .at-side { padding-bottom:0px; }
.widget-index .div-title-underbar { margin-bottom:15px; }
.widget-index .div-title-underbar span { padding-bottom:4px; }
.widget-index .div-title-underbar span b { font-weight:500; }
.widget-index .widget-img img { display:block; max-width:100%; /* 배너 이미지 */ }
.widget-box { margin-bottom:25px; }
</style>

<div class="at-container widget-index">

	<div class="at-container-service">
		<ul>
			<li class="service">
				<div class="header">
					<h1>고객 서비스</h1>
					<!--<a href="#"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_more.png" /></a>-->
				</div>
				<div class="contents">
					<ul>
						<li>
							<a href="<?php echo G5_BBS_URL; ?>/qalist.php">온라인 고객센터</a>
						</li>
						<li>
							<a href="<?php echo G5_BBS_URL; ?>/faq.php?fm_id=1">자주하는 질문</a>
						</li>
						<li class="nateon">
							<a ><img src="<?php echo THEMA_URL; ?>/assets/img/icon_nate.png" />네이트온 (준비중)</a>
						</li>
					</ul>
				</div>
				<div class="footer">
					<div class="left">
						<p>
							영업시간 : 09:00~18:00<br/>
							* 점심 12시~1시, 공휴일, 토/일 휴무
						</p>
					</div>
					<div class="right">
						<a href="tel:<?php echo $default['de_admin_company_tel']; ?>"><img src="<?php echo THEMA_URL; ?>/assets/img/icon_tel.png" /><?php echo $default['de_admin_company_tel']; ?></a>
					</div>
				</div>
			</li>
			<li class="notice">
				<div class="header">
					<h1>공지사항</h1>
					<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_more.png" /></a>
				</div>
				<div class="contents">
					<ul>
						<?php
						$sql = "SELECT * FROM `g5_write_notice` WHERE wr_is_comment = '0' ORDER BY wr_id DESC";
						$result = sql_query($sql);
						while($row = sql_fetch_array($result)) {
						?>
							<li>
								<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice&wr_id=<?php echo $row['wr_id']; ?>">
								<?php echo ( $row['ca_name'] != '공지' && $row['ca_name'] ) ? '<span class="bg-red">'. $row['ca_name'] .'</span>': ''; ?>
								<?php echo get_text($row['wr_subject']); ?></a>
							</li>
						<?php } ?>
					</ul>
				</div>
			</li>
			<li class="download-center">
				<h2>다운로드센터</h2>
				<ul>
					<li class="green">
						<a href="#">제품상세 다운로드<img src="<?php echo THEMA_URL; ?>/assets/img/icon_down_w.png" /></a>
					</li>
					<li class="">
						<a href="#">입금통장 사본<img src="<?php echo THEMA_URL; ?>/assets/img/icon_down_b.png" /></a>
					</li>
					<li class="">
						<a href="#">사업자등록증 사본<img src="<?php echo THEMA_URL; ?>/assets/img/icon_down_b.png" /></a>
					</li>
				</ul>
			</li>
		</ul>
	</div>

	<div id="product-type">
		<h1 class="title">Product Type</h1>
		<ul class="items">
			<li class="red">
				<a href="#">
					<h2>개폐식 액자</h2>
					<p>A4,A3,A2,A1 규격품 당일출고!<br />소형~2M X 4M 주문제작 가능</p>
					<p class="go">자세히 보기 ></p>
					<img src="<?php echo THEMA_URL; ?>/assets/img/img_main_type_01.png" class="product" />
				</a>
			</li>
			<li class="yellow">
				<a href="#">
					<h2>A형 철제입간판</h2>
					<p>전면 또는 테두리형 실사출력을 부착하여<br/>외부에서는 주차금지/안내 및 입간판</p>
					<p class="go">자세히 보기 ></p>
					<img src="<?php echo THEMA_URL; ?>/assets/img/img_main_type_02.png" class="product" />
				</a>
			</li>
			<li class="green">
				<a href="#">
					<h2>벨트차단봉</h2>
					<p>국산기술 및 정품자재 고품질 차단봉.<br/>최저가 판매. 수입산과 비교불가! 확실한 A/S!</p>
					<p class="go">자세히 보기 ></p>
					<img src="<?php echo THEMA_URL; ?>/assets/img/img_main_type_03.png" class="product" />
				</a>
			</li>
			<li class="gray">
				<a href="#">
					<h2>철제POP꽂이</h2>
					<p>규격품 당일출고. 주문제작 가능.<br/>테이블형/행거형/집게형 (백화점/마트/카페/매장)</p>
					<p class="go">자세히 보기 ></p>
					<img src="<?php echo THEMA_URL; ?>/assets/img/img_main_type_04.png" class="product" />
				</a>
			</li>
		</ul>
	</div>

	<!-- <div id="main-review">
		<h1 class="title">추천후기</h1>
		<ul class="reviews">
			<?php
			$sql = " select a.*, b.it_name, d.ca_name from {$g5['g5_shop_item_use_table']} a
	                 		left join {$g5['g5_shop_item_table']} b on (a.it_id = b.it_id)
						 left join {$g5['member_table']} c on (a.mb_id = c.mb_id)
						left join g5_shop_category d on b.ca_id = d.ca_id
						where is_confirm = '1' ORDER BY is_id DESC
						";
			$result = sql_query($sql);
			while($row = sql_fetch_array($result)) {
			?>
			<li>
				<a class="link">
					<div class="img pc">
						<?php echo get_it_image($row['it_id'], 100, 100); ?>
					</div>
					<div class="content">
						<div class="info">
							<div class="img mobile">
								<?php echo get_it_image($row['it_id'], 50, 50); ?>
							</div>
							<div class="subject">
								<p class="score">
									<?php
									for($i=0;$i<$row['is_score'];$i++) {
										echo '<img src="' . THEMA_URL . '/assets/img/icon_star.png" />';
									}
									$str = '';
									if ( $row['is_score'] == '5') {
										$str = '매우만족';
									}else if ( $row['is_score'] == '4') {
										$str = '만족';
									}else if ( $row['is_score'] == '3') {
										$str = '보통';
									}else if ( $row['is_score'] == '2') {
										$str = '불만';
									}else if ( $row['is_score'] == '1') {
										$str = '매우불만';
									}
									echo '<span>' . $str . '</span>';
									?>
								</p>
								<p class="type">
									<span class="cate"><?php echo $row['ca_name']; ?></span>
									<span><?php echo $row['it_name']; ?></span>
								</p>
							</div>
						</div>
						<div class="is_content">
							<?php echo preg_replace("/<img[^>]+\>/i", "", $row['is_content']); ?>
						</div>
					</div>
				</a>
			</li>
			<?php } ?>
		</ul>
	</div> -->
</div>
