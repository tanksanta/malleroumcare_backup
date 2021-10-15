<?php
include_once("./_common.php");
define('_RECIPIENT_', true);

if(!$member['mb_id'])
  alert('먼저 로그인하세요.');
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
  <link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?=G5_SHOP_URL?>/eform/css/writeeform.css">
  <link rel="stylesheet" href="<?=THEMA_URL?>/assets/bs3/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=c388393d4f69be5b284710964239c932"></script>
</head>
<body>
<style>
#popup_recipient {
  position: absolute;
  top: 20px;
  right: 20px;
}
#popup_recipient i {
  color: #959595;
  font-size: 2em;
}

.popup_recipient_security {
  padding: 30px;
}
.popup_recipient_security h1 {
  font-weight: bold;
  font-size: 1.8em;
  border-bottom: 1px solid #ddd;
  padding-top: 10px;
  margin-top :0;
  padding-bottom: 20px;
  margin-bottom: 20px;
  font-family: "Noto Sans KR", sans-serif;
}
.popup_recipient_security p.message {
  font-size: 1.2em;
  line-height: 1.3em;
  margin-bottom: 30px;
}
.popup_recipient_security_form input[type="password"] {
  border: 1px solid #b3b3b3;
    width: 100%;
    padding: 15px;
    font-size: 1.5em;
    display: block;
    margin-bottom: 20px;
}
.popup_recipient_security_form_check {
  font-weight: bold;
}
.popup_recipient_security_form_buttons {
  margin: 20px 0 10px 0;
  text-align: center;
}
.popup_recipient_security_form_button {
  background-color: #666666;
  color: white;
  width: 100px;
  height: 40px;
  border: 0;
}
input[type="submit"].popup_recipient_security_form_button {
  background-color: #ee8102;
}
.popup_recipient_security_form_button + .popup_recipient_security_form_button {
  margin-left: 10px;
}
</style>

<button id="popup_recipient" class="close_popup" style="font-size: 20px;"><i class="fa fa-times" aria-hidden="true"></i></button>

<div class="popup_recipient_security">
  <h1>내 기기 보안인증</h1>
  <p class="message">
    수급자 정보확인을 위해서는 해당기기 등록이 필요합니다.<br>
    비밀번호 입력 후 기기 등록을 완료해주세요.
  </p>

  <form class="popup_recipient_security_form">
    <p style="font-size:1.2em;">비밀번호 입력</p>
    <input type="password" />
    <input type="checkbox" id="popup_recipient_security_form_check">
    <label for="popup_recipient_security_form_check" class="popup_recipient_security_form_check">확인함</label>
    <p>
      개인정보 보호정책에 따라 가입자가 아닌 경우 해당기기 사용은 제한됩니다.
    </p>
    <div class="popup_recipient_security_form_buttons">
      <input type="submit" class="popup_recipient_security_form_button" value="확인" />
      <input type="button" class="popup_recipient_security_form_button close_popup" value="취소" />
    </div>
  </form>
</div>


<script>
function closePopup() {
  $("body", window.parent.document).removeClass("modal-open");
  $("#popup_recipient", window.parent.document).hide();
  $("#popup_recipient", window.parent.document).find("iframe").remove();
}
$(function() {
  // 창닫기 버튼
  $('.close_popup').click(closePopup);
});
</script>
</body>
</html>