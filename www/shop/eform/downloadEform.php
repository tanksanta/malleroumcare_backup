<?php
include_once("./_common.php");

$dc_id = get_search_string($_GET['dc_id']);
if($dc_id) {
  if($penLtmNum && $penNm) {
    $sql = "select * from `eform_document` where
            dc_id = UNHEX('$dc_id') and
            penLtmNum = '$penLtmNum' and
            penNm = '$penNm' and
            (dc_status = '2' or dc_status = '3')";

    $eform = sql_fetch($sql);

    if(!$eform['dc_id']) alert('존재하지 않는 계약서입니다.');
  } else {
    $eform = sql_fetch("
    SELECT HEX(`dc_id`) as uuid, e.*
    FROM `eform_document` as e
    WHERE dc_id = UNHEX('$dc_id') and entId = '{$member['mb_entId']}' and dc_status = '3' ");
    if(!$eform['uuid']) {
      die('계약서를 확인할 수 없습니다.');
    }

    // $is_simple_eform = true;
  }
  $is_simple_eform = true;

} else {
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

  if($eform['dc_status'] != '2' && $eform['dc_status'] != '3') {
    alert('계약서가 작성되지 않았습니다.');
  }
}

$pdfdir = G5_DATA_PATH.'/eform/pdf';
$pdffile = $eform['dc_pdf_file'];

if($eform['dc_status'] == '3' && !$is_simple_eform) {
  // 마이그레이션한 이전 계약서
  $pdfdir = G5_DATA_PATH.'/eform/legacy';
  $pdffile .= '/ALL.pdf';
}

header("Content-type: application/pdf");

if($dc_id)
  header("Content-Disposition: inline; filename=\"{$eform['dc_subject']}.pdf\"");
else
  header("Content-Disposition: attachment; filename=\"{$eform['dc_subject']}.pdf\"");

@readfile($pdfdir.'/'.$pdffile);

?>