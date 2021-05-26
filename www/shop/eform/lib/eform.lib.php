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
  echo json_encode(array(
    'status' => $code < 300, // success or not?
    'message' => $message
  ));
  exit;
}

// JSON API 호출 함수
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

// API 호출 GET 헬퍼
function api_get_call($url, $data = null, $port = 9901) {
  return api_call($url, 'GET', $data, $port);
}

// API 호출 POST 헬퍼
function api_post_call($url, $data = null, $port = 9901) {
  return api_call($url, 'POST', $data, $port);
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

// 계약서 생성 시 입력 값 무결성 검사
function valid_status_input($status) {
  $count_buy = $count_rent = 0;

  // 실제 구매 물품
  foreach($status['buy']['items'] as $item) {
    // 바코드 검사?
    $count_buy++;
  }

  // 실제 대여 물품
  foreach($status['rent']['items'] as $item) {
    // 바코드 검사?
    $count_rent++;
  }

  // 계약서 상 임의로 추가한 구매 물품
  foreach($status['buy']['customs'] as $item) {
    $count_buy++;
    if($item['ca_name'] === '') {
      return "{$count_buy}번째 구매물품의 품목명을 입력해주세요.";
    }
    if($item['it_name'] === '') {
      return "{$count_buy}번째 구매물품의 제품명을 입력해주세요.";
    }
    if($item['it_code'] === '') {
      return "{$count_buy}번째 구매물품의 제품기호를 입력해주세요.";
    }
    if($item['it_qty'] === '') {
      return "{$count_buy}번째 구매물품의 개수를 입력해주세요.";
    }
    if($item['it_date'] === '') {
      return "{$count_buy}번째 구매물품의 판매계약일을 입력해주세요.";
    }
    if($item['it_price'] === '') {
      return "{$count_buy}번째 구매물품의 고시가를 입력해주세요.";
    }
    if($item['it_price_pen'] === '') {
      return "{$count_buy}번째 구매물품의 본인부담금을 입력해주세요.";
    }
  }

  // 계약서 상 임의로 추가한 대여 물품
  foreach($status['rent']['customs'] as $item) {
    $count_rent++;
    if($item['ca_name'] === '') {
      return "{$count_buy}번째 대여물품의 품목명을 입력해주세요.";
    }
    if($item['it_name'] === '') {
      return "{$count_buy}번째 대여물품의 제품명을 입력해주세요.";
    }
    if($item['it_code'] === '') {
      return "{$count_buy}번째 대여물품의 제품기호를 입력해주세요.";
    }
    if($item['it_qty'] === '') {
      return "{$count_buy}번째 대여물품의 개수를 입력해주세요.";
    }
    if($item['range_from'] === '' || $item['range_to'] === '') {
      return "{$count_buy}번째 대여물품의 계약기간을 입력해주세요.";
    }
    if($item['it_price'] === '') {
      return "{$count_buy}번째 대여물품의 고시가를 입력해주세요.";
    }
    if($item['it_price_pen'] === '') {
      return "{$count_buy}번째 대여물품의 본인부담금을 입력해주세요.";
    }
  }

  return false;
}

?>
