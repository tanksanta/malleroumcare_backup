<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$content = trim($_POST['content']);

if (!$od_id || !$content) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}
$sql = "INSERT INTO purchase_order_admin_memo SET
                od_id = '{$od_id}',
                mb_id = '{$member['mb_id']}',
                om_content = '{$content}',
                om_datetime = now()
                ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '메모를 작성하였습니다.',
);

echo json_encode($ret);
?>