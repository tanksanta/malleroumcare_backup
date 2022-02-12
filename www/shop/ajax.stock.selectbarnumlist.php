<?php

    $sendData_list=[];
    $sendData_list['usrId']=$_POST['usrId'];
    $prodsSendData = [];
    $prodsData["prodId"] = $_POST["prodId"];
    array_push($prodsSendData, $prodsData);
    $sendData_list["prods"] = $prodsSendData;

    $oCurl = curl_init();
    curl_setopt($oCurl, CURLOPT_PORT, 9901);
    curl_setopt($oCurl, CURLOPT_URL, "https://test.eroumcare.com/api/stock/selectBarNumList");
    curl_setopt($oCurl, CURLOPT_POST, 1);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData_list, JSON_UNESCAPED_UNICODE));
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $res = curl_exec($oCurl);
    // $stockBarList = json_decode($res, true);
    curl_close($oCurl);
    echo $res;

?>
