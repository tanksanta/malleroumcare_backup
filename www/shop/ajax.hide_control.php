<?php
    include_once('./_common.php');

    if(!$_POST['od_hide_control']){              //사업소 -> 숨김

        $sql = " update `g5_shop_order`
            set `od_hide_control` = '1'
            where `od_id` = '".$_POST['od_id']."'";
            if(sql_query($sql)){
                echo "S";
                // echo $sql;
                set_order_admin_log($_POST['od_id'], "사업소 - 주문숨김");
            }else{
                echo "N";
            }
    }else{
        if($_POST['od_hide_control']=="1"){     //관리자 ->숨김
            $sql = " update `g5_shop_order`
            set `od_hide_control` = '1'
            where `od_id` = '".$_POST['od_id']."'";
            if(sql_query($sql)){
                echo "S1";
            }else{
                echo "N1";
            }
        }else if($_POST['od_hide_control']=="2"){                                  //관리자  ->보임
            $sql = " update `g5_shop_order`
            set `od_hide_control` = ''
            where `od_id` = '".$_POST['od_id']."'";
            if(sql_query($sql)){
                echo "S2";
            }else{
                echo "N2";
            }
        }
    }
?>
