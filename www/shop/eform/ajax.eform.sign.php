<?php
include_once("./_common.php");
include_once('./lib/eform.lib.php');
include_once(G5_LIB_PATH.'/icode.lms.lib.php');

if(!$is_member) {
  json_response(400, '먼저 로그인하세요.');
}

$uuid = $_POST['uuid'];
$state = json_decode(stripslashes($_POST['state']), true);
if(!$uuid || !$state) {
  json_response(400, '잘못된 요청입니다.');
}

$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
if(!$eform['dc_id']) {
  json_response(500, '서명할 계약서를 찾을 수 없습니다.');
}

$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '{$eform['od_id']}'";
if($is_member && !$is_admin)
    $sql .= " AND mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['mb_id']) {
  json_response(400, '계약서에 서명할 권한이 없습니다.');
}

if($eform['dc_status'] == '2') {
  json_response(400, '이미 서명이 완료된 계약서입니다.');
}

if($eform['dc_status'] != '1') {
  json_response(400, '계약서가 서명할 수 없는 상태입니다.');
}

// 서명 파일 사본 저장할 경로
$signdir = G5_DATA_PATH.'/eform/sign';
if(!is_dir($signdir)) {
  @mkdir($signdir, G5_DIR_PERMISSION, true);
  @chmod($signdir, G5_DIR_PERMISSION);
}

foreach($state as $id => $val) {
  $key = explode('_', $id);

  // 서명일 경우 서명 이미지 저장
  if($key[0] === 'sign') {
    $encoded_image = explode(",", $val)[1];
    $decoded_image = base64_decode($encoded_image);

    $filename = $uuid."_".$eform['penId']."_".$id."_".date("YmdHisw").".png";
    file_put_contents("$signdir/$filename", $decoded_image);

    $val = "/data/eform/sign/{$filename}";
  }

  sql_query("INSERT INTO `eform_document_content` SET
  `dc_id` = UNHEX('$uuid'),
  `ct_id` = '$id',
  `ct_content` = '$val'
  ");
}

$ip = $_SERVER['REMOTE_ADDR'];
$browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

// 계약서 로그 작성
$log = '전자계약서에 서명했습니다.';

sql_query("INSERT INTO `eform_document_log` SET
`dc_id` = UNHEX('$uuid'),
`dl_log` = '$log',
`dl_ip` = '$ip',
`dl_browser` = '$browser',
`dl_datetime` = '$datetime'
");

// PDF 파일 생성
$pdfdir = G5_DATA_PATH.'/eform/pdf';
if(!is_dir($pdfdir)) {
  @mkdir($pdfdir, G5_DIR_PERMISSION, true);
  @chmod($pdfdir, G5_DIR_PERMISSION);
}
$pdffile = $uuid.'_'.$eform['penId'].'_'.$eform['entId'].'_'.date("YmdHisw").'.pdf';
$pdfdir .= '/'.$pdffile;
include_once('./lib/renderpdf.lib.php');

// 감사 추적 인증서 PDF 파일 생성
$certdir = G5_DATA_PATH.'/eform/cert';
if(!is_dir($certdir)) {
  @mkdir($certdir, G5_DIR_PERMISSION, true);
  @chmod($certdir, G5_DIR_PERMISSION);
}
$certfile = $uuid.'_'.$eform['penId'].'_'.$eform['entId'].'_cert_'.date("YmdHisw").'.pdf';
$certdir .= '/'.$certfile;
include_once('./lib/rendercertpdf.lib.php');

// 계약서 정보 업데이트
sql_query("UPDATE `eform_document` SET
`dc_status` = '2',
`dc_sign_datetime` = '$datetime',
`dc_sign_ip` = '$ip',
`dc_pdf_file` = '$pdffile',
`dc_cert_pdf_file` = '$certfile'
WHERE `dc_id` = UNHEX('$uuid')
");

// 문자 발송
$send_hp = '02-830-1301';
$recv_hp = $eform['penConNum'];

$send_hp = str_replace('-', '', $send_hp);
$recv_hp = str_replace('-', '', $recv_hp);

$link = G5_SHOP_URL.'/eform/eformInquiry.php?id='.$uuid;
$msg = "[이로움]\n{$eform['penNm']}님 '".mb_substr($eform['entNm'], 0, 8, 'utf-8')."' 사업소와 전자계약이 체결되었습니다.\n\n* 문서확인 : {$link}";

$port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);
if($port_setting !== false && $recv_hp) {
    $SMS = new LMS;
    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

    $strDest     = array();
    $strDest[]   = $recv_hp;
    $strCallBack = $send_hp;
    $strCaller   = iconv_euckr('이로움');
    $strSubject  = iconv_euckr('[이로움] 계약체결완료');
    $strURL      = '';
    $strData     = iconv_euckr($msg);
    $strDate     = '';
    $nCount      = count($strDest);

    $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

    $SMS->Send();
}

json_response(200, 'OK');
?>
