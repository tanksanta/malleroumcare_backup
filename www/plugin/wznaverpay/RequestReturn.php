<?php
include_once('./_common.php');

if (!$is_admin) alert("관리자만 접근이 가능합니다.", G5_URL);

echo '<html><head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"></head>';

$ProductOrderID = $_GET['poid'] ? $_GET['poid'] : '2019123013455200';

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
//$aor->showReq = true;
$ReturnReasonCode = 'INTENT_CHANGED'; // 반품 사유 코드
$CollectDeliveryMethodCode = 'RETURN_INDIVIDUAL'; // 수거 배송 방법 코드
$CollectDeliveryCompanyCode = ''; // 수거 택배사 코드
$CollectTrackingNumber = ''; // 수거 송장 번호
$xml = $aor->RequestReturn($ProductOrderID, $ReturnReasonCode, $CollectDeliveryMethodCode, $CollectDeliveryCompanyCode, $CollectTrackingNumber);

echo '<pre>';
var_dump($xml);
echo '</pre>';