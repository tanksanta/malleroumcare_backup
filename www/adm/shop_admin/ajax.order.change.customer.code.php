<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

if (!$od_id || !$customer_code) {
    $ret = array(
            'result' => 'fail',
            'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "UPDATE {$g5['g5_shop_order_table']} SET
                customer_code = '{$customer_code}'
                WHERE od_id = '{$od_id}'
                ";
sql_query($sql);


$ret = array(
        'result' => 'success',
        'msg' => '고객코드가 정상적으로 변경되었습니다.',
);
echo json_encode($ret);
exit;

?>