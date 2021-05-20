<?php
include_once("./_common.php");
include_once('./lib/eform.lib.php');

$uuid = $_POST['uuid'];
$status = json_decode(stripslashes($_POST['status']), true);
if(!$uuid || !$status) {
  json_response(400, '잘못된 요청입니다.');
}

$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
if(!$eform['dc_id']) {
  json_response(500, '변경할 계약서를 찾을 수 없습니다.');
}

$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '{$eform['od_id']}'";
if($is_member && !$is_admin)
    $sql .= " AND mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['mb_id']) {
  json_response(400, '계약서를 변경할 권한이 없습니다.');
}

if($eform['dc_status'] != '1') {
  json_response(400, '계약서를 더 이상 변경할 수 없는 상태입니다.');
}

// 입력 값 무결성 검사
if($error = valid_status_input($status)) {
  json_response(400, $error);
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
  $it_price = intval(str_replace(",", "", $item['it_price']));
  $it_price_pen = intval(str_replace(",", "", $item['it_price_pen']));
  $it_price_ent = $it_price - $it_price_pen;
  if($gubun == '01') $item['it_date'] = $item['range_from'].'-'.$item['range_to'];
  sql_query("INSERT INTO `eform_document_item` SET
  `dc_id` = UNHEX('$uuid'),
  `gubun` = '$gubun',
  `ca_name` = '{$item['ca_name']}',
  `it_name` = '{$item['it_name']}',
  `it_code` = '{$item['it_code']}',
  `it_barcode` = '{$item['it_barcode']}',
  `it_qty` = '{$item['it_qty']}',
  `it_date` = '{$item['it_date']}',
  `it_price` = '$it_price',
  `it_price_pen` = '$it_price_pen',
  `it_price_ent` = '$it_price_ent'
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

// 계약서 정보 업데이트
sql_query("UPDATE `eform_document` SET
`entConAcc01` = '{$status['entConAcc01']}',
`entConAcc02` = '{$status['entConAcc02']}'
WHERE `dc_id` = UNHEX('$uuid')
");

// 계약서 로그 작성
$log = '전자계약서의 내용을 변경했습니다.';

sql_query("INSERT INTO `eform_document_log` SET
`dc_id` = UNHEX('$uuid'),
`dl_log` = '$log',
`dl_ip` = '$ip',
`dl_browser` = '$browser',
`dl_datetime` = '$datetime'
");

json_response(200, 'OK');
?>
