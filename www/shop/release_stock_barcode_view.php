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

    #actPopMask {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 10;
      display: none;
    }

    #actPop {
      position: fixed;
      top: 35%;
      left: 10%;
      padding: 15px;
      background: #fff;
      border: 1px solid black;
      width: 80%;
      z-index: 11;
      display: none;
    }

    #actPop .header {
      padding-bottom: 10px;
      border-bottom: 1px solid #dfdfdf;
    }

    #actPop .header p {
      font-size: 23px;
      font-weight: bold;
    }

    #actPop .header button {
      background: none;
      font-size: 38px;
      position: absolute;
      top: -12px;
      right: 0;
    }

    #actPop .body {
      padding-top: 10px;
    }

    #actPop .body li {
      font-size: 20px;
      margin-bottom: 10px;
    }

    #actPop .body li.hide {
      display: none;
    }

    #actPop .body li .type {
      width: 70px;
    }

    #actPop .body li .content {
      width: calc(100% - 70px);
    }

    #actPop .body li .content input,
    #actPop .body li .content select {
      width: 99%;
      height: 30px;
      font-size: 16px;
    }

    #actPop .body li .content input.barcode {
      border: 0;
      font-size: 20px;
    }

    #actPop .body li .content select {
      font-size: 13px;
    }

    #actPop .footer {
      margin-top: 25px;
    }

    #actPop .footer button {
      width: 49%;
      font-size: 20px;
      padding: 5px 0;
      border: 1px solid #7f7f7f;
      background: #a6a6a6;
      color: #fff;
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
    <a href="javascript:void(0);" onclick="goBack()">
      &times;
    </a>
  </div>
</div>

<div id="popupBody">
  <div id="searchForm">
    <div class="searchFormTop flex-row justify-space-between">
      <div style="width: 100%">
        <?php echo $full_it_name ?><br/>
        <span style="font-size: 13px">재고수량 : <?= $row['sum_ws_qty'] ?> / 바코드 (확인완료 <?= $row['sum_checked_barcode_qty'] ?> / 총 <?= $row['sum_barcode_qty'] ?>)<br/> 마지막 확인 일시 : <?= $last_checked_at ?></span>
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
      <li class="item flex-row align-center">
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
      <li class="item flex-row align-center">
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

<div id="actPopMask"></div>
<div id="actPop">
  <div class="hiddenValWrap" style="display: none">
    <input type="hidden" name="act" value="">
    <input type="hidden" name="bc_id" value="">
  </div>

  <div class="header flex-row justify-space-between align-center">
    <p>타이틀</p>
    <button onclick="closeActPop();">&times;</button>
  </div>

  <div class="body">
    <ul>
      <li class="flex-row align-center">
        <div class="type">바코드</div>
        <div class="content"><input type="text" class="barcode" readonly value="바코드"/></div>
      </li>
      <li class="flex-row align-center memoWrap">
        <div class="type">메모</div>
        <div class="content">
          <input type="text" class="memo" />
        </div>
      </li>
      <li class="flex-row align-center optionWrap">
        <div class="type">옵션</div>
        <div class="content">
          <select id="item_option">
            <?php
            if ($io_id) {
              $sql = "select io_id from g5_shop_item_option where it_id = '{$it_id}' and io_type = '0' and io_use = '1' ";
              $result = sql_query($sql);

              $option = '';
              $option_br = '';
              while ($io_row = sql_fetch_array($result)) {
                if ($io_id == $io_row['io_id']) { // 같은 옵션이면 건너띄우기
                  continue;
                }

                $subj = explode(',', $row['it_option_subject']);
                $opt = explode(chr(30), $io_row['io_id']);
                for ($k = 0; $k < count($subj); $k++) {
                  if ($subj[$k] && $opt[$k]) {
                    $option .= $option_br . $subj[$k] . ' : ' . $opt[$k];
                    $option_br = ' / ';
                  }
                }
            ?>
            <option value="<?php echo $io_row['io_id'] ?>"><?php echo $option ?></option>
            <?php
                $option = '';
                $option_br = '';
              }
            }
            ?>
          </select>
        </div>
      </li>
    </ul>
  </div>

  <div class="footer flex-row justify-space-between align-center">
    <button onclick="saveActPop();">저장</button>
    <button onclick="closeActPop();">취소</button>
  </div>
</div>

<?php
if (!$member['mb_id']) {
  alert('접근이 불가합니다.');
}
?>
<script>
  var LOADING = false;
  var IT_ID = '<?php echo $it_id ?>';
  var IO_ID = '<?php echo $io_id ?>';

  // 바코드 스캔용 전역변수
  var sendBarcodeTargetList;
  var cur_ct_id = null;
  var cur_it_id = null;
  var page = 1;
  $(function() {
    renderData();
	
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
      var act = $(this).attr('class'); // rental, release, change_option
      var liNode = $(this).closest('.item');
      var barcode = liNode.find('.barcode').text();
      var bc_id = liNode.data('bc_id');

      showActPop(act, barcode, bc_id)
    });
	
	// 인피니티 스크롤
    $(window).scroll(function () {
      if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight * 0.9) {
		renderData(1);
      }
    });
  });

  function showActPop(act, barcode, bc_id) {
    $('#actPop li.memoWrap').addClass('hide');
    $('#actPop li.optionWrap').addClass('hide');

    // rental, release, change_option
    if (act === 'rental') {
      $('#actPop .header p').text('대여 기록');
      $('#actPop li.memoWrap').removeClass('hide');

    } else if (act === 'release') {
      $('#actPop .header p').text('출고 기록');
      $('#actPop li.memoWrap').removeClass('hide');

    } else if (act === 'change_option') {
      $('#actPop .header p').text('옵션 이동');
      $('#actPop li.optionWrap').removeClass('hide');

    } else if (act === 'receive') {
      $('#actPop .header p').text('입고 기록');
      $('#actPop li.memoWrap').removeClass('hide');

    } else {
      alert('매개변수 오류! 관리자 문의');
      return;
    }

    $('#actPop .hiddenValWrap input[name="act"]').val(act);
    $('#actPop .hiddenValWrap input[name="bc_id"]').val(bc_id);
    $('#actPop .body input.barcode').val(barcode);

    $('#actPopMask').show();
    $('#actPop').show();
  }

  function closeActPop() {
    // 초기화
    $('#actPop .header p').text('');
    $('#actPop input').val('');
    
    // 숨기기
    $('#actPopMask').hide();
    $('#actPop').hide();
  }

  function saveActPop() {
    var act = $('#actPop .hiddenValWrap input[name="act"]').val();
    var bc_id = $('#actPop .hiddenValWrap input[name="bc_id"]').val();
    var memo = $('#actPop .body input.memo').val();

    if (!confirm('저장하시겠습니까?')) {
      return;
    }

    if (LOADING) {
      return;
    }

    LOADING = true;

    $.ajax({
      url: '/adm/shop_admin/ajax.release_stock_barcode_view_update.php',
      type: 'POST',
      data: {
        it_id: '<?php echo $it_id ?>',
        io_id: '<?php echo $io_id ?>',
        act: act,
        bc_id: bc_id,
        memo: memo,
        change_io_id: act === 'change_option' ? $('#item_option').val() : '',
      },
      dataType: 'json',
      async: false,
    })
    .done(function(result) {
      alert(result.message);
      location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      LOADING = false;
    });
  }

  function sendInvoiceNum(text) {
    var prodPayCode = '<?php echo $prod_pay_code ?>'
    var barcodeProdCode = text.slice(0, 12);
    var barcode = text.slice(12, 24);

    if (text.length !== 24) {
      alert('유효하지 않은 바코드입니다. 다시 스캔해주세요. (12자리 아님)');
      return;
    }

    if (barcodeProdCode !== prodPayCode) {
      alert('상품을 잘못 스캔하셨습니다. 상품을 다시 확인해주세요.');
      return;
    }

    if (isNaN(barcode)) {
      alert('바코드에 숫자 이외의 문자가 포함되어있습니다. 다시 스캔해주세요.')
      return;
    }

    $('#search_text').val(barcode);
    renderData();
  }

  function search() {
    renderData();
  }

  function getData(a) {
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
		page:a,
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

  function renderData(a) {
    var data;
    var html;
	if(a != 1){
		$('.listContent').empty();
		page = 1;
	}else{
		page = page+1;
	}

    data = getData(page);
    console.log(data);

    if (data.length > 0) {
      var check_status;

      for (var i = 0; i < data.length; i++) {
        if (data[i].bc_status === '대여') {
          check_status = '대여 중 (' + data[i].rentaled_at +')';
          if (data[i].bc_memo) {
            check_status += '<br/>' + data[i].bc_memo;
          }
          
        } else if (data[i].bc_status  === '출고') {
          check_status = '출고 중 (' + data[i].released_at +')';
          if (data[i].bc_memo) {
            check_status += '<br/>' + data[i].bc_memo;
          }
          
        } else if (data[i].checked_at) {
          check_status = '확인 완료 (' + data[i].checked_at +')';
          
        } else {
          check_status = '미확인';
        }

        html = '<li class="item flex-row align-center" data-bc_id="' + data[i].bc_id + '">';
        html += '  <div class="barcode">' + data[i].bc_barcode + '</div>';
        html += '  <div class="check_status">' + check_status + '</div>';
        html += '  <div class="more">';
        html += '    <span>⋮</span>';
        html += '    <ul class="select">';
        if (data[i].bc_status === '대여') {
          html += '    <li class="receive">입고함</li>';
        } else {
          html += '    <li class="rental">대여함</li>';
        }
        html += '      <li class="release">출고함</li>';
        if (IO_ID) {
          html += '    <li class="change_option">옵션이동</li>';
        }
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

    location.href = './release_stock_barcode_check.php?it_id=' + it_id + '&io_id=' + io_id;
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

  function goBack() {
    location.href = '/shop/release_stocklist.php';
  }
</script>
</body>
