<?php
include_once('./_common.php');

/*******************************************************
네이버페이에서 Callback Url 로 자동 동기화 처리되는 모듈입니다.
********************************************************/

// 로그기록
$log_txt = date('Y-m-d H:i:s', time());
$log_txt .= '|IP : '.getenv("REMOTE_ADDR");
foreach($_POST as $uk=>$uv) {
    $log_txt .= "|POST:".$uk."=".$uv;
}
foreach($_GET as $uk=>$uv) {
    $log_txt .= "|GET:".$uk."=".$uv;
}
$log_dir = G5_DATA_PATH.'/npayorderlog';
@mkdir($log_dir, G5_DIR_PERMISSION);
@chmod($log_dir, G5_DIR_PERMISSION);
$log_file = fopen($log_dir."/query_".date("Ymd").".log", "a");
fwrite($log_file, $log_txt."\r\n");
fclose($log_file);

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
$aor->ordersync_callback('ordersync');