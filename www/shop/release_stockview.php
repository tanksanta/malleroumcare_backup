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

    #popupBody #content {
      padding: 20px;
    }

    #popupBody #content .barcode {
      width: 60%;
    }

    #popupBody #content .check_status {
      width: 40%;
      text-align: center;
    }

    #popupBody #content li {
      border-bottom: 1px solid #dfdfdf;
      padding: 13px 0;
      font-size: 14px;
    }

    #popupBody #content li:first-of-type {
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

<!-- 고정 상단 -->
<div id="popupHeaderTopWrap">
  <div class="title"><?php echo $g5["title"] ?></div>
  <div class="close">
    <a href="javascript:window.close();">
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
      <div style="width: 65%">
        <?php echo $full_it_name ?>
      </div>
      <a href="javascript:alert('TODO');" class="barcodeSearch nativeDeliveryPopupOpenBtn" style="width: 35%">
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
        <div class="barcode">바코드</div>
        <div class="check_status">확인 완료 (02/22)</div>
      </li>
      <li class="flex-row align-center">
        <div class="barcode">바코드</div>
        <div class="check_status">확인 완료 (02/22)</div>
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
  var IS_POP = <?=$isPop ? 'true' : 'false'?>;
  var KEYUP_TIMER;
  var PAGE = 1;
  var LOADING = false;

  // 바코드 스캔용 전역변수
  var sendBarcodeTargetList;
  var cur_ct_id = null;
  var cur_it_id = null;

  $(function() {
    // getData();

    // 인피니티 스크롤
    $(window).scroll(function () {
      if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight * 0.9) {
        // getData();
      }
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
        sel_field: $('#searchOption').val(),
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

  function sendBarcode(text) {
    /* 기종체크 */
    var deviceUserAgent = navigator.userAgent.toLowerCase();
    var device;

    if (deviceUserAgent.indexOf("android") > -1) {
      /* android */
      device = "android";
    }

    if (deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1) {
      /* ios */
      device = "ios";
    }

    $.ajax({
      url: "/shop/ajax.release_purchaseorderview.check.php",
      type: "POST",
      data: {
        od_id: "<?=$od_id?>"
      },
      success: function (result) {
        if (result.error == "Y") {
          switch (device) {
            case "android" :
              /* android */
              window.EroummallApp.closeBarcode("");
              break;
            case "ios" :
              /* ios */
              window.webkit.messageHandlers.closeBarcode.postMessage("");
              break;
          }
          var params = getUrlParams();
          delete params.od_id;
          delete params.ct_id;
          var query_string = decodeURI($.param(params));
          window.location.href = "<?=G5_SHOP_URL?>/release_purchaseorderlist.php?" + query_string;
        } else {
          if (sendBarcodeTargetList[0]) {
            $.post('/shop/ajax.check_barcode.php', {
              it_id: cur_it_id,
              barcode: text,
            }, 'json')
              .done(function (data) {
                var sendBarcodeTarget = $(".frm_input_" + sendBarcodeTargetList[0]);
                $(sendBarcodeTarget).val(data.data.converted_barcode);
                sendBarcodeTargetList = sendBarcodeTargetList.slice(1);
                check_option(cur_it_id);
              })
              .fail(function ($xhr) {
                switch (device) {
                  case "android" :
                    /* android */
                    window.EroummallApp.closeBarcode("");
                    break;
                  case "ios" :
                    /* ios */
                    window.webkit.messageHandlers.closeBarcode.postMessage("");
                    break;
                }
                var data = $xhr.responseJSON;
                setTimeout(function () {
                  alert(data && data.message);
                }, 500);
              });
          }
        }

        notallLengthCheck(false);
      }
    });
  }
</script>

<?php include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>