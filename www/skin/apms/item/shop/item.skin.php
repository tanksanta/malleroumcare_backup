<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 관련상품 전체 추출을 위해서 재세팅함
$rmods = 100;
$rrows = 1;
$sendData = [];
$sendData['usrId'] = $member['mb_id'];
$resInfo = get_eroumcare(EROUMCARE_API_ENT_ACCOUNT, $sendData);
if(!$member['mb_id']){
  alert('회원만 이용 가능합니다.',G5_BBS_URL.'/login.php');
}
if($member['mb_level']<5 && $member['mb_type'] !== 'normal'){ //관리자나 매니저 아이디는 시스템에 등록되어 있지 않음
  if($resInfo['data']['entConfirmCd']=="02"||!$resInfo['data']['entConfirmCd']){
    alert('승인된 회원만 이용 가능합니다.',G5_BBS_URL.'/login.php');
  }
}
$is_admin = is_admin($member['mb_id']);

// 버튼컬러
$btn1 = (isset($wset['btn1']) && $wset['btn1']) ? $wset['btn1'] : 'black';
$btn2 = (isset($wset['btn2']) && $wset['btn2']) ? $wset['btn2'] : 'color';

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$item_skin_url.'/style.css" media="screen">', 0);

if($is_orderable) echo '<script src="'.$item_skin_url.'/shop.js?v=20210906"></script>'.PHP_EOL;

// 이미지처리
$j=0;
$images = array();
for($i=1; $i<=10; $i++) {
  if(!$it['it_img'.$i]) continue;

  $org_url = G5_DATA_URL.'/item/'.$it['it_img'.$i];
  //$img = apms_thumbnail($org_url, 400, 400, false, true);
  //$thumb_url = ($img['src']) ? $img['src'] : $org_url;
  $images[$j] = array(
    'src' => $org_url,
    'href' => G5_SHOP_URL.'/largeimage.php?it_id='.$it['it_id'].'&amp;ca_id='.$ca_id.'&amp;no='.$i
  );
  $j++;
}

// 카운팅
$it_comment_cnt = ($it['pt_comment'] > 0) ? ' <b class="orangered en">'.number_format($it['pt_comment']).'</b>' : '';
$it_use_cnt = ($item_use_count > 0) ? ' <b class="orangered en">('.number_format($item_use_count).')</b>' : '';
$it_qa_cnt = ($item_qa_count > 0) ? ' <b class="orangered en">'.number_format($item_qa_count).'</b>' : '';

// 판매자
$is_seller = ($it['pt_id'] && $it['pt_id'] != $config['cf_admin']) ? true : false;

if ( THEMA_KEY == 'partner' ) {
  $it_use = 'it_use_partner';
} else {
  $it_use = 'it_use';
}

if ( THEMA_KEY == 'partner' && !$member['mb_id'] ) {
  $is_orderable = false;
}

include_once(THEMA_PATH.'/side/list-cate-side.php');

# 210131 재고수량 조회
$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
if(substr($it["ca_id"], 0, 2) == "20") {
  $sendData["status02"] = true;
}
$prodsSendData = [];

if($it["optionList"]) {
  foreach($it["optionList"] as $optionData) {
    $prodsData = [];
    $prodsData["prodId"] = $it["it_id"];
    $prodsData["prodColor"] = $optionData["color"];
    $prodsData["prodSize"] = $optionData["size"];
    array_push($prodsSendData, $prodsData);
  }
} else {
  $prodsData = [];
  $prodsData["prodId"] = $it["it_id"];
  $prodsData["prodColor"] = "";
  $prodsData["prodSize"] = "";
  array_push($prodsSendData, $prodsData);
}

$sendData["prods"] = $prodsSendData;

$main_ca = '';
$it_list_url = '';
if ($it['ca_id']) {
  //$main_ca = substr($it['ca_id'], 0, 2);
  $main_ca = $it['ca_id'];
  $it_list_url = "/shop/list.php?ca_id={$main_ca}";
}
?>


<!--
<div class="samhwa-item-nav">
  <a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $ca_id; ?>&page=<?php echo $_GET['page']; ?>">< 제품 상세 정보</a>
</div>
-->

<style>
  .item-head .item-thumb img { width: 100px; height: 100px; }
  .ca_info { font-weight: 400 !important; }
  .ca_info > .help-block { float: right; margin-right:20px; font-size: 14px; }

  .detailInfo { width: 100%; margin-top: 5px; }
  .detailInfo > li { width: 100%; display: table; table-layout: fixed; }
  .detailInfo > li > span { display: table-cell; vertical-align: middle; font-size: 12px; }
  .detailInfo > li > span.infoLabel { width: 60px; }
  .detailInfo > li > span.infoLabel > span:first-of-type { margin-right: 5px; }

  .selfPriceInfo { width: 100%; border-left: 2px solid #ddd; padding: 0 15px; }
  .selfPriceInfo > .title { width: 100%; line-height: 16px; font-weight: 500; color: #333; }
  .selfPriceInfo > p { width: 100%; line-height: 16px; margin-top: 6px; }

  #item3dViewBtn { position: absolute; width: 130px; height: 40px; line-height: 38px; z-index: 10; top: 0; right: 25px; cursor: pointer; border-radius: 10px; background-color: #FFF; border: 1px solid #E2E2E2; text-align: center; font-weight: bold; font-size: 13px; }
  #item3dViewBtn > img { margin-right: 10px; }

  #item3dViewBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
  #item3dViewBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
  #item3dViewBox iframe { position: relative; width: 700px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; }
  .margin-left { margin-left:15px; }
  .margin-right { margin-left:50px; }
  .item-form.npay_btn_list th, .item-form .npay_btn_list td {padding: 0 !important;}
  @media (max-width : 750px){
    #item3dViewBox iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
  }
  @media (max-width: 991px) {
    #samhwa-mobile-tail { display:none; }
    .margin-leftm{ margin: 0 15px; }
    .samhwa-item-info .selfPriceInfo { display: none; }
  }
  @media (max-width: 960px){
    body { padding-bottom: 130px; }
    .btn_top_scroll { bottom: 140px; }
    .selfPriceInfo > p { font-size: 14px; }     
  }
</style>

<div id="item3dViewBox">
  <div>
    <iframe src="<?=G5_SHOP_URL?>/item3dview.php?it_id=<?=$it["it_id"]?>"></iframe>
  </div>
</div>

<div class="item-head">
  <div class="samhwa-item-head-container">
    <?php if(json_decode($it["it_img_3d"], true)){ ?>
    <div id="item3dViewBtn">
      <img src="/img/item3dviewVisual.jpg">
      <span>상품보기</span>
    </div>
    <?php } ?>
    <div class="samhwa-item-image">
      <div class="item-image">
        <?php if($it["prodSupYn"] == "N"){ ?>
        <b class="supInfo">비유통 상품</b>
        <?php } ?>
        <div class="item_image_slider">
          <?php
          foreach($images as $img) {
          ?>
          <!--<a href="<?=$img['href']?>" class="popup_item_image image_slide" target="_blank" title="크게보기">
            <img src="<?=$img['src']?>" alt="상품 이미지">
          </a>-->
          <div class="popup_item_image image_slide">
            <img src="<?=$img['src']?>" alt="상품 이미지">
          </div>
          <?php
          }
          ?>
        </div>
        <!--<a href="<?php echo $item_image_href;?>" id="item_image_href" class="popup_item_image" target="_blank" title="크게보기">
          <img id="item_image" src="<?php echo $item_image;?>" alt="">
        </a>-->
        <?php if($wset['shadow']) echo apms_shadow($wset['shadow']); //그림자 ?>

        <?php if($it["it_expected_warehousing_date"] !== ""){ ?>
        <div class="item-expected-warehousing-date"><?php echo $it["it_expected_warehousing_date"];?></div>
        <?php } ?>
      </div>
      <!--<div class="item-thumb text-center">
        <?php
        for($i=0; $i < count($thumbnails); $i++) {
          echo $thumbnails[$i];
        }
        ?>
      </div>-->
      <script>
      $(function(){
        // 이미지 슬라이드
        $('.item_image_slider').slick({
          dots: true,
          arrows: false,
          autoplay: true,
          autoplaySpeed: 5000
        });
        // 이미지 크게보기
        /*$(".popup_item_image").click(function() {
          var url = $(this).attr("href");
          var top = 10;
          var left = 10;
          var opt = 'scrollbars=yes,top='+top+',left='+left;
          popup_window(url, "largeimage", opt);
          return false;
        });*/
        // 이미지 3d보기
        $("#item3dViewBox").hide();
        $("#item3dViewBox").css("opacity", 1);
        $("#item3dViewBtn").click(function(){
          $("#item3dViewBox").show();
        });
      });
      </script>
      <div class="h30 visible-xs"></div>
    </div>
    <div class="samhwa-item-info-mobile mobile">
      <div class="margin-left" style="zoom: 0.8;">
        <div class="top-info-wrap">
          <ul class="top-info-list">
            <img src="<?php echo THEMA_URL; ?>/assets/img/check-icon.png" style="vertical-align: middle; ">
            <li><?=$it["ca_name"]?></li>
            <li style="font-weight: 100;">|</li>
            <li><?=$it["it_taxInfo"]?>상품</li>
          </ul>
          <?php if(!is_benefit_item($it)) { ?>
          <span style="vertical-align: middle; float: right;">급여코드 : <?php echo $it['ProdPayCode']; ?></span>
          <?php } ?>
        </div>
        <!-- <p class="help-block">* 주문가능 수량 : <?=number_format(get_it_stock_qty($it_id))?>개</p> -->
        <h1 class="item-head-title" style="font-size: 42px;"><?php echo stripslashes($it['it_name']); // 상품명 ?></h1>
        <p class="price-type">
          <?php if($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) { ?>
                <?php if(is_benefit_item($it)) { ?>
                판매가
                <?php } else { ?>
                급여가
                <?php } ?>
          <?php } else { ?>
            <?php if($member["mb_level"] == "4") { ?>
              VIP판매가
            <?php } else { ?>
              판매가
          <?php }
          }
          ?>
        </p>
        <p class="price-num">
          <?php
          if($member["mb_id"]) {
            if($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) {
                echo number_format($it["it_cust_price"]);
            } else {
              if($it['entprice']) {
                // 사업소별 지정 가격
                echo number_format($it["entprice"]);
              }else if($member["mb_level"] == "3") {
                //사업소 가격
                echo number_format($it["it_price"]);
              } else if($member["mb_level"] == "4") {
                //우수 사업소 가격
                echo ($it["it_price_dealer2"]) ? number_format($it["it_price_dealer2"]) : number_format($it["it_price"]);
              } else {
                echo number_format($it["it_price"]);
              }
            }
          }
          ?>
        </p>
        <p class="price-won">원</p>
        <?php
        $sale_cnt_txt = [];
        if ($_COOKIE["viewType"] != "basic" && !$it['entprice'] && $member['mb_type'] === 'default') {
          if (($is_admin == "super" || $member['mb_level'] == "3" || $member['mb_level'] == "9") || !$it['it_sale_percent_great']) {
            if($it["it_sale_cnt"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent"]) . '원';
            }
            if($it["it_sale_cnt_02"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_02"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_02"]) . '원';
            }
            if($it["it_sale_cnt_03"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_03"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_03"]) . '원';
            }
            if($it["it_sale_cnt_04"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_04"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_04"]) . '원';
            }
            if($it["it_sale_cnt_05"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_05"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_05"]) . '원';
            }
          } else if($member['mb_level'] == "4" && $it['it_sale_percent_great']) {
            if($it["it_sale_cnt"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great"]) . '원';
            }
            if($it["it_sale_cnt_02"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_02"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_02"]) . '원';
            }
            if($it["it_sale_cnt_03"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_03"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_03"]) . '원';
            }
            if($it["it_sale_cnt_04"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_04"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_04"]) . '원';
            }
            if($it["it_sale_cnt_05"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_05"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_05"]) . '원';
            }
          }
        }

        foreach($sale_cnt_txt as $txt_row) {
          echo '<br><span class="bottom-info-line">└</span>';
          echo '<span class="bottom-info">';
          echo $txt_row;
          echo '</span>';
        }
        ?>
        <?php if($it['it_basic']) { // 기본설명 ?>
        <p class="help-block"><?php echo $it['it_basic']; ?></p>
        <?php } ?>
        <table class="table item-form">
          <tbody>
            <tr>
              <th scope="row">
                <?php if(!is_benefit_item($it)) { ?>
                급여가(정가)
                <?php } else { ?>
                정가
                <?php } ?>
              </th>
              <td>
                <?php echo display_price($it['it_cust_price']); ?>
                
                <?php if(!is_benefit_item($it)) { ?>
                <p class="personal-price">
                  ※ 본인부담금 15%(<?=number_format($it["it_cust_price"] * 0.15)?>원), 9%(<?=number_format($it["it_cust_price"] * 0.09)?>원), 6%(<?=number_format($it["it_cust_price"] * 0.06)?>원)
                </p>
                <?php } ?>
              </td>
            </tr>

            <tr>
              <th scope="row">상품상세</th>
              <td>
                <?php if(trim($it["prodSym"])) { ?>
                <label class="quality-type">재질</label><label class="quality-text"><?=$it["prodSym"]?></label>
                <br>
                <?php } ?>
                <?php if(trim($it["prodSizeDetail"])) { ?>
                <label class="quality-type">사이즈</label><label class="quality-text"><?=$it["prodSizeDetail"]?></label>
                <br>
                <?php } ?>
                <?php if(trim($it["prodWeig"])) { ?>
                <label class="quality-type">중량</label><label class="quality-text"><?=$it["prodWeig"]?></label>
                <br>
                <?php } ?>
              </td>
            </tr>
            <?php if (!$it[$it_use]) { // 판매가능이 아닐 경우 ?>
            <tr><th scope="row">판매</th><td>판매중지</td></tr>
            <?php } else if ($it['it_tel_inq']) { // 전화문의일 경우 ?>
            <tr><th scope="row">판매</th><td>전화문의</td></tr>
            <?php } ?>
            <?php if($it['it_buy_min_qty']) { ?>
            <tr><th>최소구매수량</th><td><?php echo number_format($it['it_buy_min_qty']); ?> 개</td></tr>
            <?php } ?>
            <?php if($it['it_buy_max_qty']) { ?>
            <tr><th>최대구매수량</th><td><?php echo number_format($it['it_buy_max_qty']); ?> 개</td></tr>
            <?php } ?>
            <?php
            $ct_send_cost_label = '배송';
            $sc_price_info_spliter = '<span style="margin:0 8px 0 8px; font-size: 12px; font-weight: 100 !important; color: #d9d9d9;">|</span> ';

            if ($is_samhwa_partner) { // 파트너 유저 배송비
              $sc_price_info = "";
              if ($it['it_sc_type_partner'] != 1) {
                $sc_price_info = "배송비는 {$it['it_sc_qty_partner']}개당 배송비 부가 ({$it['it_sc_price_partner']}원)<br>* 도서산간지역은 추가배송비가 발생합니다.";
              }

              if ($it['it_sc_type_partner'] == 0) { // 쇼핑몰 디폴트 셋팅 시
                $item_price = samhwa_price($it, THEMA_KEY);
                $send_cost = get_item_sendcost_by_default_case($item_price);

                if ($send_cost > 0) {
                  // $sc_price_info = "배송비 {$send_cost}원{$sc_price_info_spliter}도서산간지역은 ".($send_cost + 2000)."원 추가됩니다.";
                  $sc_price_info = "배송비 {$send_cost}원";
                } else {
                  // $sc_price_info = "무료배송{$sc_price_info_spliter}도서산간지역은 추가배송비가 발생합니다.";
                  $sc_price_info = "무료배송";
                }
              }
            } else { // 파트너 유저 아닐 시
              $sc_price_info = "";
              if($it['it_sc_type'] == 1) {
                $sc_price_info = "무료배송";
              }
              if ($it['it_sc_type'] != 1) {
                $number_cost=number_format($it['it_sc_price']);
                // $sc_price_info = "배송비 {$number_cost}원{$sc_price_info_spliter}도서산간지역은 ".number_format($it['it_sc_price'] + 2000)."원 추가됩니다.";
                $sc_price_info = "배송비 {$number_cost}원";
              }

              if ($it['it_sc_type'] == 0) { // 쇼핑몰 디폴트 셋팅 시
                $item_price = samhwa_price($it, THEMA_KEY);
                $send_cost = get_item_sendcost_by_default_case($item_price);

                if ($send_cost > 0) {
                  // $sc_price_info = "배송비 {$send_cost}원{$sc_price_info_spliter}도서산간지역은 ".($send_cost + 2000)."원 추가됩니다.";
                  $sc_price_info = "배송비 {$send_cost}원";
                } else {
                  // $sc_price_info = "무료배송{$sc_price_info_spliter}도서산간지역은 추가배송비가 발생합니다.";
                  $sc_price_info = "무료배송";
                }
              }
            }
			$sc_price_info = $sc_price_info . "<br /><span style=\"font-size:11px; color:#7F7F7F;\">* 주문완료 후, 2~7일(주말, 공휴일 제외) 이내 배송<br>&nbsp;(제조사의 사정으로 출고가 지연될 경우 별도 안내)</span>";
            ?>
            <tr>
              <th><?php echo $ct_send_cost_label; ?></th>
              <td>
                <?php
                $sc_price = 10;
                if($it['it_sc_type'] < 4 && $it['it_sc_type'] != 1) {
                  $sc_price_info = number_format($sc_price).'만원 이상 무료배송<br>'.$sc_price_info;
                }
                if($it['it_sc_type'] == 5) {
                  $sc_price_info = '짝수 주문시 무료배송<br>기본배송비 3,300원';
                }
                if ($it['it_delivery_cnt'] > 0) {
                  $sc_price_info = "<span style=\"font-size:13px; color:#ef7c00;\">본 상품은 {$it['it_delivery_cnt']}개 주문 시 한 박스로 포장됩니다.</span><br>".$sc_price_info;
                }
                ?>
                <p class="sc_price_info" style="font-size: 13px">
                  <?php echo $sc_price_info; ?>
                </p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <script>
    $(function() {
      $('.samhwa-item-info-opener').click(function() {
        $('.samhwa-item-info').show();
      });
      $('.item-info-arrowbtn').click(function() {
        $('.samhwa-item-info').hide();
      });
    });
    </script>
    <?php if ($is_orderable && $member['mb_type'] !== 'normal') { ?>
    <div class="samhwa-item-info-opener mobile">
      <ul class="item-buy-btn">
        <li class="buy"><input type="submit" onclick="document.pressed=this.value;" value="상품주문" class="btn btn-color btn-block <?php echo $it['prodSupYn'] === 'N' ? 'disabled' : ''; ?>"></li>
        <li class="cart">
          <div class="cart-ok">
            <p>장바구니에 담았습니다.</p>
            <ul>
              <li><a href='#' class="cart-ok-close">쇼핑 계속하기</a></li>
              <li><a class="bg" href='<?php echo G5_SHOP_URL; ?>/cart.php'>장바구니 보기</a></li>
            </ul>
          </div>
          <input type="submit" onclick="document.pressed=this.value;" value="장바구니" class="btn btn-color btn-block">
        </li>
      </ul>
    </div>
    <?php } ?>
    <div class="samhwa-item-info">
      <div class="margin-leftm">
        <div class="item-info-arrowbtn mobile">
          <img src="<?php echo THEMA_URL; ?>/assets/img/icon_arrow_down.png" class="arrow" />
        </div>
        <div class="top-info-wrap pc">
          <ul class="top-info-list">
            <img src="<?php echo THEMA_URL; ?>/assets/img/check-icon.png" style="vertical-align: middle; ">
            <li><?=$it["ca_name"]?></li>
            <li style="font-weight: 100;">|</li>
            <li><?=$it["it_taxInfo"]?>상품</li>
          </ul>
          <?php if(!is_benefit_item($it)) { ?>
          <span style="vertical-align: middle; float: right;">급여코드 : <?php echo $it['ProdPayCode']; ?></span>
          <?php } ?>
        </div>

        <h1 class="item-head-title pc"><?php echo stripslashes($it['it_name']); // 상품명 ?></h1>
        <p class="price-type">
          <?php if($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) { ?>
                <?php if(is_benefit_item($it)) { ?>
                판매가
                <?php } else { ?>
                급여가
                <?php } ?>
            <?php } else { ?>
            <?php if($member["mb_level"] == "4") { ?>
              VIP판매가
            <?php } else { ?>
                판매가
            <?php }
            }
            ?>
        </p>
        <p class="price-num">
          <?php
          if($member["mb_id"]) {
            if($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) {
                echo number_format($it["it_cust_price"]);
            } else {
              if($it['entprice']) {
                // 사업소별 지정 가격
                echo number_format($it["entprice"]);
              } else if($member["mb_level"] == "3") {
                //사업소 가격
                echo number_format($it["it_price"]);
              } else if($member["mb_level"] == "4") {
                //우수 사업소 가격
                echo ($it["it_price_dealer2"]) ? number_format($it["it_price_dealer2"]) : number_format($it["it_price"]);
              } else {
                echo number_format($it["it_price"]);
              }
            }
          }
          ?>
        </p>
        <p class="price-won">원</p>
        <!-- <p class="price-block">주문가능 수량 : 9,999개</p> -->
        <?php
        $sale_cnt_txt = [];
        $sale_percent_input = "";
        if ($_COOKIE["viewType"] != "basic" && !$it['entprice'] && $member['mb_type'] === 'default') {
          if(($is_admin == "super" || $member['mb_level'] == "3" || $member['mb_level'] == "9")||!$it['it_sale_percent_great']) {
            if($it["it_sale_cnt"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent" value="' . $it["it_sale_percent"] . '" data-toggle="' . $it["it_sale_cnt"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_02"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_02"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_02"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent2" value="' . $it["it_sale_percent_02"] . '" data-toggle="' . $it["it_sale_cnt_02"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_03"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_03"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_03"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent3" value="' . $it["it_sale_percent_03"] . '" data-toggle="' . $it["it_sale_cnt_03"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_04"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_04"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_04"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent4" value="' . $it["it_sale_percent_04"] . '" data-toggle="' . $it["it_sale_cnt_04"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_05"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_05"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_05"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent5" value="' . $it["it_sale_percent_05"] . '" data-toggle="' . $it["it_sale_cnt_05"] . '" class="it_sale_percent">';
            }
          } else if($member['mb_level'] == "4" && $it['it_sale_percent_great']) {
            if($it["it_sale_cnt"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent" value="' . $it["it_sale_percent_great"] . '" data-toggle="' . $it["it_sale_cnt"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_02"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_02"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_02"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent2" value="' . $it["it_sale_percent_great_02"] . '" data-toggle="' . $it["it_sale_cnt_02"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_03"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_03"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_03"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent3" value="' . $it["it_sale_percent_great_03"] . '" data-toggle="' . $it["it_sale_cnt_03"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_04"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_04"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_04"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent4" value="' . $it["it_sale_percent_great_04"] . '" data-toggle="' . $it["it_sale_cnt_04"] . '" class="it_sale_percent">';
            }
            if($it["it_sale_cnt_05"]) {
              $sale_cnt_txt[] = $it["it_sale_cnt_05"] . '개 이상 구매 시 ' . number_format($it["it_sale_percent_great_05"]) . '원';
              $sale_percent_input .= '<input type="hidden" id="it_sale_percent5" value="' . $it["it_sale_percent_great_05"] . '" data-toggle="' . $it["it_sale_cnt_05"] . '" class="it_sale_percent">';
            }
          }
        }

        foreach($sale_cnt_txt as $txt_row) {
          echo '<br><span class="bottom-info-line">└</span>';
          echo '<span class="bottom-info">';
          echo $txt_row;
          echo '</span>';
        }
        ?>
        
        
        <p style="font-size: 32px; margin: 5px 0 20px 0; font-weight: bold;">
          
          
          <?php if(substr($it["ca_id"], 0, 2) == "20") { ?>
          <br><span style="font-weight: normal; font-size: 13px; margin-top: 15px; display: inline-block;">* 대여금액(월기준) : <?=number_format($it["it_rental_price"])?>원</span>
          <?php } ?>
        </p>
       <!--  <p class="help-block">* 주문가능 수량 : <?=number_format(get_it_stock_qty($it_id))?>개</p> -->

        <?php if($it['it_basic']) { // 기본설명 ?>
        <p class="help-block"><?php echo $it['it_basic']; ?></p>
        <?php } ?>
        
        
        <div class="it_type_box">
          <?php if($it['it_type1']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type1_color']?>; color:<?=$default['de_it_type1_color']?>;"><?=$default['de_it_type1_name']?></p><?php } ?>
          <?php if($it['it_type2']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type2_color']?>; color:<?=$default['de_it_type2_color']?>;"><?=$default['de_it_type2_name']?></p><?php } ?>
          <?php if($it['it_type3']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type3_color']?>; color:<?=$default['de_it_type3_color']?>;"><?=$default['de_it_type3_name']?></p><?php } ?>
          <?php if($it['it_type4']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type4_color']?>; color:<?=$default['de_it_type4_color']?>;"><?=$default['de_it_type4_name']?></p><?php } ?>
          <?php if($it['it_type5']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type5_color']?>; color:<?=$default['de_it_type5_color']?>;"><?=$default['de_it_type5_name']?></p><?php } ?>
          <?php if($it['it_type6']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type6_color']?>; color:<?=$default['de_it_type6_color']?>;"><?=$default['de_it_type6_name']?></p><?php } ?>
          <?php if($it['it_type7']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type7_color']?>; color:<?=$default['de_it_type7_color']?>;"><?=$default['de_it_type7_name']?></p><?php } ?>
          <?php if($it['it_type8']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type8_color']?>; color:<?=$default['de_it_type8_color']?>;"><?=$default['de_it_type8_name']?></p><?php } ?>
          <?php if($it['it_type9']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type9_color']?>; color:<?=$default['de_it_type9_color']?>;"><?=$default['de_it_type9_name']?></p><?php } ?>
          <?php if($it['it_type10'] || $is_soldout){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type10_color']?>; color:<?=$default['de_it_type10_color']?>;"><?=$default['de_it_type10_name']?></p><?php } ?>
          <?php if($it['it_type11']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type11_color']?>; color:<?=$default['de_it_type11_color']?>;"><?=substr($it['it_deadline'],0,5)." ".$default['de_it_type11_name']?></p><?php } ?>
          <?php if($it['it_10_subj'] == 'rental'){ ?><p class="p_box" style="border:1px solid red; background-color: red; color:white;"><a href="<?=$it['it_10']?>">렌탈</a></p><?php } ?>
        </div>
      </div>

      <!-- 재고수량 -->
      <?php if($_COOKIE["viewType"] != "basic") { ?>
      <div class="pc">
      <ul class="optionStockCntList" style="display: none;">
        <li style="font-weight: bold; color: #F28D0B;"><?=(substr($it["ca_id"], 0, 2) == "20") ? "My 보유 대여 재고" : "My 보유 재고"?></li>
      </ul>
      </div>
      <?php } ?>
      <?php if ( $it['it_model'] ) { ?>
      <p class="item-model">
        <?php echo str_replace(';', '<br/>', $it['it_model']); ?>
      </p>
      <?php } ?>

      <?php if ($is_tag) { // 태그 ?>
      <p class="item-tag"><?php echo $tag_list;?></p>
      <?php } ?>

      <form name="fitem" method="post" action="<?php echo $action_url; ?>" class="form item-form" role="form" onsubmit="return fitem_submit(this);">
        <input type="hidden" name="it_id[]" value="<?php echo $it_id; ?>">
        <input type="hidden" name="it_msg1[]" value="<?php echo $it['pt_msg1']; ?>">
        <input type="hidden" name="it_msg2[]" value="<?php echo $it['pt_msg2']; ?>">
        <input type="hidden" name="it_msg3[]" value="<?php echo $it['pt_msg3']; ?>">
        <input type="hidden" name="sw_direct">
        <input type="hidden" name="url">
        <input type="hidden" id="it_buy_min_qty" value="<?php echo $it['it_buy_min_qty']; ?>">
        <input type="hidden" id="it_buy_max_qty" value="<?php echo $it['it_buy_max_qty']; ?>">
        <input type="hidden" id="it_buy_inc_qty" value="<?php echo $it['it_buy_inc_qty']; ?>">
        <input type="hidden" id="entprice" value="<?php echo $it['entprice']; ?>">


          <table class="table pc">
            <tbody>
              <tr>
                <th scope="row">
                  <?php if(!is_benefit_item($it)) { ?>
                  급여가(정가)
                  <?php } else { ?>
                  정가
                  <?php } ?>
                </th>
                <td>
                  <?php echo display_price($it['it_cust_price']); ?>
                  
                  <?php if(!is_benefit_item($it)) { ?>
                  <p class="personal-price">
                    ※ 본인부담금 15%(<?=number_format($it["it_cust_price"] * 0.15)?>원), 9%(<?=number_format($it["it_cust_price"] * 0.09)?>원), 6%(<?=number_format($it["it_cust_price"] * 0.06)?>원)
                  </p>
                  <?php } ?>
                </td>
              </tr>

              <tr>
                <th scope="row">상품상세</th>
                <td>
                  <?php if(trim($it["prodSym"])) { ?>
                  <label class="quality-type">재질</label><label class="quality-text"><?=$it["prodSym"]?></label>
                  <br>
                  <?php } ?>
                  <?php if(trim($it["prodSizeDetail"])) { ?>
                  <label class="quality-type">사이즈</label><label class="quality-text"><?=$it["prodSizeDetail"]?></label>
                  <br>
                  <?php } ?>
                  <?php if(trim($it["prodWeig"])) { ?>
                  <label class="quality-type">중량</label><label class="quality-text"><?=$it["prodWeig"]?></label>
                  <br>
                  <?php } ?>
                </td>
              </tr>
            <?php if (!$it[$it_use]) { // 판매가능이 아닐 경우 ?>
            <tr><th scope="row">판매</th><td>판매중지</td></tr>
            <?php } else if ($it['it_tel_inq']) { // 전화문의일 경우 ?>
            <tr><th scope="row">판매</th><td>전화문의</td></tr>
            <?php } else { // 전화문의가 아닐 경우?>
                <?=$sale_percent_input?>
                <input type="hidden" id="it_price" value="<?php
                  if($member["mb_id"]) {
                    if($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) {
                        echo $it["it_cust_price"];
                    } else {
                      if($it['entprice']) {
                        // 사업소별 지정 가격
                        echo $it['entprice'];
                      } else if($member["mb_level"] == "3") {
                        //사업소 가격
                        echo $it["it_price"];
                      }else if($member["mb_level"] == "4") {
                        //우수 사업소 가격
                        echo ($it["it_price_dealer2"]) ? $it["it_price_dealer2"] : $it["it_price"];
                      } else {
                        echo $it["it_price"];
                      }
                    }
                  }
                  ?>">
            <?php } ?>
            <?php /* if ($config['cf_use_point']) { // 포인트 사용한다면 ?>
            <tr>
              <th scope="row">포인트</th>
              <td>
              <?php
              if($it['it_point_type'] == 2) {
                echo '구매금액(추가옵션 제외)의 '.$it['it_point'].'%';
              } else {
                $it_point = get_item_point($it);
                echo number_format($it_point).'점';
              }
              ?>
              </td>
            </tr>
            <?php } */ ?>
            <?php if($it['it_buy_min_qty']) { ?>
            <tr><th>최소구매수량</th><td><?php echo number_format($it['it_buy_min_qty']); ?> 개</td></tr>
            <?php } ?>
            <?php if($it['it_buy_max_qty']) { ?>
            <tr><th>최대구매수량</th><td><?php echo number_format($it['it_buy_max_qty']); ?> 개</td></tr>
            <?php } ?>
            <?php
            $ct_send_cost_label = '배송';
            $sc_price_info_spliter = '<span style="margin:0 8px 0 8px; font-size: 12px; font-weight: 100 !important; color: #d9d9d9;">|</span> ';

            if ($is_samhwa_partner) { // 파트너 유저 배송비
                if ($it['it_sc_type_partner'] == 1)
                    $sc_method = '무료배송';
                else {
                    if ($it['it_sc_method_partner'] == 1)
                        $sc_method = '수령후 지불';
                    else if ($it['it_sc_method_partner'] == 2) {
                        $ct_send_cost_label = '<label for="ct_send_cost">배송비결제</label>';
                        $sc_method = '<select name="ct_send_cost" id="ct_send_cost" class="form-control input-sm">
                      <option value="0">주문시 결제</option>
                      <option value="1">수령후 지불</option>
                    </select>';
                    } else
//            $sc_method = '주문시 결제';
                        $sc_method = '<select name="ct_sc_method_sel" id="ct_sc_method_sel" class="form-control input-sm">';
                    foreach ($delivery_types as $type) {
                        // if ( $type['user-order'] != true ) continue;
                        if (!$default['de_delivery_type_' . $type['val']]) continue;
                        $sc_method .= "<option value='{$type['val']}' data-type='{$type['type']}'>{$type['name']}</option>";
                    }
                    $sc_method .= '</select>';
                }

                $sc_price_info = "";
                if ($it['it_sc_type_partner'] != 1) {
                    $sc_price_info = "배송비는 {$it['it_sc_qty_partner']}개당 배송비 부가 ({$it['it_sc_price_partner']}원)<br>* 도서산간지역은 추가배송비가 발생합니다.";
                }

                if ($it['it_sc_type_partner'] == 0) { // 쇼핑몰 디폴트 셋팅 시
                    $item_price = samhwa_price($it, THEMA_KEY);
                    $send_cost = get_item_sendcost_by_default_case($item_price);

                    if ($send_cost > 0) {
                      // $sc_price_info = "배송비 {$send_cost}원{$sc_price_info_spliter}도서산간지역은 ".($send_cost + 2000)."원 추가됩니다.";
                      $sc_price_info = "배송비 {$send_cost}원";
                    } else {
                      // $sc_price_info = "무료배송{$sc_price_info_spliter}도서산간지역은 추가배송비가 발생합니다.";
                      $sc_price_info = "무료배송";
                    }
                }
            } else { // 파트너 유저 아닐 시
                if ($it['it_sc_type'] == 1)
                    $sc_method = '무료배송';
                else {
                    if ($it['it_sc_method'] == 1)
                        $sc_method = '수령후 지불';
                    else if ($it['it_sc_method'] == 2) {
                        $ct_send_cost_label = '<label for="ct_send_cost">배송비결제</label>';
                        $sc_method = '<select name="ct_send_cost" id="ct_send_cost" class="form-control input-sm">
                      <option value="0">주문시 결제</option>
                      <option value="1">수령후 지불</option>
                    </select>';
                    } else
//            $sc_method = '주문시 결제';
                        $sc_method = '<select name="ct_sc_method_sel" id="ct_sc_method_sel" class="form-control input-sm">';
                    foreach ($delivery_types as $type) {
                        // if ( $type['user-order'] != true ) continue;
                        if (!$default['de_delivery_type_' . $type['val']]) continue;
                        $sc_method .= "<option value='{$type['val']}' data-type='{$type['type']}'>{$type['name']}</option>";
                    }
                    $sc_method .= '</select>';
                }

                $sc_price_info = "";
                if ($it['it_sc_type'] != 1) {
                    $number_cost=number_format($it['it_sc_price']);
                    // $sc_price_info = "배송비 {$number_cost}원{$sc_price_info_spliter}도서산간지역은 ".number_format($it['it_sc_price'] + 2000)."원 추가됩니다.";
                    if ($number_cost > 0) {
                      $sc_price_info = "배송비 {$number_cost}원";
                    } else {
                      $sc_price_info = "무료배송";
                    }
                }

                if ($it['it_sc_type'] == 0) { // 쇼핑몰 디폴트 셋팅 시
                    $item_price = samhwa_price($it, THEMA_KEY);
                    $send_cost = get_item_sendcost_by_default_case($item_price);

                    if ($send_cost > 0) {
                      // $sc_price_info = "배송비 {$send_cost}원{$sc_price_info_spliter}도서산간지역은 ".($send_cost + 2000)."원 추가됩니다.";
                      $sc_price_info = "배송비 {$send_cost}원";
                    } else {
                      // $sc_price_info = "무료배송{$sc_price_info_spliter}도서산간지역은 추가배송비가 발생합니다.";
                      $sc_price_info = "무료배송";
                    }
                }
            }
			$sc_price_info = $sc_price_info . "<br /><span style=\"font-size:11px; color:#7F7F7F;\">* 주문완료 후, 2~7일(주말, 공휴일 제외) 이내 배송<br>&nbsp;(제조사의 사정으로 출고가 지연될 경우 별도 안내)</span>";
      ?>
      <tr>
        <th><?php echo $ct_send_cost_label; ?></th>
        <td>
          <?php
          echo $sc_method;
          //if ($it['it_sc_minimum']) {
            //$sc_price = ((int)$it['it_sc_minimum'] / 10000);
            $sc_price = 10;
            if($it['it_sc_type'] < 4 && $it['it_sc_type'] != 1) {
              $sc_price_info = number_format($sc_price).'만원 이상 무료배송<br>'.$sc_price_info;
            }
            if($it['it_sc_type'] == 5) {
              $sc_price_info = '짝수 주문시 무료배송<br>기본배송비 3,300원';
            }
          //}
          if ($it['it_delivery_cnt'] > 0) {
            $sc_price_info = "<span style=\"font-size:13px; color:#ef7c00;\">본 상품은 {$it['it_delivery_cnt']}개 주문 시 한 박스로 포장됩니다.</span><br>".$sc_price_info;
          }
          ?>
          <p class="sc_price_info" style="font-size: 13px">
            <?php echo $sc_price_info ?>
          </p>
          <p style="font-size: 11px">
            <!-- * 네이버페이 주문시 배송비는 택배(선불)로만 주문이 가능합니다.<br>
            * 네이버페이 주문 시 도서산간지역 배송비는 별도로 추가 결제해 주셔야 합니다. -->
          </p>
        </td>
      </tr>
      </tbody>
      </table>

            <script>
                var sc_price_info;
                $(function () {
                    sc_price_info = $('.sc_price_info').html();
                })
                $('#ct_sc_method_sel').change(function () {
                    if ($(this).val().includes('quick')) {
                        $('.sc_price_info').html("* 담당자와 상담 후 선택해 주시기 바랍니다. (고객센터 : 02-2267-8080)");
                    } else {
                        $('.sc_price_info').html(sc_price_info);
                    }
                });
            </script>
      <div id="item_option">
        <?php if($option_item) { ?>
          <p><b> 선택옵션</b></p>
          <table class="table samhwa-item-option-table">
          <col width="120">
          <tbody>
          <?php echo $option_item; // 선택옵션  ?>
          </tbody>
          </table>
        <?php }  ?>

        <?php if($supply_item) { ?>
          <p><b>추가옵션</b></p>
          <table class="table samhwa-item-option-table">
          <col width="120">
          <tbody>
          <?php echo $supply_item; // 추가옵션 ?>
          </tbody>
          </table>
        <?php }  ?>

        <?php if ($is_orderable) { ?>
          <div id="it_sel_option">
            <?php
            if(!$option_item) {
              if(!$it['it_buy_min_qty'])
                $it['it_buy_min_qty'] = 1;
              if($it['it_buy_inc_qty'] > $it['it_buy_min_qty'])
                $it['it_buy_min_qty'] = $it['it_buy_inc_qty'];
            ?>
              <ul id="it_opt_added" class="list-group">
                <li class="it_opt_list list-group-item <?php echo !$option_item && !$supply_item ? 'alone ' : ''; ?>">
                  <input type="hidden" name="io_type[<?php echo $it_id; ?>][]" value="0">
                  <input type="hidden" name="io_id[<?php echo $it_id; ?>][]" value="">
                  <input type="hidden" name="io_value[<?php echo $it_id; ?>][]" value="<?php echo $it['it_name']; ?>">
                  <input type="hidden" class="io_price" value="0">
                  <input type="hidden" class="io_stock" value="<?php echo $it['it_stock_qty']; ?>">
                  <div class="row">
                    <div class="col-sm-7">
                      <label>
                        <span class="it_opt_subj"><?php echo $it['it_name']; ?></span>
                        <?php
                        if($it['entprice']) {
                          echo '<span class="it_opt_prc_desc">사업소할인적용</span>';
                        }
                        ?>
                        <span class="it_opt_prc"><span class="sound_only">(+0원)</span></span>
                      </label>
                    </div>
                    <div class="col-sm-5">
                      <div class="input-group">
                        <label for="ct_qty_<?php echo $i; ?>" class="sound_only">수량</label>
                        <div class="input-group-btn">
                          <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus-circle fa-lg"></i><span class="sound_only">감소</span></button>
                        </div>
                        <input type="text" name="ct_qty[<?php echo $it_id; ?>][]" value="<?php echo $it['it_buy_min_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="form-control input-sm" size="5">
                        <div class="input-group-btn">
                          <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus-circle fa-lg"></i><span class="sound_only">증가</span></button>
                        </div>
                      </div>
                      <?php
                      if($it['it_buy_inc_qty'] > 1) {
                        echo '<span class="inc_desc">'.$it['it_buy_inc_qty'].'개씩 증가</span>';
                      }
                      ?>
                    </div>
                  </div>
                  <?php if($it['pt_msg1']) { ?>
                    <div style="margin-top:10px;">
                      <input type="text" name="pt_msg1[<?php echo $it_id; ?>][]" class="form-control input-sm" placeholder="<?php echo $it['pt_msg1'];?>">
                    </div>
                  <?php } ?>
                  <?php if($it['pt_msg2']) { ?>
                    <div style="margin-top:10px;">
                      <input type="text" name="pt_msg2[<?php echo $it_id; ?>][]" class="form-control input-sm" placeholder="<?php echo $it['pt_msg2'];?>">
                    </div>
                  <?php } ?>
                  <?php if($it['pt_msg3']) { ?>
                    <div style="margin-top:10px;">
                      <input type="text" name="pt_msg3[<?php echo $it_id; ?>][]" class="form-control input-sm" placeholder="<?php echo $it['pt_msg3'];?>">
                    </div>
                  <?php } ?>
                </li>
              </ul>
              <script>
              $(function() {
                price_calculate();
              });
              </script>
            <?php } ?>
          </div>
          <!-- 총 구매액 -->
          <div class="it_tot_price">
            <h4>
              총 상품금액(합계)
            </h4>
            <span id="it_tot_price">0원</span>
          </div>
        <?php } ?>
      </div>

      <?php if($is_soldout) { ?>
        <p id="sit_ov_soldout">재고가 부족하여 구매할 수 없습니다.</p>
      <?php } ?>

      <?php if ($is_orderable && $member['mb_type'] !== 'normal') { ?>
        <div style="text-align:center;" class="item-btns">
          <ul class="item-buy-btn">
            <li class="buy"><input type="submit" onclick="document.pressed=this.value;" value="상품주문" class="btn btn-<?php echo $btn2;?> btn-block <?php echo $it['prodSupYn'] === 'N' ? 'disabled' : ''; ?>"></li>
            <li class="cart">
              <div class="cart-ok">
                <p class="pc">선택하신 상품을 장바구니에 담았습니다.</p>
                <p class="mobile">장바구니에 담았습니다.</p>
                <ul>
                  <li><a href='#' class="cart-ok-close">쇼핑 계속하기</a></li>
                  <li><a class="bg" href='<?php echo G5_SHOP_URL; ?>/cart.php'>장바구니 보기</a></li>
                </ul>
              </div>
              <input type="submit" onclick="document.pressed=this.value;" value="장바구니" class="btn btn-<?php echo $btn1;?> btn-block">
            </li>
          </ul>
        </div>
        <?php if ( $it['it_10'] != "1") { ?> <!-- 여분필드 10에 네이버페이 노출 1로 할 경우 노출안됨 -->
          <?php if ($naverpay_button_js) { ?>
            <div style="margin-bottom:15px;"><?php echo $naverpay_request_js.$naverpay_button_js; ?></div>
          <?php } ?>
        <?php } ?>
      <?php } ?>
      <?php if(!$is_orderable && $it['it_soldout'] && $it['it_stock_sms']) { ?>
        <div style="text-align:center; padding:12px 0;">
          <button type="button" onclick="popup_stocksms('<?php echo $it['it_id']; ?>','<?php echo $ca_id; ?>');" class="btn btn-primary">재입고알림(SMS)</button>
        </div>
      <?php } ?>
      </form>
      <!-- 본인부담금 
      <div class="selfPriceInfo">
        <div class="title">본인부담금</div>
        <p>15%(<?=number_format($it["it_cust_price"] * 0.15)?>원), 9%(<?=number_format($it["it_cust_price"] * 0.09)?>원), 6%(<?=number_format($it["it_cust_price"] * 0.06)?>원)</p>
      </div>-->

      <script>
        //엔터키 막기
        // $(document).keypress(function(e) { if (e.keyCode == 13) e.preventDefault(); });
        
                // 취급상품 등록 20210324 쇼핑몰기본 기능작업
                function item_wish(f, it_id)
                {
                    f.url.value = "<?php echo G5_SHOP_URL; ?>/wishupdate.php?it_id="+it_id;
                    f.action = "<?php echo G5_SHOP_URL; ?>/wishupdate.php";
                    f.submit();
                }
        // BS3
        $(function() {
          $("select.it_option").addClass("form-control input-sm");
          $("select.it_supply").addClass("form-control input-sm");
        });

        // 재입고SMS 알림
        function popup_stocksms(it_id, ca_id) {
          url = "./itemstocksms.php?it_id=" + it_id + "&ca_id=" + ca_id;
          opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
          popup_window(url, "itemstocksms", opt);
        }

        // 상품주문, 장바구니 폼 전송
        function fitem_submit(f) {
		<?php if($it["pt_end"] != "" && $it["it_price"] =="100"){?>
			if (document.pressed == "장바구니") {
				alert("이벤트 진행 상품으로 상품구매만 가능합니다.");
				return false;
			}
		<?php }?>
		<?php if($is_buy){?>
			alert("이미 구매한 이벤트 상품으로 주문이 제한되었습니다.");
			return false;
		<?php }?>
          f.action = "<?php echo $action_url; ?>";
          f.target = "";

          if (document.pressed == "장바구니") {
            f.sw_direct.value = 0;
          } else { // 상품주문
            f.sw_direct.value = 1;  

            var prod_sup_yn = <?php echo $it['prodSupYn'] === 'Y' ? 'true' : 'false'; ?>;
            if (!prod_sup_yn) {
              alert('비유통상품은 수급자 계약 시 정보활용에만 사용되므로 상품주문이 불가능합니다.');
              return false;
            }
          }

          // 판매가격이 0 보다 작다면
          if (document.getElementById("it_price").value < 0) {
            alert("전화로 문의해 주시면 감사하겠습니다.");
            return false;
          }

          if($(".it_opt_list").size() < 1) {
            alert("선택옵션을 선택해 주십시오.");
            return false;
          }

          var val, io_type, result = true;
          var sum_qty = 0;
          var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
          var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
          var $el_type = $("input[name^=io_type]");

          $("input[name^=ct_qty]").each(function(index) {
            val = $(this).val();

            if(val.length < 1) {
              alert("수량을 입력해 주십시오.");
              result = false;
              return false;
            }

            if(val.replace(/[0-9]/g, "").length > 0) {
              alert("수량은 숫자로 입력해 주십시오.");
              result = false;
              return false;
            }

            if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
              alert("수량은 1이상 입력해 주십시오.");
              result = false;
              return false;
            }

            io_type = $el_type.eq(index).val();
            if(io_type == "0")
              sum_qty += parseInt(val);
          });

          if(!result) {
            return false;
          }

          if(min_qty > 0 && sum_qty < min_qty) {
            alert("선택옵션 개수 총합 "+number_format(String(min_qty))+"개 이상 주문해 주십시오.");
            return false;
          }

          if(max_qty > 0 && sum_qty > max_qty) {
            alert("선택옵션 개수 총합 "+number_format(String(max_qty))+"개 이하로 주문해 주십시오.");
            return false;
          }

          if (document.pressed == "장바구니") {
            $.post("./itemcart.php", $(f).serialize(), function(error) {
              if(error != "OK") {
                alert(error.replace(/\\n/g, "\n"));
                return false;
              } else {
                if ($(f).find('.cart-ok') && $(f).find('.cart-ok').length) {
                  $(f).find('.cart-ok').css('opacity', 0)
                    .show("slide", { direction: "down" }, 500)
                    .animate(
                      { opacity: 1 },
                      { queue: false, duration: 'slow' }
                    );
                  setTimeout(function() {
                    hide_cart_ok($(f).find('.cart-ok'));
                  }, 3000)
                  return false;
                }
                if(!confirm("장바구니에 담겼습니다.\n\n확인을 원하시면 '아니오'를 선택하세요")) {
                  document.location.href = "./cart.php";
                }
              }
            });
            return false;
          } else {
            return true;
          }
        }

        function hide_cart_ok(obj) {
          $(obj)
            .hide("slide", { direction: "down" }, 500)
            .animate(
              { opacity: 0 },
              { queue: false, duration: 'fast' }
            );
        }

        $(function() {
          $(".cart-ok-close").click(function(e) {
            // $(this).closest('.cart-ok').hide("slide", { direction: "down" }, 500);
            hide_cart_ok($(this).closest('.cart-ok'));
            // window.location.reload();

            <?php
            if (strlen($main_ca) > 2 ){
                $sub_ca = substr($main_ca,2,2);
                $main_ca = substr($main_ca,0,2);
                $it_list_url = "/shop/list.php?ca_id={$main_ca}&ca_sub%5B%5D={$sub_ca}&sort=custom&prodSubYn=Y";
              }else {
                $it_list_url = "/shop/list.php?ca_id={$main_ca}";
            };
            ?>

            window.location = '<?= $it_list_url ?>';
            e.preventDefault();
          })
        });

        // Wishlist
        function apms_wishlist(it_id) {
          if(!it_id) {
            alert("코드가 올바르지 않습니다.");
            return false;
          }

          $.ajax({
            url : "./itemwishlist.php",
            type : "POST",
            data : {
              it_id : it_id
            },
            success : function(result){
              result = JSON.parse(result);

              if(result.errorYN == "Y"){
                alert(result.message);
              } else {
                if(confirm("취급상품에 등록되었습니다.\n\n바로 확인하시겠습니까?")){
                  window.location.href = "./wishlist.php";
                }
              }
            }
          });

//          $.post("./itemwishlist.php", { it_id: it_id },  function(error) {
//            if(error != "OK") {
//              alert(error.replace(/\\n/g, "\n"));
//              return false;
//            } else {
//              if(confirm("취급상품에 등록되었습니다.\n\n바로 확인하시겠습니까?")) {
//                document.location.href = "./wishlist.php";
//              }
//            }
//          });

          return false;
        }

        // Recommend
        function apms_recommend(it_id, ca_id) {
          if (!g5_is_member) {
            alert("회원만 추천하실 수 있습니다.");
          } else {
            url = "./itemrecommend.php?it_id=" + it_id + "&ca_id=" + ca_id;
            opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
            popup_window(url, "itemrecommend", opt);
          }
        }
      </script>

      <div class="pull-right">
        <?php
        //include_once(G5_SNS_PATH."/item.sns.skin.php");
        ?>
      </div>
      <div class="clearfix"></div>

    </div>
  </div>
</div>

<?php if($is_viewer || $is_link) {
  // 보기용 첨부파일 확장자에 따른 FA 아이콘 - array(이미지, 비디오, 오디오, PDF)
  $viewer_fa = array("picture-o", "video-camera", "music", "file-powerpoint-o");
?>
  <?php echo apms_line('fa', 'fa-gift'); //라인 ?>

  <div class="item-view-box">
    <?php if($is_link) { ?>
      <?php for($i=0; $i < count($link); $i++) { ?>
        <a href="<?php echo $link[$i]['url']; ?>" target="_blank" class="at-tip" title="<?php echo ($link[$i]['name']) ? $link[$i]['name'] : '관련링크'; ?>"><i class="fa fa-<?php echo ($link[$i]['fa']) ? $link[$i]['fa'] : 'link';?>"></i></a>
      <?php } ?>
    <?php } ?>

    <?php if($is_viewer) { ?>
      <?php for($i=0; $i < count($viewer); $i++) { $v = ($viewer[$i]['ext'] - 1); ?>
        <?php if($viewer[$i]['href_view']) { ?>
          <a href="<?php echo $viewer[$i]['href_view'];?>" class="view_win at-tip" title="<?php echo ($viewer[$i]['free']) ? '무료보기' : '바로보기';?>">
        <?php } else { ?>
          <a onclick="alert('구매한 회원만 볼 수 있습니다.');" class="at-tip" title="유료보기">
        <?php } ?>
          <i class="fa fa-<?php echo $viewer_fa[$v];?>"></i>
        </a>
      <?php } ?>
    <?php } ?>
    <script>
      var view_win = function(href) {
        var new_win = window.open(href, 'view_win', 'left=0,top=0,width=640,height=480,toolbar=0,location=0,scrollbars=0,resizable=1,status=0,menubar=0');
        new_win.focus();
      }
      $(function() {
        $(".view_win").click(function() {
          view_win(this.href);
          return false;
        });
      });
    </script>
  </div>
<?php } ?>

<?php // 비디오
  $item_video = apms_link_video($link_video);
  if($item_video) {
    echo apms_line('fa', 'fa-video-camera');
    echo $item_video;
  }
?>

<?php if($is_download) { // 다운로드 ?>
  <?php echo apms_line('fa', 'fa-download'); // 라인 ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><i class="fa fa-download"></i> Download</h3>
    </div>
    <div class="list-group">
      <?php for($i=0; $i < count($download); $i++) { ?>
        <a class="list-group-item break-word" href="<?php echo ($download[$i]['href']) ? $download[$i]['href'] : 'javascript:alert(\'구매한 회원만 다운로드할 수 있습니다.\');';?>">
          <?php if($download[$i]['free']) { ?>
            <?php if($download[$i]['guest_use']) { ?>
              <span class="label label-default label-item pull-right"><span class="font-11 en">Free</span></span>
            <?php } else { ?>
              <span class="label label-primary label-item pull-right"><span class="font-11 en">Join</span></span>
            <?php } ?>
          <?php } else { ?>
            <span class="label label-danger label-item pull-right"><span class="font-11 en">Paid</span></span>
          <?php } ?>
          <i class="fa fa-download"></i> <?php echo $download[$i]['source'];?> (<?php echo $download[$i]['size'];?>)
        </a>
      <?php } ?>
      <?php if($i && $is_remaintime) { //이용기간 안내
        $remain_day = (int)(($is_remaintime - G5_SERVER_TIME) / 86400); //남은일수
      ?>
        <a class="list-group-item" href="#">
          <i class="fa fa-bell"></i> <?php echo date("Y.m.d H:i", $is_remaintime);?>(<?php echo number_format($remain_day);?>일 남음)까지 이용가능합니다.
        </a>
      <?php } ?>
    </div>
  </div>
<?php } ?>

<?php if ($is_torrent) { // 토렌트 파일정보 ?>
  <?php echo apms_line('fa', 'fa-cube'); // 라인 ?>
  <?php for($i=0; $i < count($torrent); $i++) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-cube"></i> <?php echo $torrent[$i]['name'];?></h3>
      </div>
      <div class="panel-body">
        <span class="pull-right hidden-xs text-muted en font-11"><i class="fa fa-clock-o"></i> <?php echo date("Y-m-d H:i", $torrent[$i]['date']);?></span>
        <?php if ($torrent[$i]['is_size']) { ?>
            <b class="en font-16"><i class="fa fa-cube"></i> <?php echo $torrent[$i]['info']['name'];?> (<?php echo $torrent[$i]['info']['size'];?>)</b>
        <?php } else { ?>
          <p><b class="en font-16"><i class="fa fa-cubes"></i> Total <?php echo $torrent[$i]['info']['total_size'];?></b></p>
          <div class="text-muted font-12">
            <?php for ($j=0;$j < count($torrent[$i]['info']['file']);$j++) {
              echo ($j + 1).'. '.implode(', ', $torrent[$i]['info']['file'][$j]['name']).' ('.$torrent[$i]['info']['file'][$j]['size'].')<br>'."\n";
            } ?>
          </div>
        <?php } ?>
      </div>
      <ul class="list-group">
        <li class="list-group-item en font-14 break-word"><i class="fa fa-magnet"></i> <?php echo $torrent[$i]['magnet'];?></li>
        <li class="list-group-item break-word">
          <div class="text-muted" style="font-size:12px;">
            <?php for ($j=0;$j < count($torrent[$i]['tracker']);$j++) { ?>
              <i class="fa fa-tags"></i> <?php echo $torrent[$i]['tracker'][$j];?><br>
            <?php } ?>
          </div>
        </li>
        <?php if($torrent[$i]['comment']) { ?>
          <li class="list-group-item en font-14 break-word"><i class="fa fa-bell"></i> <?php echo $torrent[$i]['comment'];?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>
<?php } ?>

<?php echo apms_line('fa'); // 라인 ?>

<?php if ($is_good) { // 추천 ?>
  <div class="item-good-box">
    <span class="item-good">
      <a href="#" onclick="apms_good('<?php echo $it_id;?>', '', 'good', 'it_good'); return false;">
        <b id="it_good"><?php echo number_format($it['pt_good']) ?></b>
        <br>
        <i class="fa fa-thumbs-up"></i>
      </a>
    </span>
    <span class="item-nogood">
      <a href="#" onclick="apms_good('<?php echo $it_id;?>', '', 'nogood', 'it_nogood'); return false;">
        <b id="it_nogood"><?php echo number_format($it['pt_nogood']) ?></b>
        <br>
        <i class="fa fa-thumbs-down"></i>
      </a>
    </span>
  </div>
<?php } ?>

<?php if ($is_ccl) { // CCL ?>
  <div class="h20"></div>
  <div class="well">
    <img src="<?php echo $ccl_img;?>" alt="CCL" />  &nbsp; 본 자료는 <u><?php echo $ccl_license;?></u>에 따라 이용할 수 있습니다.
  </div>
<?php } ?>

<?php if($is_seller && $wset['seller']) { // 판매자 ?>
  <div class="panel panel-default item-seller">
    <div class="panel-heading">
      <h3 class="panel-title">
        <?php if($author['partner']) { ?>
          <a href="<?php echo $at_href['myshop'];?>?id=<?php echo $author['mb_id'];?>" class="pull-right">
            <span class="label label-primary"><span class="font-11 en">My Shop</span></span>
          </a>
        <?php } ?>
        Seller
      </h3>
    </div>
    <div class="panel-body">
      <div class="pull-left text-center auth-photo">
        <div class="img-photo">
          <?php echo ($author['photo']) ? '<img src="'.$author['photo'].'" alt="">' : '<i class="fa fa-user"></i>'; ?>
        </div>
        <div class="btn-group" style="margin-top:-30px;white-space:nowrap;">
          <button type="button" class="btn btn-color btn-sm" onclick="apms_like('<?php echo $author['mb_id'];?>', 'like', 'it_like'); return false;" title="Like">
            <i class="fa fa-thumbs-up"></i> <span id="it_like"><?php echo number_format($author['liked']) ?></span>
          </button>
          <button type="button" class="btn btn-color btn-sm" onclick="apms_like('<?php echo $author['mb_id'];?>', 'follow', 'it_follow'); return false;" title="Follow">
            <i class="fa fa-users"></i> <span id="it_follow"><?php echo $author['followed']; ?></span>
          </button>
        </div>
      </div>
      <div class="auth-info">
        <div style="margin-bottom:4px;">
          <span class="pull-right">Lv.<?php echo $author['level'];?></span>
          <b><?php echo $author['name']; ?></b> &nbsp;<span class="text-muted font-11"><?php echo $author['grade'];?></span>
        </div>
        <div class="div-progress progress progress-striped no-margin">
          <div class="progress-bar progress-bar-exp" role="progressbar" aria-valuenow="<?php echo round($author['exp_per']);?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($author['exp_per']);?>%;">
            <span class="sr-only"><?php echo number_format($author['exp']);?> (<?php echo $author['exp_per'];?>%)</span>
          </div>
        </div>
        <p style="margin-top:10px;">
          <?php echo ($author['signature']) ? $author['signature'] : '등록된 서명이 없습니다.'; ?>
        </p>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>
<?php } ?>

<?php if ($is_relation) { ?>
  <div class="div-title-wrap">
    <div class="div-title" style="line-height:30px;">
      <i class="fa fa-cubes fa-lg lightgray"></i> <b>관련아이템</b>
    </div>
    <div class="div-sep-wrap">
      <div class="div-sep sep-bold"></div>
    </div>
  </div>
  <?php include_once('./itemrelation.php'); ?>
<?php } ?>

<script type="text/javascript" charset="utf-8">
  //상세설명 텝 이동
  function fnMove(seq){
      var offset = $("#div" + seq).offset();
      $('html, body').animate({scrollTop : offset.top - 150}, 400);
  }
</script>


<?php // 위젯에서 해당글 클릭시 이동위치 : icv - 댓글, iuv - 후기, iqv - 문의 ?>
<div id="item-tab" class="div-tab tabs<?php echo ($wset['tabline']) ? '' : ' trans-top';?>">
  <!-- <ul class="nav nav-tabs nav-justified">
    <li class="active"><a href="#item-explan" data-toggle="tab"><b>상세정보</b></a></li>
    <li class=""><a href="#item-review" data-toggle="tab"><b>상품후기<?php echo $it_use_cnt;?></b></a></li>
    <li><a href="#item-qa" data-toggle="tab"><b>상품문의<?php echo $it_qa_cnt;?></b></a></li>
    <?php if($is_comment) { // 댓글 ?>
      <li><a href="#item-cmt" data-toggle="tab"><b>댓글<?php echo $it_comment_cnt;?></b></a></li>
    <?php } ?>
    <?php if($is_ii) { // 상품정보고시 ?>
      <li><a href="#item-info" data-toggle="tab"><b>필수표기정보</b></a></li>
    <?php } ?>
    <li class="pc"><a href="#item-delivery" data-toggle="tab"><b>교환/반품/배송정보</b></a></li>
  </ul> -->
  <div class="tab-content" style="border:0px; padding:20px 0px;">
    <ul class="nav nav-tabs nav-justified">
      <li class="active"><a href="#"  onclick="fnMove('1')" ><b>상세정보</b></a></li>
      <li><a href="#"  onclick="fnMove('2')"  ><b>상품후기<?php echo $it_use_cnt;?></b></a></li>
      <li><a href="#"  onclick="fnMove('3')" ><b>상품문의<?php echo $it_qa_cnt;?></b></a></li>
      <li class="pc"><a href="#"  onclick="fnMove('4')" ><b>교환/반품/배송정보</b></a></li>
    </ul>
    <div class="tab-pane active" id="div1"> <!-- id="item-explan" -->
      <div class="item-explan">
        <?php if ($it['pt_explan']) { // 구매회원에게만 추가로 보이는 상세설명 ?>
          <div class="well"><?php echo apms_explan($it['pt_explan']); ?></div>
        <?php } ?>
        <?php echo apms_explan($it['it_explan']); ?>
      </div>
    </div>
    <ul class="nav nav-tabs nav-justified">
      <li><a href="#"  onclick="fnMove('1')"><b>상세정보</b></a></li>
      <li class="active"><a href="#"  onclick="fnMove('2')" ><b>상품후기<?php echo $it_use_cnt;?></b></a></li>
      <li><a href="#"  onclick="fnMove('3')"  ><b>상품문의<?php echo $it_qa_cnt;?></b></a></li>
      <li class="pc"><a href="#"  onclick="fnMove('4')" ><b>교환/반품/배송정보</b></a></li>
    </ul>
    <div class="tab-pane active" id="div2"><!-- id="item-review" -->
      <div id="iuv"></div>
      <div id="itemuse">
        <?php include_once('./itemuse.php'); ?>
      </div>
    </div>
    <ul class="nav nav-tabs nav-justified">
      <li><a href="#"  onclick="fnMove('1')" ><b>상세정보</b></a></li>
      <li><a href="#"  onclick="fnMove('2')"  ><b>상품후기<?php echo $it_use_cnt;?></b></a></li>
      <li class="active"><a href="#"  onclick="fnMove('3')" ><b>상품문의<?php echo $it_qa_cnt;?></b></a></li>
      <li class="pc"><a href="#"  onclick="fnMove('4')"  ><b>교환/반품/배송정보</b></a></li>
    </ul>
    <div class="tab-pane active" id="div3"><!-- id="item-qa" -->
      <div id="iqv"></div>
      <div id="itemqa">
        <?php include_once('./itemqa.php'); ?>
      </div>
    </div>
    <?php if($is_comment) { // 댓글 ?>
      <div class="tab-pane" id="item-cmt">
        <div id="icv"></div>
        <?php include_once('./itemcomment.php'); ?>
      </div>
    <?php } ?>
    <?php if($is_ii) { // 상품정보고시 ?>
      <div class="tab-pane" id="item-info">

        <div class="tbox-head no-line">
          <i class="fa fa-check-square fa-lg lightgray"></i> 상품요약정보 : <?php echo $item_info[$gubun]['title']; ?>
        </div>
        <div class="tbox-body">
          <div class="table-responsive">
            <table class="div-table table top-border">
            <caption>상품정보고시</caption>
            <tbody>
              <?php for($i=0; $i < count($ii); $i++) { ?>
                <tr>
                  <th><?php echo $ii[$i]['title']; ?></th>
                  <td><?php echo $ii[$i]['value']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
            </table>
          </div>
        </div>

        <div class="tbox-head no-line">
          <i class="fa fa-check-square fa-lg lightgray"></i> 거래조건에 관한 정보
        </div>
        <div class="tbox-body">
          <div class="table-responsive">
            <table class="div-table table top-border">
            <caption>거래조건</caption>
            <tbody>
              <tr>
                <th>재화 등의 배송방법에 관한 정보</th>
                <td>상품 상세설명페이지 참고</td>
              </tr>
              <tr>
                <th>주문 이후 예상되는 배송기간</th>
                <td>상품 상세설명페이지 참고</td>
              </tr>
              <tr>
                <th>제품하자가 아닌 소비자의 단순변심, 착오구매에 따른 청약철회 시 소비자가 부담하는 반품비용 등에 관한 정보</th>
                <td>배송ㆍ교환ㆍ반품 상세설명페이지 참고</td>
              </tr>
              <tr>
                <th>제품하자가 아닌 소비자의 단순변심, 착오구매에 따른 청약철회가 불가능한 경우 그 구체적 사유와 근거</th>
                <td>배송ㆍ교환ㆍ반품 상세설명페이지 참고</td>
              </tr>
              <tr>
                <th>재화등의 교환ㆍ반품ㆍ보증 조건 및 품질보증 기준</th>
                <td>소비자분쟁해결기준(공정거래위원회 고시) 및 관계법령에 따릅니다.</td>
              </tr>
              <tr>
                <th>재화등의 A/S 관련 전화번호</th>
                <td>상품 상세설명페이지 참고</td>
              </tr>
              <tr>
                <th>대금을 환불받기 위한 방법과 환불이 지연될 경우 지연에 따른 배상금을 지급받을 수 있다는 사실 및 배상금 지급의 구체적 조건 및 절차</th>
                <td>배송ㆍ교환ㆍ반품 상세설명페이지 참고</td>
              </tr>
              <tr>
                <th>소비자피해보상의 처리, 재화등에 대한 불만처리 및 소비자와 사업자 사이의 분쟁처리에 관한 사항</th>
                <td>소비자분쟁해결기준(공정거래위원회 고시) 및 관계법령에 따릅니다.</td>
              </tr>
              <tr>
                <th>거래에 관한 약관의 내용 또는 확인할 수 있는 방법</th>
                <td>상품 상세설명페이지 및 페이지 하단의 이용약관 링크를 통해 확인할 수 있습니다.</td>
              </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php } ?>
    <ul class="nav nav-tabs nav-justified pc">
      <li><a href="#"  onclick="fnMove('1')"><b>상세정보</b></a></li>
      <li><a href="#"  onclick="fnMove('2')" ><b>상품후기<?php echo $it_use_cnt;?></b></a></li>
      <li><a href="#"  onclick="fnMove('3')"  ><b>상품문의<?php echo $it_qa_cnt;?></b></a></li>
      <li class="pc active"><a href="#"  onclick="fnMove('4')" ><b>교환/반품/배송정보</b></a></li>
    </ul>
    <div class="tab-pane active" id="div4"><!-- id="item-delivery" -->
      <?php include_once($item_skin_path.'/item.delivery.php'); ?>
    </div>
  </div>
</div>

<?php echo $it_tail_html; // 하단 HTML ?>
<!--
<div class="btn-group btn-group-justified">
  <?php if($prev_href) { ?>
    <a class="btn btn-<?php echo $btn1;?>" href="<?php echo $prev_href;?>" title="<?php echo $prev_item;?>"><i class="fa fa-chevron-circle-left"></i> 이전</a>
  <?php } ?>
  <?php if($next_href) { ?>
    <a class="btn btn-<?php echo $btn1;?>" href="<?php echo $next_href;?>" title="<?php echo $next_item;?>"><i class="fa fa-chevron-circle-right"></i> 다음</a>
  <?php } ?>
  <?php if($edit_href) { ?>
    <a class="btn btn-<?php echo $btn1;?>" href="<?php echo $edit_href;?>"><i class="fa fa-plus"></i><span class="hidden-xs"> 수정</span></a>
  <?php } ?>
  <?php if ($write_href) { ?>
    <a class="btn btn-<?php echo $btn1;?>" href="<?php echo $write_href;?>"><i class="fa fa-upload"></i><span class="hidden-xs"> 등록</span></a>
  <?php } ?>
  <?php if($item_href) { ?>
    <a class="btn btn-<?php echo $btn1;?>" href="<?php echo $item_href;?>"><i class="fa fa-th-large"></i><span class="hidden-xs"> 관리</span></a>
  <?php } ?>
  <?php if($setup_href) { ?>
    <a class="btn btn-<?php echo $btn1;?> win_memo" href="<?php echo $setup_href;?>"><i class="fa fa-cogs"></i><span class="hidden-xs"> 스킨설정</span></a>
  <?php } ?>
  <a class="btn btn-<?php echo $btn2;?>" href="<?php echo $list_href;?>"><i class="fa fa-bars"></i> 목록</a>
</div>
-->

<div class="h30"></div>

<script>
$(function() {

  <?php if($member["mb_id"] && $_COOKIE['SHOW_MY_STOCK'] !== 'OFF'){ ?>
    var sendData = <?=json_encode($sendData, JSON_UNESCAPED_UNICODE)?>;

    $.ajax({
      url : "/apiEroum/stock/selectListMore.php",
      type : "POST",
      async : false,
      data : sendData,
      success : function(result){
        $.each(result.data, function(key, value){
                    console.log(result);
          // if(result["data2"]){
          //   var totalQty = (result["data2"][key]["qty"]) ? result["data2"][key]["qty"] : 0;
          // } else {
            var totalQty = 0;
          // }
          $(".optionStockCntList").show();

          var html = '<li><span class="name">' + value.name + '</span><span class="cnt">' + value.qty + '개<?=(substr($it["ca_id"], 0, 2) == "20") ? " 대여 가능" : ""?>';
          if(totalQty){
            html += " (총 " + totalQty + "개)";
          }
          html += '</span></li>';
          $(".optionStockCntList").append(html);
        });
      }
    });
  <?php } ?>

  $("a.view_image").click(function() {
    window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
    return false;
  });
});
</script>

<?php
// include_once('./itemlist.php'); // 분류목록
?>
