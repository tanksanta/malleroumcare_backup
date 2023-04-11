<?php
include_once("./_common.php");
ini_set("display_errors", 0);
if ($is_admin != 'super' && $member["mb_level"] < "9") {
  alert('최고관리자만 접근 가능합니다.');
  exit;
}

$body = $_POST['table_body'];
$body = urldecode($body);
$type = $_POST['type'];
$sel_date = $_POST['sel_date'];
if (!$type || $type == 'login') {
    $filetype = "로그인 로그";
} else if ($type == 'order') {
    $filetype = "주문서 로그";
} else if ($type == 'eform') {
    $filetype = "계약서 로그 ";
    if($sel_date == 'dc_datetime'){
        $filetype = "계약서 로그(계약서생성일)";
    } else if ($sel_date == 'dc_sign_datetime') {
        $filetype = "계약서 로그(계약서서명일)";
    }
} else if ($type == 'item_msg') {
    $filetype = "제안서 로그 ";
    if($sel_date == 'ms_created_at'){
        $filetype = "제안서 로그(제안서생성일) ";
    } else if ($sel_date == 'ml_sent_at') {
        $filetype = "제안서 로그(제안서발송일)";
    }
} else if ($type == 'check_itcare') {
    $filetype = "요양정보조회 로그";
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$filename = $filetype."(".date("YmdHis").")";
// save $table inside temporary file that will be deleted later
$tmpfile = tempnam(sys_get_temp_dir(), 'html');
file_put_contents($tmpfile, $body);

$objPHPExcel     = new PHPExcel();
$excelHTMLReader = PHPExcel_IOFactory::createReader('HTML');
$excelHTMLReader->loadIntoExisting($tmpfile, $objPHPExcel);

$sheet = $objPHPExcel -> getActiveSheet();
$highestRow = $sheet -> getHighestRow(); // 마지막 행
$highestColumn = $sheet -> getHighestColumn(); // 마지막 컬럼
$lastCell = $highestColumn.$highestRow;

$objPHPExcel->getActiveSheet()->getStyle('A1:'.$lastCell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT); // 숫자도 다 텍스트 형식으로 출력하도록
$objPHPExcel->getActiveSheet()->getStyle('A1:'.$lastCell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 중앙정렬 세로
$objPHPExcel->getActiveSheet()->getStyle("A1:".$lastCell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // 중앙정렬 가로

$objPHPExcel->getActiveSheet()->setTitle($filetype); // Change sheet's title if you want

unlink($tmpfile); // delete temporary file because it isn't needed anymore

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // header for .xlxs file
header("Content-Disposition: attachment; filename={$filename}.xls");
header('Cache-Control: max-age=0');

// Creates a writer to output the $objPHPExcel's content
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

?>
