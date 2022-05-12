<?php

	// header("Content-Type: application/json");
    include_once("./_common.php");
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
<<<<<<< HEAD
	curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/order/update");
=======
	curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_ORDER_UPDATE);
>>>>>>> dev
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	curl_close($oCurl);
    $res = json_decode($res, true);
    if($res['errorYN']=="N"){
        $ordId=$_POST['penOrdId'];
        $stoId=$_POST['prods'][0]['stoId'];
        $ordLendStrDtm=$_POST['prods'][0]['ordLendStrDtm'];
        $ordLendEndDtm=$_POST['prods'][0]['ordLendEndDtm'];
        $sql_update=" update `g5_rental_log`
        set `strdate` = '{$ordLendStrDtm}',
            `enddate` = '{$ordLendEndDtm}'
            where `ordId` = '{$ordId}' and `stoId`= '{$stoId}' ";
        sql_query($sql_update);
        echo $sql_update;
    }

    echo $res;
?>
