<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '주문내역';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 주문삭제 히스토리 테이블 필드 추가
if(!sql_query(" select mb_id from {$g5['g5_shop_order_delete_table']} limit 1 ", false)) {
    sql_query("
    ALTER TABLE `{$g5['g5_shop_order_delete_table']}`
    ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `de_data`,
    ADD `de_ip` varchar(255) NOT NULL DEFAULT '' AFTER `mb_id`,
    ADD `de_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `de_ip`
  ", true);
}


/*
// 23.10.13 : 서원 - 롯데택배 EDI를 사용하지 않음에 따름 불필요한 쿼리문 실행 차단.

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
*/


$warehouse_list = get_warehouses();

// 초기 3개월 범위 적용
if (!$fr_date && !$to_date) {
    $fr_date = date("Y-m-d", strtotime("-60 day"));
    $to_date = date("Y-m-d");
}

add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js?v='.APMS_SVER.'"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js?v='.APMS_SVER.'"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css?v='.APMS_SVER.'">', 0);
?>
<style>
    #text_size {
        display:none;
    }
    .page_title {
        display:none;
    }

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
<script src="<?php echo G5_ADMIN_URL; ?>/shop_admin/js/orderlist.js?ver=<?=APMS_SVER;?>"></script>

<div class="local_ov01 local_ov fixed">
    <?php echo $listall; ?>
    <h1 style="border:0;padding:5px 0;margin:0;">주문내역</h1>
    <span class="btn_ov01" style="display: none"><span class="ov_txt">전체 주문내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
    <?php if($od_status == '준비' && $total_count > 0) { ?>
        <a href="./orderdelivery.php" id="order_delivery" class="ov_a">엑셀배송처리</a>
    <?php } ?>
    <div class="right">
        <!-- <button id="select_important">선택 계산서 발행 확인</button> -->
        <!-- <button id="deselect_important">선택 계산서 발행 해지</button> -->
        <!-- <button id="gdexcel"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_img_ex.gif">경동엑셀</button> -->
        <!-- <button id="dzexcel"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_img_ex.gif">더존엑셀</button> -->
        <!-- <button id="handsabang" onClick="sanbang_order_send()">사방넷수동가져오기</button> -->
        <!-- <button id="list_matching_cancel">매칭데이터취소</button> -->
        <button id="delivery_inst_schedule">위탁(설치)일정표</button>
        <select class="sb1" name="" id="ct_direct_delivery_partner_sb">
            <?php
            //출고담당자 select
            $ct_direct_delivery_partner_select="";
            $partners = get_partner_members('직배송');
            $ct_direct_delivery_partner_select .= '<option value="">위탁(직배송) 선택</option>';
            $ct_direct_delivery_partner_select .= '<option value="미지정">미지정</option>';
            foreach($partners as $partner) {
                $ct_direct_delivery_partner_select .='<option value="'.$partner['mb_id'].'">'.$partner['mb_name'].'</option>';
            }
            echo $ct_direct_delivery_partner_select;
            ?>
        </select>
        <button id="ct_direct_delivery_partner_all">위탁 선택적용</button>
		<button id="ct_direct_delivery_partner_cncl">위탁 선택해제</button>
        <button id="delivery_excel_upload">택배정보 일괄 업로드</button>
        <!-- 
        <select class="sb1" name="" id="ct_manager_sb">
            <?php
            //출고담당자 select
            /*
            $od_release_select="";
            $sql_m="select b.`mb_name`, b.`mb_id` from `g5_auth` a left join `g5_member` b on (a.`mb_id`=b.`mb_id`) where a.`au_menu` = '400001'";
            $result_m = sql_query($sql_m);
            $od_release_select .= '<option value="">출고 담당자 선택</option>';
            $od_release_select .= '<option value="미지정">미지정</option>';
            for ($q=0; $row_m=sql_fetch_array($result_m); $q++){
                $selected="";
                $od_release_select .='<option value="'.$row_m['mb_id'].'" '.$selected.'>'.$row_m['mb_name'].'('.$row_m['mb_id'].')</option>';
            }
            echo $od_release_select;
            */
            ?>
        </select>
        <button id="ct_manager_send_all">출고담당자 선택변경</button>
        -->

        <select class="sb1" name="it_default_warehouse" id="ct_warehouse_sb">
            <?php
            $default_warehouse_select="";
            $default_warehouse_select .= '<option value="">출하창고 선택</option>';
            foreach($warehouse_list as $warehouse) {
                $default_warehouse_select .='<option value="'.$warehouse.'" >'.$warehouse.'</option>';
            }
            echo $default_warehouse_select;
            ?>
        </select>
        <button id="ct_warehouse_all">출하창고 선택변경</button>
		<button id="ct_warehouse_cncl">출하창고 선택해제</button>
        <button id="delivery_edi_send_all">로젠 EDI 선택 전송</button>
        <button id="delivery_edi_send_all" data-type="resend">로젠 EDI 재전송</button>
        <button id="delivery_edi_return_all">송장리턴</button>
        <!-- <button class="lotte_btn" id="delivery_lotte_send" <?php echo ($result_lotte['cnt'] > 0) ? '' : 'disabled'?>><?php echo ($result_lotte['cnt'] > 0) ? '롯데택배 '.$result_lotte['cnt'].'건 전송 필요' : '롯데택배 전송완료'?></button> -->
        <!-- <button class="orderExcel" data-type="1"><img src="/adm/shop_admin/img/btn_img_ex.gif">주문 엑셀 다운로드</button> -->
        <!-- <button class="orderExcel" data-type="2"><img src="/adm/shop_admin/img/btn_img_ex.gif">출고 엑셀 다운로드</button> -->
    </div>
</div>

<div id="upload_wrap">
    <form id="form_delivery_excel_upload" style="font-size: 14px;">
        <div class="form-group">
            <label for="datafile">택배정보 일괄 업로드</label>
            <input type="file" name="datafile" id="datafile">
            <p class="help-block">
                주문내역 엑셀에 택배정보를 작성해서 업로드해주세요.<br>
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
                    <select name="sel_date_field" id="sel_date_field">
                        <option value="ct_time" <?php echo get_selected($sel_date_field, 'ct_time'); ?>>주문일</option>
                        <option value="ct_move_date" <?php echo get_selected($sel_date_field, 'ct_move_date'); ?>>변경일</option>
                        <option value="od_receipt_time" <?php echo get_selected($sel_date_field, 'od_receipt_time'); ?>>입금일</option>
                        <option value="od_ex_date" <?php echo get_selected($sel_date_field, 'od_ex_date'); ?>>희망출고일</option>
                        <option value="ct_ex_date" <?php echo get_selected($sel_date_field, 'ct_ex_date'); ?>>출고완료일</option>
                    </select>
                    <div class="sch_last">
                        <input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton" />
                        <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton" />
                        <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton" />
                        <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton" />
                        <input type="button" value="3개월" id="select_date_3month" name="select_date" class="select_date newbutton" />
                        <button type="button" value="전체" id="select_date_all" name="select_date" class="select_date newbutton">직접입력</button>
                        <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
                        <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
                    </div>
                </td>
            </tr>
            <!--
      <tr>
        <th>결제금액</th>
        <td>
          <input type="checkbox" name="price" value="1" id="search_won" <?php echo get_checked($price, '1'); ?>><label for="search_won">&nbsp;</label>
          <input type="text" name="price_s" value="<?php echo $price_s ?>" class="line" maxlength="10" style="width:80px">
          원 ~
          <input type="text" name="price_e" value="<?php echo $price_e ?>" class="line" maxlength="10" style="width:80px">
          원
          <div class="linear">
            <span class="linear_span">결제여부</span>
            <input type="checkbox" id="od_pay_state_all" name="od_pay_state[]" value="" <?php echo $od_pay_state && count($od_pay_state) >= 3 ? 'checked' : '' ?>> <label for="od_pay_state_all" class="">전체</label>
            <input type="checkbox" id="od_pay_state_0" class="od_pay_state" name="od_pay_state[]" value="1" <?php echo ($od_pay_state && in_array('1', $od_pay_state)) ? 'checked' : '' ?>> <label for="od_pay_state_0" class="">결제</label>
            <input type="checkbox" id="od_pay_state_1" class="od_pay_state" name="od_pay_state[]" value="0" <?php echo ($od_pay_state && in_array('0', $od_pay_state)) ? 'checked' : '' ?>> <label for="od_pay_state_1" class="">미결제</label>
            <input type="checkbox" id="od_pay_state_2" class="od_pay_state" name="od_pay_state[]" value="2" <?php echo ($od_pay_state && in_array('2', $od_pay_state)) ? 'checked' : '' ?>> <label for="od_pay_state_2" class="">결제후출고</label>
          </div>
        </td>
      </tr>
      -->
            <!--
      <tr>
        <th>결제수단</th>
        <td>
          <input type="checkbox" name="od_settle_case[]" value="all" id="od_settle_case01" <?php echo $od_settle_case && in_array('all', $od_settle_case) && count($od_settle_case) >= 8 ? 'checked' : '' ?>>
          <label for="od_settle_case01">전체</label>
          <input type="checkbox" name="od_settle_case[]" value="포인트" id="od_settle_case07" <?php echo option_array_checked('포인트', $od_settle_case);    ?>>
          <label for="od_settle_case07">포인트</label>
          <input type="checkbox" name="od_settle_case[]" value="무통장" id="od_settle_case02" <?php echo option_array_checked('무통장', $od_settle_case);    ?>>
          <label for="od_settle_case02">무통장</label>
          <input type="checkbox" name="od_settle_case[]" value="가상계좌" id="od_settle_case03" <?php echo option_array_checked('가상계좌', $od_settle_case);  ?>>
          <label for="od_settle_case03">가상계좌</label>
          <input type="checkbox" name="od_settle_case[]" value="계좌이체" id="od_settle_case04" <?php echo option_array_checked('계좌이체', $od_settle_case);  ?>>
          <label for="od_settle_case04">계좌이체</label>
          <input type="checkbox" name="od_settle_case[]" value="휴대폰" id="od_settle_case05" <?php echo option_array_checked('휴대폰', $od_settle_case);    ?>>
          <label for="od_settle_case05">휴대폰</label>
          <input type="checkbox" name="od_settle_case[]" value="신용카드" id="od_settle_case06" <?php echo option_array_checked('신용카드', $od_settle_case);  ?>>
          <label for="od_settle_case06">신용카드</label>
          <input type="checkbox" name="od_settle_case[]" value="간편결제" id="od_settle_case09" <?php echo option_array_checked('간편결제', $od_settle_case);  ?>>
          <label for="od_settle_case09">PG간편결제</label>
          <input type="checkbox" name="od_settle_case[]" value="KAKAOPAY" id="od_settle_case08" <?php echo option_array_checked('KAKAOPAY', $od_settle_case);  ?>>
          <label for="od_settle_case08">KAKAOPAY</label>
          <input type="checkbox" name="od_settle_case[]" value="네이버페이" id="od_settle_case10" <?php echo option_array_checked('네이버페이', $od_settle_case);  ?>>
          <label for="od_settle_case10">네이버페이</label>
        </td>
      </tr>
-->
            <tr>
                <th>주문상태</th>
                <td class="step_before">
                    <input type="checkbox" name="od_status[]" value="all" id="step_all0" <?php echo option_array_checked('all', $od_status); ?>>
                    <label for="step_all0">전체</label>

                    <?php
                    foreach($order_steps as $order_step) {
                        if (!$order_step['orderlist']) continue;
                        ?>
                        <input type="checkbox" name="od_status[]" value="<?php echo $order_step['val']; ?>" id="step_<?php echo $order_step['val']; ?>" <?php echo option_array_checked($order_step['val'], $od_status); ?>>
                        <label for="step_<?php echo $order_step['val']; ?>"><?php echo $order_step['name']; ?></label>
                    <?php } ?>
                </td>
            </tr>
            <!--
      <tr>
        <th>주문상태 출고 전</th>
        <td class="step_before">
          <input type="checkbox" name="od_status[]" value="all" id="step_all0" <?php echo option_array_checked('all', $od_status); ?>>
          <label for="step_all0">전체</label>

          <?php
            foreach($order_steps as $order_step) {
                if (!$order_step['orderlist'] || $order_step['chulgo'] == '출고후') continue;
                ?>
          <input type="checkbox" name="od_status[]" value="<?php echo $order_step['val']; ?>" id="step_<?php echo $order_step['val']; ?>" <?php echo option_array_checked($order_step['val'], $od_status); ?>>
          <label for="step_<?php echo $order_step['val']; ?>"><?php echo $order_step['name']; ?></label>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <th>주문상태 출고 후</th>
        <td class="step_after">
          <input type="checkbox" name="od_status[]" value="all2" id="step_all1" <?php echo option_array_checked('all2', $od_status); ?>>
          <label for="step_all1">전체</label>
          <?php
            foreach($order_steps as $order_step) {
                if (!$order_step['orderlist'] || $order_step['chulgo'] != '출고후') continue;
                ?>
          <input type="checkbox" name="od_status[]" value="<?php echo $order_step['val']; ?>" id="step_<?php echo $order_step['val']; ?>" <?php echo option_array_checked($order_step['val'], $od_status); ?>>
          <label for="step_<?php echo $order_step['val']; ?>"><?php echo $order_step['name']; ?></label>
          <?php } ?>
        </td>
      </tr>
          -->
            <?php
            $member_type_flag = ($member_type_s && count($member_type_s) >= 1);
            $member_level_flag = ($member_level_s && count($member_level_s) >= 2);
            $is_member_flag = ($is_member_s && count($is_member_s) >= 2);
            ?>
            <!--
      <tr>
        <th>등급</th>
        <td>
          <input type="checkbox" name="" value="" id="member_grade" class="member_grade" <?php echo ($member_type_flag && $member_level_flag && $is_member_flag) ? 'checked' : '' ?>>
          <label for="member_grade">전체</label>
          <input type="checkbox" name="member_level_s[]" value="4" id="member_level_01" class="member_grade" <?php echo option_array_checked('4', $member_level_s);    ?>>
          <label for="member_level_01">우수사업소</label>
          <input type="checkbox" name="member_level_s[]" value="3" id="member_level_02" class="member_grade" <?php echo option_array_checked('3', $member_level_s);  ?>>
          <label for="member_level_02">사업소</label>
          <input type="checkbox" name="is_member_s[]" value="not null" id="is_member_02" class="member_grade" <?php echo option_array_checked('not null', $is_member_s);    ?>>
          <label for="is_member_02">일반회원</label>
        </td>
      </tr>
      <tr>
        <th>주문형태</th>
        <td>
          <input type="radio" id="od_recipient_all" name="od_recipient" value="" <?=($_GET["od_recipient"] != "Y" || $_GET["od_recipient"] != "N") ? "checked" : ""?>><label for="od_recipient_all"> 전체</label>
          <input type="radio" id="od_recipient_N" name="od_recipient" value="N" <?=($_GET["od_recipient"] == "N") ? "checked" : ""?>><label for="od_recipient_N"> 재고주문</label>
          <input type="radio" id="od_recipient_Y" name="od_recipient" value="Y" <?=($_GET["od_recipient"] == "Y") ? "checked" : ""?>><label for="od_recipient_Y"> 상품주문</label>
        </td>
      </tr>
      -->
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
                                        $sql = "SELECT au.*, mb.mb_name FROM g5_auth au, g5_member mb where mb.mb_id = au.mb_id AND au_menu = '400400' AND au_auth LIKE '%w%' order by mb_name";
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
                    <!--
                    <div class="select">
                        <span>출고담당자</span>
                        <div class="selectbox_multi">
                            <div class="cont multiselect">
                                <h2>출고담당자 선택</h2>
                                <div class="list">
                                    <ul>
                                        <li><input type="checkbox" name="od_release_manager[]" id="yet_release" value="yet_release" title="" <?php echo option_array_checked('yet_release', $od_release_manager); ?>><label for="yet_release">미지정</label></li>
                                        <li><input type="checkbox" name="od_release_manager[]" id="no_release" value="no_release" title="no_release" <?php echo option_array_checked('no_release', $od_release_manager); ?>><label for="no_release">출고대기</label></li>
                                        <li><input type="checkbox" name="od_release_manager[]" id="out_release" value="-" title="out_release" <?php echo option_array_checked('-', $od_release_manager); ?>><label for="out_release">외부출고</label></li>
                                        <?php
                                        $sql = "SELECT au.*, mb.mb_name FROM g5_auth au, g5_member mb where mb.mb_id = au.mb_id AND au_menu = '400001' AND au_auth LIKE '%w%' order by mb_name";
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
                    -->
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
                        <span class="linear_span">설치결과이슈사항</span>
                        <input type="checkbox" name="partner_issue[]" id="partner_issue_1" value="1" title="" <?php echo option_array_checked('1', $partner_issue); ?>><label for="partner_issue_1">상품변경</label>
                        <input type="checkbox" name="partner_issue[]" id="partner_issue_2" value="2" title="" <?php echo option_array_checked('2', $partner_issue); ?>><label for="partner_issue_2">상품추가</label>
                        <input type="checkbox" name="partner_issue[]" id="partner_issue_3" value="3" title="" <?php echo option_array_checked('3', $partner_issue); ?>><label for="partner_issue_3">미설치</label>
                        <input type="checkbox" name="od_partner_edit" id="od_partner_edit" value="1" title="" <?php echo option_array_checked('1', $od_partner_edit); ?>><label for="od_partner_edit">파트너 상품수정</label>
                    </div>
                    <br/>
                    <div style="display: inline-block; margin-left: 11px; margin-right:15px;">
                        <?php
                        $not_approved_count = sql_fetch("select count(*) as cnt from g5_cart_barcode_approve_request where status = '승인요청' and del_yn = 'N'")['cnt'];
                        ?>
                        <span class="linear_span">이슈사항</span>
                        <input type="checkbox" name="issue_1" id="issue_1" value="1" title="" <?php echo option_array_checked('1', $issue_1); ?>><label for="issue_1">출고준비 3일 경과</label>
                        <input type="checkbox" name="issue_2" id="issue_2" value="1" title="" <?php echo option_array_checked('1', $issue_2); ?>><label for="issue_2">취소/반품 요청</label>
                        <input type="checkbox" name="issue_3" id="issue_3" value="1" title="" <?php echo option_array_checked('1', $issue_3); ?>><label for="issue_3">미재고 바코드 입력(<?php echo $not_approved_count ?>)</label>
                    </div>
                    <div class="linear">
                        <span class="linear_span">바코드</span>
                        <input type="radio" name="ct_barcode_saved" id="ct_barcode_all" value="" title="" <?php echo option_array_checked('', $ct_barcode_saved); ?>><label for="ct_barcode_all">전체</label>
                        <input type="radio" name="ct_barcode_saved" id="ct_barcode_saved" value="saved" title="" <?php echo option_array_checked('saved', $ct_barcode_saved); ?>><label for="ct_barcode_saved">입력완료</label>
                        <input type="radio" name="ct_barcode_saved" id="ct_barcode_none" value="none" title="" <?php echo option_array_checked('none', $ct_barcode_saved); ?>><label for="ct_barcode_none">미입력</label>
                    </div>
                    <div class="linear">
                        <span class="linear_span">배송정보</span>
                        <input type="radio" name="ct_delivery_saved" id="ct_delivery_all" value="" title="" <?php echo option_array_checked('', $ct_delivery_saved); ?>><label for="ct_delivery_all">전체</label>
                        <input type="radio" name="ct_delivery_saved" id="ct_delivery_saved" value="saved" title="" <?php echo option_array_checked('saved', $ct_delivery_saved); ?>><label for="ct_delivery_saved">입력완료</label>
                        <input type="radio" name="ct_delivery_saved" id="ct_delivery_none" value="none" title="" <?php echo option_array_checked('none', $ct_delivery_saved); ?>><label for="ct_delivery_none">미입력</label>
                    </div>
                    <div class="linear">
                        <span class="linear_span">이카운트 엑셀 필터링</span>
                        <input type="radio" name="ct_is_ecount_excel_downloaded_saved" id="ct_is_ecount_excel_downloaded_all" value="" title="" <?php echo option_array_checked('', $ct_is_ecount_excel_downloaded_saved); ?>><label for="ct_is_ecount_excel_downloaded_all">전체</label>
                        <input type="radio" name="ct_is_ecount_excel_downloaded_saved" id="ct_is_ecount_excel_downloaded_saved" value="saved" title="" <?php echo option_array_checked('saved', $ct_is_ecount_excel_downloaded_saved); ?>><label for="ct_is_ecount_excel_downloaded_saved">엑셀받기 완료</label>
                        <input type="radio" name="ct_is_ecount_excel_downloaded_saved" id="ct_is_ecount_excel_downloaded_none" value="none" title="" <?php echo option_array_checked('none', $ct_is_ecount_excel_downloaded_saved); ?>><label for="ct_is_ecount_excel_downloaded_none">엑셀받기 미완료</label>
                    </div>

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
                        <!--<option value="barcode" <?php //echo get_selected($sel_field, 'barcode'); ?>바코드</option>-->
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
        <div class="ajax-loader">
            <img src="img/ajax-loading.gif" class="img-responsive" />
        </div>

        <ul class="order_tab">
            <li class="" data-step="" data-status="">
                <a>전체</a>
            </li>
            <?php
            foreach($order_steps as $order_step) {
                if (!$order_step['orderlist']) continue;
                ?>
                <li class="" data-step="<?php echo $order_step['step']; ?>" data-status="<?php echo $order_step['val']; ?>" id="<?php echo $order_step['val']; ?>" >
                    <a><?php echo $order_step['name']; ?>(<span>0</span>)</a>
                </li>
            <?php } ?>
        </ul>

        <div id="samhwa_order_ajax_list_table">
        </div>
    </div>

    <!--
  <div class="local_cmd01 local_cmd">
      <?php if (($od_status == '' || $od_status == '완료' || $od_status == '전체취소' || $od_status == '부분취소') == false) {
        // 검색된 주문상태가 '전체', '완료', '전체취소', '부분취소' 가 아니라면
        ?>
      <label for="od_status" class="cmd_tit">주문상태 변경</label>
      <?php
        $change_status = "";
        if ($od_status == '주문') $change_status = "입금";
        if ($od_status == '입금') $change_status = "준비";
        if ($od_status == '준비') $change_status = "배송";
        if ($od_status == '배송') $change_status = "완료";
        ?>
      <label><input type="checkbox" name="od_status" value="<?php echo $change_status; ?>"> '<?php echo $od_status ?>'상태에서 '<strong><?php echo $change_status ?></strong>'상태로 변경합니다.</label>
      <?php if($od_status == '주문' || $od_status == '준비') { ?>
      <input type="checkbox" name="od_send_mail" value="1" id="od_send_mail" checked="checked">
      <label for="od_send_mail"><?php echo $change_status; ?>안내 메일</label>
      <input type="checkbox" name="send_sms" value="1" id="od_send_sms" checked="checked">
      <label for="od_send_sms"><?php echo $change_status; ?>안내 SMS</label>
      <?php } ?>
      <?php if($od_status == '준비') { ?>
      <input type="checkbox" name="send_escrow" value="1" id="od_send_escrow">
      <label for="od_send_escrow">에스크로배송등록</label>
      <?php } ?>
      <input type="submit" value="선택수정" class="btn_submit" onclick="document.pressed=this.value">
      <?php } ?>
      <?php if ($od_status == '주문' || $od_status == '전체취소') { ?> <span>주문 또는 전체취소 상태에서만 삭제가 가능하며, 전체취소는 입금액이 없는 주문만 삭제됩니다.</span> <input type="submit" value="선택삭제" class="btn_submit" onclick="document.pressed=this.value"><?php } ?>
  </div>
  -->
    <!--
    <div class="local_desc02 local_desc">
    <p>
        &lt;무통장&gt;인 경우에만 &lt;주문&gt;에서 &lt;입금&gt;으로 변경됩니다. 가상계좌는 입금시 자동으로 &lt;입금&gt;처리됩니다.<br>
        &lt;준비&gt;에서 &lt;배송&gt;으로 변경시 &lt;에스크로배송등록&gt;을 체크하시면 에스크로 주문에 한해 PG사에 배송정보가 자동 등록됩니다.<br>
        <strong>주의!</strong> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.
    </p>
    </div>
    -->
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
    .modal-popup {
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        z-index: 999;
        background-color: rgba(0, 0, 0, 0.6);
        display:none;
    }
    .modal-popup > div {
        width: 1000px;
        max-width: 80%;
        height: 80%;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }
    .modal-popup > div iframe {
        width:100%;
        height:100%;
        border: 0;
        background-color: #FFF;
    }

    #popup_direct_delivery > div {
        background: white;
        width: 320px;
        height: 220px;
        position:relative;
        overflow: hidden;
    }
    #popup_direct_delivery > div h1 {
        padding-top: 15px;
        padding-bottom: 10px;
        margin-bottom: 16px;
    }
    #popup_direct_delivery > div p {
        text-align: center;
        margin-bottom: 10px;
        font-size: 1.2em;
    }
    #popup_direct_delivery .check_wrapper {
        font-size: 14px;
        padding: 0 40px;
        margin-bottom: 15px;
    }
    #popup_direct_delivery input[type='button'] {
        cursor: pointer;
    }
    #popup_direct_delivery input[type='checkbox'] {
        margin-right: 3px;
    }
    #popup_direct_delivery-close {
        position:absolute;
        top: 15px;
        right: 15px;
        color: #b0b0b0;
        font-size: 1.5em;
        cursor: pointer;
    }

</style>
<div id="popup_order_add" class="modal-popup">
    <div>dd</div>
</div>

<div id="popup_direct_delivery" class="modal-popup">
    <div>
        <h1>직배송 일괄전송</h1>
        <i class="fa fa-close fa-lg" id="popup_direct_delivery-close" onclick="directDeliveryPopup(false)"></i>

        <div class="check_wrapper flex-row justify-space-between">
            <span>전송 방법 : </span>
            <label><input type="checkbox" id="direct_delivery_check_email" value="1" checked />이메일,</label>
            <label><input type="checkbox" id="direct_delivery_check_fax" value="1" checked />팩스,</label>
            <label><input type="checkbox" id="direct_delivery_check_talk" value="1" />알림톡</label>
        </div>

        <p>이미 발송된 상품 제외 후 전송하시겠습니까?</p>
        <div style="text-align:center;">
            <input type="button" value="전체전송" onclick="sendDirectDelivery(true)" class="btn btn_03">
            &nbsp;&nbsp;
            <input type="button" value="제외전송" onclick="sendDirectDelivery(false)" class="btn btn_02">
        </div>
        <div class="flex-row justify-center" style="margin-top: 10px;">
            <input type="button" value="취소" onclick="directDeliveryPopup(false)" class="btn btn_04">
        </div>
    </div>
</div>

<script>
    $(function() {
		// 위탁 선택해제
		$('#ct_direct_delivery_partner_cncl').click(function() {
			var ct_id = [];
			var item = $("input[name='od_id[]']:checked");

			for (var i = 0; i < item.length; i++) {
				ct_id.push($(item[i]).val());
			}

			if (!ct_id.length) {
				alert('해제하실 주문을 선택해주세요.');
				return;
			}

			$.post('./ajax.ct_direct_delivery_partner.php', {
				ct_id: ct_id,
				ct_direct_delivery_partner: "미지정"
			 }, 'json')
				.done(function() {
					alert('위탁(직배송) 해제가 완료되었습니다.');
					location.reload();
				})
				.fail(function($xhr) {
					var data = $xhr.responseJSON;
					alert(data && data.message);
				});
		});

		// 일괄 출하창고 해제
		$('#ct_warehouse_cncl').click(function() {
			var ct_id = [];
			var item = $("input[name='od_id[]']:checked");
			
			for (var i = 0; i < item.length; i++) {
			  ct_id.push($(item[i]).val());
			}

			if (!ct_id.length) {
			  alert('해제하실 주문을 선택해주세요.');
			  return;
			}

			$.ajax({
			  method: 'POST',
			  url: './ajax.ct_warehouse_update.php',
			  data: {
				ct_id: ct_id,
				ct_warehouse: "미지정",
			  },
			}).done(function (data) {
			  // return false;
			  if (data.msg) {
				alert(data.msg);
			  }
			  if (data.result === 'success') {
				alert('출하창고가 해제되었습니다.');
				// location.reload();
			  }
			});
		});

        $(document).on("click", "#order_add", function (e) {
            e.preventDefault();

            $("#popup_order_add > div").html("<iframe src='./pop.order.add.php'></iframe>");
            $("#popup_order_add iframe").load(function(){
                $("#popup_order_add").show();
                $('#hd').css('z-index', 3);
                $('#popup_order_add iframe').contents().find('.mb_id_flexdatalist').focus();
            });

        });
    });

    function show_all_order() {
        page = 1;
        end = false;
        last_step = '';

        doSearch('Y');
    }


    var od_status = '';
    var od_step = 0;
    var page = 1;
    var loading = false;
    var end = false;
    var sub_menu = '<?php echo $sub_menu; ?>';
    var last_step = '';

    function doSearch(show_all) {
        if ( loading === true ) return;
        if ( end === true ) return;

        if (!show_all) {
            show_all = 'N';
        }
        var formdata = $.extend({}, {
            click_status: od_status,
            od_step: od_step,
            page: page,
            sub_menu: sub_menu,
            last_step: last_step,
            show_all: show_all,
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
            url: "./ajax.orderlist.php",
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
                // $('#samhwa_order_list_table>div.table:first-child tbody').append(html.left);
                // $('#samhwa_order_list_table>div.table:last-child tbody').append(html.right);

                var show_all_order_btn = false;
                $('.step_before input[name="od_status[]"]').each(function (i, v) {
                    if ($(v).attr('id') == 'step_all0') {
                        if ($(v).is(":checked")) {
                            show_all_order_btn = false;
                            return false;
                        }
                    }
                    else {
                        if ($(v).attr('id').indexOf('준비')) {
                            if ($(v).is(":checked")) {
                                show_all_order_btn = true;
                            }
                        }
                    }
                });
                if (show_all_order_btn) {
                    $('#show_all_order').show();
                }

                if ( !html.data || show_all == 'Y') {
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
    /*
    function doSearchExcel(show_all) {
        if ( loading === true ) return;
        if ( end === true ) return;

        if (!show_all) {
            show_all = 'N';
        }
        var formdata = $.extend({}, {
            click_status: od_status,
            od_step: od_step,
            page: page,
            sub_menu: sub_menu,
            last_step: last_step,
            show_all: show_all,
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
        formdata["ct_is_ecount_excel_downloaded"] = "0";


        var ajax = $.ajax({
            method: "POST",
            url: "./ajax.orderlist_ecount.php",
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
                // $('#samhwa_order_list_table>div.table:first-child tbody').append(html.left);
                // $('#samhwa_order_list_table>div.table:last-child tbody').append(html.right);

                var show_all_order_btn = false;
                $('.step_before input[name="od_status[]"]').each(function (i, v) {
                    if ($(v).attr('id') == 'step_all0') {
                        if ($(v).is(":checked")) {
                            show_all_order_btn = false;
                            return false;
                        }
                    }
                    else {
                        if ($(v).attr('id').indexOf('준비')) {
                            if ($(v).is(":checked")) {
                                show_all_order_btn = true;
                            }
                        }
                    }
                });
                if (show_all_order_btn) {
                    $('#show_all_order').show();
                }

                if ( !html.data || show_all == 'Y') {
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
    */

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
            window.open("./popup.prodBarNum.form.php?is_pop=true&no_refresh=1&orderlist=1&prodId=" + it + "&od_id=" + od + "&stock_insert=" + stock + "&option=" + option, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
        });

        var submitAction = function(e) {
            $("#frmsamhwaorderlist").attr("action", "samhwa_orderlist.php");
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

            if (od_status == "준비" || od_status == "출고준비") {
                $('#show_all_order').show();
            }
            else {
                $('#show_all_order').hide();
            }
        });

        // $('#samhwa_order_list .order_tab li:eq(0)').click();

        // $('.new_form .submit button[type="submit"]').click();

        $(window).scroll(function () {
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
            $('#ct_is_ecount_excel_downloaded_all').prop('checked', true);
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

<div class="btn_fixed_top">
    <a href="./samhwa_order_new.php" id="order_add" class="btn btn_01">주문서 추가</a>
    <input type="button" value="주문내역 엑셀다운로드" onclick="orderListExcelDownload('excel')" class="btn btn_02">
    <?php if($is_admin == 'super'){?>
    <input type="button" value="이카운트 엑셀다운로드" onclick="orderListExcelDownload('ecount')" class="btn" style="background: #6e9254; color: #fff;">
    <?php } ?>
    <input type="button" value="발주서 다운로드" onclick="orderListExcelDownload('partner')" class="btn btn_03">
    <input type="button" value="직배송 일괄전송" onclick="directDeliveryPopup(true)" class="btn btn_03">
</div>

<div class="btn_fixed_top2">
    <input type="button" value="더보기" onclick="doSearch()" class="btn btn_02">
    <input type="button" value="모든 주문보기" onclick="show_all_order()"id="show_all_order" class="btn btn_03" style="display:none;">
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
        
        var od_id = [];
        var item = $("input[name='od_id[]']:checked");
        for(var i = 0; i < item.length; i++) {
            od_id.push($(item[i]).val());
        }

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
        var href = "./order.excel.list.php";
        if (type === 'ecount') {
            href = "./order.ecount.excel.list.php";
            if(confirm('이미 다운로드한 상품은 제외하고 다운받으시겠습니까?'))
                queryString += "&new_only=1";
        }
        else if (type === 'partner') {
            if( !confirm("주문상품의 발주서를 다운로드합니다. (구매팀전용기능)") ) return;
            href = './order.partner.excel.php';
        }

        $('#loading_excel').show();

        if(type === 'partner') {
            excel_downloader = $.fileDownload(href, {
                httpMethod: "POST",
                data: queryString
            })
                .always(function() {
                    $('#loading_excel').hide();
                    item.each(function() {
                        if($(this).closest('tr').find('td.od_direct_delivery span.excel_done').length === 0)
                            $(this).closest('tr').find('td.od_direct_delivery').append('<br><span class="excel_done" style="color: #FF6600">발주완료</span>');
                    });
                });
        } else if(type === 'ecount') {
            excel_downloader = $.fileDownload(href, {
                httpMethod: "POST",
                data: queryString
            })
                .always(function() {
                    $('#loading_excel').hide();
                    if(!od_id.length) {
                        window.location.reload();
                    } else {
                        item.each(function() {
                            if($(this).closest('tr').find('td.od_step span.excel_done').length === 0)
                                $(this).closest('tr').find('td.od_step').append('<br><span class="excel_done" style="color: #77933c">이카운트 : 엑셀받기 완료</span>');
                        });
                    }
                });
        } else {
            excel_downloader = $.fileDownload(href, {
                httpMethod: "POST",
                data: queryString
            })
                .always(function() {
                    $('#loading_excel').hide();
                });
        }

        return false;
    }

    function cancelExcelDownload() {
        if(excel_downloader) {
            excel_downloader.abort();
        }
        $('#loading_excel').hide();
    }

    function directDeliveryPopup(status) {
        if (!status) {
            $("#popup_direct_delivery").hide();
            $('#hd').css('z-index', 10);
            return;
        }

        var od_id = [];
        var item = $("input[name='od_id[]']:checked");
        for(var i = 0; i < item.length; i++) {
            od_id.push($(item[i]).val());
        }

        if(!od_id.length) {
            alert('선택한 주문이 없습니다.');
            return false;
        }

        $("#popup_direct_delivery").show();
        $('#hd').css('z-index', 3);
    }

    function sendDirectDelivery(sendAllAgain) {
        var od_id = [];
        var item = $("input[name='od_id[]']:checked");
        for(var i = 0; i < item.length; i++) {
            od_id.push($(item[i]).val());
        }

        if(!od_id.length) {
            alert('선택한 주문이 없습니다.');
            return false;
        }

        $.ajax({
            method: "POST",
            url: "./ajax.send_direct_delivery_by_item.php",
            data: {
                'ct_ids': od_id,
                'sendAllAgain': sendAllAgain ? 'Y' : 'N',
                'sendEmail': $('#direct_delivery_check_email').is(':checked') ? 1 : 0,
                'sendFax': $('#direct_delivery_check_fax').is(':checked') ? 1 : 0,
                'sendTalk': $('#direct_delivery_check_talk').is(':checked') ? 1 : 0,
            },
            beforeSend: function () {
                $('.ajax-loader').css("visibility", "visible");
            },
        })
            .done(function (data) {
                console.log(data);
                $('.ajax-loader').css("visibility", "hidden");
                if (data.result == "success") {
                    alert('전송 완료');
                    window.location.reload();
                } else {
                    alert('전송 실패');
                }
            });
    }

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
    // 위탁 설치 일정표 팝업 띄우기
    $('#delivery_inst_schedule').click(function() {
        let opt = "width=1360,height=780,left=0,top=10";
        let _url = "/shop/schedule/index.php";
        if (jQuery.browser.mobile) {
            opt = "";
            _url = _url.replace("index.php", "m_index.php");
        }
        window.open(_url, "win_schedule", opt);
        return false;
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

    // 위탁 선택적용
    $('#ct_direct_delivery_partner_all').click(function() {
        var ct_id = [];
        var item = $("input[name='od_id[]']:checked");

        var sb1 = $('#ct_direct_delivery_partner_sb').val();
        if(!sb1){
            alert('위탁 파트너를 선택하신 후 변경을 눌러주세요. ');
            return false;
        }

        for (var i = 0; i < item.length; i++) {
            ct_id.push($(item[i]).val());
        }

        if (!ct_id.length) {
            alert('적용하실 주문을 선택해주세요.');
            return;
        }

        $.post('./ajax.ct_direct_delivery_partner.php', {
            ct_id: ct_id,
            ct_direct_delivery_partner: sb1
        }, 'json')
            .done(function() {
                alert('위탁(직배송) 적용이 완료되었습니다.');
            })
            .fail(function($xhr) {
                var data = $xhr.responseJSON;
                alert(data && data.message);
            });
    });
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
