<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = "주문 내역 수정";
include_once(G5_ADMIN_PATH.'/admin.head.php');

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' AND od_del_yn = 'N' ";
$od = sql_fetch($sql);
$prodList = [];
$prodListCnt = 0;
$deliveryTotalCnt = 0;
$delivery_insert=0;
if (!$od['od_id']) {
  alert("해당 주문번호로 주문서가 존재하지 않습니다.");
} else {
  if($od["ordId"]) {
    $sendData = [];
    $sendData["penOrdId"] = $od["ordId"];
    $sendData["uuid"] = $od["uuid"];

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

    if($result) {
      foreach($result as $data) {
        $thisProductData = [];
        $thisProductData["prodId"] = $data["prodId"];
        $thisProductData["prodColor"] = $data["prodColor"];
        $thisProductData["stoId"] = $data["stoId"];
        $thisProductData["prodBarNum"] = $data["prodBarNum"];
        $thisProductData["penStaSeq"] = $data["penStaSeq"];
        array_push($prodList, $thisProductData);
      }
    }
  } else {
    $sto_imsi="";
    $sql_ct = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
    $result_ct = sql_query($sql_ct);
    //배송정보

    while($row_ct = sql_fetch_array($result_ct)) {
      $sto_imsi .=$row_ct['stoId'];

      //배송정보
      if($row_ct['ct_combine_ct_id']||$row_ct['ct_delivery_num']){
        $delivery_insert++;
      }
    }
    $stoIdDataList = explode('|',$sto_imsi);
    $stoIdDataList=array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);
  }
}
$mb = get_member($od['mb_id']);
$od_status = get_step($od['od_status']);
$pay_status = get_pay_step($od['od_pay_state']);

$od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";

//수급자정보
$od_penId      = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penId'] : '';        // penId
$od_penNm      = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penNm'] : $od['od_name'];  // 수급자
$od_penTypeNm    = (isset($od['od_penId']) && $od['od_penId']) ? $od['penTypeNm'] : '';        //안전등급
$od_penExpiDtm    = (isset($od['od_penId']) && $od['od_penId']) ? $od['penExpiDtm'] : '';        //유효기간
$od_penAppEdDtm    = (isset($od['od_penId']) && $od['od_penId']) ? $od['penAppEdDtm'] : '';      //적용기간
$od_penConPnum    = (isset($od['od_penId']) && $od['od_penId']) ? $od['penConPnum'] : $od['od_tel'];  //전화번호
$od_penConNum    = (isset($od['od_penId']) && $od['od_penId']) ? $od['penConNum'] : $od['od_hp'];  //휴대전화
$od_penzip1      = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penzip1'] : $od['od_zip1'];//우편번호
$od_penzip2      = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penzip2'] : $od['od_zip2'];
$od_penzip      = (isset($od['od_penId']) && $od['od_penId']) ? $od_penzip1.$od_penzip2 : $od['od_zip1'].$od['od_zip2'];

$od_penAddr      = (isset($od['od_penId']) && $od['od_penId']) ? $od['od_penAddr'] : $od['od_addr1'].''.$od['od_addr2'].''.$od['od_addr3'];  //주소

$avail_request_return = false;

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
    a.ordLendStrDtm,
    a.ordLendEndDtm
  from
    {$g5['g5_shop_cart_table']} a
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

for($i=0; $row=sql_fetch_array($result); $i++) {

  $cate_counts[$row['ct_status']] += 1;

  // 상품의 옵션정보
  $sql = "
    select
      MT.*,
      b.prodSupYn,
      b.it_taxInfo,
      b.it_type3
    from
      {$g5['g5_shop_cart_table']} MT
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
  for($k=0; $opt=sql_fetch_array($res); $k++) {

    $opt_price = 0;

    if($opt['io_type'])
      $opt_price = $opt['io_price'];
    else
      $opt_price = $opt['ct_price'] + $opt['io_price'];

    $opt["opt_price"] = $opt_price;

    // 소계
    $opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
    if($opt["prodSupYn"] == "Y") {
      $opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
    }
    // 단가 역산
    $opt["opt_price"] = $opt['ct_price_stotal'] ? @round($opt['ct_price_stotal'] / ($opt["ct_qty"] - $opt["ct_stock_qty"])) : 0;

    // 공급가액
    $opt["basic_price"] = $opt['ct_price_stotal'];
    // 부가세
    $opt["tax_price"] = 0;
    if($opt['it_taxInfo'] != "영세" ) {
      // 공급가액
      $opt["basic_price"] = round($opt['ct_price_stotal'] / 1.1);
      // 부가세
      $opt["tax_price"] = round($opt['ct_price_stotal'] / 11);
    }

    $opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

    // 한개라도 출고완료 있으면 관리자가 반품신청 가능하도록 함.
    if ($opt['ct_status'] === '배송') {
        $avail_request_return = true;
    }

    $row['options'][] = $opt;
  }


  // 합계금액 계산
  $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
                  SUM(ct_qty) as qty,
                  SUM(ct_discount) as discount,
                  SUM(ct_send_cost) as sendcost
              from {$g5['g5_shop_cart_table']}
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

// 결제방법
$s_receipt_way = ($od['pt_case']) ? $od['pt_case'] : $od['od_settle_case'];

if($od['od_settle_case'] == '간편결제') {
  switch($od['od_pg']) {
    case 'lg':
      $s_receipt_way = 'PAYNOW';
      break;
    case 'inicis':
      $s_receipt_way = 'KPAY';
      break;
    case 'kcp':
      $s_receipt_way = 'PAYCO';
      break;
    default:
      $s_receipt_way = $row['od_settle_case'];
      break;
  }
}

$typereceipt = get_typereceipt_step($od_id);
$typereceipt_cate = get_typereceipt_cate($od_id);

$next_step = get_next_step($od['od_status']);
$prev_step = get_prev_step($od['od_status']);

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/magnific-popup.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.wheelzoom.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.magnific-popup.js"></script>', 0);
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php'); // datepicker js

// 파트너
$is_use_partner = (defined('USE_PARTNER') && USE_PARTNER) ? true : false;
$total_ct_delivery_cnt=0;
//상품 옵션 개수별 바코드 필드추가
sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}` ADD `ct_barcode` TEXT NOT NULL AFTER `ct_qty` ", false);


$sql_ct = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
$result_ct = sql_query($sql_ct);
$qty=0;
$insert_qty=0;
while($row_ct = sql_fetch_array($result_ct)) {
  if($row_ct['ct_status'] !== "취소" && $row_ct['ct_status'] !== "주문무효") {
    $qty += $row_ct['ct_qty'];
    if($row_ct['ct_barcode_insert'])
      $insert_qty += $row_ct['ct_barcode_insert']; 
  }
}

$prodBarNumCntBtnWord = $insert_qty."/".$qty;
$prodBarNumCntBtnWord = ($insert_qty >= $qty) ? "입력완료" : $prodBarNumCntBtnWord;
$prodBarNumCntBtnStatus = ($insert_qty >= $qty) ? " disable" : "";

$deliveryCntBtnWord = "배송정보 ({$delivery_insert}/{$od["od_delivery_total"]})";
$deliveryCntBtnWord = ($delivery_insert >= $od["od_delivery_total"]) ? "입력완료" : $deliveryCntBtnWord;
$deliveryCntBtnStatus = ($delivery_insert >= $od["od_delivery_total"]) ? " disable" : "";

# 설치결과보고서
$reports = [];
$report_result = sql_query("
    SELECT * FROM partner_install_report
    WHERE od_id = '$od_id'
");
while($report = sql_fetch_array($report_result)) {

    $report_mb = get_member($report['mb_id']);
    $report['member'] = $report_mb;

    $report['issue'] = [];
    if($report['ir_is_issue_1'])
      $report['issue'][] = '상품변경';
    if($report['ir_is_issue_2'])
      $report['issue'][] = '상품추가';
    if($report['ir_is_issue_3'])
      $report['issue'][] = '미설치';

    $photo_result = sql_query("
        SELECT * FROM partner_install_photo
        WHERE od_id = '$od_id' 
        AND mb_id = '{$report['mb_id']}'
        AND img_type = '설치사진'
        ORDER BY ip_id ASC
    ");

    $report['photo1'] = [];
    while($photo = sql_fetch_array($photo_result)) {
        $report['photo1'][] = $photo;
    }

    $photo_result = sql_query("
        SELECT * FROM partner_install_photo
        WHERE od_id = '$od_id' 
        AND mb_id = '{$report['mb_id']}'
        AND img_type = '실물바코드사진'
        ORDER BY ip_id ASC
    ");

    $report['photo2'] = [];
    while($photo = sql_fetch_array($photo_result)) {
        $report['photo2'][] = $photo;
    }

    $photo_result = sql_query("
        SELECT * FROM partner_install_photo
        WHERE od_id = '$od_id' 
        AND mb_id = '{$report['mb_id']}'
        AND img_type = '설치ㆍ회수ㆍ소독확인서'
        ORDER BY ip_id ASC
    ");

    $report['photo3'] = [];
    while($photo = sql_fetch_array($photo_result)) {
        $report['photo3'][] = $photo;
    }

    $photo_result = sql_query("
        SELECT * FROM partner_install_photo
        WHERE od_id = '$od_id' 
        AND mb_id = '{$report['mb_id']}'
        AND img_type = '추가사진'
        ORDER BY ip_id ASC
    ");

    $report['photo4'] = [];
    while($photo = sql_fetch_array($photo_result)) {
        $report['photo4'][] = $photo;
    }

    $reports[] = $report;
}
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

.barNumGuideBox>.title {
  width: 100%;
  font-weight: bold;
  margin-bottom: 15px;
  position: relative;
}

.barNumGuideBox>.title>button {
  float: right;
}

.barNumGuideBox>p {
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

#popup_box>div {
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

@media (max-width : 750px) {
  #popup_box iframe {
    width: 100%;
    height: 100%;
    left: 0;
    margin-left: 0;
  }
}

.report-img-wrap {
  border: 1px solid #ddd;
  display: flex;
  min-height: 112px;
}

.report-img-wrap.col {
  flex-direction: column;
}

.report-img-wrap-title {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  border: 1px solid #ddd;
  border-top: none;
  font-size: 16px;
  font-weight: bold;
  padding: 12px;
}

.report-issue {
  display: flex;
  align-items: center;
  font-size: 16px;
  font-weight: bold;
  padding: 12px;
  border-bottom: 1px solid #ddd;
}

.report-issue-select {
  display: flex;
  padding: 12px;
  border-bottom: 1px solid #ddd;
}

.report-issue-select:last-child {
  border-bottom: none;
  min-height: 112px;
}
</style>
<div id="samhwa_order_form">
  <div class="block">
    <div class="header">
      <h2>주문정보<span>(주문일시:<?php echo $od['od_time']; ?>)</span>
        <?php
                $del_button=false;
                if($od['od_stock_insert_yn']=="Y"){
                    echo "<span class='box_gray'>보유재고 등록</span>";
                }else{
                    if($od['od_penId']){
                        echo "<span class='box_green'>수급자주문</span>";
                    }else{
                        echo "<span class='box_orange'>상품주문</span>";
                        if($od_status['name']=="상품준비"||$od_status['name']=="출고준비"||$od_status['name']=="출고완료"){
                            $del_button=true;
                        }
                    }
                }
            ?>
      </h2>
      <div class="right">
        <?php if($mb['mb_id'] && !$options[$k]['ct_stock_qty']) { ?>
        <!--<input type="button" value="상품추가" class="btn shbtn" id="add_item">-->
        <?php } ?>
        <!--<input type="button" value="바코드 정보 저장" class="btn shbtn" id="prodBarNumSaveBtn">-->
        <!-- <?php 
                    $sql_cart ="select `ct_hide_control` from `g5_shop_cart` where `od_id` = '".$od['od_id']."'";
                    $result_ct = sql_fetch($sql_cart);
                ?>
                <?php if($result_ct['ct_hide_control'] == "1"){ ?>
                    <a href="#" class="orderHide" onclick="hide_control('<?=$od['od_id'] ?>', '2')">주문내역 출력</a>
                <?php }else{ ?>
                    <a href="#" class="orderHide disable" onclick="hide_control('<?=$od['od_id'] ?>', '1')">주문내역 숨김</a>
                <?php }?> -->
        <a href="#" class="prodBarNumCntBtn<?=$prodBarNumCntBtnStatus?>"><?=$prodBarNumCntBtnWord?></a>
      </div>
    </div>
    <div class="item_list">
      <form name="frmsamhwaorderform" method="post" id="frmsamhwaorderform">
        <table>
          <thead>
            <tr>
              <th class="chkbox">
                <input type="checkbox" id="sit_select_all">
              </th>
              <th class="chkbox">&nbsp;</th>
              <!--<th>분류</th>-->
              <th class="item_name">상품</th>
              <th class="item_qty">수량</th>
              <th class="item_delivery_qty">배송수량</th>
              <th class="item_barcode"></th>
              <th class="item_price">단가</th>
              <th class="item_basic_price">공급가액</th>
              <th class="item_tax_price">부가세</th>
              <!--<th class="item_discount">할인금액</th>-->
              <!-- <th class="item_sendcost">배송비</th> -->
              <th class="item_stotal">합계</th>
              <th class="item_status">상태</th>
              <th class="item_memo">요청사항</th>
              <th class="item_memo">출고/설치 예정일</th>
              <th class="item_memo">출고완료일</th>
              <th class="item_memo">출고담당자</th>
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

                        for($i=0; $i<count($carts); $i++) {

                        # 요청사항
                        $prodMemo = "";

                        # 대여기간
                        $ordLendDtm = "";

                        # 배송수량
                        $deliveryCnt = 0;
                        if($carts[$i]["prodSupYn"] == "Y" && $carts[$i]["od_delivery_yn"] == "Y"){
                        $deliveryCnt = $carts[$i]["ct_qty"] - $carts[$i]["ct_stock_qty"];
                        $deliveryTotalCnt += $deliveryCnt;
                        }

                        // 상품이미지
                        $image = "<img src='/data/item/{$carts[$i]["it_img1"]}' onerror='this.src=\"/shop/img/no_image.gif\";' style='width: 50px; height: 50px;'>";
                        $options = $carts[$i]['options'];

                        $chk_first = 0;

                        $tot_qty += $carts[$i]['sum']['qty'];
                        if(!in_array($carts[$i]['ct_status'], ['취소', '주문무효'])) {
                            $tot_price += $carts[$i]['sum']['price'] - $carts[$i]['sum']['discount'];
                            // $tot_discount += $carts[$i]['sum']['discount'];
                            $tot_sendcost += $carts[$i]['sum']['sendcost'];
                            $tot_total += ($carts[$i]["prodSupYn"] == "Y") ? $carts[$i]['sum']['price'] - $carts[$i]['sum']['discount'] : 0;
                        }

                        $prodBarNum = $prodOptNum = '';
                        $option_array = array();
                        $barcode_array = array();

                        for($k=0; $k<count($options); $k++) {
                            # 요청사항
                            $prodMemo = ($prodMemo) ? $prodMemo : $carts[$i]["prodMemo"];
                            # 대여기간
                            $ordLendDtm = ($ordLendDtm) ? $ordLendDtm : date("Y-m-d", strtotime($carts[$i]["ordLendStrDtm"]))." ~ ".date("Y-m-d", strtotime($carts[$i]["ordLendEndDtm"]));
                            // $cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' ");
                            $cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND odc_uid = '{$carts[$i]['ct_uid']}' ");
                            // 파일
                            $files = array();
                            if ( $k == 0 ) {
                                // $sql = "SELECT * FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND ctf_type = 'order' ";
                                $sql = "SELECT * FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$carts[$i]['ct_uid']}' AND ctf_type = 'order' ";
                                $file_result = sql_query($sql);
                                while($file_row = sql_fetch_array($file_result)) {
                                    $files[] = $file_row;
                                }
                            }
                            $option_array[] = $options[$k]['ct_option'];
                            $barcode_array[] = $options[$k]['ct_barcode'];
                        ?>
            <tr class="<?php echo $k==0 ? 'top-border' : ''; ?>">
              <td rowspan="1" class="chkcbox">
                <label for="sit_sel_<?php echo $i; ?>_<?php echo $k; ?>"
                  class="sound_only"><?php echo $carts[$i]['it_name']; ?> 옵션 전체선택</label>
                <input type="checkbox" id="sit_sel_<?php echo $i; ?>_<?php echo $k; ?>" name="it_sel[]"
                  value="<?=$options[$k]['ct_id']?>">
              </td>
              <td class="chkbox">
                <label for="ct_chk_<?php echo $chk_cnt; ?>"
                  class="sound_only"><?php echo get_text($options[$k]['ct_option']); ?></label>
                <!--
                                        <input type="checkbox" name="ct_chk[<?php echo $chk_cnt; ?>]" id="ct_chk_<?php echo $chk_cnt; ?>" value="<?php echo $chk_cnt; ?>" class="sct_sel_<?php echo $i; ?>">-->
                <input type="hidden" name="ct_id[<?php echo $chk_cnt; ?>]" value="<?php echo $options[$k]['ct_id']; ?>">

                <input type="checkbox" name="ct_chk[]" id="ct_chk_<?php echo $chk_cnt; ?>"
                  value="<?php echo $options[$k]['ct_id']; ?>" class="sct_sel_<?php echo $i; ?>"
                  style="visibility: hidden;">
              </td>
              <td class="item_name">
                <div class="item_name_box">
                  <div class="left" style="width: 100%; float: left;">
                    <?php if ( $options[$k]['io_type'] == 0 && $k == 0 ) { ?>
                    <a href="/shop/item.php?it_id=<?php echo $carts[$i]['it_id']; ?>" class="image" target="_blank"
                      style="float: left;"><?php echo $image; ?></a>
                    <div class="item_info" style="width: calc(100% - 80px); float: left; padding-left: 15px;">
                      <b>
                        <?php if($options[$k]['ct_status'] == "재고소진"){ echo "[재고소진]"; } ?>
                        <?php echo stripslashes($carts[$i]['it_name']); ?> <b
                          style="color: #<?=($carts[$i]["prodSupYn"] == "Y") ? "3366CC" : "DC3333"?>;">(<?=($carts[$i]["prodSupYn"] == "Y") ? "유통" : "비유통"?>)</b>
                        <?php if(substr($carts[$i]["ca_id"], 0, 2) == 20){ ?>
                        <b style="color: #FFA500;">(대여)</b>
                        <?php } ?>
                        <a href="./itemform.php?w=u&amp;it_id=<?php echo $carts[$i]['it_id']; ?>"
                          class="name">보기</a></b><br>
                      <span><?php echo $carts[$i]['it_model']; ?></span>
                      <?php if ( $carts[$i]['it_name'] != $options[$k]['ct_option']) { ?>
                      [옵션] <?php echo $options[$k]['ct_option']; ?>
                      <?php } ?>
                      <?php if($carts[$i]["ct_stock_qty"]){ ?>
                      <p style="color: #DC3333;">* <?=$options[$k]["ct_stock_qty"]?>개 재고소진</p>
                      <?php } ?>
                      <?php
                              if($od['od_writer']=="openmarket"){
                                if($carts[$i]['it_name']!=$carts[$i]['pt_old_name']){ ?>
                      <br>[매칭전]
                      <?php echo $carts[$i]['pt_old_name']."(".$carts[$i]['pt_old_opt'].")"; ?>
                      <?php }else{ ?>
                      <br>[매칭대기]
                      <?php }
                                      }
                            ?>
                    </div>

                    <?php if($od['od_tax_flag'] && $carts[$i]['ct_notax']) echo '<br/>[비과세상품]'; ?>
                    <?php }else{ ?>
                    <span style="margin-right:60px;"></span>
                    <b>
                      <?php if ( $carts[$i]['it_name'] != $options[$k]['ct_option']) { ?>[옵션]<?php } ?>
                      <?php if($options[$k]['ct_status'] == "재고소진"){ echo "재고소진"; } ?>
                      <?php echo $options[$k]['ct_option']; ?></b>
                    <?php
                                if($od['od_writer']=="openmarket"){
                              if($carts[$i]['it_name']!=$carts[$i]['pt_old_name']){ ?>
                    <br>[매칭전]
                    <?php echo $carts[$i]['pt_old_name']."(".$carts[$i]['pt_old_opt'].")"; ?>
                    <?php }
                            }
                          ?>
                    <?php } ?>
                    <?php if($od['od_writer']=="openmarket"){ ?>
                    <input type="button" value="상품매칭" class="btn shbtn"
                      id="matching_item_<?php echo $options[$k]['ct_id']; ?>"
                      data-it-id="<?php echo $carts[$i]['it_id']; ?>">
                    <?php } ?>
                    <script>
                    $(document).ready(function() {
                      // 상품 매칭
                      $('#matching_item_<?php echo $options[$k]['ct_id']; ?>').click(function() {
                        var it_id = $(this).data('it-id');
                        matching_item_pop = window.open('./pop.order.item.matching.php?od_id=' + od_id +
                          '&it_id=' + it_id + '&ct_id=<?php echo $options[$k]['ct_id']; ?>',
                          "matching_item_pop", "width=1080, height=900, resizable = no, scrollbars = no");
                      });
                    });
                    </script>
                  </div>
                  <div class="right">
                    <?php if ( count($files) ) { ?>
                    <div class="files">
                      <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_file.png" />
                      <ul class="openlayer">
                        <?php foreach($files as $file) { ?>
                        <li>
                          <a target="_blank"
                            href="<?php echo G5_DATA_URL; ?>/order_cart/<?php echo $file['ctf_name']; ?>"><?php echo $file['ctf_real_name']; ?></a>
                        </li>
                        <?php } ?>
                      </ul>
                    </div>
                    <?php } ?>
                    <?php if ( $cs['odc_no'] && $k == 0 ) { ?>
                    <div class="custom_order">
                      <button type="button" class="shbtn">주문제작</button>
                      <div class="openlayer cs_openlayer">
                        <ul class="cs_list">
                          <?php if ( $cs['size_use'] ) { ?>
                          <li>
                            <h3>기본정보</h3>
                            <div>
                              사이즈 (<?php echo $cs['size_width']; ?>mm X <?php echo $cs['size_height']; ?>mm)
                            </div>
                          </li>
                          <?php } ?>
                          <?php if ( $cs['frame_use'] ) { ?>
                          <li>
                            <h3>프레임 (도광판)</h3>
                            <div>
                              <?php echo $cs['frame_standard'] ? $cs['frame_standard']: ''; ?>
                              <?php echo $cs['frame_color'] ? ' / ' . $cs['frame_color']: ''; ?>

                              <br />

                              <?php echo $cs['frame_front'] ? '앞판: ' . $cs['frame_front']: ''; ?>
                              <?php echo $cs['frame_front_transparent_acrylic'] ? ' / 앞판 투명아크릴: ' . $cs['frame_front_transparent_acrylic']. 'T': ''; ?>
                              <?php echo $cs['frame_front_optical_scatter'] ? ' / 앞판 광학산판: ' . $cs['frame_front_optical_scatter']. 'T': ''; ?>

                              <br />

                              <?php echo $cs['frame_back'] ? '뒷판: ' . $cs['frame_back']: ''; ?>
                              <?php echo $cs['frame_back_transparent_acrylic'] ? ' / 뒷판 투명아크릴: ' . $cs['frame_back_transparent_acrylic']. 'T': ''; ?>
                              <?php echo $cs['frame_back_mdf'] ? ' / 뒷판 MDF: ' . $cs['frame_back_mdf']. 'T': ''; ?>
                              <?php echo $cs['frame_back_formax'] ? ' / 뒷판 포맥스: ' . $cs['frame_back_formax']. 'T': ''; ?>
                            </div>
                          </li>
                          <?php } ?>
                          <?php if ( $cs['lightpanel_use'] ) { ?>
                          <li>
                            <h3>라이트패널</h3>
                            <div>
                              <?php echo $cs['lightpanel_led_direction'] ? $cs['lightpanel_led_direction']: ''; ?>
                              <?php echo $cs['lightpanel_led_qty'] ? ' / ' . $cs['lightpanel_led_qty'].'개': ''; ?>
                              / 전원 <?php echo $cs['lightpanel_smps'] ? $cs['lightpanel_smps']: ''; ?>
                              <?php echo $cs['lightpanel_power_line'] ? ' / ' . $cs['lightpanel_power_line']: ''; ?>

                              <br />
                              LED <?php echo $cs['lightpanel_led_ea'] ? $cs['lightpanel_led_ea'].'개': ''; ?>
                              <?php echo $cs['lightpanel_led_k'] ? ' / ' . $cs['lightpanel_led_k']: ''; ?>

                              <br />
                              AC전원선
                              <?php echo $cs['lightpanel_power_line_ac'] ? $cs['lightpanel_power_line_ac'].'mm': '없음'; ?>
                              /
                              전원선 DC잭
                              <?php echo $cs['lightpanel_power_line_dc'] ? $cs['lightpanel_power_line_dc'].'mm': '없음'; ?>
                              /
                              와이어
                              <?php echo $cs['lightpanel_power_line_wire'] ? $cs['lightpanel_power_line_wire'].'mm': '없음'; ?>

                              <br />
                              레이저가공 <?php echo $cs['lightpanel_laser'] ? $cs['lightpanel_laser']: ''; ?>
                              <?php
                                                                    // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND ctf_type = 'lightpanel_laser'";
                                                                    $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$carts[$i]['ct_uid']}' AND ctf_type = 'lightpanel_laser'";
                                                                    $result = sql_query($sql);
                                                                    $g = 0;
                                                                    while ($row = sql_fetch_array($result)) {
                                                                        if ( $g == 0 ) echo '(';
                                                                        $g++;
                                                                    ?>
                              <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>'
                                class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                              <?php }
                                                                    if ( $g > 0 ) echo ')';
                                                                    ?>

                              <br />

                              스위치 <?php echo $cs['lightpanel_switch_use'] ? $cs['lightpanel_switch_use']: ''; ?>
                              <?php echo $cs['lightpanel_switch'] ? ' / ' . $cs['lightpanel_switch']: ''; ?>
                              <?php echo $cs['lightpanel_switch_explain'] ? ' (' . $cs['lightpanel_switch_explain'].')': ''; ?>
                            </div>
                          </li>
                          <?php } ?>
                          <?php if ( $cs['holder_use'] ) { ?>
                          <li>
                            <h3>천장걸이형/거치대</h3>
                            <div>
                              분류
                              <?php echo $cs['holder_class'] ? $cs['holder_class']: ''; ?><?php echo $cs['holder_pipe_length'] ? ' / 길이 '. $cs['holder_pipe_length'].'mm': ''; ?>
                              <br />
                              간격 <?php echo $cs['holder_pipe_interval_1'] ? $cs['holder_pipe_interval_1']: '0'; ?>mm ↔
                              <?php echo $cs['holder_pipe_interval_2'] ? $cs['holder_pipe_interval_2']: '0'; ?>mm ↔
                              <?php echo $cs['holder_pipe_interval_3'] ? $cs['holder_pipe_interval_3']: '0'; ?>mm
                            </div>
                          </li>
                          <?php } ?>
                          <?php if ( $cs['printout_use'] ) { ?>
                          <li>
                            <h3>출력물</h3>
                            <div>
                              디자인
                              <?php
                                                                    // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND ctf_type = 'printout_design'";
                                                                    $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$carts[$i]['ct_uid']}' AND ctf_type = 'printout_design'";
                                                                    $result = sql_query($sql);
                                                                    $g = 0;
                                                                    while ($row = sql_fetch_array($result)) {
                                                                        if ( $g == 0 ) echo '(';
                                                                        $g++;
                                                                    ?>
                              <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>'
                                class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                              <?php }
                                                                    if ( $g > 0 ) echo ')';
                                                                    ?>

                              <br />
                              출력물 <?php echo $cs['printout_printout'] ? $cs['printout_printout']: ''; ?>
                            </div>
                          </li>
                          <?php } ?>
                          <?php if ( $cs['content_use'] ) { ?>
                          <li>
                            <h3>작업요청내용</h3>
                            <div>
                              <?php echo $cs['content_common'] ? '공통내용: ' . $cs['content_common'].'<br/>': ''; ?>
                              <?php echo $cs['content_minart'] ? '민아트: ' . $cs['content_minart'].'<br/>': ''; ?>
                              <?php echo $cs['content_selmartec'] ? '쎌마텍: ' . $cs['content_selmartec'].'<br/>': ''; ?>
                              <?php echo $cs['content_lp'] ? 'LP팀: ' . $cs['content_lp'].'<br/>': ''; ?>
                            </div>
                          </li>
                          <?php } ?>
                        </ul>
                        <a class="shbtn edit_item" data-it-id="<?php echo $carts[$i]['it_id']; ?>"
                          data-uid="<?php echo $carts[$i]['ct_uid']; ?>">수정하기</a>
                      </div>
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </td>
              <td class="item_qty">
                <label for="ct_qty_<?php echo $chk_cnt; ?>"
                  class="sound_only"><?php echo get_text($options[$k]['ct_option']); ?> 수량</label>
                <!--
                                        <input type="text" name="ct_qty[<?php echo $chk_cnt; ?>]" id="ct_qty_<?php echo $chk_cnt; ?>" value="<?php echo $options[$k]['ct_qty']; ?>" required class="frm_input required" size="5">
                                        -->
                <!--
                                        <input type="text" name="ct_qty[<?php echo $options[$k]['ct_id']; ?>]" id="ct_qty_<?php echo $chk_cnt; ?>" value="<?php echo $options[$k]['ct_qty']; ?>" required class="frm_input required" size="5">
                                        -->
                <?php echo $options[$k]['ct_qty']; ?>
              </td>
              <td class="item_stock_qty">
                <?php 
                                            if($options[$k]['ct_delivery_cnt']&&!$options[$k]['ct_combine_ct_id']){
                                                // echo $options[$k]['ct_combine_ct_id'];
                                                echo $options[$k]['ct_delivery_cnt'];
                                                $total_ct_delivery_cnt = $total_ct_delivery_cnt + $options[$k]['ct_delivery_cnt'];
                                            }else{
                                                echo "0";
                                            }
                                        ?>
              </td>
              <td class="item_barcode">
                <!--
                                        <input type="text" name="ct_qty[<?php echo $chk_cnt; ?>]" id="ct_qty_<?php echo $chk_cnt; ?>" value="<?php echo $options[$k]['ct_qty']; ?>" required class="frm_input required" size="5">
                                        -->
                <!--
                                        <input type="text" name="ct_qty[<?php echo $options[$k]['ct_id']; ?>]" id="ct_qty_<?php echo $chk_cnt; ?>" value="<?php echo $options[$k]['ct_qty']; ?>" required class="frm_input required" size="5">
                                        -->
                <ul style="position: absolute;">
                  <?php if($options[$k]['ct_qty'] >= 3){ ?>
                  <!--
                                           <li>
                                             <input type="text" class="frm_input" style="width: 70px;">
                                             <button type="button" style="width: 35px; height: 24px; background-color: #3366CC; color: #FFF;" class="barNumCustomSubmitBtn">적용</button>
                                             <button type="button" style="width: 35px; height: 24px; background-color: #999; color: #FFF;" class="barNumGuideOpenBtn">방법</button>
                                             <div class="barNumGuideBox">
                                               <div class="title">바코드 일괄 등록 방법 <button type="button" class="closeBtn">X</button></div>
                                               <p>
                                                 공통된 문자/숫자를 앞에 부여 후 반복되는 숫자를 입력합니다.<br><br>
                                                 예시) 010101^3,4,5-10- 010101은 공동문자/숫자입니다.<br><br>
                                                 - ^이후는 자동으로 입력하기 위한 내용입니다.<br>
                                                 -    “숫자 입력 후 콤마(,)”를 입력하면 독립 숫자가 입력됩니다.<br>
                                                 - 5-10이라고 입력하면5부터10까지 순차적으로 입력됩니다.<br>
                                                 - 00-20으로 시작 숫자가00인 경우2자리 숫자로 입력됩니다
                                               </p>
                                             </div>
                                           </li>
-->
                  <?php } ?>
                  <?php
                    for($b=0;$b<$options[$k]['ct_qty'];$b++) {
                      //$ct_barcode_array = unserialize(base64_decode($options[$k]['ct_barcode']));
                      $ct_barcode_array = explode('|', $options[$k]['ct_barcode']);
                      //API전송데이터
                      $json_data[$k][$b]['penId'] = $od['od_penId'];            //수급자ID
                      $json_data[$k][$b]['prodId'] = $carts[$i]['it_id'];          //제품ID
                      $json_data[$k][$b]['prodNm'] = stripslashes($carts[$i]['it_name']); //제품명
                      $json_data[$k][$b]['itemId'] = 'ITM2020092200020';          //품목아이디
                      $json_data[$k][$b]['itemNm'] = $carts[$i]['it_model'];        //품목명
                      $json_data[$k][$b]['prodPayCode'] = 'H12060130101';          //급여코드
                      $json_data[$k][$b]['prodColor'] = $options[$k]['ct_option'];    //옵션명:색상
                      $json_data[$k][$b]['ordStatus'] = '00';                //"00" 구매/대여 여부 ( 공통코드 : PRO00001 )
                      $json_data[$k][$b]['prodOflPrice'] = '307000';            //고시가
                      $json_data[$k][$b]['penPay'] = '46050';                //테이블 정의에 없음
                      $json_data[$k][$b]['prodBarNum'] = $ct_barcode_array[$b];      //바코드 번호
                      $json_data[$k][$b]['ordNm"'] = $od['od_name'];            //수급자(주문자) 이름
                      $json_data[$k][$b]['ordCont'] = $od['od_hp'];            //수급자(주문자) 전화번호
                      $json_data[$k][$b]['ordZip'] = $od['od_zip1'].$od['od_zip2'];    //수급자(주문자) 우편번호
                      $json_data[$k][$b]['ordAddr'] = $od['od_addr1'];          //수급자(주문자) 주소
                      $json_data[$k][$b]['ordAddrDtl'] = $od['od_addr2'];          //수급자(주문자) 상세 주소
                      $json_data[$k][$b]['ordMemo'] = $od['od_memo'];            //배송 메모
                      $json_data[$k][$b]['payMehCd'] = '00';                //"00" 결제수단 ( 공통코드 : PEN00006 )
                      $json_data[$k][$b]['eformYn'] = 'N';
                    ?>
                  <li style="padding-top:5px;">
                    <input type="hidden" name="ct_barcode[<?php echo $chk_cnt; ?>][<?php echo $b;?>]"
                      id="ct_barcode_<?php echo $chk_cnt; ?>_<?php echo $b;?>"
                      value="<?=$prodList[$prodListCnt]["prodBarNum"]?>"
                      class="frm_input required prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoIdDataList[$prodListCnt]?>">
                  </li>
                  <?php $prodListCnt++; } ?>
                </ul>



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
              <!--
                                    <td class="item_discount">

                                        <?php if ( $options[$k]['ct_discount'] != 0 ) { ?>
                                            - <?php echo number_format($options[$k]['ct_discount']); ?>원
                                        <?php }else{ ?>
                                            -
                                        <?php } ?>
                                    </td>
                                        -->
              <!-- <td class="item_sendcost">
                                        <?php echo number_format($options[$k]['ct_sendcost']); ?>원
                                    </td> -->
              <td class="item_stotal">
                <?php echo number_format($options[$k]['ct_price_stotal']); ?>원
                <p style="font-size:12px; font-weight: lighter;">
                <?php
                  $_cost = get_item_delivery_cost( $options[$k]['it_id'], $options[$k]['ct_qty'] );
                  echo($_cost['cost_title']);
                  echo((($_cost['cost'])?"<br/>".$_cost['cost']."원":"")."");
                ?>
                </p>
              </td>
              <td class="item_status">
                <?php 
                                            $ct_status_text="";
                                            switch ($options[$k]['ct_status']) {
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
                                        ?>
              </td>
              <td class="item_memo">
                <?php 
                                            echo $prodMemo;
                                        ?>
              </td>
              <td class="btncol">
                <!-- 출고예정일 -->
                <?php echo $options[$k]['ct_is_direct_delivery'] && $options[$k]['ct_direct_delivery_date'] ? date('Y-m-d H시', strtotime($options[$k]['ct_direct_delivery_date'])) : '';?>
                <!-- 출고예정일 -->
              </td>
              <td class="btncol">
                <!-- 출고완료일 -->
                <?php echo $options[$k]['ct_ex_date'];?>
                <!-- 출고완료일 -->
              </td>

              <td class="btncol">
                <select class="ct_manager" data-ct-id="<?php echo $options[$k]['ct_id']; ?>">
                  <?php
                                            $sql_m="select b.`mb_name`, b.`mb_id` from `g5_auth` a left join `g5_member` b on (a.`mb_id`=b.`mb_id`) where a.`au_menu` = '400001'";
                                            $result_m = sql_query($sql_m);
                                            echo '<option value="미지정">미지정</option>';
                                            for ($q=0; $row_m=sql_fetch_array($result_m); $q++){
                                                $selected="";
                                                if($options[$k]['ct_manager'] == $row_m['mb_id']){ $selected="selected"; }
                                                echo '<option value="'.$row_m['mb_id'].'" '.$selected.'>'.$row_m['mb_name'].'('.$row_m['mb_id'].')</option>';
                                            }
                                        ?>
                </select>
              </td>

              <td class="btncol">
                <?php if($od['od_writer']!="openmarket") { /* ?>
                <div class="more">
                  <?php
                                                $temp_ct_step = get_step($options[$k]['ct_status']);
                                                if($options[$k]['it_type3'] || ($temp_ct_step['cart_editable'] || $temp_ct_step['cart_deletable']) && !$options[$k]['ct_stock_qty']){ 
                                                ?>
                  <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_more_b.png" class="item_list_more"
                    data-ct-id="<?php echo $options[$k]['ct_id']; ?>" />
                  <ul class="openlayer">
                    <?php if ($options[$k]['it_type3'] || $temp_ct_step['cart_editable']) { ?>
                    <li class="edit_item" data-od-id="<?php echo $od_id; ?>"
                      data-it-id="<?php echo $options[$k]['it_id']; ?>" data-uid="<?php echo $options[$k]['ct_uid']; ?>"
                      data-memo="<?php echo $prodMemo; ?>">수정</li>
                    <?php } ?>
                    <?php if ($options[$k]['it_type3'] || $temp_ct_step['cart_deletable']) { ?>
                    <li class="delete_item" data-od-id="<?php echo $od_id; ?>"
                      data-ct-id="<?php echo $options[$k]['ct_id']; ?>"
                      data-it-id="<?php echo $options[$k]['it_id']; ?>"
                      data-uid="<?php echo $options[$k]['ct_uid']; ?>">삭제</li>
                    <?php } ?>
                  </ul>
                  <?php } ?>
                </div>
                <?php */ } ?>
              </td>

            </tr>
            <?php
                                $chk_first++;
                                $chk_cnt++;
                                }

                $prodOptNum = implode('^', $option_array);
                $prodBarNum = implode('^', $barcode_array);

                                if ($carts[$i]['it_outsourcing_use']) {
                                    if($carts[$i]['it_outsourcing_option']) {
                                        $outsourcing_options = explode(',', $carts[$i]['it_outsourcing_option']);
                                    }
                                    if($carts[$i]['it_outsourcing_option2']) {
                                        $outsourcing_options2 = explode(',', $carts[$i]['it_outsourcing_option2']);
                                    }
                                    if($carts[$i]['it_outsourcing_option3']) {
                                        $outsourcing_options3 = explode(',', $carts[$i]['it_outsourcing_option3']);
                                    }
                                    if($carts[$i]['it_outsourcing_option4']) {
                                        $outsourcing_options4 = explode(',', $carts[$i]['it_outsourcing_option4']);
                                    }
                                    if($carts[$i]['it_outsourcing_option5']) {
                                        $outsourcing_options5 = explode(',', $carts[$i]['it_outsourcing_option5']);
                                    }
                                ?>
            <tr>
              <td colspan="2"></td>
              <?php
                                    // $outsourcing = sql_fetch("SELECT * FROM g5_shop_order_outsourcing WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND oo_state = '0' ORDER BY oo_id DESC");
                                    $outsourcing = sql_fetch("SELECT * FROM g5_shop_order_outsourcing WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND oo_uid = '{$carts[$i]['ct_uid']}' AND oo_state = '0' ORDER BY oo_id DESC");
                                    if ( $outsourcing['oo_id'] ) {
                                    ?>
              <td colspan="9" class="item_outsourcing" data-id="<?php echo $carts[$i]['it_id']; ?>"
                data-uid="<?php echo $carts[$i]['ct_uid']; ?>">
                외부발주 : <?php echo $outsourcing['oo_outsourcing_option']; ?>,
                <?php echo $outsourcing['oo_outsourcing_option2'] ? $outsourcing['oo_outsourcing_option2'] . ', ' : ''; ?>
                <?php echo $outsourcing['oo_outsourcing_option3'] ? $outsourcing['oo_outsourcing_option3'] . ', ' : ''; ?>
                <?php echo $outsourcing['oo_outsourcing_option4'] ? $outsourcing['oo_outsourcing_option4'] . ', ' : ''; ?>
                <?php echo $outsourcing['oo_outsourcing_option5'] ? $outsourcing['oo_outsourcing_option5'] . ', ' : ''; ?>

                <div id="it_outsourcing_option_file_<?php echo $i; ?>" class="it_outsourcing_option_file"
                  data-id="<?php echo $i; ?>">
                  첨부파일:
                  <ul
                    class="upload_files upload_files_outsourcing_option_apply upload_files_outsourcing_option_apply_<?php echo $carts[$i]['it_id']; ?> upload_files_outsourcing_option_apply_<?php echo $carts[$i]['ct_uid']; ?>">
                    <?php
                                                // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND it_id = '{$carts[$i]['it_id']}'";
                                                $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND ctf_uid = '{$carts[$i]['ct_uid']}'";
                                                $result = sql_query($sql);
                                                $outsourcing_files = 0;
                                                while ($row = sql_fetch_array($result)) {
                                                    $outsourcing_files++;
                                                ?>
                    <li>
                      <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink"
                        target="_blank"><?php echo $row['real_name']; ?></a>
                    </li>
                    <?php } ?>
                    <?php if (!$outsourcing_files) echo "없음&nbsp;&nbsp;&nbsp;"; ?>
                  </ul>
                </div>
                <input type="button" value="취소" class="blue shbtn btn item_outsourcing_cancel"
                  data-id="<?php echo $outsourcing['oo_id']; ?>">
                <span style="margin-left:15px;"><?php echo $outsourcing['oo_created_at']; ?></span>
              </td>
              <?php }else{ ?>
              <td colspan="9" class="item_outsourcing" data-id="<?php echo $carts[$i]['it_id']; ?>"
                data-uid="<?php echo $carts[$i]['ct_uid']; ?>">
                외부발주 :

                <select name="sales_manager">
                  <option value="">담당자 선택</option>
                  <?php
                                            $sql = "SELECT * FROM g5_auth WHERE au_menu = '400400' AND au_auth LIKE '%w%'";
                                            $auth_result = sql_query($sql);
                                            while($a_row = sql_fetch_array($auth_result)) {
                                                $a_mb = get_member($a_row['mb_id']);
                                            ?>
                  <option value="<?php echo $a_mb['mb_id']; ?>"
                    <?php echo $a_mb['mb_id'] == $od['od_sales_manager'] ? 'selected' : ''; ?>>
                    <?php echo $a_mb['mb_name']; ?></option>
                  <?php } ?>
                </select>

                <?php if($carts[$i]['it_outsourcing_option']) { ?>
                <select name="it_outsourcing_option">
                  <option value="">옵션1</option>
                  <?php foreach ($outsourcing_options as $opt) { ?>
                  <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                  <?php } ?>
                </select>
                <?php } ?>
                <?php if($carts[$i]['it_outsourcing_option2']) { ?>
                <select name="it_outsourcing_option2">
                  <option value="">옵션2</option>
                  <?php foreach ($outsourcing_options2 as $opt) { ?>
                  <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                  <?php } ?>
                </select>
                <?php } ?>
                <?php if($carts[$i]['it_outsourcing_option3']) { ?>
                <select name="it_outsourcing_option3">
                  <option value="">옵션3</option>
                  <?php foreach ($outsourcing_options3 as $opt) { ?>
                  <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                  <?php } ?>
                </select>
                <?php } ?>
                <?php if($carts[$i]['it_outsourcing_option4']) { ?>
                <select name="it_outsourcing_option4">
                  <option value="">옵션4</option>
                  <?php foreach ($outsourcing_options4 as $opt) { ?>
                  <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                  <?php } ?>
                </select>
                <?php } ?>
                <?php if($carts[$i]['it_outsourcing_option5']) { ?>
                <select name="it_outsourcing_option5">
                  <option value="">옵션5</option>
                  <?php foreach ($outsourcing_options5 as $opt) { ?>
                  <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                  <?php } ?>
                </select>
                <?php } ?>
                <div id="it_outsourcing_option_file_<?php echo $i; ?>" class="it_outsourcing_option_file"
                  data-id="<?php echo $i; ?>" data-uid="<?php echo $carts[$i]['ct_uid']; ?>">
                  <button type="button" class="shbtn uploadbtn">찾아보기</button>
                  <ul
                    class="upload_files upload_files_outsourcing_option_apply upload_files_outsourcing_option_apply_<?php echo $carts[$i]['it_id']; ?> upload_files_outsourcing_option_apply_<?php echo $carts[$i]['ct_uid']; ?>">
                    <?php
                                                // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND it_id = '{$carts[$i]['it_id']}'";
                                                $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND ctf_uid = '{$carts[$i]['ct_uid']}'";
                                                $result = sql_query($sql);
                                                while ($row = sql_fetch_array($result)) {
                                                ?>
                    <li>
                      <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink"
                        target="_blank"><?php echo $row['real_name']; ?></a>
                      <a href='#' class="remove" data-no="<?php echo $row['no']; ?>"><img
                          src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_del_s.png" /></a>
                    </li>
                    <?php } ?>
                  </ul>
                </div>
                <input type="button" value="전송" class="blue shbtn btn item_outsourcing_submit">
              </td>
              <?php } ?>
            </tr>
            <?php } ?>
            <?php if(substr($carts[$i]["ca_id"], 0, 2) == 20&&$ct_status_text == "재고소진"){ ?>
            <tr>
              <td></td>
              <td colspan="10" style="text-align: left;">
                <b>대여기간 : </b>
                <?=$ordLendDtm?>
              </td>
            </tr>
            <?php } ?>
            <?php if($prodMemo){ ?>
            <tr>
              <td></td>
              <td colspan="10" style="text-align: left;">
                <!-- <b>요청사항 : </b>
                          <?=$prodMemo?> -->
              </td>
            </tr>
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
              <td class="item_stock_qty">
                <?=$total_ct_delivery_cnt?>
              </td>
              <td class="item_barcode">
              </td>
              <td class="item_basic_price">
              </td>
              <td class="item_tax_price">
              </td>
              <td class="item_price">
                <!--<?php echo number_format($tot_price); ?>원-->
              </td>
              <!--
                            <td class="item_discount">
                                - <?php echo number_format($tot_discount); ?>원
                            </td>
                            -->
              <!-- <td class="item_sendcost">
                             <?php echo number_format($tot_sendcost); ?>원
                             </td> -->
              <td class="item_stotal">
                <?php echo number_format($tot_total); ?>원
              </td>
              <td class="item_status">
              </td>
              <td class="item_memo"></td>
              <td class="item_memo"></td>
              <td class="btncol"></td>
              <td class="btncol"></td>
              <td class="btncol"></td>
            </tr>
          </tbody>
        </table>

        <div class="frmsamhwaorderform_bottom">
          <div class="change_status">
            <span>선택한 상품 상태값</span>
            <select name="step" id="step">
              <option value="주문">주문접수</option>
              <option value="입금">입금완료</option>
              <option value="준비">상품준비</option>
              <option value="출고준비">출고준비</option>
              <option value="배송">출고완료</option>
              <option value="완료">배송완료</option>
              <option value="주문무효">주문무효</option>
              <option value="취소">주문취소</option>
            </select>
            <input type="button" value="변경하기" class="btn shbtn" id="change_cart_status">
          </div>
          <button type="button" id="btn_order_edit">주문품목 변경</button>
          <!--                    <div class="change_discount2" >-->
          <!--                        <span>* 추가 배송비</span>-->
          <!--                        <input type="text" class="frm_input" name="od_send_cost2" id="od_send_cost2" value="--><?php //echo $od['od_send_cost2']; ?>
          <!--" />-->
          <!--                        <input type="button" value="적용" class="btn shbtn" id="change_send_cost2">-->
          <!--                    </div>-->
        </div>
      </form>
    </div>
  </div>
  <?php if($od["recipient_yn"] == "Y"){ ?>
  <div class="block">
    <div class="header">
      <h2>수급자정보</h2>
    </div>
    <!-- 수급자 정보가 없는 경우 표시
        <div class="block-box">

          <p>입력된 수급자 정보가 없습니다. </p>

        </div>
        -->
    <table class="recipient_info">
      <tr>
        <th>수급자</th>
        <th>인정등급</th>
        <th>유효기간</th>
        <th>적용기간</th>
        <th>전화번호</th>
        <th>주소</th>
      </tr>
      <?php if($od['od_penId']){ ?>
      <tr>
        <td><?php echo get_text($od['od_penNm']); ?></td>
        <td><?php echo get_text($od['od_penTypeNm']); ?></td>
        <td><?php echo get_text($od['od_penExpiDtm']); ?></td>
        <td><?php echo get_text($od['od_penAppEdDtm']); ?></td>
        <td><?php echo get_text($od['od_penConPnum']); ?></td>
        <td><?php echo get_text($od['od_penAddr']); ?></td>
      </tr>
      <?php }else{ ?>
      <tr>
        <td colspan="6">수급자정보가 없습니다.</td>
      </tr>
      <?php } ?>
    </table>

  </div>
  <?php } ?>
  <div class="block">
    <div class="header">
      <h2>배송정보</h2>
      <div class="right">
        <input type="button" value="배송지 목록" class="btn shbtn" id="address_list">
        <input type="button" value="기본정보 반영" class="btn shbtn" id="reset_od_info">
        <input type="button" value="출고 리스트" class="btn shbtn" id="release_list"
          onclick="location.href='./samhwa_deliverylist.php';">
      </div>
    </div>
    <div class="delivery_info block-box">
      <form id="frmsamhwaorderdeliveryform" name="frmsamhwaorderdeliveryform">

        <div class="tbl_frm01">
          <table>
            <caption>받으시는 분 정보</caption>
            <colgroup>
              <col class="grid_4">
              <col>
            </colgroup>
            <tbody>
              <tr>
                <th scope="row"><label for="od_b_name"><span class="sound_only">받으시는 분 </span>수령인</label></th>
                <td colspan="3"><input type="text" name="od_b_name" value="<?php echo get_text($od['od_b_name']); ?>"
                    id="od_b_name" required class="frm_input required"></td>
              </tr>
              <tr>
                <th scope="row"><label for="od_b_tel"><span class="sound_only">받으시는 분 </span>전화번호</label></th>
                <td style="width:250px;"><input type="text" name="od_b_tel"
                    value="<?php echo get_text($od['od_b_tel']); ?>" id="od_b_tel" required class="frm_input required">
                </td>
                <!-- </tr>
                    <tr> -->
                <th scope="row" style="width:100px;"><label for="od_b_hp"><span class="sound_only">받으시는 분
                    </span>핸드폰</label></th>
                <td><input type="text" name="od_b_hp" value="<?php echo get_text($od['od_b_hp']); ?>" id="od_b_hp"
                    class="frm_input required"></td>
              </tr>
              <tr>
                <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                <td class="od_b_address" colspan="3">
                  <label for="od_b_zip" class="sound_only">우편번호</label>
                  <input type="text" name="od_b_zip"
                    value="<?php echo get_text($od['od_b_zip1']).get_text($od['od_b_zip2']); ?>" id="od_b_zip" required
                    class="frm_input required" size="35">
                  <button type="button" class="shbtn"
                    onclick="win_zip('frmsamhwaorderdeliveryform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소
                    검색</button><br>
                  <input type="text" name="od_b_addr1" value="<?php echo get_text($od['od_b_addr1']); ?>"
                    id="od_b_addr1" required class="frm_input required" size="35" placeholder="기본주소" readonly>
                  <input type="text" name="od_b_addr2" value="<?php echo get_text($od['od_b_addr2']); ?>"
                    id="od_b_addr2" class="frm_input" size="35" placeholder="상세주소">
                  , 지번 : <input type="text" name="od_b_addr3" value="<?php echo get_text($od['od_b_addr3']); ?>"
                    id="od_b_addr3" class="frm_input" size="35" placeholder="참고항목" readonly>
                  <input type="hidden" name="od_b_addr_jibeon" value="<?php echo get_text($od['od_b_addr_jibeon']); ?>">
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
                <th scope="row">전달 메세지</th>
                <td colspan="3"><input type="text" name="od_memo" value="<?php echo get_text($od['od_memo'], 1); ?>"
                    id="od_memo" required class="frm_input" style="width:80%"></td>
              </tr>

              <tr>
                <th scope="row"><label for="od_ex_date">출고완료일</label></th>
                <td colspan="3">
                  <!-- id="od_ex_date" -->
                  <input type="text" readonly name="od_ex_date" value="<?php echo $od['od_ex_date']; ?>" required
                    class="frm_input required" maxlength="10" minlength="10">
                </td>
              </tr>

              <tr>
                <th scope="row">배송정보</th>
                <td colspan="3">
                  <a href="#" class="deliveryCntBtn<?=$deliveryCntBtnStatus?>"><?=$deliveryCntBtnWord?></a>
                </td>
              </tr>
              <!-- <tr>
                        <th scope="row">추가 배송비</th>
                        <td colspan="3"><input type="text" name="" value="" id="" required class="frm_input required" size="30"> 원</td>
                    </tr> -->
              <tr style="display:none">
                <th scope="row">지역별 추가 배송비</th>
                <td colspan="3"><input type="text" name="od_send_cost2" value="<?php echo $od['od_send_cost2']; ?>"
                    id="od_send_cost2" required class="frm_input required" size="30" readonly>&nbsp;원&nbsp;&nbsp;*
                  추가배송비는 변경하실 수 없습니다.</td>
              </tr>
              <!--
                    <tr class="gray">
                        <th scope="row">관리자메모</th>
                        <td><textarea name="od_send_admin_memo" rows="8" placeholder="관리자메모를 입력하세요." id="od_send_admin_memo"><?php echo get_text($od['od_send_admin_memo'], 1); ?></textarea></td>
                    </tr>
                    -->
              <input type="hidden" name="od_send_admin_memo"
                value="<?php echo get_text($od['od_send_admin_memo'], 1); ?>" />
            </tbody>
          </table>
        </div>
      </form>
    </div>
    <button id="delivery_info_btn">주문정보 수정</button>
  </div>
  <?php if($reports) { ?>
  <div class="block">
    <?php foreach($reports as $report) { ?>
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
        <?php if($report['issue']) { ?>
        <div class="issue">
          이슈사항 (<?php echo implode(', ', $report['issue']); ?>)
        </div>
        <?php } ?>
      </div>

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

        <?php foreach($report['photo1'] as $photo) { ?>
        <div class="col">
          <div class="report-img">
            <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
              <img
                src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
            </a>
          </div>
        </div>
        <?php } ?>
      </div>

      <div class="row report-img-wrap-title">
        <span>설치 사진(필수)</span>
      </div>

      <div class="row report-img-wrap">
        <?php foreach($report['photo2'] as $photo) { ?>
        <div class="col">
          <div class="report-img">
            <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
              <img
                src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
            </a>
          </div>
        </div>
        <?php } ?>
      </div>

      <div class="row report-img-wrap-title">
        <span>실물 바코드 사진(필수)</span>
      </div>

      <div class="row report-img-wrap">
        <?php foreach($report['photo3'] as $photo) { ?>
        <div class="col">
          <div class="report-img">
            <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
              <img
                src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
            </a>
          </div>
        </div>
        <?php } ?>
      </div>

      <div class="row report-img-wrap-title">
        <span>설치ㆍ회수ㆍ소독확인서 사진(필수)</span>
      </div>

      <div class="row report-img-wrap">
        <?php foreach($report['photo4'] as $photo) { ?>
        <div class="col">
          <div class="report-img">
            <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
              <img
                src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
            </a>
          </div>
        </div>
        <?php } ?>
      </div>

      <div class="row report-img-wrap-title">
        <span>추가사진(선택) - 상품변경 혹은 특이사항 발생 시</span>
      </div>

      <?php if($report['issue']) { ?>
      <div class="col report-img-wrap">
        <div class="report-issue">
          이슈사항
        </div>
        <div class="report-issue-select">
          이슈사항 (
          <?php if($report['issue']) { ?>
          <?php echo implode(' /', $report['issue']); ?>
          <?php } ?>
          )
        </div>
        <div class="report-issue-select">
          <p>
            <?=nl2br($report['ir_issue'])?>
          </p>
        </div>
      </div>
      <?php } ?>

      <?php } ?>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="block">
    <div class="header">
      <h2>결제정보/매출증빙</h2>
      <!-- <div class="right">
                <input type="button" value="매출증빙 리스트" class="btn shbtn" id="maechul_zhengming_list">
            </div> -->
    </div>
    <div class="payment block-box">
      <div class="pay">
        <h3 class="box_sub_title">결제내역</h3>
        <table class="payment-table">
          <tbody>
            <tr>
              <th>결제수단</th>
              <td><?php echo $od['od_settle_case']; ?></td>
            </tr>
            <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌') { ?>
            <tr>
              <th>입금계좌</th>
              <td><?php echo $od['od_bank_account']; ?></td>
            </tr>
            <tr>
              <th><?php echo $od['od_settle_case']; ?> 입금액</th>
              <td><?php echo display_price($od['od_receipt_price']); ?></td>
            </tr>
            <tr>
              <th>입금자</th>
              <td><?php echo get_text($od['od_deposit_name']); ?></td>
            </tr>
            <tr>
              <th>입금확인일시</th>
              <td>
                <?php echo $od['od_receipt_time']; ?> (<?php echo get_yoil($od['od_receipt_time']); ?>)
              </td>
            </tr>
            <?php } ?>
            <?php if ($od['od_settle_case'] == '휴대폰') { ?>
            <tr>
              <th>휴대폰번호</th>
              <td><?php echo get_text($od['od_bank_account']); ?></td>
            </tr>
            <tr>
              <th><?php echo $od['od_settle_case']; ?> 결제액</th>
              <td><?php echo display_price($od['od_receipt_price']); ?></td>
            </tr>
            <tr>
              <th>결제 확인일시</th>
              <td>
                <?php if ($od['od_receipt_time'] == 0) { ?>결제 확인일시를 체크해 주세요.
                <?php } else { ?><?php echo $od['od_receipt_time']; ?> (<?php echo get_yoil($od['od_receipt_time']); ?>)
                <?php } ?>
              </td>
            </tr>
            <?php } ?>

            <?php if ($od['od_settle_case'] == '신용카드') { ?>
            <tr>
              <th class="sodr_sppay">신용카드 결제금액</th>
              <td>
                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <th class="sodr_sppay">카드사</th>
              <td><?php echo get_receipt_bank_name_by_value($od['od_receipt_bank']) ?></td>
            </tr>
            <tr>
              <th class="sodr_sppay">승인번호</th>
              <td><?php echo $od['od_receipt_bank_no'] ?></td>
            </tr>
            <tr>
              <th class="sodr_sppay">카드 승인일시</th>
              <td>
                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>신용카드 결제 일시 정보가 없습니다.
                <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>

            <?php if ($od['od_settle_case'] == 'KAKAOPAY') { ?>
            <tr>
              <th class="sodr_sppay">KAKOPAY 결제금액</th>
              <td>
                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <th class="sodr_sppay">KAKAOPAY 승인일시</th>
              <td>
                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>신용카드 결제 일시 정보가 없습니다.
                <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>

            <?php if ($od['od_settle_case'] == '간편결제' || ($od['od_pg'] == 'inicis' && is_inicis_order_pay($od['od_settle_case']) ) ) { ?>
            <tr>
              <th class="sodr_sppay"><?php echo $s_receipt_way; ?> 결제금액</th>
              <td>
                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <th class="sodr_sppay"><?php echo $s_receipt_way; ?> 승인일시</th>
              <td>
                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") { echo $s_receipt_way; ?> 결제 일시 정보가 없습니다.
                <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>
            <tr>
              <th>상태</th>
              <!--<td class="bold <?php echo $od['od_pay_state'] ? '' : 'red'; ?>"><?php echo $od['od_pay_state'] == '1' ? '결제완료' : ($od['od_pay_state'] == '2' ? '결제후 출고' : '미결제'); ?></td>-->
              <td class="bold">
                <span class="pay-state"
                  style="color:<?php echo $pay_status['color']; ?>;"><?php echo $pay_status['name']; ?></span>
              </td>
            </tr>
            <?php if( $od["od_pg"] == "kcp" && $od['od_settle_case'] == '신용카드' && $od['od_pay_state'] == '1'): ?>
            <tr>
              <th>매출전표</th>
              <td class="bold">
                <span><a href="javascript:showKcpWindow()" id="jonpyu">보기</a></span>
              </td>
            </tr>
            <?php endif; ?>

            <?php if ($od['od_settle_case'] == '네이버페이') {  ?>
            <tr>
              <th>결제방법</th>
              <td>
                <?php echo $od['od_naver_PaymentMeans'];?>
              </td>
            </tr>
            <?php if ($od['od_receipt_bank']) { ?>
            <tr>
              <th class="sodr_sppay">카드사</th>
              <td><?php echo get_receipt_bank_name_by_value($od['od_receipt_bank']) ?></td>
            </tr>
            <?php } ?>
            <tr>
              <th>매출증빙일시</th>
              <td>
                <?php echo $od['od_receipt_time'];?>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <div class="absolutebtndiv">
          <a class="shbtn" id="edit_payment">변경</a>
        </div>
      </div>
      <div class="maechul_zhengming">
        <h3 class="box_sub_title">매출증빙</h3>
        <form id="typereceipt_after" class="typereceipt_after" name="typereceipt">
          <table class="payment-table">
            <tbody>
              <tr>
                <th>분류</th>
                <td>
                  <select name="ot_typereceipt_cate" class="type_select">
                    <?php foreach($typereceipt_cates as $c) { ?>
                    <option value="<?php echo $c['val']; ?>"
                      <?php echo ($typereceipt['ot_typereceipt_cate'] == $c['val']) || ($typereceipt['ot_typereceipt_cate'] == null && $c['name'] == '세금계산서')  ? 'selected' : ''; ?>>
                      <?php echo $c['name']; ?> <?php echo $c['val']; ?></option>
                    <?php } ?>
                  </select>
                  <span class="tax_info" style="padding:5px 10px">*과세분류 (0)</span>

                </td>
                <script type="text/javascript" charset="utf-8">
                jQuery('.type_select').change(function() {
                  var state = jQuery('.type_select option:selected').val();
                  if (state == '31' || state == '14') {
                    $(".tax_info").text("*과세분류 (3)");
                  } else if (state == '16') {
                    $(".tax_info").text("*과세분류 (1)");
                  } else {
                    $(".tax_info").text("*과세분류 (0)");
                  }
                });
                </script>
              </tr>
              <tr>
                <th>매출증빙</th>
                <td>
                  <div style="display: inline-block;width:90%;">
                    <input type="radio" name="ot_typereceipt" id="typereceipt0" value="0"
                      <?php echo $typereceipt['ot_typereceipt'] == '0' ? 'checked="checked"' : ''; ?>> <label
                      for="typereceipt0">발급안함</label>
                    <input type="radio" name="ot_typereceipt" id="typereceipt2" value="31"
                      <?php echo $typereceipt['ot_typereceipt'] == '31' ? 'checked="checked"' : ''; ?>> <label
                      for="typereceipt2">현금영수증 </label>
                    <input type="radio" name="ot_typereceipt" id="typereceipt1" value="11"
                      <?php echo ($typereceipt['ot_typereceipt'] == '11') || ($typereceipt['ot_typereceipt'] == null) ? 'checked="checked"' : ''; ?>>
                    <label for="typereceipt1">세금계산서 </label>
                    <div id="typereceipt2_view">
                      <ul id="cash_container" class="typereceiptlay">
                        <li>
                          <input type="radio" name="ot_typereceipt_cuse" class="typereceipt_cuse" id="cuse0" value="1"
                            <?php echo $typereceipt['ot_typereceipt_cuse'] == '1' ? 'checked="checked"' : ''; ?>> <label
                            for="cuse0">개인 소득공제</label>
                          <input type="radio" name="ot_typereceipt_cuse" class="typereceipt_cuse" id="cuse1" value="2"
                            <?php echo $typereceipt['ot_typereceipt_cuse'] == '2' ? 'checked="checked"' : ''; ?>> <label
                            for="cuse1">사업자 지출증빙</label>
                        </li>
                        <li class="personallay">
                          <input type="text" name="p_typereceipt_btel" value="<?php echo $typereceipt['ot_btel'] ?>"
                            class="line number frm_input" maxlength="13" title="휴대폰번호('-' 없이 입력)"
                            placeholder="휴대폰번호('-' 없이 입력)">
                        </li>
                        <li class="businesslay" style="display:none;">
                          <input type="text" name="p_typereceipt_bnum" value="<?php echo $typereceipt['ot_bnum'] ?>"
                            class="line number frm_input" maxlength="12" title="사업자번호('-' 없이 입력)"
                            placeholder="사업자번호('-' 없이 입력)">
                        </li>
                        <li>
                          <input type="text" name="p_typereceipt_email"
                            value="<?php echo $typereceipt['ot_tax_email'] ?>" class="line frm_input" title="이메일주소"
                            placeholder="이메일주소">
                        </li>
                      </ul>
                    </div>
                    <div id="typereceipt1_view">
                      <ul id="tax_container" class="typereceiptlay">
                        <table>
                          <tbody>
                            <tr>
                              <th scope="row" style="width: 100px;">
                                <label for="ot_bname">기업명</label>
                              </th>
                              <td colspan="3">
                                <input type="text" name="ot_bname" value="<?php echo $typereceipt['ot_bname'] ?>"
                                  id="ot_bname" class="frm_input" size="30" maxlength="20">
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">
                                <label for="ot_boss_name">대표자명</label>
                              </th>
                              <td colspan="3">
                                <input type="text" name="ot_boss_name"
                                  value="<?php echo $typereceipt['ot_boss_name'] ?>" id="ot_boss_name" class="frm_input"
                                  size="30" maxlength="20">
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">
                                <label for="ot_btel">연락처</label>
                              </th>
                              <td colspan="3">
                                <input type="text" name="ot_btel" value="<?php echo $typereceipt['ot_btel'] ?>"
                                  id="ot_btel" class="frm_input" size="30" maxlength="13">
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">
                                <label for="ot_bnum">사업자번호</label>
                              </th>
                              <td colspan="3">
                                <input type="text" name="ot_bnum" value="<?php echo $typereceipt['ot_bnum'] ?>"
                                  id="ot_bnum" class="frm_input" size="30" maxlength="12">
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">
                                <label for="ot_location_zip">사업장소재지</label>
                              </th>
                              <td colspan="3">
                                <label for="ot_location_zip" class="sound_only">우편번호</label>
                                <input type="text" name="ot_location_zip"
                                  value="<?php echo get_text($typereceipt['ot_location_zip1']).get_text($typereceipt['ot_location_zip2']); ?>"
                                  id="ot_location_zip" required class="frm_input required" size="14">
                                <button type="button" class="shbtn"
                                  onclick="win_zip('typereceipt', 'ot_location_zip', 'ot_location_addr1', 'ot_location_addr2', 'ot_location_addr3', 'ot_location_jibeon');">주소
                                  검색</button><br>
                                <input type="text" name="ot_location_addr1"
                                  value="<?php echo get_text($typereceipt['ot_location_addr1']); ?>"
                                  id="ot_location_addr1" required class="frm_input required" size="30"
                                  placeholder="기본주소">
                                <input type="text" name="ot_location_addr2"
                                  value="<?php echo get_text($typereceipt['ot_location_addr2']); ?>"
                                  id="ot_location_addr2" class="frm_input" size="30" placeholder="상세주소"><br />
                                <input type="text" name="ot_location_addr3"
                                  value="<?php echo get_text($typereceipt['ot_location_addr3']); ?>"
                                  id="ot_location_addr3" class="frm_input" size="30" placeholder="지번주소">
                                <input type="hidden" name="ot_location_jibeon"
                                  value="<?php echo get_text($typereceipt['ot_location_jibeon']); ?>">
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">
                                <label for="ot_buptae">업태</label>
                              </th>
                              <td colspan="3">
                                <input type="text" name="ot_buptae" value="<?php echo $typereceipt['ot_buptae'] ?>"
                                  id="ot_buptae" class="frm_input" size="30" maxlength="20">
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">
                                <label for="ot_bupjong">업종</label>
                              </th>
                              <td colspan="3">
                                <input type="text" name="ot_bupjong" value="<?php echo $typereceipt['ot_bupjong'] ?>"
                                  id="ot_bupjong" class="frm_input" size="30" maxlength="20">
                              </td>
                            </tr>
                            <?php
                                                $sql = "SELECT * FROM g5_member_giup_manager WHERE mb_id = '{$od['mb_id']}'";
                                                $result = sql_query($sql);
                                                $managers = array();
                                                $colspan = 2;
                                                while ($m_row = sql_fetch_array($result)) {
                                                    $managers[] = $m_row;
                                                }
                                                if (!count($managers)) {
                                                    array_push($managers, array());
                                                    $colspan = 3;
                                                }
                                                ?>
                            <tr>
                              <th scope="row">
                                <label for="ot_tax_email">이메일</label>
                              </th>
                              <?php if (count($managers) > 0) { ?>
                              <style>
                              .reduce_width {
                                width: 219px !important;
                              }

                              #giup_manager_sel {
                                width: 105px !important;
                              }
                              </style>
                              <script>
                              $(function() {
                                $('#giup_manager_sel').change(function() {
                                  var selectedManager = $(this).find("option:selected");
                                  var selectedManagersEmail = selectedManager.data('email');

                                  $('input[name=ot_tax_email]').val(selectedManagersEmail);
                                })
                              })
                              </script>
                              <td colspan="1" style="width: 105px">
                                <select id="giup_manager_sel">
                                  <option data-email="<?php echo $typereceipt['ot_tax_email'] ?>" selected>담당자 선택
                                  </option>
                                  <?php for ($m = 0; $m < count($managers); $m++) { ?>
                                  <option data-email="<?php echo $managers[$m]['mm_email'] ?>">
                                    <?php echo $managers[$m]['mm_name'] ?></option>
                                  <?php } ?>
                                </select>
                              </td>
                              <?php } ?>
                              <td colspan="<?php echo $colspan ?>">
                                <input type="text" name="ot_tax_email"
                                  value="<?php echo $typereceipt['ot_tax_email'] ?>" id="ot_tax_email"
                                  class="frm_input reduce_width" size="30" maxlength="30">
                              </td>
                            </tr>
                            <tr>
                              <th scope="row">
                                <label for="ot_manager_name">담당자명</label>
                              </th>
                              <td colspan="3">
                                <input type="text" name="ot_manager_name"
                                  value="<?php echo $typereceipt['ot_manager_name'] ?>" id="ot_manager_name"
                                  class="frm_input" size="30" maxlength="20">
                              </td>
                            </tr>
                          </tbody>
                        </table>
                    </div>
                </td>
              </tr>
              <!-- <tr>
                                <th>승인일</th>
                                <td>
                                    <input type="text" class="frm_input" name="ot_time_date" id="ot_time_date" value="<?php echo $typereceipt['ot_time_date']; ?>" style="width:30%;" />
                                    <input type="text" class="frm_input" name="ot_time_hour" value="<?php echo $typereceipt['ot_time_hour']; ?>" style="width:10%;" />&nbsp;시
                                </td>
                            </tr> -->
              <tr>
                <th>식별번호</th>
                <td>
                  <input type="text" class="frm_input" name="ot_confirm_number"
                    value="<?php echo htmlspecialchars($typereceipt['ot_confirm_number']); ?>" style="width:150px;" />
                  예) 전화번호/사업자번호/자진번호

                </td>
              </tr>
              <tr>
                <th>비고</th>
                <td>
                  <input type="text" class="frm_input" name="ot_etc"
                    value="<?php echo htmlspecialchars($typereceipt['ot_etc']); ?>" style="width:70%;" />
                </td>
              </tr>
              <tr>
                <th colspan="2">
                  &nbsp;
                </th>
              </tr>
            </tbody>
          </table>
        </form>
        <table id="typereceipt_before" class="typereceipt_before payment-table">
          <tbody>
            <?php if ( $typereceipt['ot_typereceipt_cate'] ) { ?>
            <tr>
              <th>분류</th>
              <td>
                <?php echo $typereceipt_cate['name']; ?>
                <?php echo $typereceipt_cate['val'] ? $typereceipt_cate['val'] : ''; ?>
              </td>
            </tr>
            <?php } ?>
            <tr>
              <th>매출증빙</th>
              <td>
                <?php echo $typereceipt['name']; ?>
                <?php echo $typereceipt['cuse'] ? '(' . $typereceipt['cuse']['name'] . ')' : ''; ?>
              </td>
            </tr>
            <?php if ( $typereceipt['ot_bname'] ) { ?>
            <tr>
              <th>기업명</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_bname']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_boss_name'] ) { ?>
            <tr>
              <th>대표자명</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_boss_name']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_bnum'] ) { ?>
            <tr>
              <th>사업자번호</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_bnum']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_bnum']  && $typereceipt['name'] == '세금계산서') { ?>
            <tr>
              <th>사업장소재지</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_location_addr1']) . htmlspecialchars($typereceipt['ot_location_addr2']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_buptae'] ) { ?>
            <tr>
              <th>업태/업종</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_buptae']);
                                    echo ' / ';
                                    echo htmlspecialchars($typereceipt['ot_bupjong']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_tax_email'] ) { ?>
            <tr>
              <th>이메일</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_tax_email']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_btel'] && $typereceipt['cuse']['name'] == '개인소득공제') { ?>
            <tr>
              <th>연락처(현금영수증)</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_btel']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_manager_name'] ) { ?>
            <tr>
              <th>담당자명</th>
              <td>
                <?php
                                    echo htmlspecialchars($typereceipt['ot_manager_name']);
                                ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_time_date'] ) { ?>
            <!-- <tr>
                            <th>승인일</th>
                            <td>
                                <?php echo $typereceipt['ot_time_date']; ?>&nbsp;
                                <?php echo $typereceipt['ot_time_hour']; ?>시
                            </td>
                        </tr> -->
            <?php } ?>
            <?php if ( $typereceipt['ot_confirm_number'] ) { ?>
            <tr>
              <th>식별변호</th>
              <td>
                <?php echo htmlspecialchars($typereceipt['ot_confirm_number']); ?>
              </td>
            </tr>
            <?php } ?>
            <?php if ( $typereceipt['ot_etc'] ) { ?>
            <tr>
              <th>비고</th>
              <td>
                <?php echo htmlspecialchars($typereceipt['ot_etc']); ?>
              </td>
            </tr>
            <?php } ?>
            <tr>
              <th>상태</th>
              <td>
                <?php
                                    if ( $typereceipt['ot_state'] === NULL ) {
                                        echo '정보없음';
                                    }
                                    if ( $typereceipt['ot_state'] === '0' ) {
                                        echo '발급대기';
                                    }
                                    if ( $typereceipt['ot_state'] == 1 ) {
                                        echo '발급완료';
                                    }
                                    if ( $typereceipt['ot_state'] == 2 ) {
                                        echo '발급실패';
                                    }
                                ?>
                <!--<button type="button" class="shbtn">발급완료</button>-->
              </td>
            </tr>
          </tbody>
        </table>
        <div class="absolutebtndiv">
          <a class="shbtn typereceipt_before typereceipt_before_btn">수정</a>
          <a class="shbtn typereceipt_after typereceipt_after_submit">완료</a>
          <a class="shbtn typereceipt_after typereceipt_after_btn">취소</a>
        </div>
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
                $sql = "SELECT * FROM g5_shop_order_admin_memo WHERE od_id = '{$od['od_id']}' ORDER BY om_no DESC";
                $result = sql_fetch($sql);
                ?>
        <div class="om_write_box">
          <textarea name="od_shop_memo" rows="8" placeholder="입력한 메모내용이 보여집니다."
            id="memo_content"><?php echo htmlspecialchars($result['om_content']); ?></textarea>
          <input type="button" value="저장" class="btn" id="memo_submit">
        </div>
        <ul class="memo_logs">
          <?php
                    $sql = "SELECT * FROM g5_shop_order_admin_memo WHERE od_id = '{$od['od_id']}' ORDER BY om_no DESC";
                    $result = sql_query($sql);
                    $memo_counts = 0;
                    while($row = sql_fetch_array($result)) {
                        $om_mb = get_member($row['mb_id']);
                        $memo_counts++;
                    ?>
          <li>
            <div class="om_info">
              <span class="log_datetime"><?php echo $row['om_datetime']; ?></span><?php echo $om_mb['mb_name']; ?> 매니저
              수정
            </div>
            <div class="om_content">
              <?php echo nl2br(htmlspecialchars($row['om_content'])); ?>
            </div>
          </li>
          <?php
                    }
                    if ( !$memo_counts ) {
                    ?>
          <li>
            기록이 없습니다.
          </li>
          <?php
                    }
                    ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>주문취소/반품</h2>
      <div class="right">
        <?php
                $sql = "select *
                        from g5_shop_order_cancel_request
                        where od_id = '{$od['od_id']}'";

                $cancel_request_row = sql_fetch($sql);

                if ($cancel_request_row['request_type'] == 'cancel') {
                    $info = "* 환불 요청이 있습니다. 승인 시 환불이 진행됩니다. ";
                }
                if ($cancel_request_row['request_type'] == 'return') {
                    $info = "* 반품 요청이 있습니다. 승인 시 반품 단계로 이동됩니다.";
                }

                if ($cancel_request_row['od_id'] && $cancel_request_row['approved'] == 0) {
                ?>
        <span id="cancel_info"><?php echo $info ?></span> <button type="button" onclick="approveCancel()">승인</button>
        <button type="button" class="btn" onclick="rejectCancel()">거절</button>
        <?php } ?>
        <?php if (!$cancel_request_row['od_id'] && $avail_request_return) { ?>
        <button type="button" class="btn shbtn black" onclick="applyCancel('return')">반품신청</button>
        <?php } ?>
        <script>
        function approveCancel() {
          if (confirm('승인처리 하시겠습니까?')) {
            location.href = './orderinquirycancelapprove.php?od_id=<?php echo $od['od_id'] ?>';
          }
        }

        function rejectCancel() {
          if (confirm('거절처리 하시겠습니까?')) {
            location.href = './orderinquirycancelreject.php?od_id=<?php echo $od['od_id'] ?>';
          }
        }

        function applyCancel(to) {
          if (!$('input[name="od_cancel_memo"]').val()) {
            alert('사유를 입력해주세요.');
            return;
          }
          var ajax = $.ajax({
              method: "POST",
              url: './ajax.refund.php',
              data: {
                od_id: '<?php echo $od['od_id'] ?>',
                to: to,
                cancel_memo: $('input[name="od_cancel_memo"]').val(),
                request_reason_type: $('select[name="od_cancel_reason"]').val(),
              },
            })
            .done(function(data) {
              alert('반품신청되었습니다.');
              window.location.reload();
            });
        }
        </script>
      </div>
    </div>
    <div class="cancel">
      <div class="block-box cancel">
        <div class="om_cancel_write_box">
          <div class="om_cancel_header">
            <select name="od_cancel_reason"
              <?php echo $cancel_request_row['approved'] == 1 ? 'disabled="disabled"' : ''; ?>>
              <option value="단순변심" <?php echo $od['od_cancel_reason'] == '단순변심' ? 'selected' : ''; ?>>단순변심</option>
              <option value="제품파손" <?php echo $od['od_cancel_reason'] == '제품파손' ? 'selected' : ''; ?>>제품파손</option>
              <option value="제품하자" <?php echo $od['od_cancel_reason'] == '기타' ? 'selected' : ''; ?>>제품하자</option>
              <option value="오주문" <?php echo $od['od_cancel_reason'] == '오주문' ? 'selected' : ''; ?>>오주문</option>
              <option value="오배송" <?php echo $od['od_cancel_reason'] == '오배송' ? 'selected' : ''; ?>>오배송</option>
              <option value="A/S" <?php echo $od['od_cancel_reason'] == 'A/S' ? 'selected' : ''; ?>>A/S</option>
              <option value="기타" <?php echo $od['od_cancel_reason'] == '기타' ? 'selected' : ''; ?>>기타</option>
            </select>
            <div id="g5_shop_order_cancel_file">
              <?php if ($cancel_request_row['approved'] != 1) { ?>
              <button type="button" class="shbtn uploadbtn">찾아보기</button>
              <?php } ?>
              <ul class="upload_files upload_files_cancel_apply">
                <?php
                                $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'cancel_apply'";
                                $result = sql_query($sql);
                                while ($row = sql_fetch_array($result)) {
                                ?>
                <li>
                  <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink"
                    target="_blank"><?php echo $row['real_name']; ?></a>
                  <a href='#' class="remove" data-no="<?php echo $row['no']; ?>"><img
                      src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_del_s.png" /></a>
                </li>
                <?php } ?>
              </ul>
            </div>
          </div>
          <input name="od_cancel_memo" rows="8" placeholder="입력한 메모내용이 보여집니다."
            value="<?php echo get_text($od['od_cancel_memo']); ?>" id="cancel_memo_content"
            <?php echo $cancel_request_row['approved'] == 1 ? 'disabled="disabled"' : ''; ?> />
          <?php if ($cancel_request_row['approved'] == 1) { ?>
          <div class="refund">
            <h3>환불진행</h3>
            <textarea name="refund_memo" rows="8"
              placeholder="메모를 입력하세요."><?php echo $cancel_request_row['refund_memo']; ?></textarea>
            <ul>
              <li>
                <span>- 진행상태:</span>
                <select name="refund_status">
                  <option value="" <?php echo !$cancel_request_row['refund_status'] ? 'selected="selected"' : ''; ?>>없음
                  </option>
                  <option value="회수요청"
                    <?php echo $cancel_request_row['refund_status'] === '회수요청' ? 'selected="selected"' : ''; ?>>회수요청
                  </option>
                  <option value="회수완료 및 검수"
                    <?php echo $cancel_request_row['refund_status'] === '회수완료 및 검수' ? 'selected="selected"' : ''; ?>>
                    회수완료 및 검수</option>
                  <option value="검수완료"
                    <?php echo $cancel_request_row['refund_status'] === '검수완료' ? 'selected="selected"' : ''; ?>>검수완료
                  </option>
                </select>
                * 검수완료시 환불금액만큼 청구내역에서 차감됩니다.
              </li>
              <li>
                <span>- 환불금액:</span>
                <input type="text" name="refund_price"
                  value="<?php echo number_format($cancel_request_row['refund_price']); ?>" /> 원
                &nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="refund_price_all" id="refund_price_all" value="<?php echo $tot_total; ?>"
                  <?php echo $tot_total == $cancel_request_row['refund_price'] ? 'checked="checked"' : ''; ?>>
                <label for="refund_price_all">전액환불(배송비 제외)</label>
              </li>
            </ul>
          </div>
          <div style="text-align:right;">
            <input type="button" value="적용" class="btn shbtn" id="refund_submit">
          </div>
          <?php } ?>
          <!--<input type="button" value="반영" class="btn shbtn" id="cancel_submit">-->
        </div>
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
            $logs = get_order_admin_log($od['od_id']);
            foreach($logs as $log) {
                $log_mb = get_member($log['mb_id']);
                echo '<span class="log_datetime">'.$log['ol_datetime'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['ol_content'] . '<br/>';
            }
            if (!count($logs)) {
                echo '기록이 없습니다.';
            }
            ?>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>바코드 기록</h2>
      <div class="right">
      </div>
    </div>
    <div class="block-box gray logs">
      <?php
            $logs = get_barcode_log($od['od_id']);
            foreach($logs as $log) {
                $log_mb = get_member($log['mb_id']);
                echo '<span class="log_datetime">'.$log['b_date'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['b_content'] . '<br/>';
            }
            if (!count($logs)) {
                echo '기록이 없습니다.';
            }
            ?>
    </div>
  </div>

  <div class="block">
    <div class="header">
      <h2>배송 기록</h2>
      <div class="right">
      </div>
    </div>
    <div class="block-box gray logs">
      <?php
        $cnt_delivery_log = 0; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
        // $logs = get_delivery_log($od['od_id']);
        $logs = get_delivery_log_re($od['od_id']); // 중복 없이 배송 기록(로그)를 가져오는 함수로 변경
        $last_log = [];
        foreach($logs as $log) {
          $log_mb = get_member($log['mb_id']);
          //아이템 검색
          $sql_ct = "select * from g5_shop_cart where ct_id = '".$log['ct_id']."'";
          $result_ct = sql_fetch($sql_ct);

          //아이템 이름
          $it_name = $result_ct['it_name'];
          if(str_replace(' ', '', $result_ct['ct_option']) != str_replace(' ', '', $result_ct['it_name'])) { $it_name .="(".$result_ct['ct_option'].")"; }

          //택배사
          $delivery_company="";
          foreach($delivery_companys as $data){ 
            if($log["ct_delivery_company"] == $data["val"] ) {
              $delivery_company = "(".$data["name"].")";
            }
          }
          //직배송
          $direct_delivery = "";
          if($log["ct_is_direct_delivery"] == "1") {
            $direct_delivery = "[위탁:배송]";
          }
          else if($log["ct_is_direct_delivery"] == "2") {
            $direct_delivery = "[위탁:설치]";
          }

          //합포
          $combine="";
          if($log["ct_combine_ct_id"]) {
            //합포 검색
            $sql_ct_p = "select * from g5_shop_cart where ct_id = '".$log['ct_combine_ct_id']."'";
            $result_ct_p = sql_fetch($sql_ct_p);
            //합포 아이템 이름
            $it_name_p = $result_ct_p['it_name'];
            if($result_ct_p['ct_option']){ $it_name_p .= "(".$result_ct_p['ct_option'].")"; }

            $combine="합포 - ".$it_name_p."";
          }

          /*
          // 이전 로그와 비교
          if($last_log[$log['ct_id']]) {
            // 합포비교
            if($last_log[$log['ct_id']]['ct_combine_ct_id'] != $log['ct_combine_ct_id']) {
              if($log['ct_combine_ct_id']) {
                // 합포적용
                echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 합포정보 입력 : {$it_name} 상품을 {$it_name_p} 상품에 합포적용했습니다.<br/>";
              } else {
                // 합포해지
                echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 합포정보 입력 : {$it_name} 상품을 합포해지했습니다.<br/>";
              }
            }

            // 위탁비교
            if($last_log[$log['ct_id']]['ct_is_direct_delivery'] != $log['ct_is_direct_delivery']) {
              if($log['ct_is_direct_delivery']) {
                // 위탁 적용
                $direct_delivery_type = '';
                if($log["ct_is_direct_delivery"] == "1") {
                  $direct_delivery_type = '배송';
                } else if($log["ct_is_direct_delivery"] == "2") {
                  $direct_delivery_type = '설치';
                }
                echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 위탁정보 입력 : {$it_name} 상품을 위탁 적용했습니다. ({$direct_delivery_type}/{$log['ct_direct_delivery_partner']}/1개당 {$log['ct_direct_delivery_price']}원)<br/>";
              } else {
                // 위탁해지
                echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 위탁정보 입력 : {$it_name} 상품을 위탁 해지했습니다.<br/>";
              }
            }
          } else {
          */
            // 비교할 이전 로그가 없으면 자체 정보로 비교
            if(!$log['was_combined'] && $log['ct_combine_ct_id']) {
              // 합포적용
              echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 합포정보 입력 : {$it_name} 상품을 {$it_name_p} 상품에 합포적용했습니다.<br/>";
              $cnt_delivery_log++; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
            } else if($log['was_combined'] && !$log['ct_combine_ct_id']) {
              // 합포해지
              echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 합포정보 입력 : {$it_name} 상품을 합포해지했습니다.<br/>";
              $cnt_delivery_log++; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
            }
  
            if(!$log['was_direct_delivery'] && $log['ct_is_direct_delivery']) {
              // 위탁적용
              $direct_delivery_type = '';
              if($log["ct_is_direct_delivery"] == "1") {
                $direct_delivery_type = '배송';
              } else if($log["ct_is_direct_delivery"] == "2") {
                $direct_delivery_type = '설치';
              }
              echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 위탁정보 입력 : {$it_name} 상품을 위탁 적용했습니다. ({$direct_delivery_type}/{$log['ct_direct_delivery_partner']}/1개당 {$log['ct_direct_delivery_price']}원)<br/>";
              $cnt_delivery_log++; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
            } else if($log['was_direct_delivery'] && !$log['ct_is_direct_delivery']) {
              // 위탁해지
              echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . " 매니저) 위탁정보 입력 : {$it_name} 상품을 위탁 해지했습니다.<br/>";
              $cnt_delivery_log++; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
            }
          /*}*/

          if($log['ct_combine_ct_id']) {
            echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) 배송정보 입력 : '.$delivery_company.' '.$it_name.' ['. $combine.'] '.$direct_delivery.'<br/>';
            $cnt_delivery_log++; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
          } else {
            if($log['ct_delivery_num']){ // 빈 송장번호가 넘어오면 배송기록을 출력하지 못하게 한다.
                echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) 배송정보 입력 : '.$delivery_company.' '.$it_name.' 송장번호['. $log['ct_delivery_num'].'] '.$direct_delivery.'<br/>';
                $cnt_delivery_log++; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
            }
          }

          if ($log['set_warehouse']) {
            echo '<span class="log_datetime">'.$log['d_date'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) '.$log['d_content'].' 저장<br/>';
            $cnt_delivery_log++; // 배송 기록이 몇개나 출력되었는지 확인하는 카운터
          }

          $last_log[$log['ct_id']] = $log;
        }
        if (!count($logs) || $cnt_delivery_log == 0) {
          // 조회된 배송 로그 수가 0이거나
          // 출력된 배송 로그 수가 0이면
          echo '기록이 없습니다.';
        }
        ?>
    </div>
  </div>

  <div id="order_summarize">
    <div class="header">
      <!-- <h1><?=$od_status['name']?></h1> -->
      <button class="shbtn order_prints">작업지시서 출력</button>
      <div class="more">
        <button><img src='<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_more_w.png' /></button>
        <ul class="openlayer">
          <?php if ( $od['od_status'] != '주문무효' ) { ?>
          <li id="order_cancel">주문무효 처리</li>
          <?php } ?>
          <li id="order_copy">주문서 복사</li>
          <?php if ( $prev_step ) { ?>
          <li id="order_prev_step" data-prev-step-val="<?php echo $prev_step['val']; ?>">
            <?php echo $prev_step['name']; ?>단계로 되돌리기</li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <div class="content">
      <?php if ( $od_status['val'] == '작성' && !$mb['mb_id'] ) { ?>
      <div class="change_member" id="od_change_member">
        <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_order_member.png" /></a>
      </div>
      <?php } ?>
      <div class="block">
        <h2>주문번호 <?php echo $od['od_id']; ?></h2>
        <span class="so_nb"> SO-NB <?php echo $od['so_nb']; ?></span>
      </div>
      <div class="block">
        <?php if($mb['mb_id']) { ?>
        <a href="<?php echo G5_ADMIN_URL; ?>/member_form.php?&w=u&mb_id=<?php echo $mb['mb_id']; ?>" target="_blank"
          class="h2">
          <?php echo $mb['mb_name']; ?><span>(<?php echo $mb['mb_temp'] ? '임시회원' : $mb['mb_id']; ?>)</span>
        </a>
        <?php }else{ ?>
        <a href="#" class="h2">비회원</a>
        <?php } ?>
        <?php echo $od['od_send_admin_memo'] ?>
        <p>
          <?php if ( $od['od_deposit_name'] ) { ?>
          <?php echo $od['od_deposit_name']; ?>, <?php echo $od['od_bank_account']; ?><br />
          <?php } ?>
          <?php echo $od['od_name']; ?> (<?php echo $od['od_email']; ?>)<br />
          HP : <?php echo $od['od_hp']; ?> / Tel : <?php echo $od['od_tel']; ?>
        </p>
        <?php
                $customer_code = get_customer_code($od['od_id']);
                $customer_code_step = get_customer_step($customer_code);
                ?>
        고객코드: <?php echo $customer_code; ?> (<?php echo $customer_code_step; ?>)
        <br /><br />
        <a class="shbtn send_estimate">
          견적서 전송
        </a>
      </div>
      <div class="block">
        <h2>담당자</h2>
        <ul>
          <li>
            <div class="managers">
              <span class="manager_name">- 영업담당자</span>
              <!--
                            <div class="on">
                                <select name="od_sales_manager">
                                    <option value="">없음</option>
                                    <?php
                                    $sql = "SELECT * FROM g5_auth WHERE au_menu = '400400' AND au_auth LIKE '%w%'";
                                    $auth_result = sql_query($sql);
                                    while($a_row = sql_fetch_array($auth_result)) {
                                        $a_mb = get_member($a_row['mb_id']);
                                    ?>
                                        <option value="<?php echo $a_mb['mb_id']; ?>" <?php echo $a_mb['mb_id'] == $od['od_sales_manager'] ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <a class="change_manager_on change_manager_submit" data-type="od_sales_manager">변경</a>
                                <a class="change_manager_on change_manager_cancel">취소</a>
                            </div>
                            <div class="off">
                                <?php
                                $od_sales_manager = get_member($od['od_sales_manager']);
                                if ($od_sales_manager) {
                                    echo $od_sales_manager['mb_name']; ?> 담당자 <a class="change_manager_off">변경</a>
                                <?php
                                } else {
                                ?>
                                    <a class="change_manager_off">선택</a>
                                <?php } ?>
                            </div>
                            -->
              <select name="od_sales_manager">
                <option value="">없음</option>
                <?php
                                    $od_sales_manager = $od['od_sales_manager'];
                                    if (!$od_sales_manager || $od_sales_manager == '1202') {
                                        $sql_manager = "SELECT `mb_manager` FROM `g5_member` WHERE `mb_id` ='".$od['mb_id']."'";
                                        $result_manager = sql_fetch($sql_manager);
                                        $od_sales_manager = $result_manager['mb_manager'];
                                    }
                                    // echo $od_sales_manager['mb_name'] ? $od_sales_manager['mb_name'] : '없음';

                                    $sql = " SELECT mb_name, mb_id FROM g5_member WHERE mb_level = 9 ORDER BY mb_name ASC ";
                                    $auth_result = sql_query($sql);
                                    while($a_row = sql_fetch_array($auth_result)) {
                                        $a_mb = get_member($a_row['mb_id']);
                                ?>
                <option value="<?php echo $a_mb['mb_id']; ?>"
                  <?php echo $a_mb['mb_id'] == $od_sales_manager ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?>
                </option>
                <?php } ?>
              </select>
              <a class="change_manager_on change_manager_submit" data-type="od_sales_manager">변경</a>
            </div>
          </li>


          <!-- <li>
                        <span class="manager_name">- 출고담당자</span>
                        <div class="managers">
                            <div class="on">
                                <select name="od_release_manager">
                                    <option value="">미지정</option>
                                    <option value="no_release" <?php echo 'no_release' == $od['od_release_manager'] ? 'selected' : ''; ?>>출고아님</option>
                                    <option value="-" <?php echo '-' == $od['od_release_manager'] ? 'selected' : ''; ?>>외부출고</option>
                                    <?php
                                    $sql = "SELECT * FROM g5_auth WHERE au_menu = '400402' AND au_auth LIKE '%w%'";
                                    $auth_result = sql_query($sql);
                                    while($a_row = sql_fetch_array($auth_result)) {
                                        $a_mb = get_member($a_row['mb_id']);
                                    ?>
                                        <option value="<?php echo $a_mb['mb_id']; ?>" <?php echo $a_mb['mb_id'] == $od['od_release_manager'] ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <a class="change_manager_on change_manager_submit" data-type="od_release_manager">변경</a>
                                <a class="change_manager_on change_manager_cancel">취소</a>
                            </div>
                            <div class="off">
                                <?php
                                $od_release_manager = get_member($od['od_release_manager']);
                                if ($od_release_manager) {
                                    echo $od_release_manager['mb_name']; ?> 담당자 <a class="change_manager_off">변경</a>
                                <?php } else if ($od['od_release_manager'] == 'no_release') { ?>
                                    <span style="color: #ff3061;">출고아님</span> <a class="change_manager_off">변경</a>
                                <?php } else if ($od['od_release_manager'] == '-') { ?>
                                    외부출고 <a class="change_manager_off">변경</a>
                                <?php } else { ?>
                                    <a class="change_manager_off">선택</a>
                                <?php } ?>
                            </div>
                        </div>
                    </li> -->


        </ul>
      </div>
      <div class="block">
        <h2>결제정보</h2>
        <form>
          <ul class="send_cost_sales_discount_wrapper">
            <li>
              <span>배송비</span><input class="od_send_cost" type="number" value="<?=$od['od_send_cost']?>">원
            </li>
            <li>
              <span>매출할인</span><input class="od_sales_discount" type="number" value="<?=$od['od_sales_discount']?>">원
            </li>
            <li>
              <span>추가배송비</span><input class="od_send_cost2" type="number" value="<?=$od['od_send_cost2']?>">원
            </li>
            <button id="change_send_cost_sales_discount" type="button">적용</button>
          </ul>
        </form>
        <ul class="bill_info">
          <!--
                    <li>
                        <div class="left">주문금액</div>
                        <div class="right"><?php echo number_format($amount['order']); ?>원</div>
                    </li>
                    <li>
                        <div class="left">총결제액</div>
                        <div class="right"><?php echo number_format($amount['receipt']); ?>원</div>
                    </li>
                    <li>
                        <div class="left">취소금액</div>
                        <div class="right"><?php echo number_format($amount['cancel']); ?>원</div>
                    </li>
                    -->
          <li>
            <div class="left">판매금액</div>
            <div class="right"><?php echo number_format($tot_price); ?>원</div>
          </li>
          <li>
            <div class="left">배송비</div>
            <div class="right"><?php echo number_format($od['od_send_cost']); ?>원</div>
          </li>
          <li>
            <div class="left">추가배송비</div>
            <div class="right"><?php echo number_format($od['od_send_cost2']); ?>원</div>
          </li>
          <li>
            <div class="left">할인</div>
            <div class="right"><span class="red"> <?php echo number_format($tot_discount); ?>원</span></div>
          </li>
          <li>
            <div class="left">쿠폰할인</div>
            <div class="right"><span class="red"><?php echo number_format($amount['coupon']); ?>원</span></div>
          </li>
          <li>
            <div class="left">포인트결제</div>
            <div class="right"><span class="red"><?php echo number_format($od['od_receipt_point']); ?>원</span></div>
          </li>
          <li>
            <div class="left">매출할인</div>
            <div class="right"><span class="red"> <?php echo number_format($od['od_sales_discount']); ?>원</span></div>
          </li>
          <li>
            <div class="left"><b>총금액</b></div>
            <div class="right">
              <b><?php echo number_format($tot_total + $od['od_send_cost'] + $od['od_send_cost2'] + $od['od_cart_discount2'] - $od['od_sales_discount'] - $amount['coupon'] - $od['od_receipt_point']); ?>원</b>
            </div>
          </li>
        </ul>
      </div>
      <div class="block">
        <div class="oneline">
          <div class="left">결제상태</div>
          <div class="left">
            <?php echo $od['od_pay_state'] == '1' ? '결제완료' : ($od['od_pay_state'] == '2' ? '결제후 출고' : '미결제'); ?>
            (<?php echo $s_receipt_way; ?>)
          </div>
        </div>
        <!-- <div class="oneline">
                    <div class="left">매출증빙</div>
                    <div class="left">
                        안녕하세요 <a class="view_maechul_zhengming">보기</a>
                    </div>
                </div> -->
      </div>
      <!--
            <div class="block">
                <h2>진행단계</h2>
                <?php
                $sub_menu_name = 'orderlist';
                $sub_menu_name = $sub_menu == '400400' ? 'orderlist' : $sub_menu_name;
                $sub_menu_name = $sub_menu == '400401' ? 'orderlist_complete' : $sub_menu_name;
                $sub_menu_name = $sub_menu == '400403' ? 'cancellist' : $sub_menu_name;
                $sub_menu_name = $sub_menu == '400402' ? 'deliverylist' : $sub_menu_name;
                ?>
                <table>
                    <tbody>
                        <tr>
                            <?php
                            foreach($order_steps as $order_step) {
                                if ( $order_step[$sub_menu_name] == true ) {
                                    echo '<th>'. $order_step['name'] .'</th>';
                                }
                            }
                            ?>
                        </tr>
                        <tr>
                            <?php
                            foreach($order_steps as $order_step) {
                                if ( $order_step[$sub_menu_name] == true ) {
                                    echo '<td>'. ( $cate_counts[$order_step['val']] ? $cate_counts[$order_step['val']] : 0 ) .'</td>';
                                }
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            -->
    </div>
    <?php if ( $next_step ) { ?>
    <!-- <div class="submit">
            <button id="order_summarize_submit" data-next-step-val="<?php echo $next_step['val']; ?>" data-next-step-status="<?php echo $next_step["status{$od["recipient_yn"]}"]; ?>">
                <?php echo $next_step['name']; ?>
            </button>
        </div> -->
    <?php } ?>
  </div>
</div>
<div class="btn_fixed_top">
  <?php if ($sub_menu == '400400') { ?>
  <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_orderlist.php" class="btn btn_02">목록</a>
  <?php } ?>
  <?php if ($sub_menu == '400401') { ?>
  <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_orderlist_complete.php" class="btn btn_02">목록</a>
  <?php } ?>
  <?php if ($sub_menu == '400402') { ?>
  <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_deliverylist.php" class="btn btn_02">목록</a>
  <?php } ?>
  <?php if ($sub_menu == '400403') { ?>
  <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_cancellist.php" class="btn btn_02">목록</a>
  <?php } ?>
  <a href="#" class="btn btn_01 order_prints">작업지시서 출력</a>
  <input type="button" value="주문내역 엑셀다운로드" onclick="orderListExcelDownload(1)" class="btn btn_02">
  <input type="button" value="바코드 엑셀다운로드" onclick="orderListExcelDownload(2)" class="btn btn_02">
</div>

<div id="popup_box">
  <div></div>
</div>

<style>
#popup_order_add {
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index: 999;
  background-color: rgba(0, 0, 0, 0.6);
  display: none;
}

#popup_order_add>div {
  width: 1000px;
  max-width: 80%;
  height: 80%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}

#popup_order_add>div iframe {
  width: 100%;
  height: 100%;
  border: 0;
  background-color: #FFF;
}
</style>
<div id="popup_order_add">
  <div>dd</div>
</div>

<script>
$(function() {
  $(document).on("DOMNodeInserted", '.mfp-content', function() {
    window.wheelzoom($('.mfp-img'));
  });
  $('.report-img-wrap').click(function() {
    window.wheelzoom($('.mfp-img'));
  });

  $(document).on("click", "#btn_order_edit", function(e) {
    e.preventDefault();

    $("#popup_order_add > div").html("<iframe src='./pop.order.edit.php?od_id=<?=$od_id?>'></iframe>");
    $("#popup_order_add iframe").load(function() {
      $("#popup_order_add").show();
      $('#hd').css('z-index', 3);
    });
  });
});
</script>

<script>
//주문내역 숨김처리
function hide_control(od_id, ct_hide_control) {
  $.ajax({
    method: "POST",
    url: "<?=G5_SHOP_URL?>/ajax.hide_control.php",
    data: {
      od_id: od_id,
      ct_hide_control: ct_hide_control
    }
  }).done(function(data) {
    if (data == "S1") {
      alert('숨김처리가 완료되었습니다.');
      window.location.reload();
    }
    if (data == "S2") {
      alert('보이기처리가 완료되었습니다.');
      window.location.reload();
    }
  })
}

var change_member_pop, add_item_pop, matching_item_pop, edit_item_pop, delivery_print_pop, edit_payment_pop,
  send_estimate_pop, order_prints_pop;

function orderListExcelDownload(number) {
  $("#excelForm").remove();
  if (number == 1) {
    var html = "<form id='excelForm' method='post' action='./order.excel.list.php'>";
    html += "<input type='hidden' name='ref' value='orderform'>";
  } else if (number == 2) {
    var html = "<form id='excelForm' method='post' action='./order.excel.list2.php'>";
  }
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

$(document).ready(function() {
  // 오른쪽 고정
  /*
  $("#order_summarize").sticky({
    topSpacing: 0,
    className: "fixed"
  });
    */
  // 주문품목 변경 버튼
  $('#btn_order_edit').click(function() {

  });

  $(document).on("click", ".prodBarNumCntBtn", function(e) {
    e.preventDefault();

    var popupWidth = 800;
    var popupHeight = 700;

    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY = (window.screen.height / 2) - (popupHeight / 2);

    window.open("./popup.prodBarNum.form.php?od_id=<?=$od["od_id"]?>&is_pop=true", "바코드 저장", "width=" +
      popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" +
      popupX);
  });

  $(document).on("click", ".deliveryCntBtn", function(e) {
    e.preventDefault();

    var popupWidth = 1200;
    var popupHeight = 700;

    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY = (window.screen.height / 2) - (popupHeight / 2);

    window.open("./popup.prodDeliveryInfo.form.php?od_id=<?=$od["od_id"]?>", "배송정보", "width=" + popupWidth +
      ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX);
  });

  $(".barNumCustomSubmitBtn").click(function() {
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

  $(".barNumGuideBox .closeBtn").click(function() {
    $(this).closest(".barNumGuideBox").hide();
  });

  $(".barNumGuideOpenBtn").click(function() {
    $(this).next().toggle();
  });

  var stoldList = [];
  var stoIdData = "<?=$stoIdData?>";
  /*if(stoIdData){
    var sendData = {
      stoId : stoIdData
    }

    $.ajax({
      url : "<?php echo EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP ?>",
      type : "POST",
      dataType : "json",
      contentType : "application/json; charset=utf-8;",
      data : JSON.stringify(sendData),
      success : function(res){
        $.each(res.data, function(key, value){
          $("." + value.stoId).val(value.prodBarNum);
        });

        if(res.data){
          stoldList = res.data;
        }
      }
    });
  }*/

  var offset = $('#order_summarize').offset();

  function fixed_container() {
    if ($(document).scrollTop() > offset.top) {
      $('#order_summarize').addClass('fixed');
    } else {
      $('#order_summarize').removeClass('fixed');
    }
  }

  $(window).scroll(function() {
    fixed_container();
  });
  fixed_container();

  // 담당자 변경
  $('.change_manager_off').click(function() {
    var off = $(this).closest('.off');
    var on = $(this).closest('.managers').find('.on');

    $(off).hide();
    $(on).show();
  });
  $('.change_manager_cancel').click(function() {
    var on = $(this).closest('.on');
    var off = $(this).closest('.managers').find('.off');

    $(on).hide();
    $(off).show();
  });
  $('.change_manager_submit').click(function() {
    var type = $(this).data('type');
    var mb_id = $('select[name="' + type + '"]').val();
    $.ajax({
        method: "POST",
        url: "./ajax.order.manager.php",
        data: {
          type: type,
          mb_id: mb_id,
          od_id: od_id,
        },
      })
      .done(function(data) {
        // console.log(data);
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }

      })
  });

  $('#order_prev_step').click(function() {
    var prev_step_val = $(this).data('prev-step-val');
    change_step(od_id, prev_step_val);
  });

  $('#order_cancel').click(function() {
    var step_val = '주문무효';
    change_step(od_id, step_val);
  });

  $('#order_copy').click(function() {
    if (confirm("주문서를 복사하시겠습니까?")) {
      location.href = "./samhwa_order_copy.php?od_id=" + od_id;
    }
  });

  $('#memo_submit').click(function() {
    var content = $('#memo_content').val();

    if (!content.length) {
      alert('메모 내용을 입력하세요.');
      return;
    }
    $.ajax({
        method: "POST",
        url: "./ajax.order.memo.php",
        data: {
          od_id: od_id,
          content: content,
        },
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  });

  // 전체 옵션선택
  $("#sit_select_all").click(function() {
    if ($(this).is(":checked")) {
      $("input[name='it_sel[]']").attr("checked", true);
      $("input[name^=ct_chk]").attr("checked", true);
    } else {
      $("input[name='it_sel[]']").attr("checked", false);
      $("input[name^=ct_chk]").attr("checked", false);
    }
  });

  // 상품의 옵션선택
  $("input[name='it_sel[]']").click(function() {
    var cls = $(this).attr("id").replace("sit_", "sct_");
    var $chk = $("input[name^=ct_chk]." + cls);
    if ($(this).is(":checked"))
      $chk.attr("checked", true);
    else
      $chk.attr("checked", false);
  });

  $('#change_cart_status').click(function() {
    let sendcost = parseInt($('.send_cost_sales_discount_wrapper .od_send_cost').val());

    var step = document.getElementById('step');
    var it_sel = document.getElementsByName("it_sel[]");
    var formdata = $.extend({},
      $('#frmsamhwaorderform').serializeObject(), {
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
    sendData['sendcost'] = sendcost;
    $.ajax({
      method: 'POST',
      url: '/shop/schedule/ajax.update_schedule_status.php',
      data: {
        ct_id: sendData['ct_id'],
        status: sendData['step'],
      },
    }).done(function() {
      $.ajax({
        type: "post",
        url: "./ajax.cart_status.php",
        data: sendData,
        success: function(data) {
          if (data === 'success') {
            alert('변경되었습니다.');
            location.reload();
          } else {
            alert(data);
          }
        }
      });
    }).fail(function() {
      alert('알 수 없는 문제 발생');
      return;
    });
    return false;
  });

  /* 주문다음단계 */
  //출고준비 -> 06
  //배송 ->06
  //완료 -> 01
  $("#order_summarize_submit").click(function() {
    var next_step_val = $(this).data("next-step-val"); //다음 스텝
    var next_step_status = $(this).data("next-step-status"); //다음 스텝번호
    var ordId = "<?=$od["ordId"]?>";
    var eformYn = (next_step_val == "완료") ? "Y" : "N";
    var changeStatus = true;
    var stateCd = "07";

    console.log(next_step_val);
    console.log(next_step_status);
    console.log(ordId);
    console.log(eformYn);
    console.log(changeStatus);


    switch (next_step_status) {
      case "01":
        stateCd = "07";
        break;
      case "03":
        stateCd = "02";
        break;
    }

    //수급자 주문시
    if (ordId) {
      var productList = <?=($prodList) ? json_encode($prodList) : "[]"?>;
      $.each(productList, function(key, value) {
        var prodBarNumItem = $(".prodBarNumItem_" + value.penStaSeq);
        var prodBarNum = "";

        for (var i = 0; i < prodBarNumItem.length; i++) {
          if (next_step_val == "완료") {
            if (!$(prodBarNumItem[i]).val()) {
              //alert("바코드를 입력해주시길 바랍니다.");
              //changeStatus = false;
              //return false;
            }
          }
          prodBarNum += (prodBarNum) ? "," : "";
          prodBarNum += $(prodBarNumItem[i]).val();
        }

        productList[key]["prodBarNum"] = prodBarNum;
        productList[key]["stateCd"] = stateCd;
      });

      if (!changeStatus) {
        return false;
      }
      var sendData = {
        usrId: "<?=$od["mb_id"]?>",
        penOrdId: "<?=$od["ordId"]?>",
        delGbnCd: "",
        ordWayNum: "",
        delSerCd: "",
        ordNm: $("#od_b_name").val(),
        ordCont: $("#od_b_hp").val(),
        ordMeno: $("#od_memo").val(),
        ordZip: $("#od_b_zip").val(),
        ordAddr: $("#od_b_addr1").val(),
        ordAddrDtl: $("#od_b_addr2").val(),
        eformYn: eformYn,
        staOrdCd: next_step_status,
        lgsStoId: "",
        prods: productList,
        entId: "<?=get_ent_id_by_od_id($od_id)?>"
      }
      // alert(next_step_status);
      // alert(stateCd);
      // console.log(sendData);
      // return false;


      $.ajax({
        url: "samhwa_orderform_order_update.php",
        type: "POST",
        async: false,
        data: sendData,
        success: function(result) {
          result = JSON.parse(result);
          if (result.errorYN == "Y") {
            alert(result.message);
          } else {
            change_step(od_id, next_step_val);
          }
        }
      });
    } else {
      //일반주문시
      var delYn = "Y";
      if (next_step_val == "완료") {
        delYn = "N";
        $.each(stoldList, function(key, value) {
          if (!$("." + value.stoId).val()) {
            //changeStatus = false;
            //alert("바코드를 입력해주시길 바랍니다.");
            //return false;
          }
        });
      }

      var prodsList = {};
      $.each(stoldList, function(key, value) {
        prodsList[key] = {
          stoId: value.stoId,
          prodColor: value.prodColor,
          prodSize: value.prodSize,
          prodBarNum: ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
          prodManuDate: value.prodManuDate,
          stateCd: next_step_status,
          stoMemo: (value.stoMemo) ? value.stoMemo : ""
        }
      });

      var sendData = {
        usrId: "<?=$od["mb_id"]?>",
        prods: prodsList,
        entId: "<?=get_ent_id_by_od_id($od_id)?>"
      }


      //임시 작업 cart_status update - 추후 cart table 기준으로 바꿀거 대비
      var send_ct_status = {};
      send_ct_status['od_id'] = '<?php echo $_GET['od_id']?>';
      send_ct_status['ct_status'] = next_step_val;
      $.ajax({
        url: "./ct_status_update.php",
        type: "POST",
        async: false,
        data: send_ct_status,
        success: function(result) {
          console.log(result);
          if (result == "N") {
            alert('주문서가 업데이트 되지 않았습니다.');
          }
        }
      });


      $.ajax({
        url: "samhwa_orderform_stock_update.php",
        type: "POST",
        async: false,
        data: sendData,
        success: function(result) {
          result = JSON.parse(result);
          if (result.errorYN == "Y") {
            alert(result.message);
          } else {
            change_step(od_id, next_step_val);
          }
        }
      });


    }


    //출고완료시, od_ex_date 값 변경
    if (next_step_val == "배송") {
      sendData2 = {};
      sendData2['od_id'] = "<?=$_GET['od_id']?>";
      console.log(sendData2);
      $.ajax({
        url: "./ajax.od_ex_date.php",
        type: "POST",
        async: false,
        data: sendData2,
        success: function(result) {
          if (result == "N") {
            alert('주문서가 없습니다.');
          }
        }
      });
    }
  });

  // 바코드정보저장
  $('#prodBarNumSaveBtn').click(function() {
    var ordId = "<?=$od["ordId"]?>";
    var changeStatus = true;

    if (ordId) {
      var productList = <?=($prodList) ? json_encode($prodList) : "[]"?>;
      $.each(productList, function(key, value) {
        var prodBarNumItem = $(".prodBarNumItem_" + value.penStaSeq);
        var prodBarNum = "";

        for (var i = 0; i < prodBarNumItem.length; i++) {
          prodBarNum += (prodBarNum) ? "," : "";
          prodBarNum += $(prodBarNumItem[i]).val();
        }

        productList[key]["prodBarNum"] = prodBarNum;
      });

      var sendData = {
        usrId: "<?=$od["mb_id"]?>",
        penOrdId: "<?=$od["ordId"]?>",
        delGbnCd: "",
        ordWayNum: "",
        delSerCd: "",
        ordNm: $("#od_b_name").val(),
        ordCont: $("#od_b_hp").val(),
        ordMeno: $("#od_memo").val(),
        ordZip: $("#od_b_zip").val(),
        ordAddr: $("#od_b_addr1").val(),
        ordAddrDtl: $("#od_b_addr2").val(),
        eformYn: "<?=$od["eformYn"]?>",
        staOrdCd: "<?=$od["staOrdCd"]?>",
        lgsStoId: "",
        prods: productList,
        entId: "<?=get_ent_id_by_od_id($od_id)?>"
      }

      $.ajax({
        url: "samhwa_orderform_order_update.php",
        type: "POST",
        async: false,
        data: sendData,
        success: function(result) {
          result = JSON.parse(result);
          if (result.errorYN == "Y") {
            alert(result.message);
          } else {
            alert("저장이 완료되었습니다.");
          }
        }
      });
    } else {
      var prodsList = {};

      $.each(stoldList, function(key, value) {
        prodsList[key] = {
          stoId: value.stoId,
          prodColor: value.prodColor,
          prodSize: value.prodSize,
          prodBarNum: ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
          prodManuDate: value.prodManuDate,
          stateCd: value.stateCd,
          stoMemo: (value.stoMemo) ? value.stoMemo : ""
        }
      });

      var sendData = {
        usrId: "<?=$od["mb_id"]?>",
        prods: prodsList,
        entId: "<?=get_ent_id_by_od_id($od_id)?>"
      }

      $.ajax({
        url: "samhwa_orderform_stock_update.php",
        type: "POST",
        async: false,
        data: sendData,
        success: function(result) {
          result = JSON.parse(result);
          if (result.errorYN == "Y") {
            alert(result.message);
          } else {
            alert("저장이 완료되었습니다.");
          }
        }
      });
    }
  });

  //배송정보 수정
  $('#delivery_info_btn').click(function() {
    var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
    var formdata = $.extend({},
      $('#frmsamhwaorderdeliveryform').serializeObject(), {
        od_id: od_id,
        od_delivery_type_data: od_delivery_type_data,
      }
    );

    $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.php",
        data: formdata,
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      });
    /*var ordId = "<?=$od["ordId"]?>";
        var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
        var formdata = $.extend(
            {},
            $('#frmsamhwaorderdeliveryform').serializeObject(),
            {
                od_id: od_id,
                od_delivery_type_data: od_delivery_type_data,
            }
        );
    var changeStatus = true;

    if(ordId){
      var productList = <?=($prodList) ? json_encode($prodList) : "[]"?>;
      $.each(productList, function(key, value){
        var prodBarNumItem = $(".prodBarNumItem_" + value.penStaSeq);
        var prodBarNum = "";

        for(var i = 0; i < prodBarNumItem.length; i++){
          prodBarNum += (prodBarNum) ? "," : "";
          prodBarNum += $(prodBarNumItem[i]).val();
        }

        productList[key]["prodBarNum"] = prodBarNum;
      });

      var sendData = {
        usrId : "<?=$od["mb_id"]?>",
        penOrdId : "<?=$od["ordId"]?>",
        delGbnCd : "",
        ordWayNum : "",
        delSerCd : "",
        ordNm : $("#od_b_name").val(),
        ordCont : $("#od_b_hp").val(),
        ordMeno : $("#od_memo").val(),
        ordZip : $("#od_b_zip").val(),
        ordAddr : $("#od_b_addr1").val(),
        ordAddrDtl : $("#od_b_addr2").val(),
        eformYn : "<?=$od["eformYn"]?>",
        staOrdCd : "<?=$od["staOrdCd"]?>",
        lgsStoId : "",
        prods : productList,
                entId : "<?=get_ent_id_by_od_id($od_id)?>"
      }

      $.ajax({
        url : "samhwa_orderform_order_update.php",
        type : "POST",
        async : false,
        data : sendData,
        success : function(result){
          result = JSON.parse(result);
          if(result.errorYN == "Y"){
            alert(result.message);
          } else {
            $.ajax({
                  method: "POST",
                  url: "./ajax.order.delivery.php",
                  data: formdata,
                })
            .done(function(data) {
              if ( data.msg ) {
                alert(data.msg);
              }
              if ( data.result === 'success' ) {
                location.reload();
              }
            });
          }
        }
      });
    } else {
      var prodsList = {};

      $.each(stoldList, function(key, value){
        prodsList[key] = {
          stoId : value.stoId,
          prodColor : value.prodColor,
          prodSize : value.prodSize,
          prodBarNum : ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
          prodManuDate : value.prodManuDate,
          stateCd : value.stateCd,
          stoMemo : (value.stoMemo) ? value.stoMemo : ""
        }
      });

      var sendData = {
        usrId : "<?=$od["mb_id"]?>",
        prods : prodsList,
                entId : "<?=get_ent_id_by_od_id($od_id)?>"
      }

      $.ajax({
        url : "samhwa_orderform_stock_update.php",
        type : "POST",
        async : false,
        data : sendData,
        success : function(result){
          result = JSON.parse(result);
          if(result.errorYN == "Y"){
            alert(result.message);
          } else {
          $.ajax({
                method: "POST",
                url: "./ajax.order.delivery.php",
                data: formdata,
              })
          .done(function(data) {
            if ( data.msg ) {
              alert(data.msg);
            }
            if ( data.result === 'success' ) {
      //        location.reload();
            }
          });
          }
        }
      });
    }
    */
  });

  // 배송 선택
  function selected_delivery_type() {
    var checked = $('#od_delivery_type').find(':selected').data('type');

    $('.delivery_block .delivery_types').hide();
    $('.delivery_block .delivery_types.' + checked).show();
    selected_delivery_company();
  }
  $('#od_delivery_type').change(function() {
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
  $('select[name="od_delivery_company[delivery]"]').change(function() {
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
  $('#od_change_member').click(function() {
    change_member_pop = window.open('./pop.order.change_member.php?od_id=' + od_id, "change_member_pop",
      "width=430, height=600, resizable = no, scrollbars = no");
  });

  // 견적서 발송
  $('.send_estimate').click(function() {
    send_estimate_pop = window.open('<?php echo G5_SHOP_URL; ?>/pop.estimate.php?od_id=' + od_id,
      "send_estimate", "width=730, height=800, resizable = no, scrollbars = no");
  });

  // 결제정보 수정
  $('#edit_payment').click(function() {
    edit_payment_pop = window.open('./pop.order.payment.edit.php?od_id=' + od_id, "edit_payment_pop",
      "width=750, height=900, resizable = no, scrollbars = no");
  });

  // 상품 추가
  $('#add_item').click(function() {
    add_item_pop = window.open('./pop.order.item.add.php?od_id=' + od_id, "add_item_pop",
      "width=1080, height=900, resizable = no, scrollbars = yes");
  });


  // 상품 수정
  $('.edit_item').click(function() {

    var it_id = $(this).data('it-id');
    var uid = $(this).data('uid');
    var memo = $(this).data('memo');

    edit_item_pop = window.open('./pop.order.item.add.option.php?w=1&od_id=' + od_id + '&it_id=' + it_id +
      '&uid=' + uid + "&memo=" + memo, "edit_item_pop",
      "width=1080, height=900, resizable = no, scrollbars = no");
  });
  // 상품 삭제
  $('.delete_item').click(function() {
    var it_id = $(this).data('it-id');
    var uid = $(this).data('uid');
    var ct_id = $(this).data('ct-id');

    var remove = true;
    <?php
            if ($od['od_penId']) {
                $ed = sql_fetch("SELECT * FROM `eform_document` WHERE od_id = '{$od['od_id']}' AND penId = '{$od['od_penId']}'");
                if ($ed['dc_status'] !== null) {
                    echo "remove = confirm('계약서가 작성되어있어서 변경 시 기존에 생성된 계약서가 삭제됩니다. 삭제하시겠습니까?');";
                }
            }
        ?>


    if (!remove) {
      return false;
    }

    $.ajax({
        method: "POST",
        url: "./ajax.order.item.delete.php",
        data: {
          od_id: od_id,
          it_id: it_id,
          uid: uid,
          ct_id: ct_id,
        },
      })
      .done(function(data) {
        console.log(data);
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  })

  // EDI 전송
  $('.delivery_edi').click(function() {
    var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
    var formdata = $.extend({},
      $('#frmsamhwaorderdeliveryform').serializeObject(), {
        od_id: od_id,
        od_delivery_type_data: od_delivery_type_data,
      }
    );

    $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.edi.php",
        data: formdata,
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  });

  // 송장 리턴
  $('.delivery_edi_return').click(function() {

    $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.edi.return.php",
        data: {
          od_id: od_id
        },
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  });

  //사방넷 배송정보 전송
  $('.delivery_sabangnet_return').click(function() {

    $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.sabangnet.return.php",
        data: {
          od_id: od_id
        },
      })
      .done(function(data) {
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
  $('.delivery_print').click(function() {
    var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
    var formdata = $.extend({},
      $('#frmsamhwaorderdeliveryform').serializeObject(), {
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
      .done(function(data) {
        if (data.msg && data.result !== 'success') {
          alert(data.msg);
        }
        if (data.result === 'success') {
          // location.reload();
          delivery_print_pop = window.open('./pop.order.delivery.print.php?od_id=' + od_id,
            "delivery_print_pop", "width=855, height=900, resizable = yes, scrollbars = yes");
        }
      })
    //delivery_print_pop = window.open('./pop.order.delivery.print.php?od_id=' + od_id, "delivery_print_pop", "width=835, height=900, resizable = no, scrollbars = no");
  });

  // 작업 지시서
  $('.order_prints').click(function(e) {
    // e.preventdefault();
    var it_id = "";
    var checkbox = $("input[name='it_sel[]']:checked");
    for (var i = 0; i < checkbox.length; i++) {
      it_id += (it_id) ? "," : "";
      it_id += it_id;
    }

    order_prints_pop = window.open('./pop.order.prints.php?od_id=' + od_id + '|', "order_prints_pop",
      "width=850, height=800, resizable = no, scrollbars = yes");
  });



  // 주문취소 파일첨부
  $(document).on("click", '#g5_shop_order_cancel_file .uploadbtn', function() {

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

  $(document).on("change", '.g5_shop_order_file_cancel_apply', function() {

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
      .done(function(data) {

        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          var ret = '';

          for (var i = 0; i < data.data.length; i++) {
            ret += '<li>';
            ret += '<a href="/data/order_cart/' + data.data[i]['file_name'] +
              '" class="filelink" target="_blank">' + data.data[i]['real_name'] + '</a>&nbsp;';
            ret += '<a class="remove" data-no="' + data.data[i]['no'] +
              '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
            ret += '</li>';
          }

          $('.upload_files_cancel_apply').html(ret);
        }
      })

  });

  $(document).on("click", '.upload_files_cancel_apply .remove', function() {

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
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          $(obj).closest('li').remove();
        }
      });

  });

  // 주문취소 신청 버튼
  $('#cancel_submit').click(function() {
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
      .done(function(data) {
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
  $('#typereceipt2').click(function() {
    if ($(this).is(':checked')) {
      $('#typereceipt2_view').show();
      $('#typereceipt1_view').hide();
    }
  });
  $('#typereceipt1').click(function() {
    if ($(this).is(':checked')) {
      $('#typereceipt1_view').show();
      $('#typereceipt2_view').hide();
    }
  });
  $('#typereceipt0').click(function() {
    if ($(this).is(':checked')) {
      $('#typereceipt1_view').hide();
      $('#typereceipt2_view').hide();
    }
  });

  $('.typereceipt_cuse').click(function() {
    var val = $(this).val();

    if (val == 1) {
      $('.personallay').show();
      $('.businesslay').hide();
    } else {
      $('.personallay').hide();
      $('.businesslay').show();
    }
  });

  $('.typereceipt_before_btn').click(function() {
    $('.typereceipt_before').hide();
    $('.typereceipt_after').show();

    var v = $("input[name='ot_typereceipt']:checked");
    $("input[name='ot_typereceipt']:checked").click();

    console.log(v.val());

    if (v.val() === 31) {
      $("input[name='ot_typereceipt_cuse']:checked").click();
    }


  });

  $('.typereceipt_after_btn').click(function() {
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

  $('.typereceipt_after_submit').click(function() {
    submit_typereceipt_after();
  });

  $('#od_b_tel, #od_b_hp, #ot_btel, input[name="p_typereceipt_btel"]').on('keyup', function() {
    var num = $(this).val();
    num.trim();
    this.value = auto_phone_hypen(num);
  });
  $('input[name="p_typereceipt_bnum"], #ot_bnum').on('keyup', function() {
    var num = $(this).val();
    num.trim();
    this.value = auto_saup_hypen(num);
  });



  $('.pay-state').click(function() {
    $.ajax({
        method: "POST",
        url: "./ajax.order.paystate.toggle.php",
        data: {
          od_id: od_id,
        },
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  });

  // 추가배송비 적용
  $('#change_send_cost2').click(function() {
    var od_send_cost2 = parseInt($('#od_send_cost2').val());

    //2020-09-07 (-) 적용

    //if ( od_cart_discount2 < 0 ) {
    //    alert('추가할인 금액은 0보다 작은금액을 입력하실 수 없습니다.');
    //    return false;
    //}

    $.ajax({
        method: "POST",
        url: "./ajax.order.change_sendcost2.php",
        data: {
          od_id: od_id,
          od_send_cost2: od_send_cost2,
        },
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  });

  $('#change_send_cost_sales_discount').click(function() {
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
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  });


  // 외부발주 파일첨부
  $(document).on("click", '.it_outsourcing_option_file .uploadbtn', function() {

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

  $(document).on("change", '.it_outsourcing_option_file_apply', function() {

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
      .done(function(data) {

        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          var ret = '';

          for (var i = 0; i < data.data.length; i++) {
            ret += '<li>';
            ret += '<a href="/data/order_cart/' + data.data[i]['file_name'] +
              '" class="filelink" target="_blank">' + data.data[i]['real_name'] + '</a>&nbsp;';
            ret += '<a class="remove" data-no="' + data.data[i]['no'] +
              '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
            ret += '</li>';
          }

          // $('.upload_files_outsourcing_option_apply_' + it_id).html(ret);
          $('.upload_files_outsourcing_option_apply_' + uid).html(ret);
        }
      })
  });

  $(document).on("click", '.upload_files_outsourcing_option_apply .remove', function() {

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
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }

        if (data.result === 'success') {
          $(obj).closest('li').remove();
        }
      });

  });

  // 외부 발주 요청
  $('.item_outsourcing_submit').click(function() {

    var parent = $(this).closest('td');
    var it_id = $(parent).data('id');
    var uid = $(parent).data('uid');
    var it_outsourcing_option = $(parent).find('select[name="it_outsourcing_option"]').val();
    var it_outsourcing_option2 = $(parent).find('select[name="it_outsourcing_option2"]').val();
    var it_outsourcing_option3 = $(parent).find('select[name="it_outsourcing_option3"]').val();
    var it_outsourcing_option4 = $(parent).find('select[name="it_outsourcing_option4"]').val();
    var it_outsourcing_option5 = $(parent).find('select[name="it_outsourcing_option5"]').val();
    var sales_manager = $(parent).find('select[name="sales_manager"]').val();

    if (!it_id) {
      alert('알수없는 오류입니다.');
      return false;
    }

    $.ajax({
        method: "POST",
        url: "./ajax.order.outsourcing.php",
        data: {
          od_id: od_id,
          it_id: it_id,
          uid: uid,
          it_outsourcing_option: it_outsourcing_option,
          it_outsourcing_option2: it_outsourcing_option2,
          it_outsourcing_option3: it_outsourcing_option3,
          it_outsourcing_option4: it_outsourcing_option4,
          it_outsourcing_option5: it_outsourcing_option5,
          sales_manager: sales_manager,
        },
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      });

  });

  // 외부 발주 취소
  $('.item_outsourcing_cancel').click(function() {

    var oo_id = $(this).data('id');

    if (!oo_id) {
      alert('알수없는 오류입니다.');
      return false;
    }

    $.ajax({
        method: "POST",
        url: "./ajax.order.outsourcing.cancel.php",
        data: {
          od_id: od_id,
          oo_id: oo_id,
        },
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      });

  });

  // 배송정보 기본정보 반영
  $('#reset_od_info').click(function() {

    $.ajax({
        method: "POST",
        url: "./ajax.order.delivery.reset.php",
        data: {
          od_id: od_id
        },
      })
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      })
  });

  $('#customer_code_sel').change(function() {
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
      .done(function(data) {
        if (data.msg) {
          alert(data.msg);
        }
        if (data.result === 'success') {
          location.reload();
        }
      });
  });

  // 배송지 목록
  $('#address_list').click(function() {
    var url = "<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderaddress.php?mb_id=<?=$od['mb_id']?>";
    window.open(url, "win_address", "left=100,top=100,width=800,height=600,scrollbars=1");
    return false;
  });

  // 설치결과보고서 작성 버튼
  $("#popup_box").hide();
  $("#popup_box").css("opacity", 1);

  // 미매칭
  $('.install_report_match').click(function(e) {
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
      .done(function(data) {
        alert('매칭되었습니다.');
        location.reload();
      })
      .fail(function() {
        alert('반영에 실패하였습니다.');
      })
  });

  // 환불
  $(document).on("click", "#refund_price_all", function(e) {
    if ($(this).is(":checked")) {
      $('input[name="refund_price"]').val(addComma($(this).val()));
      return;
    }
    $('input[name="refund_price"]').val(0);
  });

  $(document).on("input propertychange paste", "input[name='refund_price']", function(e) {
    var input = $(this).val().replace(/[\D\s\._\-]+/g, "");

    if (input !== '') {
      input = input ? parseInt(input, 10) : 0;
      $(this).val(input.toLocaleString());
    } else {
      $(this).val('');
    }
  });

  $(document).on("click", "#refund_submit", function(e) {
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
      .done(function(data) {
        alert('환불내용이 변경되었습니다.');
        window.location.reload();
      });
  })

  $(document).on("change keyup paste", "select[name='refund_status']", function(e) {
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
  window.open(
    "https://admin8.kcp.co.kr/assist/bill.BillAction.do?cmd=card_bill&C_TRADE_NO=43A1DA77F005F7EF5F49B1E1D4AFE3FC",
    "kcpwindow", "width=400,height=600")
}

function submit_typereceipt_after(msgFlag) {
  var formdata = $.extend({},
    $('#typereceipt_after').serializeObject(), {
      od_id: od_id,
    }
  );

  $.ajax({
      method: "POST",
      url: "./ajax.order.typereceipt.php",
      data: formdata,
    })
    .done(function(data) {
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

$(".ct_manager").change(function() {

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
      .done(function(data) {
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
        let $btn_download;
        if (item._src) {
          $btn_download = $('<a class="btn-bottom btn-download">다운로드</a>')
            .attr('href', item._src)
            .attr('download', '설치파일_' + item.index + '.pdf');
        } else {
          $btn_download = $('<a class="btn-bottom btn-download">다운로드</a>')
            .attr('href', item.src)
            .attr('download', '설치이미지_' + item.index + '.jpg');
        }

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

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
