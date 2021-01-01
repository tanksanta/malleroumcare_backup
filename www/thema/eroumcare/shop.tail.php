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
	</div><!-- wrap -->


	<!-- <div class="footer_info container_wrap">
		<ul>
			<li>
				<div class="footer_info_wrap">
					<div class="subtit">CALL CENTER</div>
					<div class="point_txt">T. <?php echo $default['de_admin_company_tel']; ?></div>
					F. <?php echo $default['de_admin_company_fax']; ?><br>
					평일 오전 09:00 ~ 오후 06:00<br>
					점심 오전 12:00 ~ 오후 01:00<br>
					휴무 토 / 일 /  공휴일 휴무
				</div>
			</li>
			<li>
				<div class="footer_info_wrap">
					<div class="subtit">BANK ACCOUNT</div>
					<?php
					$banks = explode(PHP_EOL, $default['de_bank_account']);
					?>
					· <?php echo $banks[0]; ?><br>
					· <?php echo $banks[1]; ?><br>
					· <?php echo $banks[2]; ?><br><br>
					*예금주 - <?php echo $default['de_admin_company_name']; ?><br><br>
					<a href="#">입금통장 사본 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_print.png" alt="" /></a>
				</div>
			</li>
			<li>
				<div class="footer_info_wrap">
					<div class="subtit">On-line Contact</div>
					Email : <?php echo $default['de_admin_info_email']; ?> <br>
					Web hard (<?php echo $config['cf_1'] ?>)
				</div>
			</li>
			<li>
				<div class="subtit">RETURN & EXCHANGE</div>
				서울 성동구 성수이로10길 14 <br>
				에이스하이엔드성수타워 B1 B103~104호 <br>
				삼화에스앤디(주)<br><br>
				<a href="#">지도보기 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_pin.png" alt="" /></a><br><br>
			</li>
		</ul>
		<ul>
			<li>
				<div class="subtit">On-line Contact</div>
				Email : sales@shsnd.com<br>
				Web hard (ID : shboard  / PW : 2070)
			</li>
			<li>
				<div class="subtit">BUSINESS / BANK  PRINT</div>
				<a href="#">사업자등록증 사본 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_print.png" alt="" /></a>
				<a href="#">입금통장 사본 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_print.png" alt="" /></a>
			</li>
			<li>
				<div class="subtit">ORDER TRACKING</div>
				<a href="https://www.ilogen.com/m/personal/tkSearch" target="_blank">로젠택배 배송위치 조회 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_car.png" alt="" /></a>
				<a href="https://kdexp.com/main.kd" target="_blank">경동택배 배송위치 조회 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_car.png" alt="" /></a>
			</li>
		</ul>
	</div> -->

	<div class="footer_area">
		<div class="logo"><img src="<?php echo THEMA_URL; ?>/assets/img/footer_logo.png" alt="" /></div>
		<div class="info">
			<p class="link">
				<a href="<?php echo G5_BBS_URL;?>/content.php?co_id=company">회사소개 </a>   |   <a href="<?php echo G5_BBS_URL;?>/page.php?hid=provision">이용약관</a>    |   <a href="<?php echo G5_BBS_URL;?>/page.php?hid=privacy"><strong>개인정보취급방침</strong></a>
			</p>
			<p>
				<?php echo $default['de_admin_company_name']; ?> | 대표 : <?php echo $default['de_admin_company_owner']; ?> ㅣ 사업자등록번호 : <?php echo $default['de_admin_company_saupja_no']; ?> <a href="javascript:;" onclick="window.open('https://www.ftc.go.kr/bizCommPop.do?wrkr_no=6178614330','communicationViewPopup','width=750,height=700,scrollbars=yes')">[사업자정보확인]</a> <span class="pc_only"> ㅣ </span> <span class="mo_br"></span> 통신판매신고번호 : <?php echo $default['de_admin_tongsin_no']; ?><br>
				개인정보보호관리자 : <?php echo $default['de_admin_info_name']; ?> ㅣ 주소 : <?php echo $default['de_admin_company_addr']; ?><!-- <a href="http://naver.me/F4in5mn2" class="btn_map" target="_blank">지도보기 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_pin.png" alt="" /></a> -->
			</p>
			<div class="desc">
				본, 쇼핑몰의 모든 정보, 콘텐츠 및 UI, 저작물 등의 저작권은 삼화에스앤디(주)에 있으며, 어떠한 이유에서도<br>
				전시, 전송, 스크래핑, 무단복제, 도용 등은 저작권법(제97조5항)에 의거 금지되어 있으므로 이를 위반 시 법적처벌을 받을 수 있습니다.<br><br>
				Copyright ⓒEroumcare All righs reserved.
			</div>
		</div>

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



<script type="text/javascript">
wcs_do(); // wetoz : 2020-09-04
</script> 