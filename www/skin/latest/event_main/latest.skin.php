<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/event.main.css">', 0);
?>

<?php if(count($list) > 0) { // 진행중인 이벤트가 없으면 숨김 ?>
<div class="event_main_wrap">
  <div class="flex">
    <h3 class="grow">진행중인 이벤트 </h3>
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