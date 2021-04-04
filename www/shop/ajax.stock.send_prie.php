<?php
include_once('./_common.php');


$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
SUM(ct_qty) as qty
from {$g5['g5_shop_cart_table']}
where it_id = '".$it_id."'
and od_id = '".$_POST['cart_id']."' 
and ct_status IN ( '쇼핑', '주문', '입금', '출고준비', '준비', '배송', '완료', '작성' )
and ct_select = '1'
and io_type = 0
";
$sum = sql_fetch($sql);

$send_cost = get_item_sendcost($_POST['it_id'], $sum['price'], $sum['qty'], $_POST['cart_id'], 0);
$sql_prodSupYn = "SELECT `prodSupYn` FROM `g5_shop_item` WHERE `it_id` = '".$_POST['it_id']."' ";
$result_prodSupYn = sql_fetch($sql_prodSupYn);
if($result_prodSupYn['prodSupYn']=="N"){ $send_cost=0;}

echo $send_cost;





?>