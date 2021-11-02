<?php
include_once('./_common.php');

header('Content-Type: application/json');

$return_success = 0;
$return_failed = 0;
$return_count = 0;

$sql = " select * from {$g5['g5_shop_order_table']} where od_edi_result = '1' AND od_delivery_text = '' and ( od_delivery_type = 'delivery1' OR od_delivery_type = 'delivery2' ) ";
$rrr = sql_query($sql);

while($od = sql_fetch_array($rrr) ) {
    $od_id = trim($od['od_id']);

    unset($edi);
    unset($param);
    $edi['userID']			= G5_EDI_USERID;
    $edi['passWord']		= G5_EDI_PASSWORD;
    $edi['fixTakeNo']		= $od_id;

    //$client		= new SoapClient('https://ilogen.ilogen.com/iLOGEN.EDI.WebService/W_PHPServer.asmx?WSDL');
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
            $od_delivery_text = $sendnum;
            $return_success++;
        }else{
            $od_delivery_text = '';
            $return_failed++;
        }
    }else{
        $od_delivery_text = '';
        $return_failed++;
    }

    $sql = "update {$g5['g5_shop_order_table']} set 
                od_delivery_text = '$od_delivery_text'
            where od_id = '$od_id' ";
    sql_query($sql);

    $return_count++;
    set_order_admin_log($od_id, 'EDI 리턴');
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
