<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '주문신청';
include_once("./_head.php");

set_cart_id(1);
set_session("ss_direct", 1);
$tmp_cart_id = get_session('ss_cart_direct');

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
  <div class="sub_section_tit">주문신청</div>
  <div class="inner">
    <form id="simple_order" name="forderform" class="form-horizontal" action="orderformupdate.php" method="post">
      <input type="hidden" name="org_od_price" value="10000">
      <input type="hidden" name="od_price" value="10000">
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
                <input type="checkbox" id="chk_point_all">
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
        <div class="so_sch_wr flex space-between">
          <div class="so_sch_hd">품목 목록</div>
          <div class="so_sch_ipt">
            <input type="text" class="ipt_so_sch">
          </div>
          <button type="button" class="btn_so_sch">품목찾기</button>
        </div>

        <ul id="so_item_list" class="so_item_list">
          <?php for($i = 0; $i < 2; $i++) { ?>
          <li class="flex">
            <div class="it_info_wr">
              <img class="it_img" src="/img/no_img.png" onerror="this.src='/img/no_img.png';">
              <div class="it_info">
                <p class="it_name">ASH-120 (설치) (판매)</p>
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
                <input type="text" name="ct_qty[PRO2021022500562][]" value="1" id="ct_qty_0" class="form-control input-sm" size="5">
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
          <?php } ?>
        </ul>

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

      <div class="order-info" style="margin-top: 20px;">
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
var zipcode = '';

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

// 주문금액계산
function calculate_order_price() {

}

// 배송비계산 (더미코드)
function calculate_sendcost() {
  // do nothing;
}

$(function() {
  //쿠폰
  $("#od_coupon_btn").click(function() {
		$('#couponModal').modal('show');
    var $this = $(this);
    var price = parseInt($("input[name=org_od_price]").val());
    if(price <= 0) {
        alert('금액이 0원이므로 쿠폰을 사용할 수 없습니다.');
        return false;
    }
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

    $("input[name=od_price]").val(od_price - price);
    $("input[name=od_cp_id]").val(cp_id);
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
    $("input[name=od_price]").val(org_price);
    $("input[name=od_cp_id]").val('');
    $("#od_cp_price").text(0);
    calculate_order_price();
    $("#od_coupon_btn").text("쿠폰").focus();
    $(this).remove();
  });

  //배송지선택
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
});
</script>

<?php include_once("./_tail.php"); ?>
