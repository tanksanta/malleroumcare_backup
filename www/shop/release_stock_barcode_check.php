<?php
//$sub_menu = '400480';
ini_set('post_max_size', '1024M'); 
include_once("./_common.php");
//auth_check($auth[$sub_menu], "r");

$g5["title"] = "재고확인";

if (!$it_id) {
  alert('올바른 접근이 아닙니다');
}

$where = "WHERE it_id = '{$it_id}' ";

if ($io_id) {
  $where .= " AND io_id = '{$io_id}' ";
}

$row = get_stock_item_info($it_id, $io_id);

$sql = "
  SELECT IFNULL(MAX(created_at), '미확인') AS last_checked_at
  FROM stock_barcode_check_log
  {$where}
";

$last_checked_at = sql_fetch($sql)['last_checked_at'];

$prod_pay_code = sql_fetch("SELECT * FROM g5_shop_item WHERE it_id = '{$it_id}'")['ProdPayCode'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $g5["title"] ?></title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="/js/barcode_utils.js"></script>
  <link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
  <link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/flex.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
      outline: none;
      position: relative;
    }

    html, body {
      width: 100%;
      font-family: "Noto Sans KR", sans-serif;
    }

    body {
      padding-top: 60px;
      padding-bottom: 200px;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    ul, li {
      list-style: none;
    }

    button {
      border: 0;
      font-family: "Noto Sans KR", sans-serif;
      cursor: pointer;
    }

    input {
      font-family: "Noto Sans KR", sans-serif;
    }

    /* 고정 상단 */
    #popupHeaderTopWrap {
      position: fixed;
      width: 100%;
      height: 60px;
      left: 0;
      top: 0;
      z-index: 10;
      background-color: #333;
      padding: 0 20px;
    }

    #popupHeaderTopWrap:after {
      display: block;
      content: '';
      clear: both;
    }

    #popupHeaderTopWrap > div {
      height: 100%;
      line-height: 60px;
    }

    #popupHeaderTopWrap > .title {
      float: left;
      font-weight: bold;
      color: #FFF;
      font-size: 22px;
    }

    #popupHeaderTopWrap > .close {
      float: right;
    }

    #popupHeaderTopWrap > .close > a {
      color: #FFF;
      font-size: 40px;
      top: -2px;
    }

    /* 컨텐츠 */
    #popupBody select {
      width: 80px;
      height: 50px;
      float: left;
      border-radius: 5px;
      border: 1px solid #E0E0E0;
      font-size: 14px;
      text-align: center;
    }

    #popupBody input[type='text'] {
      width: 100%;
      height: 50px;
      float: left;
      text-align: center;
      border-radius: 5px;
      border: 1px solid #E0E0E0;
      font-size: 17px;
    }

    #popupBody #searchForm {
      padding: 10px 20px;
    }

    #popupBody #searchForm input[type="checkbox"] {
      display: none;
    }

    #popupBody #searchForm label {
      height: 20px;
      line-height: 20px;
      float: left;
      cursor: pointer;
    }

    #popupBody #searchForm .icon {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 1px solid #666;
      vertical-align: middle;
      top: -1px;
      margin-right: 5px;
    }

    #popupBody #searchForm .icon i {
      position: absolute;
      left: 50%;
      top: 50%;
      margin-left: -6px;
      margin-top: -6px;
      font-size: 12px;
      color: #DC3333;
      opacity: 0;
    }

    #popupBody .searchFormTop .barcodeSearch {
      display: inline-block;
      font-weight: bold;
      text-align: right;
    }

    #popupBody .searchFormTop .barcodeSearch img {
      display: inline-block;
      width: 30px;
      height: 30px;
      vertical-align: middle;
      float: right;
    }

    #popupBody #searchSubmitBtn {
      width: 120px;
      margin-left: 10px;
      height: 50px;
      float: left;
      border-radius: 5px;
      font-size: 16px;
      background-color: #333;
      color: #FFF;
      font-weight: bold;
      cursor: pointer;
    }

    #popupBody #searchForm .add_barcode_btn {
      width: 100%;
      padding: 15px 0;
      font-size: 14px;
      margin-top: 20px;
      border: 1px solid #95b3d7;
      background: #dce6f2;
      color: #95b3d7;
    }

    #popupBody #content {
      padding: 10px 20px;
    }

    #popupBody .listContent li {
      border-bottom: 1px solid #dfdfdf;
      font-size: 14px;
    }

    #popupBody .listContent li:first-of-type {
      border-top: 1px solid #dfdfdf;
    }

    #popupBody .listContent li.select {
      background: #E4E4E4;
    }

    #popupBody .listContent li > div {
      padding: 13px 0;
    }

    #popupBody .listContent .barcode {
      width: 70%;
      font-weight: bold;
      font-size: 20px;
    }

    #popupBody .listContent .check_status {
      width: 20%;
      text-align: center;
    }

    #popupBody .listContent .more {
      width: 10%;
      text-align: center;
      font-size: 26px;
    }

    #popupBody .listContent .more span {
      position: relative;
      top: -2px;
    }

    #popupBody .listContent .more .select {
      position: absolute;
      top: 38px;
      right: 0;
      width: 100px;
      color: #376092;
      display: none;
      z-index: 1;
    }

    #popupBody .listContent .more .select li {
      padding: 8px 0;
      border: 1px solid #95b3d7;
      border-bottom: 0;
      background: #fff;
    }

    #popupBody .listContent .more .select li:last-of-type {
      border-bottom: 1px solid #95b3d7;
    }

    #popupBody .listContent li.deleted .barcode {
      color: #bfbfbf;
    }

    #popupBody .listContent li.deleted .check_status span {
      color: #f00;
    }

    #popupBody .listContent li.newAdd .check_status span {
      border: 1px solid #95b3d7;
      background: #dce6f2;
      color: #95b3d7;
      padding: 2px 8px;
    }

    #popupBody .listContent li.unchecked .check_status span {
      color: #bfbfbf;
    }

    #popupBody .listContent li.newAdd .select .check {
      display: none;
    }

    /* 고정 하단 */
    #popupFooterBtnWrap {
      position: fixed;
      width: 100%;
      background-color: #000;
      bottom: 0px;
      z-index: 10;
      padding: 10px 20px;
      color: #fff;
    }

    #popupFooterBtnWrap button {
      font-size: 15px;
      font-weight: 700;
      width: 100px;
      padding: 5px 0;
    }

    #popupFooterBtnWrap > .checkBtn {
      float: left;
      width: 100%;
      height: 100%;
      background-color: #000;
      color: #FFF;
    }

    #popupFooterBtnWrap > .cancelbtn {
      float: right;
      width: 25%;
      height: 100%;
      color: #666;
      background-color: #DDD;
    }

    #selectActWrap {
      position: fixed;
      bottom: 104px;
      left: 0;
      width: 100%;
      height: 60px;
      display: none;
    }

    #selectActWrap button {
      border: 0;
      border-top: 1px solid #000;
      font-size: 15px;
    }

    #selectActWrap .check {
      width: 65%;
      border-right: 1px solid #000;
    }

    #selectActWrap .delete {
      width: 35%;
    }

    .toast {
      width: 98% !important;
      left: 0 !important;
      margin: 5px auto 0 auto !important;
      right: 0;
      text-align:center;
      font-weight:bold;
    }

    #web-barcode {
      display: none;
      position: fixed;
      background-color: rgba(0, 0, 0, 0.5);
      width: 100%;
      height: 100%;
      z-index: 9999;
      left: 0;
      top: 0;
    }

    #web-barcode-input {
      width:1px;
      height:1px;
      border:none;
    }

    #web-barcode-close {
      position: absolute;
      color: white;
      top: 15px;
      left: 20px;
      font-size: 40px;
      cursor: pointer;
    }

    .web-barcode-content {
      margin: auto;
      color: white;
      text-align: center;
    }

    #web-barcode-loading {
      display: block;
      width: 50px;
      height: 50px;
      border: 3px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: web-barcode-loading-spin 1s ease-in-out infinite;
      -webkit-animation: web-barcode-loading-spin 1s ease-in-out infinite;
      margin:0 auto 10px auto;
    }

    @keyframes web-barcode-loading-spin {
      to { -webkit-transform: rotate(360deg); }
    }
    @-webkit-keyframes web-barcode-loading-spin {
      to { -webkit-transform: rotate(360deg); }
    }

    #add_barcode_pop {
      position: fixed;
      left: 0;
      top: 0;
      z-index: 100;
      width: 100%;
      height: 100%;
      display: none;
    }

    #add_barcode_pop .dim {
      position: fixed;
      width: 100%;
      height: 100%;
      left: 0;
      top: 0;
      background: rgba(0, 0, 0, 0.5);
    }

    #add_barcode_pop .pop {
      position: absolute;
      width: 90%;
      height: 80%;
      left: 5%;
      top: 5%;
      background: #fff;
      padding: 20px 20px 70px;
    }

    #add_barcode_pop .pop .head p {
      font-size: 18px;
      font-weight: 700;
    }

    #add_barcode_pop .pop .head span {
      font-size: 30px;
      position: relative;
      top: -11px;
      cursor: pointer;
    }

    #add_barcode_pop .pop .barcode_qty button {
      width: 28px;
      font-size: 16px;
      font-weight: bold;
      border: 1px solid #bfbfbf;
      background: #f2f2f2;
      color: #000;
    }

    #add_barcode_pop .pop .barcode_qty input {
      width: 40px;
      height: 25px;
      font-size: 14px;
      border-radius: 0;
      border: 1px solid #bfbfbf;
      border-right: 0;
      border-left: 0;
      text-align: center;
    }

    #add_barcode_pop .pop .content {
      width: 100%;
      height: calc(100% - 42px);
    }

    #add_barcode_pop .pop .content p {
      font-size: 17px;
    }

    #add_barcode_pop .barcode_input_list {
      width: 100%;
      height: calc(100% - 25px);
      overflow: scroll;
	  overflow-x: hidden;
    }

    #add_barcode_pop .barcode_input_list li {
      width: 100%;
      position: relative;
    }

    #add_barcode_pop .barcode_input_list li .frm_input {
      width: 100%;
      height: 40px;
      box-sizing: border-box;
      padding-left: 15px;
      font-size: 15px;
      border: 1px solid #E4E4E4;
      margin-bottom: 5px;
    }

    #add_barcode_pop .barcode_input_list li .frm_input.active {
      border-color: #FF5858;
    }

    #add_barcode_pop .barcode_input_list li img.barcode_add {
      width: 28px;
      height: 28px;
      position: absolute;
      top: 7px;
      right: 38px;
      display: none;
    }

    #add_barcode_pop .barcode_input_list li img.barcode_icon {
      position: absolute;
      right: 76px;
      top: 11px;
      z-index: 2;
      font-size: 19px;
      opacity: 0;
    }

    #add_barcode_pop .barcode_input_list li img.barcode_icon.active {
      opacity: 1;
    }

    #add_barcode_pop .barcode_input_list li .overlap {
      position: absolute;
      right: 71px;
      top: 9px;
      z-index: 2;
      font-size: 14px;
      color: #DC3333;
      opacity: 0;
      font-weight: bold;
    }

    #add_barcode_pop .barcode_input_list li .overlap.active {
      opacity: 1;
    }

    #add_barcode_pop .barcode_input_list li .btn_pda {
      position: absolute;
      width: 30px;
      right: 5px;
      top: 5px;
      z-index: 2;
      cursor: pointer;
    }

    #add_barcode_pop .footer {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
      height: 50px;
    }

    #add_barcode_pop .footer .btn_wrap {
      width: 100%;
      height: 100%;
    }

    #add_barcode_pop .footer .btn_wrap .save {
      background: #000;
      color: #fff;
    }
	/* 팝업 */
    #popup {
      display: flex;
      justify-content: center;
      align-items: center;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, .7);
      z-index: 50;
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
    }

    #popup.hide {
      display: none;
    }

    #popup.multiple-filter {
      backdrop-filter: blur(4px) grayscale(90%);
      -webkit-backdrop-filter: blur(4px) grayscale(90%);
    }

    #popup .content {
      padding: 20px;
      background: #fff;
      border-radius: 5px;
      box-shadow: 1px 1px 3px rgba(0, 0, 0, .3);
      max-width: 90%;
    }

    #popup .content {
      max-width: 90%;
      font-size: 14px;
    }

    #popup .closepop {
      width: 100%;
      height: 40px;
      cursor: pointer;
      color: #fff;
      background-color: #000;
      border-radius: 6px;
      margin-top: 10px;
    }

	#searchForm .span {
      margin-left: 20px;
      width: 100%;
	  float: left;
    }

    #searchForm .all {
      margin-top: 10px;
	  margin-bottom: 5px;
      padding-left: 20px;
      font-size: 17px;
      text-align: left;
      float: left;
      height: 50px;
      width: 55%;
      border-radius: 6px;
      background-color: #c0c0c0;
      color: #fff;
      border: 0px;
      box-sizing: border-box;
    }

    #searchForm .all::placeholder {
      color: #fff;
    }

    #searchForm .barNumCustomSubmitBtn {
      float: left;
      margin-top: 10px;
	  margin-left: 10px;
      color: #fff;
      font-size: 17px;
      background-color: #494949;
      border: 0px;
      border-radius: 6px;
      width: 18%;
      height: 50px;
      font-weight: bold;
    }

    #searchForm .barNumGuideOpenBtn {
      float: left;
      position: relative;
      margin-top: 10px;
	  margin-left: 10px;
      width: 35px;
      cursor: pointer;
      top: 8px;
    }

    /* Chrome, Safari, Edge, Opera */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }

	#loading {
      display: none;
      background-color: rgba(0,0,0,0.7);
      position: fixed;
      top: 0;
      left: 0;
      z-index: +100 !important;
      width: 100%;
      height: 100%;
    }

    #loading > div {
      position: relative;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }

    #loading img {
      width: 150px;
    }

    #loading p {
      color: #fff;
      position: relative;
      top: -25px;
    }
  </style>

  <link rel="stylesheet" type="text/css" href="<?php echo G5_URL; ?>/css/jquery.toast.min.css" />
  <script type="text/javascript" src="<?php echo G5_URL; ?>/js/jquery.toast.min.js"></script>
</head>

<body>

<!-- 고정 상단 -->
<div id="popupHeaderTopWrap">
  <div class="title"><?php echo $g5["title"] ?></div>
  <div class="close">
    <a href="javascript:void(0);" onclick="goBack();">
      &times;
    </a>
  </div>
</div>

<?php
$option = '';
$option_br = '';
if ($row['io_type']) {
  $opt = explode(chr(30), $row['io_id']);
  if ($opt[0] && $opt[1])
    $option .= $opt[0] . ' : ' . $opt[1];
} else {
  $subj = explode(',', $row['it_option_subject']);
  $opt = explode(chr(30), $row['io_id']);
  for ($k = 0; $k < count($subj); $k++) {
    if ($subj[$k] && $opt[$k]) {
      $option .= $option_br . $subj[$k] . ' : ' . $opt[$k];
      $option_br = ' / ';
    }
  }
}

$full_it_name = $row['it_name'];
if ($option) {
  $full_it_name .= " ({$option})";
}

?>

<div id="popupBody">
  <div id="searchForm">

    <div class="searchFormTop flex-row justify-space-between">
      <div style="width: 100%">
        <?php echo $full_it_name ?><br/>
        <span style="font-size: 13px">재고수량 : <?= $row['sum_ws_qty'] ?> / 바코드 (확인완료 <?= $row['sum_checked_barcode_qty'] ?> / 총 <?= $row['sum_barcode_qty'] ?>)<br/> 마지막 확인 일시 : <?= $last_checked_at ?></span>
      </div>
    </div>

    <div class="flex-row" style="margin-top: 20px;">
      <select name="sort_option" id="sortOption" style="width: 100%" onchange="sortDataAndRender()">
        <option value="unchecked">미 확인 바코드가 위로 정렬</option>
        <option value="newAdd">신규 바코드가 위로 정렬</option>
        <option value="deleted">삭제된 바코드가 위로 정렬</option>
        <option value="barcodeDesc">바코드 내림차순 정렬</option>
        <option value="barcodeAsc">바코드 오름차순 정렬</option>
      </select>
    </div>

    <div class="flex-row" >
      <button class="add_barcode_btn" onclick="showPopBarcodeList(true)">바코드 재고 추가</button>
    </div>

    <div class="flex-row barcode-area" >
      <input type="text" class="all" placeholder="일괄 등록수식 입력 (예시1) 20120000000^1-3 | 예시2) 20120000000^1,3,5)" style="width:78%">
      <button type="button" class="barNumCustomSubmitBtn" onclick="inputRegexBarcode(this)">등록</button>
      <img src="/img/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)" title="바코드 일괄등록 방법" style="width:35px; height:35px;">
    </div>

  </div>



  <div id="content">
    <ul class="listContent">
      <!--
      <li class="flex-row align-center checked" data-bc_id="1">
        <div class="barcode">바코드</div>
        <div class="check_status">
          <img src="/img/barcode_icon_1.png" />
        </div>
        <div class="more">
          <span>⋮</span>
          <ul class="select">
            <li class="check">확인함</li>
            <li class="delete">삭제함</li>
          </ul>
        </div>
      </li>
      <li class="flex-row align-center deleted" data-bc_id="2">
        <div class="barcode">바코드</div>
        <div class="check_status">
          <span>삭제됨</span>
        </div>
        <div class="more">
          <span>⋮</span>
          <ul class="select">
            <li class="check">확인함</li>
            <li class="delete">삭제함</li>
          </ul>
        </div>
      </li>
      <li class="flex-row align-center newAdd" data-bc_id="3">
        <div class="barcode">바코드</div>
        <div class="check_status">
          <span>신규</span>
        </div>
        <div class="more">
          <span>⋮</span>
          <ul class="select">
            <li class="check">확인함</li>
            <li class="delete">삭제함</li>
          </ul>
        </div>
      </li>
      <li class="flex-row align-center unchecked" data-bc_id="4">
        <div class="barcode">바코드</div>
        <div class="check_status">
          <span>미확인</span>
        </div>
        <div class="more">
          <span>⋮</span>
          <ul class="select">
            <li class="check">확인함</li>
            <li class="delete">삭제함</li>
          </ul>
        </div>
      </li>
      -->
    </ul>
  </div>
</div>

<div id="selectActWrap">
  <div class="flex-row" style="height: 100%;">
    <button onclick="actSelectedItems('check')" class="check">선택 재고 확인함</button>
    <button onclick="actSelectedItems('delete')" class="delete">선택 삭제함</button>
  </div>
</div>

<!-- 고정 하단 -->
<div id="popupFooterBtnWrap">
  <div class="flex-row justify-space-between align-center" style="margin-bottom: 20px">
    <p>확인중 (확인완료 <span id="checkedBarcodeCnt">0</span> / 총 <span id="allBarcodeCnt"><?= $row['sum_barcode_qty'] ?></span>)</p>
    <button onclick="saveData()">완료</button>
  </div>
  <div class="flex-row justify-space-between" style="padding: 0 30px">
    <button onclick="openWebBarcode();">PDA</button>
    <button onclick="open_invoice_scan();">APP</button>
  </div>
</div>

<div id="web-barcode">
  <i id="web-barcode-close" class="fa fa-times"></i>
  <div class="web-barcode-content">
    <div id="web-barcode-loading"></div>
    <div>
      바코드 스캔 대기중..<br />
    </div>
    <input type="text" id="web-barcode-input" />
  </div>
</div>
<!-- 팝업 -->
<div id="popup" class="hide" style="z-index:99999999;">
  <div class="content">
    <p>
      바코드 일괄등록<br><br>
      1. 공동된 숫자 이후 꺽쇠(^)를 입력하세요<br>
      2. 하이픈(-)을 이용해 연속한 숫자를 입력할 수 있습니다.<br>
      3. 콤마(,)를 이용해 연속하지 않은 숫자를 입력할 수 있습니다.<br><br>

      예시1) 20120000000^1-3 입력시 <br>
      201200000001, 201200000002, 201200000003이 일괄등록 됩니다.<br><br>

      예시2) 20120000000^1,3,5 입력시<br>
      201200000001, 201200000003, 201200000005가 일괄등록 됩니다.<br><br>
    </p>
    <button class="closepop" onclick="closePopup()">닫기</button>
  </div>
</div>
<div id="loading" style="display: none">
  <div>
    <img src="../adm/shop_admin/img/ajax-loading.gif" class="img-responsive">
    <p>잠시만 기다려주세요...</p>
  </div>
</div>
<div id="add_barcode_pop">
  <div class="dim"></div>
  <div class="pop">
    <div class="head">
      <div class="flex-row justify-space-between">
        <p>바코드 재고 추가</p>
        <span onclick="showPopBarcodeList(false)">&times;</span>
      </div>
    </div>     
    
    <div class="content">
      
	  <div class="flex-row barcode_qty" style="margin-bottom: 15px">
        <p style="margin-right: 20px">수량</p>
        <button id='btn_minus' onclick="setQtyNumber(this, 'minus')">-</button><input type="number" class="qty_input" name="qty_input" value="1" onkeyup="setBarcodeInput(this);"><button id='btn_plus' onclick="setQtyNumber(this, 'plus')">+</button>
      </div>
	  
      <div style="height: calc(100% - 23px);">
        <p style="margin-bottom: 5px">바코드</p>
        
		<ul class="barcode_input_list">
          <li>
            <input type="number" maxlength="12" class="notall frm_input required" placeholder="바코드를 입력하세요.">
            <img src="/img/bacod_add_img.png" class="barcode_add" onclick="addAutoBarcode(this)">
            <img class="barcode_icon type1" src="/img/barcode_icon_1.png" alt="등록가능">
            <span class="overlap">중복</span>
            <img class="barcode_icon type2" src="/img/barcode_icon_3.png" alt="등록불가 (이미존재)">
            <img src="/img/btn_pda.png" class="nativePopupOpenBtn btn_pda" data-type="pda" onclick="openWebBarcode(this)">
          </li>
        </ul>
      </div>
    </div>
    <div class="footer">
      <div class="flex-row btn_wrap">
        <button style="width: 60%" class="save" onclick="savePopBarcodeList()">저장</button>
        <button style="width: 40%" class="cancel" onclick="showPopBarcodeList(false)">취소</button>
      </div>
    </div>
  </div>

  <div style="display: none" id="mockup">
    <li>
      <input type="number" maxlength="12" class="notall frm_input required" placeholder="바코드를 입력하세요.">
      <img src="/img/bacod_add_img.png" class="barcode_add" onclick="addAutoBarcode(this)">
      <img class="barcode_icon type1" src="/img/barcode_icon_1.png" alt="등록가능">
      <span class="overlap">중복</span>
      <img class="barcode_icon type5" src="/img/barcode_icon_3.png" alt="등록불가 (이미존재)">
      <img src="/img/btn_pda.png" class="nativePopupOpenBtn btn_pda" data-type="pda" onclick="openWebBarcode(this)">
    </li>
  </div>
</div>

<?php
if (!$member['mb_id']) {
  alert('접근이 불가합니다.');
}
?>
<script>
  var DATA = [];
  var CHANGED_DATA = [];
  var LOADING = false;
  var IS_OPEN_WEB_BARCODE = false;
  var BARCODE_INPUT_FOCUS_INTERVAL;
  var POP_BARCODE_INPUT_TARGET = null;
  var KEYUP_TIMER;
  
  $(function() {
    renderData(true);

    $(document).on('keyup', '.notall', function () {
      var last_index = $(this).closest('ul').find('li').last().index();
      var this_index = $(this).closest('li').index();

      $(this).closest('ul').find('.barcode_add').hide();
      if (last_index !== this_index && $(this).val().length === 12) {
        $(this).closest('li').find('.barcode_add').show();
      }

      if (KEYUP_TIMER) clearTimeout(KEYUP_TIMER);
      KEYUP_TIMER = setTimeout(notallLengthCheck, 300);
    });

    $(document).on('focus', '.notall', function () {
      var last_index = $(this).closest('ul').find('li').last().index();
      var this_index = $(this).closest('li').index();

      $(this).closest('ul').find('.barcode_add').hide();
      if (last_index !== this_index && $(this).val().length === 12) {
        $(this).closest('li').find('.barcode_add').show();
      }
    });

    $(document).on('click', '.listContent .more', function () {
      $('.listContent .more').not(this).find('.select').hide();
      $(this).find('.select').toggle();
    });

    $(document).on('click', function(e) {
      if ($(e.target).closest(".more").length === 0) {
        $('.listContent .more .select').hide();
      }
    });

    $(document).on('click', '.listContent .more .select li', function () {
      var act = $(this).attr('class');
      var liNode = $(this).closest('.item');
      var barcode = liNode.find('.barcode').text();
      var dataIndex = Number(liNode.data('index'));

      if (act === 'check') {
        // if (liNode.hasClass('checked')) {
        //   alert('이미 확인 상태입니다.');
        //   return;
        // }

        DATA[dataIndex]['checked_at'] = 'currentDate'
        DATA[dataIndex]['bc_del_yn'] = 'N'
      }

      if (act === 'delete') {
        if (liNode.hasClass('deleted')) {
          alert('이미 삭제 상태입니다.');
          return;
        }

        if (DATA[dataIndex]['bc_id'] === '0')  { // 신규 바코드라면 DATA에서 직접 삭제
          DATA.splice(dataIndex, 1);

          var findAddedItem = CHANGED_DATA.find(function (item) {
            return (item.bc_barcode === barcode && item.bc_id === '0')
          });
          var findAddedItemIndex = CHANGED_DATA.indexOf(findAddedItem);
          CHANGED_DATA.splice(findAddedItemIndex, 1);

          sortDataAndRender();
          return;

        } else {
          DATA[dataIndex]['checked_at'] = null
          DATA[dataIndex]['bc_del_yn'] = 'Y'
        }
      }

      // deep copy
      var changedObj = JSON.parse(JSON.stringify(DATA[dataIndex]));

      // 중복 검색
      var findItem = CHANGED_DATA.find(function (item) {
        return item.bc_id === changedObj.bc_id
      });

      var findItemIndex = CHANGED_DATA.indexOf(findItem);

      if (findItemIndex !== -1) { // 중복이 있다면 삭제
        CHANGED_DATA.splice(findItemIndex, 1);
      }

      CHANGED_DATA.push(changedObj)

      sortDataAndRender();
    });

    // 바코드 선택 삭제
    $(document).on('click', '.listContent li .barcode', function() {
      var allBarcodes = $('.listContent .item');
      var start = $('.listContent .item.start').length;
      var end = $('.listContent .item.end').length;
      var startNode = $('.listContent .item.start');
      var endNode = $('.listContent .item.end');
      var thisNode = $(this).closest('.item');
      var startIndex = start ? allBarcodes.index(startNode) : -1;
      var endIndex = end ? allBarcodes.index(endNode) : -1;
      var thisIndex = allBarcodes.index(thisNode);

      // 시작과 끝이 있는 경우
      if (start && end) {
        // 시작보다 낮은 바코드를 눌렀을 경우
        if (thisIndex < startIndex) {
          startNode.removeClass('start');
          thisNode.addClass('start');
        }

        // 시작보다 높고 끝보다 낮은 바코드를 눌럿을 경우
        if (startIndex < thisIndex && thisIndex < endIndex) {
          endNode.removeClass('end');
          thisNode.addClass('end');
        }

        // 끝보다 높은 바코드를 눌렀을 경우
        if (endIndex < thisIndex) {
          endNode.removeClass('end');
          thisNode.addClass('end');
        }

        // 시작과 끝 바코드를 눌렀다면 모두 취소
        if (thisIndex === startIndex || thisIndex === endIndex) {
          startNode.removeClass('start');
          endNode.removeClass('end');
        }
      }

      // 시작만 있는 경우
      if (start && !end) {
        // 시작보다 낮은 바코드를 눌렀을 경우
        if (thisIndex < startIndex) {
          startNode.removeClass('start');
          thisNode.addClass('start');
          startNode.addClass('end');
        }

        // 시작보다 높은 바코드를 눌렀을 경우
        if (thisIndex > startIndex) {
          thisNode.addClass('end');
        }

        if (thisIndex === startIndex) {
          thisNode.removeClass('start');
        }
      }

      // 아무것도 없는 경우
      if (!start && !end) {
        thisNode.addClass('start');
      }

      addSelectClassBarcode();
    });

    $(document).on("keyup", '.barcode_input_list', function() {
        $item = $('#add_barcode_pop .barcode_input_list').find('.notall');
        $shift_input = 0;
        $moreInput = 0;
        $currentFocus = 0;
        $arrLength = $item.length;

        console.log("Hello this is " );
        console.log("item count = %d", $item.length );

        $item.each(function(i,val) {
          $currentFocus++ ;
          $currentInput = $(this);
          $barcode = $currentInput.val();
          //console.log("input barcode = ", $barcode);

          if ($barcode.length >= 12) {
            $shift_input = 1;
            $(this).val($barcode.slice(0, 12));
          }
          
          
          if ( ($arrLength === $currentFocus) && $barcode.length == 12) {
            $moreInput = 1;
          }


          console.log("shift_input = ", $shift_input);
          console.log("barcode.length = ", $barcode.length);

          if($barcode.length == 0 && $shift_input == 1) {              
            console.log("barcode focus");
            $currentInput.focus();
            $shift_input = 0;
          }

        });
        if ($moreInput === 1) {
            $moreInput = 0;
            $('#btn_plus').trigger('click');
        }

    });




    // pda 스캔
    $(document).on('keydown', '#web-barcode-input', function (e) {
      var isScanner = e.key === 'Unidentified' || e.key === 'TVNetwork';

      if (e.key) {
        e.preventDefault();
      }

      if (!isScanner) {
        return;
      }

      if (POP_BARCODE_INPUT_TARGET) {
        receiveBarcodeOnPop();
      } else {
        receiveBarcode();
      }
    });

    $(document).on('touchstart, click', '#web-barcode-close', function (e) {
      alert('바코드스캔을 종료합니다');
      closeWebBarcode();
    });
  });
//추가 시작
  function inputRegexBarcode(x) {
    var val = $(x).closest(".barcode-area").find(".all").val();
	//var target2 = $(".barcode_input_list").find(".notall");
    var barList = [];

    if (val.indexOf("^") == -1) {
      alert("내용을 입력해주시길 바랍니다.");
	 $(".all").focus();
      return false;
    }
	var data_first = [];
	var data_last = [];
	var barcode_count = 0;
	var text = "";
    if (val) {
      val = val.split("^");
      var first = val[0];
      var secList = val[1].split(",");
      for (var i = 0; i < secList.length; i++) {
        if (secList[i].indexOf("-") == -1) {
          if(String(first + secList[i]).length != 12){
			alert("바코드는 12자리로 생성 되어야 합니다.\n입력값을 다시 확인 하시기 바랍니다.");
			$(".all").focus();
			return false;
		  }
		  if(i < 3 ){
			data_first.push(first + secList[i]);
		  }
		  if(i<secList.length && i>(secList.length-4)){
			data_last.push(first + secList[i]);
		  }
		  barList.push(first + secList[i]);
		  barcode_count++;
        } else {
          var secData = secList[i].split("-");
          var secData0Len = secData[0].length;
          secData[0] = Number(secData[0]);
          secData[1] = Number(secData[1]);

          for (var ii = secData[0]; ii < (secData[1] + 1); ii++) {
            var barData = ii;
            if (String(barData).length < secData0Len) {
              var iiiCnt = secData0Len - String(barData).length;
              for (var iii = 0; iii < iiiCnt; iii++) {
                barData = "0" + barData;
              }
            }
			if(String(first + barData).length != 12){
				alert("바코드는 12자리로 생성 되어야 합니다.\n입력값을 다시 확인 하시기 바랍니다.");
				$(".all").focus();
				return false;
			}
			if(ii < (secData[0]+3) ){
				data_first.push(first + barData);
			}
			if(ii<(secData[1] + 1) && ii>(secData[1]-3)){
				data_last.push(first + barData);
			}
            barList.push(first + barData);
			barcode_count++;
          }
        }
      }	
	  if(barcode_count < 7){
		text = barList;
	  }else{
		text = data_first+" ....... \n ....... "+data_last;
	  }
	  if(confirm("등록할 바코드는\n"+text+" \n("+barcode_count+"개)입니다. 일괄 등록 하시겠습니까?")){
		saveData2($(".all").val());
	  }
	
    }
  }
  // 팝업열기
  function showPopup(multipleFilter) {
    if (multipleFilter) {
      $('#popup').addClass('multiple-filter');
    } else {
      $('#popup').removeClass('multiple-filter');
    }

    $('#popup').removeClass('hide');
  }

  // 팝업닫기
  function closePopup() {
    $('#popup').addClass('hide');
  }
  //추가 끝
  function addSelectClassBarcode() {
    
	var allBarcodes = $('.listContent .item');
    var start = $('.listContent .item.start').length;
    var end = $('.listContent .item.end').length;
    var startNode = $('.listContent .item.start');
    var endNode = $('.listContent .item.end');
    var startIndex = start ? allBarcodes.index(startNode) : -1;
    var endIndex = end ? allBarcodes.index(endNode) : -1;

    allBarcodes.removeClass('select');

    if (start && end) {
		
      allBarcodes.each(function(key, val) {
        if (startIndex <= allBarcodes.index($(this))
          && allBarcodes.index($(this)) <= endIndex ) {
          $(this).addClass('select');
        }
      });
	  if($('.listContent li.select').length > 4001){
			alert("시스템 처리 용량 제한으로 선택은 4000개 까지만 가능합니다.");
			//allBarcodes.removeClass('select');
			allBarcodes.each(function(key, val) {
				if ((startIndex+4000) <= allBarcodes.index($(this))) {
				  $(this).removeClass('select');
				}
			  });
		}
    }

    if (start && !end) {
      startNode.addClass('select');
    }

    if ($('.listContent li.select').length > 0) {
      $('#selectActWrap').show();
    } else {
      $('#selectActWrap').hide();
    }
  }

  function actSelectedItems(act) {
    var totalLength = $('.listContent .item.select').length;
    var liNode;
    var barcode;
    var dataIndex;
    var changedObj;
    var findAddedItem;
    var findAddedItemIndex;
    var findItem;
    var findItemIndex;

    var toDeleteObjects = [];
    // var toDeleteDataIndexArr = [];
    // var toDeleteChangedDataIndexArr = [];

    $('.listContent .item.select').each(function() {
      liNode = $(this);
      barcode = liNode.find('.barcode').text();
      dataIndex = Number(liNode.data('index'));

      if (act === 'check') {
        if ( liNode.hasClass('originDeleted')
          || liNode.hasClass('checked')
          || liNode.hasClass('newAdd') ) {
          return true; // continue
        }

        DATA[dataIndex]['checked_at'] = 'currentDate'
        DATA[dataIndex]['bc_del_yn'] = 'N'
      }

      if (act === 'delete') {
        if ( liNode.hasClass('originDeleted')
          || liNode.hasClass('deleted')) {
          return true; // continue
        }

        if (DATA[dataIndex]['bc_id'] === '0') { // 신규 바코드라면 따로 모아서 삭제
          toDeleteObjects.push(DATA[dataIndex]);
          return true;

          /*
          DATA.splice(dataIndex, 1);
          // toDeleteDataIndexArr.push(dataIndex); // 바로 삭제 하지 말고 dataIndex 모아서 랜더 전에 삭제

          findAddedItem = CHANGED_DATA.find(function (item) {
            return (item.bc_barcode === barcode && item.bc_id === '0')
          });
          findAddedItemIndex = CHANGED_DATA.indexOf(findAddedItem);
          CHANGED_DATA.splice(findAddedItemIndex, 1);
          // toDeleteChangedDataIndexArr.push(findAddedItemIndex);

          if (dataIndex + 1 === totalLength)  { // 마지막 항목 처리중이라면 브레이크
            return false; // break
          } else {
            return true; // continue
          }
           */

        } else {
          DATA[dataIndex]['checked_at'] = null
          DATA[dataIndex]['bc_del_yn'] = 'Y'
        }
      }

      // deep copy
      changedObj = JSON.parse(JSON.stringify(DATA[dataIndex]));

      // 중복 검색
      findItem = CHANGED_DATA.find(function (item) {
        return item.bc_id === changedObj.bc_id
      });

      findItemIndex = CHANGED_DATA.indexOf(findItem);

      if (findItemIndex !== -1) { // 중복이 있다면 삭제
        CHANGED_DATA.splice(findItemIndex, 1);
        // toDeleteChangedDataIndexArr.push(findItemIndex);
      }

      CHANGED_DATA.push(changedObj)
    });

    // 신규 바코드 삭제
    for (var i = 0; i < toDeleteObjects.length; i++) {
      findAddedItem = DATA.find(function (item) {
        return (item.bc_barcode === toDeleteObjects[i].bc_barcode && item.bc_id === '0')
      });
      findAddedItemIndex = DATA.indexOf(findAddedItem);
      DATA.splice(findAddedItemIndex, 1);

      findAddedItem = CHANGED_DATA.find(function (item) {
        return (item.bc_barcode === toDeleteObjects[i].bc_barcode && item.bc_id === '0')
      });
      findAddedItemIndex = CHANGED_DATA.indexOf(findAddedItem);
      CHANGED_DATA.splice(findAddedItemIndex, 1);
    }

    if (CHANGED_DATA.length > 0 || toDeleteObjects.length > 0) {
      sortDataAndRender();
    }

    // item select 초기화
    $('.listContent .item.select').removeClass('select');
    $('.listContent .item.start').removeClass('start');
    $('.listContent .item.end').removeClass('end');
  }


  function getData() {
    var data = [];

    if (LOADING) {
      return;
    }

    LOADING = true;

    $.ajax({
      url: '/adm/shop_admin/ajax.release_stock_barcode_list.php',
      type: 'GET',
      data: {
        it_id: '<?php echo $it_id ?>',
        io_id: '<?php echo $io_id ?>',
        sel_field: 'bc_barcode',
        search_text: $('#search_text').val(),
        only_not_deleted_barcode: 'true',
      },
      dataType: 'json',
      async: false,
    })
    .done(function(result) {
      data = result.data;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      LOADING = false;
    });

    return data;
  }

  function renderData(pullData) {
    var html;
    var allBarcodeCnt = 0;
    var checkedBarcodeCnt = 0;

    $('.listContent').empty();

    if (pullData) {
      DATA = getData();
      sortData();
    }

    if (DATA.length > 0) {
      var check_status = '';
      var status_class = ''; // checked, deleted, newAdd, unchecked

      for (var i = 0; i < DATA.length; i++) {
        allBarcodeCnt++;

        if (DATA[i].bc_del_yn === 'Y') {
          check_status = '<span>삭제됨</span>';
          status_class = 'deleted';
          if (DATA[i].origin_del_yn === 'Y') {
            status_class = 'deleted originDeleted';
          }
          allBarcodeCnt--;

        } else if (DATA[i].bc_id === '0') {
          check_status = '<span>신규</span>';
          status_class = 'newAdd';
          checkedBarcodeCnt++;

        /*
        } else if (DATA[i].checked_at) {
          check_status = '<img src="/img/barcode_icon_1.png"/>';
          status_class = 'checked'
          checkedBarcodeCnt++;

        } else if (!DATA[i].checked_at) {
          check_status = '<span>미확인</span>';
          status_class = 'unchecked'
        }
        */

        // 페이지 진입 시 무조건 미확인으로 표기
        } else if (DATA[i].checked_at === 'currentDate') {
          check_status = '<img src="/img/barcode_icon_1.png"/>';
          status_class = 'checked';
          checkedBarcodeCnt++;

        } else {
          check_status = '<span>미확인</span>';
          status_class = 'unchecked';
        }

        html = '<li class="item item_' + i + ' flex-row align-center ' + status_class + '" data-index="' + i + '" data-bc_id="' + DATA[i].bc_id + '">';
        html += '  <div class="barcode">' + DATA[i].bc_barcode + '</div>';
        html += '  <div class="check_status">' + check_status + '</div>';
        html += '  <div class="more">';
        if (DATA[i].origin_del_yn !== 'Y') {
          html += '    <span>⋮</span>';
          html += '    <ul class="select">';
          html += '      <li class="check">확인함</li>';
          html += '      <li class="delete">삭제함</li>';
          html += '    </ul>';
        }
        html += '  </div>';
        html += '</li>';
        $('.listContent').append(html);

        check_status = '';
        status_class = '';
      }

      $('#checkedBarcodeCnt').text(checkedBarcodeCnt);
      $('#allBarcodeCnt').text(allBarcodeCnt);
    } else {
      if( !$('#_empty').length ) {
        $('.listContent').append('<li style="padding: 13px 0;"><span id="_empty">등록된 바코드가 없습니다.</span></li>');
      }
    }

    $('#selectActWrap').hide();
  }

  function sortDataAndRender() {
    sortData();
    renderData(false);
  }

  function sortData() {
    var sortBy = $('#sortOption').val();
    console.log('sort - ' + sortBy);

    if (sortBy === 'unchecked') {
      DATA.sort(dynamicSortMultiple('-checked_at', 'bc_barcode'));
    } else if (sortBy === 'newAdd') {
      DATA.sort(dynamicSortMultiple('bc_id', 'bc_barcode'));
    } else if (sortBy === 'deleted') {
      DATA.sort(dynamicSortMultiple('-bc_del_yn', 'bc_barcode'));
    } else if (sortBy === 'barcodeDesc') {
      DATA.sort(dynamicSort('-bc_barcode'));
    } else if (sortBy === 'barcodeAsc') {
      DATA.sort(dynamicSort('bc_barcode'));
    }
  }

  /*
  * https://stackoverflow.com/questions/1129216/sort-array-of-objects-by-string-property-value
  *
  * People.sort(dynamicSort("Name"));
  * People.sort(dynamicSortMultiple("Name", "-Surname"));
  */
  function dynamicSort(property) {
    var sortOrder = 1;
    if (property[0] === "-") {
      sortOrder = -1;
      property = property.substr(1);
    }
    return function (a, b) {
      /* next line works with strings and numbers,
       * and you may want to customize it to your needs
       */
      var result;
      if (property === 'checked_at') {
        if (a[property] === 'currentDate' && b[property] === 'currentDate') {
          result = 0;
        } else if (a[property] === 'currentDate' && b[property] !== 'currentDate') {
          result = -1;
        } else if (a[property] !== 'currentDate' && b[property] === 'currentDate') {
          result = 1;
        }
      } else {
        if (!isNaN(a[property]) && !isNaN(b[property])) {
          result = (Number(a[property]) < Number(b[property])) ? -1 : (Number(a[property]) > Number(b[property])) ? 1 : 0;
        } else {
          result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
        }
      }
      return result * sortOrder;
    }
  }

  function dynamicSortMultiple() {
    /*
     * save the arguments object as it will be overwritten
     * note that arguments object is an array-like object
     * consisting of the names of the properties to sort by
     */
    var props = arguments;
    return function (obj1, obj2) {
      var i = 0, result = 0, numberOfProperties = props.length;
      /* try getting a different result from 0 (equal)
       * as long as we have extra properties to compare
       */
      while (result === 0 && i < numberOfProperties) {
        result = dynamicSort(props[i])(obj1, obj2);
        i++;
      }
      return result;
    }
  }

  function saveData() {
    if (!confirm('완료하시겠습니까?')) {
      return;
    }

    if (CHANGED_DATA.length === 0) {
      alert('변경한 내역이 없습니다');
      return;
    }

    if (LOADING === true) {
      return;
    }

    LOADING = true;

    $.ajax({
      url: '/adm/shop_admin/ajax.release_stock_barcode_check_update.php',
      type: 'POST',
      data: {
        it_id: '<?php echo $it_id ?>',
        io_id: '<?php echo $io_id ?>',
        data: CHANGED_DATA,
        barcode_qty_prev: '<?= $row['sum_checked_barcode_qty'] ?>',
      },
      dataType: 'json',
      async: true,
	   beforeSend: function () {
		$('body').css('overflow-y', 'hidden');
		$('#loading').show();
       }
       , complete: function () {
        $('body').css('overflow-y', 'scroll');
		$('#loading').hide();    
       }
    })
    .done(function(result) {
      alert(result.message);
      goBack();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      LOADING = false;
    });

  }

  function saveData2(barList) {
    if (LOADING === true) {
      return;
    }	
    LOADING = true;
    $.ajax({
      url: '/adm/shop_admin/ajax.release_stock_barcode_check_update.php',
      type: 'POST',
      data: {
        it_id: '<?php echo $it_id ?>',
        io_id: '<?php echo $io_id ?>',
        //data: CHANGED_DATA,
		data2 : barList,
        barcode_qty_prev: '<?= $row['sum_checked_barcode_qty'] ?>',
      },
      dataType: 'json',
      async: true,
	   beforeSend: function () {
		$('body').css('overflow-y', 'hidden');
		$('#loading').show();
       }
       , complete: function () {
        $('body').css('overflow-y', 'scroll');
		$('#loading').hide();    
       }
    })
    .done(function(result) {
      alert(result.message);
      goBack();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      LOADING = false;
    });
	
  }

  function showLoading(flag) {
    if (flag) {
      $('body').css('overflow-y', 'hidden');
      $('#loading').show();
    } else {
      $('body').css('overflow-y', 'scroll');
      $('#loading').hide();
    }
  }

  function sendInvoiceNum(text) {
    var prodPayCode = '<?php echo $prod_pay_code ?>'
    var scannedBarcode = text;
    var barcodeProdCode = text.slice(0, 12);
    var barcode = text.slice(12, 24);

    // 바코드 정상 여부 체크
    $.post('/shop/ajax.check_barcode.php', {
      it_id: '<?php echo $it_id ?>',
      barcode: scannedBarcode,
    }, 'json')
    .done(function (data) {
      var barcode = String(data.data.converted_barcode);

      if (barcode.length !== 12) {
        $.toast('\'' + barcode + '\'는 잘못된 바코드입니다. <br/> (12글자 아님)', {
          duration: 3000,
          type: 'danger'
        });
        return;
      }

      if (isNaN(barcode)) {
        $.toast('\'' + barcode + '\'는 숫자 이외의 문자가 포함되어있습니다. <br/> 다시스캔해주세요.', {
          duration: 3000,
          type: 'danger'
        });
        return;
      }

      upsertBarcode(barcode);

      $.toast('\'' + barcode + '\'가 등록되었습니다.', {
        duration: 2000,
        type: 'info'
      });
    })
    .fail(function ($xhr) {
      var data = $xhr.responseJSON;
      $.toast(data && data.message, {
        duration: 3000,
        type: 'danger'
      });
    });

    // if (text.length !== 24) {
    //   alert('유효하지 않은 바코드입니다. 다시 스캔해주세요. (12자리 아님)');
    //   return;
    // }
    //
    // if (barcodeProdCode !== prodPayCode) {
    //   alert('상품을 잘못 스캔하셨습니다. 상품을 다시 확인해주세요.');
    //   return;
    // }
    //
    // if (isNaN(barcode)) {
    //   alert('바코드에 숫자 이외의 문자가 포함되어있습니다. 다시 스캔해주세요.')
    //   return;
    // }
    //
    // upsertBarcode(barcode);
  }

  function upsertBarcode(barcode) {
    
	var findItem = DATA.find(function (item) {
      return item.bc_barcode === barcode
    });

    var findItemIndex = DATA.indexOf(findItem);

    if (findItemIndex !== -1) { // 리스트 내 있다면 확인으로 처리
      if (!DATA[findItemIndex]['bc_id'] === '0') { // 해당 아이템이 신규가 아닐시에만 확인으로 처리
        DATA[findItemIndex]['checked_at'] = 'currentDate';
        DATA[findItemIndex]['bc_del_yn'] = 'N'
      }
    } else { // 리스트 내 없다면 새로운 데이터 푸시
      var newOjb = {
        bc_id: '0',
        bc_barcode: barcode,
        bc_del_yn: 'N',
        bc_status: '정상',
        checked_at: null,
        checked_at_full: null,
        checked_by: null,
      };

      // 데이터 삽입
      DATA.splice(findItemIndex, 0, newOjb);
      DATA.sort(dynamicSort('bc_barcode'));

      CHANGED_DATA.push(newOjb);
    }

    sortDataAndRender();
  }

  function open_invoice_scan() {
    /* 기종체크 */
    var deviceUserAgent = navigator.userAgent.toLowerCase();
    var device;

    if(deviceUserAgent.indexOf("android") > -1) {
      /* android */
      device = "android";
    }

    if(deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1) {
      /* ios */
      device = "ios";
    }

    switch(device) {
      case "android" :
        /* android */
        window.EroummallApp.openInvoiceNum("");
        break;
      case "ios" :
        /* ios */
        window.webkit.messageHandlers.openInvoiceNum.postMessage("1");
        break;
    }
  }

  function barcodeInputFocus() {
    if (!IS_OPEN_WEB_BARCODE) {
      clearInterval(BARCODE_INPUT_FOCUS_INTERVAL);
      return;
    }
    $('#web-barcode-input').attr("readonly",true);
    $('#web-barcode-input').focus();
    setTimeout(function(){ $('#web-barcode-input').attr("readonly",false); }, 80);
  }

  function openWebBarcode(target) {
    if (target) {
      POP_BARCODE_INPUT_TARGET = $(target).closest('li').find('.frm_input');
      <?php
        # 서원 : 22.08.23 - PDA입력 시 기존 input 재입력 필요시 기존 데이터 초기화
        # 추가 : if($(POP_BARCODE_INPUT_TARGET).val()) { $(POP_BARCODE_INPUT_TARGET).val(''); }
      ?>
      if($(POP_BARCODE_INPUT_TARGET).val()) {
        $(POP_BARCODE_INPUT_TARGET).val('');
      }
    }

    $('#web-barcode').css('display', 'flex');
    
    $('#web-barcode-input').attr("readonly",true);
    $('#web-barcode-input').focus();
    setTimeout(function(){ $('#web-barcode-input').attr("readonly",false); }, 80);

    IS_OPEN_WEB_BARCODE = true;
    $('#web-barcode-input').val('');
    BARCODE_INPUT_FOCUS_INTERVAL = setInterval(barcodeInputFocus, 1000);
  }

  function closeWebBarcode() {
    IS_OPEN_WEB_BARCODE = false;
    POP_BARCODE_INPUT_TARGET = null;

    clearInterval(BARCODE_INPUT_FOCUS_INTERVAL)
    $('#web-barcode').hide();
    $('#web-barcode-input').val('');
  }

  function receiveBarcode(tempBarcode) {
    setTimeout(function() {
      var scannedBarcode = tempBarcode || $('#web-barcode-input').val();
      $('#web-barcode-input').val('');

      if (!scannedBarcode) return;
      if (String(scannedBarcode).length < 3) {
        alert('키보드 사용은 불가능합니다.');
        return;
      }

      // 바코드 정상 여부 체크
      $.post('/shop/ajax.check_barcode.php', {
        it_id: '<?php echo $it_id ?>',
        barcode: scannedBarcode,
      }, 'json')
      .done(function (data) {
        var barcode = String(data.data.converted_barcode);

        if (barcode.length !== 12) {
          $.toast('\'' + barcode + '\'는 잘못된 바코드입니다. <br/> (12글자 아님)', {
            duration: 3000,
            type: 'danger'
          });
          return;
        }

        if (isNaN(barcode)) {
          $.toast('\'' + barcode + '\'는 숫자 이외의 문자가 포함되어있습니다. <br/> 다시스캔해주세요.', {
            duration: 3000,
            type: 'danger'
          });
          return;
        }

        upsertBarcode(barcode);

        $.toast('\'' + barcode + '\'가 등록되었습니다.', {
          duration: 2000,
          type: 'info'
        });

        closeWebBarcode();
      })
      .fail(function ($xhr) {
        var data = $xhr.responseJSON;
        $.toast(data && data.message, {
          duration: 3000,
          type: 'danger'
        });
      });

    }, 100);
  }

  function receiveBarcodeOnPop(tempBarcode) {
    setTimeout(function() {
      var scannedBarcode = tempBarcode || $('#web-barcode-input').val();
      $('#web-barcode-input').val('');

      if (!scannedBarcode) return;
      if (String(scannedBarcode).length < 3) {
        alert('키보드 사용은 불가능합니다.');
        return;
      }

      // 바코드 정상 여부 체크
      $.post('/shop/ajax.check_barcode.php', {
        it_id: '<?php echo $it_id ?>',
        barcode: scannedBarcode,
      }, 'json')
      .done(function (data) {
        var barcode = String(data.data.converted_barcode);

        if (barcode.length !== 12) {
          $.toast('\'' + barcode + '\'는 잘못된 바코드입니다. <br/> (12글자 아님)', {
            duration: 3000,
            type: 'danger'
          });
          return;
        }

        if (isNaN(barcode)) {
          $.toast('\'' + barcode + '\'는 숫자 이외의 문자가 포함되어있습니다. <br/> 다시스캔해주세요.', {
            duration: 3000,
            type: 'danger'
          });
          return;
        }

        <?php
          # 해당 주석은 html코드상 보이지 않음.
          # 서원 : 22.08.22 - PDA 바코드 연속 스캔시 입력된 데이터 검증 및 비어있는 input박스에 데이터 입력.
          # 삭제 : $(POP_BARCODE_INPUT_TARGET).val(barcode);
        ?>
        $item = $('#add_barcode_pop .barcode_input_list').find('.notall');
        $item.each(function (i, val) {
          if($(this).val()==""){
            $(this).val(barcode);
            notallLengthCheck();
            return false;
          }
        });

        $.toast('\'' + barcode + '\'가 등록되었습니다.', {
          duration: 2000,
          type: 'info'
        });

        <?php
          # 해당 주석은 html코드상 보이지 않음.
          # 서원 : 22.08.22 - PDA 바코드 연속 스캔시 하위 팝업(바코드 재고추가 팝업) 부분에 barcode_input_list 필드 추가
          # 삭제 : closeWebBarcode();
        ?>
        if($item.last().val()) {
          $('#btn_plus').trigger('click');
        }

      })
      .fail(function ($xhr) {
        var data = $xhr.responseJSON;
        $.toast(data && data.message, {
          duration: 3000,
          type: 'danger'
        });
      });

    }, 100);
  }

  function goBack() {
    location.href = '/shop/release_stock_barcode_view.php?it_id=<?php echo $it_id ?>&io_id=<?php echo $io_id ?>';
  }

  function showPopBarcodeList(flag) {
    if (flag) {
      $('#add_barcode_pop').show();
    } else {
      $('#add_barcode_pop').hide();
      clearPopBarcodeList();
    }
  }

  function setQtyNumber(x, param) {
    var targetNode = $(x).parent().find('.qty_input');
    var currentVal = Number(targetNode.val());
    var maxVal = 99999;
    var minVal = 1;

    if (param === 'plus') {
      if (currentVal < maxVal) {
        targetNode.val(++currentVal);
        $('#add_barcode_pop .barcode_input_list').append($('#mockup').html());
      }
    } else if (param === 'minus') {
      if (currentVal > minVal) {
        targetNode.val(--currentVal);
        $('#add_barcode_pop .barcode_input_list').find('li').last().remove();
      }
    } else { // 숫자 입력
      targetNode.val(param);
    }
    $('.frm_input').keyup(function() {
        var _id = this.id;
        $selObj = $("#"+_id);
        var maxLen = $("#"+_id).prop("maxlength");
        var txtLen = $(this).val().length;
        if (maxLen == txtLen) {
            var curIndex = Number(_id.substring(4));
            $("#list" + (curIndex + 2)).focus();
        }
    });
  }

  function setBarcodeInput(x) {
    var currentVal = Number($(x).val());
    var barcodeInputLength = $('#add_barcode_pop .barcode_input_list').find('li').length;

    if (currentVal < 0) {
      $(x).val('1');
      currentVal = 1;
    }

    if (currentVal > barcodeInputLength) {
      for (var i = 0; i < currentVal - barcodeInputLength; i++) {
        $('#add_barcode_pop .barcode_input_list').append($('#mockup').html());
      }

    } else if (currentVal < barcodeInputLength) {
      for (var i = 0; i < barcodeInputLength - currentVal; i++) {
        $('#add_barcode_pop .barcode_input_list').find('li').last().remove();
      }
    }
  }

  function addAutoBarcode(_this) {
    var ul = $(_this).closest('ul');
    var li_num = $(_this).closest('li').index();
    var li_val = $(_this).closest('li').find('.notall').val();
    var li_last = $(ul).find('li').last().index();
    var p_num = 0;

    if (li_val.length !== 12) {
      alert('바코드 12자리를 입력해주세요.');
      return false;
    }

    for (var i = li_num + 1; i <= li_last; i++) {
      p_num++;
      $(ul).find('li').eq(i).find('.notall').val((parseInt(li_val) + p_num));
    }

    notallLengthCheck();
  }

  function notallLengthCheck() {
    var result = true;

    $('#add_barcode_pop .barcode_input_list img.barcode_icon.type1').removeClass('active');
    $('#add_barcode_pop .barcode_input_list .overlap').removeClass('active');

    var $item = $('#add_barcode_pop .barcode_input_list').find('.notall');
    $item.removeClass('active');

    var dataTable = {};
    $item.each(function (i, val) {
      var $cur = $(this);
      var barcode = $cur.val();
      var maxlength = parseInt($cur.attr('maxlength'));
      var length = barcode.length;
      if (length < maxlength && length) {
        $cur.addClass('active');
        result = false;
      }
      if (length === maxlength && /^-?\d+$/.test(barcode)) { // 숫자만 입력되었는지 체크
        $cur.parent().find('img.barcode_icon.type1').addClass('active');

        if (!dataTable[barcode])
          dataTable[barcode] = [];
        dataTable[barcode].push(i);
      }
    });

    var keys = Object.keys(dataTable);
    for (var i = 0; i < keys.length; i++) {
      var val = dataTable[keys[i]];
      if (val.length > 1) {
        for (var j = 0; j < val.length; j++) {
          var idx = val[j];
          $($item[idx]).parent().find('img.barcode_icon.type1').removeClass('active');
          $($item[idx]).parent().find('.overlap').addClass('active');
          result = false;
        }
      }
    }

    return result;
  }

  function savePopBarcodeList() {
    var $item = $('#add_barcode_pop .barcode_input_list').find('.notall');
    var $item_count = 0;
    var $confirmed_count = 0;
    var $index =0;
    var $overlapBarcode = [];

    $item.each(function(i,val) {
        console.log("overlap check", $($item[$index]).parent().find('.overlap'));
        $itemOverlap = $($item[$index]).parent().find('.overlap');
        if ($itemOverlap){

            if ($itemOverlap.hasClass('active')) {
                $overlapBarcode.push($(this).val()+'\n');
                $item_count++;
                console.log("overlap found"+$overlapBarcode.length+" : "+$overlapBarcode[0]);
            }
        }
        $index++;
    });

    if (notallLengthCheck()) {
      $item.each(function() {
        if ($(this).val().length === 12) {
          upsertBarcode($(this).val());
          $confirmed_count++;
        }
      });

      alert($confirmed_count+'개의 바코드 재고가 추가 되었습니다');
      showPopBarcodeList(false);
    } else {
      if ($item_count > 0) {

        alert($item_count+' 개의 중복 바코드가 존재합니다.\n'+$overlapBarcode);
      } else {
        alert('바코드 길이가 맞지 않습니다');

      }

    }


  }


  function clearPopBarcodeList() {
    $('.all').val('');
	$('.qty_input').val('1');
    $('.barcode_input_list').empty();
    $('#add_barcode_pop .barcode_input_list').append($('#mockup').html());
  }

  
  // 앱이 아닌 경우 바코드버튼 숨김
  if(!window.EroummallApp && !(window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.openBarcode )) {
    $('.nativePopupOpenBtn').hide();
  }

  // 23.06.14 : 신규 앱 카메라 기능 동작 예외처리.
  if (window.ReactNativeWebView) { $(".nativePopupOpenBtn").show(); }
  
</script>

<?php //include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>
