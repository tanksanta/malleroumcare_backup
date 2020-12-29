<?php
include_once('./_common.php');
header('Content-type: text/xml');
$xml = "<?xml version='1.0' encoding='UTF-8'?>";
$xml .= "<SABANG_ORDER_LIST>";
$xml .= "<HEADER>";
$xml .= "<SEND_COMPAYNY_ID>".SEND_COMPAYNY_ID."</SEND_COMPAYNY_ID>";
$xml .= "<SEND_AUTH_KEY>".SEND_AUTH_KEY."</SEND_AUTH_KEY>";
$xml .= "<SEND_DATE>".date('Ymd',strtotime("+1 day"))."</SEND_DATE>";
$xml .= "</HEADER>";
$xml .= "<DATA>";
$xml .= "<ORD_ST_DATE>".date('Ymd',strtotime("-3 day"))."</ORD_ST_DATE>";
$xml .= "<ORD_ED_DATE>".date('Ymd',strtotime("+1 day"))."</ORD_ED_DATE>";
$xml .= "<ORD_FIELD>";
$xml .= "<![CDATA[ IDX|ORDER_ID|MALL_ID|ORDER_STATUS|DELV_MSG|MALL_PRODUCT_ID|PRODUCT_NAME|SALE_COST|PAY_COST|SKU_VALUE|SALE_CNT|DELV_COST|USER_ID|USER_NAME|USER_TEL|USER_CEL|USER_EMAIL|RECEIVE_TEL|RECEIVE_CEL|RECEIVE_NAME|RECEIVE_ZIPCODE|RECEIVE_ADDR|REG_DATE ]]>";
$xml .= "</ORD_FIELD>";
$xml .= "<LANG>UTF-8</LANG>";
$xml .= "</DATA>";
$xml .= "</SABANG_ORDER_LIST>";

echo $xml;
?>