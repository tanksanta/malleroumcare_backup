<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$step = $_POST['step'];
$od_id = trim($_POST['od_id']);
$ct_chk = $_POST['ct_chk'];

$k = false;
foreach($order_steps as $order_step) {
    if ( $order_step['val'] == $step ) {
        $k = true;
    }
}

if ( !$k ) {
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
        'msg' => $od_id . ' 주문번호로 주문서가 존재하지 않습니다.',
    );
    echo json_encode($ret);
    exit;
}

$step_info = get_step($step);

//print_r($ct_chk);

$bnum = 0;
foreach($ct_chk as $ct_id) {

	$barcode = '';
	for ($b=0; $b<count($_POST['ct_barcode'][$bnum]); $b++){
		$barcode .= $_POST['ct_barcode'][$bnum][$b].'|';
	}

	$ct_plus_sql = ", ct_barcode = '{$barcode}' ";

    $sql = " update {$g5['g5_shop_cart_table']}
    set ct_status = '{$step}' $ct_plus_sql ";
    $sql .= " where ct_id = '{$ct_id}' ";
    sql_query($sql);

    $sql = " SELECT * FROM  {$g5['g5_shop_cart_table']} where ct_id = '{$ct_id}' ";
    $result = sql_fetch($sql);
    set_order_admin_log($od_id, '상품 [' . $result['ct_option'] . '] 상태 ' . $step_info['name'] . ' 단계로 변경');
	$bnum++;
}

//주문서 상태변경
$sql2 = " update {$g5['g5_shop_order_table']}
set od_status = '{$step}' where od_id = '{$od_id}' ";
sql_query($sql2);

$ret = array(
    'result' => 'success',
    'msg' => '해당 상품의 상태가 ' . $step_info['name'] . ' 단계로 변경되었습니다.',
);
$json = json_encode($ret);
echo $json;
?>