<?php
include_once('./_common.php');

header('Content-Type: application/json');


$return_success = 0;
$return_failed = 0;
$return_count = 0;

$carts = array();

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
    AND c.ct_edi_result = 0
";
$cart_result = sql_query($sql);
while ( $row2 = sql_fetch_array($cart_result) ) {
    $carts[] = $row2;
}

foreach($carts as $cart) {

    unset($edi);

    $qty					= $cart['ct_delivery_cnt']; // 박스수량
    if( !$qty ) $qty		= 1;
    $priceAmt				= $cart['ct_delivery_price']; // 상품 배송비
    $gprice					= $cart['ct_price']; // 상품가격

    // 추가 금액
    // $extraAmt				= 0;
    // if( $gprice > 500000 && $gprice <= 1000000 ){
    //     $extraAmt			= round($priceAmt * 0.5);
    // }else if( $gprice > 1000000 && $gprice <= 2000000 ){
    //     $extraAmt			= round($priceAmt * 0.8);
    // }else if( $gprice > 2000000 && $gprice <= 3000000 ){
    //     $extraAmt			= $priceAmt;
    // }
    // $extraAmt				= $extraAmt * $qty;

    $tot_ea		            = 0;

    ### 2018-09-11 :: 할증 및 상품 가격 0원
    $extraAmt				= 0;
    $gprice					= 0;

    $edi['userID']			= G5_EDI_USERID;
    $edi['passWord']		= G5_EDI_PASSWORD; 
    // $edi['takeDt']			= $cart['ct_ex_date'] != '0000-00-00' ? str_replace("-", "", substr($cart['od_ex_date'], 0, 10)) : date("Ymd");
    $edi['takeDt']          = date("Ymd");
    $edi['fixTakeNo']		= $cart['od_id'];

    // 송하인
    $edi['sndCustNm']		= $cart['od_name'];
    $edi['sndCustAddr1']	= $cart['od_addr1'];
    $edi['sndCustAddr2']	= $cart['od_addr2'];
    $edi['sndTelNo']		= $cart['od_tel'] ? $cart['od_tel'] : $cart['od_hp'];
    $edi['sndHandNo']		= $cart['od_hp'] ? $cart['od_hp'] : $cart['od_tel'];

    
    // 수취인
    $edi['rcvCustNm']		= $cart['od_b_name'];

    $edi['rcvCustAddr1']	= $cart['od_b_addr1'];
    $edi['rcvCustAddr2']	= $cart['od_b_addr2'];
    $edi['rcvTelNo']		= $cart['od_b_tel'] ? $cart['od_b_tel'] : $cart['od_b_hp'];
    $edi['rcvHandNo']		= $cart['od_b_hp'] ? $cart['od_b_hp'] : $cart['od_b_tel'];
    $edi['freightType']		= '010';	// 010(선불) / 020(착불) / 030(신용)


    $edi['qty']				= $qty;
    $edi['priceAmt']		= $priceAmt;
    $edi['extraAmt']		= $extraAmt;	// 할증운임
    $edi['goodsAmt']		= $gprice;
    $edi['airAmtType']		= "";	// 제주운임이 발생하는 경우 운임구분과 동일


    // if( count($od['cart']) > 1 ) {
    //     $od_cart_count = ' 외 ' . (count($od['cart']) - 1) .'개';
    // }else{
    //     $od_cart_count = '';
    // }

    // $goods_ct = count((array)$od['cart']);
    // $show_goods_ct = $goods_ct > 1 ? '외 ' . $goods_ct . '종' : '';

    // $od_cart_count = 0;
    // $saved_uid = '';
    // $goods_ct = 0;
    // foreach($od['cart'] as $cart) {
    //     $od_cart_count += $cart['ct_qty'];
    //     if ($saved_uid != $cart['ct_uid']) {
    //         $goods_ct++;
    //         $saved_uid = $cart['ct_uid'];
    //     }
    // }
    // if ($od_cart_count > 0) {
    //     $show_od_cart_count = '(' . $od_cart_count . ')';
    // }
    // $show_goods_ct = $goods_ct > 1 ? '외 ' . ($goods_ct - 1) . '종' : '';

    // $edi['goodsName']		= $od['cart'][0]['it_model'] . $show_goods_ct . $show_od_cart_count;
    
    $it_name = $cart["it_name"];
			
    if($it_name != $cart["ct_option"]){
        $it_name .= " ({$cart["ct_option"]})";
    }

    $edi['goodsName']       = $it_name;
    // $edi['sndMsg']			= $od['od_memo'];
    $edi['sndMsg']			= '';
    $edi['inQty']			= $tot_ea;
    $edi['itemOption']		= "";


    $param		= array('parameters'=>$edi);
    $client     = new SoapClient(G5_EDI_URL);
    $array		= $client->__call('W_PHP_Tx_ExcelFile_Save', $param);
    $result		= $array->W_PHP_Tx_ExcelFile_SaveResult;
    if ( $result=='TRUE' ) {
        $edi_result = '1';
        $return_success++;
    }else{
        $edi_result = '3';
        $return_failed++;
    }
    $return_count++;

    echo $result;
    exit;

    set_order_admin_log($od_id, $it_name . ' EDI 전송');

    $sql = " update {$g5['g5_shop_cart_table']}
    set 
        ct_edi_date = now(),
        ct_edi_msg = '{$result}', 
        ct_edi_chk = '', 
        ct_edi_price = '$priceAmt', 
        ct_edi_ea = '$qty',
        ct_edi_result = '$edi_result'
    where ct_id = '{$cart['ct_id']}'
        ";
    sql_query($sql);
}

// $ret = array(
//     'result' => 'success',
//     'msg' => 'EDI 전송이 완료되었습니다.',
// );

if ($return_success) { 
    $result = 'success';
}else{
    $result = 'fail';
}

$ret = array(
    'result' => $result,
    'msg' => 'EDI 전송이 '. $return_success . '개 완료되었습니다. (' . $return_failed .'개 실패)',
    'return_success' => $return_success,
    'return_failed' => $return_failed,
    'return_count' => $return_count,
);
$json = json_encode($ret);
echo $json;
?>
