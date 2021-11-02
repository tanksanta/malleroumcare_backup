<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');

auth_check($auth[$sub_menu], 'w');

check_admin_token();

$od_id = $_POST['od_id'];

$aor = new NHNAPIORDER();
//$aor->showReq = true;

$arr_it_id = array();

$ct_status = $od_invoice = $od_delivery_company = $od_invoice_time = '';

foreach ($_POST['idx'] as $k => $v) {

    $idx = get_text($v);
    $ProductOrderID = get_text($_POST['ProductOrderID'][$idx]);
    $operation = get_text($_POST['operation'][$idx]);
    $it_id = '';

    if ($operation == 'PlaceProductOrder') { // 발주
        $ct_status = '준비';
        $xml = $aor->PlaceProductOrder($ProductOrderID);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'DelayProductOrder') { // 발송지연
        $ct_status = '준비';
        $DispatchDueDate = get_text($_POST['DispatchDueDate'][$idx]).'T00:00:00';
        $DispatchDelayReasonCode = get_text($_POST['DispatchDelayReasonCode'][$idx]);
        $DispatchDelayDetailReason = get_text($_POST['DispatchDelayDetailReason'][$idx]);
        $xml = $aor->DelayProductOrder($ProductOrderID, $DispatchDueDate, $DispatchDelayReasonCode, $DispatchDelayDetailReason);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'ShipProductOrder') { // 발송
        $ct_status = '배송';
        $DeliveryMethodCode = get_text($_POST['DeliveryMethodCode'][$idx]);
        $DeliveryCompanyCode = get_text($_POST['DeliveryCompanyCode'][$idx]);
        $TrackingNumber = get_text($_POST['TrackingNumber'][$idx]);
        $DispatchDate = get_text($_POST['DispatchDate'][$idx]);
        $DispatchHour = get_text($_POST['DispatchHour'][$idx]);
        if ($DispatchDate) {
            $DispatchDate = $DispatchDate.'T'.$DispatchHour.':00:00';
        }
        $xml = $aor->ShipProductOrder($ProductOrderID, $DeliveryMethodCode, $DeliveryCompanyCode, $TrackingNumber, $DispatchDate);

        if (!$od_invoice) { // 배송정보
            $od_invoice = $TrackingNumber;
            $od_delivery_company = $DeliveryCompanyCode;
            $od_invoice_time = str_replace('T', ' ', $DispatchDate);
        }
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'CancelSale') { // 판매취소
        $ct_status = '취소';
        $CancelReasonCode = get_text($_POST['CancelReasonCode'][$idx]);
        $xml = $aor->CancelSale($ProductOrderID, $CancelReasonCode);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'ApproveCancelApplication') { // 취소승인
        $ct_status = '취소';
        $EtcFeeDemandAmount = (int)($_POST['EtcFeeDemandAmount'][$idx]);
        $Memo = get_text($_POST['Memo'][$idx]);
        $xml = $aor->ApproveCancelApplication($ProductOrderID, $EtcFeeDemandAmount, $Memo);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'RequestReturn') { // 반품접수
        $ct_status = '반품';
        $ReturnReasonCode = get_text($_POST['ReturnReasonCode'][$idx]);
        $CollectDeliveryMethodCode = get_text($_POST['CollectDeliveryMethodCode'][$idx]);
        $CollectDeliveryCompanyCode = get_text($_POST['CollectDeliveryCompanyCode'][$idx]);
        $CollectTrackingNumber = get_text($_POST['CollectTrackingNumber'][$idx]);
        $xml = $aor->RequestReturn($ProductOrderID, $ReturnReasonCode, $CollectDeliveryMethodCode, $CollectDeliveryCompanyCode, $CollectTrackingNumber);

        if (!$od_invoice) { // 배송정보
            $od_invoice = $CollectTrackingNumber;
            $od_delivery_company = $CollectDeliveryCompanyCode;
        }
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'ApproveReturnApplication') { // 반품승인
        $ct_status = '반품';
        $EtcFeeDemandAmount = (int)($_POST['EtcFeeDemandAmount'][$idx]);
        $Memo = get_text($_POST['Memo'][$idx]);
        $xml = $aor->ApproveReturnApplication($ProductOrderID, $EtcFeeDemandAmount, $Memo);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'RejectReturn') { // 반품거부
        $ct_status = '반품거부';
        $RejectDetailContent = get_text($_POST['RejectDetailContent'][$idx]);
        $xml = $aor->RejectReturn($ProductOrderID, $RejectDetailContent);
    }
    else if ($operation == 'WithholdReturn') { // 반품보류
        $ct_status = '배송';
        $ReturnHoldCode = get_text($_POST['ReturnHoldCode'][$idx]);
        $ReturnHoldDetailContent = get_text($_POST['ReturnHoldDetailContent'][$idx]);
        $EtcFeeDemandAmount = (int)($_POST['EtcFeeDemandAmount'][$idx]);
        $xml = $aor->WithholdReturn($ProductOrderID, $ReturnHoldCode, $ReturnHoldDetailContent, $EtcFeeDemandAmount);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'ReleaseReturnHold') { // 반품보류해제
        $ct_status = '반품보류';
        $xml = $aor->ReleaseReturnHold($ProductOrderID);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'ApproveCollectedExchange') { // 교환수거완료
        $ct_status = '교환';
        $xml = $aor->ApproveCollectedExchange($ProductOrderID);
    }
    else if ($operation == 'ReDeliveryExchange') { // 교환재배송
        $ct_status = '교환';
        $ReDeliveryMethodCode = get_text($_POST['ReDeliveryMethodCode'][$idx]);
        $ReDeliveryCompanyCode = get_text($_POST['ReDeliveryCompanyCode'][$idx]);
        $ReDeliveryTrackingNumber = get_text($_POST['ReDeliveryTrackingNumber'][$idx]);
        $xml = $aor->ReDeliveryExchange($ProductOrderID, $ReDeliveryMethodCode, $ReDeliveryCompanyCode, $ReDeliveryTrackingNumber);
    }
    else if ($operation == 'RejectExchange') { // 교환거부
        $ct_status = '교환';
        $RejectDetailContent = get_text($_POST['RejectDetailContent'][$idx]);
        $xml = $aor->RejectExchange($ProductOrderID, $RejectDetailContent);
    }
    else if ($operation == 'WithholdExchange') { // 교환보류
        $ct_status = '교환보류';
        $ExchangeHoldCode = get_text($_POST['ExchangeHoldCode'][$idx]);
        $ExchangeHoldDetailContent = get_text($_POST['ExchangeHoldDetailContent'][$idx]);
        $EtcFeeDemandAmount = (int)($_POST['EtcFeeDemandAmount'][$idx]);
        $xml = $aor->WithholdExchange($ProductOrderID, $ExchangeHoldCode, $ExchangeHoldDetailContent, $EtcFeeDemandAmount);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }
    else if ($operation == 'ReleaseExchangeHold') { // 교환보류해제
        $ct_status = '교환';
        $xml = $aor->ReleaseExchangeHold($ProductOrderID);
        $it_id = wz_change_status($ct_status, $ProductOrderID);
    }

    // it_id를 배열에 저장
    if($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절' || $ct_status == '완료')
        $arr_it_id[] = $it_id;
}

$sql_common = '';
if ($od_invoice || $od_delivery_company || $od_invoice_time) {
    $sql_common = ", od_invoice = '".$od_invoice."', od_delivery_company = '".$od_delivery_company."', od_invoice_time = '".$od_invoice_time."', od_delivery_text = '".$od_invoice."', od_delivery_type = 'delivery1'";
}

// 2019-09-18 : 상품 판매수량 반영
if(is_array($arr_it_id) && !empty($arr_it_id)) {
    $unq_it_id = array_unique($arr_it_id);

    foreach($unq_it_id as $it_id) {
        $sql2 = " select sum(ct_qty) as sum_qty from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and ct_status = '완료' ";
        $row2 = sql_fetch($sql2);

        $sql3 = " update {$g5['g5_shop_item_table']} set it_sum_qty = '{$row2['sum_qty']}' where it_id = '$it_id' ";
        sql_query($sql3);
    }
}

// 2019-09-18 : 미수금 등의 정보
$info = get_order_info($od_id);

// 주문상태변경
sql_query("update {$g5['g5_shop_order_table']} set
                od_cart_price   = '{$info['od_cart_price']}',
                od_cancel_price = '{$info['od_cancel_price']}',
                od_send_cost    = '{$info['od_send_cost']}',
                od_misu         = '{$info['od_misu']}',
                od_tax_mny      = '{$info['od_tax_mny']}',
                od_vat_mny      = '{$info['od_vat_mny']}',
                od_free_mny     = '{$info['od_free_mny']}',
                od_status = '".$ct_status."' ".$sql_common."
            where od_id = '".$od_id."' ");

include_once(G5_PATH.'/head.sub.php');
?>

<script type="text/javascript">
<!--
    opener.document.location.reload();
    this.close();
//-->
</script>

<?php
function wz_change_status($ct_status='', $ProductOrderID='') {

    global $g5;

    $ct = sql_fetch("select ct_id, ct_stock_use, ct_qty, it_id, io_id, io_type, od_id from {$g5['g5_shop_cart_table']} where ProductOrderID = '".$ProductOrderID."' ");

    $sql_common = "";
    if($ct['ct_stock_use']) {

        if ($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절')
        {
            $stock_use = 0;
            // 재고에 다시 더한다.
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty + '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty + '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}' ";
            }

            sql_query($sql);
            $sql_common = ",ct_stock_use  = '".$stock_use."' ";
        }

    }
    else {

        if ($ct_status == '배송' || $ct_status == '완료')
        {
            $stock_use = 1;
            // 재고에서 뺀다.
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty - '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty - '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}' ";
            }

            sql_query($sql);
            $sql_common = ",ct_stock_use  = '".$stock_use."' ";
        }
    }

    sql_query("update {$g5['g5_shop_cart_table']} set ct_status = '".$ct_status."' ".$sql_common." where ct_id = '".$ct['ct_id']."' ");

    // APMS : 가정산 자동반영 - 2014.07.20
	apms_account_auto($ct['od_id'], $ct['ct_id'], $ct_status);

	return $ct['it_id'];
}
?>