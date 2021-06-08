<?php
function is_valid_json($str) {
  json_decode($str);
  return json_last_error() == JSON_ERROR_NONE;
}

function array_keys_exists(array $keys, array $arr) {
  return !array_diff($keys, array_keys($arr));
}

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

function api_firebase_call($url, $data) {
  $ch=curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  if($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
  
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  
  $res = curl_exec($ch);
  curl_close($ch);

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

// 계약서 생성 시 입력 값 무결성 검사
function valid_status_input($status) {
  $count_buy = $count_rent = 0;

  // 실제 구매 물품
  foreach($status['buy']['items'] as $item) {
    $count_buy++;

    if($item['deleted']) { // 계약서 상에서 삭제시킨경우
      $count_buy--;
      continue;
    }

    if($item['ca_name'] === '') {
      return "{$count_buy}번째 구매물품의 품목명이 유효하지 않습니다.";
    }
    if($item['it_name'] === '') {
      return "{$count_buy}번째 구매물품의 제품명이 유효하지 않습니다.";
    }
    if($item['it_code'] === '') {
      return "{$count_buy}번째 구매물품의 제품기호가 유효하지 않습니다.";
    }
    if($item['it_qty'] === '') {
      return "{$count_buy}번째 구매물품의 개수가 유효하지 않습니다.";
    }
    if($item['it_date'] === '') {
      return "{$count_buy}번째 구매물품의 판매계약일이 유효하지 않습니다.";
    }
    if($item['it_price'] === '') {
      return "{$count_buy}번째 구매물품의 고시가가 유효하지 않습니다.";
    }
    if($item['it_price_pen'] === '') {
      return "{$count_buy}번째 구매물품의 본인부담금이 유효하지 않습니다.";
    }
  }

  // 실제 대여 물품
  foreach($status['rent']['items'] as $item) {
    $count_rent++;

    if($item['deleted']) { // 계약서 상에서 삭제시킨경우
      $count_rent--;
      continue;
    }

    if($item['ca_name'] === '') {
      return "{$count_rent}번째 대여물품의 품목명이 유효하지 않습니다.";
    }
    if($item['it_name'] === '') {
      return "{$count_rent}번째 대여물품의 제품명이 유효하지 않습니다.";
    }
    if($item['it_code'] === '') {
      return "{$count_rent}번째 대여물품의 제품기호가 유효하지 않습니다.";
    }
    if($item['it_qty'] === '') {
      return "{$count_rent}번째 대여물품의 개수가 유효하지 않습니다.";
    }
    if($item['range_from'] === '' || $item['range_to'] === '') {
      return "{$count_rent}번째 대여물품의 계약기간이 유효하지 않습니다.";
    }
    if($item['it_price'] === '') {
      return "{$count_rent}번째 대여물품의 고시가가 유효하지 않습니다.";
    }
    if($item['it_price_pen'] === '') {
      return "{$count_rent}번째 대여물품의 본인부담금이 유효하지 않습니다.";
    }
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
      return "{$count_rent}번째 대여물품의 품목명을 입력해주세요.";
    }
    if($item['it_name'] === '') {
      return "{$count_rent}번째 대여물품의 제품명을 입력해주세요.";
    }
    if($item['it_code'] === '') {
      return "{$count_rent}번째 대여물품의 제품기호를 입력해주세요.";
    }
    if($item['it_qty'] === '') {
      return "{$count_rent}번째 대여물품의 개수를 입력해주세요.";
    }
    if($item['range_from'] === '' || $item['range_to'] === '') {
      return "{$count_rent}번째 대여물품의 계약기간을 입력해주세요.";
    }
    if($item['it_price'] === '') {
      return "{$count_rent}번째 대여물품의 고시가를 입력해주세요.";
    }
    if($item['it_price_pen'] === '') {
      return "{$count_rent}번째 대여물품의 본인부담금을 입력해주세요.";
    }
  }

  if($count_buy == 0 && $count_rent == 0) {
    return "계약할 구매 물품 또는 대여 물품을 입력해주세요.";
  }

  return false;
}

// 코드로 문서 이름 가져오는 함수 ($code = '001' | '002' | '003' | '101' ...)
function get_document_name($code) {
  switch($code) {
    case '001':
      return '복지용구 공급 계약서';
    case '002':
      return '장기요양급여 제공기록지(복지용구)';
    case '003':
      return '개인정보 수집·이용 사전동의서';
    case '101':
      return '장기요양기관 입소·이용신청서';
    case '102':
      return '재가서비스 이용내역서';
    default:
      return '';
  }
}
?>
