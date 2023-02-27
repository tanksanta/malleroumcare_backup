<?php
include_once("./_common.php");

$g5["title"] = "주문 내역 바코드 수정";
// include_once(G5_ADMIN_PATH."/admin.head.php");

$sql = (" SELECT od.*,
                ( SELECT COUNT(*) FROM g5_shop_cart WHERE od_id = od.od_id ) AS more_totalCnt,
                ( SELECT it_name FROM g5_shop_cart WHERE od_id = od.od_id ORDER BY it_id ASC LIMIT 0, 1 ) AS more_it_name
          FROM {$g5['g5_shop_order_table']} od
          WHERE od_id = '$od_id'
");
$od = sql_fetch($sql);
$prodList = [];
$prodListCnt = 0;
$prodListCnt2 = 0;
$deliveryTotalCnt = 0;

if (!$od['od_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
} else {
  $sto_imsi="";
  $_ct_barcode = [];

  $sql_ct = " select `ct_id`, `stoId`, `ct_barcode` from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
  $result_ct = sql_query($sql_ct);

  while($row_ct = sql_fetch_array($result_ct)) {
    $sto_imsi .= $row_ct['stoId'];

    if( $row_ct['ct_barcode'] )
      $_ct_barcode[ $row_ct['ct_id'] ] = json_decode( $row_ct['ct_barcode'], true);
  }

  $stoIdDataList = explode('|',$sto_imsi);
  $stoIdDataList = array_filter($stoIdDataList);
  $stoIdData = implode("|", $stoIdDataList);
  $res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
    'stoId' => $stoIdData
  ));
  $result_again = $res['data'];
}

$carts = get_carts_by_od_id($od_id, null, null, "a.ct_combine_ct_id, a.ct_id");

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    .imfomation_box > div { width: 100%; }
    .imfomation_box > div > li { width: 100%; padding: 20px; border-bottom: 1px solid #DDD; }
    .imfomation_box div .li_box{ width:100%; height:auto;text-align:center;}
    .imfomation_box div .li_box .li_box_line1{ width: 100%; height:auto; margin:auto; color:#000; }
    .imfomation_box div .li_box .li_box_line1 .p1{ width:100%; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
    .imfomation_box div .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
    .imfomation_box div .li_box .li_box_line1 .p1 .span1{ font-size: 18px; word-break: keep-all; width: 60%; }
    /* .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; } */
    .imfomation_box div .li_box .li_box_line1 .p1 .span2{ width: 120px; font-size:14px; text-align: right; }
    .imfomation_box div .li_box .li_box_line1 .p1 .span2 img{ width: 13px; margin-left: 15px; vertical-align: middle; top: -1px; }
    .imfomation_box div .li_box .li_box_line1 .p1 .span2 .up{ display: none;}
    .imfomation_box div .li_box .li_box_line1 .p1 .span3 { text-align:right; font-size:0.8em; color:#9b9b9b; }
    .imfomation_box div .li_box .li_box_line1 .p1 .span3 label { color: #000; }
    .imfomation_box div .li_box .li_box_line1 .p1 .span3 input { vertical-align:middle; }
    .imfomation_box div .li_box .li_box_line1 .cartProdMemo { width: 100%; font-size: 13px; margin-top: 2px; text-align: left; color: #FF690F; }
    /* display:none; */
    .imfomation_box div .li_box .folding_box{position: relative; text-align: center; vertical-align:middle; width:100%; padding-top: 20px; display:none; box-sizing: border-box; }
    .imfomation_box div .li_box .folding_box > span { display: block; width: 100%; }
    .imfomation_box div .li_box .folding_box > span:after { display: block; content: ''; clear: both; }
    .imfomation_box div .li_box .folding_box > .inputbox { width: 100%; position: relative; padding: 0; }
    .imfomation_box div .li_box .folding_box > .inputbox > li { width: 100%; position: relative; }

    .imfomation_box div .li_box .folding_box > .inputbox > li > .frm_input { width: 90%; height: 40px; padding-right: 85px; box-sizing: border-box; padding-left: 20px; font-size: 17px; border: 1px solid #E4E4E4; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > .frm_input.active { border-color: #FF5858; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > .frm_input::placeholder { font-size: 16px; color: #AAA; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > .check { float: left; width: 10%; height: 40px; box-sizing: border-box; border: 1px solid #E4E4E4; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > img { position: absolute; width: 30px; right: 15px; top: 11px; z-index: 2; cursor: pointer; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > i { position: absolute; right: 55px; top: 10px; z-index: 2; font-size: 19px; color: #FF6105; opacity: 0; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > i.active { opacity: 1; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > .overlap { position: absolute; right: 55px; top: 10px; z-index: 2; font-size: 14px; color: #DC3333; opacity: 0; font-weight: bold; }
    .imfomation_box div .li_box .folding_box > .inputbox > li > .overlap.active { opacity: 1; }

    .imfomation_box div .li_box .folding_box .span{ width:100%; height:50px; }
    .imfomation_box div .li_box .folding_box .all{margin-bottom:5px;padding-left :20px;font-size:17px;text-align:left;float:left;height:40px;width:40%; border-radius: 6px; background-color:#c0c0c0;  color:#fff; border:0px; box-sizing: border-box; }
    .imfomation_box div .li_box .folding_box .all::placeholder{color:#fff;}

    .imfomation_box div .li_box .folding_box .span .check_all_txt { position: absolute; float: left; width: 10%; left: 0px; top:5px; font-size: 12px; }
    .imfomation_box div .li_box .folding_box .span .check { float: left; width: 10%; height: 40px; box-sizing: border-box; border: 1px solid #E4E4E4; }


    .imfomation_box div .li_box .folding_box .barNumCustomSubmitBtn{float:left;margin-left:10px;color:#fff;font-size:17px;background-color:#494949; border:0px;border-radius: 6px;width:18%; height:40px; font-weight: bold; cursor: pointer; }
    .imfomation_box div .li_box .folding_box .barNumGuideOpenBtn{float:left; position: relative; margin-left:10px; width:35px; cursor: pointer; top: 3px; }
    .imfomation_box div .li_box .folding_box .btn_del{float:right;margin-left:10px;color:#fff;font-size:14px;background-color:coral; border:0px;border-radius: 6px; width:10%; height:40px; font-weight: bold;  cursor: pointer; }

    .imfomation_box div .li_box .folding_box .notall{
      margin-bottom:5px;font-size:20px;text-align:left;height:50px;width:90%; border-radius: 6px; background-color:#fff;  color:#666666; border:0px; ; border: 1px solid #c0c0c0;;
      /* background-image : url('<?php echo G5_IMG_URL?>/bacod_img.png');  */
      /* background-position:top right;  */
      /* background-repeat:no-repeat; */


    }
    .imfomation_box div .li_box .deliveryInfoWrap { width: 100%; position: relative; background-color: #F1F1F1; border-radius: 5px; padding: 10px; margin-top: 15px; }
    .imfomation_box div .li_box .deliveryInfoWrap:after { display: block; content: ''; clear: both; }
    .imfomation_box div .li_box .deliveryInfoWrap > select { width: 34%; height: 40px; float: left; margin-right: 1%; border: 1px solid #DDD; font-size: 17px; color: #666; padding-left: 10px; border-radius: 5px; }
    .imfomation_box div .li_box .deliveryInfoWrap > input[type="text"] { width: 65%; height: 40px; float: left; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 40px 0 10px; border-radius: 5px; }
    .imfomation_box div .li_box .deliveryInfoWrap > img { position: absolute; width: 30px; right: 15px; top: 50%; margin-top: -15px; z-index: 2; cursor: pointer; }

    /* 고정 하단 */
    #popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }

    /* 바코드 순차입력 버튼 */
    .imfomation_box div .li_box .folding_box > .inputbox > li > .barcode_add { width:35px; height:35px; position: absolute; top: 4px; right: 125px; display:none; }

    .imfomation_box div .li_box .folding_box > .inputbox > li .barcode_icon.type5 {
      position: absolute;
      width: 20px;
      right: 92px;
      top: 10px;
      z-index: 2;
      opacity: 0;
    }

    .imfomation_box div .li_box .folding_box > .inputbox > li .barcode_icon.type5.active {
      opacity: 1;
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

    .barcode_warning {
      display: none;
      font-size: 14px;
      text-align: left;
      padding: 10px 5px;
    }

    .barcode_block {
      display: none;
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.25);
      color: #fff;
      font-size: 18px;
      font-weight: bold;
      z-index: 10;
      cursor: default;
    }

    .barcode_block.active {
      display: block;
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

    .barcode_approve_wrapper {
      text-align: left;
      margin-bottom: 12px;
      color: #f00;
      font-size: 14px;
      display: none;
      cursor: auto;
      padding-left:80px;
    }

    .barcode_approve_wrapper > div { display: none; }
    .barcode_approve_wrapper_del > div { display: none; }
    .barcode_approve_wrapper_del { text-align: left; margin-bottom: 12px; color: #f00; font-size: 14px; display: none; cursor: auto; padding-left:80px; }

    .barcode_approve_wrapper button {
      border: 1px solid #f00;
      padding: 3px 7px;
      background: #fff;
      border-radius: 5px;
      color: #f00;
      margin-left: 10px;
      cursor: pointer;
    }

    .imfomation_box div .li_box .folding_box > .inputbox > li .padding0 {
      padding-left:0px;
    }
  </style>

  <?php if ($is_admin != 'super') { ?>
  <style>
    .barcode_approve_wrapper button {
      display: none;
    }
  </style>
  <?php } ?>
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
    <?php if (!$_GET['partner']) { ?>
      <a href="./popup.prodBarNum.form.excel.php?od_id=<?=$od_id?>" class="excel_btn">엑셀다운로드</a>
    <?php } ?>

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

        for($k = 0; $k < count($options); $k++) {
          $stoId_v=[];
          $stoId_v = explode('|',$options[$k]['stoId']);
          $stoId_v=array_filter($stoId_v);

          # 요청사항
          $prodMemo = ($prodMemo) ? $prodMemo : $carts[$i]["prodMemo"];
          # 카테고리 구분
          $gubun = $cate_gubun_table[substr($options[$k]['ca_id'], 0, 2)];
      ?>
      <div href="javascript:void(0)" class="<?= ($options[$k]['ct_status'] !== "취소" && $options[$k]['ct_status'] !== "주문무효") || $_GET['partner'] ? "" : "hide_area" ?> ">
        <li class="li_box">
          <div class="li_box_line1"
            <?php if ($gubun != '02' && $options[$k]['io_type'] == 0) { ?>
              onclick="openCloseToc(this)"
            <?php } ?>
            >
            <p class="p1" data-qty="<?=$options[$k]["ct_qty"]?>">
              <span class="span1">
                <!-- 상품명 -->
                <?php echo $options[$k]['io_type'] == 1 ? '[추가옵션] ' : ''; ?>
                <?=stripslashes($carts[$i]["it_name"])?>
                <!-- 옵션 -->
                <?php if($carts[$i]["it_name"] != $options[$k]["ct_option"]){ ?>
                (<?=$options[$k]["ct_option"]?>)
                <?php } ?>
                <label style="font-size:12px; margin-left:10px;">
                  <input type="checkbox" class="update_ct_status_to_delivery" name="update_ct_status_to_delivery" value="0" data-ct-id="<?php echo $carts[$i]["ct_id"]?>" data-ct-status="<?php echo $carts[$i]["ct_status"]?>" > 출고완료단계로 변경
                </label>
              </span>
              <?php if ($gubun != '02' && $options[$k]['io_type'] == 0) { ?>
              <span class="span2">
                <?php
                $add_class="";
                for($b = 0; $b< $options[$k]["ct_qty"]; $b++){
                  $add_class=$add_class.' '.$stoIdDataList[$prodListCnt2].'_v';
                  $prodListCnt2++;
                }
                ?>
                <span class="<?=$add_class?> c_num">0</span>/<?=$options[$k]["ct_qty"]?>
                <img class="up" src="<?=G5_IMG_URL?>/img_up.png" alt="">
                <img class="down" src="<?=G5_IMG_URL?>/img_down.png" alt="">
              </span>
              <?php } else { ?>
                <span class="span3" style="font-size:1em;">
                  <?php echo $gubun == '02' ? '비급여' : '추가옵션'; ?> 상품 바코드 미입력(<?=$options[$k]["ct_qty"]?>개)&nbsp;
                  <span class="outline">
                    <input
                      type="checkbox"
                      name="chk_pass_barcode_<?php echo $options[$k]['ct_id']; ?>"
                      value="1"
                      id="chk_pass_barcode_<?php echo $options[$k]['ct_id']; ?>"
                      <?php if ($options[$k]['ct_qty'] == $options[$k]['ct_barcode_insert']) { ?>
                        checked="checked"
                      <?php } ?>
                      class="chk_pass_barcode"
                      data-gubun="<?=$gubun?>"
                      data-ct-id="<?php echo $options[$k]['ct_id']; ?>"
                    >
                    <label for="chk_pass_barcode_<?php echo $options[$k]['ct_id']; ?>">확인함</label>
                  </span>
                </span>
              <?php } ?>
            </p>
            <?php if($prodMemo){ ?>
            <p class="cartProdMemo"><?=$prodMemo?></p>
            <?php } ?>
          </div>

          <?php if ($gubun != '02') { ?>
          <div class="folding_box id_<?php echo $options[$k]['ct_id']; ?>" data-id="<?php echo $options[$k]['ct_id']; ?>">
            <?php if ($options[$k]["ct_qty"] >= 2) { ?>
            <div class="span">

              <span class="check_all_txt">전체</span>
              <input type='checkbox' name='checks_all[]' id="ck_<?php echo $options[$k]['ct_id']; ?>" class="check ck_all">

              <input type="text" class="all frm_input" placeholder="일괄 등록수식 입력">
              <button type="button" class="barNumCustomSubmitBtn">등록</button>
              <img src="<?php echo G5_IMG_URL?>/ask_btn.png" alt="" class="barNumGuideOpenBtn" onclick="showPopup(true)">
              <button type="button" class="btn_del" id="btn_delete" data-id="ck_<?php echo $options[$k]['ct_id']; ?>">선택 삭제</button>
              </div>
            <?php } ?>
            <ul class="inputbox">

            <?php for ($b = 0; $b< count($stoId_v); $b++) { ?>
              <li>
                <?php if ($options[$k]["ct_qty"] >= 2) { ?>
                <input type='checkbox' name='checks[]' id="<?=$stoId_v[$b]?>" class="check ck_<?php echo $options[$k]['ct_id']; ?>" data-ck="ck_<?php echo $options[$k]['ct_id']; ?>">
                <?php } ?>
                <input type="text" maxlength="12" oninput="maxLengthCheck(this)" value="<?=$prodList[$b]["prodBarNum"]?>" class="notall frm_input frm_input_<?=$prodListCnt?> required prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoId_v[$b]?>" placeholder="바코드를 입력하세요." data-frm-no="<?=$prodListCnt?>" data-delete="" maxlength="12" onfocus="this.select()" style="<?=($options[$k]["ct_qty"]<=1)?"width:100%;":""?>">
                <img src="<?php echo G5_IMG_URL?>/bacod_add_img.png" class="barcode_add">
                <i class="fa fa-check"></i>
                <span class="overlap">중복</span>
                <img class="barcode_icon type5" src="/img/barcode_icon_3.png" alt="등록불가 (미보유재고)">

                <img src="<?php echo G5_IMG_URL?>/bacod_img.png" class="nativePopupOpenBtn" data-code="<?=$b?>" data-ct-id="<?php echo $ct['ct_id']; ?>" data-it-id="<?php echo $ct['it_id']; ?>" data-pd-code="<?php echo $ct['prodpaycode']; ?>">
                <div class="barcode_approve_wrapper <?=(($options[$k]["ct_qty"] >= 2)?"":"padding0")?>">
                  <div class="type1">
                    └ 미재고 바코드 입력 <button type="button" onclick="approveBarcode(this)" data-request_id="0">관리자 권한으로 출고 승인</button>
                  </div>
                  <div class="type2">
                    └ 미 재고 바코드 관리자 권한으로 승인됨
                  </div>
                </div>
                <div class="barcode_approve_wrapper_del <?=(($options[$k]["ct_qty"] >= 2)?"":"padding0")?>">
                  └ 출고된 바코드 사업소에서 !!삭제!! 처리됨
                </div>
              </li>
              <?php $prodListCnt++; } ?>
            </ul>

            <p class="barcode_warning">
              <span style="color: red">(주의)</span> 재고가 없는 바코드가 있습니다. 관리자 승인 시 정상 등록 됩니다.
            </p>

            <div class="barcode_block <?php echo in_array($options[$k]['ct_status'], ['배송', '완료']) ? 'active' : '' ?>">
              <div class="flex-row justify-center align-center" style="width: 100%; height: 100%">
                <p>출고완료 상태에서는 바코드 변경이 불가능합니다.</p>
              </div>
            </div>
          </div>
          <?php } ?>

          <div class="deliveryInfoWrap">
            <?php if ($options[$k]['ct_combine_ct_id']) { ?>
            <?php
            // 합포 상품 찾기
            foreach($carts as $c) {
              foreach($c['options'] as $o) {
                if($options[$k]['ct_combine_ct_id'] === $o['ct_id']) {
                  echo stripslashes($c["it_name"]);
                  if($c["it_name"] != $o["ct_option"]){
                    echo '(' . $o["ct_option"] . ')';
                  }
                  echo ' 상품과 같이 배송 됩니다.';
                }
              }
            }
            ?>
            <?php } else { ?>
            <input type="hidden" name="ct_id[]" value="<?=$options[$k]["ct_id"]?>">
            <select name="ct_delivery_company_<?=$options[$k]["ct_id"]?>" class="ct_delivery_company">
              <?php foreach($delivery_companys as $data){ ?>
              <option value="<?=$data["val"]?>" <?=($options[$k]["ct_delivery_company"] == $data["val"]) ? "selected" : ""?>><?=$data["name"]?></option>
              <?php } ?>
            </select>
              <?php if (!$_GET['partner']) { ?>
                <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" value="<?=$options[$k]["ct_delivery_num"]?>" name="ct_delivery_num_<?=$options[$k]["ct_id"]?>" placeholder="송장번호 입력">
                <img src="<?=G5_IMG_URL?>/bacod_img.png" class="nativeDeliveryPopupOpenBtn">
              <?php } ?>
              <?php if ($_GET['partner']) { ?>
                <input type="text" value="<?=$options[$k]['ct_delivery_num_name']?>" name="ct_delivery_num_name_<?=$options[$k]['ct_id']?>" class="ct_delivery_num_name" placeholder="담당자명" style="width: 25%">
                <input type="text" value="<?=$options[$k]["ct_delivery_num"]?>" name="ct_delivery_num_<?=$options[$k]["ct_id"]?>" class="ct_delivery_num" placeholder="송장번호/연락처 입력">
              <?php } ?>
            <?php } ?>
          </div>
        </li>
      </div>
      <?php
        }
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
    $(".hide_area").hide();

    var LOADING = false;
    var keyupTimer;
    var IS_POP = <?php echo $is_pop ? 'true' : 'false'?>;


    $(document).on('keyup', '.notall', function () {

      if( $(this).val().length <= 7 ) return;

      var last_index = $(this).closest('ul').find('li').last().index();
      var this_index = $(this).closest('li').index();

      $(this).closest('ul').find('.barcode_add').hide();
      if(last_index !== this_index && $(this).val().length == 12)
        $(this).closest('li').find('.barcode_add').show();

      if(keyupTimer) clearTimeout(keyupTimer);
      keyupTimer = setTimeout(notallLengthCheck, 100);

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


        var _check = "Y";
        if( !confirm("바코드 번호를 연속으로 적용 하시겠습니까?\n\n[확인] : 연속 적용\n[취소] : 빈칸 적용") ) {
          _check = "N";
        }


        for(var i = li_num+1; i<=li_last; i++){
          //p_num++;
          //$(ul).find('li').eq(i).find('.notall').val( (parseInt( li_val )+p_num) );

          if( (_check==="Y") ) {
            p_num++;
            // 연번 입력
            $(ul).find('li').eq(i).find('.notall').val( (parseInt( li_val )+p_num) );
          } else {

            // 비어 있는 칸에만 연번 입력
            if( !$(ul).find('li').eq(i).find('.notall').val() ) {
              p_num++;
              $(ul).find('li').eq(i).find('.notall').val( (parseInt( li_val )+p_num) );
            }
          }



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

      // 이벤트가 발생한 상품의 ct_id를 찾아 해당 상품에서만 검색 진행. (현재는 팝업장 내용 전체 검색인 부분을 검색 제한 한정.)

      $foldingBox.find("i").removeClass("active");
      $foldingBox.find(".overlap").removeClass("active");

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

        var ct_id = $(this).data('id');
        validateBarcodeBulk(ct_id);
        checkApprovedBarcodeBulk(ct_id);
      });
    }


    function validateBarcodeBulk(ct_id) {
      var barcodeArr = [];

      $('.folding_box.id_' + ct_id + ' li').each(function () {
        if ($(this).find('.frm_input').val().length === 12) {
          barcodeArr.push({
            ct_id: ct_id,
            index: $(this).index(),
            barcode: $(this).find('.frm_input').val(),
          });
        }
      })

      if (barcodeArr.length > 0) {
        $('.folding_box.id_' + ct_id + ' .barcode_icon.type5').removeClass('active');
        $('.folding_box.id_' + ct_id + ' .barcode_warning').hide();

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
          //console.log(result.data);
          var target = $('.folding_box.id_' + ct_id + ' li');
          var activeCount = 0;

          result.data.barcodeArr.forEach(function (_this) {
            if (_this.status === '미보유재고') {
              target.eq(_this.index).find('.fa-check').removeClass('active');
              target.eq(_this.index).find('.barcode_icon.type5').addClass('active');
              activeCount++;
            }
          });

          if (activeCount > 0) {
            $('.folding_box.id_' + ct_id + ' .barcode_warning').show();
          }
        })
        .fail(function($xhr) {
          // msgResult = 'error'
          var data = $xhr.responseJSON;
          console.warn(data && data.message);
          // alert('바코드 재고 확인 도중 오류가 발생했습니다. 관리자에게 문의해주세요.');
        })
      }
    }


    function checkApprovedBarcodeBulk(ct_id) {
      $('.folding_box.id_' + ct_id + ' .barcode_approve_wrapper').hide();
      $('.folding_box.id_' + ct_id + ' .barcode_approve_wrapper > div').hide();

      var barcodeArr = [];

      $('.folding_box.id_' + ct_id + ' li').each(function () {
        if ($(this).find('.frm_input').val().length === 12) {
          barcodeArr.push({
            ct_id: ct_id,
            index: $(this).index(),
            barcode: $(this).find('.frm_input').val(),
          });
        }
      })

      if (barcodeArr.length > 0) {
        $.ajax({
          url: './ajax.check_approved_barcode_bulk.php',
          type: 'POST',
          data: {
            ct_id: ct_id,
            barcodeArr: barcodeArr,
          },
          // dataType: 'json',
          async: false,
        })
        .done(function(result) {
          //console.log(result.data);
          var target = $('.folding_box.id_' + ct_id + ' li');
          result.data.barcodeArr.forEach(function (_this) {
            if (_this.status === '승인요청') {
              target.eq(_this.index).find('.barcode_approve_wrapper').show();
              target.eq(_this.index).find('.barcode_approve_wrapper .type1').show();
              target.eq(_this.index).find('.barcode_approve_wrapper .type1 button').attr('data-request_id', _this.request_id)
            }

            if (_this.status === '승인') {
              target.eq(_this.index).find('.barcode_approve_wrapper').show();
              target.eq(_this.index).find('.barcode_approve_wrapper .type2').show();
            }
          });
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          console.warn(data && data.message);
          // alert('바코드 재고 확인 도중 오류가 발생했습니다. 관리자에게 문의해주세요.');
        })
      }
    }


    function approveBarcode(_this) {
      if (!confirm('출고 승인 시 입력한 바코드는 재고로 등록 된 후 출고됩니다. 승인하시겠습니까?')) {
        return;
      }

      var requestId = $(_this).data('request_id');

      $.ajax({
        url: './ajax.approve_barcode.php',
        type: 'POST',
        data: {
          request_id: requestId,
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
    $('.ct_delivery_company').each(function() {
      changeDeliveryCompany.call(this);
    });

    $('.ct_delivery_company').change(function() {
      changeDeliveryCompany.call(this);
    });

    <?php

    $_barcode_list =[];
    if( $_ct_barcode ) {
      foreach ($_ct_barcode as $key => $val) { foreach ($val as $key2 => $val2) { $_barcode_list[ $key2 ] = $val2; } }
    }

    $stock_list = [];
    if ($result_again && count($result_again)) {
      foreach ($result_again as $stock) {
        $stock_list[] = array(
          'prodId' => $stock['prodId'],
          'stoId' => $stock['stoId'],
          'prodBarNum' => $stock['prodBarNum']
        );

        if( $_barcode_list[ $stock['stoId'] ] == $stock['prodBarNum'] )
          unset($_barcode_list[ $stock['stoId'] ]);

      }

      //print_r( $_barcode_list );

      if ($result_again && count($result_again)) {
        foreach ($_barcode_list as $key => $val) {
          $stock_list[] = array(
            'stoId' => $key,
            'prodBarNum' => $val,
            'del'=>'Y'
          );
        }
      }

    }
    ?>
    var stoldList = <?=json_encode($stock_list)?>;
    $.each(stoldList, function() {
      $('.' + this.stoId).val(this.prodBarNum);
      if( this.del && this.del=="Y" ) {
        //alert( this.prodBarNum );
        $('.' + this.stoId).parent().find('.barcode_approve_wrapper_del').show()
      }
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

    // 바코드 숫자만 입력되도록
    $('.inputbox li input').on('change paste keyup', function() {
      $(this).val($(this).val().replace(/[^0-9]/g,''));
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
          return false;
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
    $(".nativePopupOpenBtn").click(function(e) {
      var cnt = 0;
      var frm_no = $(this).closest("li").find(".frm_input").attr("data-frm-no");
      var item = $(this).closest("ul").find(".frm_input");
      sendBarcodeTargetList = [];

      cur_ct_id = $(this).data('ct-id');
      cur_it_id = $(this).data('it-id');
      cur_pdcode = $(this).data('pd-code');

      for(var i = 0; i < item.length; i++){
        if(!$(item[i]).val() || $(item[i]).attr("data-frm-no") == frm_no) {
          sendBarcodeTargetList.push($(item[i]).attr("data-frm-no"));
          cnt++;
        }
      }

      $('#scanner-count').val(cnt);
      $('#barcode-selector').fadeIn();
    });

    $('.update_ct_status_to_delivery').change(function() {
      var status = $(this).data('ct-status');

      if (status === '배송' || status === '완료') {
        alert('이미 출고완료 상태 입니다.');
        $(this).val(0);
        $(this).prop('checked', false);
        return;
      }

      $(this).val(0);
      var checked = $(this).is(":checked");
      if (checked) {
        $(this).val(1);
      }
    });

    var loading_barnumsave = false;
    function barNumSave() {
      var barcode_arr = [];
      var isDuplicated = false;
      var isNotNumber = false;

      $('.imfomation_box .li_box').each(function(){
          var temp_arr = [];
          $(this).find('.inputbox li input.frm_input').each(function(){
              var barcode = $(this).val();
              if (barcode != "") {
                  temp_arr.push($(this).val())
              }
              if(isNaN(barcode)) {
                isNotNumber = true;
              }
          });
          barcode_arr.push(temp_arr);
      });

      if(isNotNumber) {
        alert('입력하신 바코드 중 숫자가 아닌 값이 있습니다.');
        return false;
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

      if(loading_barnumsave) return;
      loading_barnumsave = true;

      /* 210319 배송정보 저장 */
      // 어드민 배송정보
      <?php if (!$_GET['partner']) { ?>
      $.ajax({
        url : "./samhwa_orderform_deliveryInfo_update.php",
        type : "POST",
        async : false,
        data : $("#submitForm").serialize()
      });
      <?php } ?>

      // 파트너 배송정보
      <?php if ($_GET['partner']) { ?>
      $.ajax({
        url : "/shop/ajax.partner_deliveryinfo.php",
        type : "POST",
        async : false,
        data : $("#submitForm").serialize(),
        dataType: 'json',
      });
      <?php } ?>

      var prodsList = {};
      var flag = false;
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
      if (flag) {
        alert('바코드는 12자리를 입력해주세요.');
        loading_barnumsave = false;
        return false;
      }

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

            //cart 기준 barcode insert update
            $.ajax({
              url : "<?=G5_SHOP_URL?>/ajax.ct_barcode_insert.php",
              type : "POST",
              async : false,
              data : {
                od_id : "<?=$od_id?>",
                cnt : insertBarCnt
              },
              success : function(){
                  var sendData_barcode = {
                      mb_id : "<?=$member["mb_id"]?>",
                      od_id : "<?=$_GET["od_id"]?>",
                      type : "<?=$_GET['partner']?>",
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
                }
            });


            loading_barnumsave = false;


            // 미재고 바코드 처리
            var toApproveBarcodeArr = [];
            $('.folding_box').each(function () {
              $(this).find('li').find('img.barcode_icon.type5.active').each(function () {

                var ct_id = $(this).closest('.folding_box').data('id');
                var barcode = $(this).closest('li').find('.frm_input').val();
                toApproveBarcodeArr.push({ ct_id: ct_id, barcode: barcode });
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


            // 출고완료 단계로 설정
            var ct_ids = [];
            $('.update_ct_status_to_delivery').each(function() {
              var status = $(this).data('ct-status');
              var checked = $(this).is(":checked");

              if ((status !== '배송' && status !== '완료')) {
                if (checked) {
                  ct_ids.push($(this).data('ct-id'));
                }
              }
            });


            if (ct_ids.length > 0) {
              $.ajax({
                url: "./ajax.cart_status.php",
                type: "POST",
                async: false,
                data: {
                  ct_id: ct_ids,
                  step: '배송',
                }
              });
            }


            alert("저장이 완료되었습니다.");


            if (window.opener != null && IS_POP) {
              opener.location.reload();
              window.close();
            }
            <?php if($no_refresh == 'partner') { ?>
            member_cancel();
            <?php } ?>
            // member_cancel();
          }
        },
        error: function() {
          loading_barnumsave = false;
        }
      });

      /*
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
      */

    }

    $(document).on('click', '.barcode_icon.type5.active', function() {
      var barcode = $(this).closest('li').find('.frm_input').val();
      var ct_id = $(this).closest('.folding_box').data('id');

      showBarcodeHistory(barcode, ct_id);
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
      },
      success : function(result) {
        <?php if($_GET['new']) { ?>
        history.back();
        <?php } else if($no_refresh == 'partner') { ?>
        foldingBoxSetting();
        $('.folding_box').each(function() {
          var ct_id = $(this).data('id');
          var cnt_txt = $(this).parent().find('.p1 .span2').text().trim().split('/');
          var $barcode = $(parent.document).find('.btn_barcode_info[data-id="'+ct_id+'"]');
          if(cnt_txt[0] !== cnt_txt[1]) {
            var txt = cnt_txt.join('/');
            if (txt) {
              $barcode.removeClass('disabled');
              $barcode.find('span').text(cnt_txt.join('/'));
            } else { // 상품바코드 미입력 체크일때
              if ($(this).parent().find('.p1 .chk_pass_barcode').is(":checked") == true) {
                $barcode.addClass('disabled');
                $barcode.find('span').text('입력완료');
              } else {
                $barcode.removeClass('disabled');
                $barcode.find('span').text(
                  "0/" + $(this).parent().find('.p1').data('qty')
                );
              }
            }
          } else {
            // 입력완료
            $barcode.addClass('disabled');
            $barcode.find('span').text('입력완료');
          }
        });
        $("body", parent.document).removeClass('modal-open');
        $("#popup_box", parent.document).hide();
        $("#popup_box", parent.document).find("iframe").remove();
        return;
        <?php } else { ?>
        <?php if ($no_refresh != 1) { ?>
        if (need_reload) {
          try {
            $("body", parent.document).reload();
          } catch(e) {}
          try {
            opener.location.reload();
          } catch(e) {}
        }
        <?php } ?>
        <?php if ($orderlist) { ?>
        foldingBoxSetting();
        $('.folding_box').each(function() {
          var ct_id = $(this).data('id');
          var cnt_txt = $(this).parent().find('.p1 .span2').text().trim().split('/');
          var $chk = $(opener.document).find('#check_' + ct_id);
          var $barcode = $chk.closest('tr').find('.prodBarNumCntBtn');

          if(cnt_txt[0] !== cnt_txt[1]) {
            var txt = cnt_txt.join('/');
            if (txt) {
              $barcode.removeClass('disable').text(cnt_txt.join('/'));
            } else { // 상품바코드 미입력 체크일때
              if ($(this).parent().find('.p1 .chk_pass_barcode').is(":checked") == true) {
                $barcode.addClass('disable').text('입력완료');
              } else {
                $barcode.removeClass('disable').text(
                  "0/" + $(this).parent().find('.p1').data('qty')
                );
              }
            }
          } else {
            // 입력완료
            $barcode.addClass('disable').text('입력완료');
          }
        });
        <?php } ?>
        window.close();
        try {
          $("body", parent.document).removeClass('modal-open');
          $("#popup_box", parent.document).hide();
          $("#popup_box", parent.document).find("iframe").remove();
        } catch (e) {
        }
        <?php }?>
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

  function changeDeliveryCompany() {
    var $li = $(this).closest('.deliveryInfoWrap');
    var $ct_delivery_num_name = $li.find('.ct_delivery_num_name');
    var $ct_delivery_num = $li.find('.ct_delivery_num');
    // 설치배송 선택시
    if($(this).val() === 'install') {
      $ct_delivery_num_name.show();
      $ct_delivery_num.addClass('install');
      $ct_delivery_num.attr('placeholder', '연락처 입력');
    } else {
      $ct_delivery_num_name.hide();
      $ct_delivery_num.removeClass('install');
      $ct_delivery_num.attr('placeholder', '송장번호 입력');
    }
  }

















  window.onload = function () {
      enableDragCheckGroup('input[name="checks[]"]');
  };

  // 체크 드래그 구현 (vanilla js)
  // 해당 셀렉터에 대한 함수화
  function enableDragCheckGroup(selector) {

      // 지역변수 : 체크박스 그룹이 체크되었는지 확인하기 위한 용도
      var checked = false;
      var hoveredElemCount = 0; // 한 개만 선택할때의 문제를 해결하기 위한 용도

      // 마우스를 떼면 체크상태 해제
      document.onmouseup = function () {
        if (checked === true) {
            if (hoveredElemCount === 1 && (event.target.nodeName === 'INPUT') ) {
                event.target.onmouseover(event);
            }
            checked = false;
        }
      }

      // 전체 체크박스 선택
      var checkboxes = document.querySelectorAll(selector);

      for(var i = 0; i < checkboxes.length; ++i) {
          var elem = checkboxes[i];

          elem.onmousedown = function (event) {
              checked = true;
              hoveredElemCount = 0;
              event.target.onmouseover(event);
          }
          elem.onmouseover = function (event) {
              if (checked === true)
                  event.target.checked = event.target.checked ? '' : 'checked';
                  hoveredElemCount++;
          }
      }
  }


  // 전체 선택 체크 버튼
  $("input[name='checks_all[]']").click(function() {

    var _id =  $(this).attr('id');

    if($(this).is(":checked")) { $("."+_id).prop("checked", true); }
    else { $("."+_id).prop("checked", false);}

  });


  // 개별선택 체크 박스
  $("input[name='checks[]']").click(function() {

    var _ck = $(this).data('ck');
    var total = $("." + _ck).length;
    var checked = $("." + _ck + ":checked").length;

    if(total != checked) { $("#" + _ck).prop("checked", false); }
    else { $("#" + _ck).prop("checked", true); }

  });


  // 선택 삭제 버튼
  $(".btn_del").click(function() {

    var _id =  $(this).data('id');
    var checked = $("." + _id + ":checked").length;

    if( !checked ) {
      alert("선택된 바코드가 없습니다.");
      return;
    }

    if( !confirm("선택된 바코드를 삭제 하시겠습니까?") ) {
      return;
    }

    $("." + _id + ":checked").each(function() {
      var _ck_id = $(this).attr('id');
      $("." + _ck_id).val("");
      $(this).prop("checked", false);

      $(this).closest('li').children('.barcode_add').css("display", "none");
      $(this).closest('li').children('i, .overlap, .barcode_icon.type5').removeClass("active");

    });

    $("#"+_id).prop("checked", false);
  });

</script>
<?php include_once( G5_PATH . '/shop/open_barcode.php'); ?>
</body>
