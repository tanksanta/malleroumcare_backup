<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$no = (int)$_POST['no'];
if ( !$no ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "DELETE FROM g5_shop_order_cart_file WHERE ctf_no = '{$no}' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '삭제되었습니다.',
);
echo json_encode($ret);
exit;

?>