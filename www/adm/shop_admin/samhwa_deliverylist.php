<?php
$sub_menu = '400402';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '출고리스트';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$where = array();
$qstr1 = "od_status=".urlencode($od_status)."&amp;od_settle_case=".urlencode($od_settle_case)."&amp;od_misu=$od_misu&amp;od_cancel_price=$od_cancel_price&amp;od_refund_price=$od_refund_price&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if($default['de_escrow_use'])
  $qstr1 .= "&amp;od_escrow=$od_escrow";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 주문삭제 히스토리 테이블 필드 추가
if(!sql_query(" select mb_id from {$g5['g5_shop_order_delete_table']} limit 1 ", false)) {
  sql_query("
    ALTER TABLE `{$g5['g5_shop_order_delete_table']}`
    ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `de_data`,
    ADD `de_ip` varchar(255) NOT NULL DEFAULT '' AFTER `mb_id`,
    ADD `de_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `de_ip`
  ", true);
}

$sql_lotte = "SELECT count(*) as cnt 
  FROM {$g5['g5_shop_cart_table']} 
  WHERE ct_status = '출고준비' 
  AND ct_delivery_cnt > 0 -- 박스개수 1개 이상
  AND ct_delivery_company = 'lotteglogis' 
  AND ( ct_combine_ct_id IS NULL OR ct_combine_ct_id = '') -- 합포가 아닌것
  AND ( ct_delivery_num IS NULL OR ct_delivery_num = '') -- 송장번호 없는것
  AND ct_edi_result = 0 -- 아직 api 전송 하지 않은것
  AND ct_is_direct_delivery = 0 -- 직배송 아닌것
";
$result_lotte = sql_fetch($sql_lotte);

if( function_exists('pg_setting_check') ){
  pg_setting_check(true);
}

$warehouse_list = get_warehouses();

add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);
?>

<style>

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

<div class="local_ov01 local_ov">
  <?php echo $listall; ?>
  <?php if($od_status == '준비' && $total_count > 0) { ?>
  <a href="./orderdelivery.php" id="order_delivery" class="ov_a">엑셀배송처리</a>
  <?php } ?>
  <div class="right">
    <button id="delivery_excel_upload">택배정보 일괄 업로드</button>
    <select class="sb1" name="" id="ct_manager_sb">
    <?php
        //출고담당자 select
        $od_release_select="";
        $sql_m="select b.`mb_name`, b.`mb_id` from `g5_auth` a left join `g5_member` b on (a.`mb_id`=b.`mb_id`) where a.`au_menu` = '400001'";
        $result_m = sql_query($sql_m);
        $od_release_select .= '<option value="">선택</option>';
        for ($q=0; $row_m=sql_fetch_array($result_m); $q++){
            $selected="";
            $od_release_select .='<option value="'.$row_m['mb_id'].'" '.$selected.'>'.$row_m['mb_name'].'('.$row_m['mb_id'].')</option>';
        }
        echo $od_release_select;
    ?>
    </select>
    <button id="ct_manager_send_all">출고담당자 선택변경</button>

    <select class="sb1" name="it_default_warehouse" id="ct_warehouse_sb">
      <?php
        $default_warehouse_select="";
        $default_warehouse_select .= '<option value="">선택</option>';
        foreach($warehouse_list as $warehouse) {
          $default_warehouse_select .='<option value="'.$warehouse.'" >'.$warehouse.'</option>';
        }
        echo $default_warehouse_select;
      ?>
    </select>
    <button id="ct_warehouse_all">출하창고 선택변경</button>

    <button id="deliveryExcelDownloadBtn">주문다운로드</button>
    <button id="delivery_edi_send_all">로젠 EDI 선택 전송</button>
    <button id="delivery_edi_send_all" data-type="resend">로젠 EDI 재전송</button>
    <button id="delivery_edi_return_all">송장리턴</button>
    <button onclick="applyCombine();">합포적용</button>
    <button onclick="total_picking_excel_download();">토탈피킹 엑셀다운로드</button>
    <?php
      // 23.01.11 : 서원 - [관리자_물류팀]출고리스트 롯데택배 관련버튼 블라인드 처리
    ?>
    <!--
    <button onclick="lotte_delivery_excel_download();">롯데택배 엑셀다운로드</button>
    <button class="lotte_btn" id="delivery_lotte_send" <?php echo ($result_lotte['cnt'] > 0) ? '' : 'disabled'?>><?php echo ($result_lotte['cnt'] > 0) ? '롯데택배 '.$result_lotte['cnt'].'건 전송 필요' : '롯데택배 전송완료'?></button>
    -->
  </div>
</div>

<div id="upload_wrap">
  <form id="form_delivery_excel_upload" style="font-size: 14px;">
    <div class="form-group">
      <label for="datafile">택배정보 일괄 업로드</label>
      <input type="file" name="datafile" id="datafile">
      <p class="help-block">
        주문내역 엑셀에 택배정보를 작성해서 업로드해주세요.<br>
        택배회사 목록 : <?php foreach($delivery_companys as $company) { echo $company['name'].', '; } ?>
      </p>
    </div>
    <button type="submit" class="btn btn-primary">업로드</button>
  </form>
</div>

<div id="loading_excel">
  <div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
    <button onclick="cancelExcelDownload();" class="btn_cancel_excel">취소</button>
  </div>
</div>

<form name="frmsamhwaorderlist" id="frmsamhwaorderlist">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
      <tr>
        <th>날짜</th>
        <td class="date">
          <select name="sel_date_field" id="sel_field">
            <option value="od_time" <?php echo get_selected($sel_date_field, 'od_time'); ?>>주문일</option>
            <option value="od_receipt_time" <?php echo get_selected($sel_date_field, 'od_receipt_time'); ?>>입금일</option>
          </select>
          <div class="sch_last">
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
          <div class="select">
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
          </div>-->
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
          </div>
        </td>
      </tr>
      <tr>
        <th>작업상태</th>
        <td>
          <div class="list">
            <?php
            $not_approved_count = sql_fetch("select count(*) as cnt from g5_cart_barcode_approve_request where status = '승인요청' and del_yn = 'N'")['cnt'];
            ?>
            <input type="checkbox" id="complete1" name="complete1" value="1" <?php echo option_array_checked('1', $complete1); ?>><label for="complete1"> 바코드 미완료 내역만 보기</label>
            <input type="checkbox" id="complete2" name="complete2" value="1" <?php echo option_array_checked('1', $complete2); ?>><label for="complete2"> 배송정보 미입력 내역만 보기</label>
            <input type="checkbox" id="not_complete1" name="not_complete1" value="1" <?php echo option_array_checked('1', $not_complete1); ?>><label for="not_complete1"> 바코드 완료 내역만 보기</label>
            <input type="checkbox" id="not_complete2" name="not_complete2" value="1" <?php echo option_array_checked('1', $not_complete2); ?>><label for="not_complete2"> 배송정보 입력완료 내역만 보기</label>
            <input type="checkbox" id="not_complete3" name="not_complete3" value="1" <?php echo option_array_checked('1', $not_complete3); ?>><label for="not_complete3"> 합포 미적용 내역만 보기</label>
            <input type="checkbox" id="not_approved" name="not_approved" value="1" <?php echo option_array_checked('1', $not_approved); ?>><label for="not_approved"> 미재고 바코드 입력(<?php echo $not_approved_count ?>)</label>
          </div>
        </td>
      </tr>

      <tr>
        <th>검색어</th>
        <td>
          <select name="sel_field" id="sel_field">
            <option value="od_all" <?php echo get_selected($sel_field, 'od_all'); ?>>전체</option>
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
            <option value="barcode" <?php echo get_selected($sel_field, 'barcode'); ?>>바코드</option>
          </select>
          <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
          , 추가 검색어
          <select name="sel_field_add" id="sel_field_add">
            <option value="od_all" <?php echo $sel_field_add == 'od_all' ? 'selected="selected"' : ''; ?>>전체</option>
            <option value="od_id" <?php echo get_selected($sel_field_add, 'od_id'); ?>>주문번호</option>
            <option value="it_name" <?php echo get_selected($sel_field_add, 'it_name'); ?>>상품명</option>
            <option value="it_admin_memo" <?php echo get_selected($sel_field_add, 'it_admin_memo'); ?>>관리자메모</option>
            <option value="mb_id" <?php echo get_selected($sel_field_add, 'mb_id'); ?>>회원 ID</option>
            <option value="od_name" <?php echo get_selected($sel_field_add, 'od_name'); ?>>주문자</option>
            <option value="od_tel" <?php echo get_selected($sel_field_add, 'od_tel'); ?>>주문자전화</option>
            <option value="od_hp" <?php echo get_selected($sel_field_add, 'od_hp'); ?>>주문자핸드폰</option>
            <option value="od_b_name" <?php echo get_selected($sel_field_add, 'od_b_name'); ?>>받는분</option>
            <option value="od_b_tel" <?php echo get_selected($sel_field_add, 'od_b_tel'); ?>>받는분전화</option>
            <option value="od_b_hp" <?php echo get_selected($sel_field_add, 'od_b_hp'); ?>>받는분핸드폰</option>
            <option value="od_deposit_name" <?php echo get_selected($sel_field_add, 'od_deposit_name'); ?>>입금자</option>
            <option value="ct_delivery_num" <?php echo get_selected($sel_field_add, 'ct_delivery_num'); ?>>운송장번호</option>
            <option value="barcode" <?php echo get_selected($sel_field_add, 'barcode'); ?>>바코드</option>
          </select>
          <input type="text" name="search_add" value="<?php echo $search_add; ?>" id="search_add" class="frm_input" autocomplete="off" style="width:200px;">
          	, 추가 검색어
          	<select name="sel_field_add_add" id="sel_field_add_add">
            <option value="od_all" <?php echo $sel_field_add_add == 'od_all' ? 'selected="selected"' : ''; ?>>전체</option>
            <option value="od_id" <?php echo get_selected($sel_field_add_add, 'od_id'); ?>>주문번호</option>
            <option value="it_name" <?php echo get_selected($sel_field_add_add, 'it_name'); ?>>상품명</option>
            <option value="it_admin_memo" <?php echo get_selected($sel_field_add_add, 'it_admin_memo'); ?>>관리자메모</option>
            <option value="mb_id" <?php echo get_selected($sel_field_add_add, 'mb_id'); ?>>회원 ID</option>
            <option value="od_name" <?php echo get_selected($sel_field_add_add, 'od_name'); ?>>주문자</option>
            <option value="od_tel" <?php echo get_selected($sel_field_add_add, 'od_tel'); ?>>주문자전화</option>
            <option value="od_hp" <?php echo get_selected($sel_field_add_add, 'od_hp'); ?>>주문자핸드폰</option>
            <option value="od_b_name" <?php echo get_selected($sel_field_add_add, 'od_b_name'); ?>>받는분</option>
            <option value="od_b_tel" <?php echo get_selected($sel_field_add_add, 'od_b_tel'); ?>>받는분전화</option>
            <option value="od_b_hp" <?php echo get_selected($sel_field_add_add, 'od_b_hp'); ?>>받는분핸드폰</option>
            <option value="od_deposit_name" <?php echo get_selected($sel_field_add_add, 'od_deposit_name'); ?>>입금자</option>
            <option value="ct_delivery_num" <?php echo get_selected($sel_field_add_add, 'ct_delivery_num'); ?>>운송장번호</option>
            <option value="barcode" <?php echo get_selected($sel_field_add_add, 'barcode'); ?>>바코드</option>
          </select>
          <input type="text" name="search_add_add" value="<?php echo $search_add_add; ?>" id="search_add_add" class="frm_input" autocomplete="off" style="width:200px;">
        </td>
      </tr>

    </table>
    <div class="submit">
      <button type="submit"><span>검색</span></button>
      <div class="buttons">
        <button type="button" id="set_default_setting_button" title="기본검색설정" class="ml25">기본검색설정</button>
        <button type="button" id="set_default_apply_button"title="기본검색적용">기본검색적용</button>
        <button type="button" id="search_reset_button" title="검색초기화">검색초기화</button>
      </div>
    </div>
  </div>
</form>
<form name="forderlist" id="forderlist" method="post" autocomplete="off">
  <input type="hidden" name="search_od_status" value="<?php echo $od_status; ?>">

  <div id="samhwa_order_list">
  <div class="ajax-loader">
    <img src="img/ajax-loading.gif" class="img-responsive" />
  </div>  

    <ul class="order_tab">
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
  <!-- <input type="button" value="모든 주문보기" onclick="show_all_order()" id="show_all_order" class="btn btn_03"> -->
</div>
<script>

function show_all_order() {
  page = 1;
  end = false;
  last_step = '';
  doSearch('Y');
}

var od_status = '주문';
var od_step = 0;
var page = 1;
var loading = false;
var end = false;
var sub_menu = '<?php echo $sub_menu; ?>';
var last_step = '';

function doSearch(show_all) {
  // alert(od_status);
  if ( loading === true ) return;
  if ( end === true ) return;

  if (!show_all) {
    show_all = 'N';
  }
  var formdata = $.extend({}, $('#frmsamhwaorderlist').serializeObject(), { 
    od_status: od_status, 
    od_step: od_step, 
    page: page, 
    sub_menu: sub_menu,
    last_step: last_step, 
    show_all: show_all,
  });

  loading = true;
  // console.log(formdata);
  var ajax = $.ajax({
    method: "POST",
    url: "./ajax.deliverylist.php",
    data: formdata,
    beforeSend : function() {
        $('.ajax-loader').css("visibility", "visible");
    },
  })
  .done(function(html) {
    $('.ajax-loader').css("visibility", "hidden");
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
  .fail(function() {
    console.log("ajax error");
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

    if (od_status == "출고준비") {
      $('#show_all_order').show();
    }
    else {
      $('#show_all_order').hide();
    }
  });

  // 출고리스트 접속시 기본검색 적용 자동으로 눌러주기
  $('#set_default_apply_button').click();

  setTimeout(function() {
    $('#samhwa_order_list .order_tab li:eq(0)').click();
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
  
  /* 210226 주문다운로드 */
  $("#deliveryExcelDownloadBtn").click(function(){
    var od_id = [];
    var item = $("input[name='od_id[]']:checked");
    for(var i = 0; i < item.length; i++) {
      od_id.push($(item[i]).val());
    }

    if(!od_id.length) {
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
    var href = "./delivery.excel.list.php";
    
    $('#loading_excel').show();

    excel_downloader = $.fileDownload(href, {
      httpMethod: "POST",
      data: queryString
    })
    .always(function() {
      $('#loading_excel').hide();
    });
    
    return false;
  });

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

// 합포 적용
function applyCombine() {
  var ct_id = [];
  var item = $("input[name='od_id[]']:checked");
  for(var i = 0; i < item.length; i++) {
    ct_id.push($(item[i]).val());
  }

  if(!ct_id.length) {
    return alert('선택한 주문이 없습니다.');
  }

  $.post('ajax.combine.php', {
    ct_id: ct_id
  }, 'json')
  .done(function() {
    item.each(function() {
      if($(this).closest('tr').find('td.od_step span.combine_done').length === 0)
        $(this).closest('tr').find('td.od_step').append('<br><span class="combine_done" style="color: #ff6600">(합포완료)</span>');
    });
    alert('완료되었습니다.');
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
}

// 토탈피킹 엑셀 다운로드
function total_picking_excel_download() {
    var od_id = [];
    var item = $("input[name='od_id[]']:checked");
    for(var i = 0; i < item.length; i++) {
      var ct_id = $(item[i]).val();
      od_id.push(ct_id);
    }

    if(!od_id.length) {
      return alert('선택한 주문이 없습니다.');
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
    var href = "./order.totalpicking.excel.download.php";
    // window.open(href);

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

// 롯데택배 엑셀 다운로드
function lotte_delivery_excel_download() {
    var od_id = [];
    var item = $("input[name='od_id[]']:checked");
    for(var i = 0; i < item.length; i++) {
      var ct_id = $(item[i]).val();
      od_id.push(ct_id);
    }

    if(!od_id.length) {
      return alert('선택한 주문이 없습니다.');
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
    var href = "./order.lotte.delivery.download.php";
    // window.open(href);

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

</script>
<style>
#samhwa_order_list_table>div.table thead tr.fixed {
  top: 102px !important;
}
</style>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
