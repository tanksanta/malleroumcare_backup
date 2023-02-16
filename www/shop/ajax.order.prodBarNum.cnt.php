<?php
    // X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X
    // 23.02.07 : 서원 - 사용되지 말아야 할 파일....
    //  
    // www\shop\ajax.member_cancel.php 
    // www\shop\ajax.ct_barcode_insert.php
    // 위 파일에 하래 내용 통합 적용됨.
    //
    // X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X - X

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