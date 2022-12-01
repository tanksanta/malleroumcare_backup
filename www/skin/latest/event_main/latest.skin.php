<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/event.main.css">', 0);
?>

<?php if(count($list) > 0) { // 진행중인 이벤트가 없으면 숨김 ?>
<div class="event_main_wrap">
  <div class="flex">

    <!--  TODO: 2022 연말감사제용 코드 22-12-31 이후 코드 수정할 것 / 자동으로 변경되도록 임시 설정 -->
    <?php $timenow = date("Y-m-d H:i:s");  $end_yearend = "2022-12-31 23:59:59"; $now_target = strtotime($timenow); $end_target = strtotime($end_yearend);
    if($now_target < $end_target) { ?>
        <h3 class="grow">2022 연말감사제</h3>
    <?php } else { ?>
        <h3 class="grow">진행중인 이벤트</h3>
    <?php } ?>
    <!--  TODO: 2022 연말감사제용 코드 22-12-31 이후 코드 수정할 것 / 자동으로 변경되도록 임시 설정 -->

    <div class="link_wrap">
      <a href="/bbs/board.php?bo_table=event" class="btn_default">이벤트 전체보기</a>
    </div>
  </div>
  <div class="event_wrap flex">
    <?php
    for ($i = 0; $i < count($list); $i++) {
      // 썸네일
      $list[$i]['no_img'] = $board_skin_url.'/img/no-img.jpg'; // No-Image
      $img = apms_wr_thumbnail($bo_table, $list[$i], 600, 365, false, true);
    ?>
    <a href="<?=$list[$i]['href']?>" class="event">
      <img src="<?=$img['src']?>">
      <p class="title"><?=$list[$i]['subject']?></p>
      <p class="period"><?php echo get_text($list[$i]['wr_1']); ?></p>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>