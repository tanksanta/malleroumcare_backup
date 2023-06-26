<?php
include_once("./_common.php");

$g5["title"] = "주문 내역 바코드 수정";
// include_once(G5_ADMIN_PATH."/admin.head.php");

$od = sql_fetch(" SELECT od.*, 
                ( SELECT COUNT(od_id) FROM `g5_shop_cart` WHERE `od_id` = od.od_id ) AS more_totalCnt,
                ( SELECT it_name FROM `g5_shop_cart` WHERE `od_id` = od.od_id ORDER BY it_id ASC LIMIT 0, 1 ) AS more_it_name
          FROM {$g5['g5_shop_order_table']} od 
          WHERE `od_id` = ( SELECT `od_id` FROM {$g5['g5_shop_cart_table']} WHERE `ct_id` = '$ct_id' ORDER BY it_id ASC LIMIT 0, 1)
");

$od_id = $od['od_id'];
$prodList = [];
$prodListCnt = 0;
$prodListCnt2 = 0;
$deliveryTotalCnt = 0;

if (!$od['od_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$carts = [];
$sql = " SELECT a.ct_id,
					a.it_id,
					a.it_name,
          a.io_type,
					a.ct_status,
					a.ct_qty,
					a.ct_delivery_company,
					a.ct_delivery_num,
					a.ct_combine_ct_id,
          a.ct_status,
          a.ct_option,
          a.ct_barcode_insert,
          a.stoId,
					a.prodMemo,
					b.ca_id,
          b.it_name,
          b.it_use_short_barcode,
          b.prodpaycode,
          ( SELECT io_use_short_barcode FROM g5_shop_item_option AS o WHERE o.it_id = a.it_id AND o.io_id = a.io_id ) AS io_use_short_barcode
			  FROM {$g5['g5_shop_cart_table']} a 
        LEFT JOIN {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  WHERE a.od_id = '$od_id'
        AND (
          ct_id = '$ct_id'
          OR ct_combine_ct_id = '$ct_id'
          OR ct_id = ( SELECT ct_combine_ct_id FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '$ct_id' LIMIT 1 )
          OR ct_combine_ct_id = ( SELECT ct_combine_ct_id FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '$ct_id' LIMIT 1 )
        )
			  ORDER bY a.ct_combine_ct_id, a.ct_id";

$result = sql_query($sql);

$sto_imsi = '';
$combine_it_name = '';

for ($i=0; $row=sql_fetch_array($result); $i++) {
  $carts[] = $row;
  if ($i === 0) {
    $combine_it_name = $row['it_name'];
    if ($row['it_name'] != $row['ct_option']) {
      $combine_it_name .= "({$row['ct_option']})";
    }
  }

  $sto_imsi .= $row['stoId'];
}

$stoIdDataList = explode('|',$sto_imsi);
$stoIdDataList = array_filter($stoIdDataList);
$stoIdData = implode("|", $stoIdDataList);
$res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(  'stoId' => $stoIdData ));
$result_again = $res['data'];

$moreInfoDisplayCnt = "";
$od["more_totalCnt"]--;
if($od["more_totalCnt"]){
  $moreInfoDisplayCnt = "외 {$od["more_totalCnt"]}종";
}

# 210319 배송정보
$odDeliveryNameTel = "";
$odDeliveryNameTel .= $od["od_b_name"];
if($od["od_b_tel"]) {
  $odDeliveryNameTel .= " / {$od["od_b_tel"]}";
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">  
  <meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" />
  <title>출고정보</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="/js/barcode_utils.js"></script>
  <link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
  <link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/flex.css">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; }
    html, body { width: 100%; font-family: "Noto Sans KR", sans-serif; }
    body { padding-top: 60px; padding-bottom: 70px; }
    a { text-decoration: none; color: inherit; }
    ul, li { list-style: none; }
    button { border: 0; font-family: "Noto Sans KR", sans-serif; }
    input { font-family: "Noto Sans KR", sans-serif;  }

    /* 고정 상단 */
    #popupHeaderTopWrap { position: fixed; width: 100%; height: 50px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
    #popupHeaderTopWrap:after { display: block; content: ''; clear: both; }
    #popupHeaderTopWrap > div { height: 100%; line-height: 50px; }
    #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
    #popupHeaderTopWrap > .close { float: right; }
    #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

    /* 상품기본정보 */
    #itInfoWrap { width: 100%; padding: 10px 20px; border-bottom: 1px solid #DFDFDF; }
    #itInfoWrap > .name { width: 100%; font-weight: bold; font-size: 17px; }
    #itInfoWrap > .name > .delivery { color: #FF690F; }
    #itInfoWrap > .date { width: 100%; font-size: 13px; color: #666; }
    #itInfoWrap > .deliveryInfo { width: 100%; border-radius: 5px; padding: 10px 15px; background-color: #F1F1F1; margin-top: 10px; }
    #itInfoWrap > .deliveryInfo > p { width: 100%; color: #000; font-size: 13px; }
    #itInfoWrap > .deliveryInfo > p.title { color: #666; font-size: 15px; font-weight: bold; margin-bottom: 10px; }

    /* 팝업 */
    #popup { display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, .7);z-index: 50; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);}
    #popup.hide {display: none;}
    #popup.multiple-filter { backdrop-filter: blur(4px) grayscale(90%); -webkit-backdrop-filter: blur(4px) grayscale(90%);}
    #popup .content { padding: 20px; background: #fff; border-radius: 5px; box-shadow: 1px 1px 3px rgba(0, 0, 0, .3); max-width:90%;}
    #popup .content { max-width:90%; font-size: 14px; }
    #popup .closepop { width: 100%; height: 40px; cursor: pointer; color:#fff; background-color:#000; border-radius:6px; margin-top: 10px; }

    /* 상품목록 */
    #submitForm { width: 100%; }
    .imfomation_box{ margin:0px;width:100%;position:relative; padding:10px 20px; display:block; width:100%; height:auto;  }
    .imfomation_box  ul { width: 100%; padding:5px 10px ; } */
    .imfomation_box .li_box { width: 100%; padding: 20px; /* border-bottom: 1px solid #DDD; */ }
    .imfomation_box .li_box{ width:100%; height:auto;text-align:center;}
    .imfomation_box .li_box .li_box_line1{ width: 100%; height:auto; margin:auto; color:#000; }
    .imfomation_box .li_box .li_box_line1 .p1{ width:100%; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
    .imfomation_box .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
    .imfomation_box .li_box .li_box_line1 .p1 .span1{ font-size: 18px; word-break: keep-all; width: 60%; }
    /* .imfomation_box .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; } */
    .imfomation_box .li_box .li_box_line1 .p1 .span2{ width: 120px; font-size:14px; text-align: right; }
    .imfomation_box .li_box .li_box_line1 .p1 .span2 img{ width: 13px; margin-left: 15px; vertical-align: middle; top: -1px; }
    .imfomation_box .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
    .imfomation_box .li_box .li_box_line1 .p1 .span3 { text-align:right; font-size:0.8em; color:#9b9b9b; }
    .imfomation_box .li_box .li_box_line1 .p1 .span3 .outline { border: 1px solid #9b9b9b; border-radius: 3px; padding: 5px 30px; display: inline-block; }
    .imfomation_box .li_box .li_box_line1 .p1 .span3 label { color: #000; }
    .imfomation_box .li_box .li_box_line1 .p1 .span3 input { vertical-align:middle; }
    .imfomation_box .li_box .li_box_line1 .cartProdMemo { width: 100%; font-size: 13px; margin-top: 2px; text-align: left; color: #FF690F; }
    /* display:none; */
    .imfomation_box .li_box .folding_box{text-align: center; vertical-align:middle; width:100%; padding-top: 10px; display:none; box-sizing: border-box; }
    .imfomation_box .li_box .folding_box > span { display: block; width: 100%; }
    .imfomation_box .li_box .folding_box > span:after { display: block; content: ''; clear: both; }
    .imfomation_box .li_box .folding_box > .inputbox { width: 100%; position: relative; padding: 0; }
    .imfomation_box .li_box .folding_box > .inputbox > li { width: 100%; position: relative; }
    .imfomation_box .li_box .folding_box > .inputbox > li > .frm_input { width: 100%; height: 50px; padding-right: 85px; box-sizing: border-box; padding-left: 10px; font-size: 17px; border: 1px solid #E4E4E4; }
    .imfomation_box .li_box .folding_box > .inputbox > li > .frm_input.active { border-color: #FF5858; }
    .imfomation_box .li_box .folding_box > .inputbox > li > .frm_input::placeholder { font-size: 16px; color: #AAA; }

    .imfomation_box .li_box .folding_box > .inputbox > li > .btn_bacod { position: absolute; width: 30px; right: 50px; top: 11px; z-index: 2; cursor: pointer; }
    .imfomation_box .li_box .folding_box > .inputbox > li > .btn_pda { position: absolute; width: 35px; right: 1px; top: 7px; z-index: 2; cursor: pointer; }
    
    .imfomation_box .li_box .folding_box > .inputbox > li > img { position: absolute; width: 30px; right: 15px; top: 11px; z-index: 2; cursor: pointer; }
    .imfomation_box .li_box .folding_box > .inputbox > li > i { position: absolute; right: 38px; top: 17px; z-index: 2; font-size: 19px; color: #FF6105; opacity: 0; }
    .imfomation_box .li_box .folding_box > .inputbox > li > i.active { opacity: 1; }
    .imfomation_box .li_box .folding_box > .inputbox > li > .overlap { position: absolute; right: 35px; top: 15px; z-index: 2; font-size: 14px; color: #DC3333; opacity: 0; font-weight: bold; }
    .imfomation_box .li_box .folding_box > .inputbox > li > .overlap.active { opacity: 1; }

    .imfomation_box .li_box .folding_box .span{margin-left :20px;width:90%;}
    .imfomation_box .li_box .folding_box .all{margin-bottom:5px; padding-left :20px; font-size:15px;text-align:left;float:left;height:30px; width:60%; border-radius: 6px; background-color:#c0c0c0;  color:#fff; border:0px; box-sizing: border-box; }
    .imfomation_box .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box .li_box .folding_box .barNumCustomSubmitBtn{float:left;margin-left:10px;color:#fff;font-size:15px;background-color:#494949; border:0px;border-radius: 6px;width:18%; height:30px; font-weight: bold; }
    .imfomation_box .li_box .folding_box .barNumGuideOpenBtn{float:left; position: relative; margin-left:10px; width:25px; cursor: pointer; top: 5px; }
    .imfomation_box .li_box .folding_box .notall{
      margin-bottom:5px;font-size:20px;text-align:left;height:50px;width:90%; border-radius: 6px; background-color:#fff;  color:#666666; border:0px; ; border: 1px solid #c0c0c0;;
      /* background-image : url('<?php echo G5_IMG_URL?>/bacod_img.png');  */
      /* background-position:top right;  */
      /* background-repeat:no-repeat; */

    }


    .imfomation_box .li_box .deliveryInfoWrap { width: 100%; position: relative; background-color: #F1F1F1; border-radius: 5px; padding: 10px; margin-top: 15px; }
    .imfomation_box .li_box .deliveryInfoWrap:after { display: block; content: ''; clear: both; }
    .imfomation_box .li_box .deliveryInfoWrap > select { width: 34%; height: 40px; float: left; margin-right: 1%; border: 1px solid #DDD; font-size: 17px; color: #666; padding-left: 10px; border-radius: 5px; }
    .imfomation_box .li_box .deliveryInfoWrap > input[type="text"] { width: 65%; height: 40px; float: left; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 40px 0 10px; border-radius: 5px; }
    .imfomation_box .li_box .deliveryInfoWrap > img { position: absolute; width: 30px; right: 15px; top: 50%; margin-top: -15px; z-index: 2; cursor: pointer; }


    /* 고정 하단 */
    #popupFooterBtnWrap { position: fixed; width: 100%; height: 55px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }


    /* 바코드 순차입력 버튼 */
    .imfomation_box .li_box .folding_box > .inputbox > li > .barcode_add {
      width:35px;
      height:35px;
      position: absolute;
      top: 8px;
      right: 80px;
      display:none;
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

    .imfomation_box .li_box .folding_box > .inputbox > li .barcode_icon.type5 {
      position: absolute;
      width: 20px;
      right: 38px;
      top: 17px;
      z-index: 2;
      opacity: 0;
    }

    .imfomation_box .li_box .folding_box > .inputbox > li .barcode_icon.type5.active { opacity: 1; }
    

    .barcode_warning { display: none; font-size: 14px; text-align: left; padding: 10px 5px; }
    .barcode_infotext { font-size: 14px; text-align: left; padding: 10px 5px; }


    #barcodeHistory { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; }
    #barcodeHistory .mask { background: rgba(0, 0, 0, 0.7); width: 100%; height: 100%; position: absolute; }
    #barcodeHistory .historyContent { position: absolute; width: 100%; height: 40%; bottom: 0; left: 0; background: #fff; padding: 50px 20px 10px; }
    #barcodeHistory .historyContent .header { position: absolute; top: 0; left: 0; width: 100%; padding: 10px 20px; border-bottom: 1px solid #d9d9d9; }
    #barcodeHistory .historyContent .barcode { font-size: 18px; font-weight: bold; }
    #barcodeHistory .historyContent .close { font-size: 37px; width: 33px; height: 33px; line-height: 33px; background: none; position: relative; top: -3px; }
    #barcodeHistory .historyContent .content { margin-top: 5px; height: 100%; overflow-y: scroll; }
    #barcodeHistory .historyContent li { border-bottom: 1px solid #d9d9d9; padding: 10px 0; }
    #barcodeHistory .historyContent li .subtitle { font-size: 13px; }
    #barcodeHistory .historyContent li .title { font-size: 17px; }
 

    #order_log { padding: 20px; }
    #order_log .title { font-size: 18px; margin-bottom: 15px; }
    #order_log .logs { font-size: 14px; }
    #order_log .logs .row { margin-bottom: 7px; display: inline-block; }
    #order_log .logs .log_datetime { margin-right: 10px; }
  </style>
</head>

<body>

  <!-- 고정 상단 -->
  <div id="popupHeaderTopWrap">
    <div class="title">바코드입력</div>
    <div class="close">
      <a href="javascript:member_cancel();">
        &times;
      </a>
    </div>
  </div>

  <!-- 상품기본정보 -->
  <div id="itInfoWrap">
    <p class="name">
      [<?=($od["recipient_yn"] == "Y") ? "주문" : "재고"?>] <?=$od["more_it_name"]?> <?=$moreInfoDisplayCnt?>
      <!-- <span class="delivery">(배송 : <?=$od_cart_count?>개)</span> -->
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
  <!--<a href="./popup.prodBarNum.form.excel.php?od_id=<?=$od_id?>" class="excel_btn">엑셀다운로드</a>-->

  </div>

   <!-- 상품목록 -->
  <form id="submitForm">
    <input type="hidden" name="od_id" value="<?=$od_id?>">
    <input type="hidden" name="update_type" value="popup">
    <ul class="imfomation_box" id="imfomation_box">
      <?php
      for($i = 0; $i < count($carts); $i++) {
        #바코드 8자리 사용여부
        $use_short_barcode = 'N';
        if ($carts[$i]['io_use_short_barcode']) {
          $use_short_barcode = 'Y';
        } else {
          if ($carts[$i]['it_use_short_barcode']) {
            $use_short_barcode = 'Y';
          }
        }
        
        # 요청사항
        $prodMemo = "";

        $options = $carts[$i]["options"];

        $stoId_v=[];
        $stoId_v = explode('|',$carts[$i]['stoId']);
        $stoId_v=array_filter($stoId_v);

        # 요청사항
        $prodMemo = ($prodMemo) ? $prodMemo : $carts[$i]["prodMemo"];
        # 카테고리 구분
        $gubun = $cate_gubun_table[substr($carts[$i]['ca_id'], 0, 2)];
      ?>
      <?php if ($carts[$i]['ct_combine_ct_id']) { ?>
        <div style="margin:5px 20px;margin-bottom:0px;background-color:#ff6105;color:white;text-align:center;border-radius:3px;padding:10px;font-size:13px;">
        <?php
          echo ( stripslashes($combine_it_name) . ' 상품과 같이 배송 됩니다.' );
        ?>
        </div>
      <?php } ?>

      <ul href="javascript:void(0)" class="<?= $carts[$i]['ct_status'] !== "취소" && $carts[$i]['ct_status'] !== "주문무효" ? "" : "hide_area" ?> ">

        <li class="li_box">
          <div class="li_box_line1"
            <?php if ($gubun != '02' && $carts[$i]['io_type'] == 0) { ?>
              onclick="openCloseToc(this)"
            <?php } ?>
            >
            <input type="hidden" id="it_name" value="<?=stripslashes($carts[$i]["it_name"])?>" data-it-id="<?=$carts[$i]["it_id"]?>">
            <input type="hidden" id="ct_option" value="<?=$carts[$i]["ct_option"]?>" data-it-id="<?=$carts[$i]["it_id"]?>">
            <input type="hidden" id="ct_qty" value="<?=$carts[$i]["ct_qty"]?>" data-it-id="<?=$carts[$i]["it_id"]?>">            

            <p class="p1" data-qty="<?=$carts[$i]["ct_qty"]?>">
              <span class="span1">
                <!-- 상품명 -->
                <?php echo $carts[$i]['io_type'] == 1 ? '[추가옵션] ' : ''; ?>
                <?=stripslashes($carts[$i]["it_name"])?>
                <!-- 옵션 -->
                <?php if($carts[$i]["it_name"] != $carts[$i]["ct_option"]){ ?>
                (<?=$carts[$i]["ct_option"]?>)
                <?php } ?>
                <!-- 수량 -->
                <?php echo " ({$carts[$i]["ct_qty"]}개)"; ?>
                <?php if ($use_short_barcode == 'Y') { ?>
                  <label for="it_use_short_barcode" id="it_use_short_barcode_label">
                  <input style="margin-left:10px;" type="checkbox" name="it_use_short_barcode" value="1" id="it_use_short_barcode" data-it-id="<?php echo $carts[$i]['it_id']; ?>" <?php echo ($use_short_barcode == 'Y') ? "checked" : ""; ?>> 바코드 8자리
                </label>
                <?php } ?>
              </span>
              <?php if ($gubun != '02' && $carts[$i]['io_type'] == 0) { ?>
              <span class="span2">
                <?php
                $add_class="";
                for($b = 0; $b< $carts[$i]["ct_qty"]; $b++){
                  $add_class=$add_class.' '.$stoIdDataList[$prodListCnt2].'_v';
                  $prodListCnt2++;
                }
                ?>
                <span class="<?=$add_class?> c_num">0</span>/<?=$carts[$i]["ct_qty"]?>
                <img class="up" src="<?=G5_IMG_URL?>/img_up.png" alt="">
                <img class="down" src="<?=G5_IMG_URL?>/img_down.png" alt="">
              </span>
              <?php } else { ?>
                <span class="span3" style="font-size:1em;">
                  <?php echo $gubun == '02' ? '비급여' : '추가옵션'; ?> 상품 바코드 미입력&nbsp;
                  <span class="outline">
                    <input 
                      type="checkbox"
                      name="chk_pass_barcode_<?php echo $carts[$i]['ct_id']; ?>"
                      value="1"
                      id="chk_pass_barcode_<?php echo $carts[$i]['ct_id']; ?>"
                      <?php if ($carts[$i]['ct_qty'] == $carts[$i]['ct_barcode_insert']) { ?>
                        checked="checked"
                      <?php } ?>
                      class="chk_pass_barcode"
                      data-gubun="<?=$gubun?>"
                      data-ct-id="<?php echo $carts[$i]['ct_id']; ?>"
                    >
                    <label for="chk_pass_barcode_<?php echo $carts[$i]['ct_id']; ?>">확인함</label>
                  </span>
                </span>
              <?php } ?>
            </p>
            <?php if($prodMemo){ ?>
            <p class="cartProdMemo"><?=$prodMemo?></p>
            <?php } ?>
          </div>

          <?php if ($gubun != '02') { ?>
          <div class="folding_box id_<?php echo $carts[$i]['ct_id']; ?>" data-id="<?php echo $carts[$i]['ct_id']; ?>">
            <?php if ($carts[$i]["ct_qty"] >= 2) { ?>
            <!--
            <span>
            <input type="text" class="all frm_input" placeholder="일괄 등록수식 입력">
            <button type="button" class="barNumCustomSubmitBtn">등록</button>
            <img src="<?php echo G5_IMG_URL?>/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)">
            </span>
            -->
            <?php } ?>
            <ul class="inputbox">
              <?php for ($b = 0; $b< count($stoId_v); $b++) { ?>
              <li>
                <input type="number" maxlength="12" oninput="maxLengthCheck(this)" value="<?=$prodList[$b]["prodBarNum"]?>" class="notall frm_input frm_input_<?=$prodListCnt?> required prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoId_v[$b]?>" placeholder="바코드를 입력하세요." data-frm-no="<?=$prodListCnt?>" data-it-id="<?php echo $carts[$i]['it_id']; ?>">
                <img src="<?php echo G5_IMG_URL?>/bacod_add_img.png" class="barcode_add">
                <i class="fa fa-check"></i>
                <span class="overlap">중복</span>
                <img class="barcode_icon type5" src="/img/barcode_icon_3.png" alt="등록불가 (미보유재고)">
                <img src="<?php echo G5_IMG_URL?>/btn_pda.png" class="nativePopupOpenBtn btn_pda" data-type="pda" data-code="<?=$b?>" data-ct-id="<?php echo $carts[$i]['ct_id']; ?>" data-it-id="<?php echo $carts[$i]['it_id']; ?>" data-pd-code="<?php echo $carts[$i]['prodpaycode']; ?>">
              </li>
              <?php $prodListCnt++; } ?>
            </ul>

            <p class="barcode_warning"><span style="color: red">(주의)</span> 재고가 없는 바코드가 있습니다. 관리자 승인 시 정상 등록 됩니다.</p>
            <p class="barcode_infotext"><span style="">* 입력된 바코드를 더블 클릭하면 수정 가능 합니다.</span></p>

          </div>
          <?php } ?>

          <?php if (!$carts[$i]['ct_combine_ct_id']) { ?>
          <div class="deliveryInfoWrap">
            <input type="hidden" name="ct_id[]" value="<?=$carts[$i]["ct_id"]?>">
            <select name="ct_delivery_company_<?=$carts[$i]["ct_id"]?>">
              <?php foreach($delivery_companys as $data){ ?>
              <option value="<?=$data["val"]?>" <?=($carts[$i]["ct_delivery_company"] == $data["val"]) ? "selected" : ""?>><?=$data["name"]?></option>
              <?php } ?>
            </select>
            <input type="text" value="<?=$carts[$i]["ct_delivery_num"]?>" name="ct_delivery_num_<?=$carts[$i]["ct_id"]?>" placeholder="송장번호 입력" oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
            <img src="<?=G5_IMG_URL?>/bacod_img.png" class="nativeDeliveryPopupOpenBtn">
          </div>
          <?php } ?>
        </li>
      </ul>
      <hr /><br />
      <?php
      }
      ?>
    </ul>
  </form>

  <div id="order_log">
    <p class="title">기록</p>
    <?php

    $result = sql_query("SELECT * FROM `g5_shop_order_admin_log` WHERE `od_id` = '{$od_id}' AND `ol_content` NOT LIKE '이카운트 엑셀%' ORDER BY ol_no DESC");

    $logs = array();
    while($row = sql_fetch_array($result)) {
      $logs[] = $row;
    }

    ?>
    <div class="logs">
      <?php
      foreach($logs as $log) {
        $log_mb = get_member($log['mb_id']);
        echo '<span class="row"><span class="log_datetime">'.$log['ol_datetime'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['ol_content'] . '</span><br/>';
      }
      if (!count($logs)) {
        echo '기록이 없습니다.';
      }
      ?>
    </div>
  </div>

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
  <div id="popupFooterBtnWrap">
    <button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button type="button" class="cancelbtn" onclick="member_cancel();">취소</button>
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

  if(!$member['mb_id']){alert('접근이 불가합니다.');}
  //접속시 db- >id 부과
  sql_query("update {$g5['g5_shop_cart_table']} set `ct_edit_member` = '".$member['mb_id']."' where `od_id` = '{$od_id}'");
  ?>


  <script type="text/javascript">
    var LOADING = false;
    var keyupTimer;

    $(".hide_area").hide();




    $(document).on('keyup', '.notall', function () {
      var last_index = $(this).closest('ul').find('li').last().index();
      var this_index = $(this).closest('li').index();

      $(this).closest('ul').find('.barcode_add').hide();
      if(last_index !== this_index && $(this).val().length == 12) {        
          $(this).closest('li').find('.barcode_add').show();
      }

      if(keyupTimer) clearTimeout(keyupTimer);
      keyupTimer = setTimeout(notallLengthCheck, 350);

    });

    
    $.fn.setCursorPosition = function(position) {
  if (this.length === 0) return this;
  return this.each(function() {
    if (typeof position !== "number") return;
    if (this.setSelectionRange && this.type !== "number") {
      this.setSelectionRange(position, position);
    }
  });
};



    $(".notall").dblclick(function() {
      if(!confirm("해당 바코드를 다시 스캔 하시겠습니까?\n\n[확인]: 다시 스캔\n[취소]: 수동 입력")) {
        // 수동 입력
        
        $(this).attr("readonly",false);       
        $(this).css({ "background-color": "#fff" });
        $(this).closest('li').find(".nativePopupOpenBtn.btn_pda").show();
       

        var value = $(this).val();
        var len = value.length;
        $(this).focus();

        if (this.type !== "number") {
          this.setSelectionRange(len, len);
        } else {
          var input = $(this).get(0);
          var temp = input.value;
          input.value = '';
          input.value = temp;
          //input.setSelectionRange(len, len);
        }

        
      } else {
        // 다시스캔
        $(this).val("");
        $(this).css({ "background-color": "#fff" });
        $(this).closest('li').find('i').removeClass("active");
        $(this).closest('li').find('.overlap').removeClass("active");
        $(this).closest('li').find('.type5').removeClass("active");         
        $(this).closest('li').find('.barcode_add').hide();
        $(this).closest('li').find(".nativePopupOpenBtn.btn_pda").show();
        $(this).closest('li').find('.nativePopupOpenBtn.btn_pda').click();        
      }
    });


    $('.notall').focus(function(){

        var last_index = $(this).closest('ul').find('li').last().index();
        var this_index = $(this).closest('li').index();

        $(this).closest('ul').find('.barcode_add').hide();
        if(last_index !== this_index && $(this).val().length == 12) {
            $(this).closest('li').find('.barcode_add').show();
        }
    });
    

    $('.barcode_add').click(function() {

      var ul = $(this).closest('ul');
      var li_num = $(this).closest('li').index();
      var li_val = $(this).closest('li').find('.notall').val();
      var li_last = $(ul).find('li').last().index();
      var p_num = 0;

      if(li_val.length !== 12){
          alert("바코드 12자리를 입력해주세요.");
          return false;
      }     

      for(var i = li_num+1; i<=li_last; i++){
        p_num++;
        $(ul).find('li').eq(i).find('.notall').prop('readonly', false);
        $(ul).find('li').eq(i).find('.notall').val( (parseInt( li_val )+p_num) );

        // 연속 번호로 12자리 이상을 입력할 수 없음.
        if( $(ul).find('li').eq(i).find('.notall').val().length !== 12) {

          if( $(ul).find('li').eq(i).find('.notall').val().length > 12 ) {
            alert("12자리 이상의 연속 번호는 적용할 수 없습니다.\n연속 적용하려는 바코드를 확인해주세요.");
            return false;
          } else {

            if( confirm("정확하지 않은 바코드 정보가 존재 합니다.\n바코드값: " + $(ul).find('li').eq(i).find('.notall').val() + "\n해당 필드의 바코드 정보를 덮어쓰기 하시겠습니까?") ) {
              p_num++;
              $(ul).find('li').eq(i).find('.notall').val( (parseInt( li_val )+p_num) );
            }
            
          }

        }
        
      }

      notallLengthCheck();
    });


  /* 바코드 입력글자 수 체크 */
  function notallLengthCheck() {
    var $foldingBox = $('.folding_box');

    //$(".imfomation_box .li_box .folding_box > .inputbox > li > i").removeClass("active");
    //$(".imfomation_box .li_box .folding_box > .inputbox > li > .overlap").removeClass("active");
    $foldingBox.find("i").removeClass("active");
    $foldingBox.find(".overlap").removeClass("active");

    $foldingBox.each(function() {

      var $item = $(this).find('.notall');
      $item.removeClass("active");

      var dataTable = [];
      $item.each(function(i) {

        var $cur = $(this);
        var barcode = $cur.val();

        if( !barcode ) { return true; }

        var maxlength = parseInt($cur.attr("maxlength"));
        var it_id = $cur.attr("data-it-id");


        $("input[name='it_use_short_barcode']").each(function(item) {
          if ($(this).attr("data-it-id") == it_id && $(this).is(":checked")) {
            maxlength = 8;
          }
        });

        var length = barcode.length;
        if(length < maxlength && length) {
          $cur.addClass("active");
        }

        if(length == maxlength && /^-?\d+$/.test(barcode)) { //숫자만 입력되었는지 체크 로직 추가 211103
          $cur.parent().find("i").addClass("active");
          $cur.parent().find(".nativePopupOpenBtn.btn_pda").hide();

          $cur.parent().find('.frm_input').prop('readonly', true);
          $cur.parent().find('.frm_input').css({ "background-color": "#f1f1f1" });

          if( !dataTable[barcode] ) { dataTable[barcode] = []; }
          dataTable[barcode].push(i);
        }

      });

      var keys = Object.keys(dataTable);

      for(var i = 0; i < keys.length; i++) {
        var val = dataTable[keys[i]];
      
        if(val.length > 1) {
          for(var j = 0; j < val.length; j++) {
            var idx = val[j];
            $($item[idx]).parent().find("i, .type5").removeClass("active");
            $($item[idx]).parent().find(".overlap").addClass("active");
          }
        }
      }

      var ct_id = $(this).data('id');
 
      validateBarcodeBulk(ct_id);

    });

  }


  function validateBarcodeBulk(ct_id) {
    var barcodeArr = [];

    $('.folding_box.id_' + ct_id + ' li').each(function () {
      if( ($(this).find('.frm_input').val().length === 12)&&($(this).find('.frm_input').prop("readonly") == false) ) {
          if( ($(this).find('.frm_input').val().length === 12) ) {
          barcodeArr.push({
            ct_id: ct_id,
            index: $(this).index(),
            barcode: $(this).find('.frm_input').val(),
          });
        }
      }
    })

    if (barcodeArr.length > 0) {
      //$('.folding_box.id_' + ct_id + ' .barcode_icon.type5').removeClass('active');
      //$('.folding_box.id_' + ct_id + ' .barcode_warning').hide();

      $.ajax({
        url: './ajax.barcode_validate_bulk.php',
        type: 'POST',
        data: {
          ct_id: ct_id,
          barcodeArr: barcodeArr,
        },
        dataType: 'json',
        async: false,
      })
      .done(function(result) {
        // console.log(result.data);
        var target = $('.folding_box.id_' + ct_id + ' li');
        var activeCount = 0;

        result.data.barcodeArr.forEach(function (_this) {
          if( _this.status === '미보유재고' ) {
            target.eq(_this.index).find('.fa-check').removeClass('active');
            target.eq(_this.index).find('.barcode_icon.type5').addClass('active');
            activeCount++;
          }

          target.eq(_this.index).find('.frm_input').prop('readonly', true);
          target.eq(_this.index).find('.frm_input').css({ "background-color": "#f1f1f1" });

        });

        if (activeCount > 0) {
          $('.folding_box.id_' + ct_id + ' .barcode_warning').show();
        }

      })
      .fail(function($xhr) {
        // msgResult = 'error'
        var data = $xhr.responseJSON;
        console.log(data && data.message);

        var _arrayBarcode = [];
        data.data.barcodeArr.forEach(function (_this) {
          _arrayBarcode[_this.index] = _this.status;
        });

        console.log(_arrayBarcode);
        setTimeout(function() {

          var target = $('.folding_box.id_' + ct_id + ' li');
          var activeCount = 0;

          var minValue = _arrayBarcode.length-1;
          console.log(minValue);

          $('.folding_box.id_' + ct_id + ' li').each(function () {
            
            if( _arrayBarcode[activeCount] && _arrayBarcode[activeCount] === "미등록재고" ) {

              $(this).find('.frm_input').prop('readonly', false);
              $(this).find('.frm_input').css({ "background-color": "#fff" });

              $(this).find('.fa-check').removeClass('active');
              $(this).find('.barcode_icon.type5').removeClass('active');
              $(this).find('.frm_input').val("");

            } else if( _arrayBarcode[activeCount] && _arrayBarcode[activeCount] === "정상" ){

              $(this).find('.frm_input').prop('readonly', true);
              $(this).find('.frm_input').css({ "background-color": "#f1f1f1" });
              
            }
            activeCount++;
          });

          // 23.05.22 : 서원 - 기존 입력 필드 바코드 전부 배열로 회수.
          var _TmpBarcode = [];          
          $('.folding_box.id_' + ct_id + ' li').each(function () {
            if( $(this).find('.frm_input').val().length === 12 ) {
              _TmpBarcode.push( $(this).find('.frm_input').val() );
            }

            $(this).find('.frm_input').prop('readonly', false);
            $(this).find('.frm_input').css({ "background-color": "#fff" });

            $(this).find('.fa-check').removeClass('active');
            $(this).find('.barcode_icon.type5').removeClass('active');
            $(this).find('.frm_input').val("");

          });

          // 23.05.22 : 서원 - 미등록바코드로 발생하는 공백 필드를 순차적 필드로 채움.
          var activeCount = 0;
          $('.folding_box.id_' + ct_id + ' li').each(function () {
            if( _TmpBarcode[activeCount] ){
              $(this).find('.frm_input').val(_TmpBarcode[activeCount]);
              $(this).find('.frm_input').prop('readonly', true);
              $(this).find('.frm_input').css({ "background-color": "#f1f1f1" });              
              $(this).find('.fa-check').addClass('active');
            }
            activeCount++;
          });

          alert(data && data.message);
          return;
          //alert('바코드 재고 확인 도중 오류가 발생했습니다. 관리자에게 문의해주세요.');

        }, 250);
      })
    }
  }


  var need_reload = false;
  //maxnum 지정
  function maxLengthCheck(object){
    var maxlength = object.maxLength;
    var it_id = $(object).attr("data-it-id");
    $("input[name='it_use_short_barcode']").each(function(item) {
      if ($(this).attr("data-it-id") == it_id && $(this).is(":checked")) {
        maxlength = 8;
      }
    });
    if (object.value.length > maxlength){
      object.value = object.value.slice(0, maxlength);
    }
  }


  /* 바코드 입력란 설정 */
  function foldingBoxSetting() {
    var item = $(".folding_box");
    for(var i = 0; i < item.length; i++) {
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


  function check_option(cur_it_id) {
    var option_items = [];
    $("#it_name").each(function() {
      var it_name = $(this).val();
      var it_id = $(this).attr("data-it-id");
      if (it_id == cur_it_id) {
        var options = [];
        $("#ct_option").each(function() {
          if ($(this).attr("data-it-id") == it_id) {
            var ct_option = $(this).val();
            if (it_name != ct_option) {
              options.push(ct_option);
            }
          }
        });
        if (options.length > 0) {
          option_items.push(`상품명:${it_name}\n옵션:${options.join("\n")}`);
        }
      }
    });
    if (option_items.length > 0) {
      alert("옵션이 있는 상품이 포함되어 있습니다. 확인해 주세요.\n" + option_items.join("\n"));
    }
  }

  var cur_ct_id = null;
  var cur_it_id = null;
  var cur_pdcode = null;

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
      success : function(result) {
        if(result.error == "Y") {
          
          // 23.06.14 : 신규 앱 카메라 기능 동작 예외처리.
          if (window.ReactNativeWebView) {
            // WebView가 존재하는 경우에 대한 로직
            const url = `expo://BarCodeOpen/sendInvoiceNum`;
            window.location.href = url;
          } else {

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

          }
          var params = getUrlParams();
          delete params.od_id;
          delete params.ct_id;
          var query_string = decodeURI($.param(params));
          window.location.href = "<?=G5_SHOP_URL?>/release_orderlist.php?" + query_string;
          // window.location.href = "/shop/release_orderlist.php";
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
              check_option(cur_it_id);
            })
            .fail(function($xhr) {
              // 23.06.14 : 신규 앱 카메라 기능 동작 예외처리.
              if (window.ReactNativeWebView) {
                // WebView가 존재하는 경우에 대한 로직
                const url = `expo://BarCodeOpen/sendInvoiceNum`;
                window.location.href = url;
              } else {

                // 23.06.14 : 신규 앱 카메라 기능 동작 예외처리.
                if (window.ReactNativeWebView) {
                  // WebView가 존재하는 경우에 대한 로직
                  const url = `expo://BarCodeOpen/sendInvoiceNum`;
                  window.location.href = url;
                } else {

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

                }
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
    <?php
    $stock_list = [];
    if( is_array($result_again) ) {
      foreach($result_again as $stock) {
        $stock_list[] = array(
          'prodId' => $stock['prodId'],
          'stoId' => $stock['stoId'],
          'prodBarNum' => $stock['prodBarNum']
        );
      }
    }
    ?>
    var stoldList = <?=json_encode($stock_list)?>;
    $.each(stoldList, function() {
      $('.' + this.stoId).val(this.prodBarNum)
    });

    notallLengthCheck();
    foldingBoxSetting();

    $(".nativeDeliveryPopupOpenBtn").click(function() {
      sendInvoiceTarget = $(this).parent().find("input[type='text']");

      // 23.06.14 : 신규 앱 카메라 기능 동작 예외처리.
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
    });

    $("#prodBarNumSaveBtn").click(function() {
      if (LOADING) {
        console.log('is loading now...');
        return;
      }

      LOADING = true;
      $('#prodBarNumSaveBtn').text('저장중...');

      setTimeout(function() {
        if ($(".chk_pass_barcode").data('gubun') == "02" && $(".chk_pass_barcode").is(":checked") == false) {
          if (confirm("비급여 상품 확인함을 선택하지 않으셨습니다. 선택하시겠습니까?")) {
			$(".chk_pass_barcode").prop("checked", true);
			LOADING = false;
			$('#prodBarNumSaveBtn').text('저장');
          } else {
            barNumSave();
          }
        } else {
          barNumSave();
        }

        LOADING = false;
        $('#prodBarNumSaveBtn').text('저장');
      }, 300);
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

      for(var i = 0; i < target.length; i++) {
        if(i > 0) {
          if($(target[i]).find("input").val()) {
            if(!confirm("이미 등록된 바코드가 있습니다.\n무시하고 적용하시겠습니까?")) {
              return false;
            } else {
              break;
            }
          }
        }
      }
      if(val) {
        val = val.split("^");
        var first = val[0];
        var secList = val[1].split(",");
        for(var i = 0; i < secList.length; i++) {
          if(secList[i].indexOf("-") == -1) {
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
        for(var i = 0; i < target.length; i++) {
          $(target[i]).val(barList[i]);

          var maxlength = parseInt($(target[i]).attr("maxlength"));
          var it_id = $(target[i]).attr("data-it-id");
          $("input[name='it_use_short_barcode']").each(function(item) {
            if ($(this).attr("data-it-id") == it_id && $(this).is(":checked")) {
              maxlength = 8;
            }
          });
          if(barList[i].length!==maxlength) {
            alert('바코드는 ' + maxlength + '자리 입력이 되어야합니다.');
            target[i].focus();
            return false;
          }
        }
      }

      notallLengthCheck();
    });

    $(".barNumGuideBox .closeBtn").click(function(){
      $(this).closest(".barNumGuideBox").hide();
    });

    $(".barNumGuideOpenBtn").click(function(){
      $(this).next().toggle();
    });

    $("#it_use_short_barcode_label").click(function(e) {
      e.stopPropagation();
    });
    $("#it_use_short_barcode").click(function(e) {
      e.stopPropagation();
    });

    /* 210317 */
    $(".nativePopupOpenBtn").click(function(e){
      var cnt = 0;
      var frm_no = $(this).closest("li").find(".frm_input").attr("data-frm-no");
      var item = $(this).closest("ul").find(".frm_input");
      sendBarcodeTargetList = [];

      
      cur_ct_id = $(this).data('ct-id');
      cur_it_id = $(this).data('it-id');
      cur_pdcode = $(this).data('pd-code');


      for(var i = 0; i < item.length; i++) {
        if(!$(item[i]).val() || $(item[i]).attr("data-frm-no") == frm_no) {
          sendBarcodeTargetList.push($(item[i]).attr("data-frm-no"));
          cnt++;
        }
      }

      $('#scanner-count').val(cnt);
      var type = $(this).data('type');
      if (!type) {
        $('#barcode-selector').fadeIn();
        return;
      }
      if (type === 'native') {
        $('#barcode-scanner-opener').click();
      } else if (type === 'pda') {
        $('#pda-scanner-opener').click();
      }
    });

    function barNumSave() {
      var barcode_arr = [];
      var error_arr = [];
      var str_error_arr = [];
      var $ipt_error = null;
      var isDuplicated = false;

      notallLengthCheck();

      $('.imfomation_box .li_box').each(function() {
          var empty_count = 0;
          var temp_arr = [];
          $(this).find('.inputbox li input').each(function() {
              if ($(this).val() != "") {
                if (/^-?\d+$/.test($(this).val())) {
                  temp_arr.push($(this).val())
                }
                else {
                  str_error_arr.push($(this));
                }
              } else {
                  if(!$ipt_error)
                    $ipt_error = $(this);
                  empty_count++;
              }
          });
          barcode_arr.push(temp_arr);
          
          if(empty_count !== 0) {
              // 바코드가 일부만 입력되어있는 경우
              error_arr.push($(this).find('.p1 .span1').text().replace(/(\\n|\s\s)/g, ''));
          } else {
              if(error_arr.length === 0)
                $ipt_error = null;
          }
      });

      //숫자만 입력되었는지 체크 로직 추가 211103
      if (str_error_arr.length > 0) {
        alert( '바코드는 숫자만 입력 가능합니다.' );
        str_error_arr[0].focus();
        return false;
      }

      if(error_arr.length > 0) {
        alert( error_arr.join(', ') + ' 품목의 모든 바코드가 입력되지 않아 저장할 수 없습니다.' );
        return false;
        
        /*
        // 23.01.16 : 서원 - 물류팀 재확인!! 인적 실수를 줄이고자 해당기능 오픈 금지!!
        let empty_item = error_arr.join(', ');
        if(confirm(empty_item + ' 상품 바코드가 비어있습니다.\n계속 진행하시겠습니까?')) {
        }
        else {
          $ipt_error.focus();
          return false;
        }
        */
      }
      
      barcode_arr.forEach(function(arr) {
          if (isDuplicate(arr)) {
              isDuplicated = true;
          }
      });

      if (isDuplicated) {
          alert("입력하신 바코드 중 중복 값이 있습니다.");
          return false;
      }

      need_reload = true;

      var ordId = "<?=$od["ordId"]?>";
      var changeStatus = true;
      var insertBarCnt = 0;

      /* 210319 배송정보 저장 */
      $.ajax({
        url : "./samhwa_orderform_deliveryInfo_update.php",
        type : "POST",
        async : false,
        data : $("#submitForm").serialize()
      });

      var prodsList = {};
      var flag=false;
      $.each(stoldList, function(key, value) {
        if($("." + value.stoId).val()&&$("." + value.stoId).val().length !=12){ flag =true;}
        prodsList[key] = {
          stoId : value.stoId,
          prodId : value.prodId,
          prodBarNum : ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
        }
        if($("." + value.stoId).val()){
          insertBarCnt++;
        }
      });
      if(flag){ alert('바코드는 12자리를 입력해주세요.'); return false; }

      var pass = {};
      $.each($('.chk_pass_barcode'), function(index, value) {
        pass[$(this).data('ct-id')] = $(this).is(":checked");
      });

      var sendData = {
        usrId : "<?=$od["mb_id"]?>",
        prods : prodsList,
        entId : "<?=get_ent_id_by_od_id($od_id)?>",
        pass: pass,
      }

      $.ajax({
        url : "./samhwa_orderform_stock_update.php",
        type : "POST",
        async : false,
        data : sendData,
        success : function(result){
          result = JSON.parse(result);
          if(result.errorYN == "Y") {
            alert(result.message);
          } else {
            alert("저장이 완료되었습니다.");
            //cart 기준 barcode insert update
          }
        }
      });

      $.ajax({
        url : "<?=G5_SHOP_URL?>/ajax.ct_barcode_insert.php",
        type : "POST",
        async : false,
        data : {
          od_id : "<?=$od_id?>",
          cnt : insertBarCnt
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
          console.log(result);
        }
      });

      // 미재고 바코드 처리
      var toApproveBarcodeArr = [];
      $('.folding_box').each(function () {
        $(this).find('li').find('img.barcode_icon.type5.active').each(function () {
          toApproveBarcodeArr.push({
            ct_id: $(this).closest('.folding_box').data('id'),
            barcode: $(this).closest('li').find('.frm_input').val(),
          });
        })
      });

      if (toApproveBarcodeArr.length > 0) {
        $.ajax({
          url: '/shop/ajax.ct_barcode_insert_not_approved.php',
          type: 'POST',
          data: {
            toApproveBarcodeArr: toApproveBarcodeArr
          },
          dataType: 'json',
          async: false
        })
        .done(function(result) {
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        })
      }

      member_cancel();
    }


    $(document).on('click', '.barcode_icon.type5.active', function() {
      var barcode = $(this).closest('li').find('.frm_input').val();
      var ct_id = $(this).closest('.folding_box').data('id');

      showBarcodeHistory(barcode, ct_id);
    });


    // 23.06.14 : 신규 앱 카메라 기능 동작 예외처리.
    if (window.ReactNativeWebView) { $(".nativePopupOpenBtn").show(); }


  });


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
      url : "/shop/ajax.member_cancel.php",
      type : "POST",
      async : false,
      data : {
        od_id : "<?=$od_id?>"
      },
      success : function(result) {

        var params = getUrlParams();
        delete params.od_id;
        delete params.ct_id;
        var query_string = decodeURI($.param(params));

        location.href = "<?=G5_SHOP_URL?>/release_orderlist.php?" + query_string;
      }
    });
  }

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

  function showBarcodeHistory(barcode, ct_id) {
    var data = null;

    $('#barcodeHistory .header .barcode').empty();
    $('#barcodeHistory .content ul').empty();

    $.ajax({
      url: './ajax.barcode_history.php',
      type: 'POST',
      async: false,
      data: {
        barcode: barcode,
        ct_id: ct_id,
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
