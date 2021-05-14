<?php
include_once("./_common.php");

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


$uuid = $_POST['uuid'];
$status = json_decode($_POST['status']);

// todo: 계약서를 생성 할 권한이 있는 사람인지 체크해야됨

// 계약서 상태 체크 (dc_status == 0 인지)
$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
if($eform['dc_status'] != '0') {
  echo json_response(400, '이미 계약서가 생성된 주문입니다.');
  exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
$browser = get_browser();
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

$countPostfix = sql_fetch("SELECT COUNT(`dc_id`) as cnt FROM `eform_document` WHERE `entId` = '{$eform["entId"]}' AND `penId` = '{$eform["penId"]}'")["cnt"];
$countPostfix += 1;
if($countPostfix < 10) $countPostfix = "00".$countPostfix;
else if($countPostfix < 100) $countPostfix = "0".$countPostfix;
$dcSubject = $eform["entNm"]."_".str_replace('-', '', $eform["entCrn"])."_".$eform["penNm"].substr($eform["penLtmNum"], 0, 6)."_".date("Ymd")."_".$countPostfix;

// 계약서 정보 업데이트
sql_query("UPDATE `eform_document` SET
`dc_subject` = '',
`dc_status` = '1',
`dc_datetime` = '$datetime',
`dc_ip` = '$ip',
`dc_signUrl` = '',
");

// todo: 계약서 로그 작성
?>