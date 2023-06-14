<?php
//$sub_menu = '400480';

include_once("./_common.php");
//auth_check($auth[$sub_menu], "r");

$g5["title"] = "재고관리";

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>재고관리</title>
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
      padding-bottom: 70px;
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
      padding: 20px;
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

    #popupBody #searchForm input[type="checkbox"]:checked + label .icon i {
      opacity: 1;
    }

    #popupBody #searchForm span.label {
      display: inline-block;
      font-size: 14px;
      color: #666;
      margin-right: 10px;
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

    #popupBody #searchOption {
      margin-right: 10px;
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

    #popupBody #content {
      padding: 20px;
    }

    #popupBody .warning_icon {
      font-size: 15px;
      width: 21px;
      height: 21px;
      background: yellow;
      display: inline-block;
      vertical-align: top;
      border-radius: 100%;
      border: 1px solid red;
      text-align: center;
      color: red;
      margin-left: 4px;
    }

    #popupBody #content .name {
      width: 60%;
    }

    #popupBody #content .stockQty {
      width: 20%;
      text-align: center;
    }

    #popupBody #content .barcodeQty {
      width: 20%;
      text-align: center;
    }

    #popupBody #content li {
      border-bottom: 1px solid #dfdfdf;
      padding: 13px 0;
      font-size: 14px;
    }

    #popupBody #content .listHeader li:first-of-type {
      border-top: 1px solid #dfdfdf;
      padding: 8px 0;
    }

    #popupBody .listContent li {
      cursor: pointer;
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
      width: 40%;
    }

    #loading p {
      color: #fff;
      position: relative;
      top: -25px;
    }

  </style>
</head>

<body>

<!-- 고정 상단 -->
<div id="popupHeaderTopWrap">
  <div class="title">재고관리</div>
  <div class="close">
    <a href="/">
      &times;
    </a>
  </div>
</div>

<?php
$sql = "
  SELECT
    count(*) AS cnt
  FROM
    (SELECT
        it_id,
        it_name,
        it_use
      FROM g5_shop_item i) AS a
  LEFT JOIN (SELECT * FROM g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS b ON (a.it_id = b.it_id)
";
$item_count = sql_fetch($sql)['cnt'];

$use_warehouse_where_sql = get_use_warehouse_where_sql();
$sql = "
  SELECT
    (SUM(ws_qty) - SUM(ws_scheduled_qty)) AS ws_qty
  FROM
    warehouse_stock
  WHERE
    ws_del_yn = 'N' {$use_warehouse_where_sql}
";
$stock_count = sql_fetch($sql)['ws_qty'];

$sql = "
  SELECT count(*) AS cnt
  FROM g5_cart_barcode
  WHERE bc_del_yn = 'N'
";
$barcode_count = sql_fetch($sql)['cnt'];
?>

<div id="popupBody">
  <div id="searchForm">
    <div class="searchFormTop flex-row justify-space-between">
      <div style="width: 70%">
        상품 : <?php echo $item_count ?>개<br/>보유 : <?php echo $stock_count ?>개 (바코드 <?php echo $barcode_count ?>개)
      </div>
      <a href="javascript:open_invoice_scan();" class="barcodeSearch nativeDeliveryPopupOpenBtn" style="width: 30%">
        주문찾기
        <img src="/img/bacod_img.png">
      </a>
    </div>

    <div class="flex-row" style="margin-top: 20px">
      <select name="sel_field" id="search_option">
        <!-- <option value="">선택하세요</option> -->
        <option value="all" <?php echo !$search_option ? "selected" : ''; ?>>전체</option>
        <option value="it_name" <?php echo $search_option == 'it_name' ? 'selected' : ''; ?>>상품명</option>
        <option value="io_id" <?php echo $search_option == 'io_id' ? 'selected' : ''; ?>>옵션명</option>
        <option value="ProdPayCode" <?php echo $search_option == 'ProdPayCode' ? 'selected' : ''; ?>>상품코드</option>
      </select>
      <input type="text" name="search_text" id="search_text" placeholder="검색명 입력" value="<?php echo $search_text; ?>">
      <button type="button" id="searchSubmitBtn" onclick="search()">검색</button>
    </div>
    <div style="margin-top: 20px">
      <input type="checkbox" id="cf_flag" value="true">
      <label for="cf_flag">
        <span class="icon">
          <i class="fa fa-check"></i>
        </span>
        <span class="label">보유 재고와 바코드 수량이 상이한 상품만 보기</span>
      </label>
    </div>
  </div>

  <div id="content">
    <ul class="listHeader">
      <li class="flex-row align-center">
        <div class="name">상품명(옵션명)</div>
        <div class="stockQty">보유재고</div>
        <div class="barcodeQty">바코드</div>
      </li>
    </ul>
    <ul class="listContent">
      <li class="flex-row align-center">
        <div class="name">아이템 <span class="warning_icon">!</span></div>
        <div class="stockQty">100</div>
        <div class="barcodeQty">101</div>
      </li>
      <li class="flex-row align-center">
        <div class="name">아이템</div>
        <div class="stockQty">100</div>
        <div class="barcodeQty">100</div>
      </li>
    </ul>
  </div>
</div>

<div id="loading" style="display: none">
  <div>
    <img src="../adm/shop_admin/img/ajax-loading.gif" class="img-responsive">
    <p>잠시만 기다려주세요...</p>
  </div>
</div>

<?php
if (!$member['mb_id']) {
  alert('접근이 불가합니다.');
}
?>
<script>
  var IS_POP = <?=$isPop ? 'true' : 'false'?>;
  var KEYUP_TIMER;
  var PAGE = 1;
  var LOADING = false;

  $(function() {
    getData();

    // 인피니티 스크롤
    $(window).scroll(function () {
      if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight * 0.9) {
        getData();
      }
    });

    $(document).on('click', '.listContent li', function () {
      var it_id = $(this).data('it_id');
      var io_id = $(this).data('io_id');

      location.href = './release_stock_barcode_view.php?it_id=' + it_id + '&io_id=' + io_id;
    });
  });

  function search() {
    PAGE = 1;
    getData();
  }

  function getData(isSearch) {
    if (LOADING) {
      return;
    }

    LOADING = true;
    if (PAGE === 1) {
      $('.listContent').empty()
    }
    $.ajax({
      url: '/adm/shop_admin/ajax.release_stocklist.php',
      type: 'GET',
      data: {
        page: PAGE,
        sel_field: $('#search_option').val(),
        search_text : $('#search_text').val(),
        only_diff_qty : $('#cf_flag').is(':checked') ? true : false,
      },
      // dataType: 'json'
    })
    .done(function(result) {
      $('.listContent').append(result)
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      LOADING = false;
      PAGE++;
    });
  }

  function showLoading(flag) {
    if (flag) {
      $('body').css('overflow-y', 'hidden')
      $('#loading').show();
    } else {
      $('body').css('overflow-y', 'scroll')
      $('#loading').hide();
    }
  }

  function sendInvoiceNum(text) {
    text = text.slice(0, 12);
    $('#search_text').val(text);
    $('#search_option').val('ProdPayCode');
    search();
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

    if (window.ReactNativeWebView) {
      // WebView가 존재하는 경우에 대한 로직
      const url = `expo://BarCodeOpen/sendInvoiceNum`;
      window.location.href = url;
    } else {

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

  }

  // 23.06.14 : input 엔터값 적용
  $(document).on("keyup", "#search_text", function(e) { if (e.key === 'Enter') { $("#searchSubmitBtn").click(); } });
</script>

<?php include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>