<?php
include_once('./_common.php');

if (!$is_admin) alert("관리자만 접근이 가능합니다.", G5_URL);

/*******************************************************
class.nhnapiorder.php 파일에서 service 가 Alpha2MallService41 로 되어있는경우 리뷰정보가 없기때문에 Error 를 출력합니다.
이럴경우 service 를 MallService41 로 변경 후 정보를 확인하시기 바랍니다.
********************************************************/

echo '<html><head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"></head>';

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
//$aor->showReq = true;
$aor->PurchaseReviewClassType = 'GENERAL'; // GENERAL, PREMIUM, FULL
$aor->is_cache_time = false;
$aor->InqTimeFrom = '2012-01-11T01:16:11';
$aor->InqTimeTo = '2012-01-15T12:15:11';
$xml = $aor->GetPurchaseReviewList();

echo '<pre>';
var_dump($xml);
echo '</pre>';