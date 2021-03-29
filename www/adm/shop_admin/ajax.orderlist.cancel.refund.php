<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$od_refund_type = trim($_POST['od_refund_type']);
$od_cancel_price = (int)$_POST['od_cancel_price'];

if (!$od_id || $od_refund_type == '' || $od_cancel_price == '') {
    $ret = array(
        'result' => 'fail',
        'msg' => '모든값을 입력해주세요.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "UPDATE g5_shop_order SET
                od_cancel_price = '{$od_cancel_price}',
                od_refund_type = '{$od_refund_type}',
                od_refund_time = now(),
                od_refund_admin = '{$member['mb_id']}',
                od_status = '환불완료'
                where od_id = '{$od_id}'
                ";
sql_query($sql);

set_order_admin_log($od_id, '취소관리 환불');

$ret = array(
    'result' => 'success',
    'msg' => '환불처리되었습니다.',
);

echo json_encode($ret);
?>