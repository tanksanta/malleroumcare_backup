<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$content = trim($_POST['content']);
$mod = trim($_POST['mod']);

if (!$od_id || !$content) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$_table_nm = $_set_nm = '';
if( $mod === "admin" ) {
    $_table_nm = 'purchase_order_admin_memo';
    $_set_nm = 'om_content';
}
else if( $mod === "cart" ) {
    $_table_nm = 'purchase_cart_memo';
    $_set_nm = 'ctm_memo';
}

if( !$_table_nm ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '데이터 입력중 DB 오류가 발생하였습니다.',
    );
    echo json_encode($ret);
}

$sql = "
    INSERT INTO
        {$_table_nm}
    SET
        od_id = '{$od_id}',
        mb_id = '{$member['mb_id']}',
        {$_set_nm} = '{$content}'
";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '메모를 작성하였습니다.',
);

echo json_encode($ret);
?>