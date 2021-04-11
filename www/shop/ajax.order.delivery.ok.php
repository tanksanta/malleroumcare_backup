<?php
include_once('./_common.php');
header('Content-Type: application/json');

// 재고체크
$sql = " select *
            from {$g5['g5_shop_cart_table']}
            where ct_id = '$ct_id'
              and od_id = '$od_id'
              and ct_status = '배송' 
              and mb_id = '{$member['mb_id']}'";
$result = sql_fetch($sql);

if (!$result['ct_id']) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    die(json_encode($ret));
}

$sql = "
    update {$g5['g5_shop_cart_table']}
    set ct_status = '완료' 
    where ct_id = '{$result['ct_id']}'
    ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '배송완료로 수정되었습니다.',
);
die(json_encode($ret));
?>
