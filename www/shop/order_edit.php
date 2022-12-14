<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '주문신청';
include_once("./_head.php");

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch(" SELECT * FROM g5_shop_order WHERE od_id = '$od_id' and mb_id = '{$member['mb_id']}' ");
if(!$od)
    alert('주문이 존재하지 않습니다.');

$sql = "
    SELECT * FROM
        g5_shop_cart
    WHERE
        od_id = '$od_id' and
        mb_id = '{$member['mb_id']}' and
        ct_status not in ('취소', '주문무효')
";
$result = sql_query($sql);

$carts = [];
while($ct = sql_fetch_array($result)) {
    if($ct['ct_status'] !== '준비')
        alert('상품준비 단계가 아닌 상품이 있어서 주문서 수정이 불가능합니다.');
    
    $it = sql_fetch("
        SELECT
            it_id,
            it_name,
            it_model,
            it_price,
            it_price_dealer2,
            it_cust_price,
            it_rental_price,
            ca_id,
            it_img1 as it_img,
            it_delivery_cnt,
            it_sc_type,
            it_sc_price,
            it_even_odd,
            it_even_odd_price,
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
            it_type6,
            it_type7,
            it_type8,
            it_type9,
            it_type10,
            it_expected_warehousing_date,
            it_buy_min_qty,
            it_buy_max_qty,
            it_buy_inc_qty
        FROM
            g5_shop_item
        WHERE
            it_id = '{$ct['it_id']}'
    ");

    $option_sql = "SELECT *
        FROM
            {$g5['g5_shop_item_option_table']}
        WHERE
            it_id = '{$it['it_id']}'
            AND io_type = 0 -- 선택옵션
            AND io_use = 1 -- 사용중 옵션
        ORDER BY
            io_no ASC
    ";
    $option_result = sql_query($option_sql);

    $it['options'] = [];
    while ($option_row = sql_fetch_array($option_result)) {
        $it['options'][] = $option_row;
    }

    $gubun = $cate_gubun_table[substr($it['ca_id'], 0, 2)];
    $gubun_text = '판매';
    if($gubun == '01') $gubun_text = '대여';
    else if($gubun == '02') $gubun_text = '비급여';

    $it['gubun'] = $gubun_text;

    // 우수사업소 가격
    if($member['mb_level'] == 4 && $it['it_price_dealer2']) {
      $it['it_price'] = $it['it_price_dealer2'];
    }
    unset($it['it_price_dealer2']);

    // 사업소별 판매가
    $entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$it['it_id']}' and mb_id = '{$member['mb_id']}' ");
    if($entprice['it_price']) {
      $it['it_sale_cnt'] = 0;
      $it['it_sale_cnt_02'] = 0;
      $it['it_sale_cnt_03'] = 0;
      $it['it_sale_cnt_04'] = 0;
      $it['it_sale_cnt_05'] = 0;
      $it['it_price'] = $entprice['it_price'];
    }

    $ct['it'] = $it;

    $carts[] = $ct;
}

sql_query(" UPDATE g5_shop_order SET od_is_editing = 1 WHERE od_id = '$od_id' ");

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$SKIN_URL = G5_SKIN_URL.'/apms/order/'.$skin_name;
add_stylesheet('<link rel="stylesheet" href="'.$SKIN_URL.'/css/product_order_210324.css?v=210910">');
add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_order.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
add_javascript(G5_POSTCODE_JS, 0);

?>

<section class="wrap">
  <div class="sub_section_tit">주문서 수정</div>
  <div class="inner">
    <form id="simple_order" name="forderform" class="form-horizontal" action="order_edit_result.php" method="post" onsubmit="return form_submit(this);" onkeydown="if(event.keyCode==13) return false;">
      <input type="hidden" name="org_od_price" value="0">
      <input type="hidden" name="od_price" value="0">
      <input type="hidden" name="od_settle_case" value="월 마감 정산">
      <input type="hidden" name="od_id" value="<?=$od_id?>">
      <input type="hidden" name="od_send_cost" value="0">
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
              <strong>결제금액</strong>
            </label>
            <div class="col-sm-8">
              <span id="total_price" class="form_desc">0</span>원
            </div>
          </div>
        </div>
        <div class="so_btn_wr">
          <button type="submit" class="btn_so_order active">
            <img src="<?=THEMA_URL?>/assets/img/icon_order.png" alt="">
            주문수정하기
          </button>
        </div>
      </div>

      <div class="so_item_wr">
        <div class="so_sch_wr">
          <div class="flex space-between">
            <div class="so_sch_hd">상품정보</div>
            <button type="button" class="btn_so_sch">상품검색</button>
          </div>
          <div class="ipt_so_sch_wr">
            <img src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png" >
            <input type="text" id="ipt_so_sch" class="ipt_so_sch" placeholder="여기에 추가할 상품명을 입력해주세요">
          </div>
          <div class="so_sch_pop">
            <p>상품명을 입력 후 간편하게 추가할 수 있습니다.<br> 상품명 일부만 입력해도 자동완성됩니다.</p>
            <!-- <p>상품명을 모르시면 '상품검색' 버튼을 눌러주세요.</p>
            <p><button type="button" class="btn_so_sch">상품검색</button></p> -->
          </div>
        </div>

        <div class="so_item_list_hd">추가 된 상품 목록</div>
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
              <li class="ad_sel_addr" id="pen_address">수급자 목록</li>
            </ul>
          </div>
        </div>
        <div class="table-list3">
          <ul>
            <li>
              <strong>이름</strong>
              <div>
                <input class="w-240" type="text" id="od_b_name" name="od_b_name" value="<?=$od['od_b_name']?>">
              </div>
            </li>
            <li>
              <strong>전화번호</strong>
              <div>
                <input class="w-240" type="text" id="od_b_tel" name="od_b_tel" value="<?=$od['od_b_tel']?>">
              </div>
            </li>
            <li>
              <strong>핸드폰</strong>
              <div>
                <input class="w-240" type="text" id="od_b_hp" name="od_b_hp" value="<?=$od['od_b_hp']?>">
              </div>
            </li>
            <li class="addr">
              <strong>주소</strong>
              <div>
                <div>
                  <input type="text" class="w-70" name="od_b_zip" id="od_b_zip" value="<?php echo $od['od_b_zip1'].$od['od_b_zip1'] ?>">
                  <button type="button" onclick="win_zip('forderform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">우편번호</button>
                  <input type="hidden" name="od_b_addr_jibeon" value="<?=$od['od_b_addr_jibeon']?>">
                </div>
                <div>
                  <input type="text" name="od_b_addr1" id="od_b_addr1" value="<?php echo get_text($od['od_b_addr1']) ?>" required  style="width: 100%;">
                  </div>
                  <div>
                  <input type="text" name="od_b_addr2" id="od_b_addr2" value="<?php echo get_text($od['od_b_addr2']) ?>" style="width: 100%;">
                  </div>
              </div>
            </li>
            <li>
              <strong>배송요청사항</strong>
              <input type="text" class="w-all" name="od_memo" id="od_memo" value="<?php echo get_text($od['od_memo']); ?>" placeholder="배송 시 요청사항을 입력해 주세요.">
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
      </div>
    </form>
  </div>
</section>

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
var form_sent = false;
function form_submit(form) {

    var _item = [];
    var _check = true;
    $("#so_item_list li").each(function(index) {
      if( parseInt( $(this).find("input[name='it_buy_max_qty[]']").val() ) > 1 ) {

        var array_nm = $(this).find("input[name='it_id[]']").val();

        if( ! _item[ array_nm + '_max_qty' ] ) { _item[ array_nm + '_max_qty' ] = parseInt( $(this).find("input[name='it_buy_max_qty[]']").val() ); }
        if( ! _item[ array_nm + '_sum_qty' ] ) { _item[ array_nm + '_sum_qty' ] = 0; }
        if( ! _item[ array_nm + '_item_nm' ] ) { _item[ array_nm + '_item_nm' ] = $(this).find("p.it_name").contents().get(0).nodeValue; }

        _item[ array_nm + '_sum_qty' ] +=  parseInt( $(this).find("input[name='ct_qty[]']").val() );

        if( _item[ array_nm + '_sum_qty' ] > _item[ array_nm + '_max_qty' ] ) {

          var _txt = "";
          _txt += "선택옵션 개수 총합 "+number_format(String( _item[ array_nm + '_max_qty' ] ))+"개 이하로 주문해 주십시오. \n\n";
          _txt += "제품명: " + _item[ array_nm + '_item_nm' ] + " \n";
          _txt += "최대 구매수량: " + _item[ array_nm + '_max_qty' ] + "개 ( " + (_item[ array_nm + '_sum_qty' ]-_item[ array_nm + '_max_qty' ]) + "개 초과 )";
          alert( _txt );

          _check = false;
          return false;
        }

      }
    });
    if( ! _check ) { return false; }


    if(form_sent)
      return false;

    form_sent = true;

    var result = false;

    var $li = $('#so_item_list li');
    $li.each(function() {
        if($(this).find('input[name="deleted[]"]').val() != '1')
            result = true;
    });

    if(!result) {
        form_sent = false;
        alert('상품을 하나 이상 선택해주세요.');
    }

    return result;
}

// 주문금액계산
function calculate_order_price() {
  var $li = $('#so_item_list li');

  var order_price = 0;
  var order_price_type0 = 0;
  var delivery_total = 0;
  var free_delivery = true;
  var odd_qty = 0;
  var odd_price = 0;
  var even_qty = 0;
  var even_price = 0;
  var charge_price = 0; // 유료배송비
  $li.each(function() {
    var it_id = $(this).find('input[name="it_id[]"]').val();
    var it_price = parseInt ( $(this).find('input[name="it_price[]"]').val() || 0 );    
    var it_sc_type = $(this).find('input[name="it_sc_type[]"]').val();
    var io_price = parseInt( $(this).find('select[name="io_id[]"] option:selected').data('price') || 0 );
    var io_type = $(this).find('input[name="io_type[]"]').val() || '0';
    var supply_price = parseInt( $(this).find('input[name="io_price[]"]').val() || 0 );
    var ct_qty = parseInt( $(this).find('input[name="ct_qty[]"]').val() || 0 );
    


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

    var ct_price;

    if (io_type === '0') {
      ct_price = ( it_price + io_price ) * ct_qty
      $(this).find('.it_price_wr .it_price span').text(number_format(it_price + io_price) + '원');
    } else if (io_type === '1') {
      ct_price = supply_price * ct_qty
      $(this).find('.it_price_wr .it_price span').text(number_format(supply_price) + '원');
    }

    $(this).find('.it_price_wr .ct_price').text(number_format(ct_price) + '원');
    $(this).find('input[name="ct_price[]"]').val(ct_price);
    order_price += ct_price;
    if( it_sc_type == 0 || it_sc_type == 1 || it_sc_type == 2 || it_sc_type == 3 ) { order_price_type0 += ct_price; }
  });

  // 주문금액
  $('input[name="org_od_price"]').val(order_price);
  $('input[name="od_price"]').val(order_price);
  $('#order_price').text(number_format(order_price));

  // 배송비
  //var od_send_cost2 = parseInt($('input[name=od_send_cost2]').val());
  //$('#delivery_price').text(number_format(delivery_price + od_send_cost2));
  var tmp_delivery_price = 0;
  var tmp_delivery_total = 0;
  var tmp_delivery_type0 = 0;  
  $li.each(function() {
    var _price = parseInt( $(this).find('input[name="it_delivery_price[]"]').val() ); 
    var _price_text = $(this).find('.ct_delivery_price').text();
    
    var qty = $(this).find('input[name="ct_qty[]"]').val();
    var qty_old = $(this).find('input[name="ct_qty_old[]"]').val();    
    var it_sc_type = $(this).find('input[name="it_sc_type[]"]').val();
    
    if( (qty != qty_old) || (_price_text.length == 0) ) {

      $.ajax({
        url: 'ajax.simple_order_delivery_cost.php', async: false, method: 'POST', cache: false, dataType: 'json',
        data: { 
            it_id: $(this).find('input[name="it_id[]"]').val(),
            qty: $(this).find('input[name="ct_qty[]"]').val(), 
            price: $(this).find('input[name="ct_price[]"]').val()
        },
        success: function(data) {
          _price = data.data.cost;
        },
        error: function($xhr) {
          form_loading = false;
          var data = $xhr.responseJSON;
          alert(data && data.message);
        }
      });

    }
    
    if( it_sc_type != 0 && it_sc_type != 1 && it_sc_type != 2 && it_sc_type != 3 ) {
      tmp_delivery_price += parseInt( _price );
      $(this).find('.ct_delivery_price').text( "배송비: " + ((_price>0)?number_format(_price)+"원":"무료") );
    } else {
      $(this).find('.ct_delivery_price').text( "* 주문금액 <?=number_format($default['de_send_conditional']);?>원 미만시 유료배송, <?=number_format($default['de_send_conditional']);?>원 이상 무료배송 상품");
    }
    
    tmp_delivery_total += parseInt( _price );

    $(this).find('input[name="it_delivery_price[]"]').val( _price );    
    $(this).find('input[name="ct_qty_old[]"]').val( qty );    

  });

  var send_cost_limit = "<?=$default['de_send_cost_limit']; ?>";
  var send_cost_list = "<?=$default['de_send_cost_list']; ?>";    
  send_cost_limit = send_cost_limit.split(";");
  send_cost_list = send_cost_list.split(";");
  
  if( order_price_type0 > 0 ) {
    for (let i=0; i < send_cost_limit.length; i++) {
      if(order_price_type0 < send_cost_limit[i]) { tmp_delivery_type0 = send_cost_list[i]; break; }
    }
  }

  delivery_price = parseInt(tmp_delivery_price)+parseInt(tmp_delivery_type0);
  
  $('#delivery_price').text(number_format(delivery_price));
  $('input[name="od_send_cost"]').val(delivery_price);

  //$('#delivery_price').text(number_format(delivery_price));
  //$('.delivery_total').text(number_format(tmp_delivery_total));
  //$('.delivery_discount').text(number_format(parseInt(tmp_delivery_total) - parseInt(tmp_delivery_type0)));


  // 총 결제금액
  $('#total_price').text(number_format( order_price + delivery_price ));
  $('.total_price_wr .total_price').text(number_format( order_price + delivery_price ) + '원');
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

// 품목 선택
function select_items(obj, items) {
  $('body').removeClass('modal-open');
  $('#item_popup_box').hide();
	$.ajax({
      url: "./ajax.get_item.php",
      type: "POST",
      data: {
        "it_id": obj.it_id
      },
      dataType: "json",
      async: false,
      cache: false,
      success: function(data, textStatus) {
        if(data["is_buy"] == 1){
			alert("이미 구매한 이벤트 상품으로 주문이 제한되었습니다.");	
			//$('#ipt_so_sch').val("");
			return false;
		}else if(data["soldout_ck"] == 1){
			alert("품절 상품으로 주문이 제한되었습니다.");	
			//$('#ipt_so_sch').val("");
			return false;
		}else{
			if(items.length) {
				for(var i = 0; i < items.length; i++) {
				  var item = items[i];

				  var _qty = "";
				  if( parseInt(obj.it_buy_inc_qty) < parseInt(item.ct_qty) ) {
					_qty = item.ct_qty;
				  } else { _qty = ( (obj.it_buy_inc_qty)?(obj.it_buy_inc_qty):("1") ); }

				  select_item(obj, item.io_id, _qty);

				  //select_item(obj, item.io_id, item.ct_qty);
				}
			  }
		}
      }
    });	  
}

function select_item(obj, io_id, ct_qty, ct_id, io_type) {
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
  .append('<input type="hidden" name="it_buy_min_qty[]" value="' + obj.it_buy_min_qty + '">')
  .append('<input type="hidden" name="it_buy_max_qty[]" value="' + obj.it_buy_max_qty + '">')
  .append('<input type="hidden" name="it_buy_inc_qty[]" value="' + obj.it_buy_inc_qty + '">')
  .append('<input type="hidden" name="it_sc_type[]" value="' + obj.it_sc_type + '">')  
  .append('<input type="hidden" name="it_delivery_price[]" value="' + obj.it_delivery_price + '">')
  .append('<input type="hidden" name="ct_qty_old[]" value="">');

  if(ct_id) {
      $li.append('<input type="hidden" name="ct_id[]" value="' + ct_id + '">')
      .append('<input type="hidden" name="deleted[]" value="0">')
      .append('<input type="hidden" name="io_type" value="' + io_type + '">');
  }

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
      option_html += "<option data\-price=\"" + obj.options[i]['io_price'] + "\" value=\"" + obj.options[i]['io_id'] + "\">" + obj.options[i]['io_id'].replace(//gi, " > ") + " (+" +obj.options[i]['io_price']+"원)"+ "</option>";
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
  if(obj.it_type6 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type6_color']?>;color:<?=$default['de_it_type6_color']?>"><?=$default['de_it_type6_name']?></span>');
  }
  if(obj.it_type7 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type7_color']?>;color:<?=$default['de_it_type7_color']?>"><?=$default['de_it_type7_name']?></span>');
  }
  if(obj.it_type8 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type8_color']?>;color:<?=$default['de_it_type8_color']?>"><?=$default['de_it_type8_name']?></span>');
  }
  if(obj.it_type9 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type9_color']?>;color:<?=$default['de_it_type9_color']?>"><?=$default['de_it_type9_name']?></span>');
  }
  if(obj.it_type10 == '1') {
    $it_tag.append('<span style="display:inline-block;margin-right:4px;border:1px solid <?=$default['de_it_type10_color']?>;color:<?=$default['de_it_type10_color']?>"><?=$default['de_it_type10_name']?></span>');
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
    $prod_memo,
    '<div class="ct_delivery_price" style="font-size:10px;"></div>'
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

  if( (ct_qty)&&( parseInt(ct_qty) > parseInt(obj.it_buy_inc_qty) ) ){
    $li.find('input[name="ct_qty[]"]').val(ct_qty);
  } else {$li.find('input[name="ct_qty[]"]').val(obj.it_buy_inc_qty);}

  if(ct_qty) {
    $li.find('input[name="ct_qty[]"]').val(ct_qty);
  }

  calculate_order_price();
  $('#ipt_so_sch').val('').next().focus();
}


$(function() {
  var idleTime = 0;
  var idleInterval = setInterval(timerIncrement, 60*1000); // 1 minute

  //일정시간 움직임이 있으면 초기화
  $(this).mousemove(function (e) { idleTime = 0; });
  $(this).keypress(function (e) { idleTime = 0; });

  function timerIncrement() {
    idleTime = idleTime + 1;
    if (idleTime >= 10) { // 10 minutes
      window.location.href = './orderinquiry.php';
      clearInterval(idleInterval);
    }
  }

  // 옵션 상품 변경시 가격 변동
  $(document).on('change', 'select[name="io_id[]"]', function() {    
    $(this).closest('li').find('input[name="ct_qty_old[]"]').val(0);
    calculate_order_price();
  });

  // 품목 삭제
  $(document).on('click', '.btn_del_item', function() {
    var $li = $(this).closest('li');
    if($li.find('input[name="ct_id[]"]').length > 0) {
        // 기존 주문 상품이면
        $li.find('input[name="deleted[]"]').val('1');
        $li.hide();
    } else {
        // 신규 추가 상품이면
        $li.remove();
    }

    calculate_order_price();
  });

  // 품목 검색
  $('#ipt_so_sch').flexdatalist({
    minLength: 1,
    url: 'ajax.get_item.php',
    cache: false, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    selectionRequired: true,
    focusFirstResult: true,
    searchIn: ["it_name","it_model","id", "it_name_no_space"],
    visibleCallback: function($li, item, options) {
      var $item = {};
      $item = $('<span>')
        .html("[" + item.gubun + "] " + item.it_name + " (" + number_format(item.it_price) + "원)");

      $item.appendTo($li);
      return $li;
    },
  })
  .on("select:flexdatalist", function(event, obj, options) {
    $.ajax({
      url: "./ajax.get_item.php",
      type: "POST",
      data: {
        "it_id": obj.it_id
      },
      dataType: "json",
      async: false,
      cache: false,
      success: function(data, textStatus) {
        if(data["is_buy"] == 1){
			alert("이미 구매한 이벤트 상품으로 주문이 제한되었습니다.");	
			$('#ipt_so_sch').val("");
			return false;
		}else if(data["soldout_ck"] == 1){
			alert("품절 상품으로 주문이 제한되었습니다.");	
			$('#ipt_so_sch').val("");
			return false;
		}else{
			select_item(obj);
		}
      }
    });	
  });




  // 상품수량변경
  $(document).on('click', '.it_qty_wr button', function() {
    var mode = $(this).text();

    var val = parseInt($(this).val()),
      this_qty = 0,
      min_qty = parseInt( $(this).closest('li').find('input[name^=it_buy_min_qty]').val() ),
      max_qty = parseInt( $(this).closest('li').find('input[name^=it_buy_max_qty]').val() ),
      buy_inc_qty = parseInt( $(this).closest('li').find('input[name^=it_buy_inc_qty]').val() ),
      stock = parseInt( $(this).closest("li").find("input.io_stock").val());

    var $el_qty = $(this).closest('.it_qty_wr').find('input[name^=ct_qty]');

    if(min_qty < 1) min_qty = 1;
    if(max_qty < 1) max_qty = 9999;
    if(buy_inc_qty > min_qty) min_qty = buy_inc_qty;

    switch(mode) {
        case "증가":
            this_qty = parseInt($el_qty.val().replace(/[^0-9]/, "")) + buy_inc_qty;

            if(this_qty > stock) {
                alert("재고수량 보다 많은 수량을 구매할 수 없습니다.");
                this_qty = stock;
            }

            if( (max_qty) && (this_qty > max_qty) ) {
                alert("최대 구매수량은 "+number_format(String(max_qty))+" 입니다.");
                this_qty = max_qty;
            }

            $el_qty.val(this_qty);
            calculate_order_price();
            break;

        case "감소":
            this_qty = parseInt($el_qty.val().replace(/[^0-9]/, "")) - buy_inc_qty;

            if(this_qty < min_qty) {
                alert("최소 구매수량은 "+number_format(String(min_qty))+" 입니다.");
                this_qty = min_qty;
            }

            $el_qty.val(this_qty);
            calculate_order_price();
            break;
    }

    calculate_order_price();
  });

  // 수량직접입력
  $(document).on("blur", "input[name^=ct_qty]", function() {

    var val = parseInt($(this).val()),
      min_qty = parseInt( $(this).closest('li').find('input[name^=it_buy_min_qty]').val() ),
      max_qty = parseInt( $(this).closest('li').find('input[name^=it_buy_max_qty]').val() ),
      buy_inc_qty = parseInt( $(this).closest('li').find('input[name^=it_buy_inc_qty]').val() ),
      stock = parseInt( $(this).closest('li').find('input.io_stock').val() );

    if(min_qty < 1) min_qty = 1;
    if(max_qty < 1) max_qty = 9999;
    if(buy_inc_qty > min_qty) min_qty = buy_inc_qty;

    if( isNaN(val) == false ) {

        if( val < min_qty ) {
            alert('최소 구매수량은 ' + number_format(min_qty) + ' 입니다.');
            $(this).val( min_qty );
        }
        else if( (max_qty) && (val > max_qty) ) {
            alert('최대 구매수량은 ' + number_format(max_qty) + ' 입니다.');
            $(this).val( max_qty );
        }
        else if((val < min_qty) || (val > max_qty) ) {
            alert('수량은 ' + number_format(min_qty) + '에서 ' + number_format(max_qty) + ' 사이의 값으로 입력해 주십시오.');
            $(this).val( buy_inc_qty );
        }
        else if ( val > stock ) {
            alert('재고수량 보다 많은 수량을 구매할 수 없습니다.');
            $(this).val(stock);
        }
        else if( !!(val % buy_inc_qty) ) {
            alert('수량은 ' + number_format(buy_inc_qty) + '개 단위로 구매 가능 합니다.');
            $(this).val( min_qty );
        }

    } else {

        if ( $(this).val().replace(/[0-9]/g, '').length > 0 ) {
            alert('수량은 숫자만 입력해 주십시오.');
            $(this).val( min_qty );
        }
        else {
            alert('수량이 입력되지 않았습니다.');
            $(this).val( min_qty );
        }

    }

    calculate_order_price();

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
    if($(this).attr("id") == "order_address" || $(this).attr("id") == "pen_address") {
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

  // 수급자 배송지목록
  $("#pen_address").on("click", function() {
    var url = "<?php echo G5_SHOP_URL;?>/pop.recipient_address.php";
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

  <?php
  foreach($carts as $ct) {
      $it = $ct['it'];

      $obj = json_encode($it);

      echo "select_item($obj, '{$ct['io_id']}', {$ct['ct_qty']}, '{$ct['ct_id']}', '{$ct['io_type']}')".PHP_EOL;
  }
  ?>

  // 페이지 종료시 주문수정 중 취소
  var _was_page_cleaned_up = false;
  $(window).on('unload beforeunload', function() {
    if(_was_page_cleaned_up) return;
    _was_page_cleaned_up = true;

    if(navigator && navigator.sendBeacon) {
      navigator.sendBeacon('ajax.od_is_editing.update.php');
    } else {
      $.ajax({
        type: 'GET',
        async: false,
        url: 'ajax.od_is_editing.update.php'
      });
    }
  });
});
</script>

<?php include_once("./_tail.php"); ?>
