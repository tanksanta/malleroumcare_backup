<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);

if (!$od_id) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}


//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
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


$mb = get_member($od['mb_id']);
if (!$mb['mb_id']) {
    alert("해당 회원이 존재하지 않습니다.");
}

$mb['mb_tel'] = $mb['mb_tel'] ? $mb['mb_tel'] : $mb['mb_hp'];


// $sql = " update {$g5['g5_shop_order_table']} set 
//             mb_id = '{$mb['mb_id']}',
//             od_name = '{$mb['mb_name']}',
//             od_email = '{$mb['mb_email']}',
//             od_tel = '{$mb['mb_tel']}',
//             od_hp = '{$mb['mb_hp']}',
//             od_zip1 = '{$mb['mb_zip1']}',
//             od_zip2 = '{$mb['mb_zip2']}',
//             od_addr1 = '{$mb['mb_addr1']}',
//             od_addr2 = '{$mb['mb_addr2']}',
//             od_addr3 = '{$mb['mb_addr3']}',
//             od_addr_jibeon = '{$mb['mb_addr_jibeon']}',
//             od_b_name = '{$mb['mb_name']}',
//             od_b_tel = '{$mb['mb_tel']}',
//             od_b_hp = '{$mb['mb_hp']}',
//             od_b_zip1 = '{$mb['mb_zip1']}',
//             od_b_zip2 = '{$mb['mb_zip2']}',
//             od_b_addr1 = '{$mb['mb_addr1']}',
//             od_b_addr2 = '{$mb['mb_addr2']}',
//             od_b_addr3 = '{$mb['mb_addr3']}',
//             od_b_addr_jibeon = '{$mb['mb_addr_jibeon']}'
//             where od_id = '{$od_id}' ";


$sql = " update {$g5['g5_shop_order_table']} set 
            od_b_name = '{$mb['mb_name']}',
            od_b_tel = '{$mb['mb_tel']}',
            od_b_hp = '{$mb['mb_hp']}',
            od_b_zip1 = '{$mb['mb_zip1']}',
            od_b_zip2 = '{$mb['mb_zip2']}',
            od_b_addr1 = '{$mb['mb_addr1']}',
            od_b_addr2 = '{$mb['mb_addr2']}',
            od_b_addr3 = '{$mb['mb_addr3']}',
            od_b_addr_jibeon = '{$mb['mb_addr_jibeon']}'
            where od_id = '{$od['od_id']}' ";
sql_query($sql);

set_order_admin_log($od['od_id'], '주문서 배송정보 기본정보 반영');

$ret = array(
    'result' => 'success',
    'msg' => '주문서 배송정보 기본정보가 반영되었습니다.',
);
$json = json_encode($ret);
echo $json;
?>
