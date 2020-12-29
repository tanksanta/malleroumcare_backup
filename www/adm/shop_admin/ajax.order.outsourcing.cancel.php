<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$oo_id = (int)$_POST['oo_id'];

if ( !$oo_id ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "UPDATE g5_shop_order_outsourcing SET
                oo_state = '1'
                WHERE oo_id = '{$oo_id}'
                ";
sql_query($sql);

set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ' 외부발주 취소');


$ret = array(
    'result' => 'success',
    'msg' => '취소되었습니다.',
);
echo json_encode($ret);
exit;

?>