<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

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
      $ordZip = [];
      $ordZip[0] = substr($result[0]["ordZip"], 0, 3);
      $ordZip[1] = substr($result[0]["ordZip"], 3, 2);

      sql_query("
        UPDATE {$g5["g5_shop_order_table"]} SET
            mb_id = '{$result[0]["usrId"]}'
          , od_penId = '{$result[0]["penId"]}'
          , od_delivery_text = '{$result[0]["ordWayNum"]}'
          , od_delivery_company = '{$result[0]["delSerCd"]}'
          , od_b_name = '{$result[0]["ordNm"]}'
          , od_b_tel = '{$result[0]["ordCont"]}'
          , od_memo = '{$result[0]["ordMeno"]}'
          , od_b_zip1 = '{$ordZip[0]}'
          , od_b_zip2 = '{$ordZip[1]}'
          , od_b_addr1 = '{$result[0]["ordAddr"]}'
          , od_b_addr2 = '{$result[0]["ordAddrDtl"]}'
          , payMehCd = '{$result[0]["payMehCd"]}'
          , eformYn = '{$result[0]["eformYn"]}'
          , staOrdCd = '{$result[0]["staOrdCd"]}'
        WHERE od_id = '{$od["od_id"]}'
      ");
      $od = sql_fetch("SELECT * FROM {$g5["g5_shop_order_table"]} WHERE od_id = '{$od["od_id"]}'");

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
?>

<script type="text/javascript">
// 주문 완료인경우
if (document.referrer.indexOf("shop/orderform.php") >= 0) {

  // 네이버
  if (!wcs_add) var wcs_add={};
  wcs_add["wa"] = "<?php echo NAVER_WCS_WA; ?>";
  if (!_nasa) var _nasa={};
  _nasa["cnv"] = wcs.cnv("1","<?php echo $tot_price; ?>");
  wcs_do(_nasa);

  // 다음
  //<![CDATA[
  var DaumConversionDctSv="type=P,orderID=<?php echo $od_id; ?>,amount=<?php echo $tot_price; ?>";
  var DaumConversionAccountID="<?php echo DAUM_CONVERSION_ACCOUNT_ID; ?>";
  if(typeof DaumConversionScriptLoaded=="undefined"&&location.protocol!="file:"){
    var DaumConversionScriptLoaded=true;
    document.write(unescape("%3Cscript%20type%3D%22text/javas"+"cript%22%20src%3D%22"+(location.protocol=="https:"?"https":"http")+"%3A//t1.daumcdn.net/cssjs/common/cts/vr200/dcts.js%22%3E%3C/script%3E"));
  }
  //]]>

}
</script>

<!-- 210326 배송정보팝업 -->
<div id="popupProdDeliveryInfoBox" class="listPopupBoxWrap">
  <div>
  </div>
</div>

<style>
.listPopupBoxWrap { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
.listPopupBoxWrap > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
.listPopupBoxWrap iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

@media (max-width : 750px){
  .listPopupBoxWrap iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}
</style>

<script type="text/javascript">
$(function() {
  $(".listPopupBoxWrap").hide();
  $(".listPopupBoxWrap").css("opacity", 1);

  $(".popupDeliveryInfoBtn").click(function(e) {
    e.preventDefault();

    var od = $(this).attr("data-od");
    $("#popupProdDeliveryInfoBox > div").append("<iframe src='/shop/popup.prodDeliveryInfo.php?od_id=" + od + "'>");
    $("#popupProdDeliveryInfoBox iframe").load(function() {
      $("#popupProdDeliveryInfoBox").show();
    });
  });
});
</script>
<!-- 210326 배송정보팝업 -->

<link rel="stylesheet" href="<?=$SKIN_URL?>/css/product_order_210324.css?edited=2106041957">
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
          for($i=0; $i < count($item); $i++) {
            $prodMemo = ""; $ordLendDtm = "";
            for($k=0; $k < count($item[$i]['opt']); $k++) {
              $prodMemo = ($prodMemo) ? $prodMemo : $item[$i]["prodMemo"];
              $ordLendDtm = ($ordLendDtm) ? $ordLendDtm : date("Y-m-d", strtotime($item[$i]["ordLendStrDtm"]))." ~ ".date("Y-m-d", strtotime($item[$i]["ordLendEndDtm"]));

              $rowspan = (substr($item[$i]["ca_id"], 0, 2) == 20) ? 3 : 1;
          ?>
          <div class="list">
            <ul class="cb">
              <li class="pro">
                <div class="img"><img src="/data/item/<?=$item[$i]['thumbnail']?>" onerror="this.src = '/shop/img/no_image.gif';"></div>
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
                    <?php echo $item[$i]['it_name']; ?>
                    <?php if($item[$i]['opt'][$k]['ct_stock_qty']) echo '[재고소진]'; ?>
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
                <?php if($item[$i]['opt'][$k]['ct_status'] !== "취소" && $item[$i]['opt'][$k]['ct_status'] !== "주문무효"){ ?>
                  <a href="#" class="btn-01 btn-0 popupProdBarNumInfoBtn" data-id="<?=$od["od_id"]?>" data-ct-id="<?=$item[$i]['opt'][$k]["ct_id"]?>" ><img src="<?=$SKIN_URL?>/image/icon_02.png" alt=""> 바코드 확인</a>
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
          </div>
          <?php } ?>
          <?php } ?>
        </div>
      </div>

      <?php if($od["od_stock_insert_yn"] == "N") { ?>
      <div class="order-info">
        <div class="top">
          <h5>받으시는 분</h5>
        </div>
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
                <p><?php echo get_text(sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon'])); ?></p>
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
      </div>
      <?php } ?>
      <?php 
      $sql_od ="select `od_hide_control` from `g5_shop_order` where `od_id` = '".$od['od_id']."'";
      $result_od = sql_fetch($sql_od);
      ?>
      <?php if(!$result_od['od_hide_control']) { ?>
      <div class="list-more">
        <p><a href="javascript:void(0)" onclick="hide_control('<?=$od["od_id"] ?>')">주문내역 숨김처리</a></p>
        <p >*해당 주문을 숨김처리하면 주문내역에 노출되지 않습니다.<br>*숨김처리는 주문취소가 되지 않습니다.</p>
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
            <?php echo $typereceipt['ot_btel'] ? '( ' . $typereceipt['ot_btel'] : ''; ?>
            <?php echo $typereceipt['ot_btel'] ? ')': ''; ?></span>
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
              <span><?=number_format($tot_price - $od["od_send_cost"])?> 원</span>
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
        </ul>
        <?php 
        // $total_price = $tot_price - $od['od_cart_discount'] - $od['od_cart_discount2'] ;
        $total_price = $tot_price - $od['od_cart_discount2'] ;
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
        <a href="#" id="cancel_btn" type="button" data-toggle="collapse" href="#sod_fin_cancelfrm" aria-expanded="false" aria-controls="sod_fin_cancelfrm"><?php echo $btn_name ?></a>
        <div class="h15"></div>
        <div id="sod_fin_cancelfrm" class="collapse">
          <div class="well">
            <form class="form" role="form" method="post" action="<?php echo $action_url ?>" onsubmit="return fcancel_check(this);">
            <input type="hidden" name="od_id"  value="<?php echo $od['od_id']; ?>">
            <input type="hidden" name="token"  value="<?php echo $token; ?>">
            <input type="hidden" name="type" value="<?php echo $type ?>">
            <input type="hidden" name="to" value="<?php echo $to ?>">
              <div class="input-group input-group-sm">
                <!--<span class="input-group-addon">사유</span>-->
                <select name="request_reason_type" class="form-control" style="display: table-cell; width: 100px; margin-right: 10px;">
                  <option value="단순변심">단순변심</option>
                  <option value="제품파손">제품파손</option>
                  <option value="제품하자">제품하자</option>
                  <option value="오주문">오주문</option>
                  <option value="오배송">오배송</option>
                  <option value="A/S">A/S</option>
                  <option value="기타">기타</option>
                </select>
                <input type="text" name="cancel_memo" id="cancel_memo" required class="form-control input-sm" size="40" maxlength="100" style="width: calc(100% - 110px); float: none;">
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
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
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
#send_statementBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#send_statementBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#send_statementBox iframe { position: relative; width: 730px; height: 800px; border: 0; background-color: #FFF; left: 50%; margin-left: -365px; }

@media (max-width : 750px) {
  #send_statementBox iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
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
    if(data=="S") {
      alert('삭제가 완료되었습니다.');
      location.href="<?=G5_URL?>/shop/orderinquiry.php"; 
    }
});
}

$(".popupProdBarNumInfoBtn").click(function(e) {
  e.preventDefault();

  var od_id = $(this).attr("data-id");
  var ct_id = $(this).attr("data-ct-id");

  $("#popupProdBarNumInfoBox > div").append("<iframe src='<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form_4.php?od_id=" + od_id +  "&ct_id=" + ct_id +"'>");
  $("#popupProdBarNumInfoBox iframe").load(function(){
    $("#popupProdBarNumInfoBox").show();
  });
});


function fcancel_check(f) {
  var btn_text = $('#cancel_btn').text();
  var strArray = btn_text.split('하기');

  if(!confirm(strArray[0] + " 하시겠습니까?"))
    return false;

  var memo = f.cancel_memo.value;
  if(memo == "") {
    alert("사유를 입력해 주십시오.");
    return false;
  }

  return true;
}

$(function(){
  $("#cancel_btn").click(function(e){
    e.preventDefault();

    $("#sod_fin_cancelfrm").toggleClass("collapse");
  });

  $(".delivery-confirm").click(function(){
    if(confirm("상품을 수령하셨습니까?\n\n확인시 배송완료 처리가됩니다.")) {
      return true;
    }
    return false;
  });

  // 거래명세서 출력
  $("#send_statementBox").hide();
  $("#send_statementBox").css("opacity", 1);
  $("#send_statement").click(function() {
    $("#send_statementBox").show();
  });

  /* 바코드저장 */
  var stoldList = [];
  var stoIdData = "<?=$stoIdData?>";
  if(stoIdData){
    var sendData = {
      stoId : stoIdData
    }

    $.ajax({
      url : "https://system.eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do",
      type : "POST",
      dataType : "json",
      contentType : "application/json; charset=utf-8;",
      data : JSON.stringify(sendData),
      success : function(res){
        $.each(res.data, function(key, value){
          $("." + value.stoId).text(value.prodBarNum);
        });

        if(res.data){
          stoldList = res.data;
        }
      }
    });
  }

  $("#prodBarNumSaveBtn").click(function() {
    var ordId = "<?=$od["ordId"]?>";
    var eformYn = "<?=$od["eformYn"]?>";
    var insertBarCnt = 0;

    if(ordId) {
      var productList = <?=($prodList) ? json_encode($prodList) : "[]"?>;
      $.each(productList, function(key, value) {
        var prodBarNumItem = $(".prodBarNumItem_" + value.penStaSeq);
        var prodBarNum = "";

        for(var i = 0; i < prodBarNumItem.length; i++) {
          if("<?=$od["od_status"]?>" == "완료"){
            if(!$(prodBarNumItem[i]).val()){
              alert("바코드를 입력해주시길 바랍니다.");
              return false;
            }
          }
          prodBarNum += (prodBarNum) ? "," : "";
          prodBarNum += $(prodBarNumItem[i]).val();

          if($(prodBarNumItem[i]).val()){
            insertBarCnt++;
          }
        }

        productList[key]["prodBarNum"] = prodBarNum;
      });

      var sendData = {
        ordId : "<?=$od["ordId"]?>",
        delGbnCd : "",
        ordWayNum : "",
        delSerCd : "",
        ordNm : $("#od_b_name").val(),
        ordCont : $("#od_b_hp").val(),
        ordMeno : $("#od_memo").val(),
        ordZip : $("#od_b_zip").val(),
        ordAddr : $("#od_b_addr1").val(),
        ordAddrDtl : $("#od_b_addr2").val(),
        eformYn : eformYn,
        staOrdCd : "<?=$od["staOrdCd"]?>",
        prods : productList
      }

      $.ajax({
        url : "https://system.eroumcare.com/api/pen/pen5000/pen5000/updatePen5000.do",
        type : "POST",
        dataType : "json",
        contentType : "application/json; charset=utf-8;",
        data : JSON.stringify(sendData),
        success : function(result){
          if(result.errorYN == "N") {
            alert("저장이 완료되었습니다.");

            $.ajax({
              url : "/shop/ajax.order.prodBarNum.cnt.php",
              type : "POST",
              data : {
                od_id : "<?=$od_id?>",
                cnt : insertBarCnt
              }
            });
          } else {
            alert(result.message);
          }
        }
      });
    } else {
      var delYn = "Y";
      var changeStatus = true;
      if("<?=$od["od_status"]?>" == "완료"){
        delYn = "N";
        $.each(stoldList, function(key, value){
          if(!$("." + value.stoId).val()){
            changeStatus = false;
            alert("바코드를 입력해주시길 바랍니다.");
            return false;
          }
        });
      }

      $.each(stoldList, function(key, value){
        var sendData = {
          usrId : "<?=$od["mb_id"]?>",
          prods : [
            {
              stoId : value.stoId,
              prodColor : value.prodColor,
              prodBarNum : ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
              prodManuDate : value.prodManuDate,
              stateCd : value.stateCd,
              stoMemo : (value.stoMemo) ? value.stoMemo : "",
              delYn : delYn
            }
          ]
        }

        if($("." + value.stoId).val()){
          insertBarCnt++;
        }

        $.ajax({
          url : "https://system.eroumcare.com/api/pro/pro2000/pro2000/updatePro2000ProdInfoAjaxByShop.do",
          type : "POST",
          dataType : "json",
          async : false,
          contentType : "application/json; charset=utf-8;",
          data : JSON.stringify(sendData),
          success : function(result){
            if(result.errorYN == "Y"){
              alert(result.message);
              return false;
            }
          }
        });
      });

      $.ajax({
        url : "/shop/ajax.order.prodBarNum.cnt.php",
        type : "POST",
        data : {
          od_id : "<?=$od_id?>",
          cnt : insertBarCnt
        }
      });
      alert("저장이 완료되었습니다.");
    }
  });
});
</script>

<!-- 210512 전자계약서 팝업 -->
<div id="popupEformWrite">
  <div></div>
</div>

<style>
#popupEformWrite { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popupEformWrite > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popupEformWrite iframe { position: relative; width: 1024px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -512px; }

@media (max-width : 1240px){
  #popupEformWrite iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
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
    $("#popupEformWrite > div").html("<iframe src='/shop/eform/popup.writeEform.php?od_id="+od_id+"'>");
    $("#popupEformWrite iframe").load(function(){
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
    location.href = '/shop/eform/signEform.php?od_id='+od;
  });
  $('.linkEformEdit').click(function(e) { // 내용변경 버튼
    e.preventDefault();

    var od = $(this).data('od');
    $("#popupEformWrite > div").html("<iframe src='/shop/eform/popup.editEform.php?od_id="+od+"'>");
    $("#popupEformWrite iframe").load(function(){
      $("body").addClass('modal-open');
      $("#popupEformWrite").show();
    });
  });
  $('.linkEformView').click(function(e) { // 계약서 다운로드 버튼
    e.preventDefault();

    var od = $(this).data('od');
    window.open('/shop/eform/downloadEform.php?od_id='+od);
  });

  <?php
  if($_GET['result'] == 'writeEform' && (!$eform["dc_id"] || $eform["dc_status"] == '0')) {
  ?>
  if(confirm('수급자 주문이 완료되었습니다.\n계약서를 생성하시겠습니까?')) {
    writeEform('<?=$od["od_id"]?>');
  }
  <?php
  } else if($_GET['result'] == 'writeEform' && $eform["dc_status"] == '1') {
  ?>
  if(confirm('계약서가 생성되었습니다.\n계약서를 작성하시겠습니까?')) {
    location.href = '/shop/eform/signEform.php?od_id='+<?=$od["od_id"]?>;
  }
  <?php
  }
  ?>
});
</script>
<!-- 210512 전자계약서 팝업 -->
