<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);

if (!$od_id || !$od_settle_case) {
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

$sql = " update {$g5['g5_shop_order_table']}
set 
    od_settle_case = '$od_settle_case'
    ";
$sql .= " where od_id = '$od_id' ";

sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '결제수단이 변경되었습니다.',
);

echo json_encode($ret);