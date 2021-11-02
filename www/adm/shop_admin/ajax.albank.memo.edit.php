<?php
include_once('./_common.php');

header('Content-Type: application/json');

$no = (int)$no;
$memo = htmlspecialchars($memo);

if (!$no || !$memo) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = " update tb_log_bank set 
    memo = '{$memo}'
    where no = '{$no}' ";

sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '수정 되었습니다.',
);

echo json_encode($ret);
?>