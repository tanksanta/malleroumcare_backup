<?php
include_once('./_common.php');

if (!$is_admin) alert("관리자만 접근이 가능합니다.", G5_URL);

echo '<html><head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"></head>';

$ProductOrderID = $_GET['poid'] ? $_GET['poid'] : '2019123013455200';

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
//$aor->showReq = true;
$ExchangeHoldCode = 'EXCHANGE_EXTRAFEE'; // 교환 보류 사유 코드
$ExchangeHoldDetailContent = '보류합니다.'; // 교환 보류 상세 사유
$EtcFeeDemandAmount = 100; // 기타 교환 비용
$xml = $aor->WithholdExchange($ProductOrderID, $ExchangeHoldCode, $ExchangeHoldDetailContent, $EtcFeeDemandAmount);

echo '<pre>';
var_dump($xml);
echo '</pre>';