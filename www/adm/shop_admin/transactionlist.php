<?php
$sub_menu = '400470';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '거래명세서';
include_once (G5_ADMIN_PATH.'/admin.head.php');

add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);
?>

<style>
#loading_excel {
  display: none;
  width: 100%;
  height: 100%;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 9999;
  background: rgba(0, 0, 0, 0.3);
}
#loading_excel .loading_modal {
  position: absolute;
  width: 400px;
  padding: 30px 20px;
  background: #fff;
  text-align: center;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
#loading_excel .loading_modal p {
  padding: 0;
  font-size: 16px;
}
#loading_excel .loading_modal img {
  display: block;
  margin: 20px auto;
}
#loading_excel .loading_modal button {
  padding: 10px 30px;
  font-size: 16px;
  border: 1px solid #ddd;
  border-radius: 5px;
}
#upload_wrap { display: none; }
.popModal #upload_wrap { display: block; }
.popModal .popModal_content { margin: 0 !important; }
.popModal .form-group { margin-bottom: 15px; }
.popModal label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: 700; }
.popModal input[type=file] { display: block; }
.popModal .help-block { padding: 0; display: block; margin-top: 5px; margin-bottom: 10px; color: #737373; }

.ajax-loader {
  visibility: hidden;
  background-color: rgba(255,255,255,0.7);
  position: absolute;
  z-index: +100 !important;
  width: 100%;
  height:100%;
}

.ajax-loader img {
  position: relative;
  top:50%;
  left:50%;
  transform: translate(-50%, -50%);
}
</style>

<script src="<?php echo G5_ADMIN_URL; ?>/shop_admin/js/orderlist.js?ver=<?php echo time(); ?>"></script>

<div id="loading_excel">
  <div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
    <button onclick="cancelExcelDownload();" class="btn_cancel_excel">취소</button>
  </div>
</div>

<div class="ajax-loader">
    <img src="img/ajax-loading.gif" class="img-responsive" />
</div>

<form name="frmsamhwaorderlist" id="frmsamhwaorderlist">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
      <tr>
        <th>날짜</th>
        <td class="date">
          <!-- <select name="sel_date_field" id="sel_field">
            <option value="od_time" <?php echo get_selected($sel_date_field, 'od_time'); ?>>주문일</option>
            <option value="od_receipt_time" <?php echo get_selected($sel_date_field, 'od_receipt_time'); ?>>입금일</option>
          </select> -->
          <div class="sch_last" style="margin-left:0px;">
            <input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton" />
            <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton" />
            <input type="button" value="이번주" id="select_date_thisweek" name="select_date" class="select_date newbutton" />
            <input type="button" value="지난주" id="select_date_lastweek" name="select_date" class="select_date newbutton" />
            <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton" />
            <input type="button" value="전체" id="select_date_all" name="select_date" class="select_date newbutton" />
            <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
            <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
          </div>
        </td>
      </tr>
      <tr>
        <th>기타설정</th>
        <td>
          <div class="select">
            <span>영업담당자</span>
            <div class="selectbox_multi">
              <div class="cont multiselect">
                <!--<h2><input type="checkbox" name="allmseq" class="allSelectDrop" id="allSelectDrop" br="y" value="y" checked=""> <label for="allSelectDrop"><span class="allmseq">모든 매니져</span></label></h2>-->
                <h2>영업담당자 선택</h2>
                <div class="list">
                  <ul>
                    <?php
                    $sql = "SELECT * FROM g5_auth WHERE au_menu = '400400' AND au_auth LIKE '%w%'";
                    $auth_result = sql_query($sql);
                    while($a_row = sql_fetch_array($auth_result)) {
                      $a_mb = get_member($a_row['mb_id']);
                    ?>
                    <li><input type="checkbox" name="od_sales_manager[]" id="od_sales_manager_<?php echo $a_mb['mb_id']; ?>" value="<?php echo $a_mb['mb_id']; ?>" title="<?php echo $a_mb['mb_id']; ?>" placeholder="<?php echo $a_mb['mb_id']; ?>" <?php echo option_array_checked($a_mb['mb_id'], $od_sales_manager); ?>><label for="od_sales_manager_<?php echo $a_mb['mb_id']; ?>"><?php echo $a_mb['mb_name']; ?></label></li>
                    <?php } ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <!--<div class="select">
            <span>출고담당자</span>
            <div class="selectbox_multi">
              <div class="cont multiselect">
                <h2>출고담당자 선택</h2>
                <div class="list">
                  <ul>
                    <li><input type="checkbox" name="od_release_manager[]" id="no_release" value="no_release" title="no_release" <?php echo option_array_checked('no_release', $od_release_manager); ?>><label for="no_release">출고아님</label></li>
                    <li><input type="checkbox" name="od_release_manager[]" id="out_release" value="-" title="out_release" <?php echo option_array_checked('-', $od_release_manager); ?>><label for="out_release">외부출고</label></li>
                    <?php
                    $sql = "SELECT * FROM g5_auth WHERE au_menu = '400001' AND au_auth LIKE '%w%'";
                    $auth_result = sql_query($sql);
                    while($a_row = sql_fetch_array($auth_result)) {
                      $a_mb = get_member($a_row['mb_id']);
                    ?>
                    <li><input type="checkbox" name="od_release_manager[]" id="od_release_manager_<?php echo $a_mb['mb_id']; ?>" value="<?php echo $a_mb['mb_id']; ?>" title="<?php echo $a_mb['mb_id']; ?>" placeholder="<?php echo $a_mb['mb_id']; ?>" <?php echo option_array_checked($a_mb['mb_id'], $od_release_manager); ?>><label for="od_release_manager_<?php echo $a_mb['mb_id']; ?>"><?php echo $a_mb['mb_name']; ?></label></li>
                    <?php } ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <!--<div class="linear">
            <span class="linear_span">계산서발행</span>
            <input type="radio" id="od_important_all" name="od_important" value="" <?php echo option_array_checked('', $od_important); ?>><label for="od_important_all"> 전체</label>
            <input type="radio" id="od_important_0" name="od_important" value="0" <?php echo option_array_checked('0', $od_important); ?>><label for="od_important_0"> 미발행</label>
            <input type="radio" id="od_important_1" name="od_important" value="1" <?php echo option_array_checked('1', $od_important); ?>><label for="od_important_1"> 발행</label>
          </div>
          <div class="linear">
            <span class="linear_span">위탁</span>
            <input type="radio" id="ct_is_direct_delivery_all" name="ct_is_direct_delivery" value="" <?php echo option_array_checked('', $ct_is_direct_delivery); ?>><label for="ct_is_direct_delivery_all"> 전체</label>
            <input type="radio" id="ct_is_direct_delivery_0" name="ct_is_direct_delivery" value="0" <?php echo option_array_checked('0', $ct_is_direct_delivery); ?>><label for="ct_is_direct_delivery_0"> 위탁아님</label>
            <input type="radio" id="ct_is_direct_delivery_1" name="ct_is_direct_delivery" value="1" <?php echo option_array_checked('1', $ct_is_direct_delivery); ?>><label for="ct_is_direct_delivery_1"> 배송</label>
            <input type="radio" id="ct_is_direct_delivery_2" name="ct_is_direct_delivery" value="2" <?php echo option_array_checked('2', $ct_is_direct_delivery); ?>><label for="ct_is_direct_delivery_2"> 설치</label>
            <select name="ct_direct_delivery_partner" id="ct_direct_delivery_partner">
              <option value="">전체</option>
              <?php
              $partner_result = sql_query(" SELECT * FROM g5_member WHERE mb_type = 'partner' ");
              while($partner = sql_fetch_array($partner_result)) {
                echo '<option value="'.$partner['mb_id'].'" '.get_selected($ct_direct_delivery_partner, $partner['mb_id']).'>'.$partner['mb_name'].'</option>';
              }
              ?>
            </select>
          </div>
          <div class="linear">
            <span class="linear_span">출고</span>
            <input type="radio" id="od_release_all" name="od_release" value="" <?php echo option_array_checked('', $od_release); ?>><label for="od_release_all"> 전체</label>
            <input type="radio" id="od_release_0" name="od_release" value="0" <?php echo option_array_checked('0', $od_release); ?>><label for="od_release_0"> 일반출고</label>
            <input type="radio" id="od_release_1" name="od_release" value="1" <?php echo option_array_checked('1', $od_release); ?>><label for="od_release_1"> 외부출고</label>
            <input type="radio" id="od_release_2" name="od_release" value="2" <?php echo option_array_checked('2', $od_release); ?>><label for="od_release_2"> 출고아님</label>
          </div>-->
        </td>
      </tr>
      <tr>
        <th>작업상태</th>
        <td>
          <div class="list">
            <input type="checkbox" id="complete1" name="complete1" value="1" <?php echo option_array_checked('1', $complete1); ?>><label for="complete1"> 미전송 내역만 보기</label>
            <!-- <input type="checkbox" id="complete2" name="complete2" value="1" <?php echo option_array_checked('1', $complete2); ?>><label for="complete2"> 배송정보 미입력 내역만 보기</label>
            <input type="checkbox" id="not_complete1" name="not_complete1" value="1" <?php echo option_array_checked('1', $not_complete1); ?>><label for="not_complete1"> 바코드 완료 내역만 보기</label>
            <input type="checkbox" id="not_complete2" name="not_complete2" value="1" <?php echo option_array_checked('1', $not_complete2); ?>><label for="not_complete2"> 배송정보 입력완료 내역만 보기</label> -->
          </div>
        </td>
      </tr>

      <tr>
        <th>검색어</th>
        <td>
          <select name="sel_field" id="sel_field">
            <option value="od_all" <?php echo get_selected($sel_field, 'od_all'); ?>>전체</option>
            <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>사업소명</option>
            <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
            <!-- <option value="it_name" <?php echo get_selected($sel_field, 'it_name'); ?>>상품명</option>
            <option value="it_admin_memo" <?php echo get_selected($sel_field, 'it_admin_memo'); ?>>관리자메모</option>
            <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
            <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
            <option value="od_tel" <?php echo get_selected($sel_field, 'od_tel'); ?>>주문자전화</option>
            <option value="od_hp" <?php echo get_selected($sel_field, 'od_hp'); ?>>주문자핸드폰</option> -->
            <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>수령인</option>
            <option value="od_b_email" <?php echo get_selected($sel_field, 'od_b_email'); ?>>발송이메일</option>
            <option value="od_b_fax" <?php echo get_selected($sel_field, 'od_b_fax'); ?>>발송팩스</option>
            <!-- <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
            <option value="ct_delivery_num" <?php echo get_selected($sel_field, 'ct_delivery_num'); ?>>운송장번호</option>
            <option value="barcode" <?php echo get_selected($sel_field, 'barcode'); ?>>바코드</option> -->
          </select>
          <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
          <!-- , 추가 검색어 -->
          <!-- <select name="sel_field_add" id="sel_field_add">
            <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
            <option value="it_name" <?php echo get_selected($sel_field, 'it_name'); ?>>상품명</option>
            <option value="it_admin_memo" <?php echo get_selected($sel_field, 'it_admin_memo'); ?>>관리자메모</option>
            <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
            <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
            <option value="od_tel" <?php echo get_selected($sel_field, 'od_tel'); ?>>주문자전화</option>
            <option value="od_hp" <?php echo get_selected($sel_field, 'od_hp'); ?>>주문자핸드폰</option>
            <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>받는분</option>
            <option value="od_b_tel" <?php echo get_selected($sel_field, 'od_b_tel'); ?>>받는분전화</option>
            <option value="od_b_hp" <?php echo get_selected($sel_field, 'od_b_hp'); ?>>받는분핸드폰</option>
            <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
            <option value="ct_delivery_num" <?php echo get_selected($sel_field, 'ct_delivery_num'); ?>>운송장번호</option>
          </select>
          <input type="text" name="search_add" value="<?php echo $search_add; ?>" id="search_add" class="frm_input" autocomplete="off" style="width:200px;"> -->
        </td>
      </tr>

    </table>
    <div class="submit">
      <button type="submit"><span>검색</span></button>
      <div class="buttons" style="display:none;">
        <button type="button" id="set_default_setting_button" title="기본검색설정" class="ml25">기본검색설정</button>
        <button type="button" id="set_default_apply_button"title="기본검색적용">기본검색적용</button>
        <button type="button" id="search_reset_button" title="검색초기화">검색초기화</button>
      </div>
    </div>
  </div>
</form>
<div class="r_btn_area" style="margin: 0px 20px 20px 0px;">
    <a href="javascript::" onclick="send_selected()" style="padding: 8px 12px 8px 12px;">선택 전송</a>
    <a class="send_all_transaction" href="javascript::" onclick="send_all_at_once()" style="padding: 8px 12px 8px 12px; margin-left: 8px; background-color: #666; color: white;">미전송 내역 일괄전송</a>
</div>

<form name="forderlist" id="forderlist" method="post" autocomplete="off" style="margin-top: 70px;">
    <input type="hidden" name="search_od_status" value="주문">

    <div id="samhwa_order_list">
        <ul class="order_tab" style="display:none;">
            <?php
            foreach($order_steps as $order_step) { 
                if (!$order_step['deliverylist']) continue;
            ?>
            <li class="" data-step="<?php echo $order_step['step']; ?>" data-status="<?php echo $order_step['val']; ?>"id="<?php echo $order_step['val']; ?>">
                <a><?php echo $order_step['name']; ?>(<span>0</span>)</a>
            </li>
            <?php } ?>
            <li class="" data-step="" data-status="">
                <a>전체</a>
            </li>
        </ul>
    <div id="samhwa_order_ajax_list_table">
    </div>
  </div>

</form>

<div id="fdefaultsettingform">
  <div class="fixed-container">
    <h2 class="h2_frm">기본검색 설정</h2>
    <a class="exit" id="fdefaultsettingform-exit">
      <i class="fa fa-times-circle fa-lg"></i>
    </a>

    <form name="fdefaultsettingform_form" method="post" id="fdefaultsettingform_form" action="./point_update.php" autocomplete="off">
      <input type="hidden" name="menu_id" value="<?php echo $sub_menu; ?>">

      <div class="tbl_frm01 tbl_wrap">
        <table>
        <colgroup>
          <col class="grid_4">
          <col>
        </colgroup>
        <tbody>
        </tbody>
        </table>
      </div>

      <div class="btn_confirm01 btn_confirm">
        <input type="button" value="확인" class="btn_submit btn" id="fdefaultsettingform_submit">
      </div>

    </form>
  </div>
</div>
<div class="btn_fixed_top3">
  <input type="button" value="더보기" onclick="doSearch()" class="btn btn_02">
</div>
<script>

var od_status = '배송';
var od_step = 0;
var page = 1;
var loading = false;
var end = false;
var sub_menu = '<?php echo $sub_menu; ?>';
var last_step = '';

function doSearch() {
  // alert(od_status);
  if ( loading === true ) return;
  if ( end === true ) return;

  var formdata = $.extend({}, $('#frmsamhwaorderlist').serializeObject(), { 
    od_status: '배송', 
    od_step: od_step, 
    page: page, 
    sub_menu: sub_menu,
    last_step: last_step, 
  });

  loading = true;
  console.log(formdata);
  var ajax = $.ajax({
    method: "POST",
    url: "./ajax.transactionlist.php",
    data: formdata,
  })
  .done(function(html) {
    if ( page === 1 ) {
      $('#samhwa_order_ajax_list_table').html(html.main);
    }
    $('#samhwa_order_list_table>div.table tbody').append(html.data);
    $(".ct_ex_date").datepicker({
      changeMonth: true, 
      changeYear: true, 
      dateFormat: "yy-mm-dd", 
      showButtonPanel: true, 
      yearRange: "c-99:c+99", 
      maxDate: "+365d",
      onSelect: function(ct_ex_date, inst) {
        var ct_id = $(this).data('ct-id');
        console.log(ct_id);
        console.log(ct_ex_date);
        $.ajax({
          method: "POST",
          url: "./ajax.order.delivery.change_delivery_time.php",
          data: {
            ct_ex_date: ct_ex_date,
            ct_id: ct_id,
          },
        }).done(function(data) {
          if ( data.msg ) {
            alert(data.msg);
          }
        });
      }
    });

    if ( !html.data ) {
      end = true;
    }

    if (html.last_step) {
      last_step = html.last_step;
    }

    if (html.counts) {
      $('#samhwa_order_list .order_tab li').each(function(index, item) {
        var status = $(item).data('status');
        var count = html.counts[status] || 0;

        $(item).find('span').html(count);
      });
    }
    page++;
  })
  .fail(function(xhr, textStatus, errorThrown) {
    console.log("ajax error");
    console.log(xhr.responseText);
  })
  .always(function() {
    loading = false;
  });
}

function complete() {
  page = 1;
  end = false;
  last_step = '';
  doSearch();
}

$("#complete1").click(function() {
  if ( loading === true ) return false;
  if($(this).prop('checked')) {
    $('#not_complete1').prop('checked', false);
  }
  complete();
});
$('#not_complete1').click(function() {
  if ( loading === true ) return false;
  if($(this).prop('checked')) {
    $('#complete1').prop('checked', false);
  }
  complete();
});
$("#complete2").click(function() {
  if ( loading === true ) return false;
  if($(this).prop('checked')) {
    $('#not_complete2').prop('checked', false);
  }
  complete();
});
$('#not_complete2').click(function() {
  if ( loading === true ) return false;
  if($(this).prop('checked')) {
    $('#complete2').prop('checked', false);
  }
  complete();
});

function cancelExcelDownload() {
  if(excel_downloader) {
    excel_downloader.abort();
  }
  $('#loading_excel').hide();
}

$( document ).ready(function() {
  
  $(document).on("click", ".prodBarNumCntBtn", function(e){
    e.preventDefault();
    var popupWidth = 700;
    var popupHeight = 700;
    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY= (window.screen.height / 2) - (popupHeight / 2);
    // var id = $(this).attr("data-id");
    // window.open("./popup.prodBarNum.form.php?od_id=" + id, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
    var od = $(this).attr("data-od");
    var it = $(this).attr("data-it");
    var stock = $(this).attr("data-stock");
    var option = encodeURIComponent($(this).attr("data-option"));
    //popup.prodBarNum.form_3.php 으로하면 cart 기준으로 바뀜 (상품하나씩)

    window.open("./popup.prodBarNum.form.php?no_refresh=1&orderlist=1&prodId=" + it + "&od_id=" + od + "&stock_insert=" + stock + "&option=" + option, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
  });
  
  $(document).on("click", ".deliveryCntBtn", function(e){
    e.preventDefault();
    var id = $(this).attr("data-id");
    var ct_id = $(this).attr("data-ct");
    
    var popupWidth = 1200;
    var popupHeight = 700;

    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY= (window.screen.height / 2) - (popupHeight / 2);
    
    //아래로하면 cart기준으로 바꿈(상품하나씩)
    window.open("./popup.prodDeliveryInfo.form.php?od_id=" + id +"&ct_id="+ct_id, "배송정보", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
  });


  var submitAction = function(e) {
    e.preventDefault();
    e.stopPropagation();
    /* do something with Error */
    page = 1;
    end = false;
    last_step = '';
    //doSearch();
    $('#samhwa_order_list .order_tab li:eq(0)').click();
  };
  $('#frmsamhwaorderlist').bind('submit', submitAction);

  $('#forderlist').bind('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
  });

  $("#search_reset_button").click(function(){
    clear_form("#search_detail_table");
  });

  $('#samhwa_order_list .order_tab li').click(function() {
    $('#samhwa_order_list .order_tab li').removeClass('on');
    $(this).addClass('on');

    od_status = $(this).data('status');
    od_step =  $(this).data('step');
    page = 1;
    end = false;
    last_step = '';
    doSearch();
  });

  // 출고리스트 접속시 기본검색 적용 자동으로 눌러주기
  $('#set_default_apply_button').click();

  setTimeout(function() {
    $('#samhwa_order_list .order_tab li:eq(1)').click();
    $.ajax({
      method: "GET",
      url: "./ajax.get.transaction.send.count.php",
    })
    .done(function(result) {
        console.log(result);
      if ( result.data ) {
        $('.send_all_transaction').text("미전송 내역 일괄전송("+result.data+")");
      }
    })
  }, 700);

  $(window).scroll(function() {
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
      doSearch();
    }
  });

  $('.od_delivery_type_all').click(function() {
    if($(this).is(":checked") == true) {
      $('.od_delivery_type').prop("checked", true);
    } else {
      $('.od_delivery_type').prop("checked", false);
    }
  });

    
  // 송장 리턴
  $( document ).on( "click", '.delivery_edi_return', function() {
    var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    od_id = od_id['od_id[]'];
    
    $.ajax({
      method: "POST",
      url: "./ajax.order.delivery.edi.return.php",
      data: { 
        od_id: od_id
      },
    })
    .done(function(data) {
      if ( data.msg ) {
        alert(data.msg);
      }
      if ( data.result === 'success' ) {
        location.reload();
      }
    })
  });

  // 택배정보 일괄 업로드
  $('#delivery_excel_upload').click(function() {
    $(this).popModal({
      html: $('#form_delivery_excel_upload'),
      placement: 'bottomRight',
      showCloseBut: false
    });
  });
  $('#form_delivery_excel_upload').submit(function(e) {
    e.preventDefault();

    var fd = new FormData(document.getElementById("form_delivery_excel_upload"));
    $.ajax({
        url: 'ajax.delivery.excel.upload.php',
        type: 'POST',
        data: fd,
        cache: false,
        processData: false,
        contentType: false,
        dataType: 'json'
      })
      .done(function() {
        alert('업로드가 완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
  });
  
  /* 거래명세서 단건 다운로드 */
  $( document ).on( "click", '.downloadBtn', function() {
      var od_id = $(this).attr("data-id");
      var ct_id = $(this).attr("data-ct");
    //   console.log(od_id + " " + ct_id);
      window.open("./transaction_download.php?od_id="+od_id+"&ct_id="+ct_id);
  });
});

//출고담당자
$(document).on("change", ".ct_manager", function(e) {
  if(confirm('출고담당자를 변경하시겠습니까?')) {

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
      if(data.result=="success") {
        alert('출고 담당자가 지정되었습니다.');
        // window.location.reload(); 
      } else {
        alert('실패하였습니다.');
      }
    });
  } else {
      // window.location.reload(); 
  }
});

function send_selected() {
    var arr = new Array();
    $('.send_chk').each(function() {
        if ($(this).is(":checked")) {
            var ct_id = $(this).val();
            var od_id = $(this).data('od-id');
            // console.log(od_id+"/"+ct_id);
            var dict = {};
            dict['od_id'] = od_id;
            dict['ct_id'] = ct_id;
            arr.push(dict);
        }
    });

    // window.open("./ajax.transactionlist.send.php");
    $.ajax({
        method: "POST",
        url: "./ajax.transactionlist.send.php",
        data: {
            send_data: arr
        },
        dataType: "json",
        beforeSend : function() {
            $('.ajax-loader').css("visibility", "visible");
        },
    }) 
    .done(function(data) {
        $('.ajax-loader').css("visibility", "hidden");
        alert(data.msg);
        location.reload();
    })
    .error(function (request, status, error) {
        console.log(request.responseText);
        // alert("요청 중 에러가 발생했습니다.");
    });
}

//미전송 내역 일괄전송
function send_all_at_once() {
    $.ajax({
        method: "POST",
        url: "./ajax.transactionlist.send.php",
        data: {
            send_data: "send_all_at_once"
        },
        dataType: "json",
        beforeSend : function() {
            $('.ajax-loader').css("visibility", "visible");
        },
    }) 
    .done(function(data) {
        $('.ajax-loader').css("visibility", "hidden");
        alert(data.msg);
        location.reload();
    })
    .error(function (request, status, error) {
        $('.ajax-loader').css("visibility", "hidden");
        console.log(request.responseText);
        // alert("요청 중 에러가 발생했습니다.");
    });
}

</script>
<style>
#samhwa_order_list_table>div.table thead tr.fixed {
  top: 102px !important;
}
</style>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
