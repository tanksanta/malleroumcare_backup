<?php
//$sub_menu = '400480';

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
  </style>
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
        <span style="font-size: 13px">재고수량 : <?= $row['sum_ws_qty'] ?> / 바코드 : <?= $row['sum_barcode_qty'] ?><br/> 마지막 확인 일시 : <?= $last_checked_at ?></span>
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
  </div>

  <div id="content">
    <ul class="listContent">
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
    <p>확인중 (<span id="checkedBarcodeCnt">0</span>/<span id="allBarcodeCnt"><?= $row['sum_barcode_qty'] ?></span>)</p>
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

  $(function() {
    renderData(true);

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

    // pda 스캔
    $(document).on('keydown', '#web-barcode-input', function (e) {
      var isScanner = e.key === 'Unidentified' || e.key === 'TVNetwork';

      if (e.key) {
        e.preventDefault();
      }

      if (!isScanner) {
        return;
      }

      receiveBarcode();
    });

    $(document).on('touchstart, click', '#web-barcode-close', function (e) {
      alert('바코드스캔을 종료합니다');
      closeWebBarcode();
    });
  });

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
          status_class = 'deleted'
          if (DATA[i].origin_del_yn === 'Y') {
            status_class = 'deleted originDeleted'
          }
          allBarcodeCnt--;

        } else if (DATA[i].bc_id === '0') {
          check_status = '<span>신규</span>';
          status_class = 'newAdd'
          checkedBarcodeCnt++;

        } else if (DATA[i].checked_at) {
          check_status = '<img src="/img/barcode_icon_1.png"/>';
          status_class = 'checked'
          checkedBarcodeCnt++;

        } else if (!DATA[i].checked_at) {
          check_status = '<span>미확인</span>';
          status_class = 'unchecked'
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
        $('.listContent').append(html)

        check_status = '';
        status_class = '';
      }

      $('#checkedBarcodeCnt').text(checkedBarcodeCnt);
      $('#allBarcodeCnt').text(allBarcodeCnt);
    } else {
      $('.listContent').append('<li>등록된 바코드가 없습니다.</li>')
    }

    $('#selectActWrap').hide();
  }

  function sortDataAndRender() {
    sortData();
    renderData(false);
  }

  function sortData() {
    var sortBy = $('#sortOption').val();

    if (sortBy === 'unchecked') {
      DATA.sort(dynamicSortMultiple('bc_is_check_yn', 'bc_barcode'));
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
      if (!isNaN(a[property]) && !isNaN(b[property])) {
        result = (Number(a[property]) < Number(b[property])) ? -1 : (Number(a[property]) > Number(b[property])) ? 1 : 0;
      } else {
        result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
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
      type: 'GET',
      data: {
        it_id: '<?php echo $it_id ?>',
        io_id: '<?php echo $io_id ?>',
        data: CHANGED_DATA,
      },
      dataType: 'json',
      async: false,
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

      if (isDuplicateBarcode(barcode)) {
        $.toast('\'' + barcode + '\'는 중복된 바코드입니다. <br/> 다시스캔해주세요.', {
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
    $('#web-barcode-input').focus();
    $('#web-barcode-input').click();
  }

  function openWebBarcode() {
    $('#web-barcode').css('display', 'flex');
    $('#web-barcode-input').focus();
    IS_OPEN_WEB_BARCODE = true;
    $('#web-barcode-input').val('');
    BARCODE_INPUT_FOCUS_INTERVAL = setInterval(barcodeInputFocus, 1000);
  }

  function closeWebBarcode() {
    IS_OPEN_WEB_BARCODE = false;
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

        if (isDuplicateBarcode(barcode)) {
          $.toast('\'' + barcode + '\'는 중복된 바코드입니다. <br/> 다시스캔해주세요.', {
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

    }, 100);
  }


  function goBack() {
    location.href = '/shop/release_stock_barcode_view.php?it_id=<?php echo $it_id ?>&io_id=<?php echo $io_id ?>';
  }
</script>
</body>