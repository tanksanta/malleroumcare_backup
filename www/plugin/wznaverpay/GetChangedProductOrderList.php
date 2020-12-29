<?php
include_once('./_common.php');

if (!$is_admin) alert("관리자만 접근이 가능합니다.", G5_URL);

echo '<html><head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"></head>';

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
//$aor->showReq = true;
//$aor->is_cache_time = false;
//$aor->InqTimeFrom = '2012-01-11T01:16:11';
//$aor->InqTimeTo = '2012-01-12T12:15:11';
$xml = $aor->GetChangedProductOrderList();

echo '<pre>';
var_dump($xml);
echo '</pre>';