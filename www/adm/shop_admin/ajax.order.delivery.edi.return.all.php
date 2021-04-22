<?php
include_once('./_common.php');

header('Content-Type: application/json');

$return_success = 0;
$return_failed = 0;
$return_count = 0;

$sql = "SELECT 
    c.*, 
    i.it_model,
    o.od_name,
    o.od_addr1,
    o.od_addr2,
    o.od_tel,
    o.od_hp,
    o.od_b_name,
    o.od_b_addr1,
    o.od_b_addr2,
    o.od_b_tel,
    o.od_b_hp,
    o.od_id
FROM 
    g5_shop_cart as c 
    LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
    LEFT JOIN g5_shop_order as o ON c.od_id = o.od_id
WHERE 
    o.od_status = '출고준비'
    -- AND c.ct_status = '출고준비'
    AND c.ct_delivery_cnt > 0 -- 박스개수 1개 이상
    AND c.ct_delivery_company = 'ilogen' -- 로젠택배
    AND ( c.ct_combine_ct_id IS NULL OR c.ct_combine_ct_id = '') -- 합포가 아닌것
    AND ( c.ct_delivery_num IS NULL OR c.ct_delivery_num = '') -- 송장번호 없는것
    AND c.ct_edi_result = 1 -- edi result 성공
";

$rrr = sql_query($sql);

while($cart = sql_fetch_array($rrr) ) {
    $od_id = trim($cart['od_id']);

    unset($edi);
    unset($param);
    $edi['userID']			= G5_EDI_USERID;
    $edi['passWord']		= G5_EDI_PASSWORD;
    $edi['fixTakeNo']		= $od_id;

    $client     = new SoapClient(G5_EDI_URL);
    $param		= array('parameters'=>$edi);
    $array		= $client->__call('W_PHP_NTx_TakeNoToSlip_Select', $param);
    $result		= $array->W_PHP_NTx_TakeNoToSlip_SelectResult;

    $arr		= explode("≡", $result);
    if($arr){
        foreach( $arr as $v ){
            $arr2		= explode("Ξ", $v);
            if($arr2[1] && $arr2[2]) $sendnum		= $arr2[1];
        }
        if( $sendnum ){
            // echo $sendnum;
            $ct_delivery_num = $sendnum;
            $return_success++;
        }else{
            $ct_delivery_num = '';
            $return_failed++;
        }
    }else{
        $ct_delivery_num = '';
        $return_failed++;
    }

    $it_name = $cart["it_name"];
			
    if($it_name != $cart["ct_option"]){
        $it_name .= " ({$cart["ct_option"]})";
    }

    $sql = "update {$g5['g5_shop_cart_table']} set 
                ct_delivery_num = '$ct_delivery_num'
            where ct_id = '{$cart['ct_id']}' ";
    sql_query($sql);

    $return_count++;
    set_order_admin_log($od_id, $it_name . ' EDI 리턴');
}

if ($return_success) { 
    $result = 'success';
}else{
    $result = 'fail';
}

$ret = array(
    'result' => $result,
    'msg' => 'EDI 리턴이 '. $return_success . '개 완료되었습니다. (' . $return_failed .'개 실패)',
    'return_success' => $return_success,
    'return_failed' => $return_failed,
    'return_count' => $return_count,
);

$json = json_encode($ret);
echo $json;
?>
