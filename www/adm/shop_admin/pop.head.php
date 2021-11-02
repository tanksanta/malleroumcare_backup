<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<script src="<?php echo G5_JS_URL;?>/common.js"></script>

<script type="text/javascript" src="<?php echo G5_JS_URL ?>/datetime_components/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo G5_JS_URL ?>/datetime_components/moment.min.js"></script>
<script type="text/javascript" src="<?php echo G5_JS_URL ?>/datetime_components/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo G5_JS_URL ?>/datetime_components/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="<?php echo G5_JS_URL ?>/datetime_components/ko.js"></script>
<link rel="stylesheet" href="<?php echo G5_JS_URL ?>/datetime_components/bootstrap.min.css" />
<link rel="stylesheet" href="<?php echo G5_JS_URL ?>/datetime_components/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/jquery.flexdatalist.css" />
<script type="text/javascript" src="<?php echo G5_JS_URL ?>/jquery.flexdatalist.js?20210914"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
<script>
function addComma(num)
{
  var regexp = /\B(?=(\d{3})+(?!\d))/g;
  return num.toString().replace(regexp, ',');
}
</script>
</head>
<body>