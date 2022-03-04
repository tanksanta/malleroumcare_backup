<?php
    include_once('./_common.php');
    header('Content-Type: application/json');
    if($_POST['ct_id']&&$_POST['ct_warehouse']){
        
        if( $_POST['ct_warehouse'] == "미지정"){
            $ct_warehouse = "";
        }else{
            $ct_warehouse = $_POST['ct_warehouse'];
        }

        if(is_array($_POST['ct_id']) == 1){
            for($i=0;$i<count($_POST['ct_id']);$i++){
                ct_warehouse_update($_POST['ct_id'][$i],$ct_warehouse);
            }
        }else{
            ct_warehouse_update($_POST['ct_id'],$ct_warehouse);
        }

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