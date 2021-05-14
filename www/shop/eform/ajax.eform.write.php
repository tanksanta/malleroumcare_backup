<?php
include_once("./_common.php");
include_once('./lib/eform.lib.php');

$uuid = $_POST['uuid'];
$status = json_decode(stripslashes($_POST['status']), true);
if(!$uuid || !$status) {
  echo json_response(400, '잘못된 요청입니다.');
  exit;
}

$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");

$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '{$eform['od_id']}'";
if($is_member && !$is_admin)
    $sql .= " AND mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['mb_id']) {
  echo json_response(400, '계약서를 생성할 권한이 없습니다.');
  exit;
}

if($eform['dc_status'] != '0') {
  echo json_response(400, '이미 계약서가 생성된 주문입니다.');
  exit;
}

// todo: 직인 파일 사본 저장시켜야함
$ent = api_call('POST', 'https://system.eroumcare.com/api/ent/account', array(
  'usrId' => $od['mb_id']
));
$entSealImg = $ent['data']['entSealImg'];
if(!$entSealImg) {
  echo json_response(400, '통합시스템에서 사업소 직인 이미지를 등록해주세요.');
  exit;
}

function updateItem($item) {
  if($item['deleted']) { // 물품 계약서 상에서 삭제시킨 경우
    sql_query("DELETE FROM `eform_document_item` WHERE `it_id` = '{$item['it_id']}'");
  } else {
    // 실제 구매/대여 물품은 바코드 정보만 수정할 수 있음
    sql_query("UPDATE `eform_document_item` SET
    `it_barcode` = '{$item['it_barcode']}'
    WHERE `it_id` = '{$item['it_id']}'
    ");
  }
}
// 실제 구매 물품 정보 업데이트
foreach($status['buy']['items'] as $item) {
  updateItem($item);
}
// 실제 대여 물품 정보 업데이트
foreach($status['rent']['items'] as $item) {
  updateItem($item);
}

function addItem($item, $gubun, $uuid) {
  $priceEnt = intval($item['it_price']) - intval($item['it_price_ent']);
  sql_query("INSERT INTO `eform_document_item` SET
  `dc_id` = UNHEX('$uuid'),
  `gubun` = '$gubun',
  `ca_name` = '{$item['ca_name']}',
  `it_name` = '{$item['it_name']}',
  `it_code` = '{$item['it_code']}',
  `it_barcode` = '{$item['it_barcode']}',
  `it_qty` = '{$item['it_qty']}',
  `it_date` = '{$item['it_date']}',
  `it_price` = '{$item['it_price']}',
  `it_price_pen` = '{$item['it_price_pen']}',
  `it_price_ent` = '$priceEnt'
  ");
}
// 계약서 상 구매 물품 정보 추가
foreach($status['buy']['customs'] as $item) {
  addItem($item, '00', $uuid);
}
// 계약서 상 대여 물품 정보 추가
foreach($status['rent']['customs'] as $item) {
  addItem($item, '01', $uuid);
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
`dc_signUrl` = '',
`entConAcc01` = '{$status['entConAcc01']}',
`entConAcc02` = '{$status['entConAcc02']}'
WHERE `dc_id` = UNHEX('$uuid')
");

// 계약서 로그 작성
$log = '전자계약서를 생성했습니다.';

sql_query("INSERT INTO `eform_document_log` SET
`dc_id` = UNHEX('$uuid'),
`dl_log` = '$log',
`dl_ip` = '$ip',
`dl_browser` = '$browser',
`dl_datetime` = '$datetime'
");

echo json_response(200, 'OK');
?>
