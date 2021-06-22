<?php
include_once("./_common.php");

if(!$member['mb_id'])
  alert('먼저 로그인하세요.');

$link = get_recipient_link($rl_id, $member['mb_id']);

if(!$link || $link['status'] == 'wait')
  alert('유효하지 않은 요청입니다.');

$rl = sql_fetch("
  SELECT * FROM `recipient_link`
  WHERE rl_id = {$link['rl_id']}
");

$address = "{$rl['rl_pen_addr1']} {$rl['rl_pen_addr2']} {$rl['rl_pen_addr3']}";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>수급자 추천</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?=G5_SHOP_URL?>/eform/css/writeeform.css">
  <link rel="stylesheet" href="<?=THEMA_URL?>/assets/bs3/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=c388393d4f69be5b284710964239c932"></script>
  <style>
  .popupContentWrap { padding: 20px;}
  .popupContentWrap .row { padding: 0 !important; }
  .popupContentWrap .row + .row { margin-top: 6px; }
  .pen_info_wrap { overflow: hidden; }
  .notice_wrap h5 { margin: 0; font-weight: bold; font-size: 14px; }
  #map { margin: 20px 0; }
  </style>
</head>
<body>
<?php
if($link['status'] == 'request')
  require_once('./my_recipient_link_link.php');
if($link['status'] == 'link')
  require_once('./my_recipient_link_register.php');
?>
<script>
function closePopup() {
  $("body", window.parent.document).removeClass("modal-open");
  $("#popup_recipient_link", window.parent.document).hide();
  $("#popup_recipient_link", window.parent.document).find("iframe").remove();
}
$(function() {
  // 창닫기 버튼
  $('#btnCloseEform').click(closePopup);
});
</script>
</body>
</html>