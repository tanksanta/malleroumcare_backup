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
    .imfomation_box > div.a { width: 100%; float: left; }
    .imfomation_box > div.a > li { width: 100%; float: left; padding: 20px; border-bottom: 1px solid #DDD; }
    .imfomation_box div.a .li_box{ width:100%;  height:auto;text-align:center;}
    .imfomation_box div.a .li_box .li_box_line1{ width: 100%;  height:auto; margin:auto; float:left;color:#000; }
    .imfomation_box div.a .li_box .li_box_line1 .p1{ width:100%; float:left; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
    .imfomation_box div.a .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
    .imfomation_box div.a .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; }
    .imfomation_box div.a .li_box .li_box_line1 .p1 .span2{ width: 120px; font-size:14px; text-align: right; }
    .imfomation_box div.a .li_box .li_box_line1 .p1 .span2 img{ width: 13px; margin-left: 15px; vertical-align: middle; top: -1px; }
    .imfomation_box div.a .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
    .imfomation_box div.a .li_box .li_box_line1 .cartProdMemo { width: 100%; float: left; font-size: 13px; margin-top: 2px; text-align: left; color: #FF690F; }
    /* display:none; */
    .imfomation_box div.a .li_box .folding_box{text-align: center; vertical-align:middle;width:100%; padding-top: 20px; float: left; box-sizing: border-box; }
    .imfomation_box div.a .li_box .folding_box > span { width: 100%; float: left; }
    .imfomation_box div.a .li_box .folding_box > .inputbox { width: 100%; float: left; position: relative; padding: 0; }
    .imfomation_box div.a .li_box .folding_box > .inputbox > li { width: 100%; float: left; position: relative; }
    .imfomation_box div.a .li_box .folding_box > .inputbox > li > span { 
      width: 100%;
      float: left;
      text-align: left;
      padding: 8px 25px;
      box-sizing: border-box;
      font-size: 17px;
      border: 1px solid #ffffff;
      border-color: #ebebeb;
      color: #7d7d7d;
      margin: 2px 0px;    
    }

    .imfomation_box div.a .li_box .folding_box .span{margin-left :20px;width:90%;}

    .imfomation_box div.a .li_box .deliveryInfoWrap { width: 100%; float: left; background-color: #F1F1F1; border-radius: 5px; padding: 10px; margin-top: 15px; }
    .imfomation_box div.a .li_box .deliveryInfoWrap > select { width: 34%; height: 40px; float: left; margin-right: 1%; border: 1px solid #DDD; font-size: 17px; color: #666; padding-left: 10px; border-radius: 5px; }
    .imfomation_box div.a .li_box .deliveryInfoWrap > input[type="text"] { width: 65%; height: 40px; float: left; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 40px 0 10px; border-radius: 5px; }
    .imfomation_box div.a .li_box .deliveryInfoWrap > img { position: absolute; width: 30px; right: 15px; top: 50%; margin-top: -15px; z-index: 2; cursor: pointer; }

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
      <div class="a">
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
                <span class="c_num"><?=count($result_again);?></span>/<?=$ct["ct_qty"]?>
                <img class="up" src="<?=G5_IMG_URL?>/img_up.png" alt="">
                <img class="down" src="<?=G5_IMG_URL?>/img_down.png" alt="">
              </span>
            </p>

            <?php if($prodMemo){ ?>
              <p class="cartProdMemo"><?=$prodMemo?></p>
            <?php } ?>
          </div>
          <div class="folding_box">

          <ul class="inputbox">
              <?php for($b = 0; $b< count($result_again); $b++) { if( !$result_again[$b]["prodBarNum"] ) continue; ?>
              <li>
                <span> <?=$result_again[$b]["prodBarNum"]?> </span>
              </li>
              <?php $prodListCnt++; } ?>
            </ul>
          </div>
        </li>
      </div>
    </ul>
  </form>



  <script type="text/javascript">

    $(".c_num").text("<?=$prodListCnt?>");

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
  
</body>
