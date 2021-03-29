<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);

// if (!$od_id || !$od_release_date) {
if (!$od_id || !$od_ex_date) {
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

// $sql = " update {$g5['g5_shop_order_table']} set 
//             od_release_date = '$od_release_date'
//             where od_id = '$od_id' 
// ";

$sql = " update {$g5['g5_shop_order_table']} set 
    od_ex_date = '$od_ex_date'
    where od_id = '$od_id' 
";

sql_query($sql);

set_order_admin_log($od_id, '출고예정일 변경');

$ret = array(
    'result' => 'success',
    'msg' => '출고예정일이 변경되었습니다.',
);
$json = json_encode($ret);
echo $json;
?>