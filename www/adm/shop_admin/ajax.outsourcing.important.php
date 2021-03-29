<?php
include_once('./_common.php');

header('Content-Type: application/json');

$oo_id = trim($oo_id);
$val = $_POST['val'] == 'true' ? 0 : 1;

if (!$oo_id) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = " update g5_shop_order_outsourcing set oo_important = '{$val}' where oo_id = '{$oo_id}' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'value' => !!$val,
);

echo json_encode($ret);
?>