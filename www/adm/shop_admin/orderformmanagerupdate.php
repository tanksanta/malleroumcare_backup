<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once('./admin.shop.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

auth_check($auth[$sub_menu], "w");

check_admin_token();

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od  = sql_fetch($sql);
if(!$od['od_id'])
    alert('주문자료가 존재하지 않습니다.');

if ($od_receipt_time) {
    if (check_datetime($od_receipt_time) == false)
        alert('결제일시 오류입니다.');
}

// 담당자 반영
$sql = " update {$g5['g5_shop_order_table']}
            set od_sales_manager    = '{$_POST['od_sales_manager']}',
                od_release_manager    = '{$_POST['od_release_manager']}'
            where od_id = '$od_id' ";
sql_query($sql);

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./orderform.php?od_id=$od_id&amp;$qstr");
?>
