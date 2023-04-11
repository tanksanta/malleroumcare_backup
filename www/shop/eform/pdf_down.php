<?php
include_once("./_common.php");

if(!$is_member) {
  json_response(400, '먼저 로그인하세요.');
}

$uuid = $_POST['uuid'];
//$state = json_decode(stripslashes($_POST['state']), true);
if(!$uuid) {
  json_response(400, '잘못된 요청입니다.');
}

$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
if(!$eform['dc_id']) {
  json_response(500, '서명할 계약서를 찾을 수 없습니다.');
}

// PDF 파일 생성
$pdfdir = G5_DATA_PATH.'/eform/pdf';
if(!is_dir($pdfdir)) {
  @mkdir($pdfdir, G5_DIR_PERMISSION, true);
  @chmod($pdfdir, G5_DIR_PERMISSION);
}
$pdffile = $uuid.'_'.$eform['penId'].'_'.$eform['entId'].'_view_'.date("YmdHisw").'.pdf';
$pdffile2 = $eform['dc_subject'].'.pdf';
$pdfdir .= '/'.$pdffile;

$options = array(
  'enable-javascript',
  'no-stop-slow-scripts',
  'javascript-delay' => 500,
  'page-size' => 'A4',  
  'no-outline',
  'encoding' => 'UTF-8',
  'margin-top'    => 15,
  'margin-right'  => 0,
  'margin-bottom' => 0,
  'margin-left'   => 0,
  'viewport-size' => 1240
);

$args = '';
foreach($options as $key => $val) {
  if(is_int($key)) {
    $key = $val;
    $val = null;
  }

  $args .= ' --'.$key;
  if($val !== null) $args .= ' '.$val;
}

$G5_URL = G5_URL;
exec("wkhtmltopdf{$args} \"{$G5_URL}/shop/eform/renderEform_new.php?dc_id={$uuid}&entId={$eform['entId']}&download=1\" \"{$pdfdir}\"");

header('Content-Type: text/html; charset=UTF-8');

//tmp폴더의 test.pdf를 클라이언트가 다운받을 수 있도록
$sFilePath = $pdfdir;
$sFileName = $pdffile2;

header("Content-Disposition: attachment; filename=\"".$sFileName."\"");
header('Content-type: application/pdf');
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".strval(filesize($sFilePath)));
header("Cache-Control: cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo file_get_contents($sFilePath);
flush();
unlink($sFilePath);//tmp폴더의 test.pdf 파일을 삭제
?>
