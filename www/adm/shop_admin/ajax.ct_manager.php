<?php
    include_once('./_common.php');
    header('Content-Type: application/json');
    if($_POST['ct_id']&&$_POST['ct_manager']){
        
        if( $_POST['ct_manager'] =="미지정"){
            $ct_manager ="";
        }else{
            $ct_manager = $_POST['ct_manager'];
        }

        $sql_ct = "UPDATE `g5_shop_cart` SET `ct_manager`='".$ct_manager."' where `ct_id` = '".$_POST['ct_id']."'";
        sql_query($sql_ct);
        $ret = array(
            'result' => 'success',
        );
        $json = json_encode($ret);
        echo $json;
    }else{
        $ret = array(
            'result' => 'fail',
        );
        $json = json_encode($ret);
        echo $json;
    }

?>