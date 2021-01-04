<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$type = trim($_POST['type']);
$val = $_POST['val'] == 'true' ? 0 : 1;

$column = $type == 1 ? 'od_important' : 'od_important2';
if (!$od_id) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

if ( $column == 'od_important2' && $is_admin != 'super' ) {
    $sql = "select * from {$g5['auth_table']} WHERE mb_id = '{$member['mb_id']}' and au_menu = '400402' ";
    $result = sql_fetch($sql);

    if ( !$result['au_auth'] ) {
        $ret = array(
            'result' => 'fail',
            'msg' => '권한이 없습니다.',
        );
        echo json_encode($ret);
        exit;
    }
}

$sql = " update {$g5['g5_shop_order_table']} set {$column} = '{$val}' where od_id = '{$od_id}' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'value' => !!$val,
);

echo json_encode($ret);
?>