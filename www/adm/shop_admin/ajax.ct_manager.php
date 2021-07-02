<?php
    include_once('./_common.php');
    header('Content-Type: application/json');
    if($_POST['ct_id']&&$_POST['ct_manager']){
        
        if(is_array($_POST['ct_id']) == 1){
            for($i=0;$i<count($_POST['ct_id']);$i++){
                ct_manager_update($_POST['ct_id'][$i]);
            }
        }else{
                ct_manager_update($_POST['ct_id']);
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