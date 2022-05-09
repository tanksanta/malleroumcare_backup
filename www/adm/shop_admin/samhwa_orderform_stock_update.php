<?php
include_once("./_common.php");

if ($pass) {
	foreach($pass as $ct_id => $is_pass) {
		sql_query("
			UPDATE g5_shop_cart SET
				ct_barcode_insert = " . ($is_pass === 'true' ? 'ct_qty' : '0') . "
			WHERE ct_id = '{$ct_id}'
		");
	}
}

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://test.eroumcare.com/api/stock/update");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);

echo $res;