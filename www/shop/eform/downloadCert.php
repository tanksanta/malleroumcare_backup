<?php
include_once("./_common.php");

if($dc_id && $penLtmNum && $penNm && $penBirth) {
  $sql = "select * from `eform_document` where
          dc_id = UNHEX('$dc_id') and
          penLtmNum = '$penLtmNum' and
          penNm = '$penNm' and
          penBirth = '$penBirth' and
          dc_status = '2'";

  $eform = sql_fetch($sql);

  if(!$eform['dc_id']) alert('존재하지 않는 계약서입니다.');
} else {
  if(!$is_member) {
    alert('먼저 로그인하세요.');
  }

  $sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '$od_id'";
  if($is_member && !$is_admin)
      $sql .= " AND mb_id = '{$member['mb_id']}' ";
  $od = sql_fetch($sql);
  if(!$od['mb_id']) {
    alert('감사추적인증서를 다운로드할 권한이 없습니다.');
  }

  $eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");

  if($eform['dc_status'] != '2') {
    alert('감사추적인증서가 작성되지 않았습니다.');
  }
}


$certdir = G5_DATA_PATH.'/eform/cert';
$certfile = $eform['dc_cert_pdf_file'];

header("Content-type: application/pdf");

if($dc_id)
  header("Content-Disposition: inline; filename=\"감사추적인증서_{$eform['dc_subject']}.pdf\"");
else
  header("Content-Disposition: attachment; filename=\"감사추적인증서_{$eform['dc_subject']}.pdf\"");

@readfile($certdir.'/'.$certfile);
?>
