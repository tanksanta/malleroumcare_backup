<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

$ct_status = in_array($ct_status, ['준비', '출고준비', '배송', '완료', '취소']) ? $ct_status : '';
if(!$ct_status)
  json_response(400, '유효하지 않은 요청입니다.');

$sql_common = "
  FROM
    g5_shop_cart c
  LEFT JOIN
    g5_shop_order o ON c.od_id = o.od_id
  LEFT JOIN
    g5_shop_item i ON c.it_id = i.it_id
  WHERE
    c.mb_id = '{$member['mb_id']}' and
    o.od_del_yn = 'N' and
    c.ct_status = '{$ct_status}' and
    o.od_time >= DATE(NOW() - INTERVAL 3 MONTH)
";

$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ")['cnt'] ?: 0;

$page_rows = 5;
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql = "
  SELECT
    c.*,
    o.recipient_yn,
    o.od_cart_price,
    o.od_send_cost,
    o.od_send_cost2,
    o.od_penNm,
    o.od_penTypeNm,
    o.od_time,
    o.od_ip,
    o.od_b_name,
    i.it_img1 as it_img,
    i.prodSupYn
  {$sql_common}
  ORDER BY
    c.od_id DESC,
    c.ct_id DESC
  LIMIT {$from_record}, {$page_rows}
";
$result = sql_query($sql, true);

$list = [];
while($row = sql_fetch_array($result)) {
  // 주문조회 주소 생성
  $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
  $row['od_href'] = G5_SHOP_URL.'/orderinquiryview.php?od_id='.$row['od_id'].'&uid='.$uid;

  // 주문 총 가격
  $row['od_total_price'] = $row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2'];

  // 바코드 정보
  $stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
    'stoId' => $row['stoId']
  ), 443);
  $row['barcode'] = [];
  if($stock_result['data']) {
    foreach($stock_result['data'] as $stock) {
      if($stock['prodBarNum'])
        $row['barcode'][] = $stock['prodBarNum'];
    }
  }

  // 주문상태 변환
  $ct_status_text = '';
  switch ($row['ct_status']) {
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
  $row['ct_status_text'] = $ct_status_text;

  $list[] = $row;
}

$html = '';
$last_od_id = null;
foreach($list as $row) {
  // 이전 상품이랑 주문번호가 같은지 여부 (주문번호가 같은 상품들은 하나의 li.order에 묶기 위해서)
  $is_new_order = $last_od_id !== $row['od_id'];

  if($last_od_id !== null && $is_new_order)
    $html .= '</li>';

  if($is_new_order) {
    $html .= '<li class="order">';

    // 수급자 정보
    if($row['recipient_yn'] == 'Y') {
      $html .= '
        <div class="pen_info">
          <div class="btn_pen">
            <img src="'.THEMA_URL.'/assets/img/icon_pen.png">
            수급자 주문
          </div>
          수급자 정보 : '.$row["od_penNm"].' ('.$row["od_penTypeNm"].')
        </div>
      ';
    }

    // 주문 정보
    $html .= '
      <div class="order_info">
        <span>
          <i class="pc">주문번호 :</i>
          <a href="'.$row['od_href'].'">'.$row['od_id'].'</a>
        </span>
        <span>'.display_price($row["od_total_price"]).'</span>
        <span>'.date('n월.j일 (H:i)', strtotime($row['od_time'])).'</span>
        <span>배송 : '.$row['od_b_name'].'</span>
      </div>
    ';
  }

  // 상품 정보
  $html .= '
    <div class="list">
      <ul class="cb">
        <li class="pro grow">
          <div class="img" style="min-width:100px; min-height:100px;">
            <a href="'.$row['od_href'].'"><img src="/data/item/'.$row["it_img"].'" onerror="this.src=\'/img/no_img.png\';"></a>
          </div>
          <div class="pro-info">';

  if($row["recipient_yn"] == "Y") {
    $html .= '<div class="day">';

    if($row["ordLendStrDtm"] && $row["ordLendStrDtm"] != "0000-00-00 00:00:00") {
      $html .= '<i>대여</i>' . date("Y.m.d", strtotime($row["ordLendStrDtm"])) . ' ~ ' . date("Y.m.d", strtotime($row["ordLendEndDtm"]));
    } else {
      $html .= '<i class="on-order">주문</i>';
    }
    $html .= '</div>';
  }

  $html .= '
            <div class="name">
              <a href="'.$row['od_href'].'">
                '.$row["it_name"]. ($row["ct_option"] != $row["it_name"] ? " ({$row["ct_option"]})" : '') .'
              </a>
            </div>
            <div>
              <em>
                수량 : '.$row["ct_qty"].'</em>'
                .($row["ct_stock_qty"] ? '<em>, 재고소진 : '.$row["ct_stock_qty"].'</em>' : '').'
              </em>
            </div>
          </div>
        </li>
        <li class="delivery">
          '.$row['ct_status_text'].'
        </li>
        <li class="info-btn">
          <div class="barcode_preview">
            <ul>
  ';

  if($row['barcode']) {
    for($i = 0; $i < 3; $i++) {
      if(!$row['barcode'][$i]) break;
      $html .= '<li>'.$row['barcode'][$i].'</li>';
    }

    $html .= '
      <li>
        <a href="javascript:void(0);" class="btn-01 btn-0 popupProdBarNumInfoBtn" data-id="'.$row["od_id"].'" data-ct-id="'.$row['ct_id'].'"><img src="/skin/apms/order/new_basic/image/icon_02.png"> 
          바코드
        </a>
      </li>
    ';
  }

  if(in_array($row['ct_status'], ['배송', '완료']) && $row["prodSupYn"] == "Y") {
    $html .= '
      <li>
        <a href="javascript:void(0);" class="btn-02 btn-0 popupDeliveryInfoBtn" data-od="'.$row["od_id"].'">배송정보</a>
      </li>
    ';
  }

  $html .= '
            </ul>
          </div>
        </li>
      </ul>
    </div>
  ';

  $last_od_id = $row['od_id'];
}

if($html) $html .= '</li>';

json_response(200, 'OK', $html);
?>
