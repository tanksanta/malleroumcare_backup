<?php
$sub_menu = '400400';
include_once('./_common.php');

check_admin_token();

if($_POST['mod_type'] == 'memo') {
    $sql = "update {$g5['g5_shop_order_table']} set od_shop_memo = '$od_shop_memo' where od_id = '$od_id' ";
    sql_query($sql);
}

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./orderformnaverapi.php?od_id=$od_id&amp;$qstr");
?>
