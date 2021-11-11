<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '주문신청';
include_once("./_head.php");

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$SKIN_URL = G5_SKIN_URL.'/apms/order/'.$skin_name;
add_stylesheet('<link rel="stylesheet" href="'.$SKIN_URL.'/css/product_order_210324.css?v=210910">');
add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_order.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
add_javascript(G5_POSTCODE_JS, 0);
add_javascript(G5_POSTCODE_JS, 0);

?>

<section class="wrap">
  <div class="sub_section_tit">간편 주문서 신청</div>
  <div class="inner">
    <form id="simple_order" name="forderform" class="form-horizontal" action="orderformupdate.php" method="post" onsubmit="return form_submit(this);">
      <input type="hidden" name="org_od_price" value="0">
      <input type="hidden" name="od_price" value="0">
      <input type="hidden" name="od_settle_case" value="월 마감 정산">
      <input type="hidden" name="od_send_cost2" value="0">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label">
              <strong>주문금액</strong>
            </label>
            <div class="col-sm-8">
              <span id="order_price" class="form_desc">0</span>원
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">
              <strong>배송비</strong>
            </label>
            <div class="col-sm-8">
              <span id="delivery_price" class="form_desc">0</span>원
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">
              <strong>쿠폰적용</strong>
            </label>
            <div class="col-sm-8">
              <span id="od_cp_price">0</span>원
              <input type="hidden" name="od_cp_id" value="">
              <input type="hidden" name="od_cp_price" value="0">
              <button type="button" id="od_coupon_btn" class="btn_so_coupon">쿠폰</button>
            </div>
          </div>
          <div class="form-group">
            <label for="od_temp_point" class="col-sm-2 control-label">
              <strong>포인트</strong>
            </label>
            <div class="col-sm-8">
              <input type="text" name="od_temp_point" id="od_temp_point" class="form-control input-sm" value="0">
              <label for="chk_point_all">
                <input type="checkbox" id="chk_point_all" data-point="<?=($member['mb_point'] ?: 0)?>">
                전액사용 (보유: <strong><?=number_format($member['mb_point']);?></strong>P)
              </label>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">
              <strong>결제금액</strong>
            </label>
            <div class="col-sm-8">
              <span id="total_price" class="form_desc">0</span>원
            </div>
          </div>
        </div>
        <div class="so_btn_wr">
          <button type="submit" class="btn_so_order">
            <img src="<?=THEMA_URL?>/assets/img/icon_order.png" alt="">
            주문하기
          </button>
        </div>
      </div>

      <div class="so_item_wr">
        <div class="so_sch_wr">
          <div class="so_sch_hd">품목 목록</div>
          <input type="text" id="ipt_so_sch" class="ipt_so_sch" placeholder="품목명 검색 후 추가하세요.">
          <div class="so_sch_pop">
            <p>상품명을 입력 후 간편하게 추가할 수 있습니다. 상품명 일부만 입력해도 자동완성됩니다.</p>
            <p>상품명을 모르시면 '상품검색' 버튼을 눌러주세요.</p>
            <p><button type="button" class="btn_so_sch">상품검색</button></p>
          </div>
        </div>
        
        <div class="no_item_info">
        	<img src="<?=THEMA_URL?>/assets/img/icon_box.png" alt=""><br>
        	<p>주문하실 품목을 검색한 후 추가하시면<br>추가된 모든 품목은 간편하게 주문하실 수 있습니다.</p>
        	<p class="txt_point">품목명을 모르시면 “품목찾기”버튼을 클릭해주세요.</p>
        </div>

        <ul id="so_item_list" class="so_item_list">
          <?php /* ?>
          <li class="flex">
            <input type="hidden" name="it_id[]" value="">
            <input type="hidden" name="it_price[]" value="">
            <div class="it_info_wr">
              <img class="it_img" src="/img/no_img.png" onerror="this.src='/img/no_img.png';">
              <div class="it_info">
                <p class="it_name">
                  ASH-120 (설치) (판매)
                  <select name="io_id[]">
                    <option data-price="" value="">연분홍 > XXL(110)</option>
                  </select>
                </p>
                <p class="it_price">
                  판매가 : 30,800원
                  <br>└5개 이상 구매 시 26,800원
                  <br>└20개 이상 구매 시 20,400원
                </p>
              </div>
            </div>
            <div class="it_qty_wr">
              <div class="input-group">
                <div class="input-group-btn">
                  <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>
                </div>
                <input type="text" name="ct_qty[]" value="1" class="form-control input-sm">
                <div class="input-group-btn">
                  <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>
                </div>
              </div>
              <div class="it_qty_desc">
                본 상품은 5개 주문 시 한 박스로 포장됩니다.
              </div>
            </div>
            <div class="it_price_wr flex space-between">
              <p class="it_price">30,800원</p>
              <button type="button" class="btn_del_item">삭제</button>
            </div>
          </li>
          <?php */ ?>
        </ul>
        <div class="total_price_wr">
          총 결제 금액 : 
          <span class="total_price">0원</span>
        </div>

      </div>

      <!-- 주문하시는 분 입력 시작 { -->
      <section id="sod_frm_orderer" style="margin-bottom:0px; display: none;">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>  주문하시는 분</strong></div>
          <div class="panel-body">
            <div class="form-group has-feedback">
              <label class="col-sm-2 control-label" for="od_name"><b>이름</b><strong class="sound_only">필수</strong></label>
              <div class="col-sm-3">
                <input type="text" name="od_name" value="<?php echo get_text($member['mb_name']); ?>" id="od_name" required class="form-control input-sm" maxlength="20">
                <span class="fa fa-check form-control-feedback"></span>
              </div>
            </div>
            <?php if (!$is_member) { // 비회원이면 ?>
            <div class="form-group has-feedback">
              <label class="col-sm-2 control-label" for="od_pwd"><b>비밀번호</b><strong class="sound_only">필수</strong></label>
              <div class="col-sm-3">
                <input type="password" name="od_pwd" id="od_pwd" required class="form-control input-sm" maxlength="20">
                <span class="fa fa-lock form-control-feedback"></span>
              </div>
              <div class="col-sm-7">
                <span class="help-block">영,숫자 3~20자 (주문서 조회시 필요)</span>
              </div>
            </div>
            <?php } ?>
            <div class="form-group has-feedback">
              <label class="col-sm-2 control-label" for="od_tel"><b>전화번호</b><strong class="sound_only">필수</strong></label>
              <div class="col-sm-3">
                <input type="text" name="od_tel" value="<?php echo get_text($member['mb_tel']); ?>" id="od_tel" required class="form-control input-sm" maxlength="13">
                <span class="fa fa-phone form-control-feedback"></span>
              </div>
            </div>
            <div class="form-group has-feedback">
              <label class="col-sm-2 control-label" for="od_hp"><b>핸드폰</b></label>
              <div class="col-sm-3">
                <input type="text" name="od_hp" value="<?php echo get_text($member['mb_hp']); ?>" id="od_hp" class="form-control input-sm" maxlength="13">
                <span class="fa fa-mobile form-control-feedback"></span>
              </div>
            </div>

            <div class="form-group has-feedback">
              <label class="col-sm-2 control-label"><b>주소</b><strong class="sound_only">필수</strong></label>
              <div class="col-sm-8">
                <label for="od_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                <label>
                  <input type="text" name="od_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2'] ?>" id="od_zip" required class="form-control input-sm" size="6" maxlength="6">
                </label>
                <label>
                  <button type="button" class="btn btn-black btn-sm" style="margin-top:0px;" onclick="win_zip('forderform', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon');">주소 검색</button>
                </label>

                <div class="addr-line">
                  <label class="sound_only" for="od_addr1">기본주소<strong class="sound_only"> 필수</strong></label>
                  <input type="text" name="od_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" id="od_addr1" required class="form-control input-sm" size="60" placeholder="기본주소">
                </div>

                <div class="addr-line">
                  <label class="sound_only" for="od_addr2">상세주소</label>
                  <input type="text" name="od_addr2" value="<?php echo get_text($member['mb_addr2']) ?>" id="od_addr2" class="form-control input-sm" size="50" placeholder="상세주소">
                </div>

                <label class="sound_only" for="od_addr3">참고항목</label>
                <input type="text" name="od_addr3" value="<?php echo get_text($member['mb_addr3']) ?>" id="od_addr3" class="form-control input-sm" size="50" readonly="readonly" placeholder="참고항목">
                <input type="hidden" name="od_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']) ?>">
              </div>
            </div>


            <div class="form-group has-feedback">
              <label class="col-sm-2 control-label" for="od_email"><b>E-mail</b><strong class="sound_only"> 필수</strong></label>
              <div class="col-sm-5">
                <input type="text" name="od_email" value="<?php echo $member['mb_email']; ?>" id="od_email" required class="form-control input-sm email" size="35" maxlength="100">
                <span class="fa fa-envelope form-control-feedback"></span>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- } 주문하시는 분 입력 끝 -->

      <div class="order-info" style="margin-top: 40px;">
        <div class="top">
          <h5>배송정보</h5>
          <div class="add-ac">
            <p>배송지 선택</p>
            <ul>
              <li class="ad_sel_addr" id="ad_sel_addr_same" data-value="same">주문자와 동일</li>
              <li class="ad_sel_addr" id="od_sel_addr_new" data-value="new">신규 배송지</li>
              <?php
              if ($member['mb_id']) {
                $sql = "SELECT count(*) as cnt from {$g5['g5_shop_order_address_table']} where mb_id = '{$member['mb_id']}' ";
                $result = sql_fetch($sql);
                if ($result['cnt']) {
              ?>
              <li class="ad_sel_addr" id="order_address">배송지 목록</li>
              <?php
                }
              }
              ?>
            </ul>
          </div>
        </div>
        <div class="table-list3">
          <ul>
            <li>
              <strong>이름</strong>
              <div>
                <input class="w-240" type="text" id="od_b_name" name="od_b_name" value="<?=$member['mb_name']?>">
              </div>
            </li>
            <li>
              <strong>전화번호</strong>
              <div>
                <input class="w-240" type="text" id="od_b_tel" name="od_b_tel" value="<?=$member['mb_tel']?>">
              </div>
            </li>
            <li>
              <strong>핸드폰</strong>
              <div>
                <input class="w-240" type="text" id="od_b_hp" name="od_b_hp" value="<?=$member['mb_hp']?>">
              </div>
            </li>
            <li class="addr">
              <strong>주소</strong>
              <div>
                <div>
                  <input type="text" class="w-70" name="od_b_zip" id="od_b_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2'] ?>" required>
                  <button type="button" onclick="win_zip('forderform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">우편번호</button>
                  <input type="hidden" name="od_b_addr_jibeon" value="<?=$member['mb_hp']?>">
                </div>
                <div>
                  <input type="text" name="od_b_addr1" id="od_b_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" required  style="width: 100%;">
                  </div>
                  <div>
                  <input type="text" name="od_b_addr2" id="od_b_addr2" required value="<?php echo get_text($member['mb_addr2']).get_text($member['mb_addr_jibeon']) ?>" style="width: 100%;">
                  </div>
              </div>
            </li>
            <li>
              <strong>배송요청사항</strong>
              <input type="text"   class="w-all" name="od_memo" id="od_memo" placeholder="배송 시 요청사항을 입력해 주세요.">
              <select name="od_delivery_type" id="od_delivery_type" style="display: none;">
              <?php
              foreach($delivery_types as $type) {
                // if ( $type['user-order'] != true ) continue;
                if ( !$default['de_delivery_type_' . $type['val']] ) continue;
              ?>
                <option value="<?php echo $type['val']; ?>" <?php echo $type['val'] == $od['od_delivery_type'] ? 'selected' : ''; ?> data-type="<?php echo $type['type']; ?>"><?php echo $type['name']; ?></option>
              <?php } ?>
              </select>
            </li>
          </ul>
        </div>

        <div style="display: none;">
          <div class="top">
            <h5>매출증빙</h5>
            <div class="check-ac">
              <span class="check">
                <input type="radio" id="typereceipt1" name="ot_typereceipt" value="11" checked="checked">
                <label for="typereceipt1">
                  <span class="check_on"></span>
                </label>
                <b>세금계산서</b>
              </span>
            </div>
          </div>
          <div class="table-list3 table-list4" id="typereceipt1_view">
            <ul>
              <li>
                <div class="list-con">
                  <strong>기업명</strong>
                  <div>
                    <input type="text" name="typereceipt_bname" value="<?php echo $member['mb_entNm']; ?>" id="typereceipt_bname" maxlength="20">
                  </div>
                </div>
                <div class="list-con">
                  <strong>대표자명</strong>
                  <div>
                    <input type="text" name="typereceipt_boss_name" value="<?php echo $member['mb_giup_boss_name']; ?>" id="typereceipt_boss_name" maxlength="20">
                  </div>
                </div>
              </li>
              <li>
                <div class="list-con">
                  <strong>사업자번호</strong>
                  <div>
                    <input type="text" name="typereceipt_bnum" value="<?php echo $member['mb_giup_bnum'] ?>" id="typereceipt_bnum" maxlength="12" <?php echo $member['mb_giup_bnum'] ? ' readonly ' : ''; ?>>
                  </div>
                </div>
                <div class="list-con list-tel">
                  <strong>연락처</strong>
                  <div>
                    <input type="text" name="typereceipt_btel" value="<?php echo $member['mb_tel'] ?>" id="typereceipt_btel" maxlength="20" style="margin-left: 0;">
                  </div>
                </div>
              </li>
              <li class="addr">
                <strong>주소</strong>
                <div>
                  <div>
                    <input type="text" class="w-70" name="ot_location_zip" value="<?php echo get_text($member['mb_giup_zip1']).get_text($member['mb_giup_zip2']); ?>" id="ot_location_zip" required readonly>
                    <button type="button" onclick="win_zip('forderform', 'ot_location_zip', 'ot_location_addr1', 'ot_location_addr2', 'ot_location_addr3', 'ot_location_jibeon');">우편번호</button>
                    <input type="hidden" name="ot_location_jibeon" value="">
                  </div>
                  <div>
                    <input type="text" name="ot_location_addr1" value="<?php echo get_text($member['mb_giup_addr1']); ?>" id="ot_location_addr1" required readonly>
                    </div>
                    <div>
                    <input type="text" name="ot_location_addr2" value="<?php echo get_text($member['mb_giup_addr2']); ?>" id="ot_location_addr2">
                    </div>
                </div>
              </li>
              <li>
                <div class="list-con">
                  <strong>업태</strong>
                  <div>
                    <input type="text" name="typereceipt_buptae" value="<?php echo $member['mb_giup_buptae']; ?>" id="typereceipt_buptae" maxlength="20">
                  </div>
                </div>
                <div class="list-con">
                  <strong>업종</strong>
                  <div>
                    <input type="text" name="typereceipt_bupjong" value="<?php echo $member['mb_giup_bupjong']; ?>" id="typereceipt_bupjong" maxlength="20">
                  </div>
                </div>
              </li>
              <li class="em">
                <div class="list-con">
                  <strong>이메일</strong>
                  <div>
                    <input type="text" name="typereceipt_email" value="<?php echo $member['mb_email']; ?>" id="typereceipt_email" maxlength="20">
                  </div>
                </div>
                <div class="list-con">
                  <strong>담당자명</strong>
                  <div>
                    <input type="text" name="typereceipt_manager_name" value="<?php echo $member['mb_giup_manager_name']; ?>" id="typereceipt_manager_name" maxlength="20">
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- Modal -->
<div class="modal fade" id="couponModal" tabindex="-1" role="dialog" aria-labelledby="couponModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
		<div id="couponBox"></div>
	  </div>
    </div>
  </div>
</div>

<!-- 품목찾기 팝업 -->
<div id="item_popup_box">
  <div class="popup_box_close">
    <i class="fa fa-times"></i>
  </div>
  <iframe name="iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
</div>

<!-- 팝업 박스 시작 -->
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

  $('#popup_box').click(function() {
      close_popup_box();
  });
});

function open_popup_box(url) {
  $('html, body').addClass('modal-open');
  $("#popup_box > div").html('<iframe src="' + url + '">');
  $("#popup_box iframe").load(function() {
    $("#popup_box").show();
  });
}

function close_popup_box() {
  $('html, body').removeClass('modal-open');
  $('#popup_box').hide();
  $('#popup_box').find('iframe').remove();
}
</script>
<!-- 팝업 박스 끝 -->

<script>
var item_sale_obj = {};
var zipcode = '';
var mb_level = <?=$member['mb_level']?>;
var min_point = <?=$default['de_settle_min_point']?>;

// 구매자 정보와 동일합니다.
function gumae2baesong() {
  var f = document.forderform;

  f.od_b_name.value = f.od_name.value;
  f.od_b_tel.value  = f.od_tel.value;
  f.od_b_hp.value   = f.od_hp.value;
  f.od_b_zip.value  = f.od_zip.value;
  f.od_b_addr1.value = f.od_addr1.value;
  f.od_b_addr2.value = f.od_addr2.value;
  calculate_sendcost(String(f.od_b_zip.value));
}

// 폼 전송
function form_submit(form) {

  var point = parseInt( $('#od_temp_point').val() || 0 );
  if(point > 0 && point < min_point) {
    alert('포인트는 최소 ' + min_point + 'P 이상이어야 사용할 수 있습니다.');
    return false;
  }

  var result = false;

  $.ajax({
    url: 'ajax.simple_order.php',
    async: false,
    method: 'POST',
    cache: false,
    data: $(form).serialize(),
    dataType: 'json',
    success: function() {
      result = true;
    },
    error: function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    }
  });

  return result;
}

// 주문금액계산
function calculate_order_price() {
  var $li = $('#so_item_list li');

  var order_price = 0;
  var free_delivery = true;
  $li.each(function() {
    var it_id = $(this).find('input[name="it_id[]"]').val();
    var it_price = parseInt ( $(this).find('input[name="it_price[]"]').val() || 0 );
    var io_price = parseInt( $(this).find('select[name="io_id[]"] option:selected').data('price') || 0 );
    var ct_qty = parseInt( $(this).find('input[name="ct_qty[]"]').val() || 0 );
    var it_sc_type = parseInt( $(this).find('input[name="it_sc_type[]"]').val() || 0 );

    if(it_sc_type !== 1) {
      // 무료배송이 아닌 상품이 하나라도 있으면 유료배송
      free_delivery = false;
    }

    // 묶음할인 적용
    var sale_qty = 0;
    for(var i = 0; i < $li.length; i++) {
      var this_it_id = $($li).eq(i).find('input[name="it_id[]"]').val();
      if(this_it_id !== it_id) continue;

      var this_qty = parseInt( $li.eq(i).find('input[name="ct_qty[]"]').val() );
      if( this_qty > 0 ) {
        sale_qty += this_qty;
      }
    }
    var it_sale_cnt = 0;
    if(item_sale_obj[it_id] && item_sale_obj[it_id].it_sale_cnt) {
      for(var i = 0; i < item_sale_obj[it_id].it_sale_cnt.length; i++) {
        var sale_cnt = parseInt(item_sale_obj[it_id].it_sale_cnt[i]);
        if(sale_qty >= sale_cnt && sale_cnt > it_sale_cnt) {
          it_sale_cnt = sale_cnt;
          it_price = parseInt( mb_level === 4 ? item_sale_obj[it_id].it_sale_percent_great[i] : item_sale_obj[it_id].it_sale_percent[i] );
        }
      }
    }

    var ct_price = ( it_price + io_price ) * ct_qty;
    $(this).find('.it_price_wr .it_price span').text(number_format(it_price + io_price) + '원');
    $(this).find('.it_price_wr .ct_price').text(number_format(ct_price) + '원');
    $(this).find('input[name="ct_price[]"]').val(ct_price);
    order_price += ct_price;
  });

  var delivery_price = 0;
  if(order_price < 100000 && !free_delivery) {
    // 주문금액 10만원 미만에 무료배송상품이 아닌 상품이 있는 경우 배송비
    delivery_price = 3300;
  }

  // 주문금액
  $('input[name="org_od_price"]').val(order_price);
  $('input[name="od_price"]').val(order_price);
  $('#order_price').text(number_format(order_price));

  // 배송비
  $('#delivery_price').text(number_format(delivery_price));

  // 쿠폰
  var cp_price = parseInt( $('input[name="od_cp_price"]').val() || 0 );
  if(cp_price > order_price) {
    // 쿠폰 금액이 주문금액보다 크면 쿠폰 취소
    $('#od_coupon_cancel').click();
    return;
  }

  // 포인트
  var pt_price = parseInt( $('input[name="od_temp_point"]').val() || 0 );
  if(pt_price > order_price + delivery_price - cp_price) {
    // 포인트 사용 금액이 주문금액 + 배송비 - 쿠폰사용금액보다 크면
    $('#od_temp_point').val(order_price + delivery_price - cp_price);
    $('#od_temp_point').change();
    return;
  }

  // 총 결제금액
  $('#total_price').text(number_format( order_price + delivery_price - cp_price - pt_price ));
  $('.total_price_wr .total_price').text(number_format( order_price + delivery_price - cp_price - pt_price ) + '원');
}

// 배송비계산 (더미코드)
function calculate_sendcost(code) {
  /*var el_it_ids = $('#sod_list').find("input[name^=it_id]");

  var it_ids = $.map(el_it_ids, function(el, i) {
      return $(el).val();
  });

  $.post(
      "./ordersendcost.php",
      {
          zipcode: code,
          'it_ids[]': it_ids,
      },
      function(data) {
          var od_delivery_type = $("select[name=od_delivery_type]").val();

          if (od_delivery_type !== 'delivery1') {
              data = 0;
          }
          $("input[name=od_send_cost2]").val(data);
          $("#od_send_cost2").text(number_format(String(data)));

          zipcode = code;

          calculate_order_price();
      }
  );*/
}

// 품목 없는지 체크
function check_no_item() {
  if($('#so_item_list li').length == 0) {
    $('.no_item_info').show();
  } else {
    $('.no_item_info').hide();
  }
}

// 품목 선택
function select_items(obj, items) {
  $('body').removeClass('modal-open');
  $('#item_popup_box').hide();

  if(items.length) {
    for(var i = 0; i < items.length; i++) {
      var item = items[i];

      select_item(obj, item.io_id, item.ct_qty);
    }
  }
}

function select_item(obj, io_id, ct_qty) {
  // 묶음 할인 저장
  item_sale_obj[obj.it_id] = {
    it_sale_cnt: [
      obj.it_sale_cnt,
      obj.it_sale_cnt_02,
      obj.it_sale_cnt_03,
      obj.it_sale_cnt_04,
      obj.it_sale_cnt_05,
    ],
    it_sale_percent: [
      obj.it_sale_percent,
      obj.it_sale_percent_02,
      obj.it_sale_percent_03,
      obj.it_sale_percent_04,
      obj.it_sale_percent_05,
    ],
    it_sale_percent_great: [
      obj.it_sale_percent_great,
      obj.it_sale_percent_great_02,
      obj.it_sale_percent_great_03,
      obj.it_sale_percent_great_04,
      obj.it_sale_percent_great_05
    ],
  }

  var $li = $('<li class="flex">');
  $li.append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">')
  .append('<input type="hidden" name="it_price[]" value="' + obj.it_price + '">')
  .append('<input type="hidden" name="it_sc_type[]" value="' + obj.it_sc_type + '">')
  .append('<input type="hidden" name="cp_id[]" value="">')
  .append('<input type="hidden" name="cp_price[]" value="">');

  var $info_wr = $('<div class="it_info_wr">');
  $info_wr.append('<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">');

  var $info = $('<div class="it_info">');
  var $it_name = $('<p class="it_name">');
  // 재입고예정일
  if(obj.it_expected_warehousing_date) {
    $it_name.append('<span style="color: red; font-size:14px;">' + obj.it_expected_warehousing_date + '</span><br>');
  }
  $it_name.append(obj.it_name + ' (' + obj.gubun + ')');
  var it_price = parseInt(obj.it_price);
  var ct_price = it_price;
  if (obj.options.length) {
    var option_html = "<select name=\"io_id[]\">";
    for(var i = 0; i < obj.options.length; i++) {
      if (i === 0) {
        ct_price += parseInt(obj.options[i]['io_price']);
      }
      option_html += "<option data\-price=\"" + obj.options[i]['io_price'] + "\" value=\"" + obj.options[i]['io_id'] + "\">" + obj.options[i]['io_id'].replace(//gi, " > ") + "</option>";
    }
    option_html += "</select>";
    $it_name.append(option_html);
  } else {
    var option_html = "<input type=\"hidden\" name=\"io_id[]\" value=\"\">";
    $it_name.append(option_html);
  }

  // 상품태그
  var $it_tag = $('<p class="it_tag">');
  if(obj.it_type1 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type1_color']?>;color:<?=$default['de_it_type1_color']?>"><?=$default['de_it_type1_name']?></span>');
  }
  if(obj.it_type2 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type2_color']?>;color:<?=$default['de_it_type2_color']?>"><?=$default['de_it_type2_name']?></span>');
  }
  if(obj.it_type3 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type3_color']?>;color:<?=$default['de_it_type3_color']?>"><?=$default['de_it_type3_name']?></span>');
  }
  if(obj.it_type4 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type4_color']?>;color:<?=$default['de_it_type4_color']?>"><?=$default['de_it_type4_name']?></span>');
  }
  if(obj.it_type5 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type5_color']?>;color:<?=$default['de_it_type5_color']?>"><?=$default['de_it_type5_name']?></span>');
  }

  var $it_price = $('<p class="it_price">');
  $it_price.append('판매가 : ' + number_format(it_price));

  if(item_sale_obj[obj.it_id].it_sale_cnt && item_sale_obj[obj.it_id].it_sale_cnt.length) {
    for(var i = 0; i <= item_sale_obj[obj.it_id].it_sale_cnt.length; i++) {
      var it_sale_cnt = parseInt(item_sale_obj[obj.it_id].it_sale_cnt[i]);
      if(it_sale_cnt) {
        var it_sale_price = mb_level === 4 ? item_sale_obj[obj.it_id].it_sale_percent_great[i] : item_sale_obj[obj.it_id].it_sale_percent[i];
        $it_price.append('<br>└' + it_sale_cnt + '개  이상 구매 시 ' + number_format(it_sale_price) + '원');
      }
    } 
  }

  var $prod_memo = $('<div class="flex">');
  $prod_memo.append(
    '<div class="prod_memo_hd">요청사항</div>',
    '<input type="text" class="ipt_prod_memo" name="prodMemo[]" placeholder="상품관련 요청사항을 입력하세요.">'
  );

  $info.append(
    $it_name,
    $it_tag,
    $it_price,
    $prod_memo
    )
  .appendTo($info_wr);
  $li.append($info_wr);

  var $qty_wr = $('<div class="it_qty_wr">');
  $qty_wr.append('\
    <div class="input-group">\
      <div class="input-group-btn">\
          <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>\
      </div>\
      <input type="text" name="ct_qty[]" value="1" class="form-control input-sm">\
      <div class="input-group-btn">\
          <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>\
      </div>\
  </div>\
  ');
  if(parseInt(obj.it_delivery_cnt)) {
    $qty_wr.append('\
      <div class="it_qty_desc">\
        본 상품은 ' + obj.it_delivery_cnt + '개 주문 시 한 박스로 포장됩니다.\
      </div>\
    ');
  }
  $qty_wr.appendTo($li);

  var $price_wr = $('<div class="it_price_wr flex space-between">');
  $price_wr
  .append(
    '<div><p class="it_price">단가 : <span>' + number_format(it_price) + '원</span></p>' +
    '<p class="ct_price">' + number_format(ct_price) + '원</p></div>',
    '<input type="hidden" name="ct_price[]" value="' + ct_price + '">',
    '<button type="button" class="btn_del_item">삭제</button>'
  )
  .appendTo($li);

  $('#so_item_list').append($li);

  if(io_id) {
    $li.find('select[name="io_id[]"]').val(io_id);
  }

  if(ct_qty) {
    $li.find('input[name="ct_qty[]"]').val(ct_qty);
  }

  calculate_order_price();
  $('#ipt_so_sch').val('').next().focus();

  check_no_item();
}

$(function() {
  // 품목 삭제
  $(document).on('click', '.btn_del_item', function() {
    var $li = $(this).closest('li');
    $li.remove();

    calculate_order_price();
    check_no_item();
  });

  // 품목 검색
  $('#ipt_so_sch').flexdatalist({
    minLength: 1,
    url: 'ajax.get_item.php',
    cache: true, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    selectionRequired: true,
    focusFirstResult: true,
    searchIn: ["it_name","it_model","id", "it_name_no_space"],
    visibleCallback: function($li, item, options) {
      var $item = {};
      $item = $('<span>')
        .html("[" + item.gubun + "] " + item.it_name + " (" + item.it_cust_price + "원)");

      $item.appendTo($li);
      return $li;
    },
  })
  .on("select:flexdatalist", function(event, obj, options) {
    select_item(obj);
  });

  // 포인트 전액 사용
  $('#chk_point_all').click(function() {
    var $point = $('#od_temp_point');
    var total_point = $(this).data('point');

    if($point.val() == total_point) {
      $(this).prop('checked', true);
      return false;
    }

    var checked = $(this).prop('checked');
    if(checked) {
      $point.val($(this).data('point'));
    }

    calculate_order_price();
  });
  // 포인트 입력
  $('#od_temp_point').on('change paste keyup', function() {
    var $chk_all = $('#chk_point_all');
    var total_point = $chk_all.data('point');

    var point = $(this).val().replace(/[\D\s\._\-]+/g, "");
    if(point < 0) point = 0;
    if(point > total_point) point = total_point;

    if(point == total_point)
      $chk_all.prop('checked', true);
    else
      $chk_all.prop('checked', false);
    
    $(this).val(point);
    
    calculate_order_price();
  });

  // 상품수량변경
  $(document).on('click', '.it_qty_wr button', function() {
    var mode = $(this).text();
    var this_qty;
    var $ct_qty = $(this).closest('.it_qty_wr').find('input[name^=ct_qty]');

    switch(mode) {
      case '증가':
        this_qty = parseInt($ct_qty.val().replace(/[^0-9]/, "")) + 1;
        $ct_qty.val(this_qty);
        break;
      case '감소':
        this_qty = parseInt($ct_qty.val().replace(/[^0-9]/, "")) - 1;
        if(this_qty < 1) this_qty = 1
        $ct_qty.val(this_qty);
        break;
    }

    calculate_order_price();
  });
  $(document).on('change paste keyup', 'input[name="ct_qty[]"]', function() {
    if($(this).val() < 1)
      $(this).val(1);

    calculate_order_price();
  });

  // 쿠폰
  $("#od_coupon_btn").click(function() {
    var $this = $(this);
    var price = parseInt($("input[name=org_od_price]").val());
    if(price <= 0) {
        alert('금액이 0원이므로 쿠폰을 사용할 수 없습니다.');
        return false;
    }

    $('#couponModal').modal('show');

    $.post("./ordercoupon.php", { price: price },
      function(data) {
        $("#couponBox").html(data);
      }
    );
  });
  $(document).on("click", ".od_cp_apply", function() {
		var $el = $(this).closest("tr");
    var cp_id = $el.find("input[name='o_cp_id[]']").val();
    var price = parseInt($el.find("input[name='o_cp_prc[]']").val());
    var subj = $el.find("input[name='o_cp_subj[]']").val();
    var od_price = parseInt($("input[name=org_od_price]").val());

    if(price == 0) {
      if(!confirm(subj+"쿠폰의 할인 금액은 "+price+"원입니다.\n쿠폰을 적용하시겠습니까?")) {
        return false;
      }
    }

    if(od_price - price <= 0) {
      alert("쿠폰할인금액이 주문금액보다 크므로 쿠폰을 적용할 수 없습니다.");
      return false;
    }

    $("input[name=od_cp_id]").val(cp_id);
    $("input[name=od_cp_price]").val(price);
    $("#od_cp_price").text(number_format(String(price)));
    calculate_order_price();
		$('#couponModal').modal('hide');
    $("#od_coupon_btn").text("쿠폰변경").focus();
    if(!$("#od_coupon_cancel").size())
      $("#od_coupon_btn").after(" <button type=\"button\" id=\"od_coupon_cancel\" class=\"btn btn-black btn-sm btn_frmline\">쿠폰취소</button>");
  });

  $(document).on("click", "#od_coupon_close", function() {
  $('#couponModal').modal('hide');
    $("#od_coupon_btn").focus();
  });

  $(document).on("click", "#od_coupon_cancel", function() {
    var org_price = $("input[name=org_od_price]").val();
    $("input[name=od_cp_id]").val('');
    $("input[name=od_cp_price]").val(0);
    $("#od_cp_price").text(0);
    calculate_order_price();
    $("#od_coupon_btn").text("쿠폰").focus();
    $(this).remove();
  });

  // 배송지선택
  $('.add-ac').find('p').on('click',function(){
    $(this).siblings('ul').stop().toggle();

    $('.add-ac').find('ul li').on('click',function(){
      let textValue = $(this).text();
      $(this).parents('ul').siblings('p').text(textValue);
      $(this).parents('ul').stop().hide();
    });
  });
  // 배송지선택
  $(".ad_sel_addr").on("click", function() {
    if($(this).attr("id") == "order_address"){
      return false;
    }
    var addr = $(this).attr("data-value").split(String.fromCharCode(30));

    if (addr[0] == "same") {
      gumae2baesong();
    } else {
      if(addr[0] == "new") {
        for(i = 0; i < 10; i++) {
          addr[i] = "";
        }
      }
      var f = document.forderform;
      f.od_b_name.value        = addr[0];
      f.od_b_tel.value         = addr[1];
      f.od_b_hp.value          = addr[2];
      f.od_b_zip.value         = addr[3] + addr[4];
      f.od_b_addr1.value       = addr[5];
      f.od_b_addr2.value       = addr[6];

      var zip1 = addr[3].replace(/[^0-9]/g, "");
      var zip2 = addr[4].replace(/[^0-9]/g, "");

      var code = String(zip1) + String(zip2);

      if(zipcode != code) {
        calculate_sendcost(code);
      }
    }
  });

  // 배송지목록
  $("#order_address").on("click", function() {
    var url = "<?php echo G5_SHOP_URL;?>/orderaddress.php";
    open_popup_box(url);
    return false;
  });

  // 품목찾기 팝업
  $('#item_popup_box').click(function() {
    $('body').removeClass('modal-open');
    $('#item_popup_box').hide();
  });
  $('.btn_so_sch').click(function(e) {
    var url = 'pop.item.select.php';

    $('#item_popup_box iframe').attr('src', url);
    $('body').addClass('modal-open');
    $('#item_popup_box').show();
  });

  // 상품검색 팝업
  $(document).on('focus', '.ipt_so_sch', function() {
    $('.so_sch_pop').show();
  });
  $(document).on('click', function(e) {
    if($(e.target).closest('.so_sch_wr').length > 0) 
      return;

    $('.so_sch_pop').hide();
  });
    
  // 처음 팝업
  $('.so_sch_pop').show();

  <?php
  if($_GET['dc_id']) {
    $dc_id = get_search_string($_GET['dc_id']);
    $sql = "
      SELECT
        x.it_id,
        x.it_name,
        x.it_model,
        x.it_price,
        x.it_price_dealer2,
        x.it_cust_price,
        x.it_rental_price,
        x.ca_id,
        it_img1 as it_img,
        it_delivery_cnt,
        it_sc_type,
        it_sale_cnt,
        it_sale_cnt_02,
        it_sale_cnt_03,
        it_sale_cnt_04,
        it_sale_cnt_05,
        it_sale_percent,
        it_sale_percent_02,
        it_sale_percent_03,
        it_sale_percent_04,
        it_sale_percent_05,
        it_sale_percent_great,
        it_sale_percent_great_02,
        it_sale_percent_great_03,
        it_sale_percent_great_04,
        it_sale_percent_great_05,
        it_type1,
        it_type2,
        it_type3,
        it_type4,
        it_type5,
        it_expected_warehousing_date,
        count(*) as qty
      FROM
        eform_document d
      LEFT JOIN
        eform_document_item i ON d.dc_id = i.dc_id
      LEFT JOIN
        g5_shop_item x ON i.it_code = x.ProdPayCode and
        (
          ( i.gubun = '00' and x.ca_id like '10%' ) or
          ( i.gubun = '01' and x.ca_id like '20%' )
        )
      WHERE
        d.dc_id = UNHEX('$dc_id')
      GROUP BY
        i.it_code
    ";
    $result = sql_query($sql, true);
    while($row = sql_fetch_array($result)) {
      $data = $row;

      $option_sql = "SELECT *
          FROM
              {$g5['g5_shop_item_option_table']}
          WHERE
              it_id = '{$data['it_id']}'
              and io_type = 0 -- 선택옵션
          ORDER BY
              io_no ASC
      ";
      $option_result = sql_query($option_sql);

      $data['options'] = [];
      while ($option_row = sql_fetch_array($option_result)) {
          $data['options'][] = $option_row;
      }

      $gubun = $cate_gubun_table[substr($row['ca_id'], 0, 2)];
      $gubun_text = '판매';
      if($gubun == '01') $gubun_text = '대여';
      else if($gubun == '02') $gubun_text = '비급여';

      $data['gubun'] = $gubun_text;

      $data = json_encode($data);
  ?>
  select_item(<?=$data ?: '{}'?>, null, <?=$row['qty'] ?: 1?>);
  <?php
    }
  }
  ?>
});
</script>

<?php include_once("./_tail.php"); ?>
