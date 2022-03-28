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
      padding: 13px 0;
      font-size: 14px;
    }

    #popupBody .listContent li:first-of-type {
      border-top: 1px solid #dfdfdf;
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
      <div style="width: 100%">
        <?php echo $full_it_name ?><br/>
        <span style="font-size: 13px">재고수량 : <?= $row['sum_ws_qty'] ?> / 바코드 : <?= $row['sum_barcode_qty'] ?><br/> 마지막 확인 일시 : <?= $last_checked_at ?></span>
      </div>
    </div>

    <div class="flex-row" style="margin-top: 20px;">
      <select name="sort_option" id="sortOption" style="width: 100%" onchange="sortList()">
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

<!-- 고정 하단 -->
<div id="popupFooterBtnWrap">
  <div class="flex-row justify-space-between align-center" style="margin-bottom: 20px">
    <p>확인중 (<span id="checkedBarcodeCnt">0</span>/<span id="allBarcodeCnt"><?= $row['sum_barcode_qty'] ?></span>)</p>
    <button onclick="saveData()">완료</button>
  </div>
  <div class="flex-row justify-space-between" style="padding: 0 30px">
    <button onclick="alert('TODO');">PDA</button>
    <button onclick="open_invoice_scan();">APP</button>
  </div>
</div>

<?php
if (!$member['mb_id']) {
  alert('접근이 불가합니다.');
}
?>
<script>
  var FIRST_RENDER = true;
  var ORIGIN_DATA = [];
  var DATA = [];
  var CHNAGED_DATA = [];
  var LOADING = false;

  // 바코드 스캔용 전역변수
  var sendBarcodeTargetList;
  var cur_ct_id = null;
  var cur_it_id = null;

  $(function() {
    renderData(true);

    $(document).on('click', '.listContent .more', function () {
      $(this).find('.select').toggle();
    });

    $(document).on('click', '.listContent .more .select li', function () {
      var act = $(this).attr('class');
      var liNode = $(this).closest('.item');
      var barcode = liNode.find('.barcode').text();
      var dataIndex = liNode.data('index');
      var changedObj;

      /*
      liNode.removeClass('checked');
      liNode.removeClass('deleted');
      liNode.removeClass('newAdd');
      liNode.removeClass('unchecked');
      liNode.find('.check_status').empty();

      if (act === 'check') {
        liNode.addClass('checked');
        liNode.find('.check_status').append('<img src="/img/barcode_icon_1.png"/>');
        // DATA[dataIndex]['checked_at'] = 'currentDate'
        // DATA[dataIndex]['changed'] = 'true'
      }

      if (act === 'delete') {
        liNode.addClass('deleted');
        liNode.find('.check_status').append('<span>삭제됨</span>');
        // DATA[dataIndex]['checked_at'] = null
        // DATA[dataIndex]['bc_del_yn'] = 'Y'
        // DATA[dataIndex]['changed'] = 'true'
      }
      */

      // if (ORIGIN_DATA[dataIndex]['bc_del_yn'] === 'Y') {
      //   alert('기존 삭제 상태의 바코드는 변경할 수 없습니다.');
      //   return
      // }

      if (act === 'check') {
        if (liNode.hasClass('checked')) {
          alert('이미 확인 상태입니다.');
          return;
        }

        // 원래 데이터 확인
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
          ORIGIN_DATA.splice(dataIndex, 1);

          var findAddedItem = CHNAGED_DATA.find(function (item) {
            return (item.bc_barcode === barcode && item.bc_id === '0')
          });
          var findAddedItemIndex = DATA.indexOf(findAddedItem);
          CHNAGED_DATA.splice(findAddedItemIndex, 1);

          renderData(false);
          return;

        } else {
          DATA[dataIndex]['checked_at'] = null
          DATA[dataIndex]['bc_del_yn'] = 'Y'
        }
      }

      // deep copy
      changedObj = JSON.parse(JSON.stringify(DATA[dataIndex]));

      // 중복 검색
      var findItem = CHNAGED_DATA.find(function (item) {
        return item.bc_id === changedObj.bc_id
      });

      var findItemIndex = CHNAGED_DATA.indexOf(findItem);

      if (findItemIndex !== -1) { // 중복이 있다면 삭제
        CHNAGED_DATA.splice(findItemIndex, 1);
      }

      CHNAGED_DATA.push(changedObj)


      renderData(false);
    });
  });

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

      if (FIRST_RENDER) {
        // deep copy
        ORIGIN_DATA = $.extend(true, [], DATA);
        FIRST_RENDER = false;
      }
    }

    console.log(DATA);

    if (DATA.length > 0) {
      var check_status = '';
      var status_class = ''; // checked, deleted, newAdd, unchecked

      for (var i = 0; i < DATA.length; i++) {
        allBarcodeCnt++;

        if (DATA[i].bc_del_yn === 'Y') {
          check_status = '<span>삭제됨</span>';
          status_class = 'deleted'
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
        if (ORIGIN_DATA[i].bc_del_yn !== 'Y') {
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
  }

  function sortList() {
    var sortBy = $('#sortOption').val();

    if (sortBy === 'unchecked') {
      DATA.sort(dynamicSortMultiple('bc_is_check_yn', 'bc_barcode'));
      ORIGIN_DATA.sort(dynamicSortMultiple('bc_is_check_yn', 'bc_barcode'));
    } else if (sortBy === 'newAdd') {
      DATA.sort(dynamicSortMultiple('bc_id', 'bc_barcode'));
      ORIGIN_DATA.sort(dynamicSortMultiple('bc_id', 'bc_barcode'));
    } else if (sortBy === 'deleted') {
      DATA.sort(dynamicSortMultiple('-bc_del_yn', 'bc_barcode'));
      ORIGIN_DATA.sort(dynamicSortMultiple('-bc_del_yn', 'bc_barcode'));
    } else if (sortBy === 'barcodeDesc') {
      DATA.sort(dynamicSort('-bc_barcode'));
      ORIGIN_DATA.sort(dynamicSort('-bc_barcode'));
    } else if (sortBy === 'barcodeAsc') {
      DATA.sort(dynamicSort('bc_barcode'));
      ORIGIN_DATA.sort(dynamicSort('bc_barcode'));
    }

    renderData(false);
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

    if (CHNAGED_DATA.length == 0) {
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
        data: CHNAGED_DATA,
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

    upsertBarcode(barcode);
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

      DATA.splice(findItemIndex, 0, newOjb);
      DATA.sort(dynamicSort('bc_barcode'));

      // ORIGIN_DATA 재생성
      ORIGIN_DATA = [];
      ORIGIN_DATA = $.extend(true, [], DATA);

      CHNAGED_DATA.push(newOjb);
    }

    renderData(false);
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

<?php include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>