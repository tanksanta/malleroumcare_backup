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

$od['cart'] = array();
$sql = "SELECT c.*, i.it_model FROM 
    g5_shop_cart as c 
    LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
    WHERE od_id = '{$od['od_id']}'";
$cart_result = sql_query($sql);
while ( $row2 = sql_fetch_array($cart_result) ) {
    $od['cart'][] = $row2;
}

$od_b_zip1 = preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 0, 3));
$od_b_zip2 = preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 3));
$od_email = strip_tags(clean_xss_attributes($od_email));

$od_delivery_text = $_POST['od_delivery_text'][$od_delivery_type_data];
$od_delivery_place = $_POST['od_delivery_place'][$od_delivery_type_data];
$od_delivery_tel = $_POST['od_delivery_tel'][$od_delivery_type_data];
$od_delivery_receiptperson = $_POST['od_delivery_receiptperson'][$od_delivery_type_data];
$od_delivery_qty = $_POST['od_delivery_qty'][$od_delivery_type_data];
$od_delivery_company = $_POST['od_delivery_company'][$od_delivery_type_data];
$od_delivery_price = $_POST['od_delivery_price'][$od_delivery_type_data];
$od_delivery_price = (int)$od_delivery_price ? $od_delivery_price : 0;

if ( $od_delivery_receiptperson === NULL ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '송하인을 선택해주세요.',
    );
    $json = json_encode($ret);
    echo $json;
    exit;
}

if ( !$od_delivery_qty ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '박스 수량을 선택해주세요.',
    );
    $json = json_encode($ret);
    echo $json;
    exit;
}

unset($edi);

$qty					= $od_delivery_qty; 
if( !$qty ) $qty		= 1;
//$priceAmt				= $od_send_cost + $od_send_cost2; // 상품 배송비
$priceAmt               = $od_delivery_price; // 배송정보에서 설정한 운임비
$gprice					= $od['od_cart_price'];
$extraAmt				= 0;
if( $gprice > 500000 && $gprice <= 1000000 ){
    $extraAmt			= round($priceAmt * 0.5);
}else if( $gprice > 1000000 && $gprice <= 2000000 ){
    $extraAmt			= round($priceAmt * 0.8);
}else if( $gprice > 2000000 && $gprice <= 3000000 ){
    $extraAmt			= $priceAmt;
}
$extraAmt				= $extraAmt * $qty;

$tot_ea		            = 0;
$freightType            = '010';
$freightType            = $od_delivery_type == 'delivery1' ? G5_EDI_DELIVERY1 : $freightType; // 선불
$freightType            = $od_delivery_type == 'delivery2' ? G5_EDI_DELIVERY2 : $freightType; // 착불


### 2018-09-11 :: 할증 및 상품 가격 0원
$extraAmt				= 0;
$gprice					= 0;

$edi['userID']			= G5_EDI_USERID;
$edi['passWord']		= G5_EDI_PASSWORD;
$edi['takeDt']			= $od['od_ex_date'] != '0000-00-00' ? str_replace("-", "", substr($od['od_ex_date'], 0, 10)) : date("Ymd");
$edi['fixTakeNo']		= $od['od_id'];

// 송하인
if ( !$od_delivery_receiptperson ) {
    $edi['sndCustNm']		= $default['de_admin_company_name'];

    $edi['sndCustAddr1']	= $default['de_admin_company_addr'];
    $edi['sndCustAddr2']	= " ";
    // $edi['sndCustAddr1'] = ' ';
    // $edi['sndCustAddr2'] = ' ';
    $edi['sndTelNo']		= $default['de_admin_company_tel'];
    $edi['sndHandNo']		= $default['de_admin_company_tel'];
}else{
    $edi['sndCustNm']		= $od['od_name'];

    // $edi['sndCustAddr1']	= $od['od_addr1'];
    // $edi['sndCustAddr2']	= $od['od_addr2'];
    $edi['sndCustAddr1'] = ' ';
    $edi['sndCustAddr2'] = ' ';
    $edi['sndTelNo']		= $od['od_tel'] ? $od['od_tel'] : $od['od_hp'];
    $edi['sndHandNo']		= $od['od_hp'] ? $od['od_hp'] : $od['od_tel'];
}


$edi['rcvCustNm']		= $od['od_b_name'];

$edi['rcvCustAddr1']	= $od['od_b_addr1'];
$edi['rcvCustAddr2']	= $od['od_b_addr2'];
$edi['rcvTelNo']		= $od['od_b_tel'] ? $od['od_b_tel'] : $od['od_b_hp'];
$edi['rcvHandNo']		= $od['od_b_hp'] ? $od['od_b_hp'] : $od['od_b_tel'];
$edi['freightType']		= $freightType;	// 010(선불) / 020(착불) / 030(신용)


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

$od_cart_count = 0;
$saved_uid = '';
$goods_ct = 0;
foreach($od['cart'] as $cart) {
    $od_cart_count += $cart['ct_qty'];
    if ($saved_uid != $cart['ct_uid']) {
        $goods_ct++;
        $saved_uid = $cart['ct_uid'];
    }
}
if ($od_cart_count > 0) {
    $show_od_cart_count = '(' . $od_cart_count . ')';
}
$show_goods_ct = $goods_ct > 1 ? '외 ' . ($goods_ct - 1) . '종' : '';

// $edi['goodsName']		= $od['cart'][0]['it_model'] . $od_cart_count;
$edi['goodsName']		= $od['cart'][0]['it_model'] . $show_goods_ct . $show_od_cart_count;
$edi['sndMsg']			= $od['od_memo'];
$edi['inQty']			= $tot_ea;
$edi['itemOption']		= "";

$param		= array('parameters'=>$edi);
$client     = new SoapClient(G5_EDI_URL);
$array		= $client->__call('W_PHP_Tx_ExcelFile_Save', $param);
$result		= $array->W_PHP_Tx_ExcelFile_SaveResult;
if ( $result=='TRUE' ) {
    $edi_result = '1';
}else{
    $edi_result = '3';
}

set_order_admin_log($od_id, 'EDI 전송');

$sql = " update {$g5['g5_shop_order_table']}
set 
    od_b_name = '$od_b_name',
    od_b_tel = '$od_b_tel',
    od_b_hp = '$od_b_hp',
    od_b_zip1 = '$od_b_zip1',
    od_b_zip2 = '$od_b_zip2',
    od_b_addr1 = '$od_b_addr1',
    od_b_addr2 = '$od_b_addr2',
    od_b_addr3 = '$od_b_addr3',
    od_b_addr_jibeon = '$od_b_addr_jibeon',
    od_ex_date = '$od_ex_date',
    od_memo = '$od_memo',
    od_delivery_type = '$od_delivery_type',
    od_delivery_company = '$od_delivery_company',
    od_delivery_text = '$od_delivery_text',
    od_delivery_place = '$od_delivery_place',
    od_delivery_tel = '$od_delivery_tel',
    od_delivery_receiptperson = '$od_delivery_receiptperson',
    od_delivery_qty = '$od_delivery_qty',
    od_delivery_price = '$od_delivery_price',
    od_send_admin_memo = '$od_send_admin_memo',
    od_edi_date = now(),
    od_edi_msg = '{$result}', 
    od_edi_chk = '{$od_delivery_receiptperson}', 
    od_edi_price = '$priceAmt', 
    od_edi_ea = '$qty',
    od_edi_result = '$edi_result',
    od_invoice_time = now(),
    od_invoice = '$od_delivery_text'
    ";
$sql .= " where od_id = '$od_id' ";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => 'EDI 전송이 완료되었습니다.',
);
$json = json_encode($ret);
echo $json;
?>
