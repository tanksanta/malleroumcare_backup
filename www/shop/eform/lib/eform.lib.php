<?php
function json_response($code = 200, $message = null) {
  http_response_code($code);
  header("Content-Type: application/json");
  $status = array(
    200 => '200 OK',
    400 => '400 Bad Request',
    500 => '500 Internal Server Error'
  );
  header('Status: '.$status[$code]);
  return json_encode(array(
    'status' => $code < 300, // success or not?
    'message' => $message
  ));
}

function api_call($url, $method = 'GET', $data = null, $port = 9901) {
  if($method == 'GET') $url .= '?'.http_build_query($data);

  $oCurl = curl_init();
  curl_setopt($oCurl, CURLOPT_PORT, $port);
  curl_setopt($oCurl, CURLOPT_URL, $url);
  if($method == 'POST') {
    curl_setopt($oCurl, CURLOPT_POST, 1);
    if($data) curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
  }
  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
  $res = curl_exec($oCurl);
  curl_close($oCurl);
  return json_decode($res, true);
}

function get_url_content($url) {
  $oCurl = curl_init();
  curl_setopt($oCurl, CURLOPT_URL, $url);
  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
  $res = curl_exec($oCurl);
  curl_close($oCurl);
  return $res;
}
?>
