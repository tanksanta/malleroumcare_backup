<?php
include_once('./_common.php');

if (!$is_admin) alert("관리자만 접근이 가능합니다.", G5_URL);

echo '<html><head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"></head>';

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
//$aor->showReq = true;
$aor->InqTimeFrom = '2018-10-28T12:00:00';
$aor->InqTimeTo = '2018-10-29T12:00:00';
$aor->IsAnswered = 'full'; // true, false, full
$xml = $aor->GetCustomerInquiryList();

echo '<pre>';
var_dump($xml);
echo '</pre>';
