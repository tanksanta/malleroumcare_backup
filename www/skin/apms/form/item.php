<?php
if (!defined('_GNUBOARD_')) exit;

$pg_anchor ='<ul class="anchor">';
$pg_anchor .='<li><a href="#anc_sitfrm_ini">기본정보</a></li>';
$pg_anchor .='<li><a href="#anc_sitfrm_compact">요약정보</a></li>';
$pg_anchor .='<li><a href="#anc_sitfrm_cost">가격/재고/배송비</a></li>';
$pg_anchor .='<li><a href="#anc_sitfrm_img">이미지</a></li>';
$pg_anchor .='<li><a href="#anc_sitfrm_relation">관련상품</a></li>';

if($is_auth) { // 관리자일 때
  $pg_anchor .='<li><a href="#anc_sitfrm_event">관련이벤트</a></li>';

}
$pg_anchor .='<li><a href="#anc_sitfrm_opt">옵션사항</a></li>';
$pg_anchor .='<li><a href="#anc_sitfrm_attach">파일첨부</a></li>';
if($is_auth) { // 관리자일 때
  $pg_anchor .='<li><a href="#anc_sitfrm_optional">상하단내용</a></li>';
  $pg_anchor .='<li><a href="#anc_sitfrm_extra">여분필드</a></li>';
}
$pg_anchor .='</ul>';

# 210208 entId목록
$entIdSQL = sql_query("SELECT mb_entId FROM g5_member WHERE mb_entId != '' AND mb_entId IS NOT NULL");

# 210208 분류코드 목록
$itemIdList = [];
$itemIdSQL = sql_query("SELECT ca_id, itemId FROM g5_shop_category");
for($i = 0; $row = sql_fetch_array($itemIdSQL); $i++){
  $itemIdList[$row["ca_id"]] = $row["itemId"];
}

# 210616 관리자 메모 기능 추가
if(!sql_query(" select it_admin_memo from {$g5['g5_shop_item_table']} limit 1 ", false)) {
  sql_query("
    ALTER TABLE `{$g5['g5_shop_item_table']}`
    ADD `it_admin_memo` VARCHAR(900) NOT NULL DEFAULT '' AFTER `it_name` ", true
  );
}

# 210617 대여제품 내구연한
if(!sql_query(" select it_rental_use_persisting_year from {$g5['g5_shop_item_table']} limit 1 ", false)) {
  sql_query("
    ALTER TABLE `{$g5['g5_shop_item_table']}`
    ADD `it_rental_use_persisting_year` TINYINT NOT NULL DEFAULT 0 AFTER `it_is_direct_delivery`,
    ADD `it_rental_expiry_year` INT NOT NULL DEFAULT 0 AFTER `it_rental_use_persisting_year`,
    ADD `it_rental_persisting_year` INT NOT NULL DEFAULT 0 AFTER `it_rental_expiry_year`,
    ADD `it_rental_persisting_price` INT NOT NULL DEFAULT 0 AFTER `it_rental_persisting_year` ", true
  );
}

$warehouse_list = get_warehouses();
?>
<style>
.sl { width:100% }
.sm { width:100%; max-width:300px; }
.helper { color:#5b747e; }
.anchor li { width:14.285%; min-width:100px; max-width:140px;}
.anchor li a { text-align:center; display:block; font-size:11px; letter-spacing:-1px; }
.srel .srel_list, .srel .srel_sel { height:315px;max-height:315px; }
.iframe iframe { min-height:370px; }
.btn_confirm { margin-bottom:50px; }

.importantBorder { border: 1px solid #FF3333 !important; }
</style>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_ini" class="anc-section">
  <h2 class="h2_frm">기본정보</h2>
  <div class="tbl_frm01 tbl_wrap">
    <table>
      <caption>기본정보 입력</caption>
      <colgroup>
        <col class="grid_3">
        <col>
      </colgroup>
      <tbody>
        <tr>
          <th scope="row"><label for="it_name">상품종류</label></th>
          <td>
          <?php echo help("상품종류에 따라 주문 및 결제후 이용방법이 달라집니다."); ?>
            <select name="pt_it" id="pt_it" required class="importantBorder">
              <option value="">선택해 주세요.</option>
              <?php echo apms_pt_it($it['pt_it']); ?>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="ca_id">카테고리</label></th>
          <td>
            <?php if ($w == "") echo help("기본 분류를 선택하면, 판매/재고/HTML사용 등을, 선택한 분류의 기본값으로 설정합니다."); ?>
            <?php echo help('각 분류는 기본 분류의 하위 분류 개념이 아니므로 기본 분류 선택시 해당 자료가 포함될 최하위 분류만 선택하시면 됩니다.'); ?>
            <select name="ca_id" id="ca_id" onchange="categorychange(this.form)" class="importantBorder">
              <option value="">기본 분류 선택</option>
              <?php echo conv_selected_option($category_select, $it['ca_id']); ?>
            </select>
            <script>
              var ca_use = new Array();
              var ca_stock_qty = new Array();
              //var ca_explan_html = new Array();
              var ca_sell_email = new Array();
              var ca_opt1_subject = new Array();
              var ca_opt2_subject = new Array();
              var ca_opt3_subject = new Array();
              var ca_opt4_subject = new Array();
              var ca_opt5_subject = new Array();
              var ca_opt6_subject = new Array();
              <?php echo "\n$script"; ?>
            </script>
            <?php for ($i=2; $i<=10; $i++) { ?>
            <select name="ca_id<?php echo $i; ?>" id="ca_id<?php echo $i; ?>">
              <option value=""><?php echo $i;?>차 분류 선택</option>
              <?php echo conv_selected_option($category_select, $it['ca_id'.$i]); ?>
            </select>
            <?php echo $i ==5 ? '<br/>' : ''; ?>
            <?php } ?>
          </td>
        </tr>

        <tr>
          <th scope="row">상품관리코드</th>
          <td>
            <?php if ($w == '') { // 추가 ?>
            <!-- 최근에 입력한 코드(자동 생성시)가 목록의 상단에 출력되게 하려면 아래의 코드로 대체하십시오. -->
            <!-- <input type=text class=required name=it_id value="<?php echo 10000000000-time()?>" size=12 maxlength=10 required> <a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a> -->
            <?php echo ($is_auth) ? help("상품관리코드는 10자리 숫자로 자동생성합니다. 직접 상품관리코드를 입력시 영문자, 숫자, - 만 입력 가능합니다.") : help("상품관리코드는 10자리 숫자로 자동생성합니다."); ?>
            <input type="text" name="it_id" value="<?php echo time(); ?>" id="it_id" required class="frm_input required importantBorder" size="20" maxlength="20" readonly>
            <!-- <?php if ($default['de_code_dup_use']) { ?><button type="button" class="btn_frmline" onclick="codedupcheck(document.all.it_id.value)">중복검사</a><?php } ?> -->
            <?php } else { ?>
            <input type="hidden" name="it_id" id="it_id" value="<?php echo $it['it_id']; ?>">
            <span class="frm_ca_id"><?php echo $it['it_id']; ?></span>
            <a href="<?php echo G5_SHOP_URL; ?>/item.php?it_id=<?php echo $it_id; ?>" target="blank" class="btn_frmline">상품확인</a>
            <a href="./itemuselist.php?sfl=a.it_id&amp;stx=<?php echo $it_id; ?>" target="blank" class="btn_frmline">관련후기</a>
            <a href="./itemqalist.php?sfl=a.it_id&amp;stx=<?php echo $it_id; ?>" target="blank" class="btn_frmline">관련문의</a>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_thezone">분류코드</label></th>
          <td>
            <input type="text" name="it_thezone" value="<?php echo $it['it_thezone']; ?>" id="it_thezone" class="frm_input importantBorder sl" readonly>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_thezone2">품목코드</label></th>
          <td>
            <input type="text" name="it_thezone2" value="<?php echo $it['it_thezone2']; ?>" id="it_thezone" class="frm_input importantBorder sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_name">상품명</label></th>
          <td>
            <?php echo help("HTML 입력이 불가합니다."); ?>
            <input type="text" name="it_name" value="<?php echo get_text(cut_str($it['it_name'], 250, "")); ?>" id="it_name" required class="frm_input required importantBorder sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_expected_warehousing_date">입고예정일 알림</label></th>
          <td>
            <input type="text" name="it_expected_warehousing_date" value="<?php echo get_text(cut_str($it['it_expected_warehousing_date'], 250, "")); ?>" id="it_expected_warehousing_date" class="frm_input sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_warehousing_warehouse">입고창고</label></th>
          <td>
            <?php echo help("품목입고시 지정한 창고가 기본으로 설정됩니다."); ?>
            <select name="it_warehousing_warehouse" id="it_warehousing_warehouse">
              <option value="">창고선택</option>
              <?php
              foreach($warehouse_list as $warehouse) {
                echo '<option value="'.$warehouse.'" '.get_selected($it['it_warehousing_warehouse'], $warehouse).'>'.$warehouse.'</option>';
              }
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_name">관리자메모</label></th>
          <td>
            <?php echo help("상품 검색에 활용될 수 있습니다. HTML 입력이 불가합니다."); ?>
            <input type="text" name="it_admin_memo" value="<?php echo get_text($it['it_admin_memo']); ?>" id="it_admin_memo" class="frm_input sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_basic">기본설명</label></th>
          <td>
            <?php echo help("상품명 하단에 상품에 대한 추가적인 설명이 필요한 경우에 입력합니다. HTML 입력도 가능합니다."); ?>
            <input type="text" name="it_basic" value="<?php echo get_text(html_purifier($it['it_basic'])); ?>" id="it_basic" class="frm_input sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="prodSym">재질</label></th>
          <td>
            <input type="text" name="prodSym" value="<?php echo get_text($it['prodSym']); ?>" id="prodSym" class="frm_input importantBorder" size="40">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="prodSizeDetail">사이즈 상세정보</label></th>
          <td>
            <input type="text" name="prodSizeDetail" value="<?php echo get_text($it['prodSizeDetail']); ?>" id="prodSizeDetail" class="frm_input importantBorder sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="prodWeig">중량</label></th>
          <td>
            <input type="text" name="prodWeig" value="<?php echo get_text($it['prodWeig']); ?>" id="prodWeig" class="frm_input importantBorder" size="40">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_standard">규격</label></th>
          <td>
            <input type="text" name="it_standard" value="<?php echo get_text($it['it_standard']); ?>" id="it_standard" class="frm_input" size="40">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="pt_tag">검색태그</label></th>
          <td>
            <?php echo help("등록할 검색태그를 콤마(,)로 구분해서 입력합니다."); ?>
            <input type="text" name="pt_tag" value="<?php echo get_text($it['pt_tag']); ?>" id="pt_tag" class="frm_input sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="entId">업체 아이디</label></th>
          <td>
            <select name="entId" id="entId">
            <?php for($i = 0; $row = sql_fetch_array($entIdSQL); $i++){ ?>
              <option value="<?=$row["mb_entId"]?>" <?=(get_text($it["entId"] == $row["mb_entId"])) ? "selected" : ""?>><?=$row["mb_entId"]?></option>
            <?php } ?>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="prodSupYn">유통구분</label></th>
          <td>
            <select name="prodSupYn" id="prodSupYn">
              <option value="Y" <?=(get_text($it["prodSupYn"] == "Y")) ? "selected" : ""?>>유통</option>
              <option value="N" <?=(get_text($it["prodSupYn"] == "N")) ? "selected" : ""?>>비유통</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_taxInfo">세무정보</label></th>
          <td>
            <select name="it_taxInfo" id="it_taxInfo">
              <option value="영세" <?=(get_text($it["it_taxInfo"] == "영세")) ? "selected" : ""?>>영세</option>
              <option value="과세" <?=(get_text($it["it_taxInfo"] == "과세")) ? "selected" : ""?>>과세</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_show_partner_search">상품 추가 설정</label></th>
          <td>
            <input type="checkbox" name="it_show_partner_search" value="1" id="it_show_partner_search" <?php echo ($it['it_show_partner_search']) ? "checked" : ""; ?>> 파트너 상품 검색 노출
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_use_short_barcode">바코드 입력방법</label></th>
          <td>
            <input type="checkbox" name="it_use_short_barcode" value="1" id="it_use_short_barcode" <?php echo ($it['it_use_short_barcode']) ? "checked" : ""; ?>> 바코드 8자리 사용(보장구)
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="supId">공급자 아이디</label></th>
          <td>
              <input type="text" name="supId" value="<?php echo get_text($it['supId']); ?>" id="supId" class="frm_input sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="prodPayCode">제품코드</label></th>
          <td>
              <input type="text" name="prodPayCode" value="<?php echo get_text($it['ProdPayCode']); ?>" id="prodPayCode" class="frm_input importantBorder sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="pt_show">마이샵순서</label></th>
          <td>
            <?php echo help("숫자가 작을 수록 마이샵 상위에 출력되며, -2147483648 부터 2147483647 까지 입력가능합니다. 미입력시 자동으로 출력됩니다."); ?>
            <input type="text" name="pt_show" value="<?php echo $it['pt_show']; ?>" id="pt_show" class="frm_input" size="12">
          </td>
        </tr>
        <?php if($is_auth) { // 관리자일 때만 출력 ?>
        <tr>
          <th scope="row"><label for="it_order">출력순서</label></th>
          <td>
            <?php echo help("숫자가 작을 수록 상위에 출력되며, -2147483648 부터 2147483647 까지 입력가능합니다. 미입력시 자동으로 출력됩니다."); ?>
            <input type="text" name="it_order" value="<?php echo $it['it_order']; ?>" id="it_order" class="frm_input" size="12">
          </td>
        </tr>
        <tr>
          <th scope="row">상품태그</th>
          <td>
            <?php echo help("메인화면에 유형별로 출력할때 사용합니다. 목록에서 유형별로 정렬할때 체크된 것이 가장 먼저 출력됩니다."); ?>
            <input type="checkbox" name="it_type1" value="1" <?php echo ($it['it_type1'] ? "checked" : ""); ?> id="it_type1">
            <label for="it_type1"><span style="color:<?php echo $default['de_it_type1_color']; ?>"><?php echo $default['de_it_type1_name']; ?></span></label>
            &nbsp;
            <input type="checkbox" name="it_type2" value="1" <?php echo ($it['it_type2'] ? "checked" : ""); ?> id="it_type2">
            <label for="it_type2"><span style="color:<?php echo $default['de_it_type2_color']; ?>"><?php echo $default['de_it_type2_name']; ?></span></label>
            &nbsp;
            <input type="checkbox" name="it_type3" value="1" <?php echo ($it['it_type3'] ? "checked" : ""); ?> id="it_type3">
            <label for="it_type3"><span style="color:<?php echo $default['de_it_type3_color']; ?>"><?php echo $default['de_it_type3_name']; ?></span></label>
            &nbsp;
            <input type="checkbox" name="it_type4" value="1" <?php echo ($it['it_type4'] ? "checked" : ""); ?> id="it_type4">
            <label for="it_type4"><span style="color:<?php echo $default['de_it_type4_color']; ?>"><?php echo $default['de_it_type4_name']; ?></span></label>
            &nbsp;
            <input type="checkbox" name="it_type5" value="1" <?php echo ($it['it_type5'] ? "checked" : ""); ?> id="it_type5">
            <label for="it_type5"><span style="color:<?php echo $default['de_it_type5_color']; ?>"><?php echo $default['de_it_type5_name']; ?></span></label>
            <?php
            for($x = 6; $x <= 10; $x ++) {
              $cur_it_type = 'it_type' . $x;
              if($default['de_'. $cur_it_type .'_name']) {
            ?>
            &nbsp;
            <input type="checkbox" name="<?=$cur_it_type?>" value="1" <?php echo ($it[$cur_it_type] ? "checked" : ""); ?> id="<?=$cur_it_type?>">
            <label for="<?=$cur_it_type?>"><span style="color:<?php echo $default['de_' . $cur_it_type . '_color']; ?>"><?php echo $default['de_' . $cur_it_type . '_name']; ?></span></label>
            <?php
              }
            }
            ?>
            &nbsp;
            <input type="checkbox" name="pt_main" value="1" <?php echo ($it['pt_main'] ? "checked" : ""); ?> id="pt_main">
            <label for="pt_main">메인</label>

            <br/>
            <?php echo help("등록할 상품태그를 “태그명:색정보”로 입력하고 콤마(,)로 구분해서 입력합니다."); ?>
            <input type="text" name="it_type" value="<?php echo get_text($it['it_type']); ?>" id="it_type" class="frm_input" size="40" placeholder="추가 태그 입력">
          </td>
        </tr>
        <?php } // 관리자 끝 ?>
        <tr>
          <th scope="row"><label for="it_maker">제조사</label></th>
          <td>
            <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
            <input type="text" name="it_maker" value="<?php echo get_text($it['it_maker']); ?>" id="it_maker" class="frm_input" size="40">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_origin">원산지</label></th>
          <td>
            <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
            <input type="text" name="it_origin" value="<?php echo get_text($it['it_origin']); ?>" id="it_origin" class="frm_input" size="40">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_brand">브랜드</label></th>
          <td>
            <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
            <input type="text" name="it_brand" value="<?php echo get_text($it['it_brand']); ?>" id="it_brand" class="frm_input" size="40">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_model">모델</label></th>
          <td>
            <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
            <input type="text" name="it_model" value="<?php echo get_text($it['it_model']); ?>" id="it_model" class="frm_input" size="40">
          </td>
        </tr>
        <?php if($is_auth) { // 관리자일 때만 출력 ?>
        <tr>
          <th scope="row"><label for="it_tel_inq">전화문의</label></th>
          <td>
            <?php echo help("상품 금액 대신 전화문의로 표시됩니다."); ?>
            <input type="checkbox" name="it_tel_inq" value="1" id="it_tel_inq" <?php echo ($it['it_tel_inq']) ? "checked" : ""; ?>> 예
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_nocoupon">쿠폰적용안함</label></th>
          <td>
            <?php echo help("설정에 체크하시면 쿠폰 생성 때 상품 검색 결과에 노출되지 않습니다."); ?>
            <input type="checkbox" name="it_nocoupon" value="1" id="it_nocoupon" <?php echo ($it['it_nocoupon']) ? "checked" : ""; ?>> 예
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_use">포인트결제안함</label></th>
          <td>
            <?php echo help("설정에 체크하시면 주문시 본 상품이 포함된 경우 포인트 결제 또는 포인트 사용을 할 수 없습니다."); ?>
            <label><input type="checkbox" name="pt_point" value="1" id="pt_point" <?php echo ($it['pt_point']) || !$w ? "checked" : ""; ?>> 예</label>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="ec_mall_pid">네이버쇼핑 상품ID</label></th>
          <td colspan="2">
            <?php echo help("네이버쇼핑에 입점한 경우 네이버쇼핑 상품ID를 입력하시면 네이버페이와 연동됩니다."); ?>
            <input type="text" name="ec_mall_pid" value="<?php echo get_text($it['ec_mall_pid']); ?>" id="ec_mall_pid" class="frm_input" size="20">
          </td>
        </tr>
        <?php } // 관리자 끝 ?>
        <tr>
          <th scope="row"><label for="it_use">판매가능</label></th>
          <td>
            <?php echo help("잠시 판매를 중단하거나 재고가 없을 경우에 체크를 해제해 놓으면 출력되지 않으며, 주문도 받지 않습니다."); ?>
            <label><input type="checkbox" name="it_use" value="1" id="it_use" <?php echo ($it['it_use']) ? "checked" : ""; ?>> 예</label>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_use_partner">파트너몰 판매가능</label></th>
          <td>
            <?php echo help("파트너몰에서 잠시 판매를 중단하거나 재고가 없을 경우에 체크를 해제해 놓으면 출력되지 않으며, 주문도 받지 않습니다."); ?>
            <label><input type="checkbox" name="it_use_partner" value="1" id="it_use_partner" <?php echo ($it['it_use_partner']) ? "checked" : ""; ?>> 예</label>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_use_custom_order">주문제작 가능</label></th>
          <td>
            <?php echo help("체크하시면 관리자 주문내역에서 주문제작이 가능합니다."); ?>
            <label><input type="checkbox" name="it_use_custom_order" value="1" id="it_use_custom_order" <?php echo ($it['it_use_custom_order']) ? "checked" : ""; ?>> 예</label>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_outsourcing_use">외부 발주</label></th>
          <td>
            <label><input type="checkbox" name="it_outsourcing_use" value="1" id="it_outsourcing_use" <?php echo ($it['it_outsourcing_use']) ? "checked" : ""; ?>> 사용하기</label><br/><br/>
            <table class="it_outsourcing">
              <tbody>
                <tr>
                  <th>거래처ID</th>
                  <td>
                    <input type="text" name="it_outsourcing_id" value="<?php echo get_text($it['it_outsourcing_id']); ?>" id="it_outsourcing_id" class="frm_input" size="40">
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    <p><b>- 주문 요청 시 선택사항 정보 (여러개일경우 콤마(,)로 추가해주세요.)</b></p>
                    <input type="text" name="it_outsourcing_option" value="<?php echo get_text($it['it_outsourcing_option']); ?>" id="it_outsourcing_option" class="frm_input" style="width:19%;" placeholder="옵션1">
                    <input type="text" name="it_outsourcing_option2" value="<?php echo get_text($it['it_outsourcing_option2']); ?>" id="it_outsourcing_option2" class="frm_input" style="width:19%;" placeholder="옵션2">
                    <input type="text" name="it_outsourcing_option3" value="<?php echo get_text($it['it_outsourcing_option3']); ?>" id="it_outsourcing_option3" class="frm_input" style="width:19%;" placeholder="옵션3">
                    <input type="text" name="it_outsourcing_option4" value="<?php echo get_text($it['it_outsourcing_option4']); ?>" id="it_outsourcing_option4" class="frm_input" style="width:19%;" placeholder="옵션4">
                    <input type="text" name="it_outsourcing_option5" value="<?php echo get_text($it['it_outsourcing_option5']); ?>" id="it_outsourcing_option5" class="frm_input" style="width:19%;" placeholder="옵션5">
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <th scope="row">상품설명</th>
          <td>
            <a href="<?php echo G5_BBS_URL;?>/helper.php" target="_blank" class="btn_frmline win_scrap">기능안내</a>
            <a href="<?php echo G5_BBS_URL;?>/helper.php?act=map" target="_blank" class="btn_frmline win_scrap">구글지도</a>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <?php echo editor_html('it_explan', get_text(html_purifier($it['it_explan']), 0)); ?>
          </td>
        </tr>
        <tr>
          <th scope="row">모바일 상품설명</th>
          <td>
            <a href="<?php echo G5_BBS_URL;?>/helper.php" target="_blank" class="btn_frmline win_scrap">기능안내</a>
            <a href="<?php echo G5_BBS_URL;?>/helper.php?act=map" target="_blank" class="btn_frmline win_scrap">구글지도</a>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="iframe">
            <?php echo editor_html('it_mobile_explan', get_text(html_purifier($it['it_mobile_explan']), 0)); ?>
          </td>
        </tr>
        <tr>
          <th scope="row">작업시 참고사항</th>
          <td>작업지시서 하단에 출력됩니다.</td>
        </tr>
        <tr>
          <td colspan="2">
            <?php echo editor_html('it_reference', get_text(html_purifier($it['it_reference']), 0)); ?>
          </td>
        </tr>
        <?php if($is_auth) { // 관리자일 때만 출력 ?>
        <tr>
          <th scope="row">추천인 적립율</th>
          <td>
            <?php echo help("부가세를 제한 순판매액에 대해 추천인(마케터)에게 적립됩니다."); ?>
            <input type="text" name="pt_marketer" value="<?php echo $it['pt_marketer']; ?>" id="pt_marketer" class="frm_input sm"> % 적립
          </td>
        </tr>
        <tr>
          <th scope="row">파트너 아이디</th>
          <td>
            <?php echo help("미등록시 최고관리자 아이디로 모든 활동이 이루어집니다."); ?>
            <input type="text" name="pt_id" value="<?php echo $it['pt_id']; ?>" id="pt_id" class="frm_input sm">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_sell_email">판매자 e-mail</label></th>
          <td>
            <?php echo help("운영자와 실제 판매자가 다른 경우 실제 판매자의 e-mail을 입력하면, 상품 주문 시점을 기준으로 실제 판매자에게도 주문서를 발송합니다."); ?>
            <input type="text" name="it_sell_email" value="<?php echo get_sanitize_input($it['it_sell_email']); ?>" id="it_sell_email" class="frm_input sm">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="it_shop_memo">상점메모</label></th>
          <td><textarea name="it_shop_memo" id="it_shop_memo"><?php echo html_purifier($it['it_shop_memo']); ?></textarea></td>
        </tr>
        <?php } // 관리자 끝 ?>
      </tbody>
    </table>
  </div>
</section>

<?php echo $frm_submit; ?>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_compact">
  <h2 class="h2_frm">상품요약정보</h2>
  <div class="local_desc02 local_desc">
    <p><strong>전자상거래 등에서의 상품 등의 정보제공에 관한 고시</strong>에 따라 총 35개 상품군에 대해 상품 특성 등을 양식에 따라 입력할 수 있습니다.</p>
  </div>

  <div id="sit_compact">
    <?php echo help("상품군을 선택하면 자동으로 항목이 변환됩니다."); ?>
    <select id="it_info_gubun" name="it_info_gubun">
      <option value="">상품군을 선택하세요.</option>
      <?php echo apms_it_gubun($it['it_info_gubun'], 'wear'); // 우측은 초기값 ?>
    </select>
  </div>
  <div id="sit_compact_fields">
    <?php include_once('./iteminfo.php'); ?>
  </div>

  <script>
  $(function(){
      $(document).on("change", "#it_info_gubun", function() {
      var gubun = $(this).val();
      $.post(
        "./iteminfo.php",
        { it_id: "<?php echo $it['it_id']; ?>", gubun: gubun },
        function(data) {
          $("#sit_compact_fields").empty().html(data);
        }
      );
    });
  });
  </script>
</section>

<?php echo $frm_submit; ?>

<?php echo $pg_anchor; ?>


<section id="anc_sitfrm_cost">
  <h2 class="h2_frm">가격 및 재고</h2>
  <div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>가격 및 재고 입력</caption>
    <colgroup>
      <col class="grid_3">
      <col>
    </colgroup>
    <tbody>
    <tr>
      <th scope="row"><label for="it_price">판매가격</label></th>
      <td>
        <?php
        echo help("사업소별 가격 지정시 해당 사업소는 묶음할인 적용이 불가능합니다.");
        $entprice_count = sql_fetch(" select count(*) as cnt from g5_shop_item_entprice where it_id = '$it_id' and it_price > 0 ");
        ?>
        <input type="text" name="it_price" value="<?php echo $it['it_price']; ?>" id="it_price" class="frm_input importantBorder" size="8"> 원, <span id="entprice_count"><?=$entprice_count['cnt']?></span>개 사업소 가격 수정 <button type="button" id="btn_entprice" class="btn_frmline" data-id="<?=$it_id?>">사업소 가격 지정</button>
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_purchase_order_price">구매가격</label></th>
      <td>
        <?php
        $average_sales_qty = get_average_sales_qty($it_id, 3);
        ?>
        <input type="text" name="it_purchase_order_price" value="<?php echo $it['it_purchase_order_price']; ?>" id="it_purchase_order_price" class="frm_input" size="8"> 원
        (안전재고:<input type="text" name="it_stock_manage_min_qty" value="<?php echo $it['it_stock_manage_min_qty']; ?>" placeholder="<?php echo (int)($average_sales_qty / 2) ?>" class="frm_input" size="8"> 개,
        최대재고:<input type="text" name="it_stock_manage_max_qty" value="<?php echo $it['it_stock_manage_max_qty']; ?>" placeholder="<?php echo (int)($average_sales_qty * 1.5) ?>" class="frm_input" size="8"> 개)
        <br>
        <label for="it_purchase_order_min_qty">최소구매 주문수량</label>: <input type="text" name="it_purchase_order_min_qty" id="it_purchase_order_min_qty" value="<?php echo $it['it_purchase_order_min_qty']; ?>" class="frm_input" size="8"> 개,
        <label for="it_purchase_order_unit">주문단위</label> : <input type="text" name="it_purchase_order_unit" id="it_purchase_order_unit" value="<?php echo $it['it_purchase_order_unit']; ?>" class="frm_input" size="8"> 개씩 구매
      </td>
    </tr>
    <?php if($is_auth) { // 관리자일 때만 출력 ?>
    <tr>
      <th scope="row"><label for="it_cust_price">급여가</label></th>
      <td>
        <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
        <input type="text" name="it_cust_price" value="<?php echo $it['it_cust_price']; ?>" id="it_cust_price" class="frm_input importantBorder" size="8"> 원
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_rental_price">대여금액(월)</label></th>
      <td>
        <input type="text" name="it_rental_price" value="<?php echo $it['it_rental_price']; ?>" id="it_rental_price" class="frm_input" size="8"> 원
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_rental_price">대여(내구연한설정)</label></th>
      <td>
      <label><input type="checkbox" name="it_rental_use_persisting_year" value="1" id="it_rental_use_persisting_year" <?php echo ($it['it_rental_use_persisting_year']) ? "checked" : ""; ?>> 사용</label><br>
      사용가능햇수: <input type="text" name="it_rental_expiry_year" value="<?php echo $it['it_rental_expiry_year']; ?>" id="it_rental_expiry_year" class="frm_input" size="2"> 년<br>
      연장대여햇수: <input type="text" name="it_rental_persisting_year" value="<?php echo $it['it_rental_persisting_year']; ?>" id="it_rental_persisting_year" class="frm_input" size="2"> 년 / 월 대여금액 <input type="text" name="it_rental_persisting_price" value="<?php echo $it['it_rental_persisting_price']; ?>" id="it_rental_persisting_price" class="frm_input" size="8"> 원
      </td>
    </tr>
    <?php } // 관리자 끝 ?>
    <tr>
      <th scope="row"><label for="it_price_dealer">사업소 판매가격</label></th>
      <td>
        사업소&nbsp;&nbsp;
        <input type="text" name="it_price_dealer" value="<?php echo $it['it_price_dealer']; ?>" id="it_price_dealer" class="frm_input" size="8"> 원
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <button type="button" class="btn_submit btn" id="dealer_btn">자동계산</button>
        <select name="it_price_dealer_percent" id="it_price_dealer_percent">
          <?php for($p=5;$p<=100;$p=$p+5) { ?>
            <option value="<?php echo $p; ?>" <?php echo $p == 15 ? 'selected' : ''; ?>><?php echo $p; ?>%</option>
          <?php } ?>
        </select>

        <br/>
        우수사업소&nbsp;&nbsp;
        <input type="text" name="it_price_dealer2" value="<?php echo $it['it_price_dealer2']; ?>" id="it_price_dealer2" class="frm_input" size="8"> 원
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <button type="button" class="btn_submit btn" id="dealer_btn2">자동계산</button>
        <select name="it_price_dealer_percent2" id="it_price_dealer_percent2">
          <?php for($p=5;$p<=100;$p=$p+5) { ?>
            <option value="<?php echo $p; ?>" <?php echo $p == 15 ? 'selected' : ''; ?>><?php echo $p; ?>%</option>
          <?php } ?>
        </select>
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_sale_cnt">묶음할인(쇼핑몰전용)</label></th>
      <td>
        <input type="text" name="it_sale_cnt" value="<?=$it["it_sale_cnt"]?>" id="it_sale_cnt" class="frm_input" size="2"> 개 이상 
        사업소<input type="text" name="it_sale_percent" value="<?=$it["it_sale_percent"]?>" id="it_sale_percent" class="frm_input" size="8"> 원 / 
        우수사업소<input type="text" name="it_sale_percent_great" value="<?=$it["it_sale_percent_great"]?>" id="it_sale_percent_great" class="frm_input" size="8"> 원<br>

        <input type="text" name="it_sale_cnt_02" value="<?=$it["it_sale_cnt_02"]?>" id="it_sale_cnt_02" class="frm_input" size="2"> 개 이상 
        사업소<input type="text" name="it_sale_percent_02" value="<?=$it["it_sale_percent_02"]?>" id="it_sale_percent_02" class="frm_input" size="8"> 원 / 
        우수사업소<input type="text" name="it_sale_percent_great_02" value="<?=$it["it_sale_percent_great_02"]?>" id="it_sale_percent_great_02" class="frm_input" size="8"> 원<br>

        <input type="text" name="it_sale_cnt_03" value="<?=$it["it_sale_cnt_03"]?>" id="it_sale_cnt_03" class="frm_input" size="2"> 개 이상 
        사업소<input type="text" name="it_sale_percent_03" value="<?=$it["it_sale_percent_03"]?>" id="it_sale_percent_03" class="frm_input" size="8"> 원 / 
        우수사업소<input type="text" name="it_sale_percent_great_03" value="<?=$it["it_sale_percent_great_03"]?>" id="it_sale_percent_great_03" class="frm_input" size="8"> 원<br>

        <input type="text" name="it_sale_cnt_04" value="<?=$it["it_sale_cnt_04"]?>" id="it_sale_cnt_04" class="frm_input" size="2"> 개 이상 
        사업소<input type="text" name="it_sale_percent_04" value="<?=$it["it_sale_percent_04"]?>" id="it_sale_percent_04" class="frm_input" size="8"> 원 / 
        우수사업소<input type="text" name="it_sale_percent_great_04" value="<?=$it["it_sale_percent_great_04"]?>" id="it_sale_percent_great_04" class="frm_input" size="8"> 원<br>

        <input type="text" name="it_sale_cnt_05" value="<?=$it["it_sale_cnt_05"]?>" id="it_sale_cnt_05" class="frm_input" size="2"> 개 이상 
        사업소<input type="text" name="it_sale_percent_05" value="<?=$it["it_sale_percent_05"]?>" id="it_sale_percent_05" class="frm_input" size="8"> 원 / 
        우수사업소<input type="text" name="it_sale_percent_great_05" value="<?=$it["it_sale_percent_great_05"]?>" id="it_sale_percent_great_05" class="frm_input" size="8"> 원<br>
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_price_partner">파트너몰 판매가격</label></th>
      <td>
        <input type="text" name="it_price_partner" value="<?php echo $it['it_price_partner']; ?>" id="it_price_partner" class="frm_input" size="8"> 원
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <button type="button" class="btn_submit btn" id="partner_btn">자동계산</button>
        <select name="it_price_partner_percent" id="it_price_partner_percent">
          <?php for($p=5;$p<=100;$p=$p+5) { ?>
            <option value="<?php echo $p; ?>" <?php echo $p == 20 ? 'selected' : ''; ?>><?php echo $p; ?>%</option>
          <?php } ?>
        </select>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="it_point_type">포인트 유형</label></th>
      <td>
        <?php echo help("포인트 유형을 설정할 수 있습니다. 비율로 설정했을 경우 설정 기준금액의 %비율로 포인트가 지급됩니다."); ?>
        <select name="it_point_type" id="it_point_type">
          <option value="0"<?php echo get_selected('0', $it['it_point_type']); ?>>설정금액</option>
          <option value="1"<?php echo get_selected('1', $it['it_point_type']); ?>>판매가기준 설정비율</option>
          <?php if($is_auth) { // 관리자일 때만 출력 ?>
            <option value="2"<?php echo get_selected('2', $it['it_point_type']); ?>>구매가기준 설정비율</option>
          <?php } ?>
        </select>
        <script>
        $(function() {
          $("#it_point_type").change(function() {
            if(parseInt($(this).val()) > 0)
              $("#it_point_unit").text("%");
            else
              $("#it_point_unit").text("점");
          });
        });
        </script>
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_point">포인트</label></th>
      <td>
        <?php echo help("주문완료후 환경설정에서 설정한 주문완료 설정일 후 회원에게 부여하는 포인트입니다.\n또, 포인트부여를 '아니오'로 설정한 경우 신용카드, 계좌이체로 주문하는 회원께는 부여하지 않습니다."); ?>
        <input type="text" name="it_point" value="<?php echo $it['it_point']; ?>" id="it_point" class="frm_input" size="8"> <span id="it_point_unit"><?php if($it['it_point_type']) echo '%'; else echo '점'; ?></span>
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_supply_point">추가옵션상품 포인트</label></th>
      <td>
        <?php echo help("상품의 추가옵션상품 구매에 일괄적으로 지급하는 포인트입니다. 0으로 설정하시면 구매포인트를 지급하지 않습니다.\n주문완료후 환경설정에서 설정한 주문완료 설정일 후 회원에게 부여하는 포인트입니다.\n또, 포인트부여를 '아니오'로 설정한 경우 신용카드, 계좌이체로 주문하는 회원께는 부여하지 않습니다."); ?>
        <input type="text" name="it_supply_point" value="<?php echo $it['it_supply_point']; ?>" id="it_supply_point" class="frm_input" size="8"> 점
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_soldout">상품품절</label></th>
      <td>
        <?php echo help("잠시 판매를 중단하거나 재고가 없을 경우에 체크해 놓으면 품절상품으로 표시됩니다."); ?>
        <input type="checkbox" name="it_soldout" value="1" id="it_soldout" <?php echo ($it['it_soldout']) ? "checked" : ""; ?>> 예
      </td>
    </tr>
    <?php if($is_auth) { // 관리자일 때만 출력 ?>
      <tr>
        <th scope="row"><label for="it_stock_sms">재입고SMS 알림</label></th>
        <td>
          <?php echo help("상품이 품절인 경우에 체크해 놓으면 상품상세보기에서 고객이 재입고SMS 알림을 신청할 수 있게 됩니다."); ?>
          <input type="checkbox" name="it_stock_sms" value="1" id="it_stock_sms" <?php echo ($it['it_stock_sms']) ? "checked" : ""; ?>> 예
        </td>
      </tr>
    <?php } // 관리자 끝 ?>
    <tr>
      <th scope="row"><label for="it_stock_qty">재고수량</label></th>
      <td>
        <?php echo help("<b>주문관리에서 상품별 상태 변경에 따라 자동으로 재고를 가감합니다.</b> 재고는 규격/색상별이 아닌, 상품별로만 관리됩니다.<br>재고수량을 0으로 설정하시면 품절상품으로 표시됩니다."); ?>
        <input type="text" name="it_stock_qty" value="<?php echo $it['it_stock_qty']; ?>" id="it_stock_qty" class="frm_input" size="8"> 개
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_noti_qty">재고 통보수량</label></th>
      <td>
        <?php echo help("상품의 재고가 통보수량보다 작을 때 쇼핑몰 현황 재고부족상품에 표시됩니다.<br>옵션이 있는 상품은 개별 옵션의 통보수량이 적용됩니다."); ?>
        <input type="text" name="it_noti_qty" value="<?php echo $it['it_noti_qty']; ?>" id="it_noti_qty" class="frm_input" size="8"> 개
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_buy_min_qty">최소구매수량</label></th>
      <td>
        <?php echo help("상품 구매시 최소 구매 수량을 설정합니다."); ?>
        <input type="text" name="it_buy_min_qty" value="<?php echo $it['it_buy_min_qty']; ?>" id="it_buy_min_qty" class="frm_input" size="8"> 개
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_buy_max_qty">최대구매수량</label></th>
      <td>
        <?php echo help("상품 구매시 최대 구매 수량을 설정합니다."); ?>
        <input type="text" name="it_buy_max_qty" value="<?php echo $it['it_buy_max_qty']; ?>" id="it_buy_max_qty" class="frm_input" size="8"> 개
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_buy_max_qty">수량증가단위</label></th>
      <td>
        <?php echo help("상품 구매시 증가하는 단위를 설정합니다."); ?>
        <input type="text" name="it_buy_inc_qty" value="<?php echo $it['it_buy_inc_qty']; ?>" id="it_buy_inc_qty" class="frm_input" size="8"> 개
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="it_notax">상품과세 유형</label></th>
      <td>
        <?php echo help("상품의 과세유형(과세, 비과세)을 설정합니다."); ?>
        <select name="it_notax" id="it_notax">
          <option value="0"<?php echo get_selected('0', $it['it_notax']); ?>>과세</option>
          <option value="1"<?php echo get_selected('1', $it['it_notax']); ?>>비과세</option>
        </select>
      </td>
    </tr>
    <?php
      $opt_subject = explode(',', $it['it_option_subject']);
    ?>
    <tr>
      <th scope="row">상품선택옵션</th>
      <td>
        <div class="sit_option tbl_frm01">
          <?php echo help('옵션항목은 콤마(,) 로 구분하여 여러개를 입력할 수 있습니다. 예시) 라지,미디움,스몰<br><strong>옵션명과 옵션항목에 따옴표(\', ")는 입력할 수 없습니다.</strong>'); ?>
          <table>
          <caption>상품선택옵션 입력</caption>
          <colgroup>
            <col class="grid_4">
            <col>
          </colgroup>
          <tbody>
          <tr>
            <th scope="row">
              <label for="opt1_subject">옵션1</label>
              <input type="text" name="opt1_subject" value="<?php echo $opt_subject[0]; ?>" id="opt1_subject" class="frm_input" size="15">
            </th>
            <td>
              <label for="opt1"><b>옵션1 항목</b></label>
              <input type="text" name="opt1" value="" id="opt1" class="frm_input" size="50">
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="opt2_subject">옵션2</label>
              <input type="text" name="opt2_subject" value="<?php echo $opt_subject[1]; ?>" id="opt2_subject" class="frm_input" size="15">
            </th>
            <td>
              <label for="opt2"><b>옵션2 항목</b></label>
              <input type="text" name="opt2" value="" id="opt2" class="frm_input" size="50">
            </td>
          </tr>
           <tr>
            <th scope="row">
              <label for="opt3_subject">옵션3</label>
              <input type="text" name="opt3_subject" value="<?php echo $opt_subject[2]; ?>" id="opt3_subject" class="frm_input" size="15">
            </th>
            <td>
              <label for="opt3"><b>옵션3 항목</b></label>
              <input type="text" name="opt3" value="" id="opt3" class="frm_input" size="50">
            </td>
          </tr>
          </tbody>
          </table>
          <div class="btn_confirm02 btn_confirm">
            <button type="button" id="option_table_create" class="btn_frmline">옵션목록생성</button>
          </div>
        </div>
        <div id="sit_option_frm"><?php include_once(G5_ADMIN_PATH.'/shop_admin/itemoption.php'); ?></div>

        <script>
        $(function() {
          <?php if($it['it_id'] && $po_run) { ?>
          //옵션항목설정
          var arr_opt1 = new Array();
          var arr_opt2 = new Array();
          var arr_opt3 = new Array();
          var opt1 = opt2 = opt3 = '';
          var opt_val;

          $(".opt-cell").each(function() {
            opt_val = $(this).text().split(" > ");
            opt1 = opt_val[0];
            opt2 = opt_val[1];
            opt3 = opt_val[2];

            if(opt1 && $.inArray(opt1, arr_opt1) == -1)
              arr_opt1.push(opt1);

            if(opt2 && $.inArray(opt2, arr_opt2) == -1)
              arr_opt2.push(opt2);

            if(opt3 && $.inArray(opt3, arr_opt3) == -1)
              arr_opt3.push(opt3);
          });


          $("input[name=opt1]").val(arr_opt1.join());
          $("input[name=opt2]").val(arr_opt2.join());
          $("input[name=opt3]").val(arr_opt3.join());
          <?php } ?>
          // 옵션목록생성
          $("#option_table_create").click(function() {
            var it_id = $.trim($("input[name=it_id]").val());
            var opt1_subject = $.trim($("#opt1_subject").val());
            var opt2_subject = $.trim($("#opt2_subject").val());
            var opt3_subject = $.trim($("#opt3_subject").val());
            var opt1 = $.trim($("#opt1").val());
            var opt2 = $.trim($("#opt2").val());
            var opt3 = $.trim($("#opt3").val());
            var $option_table = $("#sit_option_frm");

            if(!opt1_subject || !opt1) {
              alert("옵션명과 옵션항목을 입력해 주십시오.");
              return false;
            }

            $.post(
              "./itemoption.php",
              { it_id: it_id, w: "<?php echo $w; ?>", opt1_subject: opt1_subject, opt2_subject: opt2_subject, opt3_subject: opt3_subject, opt1: opt1, opt2: opt2, opt3: opt3 },
              function(data) {
                $option_table.empty().html(data);
              }
            );
          });

          // 모두선택
          $(document).on("click", "input[name=opt_chk_all]", function() {
            if($(this).is(":checked")) {
              $("input[name='opt_chk[]']").attr("checked", true);
            } else {
              $("input[name='opt_chk[]']").attr("checked", false);
            }
          });

          // 선택삭제
          $(document).on("click", "#sel_option_delete", function() {
            var $el = $("input[name='opt_chk[]']:checked");
            if($el.size() < 1) {
              alert("삭제하려는 옵션을 하나 이상 선택해 주십시오.");
              return false;
            }

            $el.closest("tr").each(function(index, item) {
              if (!$(item).hasClass('new') && $(item).find('input[name="opt_stock_qty[]"]').val() > 0) {
                var optionName = $(item).find('.opt-cell').text();
                alert(optionName + '는 재고를 보유하고 있습니다. 삭제를 원하시면 재고를 0으로 소진 후 삭제해주세요.');
                return false;
              } else {
                $(item).remove();
              }
            });
          });

          // 바코드 8자리
          $(document).on("click", "input[name='opt_use_short_barcode[]']", function() {
            if($(this).is(":checked")) {
              $(this).val(1);
            } else {
              $(this).val(0);
            }
          });

          // 일괄적용
                    $(document).on("click", "#opt_value_apply", function() {
            if($(".opt_com_chk:checked").size() < 1) {
              alert("일괄 수정할 항목을 하나이상 체크해 주십시오.");
              return false;
            }

            var opt_price = $.trim($("#opt_com_price").val());
            var opt_stock = $.trim($("#opt_com_stock").val());
            var opt_noti = $.trim($("#opt_com_noti").val());
            var opt_use = $("#opt_com_use").val();
            var $el = $("input[name='opt_chk[]']:checked");

            // 체크된 옵션이 있으면 체크된 것만 적용
            if($el.size() > 0) {
              var $tr;
              $el.each(function() {
                $tr = $(this).closest("tr");

                if($("#opt_com_price_chk").is(":checked"))
                  $tr.find("input[name='opt_price[]']").val(opt_price);

                if($("#opt_com_stock_chk").is(":checked"))
                  $tr.find("input[name='opt_stock_qty[]']").val(opt_stock);

                if($("#opt_com_noti_chk").is(":checked"))
                  $tr.find("input[name='opt_noti_qty[]']").val(opt_noti);

                if($("#opt_com_use_chk").is(":checked"))
                  $tr.find("select[name='opt_use[]']").val(opt_use);
              });
            } else {
              if($("#opt_com_price_chk").is(":checked"))
                $("input[name='opt_price[]']").val(opt_price);

              if($("#opt_com_stock_chk").is(":checked"))
                $("input[name='opt_stock_qty[]']").val(opt_stock);

              if($("#opt_com_noti_chk").is(":checked"))
                $("input[name='opt_noti_qty[]']").val(opt_noti);

              if($("#opt_com_use_chk").is(":checked"))
                $("select[name='opt_use[]']").val(opt_use);
            }
          });

          $('#partner_btn').on("click", function() {
            var it_price = $('#it_price').val();
            var percent = $('#it_price_partner_percent').val();

            var price = it_price - (it_price * ( percent / 100 ));
            price = Math.round(price/1000) * 1000; // 100원단위 절사(반올림)

            $('#it_price_partner').val(price);
          });

          $('#dealer_btn').on("click", function() {
            var it_price = $('#it_price').val();
            var percent = $('#it_price_dealer_percent').val();

            var price = (it_price - (it_price * ( percent / 100 )) ) / 1.1;
            price = Math.round(price/1000) * 1000;
            price = Math.floor(price * 1.1);

            $('#it_price_dealer').val(price);
          });

          $('#dealer_btn2').on("click", function() {
            var it_price = $('#it_price').val();
            var percent = $('#it_price_dealer_percent2').val();

            var price = (it_price - (it_price * ( percent / 100 )) ) / 1.1;
            price = Math.round(price/1000) * 1000;
            price = Math.floor(price * 1.1);

            $('#it_price_dealer2').val(price);
          });
        });
        </script>
      </td>
    </tr>
    <?php
      $spl_subject = explode(',', $it['it_supply_subject']);
      $spl_count = count($spl_subject);
    ?>
    <tr>
      <th scope="row">상품추가옵션</th>
      <td>
        <div id="sit_supply_frm" class="sit_option tbl_frm01">
          <?php echo help('옵션항목은 콤마(,) 로 구분하여 여러개를 입력할 수 있습니다. 예시) 라지,미디움,스몰<br><strong>옵션명과 옵션항목에 따옴표(\', ")는 입력할 수 없습니다.</strong>'); ?>
          <table>
          <caption>상품추가옵션 입력</caption>
          <colgroup>
            <col class="grid_4">
            <col>
          </colgroup>
          <tbody>
          <?php
          $i = 0;
          do {
            $seq = $i + 1;
          ?>
          <tr>
            <th scope="row">
              <label for="spl_subject_<?php echo $seq; ?>">추가<?php echo $seq; ?></label>
              <input type="text" name="spl_subject[]" id="spl_subject_<?php echo $seq; ?>" value="<?php echo $spl_subject[$i]; ?>" class="frm_input" size="15">
            </th>
            <td>
              <label for="spl_item_<?php echo $seq; ?>"><b>추가<?php echo $seq; ?> 항목</b></label>
              <input type="text" name="spl[]" id="spl_item_<?php echo $seq; ?>" value="" class="frm_input" size="40">
              <?php
              if($i > 0)
                echo '<button type="button" id="del_supply_row" class="btn_frmline">삭제</button>';
              ?>
            </td>
          </tr>
          <?php
            $i++;
          } while($i < $spl_count);
          ?>
          </tbody>
          </table>
          <div id="sit_option_addfrm_btn"><button type="button" id="add_supply_row" class="btn_frmline">옵션추가</button></div>
          <div class="btn_confirm02 btn_confirm">
            <button type="button" id="supply_table_create">옵션목록생성</button>
          </div>
        </div>
        <div id="sit_option_addfrm"><?php include_once(G5_ADMIN_PATH.'/shop_admin/itemsupply.php'); ?></div>

        <script>
        $(function() {
          <?php if($it['it_id'] && $ps_run) { ?>
          // 추가옵션의 항목 설정
          var arr_subj = new Array();
          var subj, spl;

          $("input[name='spl_subject[]']").each(function() {
            subj = $.trim($(this).val());
            if(subj && $.inArray(subj, arr_subj) == -1)
              arr_subj.push(subj);
          });

          for(i=0; i<arr_subj.length; i++) {
            var arr_spl = new Array();
            $(".spl-subject-cell").each(function(index) {
              subj = $(this).text();
              if(subj == arr_subj[i]) {
                spl = $(".spl-cell:eq("+index+")").text();
                arr_spl.push(spl);
              }
            });

            $("input[name='spl[]']:eq("+i+")").val(arr_spl.join());
          }
          <?php } ?>
          // 입력필드추가
          $("#add_supply_row").click(function() {
            var $el = $("#sit_supply_frm tr:last");
            var fld = "<tr>\n";
            fld += "<th scope=\"row\">\n";
            fld += "<label for=\"\">추가</label>\n";
            fld += "<input type=\"text\" name=\"spl_subject[]\" value=\"\" class=\"frm_input\" size=\"15\">\n";
            fld += "</th>\n";
            fld += "<td>\n";
            fld += "<label for=\"\"><b>추가 항목</b></label>\n";
            fld += "<input type=\"text\" name=\"spl[]\" value=\"\" class=\"frm_input\" size=\"40\">\n";
            fld += "<button type=\"button\" id=\"del_supply_row\" class=\"btn_frmline\">삭제</button>\n";
            fld += "</td>\n";
            fld += "</tr>";

            $el.after(fld);

            supply_sequence();
          });

          // 입력필드삭제
                    $(document).on("click", "#del_supply_row", function() {
            $(this).closest("tr").remove();

            supply_sequence();
          });

          // 옵션목록생성
          $("#supply_table_create").click(function() {
            var it_id = $.trim($("input[name=it_id]").val());
            var subject = new Array();
            var supply = new Array();
            var subj, spl;
            var count = 0;
            var $el_subj = $("input[name='spl_subject[]']");
            var $el_spl = $("input[name='spl[]']");
            var $supply_table = $("#sit_option_addfrm");

            $el_subj.each(function(index) {
              subj = $.trim($(this).val());
              spl = $.trim($el_spl.eq(index).val());

              if(subj && spl) {
                subject.push(subj);
                supply.push(spl);
                count++;
              }
            });

            if(!count) {
              alert("추가옵션명과 추가옵션항목을 입력해 주십시오.");
              return false;
            }

            $.post(
              "./itemsupply.php",
              { it_id: it_id, w: "<?php echo $w; ?>", 'subject[]': subject, 'supply[]': supply },
              function(data) {
                $supply_table.empty().html(data);
              }
            );
          });

          // 모두선택
          $(document).on("click", "input[name=spl_chk_all]", function() {
            if($(this).is(":checked")) {
              $("input[name='spl_chk[]']").attr("checked", true);
            } else {
              $("input[name='spl_chk[]']").attr("checked", false);
            }
          });

          // 선택삭제
          $(document).on("click", "#sel_supply_delete", function() {
            var $el = $("input[name='spl_chk[]']:checked");
            if($el.size() < 1) {
              alert("삭제하려는 옵션을 하나 이상 선택해 주십시오.");
              return false;
            }

            $el.closest("tr").each(function(index, item) {
              if (!$(item).hasClass('new') && $(item).find('input[name="spl_stock_qty[]"]').val() > 0) {
                var supplyName = $(item).find('.spl-cell').text();
                alert(supplyName + '는 재고를 보유하고 있습니다. 삭제를 원하시면 재고를 0으로 소진 후 삭제해주세요.');
                return false;
              } else {
                $(item).remove();
              }
            });
          });

          // 일괄적용
          $(document).on("click", "#spl_value_apply", function() {
            if($(".spl_com_chk:checked").size() < 1) {
              alert("일괄 수정할 항목을 하나이상 체크해 주십시오.");
              return false;
            }

            var spl_price = $.trim($("#spl_com_price").val());
            var spl_stock = $.trim($("#spl_com_stock").val());
            var spl_noti = $.trim($("#spl_com_noti").val());
            var spl_use = $("#spl_com_use").val();
            var $el = $("input[name='spl_chk[]']:checked");

            // 체크된 옵션이 있으면 체크된 것만 적용
            if($el.size() > 0) {
              var $tr;
              $el.each(function() {
                $tr = $(this).closest("tr");

                if($("#spl_com_price_chk").is(":checked"))
                  $tr.find("input[name='spl_price[]']").val(spl_price);

                if($("#spl_com_stock_chk").is(":checked"))
                  $tr.find("input[name='spl_stock_qty[]']").val(spl_stock);

                if($("#spl_com_noti_chk").is(":checked"))
                  $tr.find("input[name='spl_noti_qty[]']").val(spl_noti);

                if($("#spl_com_use_chk").is(":checked"))
                  $tr.find("select[name='spl_use[]']").val(spl_use);
              });
            } else {
              if($("#spl_com_price_chk").is(":checked"))
                $("input[name='spl_price[]']").val(spl_price);

              if($("#spl_com_stock_chk").is(":checked"))
                $("input[name='spl_stock_qty[]']").val(spl_stock);

              if($("#spl_com_noti_chk").is(":checked"))
                $("input[name='spl_noti_qty[]']").val(spl_noti);

              if($("#spl_com_use_chk").is(":checked"))
                $("select[name='spl_use[]']").val(spl_use);
            }
          });
                    //지움
          // $(document).on("propertychange change keyup paste input", 'input[name="opt_price[]"]', function() {
          //   var it_price = $(this).val();
          //   var percent = $('#it_price_partner_percent').val();

          //   console.log($('#it_price_partner_percent').val());

          //   var price = it_price - (it_price * ( percent / 100 ));
          //   price = Math.round(price/1000) * 1000; // 100원단위 절사(반올림)

          //   $(this).next().val(price);

          //   percent = $('#it_price_dealer_percent').val();
          //   var price = (it_price - (it_price * ( percent / 100 )) ) / 1.1;
          //   price = Math.round(price/1000) * 1000;
          //   price = Math.floor(price * 1.1);
          //   $(this).next().next().val(price);
          //   $(this).next().next().next().val(price);
          // });
          $(document).on("propertychange change keyup paste input", 'input[name="spl_price[]"]', function() {
            var it_price = $(this).val();
            var percent = $('#it_price_partner_percent').val();

            console.log($('#it_price_partner_percent').val());

            var price = it_price - (it_price * ( percent / 100 ));
            price = Math.round(price/1000) * 1000; // 100원단위 절사(반올림)

            $(this).next().val(price);

            percent = $('#it_price_dealer_percent').val();
            var price = (it_price - (it_price * ( percent / 100 )) ) / 1.1;
            price = Math.round(price/1000) * 1000;
            price = Math.floor(price * 1.1);
            $(this).next().next().val(price);
            $(this).next().next().next().val(price);
          });
        });

        function supply_sequence()
        {
          var $tr = $("#sit_supply_frm tr");
          var seq;
          var th_label, td_label;

          $tr.each(function(index) {
            seq = index + 1;
            $(this).find("th label").attr("for", "spl_subject_"+seq).text("추가"+seq);
            $(this).find("th input").attr("id", "spl_subject_"+seq);
            $(this).find("td label").attr("for", "spl_item_"+seq);
            $(this).find("td label b").text("추가"+seq+" 항목");
            $(this).find("td input").attr("id", "spl_item_"+seq);
          });
        }
        </script>
      </td>
    </tr>
    <tr>
      <th scope="row"><label>상품메모옵션</label></th>
      <td>
        <?php echo help("텍스트 입력 옵션으로 미입력시 출력되지 않습니다. 옵션 항목명을 입력해 주세요."); ?>
        <input type="text" name="pt_msg1" value="<?php echo $it['pt_msg1']; ?>" id="pt_msg1" class="frm_input" size="18">
        <input type="text" name="pt_msg2" value="<?php echo $it['pt_msg2']; ?>" id="pt_msg2" class="frm_input" size="18">
        <input type="text" name="pt_msg3" value="<?php echo $it['pt_msg3']; ?>" id="pt_msg3" class="frm_input" size="18">
      </td>
    </tr>
    </tbody>
    </table>
  </div>
</section>

<section id="anc_sitfrm_sendcost">
  <h2 class="h2_frm">배송비</h2>
  <div class="local_desc02 local_desc">
    <p>쇼핑몰설정 &gt; 배송비유형 설정보다 <strong>개별상품 배송비설정이 우선</strong> 적용됩니다.</p>
  </div>

  <div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>배송비 입력</caption>
    <colgroup>
      <col class="grid_3">
      <col>
    </colgroup>
    <tbody>
      <tr>
        <th scope="row"><label for="it_is_direct_delivery">위탁</label></th>
        <td>
          <?php echo help("체크하시면 로젠택배 EDI전송을 사용하지 않습니다."); ?>
          <label>
            <input type="checkbox" name="it_is_direct_delivery" value="1" <?php echo ($it['it_is_direct_delivery'] == 1) ? "checked" : ""; ?>> 직배송
          </label>
          <select name="it_direct_delivery_partner1">
            <option value="">파트너선택</option>
            <?php
            $partners = get_partner_members();
            foreach($partners as $partner) {
            ?>
            <option value="<?=$partner['mb_id']?>"<?=get_selected($partner['mb_id'], $it['it_direct_delivery_partner'])?>><?=$partner['mb_name']?></option>
            <?php } ?>
          </select>
          1개 <input type="text" name="it_direct_delivery_price1" value="<?=$it['it_direct_delivery_price']?>" class="frm_input" size="8">원 (VAT포함)
          <br>
          <label>
            <input type="checkbox" name="it_is_direct_delivery" value="2" <?php echo ($it['it_is_direct_delivery'] == 2) ? "checked" : ""; ?>> 설치
          </label>
          <select name="it_direct_delivery_partner2">
            <option value="">파트너선택</option>
            <?php
            $partners = get_partner_members();
            foreach($partners as $partner) {
            ?>
            <option value="<?=$partner['mb_id']?>"<?=get_selected($partner['mb_id'], $it['it_direct_delivery_partner'])?>><?=$partner['mb_name']?></option>
            <?php } ?>
          </select>
          1개 <input type="text" name="it_direct_delivery_price2" value="<?=$it['it_direct_delivery_price']?>" class="frm_input" size="8">원 (VAT포함)
          &nbsp;
          <label>
            <input type="checkbox" name="it_is_direct_release_ready" value="1" <?php echo ($it['it_is_direct_release_ready']) ? "checked" : ""; ?>> 주문접수 시 바로 출고준비 단계로 이동
          </label>
        </td>
      </tr>

      <tr>
        <th scope="row"><label for="it_purchase_order_partner">발주업체</label></th>
        <td>
          <select name="it_purchase_order_partner">
            <option value="">파트너선택</option>
            <?php
            $partners = get_partner_members('물품공급');
            foreach ($partners as $partner) {
            ?>
              <option value="<?=$partner['mb_id']?>"<?=get_selected($partner['mb_id'], $it['it_purchase_order_partner'])?>><?=$partner['mb_name']?></option>
            <?php } ?>
          </select>
        </td>
      </tr>

      <tr>
        <th scope="row"><label for="it_default_warehouse">출하창고</label></th>
        <td>
          <?php echo help("기본으로 지정될 출하창고를 설정합니다."); ?>
          <select name="it_default_warehouse" id="it_default_warehouse">
            <?php
            foreach($warehouse_list as $warehouse) {
              echo '<option value="'.$warehouse.'" '.get_selected($it['it_default_warehouse'], $warehouse).'>'.$warehouse.'</option>';
            }
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <!-- <th scope="row"><label for="it_delivery_cnt">배송비 상세조건</label></th> -->
        <th scope="row"><label for="it_delivery_cnt">박스당 개수(쇼핑몰 안내)</label></th>
        <td>
          본 상품은<input type="text" name="it_delivery_cnt" value="<?php echo $it['it_delivery_cnt']; ?>" id="it_delivery_cnt" class="frm_input" size="8">개 주문 시 한 박스로 포장됩니다.

          <input type="hidden" name="it_delivery_price" value="<?php echo $it['it_delivery_price']; ?>" id="it_delivery_price">
          <input type="hidden" name="it_delivery_min_cnt" value="<?php echo $it['it_delivery_min_cnt']; ?>" id="it_delivery_min_cnt">
          <input type="hidden" name="it_delivery_min_price" value="<?php echo $it['it_delivery_min_price']; ?>" id="it_delivery_min_price">
          <input type="hidden" name="it_delivery_company" value="<?php echo $it['it_delivery_company']; ?>" id="it_delivery_company">
          
          <input type="hidden" name="it_delivery_cnt2" value="<?php echo $it['it_delivery_cnt2']; ?>" id="it_delivery_cnt2">
          <input type="hidden" name="it_delivery_price2" value="<?php echo $it['it_delivery_price2']; ?>" id="it_delivery_price2">
          <input type="hidden" name="it_delivery_min_cnt2" value="<?php echo $it['it_delivery_min_cnt2']; ?>" id="it_delivery_min_cnt2">
          <input type="hidden" name="it_delivery_min_price2" value="<?php echo $it['it_delivery_min_price2']; ?>" id="it_delivery_min_price2">
          <input type="hidden" name="it_delivery_company2" value="<?php echo $it['it_delivery_company2']; ?>" id="it_delivery_company2">

          <!--
          <?php echo help("상품의 박스 수량에 따라 배송비가 부과됩니다."); ?>
          1박스 기준 <input type="text" name="it_delivery_cnt" value="<?php echo $it['it_delivery_cnt']; ?>" id="it_delivery_cnt" class="frm_input" size="8">개 마다 배송비 
          <input type="text" name="it_delivery_price" value="<?php echo $it['it_delivery_price']; ?>" id="it_delivery_price" class="frm_input" size="8">원 부과,
          최소수량 <input type="text" name="it_delivery_min_cnt" value="<?php echo $it['it_delivery_min_cnt']; ?>" id="it_delivery_min_cnt" class="frm_input" size="8">개 이하 
          <input type="text" name="it_delivery_min_price" value="<?php echo $it['it_delivery_min_price']; ?>" id="it_delivery_min_price" class="frm_input" size="8">원 부과
          &nbsp;&nbsp;&nbsp;
          택배사 : <select name="it_delivery_company" id="it_delivery_company">
            <option value="ilogen"<?php echo get_selected('ilogen', $it['it_delivery_company']); ?>>로젠택배</option>
            <option value="lotteglogis"<?php echo get_selected('lotteglogis', $it['it_delivery_company']); ?>>롯데택배</option>
          </select>
          <br>
          1박스 기준 <input type="text" name="it_delivery_cnt2" value="<?php echo $it['it_delivery_cnt2']; ?>" id="it_delivery_cnt2" class="frm_input" size="8">개 마다 배송비 
          <input type="text" name="it_delivery_price2" value="<?php echo $it['it_delivery_price2']; ?>" id="it_delivery_price2" class="frm_input" size="8">원 부과,
          최소수량 <input type="text" name="it_delivery_min_cnt2" value="<?php echo $it['it_delivery_min_cnt2']; ?>" id="it_delivery_min_cnt2" class="frm_input" size="8">개 이하 
          <input type="text" name="it_delivery_min_price2" value="<?php echo $it['it_delivery_min_price2']; ?>" id="it_delivery_min_price" class="frm_input" size="8">원 부과
          &nbsp;&nbsp;&nbsp;
          택배사 : <select name="it_delivery_company2" id="it_delivery_company2">
            <option value="ilogen"<?php echo get_selected('ilogen', $it['it_delivery_company2']); ?>>로젠택배</option>
            <option value="lotteglogis"<?php echo get_selected('lotteglogis', $it['it_delivery_company2']); ?>>롯데택배</option>
          </select>
          -->
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="it_sc_type">배송비 유형</label></th>
        <td>
          <?php echo help("배송비 유형을 선택하면 자동으로 항목이 변환됩니다."); ?>
          <select name="it_sc_type" id="it_sc_type">
            <option value="0"<?php echo get_selected('0', $it['it_sc_type']); ?>>쇼핑몰 기본설정 사용</option>
            <option value="1"<?php echo get_selected('1', $it['it_sc_type']); ?>>무료배송</option>
            <option value="2"<?php echo get_selected('2', $it['it_sc_type']); ?>>조건부 무료배송</option>
            <option value="3"<?php echo get_selected('3', $it['it_sc_type']); ?>>유료배송</option>
            <option value="4"<?php echo get_selected('4', $it['it_sc_type']); ?>>수량별 부과</option>
            <option value="5"<?php echo get_selected('5', $it['it_sc_type']); ?>>홀수/짝수 배송</option>
          </select>
          <div id="even_odd_wr">
            <label class="radio-inline">
              <input type="radio" name="it_even_odd" id="it_even_odd_0" value="0" <?php echo get_checked($it['it_even_odd'], 0); ?>> 홀수
            </label>
            <label class="radio-inline">
              <input type="radio" name="it_even_odd" id="it_even_odd_1" value="1" <?php echo get_checked($it['it_even_odd'], 1); ?>> 짝수
            </label>
            <input type="text" name="it_even_odd_price" value="<?php echo $it['it_even_odd_price']; ?>" id="it_even_odd_price" class="frm_input" size="8">원
          </div>
        </td>
      </tr>
      <tr id="sc_con_method">
        <th scope="row"><label for="it_sc_method">배송비 결제</label></th>
        <td>
          <select name="it_sc_method" id="it_sc_method">
            <option value="0"<?php echo get_selected('0', $it['it_sc_method']); ?>>선불</option>
            <option value="1"<?php echo get_selected('1', $it['it_sc_method']); ?>>착불</option>
            <option value="2"<?php echo get_selected('2', $it['it_sc_method']); ?>>사용자선택</option>
          </select>
        </td>
      </tr>
      <tr id="sc_con_basic">
        <th scope="row"><label for="it_sc_price">기본배송비</label></th>
        <td>
          <?php echo help("무료배송 이외의 설정에 적용되는 배송비 금액입니다."); ?>
          <input type="text" name="it_sc_price" value="<?php echo $it['it_sc_price']; ?>" id="it_sc_price" class="frm_input" size="8"> 원
        </td>
      </tr>
      <tr id="sc_con_minimum">
        <th scope="row"><label for="it_sc_minimum">배송비 상세조건</label></th>
        <td>
          주문금액 <input type="text" name="it_sc_minimum" value="<?php echo $it['it_sc_minimum']; ?>" id="it_sc_minimum" class="frm_input" size="8"> 이상 무료 배송
        </td>
      </tr>
      <tr id="sc_con_qty">
        <th scope="row"><label for="it_sc_qty">배송비 상세조건</label></th>
        <td>
          <?php echo help("상품의 주문 수량에 따라 배송비가 부과됩니다. 예를 들어 기본배송비가 3,000원 수량을 3으로 설정했을 경우 상품의 주문수량이 5개이면 6,000원 배송비가 부과됩니다."); ?>
          주문수량 <input type="text" name="it_sc_qty" value="<?php echo $it['it_sc_qty']; ?>" id="it_sc_qty" class="frm_input" size="8"> 마다 배송비 부과
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="it_sc_add_sendcost">산간지역 추가 배송비</label></th>
        <td>
          <?php echo help("도서산간지역 배송 요청 시 배송비용 추가 금액('-1'입력시 쇼핑몰에 설정된 기본 금액으로 설정됩니다)"); ?>
          <input type="text" name="it_sc_add_sendcost" value="<?php echo $w == 'u' ? $it['it_sc_add_sendcost'] : '-1'; ?>" id="it_sc_add_sendcost" class="frm_input" size="8">원
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="it_box_size_width">박스 규격</label></th>
        <td>
          <?php
          echo help("합포장 자동계산이 필요한 경우 규격을 입력해주세요. 미 입력 시 계산에 포함되지 않습니다.");
          $it_box_size = $w == 'u' ? explode(chr(30), $it['it_box_size']) : [];
          ?>
          <label for="it_box_size_width">가로:</label>
          <input type="text" name="it_box_size_width" value="<?php echo $it_box_size[0] ?? ''; ?>" id="it_box_size_width" class="frm_input" size="5">
          <label for="it_box_size_length">세로:</label>
          <input type="text" name="it_box_size_length" value="<?php echo $it_box_size[1] ?? ''; ?>" id="it_box_size_length" class="frm_input" size="5">
          <label for="it_box_size_height">높이:</label>
          <input type="text" name="it_box_size_height" value="<?php echo $it_box_size[2] ?? ''; ?>" id="it_box_size_height" class="frm_input" size="5">
        </td>
      </tr>
    </tbody>
    </table>
  </div>

  <h2 class="h2_frm">파트너배송비</h2>

  <div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>파트너 배송비 입력</caption>
    <colgroup>
      <col class="grid_3">
      <col>
    </colgroup>
    <tbody>
      <tr>
        <th scope="row"><label for="it_sc_type_partner">파트너 배송비 유형</label></th>
        <td>
          <?php echo help("배송비 유형을 선택하면 자동으로 항목이 변환됩니다."); ?>
          <select name="it_sc_type_partner" id="it_sc_type_partner">
            <option value="0"<?php echo get_selected('0', $it['it_sc_type_partner']); ?>>쇼핑몰 기본설정 사용</option>
            <option value="1"<?php echo get_selected('1', $it['it_sc_type_partner']); ?>>무료배송</option>
            <option value="2"<?php echo get_selected('2', $it['it_sc_type_partner']); ?>>조건부 무료배송</option>
            <option value="3"<?php echo get_selected('3', $it['it_sc_type_partner']); ?>>유료배송</option>
            <option value="4"<?php echo get_selected('4', $it['it_sc_type_partner']); ?>>수량별 부과</option>
          </select>
        </td>
      </tr>
      <tr id="sc_con_method_partner">
        <th scope="row"><label for="it_sc_method_partner">파트너 배송비 결제</label></th>
        <td>
          <select name="it_sc_method_partner" id="it_sc_method_partner">
            <option value="0"<?php echo get_selected('0', $it['it_sc_method_partner']); ?>>선불</option>
            <option value="1"<?php echo get_selected('1', $it['it_sc_method_partner']); ?>>착불</option>
            <option value="2"<?php echo get_selected('2', $it['it_sc_method_partner']); ?>>사용자선택</option>
          </select>
        </td>
      </tr>
      <tr id="sc_con_basic_partner">
        <th scope="row"><label for="it_sc_price_partner">기본배송비</label></th>
        <td>
          <?php echo help("무료배송 이외의 설정에 적용되는 배송비 금액입니다."); ?>
          <input type="text" name="it_sc_price_partner" value="<?php echo $it['it_sc_price_partner']; ?>" id="it_sc_price_partner" class="frm_input" size="8"> 원
        </td>
      </tr>
      <tr id="sc_con_minimum_partner">
        <th scope="row"><label for="it_sc_minimum_partner">배송비 상세조건</label></th>
        <td>
          주문금액 <input type="text" name="it_sc_minimum_partner" value="<?php echo $it['it_sc_minimum_partner']; ?>" id="it_sc_minimum_partner" class="frm_input" size="8"> 이상 무료 배송
        </td>
      </tr>
      <tr id="sc_con_qty_partner">
        <th scope="row"><label for="it_sc_qty_partner">배송비 상세조건</label></th>
        <td>
          <?php echo help("상품의 주문 수량에 따라 배송비가 부과됩니다. 예를 들어 기본배송비가 3,000원 수량을 3으로 설정했을 경우 상품의 주문수량이 5개이면 6,000원 배송비가 부과됩니다."); ?>
          주문수량 <input type="text" name="it_sc_qty_partner" value="<?php echo $it['it_sc_qty_partner']; ?>" id="it_sc_qty_partner" class="frm_input" size="8"> 마다 배송비 부과
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="it_sc_add_sendcost_partner">파트너회원 산간지역 추가 배송비</label></th>
        <td>
          <?php echo help("도서산간지역 배송 요청 시 배송비용 추가 금액('-1'입력시 쇼핑몰에 설정된 기본 금액으로 설정됩니다)"); ?>
          <input type="text" name="it_sc_add_sendcost_partner" value="<?php echo $w == 'u' ? $it['it_sc_add_sendcost_partner'] : '-1'; ?>" id="it_sc_add_sendcost_partner" class="frm_input" size="8">원
        </td>
      </tr>
    </tbody>
    </table>
  </div>

  <script>
  $(function() {
    <?php
    switch($it['it_sc_type']) {
      case 1:
        echo '$("#sc_con_method").hide();'.PHP_EOL;
        echo '$("#sc_con_basic").hide();'.PHP_EOL;
        echo '$("#sc_con_minimum").hide();'.PHP_EOL;
        echo '$("#sc_con_qty").hide();'.PHP_EOL;
        echo '$("#sc_grp").attr("rowspan","1");'.PHP_EOL;
        echo '$("#even_odd_wr").hide();'.PHP_EOL;
        break;
      case 2:
        echo '$("#sc_con_method").show();'.PHP_EOL;
        echo '$("#sc_con_basic").show();'.PHP_EOL;
        echo '$("#sc_con_minimum").show();'.PHP_EOL;
        echo '$("#sc_con_qty").hide();'.PHP_EOL;
        echo '$("#sc_grp").attr("rowspan","4");'.PHP_EOL;
        echo '$("#even_odd_wr").hide();'.PHP_EOL;
        break;
      case 3:
        echo '$("#sc_con_method").show();'.PHP_EOL;
        echo '$("#sc_con_basic").show();'.PHP_EOL;
        echo '$("#sc_con_minimum").hide();'.PHP_EOL;
        echo '$("#sc_con_qty").hide();'.PHP_EOL;
        echo '$("#sc_grp").attr("rowspan","3");'.PHP_EOL;
        echo '$("#even_odd_wr").hide();'.PHP_EOL;
        break;
      case 4:
        echo '$("#sc_con_method").show();'.PHP_EOL;
        echo '$("#sc_con_basic").show();'.PHP_EOL;
        echo '$("#sc_con_minimum").hide();'.PHP_EOL;
        echo '$("#sc_con_qty").show();'.PHP_EOL;
        echo '$("#sc_grp").attr("rowspan","4");'.PHP_EOL;
        echo '$("#even_odd_wr").hide();'.PHP_EOL;
        break;
      case 5:
        echo '$("#sc_con_method").hide();'.PHP_EOL;
        echo '$("#sc_con_basic").hide();'.PHP_EOL;
        echo '$("#sc_con_minimum").hide();'.PHP_EOL;
        echo '$("#sc_con_qty").hide();'.PHP_EOL;
        echo '$("#sc_grp").attr("rowspan","2");'.PHP_EOL;
        echo '$("#even_odd_wr").show();'.PHP_EOL;
        break;
      default:
        echo '$("#sc_con_method").hide();'.PHP_EOL;
        echo '$("#sc_con_basic").hide();'.PHP_EOL;
        echo '$("#sc_con_minimum").hide();'.PHP_EOL;
        echo '$("#sc_con_qty").hide();'.PHP_EOL;
        echo '$("#sc_grp").attr("rowspan","2");'.PHP_EOL;
        echo '$("#even_odd_wr").hide();'.PHP_EOL;
        break;
    }
    switch($it['it_sc_type_partner']) {
      case 1:
        echo '$("#sc_con_method_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_basic_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_minimum_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_qty_partner").hide();'.PHP_EOL;
        echo '$("#sc_grp_partner").attr("rowspan","1");'.PHP_EOL;
        break;
      case 2:
        echo '$("#sc_con_method_partner").show();'.PHP_EOL;
        echo '$("#sc_con_basic_partner").show();'.PHP_EOL;
        echo '$("#sc_con_minimum_partner").show();'.PHP_EOL;
        echo '$("#sc_con_qty_partner").hide();'.PHP_EOL;
        echo '$("#sc_grp_partner").attr("rowspan","4");'.PHP_EOL;
        break;
      case 3:
        echo '$("#sc_con_method_partner").show();'.PHP_EOL;
        echo '$("#sc_con_basic_partner").show();'.PHP_EOL;
        echo '$("#sc_con_minimum_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_qty_partner").hide();'.PHP_EOL;
        echo '$("#sc_grp_partner").attr("rowspan","3");'.PHP_EOL;
        break;
      case 4:
        echo '$("#sc_con_method_partner").show();'.PHP_EOL;
        echo '$("#sc_con_basic_partner").show();'.PHP_EOL;
        echo '$("#sc_con_minimum_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_qty_partner").show();'.PHP_EOL;
        echo '$("#sc_grp_partner").attr("rowspan","4");'.PHP_EOL;
        break;
      default:
        echo '$("#sc_con_method_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_basic_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_minimum_partner").hide();'.PHP_EOL;
        echo '$("#sc_con_qty_partner").hide();'.PHP_EOL;
        echo '$("#sc_grp_partner").attr("rowspan","2");'.PHP_EOL;
        break;
    }
    ?>
    $("#it_sc_type").change(function() {
      var type = $(this).val();

      switch(type) {
        case "1":
          $("#sc_con_method").hide();
          $("#sc_con_basic").hide();
          $("#sc_con_minimum").hide();
          $("#sc_con_qty").hide();
          $("#sc_grp").attr("rowspan","1");
          $("#even_odd_wr").hide();
          break;
        case "2":
          $("#sc_con_method").show();
          $("#sc_con_basic").show();
          $("#sc_con_minimum").show();
          $("#sc_con_qty").hide();
          $("#sc_grp").attr("rowspan","4");
          $("#even_odd_wr").hide();
          break;
        case "3":
          $("#sc_con_method").show();
          $("#sc_con_basic").show();
          $("#sc_con_minimum").hide();
          $("#sc_con_qty").hide();
          $("#sc_grp").attr("rowspan","3");
          $("#even_odd_wr").hide();
          break;
        case "4":
          $("#sc_con_method").show();
          $("#sc_con_basic").show();
          $("#sc_con_minimum").hide();
          $("#sc_con_qty").show();
          $("#sc_grp").attr("rowspan","4");
          $("#even_odd_wr").hide();
          break;
        case "5":
          $("#sc_con_method").hide();
          $("#sc_con_basic").hide();
          $("#sc_con_minimum").hide();
          $("#sc_con_qty").hide();
          $("#sc_grp").attr("rowspan","4");
          $("#even_odd_wr").show();
          break;
        default:
          $("#sc_con_method").hide();
          $("#sc_con_basic").hide();
          $("#sc_con_minimum").hide();
          $("#sc_con_qty").hide();
          $("#sc_grp").attr("rowspan","1");
          $("#even_odd_wr").hide();
          break;
      }
    });

    $("#it_sc_type_partner").change(function() {
      var type = $(this).val();

      switch(type) {
        case "1":
          $("#sc_con_method_partner").hide();
          $("#sc_con_basic_partner").hide();
          $("#sc_con_minimum_partner").hide();
          $("#sc_con_qty_partner").hide();
          $("#sc_grp_partner").attr("rowspan","1");
          break;
        case "2":
          $("#sc_con_method_partner").show();
          $("#sc_con_basic_partner").show();
          $("#sc_con_minimum_partner").show();
          $("#sc_con_qty_partner").hide();
          $("#sc_grp_partner").attr("rowspan","4");
          break;
        case "3":
          $("#sc_con_method_partner").show();
          $("#sc_con_basic_partner").show();
          $("#sc_con_minimum_partner").hide();
          $("#sc_con_qty_partner").hide();
          $("#sc_grp_partner").attr("rowspan","3");
          break;
        case "4":
          $("#sc_con_method_partner").show();
          $("#sc_con_basic_partner").show();
          $("#sc_con_minimum_partner").hide();
          $("#sc_con_qty_partner").show();
          $("#sc_grp_partner").attr("rowspan","4");
          break;
        default:
          $("#sc_con_method_partner").hide();
          $("#sc_con_basic_partner").hide();
          $("#sc_con_minimum_partner").hide();
          $("#sc_con_qty_partner").hide();
          $("#sc_grp_partner").attr("rowspan","1");
          break;
      }
    });
  });
  </script>
</section>

<?php if($is_admin == 'super') { //최고관리자만 가능 ?>
<section id="anc_sitfrm_commission">
  <h2 class="h2_frm">판매수수료/인센티브</h2>

  <div class="local_desc02 local_desc">
    <p>파트너 기본설정 또는 파트너별 설정보다 <strong>상품의 개별설정이 우선</strong> 적용됩니다.</p>
  </div>

  <div class="tbl_frm01 tbl_wrap">
    <table>
      <caption>판매수수료/인센티브 입력</caption>
      <colgroup>
        <col class="grid_3">
        <col>
      </colgroup>
      <tbody>
        <tr id="sc_con_basic">
          <th scope="row"><label for="it_sc_price">판매수수료</label></th>
          <td>
            <?php echo help("판매금액에서 차감되는 수수료율입니다. 0 입력시 기본 또는 파트너별 수수료가 자동적용됩니다."); ?>
            <input type="text" name="pt_commission" value="<?php echo $it['pt_commission']; ?>" id="pt_commission" class="frm_input" size="4"> %
          </td>
        </tr>
        <tr id="sc_con_basic">
          <th scope="row"><label for="it_sc_price">인센티브</label></th>
          <td>
            <?php echo help("정산금액에 추가되는 인센티브율입니다. 0 입력시 기본 또는 파트너별 인센티브가 자동적용됩니다."); ?>
            <input type="text" name="pt_incentive" value="<?php echo $it['pt_incentive']; ?>" id="pt_incentive" class="frm_input" size="4"> %
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>
<?php } ?>

<?php echo $frm_submit; ?>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_img" class="anc-section">
  <h2 class="h2_frm">이미지</h2>
  <div class="local_desc02 local_desc">
    {이미지:1}, {이미지:2} 와 같이 이미지 번호를 입력하면 해당 첨부이미지를 내용에 출력할 수 있습니다.
  </div>

  <div class="tbl_frm01 tbl_wrap tbl_img_frm">
    <table>
      <caption>이미지 업로드</caption>
      <colgroup>
        <col class="grid_3">
        <col>
      </colgroup>
      <tbody>
      <?php for($i=1; $i<=10; $i++) { ?>
      <tr>
        <th scope="row"><label for="it_img1">이미지 <?php echo $i; ?></label></th>
        <td>
          <input type="file" name="it_img<?php echo $i; ?>" id="it_img<?php echo $i; ?>">
          <?php
          $it_img = G5_DATA_PATH.'/item/'.$it['it_img'.$i];
          if(is_file($it_img) && $it['it_img'.$i]) {
            $size = @getimagesize($it_img);
            $thumb = get_it_thumbnail($it['it_img'.$i], 25, 25);
          ?>
          <label for="it_img<?php echo $i; ?>_del"><span class="sound_only">이미지 <?php echo $i; ?> </span>파일삭제</label>
          <input type="checkbox" name="it_img<?php echo $i; ?>_del" id="it_img<?php echo $i; ?>_del" value="1">
          <span class="sit_wimg_limg<?php echo $i; ?>"><?php echo $thumb; ?></span>
          <div id="limg<?php echo $i; ?>" class="banner_or_img">
            <img src="<?php echo G5_DATA_URL; ?>/item/<?php echo $it['it_img'.$i]; ?>" alt="" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>">
            <button type="button" class="sit_wimg_close">닫기</button>
          </div>
          <script>
          $('<button type="button" id="it_limg<?php echo $i; ?>_view" class="btn_frmline sit_wimg_view">이미지<?php echo $i; ?> 확인</button>').appendTo('.sit_wimg_limg<?php echo $i; ?>');
          </script>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
      <tr>
        <th scope="row"><label for="it_img_3d">3D 이미지</label></th>
        <td>
          <input type="file" name="it_img_3d[]" id="it_img_3d" multiple>
          <?php if($it['it_img_3d']) { ?>
          <label for="it_img<?php echo $i; ?>_del"><span class="sound_only">이미지 <?php echo $i; ?> </span>파일삭제</label>
          <input type="checkbox" name="it_img_3d_del" id="it_img_3d_del" value="1">
          <?php } ?>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</section>

<?php echo $frm_submit; ?>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_relation" class="srel anc-section">
  <h2 class="h2_frm">관련상품</h2>
  <div class="local_desc02 local_desc">
    <p>
      등록된 전체상품 목록에서 분류를 선택하면 해당 상품 리스트가 연이어 나타납니다.<br>
      상품 리스트에서 관련상품으로 추가하시면 선택된 관련상품 목록에 <strong>함께</strong> 추가됩니다.<br>
      예를 들어, A 상품에 B 상품을 관련상품으로 등록하면 B 상품에도 A 상품이 관련상품으로 자동 추가되며, <strong>확인 버튼을 누르셔야 정상 반영됩니다.</strong>
    </p>
  </div>

  <div class="compare_wrap">
    <section class="compare_left">
      <h3>등록된 전체상품 목록</h3>
      <label for="sch_relation" class="sound_only">분류</label>
      <span class="srel_pad">
        <select id="sch_relation">
          <option value=''>분류별 상품</option>
          <?php echo apms_it_category($is_auth, $is_partner);?>
        </select>
        <label for="sch_name" class="sound_only">상품명</label>
        <input type="text" name="sch_name" id="sch_name" class="frm_input" size="15">
        <button type="button" id="btn_search_item" class="btn_frmline">검색</button>
      </span>
      <div id="relation" class="srel_list">
        <p>분류를 선택하거나 상품명 입력 후 검색해 주세요.</p>
      </div>
      <script>
        $(function() {
          $("#btn_search_item").click(function() {
            var ca_id = $("#sch_relation").val();
            var it_name = $.trim($("#sch_name").val());
            var $relation = $("#relation");

            if(ca_id == "" && it_name == "") {
              $relation.html("<p>분류를 선택하거나 상품명 입력 후 검색해 주세요.</p>");
              return false;
            }

            $("#relation").load(
              "./itemformrelation.php",
              { it_id: "<?php echo $it_id; ?>", ca_id: ca_id, it_name: it_name }
            );
          });

          $(document).on("click", "#relation .add_item", function() {
            // 이미 등록된 상품인지 체크
            var $li = $(this).closest("li");
            var it_id = $li.find("input:hidden").val();
            var it_id2;
            var dup = false;
            $("#reg_relation input[name='re_it_id[]']").each(function() {
              it_id2 = $(this).val();
              if(it_id == it_id2) {
                dup = true;
                return false;
              }
            });

            if(dup) {
              alert("이미 선택된 상품입니다.");
              return false;
            }

            var cont = "<li>"+$li.html().replace("add_item", "del_item").replace("추가", "삭제")+"</li>";
            var count = $("#reg_relation li").size();

            if(count > 0) {
              $("#reg_relation li:last").after(cont);
            } else {
              $("#reg_relation").html("<ul>"+cont+"</ul>");
            }

            $li.remove();
          });

          $(document).on("click", "#reg_relation .del_item", function() {
            if(!confirm("관련상품을 삭제하시겠습니까?"))
              return false;

            $(this).closest("li").remove();

            var count = $("#reg_relation li").size();
            if(count < 1)
              $("#reg_relation").html("<p>선택된 상품이 없습니다.</p>");
          });
        });
      </script>
    </section>

    <section class="compare_right">
      <h3>선택된 관련상품 목록</h3>
      <span class="srel_pad"></span>
      <div id="reg_relation" class="srel_sel">
        <?php
        $str = array();
        $sql = "
          select b.ca_id, b.it_id, b.it_name, b.it_price
            from {$g5['g5_shop_item_relation_table']} a
            left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id)
          where a.it_id = '$it_id'
          order by ir_no asc
        ";
        $result = sql_query($sql);
        for($g=0; $row=sql_fetch_array($result); $g++)
        {
          $it_name = get_it_image($row['it_id'], 50, 50).' '.$row['it_name'];

          if($g==0)
            echo '<ul>';
        ?>
          <li>
            <input type="hidden" name="re_it_id[]" value="<?php echo $row['it_id']; ?>">
            <div class="list_item"><?php echo $it_name; ?></div>
            <div class="list_item_btn"><button type="button" class="del_item btn_frmline">삭제</button></div>
          </li>
        <?php
          $str[] = $row['it_id'];
        }
        $str = implode(",", $str);

        if($g > 0)
          echo '</ul>';
        else
          echo '<p>선택된 상품이 없습니다.</p>';
        ?>
      </div>
      <input type="hidden" name="it_list" value="<?php echo $str; ?>">
    </section>

  </div>

</section>

<?php echo $frm_submit; ?>

<?php if($is_auth) { ?>
  <?php echo $pg_anchor; ?>
  <section id="anc_sitfrm_event" class="srel anc-section">
    <h2 class="h2_frm">관련이벤트</h2>
    <div class="compare_wrap">
      <section class="compare_left">
        <h3>등록된 전체이벤트 목록</h3>
        <div id="event_list" class="srel_list srel_noneimg">
          <?php
          $sql = " select ev_id, ev_subject from {$g5['g5_shop_event_table']} order by ev_id desc ";
          $result = sql_query($sql);
          for ($g=0; $row=sql_fetch_array($result); $g++) {
            if($g == 0)
              echo '<ul>';
          ?>
            <li>
              <input type="hidden" name="ev_id[]" value="<?php echo $row['ev_id']; ?>">
              <div class="list_item"><?php echo get_text($row['ev_subject']); ?></div>
              <div class="list_item_btn"><button type="button" class="add_event btn_frmline">추가</button></div>
            </li>
          <?php
          }

          if($g > 0)
            echo '</ul>';
          else
            echo '<p>등록된 이벤트가 없습니다.</p>';
          ?>
        </div>
        <script>
        $(function() {
          $(document).on("click", "#event_list .add_event", function() {
            // 이미 등록된 이벤트인지 체크
            var $li = $(this).closest("li");
            var ev_id = $li.find("input:hidden").val();
            var ev_id2;
            var dup = false;
            $("#reg_event_list input[name='ev_id[]']").each(function() {
              ev_id2 = $(this).val();
              if(ev_id == ev_id2) {
                dup = true;
                return false;
              }
            });

            if(dup) {
              alert("이미 선택된 이벤트입니다.");
              return false;
            }

            var cont = "<li>"+$li.html().replace("add_event", "del_event").replace("추가", "삭제")+"</li>";
            var count = $("#reg_event_list li").size();

            if(count > 0) {
              $("#reg_event_list li:last").after(cont);
            } else {
              $("#reg_event_list").html("<ul>"+cont+"</ul>");
            }
          });

          $(document).on("click", "#reg_event_list .del_event", function() {
            if(!confirm("상품을 삭제하시겠습니까?"))
              return false;

            $(this).closest("li").remove();

            var count = $("#reg_event_list li").size();
            if(count < 1)
              $("#reg_event_list").html("<p>선택된 이벤트가 없습니다.</p>");
          });
        });
        </script>
      </section>

      <section class="compare_right">
        <h3>선택된 관련이벤트 목록</h3>
        <div id="reg_event_list" class="srel_sel srel_noneimg">
          <?php
          $str = "";
          $comma = "";
          $sql = " select b.ev_id, b.ev_subject
                 from {$g5['g5_shop_event_item_table']} a
                 left join {$g5['g5_shop_event_table']} b on (a.ev_id=b.ev_id)
                where a.it_id = '$it_id'
                order by b.ev_id desc ";
          $result = sql_query($sql);
          for ($g=0; $row=sql_fetch_array($result); $g++) {
            $str .= $comma . $row['ev_id'];
            $comma = ",";

            if($g == 0)
              echo '<ul>';
          ?>
            <li>
              <input type="hidden" name="ev_id[]" value="<?php echo $row['ev_id']; ?>">
              <div class="list_item"><?php echo get_text($row['ev_subject']); ?></div>
              <div class="list_item_btn"><button type="button" class="del_event btn_frmline">삭제</button></div>
            </li>
          <?php
          }

          if($g > 0)
            echo '</ul>';
          else
            echo '<p>선택된 이벤트가 없습니다.</p>';
          ?>
        </div>
        <input type="hidden" name="ev_list" value="<?php echo $str; ?>">
      </section>
    </div>

  </section>

  <?php echo $frm_submit; ?>

<?php } ?>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_opt" class="anc-section">
  <h2 class="h2_frm">옵션사항</h2>
  <div class="tbl_frm01 tbl_wrap">
    <table>
      <caption>옵션사항</caption>
      <colgroup>
        <col class="grid_3">
        <col>
      </colgroup>
      <tbody>
        <tr>
          <th scope="row">추가 상품설명</th>
          <td class="helper">
            구매한 회원에게만 출력되는 추가 상품설명입니다.
          </td>
        </tr>
        <tr>
          <td colspan="2" class="iframe">
            <?php echo editor_html('pt_explan', get_text(html_purifier($it['pt_explan']),0)); ?>
          </td>
        </tr>
        <tr>
          <th scope="row">모바일 추가 상품설명</th>
          <td class="helper">
            구매한 회원에게만 출력되는 모바일 추가 상품설명입니다.
          </td>
        </tr>
        <tr>
          <td colspan="2" class="iframe">
            <?php echo editor_html('pt_mobile_explan', get_text(html_purifier($it['pt_mobile_explan']),0)); ?>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="pt_ccl">CCL</label></th>
          <td>
            <select id="pt_ccl" name="pt_ccl">
              <option value="0"<?php echo get_selected('0', $it['pt_ccl']); ?>>표시안함</option>
              <option value="1"<?php echo get_selected('1', $it['pt_ccl']); ?>>저작자표시</option>
              <option value="2"<?php echo get_selected('2', $it['pt_ccl']); ?>>저작자표시-비영리</option>
              <option value="3"<?php echo get_selected('3', $it['pt_ccl']); ?>>저작자표시-변경금지</option>
              <option value="4"<?php echo get_selected('4', $it['pt_ccl']); ?>>저작자표시-동일조건변경허락</option>
              <option value="5"<?php echo get_selected('5', $it['pt_ccl']); ?>>저작자표시-비영리-동일조건변경허락</option>
              <option value="6"<?php echo get_selected('6', $it['pt_ccl']); ?>>저작자표시-비영리-변경금지</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="pt_link1">유튜브링크</label></th>
          <td>
            <?php echo help("상품 리스트에 나오는 유튜브 링크입니다"); ?>
            <input type="text" name="it_youtube_link" value="<?php echo get_text($it['it_youtube_link']); ?>" id="it_youtube_link" class="frm_input sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="pt_link1">관련링크1</label></th>
          <td>
            <?php echo help("각 요소는 바(|)로 구분하며, \"주소|이름|FA아이콘명\" 순으로 입력하시면 됩니다."); ?>
            <input type="text" name="pt_link1" value="<?php echo get_text($it['pt_link1']); ?>" id="pt_link1" class="frm_input sl">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="pt_link2">관련링크2</label></th>
          <td>
            <?php echo help("ex) http://www.youtube.com|유튜브|youtube"); ?>
            <input type="text" name="pt_link2" value="<?php echo get_text($it['pt_link2']); ?>" id="pt_link2" class="frm_input sl">
          </td>
        </tr>
        <?php if($is_auth || $default['pt_review_use']) { ?>
        <tr>
        <th scope="row"><label for="pt_review_use">후기등록</label></th>
          <td>
            <?php echo help("체크하시면 쇼핑몰 설정과 관계없이 후기등록이 가능합니다."); ?>
            <label><input type="checkbox" name="pt_review_use" value="1" id="pt_review_use" <?php echo ($it['pt_review_use']) ? "checked" : ""; ?>> 구매와 상관없이 후기작성이 가능하도록 합니다.</label>
          </td>
        </tr>
        <?php } ?>
        <?php if($is_auth || $default['pt_comment_use']) { ?>
        <tr>
          <th scope="row"><label for="pt_comment_use">댓글등록</label></th>
          <td>
            <select name="pt_comment_use" id="pt_comment_use">
              <option value="1"<?php echo get_selected('1', $it['pt_comment_use']); ?>>모두 등록가능</option>
              <option value="2"<?php echo get_selected('2', $it['pt_comment_use']); ?>>나만 등록가능</option>
              <?php if($is_auth || $default['pt_comment_use'] != "2") { ?>
              <option value="0"<?php echo get_selected('0', $it['pt_comment_use']); ?>>댓글 사용안함</option>
              <?php } ?>
            </select>
          </td>
        </tr>
        <?php } ?>

        <?php if($is_auth || !$is_reserve_none) { ?>
        <?php if($is_auth || $is_reserve) { // 관리자 일 때 ?>
        <tr>
          <th scope="row"><label for="pt_reserve">판매예약</label></th>
          <td>
            <?php if(!$is_auth && $is_reserve_none) { ?>
              <?php if($it['pt_no']) { ?>
                <?php echo date("Y년 m월 d일 H시 i분", $it['pt_no']);?>에 판매되었습니다.
              <?php } else if($it['pt_reserve']) { ?>
                현재 <?php echo date("Y년 m월 d일 H시 i분", $it['pt_reserve']);?>으로 판매예약되어 있습니다.
              <?php } else { ?>
                상품등록 후 <?php echo $default['pt_reserve_none'];?>시간이 지나 예약설정 또는 변경이 불가능합니다.
              <?php } ?>
              <input type="hidden" name="pt_reserve_use" value="<?php echo $it['pt_reserve_use']; ?>">
              <input type="hidden" name="pt_reserve" value="<?php echo $it['pt_reserve']; ?>">
            <?php } else { ?>
              <?php
                if($is_auth) {
                  if($is_reserve) {
                    echo help("현재 ".$default['pt_reserve_day']."일 이내 등록상품을 대상으로 ".$default['pt_reserve_cache']."분 간격으로 예약상품을 체크하고 있습니다.");
                  } else {
                    echo help("판매예약은 테마관리 > 기본설정 > 쇼핑몰 기본 설정에서 예약가능일, 예약체크, 예약대상을 모두 설정해 주셔야 작동합니다. <input type=\"button\" value=\"예약설정하기\"  class=\"btn_frmline\" style=\"cursor:pointer;\" onclick=\"javascript:window.open('/adm/apms_admin/apms.admin.php?ap=thema','pt_reserve','top=10, left=10, width=1300, height=900');\">"); 
                  }
                } else {
                  echo help("등록 후 {$default['pt_reserve_none']}시간이 지나면 예약 설정을 할 수 없습니다.");
                }
              ?>
              <input type="text" id="pt_reserve_date"  name="pt_reserve_date" value="<?php echo $pt_rdate; ?>" class="frm_input" size="10" maxlength="10" readonly>
              <script>
                $(function(){
                  $("#pt_reserve_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-0:c+99", maxDate: "<?php echo $default['pt_reserve_end']?>" });
                });
              </script>
              &nbsp;
              <select name="pt_reserve_hour" id="pt_reserve_hour">
                <?php for($i=0; $i < 24; $i++) { $im = sprintf("%02d", $i); ?>
                  <option value="<?php echo $im;?>"<?php echo get_selected($im, $pt_rhour); ?>><?php echo $im;?>시</option>
                <?php } ?>
              </select>
              &nbsp;
              <select name="pt_reserve_minute" id="pt_reserve_minute">
                <?php for($i=0; $i < 60; $i++) { $im = ($i<10)? "0".$i:$i; ?>
                  <option value="<?php echo $im;?>"<?php echo get_selected((string)$im, (string)$pt_rminute); ?>><?php echo $im;?>분</option>
                <?php } ?>
              </select>
              &nbsp;
              <label><input type="checkbox" name="pt_reserve_use" value="1" id="pt_reserve_use" <?php echo ($it['pt_reserve_use']) ? "checked" : ""; ?>> 예약하기</label>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
        <tr>
          <th scope="row"><label for="pt_end">판매종료</label></th>
          <td>
            <?php if($is_reserve_none) echo help("판매종료는 테마관리 > 기본설정 > 쇼핑몰 기본 설정에서 예약체크가 설정되어 있어야 작동합니다."); ?>
            <input type="text" id="pt_end_date"  name="pt_end_date" value="<?php echo $pt_edate; ?>" class="frm_input" size="10" maxlength="10" readonly>
            <script>
              $(function(){
                $("#pt_end_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-0:c+2" });
              });
            </script>
            &nbsp;
            <select name="pt_end_hour" id="pt_end_hour">
              <?php for($i=0; $i < 24; $i++) { $im = sprintf("%02d", $i); ?>
              <option value="<?php echo $im;?>"<?php echo get_selected($im, $pt_ehour); ?>><?php echo $im;?>시</option>
              <?php } ?>
            </select>
            &nbsp;
            <select name="pt_end_minute" id="pt_end_minute">
              <?php for($i=0; $i < 60; $i++) { $im = ($i<10)? "0".$i:$i; ?>
              <option value="<?php echo $im;?>"<?php echo get_selected((string)$im, (string)$pt_eminute); ?>><?php echo $im;?>분</option>
              <?php } ?>
            </select>
            &nbsp;
            <button type="button" onclick="this.form.pt_end_date.value='';" class="btn_frmline">종료설정해제</button>
          </td>
        </tr>
        <?php } // 예약 끝 ?>
        <?php if($is_auth) { // 관리자일 때만 출력 ?>
        <?php if ($w == "u") { ?>
        <tr>
          <th scope="row">입력일시</th>
          <td colspan="2">
            <?php echo help("상품을 처음 입력(등록)한 시간입니다."); ?>
            <?php echo $it['it_time']; ?>
          </td>
        </tr>
        <tr>
          <th scope="row">수정일시</th>
          <td colspan="2">
            <?php echo help("상품을 최종 수정한 시간입니다."); ?>
            <?php echo $it['it_update_time']; ?>
          </td>
        </tr>
        <?php } ?>
        <?php } // 관리자 끝 ?>
      </tbody>
    </table>
  </div>
</section>

<?php echo $frm_submit; ?>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_attach" class="anc-section">
  <h2 class="h2_frm">파일첨부</h2>
  <div class="tbl_frm01 tbl_wrap">
    <table>
      <caption>첨부파일 업로드</caption>
      <colgroup>
        <col class="grid_3">
        <col>
        <col class="grid_3">
        <col class="grid_3">
        <col class="grid_3">
        <col class="grid_3">
        <col class="grid_3">
      </colgroup>
      <tbody>
        <tr>
          <th>옵션설정안내</th>
          <td colspan="6">
            "<strong>구매</strong>" 체크시 구매한 회원에게만 노출되며, "<strong>다운</strong>" 체크시 다운로드 가능하고, 오디오나 동영상은 "<strong>보기</strong>" 체크시 JWPlayer로 실행할 수 있습니다.
          </td>
        </tr>
        <?php $attach = apms_get_file('item', $it['it_id']); ?>
        <?php for ($i=0; $i < 10; $i++) { ?>
        <tr>
          <td>
            <?php if($attach[$i]['file']) { ?>
            <a href="<?php echo $attach[$i]['href'];?>"><?php echo $attach[$i]['source'];?> (<?php echo $attach[$i]['size'];?>)</a>
            <?php } ?>
          </td>
          <td>
            <input type="file" name="pf_file[]" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능">
          </td>
          <td align="center">
            <?php if($attach[$i]['file']) { ?>
            <label><input type="checkbox" id="pf_file_del<?php echo $i ?>" name="pf_file_del[<?php echo $i;  ?>]" value="1"> 삭제</label>
            <?php } ?>
          </td>
          <td align="center">
            <label><input type="checkbox" id="pf_purchase<?php echo $i ?>" name="pf_purchase[<?php echo $i;  ?>]" value="1"<?php echo ($attach[$i]['purchase_use']) ? ' checked' : '';?>> 구매</label>
          </td>
          <td align="center">
            <label><input type="checkbox" id="pf_download<?php echo $i ?>" name="pf_download[<?php echo $i;  ?>]" value="1"<?php echo ($attach[$i]['download_use']) ? ' checked' : '';?>> 다운</label>
          </td>
          <td align="center">
            <label><input type="checkbox" id="pf_view<?php echo $i ?>" name="pf_view[<?php echo $i;  ?>]" value="1"<?php echo ($attach[$i]['view_use']) ? ' checked' : '';?>> 보기</label>
          </td>
          <td align="center">
            <label><input type="checkbox" id="pf_guest<?php echo $i ?>" name="pf_guest[<?php echo $i;  ?>]" value="1"<?php echo ($attach[$i]['guest_use']) ? ' checked' : '';?>> 비회원</label>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
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
// 사업소별 가격 수정
$("#popup_box").hide();
$("#popup_box").css("opacity", 1);

$('#btn_entprice').click(function(e) {
  $('body').addClass('modal-open');

  var it_id = $(this).data('id');
  $("body").addClass('modal-open');
  $("#popup_box > div").html('<iframe src="pop.item.entprice.php?it_id=' + it_id + '">');
  $("#popup_box iframe").load(function() {
    $("#popup_box").show();
  });
});

$('#popup_box').click(function() {
  $('body').removeClass('modal-open');
  $('#popup_box').hide();
});
</script>

<?php echo $frm_submit; ?>

<?php if($is_auth) { // 관리자일 때만 출력 ?>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_optional" class="anc-section">
  <h2 class="h2_frm">상하단내용</h2>
  <div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>상하단내용설정</caption>
    <colgroup>
      <col class="grid_3">
      <col>
    </colgroup>
    <tbody>
    <tr>
      <th scope="row">상단내용</th>
      <td class="helper">상세설명 페이지 상단에 출력하는 HTML 내용입니다.</td>
    </tr>
    <tr>
      <td colspan="2" class="iframe"><?php echo editor_html('it_head_html', get_text(html_purifier($it['it_head_html']), 0)); ?></td>
    </tr>
    <tr>
      <th scope="row">하단내용</th>
      <td class="helper">상세설명 페이지 하단에 출력하는 HTML 내용입니다.</td>
    </tr>
    <tr>
      <td colspan="2" class="iframe"><?php echo editor_html('it_tail_html', get_text(html_purifier($it['it_tail_html']), 0)); ?></td>
    </tr>
    <tr>
      <th scope="row">모바일 상단내용</th>
      <td class="helper">모바일 상세설명 페이지 상단에 출력하는 HTML 내용입니다.</td>
    </tr>
    <tr>
      <td colspan="2" class="iframe"><?php echo editor_html('it_mobile_head_html', get_text(html_purifier($it['it_mobile_head_html']), 0)); ?></td>
    </tr>
    <tr>
      <th scope="row">모바일 하단내용</th>
      <td class="helper">모바일 상세설명 페이지 하단에 출력하는 HTML 내용입니다.</td>
    </tr>
    <tr>
      <td colspan="2" class="iframe"><?php echo editor_html('it_mobile_tail_html', get_text(html_purifier($it['it_mobile_tail_html']), 0)); ?></td>
    </tr>
    </tbody>
    </table>
  </div>
</section>

<?php echo $frm_submit; ?>

<?php echo $pg_anchor; ?>

<section id="anc_sitfrm_extra" class="anc-section">
  <h2 class="h2_frm">여분필드 설정</h2>
  <div class="tbl_frm01 tbl_wrap">
    <table>
    <colgroup>
      <col class="grid_3">
      <col>
    </colgroup>
    <tbody>
    <?php for ($i=1; $i<=10; $i++) { ?>
    <tr>
      <th scope="row">여분필드<?php echo $i ?></th>
      <td class="td_extra">
        <label for="it_<?php echo $i ?>_subj">여분필드 <?php echo $i ?> 제목</label>
        <input type="text" name="it_<?php echo $i ?>_subj" id="it_<?php echo $i ?>_subj" value="<?php echo get_text($it['it_'.$i.'_subj']) ?>" class="frm_input">
        <label for="it_<?php echo $i ?>">여분필드 <?php echo $i ?> 값</label>
        <input type="text" name="it_<?php echo $i ?>" value="<?php echo get_text($it['it_'.$i]) ?>" id="it_<?php echo $i ?>" class="frm_input">
      </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
  </div>
</section>

<?php } else { // 파트너 ?>
  <?php for ($i=1; $i<=10; $i++) { ?>
  <input type="hidden" name="it_<?php echo $i ?>_subj" id="it_<?php echo $i ?>_subj" value="<?php echo get_text($it['it_'.$i.'_subj']) ?>">
  <input type="hidden" name="it_<?php echo $i ?>" value="<?php echo get_text($it['it_'.$i]) ?>" id="it_<?php echo $i ?>">
  <?php } ?>
<?php } ?>

<?php echo $frm_submit; ?>

<script>
function dataURItoBlob(dataURI) {
  // convert base64/URLEncoded data component to raw binary data held in a string
  var byteString;
  if (dataURI.split(',')[0].indexOf('base64') >= 0)
    byteString = atob(dataURI.split(',')[1]);
  else
    byteString = unescape(dataURI.split(',')[1]);

  // separate out the mime component
  var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

  // write the bytes of the string to a typed array
  var ia = new Uint8Array(byteString.length);
  for (var i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }

  return new Blob([ia], {type:mimeString});
}

var nowToDataURLResult = "";
function toDataURL(url) {
  return new Promise(function(resolve, reject){
    var xhr = new XMLHttpRequest();
    xhr.onload = function() {
    var reader = new FileReader();
    reader.onloadend = function() {
      nowToDataURLResult = reader.result;
      resolve();
    }
    reader.readAsDataURL(xhr.response);
    };
    xhr.open('GET', url);
    xhr.responseType = 'blob';
    xhr.send();
  });
}

function dataURLtoFile(dataurl, filename) {
  var arr = dataurl.split(','),
    mime = arr[0].match(/:(.*?);/)[1],
    bstr = atob(arr[1]),
    n = bstr.length,
    u8arr = new Uint8Array(n);

  while(n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }

  return new File([u8arr], filename, {type:mime});
}

var f = document.fitemform;

<?php if ($w == 'u') { ?>
$(".banner_or_img").addClass("sit_wimg");
$(function() {
  $(".sit_wimg_view").bind("click", function() {
    var sit_wimg_id = $(this).attr("id").split("_");
    var $img_display = $("#"+sit_wimg_id[1]);

    $img_display.toggle();

    if($img_display.is(":visible")) {
      $(this).text($(this).text().replace("확인", "닫기"));
    } else {
      $(this).text($(this).text().replace("닫기", "확인"));
    }

    var $img = $("#"+sit_wimg_id[1]).children("img");
    var width = $img.width();
    var height = $img.height();
    if(width > 700) {
      var img_width = 700;
      var img_height = Math.round((img_width * height) / width);

      $img.width(img_width).height(img_height);
    }
  });
  $(".sit_wimg_close").bind("click", function() {
    var $img_display = $(this).parents(".banner_or_img");
    var id = $img_display.attr("id");
    $img_display.toggle();
    var $button = $("#it_"+id+"_view");
    $button.text($button.text().replace("닫기", "확인"));
  });
});
<?php } ?>

function codedupcheck(id)
{
  if (!id) {
    alert('상품관리코드를 입력하십시오.');
    f.it_id.focus();
    return;
  }

  var it_id = id.replace(/[A-Za-z0-9\-_]/g, "");
  if(it_id.length > 0) {
    alert("상품관리코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.");
    return false;
  }

  $.post(
    "./codedupcheck.php",
    { it_id: id },
    function(data) {
      if(data.name) {
        alert("코드 '"+data.code+"' 는 '".data.name+"' (으)로 이미 등록되어 있으므로\n\n사용하실 수 없습니다.");
        return false;
      } else {
        alert("'"+data.code+"' 은(는) 등록된 코드가 없으므로 사용하실 수 있습니다.");
        document.fitemform.codedup.value = '';
      }
    }, "json"
  );
}

function fitemformcheck(f)
{

  var importantItem = $(".importantBorder");
  for(var i = 0; i < importantItem.length; i++) {
    if(!$(importantItem[i]).val()){
      var id = $(importantItem[i]).attr("id");
      var label = $("label[for='" + id + "']").text();

      if(label){
        alert(label + "이(가) 입력되지 않았습니다.");
      }
      $(importantItem[i]).focus();
      return false;
    }
  }

  if (!f.ca_id.value) {
    alert("기본분류를 선택하십시오.");
    f.ca_id.focus();
    return false;
  }

  if (f.w.value == "") {
    var error = "";
    $.ajax({
      url: "./ajax.it_id.php",
      type: "POST",
      data: {
        "it_id": f.it_id.value
      },
      dataType: "json",
      async: false,
      cache: false,
      success: function(data, textStatus) {
        error = data.error;
      }
    });

    if (error) {
      alert(error);
      return false;
    }
  }

  if(f.it_point_type.value == "1" || f.it_point_type.value == "2") {
    var point = parseInt(f.it_point.value);
    if(point > 99) {
      alert("포인트 비율을 0과 99 사이의 값으로 입력해 주십시오.");
      return false;
    }
  }

  if(parseInt(f.it_sc_type.value) > 1 && parseInt(f.it_sc_type.value) != 5) {
    if(!f.it_sc_price.value || f.it_sc_price.value == "0") {
      alert("기본배송비를 입력해 주십시오.");
      return false;
    }

    if(f.it_sc_type.value == "2" && (!f.it_sc_minimum.value || f.it_sc_minimum.value == "0")) {
      alert("배송비 상세조건의 주문금액을 입력해 주십시오.");
      return false;
    }

    if(f.it_sc_type.value == "4" && (!f.it_sc_qty.value || f.it_sc_qty.value == "0")) {
      alert("배송비 상세조건의 주문수량을 입력해 주십시오.");
      return false;
    }
  }

  // 관련상품처리
  var item = new Array();
  var re_item = it_id = "";

  $("#reg_relation input[name='re_it_id[]']").each(function() {
    it_id = $(this).val();
    if(it_id == "")
      return true; //

    item.push(it_id);
  });

  if(item.length > 0)
    re_item = item.join();

  $("input[name=it_list]").val(re_item);

  <?php if($is_auth) { // 관리자 일 때 ?>
  // 이벤트처리
  var evnt = new Array();
  var ev = ev_id = "";

  $("#reg_event_list input[name='ev_id[]']").each(function() {
    ev_id = $(this).val();
    if(ev_id == "")
      return true; //

    evnt.push(ev_id);
  });

  if(evnt.length > 0)
    ev = evnt.join();

  $("input[name=ev_list]").val(ev);
  <?php } // 관리자 끝 ?>

  <?php echo get_editor_js('it_explan'); ?>
  <?php echo get_editor_js('pt_explan'); // APMS : 2014.07.20 ?>
  <?php echo get_editor_js('it_mobile_explan'); ?>
  <?php echo get_editor_js('pt_mobile_explan'); // APMS : 2014.07.20 ?>
  <?php echo get_editor_js('it_reference'); ?>
  <?php
  if($is_auth) { // 관리자 일 때
    echo get_editor_js('it_head_html');
    echo get_editor_js('it_tail_html');
    echo get_editor_js('it_mobile_head_html');
    echo get_editor_js('it_mobile_tail_html');
  }
  ?>

  var w = '<?php echo $w; ?>';
  if(!f.it_img1.value && !w) {
    alert("이미지를 등록해주세요.");
    return false;
  }

    return true;
}

function categorychange(f)
{
  var idx = f.ca_id.value;

  if (f.w.value == "" && idx)
  {
    //f.it_use.checked = ca_use[idx] ? true : false;
    f.it_stock_qty.value = ca_stock_qty[idx];
  <?php if($is_auth) { // 관리자 일 때 ?>
    f.it_sell_email.value = ca_sell_email[idx];
  <?php } ?>
  }
}

categorychange(document.fitemform);

$(function() {
  // 직배송 or 설치 둘 중 하나만 선택
  $('input[name="it_is_direct_delivery"]').change(function(e) {
    var val = $(this).val();
    var checked = $(this).prop('checked');

    if(checked) {
      var another = (val == '1') ? '2' : '1';
      $('input[name="it_is_direct_delivery"][value="'+another+'"]').prop('checked', false);
    }

    setTimeout(function() {
      if ($($("input[name='it_is_direct_delivery']")[0]).is(":checked")) {
        $('input[name="it_is_direct_release_ready"]').prop("checked", false);
        $('input[name="it_is_direct_release_ready"]').prop("disabled", true);
      } else {
        $('input[name="it_is_direct_release_ready"]').prop("disabled", false);
      }
    }, 0);
  });

  /* 210208 */
  var itemIdList = <?=json_encode($itemIdList)?>;
  $("#ca_id").change(function(){
    $("#it_thezone").val(itemIdList[$(this).val()]);
  });
  $("#it_thezone").val(itemIdList[$("#ca_id").val()]);

});

</script>
