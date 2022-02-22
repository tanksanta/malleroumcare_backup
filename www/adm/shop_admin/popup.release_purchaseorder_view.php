<?php
$sub_menu = '400480';

include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g5["title"] = "주문 내역 바코드 수정";
// include_once(G5_ADMIN_PATH."/admin.head.php");

$sql = " select * from purchase_order where od_id = ( SELECT od_id FROM purchase_cart WHERE ct_id = '$ct_id') ";
$od = sql_fetch($sql);
$od_id = $od['od_id'];
$prodList = [];
$prodListCnt = 0;
$prodListCnt2 = 0;
$deliveryTotalCnt = 0;

if (!$od['od_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$sql = "
  select * from purchase_cart where od_id = {$od_id} and ct_id = {$ct_id}
";
$ct = sql_fetch($sql);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>출고정보</title>
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

    /* 상품기본정보 */
    #itInfoWrap {
      width: 100%;
      padding: 20px;
      border-bottom: 1px solid #DFDFDF;
    }

    #itInfoWrap > .name {
      width: 100%;
      font-weight: bold;
      font-size: 17px;
    }

    #itInfoWrap > .name > .delivery {
      color: #FF690F;
    }

    #itInfoWrap > .date,
    #itInfoWrap > .info {
      width: 100%;
      font-size: 13px;
      color: #666;
    }

    #itInfoWrap > .qtyInfo {
      width: 100%;
      border-radius: 5px;
      padding: 10px 15px;
      background-color: #F1F1F1;
      margin-top: 20px;
    }

    #itInfoWrap > .qtyInfo > p {
      width: 100%;
      color: #000;
      font-size: 15px;
      font-weight: bold;
      text-align: center;
    }

    #itInfoWrap .deliveredQty {
      margin-top: 20px;
      font-size: 17px;
      font-weight: bold;
      color: #000;
    }

    #itInfoWrap .deliveredQty .qty button {
      width: 31px;
      font-size: 20px;
      font-weight: bold;
      border: 1px solid #000;
      background: #000;
      color: #fff;
    }

    #itInfoWrap .deliveredQty .qty input {
      width: 50px;
      height: 31px;
      font-size: 20px;
      border-radius: 0;
      border: 1px solid #000;
      text-align: center;
    }

    #itInfoWrap .deliveredQty .qty input[type="number"]::-webkit-outer-spin-button,
    #itInfoWrap .deliveredQty .qty input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    #itInfoWrap .deliveredQty > button {
      border: 1px solid #000;
      padding: 4px 10px;
      font-size: 15px;
      background: #fff;
    }

    #itInfoWrap .barcodeMemo {
      margin-top: 20px;
    }

    #itInfoWrap .barcodeMemo p {
      font-size: 17px;
      font-weight: bold;
      color: #000;
    }

    #itInfoWrap .barcodeMemo input {
      border: 1px solid #DFDFDF;
      margin-top: 5px;
      padding: 8px;
    }

    #itInfoWrap .purchaseOrderEndBtn {
      margin-top: 20px;
    }

    #itInfoWrap .purchaseOrderEndBtn p {
      font-size: 12px;
      width: 75%;
    }

    #itInfoWrap .purchaseOrderEndBtn button {
      border: 1px solid #000;
      padding: 4px 10px;
      font-size: 15px;
      background: #fff;
      white-space: nowrap;
    }

    #itInfoWrap .purchaseOrderEndBtn button.red {
      background: #fff;
      border-color: #f00;
      color: #f00;
    }

    #itInfoWrap .purchaseOrderEndBtn button.grey {
      background: #F2F2F2;
      border-color: #BFBFBF;
      color: #BFBFBF;
    }

    #log {
      padding: 20px;
    }

    #log .title {
      font-size: 17px;
      font-weight: bold;
      color: #000;
      margin-bottom: 10px;
    }

    #log li {
      font-size: 13px;
      margin-bottom: 8px;
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

    /* 상품목록 */
    #submitForm {
      width: 100%;
    }

    .imfomation_box {
      margin: 0px;
      width: 100%;
      position: relative;
      padding: 0px;
      display: block;
      width: 100%;
      height: auto;
    }

    .imfomation_box > a {
      width: 100%;
    }

    .imfomation_box > a > li {
      width: 100%;
      padding: 20px;
      border-bottom: 1px solid #DDD;
    }

    .imfomation_box a .li_box {
      width: 100%;
      height: auto;
      text-align: center;
    }

    .imfomation_box a .li_box .li_box_line1 {
      width: 100%;
      height: auto;
      margin: auto;
      color: #000;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 {
      width: 100%;
      color: #000;
      text-align: left;
      box-sizing: border-box;
      display: table;
      table-layout: fixed;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 > span {
      height: 100%;
      display: table-cell;
      vertical-align: middle;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 .span1 {
      font-size: 18px;
      word-break: keep-all;
      width: 60%;
    }

    /* .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; } */
    .imfomation_box a .li_box .li_box_line1 .p1 .span2 {
      width: 120px;
      font-size: 14px;
      text-align: right;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 .span2 img {
      width: 13px;
      margin-left: 15px;
      vertical-align: middle;
      top: -1px;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 .span2 .up {
      display: none;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 .span3 {
      text-align: right;
      font-size: 0.8em;
      color: #9b9b9b;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 .span3 .outline {
      border: 1px solid #9b9b9b;
      border-radius: 3px;
      padding: 5px 30px;
      display: inline-block;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 .span3 label {
      color: #000;
    }

    .imfomation_box a .li_box .li_box_line1 .p1 .span3 input {
      vertical-align: middle;
    }

    .imfomation_box a .li_box .li_box_line1 .cartProdMemo {
      width: 100%;
      font-size: 13px;
      margin-top: 2px;
      text-align: left;
      color: #FF690F;
    }

    /* display:none; */
    .imfomation_box a .li_box .folding_box {
      text-align: center;
      vertical-align: middle;
      width: 100%;
      padding-top: 20px;
      display: none;
      box-sizing: border-box;
    }

    .imfomation_box a .li_box .folding_box > span {
      display: block;
      width: 100%;
    }

    .imfomation_box a .li_box .folding_box > span:after {
      display: block;
      content: '';
      clear: both;
    }

    .imfomation_box a .li_box .folding_box > .inputbox {
      width: 100%;
      position: relative;
      padding: 0;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li {
      width: 100%;
      position: relative;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input {
      width: 100%;
      height: 50px;
      padding-right: 85px;
      box-sizing: border-box;
      padding-left: 20px;
      font-size: 17px;
      border: 1px solid #E4E4E4;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input.active {
      border-color: #FF5858;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input::placeholder {
      font-size: 16px;
      color: #AAA;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > .btn_bacod {
      position: absolute;
      width: 30px;
      right: 50px;
      top: 11px;
      z-index: 2;
      cursor: pointer;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > .btn_pda {
      position: absolute;
      width: 30px;
      right: 15px;
      top: 11px;
      z-index: 2;
      cursor: pointer;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > img {
      position: absolute;
      width: 30px;
      right: 15px;
      top: 11px;
      z-index: 2;
      cursor: pointer;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > i {
      position: absolute;
      right: 100px;
      top: 17px;
      z-index: 2;
      font-size: 19px;
      color: #FF6105;
      opacity: 0;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > i.active {
      opacity: 1;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > .overlap {
      position: absolute;
      right: 55px;
      top: 15px;
      z-index: 2;
      font-size: 14px;
      color: #DC3333;
      opacity: 0;
      font-weight: bold;
    }

    .imfomation_box a .li_box .folding_box > .inputbox > li > .overlap.active {
      opacity: 1;
    }

    .imfomation_box a .li_box .folding_box .span {
      margin-left: 20px;
      width: 90%;
    }

    .imfomation_box a .li_box .folding_box .all {
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

    .imfomation_box a .li_box .folding_box .all::placeholder {
      color: #fff;
    }

    .imfomation_box a .li_box .folding_box .all::placeholder {
      color: #fff;
    }

    .imfomation_box a .li_box .folding_box .all::placeholder {
      color: #fff;
    }

    .imfomation_box a .li_box .folding_box .barNumCustomSubmitBtn {
      float: left;
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

    .imfomation_box a .li_box .folding_box .barNumGuideOpenBtn {
      float: left;
      position: relative;
      margin-left: 10px;
      width: 35px;
      cursor: pointer;
      top: 8px;
    }

    .imfomation_box a .li_box .folding_box .notall {
      margin-bottom: 5px;
      font-size: 20px;
      text-align: left;
      height: 50px;
      width: 90%;
      border-radius: 6px;
      background-color: #fff;
      color: #666666;
      border: 0px;;
      border: 1px solid #c0c0c0;;
      /* background-image : url('
    <?php echo G5_IMG_URL?> /bacod_img.png');  */
      /* background-position:top right;  */
      /* background-repeat:no-repeat; */


    }

    .imfomation_box a .li_box .deliveryInfoWrap {
      width: 100%;
      position: relative;
      background-color: #F1F1F1;
      border-radius: 5px;
      padding: 10px;
      margin-top: 15px;
    }

    .imfomation_box a .li_box .deliveryInfoWrap:after {
      display: block;
      content: '';
      clear: both;
    }

    .imfomation_box a .li_box .deliveryInfoWrap > select {
      width: 34%;
      height: 40px;
      float: left;
      margin-right: 1%;
      border: 1px solid #DDD;
      font-size: 17px;
      color: #666;
      padding-left: 10px;
      border-radius: 5px;
    }

    .imfomation_box a .li_box .deliveryInfoWrap > input[type="text"] {
      width: 65%;
      height: 40px;
      float: left;
      border: 1px solid #DDD;
      font-size: 17px;
      color: #666;
      padding: 0 40px 0 10px;
      border-radius: 5px;
    }

    .imfomation_box a .li_box .deliveryInfoWrap > img {
      position: absolute;
      width: 30px;
      right: 15px;
      top: 50%;
      margin-top: -15px;
      z-index: 2;
      cursor: pointer;
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

    #popupFooterBtnWrap > .savebtn {
      float: left;
      width: 75%;
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

    /* 바코드 순차입력 버튼 */
    .imfomation_box a .li_box .folding_box > .inputbox > li > .barcode_add {
      width: 35px;
      height: 35px;
      position: absolute;
      top: 8px;
      right: 130px;
      display: none;
    }

    .excel_btn {
      display: inline-block;
      margin-top: 10px;
      color: #fff;
      font-size: 17px;
      background-color: #494949;
      border: 0px;
      border-radius: 6px;
      width: 18%;
      height: 50px;
      font-weight: bold;
      text-align: center;
      line-height: 50px;
    }
  </style>

  <?php if ($isPop) { ?>
  <style>
    #popupHeaderTopWrap {
      background-color: #fff;
    }

    #popupHeaderTopWrap .title {
      color: #000;
      width: 100%;
      border-bottom: 1px solid #000;
    }

    #popupHeaderTopWrap .close {
      display: none;
    }
  </style>
  <?php } ?>
</head>

<body>

<!-- 고정 상단 -->
<div id="popupHeaderTopWrap">
  <div class="title">입고관리</div>
  <div class="close">
    <a href="javascript:member_cancel();">
      &times;
    </a>
  </div>
</div>

<!-- 상품기본정보 -->
<div id="itInfoWrap">
  <p class="name">
    <?php
    $ct_it_name = $ct['it_name'];                                                                             // 상품이름
    $ct_option = ($ct["ct_option"] == $ct['it_name']) ? "" : "(" . $ct['ct_option'] . ")";         // 옵션
    $ct_it_name = $ct_it_name . $ct_option;
    ?>
    <?= $ct_it_name ?>
  </p>

  <p class="info">
    발주 수량 : <?= $ct["ct_qty"] ?>개 / 배송지 : <?= $ct["ct_warehouse"] ?> / 공급업체 : <?= $od["od_name"] ?>
  </p>

  <p class="date">
    <?= date("y-m-d(H:i)", strtotime($od["od_time"])) ?>
  </p>

  <div class="qtyInfo">
    <p>
      입고 예정 수량 : <?= $ct["ct_qty"] - $ct["ct_delivered_qty"] ?>개
    </p>
  </div>

  <div class="deliveredQty flex-row align-center">
    <span>입고수량</span> :
    <div class="flex-row align-center qty" style="margin-left: 5px">
      <button onclick="setNumberDeliveredQty('minus')">-</button><input type="number" id="delivered_qty" name="" value="0"><button onclick="setNumberDeliveredQty('plus')">+</button>
    </div>
    <button onclick="setNumberDeliveredQty('<?= $ct["ct_qty"] - $ct["ct_delivered_qty"] ?>')" style="margin-left: 10px">전체입고</button>
  </div>

  <div class="barcodeMemo">
    <p>바코드 메모</p>
    <input class="full-width" type="text" name="barcode_memo" placeholder="내용을 입력하세요." value="<?= $ct["ct_barcode_memo"] ?>">
  </div>

  <?php if (check_auth($member['mb_id'], '400480', 'w')) { ?>
  <div class="purchaseOrderEndBtn flex-row align-center justify-space-between">
    <p>입고예정 수량 미 입고 상태에서 발주 종료 시<br/>전체 발주 수량에서 차감된 후 발주가 종료 됩니다.</p>
    <?php
    if (!$ct['is_purchase_end']) {
      echo '<button class="purchaseEndBtn red" onclick="setPurchaseEnd(\'doEnd\')">발주 종료</button>';
    } else {
      echo '<button class="purchaseEndBtn grey" onclick="setPurchaseEnd(\'cancelEnd\')">종료 완료</button>';
    }
    ?>
  </div>
  <?php } ?>
</div>


<?php
//발주 기록
$sql = "SELECT * FROM purchase_order_admin_log WHERE od_id = '{$od_id}' AND ct_id = '{$ct_id}' ORDER BY ol_no DESC";
$result = sql_query($sql);
$logs = array();
while($row = sql_fetch_array($result)) {
  $logs[] = $row;
}
?>
<div id="log">
  <p class="title">기록</p>
  <ul>
    <?php
    foreach($logs as $log) {
      $log_mb = get_member($log['mb_id']);
      if ($log_mb['mb_id'] == $member['mb_id']) {
        $manager = $member['mb_name'];
      } else if ($log_mb['mb_type'] != 'manager') {
        $manager = '이로움 관리자';
      } else {
        $manager = $member['mb_name'] . '>[직원]' . $log_mb['mb_name'];
      }
      echo '<li class="log"><div class="row">
              <div class="log_datetime">' . $log['ol_datetime'] . '</div>
              <div>(' . $manager . ') ' . $log['ol_content'] . '</div>
            </div></li>';
    }
    if (!count($logs)) {
      echo '기록이 없습니다.';
    }
    ?>
  </ul>
</div>

<!-- 고정 하단 -->
<div id="popupFooterBtnWrap">
  <button type="button" class="savebtn" onclick="saveData();">저장</button>
  <button type="button" class="cancelbtn" onclick="member_cancel();">취소</button>
</div>

<?php

if (!$member['mb_id']) {
  alert('접근이 불가합니다.');
}
//접속시 db- >id 부과
sql_query("update purchase_cart set `ct_edit_member` = '" . $member['mb_id'] . "' where `od_id` = '{$od_id}'");
?>

<script>
  var CT_QTY = Number('<?= $ct["ct_qty"] ?>');
  var IS_POP = <?=$isPop ? 'true' : 'false'?>;

  function getUrlParams() {
    var params = {};

    window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,
      function(str, key, value) {
        params[key] = decodeURI(value);
      }
    );

    return params;
  }

  //종료시 멤버 수정중없에기
  function member_cancel(){
    $.ajax({
      url : "/shop/ajax.purchase_member_cancel.php",
      type : "POST",
      async : false,
      data : {
        od_id : "<?=$od_id?>"
      }
    });

    var params = getUrlParams();
    delete params.od_id;
    delete params.ct_id;
    var query_string = decodeURI($.param(params));

    if (IS_POP) {
      window.close();
    } else {
      location.href = "<?=G5_SHOP_URL?>/release_purchaseorderlist.php?" + query_string;
    }
  }

  function setNumberDeliveredQty(param) {
    var targetNode = $('#delivered_qty');
    var currentVal = Number(targetNode.val());
    var maxVal = Number('<?= $ct["ct_qty"] - $ct["ct_delivered_qty"] ?>');
    var minVal = Number('<?= $ct["ct_delivered_qty"] ?>')

    if (param === 'plus') {
      if (currentVal < maxVal)
        targetNode.val(++currentVal);
    } else if (param === 'minus') {
      if (currentVal > -minVal)
        targetNode.val(--currentVal);
    } else { // 숫자 입력
      targetNode.val(param);
    }
  }

  function setPurchaseEnd(command) {
    var text;

    if (command === 'doEnd') {
      text = '발주 종료 시 입고예정 수량이 발주수량에서 차감됩니다.\n종료하시겠습니까?';
    } else {
      text = '발주 종료내역을 취소하시겠습니까?';
    }

    if (!confirm(text)) {
      return;
    }

    $.ajax({
      url: '/shop/ajax.set_purchase_end.php',
      type: 'POST',
      async: false,
      data: {
        od_id: '<?=$od_id?>',
        ct_id: '<?=$ct_id?>',
        is_purchase_end : command === 'doEnd' ? '1' : '0',
      },
      dataType: 'json',
    })
    .done(function() {
      location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  }

  function saveData() {
    var delivered_qty = Number($('#delivered_qty').val());
    var barcode_memo = $('input[name="barcode_memo"]').val();

    <?php if ($ct['is_purchase_end']) { ?>
    alert('발주 종료 상태입니다.');
    return;
    <?php } ?>

    if (delivered_qty > CT_QTY || delivered_qty === 0) {
      alert('입고수량은 0이 아니어야 하며, 발주 수량 이하 값이어야 합니다.');
      return;
    }

    if (!confirm('저장하시겠습니까?')) {
      return;
    }

    $.ajax({
      url: '/shop/ajax.update_purchase_cart.php',
      type: 'POST',
      async: false,
      data: {
        od_id: '<?=$od_id?>',
        ct_id: '<?=$ct_id?>',
        delivered_qty: delivered_qty,
        barcode_memo: barcode_memo,
      },
      dataType: 'json',
    })
    .done(function() {
      location.reload();
      if (IS_POP) {
        opener.location.reload();
      }
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  }
</script>
</body>