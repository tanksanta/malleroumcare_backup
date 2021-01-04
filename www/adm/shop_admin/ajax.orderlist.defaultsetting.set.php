<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$menu_id = (int)$_POST['menu_id'];

if ( !$menu_id ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}


$data = $_POST;

$input_data = serialize($data);

sql_query(" DELETE FROM g5_shop_order_default WHERE mb_id = '{$member['mb_id']}' AND menu_id = '{$menu_id}' ");

$sql = " INSERT INTO g5_shop_order_default SET
            mb_id = '{$member['mb_id']}',
            menu_id = '{$menu_id}',
            sod_data = '{$input_data}'
";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '저장되었습니다.',
);
echo json_encode($ret);
exit;

?>