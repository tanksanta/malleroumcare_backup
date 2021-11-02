<?php
include_once('./_common.php');
$od_id=$_POST["od_id"];
if(!$od_id){echo "N"; return false;}
$od_ex_date = date("Y-m-d");
$sql = " update {$g5['g5_shop_order_table']} set 
    od_ex_date = '$od_ex_date'
    where od_id = '$od_id' 
";
sql_query($sql);
?>