<?php
include_once('./_common.php');

define('_PRINT_REC_', true);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>욕구사정기록지</title>
  <link rel="stylesheet" href="<?=THEMA_URL?>/assets/bs3/css/bootstrap.min.css">
  <link rel="stylesheet" href="/adm/css/popup.css?v=<?php echo time(); ?>">
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <script src="<?php echo G5_JS_URL;?>/common.js"></script>
</head>
<body>
<style>
.info_wrap .row { margin-left: 0; margin-right: 0; }
#printBtns { position: fixed; left:0; bottom:0; width: 100%; height: 80px; background: #525152; text-align: center; }
#printBtns button { display: inline-block; height: 50px; margin-top: 15px; color: #fff; font-size: 16px; line-height: 20px; padding: 15px 30px; background: #828282; border: none; border-radius: 5px; }
#printBtns button + button { margin-left: 10px; }
#printBtns button.primary { background: #f2730d; width: 150px; }
.sub_section_tit {
  font-size: 30px;
  font-weight: bold;
  padding: 10px 5px;
  line-height: 50px;
  position: relative;
}
label {
  font-weight: normal;
}
.recipient_rec_wrap { padding: 10px 10px 90px 10px; }
</style>
<style type="text/css" media="print">
.noprint {display:none;}
.prints {width:100%;}
.btn { width:100px; border:1px solid #CCC; padding:5px 15px; font:bold 12px dotumche; color:#333; text-align:center; background:#EEEEEE; }
.recipient_rec_wrap { padding: 0; }
body { margin: 10px; }
</style>

<?php
if($_GET['type'] == 'simple') {
  $_GET['rs_id'] = $_GET['recId'];
  include_once('./my_recipient_rec_form.php');
} else if($_GET['type'] == 'detail') {
  $_GET['rd_id'] = $_GET['recId'];
  include_once('./my_recipient_rec_detail_form.php');
}
?>

<div id="printBtns" class="noprint">
  <button type="button" class="primary" onclick="go_prints();">인쇄</button>
  <button type="button" onclick="close_popup();">취소</button>
</div>

<script>
function go_prints() {
  samhwaprint($('html').html());
}

function close_popup() {
  $("html, body", window.parent.document).removeClass('modal-open');
  $("#popup_rec", window.parent.document).hide();
  $("#popup_rec", window.parent.document).find("iframe").remove();
}

$(function() {
  $('input[type="text"]').prop('readonly', true);
  $('textarea').prop('readonly', true);
});
</script>
</body>
</html>
