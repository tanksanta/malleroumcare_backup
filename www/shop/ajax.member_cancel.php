<?php
	include_once("./_common.php");
    if($_POST['od_id']){
        sql_query("update {$g5['g5_shop_cart_table']} set `ct_edit_member` = '' where `od_id` = '{$od_id}'");
    }else{
        sql_query("update {$g5['g5_shop_cart_table']} set `ct_edit_member` = '' where `ct_id` = '{$ct_id}'");
    }
?>