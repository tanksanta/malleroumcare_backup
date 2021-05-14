<?php
include_once("./_common.php");
include_once('./lib/eform.lib.php');

$uuid = $_POST['uuid'];
$status = json_decode($_POST['status']);

$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");

// todo: 계약서를 생성 할 권한이 있는 사람인지 체크해야됨
$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '{$eform['od_id']}'";
if($is_member && !$is_admin)
    $sql .= " and mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['id_id']) {
  echo json_response(400, '계약서를 생성할 권한이 없습니다.');
  exit;
}

// 계약서 상태 체크 (dc_status == 0 인지)
if($eform['dc_status'] != '0') {
  echo json_response(400, '이미 계약서가 생성된 주문입니다.');
  exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
$browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

$countPostfix = sql_fetch("SELECT COUNT(`dc_id`) as cnt FROM `eform_document` WHERE `entId` = '{$eform["entId"]}' AND `penId` = '{$eform["penId"]}' AND `dc_status` != '0'")["cnt"] + 1;
if($countPostfix < 10) $countPostfix = "00".$countPostfix; else if($countPostfix < 100) $countPostfix = "0".$countPostfix;
$subject = $eform["entNm"]."_".str_replace('-', '', $eform["entCrn"])."_".$eform["penNm"].substr($eform["penLtmNum"], 0, 6)."_".date("Ymd")."_".$countPostfix;

// 계약서 정보 업데이트
sql_query("UPDATE `eform_document` SET
`dc_subject` = '$subject',
`dc_status` = '1',
`dc_datetime` = '$datetime',
`dc_ip` = '$ip',
`dc_signUrl` = ''
WHERE `dc_id` = UNHEX('$uuid')
");

// todo: 계약서 로그 작성

// todo: 직인 파일 사본 저장시켜야함


echo json_response(200, 'OK');
?>
