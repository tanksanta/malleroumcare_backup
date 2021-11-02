<?php
function wznpayRequestBody($showReq = false, $service = '', $operation = '', $ReqUrl = '', $rbody = '') {

    $headers = array('Content-type: text/xml;charset=UTF-8', 'SOAPAction: '.$service.'#'.$operation);
    if ($showReq) {
        echo 'targetUrl : '.$ReqUrl.'<br /><pre>'.var_dump($headers).'</pre><br /><pre>'.str_replace('<','<', str_replace('>', '>', $rbody)).'</pre>';
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $ReqUrl);
    curl_setopt($curl, CURLOPT_VERBOSE, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $rbody);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSLVERSION, true); // SSL 버젼 (https 접속시에 필요)
    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    $xml = curl_exec($curl);
    curl_close($curl);
    if ($xml) {
        $replace_head = array('n1:', 'n:', 'SOAP:', 'soapenv:');
        $xml = str_ireplace($replace_head, '', $xml);
        $xml = simplexml_load_string($xml);
    }

    return $xml;
}