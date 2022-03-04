<?php
    include_once('./_common.php');
    header('Content-Type: application/json');
    if($_POST['ct_ids']){
        if(is_array($_POST['ct_ids']) == 1){
            for($i=0; $i < count($_POST['ct_ids']); $i++){
                update_ct_status_to_delivery($_POST['ct_ids'][$i]);
            }
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