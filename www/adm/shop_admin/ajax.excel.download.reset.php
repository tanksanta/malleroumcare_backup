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

$ct = sql_fetch("select od_id, it_name, ct_option from {$g5['g5_shop_cart_table']} where ct_id = '$ct_id' ");
$ct['it_name'] .= $ct['ct_option'] && $ct['ct_option'] != $ct['it_name'] ? " ({$ct['ct_option']})" : '';

if($type == 'ecount') {
    set_order_admin_log($ct['od_id'], '이카운트 엑셀 다운로드 취소 : ' . $ct['it_name']);
} else {
    set_order_admin_log($ct['od_id'], '발주서 다운로드 취소 : ' . $ct['it_name']);
}

$ret = array(
    'result' => 'success',
    'msg' => "삭제되었습니다.",
);

$json = json_encode($ret);
echo $json;
?>
