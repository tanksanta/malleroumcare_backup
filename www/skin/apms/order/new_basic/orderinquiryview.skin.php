<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/magnific-popup.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.wheelzoom.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.magnific-popup.js"></script>', 0);

// 목록헤드
if(isset($wset['ivhead']) && $wset['ivhead']) {
  add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ivhead'].'.css" media="screen">', 0);
  $head_class = 'list-head';
} else {
  $head_class = (isset($wset['ivcolor']) && $wset['ivcolor']) ? 'tr-head border-'.$wset['ivcolor'] : 'tr-head border-black';
}

// 헤더 출력
if($header_skin)
  include_once('./header.php');

// echo $_SERVER['HTTP_REFERER'];

// if(strpos($_SERVER['HTTP_REFERER'], 'orderform') !== false) {
// }

  $prodListCnt = 0;
  $prodList = [];

  if($od["ordId"]) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_URL, "https://system.eroumcare.com/api/pen/pen5000/pen5000/selectPen5000.do?ordId={$od["ordId"]}&uuid={$od["uuid"]}");
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    $result = $result["data"];

    if($result) {
      foreach($result as $data){
        $thisProductData = [];
        $thisProductData["prodId"] = $data["prodId"];
        $thisProductData["prodColor"] = $data["prodColor"];
        $thisProductData["prodBarNum"] = $data["prodBarNum"];
        $thisProductData["penStaSeq"] = $data["penStaSeq"];
        array_unshift($prodList, $thisProductData);
      }
    }
  } else {
    $stoIdData = $od["stoId"];
    $stoIdData = explode(",", $stoIdData);
    $stoIdDataList = [];
    foreach($stoIdData as $data){
      array_push($stoIdDataList, $data);
    }
    $stoIdData = implode("|", $stoIdDataList);
  }

  # 스킨경로
  $SKIN_URL = G5_SKIN_URL.'/apms/order/'.$skin_name;

# 수급자 주문일 시
if($od["od_penId"]) {
  $entData = sql_fetch("SELECT `mb_entId`, `mb_entNm`, `mb_email`, `mb_giup_boss_name`, `mb_giup_bnum`, `mb_entConAcc01`, `mb_entConAcc02` FROM `g5_member` WHERE mb_id = '{$od["mb_id"]}'");
  $res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $od["mb_id"],
    'entId' => $entData["mb_entId"],
    'penId' => $od["od_penId"]
  ));
  if(!$res["data"]) {
    alert('존재하지 않는 수급자에 대한 주문입니다.');
  }
  $penData = $res["data"][0];

  # 210324 수급자정보
  if(!$od["od_penLtmNum"]) {
    if($penData["penLtmNum"]) {
      sql_query("
        UPDATE {$g5["g5_shop_order_table"]} SET
          od_penLtmNum = '{$penData["penLtmNum"]}'
        WHERE od_id = '{$od["od_id"]}'
      ");
      $od["od_penLtmNum"] = $penData["penLtmNum"];
    }
  }

  # 200512 전자계약서
  $eform = [];
  $eform = sql_fetch("SELECT * FROM `eform_document` WHERE od_id = '{$od["od_id"]}'");
  if(!$eform['dc_id']) { // 전자계약서가 없을 경우

    $dcId = sql_fetch("SELECT REPLACE(UUID(),'-','') as uuid")["uuid"];

    sql_query("INSERT INTO `eform_document` SET
      `dc_id` = UNHEX('$dcId'),
      `dc_status` = '0',
      `od_id` = '{$od["od_id"]}',
      `entId` = '{$entData["mb_entId"]}',
      `entNm` = '{$entData["mb_entNm"]}',
      `entCrn` = '{$entData["mb_giup_bnum"]}',
      `entNum` = '{$member["mb_ent_num"]}',
      `entMail` = '{$entData["mb_email"]}',
      `entCeoNm` = '{$entData["mb_giup_boss_name"]}',
      `entConAcc01` = '{$entData["mb_entConAcc01"]}',
      `entConAcc02` = '{$entData["mb_entConAcc02"]}',
      `penId` = '{$penData["penId"]}',
      `penNm` = '{$penData["penNm"]}',
      `penConNum` = '{$penData["penConNum"]}', # 휴대전화번호인데 전화번호랑 둘중에 어떤거 입력해야될지?
      `penBirth` = '{$penData["penBirth"]}',
      `penLtmNum` = '{$penData["penLtmNum"]}',
      `penRecGraCd` = '{$penData["penRecGraCd"]}', # 장기요양등급
      `penRecGraNm` = '{$penData["penRecGraNm"]}',
      `penRecTypeCd` = '{$penData["penRecTypeCd"]}', # 수령방법
      `penRecTypeTxt` = '{$penData["penRecTypeTxt"]}',
      `penTypeCd` = '{$penData["penTypeCd"]}', # 본인부담금율
      `penTypeNm` = '{$penData["penTypeNm"]}',
      `penExpiDtm` = '{$penData["penExpiDtm"]}', # 수급자 이용기간
      `penJumin` = '{$penData["penJumin"]}',
      `penZip` = '{$penData["penZip"]}',
      `penAddr` = '{$penData["penAddr"]}',
      `penAddrDtl` = '{$penData["penAddrDtl"]}'
    ");

    // 계약서 품목별 초기값 가져오기
    $res = get_eroumcare(EROUMCARE_API_EFORM_SELECT_INITIAL_STATE_LIST, array(
      'penOrdId' => $od["ordId"]
    ));

    foreach($res["data"] as $it) {
      $priceEnt = intval($it["prodPrice"]) - intval($it["penPrice"]);
            
      // 비급여 품목은 계약서에서 제외
      if ($it['gubun'] != '02') {
        sql_query("INSERT INTO `eform_document_item` SET
          `dc_id` = UNHEX('$dcId'),
          `gubun` = '{$it["gubun"]}',
          `ca_name` = '{$it["itemNm"]}',
          `it_name` = '{$it["prodNm"]}',
          `it_code` = '{$it["prodPayCode"]}',
          `it_barcode` = '{$it["prodBarNum"]}',
          `it_qty` = '1',
          `it_date` = '{$it["contractDate"]}',
          `it_price` = '{$it["prodPrice"]}',
          `it_price_pen` = '{$it["penPrice"]}',
          `it_price_ent` = '$priceEnt'
        ");
      }
    }
  }
}

# 설치결과보고서
$report = sql_fetch("
    SELECT * FROM partner_install_report
    WHERE od_id = '$od_id'
");
$report['issue'] = [];
if($report['ir_is_issue_1'])
  $report['issue'][] = '상품변경';
if($report['ir_is_issue_2'])
  $report['issue'][] = '상품추가';
if($report['ir_is_issue_3'])
  $report['issue'][] = '미설치';
  
$report_mb = get_member($report['mb_id']);
$report['member'] = $report_mb;

$photo_result = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND img_type = '설치사진'
  ORDER BY ip_id ASC
", true);
$report['photo'] = [];
while($photo = sql_fetch_array($photo_result)) {
  $report['photo'][] = $photo;
}

$photo_result2 = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND img_type = '실물바코드사진'
  ORDER BY ip_id ASC
", true);
$report['photo2'] = [];
while($photo = sql_fetch_array($photo_result2)) {
  $report['photo2'][] = $photo;
}

$photo_result3 = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND img_type = '설치ㆍ회수ㆍ소독확인서'
  ORDER BY ip_id ASC
", true);
$report['photo3'] = [];
while($photo = sql_fetch_array($photo_result3)) {
  $report['photo3'][] = $photo;
}

$photo_result4 = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND img_type = '추가사진'
  ORDER BY ip_id ASC
", true);
$report['photo4'] = [];
while($photo = sql_fetch_array($photo_result4)) {
  $report['photo4'][] = $photo;
}
?>

<script type="text/javascript">
// 주문 완료인경우
if (document.referrer.indexOf("shop/orderform.php") >= 0) {

}
</script>

<!-- 210326 배송정보팝업 -->
<div id="popupProdDeliveryInfoBox" class="listPopupBoxWrap">
  <div>
  </div>
</div>

<style>
.listPopupBoxWrap {
  position: fixed;
  width: 100vw;
  height: 100vh;
  left: 0;
  top: 0;
  z-index: 99999999;
  background-color: rgba(0, 0, 0, 0.6);
  display: table;
  table-layout: fixed;
  opacity: 0;
}

.listPopupBoxWrap>div {
  width: 100%;
  height: 100%;
  display: table-cell;
  vertical-align: middle;
}

.listPopupBoxWrap iframe {
  position: relative;
  width: 500px;
  height: 700px;
  border: 0;
  background-color: #FFF;
  left: 50%;
  margin-left: -250px;
}

@media (max-width : 750px) {
  .listPopupBoxWrap iframe {
    width: 100%;
    height: 100%;
    left: 0;
    margin-left: 0;
  }
}
</style>

<script type="text/javascript">
$(function() {
  $(".listPopupBoxWrap").hide();
  $(".listPopupBoxWrap").css("opacity", 1);

  $(".popupDeliveryInfoBtn").click(function(e) {
    e.preventDefault();

    var od = $(this).attr("data-od");
    $("#popupProdDeliveryInfoBox > div").append("<iframe src='/shop/popup.prodDeliveryInfo.php?od_id=" + od +
      "'>");
    $("#popupProdDeliveryInfoBox iframe").load(function() {
      $("#popupProdDeliveryInfoBox").show();
    });
  });
});
</script>
<!-- 210326 배송정보팝업 -->

<link rel="stylesheet" href="<?=$SKIN_URL?>/css/product_order_210324.css?v=210913">
<section id="pro-order2" class="wrap order-list">
  <h2 class="tti">
    주문상세
    <div class="list-more"><a href="./orderinquiry.php">목록</a></div>
  </h2>
  <div class="od_status">
    <?php
    $sql = "select *
        from g5_shop_order_cancel_request
        where od_id = '{$od['od_id']}'";
  
    $cancel_request_row = sql_fetch($sql);
    $info="";
    if ($cancel_request_row['request_type'] == 'cancel') {
      $info = "주문취소를 요청하셨습니다.";
    
      if ($cancel_request_row['approved'] == 1)
        $info = "주문취소가 완료되었습니다.";
    }
    if ($cancel_request_row['request_type'] == 'return') {
      $info = "주문반품을 요청하셨습니다.";
    
      if ($cancel_request_row['approved'] == 1)
        $info = "주문반품이 완료되었습니다.";
    }
    if(!$info) {
      if($od["od_stock_insert_yn"] == "Y") {
        echo "재고 등록이 완료되었습니다";
      } else {
        /*
        switch ($od["od_status"]) {
          case '준비': echo "주문이 완료되었습니다.";  break;
          case '출고준비': echo "주문이 완료되었습니다.";  break;
          case '배송': echo "배송이 시작되었습니다.";  break;
          case '완료': echo "배송이 완료되었습니다.";  break;
          case '취소': echo "주문이 취소되었습니다.";  break;
          case '주문무효': echo "주문이 취소되었습니다.";  break;
          default: break;
        }
        */
        echo "주문이 완료되었습니다.";
      }
    } else {
      echo $info;
    }
    ?>
  </div>

  <section class="tab-wrap tab-2 on">
    <?php if($od["od_penId"]) { ?>
    <div class="detail-price pc_none tablet_block">
      <h5>수급자 정보</h5>
      <div class="all-info all-info2">
        <ul>
          <li>
            <ul class="eform-tab">
              <li class="eform-tab-head">공급계약서</li>
              <li class="eform-tab-desc">수급자 주문시 간편하게 작성하는 온라인 계약</li>
              <li class="eform-tab-links">
                <?php if(!$eform["dc_id"] || $eform["dc_status"] == '0') { // 계약서 생성 전 ?>
                <a href="#" class="linkEformWrite eform-tab-link" data-od="<?=$od["od_id"]?>">계약서 생성</a>
                <?php } else if ($eform['dc_status'] == '1') { // 계약서 생성 후 & 작성 전 ?>
                <div class="eform-tab-flexbox">
                  <a href="#" class="linkEformSign eform-tab-link half" data-od="<?=$od["od_id"]?>">계약서 작성</a>
                  <a href="#" class="linkEformEdit eform-tab-link half white" data-od="<?=$od["od_id"]?>">내용변경</a>
                </div>
                <?php } else if ($eform['dc_status'] == '2' || $eform['dc_status'] == '3') { // 계약서 작성 완료 ?>
                <a href="#" class="linkEformView eform-tab-link white" data-od="<?=$od["od_id"]?>">계약서 다운로드</a>
                <?php } ?>
              </li>
              <?php if ($eform['dc_send_sms'] === '1') { ?>
              <li style="margin-top:5px">
                * <?php echo $eform['penConNum']; ?> 번호로 계약서 전송 완료
              </li>
              <?php } ?>
            </ul>
          </li>
          <li>
            <div>
              <b>수급자명</b>
              <span><?=($od["od_penNm"]) ? $od["od_penNm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>인정등급</b>
              <span><?=($od["od_penTypeNm"]) ? $od["od_penTypeNm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>장기요양번호</b>
              <span><?=($od["od_penLtmNum"]) ? $od["od_penLtmNum"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>유효기간</b>
              <span><?=($od["od_penExpiDtm"]) ? $od["od_penExpiDtm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>적용기간</b>
              <span><?=($od["od_penAppEdDtm"]) ? $od["od_penAppEdDtm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>전화번호</b>
              <span><?=($od["od_penConPnum"]) ? $od["od_penConPnum"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>휴대폰</b>
              <span><?=($od["od_penConNum"]) ? $od["od_penConNum"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>주소</b>
              <span><?=($od["od_penAddr"]) ? $od["od_penAddr"] : "-"?></span>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <?php } ?>
    <div class="detail-wrap">
      <div class="name-top<?=($od["recipient_yn"] == "N") ? " gray" : ""?>">
        <div>
          <?php if($od["recipient_yn"] == "Y"){ ?>
          <p>수급자 주문</p>
          <a href="javascript;;" style="display: none;">계약서</a>
          <?php }else if($od["od_stock_insert_yn"] == "Y"){ ?>
          <p>보유재고 등록</p>
          <a href="javascript;;" style="display: none;">보유재고등록</a>
          <?php } else { ?>
          <p>상품 주문</p>
          <a href="javascript;;" style="display: none;">재고확인</a>
          <?php } ?>
        </div>
      </div>
      <?php if($report['photo'] || $report['photo2'] || $report['photo3'] || $report['photo4']) { ?>
      <div class="install-report">
        <div class="top-wrap row justify-space-between">
          <span>설치결과보고서</span>
          <p><?=$report['member']['mb_name']?></p>
        </div>
        <?php if($report) { ?>
        <div class="mid-wrap">
          <?php if($report['ir_file_url']) { ?>
          <a href="<?=G5_SHOP_URL."/eform/install_report_download.php?od_id={$od_id}"?>" class="btn_ir_download">결과보고서
            다운로드</a>
          <?php } ?>
        </div>
        <?php } ?>

        <?php if($report['photo']) { ?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='/shop/img/no_image.gif';">
              </a>
            </div>
          </div>
          <?php } ?>

          <?php foreach($report['photo'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          설치 사진(필수)
        </div>
        <?php } ?>

        <?php if($report['photo2']) { ?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
          <?php foreach($report['photo2'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          실물 바코드 사진(필수)
        </div>
        <?php } ?>

        <?php if($report['photo3']) { ?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
          <?php foreach($report['photo3'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          설치ㆍ회수ㆍ소독확인서 사진(필수)
        </div>
        <?php } ?>

        <?php if($report['photo4']) { ?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
          <?php foreach($report['photo4'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          추가사진(선택) - 상품변경 혹은 특이사항 발생 시
        </div>
        <?php } ?>
      </div>
      <?php } ?>

      <?php if($report['issue']) { ?>
      <div class="col issue-wrap">
        <div class="col title-wrap">
          이슈사항
        </div>
        <div class="issue-select">
          이슈사항 (
          <?php echo implode(' /', $report['issue']); ?>
          )
        </div>
        <div class="issue">
          <p>
            <?=nl2br($report['ir_issue'])?>
          </p>
        </div>
      </div>
      <?php } ?>

      <h4>상품 정보</h4>
      <div class="info-wrap">
        <div class="table-list2">
          <ul class="head">
            <li class="pro">상품(옵션)</li>
            <li class="num">수량</li>
            <li class="pro-price">단가</li>
            <li class="basic-price">공급가액</li>
            <li class="tax-price">부가세</li>
            <li class="price">총금액</li>
            <li class="delivery-price">주문상태</li>
            <li class="barcode">바코드</li>
          </ul>

          <?php
          $isReceiverEdit = true;
          $isDeliveryInfo = false;
          $is_all_ready = true;
          for($i=0; $i < count($item); $i++) {
            $prodMemo = ""; $ordLendDtm = "";
            for($k=0; $k < count($item[$i]['opt']); $k++) {
              $prodMemo = ($prodMemo) ? $prodMemo : $item[$i]["prodMemo"];
              $ordLendDtm = ($ordLendDtm) ? $ordLendDtm : date("Y-m-d", strtotime($item[$i]["ordLendStrDtm"]))." ~ ".date("Y-m-d", strtotime($item[$i]["ordLendEndDtm"]));
              
              if ($item[$i]['opt'][$k]['ct_delivery_num']) {
                $isDeliveryInfo = true;
              }

              if ($item[$i]['opt'][$k]['ct_status'] != '준비') {
                $isReceiverEdit = false;
              }

              $rowspan = (substr($item[$i]["ca_id"], 0, 2) == 20) ? 3 : 1;
          ?>
          <div class="list">
            <ul class="cb">
              <li class="pro">
                <div class="img">
                  <a
                    href="<?=G5_SHOP_URL?>/item.php?it_id=<?=$item[$i]['it_id']?>&ca_id=<?=substr($item[$i]["ca_id"], 0, 2) ?>">
                    <img src="/data/item/<?=$item[$i]['thumbnail']?>" onerror="this.src = '/shop/img/no_image.gif';">
                  </a>
                </div>
                <div class="pro-info">
                  <div class="pro-icon">
                    <?php if(!is_benefit_item($item[$i])) { ?>
                    <i class="icon01"><?=($item[$i]["prodSupYn"] == "N") ? "비유통" : "유통"?></i>
                    <?php } ?>
                    <?php if(substr($item[$i]["ca_id"], 0, 2) == 10) { ?>
                    <i class="icon03">판매</i>
                    <?php } ?>
                    <?php if(substr($item[$i]["ca_id"], 0, 2) == 20) { ?>
                    <i class="icon02">대여</i>
                    <?php } ?>
                    <?php if(is_benefit_item($item[$i])) { ?>
                    <i class="icon03">비급여</i>
                    <?php } ?>
                  </div>
                  <div class="name">
                    <a
                      href="<?=G5_SHOP_URL?>/item.php?it_id=<?=$item[$i]['it_id']?>&ca_id=<?=substr($item[$i]["ca_id"], 0, 2) ?>">
                      <?php echo $item[$i]['it_name']; ?>
                      <?php if($item[$i]['opt'][$k]['ct_stock_qty']) echo '[재고소진]'; ?>
                    </a>
                  </div>
                  <?php if($item[$i]['opt'][$k]['ct_option'] != $item[$i]['it_name']) { ?>
                  <div class="text"><?=$item[$i]['opt'][$k]['ct_option']?></div>
                  <?php } ?>
                  <!--모바일용-->
                  <div class="info_pc_none">
                    <div>
                      <p><?php echo number_format($item[$i]['opt'][$k]['ct_qty']); ?>개</p>
                    </div>
                    <!-- <div>
                      <p><?php echo number_format($item[$i]['opt'][$k]['opt_price']); ?></p>
                    </div> -->
                    <div>
                      <p>상품금액 : <?php echo number_format($item[$i]['opt'][$k]['sell_price']); ?></p>
                    </div>
                  </div>
                  <?php if($od["od_delivery_insert"] && ($item[$i]["prodSupYn"] == "Y")) { ?>
                  <div class="delivery_price_pc">
                    <p>
                      <a href="#" class="de-btn popupDeliveryInfoBtn" data-od="<?=$od["od_id"]?>">배송조회</a>
                    </p>
                  </div>
                  <?php } ?>
                </div>
              </li>
              <li class="num m_none">
                <p><?php echo number_format($item[$i]['opt'][$k]['ct_qty']); ?>개</p>
              </li>
              <li class="pro-price m_none">
                <p><?php echo number_format($item[$i]['opt'][$k]['opt_price']); ?>원</p>
              </li>
              <li class="basic-price m_none">
                <p><?php echo number_format($item[$i]['opt'][$k]['basic_price']); ?>원</p>
              </li>
              <li class="tax-price m_none">
                <p><?php echo number_format($item[$i]['opt'][$k]['tax_price']); ?>원</p>
              </li>
              <li class="price m_none">
                <p><?php echo number_format($item[$i]['opt'][$k]['sell_price']); ?>원</p>
              </li>
              <li class="delivery-price m_none">
                <p>
                  <?php
                      if($od["od_stock_insert_yn"] == "Y"){
                      echo "등록완료";
                      }else{
                        if($item[$i]["prodSupYn"] == "N"){
                            echo "등록완료";
                        }else{ 
                            if($od["od_status"]=="주문무효"||$od["od_status"]=="주문취소"){
                                echo $od["od_status"];
                            }else{
                                $ct_status_text="";
                                switch ($item[$i]['opt'][$k]['ct_status']) {
                                    case '보유재고등록': $ct_status_text="보유재고등록"; break;
                                    case '재고소진': $ct_status_text="재고소진"; break;
                                    case '작성': $ct_status_text="작성"; break;
                                    case '주문무효': $ct_status_text="주문무효"; break;
                                    case '취소': $ct_status_text="주문취소"; break;
                                    case '주문': $ct_status_text="주문접수"; break;
                                    case '입금': $ct_status_text="입금완료"; break;
                                    case '준비': $ct_status_text="상품준비"; break;
                                    case '출고준비': $ct_status_text="출고준비"; break;
                                    case '배송': $ct_status_text="출고완료"; break;
                                    case '완료': $ct_status_text="배송완료"; break;
                                }
                                echo $ct_status_text;
                            }
                        }
                      }
                    ?>
                </p>
              </li>
              <li class="barcode">
                <?php if($item[$i]['opt'][$k]['ct_status'] !== "취소" && $item[$i]['opt'][$k]['ct_status'] !== "주문무효" && !is_benefit_item($item[$i]) &&  $item[$i]['opt'][$k]['io_type'] == '0'){ ?>
                <a href="#" class="btn-01 btn-0 popupProdBarNumInfoBtn" data-id="<?=$od["od_id"]?>"
                  data-ct-id="<?=$item[$i]['opt'][$k]["ct_id"]?>"><img src="<?=$SKIN_URL?>/image/icon_02.png" alt="">
                  바코드 확인</a>
                <?php } ?>
              </li>
            </ul>
            <div class="list-btm">
              <?php if(substr($item[$i]["ca_id"], 0, 2) == 20) { ?>
              <div>
                <span class="btm-tti">대여금액(월) : </span>
                <span><?=number_format($item[$i]["it_rental_price"])?>원</span>
              </div>
              <?php if($od["recipient_yn"] == "Y") { ?>
              <div>
                <span class="btm-tti">대여기간 : </span>
                <span>
                  <?=$ordLendDtm?>
                </span>
              </div>
              <?php } ?>
              <?php } ?>
              <?php if($prodMemo){ ?>
              <div>
                <span class="btm-tti">요청사항 : </span>
                <span><?=$prodMemo?></span>
              </div>
              <?php } ?>
            </div>
            <?php
            if($item[$i]['opt'][$k]['ct_is_direct_delivery'] == 2 && $item[$i]['opt'][$k]['ct_direct_delivery_date'] && $item[$i]['opt'][$k]['ct_delivery_num'] && !in_array($item[$i]['opt'][$k]['ct_status'], ['취소', '주문무효'])) {
              $delivery_company_name = '';
              foreach($delivery_companys as $company) {
                if($company['val'] == $item[$i]['opt'][$k]['ct_delivery_company']) {
                  $delivery_company_name = $company['name'];
                  break;
                }
              }
              echo '<div style="background-color: #f3f3f3; color: #666; font-size: 14px;padding: 8px;">설치 예정일 : '.date('Y-m-d H시', strtotime($item[$i]['opt'][$k]['ct_direct_delivery_date'])).', 배송정보 : ['.$delivery_company_name.'] '.$item[$i]['opt'][$k]['ct_delivery_num'].'</div>';
            }
            ?>
          </div>
          <?php } ?>
          <?php } ?>
        </div>
      </div>

      <?php if($isReceiverEdit) { ?>
      <a href="order_edit.php?od_id=<?=$od_id?>" class="btn_od_edit">주문상품 변경</a>
      <?php } ?>

      <style>
      .btn_od_edit {
        display: block;
        border: 1px solid #ee8102;
        border-radius: 3px;
        background-color: #ffffff;
        margin: 20px auto 20px auto;
        padding: 15px;
        max-width: 250px;
        text-align: center;
        color: #ee8102 !important;
        font-weight: bold;
      }

      #frmorderinquiryviewdeliveryform .shbtn {
        border: 1px solid #cccccc;
        font-size: 12px;
        cursor: pointer;
        padding: 0 13px;
        height: 29px;
        line-height: 29px;
        display: inline-block;
        color: #656565;
        background-color: white;
        vertical-align: middle;
      }

      #frmorderinquiryviewdeliveryform tbody td input[type="text"] {
        background-color: white !important;
        padding: 0px 8px;
        height: 28px;
        min-width: 150px;
        background: none !important;
        vertical-align: middle;
      }

      #frmorderinquiryviewdeliveryform #delivery_info_btn {
        margin: 20px auto;
        color: white;
        background-color: #454545;
        border: none;
        display: block;
        font-size: 15px;
        padding: 8px 15px;
        font-weight: bold;
      }

      .popupDeliveryInfoBtn {
        margin-bottom: 6px;
        display: block;
        vertical-align: top;
        width: 100px;
        border: 1px solid #ddd;
        padding: 3px 0;
        border-radius: 5px;
        font-size: 14px;
        text-align: center;
        background: #ddd;
      }
      </style>

      <?php add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js ?>
      <?php if($od["od_stock_insert_yn"] == "N") { ?>
      <div class="order-info">
        <div class="top">
          <h5>받으시는 분</h5>
          <?php if ($isDeliveryInfo) { ?>
          <a href="#" class="btn-02 btn-0 popupDeliveryInfoBtn" data-od="<?php echo $od['od_id']; ?>">배송정보</a>
          <?php } ?>
        </div>
        <?php if ($isReceiverEdit) { ?>

        <form id="frmorderinquiryviewdeliveryform" name="frmorderinquiryviewdeliveryform"
          action="./orderinquiryview_delivery.php" method="POST">
          <div class="tbl_frm01">
            <table>
              <colgroup>
                <col class="grid_4">
                <col>
              </colgroup>
              <tbody>
                <tr>
                  <th scope="row"><label for="od_b_name"><span class="sound_only">받으시는 분 </span>이름</label></th>
                  <td colspan="3"><input type="text" name="od_b_name" value="<?php echo get_text($od['od_b_name']); ?>"
                      id="od_b_name" required class="frm_input required"></td>
                </tr>
                <tr>
                  <th scope="row"><label for="od_b_tel"><span class="sound_only">받으시는 분 </span>전화번호</label></th>
                  <td colspan="3"><input type="text" name="od_b_tel" value="<?php echo get_text($od['od_b_tel']); ?>"
                      id="od_b_tel" required class="frm_input required"></td>
                </tr>
                <tr>
                  <th scope="row"><label for="od_b_hp"><span class="sound_only">받으시는 분 </span>핸드폰</label></th>
                  <td colspan="3"><input type="text" name="od_b_hp" value="<?php echo get_text($od['od_b_hp']); ?>"
                      id="od_b_hp" class="frm_input required"></td>
                </tr>
                <tr>
                  <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                  <td class="od_b_address" colspan="3">
                    <label for="od_b_zip" class="sound_only">우편번호</label>
                    <input type="text" name="od_b_zip"
                      value="<?php echo get_text($od['od_b_zip1']).get_text($od['od_b_zip2']); ?>" id="od_b_zip"
                      required class="frm_input required" size="35">
                    <button type="button" class="shbtn"
                      onclick="win_zip('frmorderinquiryviewdeliveryform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소
                      검색</button><br>
                    <input type="text" name="od_b_addr1" value="<?php echo get_text($od['od_b_addr1']); ?>"
                      id="od_b_addr1" required class="frm_input required" size="35" placeholder="기본주소" readonly>
                    <input type="text" name="od_b_addr2" value="<?php echo get_text($od['od_b_addr2']); ?>"
                      id="od_b_addr2" class="frm_input" size="35" placeholder="상세주소">
                    <br /><input type="hidden" name="od_b_addr3" value="<?php echo get_text($od['od_b_addr3']); ?>"
                      id="od_b_addr3" class="frm_input" size="35" placeholder="참고항목" readonly>
                    <input type="hidden" name="od_b_addr_jibeon"
                      value="<?php echo get_text($od['od_b_addr_jibeon']); ?>">
                    <?php
                  $szip = get_text($od['od_b_zip1']).get_text($od['od_b_zip2']);
                  $sql = "SELECT * FROM g5_shop_sendcost WHERE sc_zip1 <= '{$szip}' AND sc_zip2 >= '{$szip}'";
                  $szip_result = sql_fetch($sql);

                  if ( $szip_result['sc_id'] ) {
                  ?>
                    <div class="add_sendcost_address">
                      <span class="red">* 도서산간지역</span>
                    </div>
                    <?php } ?>
                  </td>
                </tr>
                <tr>
                  <th scope="row"><label for="od_memo"><span class="sound_only">받으시는 분 </span>배송요청사항</label></th>
                  <td colspan="3"><input type="text" name="od_memo" value="<?php echo get_text($od['od_memo']); ?>"
                      id="od_memo" class="frm_input"></td>
                </tr>
              </tbody>
            </table>
          </div>
          <input type="hidden" name="od_id" value="<?php echo $od['od_id']; ?>">
          <button id="delivery_info_btn">배송정보 수정</button>
        </form>

        <?php } else { ?>
        <div class="table-list3">
          <ul>
            <li>
              <strong>이름</strong>
              <div>
                <p><?php echo get_text($od['od_b_name']); ?></p>
              </div>
            </li>
            <li>
              <strong>전화번호</strong>
              <div>
                <p><?php echo get_text($od['od_b_tel']); ?></p>
              </div>
            </li>
            <li>
              <strong>핸드폰</strong>
              <div>
                <p><?php echo get_text($od['od_b_hp']); ?></p>
              </div>
            </li>
            <li>
              <strong>주소</strong>
              <div>
                <p>
                  <?php echo get_text(sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon'])); ?>
                </p>
              </div>
            </li>
            <li>
              <strong>배송요청사항</strong>
              <div>
                <p><?php echo get_text($od['od_memo']); ?></p>
              </div>
            </li>
            <li>
              <strong>E-mail</strong>
              <div>
                <p><?php echo get_text($od['od_email']); ?></p>
              </div>
            </li>
          </ul>
        </div>
        <?php } ?>
      </div>
      <?php } ?>

      <?php 
      $sql_od ="select `od_hide_control` from `g5_shop_order` where `od_id` = '".$od['od_id']."'";
      $result_od = sql_fetch($sql_od);
      ?>
      <?php if(!$result_od['od_hide_control']) { ?>
      <div class="list-more">
        <p><a href="javascript:void(0)" onclick="hide_control('<?=$od["od_id"] ?>')">주문내역 숨김처리</a></p>
        <p>*해당 주문을 숨김처리하면 주문내역에 노출되지 않습니다.<br>*숨김처리는 주문취소가 되지 않습니다.</p>
      </div>
      <?php } ?>
    </div>

    <div class="detail-price">
      <?php if($od["od_penId"]) { ?>
      <h5 class="m_none tablet_none">수급자 정보</h5>
      <div class="all-info all-info2 m_none tablet_none">
        <ul>
          <li>
            <ul class="eform-tab">
              <li class="eform-tab-head">공급계약서</li>
              <li class="eform-tab-desc">수급자 주문시 간편하게 작성하는 온라인 계약</li>
              <li class="eform-tab-links">
                <?php if(!$eform["dc_id"] || $eform["dc_status"] == '0') { // 계약서 생성 전 ?>
                <a href="#" class="linkEformWrite eform-tab-link" data-od="<?=$od["od_id"]?>">계약서 생성</a>
                <?php } else if ($eform['dc_status'] == '1') { // 계약서 생성 후 & 작성 전 ?>
                <div class="eform-tab-flexbox">
                  <a href="#" class="linkEformSign eform-tab-link half" data-od="<?=$od["od_id"]?>">계약서 작성</a>
                  <a href="#" class="linkEformEdit eform-tab-link half white" data-od="<?=$od["od_id"]?>">내용변경</a>
                </div>
                <?php } else if ($eform['dc_status'] == '2' || $eform['dc_status'] == '3') { // 계약서 작성 완료 ?>
                <a href="#" class="linkEformView eform-tab-link white" data-od="<?=$od["od_id"]?>">계약서 다운로드</a>
                <?php } ?>
              </li>
              <?php if ($eform['dc_send_sms'] === '1') { ?>
              <li style="margin-top:5px">
                * <?php echo $eform['penConNum']; ?> 번호로 계약서 전송 완료
              </li>
              <?php } ?>
            </ul>
          </li>
          <li>
            <div>
              <b>수급자명</b>
              <span><?=($od["od_penNm"]) ? $od["od_penNm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>인정등급</b>
              <span><?=($od["od_penTypeNm"]) ? $od["od_penTypeNm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>장기요양번호</b>
              <span><?=($od["od_penLtmNum"]) ? $od["od_penLtmNum"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>유효기간</b>
              <span><?=($od["od_penExpiDtm"]) ? $od["od_penExpiDtm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>적용기간</b>
              <span><?=($od["od_penAppEdDtm"]) ? $od["od_penAppEdDtm"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>전화번호</b>
              <span><?=($od["od_penConPnum"]) ? $od["od_penConPnum"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>휴대폰</b>
              <span><?=($od["od_penConNum"]) ? $od["od_penConNum"] : "-"?></span>
            </div>
          </li>
          <li>
            <div>
              <b>주소</b>
              <span><?=($od["od_penAddr"]) ? $od["od_penAddr"] : "-"?></span>
            </div>
          </li>
        </ul>
      </div>
      <?php } ?>

      <h5>결제정보</h5>
      <div class="all-info all-info2">
        <ul>
          <li>
            <div>
              <b>주문번호</b>
              <span><?=$od["od_id"]?></span>
            </div>
          </li>
          <li>
            <div>
              <b>주문일시</b>
              <span><?=$od["od_time"]?></span>
            </div>
          </li>
          <?php if($od["od_stock_insert_yn"] == "N") { ?>
          <li>
            <div>
              <b>결제방식</b>
              <span><?php echo ($easy_pay_name ? $easy_pay_name.'('.$od['od_settle_case'].')' : check_pay_name_replace($od['od_settle_case']) ); ?></span>
            </div>
          </li>
          <li>
            <div>
              <b>매출증빙</b>
              <span><?php echo $typereceipt['name']; ?>
                <?php echo $typereceipt['ot_bnum'] ? '( ' . $typereceipt['ot_bnum'] : ''; ?>
                <?php echo $typereceipt['ot_bnum'] ? ')': ''; ?></span>
            </div>
          </li>
          <?php } ?>
        </ul>
      </div>

      <div class="all-info">
        <ul>
          <li>
            <div>
              <b>주문금액</b>
              <span><?=number_format($tot_price - $od["od_send_cost"] - $od['od_send_cost2'])?> 원</span>
            </div>
          </li>
          <?php if($od['od_coupon'] > 0) { ?>
          <li>
            <div>
              <b>쿠폰할인</b>
              <span><?php echo number_format($od['od_coupon']); ?> 원</span>
            </div>
          </li>
          <?php } ?>

          <?php if($od['od_receipt_point'] > 0) { ?>
          <li>
            <div>
              <b>포인트결제</b>
              <span><?php echo number_format($od['od_receipt_point']); ?> 원</span>
            </div>
          </li>
          <?php } ?>

          <?php if ($od['od_cart_discount'] > 0) { ?>
          <!--
          <li>
            <div>
              <b>할인금액</b>
              <span><?php echo number_format($od['od_cart_discount']); ?> 원</span>
            </div>
          </li>
          -->
          <?php } ?>

          <?php if ($od['od_cart_discount2'] > 0) { ?>
          <li>
            <div>
              <b>추가할인금액</b>
              <span><?php echo number_format($od['od_cart_discount2']); ?> 원</span>
            </div>
          </li>
          <?php } ?>
          <?php if ($od['od_send_cost2'] > 0) { ?>
          <li>
            <div>
              <b>추가배송비</b>
              <span><?php echo number_format($od['od_send_cost2']); ?> 원</span>
            </div>
          </li>
          <?php } ?>
          <li>
            <div>
              <b>배송비</b>
              <span><?php echo number_format($od['od_send_cost']); ?> 원</span>
            </div>
          </li>
          <?php if($od['od_sales_discount']) { ?>
          <li>
            <div>
              <b>매출할인</b>
              <span>- <?php echo number_format($od['od_sales_discount']); ?> 원</span>
            </div>
          </li>
          <?php } ?>
        </ul>
        <?php 
        // $total_price = $tot_price - $od['od_cart_discount'] - $od['od_cart_discount2'] ;
        $total_price = $tot_price - $od['od_cart_discount2'] - $od['od_sales_discount'];
        ?>
        <div class="all-info-price">
          <b>합계금액</b>
          <span><?php echo number_format($total_price); ?> 원</span>
        </div>
      </div>

      <div class="pay-btn2">
        <?php if($od["od_stock_insert_yn"] == "N" && $deliveryItem) { ?>
        <button type="button" id="send_statement"><img src="<?=$SKIN_URL?>/image/icon_24.png" alt=""> 거래명세서 출력</button>
        <?php } ?>

        <?php if ($cancel_price == 0) { // 취소한 내역이 없다면
          $type = 0;
          if ($custom_cancel)
            $type = 1;
          if ($pay_complete_cancel || $preparation_cancel)
            $type = 2;

          // $btn_name = "주문 취소하기";
          // $action_url = "./orderinquirycancel.php";
          // $to = "";

          $sql = "select *
                  from g5_shop_order_cancel_request
                  where od_id = '{$od['od_id']}' and approved = 0";

          $cancel_request_row = sql_fetch($sql);

          $sql = "select * from g5_shop_cart where od_id = '{$od['od_id']}'";
          $sql_result = sql_query($sql);
          $flag=true;
          while ($row = sql_fetch_array($sql_result)) {
              if($row['ct_status'] !=="준비") $flag= false;
          }

          if ($flag) {
            $action_url = "./orderinquirycancelrequest.php";
            $btn_name = "취소 요청하기";
            $to = "cancel";
          }
        ?>
        <?php if($od["od_stock_insert_yn"] !== "Y"&&$flag&&!$cancel_request_row['od_id']) {  ?>
        <a href="#" id="cancel_btn" type="button" data-toggle="collapse" href="#sod_fin_cancelfrm" aria-expanded="false"
          aria-controls="sod_fin_cancelfrm"><?php echo $btn_name ?></a>
        <div class="h15"></div>
        <div id="sod_fin_cancelfrm" class="collapse">
          <div class="well">
            <form class="form" role="form" method="post" action="<?php echo $action_url ?>"
              onsubmit="return fcancel_check(this);">
              <input type="hidden" name="od_id" value="<?php echo $od['od_id']; ?>">
              <input type="hidden" name="token" value="<?php echo $token; ?>">
              <input type="hidden" name="type" value="<?php echo $type ?>">
              <input type="hidden" name="to" value="<?php echo $to ?>">
              <div class="input-group input-group-sm">
                <!--<span class="input-group-addon">사유</span>-->
                <select name="request_reason_type" class="form-control"
                  style="display: table-cell; width: 100px; margin-right: 10px;">
                  <option value="단순변심">단순변심</option>
                  <option value="제품파손">제품파손</option>
                  <option value="제품하자">제품하자</option>
                  <option value="오주문">오주문</option>
                  <option value="오배송">오배송</option>
                  <option value="A/S">A/S</option>
                  <option value="기타">기타</option>
                </select>
                <input type="text" name="cancel_memo" id="cancel_memo" required class="form-control input-sm" size="40"
                  maxlength="100" style="width: calc(100% - 110px); float: none;">
                <span class="input-group-btn">
                  <button type="submit" class="btn btn-black btn-sm">확인</button>
                </span>
              </div>
            </form>
          </div>
        </div>
        <?php } ?>
        <?php } ?>
      </div>
    </div>
  </section>
</section>

<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
            class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">상태설명</h4>
      </div>
      <div class="modal-body">
        <ul>
          <li>주문 : 주문이 접수되었습니다.</li>
          <li>입금 : 입금(결제)이 완료 되었습니다.</li>
          <li>준비 : 상품 준비 중입니다.</li>
          <li>배송 : 상품 배송 중입니다.</li>
          <li>완료 : 상품 배송이 완료 되었습니다.</li>
        </ul>
        <br>
        <p class="text-center">
          <button type="button" class="btn btn-black btn-sm" data-dismiss="modal">닫기</button>
        </p>
      </div>
    </div>
  </div>
</div>

<div id="send_statementBox">
  <div>
    <iframe src="<?php echo G5_URL; ?>/shop/pop.statement.php?&od_id=<?=$_GET["od_id"]?>"></iframe>
  </div>
</div>
<div id="popupProdBarNumInfoBox" class="listPopupBoxWrap">
  <div></div>
</div>

<style>
#send_statementBox {
  position: fixed;
  width: 100vw;
  height: 100vh;
  left: 0;
  top: 0;
  z-index: 5000;
  background-color: rgba(0, 0, 0, 0.6);
  display: table;
  table-layout: fixed;
  opacity: 0;
}

#send_statementBox>div {
  width: 100%;
  height: 100%;
  display: table-cell;
  vertical-align: middle;
}

#send_statementBox iframe {
  position: relative;
  width: 730px;
  height: 800px;
  border: 0;
  background-color: #FFF;
  left: 50%;
  margin-left: -365px;
}

@media (max-width : 750px) {
  #send_statementBox iframe {
    width: 100%;
    height: 100%;
    left: 0;
    margin-left: 0;
  }
}
</style>

<script>
function hide_control(od_id) {
  $.ajax({
      method: "POST",
      url: "./ajax.hide_control.php",
      data: {
        od_id: od_id
      }
    })
    .done(function(data) {
      if (data == "S") {
        alert('해당 주문 건이 숨김 처리되었습니다.');
        location.href = "<?=G5_URL?>/shop/orderinquiry.php";
      }
    });
}

$(".popupProdBarNumInfoBtn").click(function(e) {
  e.preventDefault();

  var od_id = $(this).attr("data-id");
  var ct_id = $(this).attr("data-ct-id");

  $("#popupProdBarNumInfoBox > div").append(
    "<iframe src='<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form_4.php?od_id=" + od_id + "&ct_id=" +
    ct_id + "'>");
  $("#popupProdBarNumInfoBox iframe").load(function() {
    $("#popupProdBarNumInfoBox").show();
  });
});


function fcancel_check(f) {
  var btn_text = $('#cancel_btn').text();
  var strArray = btn_text.split('하기');

  if (!confirm(strArray[0] + " 하시겠습니까?"))
    return false;

  var memo = f.cancel_memo.value;
  if (memo == "") {
    alert("사유를 입력해 주십시오.");
    return false;
  }

  return true;
}

$(function() {
  $("#cancel_btn").click(function(e) {
    e.preventDefault();

    $("#sod_fin_cancelfrm").toggleClass("collapse");
  });

  $(".delivery-confirm").click(function() {
    if (confirm("상품을 수령하셨습니까?\n\n확인시 배송완료 처리가됩니다.")) {
      return true;
    }
    return false;
  });

  $(document).on("DOMNodeInserted", '.mfp-content', function() {
    window.wheelzoom($('.mfp-img'));
  });
  $('.report-img-wrap').click(function() {
    window.wheelzoom($('.mfp-img'));
  });

  // 거래명세서 출력
  $("#send_statementBox").hide();
  $("#send_statementBox").css("opacity", 1);
  $("#send_statement").click(function() {
    $("#send_statementBox").show();
  });
});
</script>

<!-- 210512 전자계약서 팝업 -->
<div id="popupEformWrite">
  <div></div>
</div>

<style>
#popupEformWrite {
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index: 99999999;
  background-color: rgba(0, 0, 0, 0.6);
  display: table;
  table-layout: fixed;
  opacity: 0;
}

#popupEformWrite>div {
  width: 100%;
  height: 100%;
  display: table-cell;
  vertical-align: middle;
}

#popupEformWrite iframe {
  position: relative;
  width: 1024px;
  height: 700px;
  border: 0;
  background-color: #FFF;
  left: 50%;
  margin-left: -512px;
}

@media (max-width : 1240px) {
  #popupEformWrite iframe {
    width: 100%;
    height: 100%;
    left: 0;
    margin-left: 0;
  }
}

body.modal-open {
  overflow: hidden;
}
</style>

<script type="text/javascript">
$(function() {
  $("#popupEformWrite").hide();
  $("#popupEformWrite").css("opacity", 1);

  function writeEform(od_id) {
    $("#popupEformWrite > div").html("<iframe src='/shop/eform/popup.writeEform.php?od_id=" + od_id + "'>");
    $("#popupEformWrite iframe").load(function() {
      $("body").addClass('modal-open');
      $("#popupEformWrite").show();
    });
  }

  $(".linkEformWrite").click(function(e) { // 계약서 생성 버튼
    e.preventDefault();

    var od = $(this).data('od');
    writeEform(od);
  });
  $('.linkEformSign').click(function(e) { // 계약서 작성 버튼
    e.preventDefault();

    var od = $(this).data('od');
    location.href = '/shop/eform/signEform.php?od_id=' + od;
  });
  $('.linkEformEdit').click(function(e) { // 내용변경 버튼
    e.preventDefault();

    var od = $(this).data('od');
    $("#popupEformWrite > div").html("<iframe src='/shop/eform/popup.editEform.php?od_id=" + od + "'>");
    $("#popupEformWrite iframe").load(function() {
      $("body").addClass('modal-open');
      $("#popupEformWrite").show();
    });
  });
  $('.linkEformView').click(function(e) { // 계약서 다운로드 버튼
    e.preventDefault();

    var od = $(this).data('od');
    window.open('/shop/eform/downloadEform.php?od_id=' + od);
  });

  <?php
  if($_GET['result'] == 'writeEform' && (!$eform["dc_id"] || $eform["dc_status"] == '0')) {
  ?>
  if (confirm('수급자 주문이 완료되었습니다.\n계약서를 생성하시겠습니까?')) {
    writeEform('<?=$od["od_id"]?>');
  }
  <?php
  } else if($_GET['result'] == 'writeEform' && $eform["dc_status"] == '1') {
  ?>
  if (confirm('계약서가 생성되었습니다.\n계약서를 작성하시겠습니까?')) {
    location.href = '/shop/eform/signEform.php?od_id=' + <?=$od["od_id"]?>;
  }
  <?php
  }
  ?>

  <?php
  $eform_check = sql_fetch("
    SELECT
      hex(dc_id) as uuid
    FROM
      eform_document
    WHERE
      od_id = '$od_id' and
      dc_status = '11' and
      entId = '{$member['mb_entId']}'
  ");

  if($eform_check['uuid']) {
  ?>
  if (confirm('수급자 계약서를 작성하시겠습니까?')) {
    window.location.href = 'simple_eform.php?dc_id=<?=$eform_check['uuid']?>';
  }
  <?php
  }
  ?>

  <?php

  // 튜토리얼 
  $t_sql = "SELECT * FROM tutorial
  WHERE 
    t_data = '{$od_id}' AND 
    mb_id = '{$member['mb_id']}' AND
    t_type = 'recipient_order'
  ";
  $t_result = sql_fetch($t_sql);

  $t_document = get_tutorial('document');
  if ($t_result['t_id'] && $t_document['t_state'] == '0' && ($eform['dc_status'] == '2' || $eform['dc_status'] == '3')) {
  ?>
  show_eroumcare_popup({
    title: '전자문서 확인',
    content: '작성한 전자 계약서를<br/>확인하시겠습니까?',
    activeBtn: {
      text: '전자계약서확인',
      href: '/shop/electronic_manage.php'
    },
    hideBtn: {
      text: '다음에',
    }
  });
  <?php } ?>
});
</script>
<!-- 210512 전자계약서 팝업 -->

<script>
$(function() {
  $('.report-img-wrap').magnificPopup({
    delegate: 'a',
    type: 'image',
    image: {
      titleSrc: function(item) {

        var $div = $('<div>');

        // 원본크기
        var $btn_zoom_orig = $('<button type="button" class="btn-bottom btn-zoom-orig">원본크기</button>')
          .click(function() {
            $btn_zoom_orig.hide();
            $btn_zoom_fit.show();

            $(item.img).css('max-width', 'unset');
            $(item.img).css('max-height', 'unset');
          });

        // 창맞추기
        var $btn_zoom_fit = $('<button type="button" class="btn-bottom btn-zoom-fit">창맞추기</button>"')
          .hide()
          .click(function() {
            $btn_zoom_orig.show();
            $btn_zoom_fit.hide();

            $(item.img).css('max-width', '100%');
            $(item.img).css('max-height', '100%');
          });

        // 다운로드
        var $btn_download = $('<a class="btn-bottom btn-download">다운로드</a>')
          .attr('href', item.src)
          .attr('download', '설치이미지_' + item.index + '.jpg');

        // 회전
        var rotate_deg = 0;
        var $btn_rotate = $('<button type="button" class="btn-bottom btn-rotate">회전</button>')
          .click(function() {
            rotate_deg = (rotate_deg + 90) % 360;
            $(item.img).css('transform', 'rotate(' + rotate_deg + 'deg)')
          });

        return $div.append(
          $btn_zoom_orig,
          $btn_zoom_fit,
          $btn_download,
          $btn_rotate);
      },
    },
    gallery: {
      enabled: true,
      tPrev: '이전', // title for left button
      tNext: '다음', // title for right button
      tCounter: '%curr% / %total%'
    },
  });
});
</script>