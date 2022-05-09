<?php

    $oCurl = curl_init();
    curl_setopt($oCurl, CURLOPT_PORT, 9901);
    curl_setopt($oCurl, CURLOPT_URL, "https://test.eroumcare.com/api/stock/selectBarNumList");
    curl_setopt($oCurl, CURLOPT_POST, 1);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $res = curl_exec($oCurl);
    $stockBarList = json_decode($res, true);
    curl_close($oCurl);
    // alert($res);
    // print_r($stockBarList);
    // echo '<br>';
    // print_r($stockBarList["errorYN"]);
    // echo '<br>';
    // print_r($stockBarList["message"]);
    // echo '<br>';
    // print_r($stockBarList["data"][0]['prodBarNumList']);

    // echo '<br>';
    return false;
    if($stockBarList["errorYN"] == "N"){
        // print_r($stockBarList["stockBarList"]);
        // print_r($stockBarList["message"]);
        // alert($res["message"], "/");
        // array_push($stoIdList, $res["data"][0]["stoId"]);
    }
?>
