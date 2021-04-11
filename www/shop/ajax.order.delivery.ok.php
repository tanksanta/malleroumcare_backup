<?php
include_once('./_common.php');
header('Content-Type: application/json');

$sql = " select ct.*, od.stoId as sto_id
            from {$g5['g5_shop_cart_table']} as ct
            INNER JOIN {$g5['g5_shop_order_table']} as od ON ct.od_id = od.od_id
            where ct.ct_id = '$ct_id'
              and ct.od_id = '$od_id'
              and ct.mb_id = '{$member['mb_id']}'";
$result = sql_fetch($sql); // and ct.ct_status = '배송' 

if (!$result['ct_id']) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    die(json_encode($ret));
}

// 이로움 STOID 가져오기
$api_data = array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'prodId' => $result['it_id'],
);
$api_result = get_eroumcare(EROUMCARE_API_SELECT_DETAIL_LIST, $api_data);

$sto_ids = explode(',', $result['sto_id']);

if ($api_result['data'] && count($api_result['data'])) {
    $filtered_arr = array_values(array_filter($api_result['data'], function($arr) {
        global $sto_ids;
        return in_array($arr['stoId'], $sto_ids);
    }));
}

$new_sto_ids = array_map(function($data) {
    return array(
        'stoId' => $data['stoId'],
        'stateCd' => '01',
    );
}, $filtered_arr);

// 배송완료 재고있음 상태 변경
$api_data = array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'prods' => $new_sto_ids,
);

$api_result = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $api_data);
if ($api_result['errorYN'] === 'N') {
    
    $sql = "
    update {$g5['g5_shop_cart_table']}
        set ct_status = '배송완료' 
        where ct_id = '{$result['ct_id']}'
    ";
    sql_query($sql);

    $ret = array(
        'result' => 'success',
        'msg' => '배송완료로 수정되었습니다.',
    );
    die(json_encode($ret));
}

die(json_encode(array(
    'result' => 'fail',
    'msg' => '알수없는 오류로 실패하였습니다.',
)));
