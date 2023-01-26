<?php
$sub_menu = '400480';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '발주내역';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);

// 스마트 발주
$_SESSION['smart_purchase_data'] = json_decode(html_entity_decode(stripslashes($smart_purchase_data)));
?>
<style>
#text_size {
  display:none;
}
.page_title {
  display:none;
}
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
</style>
<script src="<?php echo G5_ADMIN_URL; ?>/shop_admin/js/orderlist.js?ver=<?php echo time(); ?>"></script>

<div class="local_ov01 local_ov fixed">
  <?php echo $listall; ?>
  <h1 style="border:0;padding:5px 0;margin:0;">발주내역</h1>
  <span class="btn_ov01" style="display: none"><span class="ov_txt">전체 발주내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
  <div class="right">
  </div>
</div>

<form name="frmsamhwaorderlist" id="frmsamhwaorderlist">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
      <tr>
        <th>날짜</th>
        <td class="date">
          <select name="sel_date_field" id="sel_date_field">
              <option value="od_time" <?php echo get_selected($sel_date_field, 'od_time'); ?>>발주일</option>
              <option value="ct_move_date" <?php echo get_selected($sel_date_field, 'ct_move_date'); ?>>변경일</option>
              <option value="_in_dt_confirm" <?php echo get_selected($sel_date_field, '_in_dt_confirm'); ?>>입고완료일</option>
          </select>
          <div class="sch_last">
            <input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton" />
            <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton" />
            <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton" />
            <input type="button" value="이번달" id="select_date_month" name="select_date" class="select_date newbutton" />
            <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton" />
            <input type="button" value="전체" id="select_date_all" name="select_date" class="select_date newbutton" />
            <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
            <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
          </div>
        </td>
      </tr>
      <tr>
        <th>발주상태</th>
        <td class="step_before">
          <input type="checkbox" name="od_status[]" value="all" id="step_all0" <?php echo option_array_checked('all', $od_status); ?>>
          <label for="step_all0">전체</label>

          <?php
          foreach($purchase_order_steps as $order_step) {
              if (!$order_step['orderlist']) continue;
          ?>
          <input type="checkbox" name="od_status[]" value="<?php echo $order_step['val']; ?>" id="step_<?php echo $order_step['val']; ?>" <?php echo option_array_checked($order_step['val'], $od_status); ?>>
          <label for="step_<?php echo $order_step['val']; ?>"><?php echo $order_step['name']; ?></label>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <th>기타설정</th>
        <td>
          발주서 발송상태 &nbsp;
          <input type="checkbox" name="od_send_n" value="1" id="od_send_n" <?php echo option_array_checked('1', $od_send_n); ?>>
          <label for="od_send_n">미발송</label>
          <input type="checkbox" name="od_send_y" value="1" id="od_send_y" <?php echo option_array_checked('1', $od_send_y); ?>>
          <label for="od_send_y">발송</label>
          &nbsp; | &nbsp; &nbsp;
          발주서 발송방식 &nbsp;
          <input type="checkbox" name="od_send_mail_yn" value="1" id="od_send_mail_yn" <?php echo option_array_checked('1', $od_send_mail_yn); ?>>
          <label for="od_send_mail_yn">이메일</label>
          <input type="checkbox" name="od_send_hp_yn" value="1" id="od_send_hp_yn" <?php echo option_array_checked('1', $od_send_hp_yn); ?>>
          <label for="od_send_hp_yn">문자</label>
          <input type="checkbox" name="od_send_fax_yn" value="1" id="od_send_fax_yn" <?php echo option_array_checked('1', $od_send_fax_yn); ?>>
          <label for="od_send_fax_yn">팩스</label>
          &nbsp; | &nbsp; &nbsp;
          <select name="od_writer">
            <option value="">발주담당자</option>
            <?php
            $sql = ("
                      SELECT 
                        mb.mb_name, mb.mb_id 
                      FROM 
                        g5_auth au, g5_member mb 
                      WHERE 
                        mb.mb_id = au.mb_id 
                        AND au_menu = '400480' 
                        AND au_auth 
                      LIKE '%w%' 
                      ORDER BY mb_name ASC
            ");

            $auth_result = sql_query($sql);
            while ($a_row = sql_fetch_array($auth_result)) {
            $a_mb = get_member($a_row['mb_id']);
            ?>
            <option value="<?php echo $a_mb['mb_id']; ?>" <?php echo $a_mb['mb_id'] == $od_writer ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?></option>
            <?php } ?>
          </select>
        </td>
      </tr>
      <tr>
        <th>검색어</th>
        <td>
          <select name="sel_field" id="sel_field">
            <option value="od_all" <?php echo $sel_field == 'od_all' ? 'selected="selected"' : ''; ?>>전체</option>
            <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
            <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>받는분</option>
            <option value="prodMemo" <?php echo get_selected($sel_field, 'prodMemo'); ?>>상품요청사항</option>
            <option value="od_memo" <?php echo get_selected($sel_field, 'od_memo'); ?>>배송요청사항</option>
            <option value="it_name" <?php echo $sel_field == 'it_name' ? 'selected="selected"' : ''; ?>>상품명</option>
            <option value="ct_option" <?php echo $sel_field == 'ct_option' ? 'selected="selected"' : ''; ?>>옵션</option>
            <option value="it_admin_memo" <?php echo $sel_field == 'it_admin_memo' ? 'selected="selected"' : ''; ?>>관리자메모</option>
            <option value="it_maker" <?php echo $sel_field == 'it_maker' ? 'selected="selected"' : ''; ?>>제조사</option>
            <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
            <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
            <option value="od_tel" <?php echo get_selected($sel_field, 'od_tel'); ?>>주문자전화</option>
            <option value="od_hp" <?php echo get_selected($sel_field, 'od_hp'); ?>>주문자핸드폰</option>
            <option value="od_b_tel" <?php echo get_selected($sel_field, 'od_b_tel'); ?>>받는분전화</option>
            <option value="od_b_hp" <?php echo get_selected($sel_field, 'od_b_hp'); ?>>받는분핸드폰</option>
            <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
            <option value="ct_delivery_num" <?php echo get_selected($sel_field, 'ct_delivery_num'); ?>>운송장번호</option>
            <option value="barcode" <?php echo get_selected($sel_field, 'barcode'); ?>>바코드</option>
          </select>
          <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
          	, 추가 검색어
          	<select name="sel_field_add" id="sel_field_add">
            <option value="od_all" <?php echo $sel_field_add == 'od_all' ? 'selected="selected"' : ''; ?>>전체</option>
            <option value="od_name" <?php echo get_selected($sel_field_add, 'od_name'); ?>>주문자</option>
            <option value="od_b_name" <?php echo get_selected($sel_field_add, 'od_b_name'); ?>>받는분</option>
            <option value="prodMemo" <?php echo get_selected($sel_field_add, 'prodMemo'); ?>>상품요청사항</option>
            <option value="od_memo" <?php echo get_selected($sel_field_add, 'od_memo'); ?>>배송요청사항</option>
            <option value="it_name" <?php echo $sel_field_add == 'it_name' ? 'selected="selected"' : ''; ?>>상품명</option>
            <option value="ct_option" <?php echo $sel_field_add == 'ct_option' ? 'selected="selected"' : ''; ?>>옵션</option>
            <option value="it_admin_memo" <?php echo $sel_field_add == 'it_admin_memo' ? 'selected="selected"' : ''; ?>>관리자메모</option>
            <option value="it_maker" <?php echo $sel_field_add == 'it_maker' ? 'selected="selected"' : ''; ?>>제조사</option>
            <option value="od_id" <?php echo get_selected($sel_field_add, 'od_id'); ?>>주문번호</option>
            <option value="mb_id" <?php echo get_selected($sel_field_add, 'mb_id'); ?>>회원 ID</option>
            <option value="od_tel" <?php echo get_selected($sel_field_add, 'od_tel'); ?>>주문자전화</option>
            <option value="od_hp" <?php echo get_selected($sel_field_add, 'od_hp'); ?>>주문자핸드폰</option>
            <option value="od_b_tel" <?php echo get_selected($sel_field_add, 'od_b_tel'); ?>>받는분전화</option>
            <option value="od_b_hp" <?php echo get_selected($sel_field_add, 'od_b_hp'); ?>>받는분핸드폰</option>
            <option value="od_deposit_name" <?php echo get_selected($sel_field_add, 'od_deposit_name'); ?>>입금자</option>
            <option value="ct_delivery_num" <?php echo get_selected($sel_field_add, 'ct_delivery_num'); ?>>운송장번호</option>
            <option value="barcode" <?php echo get_selected($sel_field_add, 'barcode'); ?>>바코드</option>
          </select>
          <input type="text" name="search_add" value="<?php echo $search_add; ?>" id="search_add" class="frm_input" autocomplete="off" style="width:200px;">
          <?php
          /* 22.11.02 : 서원 - 구매발주기능 개선 ( 검색어 조건 항목 삭제 )
            , 추가 검색어
          	<select name="sel_field_add_add" id="sel_field_add_add">
            <option value="od_all" <?php echo $sel_field_add_add == 'od_all' ? 'selected="selected"' : ''; ?>>전체</option>
            <option value="od_name" <?php echo get_selected($sel_field_add_add, 'od_name'); ?>>주문자</option>
            <option value="od_b_name" <?php echo get_selected($sel_field_add_add, 'od_b_name'); ?>>받는분</option>
            <option value="prodMemo" <?php echo get_selected($sel_field_add_add, 'prodMemo'); ?>>상품요청사항</option>
            <option value="od_memo" <?php echo get_selected($sel_field_add_add, 'od_memo'); ?>>배송요청사항</option>
            <option value="it_name" <?php echo $sel_field_add_add == 'it_name' ? 'selected="selected"' : ''; ?>>상품명</option>
            <option value="ct_option" <?php echo $sel_field_add_add == 'ct_option' ? 'selected="selected"' : ''; ?>>옵션</option>
            <option value="it_admin_memo" <?php echo $sel_field_add_add == 'it_admin_memo' ? 'selected="selected"' : ''; ?>>관리자메모</option>
            <option value="it_maker" <?php echo $sel_field_add_add == 'it_maker' ? 'selected="selected"' : ''; ?>>제조사</option>
            <option value="od_id" <?php echo get_selected($sel_field_add_add, 'od_id'); ?>>주문번호</option>
            <option value="mb_id" <?php echo get_selected($sel_field_add_add, 'mb_id'); ?>>회원 ID</option>
            <option value="od_tel" <?php echo get_selected($sel_field_add_add, 'od_tel'); ?>>주문자전화</option>
            <option value="od_hp" <?php echo get_selected($sel_field_add_add, 'od_hp'); ?>>주문자핸드폰</option>
            <option value="od_b_tel" <?php echo get_selected($sel_field_add_add, 'od_b_tel'); ?>>받는분전화</option>
            <option value="od_b_hp" <?php echo get_selected($sel_field_add_add, 'od_b_hp'); ?>>받는분핸드폰</option>
            <option value="od_deposit_name" <?php echo get_selected($sel_field_add_add, 'od_deposit_name'); ?>>입금자</option>
            <option value="ct_delivery_num" <?php echo get_selected($sel_field_add_add, 'ct_delivery_num'); ?>>운송장번호</option>
            <option value="barcode" <?php echo get_selected($sel_field_add_add, 'barcode'); ?>>바코드</option>
          </select>
          <input type="text" name="search_add_add" value="<?php echo $search_add_add; ?>" id="search_add_add" class="frm_input" autocomplete="off" style="width:200px;">
          */
          ?>
        </td>
      </tr>

    </table>
    <div class="submit">
      <button type="submit" id="search-btn"><span>검색</span></button>
      <div class="buttons">
        <button type="button" id="set_default_setting_button" title="기본검색설정" class="ml25">기본검색설정</button>
        <button type="button" id="set_default_apply_button" title="기본검색적용">기본검색적용</button>
        <button type="button" id="search_reset_button" title="검색초기화">검색초기화</button>
      </div>
    </div>
  </div>
  <input type="hidden" name="mb_info" value="<?php echo $mb_info ?>"/>
</form>
<form name="forderlist" id="forderlist" method="post" autocomplete="off">
  <input type="hidden" name="search_od_status" value="<?php echo $od_status; ?>">

  <?php
  if ($sel_field == 'mb_id' && $search && $mb_info) {

  $sql =  "select *
          from g5_member
          where mb_id = '{$search}'";
  $mb_row = sql_fetch($sql);

  $mb_type_arr = Array();

  if ($mb_row['mb_giup_type'] > 0) { // 기업
    array_push($mb_type_arr, "기업");
  } else {
    array_push($mb_type_arr, "일반");
  }

  if ($mb_row['mb_type'] == 'partner') { // 파트너
    array_push($mb_type_arr, "파트너");
  }
  if ($mb_row['mb_level'] == 3) { // 딜러
    array_push($mb_type_arr,  "사업소");
  }
  if ($mb_row['mb_level'] == 4) { // 우수딜러
    array_push($mb_type_arr,  "우수사업소");
  }

  $mb_type = '유형 : ' . (implode(', ', $mb_type_arr));

  if ($mb_row['mb_giup_type'] > 0) {
    $mb_giup_bnum = ' | 사업자번호 : ' . $mb_row["mb_giup_bnum"];
  }

  $mb_phone = ' | 연락처 : ' . $mb_row['mb_hp'];

  $sql = "select sum(od_cart_price) as od_cart_price, sum(od_send_cost) as od_send_cost, sum(od_send_cost2) as od_send_cost2, sum(od_cart_discount) as od_cart_discount
          from g5_shop_order
          where mb_id = '{$search}'";
  // $where_pay_state_0 = "and od_pay_state = '0'";
  // $total_result = sql_fetch($sql.$where_pay_state_0);
  // $outstanding_balance = number_format($total_result['od_cart_price'] + $total_result['od_send_cost'] + $total_result['od_send_cost2'] - $total_result['od_cart_discount']  - $total_result['od_cart_discount2']);

  $outstanding_balance = samhwa_get_misu($search);
  $outstanding_balance = number_format($outstanding_balance['misu']);

  $total_result2 = sql_fetch($sql);
  $total_balance = number_format($total_result2['od_cart_price'] + $total_result2['od_send_cost'] + $total_result2['od_send_cost2'] - $total_result2['od_cart_discount'] - $total_result2['od_cart_discount2'] - $total_result2['od_sales_discount']);
  ?>
  <div id="mb_info">
    <div>
      <span class="mb_name"><?php echo $mb_row['mb_name'] ?></span> <a class="btn1" href="/adm/member_form.php?w=u&mb_id=<?php echo $search ?>"> 회원정보보기</a>
    </div>
    <div>
      <span class="mb_detail"><?php echo $mb_type.$mb_giup_bnum.$mb_phone?> | 미수금 : <?php echo $outstanding_balance?>원</span> <button class="btn2" type="button" onclick="setPayState('paid')">선택 결제처리</button> <button class="btn2" type="button" onclick="setPayState('notPaid')">선택 미결제처리</button> <button class="btn1" type="button" onclick="calculate_balance()">선택계산</button> 검색주문건 합계금액 : <span id="total-search-price"><?php echo $total_balance ?></span>원
    </div>
  </div>
  <?php
  }
  ?>

  <div id="samhwa_order_list">
    <ul class="order_tab">
      <li class="purchase_order_steps" data-step="" data-status="">
        <a>전체</a>
      </li>
      <?php
      foreach($purchase_order_steps as $order_step) {
        if (!$order_step['orderlist']) continue;
      ?>
      <li class="purchase_order_steps" data-step="<?php echo $order_step['step']; ?>" data-status="<?php echo $order_step['val']; ?>" id="<?php echo $order_step['val']; ?>" >
        <a>
          <?php echo $order_step['name']; ?><br />(<span>0</span>)
        </a>
      </li>
      <?php } ?>
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

<style>
#popup_order_add {
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index: 999;
  background-color: rgba(0, 0, 0, 0.6);
  display:none;
}
#popup_order_add > div {
  width: 1000px;
  max-width: 80%;
  height: 80%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}
#popup_order_add > div iframe {
  width:100%;
  height:100%;
  border: 0;
  background-color: #FFF;
}

</style>
<div id="popup_order_add">
  <div>dd</div>
</div>

<script>
$(function() {
  <?php if (isset($_SESSION['smart_purchase_data'])) { ?>
    $('#order_add').click();
  <?php } ?>
});

var od_status = '';
var od_step = 0;
var page = 1;
var loading = false;
var end = false;
var sub_menu = '<?php echo $sub_menu; ?>';
var last_step = '';

function doSearch() {
  if ( loading === true ) return;
  if ( end === true ) return;

  var formdata = $.extend({}, {
    click_status: od_status,
    od_step: od_step,
    page: page,
    sub_menu: sub_menu,
    last_step: last_step,
  },$('#frmsamhwaorderlist').serializeObject());
  loading = true;

  // form object rename
  formdata['od_settle_case'] = formdata['od_settle_case[]']; // Assign new key
  delete formdata['od_settle_case[]']; // Delete old key

  if (formdata['od_status[]'] != undefined) {
    formdata['od_status'] = formdata['od_status[]']; // Assign new key
    delete formdata['od_status[]']; // Delete old key
  }

  formdata['od_openmarket'] = formdata['od_openmarket[]']; // Assign new key
  delete formdata['od_openmarket[]']; // Delete old key

  formdata['add_admin'] = formdata['add_admin']; // Assign new key
  // delete formdata['add_admin[]']; // Delete old key

  formdata['od_important'] = formdata['od_important']; // Assign new key
  // delete formdata['od_important[]']; // Delete old key

  formdata["od_recipient"] = "<?=$_GET["od_recipient"]?>";

  var ajax = $.ajax({
    method: "POST",
    url: "./ajax.purchaseorderlist.php",
    data: formdata,
  })
  .done(function(html) {
    if ( page === 1 ) {
      $('#samhwa_order_ajax_list_table').html(html.main);
    }
    $('#samhwa_order_list_table>div.table tbody').append(html.data);
    // $('#samhwa_order_list_table>div.table:first-child tbody').append(html.left);
    // $('#samhwa_order_list_table>div.table:last-child tbody').append(html.right);

    if ( !html.data ) {
      end = true;
    }
    if (html.counts) {
      $('#samhwa_order_list .order_tab li').each(function(index, item) {
        var status = $(item).data('status');
        var count = html.counts[status] || 0;

        $(item).find('span').html(count);
      });
    }
    if (html.last_step) {
      last_step = html.last_step;
    }
    page++;
  })
  .fail(function() {
      console.log("ajax error");
  })
  .always(function() {
      loading = false;
  });
}

function sanbang_order_send(){
  $('#process').attr('src', "/sabangnet/curl_xml_send.php?iframe=y");
}

$( document ).ready(function() {

  var last_click_index = 0;
  // 체크박스 범위선택 (shift + 클릭)
  $(document).on('click', 'input[name="od_id[]"]',  function(e) {
    var $tr = $('#samhwa_order_list_table table tr');
    var index = $tr.index($(this).closest('tr'));

    if((e.shiftKey) && last_click_index > 0) {
      var start_index, end_index;
      if(last_click_index < index) {
        start_index = last_click_index;
        end_index = index;
      } else {
        start_index = index;
        end_index = last_click_index;
      }
      for(var i = start_index; i <= end_index; i++) {
        $tr.eq(i).find('input[name="od_id[]"]').prop('checked', true);
      }
    } else {
      if($(this).prop('checked')) {
        last_click_index = index;
      } else {
        last_click_index = 0;
      }
    }
  });
  
  $(document).on("click", ".prodBarNumCntBtn", function(e) {
    e.preventDefault();
    var popupWidth = 800;
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

  var submitAction = function(e) {
    $("#frmsamhwaorderlist").attr("action", "purchase_orderlist.php");
    return true;
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

  // $('#samhwa_order_list .order_tab li:eq(0)').click();

  // $('.new_form .submit button[type="submit"]').click();

  $(window).scroll(function() {
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
      doSearch();
    }
  });
  /*
  if ( $('#samhwa_order_list') ) {
    if ( $('#samhwa_order_list').width() % 2 ) {
      $('#samhwa_order_list').width( $('#samhwa_order_list').width() - 1 + 'px');
    }
  }
  */

  // 경동엑셀
  $("#gdexcel").click(function() {

    $('.dynamic_od_id').remove();

    var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var ret_od_id;

    if ( od_id['od_id[]'] === undefined ) {
      ret_od_id = '';
    } else {
      if ( Array.isArray(od_id['od_id[]']) ) {
        ret_od_id = od_id['od_id[]'].join('|');
      }else{
        ret_od_id = od_id['od_id[]'];
      }
    }

    $("#frmsamhwaorderlist").append("<input type='hidden' value="+ ret_od_id +" name='ret_od_id' class='dynamic_od_id'>");

    $("#frmsamhwaorderlist").attr("action", "excel_gd.php");
    $("#frmsamhwaorderlist")[0].submit();
  });

  // 더존엑셀
  $("#dzexcel").click(function() {

    $('.dynamic_od_id').remove();

    var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var ret_od_id;

    if ( od_id['od_id[]'] === undefined ) {
      ret_od_id = '';
    } else {
      if ( Array.isArray(od_id['od_id[]']) ) {
        ret_od_id = od_id['od_id[]'].join('|');
      }else{
        ret_od_id = od_id['od_id[]'];
      }
    }

    $("#frmsamhwaorderlist").append("<input type='hidden' value="+ ret_od_id +" name='ret_od_id' class='dynamic_od_id'>");

    $("#frmsamhwaorderlist").attr("action", "excel_dz.php");
    $("#frmsamhwaorderlist")[0].submit();
  });

  $(".orderExcel").click(function() {
    alert();
    var type = $(this).data('type');

    $('.dynamic_od_id').remove();
    $('#od_type').remove();

    var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var ret_od_id;

    if ( od_id['od_id[]'] === undefined ) {
      ret_od_id = '';
    } else {
      if ( Array.isArray(od_id['od_id[]']) ) {
        ret_od_id = od_id['od_id[]'].join('|');
      }else{
        ret_od_id = od_id['od_id[]'];
      }
    }

    $("#frmsamhwaorderlist").append("<input type='hidden' value="+ ret_od_id +" name='ret_od_id' class='dynamic_od_id'>");
    $("#frmsamhwaorderlist").append("<input type='hidden' value="+ type +" name='type' id='od_type'>");

    $("#frmsamhwaorderlist").attr("action", "excel_order.php");
    $("#frmsamhwaorderlist")[0].submit();
  });

  $("#search-btn").submit(function () {
    return false;
  })

  $('#od_settle_case01').change(function () {
    if ($(this).is(":checked")) {
      $('input[name="od_settle_case[]"]').each(function (i, v) {
        $(v).prop("checked", true);
      });
    } else {
      $('input[name="od_settle_case[]"]').each(function (i, v) {
        $(v).prop("checked", false);
      });
    }
  });

  $('input[name="od_settle_case[]').change(function () {
    var all = $('input[name="od_settle_case[]"]').length - 1;
    var count = 0;

    $('input[name="od_settle_case[]"]').each(function (i, v) {
      if ($(v).attr('id') != 'od_settle_case01') {
        if ($(v).is(":checked")) {
          count++;
        }
      }
    });

    if (count == all) {
      $('#od_settle_case01').prop('checked', true);
    } else {
      $('#od_settle_case01').prop('checked', false);
    }
  });

  <?php if (!$od_settle_case) { ?>
  // $('#od_settle_case01').prop('checked', true);
  $('#od_settle_case01').trigger('change');
  <?php } ?>

  $('#od_openmarket_0').change(function () {
    if ($(this).is(":checked")) {
      $('input[name="od_openmarket[]"]').each(function (i, v) {
        $(v).prop("checked", true);
      })
    } else {
      $('input[name="od_openmarket[]"]').each(function (i, v) {
        $(v).prop("checked", false);
      })
    }
  });

  $('input[name="od_openmarket[]"]').change(function () {
    var all = $('input[name="od_openmarket[]"]').length - 1;
    var count = 0;

    $('input[name="od_openmarket[]"]').each(function (i, v) {
      if ($(v).attr('id') != 'od_openmarket_0') {
        if ($(v).is(":checked")) {
          count++;
        }
      }
    })

    if (count == all) {
      $('#od_openmarket_0').prop('checked', true);
    } else {
      $('#od_openmarket_0').prop('checked', false);
    }
  });

  <?php if (!$od_openmarket) { ?>
  // $('#od_openmarket_0').prop('checked', true);
  $('#od_openmarket_0').trigger('change');
  <?php } ?>

    
  $('#member_grade').change(function () {
    if ($(this).is(":checked")) {
      $('input.member_grade').each(function (i, v) {
        $(v).prop("checked", true);
      });
    } else {
      $('input.member_grade').each(function (i, v) {
        $(v).prop("checked", false);
      });
    }
  });

  $('input.member_grade').change(function () {
    var all = $('input.member_grade').length - 1;
    var count = 0;

    $('input.member_grade').each(function (i, v) {
      if ($(v).attr('id') != 'member_grade') {
        if ($(v).is(":checked")) {
          count++;
        }
      }
    });

    if (count == all) {
      $('#member_grade').prop('checked', true);
    } else {
      $('#member_grade').prop('checked', false);
    }
  });
  
  <?php if (!$member_type_s && !$member_level_s && !$is_member_s) { ?>
  // $('#member_grade').prop('checked', true);
  $('#member_grade').trigger('change');
  <?php } ?>
    

  $('#od_pay_state_all').change(function () {
    if ($(this).is(":checked")) {
      $('input.od_pay_state').each(function (i, v) {
        $(v).prop("checked", true);
      });
    } else {
      $('input.od_pay_state').each(function (i, v) {
        $(v).prop("checked", false);
      });
    }
  });

  $('input.od_pay_state').change(function () {
    var all = $('input.od_pay_state').length;
    var count = 0;

    $('input.od_pay_state').each(function (i, v) {
      if ($(v).is(":checked")) {
        count++;
      }
    });

    $('#od_pay_state_all').prop('checked', count === all);
  });
    
  <?php if (!$od_pay_state) { ?>
  // $('#od_pay_state_all').prop('checked', true);
  $('#od_pay_state').trigger('change');
  <?php } ?>
    
  // 검색시 탭 숨기기
  // toggle_order_tab();


  // 리스트 메모
  $( document ).on( "click", '.open_list_memo_layer_popup', function() {
      $('.list_memo_pop').toggle();

      var od_id = $(this).data('od-id');
      var text = $(this).text().trim();

      $('.list_memo_pop').find('input[name="od_id"]').val(od_id);
      $('.list_memo_pop').find('textarea[name="od_list_memo"]').val(text);
  });

  $('#list_memo_pop_exit').click(function() {
    $('.list_memo_pop').hide();
  });

  $('.list_memo_pop input[type="submit"]').click(function(e) {
    e.preventDefault();

    var od_id = $('.list_memo_pop').find('input[name="od_id"]').val();
    var od_list_memo = $('.list_memo_pop').find('textarea[name="od_list_memo"]').val();
    
    if (!od_id) {
      alert('오류발생');
    }

    $.ajax({
      method: "POST",
      url: "./ajax.order.list_memo.php",
      data: {
        od_id: od_id,
        od_list_memo: od_list_memo
      }
    })
    .done(function(data) {
      if ( data.msg ) {
        alert(data.msg);
      }
      if ( data.result === 'success' ) {
        $('.list_memo_pop').hide();
        $('.list_memo_' + od_id).text(data.data);
      }
    });
    //$('.list_memo_pop').hide();
  });


  // 주문상태 출고 전 전체 선택
  $('#step_all0').change(function () {
    if ($(this).is(":checked")) {
      $('.step_before input[name="od_status[]"]').each(function (i, v) {
        $(v).prop("checked", true);
      });
    } else {
      $('.step_before input[name="od_status[]"]').each(function (i, v) {
        $(v).prop("checked", false);
      });
    }
  });

  $('.step_before input[name="od_status[]"]').change(function () {
    var all = $('.step_before input[name="od_status[]"]').length - 1;
    var count = 0;

    $('.step_before input[name="od_status[]"]').each(function (i, v) {
      if ($(v).attr('id') != 'step_all0') {
        if ($(v).is(":checked")) {
          count++;
        }
      }
    })

    if (count == all) {
      $('#step_all0').prop('checked', true);
    } else {
      $('#step_all0').prop('checked', false);
    }
  });

  <?php if (!$od_status || in_array('all', $od_status)) { ?>
  // $('#step_all0').prop('checked', true);
  $('#step_all0').trigger('change');
  <?php } ?>

  // 주문상태 출고 후 전체 선택
  $('#step_all1').change(function () {
    if ($(this).is(":checked")) {
      $('.step_after input[name="od_status[]"]').each(function (i, v) {
        $(v).prop("checked", true);
      })
    } else {
      $('.step_after input[name="od_status[]"]').each(function (i, v) {
        $(v).prop("checked", false);
      })
    }
  });

  $('.step_after input[name="od_status[]"]').change(function () {
    var all = $('.step_after input[name="od_status[]"]').length - 1;
    var count = 0;

    $('.step_after input[name="od_status[]"]').each(function (i, v) {
      if ($(v).attr('id') != 'step_all1') {
        if ($(v).is(":checked")) {
          count++;
        }
      }
    })

    if (count == all) {
      $('#step_all1').prop('checked', true);
    } else {
      $('#step_all1').prop('checked', false);
    }
  });

  <?php if (!$od_status || in_array('all2', $od_status)) { ?>
  // $('#step_all1').prop('checked', true);
  $('#step_all1').trigger('change');
  <?php } ?>

  <?php if (!$_GET['token']) { ?>
  // 주문내역 접속시 기본검색 적용 자동으로 눌러주기
  <?php if (!$_GET['mb_info']) { ?>
  $('#set_default_apply_button').click();
  <?php } ?>

  setTimeout(function() {

    <?php if ($sel_field) { ?>
    $('#sel_field').val('<?php echo htmlspecialchars($sel_field); ?>').prop("selected", true);
    <?php } ?>
    <?php if ($search) { ?>
    $('#search').val('<?php echo htmlspecialchars($search); ?>');
    <?php } ?>

    doSearch();
  
    $('#ct_barcode_all').prop('checked', true);
    $('#ct_delivery_all').prop('checked', true);
  }, 700);
  <?php } else { ?>
  // doSearch();
  $('#samhwa_order_list .order_tab li:eq(0)').click();
  $('ul.order_tab').hide();
  <?php } ?>

  // 엔터
  $(document).keydown(function(key) {
    if (key.keyCode == 13) {
      $('#search-btn').click();
    }
  });

});

function toggle_order_tab() {
  var flag = false;

  $('input[name="od_status[]"]').each(function (i, v) {
    if ($(v).is(":checked")) {
      flag = true;
    }
  })

  if (flag) {
    $('ul.order_tab').hide();
  }
}

function calculate_balance() {
  var target = $('#samhwa_order_list_table > div.table td input[type=checkbox]:checked');

  if (target.length == 0) {
    alert("선택된 주문이 없습니다.");
    return;
  } else {
    var price = 0;
    target.each(function (i, v) {
      price += Number($(v).parent().parent().find('.od_price').text().replace(/[^0-9]/g,""));
    })

    $('#total-search-price').text(numberWithCommas(price));
  }
}

function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function setPayState(toState) {
  $('#dynamic_od_id').remove();
  $('#toState').remove();

  var od_id = $('#samhwa_order_list_table > div.table td input[type=checkbox]:checked').serializeObject();
  var ret_od_id;

  if ( od_id['od_id[]'] === undefined ) {
    alert("선택된 주문이 없습니다.");
    return;
  } else {
    if ( Array.isArray(od_id['od_id[]']) ) {
      ret_od_id = od_id['od_id[]'].join('|');
    }else{
      ret_od_id = od_id['od_id[]'];
    }
  }

  $("#frmsamhwaorderlist").append("<input type='hidden' value="+ ret_od_id +" name='ret_od_id' id='dynamic_od_id'>");
  $("#frmsamhwaorderlist").append("<input type='hidden' value="+ toState +" name='to_state' id='toState'>");

  $("#frmsamhwaorderlist").attr("action", "order_pay_state.php");
  $("#frmsamhwaorderlist")[0].submit();
}
</script>

<!-- wetoz : naverpayorder -->
<?php
if ($default['de_naverpayorder_AccessLicense'] && $default['de_naverpayorder_SecretKey']) {
  @include_once(G5_DATA_PATH.'/cache/naverpayorder-ordersync.php');
  ?>
  <div class="btn_confirm01 btn_confirm"><a href="#none" onclick="sync_naverapi();" id="btn-naverapi">네이버 주문정보 동기화 <?php if ($InqTimeFrom) echo '(최종 : '.str_replace('T', ' ', $InqTimeFrom).')';?></a></div>
  <!--
  <script type="text/javascript">
  function sync_naverapi() {
      $.ajax({
          url: g5_url+'/plugin/wznaverpay/sync_rotation.php',
          dataType: 'html',
          type:'post',
          beforeSend : function() {
              $('#btn-naverapi').html('네이버 주문정보 동기화 처리중.. <img src="'+g5_url+'/plugin/wznaverpay/img/loading.gif" />');
          },
          success:function(req) {
              //if (req == 'RESULT=TRUE') {
                  location.reload();
              //}
              //else {
              //    alert('동기화에 실패하였습니다.');
              //    location.reload();
              //}
          }
      });
  }
  //-->
  </script>
<?php } ?>
<!-- wetoz : naverpayorder -->

<?php
if( function_exists('pg_setting_check') ){
  pg_setting_check(true);
}
?>

<!--<div class="btn_fixed_top">-->
<!--  <a href="./samhwa_order_new.php" id="order_add" class="btn btn_01">주문서 추가</a>-->
<!--  <input type="button" value="주문내역 엑셀다운로드" onclick="orderListExcelDownload('excel')" class="btn btn_02">-->
<!--  --><?php //if($member['mb_id'] == 'admin') { ?>
<!--  <input type="button" value="이카운트 엑셀다운로드" onclick="orderListExcelDownload('ecount')" class="btn" style="background: #6e9254; color: #fff;">-->
<!--  --><?php //} ?>
<!--  <input type="button" value="위탁 엑셀다운로드" onclick="orderListExcelDownload('partner')" class="btn btn_03">-->
<!--</div>-->

<div class="btn_fixed_top2" style="bottom: 0;">
  <a href="javascript:void(0);" id="order_add" onclick="popAddOrder()" class="btn btn_01">발주서 추가</a>
  <input type="button" value="더보기" onclick="doSearch()" class="btn btn_02">
  <input type="button" value="발주내역 엑셀다운로드" onclick="orderListExcelDownload('excel')" class="btn btn_04">
</div>

<iframe src="about:blank" name="process" id="process" width="0" height="0" style="display:none"></iframe>

<form>
  <div class="list_memo_pop">
    <div class="fixed-container">
      <h2 class="h2_frm">메모수정</h2>
      <a class="exit btn btn_02" id="list_memo_pop_exit" href="#">X</a>

      <br/>
      <input type="hidden" name="od_id" value="" />
      <textarea name="od_list_memo" class="frm_input"></textarea>
      <input type="submit" class="btn btn_03" value="수정" />
    </div>
  </div>
</form>

<script>
var excel_downloader = null;

function orderListExcelDownload(type) {
  // alert("테스트 버튼 확인");

    var od_id = [];
    var item = $("input[name='od_id[]']:checked");
    for(var i = 0; i < item.length; i++) {
        od_id.push($(item[i]).val());
    }

    console.log(formdata);

    if(!od_id.length) {
        if(type === 'partner') return alert('선택한 주문이 없습니다.');

        if(!confirm('선택한 주문이 없습니다.\n검색결과 내 모든 주문내역을 다운로드하시겠습니까?')) return false;
    }

    var formdata = $.extend({}, {
        click_status: od_status,
        od_step: od_step,
        page: page,
        sub_menu: sub_menu,
        last_step: last_step,
        od_id: od_id
    },$('#frmsamhwaorderlist').serializeObject());

    // form object rename
    formdata['od_settle_case'] = formdata['od_settle_case[]']; // Assign new key
    delete formdata['od_settle_case[]']; // Delete old key

    if (formdata['od_status[]'] != undefined) {
        formdata['od_status'] = formdata['od_status[]']; // Assign new key
        delete formdata['od_status[]']; // Delete old key
    }

    formdata['od_openmarket'] = formdata['od_openmarket[]']; // Assign new key
    delete formdata['od_openmarket[]']; // Delete old key

    formdata['add_admin'] = formdata['add_admin']; // Assign new key
    // delete formdata['add_admin[]']; // Delete old key

    formdata['od_important'] = formdata['od_important']; // Assign new key
    // delete formdata['od_important[]']; // Delete old key

    formdata["od_recipient"] = "<?=$_GET["od_recipient"]?>";

    var queryString = $.param(formdata);
    var href = "./purchase_orderlist.excel.list.php";

    $('#loading_excel').show();

    excel_downloader = $.fileDownload(href, {
        httpMethod: "POST",
        data: queryString
    })
    .always(function() {
        $('#loading_excel').hide();
    });

    return false;
}

//function orderListExcelDownload(type) {
//  var od_id = [];
//  var item = $("input[name='od_id[]']:checked");
//  for(var i = 0; i < item.length; i++) {
//    od_id.push($(item[i]).val());
//  }
//
//  if(!od_id.length) {
//    if(type === 'partner') return alert('선택한 주문이 없습니다.');
//
//    if(!confirm('선택한 주문이 없습니다.\n검색결과 내 모든 주문내역을 다운로드하시겠습니까?')) return false;
//  }
//
//  var formdata = $.extend({}, {
//    click_status: od_status,
//    od_step: od_step,
//    page: page,
//    sub_menu: sub_menu,
//    last_step: last_step,
//    od_id: od_id
//  },$('#frmsamhwaorderlist').serializeObject());
//
//  // form object rename
//  formdata['od_settle_case'] = formdata['od_settle_case[]']; // Assign new key
//  delete formdata['od_settle_case[]']; // Delete old key
//
//  if (formdata['od_status[]'] != undefined) {
//    formdata['od_status'] = formdata['od_status[]']; // Assign new key
//    delete formdata['od_status[]']; // Delete old key
//  }
//
//  formdata['od_openmarket'] = formdata['od_openmarket[]']; // Assign new key
//  delete formdata['od_openmarket[]']; // Delete old key
//
//  formdata['add_admin'] = formdata['add_admin']; // Assign new key
//  // delete formdata['add_admin[]']; // Delete old key
//
//  formdata['od_important'] = formdata['od_important']; // Assign new key
//  // delete formdata['od_important[]']; // Delete old key
//
//  formdata["od_recipient"] = "<?//=$_GET["od_recipient"]?>//";
//
//  var queryString = $.param(formdata);
//  var href = "./order.excel.list.php";
//  if (type === 'ecount') {
//    href = "./order.ecount.excel.list.php";
//    if(confirm('이미 다운로드한 상품은 제외하고 다운받으시겠습니까?'))
//      queryString += "&new_only=1";
//  }
//  else if (type === 'partner') {
//    href = './order.partner.excel.php';
//  }
//
//  $('#loading_excel').show();
//
//  if(type === 'partner') {
//    excel_downloader = $.fileDownload(href, {
//      httpMethod: "POST",
//      data: queryString
//    })
//    .always(function() {
//      $('#loading_excel').hide();
//      item.each(function() {
//        if($(this).closest('tr').find('td.od_direct_delivery span.excel_done').length === 0)
//          $(this).closest('tr').find('td.od_direct_delivery').append('<br><span class="excel_done" style="color: #FF6600">엑셀 다운로드 완료</span>');
//      });
//    });
//  } else if(type === 'ecount') {
//    excel_downloader = $.fileDownload(href, {
//      httpMethod: "POST",
//      data: queryString
//    })
//    .always(function() {
//      $('#loading_excel').hide();
//      if(!od_id.length) {
//        window.location.reload();
//      } else {
//        item.each(function() {
//          if($(this).closest('tr').find('td.od_step span.excel_done').length === 0)
//            $(this).closest('tr').find('td.od_step').append('<br><span class="excel_done" style="color: #77933c">이카운트 : 엑셀받기 완료</span>');
//        });
//      }
//    });
//  } else {
//    excel_downloader = $.fileDownload(href, {
//      httpMethod: "POST",
//      data: queryString
//    })
//    .always(function() {
//      $('#loading_excel').hide();
//    });
//  }
//
//  return false;
//}
//
//function cancelExcelDownload() {
//  if(excel_downloader) {
//    excel_downloader.abort();
//  }
//  $('#loading_excel').hide();
//}

//출고담당자
$(document).on("change", ".ct_manager", function(e){
  // if(confirm('출고담당자를 변경하시겠습니까?')) {

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
      if(data.result=="success"){
        alert('출고 담당자가 지정되었습니다.');
        // window.location.reload(); 
      } else {
        alert('실패하였습니다.');
      }
    });
  // } else {
    // window.location.reload(); 
  // }
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

function popAddOrder() {
  $("#popup_order_add > div").html("<iframe src='./pop.purchase.order.add.php'></iframe>");
  $("#popup_order_add iframe").load(function(){
    $("#popup_order_add").show();
    $('#hd').css('z-index', 3);
    $('#popup_order_add iframe').contents().find('.mb_id_flexdatalist').focus();
  });
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
