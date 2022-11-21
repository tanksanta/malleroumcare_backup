<?php
include_once("./_common.php");

$dc_id = get_search_string($_GET['dc_id']);
if($dc_id) {
  if($penLtmNum && $penNm) {
    $sql = "select * from `eform_document` where
            dc_id = UNHEX('$dc_id') and
            penLtmNum = '$penLtmNum' and
            penNm = '$penNm' and
            dc_status = '2'";

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

    $is_simple_eform = true;
  }
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

// 시작 -->
// 서원 : 22.09.01 - 감사 추적 인증서 pdf파일 생성 과정에 코드상의 문제로 인하여 실제 파일과 DB에 입력되는 파일명이 마이크로초 단위로 다르게 저장됨.
//                   해당 코드부분을 수정하고 기존에 이미 생성된 파일에 대한 컨버전 DB작업이나 파일 명칭 변경 작업은 위험상이 따름에 따라
//                   다운로드 화면인 본 파일에서 마이크로초를 제외한 부분을 검색하고 동일한 파일명일 있으면 해당 변수의 값을 치환 하는 방식으로 처리함.
//

$is_file_exist = $certdir.'/'.$certfile;
$len = strlen($certfile)-6;
if ( !file_exists($is_file_exist) ) {

  // 서원 : 22.09.01 - DB에서 가져온 파일이 없을 경우 확장자와 끝부분 마이크로초 2자리를 제외하고 파일을 검색하여 찾는다.
  $_File = mb_substr($certfile,0,mb_strrpos($certfile,".")-2);
  $opendir = opendir($certdir);
  if( $opendir ) {

    // 서원 : 22.09.01 - 파일 시스템의 특성상 폴더 전체 검색...
    //                   추후 파일이 많아지게 되면 해당 파일명 DB명 컨버전 또는 파일 체계 변경 작업 필요 예상.
    //                   년도별,월별 폴더를 나눠 놓지 않아 파일이 무제한 한 폴더에 생성될 가능 성이 있음.

    while( false !== ($file = readdir($opendir)) ) {
      // 서원 : 22.09.01 - 마이크로초 부분 (끝2자리)를 제외한 파일명이 동일한 파일 검색.
      //                   해당 파일이 있을 경우 기존 $certfile 변수에 있던 데이터를 검색된 파일 명인 $file 변수 값으로 변경 한다.
      if( !strncmp( $_File , $file , $len ) ) { $certfile = $file; }
    }
  }
	
}
//
// 종료 -->


header("Content-type: application/pdf");

if($dc_id)
  header("Content-Disposition: inline; filename=\"감사추적인증서_{$eform['dc_subject']}.pdf\"");
else
  header("Content-Disposition: attachment; filename=\"감사추적인증서_{$eform['dc_subject']}.pdf\"");

@readfile($certdir.'/'.$certfile);
?>
