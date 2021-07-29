<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "파트너 주문상세";
include_once("./_head.php");


?>

<section class="wrap">
  <div class="sub_section_tit">주문상세
  	<div class="r_area"><a href="./partner_orderinquiry_list.php">목록</a></div>
  </div>

  <div class="inner">
    
  </div>
</section>



<?php
include_once('./_tail.php');
?>
