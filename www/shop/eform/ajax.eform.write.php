<?php
include_once("./_common.php");

if(!$is_member) {
  json_response(400, '먼저 로그인하세요.');
}

$uuid = $_POST['uuid'];
$status = json_decode(stripslashes($_POST['status']), true);
if(!$uuid || !$status) {
  json_response(400, '잘못된 요청입니다.');
}

$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
if(!$eform['dc_id']) {
  json_response(500, '생성할 계약서를 찾을 수 없습니다.');
}

$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '{$eform['od_id']}'";
if($is_member && !$is_admin)
    $sql .= " AND mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['mb_id']) {
  json_response(400, '계약서를 생성할 권한이 없습니다.');
}

if($eform['dc_status'] != '0') {
  json_response(400, '이미 계약서가 생성된 주문입니다.');
}

// 입력 값 무결성 검사
if($error = valid_status_input($status)) {
  json_response(400, $error);
}

$member_update_sql = '';
//서버 최대 용량 10Mb
$max_file_size = 1024*1024*10;

if($_FILES['sealFile']['tmp_name']){
  // 변수 정리
  $uploads_dir = G5_DATA_PATH.'/file/member/stamp';
  $error = $_FILES['sealFile']['error'];
  $name = $_FILES['sealFile']['name'];
  $allowed_ext = array('jpg', 'png', 'jpeg', 'gif', 'bmp');
  $ext = array_pop(explode('.', $name));
  $temp = explode(".", $_FILES["sealFile"]["name"]);
  $sealFile_name = $member['mb_id'].'_'.round(microtime(true)) . '.' . end($temp);
  $sealFile = "$uploads_dir/$sealFile_name";
  // 오류 확인
  if( $error != UPLOAD_ERR_OK ) {
    switch( $error ) {
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        json_response(400, '파일이 너무 큽니다.');
        break;
      default:
        json_response(400, '파일을 첨부해주세요.');
    }
    exit;
  }
  if($_FILES['sealFile']['size'] >= $max_file_size) {
    json_response(400, '10Mb 까지만 업로드 가능합니다.');
  }
  // 확장자 확인
  if(!in_array($ext, $allowed_ext)) {
    json_response(400, '허용되지 않는 확장자입니다.');
  }
  move_uploaded_file( $_FILES['sealFile']['tmp_name'], $sealFile);
  $member_update_sql .= " sealFile = '".$sealFile_name."' ";
}

if ($mb_ent_num) {
  if ($_FILES['sealFile']['tmp_name']) {
    $member_update_sql .= ', ';
  }

  $member_update_sql .= " mb_ent_num = '{$mb_ent_num}' ";
}

if ($member_update_sql) {
  // 사용자 업데이트
  sql_query("
    UPDATE {$g5["member_table"]} SET
      {$member_update_sql}
    WHERE mb_id = '{$member["mb_id"]}'
  ");
  $member = get_member($member['mb_id']);

  // 계약서 업데이트
  sql_query("
    UPDATE `eform_document` SET 
      `entNum` = '{$member["mb_ent_num"]}'
    WHERE `dc_id` = UNHEX('$uuid')
  ");
  $eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
}

// 직인 파일 사본 저장
$seal_file = $member['sealFile'];
$seal_dir = G5_DATA_PATH.'/file/member/stamp';
if(!$seal_file) {
  json_response(400, '회원정보에 직인 이미지를 등록해주세요.');
}
$seal_data = @file_get_contents($seal_dir.'/'.$seal_file);
$signdir = G5_DATA_PATH.'/eform/sign';
if(!is_dir($signdir)) {
  @mkdir($signdir, G5_DIR_PERMISSION, true);
  @chmod($signdir, G5_DIR_PERMISSION);
}
$filename = $uuid."_".$eform['entId']."_".date("YmdHisw").".png";
file_put_contents("$signdir/$filename", $seal_data);

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
  
  // 비급여 품목은 계약서에서 제외
  if ($gubun != '02') {
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

// 문서 제목 생성
$subject = $eform["entNm"]."_".str_replace('-', '', $eform["entCrn"])."_".$eform["penNm"].substr($eform["penLtmNum"], 0, 6)."_".date("Ymd")."_";
$subject_count_postfix = sql_fetch("SELECT COUNT(`dc_id`) as cnt FROM `eform_document` WHERE `dc_subject` LIKE '{$subject}%'")["cnt"];
$subject_count_postfix = str_pad($subject_count_postfix + 1, 3, '0', STR_PAD_LEFT); // zerofill
$subject .= $subject_count_postfix;

// 계약서 정보 업데이트
sql_query("UPDATE `eform_document` SET
`dc_subject` = '$subject',
`dc_status` = '1',
`dc_datetime` = '$datetime',
`dc_ip` = '$ip',
`dc_signUrl` = '/data/eform/sign/$filename',
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

json_response(200, 'OK');
?>
