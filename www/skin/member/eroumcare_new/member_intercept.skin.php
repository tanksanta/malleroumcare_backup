<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

?>



<?php if(!$pim) { ?>
	<div class="wait_member">
		<img src="/img/icon_home.png" alt="" />
		<p>관리자 권한으로 서비스 이용이 중지되었습니다.</p>
		<p>관리자 승인 후 서비스 이용이 가능합니다. </p>
		<a href="/bbs/content.php?co_id=guide">서비스 이용안내</a>
	</div>

	<script>
	$(document).ready(function() {
		$('#head_tutorial').hide();
	});
	</script>
<?php } ?>