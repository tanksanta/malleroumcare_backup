<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$od_cart_discount2 = (int)trim($_POST['od_cart_discount2']);

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

$sql = " update {$g5['g5_shop_order_table']} set 
            od_cart_discount2 = '$od_cart_discount2'
            where od_id = '$od_id' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '추가할인을 적용하였습니다.',
);

set_order_admin_log($od_id, '추가할인 '. $od_cart_discount2 .'원 적용');

echo json_encode($ret);
?>