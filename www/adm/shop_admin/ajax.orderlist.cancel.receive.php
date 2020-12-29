<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);

if (!$od_id) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "UPDATE g5_shop_order SET
                od_cancel_receive_admin = '{$member['mb_id']}',
                od_status = '입고확인'
                where od_id = '{$od_id}'
                ";
sql_query($sql);

set_order_admin_log($od_id, '취소관리 입고 확인');

$ret = array(
    'result' => 'success',
    'msg' => '입고 확인되었습니다.',
);

echo json_encode($ret);
?>