<?php
include_once('./_common.php');

$g5['title'] = "발주내역";
include_once("./_head.php");

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);
?>

<section class="wrap">
  <div class="sub_section_tit">발주내역</div>
</section>

<?php
include_once('./_tail.php');
?>
