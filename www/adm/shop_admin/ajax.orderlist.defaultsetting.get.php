<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$menu_id = (int)$_POST['menu_id'];

if ( !$menu_id ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$result = sql_fetch(" SELECT * FROM g5_shop_order_default WHERE mb_id = '{$member['mb_id']}' AND menu_id = '{$menu_id}' ");

$data = unserialize($result['sod_data']);

$ret = array(
    'result' => 'success',
    'data' => $data,
);
echo json_encode($ret);
exit;

?>