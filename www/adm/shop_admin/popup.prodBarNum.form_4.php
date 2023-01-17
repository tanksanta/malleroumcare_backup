<?php

include_once("./_common.php");
$g5["title"] = "주문 내역 바코드 수정";
// include_once(G5_ADMIN_PATH."/admin.head.php");
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
$sql = " select * from {$g5['g5_shop_cart_table']} where `ct_id` = '$ct_id' ";
$ct = sql_fetch($sql);
$prodList = [];
$prodListCnt = 0;
$deliveryTotalCnt = 0;
  $prodSupYn_count=0;
if (!$ct['ct_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
} else {
  $sto_imsi=$ct['stoId'];
  $stoIdDataList = explode('|',$sto_imsi);
  $stoIdDataList=array_filter($stoIdDataList);
  $stoIdData = implode("|", $stoIdDataList);
  $sendData["stoId"] = $stoIdData;
  $res = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
  $result_again =$res['data'];
}
$carts = get_carts_by_od_id($od_id);

function arr_sort( $array, $key, $sort ){
  $keys = array();
  $vals = array();
  foreach( $array as $k=>$v ){
    $i = $v[$key].'.'.$k;
    $vals[$i] = $v;
    array_push($keys, $k);
  }
  unset($array);

  if( $sort=='asc' ){
    ksort($vals);
  }else{
    krsort($vals);
  }
  
  $ret = array_combine( $keys, $vals );

  unset($keys);
  unset($vals);
  
  return $ret;
}

if ($result_again) {
  $result_again = arr_sort($result_again, 'prodBarNum', 'asc');
}

# 210317 추가정보
$moreInfo = sql_fetch("
  SELECT
    ( SELECT it_name FROM g5_shop_cart WHERE od_id = a.od_id ORDER BY it_id ASC LIMIT 0, 1 ) AS it_name,
    ( SELECT COUNT(*) FROM g5_shop_cart WHERE od_id = a.od_id ) AS totalCnt
  FROM g5_shop_order a
  WHERE od_id = '{$od_id}'
");

$moreInfoDisplayCnt = "";
$moreInfo["totalCnt"]--;
if($moreInfo["totalCnt"]){
  $moreInfoDisplayCnt = "외 {$moreInfo["totalCnt"]}종";
}

# 210319 배송정보
$odDeliveryNameTel = "";
$odDeliveryNameTel .= $od["od_b_name"];
if($od["od_b_tel"]){
  $odDeliveryNameTel .= " / {$od["od_b_tel"]}";
}

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

  <style>
    * { margin: 0; padding: 0; position: relative; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; }
    html, body { width: 100%; float: left; font-family: "Noto Sans KR", sans-serif; }
    body { padding-top: 60px; padding-bottom: 70px; }
    a { text-decoration: none; color: inherit; }
    ul, li { list-style: none; }
    button { border: 0; font-family: "Noto Sans KR", sans-serif; }
    input { font-family: "Noto Sans KR", sans-serif;  }

    /* 고정 상단 */
    #popupHeaderTopWrap { position: fixed; width: 100%; height: 60px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
    #popupHeaderTopWrap > div { height: 100%; line-height: 60px; }
    #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
    #popupHeaderTopWrap > .close { float: right; }
    #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

    /* 상품기본정보 */
    #itInfoWrap { width: 100%; float: left; padding: 20px; border-bottom: 1px solid #DFDFDF; }
    #itInfoWrap > .name { width: 100%; float: left; font-weight: bold; font-size: 17px; }
    #itInfoWrap > .name > .delivery { color: #FF690F; }
    #itInfoWrap > .date { width: 100%; float: left; font-size: 13px; color: #666; }
    #itInfoWrap > .deliveryInfo { width: 100%; float: left; border-radius: 5px; padding: 10px 15px; background-color: #F1F1F1; margin-top: 20px; }
    #itInfoWrap > .deliveryInfo > p { width: 100%; float: left; color: #000; font-size: 13px; }
    #itInfoWrap > .deliveryInfo > p.title { color: #666; font-size: 15px; font-weight: bold; margin-bottom: 10px; }

    /* 팝업 */
    #popup { display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, .7);z-index: 50; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);}
    #popup.hide {display: none;}
    #popup.multiple-filter { backdrop-filter: blur(4px) grayscale(90%); -webkit-backdrop-filter: blur(4px) grayscale(90%);}
    #popup .content { padding: 20px; background: #fff; border-radius: 5px; box-shadow: 1px 1px 3px rgba(0, 0, 0, .3); max-width:90%;}
    #popup .content { max-width:90%; font-size: 14px; }
    #popup .closepop { width: 100%; height: 40px; cursor: pointer; color:#fff; background-color:#000; border-radius:6px; margin-top: 10px; }

    /* 상품목록 */
    #submitForm { width: 100%; float: left; }
    .imfomation_box{ margin:0px;width:100%;position:relative; padding:0px;display:block; width:100%; height:auto; float: left; }
    .imfomation_box > a { width: 100%; float: left; }
    .imfomation_box > a > li { width: 100%; float: left; padding: 20px; border-bottom: 1px solid #DDD; }
    .imfomation_box a .li_box{ width:100%;  height:auto;text-align:center;}
    .imfomation_box a .li_box .li_box_line1{ width: 100%;  height:auto; margin:auto; float:left;color:#000; }
    .imfomation_box a .li_box .li_box_line1 .p1{ width:100%; float:left; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
    .imfomation_box a .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
    .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; }
    .imfomation_box a .li_box .li_box_line1 .p1 .span2{ width: 120px; font-size:14px; text-align: right; }
    .imfomation_box a .li_box .li_box_line1 .p1 .span2 img{ width: 13px; margin-left: 15px; vertical-align: middle; top: -1px; }
    .imfomation_box a .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
    .imfomation_box a .li_box .li_box_line1 .cartProdMemo { width: 100%; float: left; font-size: 13px; margin-top: 2px; text-align: left; color: #FF690F; }
    /* display:none; */
    .imfomation_box a .li_box .folding_box{text-align: center; vertical-align:middle;width:100%; padding-top: 20px; display:none; float: left; box-sizing: border-box; }
    .imfomation_box a .li_box .folding_box > span { width: 100%; float: left; }
    .imfomation_box a .li_box .folding_box > .inputbox { width: 100%; float: left; position: relative; padding: 0; }
    .imfomation_box a .li_box .folding_box > .inputbox > li { width: 100%; float: left; position: relative; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input { width: 100%; height: 50px; float: left; padding-right: 85px; box-sizing: border-box; padding-left: 20px; font-size: 17px; border: 1px solid #E4E4E4; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input.active { border-color: #FF5858; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input::placeholder { font-size: 16px; color: #AAA; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > img { position: absolute; width: 30px; right: 15px; top: 11px; z-index: 2; cursor: pointer; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > i { position: absolute; right: 55px; top: 17px; z-index: 2; font-size: 19px; color: #FF6105; opacity: 0; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > i.active { opacity: 1; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .overlap { position: absolute; right: 55px; top: 15px; z-index: 2; font-size: 14px; color: #DC3333; opacity: 0; font-weight: bold; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .overlap.active { opacity: 1; }

    .imfomation_box a .li_box .folding_box .span{margin-left :20px;width:90%;}
    .imfomation_box a .li_box .folding_box .all{margin-bottom:5px;padding-left :20px;font-size:17px;text-align:left;float:left;height:50px;width:55%; border-radius: 6px; background-color:#c0c0c0;  color:#fff; border:0px; box-sizing: border-box; }
    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}
    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box a .li_box .folding_box .barNumCustomSubmitBtn{float:left;margin-left:10px;color:#fff;font-size:17px;background-color:#494949; border:0px;border-radius: 6px;width:18%; height:50px; font-weight: bold; }
    .imfomation_box a .li_box .folding_box .barNumGuideOpenBtn{float:left;margin-left:10px;width:35px; cursor: pointer; top: 8px; }
    .imfomation_box a .li_box .folding_box .notall{
      margin-bottom:5px;font-size:20px;text-align:left;float:left;height:50px;width:90%; border-radius: 6px; background-color:#fff;  color:#666666; border:0px; ; border: 1px solid #c0c0c0;;
      /* background-image : url('<?php echo G5_IMG_URL?>/bacod_img.png');  */
      /* background-position:top right;  */
      /* background-repeat:no-repeat; */


    }
    .imfomation_box a .li_box .deliveryInfoWrap { width: 100%; float: left; background-color: #F1F1F1; border-radius: 5px; padding: 10px; margin-top: 15px; }
    .imfomation_box a .li_box .deliveryInfoWrap > select { width: 34%; height: 40px; float: left; margin-right: 1%; border: 1px solid #DDD; font-size: 17px; color: #666; padding-left: 10px; border-radius: 5px; }
    .imfomation_box a .li_box .deliveryInfoWrap > input[type="text"] { width: 65%; height: 40px; float: left; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 40px 0 10px; border-radius: 5px; }
    .imfomation_box a .li_box .deliveryInfoWrap > img { position: absolute; width: 30px; right: 15px; top: 50%; margin-top: -15px; z-index: 2; cursor: pointer; }

    /* 고정 하단 */
    #popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }

    .excel_btn{
        float: left;
        margin-top: 10px;
        color: #fff;
        font-size: 13px;
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
 </head>

 <body>

   <!-- 고정 상단 -->
  <div id="popupHeaderTopWrap">
    <div class="title">바코드</div>
    <div class="close">
      <a href="#" class="popupCloseBtn">
        &times;
      </a>
    </div>
  </div>

  <!-- 상품기본정보 -->
  <div id="itInfoWrap">
    <p class="name">
      [<?=($od["recipient_yn"] == "Y") ? "주문" : "재고"?>] <?=$ct["it_name"]?>
    </p>

    <p class="date">
      <?=date("y-m-d(H:i)", strtotime($od["od_time"]))?>
      <?=($od["od_b_name"]) ? " / {$od["od_name"]}" : ""?>
    </p>

    <div class="deliveryInfo">
      <p class="title">배송정보</p>
      <p>
        <span><?=$odDeliveryNameTel?></span>
        <?=($odDeliveryNameTel) ? "<br>" : ""?>
        <span><?=$od["od_b_addr1"]?> <?=$od["od_b_addr2"]?></span>
      </p>
    </div>
    <a href="./popup.prodBarNum.form.excel2.php?ct_id=<?=$ct_id?>" class="excel_btn">엑셀다운로드</a>
  </div>

   <!-- 상품목록 -->
  <form id="submitForm">
    <input type="hidden" name="od_id" value="<?=$od_id?>">
    <input type="hidden" name="update_type" value="popup">
    <ul class="imfomation_box" id="imfomation_box">
      <?php
      # 요청사항
      $prodMemo = "";
      //보유재고 (무조건 입력) / 유통(미입력) 비유통(입력)
      $readonly = "";
      if($_GET['stock_insert']=="1"){
          if($ct['prodSupYn']=="N"){
              $barcode_placeholder ="바코드를 입력하세요.";
          }else{
              $barcode_placeholder ="바코드가 입력되지 않았습니다.";
              $readonly="readonly";
          }
      }else{
          $barcode_placeholder ="바코드를 입력하세요.";
          $prodSupYn_count++;
      }
      if($member['mb_id']=="admin"){
      $readonly = "";
      $prodSupYn_count++;
      }


      if($ct['prodSupYn']==="N"){ $prodSupYn_count++; }
      # 요청사항
      $prodMemo = $ct["prodMemo"];
      ?>
      <a href="javascript:void(0)">
        <li class="li_box">
          <div class="li_box_line1"   onclick="openCloseToc(this)">
            <p class="p1">
              <span class="span1">
                <!-- 상품명 -->
                <?php if($ct['ct_stock_qty']){ echo '[재고소진]'; } ?>
                <?=stripslashes($ct["it_name"])?>
                <!-- 옵션 -->
                <?php if($ct["it_name"] != $ct["ct_option"]){ ?>
                (<?=$ct["ct_option"]?>)
                <?php } ?>
              </span>
              <span class="span2">
                <span class="<?=$add_class?> c_num">0</span>/<?=$ct["ct_qty"]?>
                <img class="up" src="<?=G5_IMG_URL?>/img_up.png" alt="">
                <img class="down" src="<?=G5_IMG_URL?>/img_down.png" alt="">
              </span>
            </p>

            <?php if($prodMemo){ ?>
              <p class="cartProdMemo"><?=$prodMemo?></p>
            <?php } ?>
          </div>
          <div class="folding_box">
          <?php /* 23.01.17 : 서원 - 화면상 동작하지 않는 기능 UI제거
            if($ct["ct_qty"] >= 2){ ?>
            <span>
            <input type="text" class="all frm_input" placeholder="일괄 등록수식 입력">
            <button type="button" class="barNumCustomSubmitBtn">등록</button>
            <img src="<?php echo G5_IMG_URL?>/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)">
            </span>
            <?php } */
            ?>
            <ul class="inputbox">
              <?php 
              if($ct["prodSupYn"] == "N"||$od['od_stock_insert_yn']=="Y"){
                $readonly="";
                $readonly_text="바코드를 입력하세요.";
              } else {
                $readonly="readonly";
                $readonly_text="바코드가 입력되지 않았습니다.";
              }
              ?>
              <?php for($b = 0; $b< count($result_again); $b++) { ?>
              <li>
                <input type="text" maxlength="12" oninput="maxLengthCheck(this)" value="<?=$result_again[$b]["prodBarNum"]?>"class="notall frm_input frm_input_<?=$prodListCnt?> required prodBarNumItem_<?=$result_again[$b]["stoId"]?> <?=$result_again[$b]["stoId"]?>" <?=$readonly?> placeholder="<?=$readonly_text?>"  data-frm-no="<?=$prodListCnt?>" maxlength="12">
                <i class="fa fa-check"></i>
                <span class="overlap">중복</span>
                <!-- <img src="<?php echo G5_IMG_URL?>/bacod_img.png" class="nativePopupOpenBtn" data-code="<?=$b?>" data-ct-id="<?php echo $ct['ct_id']; ?>" data-it-id="<?php echo $ct['it_id']; ?>"> -->
              </li>
              <?php $prodListCnt++; } ?>
            </ul>
          </div>
        </li>
      </a>
    </ul>
  </form>

  <!-- 팝업 -->
  <div id="popup" class="hide">
    <div class="content">
    <p>
      바코드 일괄등록<br><br>
      1. 공동된 숫자 이후 꺽쇠(^)를 입력하세요<br>
      2. 하이픈(-)을 이용해 연속한 숫자를 입력할 수 있습니다.<br>
      3. 콤마(,)를 이용해 연속하지 않은 숫자를 입력할 수 있습니다.<br><br>

      예시1) 2012000^1-3 입력시 <br>
      20120001, 20120002, 20120003이 일괄등록 됩니다.<br><br>

      예시2) 2012000^1,3,5 입력시<br>
      20120001, 20120003, 20120005가 일괄등록 됩니다.<br><br>

      <!-- 공통된 문자/숫자를 앞에 부여 후 반복되는 숫자를 입력합니다.<br><br>
      예시) 010101^3,4,5-10- 010101은 공동문자/숫자입니다.<br><br>
      - ^이후는 자동으로 입력하기 위한 내용입니다.<br>
      -    “숫자 입력 후 콤마(,)”를 입력하면 독립 숫자가 입력됩니다.<br>
      - 5-10이라고 입력하면5부터10까지 순차적으로 입력됩니다.<br>
      - 00-20으로 시작 숫자가00인 경우2자리 숫자로 입력됩니다 -->
    </p>
    <button class="closepop" onclick="closePopup()">닫기</button>
    </div>
  </div>
  <!-- 고정 하단 -->
  <?php if($ct["prodSupYn"] == "N"||$od['od_stock_insert_yn']=="Y") { ?>
  <div id="popupFooterBtnWrap">
    <button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button type="button" class="cancelbtn popupCloseBtn">취소</button>
  </div>
  <?php } ?>
  <?php

  if(!$member['mb_id']) { alert('접근이 불가합니다.'); }
  //접속시 db- >id 부과
  sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '".$member['mb_id']."' where `od_id` = '{$od_id}'");

  ?>

  <script type="text/javascript">
  //maxnum 지정
  function maxLengthCheck(object){
    if (object.value.length > object.maxLength) {
      object.value = object.value.slice(0, object.maxLength);
    }
  }

  /* 바코드 입력란 설정 */
  function foldingBoxSetting(){
    var item = $(".folding_box");
    for(var i = 0; i < item.length; i++){
      var openStatus = true;
      var d_count=0;
      var notalls = $(item[i]).find(".notall");
      for(var n = 0; n < notalls.length; n++){
        if(!$(notalls[n]).val() || $(notalls[n]).val().length<12){
          d_count++;
          openStatus = false;
        }
      }
      //숫자채우기
      $(item[i]).parent().find(".p1 .span2 .c_num").html(notalls.length-d_count);
      if(!openStatus){
        $(item[i]).show();
        $(item[i]).parent().find(".p1 .span2 .up").css("display", "inline-block");
        $(item[i]).parent().find(".p1 .span2 .down").css("display", "none");
      }
    }
  }

  /* 바코드 입력글자 수 체크 */
  function notallLengthCheck(){
    var item = $(".notall");

    $(item).removeClass("active");
    $(".imfomation_box a .li_box .folding_box > .inputbox > li > i").removeClass("active");
    $(".imfomation_box a .li_box .folding_box > .inputbox > li > .overlap").removeClass("active");

    for(var i = 0; i < item.length; i++){
      var length = $(item[i]).val().length;
      if(length < 12 && length){
        $(item[i]).addClass("active");
      }
      if(length == 12){
        $(item[i]).parent().find("i").addClass("active");
        var index = $(item[i]).parent("li").index();
        var prodItem = $(item[i]).closest(".inputbox").find("li");
        for(var ii = 0; ii < prodItem.length; ii++){
          if($(prodItem[ii]).index() != index){
            if($(prodItem[ii]).find(".notall").val() == $(item[i]).val()){
              $(item[i]).parent().find("i").removeClass("active");
              $(item[i]).parent().find(".overlap").addClass("active");
            }
          }
        }
      }
    }
  }

  var cur_ct_id = null;
  var cur_it_id = null;

  /* 기종체크 */
  var deviceUserAgent = navigator.userAgent.toLowerCase();
  var device;

  if(deviceUserAgent.indexOf("android") > -1){
    /* android */
    device = "android";
  }

  if(deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1){
    /* ios */
    device = "ios";
  }

  var sendBarcodeTargetList = [];
  function sendBarcode(text) {
    $.ajax({
      url : "/shop/ajax.release_orderview.check.php",
      type : "POST",
      data : {
        od_id : "<?=$od_id?>"
      },
      success : function(result){
        if(result.error == "Y"){
          switch(device){
            case "android" :
              /* android */
              window.EroummallApp.closeBarcode("");
              break;
            case "ios" :
              /* ios */
              window.webkit.messageHandlers.closeBarcode.postMessage("");
              break;
          }
          window.location.href = "/shop/release_orderlist.php";
        } else {
          if(sendBarcodeTargetList[0]) {
            $.post('/shop/ajax.check_barcode.php', {
              it_id: cur_it_id,
              barcode: text,
            }, 'json')
            .done(function(data) {
              var sendBarcodeTarget = $(".frm_input_" + sendBarcodeTargetList[0]);
              $(sendBarcodeTarget).val(data.data.converted_barcode);
              sendBarcodeTargetList = sendBarcodeTargetList.slice(1);
            })
            .fail(function($xhr) {
              switch(device){
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
              setTimeout(function() {
                alert(data && data.message);
              }, 500);
            });
          }
        }

        notallLengthCheck();
      }
    });
  }

  var sendInvoiceTarget;
  function sendInvoiceNum(text){
    $(sendInvoiceTarget).val(text);
  }

  $(function() {
    notallLengthCheck();

    $(".nativeDeliveryPopupOpenBtn").click(function() {
      sendInvoiceTarget = $(this).parent().find("input[type='text']");

      switch(device){
        case "android" :
          /* android */
          window.EroummallApp.openInvoiceNum("");
          break;
        case "ios" :
          /* ios */
          window.webkit.messageHandlers.openInvoiceNum.postMessage("1");
          break;
      }
    });

    var $notall = $(".notall").keyup(function() {
      $(this).val($(this).val().replace(/[^0-9]/g,""));
      if($(this).val().length == 12){
        var idx = $notall.index(this); // <- 변경된 코드
        var num = idx+1;

        var item = $(".notall");
        if(num < item.length){
          $notall[num].focus();
        }
      }
      notallLengthCheck();
    });

		<?php
		$stock_list = [];
		foreach($result_again as $stock) {
			$stock_list[] = array(
				'prodId' => $stock['prodId'],
				'stoId' => $stock['stoId'],
				'prodBarNum' => $stock['prodBarNum']
			);
		}
		?>
    var stoldList = <?=json_encode($stock_list)?>;
		$.each(stoldList, function() {
			$('.' + this.stoId).val(this.prodBarNum)
		});

    var count=0;
    var stoIdData = "<?=$stoIdData?>";
    foldingBoxSetting();

    $("#prodBarNumSaveBtn").click(function() {
      var barcode_arr = [];
      var isDuplicated = false;

      $('.imfomation_box .li_box').each(function(){
          var temp_arr = [];
          $(this).find('.inputbox li input').each(function(){
              if ($(this).val() != "") {
                  temp_arr.push($(this).val())
              }
          });
          barcode_arr.push(temp_arr);
      });

      barcode_arr.forEach(function(arr) {
          if (isDuplicate(arr)) {
              isDuplicated = true;
          }
      });

      if (isDuplicated) {
          alert("입력하신 바코드 중 중복 값이 있습니다.");
          return false;
      }
      
      var ordId = "<?=$od["ordId"]?>";
      var changeStatus = true;
      var insertBarCnt = 0;
      var prodsList = {};
      var flag=false;
      $.each(stoldList, function(key, value){
        if($("." + value.stoId).val()&&$("." + value.stoId).val().length !=12){ flag =true; }
        var prodBarNum = ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "";
        prodBarNum = (prodBarNum) ?  prodBarNum : $(".2" + value.stoId).val();
        prodsList[key] = {
          stoId : value.stoId,
          prodId : value.prodId,
          prodColor : value.prodColor,
          prodSize : value.prodSize,
          prodBarNum : prodBarNum,
          prodManuDate : value.prodManuDate,
          stateCd : value.stateCd,
          stoMemo : (value.stoMemo) ? value.stoMemo : ""
        }
        if(flag){ alert('바코드는 12자리를 입력해주세요.'); return false; }
        if($("." + value.stoId).val()){
          insertBarCnt++;
        }
      });
      if(flag){ return false;}
      var sendData = {
        usrId : "<?=$member["mb_id"]?>",
        prods : prodsList,
        entId : "<?=get_ent_id_by_od_id($od_id)?>"
      }

      $.ajax({
        url : "./samhwa_orderform_stock_update.php",
        type : "POST",
        async : false,
        data : sendData,
        success : function(result) {
          result = JSON.parse(result);
          if(result.errorYN == "Y"){
            alert(result.message);
          } else {
            //cart 기준 barcode insert update
            $.ajax({
              url : "<?=G5_SHOP_URL?>/ajax.ct_barcode_insert.php",
              type : "POST",
              async : false,
              data : {
                od_id : "<?=$od_id?>",
              }
            });
            var sendData_barcode = {
              mb_id : "<?=$member["mb_id"]?>",
              od_id : "<?=$_GET["od_id"]?>",
              prods : prodsList
            }
            $.ajax({
              url : "./ajax.barcode_log.php",
              type : "POST",
              async : false,
              data : sendData_barcode,
              success : function(result){
                alert('완료되었습니다.');
                close();
              }
            });
          }
        }
      });
    });


    //넘버 검사
    $(".barNumCustomSubmitBtn").click(function() {
      var val = $(this).closest(".folding_box").find(".all").val();
      var target = $(this).closest(".folding_box").find(".notall");
      var barList = [];

      if(val.indexOf("^") == -1){
        alert("내용을 입력해주시길 바랍니다.");
        return false;
      }

      for(var i = 0; i < target.length; i++){
        if(i > 0){
          if($(target[i]).find("input").val()){
            if(!confirm("이미 등록된 바코드가 있습니다.\n무시하고 적용하시겠습니까?")){
              return false;
            } else {
              break;
            }
          }
        }
      }
            
      if(val){
        val = val.split("^");
        var first = val[0];
        var secList = val[1].split(",");
        for(var i = 0; i < secList.length; i++){
          if(secList[i].indexOf("-") == -1){
            barList.push(first + secList[i]);
          } else {
            var secData = secList[i].split("-");
            var secData0Len = secData[0].length;
            secData[0] = Number(secData[0]);
            secData[1] = Number(secData[1]);

            for(var ii = secData[0]; ii < (secData[1] + 1); ii++) {
              var barData = ii;
              if(String(barData).length < secData0Len){
                var iiiCnt = secData0Len - String(barData).length;
                for(var iii = 0; iii < iiiCnt; iii++){
                  barData = "0" + barData;
                }
              }

              barList.push(first + barData);
            }
          }
        }

        notallLengthCheck();
        for(var i = 0; i < target.length; i++){
          $(target[i]).val(barList[i]);
          if(barList[i].length!==12){
            alert('바코드는 12자리 입력이 되어야합니다.');
            target[i].focus();
            return false;
          }
        }
      }
      notallLengthCheck();
    });

    $(".barNumGuideBox .closeBtn").click(function() {
      $(this).closest(".barNumGuideBox").hide();
    });

    $(".barNumGuideOpenBtn").click(function() {
      $(this).next().toggle();
    });

    /* 210317 */
    $(".nativePopupOpenBtn").click(function(e){
      var cnt = 0;
      var frm_no = $(this).closest("li").find(".frm_input").attr("data-frm-no");
      var item = $(this).closest("ul").find(".frm_input");
      sendBarcodeTargetList = [];
      
      cur_ct_id = $(this).data('ct-id');
      cur_it_id = $(this).data('it-id');

      for(var i = 0; i < item.length; i++){
        if(!$(item[i]).val() || $(item[i]).attr("data-frm-no") == frm_no){
          sendBarcodeTargetList.push($(item[i]).attr("data-frm-no"));
          cnt++;
        }
      }

      $('#scanner-count').val(cnt);
      $('#barcode-selector').fadeIn();
    });
  });

  function openCloseToc(click) {
    if($(click).closest('li').children('.folding_box').css("display")=="none"){
      $(click).closest('li').children('.folding_box').css("display", "block");
      $(click).find('.p1 .span2 .up').css("display", "inline-block");
      $(click).find('.p1 .span2 .down').css("display", "none");
    } else {
      $(click).closest('li').children('.folding_box').css("display", "none");
      $(click).find('.p1 .span2 .up').css("display", "none");
      $(click).find('.p1 .span2 .down').css("display", "inline-block");
    }
  }

  // 팝업열기
  function showPopup(multipleFilter) {
  const popup = document.querySelector('#popup');

    if (multipleFilter) {
      popup.classList.add('multiple-filter');
    } else {
      popup.classList.remove('multiple-filter');
    }

    popup.classList.remove('hide');
  }

  // 팝업닫기
  function closePopup() {
    const popup = document.querySelector('#popup');
    popup.classList.add('hide');
  }
  $(".popupCloseBtn").click(function(e) {
    e.preventDefault();
    close();
  });
  $(".popupCloseBtn").click(function(e) {
    e.preventDefault();
    close();
  });
  function close() {
    $("#popupProdBarNumInfoBox", parent.document).hide();
    $("#popupProdBarNumInfoBox", parent.document).find("iframe").remove();
  }

  </script>
<?php include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>
