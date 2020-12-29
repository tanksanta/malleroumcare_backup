<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($od_id);
$od_list_memo = trim(htmlspecialchars($od_list_memo));

if (!$od_id || !$od_list_memo) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "UPDATE {$g5['g5_shop_order_table']} SET
                od_list_memo = '{$od_list_memo}'
                where od_id = '{$od_id}'
                ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '메모를 수정하였습니다.',
    'data' => $od_list_memo,
);

echo json_encode($ret);
?>