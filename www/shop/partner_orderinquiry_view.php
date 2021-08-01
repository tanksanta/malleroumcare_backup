<?php
include_once('./_common.php');

if (!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "파트너 주문상세";
include_once("./_head.php");
?>

<section id="partner-order" class="wrap">
  <h2 class="title row no-gutter">
    주문상세
  </h2>

  <section class="row no-gutter justify-space-between">
    <div class="left-wrap">
      <div class="top row no-gutter justify-space-between align-center">
        <div class="col title">
          사업소명 (주문일시:2021-06-25 17:01:18)
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
          <?php
          for ($i = 0; $i < 2; $i++) {
          ?>
          <li class="item row align-center">
            <div class="col checkbox-wrap text-center">
              <input name="" type="checkbox"/>
            </div>
            <div class="col item-img-wrap">
              <div class="item-img">
                <img src="/shop/img/no_image.gif" onerror="this.src='/shop/img/no_image.gif';">
              </div>
            </div>
            <div class="col item-info-wrap">
              <div class="title full-width">
                품목명입니다. (옵션)
              </div>
              <div class="price full-width text-grey">
                금액 : 공급가(100,000원), 부가세(10,000원)
              </div>
              <div class="qty full-width text-grey">
                수량 : 2개 / 위탁 : 배송+설치
              </div>
            </div>
            <div class="col delivery-wrap text-center">
              상품준비
            </div>
            <div class="col barcode-wrap text-center">
              <a href="#" class="barcode-btn popupProdBarNumInfoBtn" data-id="" data-ct-id="">
                <img src="/skin/apms/order/new_basic/image/icon_02.png" alt="">
                바코드
              </a>
            </div>
          </li>
          <?php
          }
          ?>
        </ul>
        <div class="install-report">
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
        </div>
      </div>

      <div class="row no-gutter">
        <div class="col title">배송정보</div>
      </div>
      <div class="row no-gutter delivery-info-wrap">
        <ul>
          <li>
            <div class="row">
              <div class="col left">수령인</div>
              <div class="col right">홍길동</div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">연락처</div>
              <div class="col right">연락처 010-1111-2222, 휴대폰 010-2222-2222</div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">주소</div>
              <div class="col right">서울시 강남구 서초동 123-11</div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">전달메시지</div>
              <div class="col right">없음</div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="right-wrap">
      <div class="row no-gutter">
        <a href="#" class="instructor-btn">작업지시서 다운로드</a>
      </div>
      <div class="delivery-status-title row no-gutter title">
        배송정보
      </div>
      <div class="row no-gutter">
        <a href="#" class="delivery-status-info col full-width text-center">
          배송정보 (1/2)
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
            <div class="col">200,000원</div>
          </li>
          <li class="row no-gutter justify-space-between">
            <div class="col">부가세</div>
            <div class="col">20,000원</div>
          </li>
          <li class="row no-gutter justify-space-between">
            <div class="col">합계</div>
            <div class="col total">220,000원</div>
          </li>
        </ul>
      </div>
    </div>
  </section>
</section>


<?php
include_once('./_tail.php');
?>
