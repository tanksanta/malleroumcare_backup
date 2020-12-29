<?php
include_once('./_common.php');

header('Content-Type: application/json');

if (!$od_id || !$od_delivery_text) {
    $ret = array(
            'result' => 'fail',
            'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}


$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    $ret = array(
            'result' => 'fail',
            'msg' => '해당 주문번호로 주문서가 존재하지 않습니다.',
    );
    echo json_encode($ret);
    exit;
}

$od_delivery_company = $od_delivery_company ? $od_delivery_company : $od['od_delivery_company'];

$sql = "update {$g5['g5_shop_order_table']}
        set
            od_delivery_company = '$od_delivery_company',
            od_delivery_text = '$od_delivery_text'
        where od_id = '$od_id' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => "주문번호({$od_id}) 배송 정보가 수정되었습니다.",
);

if ($od_delivery_company == 'ilogen') {
    $ret = array(
            'result' => 'success',
            'msg' => "주문번호({$od_id}) 배송 정보가 수정되었습니다.\n로젠택배는 EDI 송장 리턴을 이용해주세요.",
    );
}

$json = json_encode($ret);
echo $json;
?>
