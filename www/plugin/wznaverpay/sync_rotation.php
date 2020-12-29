<?php
include_once('./_common.php');

/*******************************************************
영카트 관리자화면에서 버튼을 클릭했을때 동기화 처리되는 모듈입니다.
********************************************************/

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
$aor->ordersync_rotation('ordersync');