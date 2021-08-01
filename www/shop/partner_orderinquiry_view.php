<?php
include_once('./_common.php');

if (!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "파트너 주문상세";
include_once("./_head.php");

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch(" SELECT * FROM {$g5['g5_shop_order_table']} WHERE od_id = '{$od_id}' ");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

$cart_result = sql_query("
  SELECT * FROM {$g5['g5_shop_cart_table']}
  WHERE od_id = '{$od_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
  ORDER BY ct_id ASC
");

$total_price_p = 0; // 총 공급가 합계
$total_price_s = 0; // 총 부가세 합계
$count_delivery_inserted = 0; // 배송비 정보 입력된 숫자

$carts = [];
while($row = sql_fetch_array($cart_result)) {
  $row['report'] = null;
  if($row['ct_is_direct_delivery'] == 2) {
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

  <section class="row no-gutter justify-space-between">
    <div class="left-wrap">
      <div class="top row no-gutter justify-space-between align-center">
        <div class="col title">
          <?=$od['od_name']?> (주문일시: <?=date('Y-m-d H:i:s', strtotime($od['od_time']))?>)
        </div>
        <div class="col">
          <select name="" class="order-status-select">
            <option>상품준비</option>
          </select>
          <button type="button" class="order-status-btn">저장</button>
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
              <a href="#" class="barcode-btn popupProdBarNumInfoBtn" data-id="" data-ct-id="">
                <img src="/skin/apms/order/new_basic/image/icon_02.png" alt="">
                바코드
              </a>
            </div>
          </li>
          <li class="install-report">
            <div class="top-wrap row no-gutter justify-space-between">
              <span>설치 결과 보고서</span>
              <button type="button" class="report-btn">결과보고서 작성</button>
            </div>
            <div class="row report-img-wrap">
              <div class="col">
                <div class="report-img">
                  <img src="/shop/img/no_image.gif" onerror="this.src='/shop/img/no_image.gif';">
                </div>
              </div>
              <div class="col">
                <div class="report-img">
                  <img src="/shop/img/no_image.gif" onerror="this.src='/shop/img/no_image.gif';">
                </div>
              </div>
              <div class="col">
                <p class="issue">
                  관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다
                  관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다
                  관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다
                  관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다 관리자가 작성한 이슈가 보여집니다
                </p>
              </div>
            </div>
          </li>
          <?php } ?>
        </ul>
      </div>

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
        <a href="<?=G5_SHOP_URL?>/안전손잡이_설치확인서.xlsx" class="instructor-btn">작업지시서 다운로드</a>
      </div>
      <div class="delivery-status-title row no-gutter title">
        배송정보
      </div>
      <div class="row no-gutter">
        <a href="#" class="delivery-status-info col full-width text-center">
          배송정보 (<?=$count_delivery_inserted?>/<?=count($carts)?>)
        </a>
      </div>
      <div class="delivery-info-list">
        <ul>
          <?php
          for ($i = 0; $i < 2; $i++) {
          ?>
          <li class="delivery-info-item">
            <div class="info-title text-weight-bold">
              품목명입니다. (옵션명)
            </div>
            <div class="row">
              <div class="col left">출고 예정일</div>
              <div class="col right">
                <input type="text" name="" value="">
              </div>
            </div>
            <div class="row">
              <div class="col left">출고 완료일</div>
              <div class="col right">대기</div>
            </div>
          </li>
          <?php
          }
          ?>
        </ul>
        <button type="button" class="delivery-save-btn">출고예정일 저장</button>
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


<?php
include_once('./_tail.php');
?>
