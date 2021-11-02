<?php
$sub_menu = '400403';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '반품및 취소 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
alert("준비중입니다.",G5_URL."/adm/shop_admin/samhwa_orderlist.php");
$where = array();

$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('od_id', 'od_cart_price', 'od_receipt_price', 'od_cancel_price', 'od_misu', 'od_cash')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_field = get_search_string($sel_field);
if( !in_array($sel_field, array('od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'od_invoice')) ){   //검색할 필드 대상이 아니면 값을 제거
    $sel_field = '';
}
$od_status = get_search_string($od_status);
$search = get_search_string($search);
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$od_misu = preg_replace('/[^0-9a-z]/i', '', $od_misu);
$od_cancel_price = preg_replace('/[^0-9a-z]/i', '', $od_cancel_price);
$od_refund_price = preg_replace('/[^0-9a-z]/i', '', $od_refund_price);
$od_receipt_point = preg_replace('/[^0-9a-z]/i', '', $od_receipt_point);
$od_coupon = preg_replace('/[^0-9a-z]/i', '', $od_coupon); 

$sql_search = "";
if ($search != "") {
    if ($sel_field != "") {
        $where[] = " $sel_field like '%$search%' ";
    }

    if ($save_search != $search) {
        $page = 1;
    }
}

if ($od_status) {
    switch($od_status) {
        case '전체취소':
            $where[] = " od_status = '취소' ";
            break;
        case '부분취소':
            $where[] = " od_status IN('주문', '입금', '준비', '배송', '완료') and od_cancel_price > 0 ";
            break;
        default:
            $where[] = " od_status = '$od_status' ";
            break;
    }

    switch ($od_status) {
        case '주문' :
            $sort1 = "od_id";
            $sort2 = "desc";
            break;
        case '입금' :   // 결제완료
            $sort1 = "od_receipt_time";
            $sort2 = "desc";
            break;
        case '배송' :   // 배송중
            $sort1 = "od_invoice_time";
            $sort2 = "desc";
            break;
    }
}

if ($od_settle_case) {
    $where[] = " od_settle_case = '$od_settle_case' ";
}

if ($od_misu) {
    $where[] = " od_misu != 0 ";
}

if ($od_cancel_price) {
    $where[] = " od_cancel_price != 0 ";
}

if ($od_refund_price) {
    $where[] = " od_refund_price != 0 ";
}

if ($od_receipt_point) {
    $where[] = " od_receipt_point != 0 ";
}

if ($od_coupon) {
    $where[] = " ( od_cart_coupon > 0 or od_coupon > 0 or od_send_coupon > 0 ) ";
}

if ($od_escrow) {
    $where[] = " od_escrow = 1 ";
}

if ($fr_date && $to_date) {
    $where[] = " od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

$where[] = " ( od_status = '입고대기' OR od_status = '입고확인' OR od_status = '검수확인' OR od_status = '환불완료' ) ";

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from {$g5['g5_shop_order_table']} $sql_search ";

$sql = " select count(od_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *,
            (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
           $sql_common
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "od_status=".urlencode($od_status)."&amp;od_settle_case=".urlencode($od_settle_case)."&amp;od_misu=$od_misu&amp;od_cancel_price=$od_cancel_price&amp;od_refund_price=$od_refund_price&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if($default['de_escrow_use'])
    $qstr1 .= "&amp;od_escrow=$od_escrow";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

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
    <span class="btn_ov01"><span class="ov_txt">전체 반품및 취소</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
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
                <button type="button" id="set_default_apply_button"title="기본검색적용">기본검색적용</button>
                <button type="button" id="search_reset_button" title="검색초기화">검색초기화</button>
            </div>
	    </div>
    </div>
</form>
<form name="forderlist" id="forderlist" method="post" autocomplete="off">
<input type="hidden" name="search_od_status" value="<?php echo $od_status; ?>">

<div id="samhwa_order_list">
    <ul class="order_tab">
        <li class="" data-step="" data-status="">
            <a>전체</a>
        </li>
        <?php
        foreach($order_steps as $order_step) { 
            if (!$order_step['cancellist']) continue;
        ?>
            <li class="" data-step="<?php echo $order_step['step']; ?>" data-status="<?php echo $order_step['val']; ?>">
                <a><?php echo $order_step['name']; ?>(<span>0</span>)</a>
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

<script>

var od_status = '주문';
var od_step = 0;
var page = 1;
var loading = false;
var end = false;
var sub_menu = '<?php echo $sub_menu; ?>';
var last_step = '';

$( document ).ready(function() {

    function doSearch() {
        if ( loading === true ) return;
        if ( end === true ) return;

        var formdata = $.extend({}, $('#frmsamhwaorderlist').serializeObject(), { 
            od_status: od_status, 
            od_step: od_step, 
            page: page, 
            sub_menu: sub_menu,
            last_step: last_step, 
        });
        loading = true;

        var ajax = $.ajax({
                        method: "POST",
                        url: "./ajax.cancellist.php",
                        data: formdata,
                    })
            .done(function(html) {
                if ( page === 1 ) {
                    $('#samhwa_order_ajax_list_table').html(html.main);
                }
                $('#samhwa_order_list_table>div.table tbody').append(html.data);

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

    $('#samhwa_order_list .order_tab li:eq(0)').click();

    $(window).scroll(function() {
        if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            doSearch();
        }
    });

    $('.od_delivery_type_all').click(function() {
        if($(this).is(":checked") == true) {
            $('.od_delivery_type').prop("checked", true);
        }else{
            $('.od_delivery_type').prop("checked", false);
        }
    });

    $(document).on("click", ".od_cancel_receive_update", function() {
        var od_id = $(this).data('od-id');

        $.ajax({
            method: "POST",
            url: "./ajax.orderlist.cancel.receive.php",
            data: {
                od_id: od_id,
            },
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if (data.result === 'success') {
                location.reload();
            }
        });

    });



    // 주문 취소 검수 파일첨부
    $( document ).on( "click", '.g5_shop_order_cancel_inspection_file .uploadbtn', function() {

        var od_id = $(this).data('od-id');

        var $form = $('<form class="hidden_form"></form>');
        $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
        $form.attr('method', 'post');
        //$form.attr('target', 'iFrm');
        $form.appendTo('body');

        var str = $('<input type="file" name="file" class="g5_shop_order_file_cancel_inspection">');
        $form.append(str);
        $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
        $form.append('<input type="hidden" name="type" value="cancel_inspection" />');

        $($form).find('input[type="file"]').click();
    });

    $( document ).on( "change", '.g5_shop_order_file_cancel_inspection', function() {

        var form = $(this).closest('form')[0];

        var form_data = new FormData(form);

        $.ajax({
                type : 'POST',
                enctype: 'multipart/form-data',
                processData : false,
                contentType : false,
                url : "./ajax.order.item.add.cart_file_upload.php",
                data : form_data,
            })
            .done(function(data) {

                if ( data.msg ) {
                    alert(data.msg);
                }

                if ( data.result === 'success' ) {
                    var ret = '';
                    var od_id = '';

                    for(var i=0; i<data.data.length;i++) {
                        ret += '<li>';
                        ret += '<a href="/data/order_cart/' + data.data[i]['file_name'] + '" class="filelink" target="_blank">' + data.data[i]['real_name'] + '</a>&nbsp;';
                        ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
                        ret += '</li>';

                        od_id = data.data[i]['od_id'];
                    }

                    $('.od_cancel_inspection_file_' + od_id).html(ret);
                }
            })

    });

    $( document ).on( "click", '.od_cancel_inspection_file .remove', function() {

        var no = $(this).data('no');
        var obj = $(this);

        var formdata = {
            no: no,
        }
        $.ajax({
            method: "POST",
            url: "./ajax.order.item.add.cart_file_remove.php",
            data: formdata,
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }

            if ( data.result === 'success' ) {
                $(obj).closest('li').remove();
            }
        });

    });

    $(document).on("click", '.od_cancel_inspection_price_all', function() {
        var parent = $(this).closest('td.cancel_receive_container');
        var od_id = $(parent).data('od-id');

        var price = $(this).data('price');
        price = price.replace(/[^0-9]/g,"");
        
        if ( $(this).is(":checked") == true ) {
            $('input[name="od_cancel_inspection_price[' + od_id + ']"]').val(price).attr("readonly",true);
        }else{
            $('input[name="od_cancel_inspection_price[' + od_id + ']"]').val('0').attr("readonly",false).focus();
        }

    });

    // 주문취소 검수 신청 버튼
    $(document).on("click", '.od_cancel_inspection_submit', function() {
        var parent = $(this).closest('td.cancel_receive_container');
        var od_id = $(parent).data('od-id');
        var od_cancel_inspection_status = $('input[name="od_cancel_inspection_status[' + od_id + ']"]:checked').val();
        var od_cancel_inspection_price = $('input[name="od_cancel_inspection_price[' + od_id + ']"]').val();
        var od_cancel_inspection_memo = $('textarea[name="od_cancel_inspection_memo[' + od_id + ']"]').val();

        $.ajax({
                    method: "POST",
                    url: "./ajax.orderlist.cancel.inspection.php",
                    data: {
                        od_id: od_id,
                        od_cancel_inspection_status: od_cancel_inspection_status,
                        od_cancel_inspection_price: od_cancel_inspection_price,
                        od_cancel_inspection_memo: od_cancel_inspection_memo,
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

    // 주문취소 검수 취소 버튼
    $(document).on("click", '.od_cancel_inspection_cancel', function() {
        var parent = $(this).closest('td.cancel_receive_container');
        var od_id = $(parent).data('od-id');

        $.ajax({
                    method: "POST",
                    url: "./ajax.orderlist.cancel.inspection.cancel.php",
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

    // 환불
    $(document).on("click", '.od_cancel_price_to', function() {
        var parent = $(this).closest('td.cancel_receive_container');
        var od_id = $(parent).data('od-id');

        var price = $(this).data('price');
        
        if ( $(this).is(":checked") == true ) {
            $('input[name="od_cancel_price[' + od_id + ']"]').val(price).attr("readonly",true);
        }else{
            $('input[name="od_cancel_price[' + od_id + ']"]').val('0').attr("readonly",false).focus();
        }

    });

    $(document).on("click", '.od_refund_submit', function() {
        var parent = $(this).closest('td.cancel_receive_container');
        var od_id = $(parent).data('od-id');
        var od_refund_type = $('select[name="od_refund_type[' + od_id + ']"]').val();
        var od_cancel_price = $('input[name="od_cancel_price[' + od_id + ']"]').val();

        if (od_refund_type === '') {
            alert('환불방법을 선택해주세요.');
            return;
        }
        if (od_cancel_price === '') {
            alert('환불금액을 입력해주세요.');
            return;
        }

        $.ajax({
                    method: "POST",
                    url: "./ajax.orderlist.cancel.refund.php",
                    data: {
                        od_id: od_id,
                        od_refund_type: od_refund_type,
                        od_cancel_price: od_cancel_price,
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
    
});
</script>
<style>
#samhwa_order_list_table>div.table thead tr.fixed {
    top: 102px !important;
}
</style>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
