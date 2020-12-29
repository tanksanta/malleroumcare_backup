<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

?>
		<?php if($col_name) { ?>
			<?php if($col_name == "two") { ?>
					</div>
					<div class="col-md-<?php echo $col_side;?><?php echo ($at_set['side']) ? ' pull-left' : '';?> at-col at-side">
						<?php include_once($is_side_file); // Side ?>
					</div>
				</div>
			<?php } else { ?>
				</div><!-- .at-content -->
			<?php } ?>
			</div><!-- .at-container -->
		<?php } ?>
	</div><!-- .at-body -->

	<?php if(!$is_main_footer) { ?>
		<footer class="at-footer">
			<nav class="at-links">
				<div class="at-container">
					<ul class="pull-left">
						<li><a href="<?php echo G5_BBS_URL;?>/content.php?co_id=company">사이트 소개</a></li> 
						<li><a href="<?php echo G5_BBS_URL;?>/content.php?co_id=provision">이용약관</a></li> 
						<li><a href="<?php echo G5_BBS_URL;?>/content.php?co_id=privacy">개인정보처리방침</a></li>
						<li><a href="<?php echo G5_BBS_URL;?>/page.php?hid=noemail">이메일 무단수집거부</a></li>
						<!-- <li><a href="<?php echo G5_BBS_URL;?>/page.php?hid=disclaimer">책임의 한계와 법적고지</a></li> -->
					</ul>
					<ul class="pull-right">
						<li><a href="<?php echo G5_BBS_URL;?>/page.php?hid=guide">이용안내</a></li>
						<li><a href="<?php echo $at_href['secret'];?>">문의하기</a></li>
						<li><a href="<?php echo $as_href['pc_mobile'];?>"><?php echo (G5_IS_MOBILE) ? 'PC' : '모바일';?>버전</a></li>
					</ul>
					<div class="clearfix"></div>
				</div>
			</nav>
			<div class="at-infos">
				<div class="at-container">
					<?php if(IS_YC) { // YC5 ?>
						<div class="media">
							<!-- <div class="pull-right hidden-xs">
								하단 우측 아이콘
							</div>
							<div class="pull-left hidden-xs">
								하단 좌측 로고
								<i class="fa fa-leaf"></i>
							</div> -->
							<div class="media-body">
						
								<ul class="at-about hidden-xs">
									<li><b><?php echo $default['de_admin_company_name']; ?></b></li>
									<li>대표 : <?php echo $default['de_admin_company_owner']; ?></li>
									<li><?php echo $default['de_admin_company_addr']; ?></li>
									<li>전화 : <span><?php echo $default['de_admin_company_tel']; ?></span></li>
									<li>사업자등록번호 : <span><?php echo $default['de_admin_company_saupja_no']; ?></span></li>
									<li><a href="http://www.ftc.go.kr/info/bizinfo/communicationList.jsp" target="_blank">사업자정보확인</a></li>
									<li>통신판매업신고 : <span><?php echo $default['de_admin_tongsin_no']; ?></span></li>
									<li>개인정보관리책임자 : <?php echo $default['de_admin_info_name']; ?></li>
									<li>이메일 : <span><?php echo $default['de_admin_info_email']; ?></span></li>
								</ul>
								
								<div class="clearfix"></div>

								<div class="copyright">
									Copyright<strong><?php echo $config['cf_title'];?> <i class="fa fa-copyright"></i></strong>
									<span>All rights reserved.</span>
								</div>

								<div class="clearfix"></div>
							</div>
						</div>
					<?php } else { // G5 ?>
						<div class="at-copyright">
							<i class="fa fa-leaf"></i>
							<strong><?php echo $config['cf_title'];?> <i class="fa fa-copyright"></i></strong>
							All rights reserved.
						</div>
					<?php } ?>
				</div>
			</div>
		</footer>
	<?php } ?>

	
	<style>
	</style>

	<div id="samhwa-tail">
		<div class="header">
			<ul>
				<li>
					<div class="content">
						<h2>입금계좌</h2>
						<div class="bank">
							<?php
							$banks = explode(PHP_EOL, $default['de_bank_account']); 
							?>
							<p>
								<img src="<?php echo THEMA_URL; ?>/assets/img/icon_bank_kb.png">
								<span><?php echo $banks[0]; ?></span>
							</p>
							<p>
								<img src="<?php echo THEMA_URL; ?>/assets/img/icon_bank_wr.png">
								<span><?php echo $banks[1]; ?></span>
							</p>
							<p>
								<img src="<?php echo THEMA_URL; ?>/assets/img/icon_bank_nh.png">
								<span><?php echo $banks[2]; ?></span>
							</p>
							<p class="name">
								<b>예금주 : 삼화에스엔디(주)</b>
								<br/>
								입금자명은 반드시 주문자명과 같게 해주세요.
							</p>
						</div>
					</div>
				</li>
				<li>
					<div class="content">
						<h2>주문/배송조회</h2>
						<div class="kuaidi">
							<p>
								<span>로젠택배 조회</span>
								<a href="#" class='btn'>운송장조회하기</a>
							</p>
							<p>
								<span>운송장 번호를 모르시면</span>
								<a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" class='btn'>로그인 / 주문번호로 조회</a>
								<!--<a href="<?php echo G5_BBS_URL; ?>/login.php" class='btn'>로그인 / 주문번호로 조회</a>-->
							</p>
						</div>
					</div>
				</li>
				<li class="half"> 
					<div class="content">
						<h2>본사/공장</h2>
						<div class="address">
							<div class="left">
								<p>
									영업시간 : 09:00 ~ 18:00<br/>
									*점심 12시~1시, 공휴일, 토/일 휴무<br/>
									<br class="pc"/>
									<br/>
									<?php echo $default['de_admin_company_addr']; ?>
									<br />
									<br/>
									<b>Tel: <?php echo $default['de_admin_company_tel']; ?></b><br/>
									<b>Tel: <?php echo $default['de_admin_company_fax']; ?></b>
								</p>
							</div>
							<div class="right">
								<a href="#">
									<img src="<?php echo THEMA_URL; ?>/assets/img/img_map_gray.png">
									<span>지도보기</span>
								</a>
							</div>
						</div>
					</div>
				</li>
			</ul>
		</div>
		<div class="contents">
			<div class="left">
				<img src="<?php echo THEMA_URL; ?>/assets/img/icon_bottom_logo.png" class="pc">
				<b class="mobile"><?php echo $default['de_admin_company_name']; ?></b>
			</div>
			<div class="right">
				<ul class="menu">
					<li>
						<a href="<?php echo G5_BBS_URL;?>/content.php?co_id=company">회사소개</a>
					</li>
					<li>
						<a href="<?php echo G5_BBS_URL;?>/content.php?co_id=provision">이용약관</a>
					</li>
					<li>
						<a href="<?php echo G5_BBS_URL;?>/content.php?co_id=privacy"><b>개인정보취급방침</b></a>
					</li>
					<li class="mobile-end">
						<a href="<?php echo G5_BBS_URL; ?>/qalist.php">고객센터</a>
					</li>
					<li class="pc">
						<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice">공지사항</a>
					</li>
					<!-- <li class="pc">
						<a href="#">영수증안내</a>
					</li>
					<li class="pc">
						<a href="#">결제오류</a>
					</li> -->
					<li class="pc">
						<a href="#">입금통장</a>
					</li>
					<li class="pc">
						<a href="#">사업자등록증</a>
					</li>
				</ul>
				<p>
					<!--
					<b><?php echo $default['de_admin_company_name']; ?></b>
					-->
					대표 : <?php echo $default['de_admin_company_owner']; ?><span class="pc"> | </span><br class="mobile" />
					<span class="pc">주소 : </span><?php echo $default['de_admin_company_addr']; ?><span class="pc"> | </span><br class="mobile" />
					사업자등록번호 : <span><?php echo $default['de_admin_company_saupja_no']; ?></span><br class="mobile" />
					<a href="http://www.ftc.go.kr/info/bizinfo/communicationList.jsp" target="_blank" style="font-weight:bold" class="pc">사업자정보확인</a>

					<br class="pc" />

					통신판매업신고 : <span><?php echo $default['de_admin_tongsin_no']; ?></span><span class="pc"> | </span><br class="mobile" />
					개인정보관리책임자 : <?php echo $default['de_admin_info_name']; ?><span class="pc"> | </span><br class="mobile" />
					이메일 : <span><?php echo $default['de_admin_info_email']; ?></span>
				</p>
				<p class='copy'>Copyrightⓒ2013 삼화에스앤디(주) All rights reserved.</p>
				<div class="imgs">
					<img src="<?php echo THEMA_URL; ?>/assets/img/bottom_gongjung.gif" class="gongjung">
					<img src="<?php echo THEMA_URL; ?>/assets/img/kcp.png" class="kcp">
				</div>
			</div>
		</div>
	</div>
	<div id="samhwa-tail-footer">
		<div class="content">
			<div class="left">
				<a href="#">
					<img src="<?php echo THEMA_URL; ?>/assets/img/icon_email.png">
					<span>saled@samhwasign.com</span>
				</a>
			</div>
			<div class="right">
				<a href="#">
					<img src="<?php echo THEMA_URL; ?>/assets/img/icon_webhard.png">
					<span><b>Webhard</b> ID: shboard / PW : 2070</span>
				</a>
			</div>
		</div>
	</div>

	<div style="height:250px;" class="mobile"></div>
	<div id="samhwa-mobile-tail" class="mobile">
		<ul>
			<li class="<?php echo $is_main ? 'on' : ''; ?>">
				<a href="<?php echo G5_URL; ?>">
					<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_home_off.png" class='off' />
					<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_home_on.png" class='on' />
					홈
				</a>
			</li>
			<li class="<?php echo $bo_table == 'notice' ? 'on' : ''; ?>">
				<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice">
					<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_notice_off.png" class='off' />
					<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_notice_on.png" class='on' />
					공지사항
				</a>
			</li>
			<li class="<?php echo $_SERVER['REQUEST_URI'] == '/shop/personalpay.php' ? 'on' : ''; ?>">
				<a href="<?php echo G5_SHOP_URL; ?>/personalpay.php">
					<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_pay_off.png" class='off' />
					<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_pay_on.png" class='on' />
					개인결제창
				</a>
			</li>
			<?php if($is_member) { // 로그인 상태 ?>
				<li>
				<a href="<?php echo G5_BBS_URL; ?>/logout.php">
					<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_logout.png" />
					로그아웃
				</a>
			</li>
			<?php }else{ ?>
				<li>
					<a href="<?php echo G5_BBS_URL; ?>/login.php">
						<img src="<?php echo THEMA_URL; ?>/assets/img/mo_menu_login.png" />
						로그인
					</a>
				</li>
			<?php } ?>
		</ul>
	</div>
</div><!-- .wrapper -->

<div class="at-go">
	<div id="go-btn" class="go-btn">
		<span class="go-top cursor"><i class="fa fa-chevron-up"></i></span>
		<span class="go-bottom cursor"><i class="fa fa-chevron-down"></i></span>
	</div>
</div>

<!--[if lt IE 9]>
<script type="text/javascript" src="<?php echo THEMA_URL;?>/assets/js/respond.js"></script>
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
<?php if($is_sticky_nav) { ?>
<script src="<?php echo THEMA_URL;?>/assets/js/sticky.js"></script>
<?php } ?>

<?php echo apms_widget('basic-sidebar'); //사이드바 및 모바일 메뉴(UI) ?>

<?php if($is_designer || $is_demo) include_once(THEMA_PATH.'/assets/switcher.php'); //Style Switcher ?>
