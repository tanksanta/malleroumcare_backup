<?php
include_once("./_common.php");

$dc_id = $_POST['dc_id'];
$penLtmNum = $_POST['penLtmNum'];
$penNm = $_POST['penNm'];
$penBirth1 = $_POST['penBirth1'];
$penBirth2 = $_POST['penBirth2'];
$penBirth3 = $_POST['penBirth3'];
$penBirth = $penBirth1.'.'.$penBirth2.'.'.$penBirth3;


if(!$dc_id) alert('잘못된 접근입니다.');
$sql = "select * from `eform_document` where
          dc_id = UNHEX('$dc_id') and
          penLtmNum = '$penLtmNum' and
          penNm = '$penNm' and
          penBirth = '$penBirth' and
          (dc_status = '2' or dc_status = '3')";

$eform = sql_fetch($sql);

if(!$eform['dc_id']) alert('존재하지 않는 주문입니다.');

$pdfdir = G5_DATA_PATH.'/eform/pdf';
$pdffile = $eform['dc_pdf_file'];

if($eform['dc_status'] == '3') {
  // 마이그레이션한 이전 계약서
  $pdfdir = G5_DATA_PATH.'/eform/legacy';
  $pdffile .= '/ALL.pdf';
}

header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=\"{$eform['dc_subject']}.pdf\"");

@readfile($pdfdir.'/'.$pdffile);
?>
