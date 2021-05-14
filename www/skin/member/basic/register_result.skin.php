<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

?>



<?php if(!$pim) { ?>
	<div class="text-center" style="margin:30px 0px;">
		<a href="<?php echo G5_URL; ?>/" class="btn btn-color" role="button">메인으로</a>
	</div>
<?php } ?>