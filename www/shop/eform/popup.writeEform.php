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
    padding-bottom: 12px;
  }

  #popupWrap .flex {
    display: -ms-flexbox;      /* TWEENER - IE 10 */
    display: -webkit-flex;     /* NEW - Chrome */
    display: flex;             /* NEW, Spec - Opera 12.1, Firefox 20+ */
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
    justify-content: space-between;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }

  #popupWrap .head {
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

  #popupWrap .row {
    padding: 0 18px;
  }

  #popupWrap h3 {
    margin: 0;
    padding: 12px 0;
    font-size: 16px;
    font-weight: bold;
  }

  #tablePenInfo {
    border: 0;
    border-top: 12px solid #f5f5f5;
    border-bottom: 12px solid #f5f5f5;
    background-color: #f5f5f5;
    width: 100%;
  }

  #tablePenInfo th,
  #tablePenInfo td {
    padding: 2px 8px;
  }

  #tablePenInfo th {
    min-width: 126px;
    text-align: left;
    font-weight: normal;
  }

  #tablePenInfo th:before {
    display: inline;
    content: '·';
    padding-right: 2px;
  }

  #tablePenInfo td {
    width: 100%;
  }

  #prodRow .right {
    padding: 8px;
  }

  #prodRow .notice {
    color: red;
  }

  #prodRow .checkbox {
    display: inline-block;
    margin-left: 12px;
  }

  #chkConfirm {
    vertical-align: middle;
    margin-right: 6px;
  }

  #popupWrap .row.entConAcc textarea {
    display: block;
    width: 100%;
    height: 100px;
    resize: vertical;
    padding: 8px;
  }
  </style>
</head>
<body>
  <div id="popupWrap">
    <div class="head flex">
      <h1 class="title">계약서 생성</h1>
      <div class="menu">
        <button id="btnResetEform">변경사항 초기화</button>
        <button id="btnCloseEform">&times;</button>
      </div>
    </div>
    <div id="penRow" class="row">
      <h3>수급자정보</h3>
      <table id="tablePenInfo">
        <tr>
          <th>수급자</th>
          <td>홍길동</td>
        </tr>
        <tr>
          <th>장기요양인정번호</th>
          <td>L11111121233</td>
        </tr>
        <tr>
          <th>인정등급</th>
          <td>2등급</td>
        </tr>
        <tr>
          <th>구분</th>
          <td>감경 6%</td>
        </tr>
      </table>
    </div>
    <div id="prodRow" class="row">
      <div class="flex">
        <h3>공급물품</h3>
        <div class="right">
          <span class="notice">*계약서 작성을 위해 추가하는 물품은 통합시스템에서 관리되지 않고 계약서 작성에만 활용됩니다.</span>
          <label class="checkbox"><input id="chkConfirm" type="checkbox">확인함</label>
        </div>
      </div>
    </div>
    <div id="entConAcc01Row" class="row entConAcc">
      <h3>특약사항1</h3>
      <textarea name="entConAcc01" id="entConAcc01">본 계약은 국민건강보험 노인장기요양보험 급여상품의 공급계약을 체결함에 목적이 있다.</textarea>
    </div>
    <div id="entConAcc02Row" class="row entConAcc">
      <h3>특약사항2</h3>
      <textarea name="entConAcc02" id="entConAcc02">본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.</textarea>
    </div>
  </div>
</body>
</html>