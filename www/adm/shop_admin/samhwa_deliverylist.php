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
    <?php if($od_status == '준비' && $total_count > 0) { ?>
    <a href="./orderdelivery.php" id="order_delivery" class="ov_a">엑셀배송처리</a>
    <?php } ?>
    <div class="right">
        <button id="deliveryExcelDownloadBtn">주문다운로드</button>
        <button id="deliveryExcelDownloadBtn2">배송업로드 다운받기</button>
        <button id="deliveryExcelUploadBtn">배송정보 일괄 업로드</button>
        <button id="delivery_edi_send_all">로젠 EDI 일괄 전송</button>
        <button id="delivery_edi_return_all">송장리턴</button>
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
                                        $sql = "SELECT * FROM g5_auth WHERE au_menu = '400402' AND au_auth LIKE '%w%'";
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
                        <input type="radio" id="od_release_2" name="od_release" value="2" <?php echo option_array_checked('2', $od_release); ?>><label for="od_release_2"> 출고아님</label>
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

<script>

var od_status = '주문';
var od_step = 0;
var page = 1;
var loading = false;
var end = false;
var sub_menu = '<?php echo $sub_menu; ?>';
var last_step = '';

$( document ).ready(function() {
	
	$("#deliveryExcelDownloadBtn2").click(function(){
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
	});
	
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

        window.open("./popup.prodBarNum.form.php?prodId=" + it + "&od_id=" + od + "&stock_insert=" + stock + "&option=" + option, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
    });
	
	$(document).on("click", ".deliveryCntBtn", function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var ct_id = $(this).attr("data-ct");
		
		var popupWidth = 700;
		var popupHeight = 700;

		var popupX = (window.screen.width / 2) - (popupWidth / 2);
		var popupY= (window.screen.height / 2) - (popupHeight / 2);
		
        //아래로하면 cart기준으로 바꿈(상품하나씩)
		// window.open("./popup.prodDeliveryInfo.form2.php?od_id=" + id +"&ct_id="+ct_id, "배송정보", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
		window.open("./popup.prodDeliveryInfo.form.php?od_id=" + id +"&ct_id="+ct_id, "배송정보", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
	});

    function doSearch() {
        // alert(od_status);
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
        console.log(formdata);
        var ajax = $.ajax({
                        method: "POST",
                        url: "./ajax.deliverylist.php",
                        data: formdata,
                    })
            .done(function(html) {
                if ( page === 1 ) {
                    $('#samhwa_order_ajax_list_table').html(html.main);
                }
                $('#samhwa_order_list_table>div.table tbody').append(html.data);
                // $(".od_release_date").datepicker(
                //     { 
                //         changeMonth: true, 
                //         changeYear: true, 
                //         dateFormat: "yy-mm-dd", 
                //         showButtonPanel: true, 
                //         yearRange: "c-99:c+99", 
                //         maxDate: "+365d",
                //         onSelect: function(od_release_date, inst) {
                //             var od_id = $(this).data('od-id');
                //             $.ajax({
                //                 method: "POST",
                //                 url: "./ajax.order.delivery.change_delivery_time.php",
                //                 data: {
                //                     od_release_date: od_release_date,
                //                     od_id: od_id,
                //                 },
                //             }).done(function(data) {
                //                 if ( data.msg ) {
                //                     alert(data.msg);
                //                 }
                //             });
                //         }
                //     }
                // );
                $(".ct_ex_date").datepicker(
                    { 
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
                    }
                );

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
    })

    
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
	
	/* 210226 주문다운로드 */
	$("#deliveryExcelDownloadBtn").click(function(){
		$("#excelForm").remove();
		
		var html = "<form id='excelForm' method='post' action='./order.delivery.excel.list.php'>";
		
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
	});
	
	$("#deliveryExcelUploadBtn").click(function(){
		var opt = "width=600,height=450,left=10,top=10";
		window.open("./deliveryexcel.php", "win_excel", opt);
		return false;
	});
	
});

//출고담당자
$(document).on("change", ".ct_manager", function(e){
    if(confirm('출고담당자를 변경하시겠습니까?')){

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
            }else{
                alert('실패하였습니다.');
            }
        });
    }else{
        // window.location.reload(); 
    }
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
