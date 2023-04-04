<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
  require_once(G5_SHOP_PATH.'/yc/orderinquiryview.php');
  return;
}
$reload = false;

$od_id = isset($od_id) ? preg_replace('/[^A-Za-z0-9\-_]/', '', strip_tags($od_id)) : 0;

if( isset($_GET['ini_noti']) && !isset($_GET['uid']) ){
  goto_url(G5_SHOP_URL.'/orderinquiry.php');
}

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

if (!$is_member) {
  if (get_session('ss_orderview_uid') != $_GET['uid'])
    alert("직접 링크로는 주문서 조회가 불가합니다.\\n\\n주문조회 화면을 통하여 조회하시기 바랍니다.", G5_SHOP_URL);
}

$sql = "select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' AND od_del_yn = 'N' ";
if($is_member && !$is_admin)
  $sql .= " and mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if (!$od['od_id'] || (!$is_member && md5($od['od_id'].$od['od_time'].$od['od_ip']) != get_session('ss_orderview_uid'))) {
  alert("조회하실 주문서가 없습니다.", G5_SHOP_URL);
}


// 결제방법
$settle_case = $od['od_settle_case'];

// 주문상품
$item = array();
$arr_it_orderform = array();
$deliveryItem = array();

$st_count1 = $st_count2 = $st_count3 = 0;
$custom_cancel = false; // 결제완료 전 결제 취소
$pay_complete_cancel = false; // 결제완료에서 결제 취소 (무통장 제외)
$pay_complete_cancel2 = false; // 결제완료에서 결제 취소 요청 (무통장)
$preparation_cancel = false; // 상품준비, 출고준비 단계에서 결제 취소 요청
$shipped_cancel = false; // 출고완료, 배송완료 단계에서 반품 요청
$tot_price = 0;

$sql = " select a.it_id,
        a.it_name,
        a.ct_send_cost,
        a.it_sc_type,
        a.pt_it,
        a.pt_id,
        a.pt_send,
        a.pt_send_num,
        a.ct_status,
        b.ca_id,
        b.ca_id2,
        b.ca_id3,
        b.pt_msg1,
        b.pt_msg2,
        b.pt_msg3,
        a.ct_uid,
        b.it_model,
        a.prodMemo,
        a.prodSupYn,
        a.ct_stock_qty,
        b.it_img1,
        a.ordLendStrDtm,
        a.ordLendEndDtm,
        b.it_rental_price,
        a.ct_delivery_yn,
        a.ct_delivery_company,
        a.ct_delivery_num,
        a.ct_discount
      from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
      where a.od_id = '$od_id'
      group by a.it_id, a.ct_uid
      order by a.ct_id ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {

  if($row["ct_delivery_yn"] == "Y"){
    $deliveryItem[$i] = $row;
  }

  $item[$i] = $row;
  $item[$i]["thumbnail"] = $row["it_img1"];

  $sql = " select ct_id, mb_id, it_name, ct_option, ct_qty, ct_stock_qty, ct_price, ct_point, ct_status, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_uid, ct_discount, ct_delivery_num, ct_delivery_company, ct_is_direct_delivery, ct_direct_delivery_date,ct_type
        , ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn
        , ( SELECT it_taxInfo FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_taxInfo
        from {$g5['g5_shop_cart_table']} MT
        where od_id = '$od_id'
          and it_id = '{$row['it_id']}'
          and ct_uid = '{$row['ct_uid']}'
        order by io_type asc, ct_id asc ";
  $res = sql_query($sql);

  $item[$i]['rowspan'] = sql_num_rows($res) + 1;

  // 상품유형
  $item[$i]['pt_it'] = apms_pt_it($row['pt_it'],1);

  // 합계금액 계산
  $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
          SUM(ct_qty) as qty
        from {$g5['g5_shop_cart_table']}
        where it_id = '{$row['it_id']}'
          and od_id = '$od_id'
          and ct_uid = '{$row['ct_uid']}'";
  $sum = sql_fetch($sql);

  // 배송비
  switch($row['ct_send_cost'])
  {
    case 1:
      $ct_send_cost = '착불';
      break;
    case 2:
      $ct_send_cost = '무료';
      break;
    default:
      $ct_send_cost = '선불';
      break;
  }

  // 조건부무료
  if($row['it_sc_type'] == 2) {
    $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $od_id);

    if($sendcost == 0)
      $ct_send_cost = '무료';
  }

  $item[$i]['ct_send_cost'] = $ct_send_cost;

  // 판매자
  if(USE_PARTNER && $row['pt_id']) {
    $pmb = get_member($row['pt_id'], 'mb_nick, mb_email, mb_homepage');
    if($pmb['mb_nick']) {
      $item[$i]['seller'] = apms_sideview($row['pt_id'], get_text($pmb['mb_nick']), $pmb['mb_email'], $pmb['mb_homepage']);
    }

  }

  // 상품구분
  $item[$i]['sendcost'] = 0;
  if(in_array($row['pt_it'], $g5['apms_automation'])) {
    // 배송불가 - 컨텐츠
    $item[$i]['is_delivery'] = false;

    // 이용정보
    if($row['ct_status'] == "완료") {
      $use = sql_fetch(" select use_cnt, use_file, use_datetime from {$g5['apms_use_log']} where od_id = '$od_id' and it_id = '$it_id' and mb_id = '{$member['mb_id']}' ", false);
      $item[$i]['use_cnt'] = $use['use_cnt'];
      $item[$i]['use_date'] = $use['use_datetime'];
      $item[$i]['use_file'] = $use['use_file'];
    }
  } else {
    // 배송가능
    $arr_it_orderform[] = $row['it_id'];

    $item[$i]['is_delivery'] = true;

    // 수령확인
    if($row['ct_status'] == "배송") {
      $item[$i]['de_confirm'] = G5_SHOP_URL.'/orderconfirm.php?od_id='.$od['od_id'].'&amp;it_id='.urlencode($row['it_id']).'&amp;uid='.urlencode($_GET['uid']);
    }

    // 배송정보
    if(USE_PARTNER && $row['pt_id']) {
      // 개별배송비
      $cost = sql_fetch("select sc_price from {$g5['apms_sendcost']} where it_id = '{$row['it_id']}' and od_id = '{$od['od_id']}' and pt_id = '{$row['pt_id']}' ", false);
      $item[$i]['sendcost'] = $cost['sc_price'];

      $item[$i]['de_company'] = $row['pt_send'];
      $item[$i]['de_invoice'] = $row['pt_send_num'];
      $item[$i]['de_check'] = get_delivery_inquiry($row['pt_send'], $row['pt_send_num']);
    } else {
      $item[$i]['de_company'] = $od['od_delivery_company'];
      $item[$i]['de_invoice'] = $od['od_invoice'];
      $item[$i]['de_check'] = get_delivery_inquiry($od['od_delivery_company'], $od['od_invoice']);
    }
  }

  for($k=0; $opt=sql_fetch_array($res); $k++) {

    // 구매회원 아이디 체크
    if($od['mb_id'] && $od['mb_id'] != $opt['mb_id']) {
      sql_query(" update {$g5['g5_shop_cart_table']} set mb_id = '{$od['mb_id']}' where od_id = '{$od_id}' and ct_id = '{$opt['ct_id']}' ", false);
    }

    if($row["ct_delivery_yn"] == "Y"){
      $deliveryItem[$i]["opt"][$k] = $opt;
    }

    $item[$i]['opt'][$k] = $opt;

    $opt_msg = get_text($opt['ct_option']);
    if($opt['pt_msg1']) {
      $opt_msg .= '<div class="text-muted">';
      if($row['pt_msg1']) $opt_msg .= $row['pt_msg1'].' : ';
      $opt_msg .= get_text($opt['pt_msg1']).'</div>';
    }
    if($opt['pt_msg2']) {
      $opt_msg .= '<div class="text-muted">';
      if($row['pt_msg2']) $opt_msg .= $row['pt_msg2'].' : ';
      $opt_msg .= get_text($opt['pt_msg2']).'</div>';
    }
    if($opt['pt_msg3']) {
      $opt_msg .= '<div class="text-muted">';
      if($row['pt_msg3']) $opt_msg .= $row['pt_msg3'].' : ';
      $opt_msg .= get_text($opt['pt_msg3']).'</div>';
    }

    $item[$i]['opt'][$k]['ct_option'] = $opt_msg;

    if($opt['io_type'])
      $opt_price = $opt['io_price'];
    else
      $opt_price = $opt['ct_price'] + $opt['io_price'];

    // 총 금액
    $sell_price = $opt_price * ($opt["ct_qty"] - $opt["ct_stock_qty"]) - $opt["ct_discount"]; // ct_discount를 빼고 보여줘야함.
    // 단가 역산
    $opt_price = $sell_price ? @round($sell_price / ($opt["ct_qty"] - $opt["ct_stock_qty"])) : 0;

    $basic_price = $sell_price;
    $tax_price = 0;
    if($opt['it_taxInfo'] != "영세" ) {
      // 공급가액
      $basic_price = round($sell_price / 1.1);
      // 부가세
      $tax_price = round($sell_price / 11);
    }

    $point = $opt['ct_point'] * $opt['ct_qty'];

    $item[$i]['opt'][$k]['opt_price'] = $opt_price;
    $item[$i]['opt'][$k]['sell_price'] = $sell_price;
    $item[$i]['opt'][$k]['point'] = $point;
    if(!in_array($opt['ct_status'], ['취소', '주문무효']))
        $tot_price += ($opt["prodSupYn"] == "Y") ? $sell_price : 0;

    $item[$i]['opt'][$k]['basic_price'] = $basic_price;
    $item[$i]['opt'][$k]['tax_price'] = $tax_price;

    $tot_point += $point;

    $st_count1++;
    if($opt['ct_status'] == '주문')
      $st_count2++;
    if($opt['ct_status'] == '입금')
      $st_count3++;
  }
}

// 자동처리 주문서인지 체크
$is_orderform = false;
if(is_array($arr_it_orderform) && !empty($arr_it_orderform)) {
  $is_orderform = true;
}

// 주문 상품의 상태가 모두 주문이면 고객 취소 가능
if($st_count1 > 0 && $st_count1 == $st_count2 && $od['od_status'] == '주문') {
  $custom_cancel = true;
}

// 주문 상품의 상태가 입금완료(결제완료)여도 고객 취소 가능 (단 무통장은 제외)
if(($custom_cancel == false) && ($st_count1 == $st_count3) && $od['od_status'] == '입금') {
  if ($od['od_settle_case'] != "무통장")
    $pay_complete_cancel = true;
  if ($od['od_settle_case'] == "무통장")
    $pay_complete_cancel2 = true;
}

if ($od['od_status'] == '준비' || $od['od_status'] == '출고준비') {
  $preparation_cancel = true;
}

if ($od['od_status'] == '배송' || $od['od_status'] == '완료') {
  $shipped_cancel = true;
}


// 총계 = 주문상품금액합계 + 배송비 - 상품할인 - 결제할인 - 배송비할인 - 할인금액 - 추가할인금액
//$tot_price = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2']
//        - $od['od_cart_coupon'] - $od['od_coupon'] - $od['od_send_coupon']
//        - $od['od_cancel_price'] - $od['od_cart_discount'] - $od['od_cart_discount2'];
$tot_price += $od["od_send_cost"] + $od["od_send_cost2"] - $od['od_cart_coupon'] - $od['od_coupon'] - $od['od_send_coupon'] - $od['od_receipt_point'];

// 결제,배송정보
$receipt_price  = $od['od_receipt_price'] + $od['od_receipt_point'];
$cancel_price   = $od['od_cancel_price'];

// 결제 상태
$pay_status = get_pay_step($od['od_pay_state']);

$misu = true;
$misu_price = $tot_price - $receipt_price - $cancel_price;

if ($misu_price == 0 && ($od['od_cart_price'] > $od['od_cancel_price'])) {
  $wanbul = " (완불)";
  $misu = false; // 미수금 없음
} else {
  $wanbul = display_price($receipt_price);
}

// 결제정보처리
if($od['od_receipt_price'] > 0)
  $od_receipt_price = display_price($od['od_receipt_price']);
else
  $od_receipt_price = '아직 <b>'.number_format($misu_price).'원</b>이 입금되지 않았거나 입금정보를 입력하지 못하였습니다.';

$app_no_subj = '';
$disp_bank = true;
$disp_receipt = false;
if($od['od_settle_case'] == '신용카드' || $od['od_settle_case'] == 'KAKAOPAY' || is_inicis_order_pay($od['od_settle_case']) ) {
  $app_no_subj = '승인번호';
  $app_no = $od['od_app_no'];
  $disp_bank = false;
  $disp_receipt = true;
} else if($od['od_settle_case'] == '간편결제') {
  $app_no_subj = '승인번호';
  $app_no = $od['od_app_no'];
  $disp_bank = false;
  switch($od['od_pg']) {
    case 'lg':
      $easy_pay_name = 'PAYNOW';
      break;
    case 'inicis':
      $easy_pay_name = 'KPAY';
      break;
    case 'kcp':
      $easy_pay_name = 'PAYCO';
      break;
    default:
      break;
  }
} else if($od['od_settle_case'] == '휴대폰') {
  $app_no_subj = '휴대폰번호';
  $app_no = $od['od_bank_account'];
  $disp_bank = false;
  $disp_receipt = true;
} else if($od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') {
  $app_no_subj = '거래번호';
  $app_no = $od['od_tno'];
}

// 영수증
$disp_receipt_href = '';
if($disp_receipt) {
  if($od['od_settle_case'] == '휴대폰')
  {
    if($od['od_pg'] == 'lg') {
      require_once G5_SHOP_PATH.'/settle_lg.inc.php';
      $LGD_TID      = $od['od_tno'];
      $LGD_MERTKEY  = $config['cf_lg_mert_key'];
      $LGD_HASHDATA = md5($LGD_MID.$LGD_TID.$LGD_MERTKEY);

      $hp_receipt_script = 'showReceiptByTID(\''.$LGD_MID.'\', \''.$LGD_TID.'\', \''.$LGD_HASHDATA.'\');';
    } else if($od['od_pg'] == 'inicis') {
      $hp_receipt_script = 'window.open(\'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid='.$od['od_tno'].'&noMethod=1\',\'receipt\',\'width=430,height=700\');';
    } else {
      $hp_receipt_script = 'window.open(\''.G5_BILL_RECEIPT_URL.'mcash_bill&tno='.$od['od_tno'].'&order_no='.$od['od_id'].'&trade_mony='.$od['od_receipt_price'].'\', \'winreceipt\', \'width=500,height=690,scrollbars=yes,resizable=yes\');';
    }

    $disp_receipt_href = 'href="javascript:;" onclick="'.$hp_receipt_script.'"';
  }

  if($od['od_settle_case'] == '신용카드' || is_inicis_order_pay($od['od_settle_case']) )
  {
    if($od['od_pg'] == 'lg') {
      require_once G5_SHOP_PATH.'/settle_lg.inc.php';
      $LGD_TID      = $od['od_tno'];
      $LGD_MERTKEY  = $config['cf_lg_mert_key'];
      $LGD_HASHDATA = md5($LGD_MID.$LGD_TID.$LGD_MERTKEY);

      $card_receipt_script = 'showReceiptByTID(\''.$LGD_MID.'\', \''.$LGD_TID.'\', \''.$LGD_HASHDATA.'\');';
    } else if($od['od_pg'] == 'inicis') {
      $card_receipt_script = 'window.open(\'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid='.$od['od_tno'].'&noMethod=1\',\'receipt\',\'width=430,height=700\');';
    } else {
      $card_receipt_script = 'window.open(\''.G5_BILL_RECEIPT_URL.'card_bill&tno='.$od['od_tno'].'&order_no='.$od['od_id'].'&trade_mony='.$od['od_receipt_price'].'\', \'winreceipt\', \'width=470,height=815,scrollbars=yes,resizable=yes\');';
    }

    $disp_receipt_href = 'href="javascript:;" onclick="'.$card_receipt_script.'"';
  }

  if($od['od_settle_case'] == 'KAKAOPAY')
  {
    $card_receipt_script = 'window.open(\'https://mms.cnspay.co.kr/trans/retrieveIssueLoader.do?TID='.$od['od_tno'].'&type=0\', \'popupIssue\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=420,height=540\');';

    $disp_receipt_href = 'href="javascript:;" onclick="'.$card_receipt_script.'"';
  }
}

// 현금영수증 발급을 사용하는 경우에만
$taxsave_href = '';
$taxsave_confirm = false;
if ($default['de_taxsave_use']) {
  // 미수금이 없고 현금일 경우에만 현금영수증을 발급 할 수 있습니다.
  if ($misu_price == 0 && $od['od_receipt_price'] && ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '계좌이체' || $od['od_settle_case'] == '가상계좌')) {

    if ($od['od_cash'])
    {
      if($od['od_pg'] == 'lg') {
        require_once G5_SHOP_PATH.'/settle_lg.inc.php';

        switch($od['od_settle_case']) {
          case '계좌이체':
            $trade_type = 'BANK';
            break;
          case '가상계좌':
            $trade_type = 'CAS';
            break;
          default:
            $trade_type = 'CR';
            break;
        }
        $cash_receipt_script = 'javascript:showCashReceipts(\''.$LGD_MID.'\',\''.$od['od_id'].'\',\''.$od['od_casseqno'].'\',\''.$trade_type.'\',\''.$CST_PLATFORM.'\');';
      } else if($od['od_pg'] == 'inicis') {
        $cash = unserialize($od['od_cash_info']);
        $cash_receipt_script = 'window.open(\'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/Cash_mCmReceipt.jsp?noTid='.$cash['TID'].'&clpaymethod=22\',\'showreceipt\',\'width=380,height=540,scrollbars=no,resizable=no\');';
      } else {
        require_once G5_SHOP_PATH.'/settle_kcp.inc.php';

        $cash = unserialize($od['od_cash_info']);
        $cash_receipt_script = 'window.open(\''.G5_CASH_RECEIPT_URL.$default['de_kcp_mid'].'&orderid='.$od_id.'&bill_yn=Y&authno='.$cash['receipt_no'].'\', \'taxsave_receipt\', \'width=360,height=647,scrollbars=0,menus=0\');';
      }
      $taxsave_href = 'href="javascript:;" onclick="'.$cash_receipt_script.'"';
      $taxsave_confirm = true;
    }
    else
    {
      $taxsave_href = 'href="javascript:;" onclick="window.open(\''.G5_SHOP_URL.'/taxsave.php?od_id='.$od_id.'\', \'taxsave\', \'width=550,height=400,scrollbars=1,menus=0\');"';
    }
  }
}

// 가상계좌테스트
$is_account_test = false;
if ($od['od_settle_case'] == '가상계좌' && $od['od_misu'] > 0 && $default['de_card_test'] && $is_admin && $od['od_pg'] == 'kcp') {
  preg_match("/\s{1}([^\s]+)\s?/", $od['od_bank_account'], $matchs);
  $deposit_no = trim($matchs[1]);
  $is_accoutn_test = true;
}

// Page ID
$pid = ($pid) ? $pid : 'inquiryview';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

// 스킨 체크
list($order_skin_path, $order_skin_url) = apms_skin_thema('shop/order', $order_skin_path, $order_skin_url);

// 스킨설정
$wset = array();
if($skin_row['order_'.MOBILE_.'set']) {
  $wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
}

// 데모
if($is_demo) {
  @include ($demo_setup_file);
}

// 설정값 불러오기
$is_inquiryview_sub = false;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '주문상세내역';

if($is_inquiryview_sub) {
  include_once(G5_PATH.'/head.sub.php');
  if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
  include_once('./_head.php');
}

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

// 셋업
$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
  $setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
}

// LG 현금영수증 JS
if($od['od_pg'] == 'lg') {
  if($default['de_card_test']) {
  echo '<script language="JavaScript" src="http://pgweb.uplus.co.kr:7085/WEB_SERVER/js/receipt_link.js"></script>'.PHP_EOL;
  } else {
    echo '<script language="JavaScript" src="http://pgweb.uplus.co.kr/WEB_SERVER/js/receipt_link.js"></script>'.PHP_EOL;
  }
}

$typereceipt = get_typereceipt_step($od['od_id']);
$typereceipt_cate = get_typereceipt_cate($od['od_id']);

// 주문내역 스킨 불러오기
$sql_q = "select * from `g5_shop_order` where `od_id`= '".$_GET['od_id']."'";
$row_q= sql_fetch($sql_q);

include_once($skin_path.'/orderinquiryview.skin.php');

if($is_inquiryview_sub) {
  if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
  include_once(G5_PATH.'/tail.sub.php');
} else {
  include_once('./_tail.php');
}
?>

<?php

if($_GET["result"] == "writeEform" && $_SESSION["productList{$_GET["od_id"]}"]) {
  $reload = true;
  $insertProds = addslashes(htmlspecialchars(json_encode($_SESSION["productList{$_GET["od_id"]}"])));

  sql_query("
    UPDATE g5_shop_order SET
        eformYn = 'Y'
      , payMehCd = '0'
      , prods = '{$insertProds}'
    WHERE od_id = '{$_GET["od_id"]}'
  ");

  $orderData = sql_fetch("SELECT * FROM g5_shop_order WHERE od_id = '{$_GET["od_id"]}'");

  $productList = $_SESSION["productList{$_GET["od_id"]}"];

  #모두 재고소진, 보유재고등록일 경우 배송비 - 0원 처리
  $sql_up ="select * from `g5_shop_cart` where `od_id`='".$_GET["od_id"]."'";
  $result_up=sql_query($sql_up);
  $flag=true;
  for ($i=0; $row=sql_fetch_array($result_up); $i++) {
    if($row['ct_status']=="재고소진"||$row['ct_status']=="보유재고등록") {
      continue;
    } else {
      $flag = false;
    }
  }
  if($flag) {
    sql_query("
    UPDATE g5_shop_order SET
      od_send_cost = '0'
    WHERE od_id = '{$_GET["od_id"]}'
    ");
  }

  #판매, 대여로그 작성
  $sendData2 = [];
  $sendData2["uuid"] = $_SESSION["uuid{$_GET["od_id"]}"];
  $sendData2["penOrdId"] = $_SESSION["penOrdId{$_GET["od_id"]}"];
  $oCurl = curl_init();
  curl_setopt($oCurl, CURLOPT_PORT, 9901);
  curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_ORDER_SELECT_LIST);
  curl_setopt($oCurl, CURLOPT_POST, 1);
  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData2, JSON_UNESCAPED_UNICODE));
  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
  $res2 = curl_exec($oCurl);
  $res2 = json_decode($res2, true);
  curl_close($oCurl);
  $count=count($res2['data']);
  if($count>0) {
  
    $sendData3=[];
    $sendData3["usrId"] = $member["mb_id"];
    $sendData3["entId"] = $member["mb_entId"];
    $sendData3["pageNum"] = 1;
    $sendData3["pageSize"] = 1;
    $sendData3["penId"] = $res2['data'][0]['penId'];

    $oCurl = curl_init();
    curl_setopt($oCurl, CURLOPT_PORT, 9901);
    curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_RECIPIENT_SELECTLIST);
    curl_setopt($oCurl, CURLOPT_POST, 1);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData3, JSON_UNESCAPED_UNICODE));
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $res_pen = curl_exec($oCurl);
    $res_pen = json_decode($res_pen, true);
    curl_close($oCurl);
    $data = $res_pen["data"][0];
    //수급자명
    $ren_person=$data["penNm"];
    $stoId=$res2['data'][0]['stoId'];
    $penOrdId=$res2['data'][0]['penOrdId'];
    $strdate=date("Y-m-d", strtotime($res2['data'][0]['ordLendStrDtm']));
    $enddate=date("Y-m-d", strtotime($res2['data'][0]['ordLendEndDtm']));
    $ren_eformUrl=$res2['data'][0]['eformUrl'];
    for($i=0; $i<$count; $i++) {
      $rental_log_Id="rental_log".round(microtime(true)).rand();
      $stoId=$res2['data'][$i]['stoId'];
      $penOrdId=$res2['data'][$i]['penOrdId'];
      $strdate=date("Y-m-d", strtotime($res2['data'][$i]['ordLendStrDtm']));
      $enddate=date("Y-m-d", strtotime($res2['data'][$i]['ordLendEndDtm']));

      $dis_total_date=G5_TIME_YMDHIS;
      $sql = " insert into `g5_rental_log` set
              `rental_log_Id` = '$rental_log_Id',
              `stoId` = '$stoId',
              `ordId` = '$penOrdId',
              `strdate` = '$strdate',
              `enddate` = '$enddate',
              `dis_total_date` = '$dis_total_date',
              `ren_person` = '$ren_person',
              `ren_eformUrl` = '$ren_eformUrl',
              `rental_log_division` = '2' ";
      sql_query($sql);
    }
  }
  #대여로그 작성

  if($od["od_penId"]) {
    #재고소진 상태값 변경
    $sto_imsi="";
    $sql_ct = " select `stoId` from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_status ='재고소진'";
    $result_ct = sql_query($sql_ct);
    while($row_ct = sql_fetch_array($result_ct)) {
      $sto_imsi .=$row_ct['stoId'];
    }

    $stoIdDataList = explode('|',$sto_imsi);
    $stoIdDataList=array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);
    $sendData["stoId"] = $stoIdData;
    $res = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
    $result_again =$res['data'];
    for($i = 0; $i < count($result_again); $i++){
      $productList2[$i]["stoId"] = $result_again[$i]["stoId"];
      $productList2[$i]["prodBarNum"] = $result_again[$i]["prodBarNum"];
      $productList2[$i]["stateCd"] = "02";
    }

    //orderupdate 업데이트 - >eformYn를 위해서 (계약서 작성완료)
    $sendData_order = [];
    $sendData_order["usrId"] = $member["mb_id"];
    $sendData_order["penId"] = $orderData["penId"];
    $sendData_order["penOrdId"] = $orderData["ordId"];
    $sendData_order["delGbnCd"] = "";
    $sendData_order["ordWayNum"] = "";
    $sendData_order["delSerCd"] = "";
    $sendData_order["ordNm"] = $orderData["od_b_name"];
    $sendData_order["ordCont"] = ($orderData["od_b_tel"]) ? $orderData["od_b_tel"] : $orderData["od_b_hp"];
    $sendData_order["ordMeno"] = $orderData["od_memo"];
    $sendData_order["ordZip"] = "{$orderData["od_b_zip1"]}{$orderData["od_b_zip2"]}";
    $sendData_order["ordAddr"] = $orderData["od_b_addr1"];
    $sendData_order["ordAddrDtl"] = $orderData["od_b_addr2"];
    $sendData_order["eformYn"] = "Y";
    $sendData_order["staOrdCd"] = "02";
    $sendData_order["lgsStoId"] = "";
    $sendData_order["prods"] = $productList2;
    $api_result1 = get_eroumcare(EROUMCARE_API_ORDER_UPDATE, $sendData_order);


    //누실된 바코드가 있을 수 있으로 stock_update 다시 진행
    $sendData = [];
    $sendData["usrId"] = $member["mb_id"];
    $sendData["entId"] = $member["mb_entId"];
    $sendData["prods"] = $productList2;
    $api_result = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $sendData);
  }

  unset($_SESSION["productList{$_GET["od_id"]}"]);
  unset($_SESSION["deliveryTotalCnt{$_GET["od_id"]}"]);
  unset($_SESSION["uuid{$_GET["od_id"]}"]);
  unset($_SESSION["penOrdId{$_GET["od_id"]}"]);

?>

<script type="text/javascript">
//cart 기준 barcode insert update
$.ajax({
  url : "<?=G5_SHOP_URL?>/ajax.ct_barcode_insert.php",
  type : "POST",
  async : false,
  data : {
    od_id : "<?=$_GET["od_id"]?>",
  }
});

<?php if($reload) { ?>
window.location.reload();
<?php } ?>
var sendData = <?=json_encode($sendData, JSON_UNESCAPED_UNICODE)?>;
</script>

<?php } ?>
