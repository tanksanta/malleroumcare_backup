<?php
include_once("./_common.php");

if(!$is_member) {
  alert('먼저 로그인하세요.');
}

$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '$od_id'";
if($is_member && !$is_admin)
    $sql .= " AND mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['mb_id']) {
  alert('계약서를 다운로드할 권한이 없습니다.');
}

$eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");

if($eform['dc_status'] != '2' || $eform['dc_status'] != '3') {
  alert('계약서가 작성되지 않았습니다.');
}

$pdfdir = G5_DATA_PATH.'/eform/pdf';
$pdffile = $eform['dc_pdf_file'];

if($eform['dc_status'] == '3') {
  // 마이그레이션한 이전 계약서
  $pdfdir = G5_DATA_PATH.'/eform/legacy';
  $pdffile .= '/ALL.pdf';
}

header("Content-type: application/pdf");
header("Content-Disposition: attachment; filename=\"{$eform['dc_subject']}.pdf\"");

@readfile($pdfdir.'/'.$pdffile);
?>
