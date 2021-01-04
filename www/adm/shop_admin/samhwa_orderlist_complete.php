<?php
$sub_menu = '400401';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '주문내역';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql_common = " from {$g5['g5_shop_order_table']} ";

$sql = " select count(od_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 주문삭제 히스토리 테이블 필드 추가
if(!sql_query(" select mb_id from {$g5['g5_shop_order_delete_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_delete_table']}`
                    ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `de_data`,
                    ADD `de_ip` varchar(255) NOT NULL DEFAULT '' AFTER `mb_id`,
                    ADD `de_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `de_ip` ", true);
}

if( function_exists('pg_setting_check') ){
	pg_setting_check(true);
}
?>

<script src="<?php echo G5_ADMIN_URL; ?>/shop_admin/js/orderlist.js?ver=<?php echo time(); ?>"></script>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 주문내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
    <?php if($od_status == '준비' && $total_count > 0) { ?>
    <a href="./orderdelivery.php" id="order_delivery" class="ov_a">엑셀배송처리</a>
    <?php } ?>
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
                <th>결제금액</th>
                <td>
                    <input type="checkbox" name="price" value="1" id="search_won"><label for="search_won">&nbsp;</label>
                    <input type="text" name="price_s" value="" class="line" maxlength="10" style="width:80px">
                    원 ~
                    <input type="text" name="price_e" value="" class="line" maxlength="10" style="width:80px">
                    원
                    <!--
                    <div class="linear">
                        <span>결제후 출고</span>
                        <input type="checkbox" id="cs_settle_y" name="cs_settle[]" value="Y"> <label for="cs_settle_y" class="">결제</label>
                        <input type="checkbox" id="cs_settle_n" name="cs_settle[]" value="N"> <label for="cs_settle_n" class="">미결제</label>
                        <input type="checkbox" id="cs_settle_s" name="cs_settle[]" value="S"> <label for="cs_settle_s" class="">결제후출고</label>
                    </div>
                    -->
                </td>
            </tr>
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
                                            <li><input type="checkbox" name="od_sales_manager[]" id="od_sales_manager_<?php echo $a_mb['mb_id']; ?>" value="<?php echo $a_mb['mb_id']; ?>" title="<?php echo $a_mb['mb_id']; ?>" placeholder="<?php echo $a_mb['mb_id']; ?>"><label for="od_sales_manager_<?php echo $a_mb['mb_id']; ?>"><?php echo $a_mb['mb_name']; ?></label></li>
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
                                        <?php
                                        $sql = "SELECT * FROM g5_auth WHERE au_menu = '400402' AND au_auth LIKE '%w%'";
                                        $auth_result = sql_query($sql);
                                        while($a_row = sql_fetch_array($auth_result)) {
                                            $a_mb = get_member($a_row['mb_id']);
                                        ?>
                                            <li><input type="checkbox" name="od_release_manager[]" id="od_release_manager_<?php echo $a_mb['mb_id']; ?>" value="<?php echo $a_mb['mb_id']; ?>" title="<?php echo $a_mb['mb_id']; ?>" placeholder="<?php echo $a_mb['mb_id']; ?>"><label for="od_release_manager_<?php echo $a_mb['mb_id']; ?>"><?php echo $a_mb['mb_name']; ?></label></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>검색어</th>
                <td>
                    <select name="sel_field" id="sel_field">
                        <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
                        <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
                        <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
                        <option value="od_tel" <?php echo get_selected($sel_field, 'od_tel'); ?>>주문자전화</option>
                        <option value="od_hp" <?php echo get_selected($sel_field, 'od_hp'); ?>>주문자핸드폰</option>
                        <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>받는분</option>
                        <option value="od_b_tel" <?php echo get_selected($sel_field, 'od_b_tel'); ?>>받는분전화</option>
                        <option value="od_b_hp" <?php echo get_selected($sel_field, 'od_b_hp'); ?>>받는분핸드폰</option>
                        <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
                        <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
                    </select>
                    <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
                    <span class="search_keyworld_msg">
                            *주문번호, 회원아이디, 주문자, 주문자번호, 받는분, 받는분연락처, 입금자, 운송장번호로 검색이 가능합니다.
                    </span>
                </td>
            </tr>
        </table>
        <div class="submit">
            <button type="submit"><span>검색</span></button>
            <div class="buttons">
                <button type="button" id="set_default_setting_button" title="기본검색설정" class="ml25">기본검색설정</button>
                <button type="button" id="set_default_apply_button" title="기본검색적용">기본검색적용</button>
                <button type="button" id="search_reset_button" title="검색초기화">검색초기화</button>
            </div>
	    </div>
    </div>
</form>
<form name="forderlist" id="forderlist" method="post" autocomplete="off">
<input type="hidden" name="search_od_status" value="<?php echo $od_status; ?>">

<div id="samhwa_order_list">
    <ul class="order_tab">
        <?php
        foreach($order_steps as $order_step) { 
            if (!$order_step['orderlist_complete']) continue;
        ?>
            <li class="" data-step="<?php echo $order_step['step']; ?>" data-status="<?php echo $order_step['val']; ?>">
                <a><?php echo $order_step['name']; ?>(<span>0</span>)</a>
            </li>
        <?php } ?>
    </ul>
    <div id="samhwa_order_ajax_list_table">
    </div>
</div>

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

var od_status = '주문';
var od_step = 0;
var page = 1;
var loading = false;
var end = false;
var sub_menu = '<?php echo $sub_menu; ?>';

$( document ).ready(function() {
    function doSearch() {
        if ( loading === true ) return;
        if ( end === true ) return;

        var formdata = $.extend({}, $('#frmsamhwaorderlist').serializeObject(), { 
            od_status: od_status, 
            od_step: od_step, 
            page: page, 
            sub_menu: sub_menu 
        });
        loading = true;

        // form object rename
        formdata['od_settle_case'] = formdata['od_settle_case[]']; // Assign new key
        delete formdata['od_settle_case[]']; // Delete old key

        var ajax = $.ajax({
                        method: "POST",
                        url: "./ajax.orderlist.php",
                        data: formdata,
                    })
            .done(function(html) {
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
                page++;
            })
            .fail(function() {
                console.log("ajax error");
            })
            .always(function() {
                loading = false;
            });
    }
    var submitAction = function(e) {
        e.preventDefault();
        e.stopPropagation();
        /* do something with Error */
        page = 1;
        end = false;
        doSearch();
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
        doSearch();
    });

    $('#samhwa_order_list .order_tab li:eq(0)').click();

    $(window).scroll(function() {
        if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            doSearch();
        }
    });

    if ( $('#samhwa_order_list') ) {
        if ( $('#samhwa_order_list').width() % 2 ) {
            $('#samhwa_order_list').width( $('#samhwa_order_list').width() - 1 + 'px');
        }
    }


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
});
</script>
<div class="btn_fixed_top">
    <a href="./samhwa_order_new.php" id="order_add" class="btn btn_01">주문서 추가</a>
</div>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
