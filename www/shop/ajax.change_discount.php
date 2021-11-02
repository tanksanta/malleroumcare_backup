<?php

include_once('./_common.php');

    $it_id = $_POST['it_id'];

    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);


    $ct_discount = 0;
    $ct_sale_qty = $_POST['ct_sale_qty'];

    $itSaleCntList = [$it["it_sale_cnt"], $it["it_sale_cnt_02"], $it["it_sale_cnt_03"], $it["it_sale_cnt_04"], $it["it_sale_cnt_05"]];
    $itSalePriceList = [$it["it_sale_percent"], $it["it_sale_percent_02"], $it["it_sale_percent_03"], $it["it_sale_percent_04"], $it["it_sale_percent_05"]];
    $itSaleCnt = 0;

    for($saleCnt = 0; $saleCnt < count($itSaleCntList); $saleCnt++){
        if($itSaleCntList[$saleCnt] <= $ct_sale_qty){
            if($itSaleCnt < $itSaleCntList[$saleCnt]){
                $ct_discount = $itSalePriceList[$saleCnt] * $ct_sale_qty;
                $ct_discount = ($it['it_price'] * $ct_sale_qty) - $ct_discount;
                $itSaleCnt = $itSaleCntList[$saleCnt];
            }
        }
    }


    echo $ct_discount;

?>