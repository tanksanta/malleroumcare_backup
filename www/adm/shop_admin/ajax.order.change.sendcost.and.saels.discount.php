<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$od_send_cost = (int)trim($_POST['od_send_cost']);
$od_sales_discount = (int)trim($_POST['od_sales_discount']);

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

$sql = " update 
            {$g5['g5_shop_order_table']} 
        set 
            od_send_cost = '$od_send_cost',
            od_sales_discount = '$od_sales_discount'
        where 
            od_id = '$od_id' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '적용 되었습니다.',
);

set_order_admin_log($od_id, '배송비 '. $od_send_cost .'원 변경');
set_order_admin_log($od_id, '매출할인 '. $od_sales_discount .'원 변경');

echo json_encode($ret);
?>