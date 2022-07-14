<?php
include_once('./_common.php');

$where_ct_id = "where ct_id ='";
if( is_array($_POST['ct_id']) ){
    $where_ct_id .= implode("' or ct_id = '", $_POST['ct_id']);
} else {
    $where_ct_id = $_POST['ct_id'];
}

$where_ct_id .= "'";

$sql = "select sum(it.it_type1 + it.it_type2 + it.it_type10) as is_soldout from g5_shop_cart ct left join g5_shop_item it on ct.it_id = it.it_id ".$where_ct_id;
$result = sql_fetch($sql);

if ($result['is_soldout'] > (int)0 ) {
    $ret = array(
        'result' => 'soldout',
    );
    echo json_encode($ret);
    exit;
} else {
    $ret = array(
        'result' => 'ok',
    );
    echo json_encode($ret);
    exit;
}

?>