<?php
	include_once("./_common.php");


    
    if($_POST['ct_status']&&$_POST['od_id']){
        $sql_ct = 'update `g5_shop_cart` set `ct_status` = "'.$_POST['ct_status'].'" where od_id = "'.$_POST['od_id'].'"';
        sql_query($sql_ct);
        echo "Y";
    }else{
        echo "N";
    }

?>