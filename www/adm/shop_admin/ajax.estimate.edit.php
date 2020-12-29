<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$content = trim(htmlspecialchars($_POST['content']));
$name = trim(htmlspecialchars($_POST['name']));
$title = trim(htmlspecialchars($_POST['title']));
$time = trim(htmlspecialchars($_POST['time']));

if (!$od_id || !$content || !$name || !$title || !$time) {
    $ret = array(
        'result' => 'fail',
        'msg' => '모든 값을 입력해주세요.',
    );
    echo json_encode($ret);
    exit;
}
//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
/*
if (!$od['od_id']) {
    $ret = array(
        'result' => 'fail',
        'msg' => '주문번호가 존재하지 않습니다.',
    );
    echo json_encode($ret);
    exit;
}
*/

// 견적서 정보
$sql = " select * from g5_shop_order_estimate where od_id = '$od_id' ";
$est = sql_fetch($sql);

if ( $est['est_no'] ) {
    $sql = "UPDATE g5_shop_order_estimate SET
                    est_content = '{$content}',
                    est_name = '{$name}',
                    est_title = '{$title}',
                    est_time = '{$time}'
                    where od_id = '{$od_id}'
                    ";
    sql_query($sql);
}else{
    $sql = "INSERT INTO g5_shop_order_estimate SET
                    od_id = '{$od_id}',
                    est_content = '{$content}',
                    est_name = '{$name}',
                    est_title = '{$title}',
                    est_time = '{$time}'
                    ";
    sql_query($sql);
}

set_order_admin_log($od_id, '견적서 정보 수정');

$ret = array(
    'result' => 'success',
    'msg' => '저장하였습니다.',
);

echo json_encode($ret);
?>