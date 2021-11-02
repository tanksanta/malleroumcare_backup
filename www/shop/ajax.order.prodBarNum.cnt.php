<?php
	include_once("./_common.php");
    if($_POST['cancel']=="y"){
        sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '' where `od_id` = '{$_POST['od_id']}'");
    }else{
        sql_query("
            UPDATE g5_shop_order SET
                od_prodBarNum_insert = '{$_POST["cnt"]}'
            WHERE od_id = '{$_POST["od_id"]}'
        ");
        sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '' where `od_id` = '{$_POST['od_id']}'");
    }
?>