<?php
include_once('./_common.php');

header('Content-Type: application/json');

if (!$ct_id || !$type) {
    $ret = array(
            'result' => 'fail',
            'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$set_query = sprintf(" %s = 0 ", ($type == 'ecount' ? 'ct_is_ecount_excel_downloaded' : 'ct_is_delivery_excel_downloaded'));
$sql = "update {$g5['g5_shop_cart_table']}
        set
        {$set_query}
        where ct_id = '$ct_id' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => "삭제되었습니다.",
);

$json = json_encode($ret);
echo $json;
?>
