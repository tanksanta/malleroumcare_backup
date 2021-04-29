<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

if ( !$od_id || !$it_id || !$uid ) {
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








// 상품정보
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

//stoId cart 에서 가져옴
$sql = "SELECT `stoId` FROM `g5_shop_cart` WHERE `od_id` = '{$od_id}' AND `ct_id` = '{$ct_id}'";
$result = sql_fetch($sql);

//시스템재고 삭제
$sendData  = [];
$sendData_stoId= [];
$sendData_stoId = explode('|',$result['stoId']);
$sendData_stoId['stoId']=array_filter($sendData_stoId);
$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/stock/deleteMulti");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData_stoId, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);

//cart삭제
$sql = "DELETE FROM `g5_shop_cart` WHERE `od_id` = '{$od_id}' AND `it_id` = '{$it_id}' AND `ct_id` = '{$ct_id}'";
sql_query($sql);

// 상품정보
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);
set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ', ' . $it['io_id'] .' 상품 삭제');
samhwa_order_calc($od_id);

//들어있는 바코드수 구하기
$sto_imsi="";
$sql_ct = " select `stoId` from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
$result_ct = sql_query($sql_ct);
while($row_ct = sql_fetch_array($result_ct)) {
    $sto_imsi .=$row_ct['stoId'];
}

$stoIdDataList = explode('|',$sto_imsi);
$stoIdDataList=array_filter($stoIdDataList);
$stoIdData = implode("|", $stoIdDataList);

$count_b=0;
$sendData["stoId"] = $stoIdData;
$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);
$result_again = json_decode($res, true);
$result_again =$result_again['data'];
for($k=0; $k < count($result_again); $k++){
    if($result_again[$k]['prodBarNum']){
        $count_b ++;
    }
}
//바코드 od_prodBarNum_insert 조정
$sql = "update `g5_shop_order` set `od_prodBarNum_insert` = ".$count_b." where `od_id` = '".$od_id."'";
sql_query($sql);

//order total 수 조정
$sql = "update `g5_shop_order` set `od_prodBarNum_total` = ".count($result_again)." where `od_id` = '".$od_id."'";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '삭제되었습니다.',
);
echo json_encode($ret);
exit;

?>