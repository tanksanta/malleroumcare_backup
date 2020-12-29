<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');

/*******************************************************
리뷰정보 싱크정보를 직접 확인할수있는 모듈입니다. (사용하는페이지 없음)
설치이전 날짜의 리뷰정보를 가져오고싶을때 사용하세요.
날짜는 하루간격만 조회가 됩니다.
********************************************************/

$search_date = '2019-02-20';

$aor = new NHNAPIORDER();
$aor->PurchaseReviewClassType = 'GENERAL'; // 프리미엄평가져오기
$aor->is_cache_time = false;
$aor->InqTimeFrom = $search_date.'T00:00:00';
$aor->InqTimeTo = $search_date.'T23:59:59';
$aor->customersync_callback('GetPurchaseReviewList-GENERAL');

$aor = new NHNAPIORDER();
$aor->PurchaseReviewClassType = 'PREMIUM'; // 프리미엄평가져오기
$aor->is_cache_time = false;
$aor->InqTimeFrom = $search_date.'T00:00:00';
$aor->InqTimeTo = $search_date.'T23:59:59';
$aor->customersync_callback('GetPurchaseReviewList-PREMIUM');