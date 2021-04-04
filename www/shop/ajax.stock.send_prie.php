<?php
include_once('./_common.php');


$sql = " select `it_sc_price`
from {$g5['g5_shop_cart_table']}
where it_id = '".$it_id."'
and od_id = '".$_POST['cart_id']."' 
";
$sum = sql_fetch($sql);

$send_cost = get_item_sendcost($_POST['it_id'], $sum['it_sc_price'], 1, $_POST['cart_id'], 0);
$sql_prodSupYn = "SELECT `prodSupYn` FROM `g5_shop_item` WHERE `it_id` = '".$_POST['it_id']."' ";
$result_prodSupYn = sql_fetch($sql_prodSupYn);
if($result_prodSupYn['prodSupYn']=="N"){ $send_cost=0;}

echo $send_cost;





?>