<?php
include_once('./_common.php');
$data=date("Y-m-d H:i:s");
for($i=0; $i<count($_POST); $i++){
    sql_query('INSERT INTO `g5_barcode_log`(`stoId`, `barcode`, `b_date`) VALUES ("'.$_POST[$i]['stoId'].'","'.$_POST[$i]['prodBarNum'].'","'.$data.'")');
}

?>