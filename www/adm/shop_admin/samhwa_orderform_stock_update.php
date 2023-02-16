<?php
include_once("./_common.php");

if ($pass) {

	// 23.02.07 : 서원 - 트랜잭션 시작
	sql_query("START TRANSACTION");

	try {

		foreach($pass as $ct_id => $is_pass) {
			sql_query(" UPDATE g5_shop_cart SET ct_barcode_insert = " . ($is_pass === 'true' ? 'ct_qty' : '0') . " WHERE ct_id = '{$ct_id}' ");
		}

        // 23.02.07 : 서원 - 트랜잭션 커밋
        sql_query("COMMIT");

    } catch (Exception $e) {
        // 23.02.07 : 서원 - 트랜잭션 롤백
        sql_query("ROLLBACK");
    }
}

// 시간측정 페이지 상단
//$start_time = array_sum(explode(' ', microtime()));

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_UPDATE);
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 2); // curl이 첫 응답 시간에 대한 timeout
curl_setopt($oCurl, CURLOPT_TIMEOUT, 5); // curl 전체 실행 시간에 대한 timeout
$res = curl_exec($oCurl);
curl_close($oCurl);


echo $res;

// 시간측정 페이지 하단
//$end_time = array_sum(explode(' ', microtime()));
///echo "TIME : ". ( $end_time - $start_time );
