<?php

	if(!defined("_GNUBOARD_")) exit;

?>

<div class="main_top_wrap">
	<img src="<?php echo G5_URL; ?>/thema/eroumcare/assets/img/main_top_desc.png" alt="" />
	<p>
		<a href="<?php echo G5_URL; ?>/main.php">전체 상품 보기</a>
	</p>
	
</div>
<div class="service_info">
	<div class="service_tit">
		이로움 주요서비스
		<p>장기요양기관을 위한 편안하게 관리할 수 있는 <span class="m_br"></span> 통합관리시스템을 제공합니다.</p>
	</div>
	<ul>
		<li>
			<div class="service_desc">
				<p>SYSYEM <span>01</span></p>
				<p>복지용구급여</p>
				<p>복지용구 사업소<br>업무편의를 쉽고 간편하게</p>
				<a href="<?php echo G5_URL; ?>/main.php">바로가기</a>
			</div>
			<div class="service_icon">
				<img src="<?php echo G5_URL; ?>/thema/eroumcare/assets/img/main_service_icon_01.png" alt="" />
			</div>
		</li>
		<li>
			<div class="service_desc">
				<p>SYSYEM <span>02</span></p>
				<p>시설급여</p>
				<p>노인요양시설 / 노인요양<br>공동생활가정의 업무를<br>쉽고 간편하게</p>
			</div>
			<div class="service_icon">
				<img src="<?php echo G5_URL; ?>/thema/eroumcare/assets/img/main_service_icon_02.png" alt="" />
			</div>
		</li>
		<li>
			<div class="service_desc">
				<p>SYSYEM <span>03</span></p>
				<p>주야간보호급여</p>
				<p>주야간 보호급여센터<br>업무를 쉽고 간편하게</p>
			</div>
			<div class="service_icon">
				<img src="<?php echo G5_URL; ?>/thema/eroumcare/assets/img/main_service_icon_03.png" alt="" />
			</div>
		</li>
		<li>
			<div class="service_desc">
				<p>SYSYEM <span>04</span></p>
				<p>방문급여</p>
				<p>방문요양/간호/목욕 시설의<br>업무를 쉽고 간편하게</p>
			</div>
			<div class="service_icon">
				<img src="<?php echo G5_URL; ?>/thema/eroumcare/assets/img/main_service_icon_04.png" alt="" />
			</div>
		</li>
		<li>
			<div class="service_desc">
				<p>SYSYEM <span>05</span></p>
				<p>재무회계</p>
				<p>W4C 연동 재무회계서비스를<br>쉽고 간편하게</p>
			</div>
			<div class="service_icon">
				<img src="<?php echo G5_URL; ?>/thema/eroumcare/assets/img/main_service_icon_05.png" alt="" />
			</div>
		</li>
		<li>
			<div class="service_desc">
				<p>SYSYEM <span>06</span></p>
				<p>시니어타운</p>
				<p>노후를 즐겁게하는 실버 커뮤니티<br>각종정보/재취업/<br>노인용품/실버프로그램</p>
			</div>
			<div class="service_icon">
				<img src="<?php echo G5_URL; ?>/thema/eroumcare/assets/img/main_service_icon_06.png" alt="" />
			</div>
		</li>
	</ul>
</div>

<!-- 메인 배너 -->
	<div id="mainBannerWrap">
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
	</div>
	

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