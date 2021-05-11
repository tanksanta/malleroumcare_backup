<?php
	include_once("./_common.php");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>계약서 생성</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
	<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="shortcut icon" href="<?php echo THEMA_URL; ?>/assets/img/top_logo_icon.ico">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <style>
  * { margin: 0; padding: 0; box-sizing: border-box; position: relative; }
  html, body { width: 100%; min-width: 100%; margin: 0 !important; padding: 0; font-family: "Noto Sans KR", sans-serif; font-size: 13px; }

  #popupWrap {

  }

  #popupWrap .head {
    display: -ms-flexbox;      /* TWEENER - IE 10 */
    display: -webkit-flex;     /* NEW - Chrome */
    display: flex;             /* NEW, Spec - Opera 12.1, Firefox 20+ */
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
    justify-content: space-between;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #ddd;
  }

  #popupWrap .head .title {
    -webkit-flex: 1;          /* Chrome */
    -ms-flex: 1;              /* IE 10 */
    flex: 1;                  /* NEW, Spec - Opera 12.1, Firefox 20+ */
    font-size: 20px;
    font-weight: bold;
    padding: 0 6px;
  }

  #popupWrap .head .menu {
  }

  #btnResetEform {
    padding: 6px 12px;
    background-color: #f5f5f5;
    border: 1px solid #dedede;
    color: #666;
  }

  #btnCloseEform {
    margin-left: 14px;
    padding: 6px;
    color: #666;
    font-size: 40px;
    line-height: 22px;
    vertical-align: middle;
  }

  #popupWrap h3 {
    margin: 0;
    padding: 12px 0;
  }
  </style>
</head>
<body>
  <div id="popupWrap">
    <div class="head">
      <div class="title">계약서 생성</div>
      <div class="menu">
        <button id="btnResetEform">변경사항 초기화</button>
        <button id="btnCloseEform">&times;</button>
      </div>
    </div>
    <div id="penRow">
      <h3></h3>
    </div>
    <div id="prodRow"></div>
    <div id="agree1Row"></div>
    <div id="agree2Row"></div>
  </div>
</body>
</html>