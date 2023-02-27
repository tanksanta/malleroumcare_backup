<?php
	include_once("./_common.php");
    if($_POST['od_id']){
        // 오더 테이블 작업중 취소
        sql_query("update {$g5['g5_shop_order_table']} set `od_edit_member` = '' where `od_id` = '{$_POST['od_id']}'");
        // 카트 테이블 작업중 취소
        sql_query("update {$g5['g5_shop_cart_table']} set `ct_edit_member` = '' where `od_id` = '{$od_id}'");
    }else{
        // 카트 테이블 작업중 취소
        sql_query("update {$g5['g5_shop_cart_table']} set `ct_edit_member` = '' where `ct_id` = '{$ct_id}'");
    }
?>