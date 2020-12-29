<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_ids = array();
if ( is_array($od_id) ) {
    $od_ids = $od_id;
}else{
    $od_ids[0] = $od_id;
}

$return_success = 0;
$return_failed = 0;

foreach($od_ids as $od_id) {
    if (!$od_id) {
        continue;
    }
    
    $od_id = trim($od_id);

    //------------------------------------------------------------------------------
    // 주문서 정보
    //------------------------------------------------------------------------------
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $od = sql_fetch($sql);
    if (!$od['od_id']) {
        continue;
    }


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
            // $return_failed++;
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

    set_order_admin_log($od_id, 'EDI 리턴');
}
if ( $return_success) {
    $ret = array(
        'result' => 'success',
        'msg' => 'EDI 리턴이 '. $return_success . '개 완료되었습니다.',
        'return_success' => $return_success,
        'return_failed' => $return_failed,
    );
}else{
    $ret = array(
        'result' => 'fail',
        'msg' => 'EDI 리턴이 실패하였습니다.',
        'return_success' => $return_success,
        'return_failed' => $return_failed,
    );
}
$json = json_encode($ret);
echo $json;
?>
