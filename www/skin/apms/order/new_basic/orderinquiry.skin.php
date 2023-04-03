<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['ihead']) && $wset['ihead']) {
  add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ihead'].'.css" media="screen">', 0);
  $head_class = 'list-head';
} else {
  $head_class = (isset($wset['icolor']) && $wset['icolor']) ? 'tr-head border-'.$wset['icolor'] : 'tr-head border-black';
}

$delivery_company = array('chunilps' => 'kr.chunilps', 'cjlogistics' => 'kr.cjlogistics', 'ds3211' => 'kr.daesin', 'hanjin' => 'kr.hanjin', 'hdexp' => 'kr.hdexp',
'ilogen' => 'kr.logen', 'ilyanglogis' => 'kr.ilyanglogis', 'kdexp' => 'kr.kdexp', 'kunyoung' => 'kr.kunyoung', 'lotteglogis' => 'kr.lotte');

// 헤더 출력
if($header_skin)
  include_once('./header.php');

  # 스킨경로  
  $SKIN_URL = G5_SKIN_URL.'/apms/order/'.$skin_name;

?>

<link rel="stylesheet" href="<?=$SKIN_URL?>/css/jquery-ui.min.css">
<link rel="stylesheet" href="<?=$SKIN_URL?>/css/product_order.css?v=20210923">
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
  $( function() {
    //캘린더
    $("#date1").datepicker({
      dateFormat : 'yy-mm-dd',
      prevText: '이전달',
      nextText: '다음달',
      monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
      monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
      dayNames: ['일', '월', '화', '수', '목', '금', '토'],
      dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
      dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
      showMonthAfterYear: true,
      changeMonth: true,
      changeYear: true,
      showOn: "both",
      buttonImage: "<?=$SKIN_URL?>/image/icon_17.png",
      buttonImageOnly: true,
      buttonText: "Select date"
    });
    $("#date2").datepicker({
      dateFormat : 'yy-mm-dd',
      prevText: '이전달',
      nextText: '다음달',
      monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
      monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
      dayNames: ['일', '월', '화', '수', '목', '금', '토'],
      dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
      dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
      showMonthAfterYear: true,
      changeMonth: true,
      changeYear: true,
      showOn: "both",
      buttonImage: "<?=$SKIN_URL?>/image/icon_17.png",
      buttonImageOnly: true,
      buttonText: "Select date"
    });

    //셀렉트(주문+재고, 전체 상태)
    $('.order-date .list-select .select').find('p').each(function(){
      $(this).on('click',function(){
        $(this).siblings('ul').stop().slideToggle();
        $(this).parent('.select').siblings('.select').find('ul').stop().slideUp();
        $(this).siblings('ul').find('li a ').on('click',function(){
          let textVal = $(this).text();
          $(this).parents('ul').siblings('p').text(textVal);
          $(this).parents('ul').stop().slideUp();

        });
      });
    });
  } );
</script>

<style>
.listPopupBoxWrap { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
.listPopupBoxWrap > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
.listPopupBoxWrap iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

@media (max-width : 750px){
  .listPopupBoxWrap iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}

#popupDeliveryTracking iframe { height: 500px; }
#popupDeliveryTracking #popupDeliveryTrackingClose { position: relative; width: 500px; border: 0; background-color: #000; color:#fff; left: 50%; text-align:center; margin-left: -250px; display: block;  padding: 15px 0; top: -5px; }

.list-checkbox { height: 40px; }
.list-checkbox input[type=checkbox] { display:none; }
.list-checkbox input[type=checkbox] + label { font-size: 20px; display: inline-block; cursor: pointer; line-height: 21px; padding-left: 27px; background: url('/adm/shop_admin/img/checkbox.png') left/21px no-repeat; margin-right:10px; height:21px; }
.list-checkbox input[type=checkbox]:checked + label { background-image: url('/adm/shop_admin/img/checkbox_checked.png'); }
</style>

<!-- 210326 재고조회팝업 -->
<div id="popupProdBarNumInfoBox" class="listPopupBoxWrap">
  <div>
  </div>
</div>
<!-- 210326 재고조회팝업 -->

<!-- 230104 배송조회팝업 -->
<div id="popupDeliveryTracking" class="listPopupBoxWrap">
  <div></div>  
</div>
<!-- 210326 재고조회팝업 -->

<!-- 210326 배송정보팝업 -->
<div id="popupProdDeliveryInfoBox" class="listPopupBoxWrap">
  <div>
  </div>
</div>

<div id="popup_box" class="listPopupBoxWrap">
  <div></div>
</div>

<script type="text/javascript">
$(function(){

  $(".listPopupBoxWrap").hide();
  $(".listPopupBoxWrap").css("opacity", 1);

  $(".popupDeliveryInfoBtn").click(function(e){
    if( (screen.width < 500) || (screen.height < 400) ){ alert("배송정보는 PC에서 확인 가능 합니다."); return; }

    e.preventDefault();

    var od = $(this).attr("data-od");
    $("#popupProdDeliveryInfoBox > div").append("<iframe src='/shop/popup.prodDeliveryInfo.php?od_id=" + od + "'>");
    $("#popupProdDeliveryInfoBox iframe").load(function(){
      $("#popupProdDeliveryInfoBox").show();
    });
  });

  $(".popupProdBarNumInfoBtn").click(function(e){
    e.preventDefault();
    var od_id = $(this).attr("data-id");
    var ct_id = $(this).attr("data-ct-id");
    $("#popupProdBarNumInfoBox > div").append("<iframe src='<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form_4.php?od_id=" + od_id +  "&ct_id=" + ct_id +"'>");
    $("#popupProdBarNumInfoBox iframe").load(function(){
      $("#popupProdBarNumInfoBox").show();
    });
  });

  $(".btn_install_report").click(function(e){
    e.preventDefault();
    var od_id = $(this).data('id');
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="/shop/popup.install_report.php?od_id=' + od_id + '">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  $(".btn_delivery_tracking_n").click(function(e){
    e.preventDefault();
    alert("택배 회사를 다시 확인해주세요.");
  });

  $(".popupDeliveryTrackingBtn").click(function(e){
    e.preventDefault();
    var url = $(this).attr("data-url");
    $("#popupDeliveryTracking > div").html("<iframe src='" + url +  "'></iframe><p id='popupDeliveryTrackingClose' onclick='$(\"#popupDeliveryTracking\").hide();'>닫기 (<i class='fa fa-times'></i>)</p>");
    $("#popupDeliveryTracking iframe").load(function(){
      $("#popupDeliveryTracking").show();
    });
  });

});
</script>
<!-- 210326 배송정보팝업 -->
<script src="/js/detectmobilebrowser.js">
</script>

<section id="pro-order" class="wrap order-list">
  <div class="sub_section_tit">주문내역</div>
  <div class="r_btn_area">
    <a href="javascript:void(0)" id="btn_hidden_order" class="btn eroumcare_btn2" title="숨김처리한 주문">숨김처리한 주문</a>
  </div>
  <div class="r_btn_area2">
    <a href="./schedule/index.php" class="btn eroumcare_btn2" onclick="return showSchdule(this.href);"
            target="_blank" title="일정 보기">일정 보기</a>
  </div>
  <div id="hidden_order">
    <div class="hidden_order_title">숨김처리한 주문</div>
    <ul>
      <?php
      $hidden_order_result = sql_query("
        SELECT o.*, c.it_name, c.ct_option, count(*) as cnt FROM {$g5['g5_shop_order_table']} o
        INNER JOIN {$g5['g5_shop_cart_table']} c ON o.od_id = c.od_id
        WHERE o.mb_id = '{$member['mb_id']}' and od_hide_control = '1'
        GROUP BY o.od_id
        ORDER BY o.od_id desc
      ");
      $total_hidden_order = sql_num_rows($hidden_order_result);
      if(!$total_hidden_order) {
        echo '<div class="hidden_order_empty">숨긴 주문이 없습니다.</div>';
      }
      while($hidden_order = sql_fetch_array($hidden_order_result)) {
        $hidden_order['it_name'] .= ($hidden_order['ct_option'] != $hidden_order['it_name'] ? '('.$hidden_order['ct_option'].')' : '');
        $hidden_order['it_name'] .= ($hidden_order['cnt'] > 1 ? ' 외 '.$hidden_order['cnt'].'건' : '');
      ?>
      <li>
        <div class="hidden_order_info">
          <div class="row">주문번호 : <?=$hidden_order['od_id']?></div>
          <div class="row">주문일시 : <?=date('Y.m.d (H:i)', strtotime($hidden_order['od_time']))?></div>
          <div class="row">주문상품 : <?=$hidden_order['it_name']?></div>
        </div>
        <button class="btn_cancel btn_cancel_hide" data-id="<?=$hidden_order['od_id']?>">취소</button>
      </li>
      <?php } ?>
    </ul>
  </div>
  <form id="form_order_search" method="get">
    <div class="order-date">
      <div class="list-text" style="display:none">
        <div>
          <span><img src="<?=$SKIN_URL?>/image/icon_13.png" alt="">상품준비 <b><?=$item_wait_count?>건</b></span>
        </div>
        <div>
          <span><img src="<?=$SKIN_URL?>/image/icon_14.png" alt="">배송중 <b><?=$delivery_ing_count?>건</b></span>
        </div>
      </div>
      <div class="date-box" style="width: 100%;" method="get">
        <div class="list-checkbox">  
          <input type="checkbox" name="od_type0" id="od_type_0" value="0"<?=option_array_checked('0', $od_type0);?>/>&nbsp;<label for="od_type_0" style="vertical-align:-3px;">사업소주문</label>
          <?php
                // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  
                // 23.03.07 : 서원 - 이로움ON 에서 발생한 주문 정보에 대한 페이지 링크
                //                    관리자 및 특정 사업소 아이디 하드 코딩으로 접근.
                //                    추후 해당 부분 제거 필요 또는 특정 사업소 추가시 아이디 추가 필요.
                // H/C 파일 - \www\thema\eroumcare\shop.head.php
                //          - \www\skin\apms\order\new_basic\orderinquiry.skin.php
                //          - \www\shop\electronic_manage_new.php
                if( 
                  ($member['mb_level'] >= 9 ) || ($member['mb_id'] == "ariamart") || ($member['mb_id'] == "hula1202")
                ) {
              // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
          ?>
          <input type="checkbox" name="od_type1" id="od_type_1" value="1"<?=option_array_checked('1', $od_type1);?>/>&nbsp;<label for="od_type_1" style="vertical-align:-3px;">수급자주문(이로움ON)</label>
          <?php } ?>
        </div>
      </div>
        
        <div class="date-box" style="width: 100%;" method="get">
        <div class="list-date">
          <input type="text" name="s_date" value="<?=$_GET["s_date"]?>" id="date1" />
          ~
          <input type="text" name="e_date" value="<?=$_GET["e_date"]?>" id="date2" />
        </div>
        <div class="list-tab">
          <a href="javascript:;" onclick="searchDateSetting('1week');">일주일</a>
          <a href="javascript:;" onclick="searchDateSetting('1month');">이번달</a>
          <a href="javascript:;" onclick="searchDateSetting('3month');">3개월</a>
        </div>
        <div class="list-select">
          <input type="hidden" name="ct_status" value="<?=$_GET["ct_status"]?>">
          <input type="hidden" name="od_stock" value="<?=$_GET["od_stock"]?>">
          <input type="hidden" name="ct_release" value="<?=$_GET["ct_release"]?>">
          <div class="select">
            <p><?=$search_od_stock?></p>
            <ul>
              <li><a href="javscript:;" class="hiddenChange" data-target="od_stock" data-val="">주문+재고</a></li>
              <?php for ($i = 0; $i < count($order_stocks); $i++) { ?>
                <li><a href="javscript:;" class="hiddenChange" data-target="od_stock" data-val="<?= $order_stocks[$i]["val"] ?>"><?= $order_stocks[$i]["name"] ?></a></li>
              <?php } ?>
              <li><a href="javscript:;" class="hiddenChange" data-target="ct_release" data-val="true">출고</a></li>
            </ul>
          </div>
          <?php /*
          <div class="select">
            <input type="hidden" name="od_status" value="<?=$_GET["od_status"]?>">
            <p><?=$search_od_status?></p>
            <ul>
              <li><a href="javscript:;" class="hiddenChange" data-target="od_status" data-val="">전체 상태</a></li>
            <?php for($i = 0; $i < count($order_steps); $i++){ ?>
              <li><a href="javscript:;" class="hiddenChange" data-target="od_status" data-val="<?=$order_steps[$i]["val"]?>"><?=$order_steps[$i]["name"]?></a></li>
            <?php } ?>
            </ul>
          </div>
          */ ?>
          <button type="submit">검색</button>
        </div>
      </div>
    </div>

    <div class="cb">
      <div class="search_box">
        <select name="sel_field" id="sel_field">
          <option value="all"<?php if($sel_field == 'all') echo ' selected'; ?>>전체</option>
          <option value="o.od_id"<?php if($sel_field == 'o.od_id') echo ' selected'; ?>>주문번호</option>
          <option value="i.it_name"<?php if($sel_field == 'i.it_name') echo ' selected'; ?>>상품명</option>
          <option value="o.od_b_name"<?php if($sel_field == 'o.od_b_name') echo ' selected'; ?>>수령인</option>
        </select>
        <div class="input_search">
            <input name="search" value="<?=$search?>" type="text">
            <button id="btn_search" type="submit"></button>
        </div>
      </div>
    </div>
  </form>
  <div class="orderinquiry-head">
    <ul class="list_tab">
      <li class="<?php echo $ct_status !== '주문무효' ? 'active' : ''; ?>" data-tab="0"><a href="javascript:void(0);">주문내역</a></li>
      <li class="<?php echo $ct_status === '주문무효' ? 'active' : ''; ?>" data-tab="1"><a href="javascript:void(0);">취소/환불</a></li>
    </ul>
    <?php if ($_GET['ct_status'] !== '주문무효') { ?>
    <div class="latest_order_head flex" style="display: flex;">
      <a href="javascript:void(0);" class="step <?php echo $ct_status === '준비' ? 'active' : ''; ?>" data-step="준비">
        <div class="num"><?php echo $list_count['준비']; ?></div>
        <div class="desc">상품준비</div>
      </a>
      <div class="next">&gt;</div>
      <a href="javascript:void(0);" class="step <?php echo $ct_status === '출고준비' ? 'active' : ''; ?>" data-step="출고준비">
        <div class="num"><?php echo $list_count['출고준비']; ?></div>
        <div class="desc">출고준비</div>
      </a>
      <div class="next">&gt;</div>
      <a href="javascript:void(0);" class="step <?php echo $ct_status === '배송' ? 'active' : ''; ?>" data-step="배송">
        <div class="num"><?php echo $list_count['배송']; ?></div>
        <div class="desc">출고완료</div>
      </a>
      <div class="next">&gt;</div>
      <a href="javascript:void(0);" class="step <?php echo $ct_status === '완료' ? 'active' : ''; ?>" data-step="완료">
        <div class="num"><?php echo $list_count['완료']; ?></div>
        <div class="desc">배송완료</div>
      </a>
    </div>
    <?php } ?>
  </div>
  <script>


  $(function() {

    $(document).on('click', '.list_tab li', function() {
      tab = parseInt($(this).data('tab'));
      if (tab === 0) {
        $('input[name="ct_status"]').val('');
      }else {
        $('input[name="ct_status"]').val('주문무효');
      }
      $('#form_order_search').submit();
    });

    $(document).on('click', '.latest_order_head .step', function() {
      var step = $(this).data('step');
      $('input[name="ct_status"]').val(step);
      $('#form_order_search').submit();
    });
  });
  </script>
  <div class="list-wrap">
    <?php if(!$list){ ?>
        <style>
            .no_content{
                width:100%; height:100px; text-align:center;margin-top:150px;
            }
        </style>
        <div class="no_content">
            내용이 없습니다
        </div>
        <?php } ?>
  <?php for ($i = 0; $i < count($list); $i++){ $row = $list[$i]; ?>
  <?php
    $itemList = [];
    $stock_insert ="1";

    $ct_sql_search = '';
    $ct_where = [];
    $ct_where[] = " od_id = '{$row["od_id"]}' ";

    if( $od_type0=="0" ) $ct_where[] = " ct_type = '0' ";
    if( $od_type1=="1" ) $ct_where[] = " ct_type = '1' ";

    // if ($ct_status) {
    //   if ( $ct_status === '주문무효') {
    //     $where[] = " a.ct_status IN ('주문무효', '취소') ";
    //   } else {
    //     $where[] = " a.ct_status = '{$ct_status}' ";
    //   }
    // } else {
    //   // $ct_where[] = " a.ct_status IN ('준비', '출고준비', '배송', '완료') ";
    // }

    if ($ct_where) {
      $ct_where_query = $ct_sql_search ? ' and ' : ' where ';
      $ct_sql_search = $ct_where_query.implode(' and ', $ct_where);
    }

    $itemSQL = sql_query("
      SELECT a.*
        , ( SELECT it_img1 FROM {$g5["g5_shop_item_table"]} WHERE it_id = a.it_id ) AS it_img
        , ( SELECT prodSupYn FROM {$g5["g5_shop_item_table"]} WHERE it_id = a.it_id ) AS prodSupYn
      FROM {$g5["g5_shop_cart_table"]} a
      {$ct_sql_search}
    ");

    for($ii = 0; $item = sql_fetch_array($itemSQL); $ii++){
      // 설치결과보고서
      $item['report'] = null;
      $report = sql_fetch(" SELECT * FROM partner_install_report WHERE od_id = '{$item['od_id']}' ", true);
      if($report['od_id']) {
        $item['report'] = $report;
      }
      array_push($itemList, $item);
    }
  ?>
    <div class="table-list table-list2 orderinquiry-list-table">
      <!--
      <div class="top">
        <span> <i class="m_none">주문번호 :</i> <a href="<?=$row["od_href"]?>"><?=$row["od_id"]?></a> </span>
        <span> <?=display_price($row["od_total_price"])?> </span>
        <span class="m_none"> <?=date("Y.m.d(H:i)", strtotime($row["od_time"]))?></span>
      <?php if($row["recipient_yn"] == "Y"){ ?>
        <span class="btn-pro"> <img src="<?=$SKIN_URL?>/image/icon_15.png" alt=""> 수급자 주문 </span>
      <?php }else if($row["od_stock_insert_yn"] == "Y"){
                $stock_insert ="2";
            ?>
        <span class="btn-pro on"> 보유재고등록 </span>
      <?php } else { ?>
        <span class="btn-pro on"> <img src="<?=$SKIN_URL?>/image/icon_16.png" alt=""> 상품 주문 </span>
      <?php } ?>
      </div>

      <div class="info-wrap">
      <?php if($row["recipient_yn"] == "Y") { ?>
        <div class="info-top">
          <h5>수급자 정보 : <?=$row["od_penNm"]?> (<?=$row["od_penTypeNm"]?>)</h5>
          <a href="javascript:;" style="display: none;">계약서</a>
        </div>
      <?php } else { ?>
        <div class="info-top" style="background-color: #f5f5f5">
          <h5>받으시는 분 : <?=$row['od_b_name']?></h5>
        </div>
      <?php } ?>
      </div>
      -->
      <?php

      // 수급자 정보
      if($row['recipient_yn'] == 'Y') {
        echo '
          <div class="pen_info">
            <div class="btn_pen">
              <img src="'.THEMA_URL.'/assets/img/icon_pen.png">
              수급자 주문
            </div>
            수급자 정보 : '.$row["od_penNm"].' ('.$row["od_penTypeNm"].')
          </div>
        ';
      }
    ?>
      <div class="order_info">
        <span>
          <i class="pc">주문번호 :</i>
          <a href="<?php echo $row['od_href']; ?>"><?php echo $row["od_id"]; ?></a>
        </span>
        <span><?php echo display_price($row["od_total_price"]); ?></span>
        <span><?php echo '주문 : ' . date('n월 j일 (H:i)', strtotime($row['od_time'])); ?></span>
        <?php /* if ($row['ct_direct_delivery_date']) { ?>
          <span><?php echo '출고예정 : ' . date('n월 j일 (H:i)', strtotime($row['ct_direct_delivery_date'])); ?></span>
        <?php } */ ?>
        <?php if ($row['od_b_name']) { ?>
        <span>배송 : <?php echo $row['od_b_name']; ?></span>
        <?php } ?>
      </div>

      <?php foreach($itemList as $item){
                // //바코드 개수 구하기
                // $sendData2=[];
                // // $sendData2["stateCd"] =['01','02','08','09'];
                // $sendData2["usrId"] = $member["mb_id"];
                // $sendData2["entId"] = $member["mb_entId"];
                // $sendData2["prodId"] = $item["it_id"];
                // $oCurl = curl_init();
                // curl_setopt($oCurl, CURLOPT_PORT, 9901);
                // curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_SELECT_DETAIL_LIST);
                // curl_setopt($oCurl, CURLOPT_POST, 1);
                // curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData2, JSON_UNESCAPED_UNICODE));
                // curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                // curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                // $res = curl_exec($oCurl);
                // $res = json_decode($res, true);
                // curl_close($oCurl);
                // // print_r($res["data"][0]['prodBarNum']);
                // $barcode_c=0;
                // for($k=0;$k<count($res["data"]); $k++){
                //     if($res["data"][$k]['prodBarNum']) $barcode_c++;
                // }
                // print_r($item['ct_qty']);
            ?>

        <div class="list">
          <ul class="cb">
            <li class="pro">
              <div class="img" style="min-width:100px; min-height:100px;">
              <?php if($item["it_img"]){ ?>
                <a href="<?=$row["od_href"]?>"><img src="/data/item/<?=$item["it_img"]?>" onerror="this.src='/img/no_img.png';"></a>
              <?php } ?>
              </div>
              <div class="pro-info">
              <?php if($row["recipient_yn"] == "Y"){ ?>
                <div class="day">
                <?php if($item["ordLendStrDtm"] && $item["ordLendStrDtm"] != "0000-00-00 00:00:00"){ ?>
                  <i>대여</i>
                  <?=date("Y.m.d", strtotime($item["ordLendStrDtm"]))?> ~ <?=date("Y.m.d", strtotime($item["ordLendEndDtm"]))?>
                <?php } else { ?>
                  <i class="on-order">주문</i>
                <?php } ?>
                </div>
              <?php } ?>
                <div class="name">
                  <a href="<?=$row["od_href"]?>">
                                    <?php $option_v = $item["ct_option"];?>
                  <?=$item["it_name"]?> <?=($item["ct_option"] && $item["ct_option"] != $item["it_name"]) ? "({$item["ct_option"]})" : ""?>
                  <?=($item["prodSupYn"] == "N") ? "<b>비유통</b>" : ""?>
                  </a>
                  <?php if( $item['ct_direct_delivery_date'] ) { ?>
                    <?php if( $item['ct_is_direct_delivery'] == '2' ) { ?>
                    <span style="padding-left:50px; font-size:11px;"><?php echo '설치예정 : ' . date('n월 j일 (H:i)', strtotime($item['ct_direct_delivery_date'])); ?></span>
                    <?php } else {?>
                    <span style="padding-left:50px; font-size:11px;"><?php echo '출고예정 : ' . date('n월 j일 (H:i)', strtotime($item['ct_direct_delivery_date'])); ?></span>
                    <?php } ?>
                  <?php } ?>
                </div>
                <div>
                  <em>수량 : <?=$item["ct_qty"]?></em>
                <?php if($item["ct_stock_qty"]){ ?>
                  <em>, 재고소진 : <?=$item["ct_stock_qty"]?></em>
                <?php } ?>
                </div>
                <div class="pc_none">
                  <?php
                  $sql = "select *
                          from g5_shop_order_cancel_request
                          where od_id = '{$row['od_id']}' and approved = 0";
                  $cancel_request_row = sql_fetch($sql);

                  if (!empty($cancel_request_row)) {
                    if ($cancel_request_row['request_type'] == 'cancel')
                      echo '취소요청';
                    if ($cancel_request_row['request_type'] == 'return')
                      echo '반품요청';
                  } else {
                    if ($row["od_status"]=="주문무효"||$row["od_status"]=="주문취소"){
                      echo $row["od_status"];
                    } else {
                      $ct_status_text="";
                      switch ($item['ct_status']) {
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
                  ?>
                </div>
              </div>
            </li>
            <li class="delivery m_none">
              <?php
              if (!empty($cancel_request_row)) {
                if ($cancel_request_row['request_type'] == 'cancel')
                  echo '취소요청';
                if ($cancel_request_row['request_type'] == 'return')
                  echo '반품요청';
              } else {
                if ($row["od_status"]=="주문무효"||$row["od_status"]=="주문취소"){
                  echo $row["od_status"];
                } else {
                  echo $ct_status_text;
                }
              }
              ?>
            </li>
            <li class="info-btn">
              <?php if($item['ct_status'] !== "취소" && $item['ct_status'] !== "주문무효"){ ?>
                            <div class="barcode_preview">
                                <ul>
                                    <?php
                                    // 바코드 5개 미리보기
                                    $stoId_arr = array(
                                            'stoId' => $item['stoId']
                                    );
                                    $res = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $stoId_arr);

                                    $prodBarNum_arr = [];
                                    for ($j = 0; $j < count($res['data']); $j++) {
                                        if (!empty($res['data'][$j]['prodBarNum'])) {
                                            $prodBarNum_arr[] =  $res['data'][$j]['prodBarNum'];
                                        }
                                    }
                                    sort($prodBarNum_arr); // 오름차순 정렬

                                    $limit = count($prodBarNum_arr) > 6 ? 6 : count($prodBarNum_arr);
                                    for ($j = 0; $j < $limit; $j++) {
                                        echo "<li>{$prodBarNum_arr[$j]}</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
              <div>
                            <?php
                                $sendData = [];
                                $sendData["penOrdId"] = $item["ordId"];
                                $sendData["uuid"] = $item["uuid"];
                                $sendData["it_id"] = $item["it_id"];

                                $oCurl = curl_init();
                                curl_setopt($oCurl, CURLOPT_PORT, 9901);

                                curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_ORDER_SELECT_LIST);
                                curl_setopt($oCurl, CURLOPT_POST, 1);
                                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
                                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                                $res = curl_exec($oCurl);
                                curl_close($oCurl);

                                $result = json_decode($res, true);
                                $result = $result["data"];
                                // print_r($item);
                            ?>
                                <?php if($item["prodSupYn"] == "N"){ ?>

                <!-- <li class="barInfo barcode_box  disable" data-id="2021052417305437" data-ct-id="48984"><span class="cnt">입력완료</span></li> -->
                <a href="#" class="btn-03 btn-0 popupProdBarNumInfoBtn" data-id="<?=$row["od_id"]?>" data-ct-id="<?=$item["ct_id"]?>">
                바코드 확인
                </a>
                <?php } else {
                  if($limit>0){
                ?>
                  <a href="#" class="btn-01 btn-0 popupProdBarNumInfoBtn" data-id="<?=$row["od_id"]?>" data-ct-id="<?=$item["ct_id"]?>"><img src="<?=$SKIN_URL?>/image/icon_02.png" alt="">
                  바코드
                  </a>
                <?php   }
                }
                ?>
              <?php if(($item['ct_status'] == '배송' || $item['ct_status'] == '완료') && ($item["prodSupYn"] == "Y")){ ?>
                <a href="#" class="btn-02 btn-0 popupDeliveryInfoBtn" data-od="<?=$row["od_id"]?>">배송정보</a>
              <?php } ?>
              <?php if($row["od_status"] == "배송완료") {
                $sql_v= "SELECT `ca_id` FROM `g5_shop_item` WHERE `it_id` = '".$item["it_id"]."'";
                $result_v=sql_fetch($sql_v);
                $str = substr($result_v['ca_id'],0 , 2);
                if($str=="20"){
                    $path ="sales_Inventory_datail2.php";
                }else{
                    $path ="sales_Inventory_datail.php";
                }
              ?>
                <a href="<?php echo G5_SHOP_URL; ?>/<?=$path?>?prodId=<?=$item["it_id"]?>&page=&searchtype=&searchtypeText=" class="btn-02 btn-0">재고확인</a>
              <?php } ?>
              <?php if($ct_status_text == "출고완료"){ ?>
                <!-- 이카운트 등록오류 가능할 수 있어 임시로 삭제
                  <a href="#" class="btn-04 btn-0 delivery_ok" data-ct-id="<?php echo $item['ct_id']; ?>" data-od-id="<?php echo $row["od_id"]; ?>">배송완료</a> -->
              <?php } ?>
              </div>
              <?php } ?>

              <?php if($item['report'] && ($item['report']['ir_cert_url'] || $item['report']['ir_file_url'])) { ?>
              <div style="margin-top: 6px;">
                <a href="#" class="btn-01 btn-0 btn_install_report" style="font-size: 12px; color: #666" data-id="<?=$row["od_id"]?>">설치결과보고서</a>
              </div>
              <?php } ?>

              <?php if( $item["ct_delivery_num"] != null ) { ?>
                <div style="margin-top: 6px;">
                <?php if($delivery_company[$item["ct_delivery_company"]]){ ?>
                  <a href="/#/" data-url="https://tracker.delivery/#/<?=$delivery_company[$item["ct_delivery_company"]]?>/<?=str_replace('-','',$item["ct_delivery_num"])?>" class="btn-01 btn-0 btn_delivery_tracking_y popupDeliveryTrackingBtn" style="font-size: 12px; color: #666" target="_blank">배송조회</a>
                <?php } else {?>
                  <a href="/#/" class="btn-01 btn-0 btn_delivery_tracking_n" style="font-size: 12px; color: #666">배송조회</a>
                <?php }?>
                </div>
              <?php } ?>
            </li>
          </ul>
        </div>

      <?php } ?>
    </div>
  <?php } ?>
  </div>

</section>

<div class="text-center">
  <ul class="pagination pagination-sm en">
    <?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
  </ul>
</div>

<?php if($setup_href) { ?>
  <p class="text-center">
    <a class="btn btn-color btn-sm win_memo" href="<?php echo $setup_href;?>">
      <i class="fa fa-cogs"></i> 스킨설정
    </a>
  </p>
<?php } ?>

<script type="text/javascript">
function showSchdule(url) {
    let opt = "width=1360,height=780,left=0,top=10";
    let _url = url;
    if (jQuery.browser.mobile) {
        opt = "";
        _url = _url.replace("index.php", "m_index.php");
    }
    window.open(_url, "win_schedule", opt);
    return false;
}

function searchDateSetting(type){
  switch(type){
    case "1week" :
      $("#date1").val("<?=date("Y-m-d", strtotime("- 7 days"))?>");
      break;
    case "1month" :
      $("#date1").val("<?=date("Y-m-01")?>");
      break;
    case "3month" :
      $("#date1").val("<?=date("Y-m-d", strtotime("- 3 month"))?>");
      break;
  }

  $("#date2").val("<?=date("Y-m-d")?>");
}

$(function() {
  $(document).click(function(event) {
    if(
      !$(event.target).closest('#hidden_order').length &&
      !$(event.target).is('#hidden_order') &&
      !$(event.target).is('#btn_hidden_order') &&
      !$('#hidden_order').is(':hidden')
    ) {
      $('#hidden_order').hide();
    }
  });
  $('#btn_hidden_order').click(function() {
    if($('#hidden_order').is(':hidden'))
      $('#hidden_order').show();
    else
      $('#hidden_order').hide();
  });
  $('.btn_cancel_hide').click(function() {
    var od_id = $(this).data('id');

    $.post('ajax.order.cancel_hide.php', {
      od_id: od_id
    }, 'json')
    .done(function() {
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $(".hiddenChange").click(function(){
    var target = $(this).attr("data-target");
    var val = $(this).attr("data-val");

    $(this).closest("form").find("input[name='" + target + "']").val(val);

    if (target === 'od_stock') {
      $(this).closest("form").find('input[name="ct_release"]').val('');
    } else {
      $(this).closest("form").find('input[name="od_stock"]').val('');
    }
  });

  $('.delivery_ok').click(function(e) {

    e.preventDefault();

    var od_id = $(this).data('od-id');
    var ct_id = $(this).data('ct-id');

    $.ajax({
      method: "POST",
      dataType:"json",
      url: "./ajax.order.delivery.ok.php",
      data: {
        od_id: od_id,
        ct_id: ct_id
      }
    }).done(function(data) {
      console.log(data);
      if ( data.msg ) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        location.reload(true);
      }
    })
  })

});
</script>
