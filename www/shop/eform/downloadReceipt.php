<?php
include_once('./_common.php');

include_once(G5_LIB_PATH.'/PHPExcel.php');

$reader = PHPExcel_IOFactory::createReader('Excel5');
$excel = $reader->load('receiptForm.xls');

# 사업소 정보
$excel->getActiveSheet()->setCellValue('E5', $member['mb_entNm']);
$excel->getActiveSheet()->setCellValue('L5', $member['mb_giup_btel']);
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"거래영수증.xls\"");
header("Cache-Control: max-age=0");

# 직인
$excel->getActiveSheet()->setCellValue('A29', "{$member['mb_entNm']} 사업소 (인)");
$seal_path = G5_DATA_PATH."/file/member/stamp/{$member['sealFile']}";
$seal = new PHPExcel_Worksheet_Drawing();
$seal->setName('직인');
$seal->setDescription('직인 이미지');
$seal->setPath($seal_path);
$seal->setCoordinates('H29');
$seal->setOffsetX(20);
$seal->setOffsetY(5);
$seal->setHeight(100);
$seal->setResizeProportional(true);
$seal->setWorksheet($excel->getActiveSheet());

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
