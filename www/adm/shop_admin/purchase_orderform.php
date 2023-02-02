<?php
$sub_menu = '400480';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = "발주 내역 수정";
include_once(G5_ADMIN_PATH . '/admin.head.php');

/**
* 기존에 있던  purchase_order,purchase_cart_memo,purchase_order_admin_log,purchase_cart 테이블을 재사용하기 위한 작업
* 새로이 필요한 컬럼이 존재하는지 확인 후, 없으면 새로 추가하는 작업 진행
* 기존 purchase_order_admin_memo 테이블의 om_datetime 컬럼 디폴트 값이 current_timestamp() 아니면 설정
*/
$sql_check = "
  show columns from purchase_order where field in ('od_fax','od_send_mail_yn','od_send_hp_yn','od_send_fax_yn','od_send_yn','od_discount_info');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){
  $append_col = "alter table purchase_order ".
                "add column od_fax varchar(20) default null comment '팩스번호 저장' after od_hp,".
                "add column od_send_mail_yn tinyint default '0' comment '발주서 메일발송 여부' after od_addr_jibeon,".
                "add column od_send_hp_yn tinyint default '0' comment '발주서 문자발송 여부' after od_addr_jibeon,".
                "add column od_send_fax_yn tinyint default '0' comment '발주서 팩스 발송 여부' after od_addr_jibeon,".
                "add column od_send_yn tinyint default '0' comment '발주서 발송여부' after od_addr_jibeon,".
                "add column od_discount_info text default null comment '할인 정보 json 방식 데이터 저장' after od_purchase_manager";
  sql_query($append_col);
}

$sql_check = "
  show columns from purchase_cart_memo where field in ('mb_id');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){
  $append_col = "alter table purchase_cart_memo ".
                "add column mb_id varchar(20) default null comment '작성자 id' after od_id";
  sql_query($append_col);
}

$sql_check = "
  show columns from purchase_order_admin_log where field in ('ol_type');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){
  $append_col = "alter table purchase_order_admin_log ".
                "add column ol_type tinyint default '0' comment '1:메일발송, 2:SMS방송, 3:FAX발송' after ol_content";
  sql_query($append_col);
}

$sql_check = "
  show columns from purchase_cart where field in ('ct_part_info', 'ct_modify_date');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){
  $append_col = "alter table purchase_cart ".
                "add column ct_modify_date datetime default null comment '부분 입,출고 차수에 따른 수량 및 배송정보' after ct_notax,".
                "add column ct_part_info text default null comment '부분 입,출고 차수에 따른 수량 및 배송정보' after ct_notax";
  sql_query($append_col);
}

$sql_check = "
  show columns from purchase_order_admin_memo where field = 'om_datetime';
";
$res_check = sql_fetch($sql_check);
if($res_check['Default'] != 'current_timestamp()') {
  $append_col = "alter table purchase_order_admin_memo modify column om_datetime datetime not null default current_timestamp()";
  sql_query($append_col);
};

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from purchase_order where od_id = '$od_id' AND od_del_yn = 'N' ";
$od = sql_fetch($sql);
$prodList = [];
$prodListCnt = 0;
$deliveryTotalCnt = 0;
$delivery_insert = 0;
if (!$od['od_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
} else {
  $sto_imsi = "";
  $sql_ct = " select * from purchase_cart where od_id = '$od_id' ";
  $result_ct = sql_query($sql_ct);
  //배송정보

  while ($row_ct = sql_fetch_array($result_ct)) {
    $sto_imsi .= $row_ct['stoId'];

    //배송정보
    if ($row_ct['ct_combine_ct_id'] || $row_ct['ct_delivery_num']) {
      $delivery_insert++;
    }
  }
  $stoIdDataList = explode('|', $sto_imsi);
  $stoIdDataList = array_filter($stoIdDataList);
  $stoIdData = implode("|", $stoIdDataList);
}

$mb = get_member($od['mb_id']);
$od_status = get_step($od['od_status']);
$pay_status = get_pay_step($od['od_pay_state']);

$od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";

//수급자정보
$od_penId = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penId'] : '';        // penId
$od_penNm = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penNm'] : $od['od_name'];  // 수급자
$od_penTypeNm = (isset($od['od_penId']) && $od['od_penId']) ? $od['penTypeNm'] : '';        //안전등급
$od_penExpiDtm = (isset($od['od_penId']) && $od['od_penId']) ? $od['penExpiDtm'] : '';        //유효기간
$od_penAppEdDtm = (isset($od['od_penId']) && $od['od_penId']) ? $od['penAppEdDtm'] : '';      //적용기간
$od_penConPnum = (isset($od['od_penId']) && $od['od_penId']) ? $od['penConPnum'] : $od['od_tel'];  //전화번호
$od_penConNum = (isset($od['od_penId']) && $od['od_penId']) ? $od['penConNum'] : $od['od_hp'];  //휴대전화
$od_penzip1 = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penzip1'] : $od['od_zip1'];//우편번호
$od_penzip2 = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penzip2'] : $od['od_zip2'];
$od_penzip = (isset($od['od_penId']) && $od['od_penId']) ? $od_penzip1 . $od_penzip2 : $od['od_zip1'] . $od['od_zip2'];

$od_penAddr = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penAddr'] : $od['od_addr1'] . '' . $od['od_addr2'] . '' . $od['od_addr3'];  //주소

$avail_request_return = false;

// 창고목록
$sql = "select * from warehouse where wh_use_yn = 'Y' order by wh_id asc";
$result = sql_query($sql);
$warehouse_list = [];
while($row = sql_fetch_array($result)) {
  $warehouse_list[] = $row;
}

// 상품목록
$sql = "
  select
    a.ct_id,
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
    a.ct_warehouse,
    a.ct_warehouse_address,
    a.ct_warehouse_phone,
    a.ct_part_info
  from
    purchase_cart a
  left join
    {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
  where
    a.od_id = '$od_id'
  group by
    a.it_id, a.ct_uid
  order by
    a.ct_id
";

$result = sql_query($sql);

$carts = array();
$cate_counts = array();

for ($i = 0; $row = sql_fetch_array($result); $i++) {

  $cate_counts[$row['ct_status']] += 1;

  // 상품의 옵션정보
  $sql = "
    select
      MT.*,
      b.prodSupYn,
      b.it_taxInfo,
      b.it_type3
    from
      purchase_cart MT
      left join
        {$g5['g5_shop_item_table']} b on ( MT.it_id = b.it_id )
    where
      MT.od_id = '{$od['od_id']}' and
      MT.it_id = '{$row['it_id']}' and
      MT.ct_uid = '{$row['ct_uid']}'
    order by
      MT.io_type asc, MT.ct_id asc
  ";
  $res = sql_query($sql);

  $row['options_span'] = sql_num_rows($res);

  $row['options'] = array();

  for ($k = 0; $opt = sql_fetch_array($res); $k++) {

    $opt_price = 0;

    if ($opt['io_type'])
      $opt_price = $opt['io_price'];
    else
      $opt_price = $opt['ct_price'] + $opt['io_price'];

    $opt["opt_price"] = $opt_price;

    // 소계
    $opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
    if ($opt["prodSupYn"] == "Y") {
      $opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
    }
    // 단가 역산
    $opt["opt_price"] = $opt['ct_price_stotal'] ? @round($opt['ct_price_stotal'] / ($opt["ct_qty"] - $opt["ct_stock_qty"])) : 0;

    // 공급가액
    $opt["basic_price"] = $opt['ct_price_stotal'];
    // 부가세
    $opt["tax_price"] = 0;
    if ($opt['it_taxInfo'] != "영세") {
      // 공급가액
      $opt["basic_price"] = round($opt['ct_price_stotal'] / 1.1);
      // 부가세
      $opt["tax_price"] = round($opt['ct_price_stotal'] / 11);
    }

    $opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

    // 한개라도 출고완료 있으면 관리자가 반품신청 가능하도록 함.
//    if ($opt['ct_status'] === '배송') {
//      $avail_request_return = true;
//    }

    $row['options'][] = $opt;
  }


  // 합계금액 계산
  $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
                  SUM(ct_qty) as qty,
                  SUM(ct_discount) as discount,
                  SUM(ct_send_cost) as sendcost
              from purchase_cart
              where it_id = '{$row['it_id']}'
                  and od_id = '{$od['od_id']}'
                  and ct_uid = '{$row['ct_uid']}'";
  $sum = sql_fetch($sql);

  $row['sum'] = $sum;

  $carts[] = $row;
}

// 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액 - 추가할인금액
$amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_cart_discount2'] - $od['od_sales_discount'];

// 입금액 = 결제금액 + 포인트
$amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

// 쿠폰금액
$amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

// 취소금액
$amount['cancel'] = $od['od_cancel_price'];

// 미수금 = 주문금액 - 취소금액 - 입금금액 - 쿠폰금액
//$amount['미수'] = $amount['order'] - $amount['receipt'] - $amount['coupon'];

$typereceipt = get_typereceipt_step($od_id);
$typereceipt_cate = get_typereceipt_cate($od_id);

$next_step = get_next_step($od['od_status']);
$prev_step = get_prev_step($od['od_status']);

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
add_stylesheet('<link rel="stylesheet" href="' . G5_CSS_URL . '/magnific-popup.css">', 0);
add_javascript('<script src="' . G5_JS_URL . '/jquery.magnific-popup.js"></script>', 0);
include_once(G5_PLUGIN_PATH . '/jquery-ui/datepicker.php'); // datepicker js

// 파트너
$is_use_partner = (defined('USE_PARTNER') && USE_PARTNER) ? true : false;
$total_ct_delivery_cnt = 0;
//상품 옵션 개수별 바코드 필드추가
sql_query(" ALTER TABLE `purchase_cart` ADD `ct_barcode` TEXT NOT NULL AFTER `ct_qty` ", false);


$sql_ct = " select * from purchase_cart where od_id = '$od_id' ";
$result_ct = sql_query($sql_ct);
$qty = 0;
$insert_qty = 0;
while ($row_ct = sql_fetch_array($result_ct)) {
  if ($row_ct['ct_status'] !== "취소" && $row_ct['ct_status'] !== "주문무효") {
    $qty += $row_ct['ct_qty'];
    if ($row_ct['ct_barcode_insert'])
      $insert_qty += $row_ct['ct_barcode_insert'];
  }
}

$prodBarNumCntBtnWord = $insert_qty . "/" . $qty;
$prodBarNumCntBtnWord = ($insert_qty >= $qty) ? "입력완료" : $prodBarNumCntBtnWord;
$prodBarNumCntBtnStatus = ($insert_qty >= $qty) ? " disable" : "";

$deliveryCntBtnWord = "배송정보 ({$delivery_insert}/{$od["od_delivery_total"]})";
$deliveryCntBtnWord = ($delivery_insert >= $od["od_delivery_total"]) ? "입력완료" : $deliveryCntBtnWord;
$deliveryCntBtnStatus = ($delivery_insert >= $od["od_delivery_total"]) ? " disable" : "";


?>
<script>
  var od_id = '<?php echo $od['od_id']; ?>';
</script>
<style>
  /*
#samhwa_order_form>.block .item_list table .item_barcode {
    width:15%;
}
*/

  #prodBarNumSaveBtn {
    border: 1px solid #333 !important;
    background-color: #333 !important;
    color: #FFF !important;
  }

  #prodBarNumSaveBtn:hover {
    background-color: #222 !important;
  }

  .barNumGuideBox {
    position: absolute;
    border: 1px solid #DDD;
    background-color: #FFF;
    text-align: left;
    padding: 15px 20px;
    display: none;
    margin-left: 35px;
    margin-top: 5px;
  }

  .barNumGuideBox > .title {
    width: 100%;
    font-weight: bold;
    margin-bottom: 15px;
    position: relative;
  }

  .barNumGuideBox > .title > button {
    float: right;
  }

  .barNumGuideBox > p {
    width: 100%;
    padding: 0;
  }

  .prodBarNumCntBtn {
    height: 29px;
    line-height: 29px;
    font-size: 11px;
  }

  #popup_box {
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

  #popup_box > div {
    width: 100%;
    height: 100%;
    display: table-cell;
    vertical-align: middle;
  }

  #popup_box iframe {
    position: relative;
    width: 500px;
    height: 700px;
    border: 0;
    background-color: #FFF;
    left: 50%;
    margin-left: -250px;
  }

  @media (max-width: 750px) {
    #popup_box iframe {
      width: 100%;
      height: 100%;
      left: 0;
      margin-left: 0;
    }
  }
</style>
<div id="samhwa_order_form">
  <div class="block">
    <div class="header">
      <h2>발주정보<span>(발주일시:<?php echo $od['od_time']; ?>)</span>
        <span class='box_orange'>구매발주</span>
      </h2>
    </div>
    <div class="item_list">
      <form name="frmsamhwaorderform" method="post" id="frmsamhwaorderform">
        <table style="border-collapse: collapse">
          <thead>
          <tr>
            <th class="chkbox">
              <input type="checkbox" id="sit_select_all">
            </th>
            <th class="chkbox">&nbsp;</th>
            <!--<th>분류</th>-->
            <th class="item_name">상품</th>
            <th class="item_qty">수량</th>
            <th class="item_price">단가</th>
            <th class="item_basic_price">공급가액</th>
            <th class="item_tax_price">부가세</th>
            <th class="item_stotal">합계</th>
            <th class="item_status">상태</th>
            <th class="item_memo">요청사항</th>
            <th class="item_memo">입고예정일</th>
            <th class="item_memo">입고완료일</th>
            <th class="item_memo">입고관리</th>
            <th class="btncol"></th>
          </tr>
          </thead>
          <tbody>
          <?php
          $pt_email = array();
          $pt_name = array();
          $chk_cnt = 0;
          $tot_price = 0;
          $tot_qty = 0;
          $tot_discount = 0;
          $tot_total = 0;
          $tot_sendcost = 0;

          for ($i = 0; $i < count($carts); $i++) {
            # 요청사항
            $prodMemo = "";

            # 대여기간
            $ordLendDtm = "";

            # 배송수량
            $deliveryCnt = 0;
            if ($carts[$i]["prodSupYn"] == "Y" && $carts[$i]["od_delivery_yn"] == "Y") {
              $deliveryCnt = $carts[$i]["ct_qty"] - $carts[$i]["ct_stock_qty"];
              $deliveryTotalCnt += $deliveryCnt;
            }

            // 상품이미지
            $image = "<img src='/data/item/{$carts[$i]["it_img1"]}' onerror='this.src=\"/shop/img/no_image.gif\";' style='width: 50px; height: 50px;'>";
            $options = $carts[$i]['options'];

            $chk_first = 0;

            $tot_price += $carts[$i]['sum']['price'] - $carts[$i]['sum']['discount'];
            $tot_qty += $carts[$i]['sum']['qty'];
            // $tot_discount += $carts[$i]['sum']['discount'];
            $tot_sendcost += $carts[$i]['sum']['sendcost'];
            $tot_total += ($carts[$i]["prodSupYn"] == "Y") ? $carts[$i]['sum']['price'] - $carts[$i]['sum']['discount'] : 0;

            $prodBarNum = $prodOptNum = '';
            $option_array = array();
            $barcode_array = array();

            // 발주서 양식 개편으로 인해 발주서 내 모든 상품 도착지가 같음
            $od_warehouse = $options[0]['ct_warehouse'];
            $od_warehouse_phone = $options[0]['ct_warehouse_phone'];
            $od_warehouse_address = $options[0]['ct_warehouse_address'];

            for ($k = 0; $k < count($options); $k++) {
              # 요청사항
              $prodMemo = ($prodMemo) ? $prodMemo : $carts[$i]["prodMemo"];
              $option_array[] = $options[$k]['ct_option'];
              $barcode_array[] = $options[$k]['ct_barcode'];
              ?>
              <tr class="<?php echo $k == 0 ? 'top-border' : ''; ?>">
                <td rowspan="1" class="chkcbox">
                  <label for="sit_sel_<?php echo $i; ?>_<?php echo $k; ?>"
                         class="sound_only"><?php echo $carts[$i]['it_name']; ?> 옵션 전체선택</label>
                  <input type="checkbox" id="sit_sel_<?php echo $i; ?>_<?php echo $k; ?>" name="it_sel[]"
                         value="<?= $options[$k]['ct_id'] ?>">
                </td>
                <td class="chkbox">
                  <label for="ct_chk_<?php echo $chk_cnt; ?>"
                         class="sound_only"><?php echo get_text($options[$k]['ct_option']); ?></label>
                  <!--
                                        <input type="checkbox" name="ct_chk[<?php echo $chk_cnt; ?>]" id="ct_chk_<?php echo $chk_cnt; ?>" value="<?php echo $chk_cnt; ?>" class="sct_sel_<?php echo $i; ?>">-->
                  <input type="hidden" name="ct_id[<?php echo $chk_cnt; ?>]"
                         value="<?php echo $options[$k]['ct_id']; ?>">

                  <input type="checkbox" name="ct_chk[]" id="ct_chk_<?php echo $chk_cnt; ?>"
                         value="<?php echo $options[$k]['ct_id']; ?>" class="sct_sel_<?php echo $i; ?>"
                         style="visibility: hidden;">
                </td>
                <td class="item_name">
                  <div class="item_name_box">
                    <div class="left" style="width: 100%; float: left;">
                      <?php if ($options[$k]['io_type'] == 0 && $k == 0) { ?>
                        <a href="/shop/item.php?it_id=<?php echo $carts[$i]['it_id']; ?>" class="image" target="_blank"
                           style="float: left;"><?php echo $image; ?></a>
                        <div class="item_info" style="width: calc(100% - 80px); float: left; padding-left: 15px;">
                          <b>
                            <?php echo stripslashes($carts[$i]['it_name']); ?>
                            <a href="./itemform.php?w=u&amp;it_id=<?php echo $carts[$i]['it_id']; ?>" class="name">보기</a></b><br>
                          <span><?php echo $carts[$i]['it_model']; ?></span>
                          <?php if ($carts[$i]['it_name'] != $options[$k]['ct_option']) { ?>
                            [옵션] <?php echo $options[$k]['ct_option']; ?>
                          <?php } ?>
                        </div>
                      <?php } else { ?>
                        <span style="margin-right:60px;"></span>
                        <b>
                          <?php if ($carts[$i]['it_name'] != $options[$k]['ct_option']) { ?>[옵션]<?php } ?>
                          <?php echo $options[$k]['ct_option']; ?></b>
                      <?php } ?>
                    </div>
                  </div>
                </td>
                <td class="item_qty">
                  <label for="ct_qty_<?php echo $chk_cnt; ?>" class="sound_only"><?php echo get_text($options[$k]['ct_option']); ?> 수량</label>
                  <?php echo $options[$k]['ct_qty']; ?>
                </td>
                <td class="item_price">
                  <?php echo number_format($options[$k]['opt_price']); ?>원
                </td>
                <td class="item_basic_price">
                  <?php echo number_format($options[$k]['basic_price']); ?>원
                </td>
                <td class="item_tax_price">
                  <?php echo number_format($options[$k]['tax_price']); ?>원
                </td>
                <td class="item_stotal">
                  <?php echo number_format($options[$k]['ct_price_stotal']); ?>원
                </td>
                <td class="item_status">
                  <?php
                  echo $options[$k]['ct_status'];
                  ?>
                </td>
                <td class="item_memo">
                  <?php
                  echo $prodMemo;
                  ?>
                </td>
                <td class="btncol">
                  <!-- 입고예정일 -->
                  <?php $ct_part_info = json_decode($options[$k]['ct_part_info'],true)[1]; echo $ct_part_info['_in_dt'] ? date('Y-m-d H시', strtotime($ct_part_info['_in_dt'])) : ''; ?>
                  <!-- 입고예정일 -->
                </td>
                <td class="btncol">
                  <!-- 입고완료일 -->
                  <?php echo $ct_part_info['_in_dt_confirm'] ? date('Y-m-d H시', strtotime($ct_part_info['_in_dt_confirm'])) : ''; ?>
                  <!-- 입고완료일 -->
                </td>
                <td class="btncol">
                  <a href="javascript:void();" data-ct-id="<?=$options[$k]['ct_id']?>" class="prodBarNumCntBtn purchaseOrderViewBtn">입고관리 (<?=$options[$k]['ct_delivered_qty']?>/<?=$options[$k]['ct_qty']?>)</a>
                </td>
              </tr>
              <?php
              $chk_first++;
              $chk_cnt++;
            }

            $prodOptNum = implode('^', $option_array);
            $prodBarNum = implode('^', $barcode_array);

            if ($prodMemo) { ?>
<!--              <tr>-->
<!--                <td></td>-->
<!--                <td colspan="11" style="text-align: left;">-->
<!--                  <b>요청사항 : </b> --><?//= $prodMemo ?>
<!--                </td>-->
<!--              </tr>-->
            <?php } ?>
          <?php } ?>
          <tr class="result">
            <td class="chkbox">
            </td>
            <td class="chkbox">
            </td>
            <td class="item_name">
            </td>
            <td class="item_qty">
              <?php echo number_format($tot_qty); ?>
            </td>
            <td class="item_basic_price">
            </td>
            <td class="item_tax_price">
            </td>
            <td class="item_price">
            </td>
            <td class="item_stotal">
              <?php echo number_format($tot_total); ?>원
            </td>
            <td class="item_status">
            </td>
            <td class="item_memo"></td>
            <td class="btncol"></td>
            <td class="btncol"></td>
            <td class="btncol"></td>
          </tr>
          <?php $total_discount = 0; $total_qty = 0;
          if($od['od_discount_info']) {
            $od_discount_info = json_decode($od['od_discount_info'], true);
          ?>
          <tr style="margin-top: 10px;">
            <td colspan="13" style="text-align: left; padding: 10px 0 5px 0;">
              <p style="font-size: small; font-weight: bolder; padding: 0 5px;">할인/반품 정보</p>
            </td>
          </tr>
          <tr style="border-top: 1px solid #dddddd; padding: 0; background-color: #f3f3f3;">
            <th>No.</th>
            <th colspan="2">상품명</th>
            <th colspan="1">수량</th>
            <th colspan="2">가격</th>
            <th colspan="1">공급가액</th>
            <th colspan="1">부가세</th>
            <th colspan="2">합계</th>
            <th colspan="3">요청사항</th>
          </tr>
          <?php for($ind = 0; $ind <count($od_discount_info); $ind++) {
            $total_qty += $od_discount_info[$ind]['discount_qty'];
            $total_discount += $od_discount_info[$ind]['discount_it_price']*$od_discount_info[$ind]['discount_qty'];?>
          <tr style="border-top: 1px solid #dddddd;">
            <td class="no"><span class="index"><?=$ind+1;?></span></td>
            <td colspan="2" name="discount_it_name[]" ><?=$od_discount_info[$ind]['discount_it_name'];?></td>
            <td colspan="1" name="discount_qty[]"><?=$od_discount_info[$ind]['discount_qty'];?></td>
            <td colspan="2" name="discount_it_price[]"><?=number_format($od_discount_info[$ind]['discount_it_price']);?>원</td>
            <td colspan="1" class="basic_price"><?=number_format(round(($od_discount_info[$ind]['discount_it_price']*$od_discount_info[$ind]['discount_qty']) / 1.1));?>원</td>
            <td colspan="1" class="tax_price"><?=number_format(($od_discount_info[$ind]['discount_it_price']*$od_discount_info[$ind]['discount_qty']) - round(($od_discount_info[$ind]['discount_it_price']*$od_discount_info[$ind]['discount_qty']) / 1.1));?>원</td>
            <td colspan="2" class="tax_price"><?=number_format($total_discount);?>원</td>
            <td colspan="3" name="discount_memo[]"><?=$od_discount_info[$ind]['discount_memo'];?></td>
          </tr>
          <?php } ?>
          <tr style="border-top: 1px solid #dddddd; background-color: #f3f3f3;">
            <th></th>
            <th colspan="2"></th>
            <th colspan="1"><?=$total_qty;?></th>
            <th colspan="2"></th>
            <th colspan="1"></th>
            <th colspan="1"></th>
            <th colspan="2"><?=number_format($total_discount);?>원</th>
            <th colspan="3"></th>
          </tr>
          <?php } ?>
          <tr style="border-top: 1px solid #dddddd;">
            <td colspan="13" style="text-align: left; padding: 5px 10px;">
              <?php echo "배송주소 : {$od_warehouse} / {$od_warehouse_address} / {$od_warehouse_phone}" ?>
            </td>
          </tr>
          </tbody>
        </table>

        <div class="frmsamhwaorderform_bottom">
          <div class="change_status">
            <span>선택한 상품 상태값</span>
            <select name="step" id="step">
              <option value="발주대기">발주대기</option>
              <option value="발주완료">발주완료</option>
              <option value="출고완료">출고완료</option>
              <option value="입고완료">입고완료</option>
              <option value="마감완료">마감완료</option>
              <option value="발주취소">발주취소</option>
            </select>
            <input type="button" value="변경하기" class="btn shbtn" id="change_cart_status">
          </div>
          <div style="float:right">
            <a href="javascript:void(0);" id="order_mod" onclick="popModOrder()" class="btn btn_02">발주서 수정</a>
            <!-- <input type="button" value="배송정보 저장" class="btn shbtn" id="change_warehouse"> -->
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>발주서 메모(비고)</h2>
      <div class="right"></div>
    </div>
    <div class="memo">
      <div class="block-box memo">
        <?php
          $sql = "SELECT * FROM purchase_cart_memo WHERE od_id = '{$od['od_id']}' and mb_id is not null ORDER BY ctm_no DESC";
          $result = sql_query($sql);
          $row = sql_fetch_array($result); mysqli_data_seek($result,0);
        ?>

        <div class="om_write_box">
          <textarea name="od_shop_memo" rows="8" placeholder="입력한 메모내용이 보여집니다."
                    id="memo_cart_content"><?php echo htmlspecialchars($row['ctm_memo']); ?></textarea>
          <input type="button" value="저장" class="btn" id="memo_cart_submit">
        </div>
        <ul class="memo_logs">
          <?php
            $memo_counts = 0;
            while ($row = sql_fetch_array($result)) {
              $om_mb = get_member($row['mb_id']);
              $memo_counts++;
              if( !$row['ctm_memo'] ) continue;
          ?>
          <li>
            <div class="om_info" style="display: block; float: left; width: 23%; padding-right: 5px;"> <span class="log_datetime"><?php echo $row['ctm_date']; ?></span>(<?php echo $om_mb['mb_name']; ?> 매니저) </div>
            <div class="om_content" style="display: block; float: left; width: 77%; padding-bottom: 5px;"> <span style="display: block;"><?php echo nl2br(htmlspecialchars($row['ctm_memo'])); ?></span> </div>
          </li>
          <?php }
            if(!$memo_counts) { ?>
          <li>기록이 없습니다.</li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>관리자메모</h2>
      <div class="right">
      </div>
    </div>
    <div class="memo">
      <div class="block-box memo">
        <?php
          $sql = "SELECT * FROM purchase_order_admin_memo WHERE od_id = '{$od['od_id']}' ORDER BY om_no DESC";
          $result = sql_query($sql);
          $row = sql_fetch_array($result);
          mysqli_data_seek($result,0);
        ?>
        <div class="om_write_box">
          <textarea name="od_shop_memo" rows="8" placeholder="입력한 메모내용이 보여집니다." id="memo_content"><?php // echo htmlspecialchars($result['om_content']); htmlspecialchars=>오류 ?></textarea>
          <input type="button" value="저장" class="btn" id="memo_admin_submit">
        </div>
        <ul class="memo_logs">
          <?php
          $memo_counts = 0;
          while ($row = sql_fetch_array($result)) {
            $om_mb = get_member($row['mb_id']);
            $memo_counts++;
            ?>
            <li>
              <div class="om_info" style="display: block; float: left; width: 23%; padding-right: 5px;">
                  <span class="log_datetime"><?php echo $row['om_datetime']; ?></span>(<?php echo $om_mb['mb_name']; ?> 매니저)
              </div>
              <div class="om_content" style="display: block; float: left; width: 77%; padding-bottom: 5px;">
                  <?php echo nl2br(htmlspecialchars($row['om_content'])); ?>
              </div>
          </li>
          <?php } if (!$memo_counts) { ?>
            <li> 기록이 없습니다. </li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>기록</h2>
      <div class="right">
      </div>
    </div>
    <div class="block-box gray logs">
      <?php
      $logs = get_purchase_order_admin_log($od['od_id']);
      foreach ($logs as $log) {
        $log_mb = get_member($log['mb_id']);
        echo '<span class="log_datetime">' . $log['ol_datetime'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['ol_content'] . '<br/>';
      }
      if (!count($logs)) {
        echo '기록이 없습니다.';
      }
      ?>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>입고기록</h2>
      <div class="right">
      </div>
    </div>
    <div class="block-box gray logs">
      <?php
      $logs = get_purchase_order_admin_log($od['od_id'], 'not_null');
      foreach ($logs as $log) {
        $log_mb = get_member($log['mb_id']);
        echo '<span class="log_datetime">' . $log['ol_datetime'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['ol_content'] . '<br/>';
      }
      if (!count($logs)) {
        echo '기록이 없습니다.';
      }
      ?>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>발주서 발송 기록</h2>
      <div class="right">
      </div>
    </div>
    <div class="block-box gray logs">
    <?php
      $sql = "SELECT * FROM purchase_order_admin_log WHERE od_id = '{$od['od_id']}' AND ol_type IN ('1','2','3') ORDER BY ol_no DESC";
      $result = sql_query($sql);
      while ($row = sql_fetch_array($result)) {
        $log_mb = get_member($log['mb_id']);
        echo '<span class="log_datetime">' . $row['ol_datetime'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) ' . $row['ol_content'] . '<br/>';
      }
      if($result->num_rows<1) { echo '기록이 없습니다.'; }
      ?>
    </div>
  </div>

  <div id="order_summarize">
  <!--
    <div class="header">
      <button class="shbtn order_prints">작업지시서 출력</button>
    </div>
  -->
    <div class="content">
      <div class="block">
        <h2>발주번호 <?php echo $od['od_id']; ?></h2>
        <span class="so_nb"> SO-NB <?php echo $od['so_nb']; ?></span>
      </div>
      <div class="block">
        <?php if ($mb['mb_id']) { ?>
          <a href="<?php echo G5_ADMIN_URL; ?>/member_form.php?&w=u&mb_id=<?php echo $mb['mb_id']; ?>" target="_blank"
             class="h2">
            <?php echo $mb['mb_name']; ?><span>(<?php echo $mb['mb_temp'] ? '임시회원' : $mb['mb_id']; ?>)</span>
          </a>
        <?php } else { ?>
          <a href="#" class="h2">비회원</a>
        <?php } ?>
        <?php echo $od['od_send_admin_memo'] ?>
        <p>
          <?=$od['od_name']; ?> (<?=$od['od_email']; ?>)<br/>
          HP : <?=$od['od_hp']; ?>  /  Tel : <?=$od['od_tel']; ?><br/>
          Fax : <?=$od['od_fax']; ?>
        </p>

        고객(거래처)코드: <?php if(get_member($od['mb_id'])['mb_thezone']) echo get_member($od['mb_id'])['mb_thezone']; else echo str_replace('-','',get_member($od['mb_id'])['mb_giup_bnum']);?>
        <br/><br/>
        <a class="shbtn send_estimate">
          구매발주서 전송
        </a>
      </div>
      <div class="block">
        <h2>발주담당자</h2>
        <ul>
          <li>
            <div class="managers">
              <select name="od_purchase_manager">
                <option value="">없음</option>
                <?php
                $od_purchase_manager = $od['od_purchase_manager'];
                if (!$od_purchase_manager || $od_purchase_manager == '1202') {
                  $sql_manager = "SELECT `mb_manager` FROM `g5_member` WHERE `mb_id` ='" . $od['mb_id'] . "'";
                  $result_manager = sql_fetch($sql_manager);
                  $od_purchase_manager = $result_manager['mb_manager'];
                }

                $sql = ("	
                  SELECT 	
                    mb.mb_name, mb.mb_id 	
                  FROM 	
                    g5_auth au, g5_member mb 	
                  WHERE 	
                    mb.mb_id = au.mb_id 	
                    AND au_menu = '400480' 	
                    AND au_auth 	
                  LIKE '%w%' 	
                  ORDER BY mb_name ASC	
                ");
                $auth_result = sql_query($sql);
                while ($a_row = sql_fetch_array($auth_result)) {
                  $a_mb = get_member($a_row['mb_id']);
                  ?>
                  <option
                      value="<?php echo $a_mb['mb_id']; ?>" <?php echo $a_mb['mb_id'] == $od_purchase_manager ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?></option>
                <?php } ?>
              </select>
              <a class="change_manager_on change_manager_submit" data-type="od_purchase_manager">변경</a>
            </div>
          </li>
        </ul>
      </div>
      <div class="block">
        <h2>구매정보</h2>
        <ul class="bill_info">
          <li>
            <div class="left"><b>총금액</b></div>
            <div class="right">
              <b><?php echo number_format($tot_total + $od['od_send_cost'] + $od['od_send_cost2'] + $od['od_cart_discount2'] - $od['od_sales_discount'] - $amount['coupon'] - $od['od_receipt_point'] - $total_discount); ?>
                원</b></div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<div class="btn_fixed_top">
  <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/purchase_orderlist.php" class="btn btn_02">목록</a>
  <a href="#" class="btn btn_01 order_prints">작업지시서 출력</a>
  <input type="button" value="발주내역 엑셀다운로드" onclick="orderListExcelDownload()" class="btn btn_02">
</div>

<div id="popup_box">
  <div></div>
</div>

<style>
  #popup_order_mod {
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    z-index: 999;
    background-color: rgba(0, 0, 0, 0.6);
    display: none;
  }

  #popup_order_mod > div {
    width: 1000px;
    max-width: 80%;
    height: 80%;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
  }

  #popup_order_mod > div iframe {
    width: 100%;
    height: 100%;
    border: 0;
    background-color: #FFF;
  }

  #memo_cart_submit, #memo_admin_submit {
    position: absolute;
    border: 1px solid #cccccc;
    top: 116px;
    right: 14px;
    font-size: 13px;hide_control
    cursor: pointer;
    padding: 8px 20px;
    height: auto;
    background-color: white;
    color: #656565;
  }
</style>

<div id="popup_order_mod"><div>_</div></div>

<script>
  var change_member_pop, add_item_pop, matching_item_pop, edit_item_pop, delivery_print_pop, edit_payment_pop,
    send_estimate_pop, order_prints_pop, release_purchaseorderview_pop;

  function orderListExcelDownload() {
    $("#excelForm").remove();
    var html = "<form id='excelForm' method='post' action='./purchase_order.excel.list.php'>";
    html += "<input type='hidden' name='ref' value='orderform'>";

    var od_id = [];

    od_id.push("<?=$od["od_id"]?>");
    html += "<input type='hidden' name='od_id[]' value='<?=$od["od_id"]?>'>";

    html += "</form>";

    if (!od_id.length) {
      alert("선택된 주문내역이 존재하지 않습니다.");
      return false;
    }

    $("body").append(html);
    $("#excelForm").submit();
  }

  $(document).ready(function () {
    $(document).on("click", ".deliveryCntBtn", function (e) {
      e.preventDefault();

      var popupWidth = 1200;
      var popupHeight = 700;

      var popupX = (window.screen.width / 2) - (popupWidth / 2);
      var popupY = (window.screen.height / 2) - (popupHeight / 2);

      window.open("./popup.prodDeliveryInfo.form.php?od_id=<?=$od["od_id"]?>", "배송정보", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX);
    });

    $(document).on("click", ".purchaseOrderViewBtn", function (e) {
      e.preventDefault();

      var popupWidth = 650;
      var popupHeight = 850;

      var popupX = (window.screen.width / 2) - (popupWidth / 2);
      var popupY = (window.screen.height / 2) - (popupHeight / 2);

      var ct_id = $(this).data('ct-id');

      release_purchaseorderview_pop = window.open("./popup.release_purchaseorder_view.php?isPop=true&od_id=<?=$od["od_id"]?>&ct_id=" + ct_id, "배송정보", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX);
    });

    $(".barNumCustomSubmitBtn").click(function () {
      var val = $(this).closest("li").find("input").val();
      var target = $(this).closest("ul").find("li");
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
                for (var iii = 0; iii < (secData0Len - 1); iii++) {
                  barData = "0" + barData;
                }
              }

              barList.push(first + barData);
            }
          }
        }

        for (var i = 0; i < target.length; i++) {
          if (i > 0) {
            $(target[i]).find("input").val(barList[i - 1]);
          }
        }
      }
    });

    $(".barNumGuideBox .closeBtn").click(function () {
      $(this).closest(".barNumGuideBox").hide();
    });

    $(".barNumGuideOpenBtn").click(function () {
      $(this).next().toggle();
    });

    var stoldList = [];

    var offset = $('#order_summarize').offset();

    function fixed_container() {
      if ($(document).scrollTop() > offset.top) {
        $('#order_summarize').addClass('fixed');
      } else {
        $('#order_summarize').removeClass('fixed');
      }
    }

    $(window).scroll(function () {
      fixed_container();
    });
    fixed_container();

    // 담당자 변경
    $('.change_manager_off').click(function () {
      var off = $(this).closest('.off');
      var on = $(this).closest('.managers').find('.on');

      $(off).hide();
      $(on).show();
    });

    $('.change_manager_cancel').click(function () {
      var on = $(this).closest('.on');
      var off = $(this).closest('.managers').find('.off');

      $(on).hide();
      $(off).show();
    });

    $('.change_manager_submit').click(function () {
      var type = $(this).data('type');
      var mb_id = $('select[name="' + type + '"]').val();
      $.ajax({
        method: "POST",
        url: "./ajax.purchase_order.manager.php",
        data: {
          type: type,
          mb_id: mb_id,
          od_id: od_id,
        },
      })
      .done(function (data) {
        // console.log(data);
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
    });

    $('#memo_admin_submit').click(function () {
      var content = $('#memo_content').val();

      if (!content.length) {
        alert('메모 내용을 입력하세요.');
        return;
      }
      $.ajax({
        method: "POST",
        url: "./ajax.purchase_order.memo.php",
        data: {
          od_id: od_id,
          content: content,
          mod:'admin',
        },
      })
        .done(function (data) {
          if (data.msg) {
            alert(data.msg);
          }
          if (data.result === 'success') {
            location.reload();
          }
        })
    });

    $('#memo_cart_submit').click(function () {
        var content = $('#memo_cart_content').val();

        if (!content.length) {
            alert('메모 내용을 입력하세요.');
            return;
        }

        $.ajax({
            method: "POST",
            url: "./ajax.purchase_order.memo.php",
            data: {
                od_id: od_id,
                content: content,
                mod: 'cart'
            }
        })
        .done(function (data) {
            if (data.msg) { alert(data.msg); }
            if (data.result === 'success') { location.reload(); }
        })
    });

    // 전체 옵션선택
    $("#sit_select_all").click(function () {
      if ($(this).is(":checked")) {
        $("input[name='it_sel[]']").attr("checked", true);
        $("input[name^=ct_chk]").attr("checked", true);
      } else {
        $("input[name='it_sel[]']").attr("checked", false);
        $("input[name^=ct_chk]").attr("checked", false);
      }
    });

    // 상품의 옵션선택
    $("input[name='it_sel[]']").click(function () {
      var cls = $(this).attr("id").replace("sit_", "sct_");
      var $chk = $("input[name^=ct_chk]." + cls);
      if ($(this).is(":checked"))
        $chk.attr("checked", true);
      else
        $chk.attr("checked", false);
    });

    $('#change_cart_status').click(function () {
      var step = document.getElementById('step');
      var it_sel = document.getElementsByName("it_sel[]");
      var formdata = $.extend(
        {},
        $('#frmsamhwaorderform').serializeObject(),
        {
          od_id: od_id,
        }
      );
      if (formdata['it_sel[]'] === undefined) {
        alert('상품을 체크해주세요.');
        return;
      }
      var ct_id = [];
      for (var k = 0; k < it_sel.length; k++) {
        if (it_sel[k].checked == true) {
          ct_id.push(it_sel[k].value);
        }
      }
      var sendData = {};
      sendData['ct_id'] = ct_id;
      sendData['step'] = step.value;
      $.ajax({
        type: "post",
        url: "./ajax.purchase_cart_status.php",
        data: sendData,
        success: function (data) {
          if (data === 'success') {
            alert('변경되었습니다.');
            location.reload();
          } else {
            alert(data);
          }
        }
      });
    });

    $('#change_warehouse').click(function() {
      var ct_ids = [];
      var wh_ids = [];
      var checked_it = $('input[name="it_sel[]"]:checked');

      if (checked_it.length === 0) {
        alert('상품을 체크해주세요.');
        return;
      }

      if (!confirm('배송지를 수정하시겠습니까?')) {
        return;
      }

      checked_it.each(function(i, v) {
        ct_ids.push($(this).val());
        wh_ids.push($(this).closest('tr').next().find('select[name="warehouse"]').val());
      });

      // console.log(ct_ids);
      // console.log(wh_ids);

      $.ajax({
        method: "POST",
        url: "./ajax.purchase_cart_warehouse.php",
        data: {
          od_id: od_id,
          ct_ids: ct_ids,
          wh_ids: wh_ids,
        },
      })
      .done(function (result) {
        alert('완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });

      return;
    });

    // 배송 선택
    function selected_delivery_type() {
      var checked = $('#od_delivery_type').find(':selected').data('type');

      $('.delivery_block .delivery_types').hide();
      $('.delivery_block .delivery_types.' + checked).show();
      selected_delivery_company();
    }

    $('#od_delivery_type').change(function () {
      selected_delivery_type();
      clear_form('.delivery_block');
    });
    selected_delivery_type();

    // 택배 배송 회사 선택
    function selected_delivery_company() {
      var checked = $('select[name="od_delivery_company[delivery]"]').find(':selected').val();
      var checked2 = $('#od_delivery_type').find(':selected').data('type');

      if (checked === 'ilogen' && checked2 === 'delivery') {
        $('.delivery_edi_div').show();
        // $('input[name="od_delivery_text[delivery]"]').attr("readonly",true);
      } else {
        $('.delivery_edi_div').hide();
        // $('input[name="od_delivery_text[delivery]"]').attr("readonly",false);
      }
    }

    $('select[name="od_delivery_company[delivery]"]').change(function () {
      selected_delivery_company();
    });
    selected_delivery_company();

    // 출고예정일
    $("#od_ex_date").datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: "yy-mm-dd",
      showButtonPanel: true,
      yearRange: "c-99:c+99",
      maxDate: "+365d"
    });

    // 주문자 회원 변경
    $('#od_change_member').click(function () {
      change_member_pop = window.open('./pop.order.change_member.php?od_id=' + od_id, "change_member_pop", "width=430, height=600, resizable = no, scrollbars = no");
    });

    // 견적서 전송
    $('.send_estimate').click(function () {
      send_estimate_pop = window.open('<?php echo G5_SHOP_URL; ?>/pop.purchase_estimate.php?od_id=' + od_id, "send_estimate", "width=800, height=800, resizable = no, scrollbars = no");
    });

    // EDI 전송
    $('.delivery_edi').click(function () {
      var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
      var formdata = $.extend(
        {},
        $('#frmsamhwaorderdeliveryform').serializeObject(),
        {
          od_id: od_id,
          od_delivery_type_data: od_delivery_type_data,
        }
      );

      $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.edi.php",
        data: formdata,
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
    });

    // 송장 리턴
    $('.delivery_edi_return').click(function () {

      $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.edi.return.php",
        data: {
          od_id: od_id
        },
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
    });

    //사방넷 배송정보 전송
    $('.delivery_sabangnet_return').click(function () {

      $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.sabangnet.return.php",
        data: {
          od_id: od_id
        },
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          alert('전송이 완료되었습니다.');
          location.reload();
        }
      })
    });

    // 배송정보 프린트
    $('.delivery_print').click(function () {
      var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
      var formdata = $.extend(
        {},
        $('#frmsamhwaorderdeliveryform').serializeObject(),
        {
          od_id: od_id,
          od_delivery_type_data: od_delivery_type_data,
          'type': 'print',
        }
      );

      $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.php",
        data: formdata,
      })
      .done(function (data) {
        if (data.msg && data.result !== 'success') {
          alert(data.msg);
        }
        if (data.result === 'success') {
          // location.reload();
          delivery_print_pop = window.open('./pop.order.delivery.print.php?od_id=' + od_id, "delivery_print_pop", "width=855, height=900, resizable = yes, scrollbars = yes");
        }
      })
    });

    // 작업 지시서
    $('.order_prints').click(function (e) {
      var it_id = "";
      var checkbox = $("input[name='it_sel[]']:checked");
      for (var i = 0; i < checkbox.length; i++) {
        it_id += (it_id) ? "," : "";
        it_id += it_id;
      }

      order_prints_pop = window.open('./pop.purchase_order.prints.php?od_id=' + od_id + '|', "order_prints_pop", "width=850, height=800, resizable = no, scrollbars = yes");
    });


    // 주문취소 파일첨부
    $(document).on("click", '#g5_shop_order_cancel_file .uploadbtn', function () {

      var $form = $('<form class="hidden_form"></form>');
      $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
      $form.attr('method', 'post');
      //$form.attr('target', 'iFrm');
      $form.appendTo('body');

      var str = $('<input type="file" name="file" class="g5_shop_order_file_cancel_apply">');
      $form.append(str);
      $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
      $form.append('<input type="hidden" name="type" value="cancel_apply" />');

      $($form).find('input[type="file"]').click();
    });

    $(document).on("change", '.g5_shop_order_file_cancel_apply', function () {

      var form = $(this).closest('form')[0];

      var form_data = new FormData(form);

      $.ajax({
        type: 'POST',
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        url: "./ajax.order.item.add.cart_file_upload.php",
        data: form_data,
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          var ret = '';

          for (var i = 0; i < data.data.length; i++) {
            ret += '<li>';
            ret += '<a href="/data/order_cart/' + data.data[i]['file_name'] + '" class="filelink" target="_blank">' + data.data[i]['real_name'] + '</a>&nbsp;';
            ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
            ret += '</li>';
          }

          $('.upload_files_cancel_apply').html(ret);
        }
      })
    });

    $(document).on("click", '.upload_files_cancel_apply .remove', function () {
      var no = $(this).data('no');
      var obj = $(this);

      var formdata = {
        no: no,
      }
      $.ajax({
        method: "POST",
        url: "./ajax.order.item.add.cart_file_remove.php",
        data: formdata,
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          $(obj).closest('li').remove();
        }
      });
    });

    // 주문취소 신청 버튼
    $('#cancel_submit').click(function () {
      var od_cancel_reason = $('select[name="od_cancel_reason"]').val();
      var od_cancel_memo = $('#cancel_memo_content').val();

      $.ajax({
        method: "POST",
        url: "./ajax.order.cancel.apply.php",
        data: {
          od_id: od_id,
          od_cancel_reason: od_cancel_reason,
          od_cancel_memo: od_cancel_memo,
        },
      })
      .done(function (data) {
        // console.log(data);
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          //location.reload();
          location.href = './samhwa_orderform.php?od_id=' + od_id + '&sub_menu=400403';
        }
      })
    });

    // 매출증빙
    $('#typereceipt2').click(function () {
      if ($(this).is(':checked')) {
        $('#typereceipt2_view').show();
        $('#typereceipt1_view').hide();
      }
    });
    $('#typereceipt1').click(function () {
      if ($(this).is(':checked')) {
        $('#typereceipt1_view').show();
        $('#typereceipt2_view').hide();
      }
    });
    $('#typereceipt0').click(function () {
      if ($(this).is(':checked')) {
        $('#typereceipt1_view').hide();
        $('#typereceipt2_view').hide();
      }
    });

    $('.typereceipt_cuse').click(function () {
      var val = $(this).val();

      if (val == 1) {
        $('.personallay').show();
        $('.businesslay').hide();
      } else {
        $('.personallay').hide();
        $('.businesslay').show();
      }
    });

    $('.typereceipt_before_btn').click(function () {
      $('.typereceipt_before').hide();
      $('.typereceipt_after').show();

      var v = $("input[name='ot_typereceipt']:checked");
      $("input[name='ot_typereceipt']:checked").click();

      // console.log(v.val());

      if (v.val() === 31) {
        $("input[name='ot_typereceipt_cuse']:checked").click();
      }
    });

    $('.typereceipt_after_btn').click(function () {
      $('.typereceipt_before').show();
      $('.typereceipt_after').hide();
    });

    // 출고예정일
    $("#ot_time_date").datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: "yy-mm-dd",
      showButtonPanel: true,
      yearRange: "c-99:c+99",
      maxDate: "+365d"
    });

    $('.typereceipt_after_submit').click(function () {
      submit_typereceipt_after();
    });

    $('#od_b_tel, #od_b_hp, #ot_btel, input[name="p_typereceipt_btel"]').on('keyup', function () {
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num);
    });
    $('input[name="p_typereceipt_bnum"], #ot_bnum').on('keyup', function () {
      var num = $(this).val();
      num.trim();
      this.value = auto_saup_hypen(num);
    });


    $('.pay-state').click(function () {
      $.ajax({
        method: "POST",
        url: "./ajax.order.paystate.toggle.php",
        data: {
          od_id: od_id,
        },
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
    });

    // 추가배송비 적용
    $('#change_send_cost2').click(function () {
      var od_send_cost2 = parseInt($('#od_send_cost2').val());

      //2020-09-07 (-) 적용

      $.ajax({
        method: "POST",
        url: "./ajax.order.change_sendcost2.php",
        data: {
          od_id: od_id,
          od_send_cost2: od_send_cost2,
        },
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
    });

    $('#change_send_cost_sales_discount').click(function () {
      var od_send_cost = parseInt($('.send_cost_sales_discount_wrapper .od_send_cost').val());
      var od_send_cost2 = parseInt($('.send_cost_sales_discount_wrapper .od_send_cost2').val());
      var od_sales_discount = parseInt($('.send_cost_sales_discount_wrapper .od_sales_discount').val());

      $.ajax({
        method: "POST",
        url: "./ajax.order.change.sendcost.and.saels.discount.php",
        data: {
          od_id: od_id,
          od_send_cost: od_send_cost,
          od_send_cost2: od_send_cost2,
          od_sales_discount: od_sales_discount,
        },
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
    });


    // 외부발주 파일첨부
    $(document).on("click", '.it_outsourcing_option_file .uploadbtn', function () {

      var it_id = $(this).closest('td').data('id');
      var uid = $(this).closest('td').data('uid');

      var $form = $('<form class="hidden_form"></form>');
      $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
      $form.attr('method', 'post');
      //$form.attr('target', 'iFrm');
      $form.appendTo('body');

      var str = $('<input type="file" name="file" class="it_outsourcing_option_file_apply">');
      $form.append(str);
      $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
      $form.append('<input type="hidden" name="it_id" value="' + it_id + '" />');
      $form.append('<input type="hidden" name="uid" value="' + uid + '" />');
      $form.append('<input type="hidden" name="type" value="order_outsourcing" />');

      $($form).find('input[type="file"]').click();
    });

    $(document).on("change", '.it_outsourcing_option_file_apply', function () {

      var form = $(this).closest('form')[0];

      var form_data = new FormData(form);

      var it_id = $(form).find('input[name="it_id"]').val();
      var uid = $(form).find('input[name="uid"]').val();

      $.ajax({
        type: 'POST',
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        url: "./ajax.order.item.add.cart_file_upload.php",
        data: form_data,
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          var ret = '';

          for (var i = 0; i < data.data.length; i++) {
            ret += '<li>';
            ret += '<a href="/data/order_cart/' + data.data[i]['file_name'] + '" class="filelink" target="_blank">' + data.data[i]['real_name'] + '</a>&nbsp;';
            ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
            ret += '</li>';
          }

          // $('.upload_files_outsourcing_option_apply_' + it_id).html(ret);
          $('.upload_files_outsourcing_option_apply_' + uid).html(ret);
        }
      })
    });

    $(document).on("click", '.upload_files_outsourcing_option_apply .remove', function () {

      var no = $(this).data('no');
      var obj = $(this);

      var formdata = {
        no: no,
      }
      $.ajax({
        method: "POST",
        url: "./ajax.order.item.add.cart_file_remove.php",
        data: formdata,
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          $(obj).closest('li').remove();
        }
      });
    });

    // 배송정보 기본정보 반영
    $('#reset_od_info').click(function () {

      $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.reset.php",
        data: {
          od_id: od_id
        },
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
    });

    $('#customer_code_sel').change(function () {
      var customer_code = $("option:selected", this).val();

      if (!customer_code) {
        return false;
      }

      $.ajax({
        method: "POST",
        url: "./ajax.order.change.customer.code.php",
        data: {
          od_id: od_id,
          customer_code: customer_code,
        },
      })
      .done(function (data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      });
    });

    // 배송지 목록
    $('#address_list').click(function () {
      var url = "<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderaddress.php?mb_id=<?=$od['mb_id']?>";
      window.open(url, "win_address", "left=100,top=100,width=800,height=600,scrollbars=1");
      return false;
    });

    // 설치결과보고서 작성 버튼
    $("#popup_box").hide();
    $("#popup_box").css("opacity", 1);

    // 미매칭
    $('.install_report_match').click(function (e) {
      var before_ct_id = $(this).data('ct-id');
      var after_ct_id = $(this).closest('li').find('select').val();

      $.ajax({
        method: "POST",
        url: "./ajax.report.match.php",
        data: {
          before_ct_id: before_ct_id,
          after_ct_id: after_ct_id,
        },
      })
      .done(function (data) {
        alert('매칭되었습니다.');
        location.reload();
      })
      .fail(function () {
        alert('반영에 실패하였습니다.');
      })
    });

    // 환불
    $(document).on("click", "#refund_price_all", function (e) {
      if ($(this).is(":checked")) {
        $('input[name="refund_price"]').val(addComma($(this).val()));
        return;
      }
      $('input[name="refund_price"]').val(0);
    });

    $(document).on("input propertychange paste", "input[name='refund_price']", function (e) {
      var input = $(this).val().replace(/[\D\s\._\-]+/g, "");

      if (input !== '') {
        input = input ? parseInt(input, 10) : 0;
        $(this).val(input.toLocaleString());
      } else {
        $(this).val('');
      }
    });

    $(document).on("click", "#refund_submit", function (e) {
      if (!$('textarea[name="refund_memo"]').val()) {
        alert('메모를 입력하세요.');
        return;
      }
      if (!$('select[name="refund_status"]').val()) {
        alert('환불상태를 선택하세요.');
        return;
      }
      var refund_price = $('input[name="refund_price"]').val().replace(/[\D\s\._\-]+/g, "");
      refund_price = refund_price ? parseInt(refund_price, 10) : 0;
      if (refund_price < 0) {
        alert('환불금액은 0이상 되어야합니다.');
        return;
      }
      var ajax = $.ajax({
        method: "POST",
        url: './ajax.refund.php',
        data: {
          od_id: od_id,
          refund_memo: $('textarea[name="refund_memo"]').val(),
          refund_status: $('select[name="refund_status"]').val(),
          refund_price: $('input[name="refund_price"]').val().replace(/[\D\s\._\-]+/g, ""),
        },
      })
        .done(function (data) {
          alert('환불내용이 변경되었습니다.');
          window.location.reload();
        });
    })

    $(document).on("change keyup paste", "select[name='refund_status']", function (e) {
      reset_refund_price();
    });

    reset_refund_price();
  });

  function reset_refund_price() {
    var select_val = $("select[name='refund_status']").val();
    if (select_val === '회수완료 및 검수' || select_val === '검수완료') {
      $('input[name="refund_price"]').attr("disabled", false);
      $('#refund_price_all').attr("disabled", false);
      return;
    }
    $('input[name="refund_price"]').val("0");
    $('input[name="refund_price"]').attr("disabled", true);
    $('#refund_price_all').attr("disabled", true);
    $('#refund_price_all').prop("checked", false)
  }

  function addComma(num) {
    var regexp = /\B(?=(\d{3})+(?!\d))/g;
    return num.toString().replace(regexp, ',');
  }

  function showKcpWindow() {
    window.open("https://admin8.kcp.co.kr/assist/bill.BillAction.do?cmd=card_bill&C_TRADE_NO=43A1DA77F005F7EF5F49B1E1D4AFE3FC", "kcpwindow", "width=400,height=600")
  }

  function submit_typereceipt_after(msgFlag) {
    var formdata = $.extend(
      {},
      $('#typereceipt_after').serializeObject(),
      {
        od_id: od_id,
      }
    );

    $.ajax({
      method: "POST",
      url: "./ajax.order.typereceipt.php",
      data: formdata,
    })
    .done(function (data) {
      if (data.msg) {
        if (msgFlag != false) {
          alert(data.msg);
        }
      }
      if (data.result === 'success') {
        location.reload();
      }
    })
  }

  $(".ct_manager").change(function () {

    if (confirm('출고담당자를 변경하시겠습니까?')) {

      var ct_manager = $(this).val();
      var ct_id = $(this).data('ct-id');
      var sendData = {};
      sendData['ct_manager'] = ct_manager;
      sendData['ct_id'] = ct_id;

      $.ajax({
        method: "POST",
        url: "./ajax.ct_manager.php",
        data: sendData
      })
      .done(function (data) {
        if (data.result == "success") {
          alert('출고 담당자가 지정되었습니다.');
          window.location.reload();
        } else {
          alert('실패하였습니다.');
        }
      });
    } else {
      window.location.reload();
    }
  });
</script>

<script>
  $(function () {
    $('.report-img-wrap').magnificPopup({
      delegate: 'a',
      type: 'image',
      image: {
        titleSrc: function (item) {

          var $div = $('<div>');

          // 원본크기
          var $btn_zoom_orig = $('<button type="button" class="btn-bottom btn-zoom-orig">원본크기</button>')
            .click(function () {
              $btn_zoom_orig.hide();
              $btn_zoom_fit.show();

              $(item.img).css('max-width', 'unset');
              $(item.img).css('max-height', 'unset');
            });

          // 창맞추기
          var $btn_zoom_fit = $('<button type="button" class="btn-bottom btn-zoom-fit">창맞추기</button>"')
            .hide()
            .click(function () {
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
            .click(function () {
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

  function popModOrder() {
    $("#popup_order_mod > div").html("<iframe src='./pop.purchase.order.mod.php?od_id=<?=$od_id;?>'></iframe>");
    $("#popup_order_mod iframe").load(function(){
      $("#popup_order_mod").show();
      $('#hd').css('z-index', 3);
      $('#popup_order_mod iframe').contents().find('.mb_id_flexdatalist').focus();
    });
  }
</script>

<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
?>
