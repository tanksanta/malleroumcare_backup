<?php
$sub_menu = '400480';

include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g5["title"] = "주문 내역 바코드 수정";
// include_once(G5_ADMIN_PATH."/admin.head.php");

$od = sql_fetch(" SELECT * FROM `purchase_order` WHERE `od_id` = ( SELECT od_id FROM `purchase_cart` WHERE `ct_id` = '" . $ct_id . "') ");
$od_id = $od['od_id'];
$prodList = [];
$prodListCnt = 0;
$prodListCnt2 = 0;
$deliveryTotalCnt = 0;

if (!$od['od_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$ct = sql_fetch(" SELECT * FROM `purchase_cart` WHERE `od_id` = '" . $od_id . "' AND `ct_id` = '" . $ct_id . "' " );

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
      padding-top: 20px;
      border-top: 1px solid #dfdfdf;
    }

    #itInfoWrap .purchaseOrderEndBtn p {
      font-size: 12px;
      width: 75%;
    }

    #itInfoWrap .purchaseOrderEndBtn button {
      border: 1px solid #000;
      padding: 4px 10px;
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

    {
      width: 100%;
      height: auto;
      text-align: center;
    }

    .li_box_line1 {
      width: 100%;
      height: auto;
      margin: auto;
      color: #000;
    }

    .li_box_line1 .p1 {
      width: 100%;
      color: #000;
      text-align: left;
      box-sizing: border-box;
      display: table;
      table-layout: fixed;
    }

    .li_box_line1 .p1 > span {
      height: 100%;
      display: table-cell;
      vertical-align: middle;
    }

    .li_box_line1 .p1 .span1 {
      font-size: 18px;
      word-break: keep-all;
      width: 60%;
    }

    /* .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; } */
    .li_box_line1 .p1 .span2 {
      width: 120px;
      font-size: 14px;
      text-align: right;
    }

    .li_box_line1 .p1 .span2 img {
      width: 13px;
      margin-left: 15px;
      vertical-align: middle;
      top: -1px;
    }

    .li_box_line1 .p1 .span2 .up {
      display: none;
    }

    .li_box_line1 .p1 .span3 {
      text-align: right;
      font-size: 0.8em;
      color: #9b9b9b;
    }

    .li_box_line1 .p1 .span3 .outline {
      border: 1px solid #9b9b9b;
      border-radius: 3px;
      padding: 5px 30px;
      display: inline-block;
    }

    .li_box_line1 .p1 .span3 label {
      color: #000;
    }

    .li_box_line1 .p1 .span3 input {
      vertical-align: middle;
    }

    .li_box_line1 .cartProdMemo {
      width: 100%;
      font-size: 13px;
      margin-top: 2px;
      text-align: left;
      color: #FF690F;
    }

    /* display:none; */
    .folding_box {
      text-align: center;
      vertical-align: middle;
      width: 100%;
      padding-top: 20px;
      /*display: none;*/
      box-sizing: border-box;
    }

    .folding_box > span {
      display: block;
      width: 100%;
    }

    .folding_box > span:after {
      display: block;
      content: '';
      clear: both;
    }

    .folding_box > .inputbox {
      width: 100%;
      position: relative;
      padding: 0;
    }

    .folding_box > .inputbox > li {
      width: 100%;
      position: relative;
    }

    .folding_box > .inputbox > li > .frm_input {
      width: 100%;
      height: 50px;
      padding-right: 85px;
      box-sizing: border-box;
      padding-left: 20px;
      font-size: 17px;
      border: 1px solid #E4E4E4;
    }

    .folding_box > .inputbox > li > .frm_input.active {
      border-color: #FF5858;
    }

    .folding_box > .inputbox > li > .frm_input.select {
      background: #E4E4E4;
    }

    .folding_box > .inputbox > li > .frm_input::placeholder {
      font-size: 16px;
      color: #AAA;
    }

    .folding_box > .inputbox > li > .btn_bacod {
      position: absolute;
      width: 30px;
      right: 50px;
      top: 11px;
      z-index: 2;
      cursor: pointer;
    }

    .folding_box > .inputbox > li > .btn_pda {
      position: absolute;
      width: 30px;
      right: 15px;
      top: 11px;
      z-index: 2;
      cursor: pointer;
    }

    .folding_box > .inputbox > li > .btn_pda {
      position: absolute;
      width: 30px;
      right: 5px;
      top: 11px;
      z-index: 2;
      cursor: pointer;
    }

    .folding_box > .inputbox > li > .btn_bacod {
      position: absolute;
      width: 30px;
      right: 37px;
      top: 11px;
      z-index: 2;
      cursor: pointer;
    }

    .folding_box > .inputbox > li > .barcode_icon {
      position: absolute;
      right: 79px;
      top: 17px;
      z-index: 2;
      opacity: 0;
    }

    .folding_box > .inputbox > li > .barcode_icon.active {
      opacity: 1;
    }

    .complete.folding_box > .inputbox > li > .barcode_icon {
      position: absolute;
      right: 95px;
      top: 17px;
      z-index: 2;
      opacity: 0;
    }

    .complete.folding_box > .inputbox > li > .barcode_icon.active {
      opacity: 1;
    }

    .folding_box > .inputbox > li > .overlap {
      position: absolute;
      right: 75px;
      top: 15px;
      z-index: 2;
      font-size: 14px;
      color: #DC3333;
      opacity: 0;
      font-weight: bold;
    }

    .folding_box > .inputbox > li > .overlap.active {
      opacity: 1;
    }

    .folding_box .span {
      margin-left: 20px;
      width: 90%;
    }

    .folding_box .all {
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

    .folding_box .all::placeholder {
      color: #fff;
    }

    .folding_box .all::placeholder {
      color: #fff;
    }

    .folding_box .all::placeholder {
      color: #fff;
    }

    .folding_box .barNumCustomSubmitBtn {
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

    .folding_box .barNumGuideOpenBtn {
      float: left;
      position: relative;
      margin-left: 10px;
      width: 35px;
      cursor: pointer;
      top: 8px;
    }

    .folding_box .notall {
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
    }

    .deliveryInfoWrap {
      width: 100%;
      position: relative;
      background-color: #F1F1F1;
      border-radius: 5px;
      padding: 10px;
      margin-top: 15px;
    }

    .deliveryInfoWrap:after {
      display: block;
      content: '';
      clear: both;
    }

    .deliveryInfoWrap > select {
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

    .deliveryInfoWrap > input[type="text"] {
      width: 65%;
      height: 40px;
      float: left;
      border: 1px solid #DDD;
      font-size: 17px;
      color: #666;
      padding: 0 40px 0 10px;
      border-radius: 5px;
    }

    .deliveryInfoWrap > img {
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
    .folding_box > .inputbox > li > .barcode_add {
      width: 35px;
      height: 35px;
      position: absolute;
      top: 8px;
      right: 105px;
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

    .input_btn {
      min-width: 77px;
      border: 1px solid red;
      background: #fff;
      color: red;
      padding: 5px 0;
    }

    .input_btn.type1 {
      margin-left: 15px;
      padding: 5px;
    }

    .input_btn.type2 {
      position: absolute;
      top: 10px;
      right: 9px;
    }

    .barcodeList {
      border-top: 1px solid #dfdfdf;
      margin-top: 20px;
      padding-top: 20px;
    }

    .barcodeList .top-area {
      margin-bottom: 20px;
      min-height: 30px;
    }

    .barcodeList .top-area .arrow-wrap {
      min-width: 60px;
      text-align: right;
    }

    .barcodeList .top-area .arrow-wrap img {
      width: 13px;
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

    #barcodeHistory {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1000;
    }

    #barcodeHistory .mask {
      background: rgba(0, 0, 0, 0.7);
      width: 100%;
      height: 100%;
      position: absolute;
    }

    #barcodeHistory .historyContent {
      position: absolute;
      width: 100%;
      height: 40%;
      bottom: 0;
      left: 0;
      background: #fff;
      padding: 50px 20px 10px;
    }

    #barcodeHistory .historyContent .header {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      padding: 10px 20px;
      border-bottom: 1px solid #d9d9d9;
    }

    #barcodeHistory .historyContent .barcode {
      font-size: 18px;
      font-weight: bold;
    }

    #barcodeHistory .historyContent .close {
      font-size: 37px;
      width: 33px;
      height: 33px;
      line-height: 33px;
      background: none;
      position: relative;
      top: -3px;
    }

    #barcodeHistory .historyContent .content {
      margin-top: 5px;
      height: 100%;
      overflow-y: scroll;
    }

    #barcodeHistory .historyContent li {
      border-bottom: 1px solid #d9d9d9;
      padding: 10px 0;
    }

    #barcodeHistory .historyContent li .subtitle {
      font-size: 13px;
    }

    #barcodeHistory .historyContent li .title {
      font-size: 17px;
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

<!--  <div class="deliveredQty flex-row align-center">
    <span>입고수량</span> :
    <div class="flex-row align-center qty" style="margin-left: 5px">
      <button onclick="setNumberDeliveredQty('minus')">-</button><input type="number" id="delivered_qty" name="" value="0"><button onclick="setNumberDeliveredQty('plus')">+</button>
    </div>
    <button onclick="setNumberDeliveredQty('<?/*= $ct["ct_qty"] - $ct["ct_delivered_qty"] */?>')" style="margin-left: 10px">전체입고</button>
  </div>-->

<!--  <div class="barcodeMemo">
    <p>바코드 메모</p>
    <input class="full-width" type="text" name="barcode_memo" placeholder="내용을 입력하세요." value="<?/*= $ct["ct_barcode_memo"] */?>">
  </div>-->

  <!-- 바코드 영역 (등록 완료) -->
  <?php
  $sql = "select * from g5_cart_barcode where bc_del_yn = 'N' and pct_id = '{$ct['ct_id']}' order by bc_id asc ";
  $result = sql_query($sql);
  $complete_count = sql_num_rows($result);
  ?>
  <div class="barcodeList complete folding_box" data-id="<?php echo $ct['ct_id']; ?>">
    <div class="top-area flex-row justify-space-between full-width">
      <div class="flex-row align-center">
        <p>등록완료 (<?php echo $complete_count ?>)</p>
        <button id="cancelSelectBarcodeBtn" class="input_btn type1" onclick="cancelBarcodeBulk(event)" style="display: none">선택 바코드 등록 취소</button>
      </div>
      <div class="flex-row align-center">
        <div class="arrow-wrap">
          <img class="up" src="/img/img_up.png" style="display: none">
          <img class="down" src="/img/img_down.png">
        </div>
      </div>
    </div>
    <ul class="inputbox barcode-area" style="display: none">
      <?php
      if ($complete_count > 0) {
        while ($row = sql_fetch_array($result)) {
          $img_active = ($row['bc_status'] == '출고' || $row['bc_status'] == '관리자삭제') ? 'active' : '';
        ?>
          <li>
            <input type="text" data-id="<?php echo $row['bc_id'] ?>" value="<?php echo $row['bc_barcode'] ?>" class="notall frm_input" maxlength="12" readonly>
            <img class="barcode_icon type2 <?php echo $img_active?>" src="/img/barcode_icon_2.png" alt="등록가능 (이미 출고, 관리자 삭제)">
            <button class="input_btn type2" onclick="cancelBarcode(this)">등록 취소</button>
          </li>
          <?php
        }
      } else {
      ?>
      <li>
        등록된 바코드가 없습니다.
      </li>
      <?php
      }
      ?>
    </ul>
  </div>

  <!-- 바코드 영역 (미등록) -->
  <?php
  $incomplete_count = $ct['ct_qty'] - $ct['ct_delivered_qty'];
  ?>
  <div class="barcodeList incomplete folding_box" data-id="<?php echo $ct['ct_id']; ?>">
    <div class="top-area flex-row justify-space-between full-width">
      <div class="flex-row align-center">
        <p>미등록 (<?php echo $incomplete_count ?>)</p>
      </div>
      <div class="flex-row align-center">
        <div class="arrow-wrap">
          <img class="up" src="/img/img_up.png" style="display: <?php echo $incomplete_count > 0 ? 'inline' : 'none' ?>">
          <img class="down" src="/img/img_down.png" style="display: <?php echo $incomplete_count < 0 ? 'inline' : 'none' ?>">
        </div>
      </div>
    </div>
    <?php if ($incomplete_count >= 2) { ?>
      <span class="barcode-area" style="display: none">
        <input type="text" class="all frm_input" placeholder="일괄 등록수식 입력">
        <button type="button" class="barNumCustomSubmitBtn" onclick="inputRegexBarcode(this)">등록</button>
        <img src="/img/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)">
        </span>
    <?php } ?>
    <ul class="inputbox barcode-area" style="display: <?php echo $incomplete_count > 0 ? 'block' : 'none' ?>">
      <?php
      $prodListCnt = 0;
      for ($i = 0; $i < $incomplete_count; $i++) {
        ?>
        <li>
          <input type="text" maxlength="12" oninput="maxLengthCheck(this)" value="" class="notall frm_input frm_input_<?=$prodListCnt?> required" placeholder="바코드를 입력하세요." data-frm-no="<?=$prodListCnt?>" maxlength="12">
          <img src="/img/bacod_add_img.png" class="barcode_add">
          <img class="barcode_icon type1" src="/img/barcode_icon_1.png" alt="정상">
          <img class="barcode_icon type2" src="/img/barcode_icon_2.png" alt="등록가능 (이미 출고)">
          <img class="barcode_icon type3" src="/img/barcode_icon_2.png" alt="등록가능 (관리자 삭제)">
          <img class="barcode_icon type4" src="/img/barcode_icon_3.png" alt="등록불가 (보유재고)">
          <span class="overlap">중복</span>
          <!--
          <img src="/img/bacod_img.png" class="nativePopupOpenBtn btn_bacod" onclick="openNativeBarcodeScan(this)" data-type="native" data-code="<?=$i?>" data-ct-id="<?php echo $ct['ct_id']; ?>" data-it-id="<?php echo $ct['it_id']; ?>">
          -->
          <img src="/img/btn_pda.png" class="nativePopupOpenBtn btn_pda" onclick="openNativeBarcodeScan(this)" data-type="pda" data-code="<?=$i?>" data-ct-id="<?php echo $ct['ct_id']; ?>" data-it-id="<?php echo $ct['it_id']; ?>" data-pd-code="<?php echo $ct['prodpaycode']; ?>">
        </li>
        <?php
        $prodListCnt++;
      }
      ?>
    </ul>
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
  <?php
  $save_text = '저장';
  if ($complete_count > 0) {
    $save_text .= " (입고수량 : {$complete_count})";
  }
  ?>
  <button type="button" class="savebtn" onclick="submit();"><?php echo $save_text ?></button>
  <button type="button" class="cancelbtn" onclick="member_cancel();">취소</button>
</div>

<!-- 팝업 -->
<div id="popup" class="hide">
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

<div id="loading">
  <div>
    <img src="img/ajax-loading.gif" class="img-responsive">
    <p>잠시만 기다려주세요...</p>
  </div>
</div>

<div id="barcodeHistory">
  <div class="mask"></div>
  <div class="historyContent">
    <div class="header flex-row justify-space-between align-center">
      <div class="barcode">barcode</div>
      <button class="close" onclick="closeBarcodeHistory()">×</button>
    </div>
    <div class="content">
      <ul>
        <li>
          <p class="subtitle">2022-01-01 13:11 홍길동 담당자</p>
          <p class="title">상품 출고 (NO 222222)</p>
        </li>
        <li>
          <p class="subtitle">2022-01-01 13:11 홍길동 담당자</p>
          <p class="title">상품 출고 (NO 222222)</p>
        </li>
        <li>
          <p class="subtitle">2022-01-01 13:11 홍길동 담당자</p>
          <p class="title">상품 출고 (NO 222222)</p>
        </li>
      </ul>
    </div>
  </div>
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
  var KEYUP_TIMER;

  // 바코드 스캔용 전역변수
  var sendBarcodeTargetList;
  var cur_ct_id = null;
  var cur_it_id = null;
  var cur_pdcode = null;

  $(function() {
    window.addEventListener('beforeunload', function(event) {
      member_cancel();
    });

    // 바코드 입력 인풋
    $(".notall").keyup(function () {
      var last_index = $(this).closest('ul').find('li').last().index();
      var this_index = $(this).closest('li').index();

      $(this).closest('ul').find('.barcode_add').hide();
      if (last_index !== this_index && $(this).val().length == 12)
        $(this).closest('li').find('.barcode_add').show();

      if (KEYUP_TIMER) clearTimeout(KEYUP_TIMER);
      KEYUP_TIMER = setTimeout(function() {
        notallLengthCheck(false);
      }, 200);
    });

    $('.notall').focus(function () {

      var last_index = $(this).closest('ul').find('li').last().index();
      var this_index = $(this).closest('li').index();

      $(this).closest('ul').find('.barcode_add').hide();
      if (last_index !== this_index && $(this).val().length == 12)
        $(this).closest('li').find('.barcode_add').show();
    });

    $('.barcode_add').click(function () {
      var ul = $(this).closest('ul');
      var li_num = $(this).closest('li').index();
      var li_val = $(this).closest('li').find('.notall').val();
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
      notallLengthCheck(false);
    });

    // 접기 버튼
    $('.barcodeList .top-area').on('click', function() {
      var type = $(this).find('.arrow-wrap img:visible').attr('class'); // up, down

      // 아이콘 처리
      $(this).find('.arrow-wrap').find('img').hide();
      if (type === 'up') {
        $(this).find('.arrow-wrap').find('.down').show();
      } else {
        $(this).find('.arrow-wrap').find('.up').show();
      }

      // 바코드 박스 처리
      if (type === 'down') {
        $(this).closest('.barcodeList').find('.barcode-area').show();
      } else {
        $(this).closest('.barcodeList').find('.barcode-area').hide();
      }
    })

    // 바코드 선택 삭제
    $('.folding_box.complete .frm_input').on('click', function() {
      var allBarcodes = $('.folding_box.complete .frm_input');
      var start = $('.folding_box.complete .frm_input.start').length;
      var end = $('.folding_box.complete .frm_input.end').length;
      var startNode = $('.folding_box.complete .frm_input.start');
      var endNode = $('.folding_box.complete .frm_input.end');
      var thisNode = $(this);
      var startIndex = start ? allBarcodes.index(startNode) : -1;
      var endIndex = end ? allBarcodes.index(endNode) : -1;
      var thisIndex = allBarcodes.index($(this));

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
          $(this).addClass('end');
        }

        if (thisIndex === startIndex) {
          $(this).removeClass('start');
        }
      }

      // 아무것도 없는 경우
      if (!start && !end) {
        $(this).addClass('start');
      }

      addSelectClassBarcode();
    });

    $('.barcode_icon.type2, .barcode_icon.type3').on('click', function() {
      var barcode = $(this).closest('li').find('.frm_input').val();

      showBarcodeHistory(barcode);
    });
  })

  function addSelectClassBarcode() {
    var allBarcodes = $('.folding_box.complete .frm_input');
    var start = $('.folding_box.complete .frm_input.start').length;
    var end = $('.folding_box.complete .frm_input.end').length;
    var startNode = $('.folding_box.complete .frm_input.start');
    var endNode = $('.folding_box.complete .frm_input.end');
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

    if ($('.folding_box.complete .frm_input.select').length > 0) {
      $('#cancelSelectBarcodeBtn').show();
    } else {
      $('#cancelSelectBarcodeBtn').hide();
    }
  }

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

  function submit() {
    if (!confirm('저장하시겠습니까?')) {
      return;
    }

    showLoading(true);

    setTimeout(function() {
      saveData();
    }, 200)
  }

  function saveData() {
    var delivered_qty = Number($('#delivered_qty').val());
    var barcode_memo = $('input[name="barcode_memo"]').val();
    var errorMsg = '';

    <?php if ($ct['is_purchase_end']) { ?>
    alert('발주 종료 상태입니다.');
    showLoading(false);
    return;
    <?php } ?>

    if (delivered_qty > CT_QTY || delivered_qty === 0) {
      alert('입고수량은 0이 아니어야 하며, 발주 수량 이하 값이어야 합니다.');
      return;
    }

    // 바코드 길이 검증
    $('.barcodeList.incomplete .inputbox .frm_input').each(function() {
      if ($(this).val().length > 0 && $(this).val().length != 12) {
        errorMsg = '12자리가 아닌 바코드가 존재합니다. 확인해주세요.';
        return false;
      }
    });

    if (errorMsg.length > 0) {
      alert(errorMsg);
      showLoading(false);
      return;
    }

    // 바코드 상태 검증
    notallLengthCheck(true);

    // 바코드 상태 검증 결과 체크
    $('.barcodeList.incomplete .inputbox li').each(function() {

      if ($(this).find('.barcode_icon.type4').hasClass('active')) {
        errorMsg = '등록 불가능한 바코드가 존재합니다. 확인해주세요.';
        return false;
      }

      if ($(this).find('.overlap').hasClass('active')) {
        errorMsg = '중복으로 입력된 바코드가 존재합니다. 확인해주세요.';
        return false;
      }
    });

    if (errorMsg.length > 0) {
      alert(errorMsg);
      showLoading(false);
      return;
    }

    // 바코드 파싱
    var barcodeArr = [];
    var barcode = '';
    var barcodeStatus = '';
    $('.barcodeList.incomplete .inputbox li').each(function() {
      barcode = '';
      barcodeStatus = '';

      barcode = $(this).find('.notall.frm_input').val();

      if (barcode.length == 0) {
        return false; // continue;
      }

      if ($(this).find('.barcode_icon.type1').hasClass('active')) {
        barcodeStatus = '정상'
      }
      if ($(this).find('.barcode_icon.type2').hasClass('active')) {
        barcodeStatus = '출고'
      }
      if ($(this).find('.barcode_icon.type3').hasClass('active')) {
        barcodeStatus = '관리자삭제'
      }
      if ($(this).find('.barcode_icon.type4').hasClass('active')) {
        barcodeStatus = '보유재고'
      }

      barcodeArr.push({
        barcode: barcode,
        barcodeStatus: barcodeStatus,
      })
    });

    $.ajax({
      url: '/shop/ajax.update_purchase_cart.php',
      type: 'POST',
      async: true,
      data: {
        od_id: '<?=$od_id?>',
        ct_id: '<?=$ct_id?>',
        barcodeArr: barcodeArr,
        // delivered_qty: delivered_qty,
        // barcode_memo: barcode_memo,
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
      // alert(data && data.message);
    })
    .always(function() {
      showLoading(false);
    });
  }

  function maxLengthCheck(object){
    if (object.value.length > object.maxLength){
      object.value = object.value.slice(0, object.maxLength);
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

  function inputRegexBarcode(x) {
    var val = $(x).closest(".folding_box").find(".all").val();
    var target = $(x).closest(".folding_box").find(".notall");
    var barList = [];

    if (val.indexOf("^") == -1) {
      alert("내용을 입력해주시길 바랍니다.");
      return false;
    }

    for (var i = 0; i < target.length; i++) {
      if (i > 0) {
        if ($(target[i]).find("input").val()) {
          if (!confirm("이미 등록된 바코드가 있습니다.\n무시하고 적용하시겠습니까?")) {
            return false;
          } else {
            break;
          }
        }
      }
    }
    if (val) {
      val = val.split("^");
      var first = val[0];
      var secList = val[1].split(",");
      for (var i = 0; i < secList.length; i++) {
        if (secList[i].indexOf("-") == -1) {
          barList.push(first + secList[i]);
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

            barList.push(first + barData);
          }
        }
      }

      for (var i = 0; i < target.length; i++) {
        $(target[i]).val(barList[i]);
        if (barList[i] && barList[i].length !== 12) {
          alert('바코드는 12자리 입력이 되어야합니다.');
          target[i].focus();
          return false;
        }
      }
    }

    notallLengthCheck(false);
  }

  function notallLengthCheck(barcodeAjaxCheck) {
    var $foldingBox = $('.folding_box.incomplete');

    $(".folding_box.incomplete > .inputbox > li > .barcode_icon").removeClass("active");
    $(".folding_box.incomplete > .inputbox > li > .overlap").removeClass("active");

    $foldingBox.each(function () {
      var $item = $(this).find('.notall');
      $item.removeClass("active");

      var dataTable = {};
      $item.each(function (i) {
        var $cur = $(this);
        var barcode = $cur.val();
        var length = barcode.length;
        if (length < 12 && length) {
          $cur.addClass("active");
        }
        if (length === 12) {
          if (barcodeAjaxCheck) {
            var checkBarcodeResult = validateBarcode(barcode);

            if (checkBarcodeResult === 'OK') {
              $cur.parent().find(".barcode_icon.type1").addClass("active");
            }
            if (checkBarcodeResult === '출고') {
              $cur.parent().find(".barcode_icon.type2").addClass("active");
            }
            if (checkBarcodeResult === '관리자삭제') {
              $cur.parent().find(".barcode_icon.type3").addClass("active");
            }
            if (checkBarcodeResult === '보유재고') {
              $cur.parent().find(".barcode_icon.type4").addClass("active");
            }
          }

          if (!dataTable[barcode])
            dataTable[barcode] = [];
          dataTable[barcode].push(i);
        }
      });

      // 중복 검사
      var keys = Object.keys(dataTable);
      for (var i = 0; i < keys.length; i++) {
        var val = dataTable[keys[i]];
        if (val.length > 1) {
          for (var j = 0; j < val.length; j++) {
            var idx = val[j];
            $($item[idx]).parent().find(".barcode_icon").removeClass("active");
            $($item[idx]).parent().find(".overlap").addClass("active");
          }
        }
      }
    });
  }

  function validateBarcode(barcode) {
    if (barcode.length !== 12) {
      return 'error';
    }

    var msgResult = null;

    $.ajax({
      url: './ajax.barcode_validate.php',
      type: 'POST',
      data: {
        barcode: barcode,
      },
      dataType: 'json',
      async: false,
    })
    .done(function(result) {
      var msg = result.message;
      console.log(msg);
      msgResult = msg;
    })
    .fail(function($xhr) {
      msgResult = 'error'
    })

    return msgResult;
  }

  function showLoading(flag) {
    if (flag) {
      $('body').css('overflow-y', 'hidden')
      $('#loading').show();
    } else {
      $('body').css('overflow-y', 'scroll')
      $('#loading').hide();
    }

    console.log(`loading : ${flag}`)
  }

  function sleep(ms) {
    const wakeUpTime = Date.now() + ms;
    while (Date.now() < wakeUpTime) {}
  }

  function openNativeBarcodeScan(_this) {
    var cnt = 0;
    var frm_no = $(_this).closest("li").find(".frm_input").attr("data-frm-no");
    var item = $(_this).closest("ul").find(".frm_input");
    sendBarcodeTargetList = [];

    cur_ct_id = $(_this).data('ct-id');
    cur_it_id = $(_this).data('it-id');
    cur_pdcode = $(this).data('pd-code');

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

  function cancelBarcodeBulk(e) {
    e.stopPropagation();

    if (!confirm('선택한 바코드를 모두 등록 취소하시겠습니까?')) {
      return;
    }

    var cancelBarcodeList = [];
    $('.folding_box.complete .frm_input.select').each(function() {
      cancelBarcodeList.push($(this).data('id'));
    })

    $.ajax({
      url: './ajax.barcode_cancel.php',
      type: 'POST',
      async: false,
      data: {
        pod_id: '<?=$od_id?>',
        pct_id: '<?=$ct_id?>',
        bc_id: cancelBarcodeList,
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

  function cancelBarcode(_this) {
    if (!confirm('해당 바코드를 등록 취소하시겠습니까?')) {
      return;
    }

    $.ajax({
      url: './ajax.barcode_cancel.php',
      type: 'POST',
      async: false,
      data: {
        pod_id: '<?=$od_id?>',
        pct_id: '<?=$ct_id?>',
        bc_id: $(_this).closest('li').find('.frm_input').data('id'),
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

  function showBarcodeHistory(barcode) {
    var data = null;

    $('#barcodeHistory .header .barcode').empty();
    $('#barcodeHistory .content ul').empty();

    $.ajax({
      url: './ajax.barcode_history.php',
      type: 'POST',
      async: false,
      data: {
        barcode: barcode,
        ct_id: '<?=$ct_id?>',
      },
      dataType: 'json',
    })
    .done(function(result) {
      data = result.data;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });

    $('#barcodeHistory').show();
    $('#barcodeHistory .header .barcode').text(barcode);

    if (data.length > 0) {
      var subtitle = '';
      var title = '';
      var html = '';

      $('body').css('overflow', 'hidden');

      data.forEach(function (obj) {
        subtitle = obj.created_at + ' ' + obj.mb_name + ' 담당자';
        title = obj.bch_content;

        html += '<li>'
        html += '<p class="subtitle">' + subtitle + '</p>'
        html += '<p class="title">' + title + '</p>'
        html += '</li>'
      });

    } else {
      html = '<li>내역이 없습니다.</li>';
    }

    $('#barcodeHistory .content ul').append(html);
  }

  function closeBarcodeHistory() {
    $('body').css('overflow', 'auto');
    $('#barcodeHistory').hide();
  }
</script>

<?php include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>