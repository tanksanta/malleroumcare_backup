<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$g5['title'] = "파트너 발주상세";
include_once("./_head.php");
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$manager_mb_id = get_session('ss_manager_mb_id');

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch("
  SELECT
    o.*,
    m.mb_temp,
    m.mb_name,
    mb_entNm
  FROM
    purchase_order o
  LEFT JOIN
    {$g5['member_table']} m ON o.mb_id = m.mb_id
  WHERE
    od_id = '{$od_id}'
");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

//발주 기록
$sql = "SELECT * FROM purchase_order_admin_log WHERE od_id = '{$od_id}' ORDER BY ol_no DESC";
$result = sql_query($sql);
$logs = array();
while($row = sql_fetch_array($result)) {
    $logs[] = $row;
}

// 임시회원의경우 mb_entNm 대신 mb_name 출력
if($od['mb_temp']) {
  $od['mb_entNm'] = $od['mb_name'];
}

$cart_result = sql_query("
  SELECT
    c.*,
    i.it_img1
  FROM
    purchase_cart c
  LEFT JOIN
    {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
  WHERE
    od_id = '{$od_id}' and
    ct_supply_partner = '{$member['mb_id']}' and
    ct_status IN('발주완료', '출고완료', '입고완료', '취소')
  ORDER BY
    ct_id ASC
");

$total_price_p = 0; // 총 공급가 합계
$total_price_s = 0; // 총 부가세 합계
$count_delivery_inserted = 0; // 배송비 정보 입력된 숫자

$carts = [];
$has_install = false; // 설치 상품 있는지 여부
while($row = sql_fetch_array($cart_result)) {
  if($row['ct_delivery_num'])
    $count_delivery_inserted++;

  $row['it_name'] .= ($row['ct_option'] != $row['it_name'] ? " ({$row['ct_option']})" : '');

  $ct_direct_delivery_text = '배송';
  if($row['ct_is_direct_delivery'] == '2') {
    $ct_direct_delivery_text = '설치';
    $has_install = true;
  }
  $row['ct_direct_delivery'] = $ct_direct_delivery_text;

  $price = intval($row['ct_price']) * intval($row['ct_qty']);
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

// 담당자
$manager_result = sql_query("
  select * from g5_member
  where mb_type = 'manager' and mb_manager = '{$member['mb_id']}'
");
$managers = [];
while($manager = sql_fetch_array($manager_result)) {
  $managers[] = $manager;
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/partner_order.css?v=1128">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/magnific-popup.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.magnific-popup.js"></script>', 0);
?>

<section id="partner-order" class="wrap">
  <h2 class="title row no-gutter">
    발주상세
  </h2>

  <section class="row no-gutter justify-space-between container">
    <div class="left-wrap">
      <form id="form_ct_status">
        <div class="top row no-gutter justify-space-between align-center">
          <div class="col title">
            <?=$od['mb_entNm']?> (주문일시: <?=date('Y-m-d H:i:s', strtotime($od['od_time']))?>)
          </div>
          <div class="col">
            <select name="ct_status" class="order-status-select">
              <option value="출고완료" selected>출고완료</option>
              <option value="취소">취소</option>
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
                  <img src="/data/item/<?=$cart["it_img1"]?>" onerror="this.src='/shop/img/no_image.gif';">
                </div>
              </div>
              <div class="col item-info-wrap" style="width: calc(100% - 20% - 100px);">
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
                <?=$cart['ct_status']?>
              </div>
            </li>
            <li class="item row align-center" style="border-top: 0;padding-left: 40px;">
              <div class="col full-width" style="border: 1px solid #DDDDDD;padding: 10px;">
                <p style="margin-bottom: 0">배송지 : <?=$cart['ct_warehouse']?></p>
                <p style="margin-bottom: 0">주소 : <?=$cart['ct_warehouse_address']?></p>
                <p style="margin-bottom: 0">연락처 : <?=$cart['ct_warehouse_phone']?></p>
              </div>
            </li>
            <?php
            }
            ?>
          </ul>
        </div>
      </form>

      <div class="row no-gutter">
        <div class="col title" style="margin-top:20px;">기록</div>
      </div>
      <div class="row no-gutter delivery-info-wrap">
        <ul>
          <?php
            foreach($logs as $log) {
              $log_mb = get_member($log['mb_id']);
              if ($log_mb['mb_id'] == $member['mb_id']) {
                $manager = $member['mb_name'];
              }
              else if ($log_mb['mb_type'] != 'manager') {
                $manager = '이로움 관리자';
              }
              else {
                $manager = $member['mb_name'] . '>[직원]' . $log_mb['mb_name'];
              }
              echo '<li class="log"><div class="row">
                      <div class="log_datetime">'.$log['ol_datetime'] . '</div>
                      <div>(' . $manager . ') ' . $log['ol_content'] . '</div>
                    </div></li>';
            }
            if (!count($logs)) {
                echo '기록이 없습니다.';
            }
          ?>
        </ul>
      </div>
    </div>

    <div class="right-wrap">
      <div class="delivery-status-title row no-gutter title justify-space-between">
        <div>담당자</div>
        <?php
        if($manager_mb_id) {
          $manager_txt = '미지정';
          if($od['od_partner_manager']) {
            $manager = get_member($od['od_partner_manager']);
            $manager_txt = '[직원] ' . $manager['mb_name'];
          }
          echo "<div style=\"font-size: 16px;\">{$manager_txt}</div>";
        } else {
        ?>
        <select class="sel_manager order-status-select" data-id="<?=$od_id?>" style="width: 150px;">
          <option value="">미지정</option>
          <?php foreach($managers as $manager) { ?>
          <option value="<?=$manager['mb_id']?>" <?=get_selected($od['od_partner_manager'], $manager['mb_id'])?>>[직원] <?=$manager['mb_name']?></option>
          <?php } ?>
        </select>
        <?php } ?>
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
              <div class="info-title text-weight-bold">
                <?=$cart['it_name']?>
              </div>
              <div class="row">
                <div class="col left">입고 예정일</div>
                <div class="col right">
                  <input type="hidden" name="ct_id[]" value="<?=$cart['ct_id']?>">
                  <input type="text" class="datepicker" name="ct_delivery_expect_date_<?=$cart['ct_id']?>" value="<?=$cart['ct_delivery_expect_date'] ? date('Y-m-d', strtotime($cart['ct_delivery_expect_date'])) : ''?>">
                  <select name="ct_delivery_expect_time_<?=$cart['ct_id']?>">
                    <?php
                    $ct_delivery_expect_time = $cart['ct_delivery_expect_date'] ? date('H', strtotime($cart['ct_delivery_expect_date'])) : '';
                    for($i = 0; $i < 24; $i++) {
                      $time = str_pad($i, 2, '0', STR_PAD_LEFT); 
                    ?>
                    <option value="<?=$time?>" <?=get_selected($ct_delivery_expect_time, $time)?>><?=$time?>시</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col left">출고 완료일</div>
                <div class="col right"><?=$cart['ct_ex_date'] ?: '대기'?></div>
              </div>
              <?php
              if($cart['ct_delivery_num']) {
                $delivery_company_name = '';
                foreach($delivery_companys as $data) {
                  if($data['val'] == $cart['ct_delivery_company']) {
                    $delivery_company_name = $data['name'];
                    break;
                  }
                } 
              ?>
                <div class="row">
                <div class="col left">[<?=$delivery_company_name?>]</div>
                <div class="col right"><?=$cart['ct_delivery_num']?></div>
              </div>
              <?php } ?>
            </li>
            <?php } ?>
          </ul>
          <button type="button" id="btn_delivery_date" class="delivery-save-btn">입고예정일 저장</button>
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
    $("#popup_box > div").html('<iframe src="popup.supply_partner_deliveryinfo.php?od_id=<?=$od_id?>">');
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

    // 주문상태 변경
    if($('select[name="ct_status"]').val() == '취소' && !confirm('주문취소 후 상태 변경은 불가능합니다. 취소하시겠습니까?')) {
      return false;
    }
    $.post('ajax.supply_partner_ctstatus.php', $(this).serialize(), 'json')
    .done(function() {
      alert('변경이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 입고예정일 변경
  $('#btn_delivery_date').click(function() {
    $('#form_delivery_date').submit();
  });
  $('#form_delivery_date').on('submit', function(e) {
    e.preventDefault();

    $.post('ajax.supply_partner_deliverydate.php', $(this).serialize(), 'json')
    .done(function() {
      alert('변경이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 담당자 선택
  var loading_manager = false;
  $('.sel_manager').change(function() {
    if(loading_manager)
      return alert('로딩중입니다. 잠시후 다시 시도해주세요.');
    
    var od_id = $(this).data('id');
    var manager = $(this).val();
    var manager_name = $(this).find('option:selected').text();
    
    loading_manager = true;
    $.post('ajax.supply_partner_manager.php', {
      od_id: od_id,
      manager: manager
    }, 'json')
    .done(function() {
      alert(manager_name + ' 담당자로 변경되었습니다.');
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      loading_manager = false;
    })
  });
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
        var $btn_download = $('<a class="btn-bottom btn-download">다운로드</a>')
          .attr('href', item.src)
          .attr('download', '설치이미지_' + item.index + '.jpg');
        
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
    gallery:{
      enabled:true,
      tPrev: '이전', // title for left button
      tNext: '다음', // title for right button
      tCounter: '%curr% / %total%'
    },
  });
});
</script>

<?php
include_once('./_tail.php');
?>
