<?php






	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
<<<<<<< HEAD
	curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/stock/deleteMulti");
=======
	curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_DELETE_MULTI);
>>>>>>> dev
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	curl_close($oCurl);
	echo $res;

?>
