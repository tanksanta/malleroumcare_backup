<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');

/*******************************************************
문의내역 싱크정보를 직접 확인할수있는 모듈입니다. (사용하는페이지 없음)
설치이전 날짜의 문의정보를 가져오고싶을때 사용하세요.
날짜기간이 7일이상을 초과할경우 오류가 날수가 있으니 날짜를 줄여가며 조회바랍니다.
********************************************************/

$search_date = '2019-03-01';

$aor = new NHNAPIORDER();
$aor->IsAnswered = 'FULL'; // 답변도 가져올것인지.
$aor->is_cache_time = false;
$aor->InqTimeFrom = $search_date.'T00:00:00';
$aor->InqTimeTo = $search_date.'T23:59:59';
$aor->customersync_rotation('GetCustomerInquiryList'); // 문의내역동기화