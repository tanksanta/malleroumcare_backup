<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

?>
    <link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">
	
	<form class="form" role="form" name="fmemberconfirm" action="<?php echo $url ?>" onsubmit="return fmemberconfirm_submit(this);" method="post">
	<input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
	<input type="hidden" name="w" value="u">

	<section class="thkc_section">
		<!-- 팝업 오버뷰 -->
		<div class="thkc_popOverlay"></div>
		<!-- 타이틀틀 -->
		<div class="thkc_memberModifyWrap">
			<h3>비밀번호 입력</h3>
		</div>
		<!-- 비밀번호 내용 -->
		<div class="thkc_joinWrap">
			<!-- title 계정정보-->
			<p>회원님의 정보보호를 위하여 비밀번호를 한 번 더 확인합니다.</p>
			<div class="joinTitle">
				<div class="boxLeft"></div>
			</div>
			<!-- table 계정정보 -->
			<div class="thkc_tableWrap">
				<div class="table-box  m30">
					<div class="tit">아이디</div>
					<div class="thkc_cont"><?php echo $member['mb_id'] ?></div>
				</div>
				<div class="table-box">
					<div class="tit">비밀번호</div>
					<div class="thkc_cont">
						<div>
							<label for="confirm_mb_password" class="thkc_blind">비밀번호</label>
							<input class="thkc_input" id="confirm_mb_password" name="mb_password" placeholder="영문/숫자를 포함한 6자리 ~ 12자리 이하로 입력" value="" type="password" />
						</div>
						<div class="error-txt error"></div>
					</div>
				</div>

				<!-- 버튼 -->
				<div class="thkc_btnWrap thkc_mtb_01">
					<a href=""><button class="btn_submit_01" type="submit" id="btn_sumbit" >확인</button></a><br>
				</div>
			</div>

		</div>
	</section>

	</form>


	<script>
		function fmemberconfirm_submit(f) {
			document.getElementById("btn_submit").disabled = true;

			return true;
		}
	</script>
<!-- } 회원 비밀번호 확인 끝 -->