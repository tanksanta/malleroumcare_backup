<?php
    include_once('./_common.php');
    $sql = " update `g5_shop_cart`
        set `ct_hide_control` = '1'
        where `od_id` = '".$_POST['od_id']."'";
        if(sql_query($sql)){
            echo "S";
        }else{
            echo $sql;
        }
?>
