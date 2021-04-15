<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

// 유효성 체크
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

// 캐시 방지
header("Progma:no-cache");
header("Cache-Control: no-store, no-cache ,must-revalidate");

$g5['title'] = "주문 내역 복사";

$od_id_new = get_uniqid(); // 오더 번호 생성
$so_nb = get_uniqid_so_nb();

$current_time = G5_TIME_YMDHIS;

/**
 * 매출 증빙 테이블 복사
 *
 * 수정항목
 * od_id 새로운 주문 번호
 */

$sql = "INSERT INTO g5_shop_order_typereceipt
        (od_id,
        ot_typereceipt_cate,
        ot_typereceipt,
        ot_typereceipt_cuse,
        ot_bname,
        ot_boss_name,
        ot_btel,
        ot_bnum,
        ot_buptae,
        ot_bupjong,
        ot_tax_email,
        ot_manager_name,
        ot_state,
        ot_time_date,
        ot_time_hour,
        ot_confirm_number,
        ot_etc,
        ot_location_zip1,
        ot_location_zip2,
        ot_location_addr1,
        ot_location_addr2,
        ot_location_addr3,
        ot_location_jibeon)
        SELECT
            '{$od_id_new}',
            ot_typereceipt_cate,
            ot_typereceipt,
            ot_typereceipt_cuse,
            ot_bname,
            ot_boss_name,
            ot_btel,
            ot_bnum,
            ot_buptae,
            ot_bupjong,
            ot_tax_email,
            ot_manager_name,
            ot_state,
            ot_time_date,
            ot_time_hour,
            ot_confirm_number,
            ot_etc,
            ot_location_zip1,
            ot_location_zip2,
            ot_location_addr1,
            ot_location_addr2,
            ot_location_addr3,
            ot_location_jibeon
        FROM g5_shop_order_typereceipt
        WHERE od_id = '{$od_id}'
        ";

sql_query($sql);

/**
 * 주문 커스텀 테이블 복사
 *
 * 수정항목
 * od_id 새로운 주문 번호
 */

$sql = "INSERT INTO g5_shop_order_custom
        (odc_no,
        od_id,
        it_id,
        cs_type,
        size_use,
        size_width,
        size_height,
        frame_use,
        frame_standard,
        frame_color,
        frame_front,
        frame_front_transparent_acrylic,
        frame_front_optical_scatter,
        frame_back,
        frame_back_transparent_acrylic,
        frame_back_mdf,
        frame_back_formax,
        lightpanel_use,
        lightpanel_led_direction,
        lightpanel_led_qty,
        lightpanel_smps,
        lightpanel_power_line,
        lightpanel_led_ea,
        lightpanel_led_k,
        lightpanel_power_line_dc,
        lightpanel_power_line_wire,
        lightpanel_laser,
        lightpanel_switch_use,
        lightpanel_switch_explain,
        lightpanel_switch,
        holder_use,
        holder_class,
        holder_pipe_interval_1,
        holder_pipe_interval_2,
        holder_pipe_interval_3,
        holder_pipe_length,
        printout_use,
        printout_printout,
        content_use,
        content_common,
        content_minart,
        content_selmartec,
        content_lp)
        SELECT
            0,
            '{$od_id_new}',
            it_id,
            cs_type,
            size_use,
            size_width,
            size_height,
            frame_use,
            frame_standard,
            frame_color,
            frame_front,
            frame_front_transparent_acrylic,
            frame_front_optical_scatter,
            frame_back,
            frame_back_transparent_acrylic,
            frame_back_mdf,
            frame_back_formax,
            lightpanel_use,
            lightpanel_led_direction,
            lightpanel_led_qty,
            lightpanel_smps,
            lightpanel_power_line,
            lightpanel_led_ea,
            lightpanel_led_k,
            lightpanel_power_line_dc,
            lightpanel_power_line_wire,
            lightpanel_laser,
            lightpanel_switch_use,
            lightpanel_switch_explain,
            lightpanel_switch,
            holder_use,
            holder_class,
            holder_pipe_interval_1,
            holder_pipe_interval_2,
            holder_pipe_interval_3,
            holder_pipe_length,
            printout_use,
            printout_printout,
            content_use,
            content_common,
            content_minart,
            content_selmartec,
            content_lp
        FROM g5_shop_order_custom
        WHERE od_id = '{$od_id}'
        ";

sql_query($sql);

/**
 * 카트 테이블 복사
 *
 * 수정항목
 * od_id 새로운 주문 번호
 * ct_time, ct_select_time 현재시간
 *
 * 제외항목
 * pt_datetime 제외 (택배 완료 시간인듯?)
 */

$sql = "INSERT INTO {$g5['g5_shop_cart_table']}
        (ct_id,
        od_id,
        mb_id,
        it_id,
        it_name,
        it_sc_type,
        it_sc_method,
        it_sc_price,
        it_sc_minimum,
        it_sc_qty,
        ct_status,
        ct_history,
        ct_price,
        ct_discount,
        ct_point,
        cp_price,
        ct_point_use,
        ct_stock_use,
        ct_option,
        ct_qty,
        ct_notax,
        io_id,
        io_type,
        io_price,
        ct_time,
        ct_ip,
        ct_send_cost,
        ct_sendcost,
        ct_direct,
        ct_select,
        ct_select_time,
        pt_sale,
        pt_commission,
        pt_point,
        pt_incentive,
        pt_net,
        pt_commission_rate,
        pt_incentive_rate,
        pt_it,
        pt_id,
        pt_send,
        pt_send_num,
        pt_msg1,
        pt_msg2,
        pt_msg3,
        mk_id,
        mk_profit,
        mk_benefit,
        mk_profit_rate,
        mk_benefit_rate,
        ct_memo,
        ProductOrderID,
        ClaimType,
        ClaimStatus,
        PlaceOrderStatus,
        DelayedDispatchReason,
        od_naver_orderid,
        ct_uid,
        io_thezone)
        SELECT
            0,
            '{$od_id_new}',
            mb_id,
            it_id,
            it_name,
            it_sc_type,
            it_sc_method,
            it_sc_price,
            it_sc_minimum,
            it_sc_qty,
            ct_status,
            ct_history,
            ct_price,
            ct_discount,
            ct_point,
            cp_price,
            ct_point_use,
            ct_stock_use,
            ct_option,
            ct_qty,
            ct_notax,
            io_id,
            io_type,
            io_price,
            '{$current_time}',
            ct_ip,
            ct_send_cost,
            ct_sendcost,
            ct_direct,
            ct_select,
            '{$current_time}',
            pt_sale,
            pt_commission,
            pt_point,
            pt_incentive,
            pt_net,
            pt_commission_rate,
            pt_incentive_rate,
            pt_it,
            pt_id,
            pt_send,
            pt_send_num,
            pt_msg1,
            pt_msg2,
            pt_msg3,
            mk_id,
            mk_profit,
            mk_benefit,
            mk_profit_rate,
            mk_benefit_rate,
            ct_memo,
            ProductOrderID,
            ClaimType,
            ClaimStatus,
            PlaceOrderStatus,
            DelayedDispatchReason,
            od_naver_orderid,
            ct_uid,
            io_thezone
        FROM {$g5['g5_shop_cart_table']}
        WHERE od_id = '{$od_id}'
        ";
sql_query($sql);


/**
 * 주문 테이블 복사
 *
 * 수정항목
 * od_id 새로운 주문번호로 수정
 * od_time 현재날짜로 수정
 * od_status 작성으로 변경
 *
 * 제외 항목
 * od_hope_date 희망 배송일 제외
 * od_ex_date 희망 출고일 제외
 * od_edi_date 제외
 * od_release_date 제외
 * od_receipt_time 결제일시 제외
 * od_invoice 운송장 제외
 * od_invoice_time 배송일자 제외
 * od_pay_time_type 제외
 * od_pay_state 제외
 * od_cancel_time 제외
 * od_cancel_reason 제외
 * od_cancel_memo 제외
 * od_cancel_receive_admin 제외
 * od_cancel_inspection_status 제외
 * od_cancel_inspection_price 제외
 * od_cancel_inspection_memo 제외
 * od_cancel_inspection_admin 제외
 * od_cancel_inspection_time 제외
 * od_refund_type 제외
 * od_refund_admin 제외
 * od_refund_time 제외
 * od_naver_orderid 제외
 * od_naver_sync_time 제외
 */

$sql = "INSERT INTO {$g5['g5_shop_order_table']}
        (od_id,
        mb_id,
        od_name,
        od_email,
        od_tel,
        od_hp,
        od_zip1,
        od_zip2,
        od_addr1,
        od_addr2,
        od_addr3,
        od_addr_jibeon,
        od_deposit_name,
        od_b_name,
        od_b_tel,
        od_b_hp,
        od_b_zip1,
        od_b_zip2,
        od_b_addr1,
        od_b_addr2,
        od_b_addr3,
        od_b_addr_jibeon,
        od_memo,
        od_cart_count,
        od_cart_price,
        od_cart_discount,
        od_cart_discount2,
        od_cart_coupon,
        od_send_cost,
        od_send_cost2,
        od_send_coupon,
        od_receipt_price,
        od_cancel_price,
        od_receipt_point,
        od_refund_price,
        od_bank_account,
        od_coupon,
        od_misu,
        od_shop_memo,
        od_mod_history,
        od_status,
        od_settle_case,
        od_test,
        od_mobile,
        od_pg,
        od_tno,
        od_app_no,
        od_escrow,
        od_casseqno,
        od_tax_flag,
        od_tax_mny,
        od_vat_mny,
        od_free_mny,
        od_delivery_company,
        od_cash,
        od_cash_no,
        od_cash_info,
        od_time,
        od_pwd,
        od_ip,
        pt_case,
        pt_price,
        pt_memo,
        od_giup_manager,
        od_sales_manager,
        od_release_manager,
        od_important,
        od_important2,
        od_delivery_type,
        od_send_admin_memo,
        od_delivery_text,
        od_delivery_receiptperson,
        od_delivery_qty,
        od_delivery_price,
        od_edi_result,
        od_edi_msg,
        od_edi_chk,
        od_edi_price,
        od_edi_ea,
        od_pay_memo,
        od_writer,
        od_add_admin,
        so_nb)
        SELECT
            '{$od_id_new}',
            mb_id,
            od_name,
            od_email,
            od_tel,
            od_hp,
            od_zip1,
            od_zip2,
            od_addr1,
            od_addr2,
            od_addr3,
            od_addr_jibeon,
            od_deposit_name,
            od_b_name,
            od_b_tel,
            od_b_hp,
            od_b_zip1,
            od_b_zip2,
            od_b_addr1,
            od_b_addr2,
            od_b_addr3,
            od_b_addr_jibeon,
            od_memo,
            od_cart_count,
            od_cart_price,
            od_cart_discount,
            od_cart_discount2,
            od_cart_coupon,
            od_send_cost,
            od_send_cost2,
            od_send_coupon,
            od_receipt_price,
            od_cancel_price,
            od_receipt_point,
            od_refund_price,
            od_bank_account,
            od_coupon,
            od_misu,
            od_shop_memo,
            od_mod_history,
            '작성',
            od_settle_case,
            od_test,
            od_mobile,
            od_pg,
            od_tno,
            od_app_no,
            od_escrow,
            od_casseqno,
            od_tax_flag,
            od_tax_mny,
            od_vat_mny,
            od_free_mny,
            od_delivery_company,
            od_cash,
            od_cash_no,
            od_cash_info,
            '{$current_time}',
            od_pwd,
            od_ip,
            pt_case,
            pt_price,
            pt_memo,
            od_giup_manager,
            od_sales_manager,
            od_release_manager,
            od_important,
            od_important2,
            od_delivery_type,
            od_send_admin_memo,
            od_delivery_text,
            od_delivery_receiptperson,
            od_delivery_qty,
            od_delivery_price,
            od_edi_result,
            od_edi_msg,
            od_edi_chk,
            od_edi_price,
            od_edi_ea,
            od_pay_memo,
            od_writer,
            od_add_admin,
            '{$so_nb}'
        FROM {$g5['g5_shop_order_table']}
        WHERE od_id = '{$od_id}'
        ";

sql_query($sql);

// 로그 기록
set_order_admin_log($od_id_new, "{$od_id} 주문서 복사");

?>
<script>
    alert('주문서가 복사되었습니다.');
    location.href="./samhwa_orderform.php?od_id=<?php echo $od_id_new; ?>";
</script>