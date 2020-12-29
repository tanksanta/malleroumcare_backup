<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

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

$pay_status = get_pay_step($od['od_pay_state']);

if ( $pay_status['next'] === NULL ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '잘못된 접근입니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = " update {$g5['g5_shop_order_table']} set od_pay_state = '{$pay_status['next']}' where od_id = '{$od_id}' ";
sql_query($sql);

$new_pay_status = get_pay_step($pay_status['next']);

$ret = array(
    'result' => 'success',
    'msg' => '결제정보 상태가 ' . $new_pay_status['name'] . '로 수정되었습니다.',
);

set_order_admin_log($od_id, '결제정보 상태 ' . $new_pay_status['name'] . '로 수정');

$json = json_encode($ret);
echo $json;
?>