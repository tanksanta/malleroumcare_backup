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
    $filename = "로그인 로그";
} else if ($type == 'order') {
    $filename = "주문서 로그";
} else if ($type == 'eform') {
    $filename = "계약서 로그 ";
    if($sel_date == 'dc_datetime'){
        $filename = "계약서 로그(계약서생성일)";
    } else if ($sel_date == 'dc_sign_datetime') {
        $filename = "계약서 로그(계약서서명일)";
    }
} else if ($type == 'item_msg') {
    $filename = "제안서 로그 ";
    if($sel_date == 'ms_created_at'){
        $filename = "제안서 로그(제안서생성일) ";
    } else if ($sel_date == 'ml_sent_at') {
        $filename = "제안서 로그(제안서발송일)";
    }
} else if ($type == 'check_itcare') {
    $filename = "요양정보조회 로그";
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$filename = $filename."(".date("YmdHis").")";
// save $table inside temporary file that will be deleted later
$tmpfile = tempnam(sys_get_temp_dir(), 'html');
file_put_contents($tmpfile, $body);

$objPHPExcel     = new PHPExcel();
$excelHTMLReader = PHPExcel_IOFactory::createReader('HTML');
$excelHTMLReader->loadIntoExisting($tmpfile, $objPHPExcel);
// $objPHPExcel->getActiveSheet()->setTitle('any name you want'); // Change sheet's title if you want

unlink($tmpfile); // delete temporary file because it isn't needed anymore

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // header for .xlxs file
header("Content-Disposition: attachment; filename={$filename}.xls");
header('Cache-Control: max-age=0');

// Creates a writer to output the $objPHPExcel's content
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

?>
