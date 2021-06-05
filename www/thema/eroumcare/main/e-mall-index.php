<?php

	if(!defined("_GNUBOARD_")) exit;

?>

<div class="main_top_wrap">
	<div class="main_slider_nav">
		<div class="active">복지용구 관리</div>
		<!--<div>방문급여</div>
		<div>시설급여</div>
		<div>주야간보호급여</div>
		<div>재무회계</div>-->
	</div>
	<div class="main_slider">
		<div class="main_slide">
			<div class="main_slide_head">
				<div class="desc">복지용구 사업소 업무를 쉽고 간편하게</div>
				<h2>복지용구 통합관리 시스템</h2>
			</div>
			<img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_service_info_01.png" alt="복지용구 통합관리 시스템">
		</div>
	</div>
</div>

<div class="service_info">
	<div class="service_tit">
		서비스 이용문의
		<p>복지용구 판매 사업소 및 요양센터 운영담당자 들의 문의를 받습니다.</p>
	</div>
	<a href="#" onclick="alert('준비 중입니다.')" class="service_link">간편 문의하기</a>
</div>

<!-- 메인 배너 -->
	<!--<div id="mainBannerWrap">
		<div class="listWrap">
			<div class="slick">
				<div class="item">
					<a href="/bbs/content.php?co_id=guide">
						<img src="<?=THEMA_URL?>/assets/img/main_c_banner_01.png" alt="" class="pc_layout">
						<img src="<?=THEMA_URL?>/assets/img/main_c_banner_m_01.jpg" alt="" class="mo_layout">
					</a>
				</div>
			</div>
		</div>
	</div>-->
	

<!-- 메인 최근게시글 -->
	<div id="mainBoardListWrap">
		<div class="customer">
			<div class="title">
				<span>이로움 고객만족센터</span>
			</div>
			
			<ul class="info">
				<li class="call">
					<img src="<?=THEMA_URL?>/assets/img/mainCallIcon.png" alt="">
					<p>
						<?php 
                            $manager_hp="";
                            $manager_name="";
                            if($member['mb_manager']){
                                $sql_m ='select * from `g5_member` where `mb_id` = "'.$member['mb_manager'].'"';
                                $result_m = sql_fetch($sql_m);
                                $manager_hp = $result_m['mb_hp'];
                                $manager_name = $result_m['mb_name'];
                            }
                            if($manager_hp){
                        ?>
                            <span class="Label"><?=$manager_name?> <span style="font-size:11px;">(담당자)</span> </span>
                            <span class="value" ><?=$manager_hp?></span>
                        <?php 
                            }else{
                        ?>
                            <span class="Label">주문안내</span>
                            <span class="value">032-562-6608</span>
                        <?php } ?>
					</p>
					<p>
						<span class="Label">시스템안내</span>
						<span class="value">02-830-1301~2</span>
					</p>
				</li>
				<li class="time">월~금 09:00~18:00 (점심시간 12시~13시)</li>
				<li class="etc">
					<p>
						<span>Email</span>
						<span class="line"></span>
						<span><?php echo $default['de_admin_info_email']; ?></span>
					</p>
					<p>
						<span>Fax</span>
						<span class="line"></span>
						<span><?php echo $default['de_admin_company_fax']; ?></span>
					</p>
				</li>
			</ul>
		</div>
		
		<div class="board">
			<div class="title">
				<span>공지사항</span> 
				<a href="/bbs/board.php?bo_table=notice" title="더보기">더보기<i class="fa fa-plus-square-o"></i></a>
			</div>
			<?php  echo latest('list_main', 'notice', 5, 30); ?>
		</div>
		
		<div class="board">
			<div class="title">
				<span>자주하는 질문</span>
				<a href="/bbs/board.php?bo_table=faq" title="더보기">더보기<i class="fa fa-plus-square-o"></i></a>
			</div>
			<?php  echo latest('list_main', 'faq', 5, 30); ?>
		</div>
	</div>