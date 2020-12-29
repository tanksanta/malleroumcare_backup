<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}


$od_receipt_time = is_null_time($_POST['od_receipt_time2']) ? $_POST['od_receipt_time'] : $_POST['od_receipt_time2'];
if ($od_receipt_time) {
    if (check_datetime($od_receipt_time) == false)
        alert('결제일시 오류입니다.');
}

// 결제정보 반영
$sql = " update {$g5['g5_shop_order_table']}
            set od_deposit_name    = '{$_POST['od_deposit_name']}',
                od_bank_account    = '{$_POST['od_bank_account']}',
                od_receipt_time    = '{$od_receipt_time}',
                od_receipt_price   = '{$_POST['od_receipt_price']}',
                od_receipt_point   = '{$_POST['od_receipt_point']}',
                od_refund_price    = '{$_POST['od_refund_price']}',
                od_delivery_company= '{$_POST['od_delivery_company']}',
                od_invoice         = '{$_POST['od_invoice']}',
                od_invoice_time    = '{$_POST['od_invoice_time']}',
                od_pay_state       = '{$_POST['od_pay_state']}',
                od_pay_time_type   = '{$_POST['od_pay_time_type']}',
                od_pay_memo        = '{$_POST['od_pay_memo']}',
                od_receipt_bank    = '{$_POST['od_receipt_bank']}',
                od_receipt_bank_no = '{$_POST['od_receipt_bank_no']}'
            where od_id = '$od_id' ";
sql_query($sql);

// 주문정보
$info = get_order_info($od_id);
if(!$info)
    alert('주문자료가 존재하지 않습니다.');

$od_status = $od['od_status'];
$cart_status = false;

// 에스크로 배송처리
/*
if($_POST['od_tno'] && $_POST['od_escrow'] == 1)
{
    $escrow_tno  = $_POST['od_tno'];
    $escrow_corp = $_POST['od_delivery_company'];
    $escrow_numb = $_POST['od_invoice'];

    include(G5_SHOP_PATH.'/'.$od['od_pg'].'/escrow.register.php');
}
*/

$title = '결제정보 수정';
include_once('./pop.head.php');
?>
<script>

try{

    alert('수정되었습니다.');
    window.opener.document.location.href=window.opener.document.URL;
    window.close();

}catch(e){ 
    window.close();
}

</script>
<?php
include_once('./pop.tail.php');
?>