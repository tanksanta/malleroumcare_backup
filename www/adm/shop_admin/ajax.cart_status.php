<?php
    include_once('./_common.php');
    if($_POST['ct_id']&&$_POST['step']){
        for($i=0;$i<count($_POST['ct_id']); $i ++){
            $sql_ct = "update `g5_shop_cart` set `ct_status` = '".$_POST['step']."' where `ct_id` = '".$_POST['ct_id'][$i]."'";
            sql_query($sql_ct);
        }
        echo "success";
    }else{
        echo "fail";
    }
?>