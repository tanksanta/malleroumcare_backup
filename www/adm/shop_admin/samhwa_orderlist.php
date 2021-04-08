<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '주문내역';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 주문삭제 히스토리 테이블 필드 추가
if(!sql_query(" select mb_id from {$g5['g5_shop_order_delete_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_delete_table']}`
                    ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `de_data`,
                    ADD `de_ip` varchar(255) NOT NULL DEFAULT '' AFTER `mb_id`,
                    ADD `de_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `de_ip` ", true);
}

?>
<style>
#text_size {
    display:none;
}
.page_title {
    display:none;
}
</style>
<script src="<?php echo G5_ADMIN_URL; ?>/shop_admin/js/orderlist.js?ver=<?php echo time(); ?>"></script>

<div class="local_ov01 local_ov fixed">
    <?php echo $listall; ?>
    <h1 style="border:0;padding:5px 0;margin:0;">주문내역</h1>
    <span class="btn_ov01" style="display: none"><span class="ov_txt">전체 주문내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
    <?php if($od_status == '준비' && $total_count > 0) { ?>
    <a href="./orderdelivery.php" id="order_delivery" class="ov_a">엑셀배송처리</a>
    <?php } ?>
    <div class="right">
        <button id="select_important">선택 계산서 발행 확인</button>
        <button id="deselect_important">선택 계산서 발행 해지</button>
        <!-- <button id="gdexcel"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_img_ex.gif">경동엑셀</button> -->
        <!-- <button id="dzexcel"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_img_ex.gif">더존엑셀</button> -->
        <!-- <button id="handsabang" onClick="sanbang_order_send()">사방넷수동가져오기</button> -->
		<!-- <button id="list_matching_cancel">매칭데이터취소</button> -->
        <button id="delivery_edi_return_all">송장리턴</button>
        <button class="orderExcel" data-type="1"><img src="/adm/shop_admin/img/btn_img_ex.gif">주문 엑셀 다운로드</button>
        <button class="orderExcel" data-type="2"><img src="/adm/shop_admin/img/btn_img_ex.gif">출고 엑셀 다운로드</button>
    </div>
</div>

<form name="frmsamhwaorderlist" id="frmsamhwaorderlist">
    <div class="new_form">
        <table class="new_form_table" id="search_detail_table">
            <tr>
                <th>날짜</th>
                <td class="date">
                    <select name="sel_date_field" id="sel_date_field">
                        <option value="od_time" <?php echo get_selected($sel_date_field, 'od_time'); ?>>주문일</option>
                        <option value="od_receipt_time" <?php echo get_selected($sel_date_field, 'od_receipt_time'); ?>>입금일</option>
                        <option value="od_ex_date" <?php echo get_selected($sel_date_field, 'od_ex_date'); ?>>희방출고일</option>
                    </select>
                    <div class="sch_last">
                        <input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton" />
                        <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton" />
                        <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton" />
                        <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton" />
                        <input type="button" value="전체" id="select_date_all" name="select_date" class="select_date newbutton" />
                        <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
                        <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
                    </div>
                </td>
            </tr>
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
    <!--
            <tr>
                <th>주문경로</th>
                <td>
                    <input type="checkbox" name="od_openmarket[]" value="" id="od_openmarket_0" <?php echo $od_openmarket && count($od_openmarket) >= 7 ? 'checked' : '' ?>>
                    <label for="od_openmarket_0">전체</label>
                    <input type="checkbox" name="od_openmarket[]" value="my" id="od_openmarket_1" <?php echo option_array_checked('my', $od_openmarket); ?>>
                    <label for="od_openmarket_1">내사이트</label>
                    <input type="checkbox" name="od_openmarket[]" value="11번가" id="od_openmarket_2" <?php echo option_array_checked('11번가', $od_openmarket); ?>>
                    <label for="od_openmarket_2">11번가</label>
                    <input type="checkbox" name="od_openmarket[]" value="인터파크" id="od_openmarket_3" <?php echo option_array_checked('인터파크', $od_openmarket); ?>>
                    <label for="od_openmarket_3">인터파크</label>
                    <input type="checkbox" name="od_openmarket[]" value="스마트스토어" id="od_openmarket_4" <?php echo option_array_checked('스마트스토어', $od_openmarket); ?>>
                    <label for="od_openmarket_4">스마트스토어</label>
                    <input type="checkbox" name="od_openmarket[]" value="ESM옥션" id="od_openmarket_5" <?php echo option_array_checked('ESM옥션', $od_openmarket); ?>>
                    <label for="od_openmarket_5">ESM옥션</label>
                    <input type="checkbox" name="od_openmarket[]" value="ESM지마켓" id="od_openmarket_6" <?php echo option_array_checked('ESM지마켓', $od_openmarket); ?>>
                    <label for="od_openmarket_6">ESM지마켓</label>
                    <input type="checkbox" name="od_openmarket[]" value="오너클랜" id="od_openmarket_7" <?php echo option_array_checked('오너클랜', $od_openmarket); ?>>
                    <label for="od_openmarket_7">오너클랜</label>
                </td>
            </tr>
-->
            <tr>
                <th>기타선택</th>
                <td>
                    <input type="checkbox" name="od_misu" value="Y" id="od_misu01" <?php echo get_checked($od_misu, 'Y'); ?>>
                    <label for="od_misu01">미수금</label>
                    <input type="checkbox" name="od_cancel_price" value="Y" id="od_misu02" <?php echo get_checked($od_cancel_price, 'Y'); ?>>
                    <label for="od_misu02">반품,품절</label>
                    <input type="checkbox" name="od_refund_price" value="Y" id="od_misu03" <?php echo get_checked($od_refund_price, 'Y'); ?>>
                    <label for="od_misu03">환불</label>
                    <input type="checkbox" name="od_receipt_point" value="Y" id="od_misu04" <?php echo get_checked($od_receipt_point, 'Y'); ?>>
                    <label for="od_misu04">포인트주문</label>
                    <input type="checkbox" name="od_coupon" value="Y" id="od_misu05" <?php echo get_checked($od_coupon, 'Y'); ?>>
                    <label for="od_misu05">쿠폰</label>
                    <?php if($default['de_escrow_use']) { ?>
                    <input type="checkbox" name="od_escrow" value="Y" id="od_misu06" <?php echo get_checked($od_escrow, 'Y'); ?>>
                    <label for="od_misu06">에스크로</label>
                    <?php } ?>
                    <div class="linear">
                        <span class="linear_span">주문방법</span>
                        <input type="radio" id="add_admin_all" name="add_admin" value="" <?php echo option_array_checked('', $add_admin); ?>><label for="add_admin_all"> 전체</label>
                        <input type="radio" id="add_admin_0" name="add_admin" value="0" <?php echo option_array_checked('0', $add_admin); ?>><label for="add_admin_0"> 일반주문</label>
                        <input type="radio" id="add_admin_1" name="add_admin" value="1" <?php echo option_array_checked('1', $add_admin); ?>><label for="add_admin_1"> 관리자주문</label>
				    </div>
                </td>
            </tr>
            <?php
            $member_type_flag = ($member_type_s && count($member_type_s) >= 1);
            $member_level_flag = ($member_level_s && count($member_level_s) >= 2);
            $is_member_flag = ($is_member_s && count($is_member_s) >= 2);
            ?>
            <tr>
                <th>등급</th>
                <td>
                    <input type="checkbox" name="" value="" id="member_grade" class="member_grade" <?php echo ($member_type_flag && $member_level_flag && $is_member_flag) ? 'checked' : '' ?>>
                    <label for="member_grade">전체</label>
                    <input type="checkbox" name="member_type_s[]" value="partner" id="member_type_01" class="member_grade" <?php echo option_array_checked('partner', $member_type_s);    ?>>
                    <label for="member_type_01">파트너</label>
                    <input type="checkbox" name="member_level_s[]" value="4" id="member_level_01" class="member_grade" <?php echo option_array_checked('4', $member_level_s);    ?>>
                    <label for="member_level_01">우수사업소</label>
                    <input type="checkbox" name="member_level_s[]" value="3" id="member_level_02" class="member_grade" <?php echo option_array_checked('3', $member_level_s);  ?>>
                    <label for="member_level_02">사업소</label>
                    <input type="checkbox" name="is_member_s[]" value="null" id="is_member_01" class="member_grade" <?php echo option_array_checked('null', $is_member_s);  ?>>
                    <label for="is_member_01">비회원</label>
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
                                    <li><input type="checkbox" name="od_release_manager[]" id="yet_release" value="yet_release" title="" <?php echo option_array_checked('yet_release', $od_release_manager); ?>><label for="yet_release">미지정</label></li>
                                        <li><input type="checkbox" name="od_release_manager[]" id="no_release" value="no_release" title="no_release" <?php echo option_array_checked('no_release', $od_release_manager); ?>><label for="no_release">출고대기</label></li>
                                        <li><input type="checkbox" name="od_release_manager[]" id="out_release" value="-" title="out_release" <?php echo option_array_checked('-', $od_release_manager); ?>><label for="out_release">외부출고</label></li>
                                        <?php
                                        // $sql = "SELECT * FROM g5_auth WHERE au_menu = '400402' AND au_auth LIKE '%w%'";
                                        $sql = '';
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
                    <div class="linear">
                        <span class="linear_span">계산서발행</span>
                        <input type="radio" id="od_important_all" name="od_important" value="" <?php echo option_array_checked('', $od_important); ?>><label for="od_important_all"> 전체</label>
                        <input type="radio" id="od_important_0" name="od_important" value="0" <?php echo option_array_checked('0', $od_important); ?>><label for="od_important_0"> 미발행</label>
                        <input type="radio" id="od_important_1" name="od_important" value="1" <?php echo option_array_checked('1', $od_important); ?>><label for="od_important_1"> 발행</label>
				    	</div>
                    <div class="linear">
                        <span class="linear_span">출고</span>
                        <input type="radio" id="od_release_all" name="od_release" value="" <?php echo option_array_checked('', $od_release); ?>><label for="od_release_all"> 전체</label>
                        <input type="radio" id="od_release_0" name="od_release" value="0" <?php echo option_array_checked('0', $od_release); ?>><label for="od_release_0"> 일반출고</label>
                        <input type="radio" id="od_release_1" name="od_release" value="1" <?php echo option_array_checked('1', $od_release); ?>><label for="od_release_1"> 외부출고</label>
                        <input type="radio" id="od_release_2" name="od_release" value="2" <?php echo option_array_checked('2', $od_release); ?>><label for="od_release_2"> 출고대기</label>
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
                        <option value="it_name" <?php echo $sel_field == 'it_name' ? 'selected="selected"' : ''; ?>>상품명</option>
                        <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
                        <option value="od_naver_orderid" <?php echo get_selected($sel_field, 'od_naver_orderid'); ?>>네이버페이주문번호</option> <!-- wetoz : naverpayorder -->
                        <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
                        <option value="od_tel" <?php echo get_selected($sel_field, 'od_tel'); ?>>주문자전화</option>
                        <option value="od_hp" <?php echo get_selected($sel_field, 'od_hp'); ?>>주문자핸드폰</option>
                        <option value="od_b_tel" <?php echo get_selected($sel_field, 'od_b_tel'); ?>>받는분전화</option>
                        <option value="od_b_hp" <?php echo get_selected($sel_field, 'od_b_hp'); ?>>받는분핸드폰</option>
                        <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
                        <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
                    </select>
                    <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
                    <span class="search_keyworld_msg">
                            *설정을 '전체'로 하고 검색할 경우 결과값 확인까지 오래걸릴 수 있습니다.  
                    </span>
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
$total_balance = number_format($total_result2['od_cart_price'] + $total_result2['od_send_cost'] + $total_result2['od_send_cost2'] - $total_result2['od_cart_discount']  - $total_result2['od_cart_discount2']);

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
        <li class="" data-step="" data-status="">
            <a>전체</a>
        </li>
        <?php
        foreach($order_steps as $order_step) {
            if (!$order_step['orderlist']) continue;
        ?>
            <li class="" data-step="<?php echo $order_step['step']; ?>" data-status="<?php echo $order_step['val']; ?>">
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

<script>

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
        url: "./ajax.orderlist.php",
        data: formdata,
    })
        .done(function(html) {
            // console.log(html)
            if ( page === 1 ) {
                $('#samhwa_order_ajax_list_table').html(html.main);
            }
            $('#samhwa_order_list_table>div.table:first-child tbody').append(html.left);
            $('#samhwa_order_list_table>div.table:last-child tbody').append(html.right);

            if ( !html.left ) {
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
	
	$(document).on("click", ".prodBarNumCntBtn", function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		
		var popupWidth = 700;
		var popupHeight = 700;

		var popupX = (window.screen.width / 2) - (popupWidth / 2);
		var popupY= (window.screen.height / 2) - (popupHeight / 2);
		
		window.open("./popup.prodBarNum.form.php?od_id=" + id, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
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
    $("#gdexcel").click(function(){

        $('.dynamic_od_id').remove();

        var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
        var ret_od_id;

        if ( od_id['od_id[]'] === undefined ) {
            ret_od_id = '';
        }else{
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
    $("#dzexcel").click(function(){

        $('.dynamic_od_id').remove();

        var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
        var ret_od_id;

        if ( od_id['od_id[]'] === undefined ) {
            ret_od_id = '';
        }else{
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
            })
        } else {
            $('input[name="od_settle_case[]"]').each(function (i, v) {
                $(v).prop("checked", false);
            })
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
        })

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
            })
        } else {
            $('input.member_grade').each(function (i, v) {
                $(v).prop("checked", false);
            })
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
        })

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
            })
        } else {
            $('input.od_pay_state').each(function (i, v) {
                $(v).prop("checked", false);
            })
        }
    });

    $('input.od_pay_state').change(function () {
        var all = $('input.od_pay_state').length;
        var count = 0;

        $('input.od_pay_state').each(function (i, v) {
            if ($(v).is(":checked")) {
                count++;
            }
        })

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
        })


        //$('.list_memo_pop').hide();
    });


    // 주문상태 출고 전 전체 선택
    $('#step_all0').change(function () {
        if ($(this).is(":checked")) {
            $('.step_before input[name="od_status[]"]').each(function (i, v) {
                $(v).prop("checked", true);
            })
        } else {
            $('.step_before input[name="od_status[]"]').each(function (i, v) {
                $(v).prop("checked", false);
            })
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
    }, 700);
    <?php }else{ ?>
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
	<script type="text/javascript">
	<!--
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
    <input type="button" value="주문내역 엑셀다운로드" onclick="orderListExcelDownload()" class="btn btn_02">
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
	function orderListExcelDownload(){
		$("#excelForm").remove();
		
		var html = "<form id='excelForm' method='post' action='./order.excel.list.php'>";
		
		var od_id = [];
		var item = $("input[name='od_id[]']:checked");
		
		for(var i = 0; i < item.length; i++){
			od_id.push($(item[i]).val());
			
			html += "<input type='hidden' name='od_id[]' value='" + $(item[i]).val() + "'>";
		}
		
		html += "</form>";
		
		if(!od_id.length){
			alert("선택된 주문내역이 존재하지 않습니다.");
			return false;
		}
		
		$("body").append(html);
		$("#excelForm").submit();
	}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
