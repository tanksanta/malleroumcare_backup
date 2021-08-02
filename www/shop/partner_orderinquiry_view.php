<?php
include_once('./_common.php');

if (!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "파트너 주문상세";
include_once("./_head.php");
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch(" SELECT * FROM {$g5['g5_shop_order_table']} WHERE od_id = '{$od_id}' ");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

$cart_result = sql_query("
  SELECT * FROM {$g5['g5_shop_cart_table']}
  WHERE od_id = '{$od_id}' and ct_direct_delivery_partner = '{$member['mb_id']}' and ct_status IN('준비', '출고준비', '배송', '완료')
  ORDER BY ct_id ASC
");

$total_price_p = 0; // 총 공급가 합계
$total_price_s = 0; // 총 부가세 합계
$count_delivery_inserted = 0; // 배송비 정보 입력된 숫자

$carts = [];
while($row = sql_fetch_array($cart_result)) {
  $row['report'] = null;
  if($row['ct_is_direct_delivery'] == 2) { // 배송+설치
    $report = sql_fetch(" SELECT * FROM partner_install_report WHERE ct_id = '{$row['ct_id']}' ");
    if($report['ct_id']) {
      $photo_result = sql_query("
        SELECT * FROM partner_install_photo
        WHERE ct_id = '{$report['ct_id']}' and mb_id = '{$report['mb_id']}'
        ORDER BY ip_id ASC
      ");

      $photos = [];
      while($photo = sql_fetch_array($photo_result)) {
        $photos[] = $photo;
      }
      $report['photo'] = $photos;
      $row['report'] = $report;
    }
  }

  if($row['ct_delivery_num'])
    $count_delivery_inserted++;

  $row['it_name'] .= ($row['ct_option'] != $row['it_name'] ? " ({$row['ct_option']})" : '');

  $ct_direct_delivery_text = '배송';
  if($row['ct_is_direct_delivery'] == '2') {
    $ct_direct_delivery_text = '배송/설치';
  }
  $row['ct_direct_delivery'] = $ct_direct_delivery_text;

  $price = intval($row['ct_direct_delivery_price']) * intval($row['ct_qty']);
  // 공급가액
  $price_p = @round(($price ?: 0) / 1.1);
  // 부가세
  $price_s = @round(($price ?: 0) / 1.1 / 10);

  $total_price_p += $price_p;
  $total_price_s += $price_s;

  $row['price_p'] = $price_p;
  $row['price_s'] = $price_s;

  $carts[] = $row;
}

if(!$carts)
  alert('존재하지 않는 주문입니다.');

function trans_ct_status_text($ct_status_text) {
  switch ($ct_status_text) {
    case '보유재고등록': $ct_status_text = "보유재고등록"; break;
    case '재고소진': $ct_status_text = "재고소진"; break;
    case '주문무효': $ct_status_text = "주문무효"; break;
    case '취소': $ct_status_text = "주문취소"; break;
    case '주문': $ct_status_text = "상품주문"; break;
    case '입금': $ct_status_text = "입금완료"; break;
    case '준비': $ct_status_text = "상품준비"; break;
    case '출고준비': $ct_status_text = "출고준비"; break;
    case '배송': $ct_status_text = "출고완료"; break;
    case '완료': $ct_status_text = "배송완료"; break;
  }

  return $ct_status_text;
}
?>

<section id="partner-order" class="wrap">
  <h2 class="title row no-gutter">
    주문상세
  </h2>

  <section class="row no-gutter justify-space-between container">
    <div class="left-wrap">
      <form id="form_ct_status">
        <div class="top row no-gutter justify-space-between align-center">
          <div class="col title">
            <?=$od['od_name']?> (주문일시: <?=date('Y-m-d H:i:s', strtotime($od['od_time']))?>)
          </div>
          <div class="col">
            <select name="ct_status" class="order-status-select">
              <option value="준비">상품준비</option>
              <option value="출고준비">출고준비</option>
              <option value="배송">출고완료</option>
            </select>
            <button type="button" id="btn_ct_status" class="order-status-btn">저장</button>
          </div>
        </div>

        <div class="item-list">
          <ul>
            <?php foreach($carts as $cart) { ?>
            <li class="item row align-center">
              <div class="col checkbox-wrap text-center">
                <input type="checkbox" name="ct_id[]" value="<?=$cart['ct_id']?>"/>
              </div>
              <div class="col item-img-wrap">
                <div class="item-img">
                  <img src="/shop/img/no_image.gif" onerror="this.src='/shop/img/no_image.gif';">
                </div>
              </div>
              <div class="col item-info-wrap">
                <div class="title full-width">
                  <?=$cart['it_name']?>
                </div>
                <div class="price full-width text-grey">
                  금액 : 공급가(<?=number_format($cart['price_p'])?>원), 부가세(<?=number_format($cart['price_s'])?>원)
                </div>
                <div class="qty full-width text-grey">
                  수량 : <?=$cart['ct_qty']?>개 / 위탁 : <?=$cart['ct_direct_delivery']?>
                </div>
              </div>
              <div class="col delivery-wrap text-center">
                <?=trans_ct_status_text($cart['ct_status'])?>
              </div>
              <div class="col barcode-wrap text-center">
                <a href="javascript:void(0);" class="barcode-btn btn_barcode_info" data-id="<?=$cart['ct_id']?>">
                  <img src="/skin/apms/order/new_basic/image/icon_02.png" alt="">
                  바코드
                </a>
              </div>
            </li>
            <?php
              if($cart['ct_is_direct_delivery'] == 2) {
            ?>
            <li class="install-report">
              <div class="top-wrap row no-gutter justify-space-between">
                <span>설치 결과 보고서</span>
                <button type="button" class="report-btn btn_install_report" data-id="<?=$cart['ct_id']?>">결과보고서 작성</button>
              </div>
              <?php if($cart['report'] && $cart['report']['ir_cert_url']) { ?>
              <div class="row report-img-wrap">
                <div class="col">
                  <div class="report-img">
                    <img src="<?=G5_DATA_URL.'/partner/img/'.$cart['report']['ir_cert_url']?>" onerror="this.src='/shop/img/no_image.gif';">
                  </div>
                </div>
                <?php foreach($cart['report']['photo'] as $photo) { ?>
                <div class="col">
                  <div class="report-img">
                    <img src="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" onerror="this.src='/shop/img/no_image.gif';">
                  </div>
                </div>
                <?php } ?>
                <div class="col issue-wrap">
                  <p class="issue">
                    <?=nl2br($cart['report']['ir_issue'])?>
                  </p>
                </div>
              </div>
              <?php } ?>
            </li>
            <?php
              }
            }
            ?>
          </ul>
        </div>
      </form>

      <div class="row no-gutter">
        <div class="col title">배송정보</div>
      </div>
      <div class="row no-gutter delivery-info-wrap">
        <ul>
          <li>
            <div class="row">
              <div class="col left">수령인</div>
              <div class="col right"><?=get_text($od['od_b_name'])?></div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">연락처</div>
              <div class="col right">연락처 <?=get_text($od['od_b_tel']) ?: '-'?>, 휴대폰 <?=get_text($od['od_b_hp']) ?: '-'?></div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">주소</div>
              <div class="col right"><?=get_text(sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon']))?></div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">전달메시지</div>
              <div class="col right">
                <?php
                $prod_memo_text = '';
                foreach($carts as $cart) {
                  if($cart['prodMemo']) {
                    $prod_memo_text .= '<b>'.$cart['it_name'].' : </b>';
                    $prod_memo_text .= $cart['prodMemo'];
                    $prod_memo_text .= '<br>';
                  }
                }
                if($prod_memo_text)
                  echo $prod_memo_text;
                else 
                  echo '없음';
                ?>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="right-wrap">
      <div class="row no-gutter">
        <a href="<?=G5_SHOP_URL?>/installation_report.xlsx" class="instructor-btn">작업지시서 다운로드</a>
      </div>
      <div class="delivery-status-title row no-gutter title">
        배송정보
      </div>
      <div class="row no-gutter">
        <a href="javascript:void(0);" id="btn_delivery_info" class="delivery-status-info col full-width text-center">
          배송정보 (<?=$count_delivery_inserted?>/<?=count($carts)?>)
        </a>
      </div>
      <div class="delivery-info-list">
        <form id="form_delivery_date">
          <input type="hidden" name="od_id" value="<?=$od_id?>">
          <ul>
            <?php
            foreach($carts as $cart) {
            ?>
            <li class="delivery-info-item">
              <input type="hidden" name="ct_id[]" value="<?=$cart['ct_id']?>">
              <div class="info-title text-weight-bold">
                <?=$cart['it_name']?>
              </div>
              <div class="row">
                <div class="col left">출고 예정일</div>
                <div class="col right">
                  <input type="text" class="datepicker" name="ct_direct_delivery_date_<?=$cart['ct_id']?>" value="<?=$cart['ct_direct_delivery_date']?>">
                </div>
              </div>
              <div class="row">
                <div class="col left">출고 완료일</div>
                <div class="col right"><?=$cart['ct_ex_date'] ?: '대기'?></div>
              </div>
            </li>
            <?php } ?>
          </ul>
          <button type="button" id="btn_delivery_date" class="delivery-save-btn">출고예정일 저장</button>
        </form>
      </div>

      <div class="order-settle-title title row no-gutter">
        정산정보
      </div>
      <div class="order-settle-info">
        <ul>
          <li class="row no-gutter justify-space-between">
            <div class="col">공급가액</div>
            <div class="col"><?=number_format($total_price_p)?>원</div>
          </li>
          <li class="row no-gutter justify-space-between">
            <div class="col">부가세</div>
            <div class="col"><?=number_format($total_price_s)?>원</div>
          </li>
          <li class="row no-gutter justify-space-between">
            <div class="col">합계</div>
            <div class="col total"><?=number_format($total_price_p + $total_price_s)?>원</div>
          </li>
        </ul>
      </div>
    </div>
  </section>
</section>

<style>
#popup_box { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popup_box > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popup_box iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

@media (max-width : 750px) {
  #popup_box iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}
</style>

<div id="popup_box">
  <div></div>
</div>

<script>
$(function() {
  $("#popup_box").hide();
  $("#popup_box").css("opacity", 1);

  // 출고예정일 datepicker
  $('.datepicker').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    showButtonPanel: true,
    yearRange: "c-99:c+99"
  });

  // 배송정보 버튼
  $('#btn_delivery_info').click(function(e) {
    e.preventDefault();
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.partner_deliveryinfo.php?od_id=<?=$od_id?>">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 설치결과보고서 작성 버튼
  $('.btn_install_report').click(function() {
    var ct_id = $(this).data('id');
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.partner_installreport.php?ct_id=' + ct_id + '">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 바코드 버튼
  $('.btn_barcode_info').click(function(e) {
    e.preventDefault();

    var ct_id = $(this).data('id');
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.partner_barcodeinfo.php?ct_id=' + ct_id + '">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 주문상태 변경
  $('#btn_ct_status').click(function() {
    $('#form_ct_status').submit();
  });
  $('#form_ct_status').on('submit', function(e) {
    e.preventDefault();

    $.post('ajax.partner_ctstatus.php', $(this).serialize(), 'json')
    .done(function() {
      alert('변경이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 출고예정일 변경
  $('#btn_delivery_date').click(function() {
    $('#form_delivery_date').submit();
  });
  $('#form_delivery_date').on('submit', function(e) {
    e.preventDefault();

    $.post('ajax.partner_deliverydate.php', $(this).serialize(), 'json')
    .done(function() {
      alert('변경이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});
</script>

<?php
include_once('./_tail.php');
?>
