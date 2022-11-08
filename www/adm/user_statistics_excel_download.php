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
if (!$type || $type == 'user') {
    $filename = "회원등급";
} else if ($type == 'region') {
    $filename = "사업소지역";
} else if ($type == 'amount') {
    $filename = "매출금액";
} else if ($type == 'proposal_c') {
    $filename = "제안서생성";
} else if ($type == 'proposal_s') {
    $filename = "제안서발송";
} else if ($type == 'contract_c') {
    $filename = "계약서생성";
} else if ($type == 'contract_s') {
    $filename = "계약서서명";
} else if ($type == 'order_c') {
    $filename = "주문서생성";
} else if ($type == 'order_user') {
    $filename = "주문서생성(사업소별)";
} else if ($type == 'login_daily') {
    $filename = "방문자집계(일자별)";
} else if ($type == 'login_user') {
    $filename = "방문자집계(사업소별)";
} else if ($type == 'recipient') {
    $filename = "등록한수급자";
} else if ($type == 'inquire_data') {
    if($page == 'all'){
        $filename = "요양정보 조회 집계(전체사업소) ".$todate;
    } else if ($page == 'ent') {
        $filename = "요양정보 조회 집계(사업소별)";
    } else {
        $filename = "요양정보 조회 집계(일자별)";
    }
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
