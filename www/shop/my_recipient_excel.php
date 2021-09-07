<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default' || !$member['mb_entId']) {
  alert('사업소 회원만 접근할 수 있습니다.');
}

$result = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId']
));

$data = [];
if($result['data']) {
  foreach($result['data'] as $pen) {
    $penExpiDtm = explode(" ~ ", $pen["penExpiDtm"]);
    $penExpiStDtm = $pen["penExpiDtm"] ? $penExpiDtm[0] : '';
    $penExpiEdDtm = $pen["penExpiDtm"] ? $penExpiDtm[1] : '';

    $penProRel = $penCnmType = $penRecType = '';

    if($pen['penProRel'] != '11') {
      $penProRel = $pen_pro_rel_cd[$pen['penProRel']];
    } else {
      $penProRel = $pen['penProRelEtc'];
    }

    if($pen['penCnmTypeCd'] == '00')
      $penCnmType = '수급자';
    else if($pen['penCnmTypeCd'] == '01')
      $penCnmType = '보호자';
    
    if($pen['penRecTypeCd'] == '00')
      $penRecType = '방문';
    else if($pen['penRecTypeCd'] == '01')
      $penRecType = '유선';

    $data[] = [
      $pen['penNm'],
      $pen['penJumin'],
      $pen['penBirth'],
      $pen['penGender'],
      $pen['penConNum'],
      $pen['penConPnum'],
      $pen['penZip'],
      $pen['penAddr'],
      $pen['penAddrDtl'],
      $pen['penLtmNum'],
      $pen['penRecGraNm'],
      $pen['penTypeNm'],
      $penExpiStDtm,
      $penExpiEdDtm,
      $pen['penGraApplyDate'],
      $penProRel,
      $pen['penProNm'],
      $pen['penProBirth'],
      $pen['penProEmail'],
      $pen['penProConNum'],
      $pen['penProConPnum'],
      $pen['penProZip'],
      $pen['penProAddr'],
      $pen['penProAddrDtl'],
      $penCnmType,
      $penRecType,
      $pen['penRemark']
    ];
  }
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load(G5_DATA_PATH.'/recipient_list_form.xlsx');
$sheet = $excel->getActiveSheet();

$last_row = count($data) + 2;
if($last_row < 3) $last_row = 3;

// 스타일 적용
$styleArray = array(
  'font' => array(
    'size' => 11,
    'name' => 'Malgun Gothic'
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
  )
);
$sheet->getStyle('A3:AA'.$last_row)->applyFromArray($styleArray);

$sheet->fromArray($data,NULL,'A3');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"이로움_수급자목록.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
?>
