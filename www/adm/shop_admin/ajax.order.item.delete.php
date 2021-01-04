<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

if ( !$od_id || !$it_id || !$uid ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
        $ret = array(
        'result' => 'fail',
        'msg' => '해당 주문번호로 주문서가 존재하지 않습니다.',
    );
    echo json_encode($ret);
    exit;
}

// 상품정보
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

$sql = "DELETE FROM g5_shop_cart WHERE od_id = '{$od_id}' AND it_id = '{$it_id}' AND ct_uid = '{$uid}' ";
sql_query($sql);

set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ', ' . $it['io_id'] .' 상품 삭제');

samhwa_order_calc($od_id);

$ret = array(
    'result' => 'success',
    'msg' => '삭제되었습니다.',
);
echo json_encode($ret);
exit;

?>