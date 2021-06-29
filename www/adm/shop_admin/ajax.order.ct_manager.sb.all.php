<?php
    include_once('./_common.php');
    header('Content-Type: application/json');
    if($_POST['ct_id']&&$_POST['ct_manager']){
        
        for($i=0;$i<count($_POST['ct_id']);$i++){

            $ct_id=$_POST['ct_id'][$i];
            if( $_POST['ct_manager'] =="미지정"){
                $ct_manager ="";
            }else{
                $ct_manager = $_POST['ct_manager'];
            }

            $sql = "SELECT * FROM `g5_shop_cart` WHERE ct_id = '{$ct_id}'";
            $cart = sql_fetch($sql);

            $ct_it_name = $cart['it_name'];
            $ct_option = ($cart["ct_option"] == $cart['it_name']) ? "" : "(".$cart['ct_option'].")";
            $ct_it_name = $ct_it_name.$ct_option;

            // 주문자 정보
            $order_member = get_member($cart['mb_id']);
            $giup_name = $order_member['mb_giup_bname'] ? "[" . $order_member['mb_giup_bname'] . "] " : "";

            add_notification(
                array(),
                $ct_manager,
                '신규 출고 담당자로 지정된 상품이 있습니다.',
                $giup_name . $ct_it_name . " * " . $cart['ct_qty'] . "개",
                // 'http://naver.com',
            );

            $sql_ct = "UPDATE `g5_shop_cart` SET `ct_manager`='".$ct_manager."' where `ct_id` = '".$ct_id."'";
            sql_query($sql_ct);
            
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