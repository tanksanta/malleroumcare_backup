<?php
include_once('./_common.php');
define('_INVENTORY_', true);

if (!$member['mb_id'] || !$member['mb_entId'] || !$gubun) {
  alert('잘못된 요청입니다.');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$sendData = array(
  'usrId' => $member["mb_id"],
  'entId' => $member["mb_entId"],
  'gubun' => $gubun,
);

if($_GET['searchtype']){
  if($_GET['searchtype']=="1"){
      $sendData["prodNm"] = ($_GET["searchtypeText"]) ? $_GET["searchtypeText"] : "";
  }else{
      $sendData["prodId"] = ($_GET["searchtypeText"]) ? $_GET["searchtypeText"] : "";
  }
}

$cells = array(
  'A' => array(15, 'num', '번호'),
  'B' => array(30, 'name',  '상품정보'),
  'C' => array(30, 'code', '제품코드'),
  'D' => array(20, 'qty', '재고'),
  'E' => array(20, 'ordered_qty', '판매완료'),
  'F' => array(20, 'cust_price', '급여가')
);

$res = api_post_call(EROUMCARE_API_STOCK_LIST, $sendData);
$list = [];
if($res["data"]){
    // $list = $res["data"];
    $i = count($res['data']);
    foreach($res['data'] as $data) {

      $sql = 'SELECT  `it_taxInfo`, `it_img1`, `it_cust_price` FROM `g5_shop_item` WHERE `it_id`="'.$data['prodId'].'"';
      $row = sql_fetch($sql);
      
      $list[] = array(
        'num' => $i--,
        'name' => $data['prodNm'], // 상품정보
        'code' => $data['prodPayCode'], // 제품코드
        'qty' => $data['quantity'], // 재고
        'ordered_qty' => $data['orderQuantity'], // 판매완료
        'cust_price' => $row['it_cust_price'], // 재고가
      );
    }
}

// print_r2($list);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

foreach ($cells as $key => $val) {
  $cellName = $key.'1';

  $sheet->getColumnDimension($key)->setWidth($val[0]);
  $sheet->getRowDimension('1')->setRowHeight(20);
  $sheet->setCellValue($cellName, $val[2]);
  $sheet->getStyle($cellName)->getFont()->setBold(true);
  // $sheet->getStyle($cellName)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
  $sheet->getStyle($cellName)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
}


for ($i = 2; $row = array_shift($list); $i++) {
  foreach ($cells as $key => $val) {
      $sheet->setCellValue($key.$i, $row[$val[1]]);
  }
}

$filename = $gubun === '00' ? '판매재고' : '대여재고';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');