<?php
include_once('./_common.php');

header('Content-Type: application/json');

$where = "c.ct_status = '출고준비'";

if (is_array($ct_id)) {
    $where = ' c.ct_id IN (\'' . implode('\',\'', $ct_id) . '\')';
}

if (!$ct_id || !is_array($ct_id)) {
    $ret = array(
        'result' => 'fail',
        'msg' => '전송할 주문을 선택해주세요.',
    );
    echo json_encode($ret);
    exit;
}

if (count($ct_id) > 99) {
    $ret = array(
        'result' => 'fail',
        'msg' => '전송할 주문이 너무 많습니다. (최대 99개)',
    );
    echo json_encode($ret);
    exit;
}

if ($type === 'resend') {
    // if (!$ct_id) {
    //     $ret = array(
    //         'result' => 'fail',
    //         'msg' => '재전송할 주문을 선택해주세요.',
    //     );
    //     echo json_encode($ret);
    //     exit;
    // }
} else {
    $where .= " AND c.ct_edi_result = 0 ";
}

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
    o.od_id,
    o.od_b_zip1,
    o.od_b_zip2,
    o.od_memo
FROM 
    g5_shop_cart as c 
    LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
    LEFT JOIN g5_shop_order as o ON c.od_id = o.od_id
WHERE 
    {$where}
    -- AND c.ct_status = '출고준비'
    AND c.ct_delivery_cnt > 0 -- 박스개수 1개 이상
    AND c.ct_delivery_company = 'ilogen' -- 로젠택배
    AND ( c.ct_combine_ct_id IS NULL OR c.ct_combine_ct_id = '') -- 합포가 아닌것
    AND ( c.ct_delivery_num IS NULL OR c.ct_delivery_num = '') -- 송장번호 없는것
    -- AND c.ct_edi_result = 0 -- 위에서 처리
    AND c.ct_is_direct_delivery = 0 -- 직배송 아닌것
    -- and o.od_id = '2021042313174631'
ORDER BY c.ct_move_date ASC, o.od_id ASC
";
$cart_result = sql_query($sql);
while ( $row2 = sql_fetch_array($cart_result) ) {
    $carts[] = $row2;
}
// $sql_v=[];
// $sql_v['v']=$sql;
// $json = json_encode($carts);
// echo $json;
// return false;
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

    $take_no                = $cart['od_id'] . '_' . $cart['ct_id'];

    $edi['userID']			= G5_EDI_USERID;
    $edi['passWord']		= G5_EDI_PASSWORD; 
    // $edi['takeDt']			= $cart['ct_ex_date'] != '0000-00-00' ? str_replace("-", "", substr($cart['od_ex_date'], 0, 10)) : date("Ymd");
    $edi['takeDt']          = date("Ymd");
    $edi['fixTakeNo']		= $take_no;

    // 송하인
    // $edi['sndCustNm']		= $cart['od_name'];
    // $edi['sndCustAddr1']	= $cart['od_addr1'];
    // $edi['sndCustAddr2']	= $cart['od_addr2'];
    // $edi['sndTelNo']		= $cart['od_tel'] ? $cart['od_tel'] : $cart['od_hp'];
    // $edi['sndHandNo']		= $cart['od_hp'] ? $cart['od_hp'] : $cart['od_tel'];
    $edi['sndCustNm']		= '이로움';
    $edi['sndCustAddr1']	= '인천광역시 서구 이든1로 21';
    $edi['sndCustAddr2']	= '이로움';
    $edi['sndTelNo']		= '1533-5088';
    $edi['sndHandNo']		= '1533-5088';
    
    // 수취인
    $edi['rcvCustNm']		= $cart['od_b_name'];

    $edi['rcvZipCd']        = $cart['od_b_zip1'] . $cart['od_b_zip2'];
    $edi['rcvCustAddr1']	= $cart['od_b_addr1'];
    $edi['rcvCustAddr2']	= $cart['od_b_addr2'];
    $edi['rcvTelNo']		= $cart['od_b_tel'] ? $cart['od_b_tel'] : $cart['od_b_hp'];
    $edi['rcvHandNo']		= $cart['od_b_hp'] ? $cart['od_b_hp'] : $cart['od_b_tel'];
    $edi['freightType']		= '030';	// 010(선불) / 020(착불) / 030(신용)


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

    $it_name .= ' ' . $cart['ct_qty'] . '개';

    // 합포
    $sql = "SELECT * FROM g5_shop_cart WHERE ct_combine_ct_id = '{$cart['ct_id']}'";
    $combine_result = sql_query($sql);
    $combine = false;
    while ($combine_row = sql_fetch_array($combine_result)) {
        
        $combine_it_name = $combine_row["it_name"];
        if($combine_it_name != $combine_row["ct_option"]){
            $combine_it_name .= "(".$combine_row["ct_option"].")";
        }
        $combine_it_name .= " ".$combine_row['ct_qty']."개";
        $it_name .= ' #' . $combine_it_name;
        $combine = true;
    }

    // 합포 박스
    try {
        if (!$combine) {
            throw new Exception();
        }
        $data = get_packed_boxes($cart['od_id']);
        foreach ($data['joinPacked'] as $box) {
            foreach ($box['items'] as $box_ct_id => $box_item) {
                if ($box_ct_id == $cart['ct_id']) {
                    $it_name .= ' #' . "[{$box['name']}]";
                }
            }
        }
    } catch(Exception $e) {
    }

    $edi['goodsName']       = $it_name;
    $edi['sndMsg']			= $cart['od_memo'];
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

    set_order_admin_log($cart['od_id'], $it_name . ' EDI 전송');

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
