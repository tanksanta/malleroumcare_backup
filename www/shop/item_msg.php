<?php
include_once('./_common.php');

$url = get_search_string($_GET['url']);
if(!$url) exit;

$sql = "
  SELECT i.*, m.mb_entNm
  FROM recipient_item_msg i
  LEFT JOIN g5_member m ON i.mb_id = m.mb_id
  WHERE ms_url = '{$url}'
";
$ms = sql_fetch($sql);
if(!$ms['ms_id']) exit;

$sql = " SELECT * FROM recipient_item_msg_item WHERE ms_id = '{$ms['ms_id']}' ORDER BY mi_id ASC ";
$result = sql_query($sql);

$items = [];
while($row = sql_fetch_array($result)) {
  $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>이로움 : 추천품목</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/item_msg.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <?php
  echo '<link rel="stylesheet" href="'.G5_CSS_URL.'/'.(G5_IS_MOBILE?'mobile':'default').$shop_css.'.css?v='.APMS_SVER.'">'.PHP_EOL;
  ?>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
</head>
<body>
<div class="im_wr">
  <div class="im_hd">
    <img src="<?=THEMA_URL?>/assets/img/hd_logo.png">
  </div>
  <div class="im_msg_wr">
    <p><?php echo $ms['ms_pen_nm']; ?>님에게</p>
    <p><?php echo $ms['mb_entNm']; ?> 사업소에서 추천 품목이 전송되었습니다.</p>
  </div>
  <div class="im_list_wr">
    <?php require_once('./item_msg_render.php'); ?>
  </div>
</div>
</body>

<script>
//프로젝트 동영상 리사이징
$(window).load(function () {
  // video size
  var _video = $('.video_wrap').find('iframe');
  var video_w = _video.width();
  var video_h = video_w * (9 / 16);
  _video.css('height', video_h);
  // var
});
$(window).resize(function () {
  // video size
  var _video = $('.video_wrap').find('iframe');
  var video_w = _video.width();
  var video_h = video_w * (9 / 16);
  _video.css('height', video_h);
  // var
});
</script>

</html>
