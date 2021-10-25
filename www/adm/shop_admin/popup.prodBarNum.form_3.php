<?php

include_once("./_common.php");

$g5["title"] = "주문 내역 바코드 수정";
// include_once(G5_ADMIN_PATH."/admin.head.php");

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = ( SELECT od_id FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '$ct_id') ";
$od = sql_fetch($sql);
$od_id = $od['od_id'];
$prodList = [];
$prodListCnt = 0;
$prodListCnt2 = 0;
$deliveryTotalCnt = 0;

if (!$od['od_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
} else {
  $sto_imsi="";
  $sql_ct = "SELECT `stoId` FROM {$g5['g5_shop_cart_table']}
  WHERE 
    od_id = '$od_id'
    AND (
      ct_id = '$ct_id'
      OR ct_combine_ct_id = '$ct_id'
      OR ct_id = ( SELECT ct_combine_ct_id FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '$ct_id' LIMIT 1 )
      OR ct_combine_ct_id = ( SELECT ct_combine_ct_id FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '$ct_id' LIMIT 1 )
    )
	ORDER BY ct_combine_ct_id, ct_id";
  $result_ct = sql_query($sql_ct);

  while($row_ct = sql_fetch_array($result_ct)) {
      $sto_imsi .=$row_ct['stoId'];
  }
  $stoIdDataList = explode('|',$sto_imsi);
  $stoIdDataList = array_filter($stoIdDataList);
  $stoIdData = implode("|", $stoIdDataList);
  $res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
    'stoId' => $stoIdData
  ), 443);
  $result_again = $res['data'];
}

$carts = [];
$sql = " select a.ct_id,
					a.it_id,
					a.it_name,
					a.cp_price,
					a.ct_notax,
					a.ct_send_cost,
					a.ct_sendcost,
					a.it_sc_type,
					a.pt_it,
					a.pt_id,
					b.ca_id,
					b.ca_id2,
					b.ca_id3,
					b.pt_msg1,
					b.pt_msg2,
					b.pt_msg3,
					a.ct_status,
					b.it_model,
					b.it_outsourcing_use,
					b.it_outsourcing_company,
					b.it_outsourcing_manager,
					b.it_outsourcing_email,
					b.it_outsourcing_option,
					b.it_outsourcing_option2,
					b.it_outsourcing_option3,
					b.it_outsourcing_option4,
					b.it_outsourcing_option5,
					a.pt_old_name,
					a.pt_old_opt,
					a.ct_uid,
					a.prodMemo,
					a.prodSupYn,
					a.ct_qty,
					a.ct_stock_qty,
					b.it_img1,
					a.ct_delivery_company,
					a.ct_delivery_num,
					a.ct_combine_ct_id,
					b.it_delivery_cnt,
					b.it_delivery_price,
					a.ct_delivery_cnt,
					a.ct_delivery_price,
					a.ct_is_direct_delivery,
          b.it_name,
          a.ct_status,
          a.io_type,
          a.stoId,
          ct_option,
          ct_barcode_insert
			  from {$g5['g5_shop_cart_table']} a 
        left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
        AND (
          ct_id = '$ct_id'
          OR ct_combine_ct_id = '$ct_id'
          OR ct_id = ( SELECT ct_combine_ct_id FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '$ct_id' LIMIT 1 )
          OR ct_combine_ct_id = ( SELECT ct_combine_ct_id FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '$ct_id' LIMIT 1 )
        )
			  order by a.ct_combine_ct_id, a.ct_id";

$result = sql_query($sql);

$combine_it_name = '';
for ($i=0; $row=sql_fetch_array($result); $i++) {
  $carts[] = $row;
  if ($i === 0) {
    $combine_it_name = $row['it_name'];
    if ($row['it_name'] != $row['ct_option']) {
      $combine_it_name .= "({$row['ct_option']})";
    }
  }

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
if($od["od_b_tel"]) {
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
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; }
    html, body { width: 100%; font-family: "Noto Sans KR", sans-serif; }
    body { padding-top: 60px; padding-bottom: 70px; }
    a { text-decoration: none; color: inherit; }
    ul, li { list-style: none; }
    button { border: 0; font-family: "Noto Sans KR", sans-serif; }
    input { font-family: "Noto Sans KR", sans-serif;  }

    /* 고정 상단 */
    #popupHeaderTopWrap { position: fixed; width: 100%; height: 60px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
    #popupHeaderTopWrap:after { display: block; content: ''; clear: both; }
    #popupHeaderTopWrap > div { height: 100%; line-height: 60px; }
    #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
    #popupHeaderTopWrap > .close { float: right; }
    #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

    /* 상품기본정보 */
    #itInfoWrap { width: 100%; padding: 20px; border-bottom: 1px solid #DFDFDF; }
    #itInfoWrap > .name { width: 100%; font-weight: bold; font-size: 17px; }
    #itInfoWrap > .name > .delivery { color: #FF690F; }
    #itInfoWrap > .date { width: 100%; font-size: 13px; color: #666; }
    #itInfoWrap > .deliveryInfo { width: 100%; border-radius: 5px; padding: 10px 15px; background-color: #F1F1F1; margin-top: 20px; }
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
    .imfomation_box{ margin:0px;width:100%;position:relative; padding:0px;display:block; width:100%; height:auto;  }
    .imfomation_box > a { width: 100%; }
    .imfomation_box > a > li { width: 100%; padding: 20px; border-bottom: 1px solid #DDD; }
    .imfomation_box a .li_box{ width:100%; height:auto;text-align:center;}
    .imfomation_box a .li_box .li_box_line1{ width: 100%; height:auto; margin:auto; color:#000; }
    .imfomation_box a .li_box .li_box_line1 .p1{ width:100%; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
    .imfomation_box a .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
    .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size: 18px; word-break: keep-all; width: 60%; }
    /* .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; } */
    .imfomation_box a .li_box .li_box_line1 .p1 .span2{ width: 120px; font-size:14px; text-align: right; }
    .imfomation_box a .li_box .li_box_line1 .p1 .span2 img{ width: 13px; margin-left: 15px; vertical-align: middle; top: -1px; }
    .imfomation_box a .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span3 {
      text-align:right;
      font-size:0.8em;
      color:#9b9b9b;
    }
    .imfomation_box a .li_box .li_box_line1 .p1 .span3 label {
      color: #000;
    }
    .imfomation_box a .li_box .li_box_line1 .p1 .span3 input {
      vertical-align:middle;
    }
    .imfomation_box a .li_box .li_box_line1 .cartProdMemo { width: 100%; font-size: 13px; margin-top: 2px; text-align: left; color: #FF690F; }
    /* display:none; */
    .imfomation_box a .li_box .folding_box{text-align: center; vertical-align:middle; width:100%; padding-top: 20px; display:none; box-sizing: border-box; }
    .imfomation_box a .li_box .folding_box > span { display: block; width: 100%; }
    .imfomation_box a .li_box .folding_box > span:after { display: block; content: ''; clear: both; }
    .imfomation_box a .li_box .folding_box > .inputbox { width: 100%; position: relative; padding: 0; }
    .imfomation_box a .li_box .folding_box > .inputbox > li { width: 100%; position: relative; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input { width: 100%; height: 50px; padding-right: 85px; box-sizing: border-box; padding-left: 20px; font-size: 17px; border: 1px solid #E4E4E4; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input.active { border-color: #FF5858; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .frm_input::placeholder { font-size: 16px; color: #AAA; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .btn_bacod { position: absolute; width: 30px; right: 50px; top: 11px; z-index: 2; cursor: pointer; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .btn_pda { position: absolute; width: 30px; right: 15px; top: 11px; z-index: 2; cursor: pointer; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > img { position: absolute; width: 30px; right: 15px; top: 11px; z-index: 2; cursor: pointer; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > i { position: absolute; right: 100px; top: 17px; z-index: 2; font-size: 19px; color: #FF6105; opacity: 0; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > i.active { opacity: 1; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .overlap { position: absolute; right: 55px; top: 15px; z-index: 2; font-size: 14px; color: #DC3333; opacity: 0; font-weight: bold; }
    .imfomation_box a .li_box .folding_box > .inputbox > li > .overlap.active { opacity: 1; }

    .imfomation_box a .li_box .folding_box .span{margin-left :20px;width:90%;}
    .imfomation_box a .li_box .folding_box .all{margin-bottom:5px;padding-left :20px;font-size:17px;text-align:left;float:left;height:50px;width:55%; border-radius: 6px; background-color:#c0c0c0;  color:#fff; border:0px; box-sizing: border-box; }
    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}
    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box a .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box a .li_box .folding_box .barNumCustomSubmitBtn{float:left;margin-left:10px;color:#fff;font-size:17px;background-color:#494949; border:0px;border-radius: 6px;width:18%; height:50px; font-weight: bold; }
    .imfomation_box a .li_box .folding_box .barNumGuideOpenBtn{float:left; position: relative; margin-left:10px;width:35px; cursor: pointer; top: 8px; }
    .imfomation_box a .li_box .folding_box .notall{
      margin-bottom:5px;font-size:20px;text-align:left;height:50px;width:90%; border-radius: 6px; background-color:#fff;  color:#666666; border:0px; ; border: 1px solid #c0c0c0;;
      /* background-image : url('<?php echo G5_IMG_URL?>/bacod_img.png');  */
      /* background-position:top right;  */
      /* background-repeat:no-repeat; */


    }
    .imfomation_box a .li_box .deliveryInfoWrap { width: 100%; position: relative; background-color: #F1F1F1; border-radius: 5px; padding: 10px; margin-top: 15px; }
    .imfomation_box a .li_box .deliveryInfoWrap:after { display: block; content: ''; clear: both; }
    .imfomation_box a .li_box .deliveryInfoWrap > select { width: 34%; height: 40px; float: left; margin-right: 1%; border: 1px solid #DDD; font-size: 17px; color: #666; padding-left: 10px; border-radius: 5px; }
    .imfomation_box a .li_box .deliveryInfoWrap > input[type="text"] { width: 65%; height: 40px; float: left; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 40px 0 10px; border-radius: 5px; }
    .imfomation_box a .li_box .deliveryInfoWrap > img { position: absolute; width: 30px; right: 15px; top: 50%; margin-top: -15px; z-index: 2; cursor: pointer; }

    /* 고정 하단 */
    #popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }
    /* 바코드 순차입력 버튼 */
    .imfomation_box a .li_box .folding_box > .inputbox > li > .barcode_add {
      width:35px;
      height:35px;
      position: absolute;
      top: 8px;
      right: 130px;
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
      [<?=($od["recipient_yn"] == "Y") ? "주문" : "재고"?>] <?=$moreInfo["it_name"]?> <?=$moreInfoDisplayCnt?>
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
        <div style="margin:20px;margin-bottom:0px;background-color:#ff6105;color:white;text-align:center;border-radius:3px;padding:10px;font-size:13px;">
        <?php
        echo stripslashes($combine_it_name);
        echo ' 상품과 같이 배송 됩니다.';
        ?>
        </div>
      <?php } ?>
      <a href="javascript:void(0)" class="<?= $carts[$i]['ct_status'] !== "취소" && $carts[$i]['ct_status'] !== "주문무효" ? "" : "hide_area" ?> ">
        <li class="li_box">
          <div class="li_box_line1"
            <?php if ($gubun != '02' && $carts[$i]['io_type'] == 0) { ?>
              onclick="openCloseToc(this)"
            <?php } ?>
            >
            <p class="p1" data-qty="<?=$carts[$i]["ct_qty"]?>">
              <span class="span1">
                <!-- 상품명 -->
                <?php echo $carts[$i]['io_type'] == 1 ? '[추가옵션] ' : ''; ?>
                <?=stripslashes($carts[$i]["it_name"])?>
                <!-- 옵션 -->
                <?php if($carts[$i]["it_name"] != $carts[$i]["ct_option"]){ ?>
                (<?=$carts[$i]["ct_option"]?>)
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
                <span class="span3">
                  <?php echo $gubun == '02' ? '비급여' : '추가옵션'; ?> 상품 바코드 미입력&nbsp;
                  <input 
                    type="checkbox"
                    name="chk_pass_barcode_<?php echo $carts[$i]['ct_id']; ?>"
                    value="1"
                    id="chk_pass_barcode_<?php echo $carts[$i]['ct_id']; ?>"
                    <?php if ($carts[$i]['ct_qty'] == $carts[$i]['ct_barcode_insert']) { ?>
                      checked="checked"
                    <?php } ?>
                    class="chk_pass_barcode"
                    data-ct-id="<?php echo $carts[$i]['ct_id']; ?>"
                  >
                  <label for="chk_pass_barcode_<?php echo $carts[$i]['ct_id']; ?>">확인함</label>
                </span>
              <?php } ?>
            </p>
            <?php if($prodMemo){ ?>
            <p class="cartProdMemo"><?=$prodMemo?></p>
            <?php } ?>
          </div>

          <?php if ($gubun != '02') { ?>
          <div class="folding_box" data-id="<?php echo $carts[$i]['ct_id']; ?>">
            <?php if ($carts[$i]["ct_qty"] >= 2) { ?>
            <span>
            <input type="text" class="all frm_input" placeholder="일괄 등록수식 입력">
            <button type="button" class="barNumCustomSubmitBtn">등록</button>
            <img src="<?php echo G5_IMG_URL?>/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)">
            </span>
            <?php } ?>
            <ul class="inputbox">
              <?php for ($b = 0; $b< count($stoId_v); $b++) { ?>
              <li>
                <input type="text" maxlength="12" oninput="maxLengthCheck(this)" value="<?=$prodList[$b]["prodBarNum"]?>"class="notall frm_input frm_input_<?=$prodListCnt?> required prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoId_v[$b]?>" placeholder="바코드를 입력하세요." data-frm-no="<?=$prodListCnt?>" maxlength="12">
                <img src="<?php echo G5_IMG_URL?>/bacod_add_img.png" class="barcode_add">
                <i class="fa fa-check"></i>
                <span class="overlap">중복</span>
                
                <img src="<?php echo G5_IMG_URL?>/bacod_img.png" class="nativePopupOpenBtn btn_bacod" data-type="native" data-code="<?=$b?>" data-ct-id="<?php echo $carts[$i]['ct_id']; ?>" data-it-id="<?php echo $carts[$i]['it_id']; ?>">
                <img src="<?php echo G5_IMG_URL?>/btn_pda.png" class="nativePopupOpenBtn btn_pda" data-type="pda" data-code="<?=$b?>" data-ct-id="<?php echo $carts[$i]['ct_id']; ?>" data-it-id="<?php echo $carts[$i]['it_id']; ?>">
              </li>
              <?php $prodListCnt++; } ?>
            </ul>
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
            <input type="text" value="<?=$carts[$i]["ct_delivery_num"]?>" name="ct_delivery_num_<?=$carts[$i]["ct_id"]?>" placeholder="송장번호 입력">
            <img src="<?=G5_IMG_URL?>/bacod_img.png" class="nativeDeliveryPopupOpenBtn">
          </div>
          <?php } ?>
        </li>
      </a>
      <?php
      }
      ?>
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
  <div id="popupFooterBtnWrap">
    <button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button type="button" class="cancelbtn" onclick="member_cancel();">취소</button>
  </div>

  <?php

  if(!$member['mb_id']){alert('접근이 불가합니다.');}
  //접속시 db- >id 부과
  sql_query("update {$g5['g5_shop_cart_table']} set `ct_edit_member` = '".$member['mb_id']."' where `od_id` = '{$od_id}'");
  ?>

  <script type="text/javascript">
    $(".hide_area").hide();

    var keyupTimer;
    
    $(".notall").keyup(function() {
      var last_index = $(this).closest('ul').find('li').last().index();
      var this_index = $(this).closest('li').index();

      $(this).closest('ul').find('.barcode_add').hide();
      if(last_index !== this_index && $(this).val().length == 12)
          $(this).closest('li').find('.barcode_add').show();

      if(keyupTimer) clearTimeout(keyupTimer);
      keyupTimer = setTimeout(notallLengthCheck, 200);
    });

    $('.notall').focus(function(){

        var last_index = $(this).closest('ul').find('li').last().index();
        var this_index = $(this).closest('li').index();

        $(this).closest('ul').find('.barcode_add').hide();
        if(last_index !== this_index && $(this).val().length == 12)
            $(this).closest('li').find('.barcode_add').show();
    });
    
    $('.barcode_add').click(function() {
        var ul = $(this).closest('ul');
        var li_num = $(this).closest('li').index();
        var li_val = $(this).closest('li').find('.notall').val();
        var li_last = $(ul).find('li').last().index();
        var p_num = 0;

        if(li_val.length !== 12){
            alert('바코드 12자리를 입력해주세요.');
            return false;
        }
        
        for(var i = li_num+1; i<=li_last; i++){
            p_num++;
            $(ul).find('li').eq(i).find('.notall').val( (parseInt( li_val )+p_num) );
        }
        notallLengthCheck();
    });



  var need_reload = false;
  //maxnum 지정
  function maxLengthCheck(object){
    if (object.value.length > object.maxLength){
      object.value = object.value.slice(0, object.maxLength);
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

  /* 바코드 입력글자 수 체크 */
  function notallLengthCheck() {
    var $foldingBox = $('.folding_box');

    $(".imfomation_box a .li_box .folding_box > .inputbox > li > i").removeClass("active");
    $(".imfomation_box a .li_box .folding_box > .inputbox > li > .overlap").removeClass("active");

    $foldingBox.each(function() {
      var $item = $(this).find('.notall');
      $item.removeClass("active");

      var dataTable = {};
      $item.each(function(i) {
        var $cur = $(this);
        var barcode = $cur.val();
        var length = barcode.length;
        if(length < 12 && length) {
          $cur.addClass("active");
        }
        if(length == 12) {
          $cur.parent().find("i").addClass("active");
          
          if(!dataTable[barcode])
            dataTable[barcode] = [];
          dataTable[barcode].push(i);
        }
      });

      var keys = Object.keys(dataTable);
      for(var i = 0; i < keys.length; i++) {
        var val = dataTable[keys[i]];
        if(val.length > 1) {
          for(var j = 0; j < val.length; j++) {
            var idx = val[j];
            $($item[idx]).parent().find("i").removeClass("active");
            $($item[idx]).parent().find(".overlap").addClass("active");
          }
        }
      }
    });
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
      success : function(result) {
        if(result.error == "Y") {
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

    notallLengthCheck();
    foldingBoxSetting();

    $(".nativeDeliveryPopupOpenBtn").click(function() {
      sendInvoiceTarget = $(this).parent().find("input[type='text']");

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
    });

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
            $.ajax({
              url : "<?=G5_SHOP_URL?>/ajax.ct_barcode_insert.php",
              type : "POST",
              async : false,
              data : {
                od_id : "<?=$od_id?>",
              }
            });
            $.ajax({
              url : "/shop/ajax.order.prodBarNum.cnt.php",
              type : "POST",
              async : false,
              data : {
                od_id : "<?=$od_id?>",
                cnt : insertBarCnt
              }
            });
            member_cancel();
          }
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
          if(barList[i].length!==12) {
            alert('바코드는 12자리 입력이 되어야합니다.');
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

    /* 210317 */
    $(".nativePopupOpenBtn").click(function(e){
      var cnt = 0;
      var frm_no = $(this).closest("li").find(".frm_input").attr("data-frm-no");
      var item = $(this).closest("ul").find(".frm_input");
      sendBarcodeTargetList = [];

      cur_ct_id = $(this).data('ct-id');
      cur_it_id = $(this).data('it-id');

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
  });


  //종료시 멤버 수정중없에기
  function member_cancel(){

    $.ajax({
      url : "/shop/ajax.member_cancel.php",
      type : "POST",
      async : false,
      data : {
        od_id : "<?=$od_id?>"
      }
    });
        
    $.ajax({
      url : "/shop/ajax.order.prodBarNum.cnt.php",
      type : "POST",
      async : false,
      data : {
        od_id : "<?=$od_id?>",
        cancel : "y"
      },
      success : function(result) {
        location.href = "<?=G5_SHOP_URL?>/release_orderlist.php";
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
</script>
<?php include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>