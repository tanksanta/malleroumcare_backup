<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$od_cancel_inspection_status = trim($_POST['od_cancel_inspection_status']);
$od_cancel_inspection_memo = trim($_POST['od_cancel_inspection_memo']);
$od_cancel_inspection_price = (int)$_POST['od_cancel_inspection_price'];

if (!$od_id || !$od_cancel_inspection_status || !$od_cancel_inspection_memo || !$od_cancel_inspection_price) {
    $ret = array(
        'result' => 'fail',
        'msg' => '모든값을 입력해주세요.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "UPDATE g5_shop_order SET
                od_cancel_inspection_status = '{$od_cancel_inspection_status}',
                od_cancel_inspection_price = '{$od_cancel_inspection_price}',
                od_cancel_inspection_memo = '{$od_cancel_inspection_memo}',
                od_cancel_inspection_time = now(),
                od_cancel_inspection_admin = '{$member['mb_id']}',
                od_status = '검수확인'
                where od_id = '{$od_id}'
                ";
sql_query($sql);

set_order_admin_log($od_id, '취소관리 검수 확인');

$ret = array(
    'result' => 'success',
    'msg' => '검수 확인되었습니다.',
);

echo json_encode($ret);
?>