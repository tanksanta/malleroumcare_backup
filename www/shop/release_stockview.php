<?php
//$sub_menu = '400480';

include_once("./_common.php");
//auth_check($auth[$sub_menu], "r");

$g5["title"] = "재고관리";

if (!$it_id) {
  alert('올바른 접근이 아닙니다');
}

$where = "WHERE it_id = '${it_id}' ";

if ($io_id) {
  $where .= " AND io_id = '{$io_id}' ";
}

$sql = "
  SELECT
   T.*
  FROM
  (SELECT
    (SELECT 
      IFNULL(sum(ws_qty) - sum(ws_scheduled_qty), 0) 
    FROM warehouse_stock 
    WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND ws_del_yn = 'N') AS sum_ws_qty,
    (SELECT count(*)
      FROM g5_cart_barcode
      WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND bc_del_yn = 'N') AS sum_barcode_qty,
    a.*,
    b.io_type,
    b.io_id
  FROM
    (SELECT
      it_id,
      it_name,
      it_use,
      it_option_subject
    FROM g5_shop_item i) AS a
  LEFT JOIN (SELECT * from g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS b ON (a.it_id = b.it_id)) AS T 
  {$where}
";
$row = sql_fetch($sql);

$sql = "
  SELECT IFNULL(MAX(created_at), '미확인') AS last_checked_at
  FROM stock_barcode_check_log
  {$where}
";

$last_checked_at = sql_fetch($sql)['last_checked_at'];

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

    #popupBody .searchFormTop {
      padding-top: 25px;
    }

    #popupBody .searchFormTop .barcodeSearch {
      position: absolute;
      display: inline-block;
      font-weight: bold;
      text-align: right;
      top: -5px;
      right: 0px;
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

    #popupBody #content {
      padding: 20px;
    }

    #popupBody .listContent .barcode {
      width: 55%;
      font-weight: bold;
      font-size: 20px;
    }

    #popupBody .listContent .check_status {
      width: 35%;
      text-align: center;
    }

    #popupBody .listContent .more {
      width: 10%;
      text-align: center;
    }

    #popupBody .listContent .more {
      width: 10%;
      text-align: center;
      font-size: 26px;
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

    #popupBody .listContent li {
      border-bottom: 1px solid #dfdfdf;
      padding: 13px 0;
      font-size: 14px;
    }

    #popupBody .listContent li:first-of-type {
      border-top: 1px solid #dfdfdf;
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

    /* 고정 하단 */
    #popupFooterBtnWrap {
      position: fixed;
      width: 100%;
      height: 70px;
      background-color: #000;
      bottom: 0px;
      z-index: 10;
    }

    #popupFooterBtnWrap > button {
      font-size: 18px;
      font-weight: bold;
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

  </style>
</head>

<body>

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

<!-- 고정 상단 -->
<div id="popupHeaderTopWrap">
  <div class="title"><?php echo $g5["title"] ?></div>
  <div class="close">
    <a href="javascript:window.close();">
      &times;
    </a>
  </div>
</div>

<div id="popupBody">
  <div id="searchForm">
    <div class="searchFormTop flex-row justify-space-between">
      <div style="width: 100%">
        <?php echo $full_it_name ?><br/>
        <span style="font-size: 13px">재고수량 : <?= $row['sum_ws_qty'] ?> / 바코드 : <?= $row['sum_barcode_qty'] ?><br/> 마지막 확인 일시 : <?= $last_checked_at ?></span>
      </div>
      <a href="javascript:open_invoice_scan();" class="barcodeSearch nativeDeliveryPopupOpenBtn" style="width: 35%">
        바코드찾기
        <img src="/img/bacod_img.png">
      </a>
    </div>

    <div class="flex-row" style="margin-top: 20px">
      <input type="text" name="search_text" id="search_text" placeholder="바코드 입력" value="<?php echo $search_text; ?>">
      <button type="button" id="searchSubmitBtn" onclick="search()">검색</button>
    </div>
  </div>

  <div id="content">
    <ul class="listContent">
      <li class="flex-row align-center">
        <div class="barcode">1111111111111</div>
        <div class="check_status">확인 완료 (02/22)</div>
        <div class="more">
          <span>⋮</span>
          <ul class="select">
            <li class="rental">대여함</li>
            <li class="release">출고함</li>
            <li class="change_option">옵션이동</li>
          </ul>
        </div>
      </li>
      <li class="flex-row align-center">
        <div class="barcode">1111111111112</div>
        <div class="check_status">확인 완료 (02/22)</div>
        <div class="more">
          <span>⋮</span>
          <ul class="select">
            <li class="rental">대여함</li>
            <li class="release">출고함</li>
            <li class="change_option">옵션이동</li>
          </ul>
        </div>
      </li>
    </ul>
  </div>
</div>

<!-- 고정 하단 -->
<div id="popupFooterBtnWrap">
  <button type="button" class="checkBtn" onclick="gotoStockCheck();">재고 확인 시작</button>
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
  var LOADING = false;

  // 바코드 스캔용 전역변수
  var sendBarcodeTargetList;
  var cur_ct_id = null;
  var cur_it_id = null;

  $(function() {
    renderData();

    $(document).on('click', '.listContent .more', function () {
      $(this).find('.select').toggle();
    });
  });

  function sendInvoiceNum(text) {
    alert(text);
    text = text.slice(0, 12);
    $('#search_text').val(text);
    renderData();
  }

  function search() {
    renderData();
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

  function renderData() {
    var data;
    var html;

    $('.listContent').empty();

    data = getData();

    if (data.length > 0) {
      var check_status;

      for (var i = 0; i < data.length; i++) {
        check_status = data[i].checked_at ? '확인 완료 (' + data[i].checked_at +')' : '미확인';

        html = '<li class="flex-row align-center" data-bc_id="' + data[i].bc_id + '">';
        html += '  <div class="barcode">' + data[i].bc_barcode + '</div>';
        html += '  <div class="check_status">' + check_status + '</div>';
        html += '  <div class="more">';
        html += '    <span>⋮</span>';
        html += '    <ul class="select">';
        html += '      <li class="rental">대여함</li>';
        html += '      <li class="release">출고함</li>';
        html += '      <li class="change_option">옵션이동</li>';
        html += '    </ul>';
        html += '  </div>';
        html += '</li>';
        $('.listContent').append(html)
      }
    } else {
      $('.listContent').append('<li>등록된 바코드가 없습니다.</li>')
    }
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

  function openNativeBarcodeScan(_this) {
    var cnt = 0;
    var frm_no = $(_this).closest("li").find(".frm_input").attr("data-frm-no");
    var item = $(_this).closest("ul").find(".frm_input");
    sendBarcodeTargetList = [];

    cur_ct_id = $(_this).data('ct-id');
    cur_it_id = $(_this).data('it-id');

    for (var i = 0; i < item.length; i++) {
      if (!$(item[i]).val() || $(item[i]).attr("data-frm-no") == frm_no) {
        sendBarcodeTargetList.push($(item[i]).attr("data-frm-no"));
        cnt++;
      }
    }

    $('#scanner-count').val(cnt);
    var type = $(_this).data('type');
    if (!type) {
      $('#barcode-selector').fadeIn();
      return;
    }
    if (type === 'native') {
      $('#barcode-scanner-opener').click();
    } else if (type === 'pda') {
      $('#pda-scanner-opener').click();
    }
  }

  function gotoStockCheck() {
    var it_id = '<?php echo $it_id ?>';
    var io_id = '<?php echo $io_id ?>';

    window.open('./release_stockcheck.php?it_id=' + it_id + '&io_id=' + io_id, '_blank');
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
</script>
</body>