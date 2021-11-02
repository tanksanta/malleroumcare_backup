<?php
include_once('./_common.php');
$data=date("Y-m-d H:i:s");

for($i=0; $i<count($_POST['prods']); $i++){
    $sql_it = "select * from g5_shop_item where it_id ='".$_POST['prods'][$i]['prodId']."'";
    $result_it = sql_fetch($sql_it);
    $option = "";
    if($_POST['prods'][$i]['prodColor']) $option .= "(".$_POST['prods'][$i]['prodColor'].")";
    if($_POST['prods'][$i]['prodSize']) $option .= "(".$_POST['prods'][$i]['prodSize'].")";
    $content = "바코드입력 : ".$result_it['it_name'].$option."[ ".$_POST['prods'][$i]['prodBarNum']." ]";
    sql_query('INSERT INTO `g5_barcode_log`(`od_id`, `mb_id`, `stoId`, `barcode`, `b_content`, `b_date`) VALUES ("'.$_POST['od_id'].'","'.$_POST['mb_id'].'","'.$_POST['prods'][$i]['stoId'].'","'.$_POST['prods'][$i]['prodBarNum'].'","'.$content.'","'.$data.'")');
}



?>