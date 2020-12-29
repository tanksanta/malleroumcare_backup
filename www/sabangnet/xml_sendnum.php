<?php
include_once('./_common.php');
header('Content-type: text/xml');

$sql = " select * from {$g5['g5_shop_order_table']} where sabang_od_id like '%{$od_id}%' ";
$od = sql_fetch($sql);
if (!$od['od_id']) exit;

switch($od['od_delivery_company']){
	case "cjlogistics":
                        $delivery_code = "055";
		                break;
	default:
                        $delivery_code = "007";
}

$sabang_od_id_arr = explode(",",$od['sabang_od_id']);

$xml = "<?xml version='1.0' encoding='EUC-KR'?>";
$xml .= "<SABANG_INV_REGI>";
$xml .= "<HEADER>";
$xml .= "	<SEND_COMPAYNY_ID>".SEND_COMPAYNY_ID."</SEND_COMPAYNY_ID>";
$xml .= "	<SEND_AUTH_KEY>".SEND_AUTH_KEY."</SEND_AUTH_KEY>";
$xml .= "	<SEND_DATE>".date('Ymd')."</SEND_DATE>";
$xml .= "	<SEND_INV_EDIT_YN>Y</SEND_INV_EDIT_YN>";
$xml .= "	<RESULT_TYPE>XML</RESULT_TYPE>";
$xml .= "</HEADER>";
for($i=0; $i<count($sabang_od_id_arr); $i++){
	$xml .= "<DATA>";             	
	$xml .= "	<SABANGNET_IDX><![CDATA[".$sabang_od_id_arr[$i]."]]></SABANGNET_IDX>";   
	$xml .= "	<TAK_CODE><![CDATA[".$delivery_code."]]></TAK_CODE>";
	$xml .= "	<TAK_INVOICE><![CDATA[".$od['od_delivery_text']."]]></TAK_INVOICE>";
	$xml .= "</DATA>";
}
$xml .= "</SABANG_INV_REGI>";

echo $xml;
?>