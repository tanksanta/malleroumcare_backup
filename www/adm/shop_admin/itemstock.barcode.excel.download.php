<?php
$sub_menu = '400620';
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

include_once(G5_LIB_PATH . '/PHPExcel/Cell/DefaultValueBinder.php');

class PHPExcel_Cell_MyColumnValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
  protected $stringColumns = [];

  public function __construct(array $stringColumnList = [])
  {
    // Accept a list of columns that will always be set as strings
    $this->stringColumns = $stringColumnList;
  }

  public function bindValue(PHPExcel_Cell $cell, $value = null)
  {
    // If the cell is one of our columns to set as a string...
    if (in_array($cell->getColumn(), $this->stringColumns)) {
      // ... then we cast it to a string and explicitly set it as a string
      $cell->setValueExplicit((string)$value, PHPExcel_Cell_DataType::TYPE_STRING);
      return true;
    }
    // Otherwise, use the default behaviour
    return parent::bindValue($cell, $value);
  }
}

// 데이터 처리
$data = [];

$sql = "
	SELECT 
		bc_id, 
		ct_id, 
		it_id, 
		io_id, 
		bc_barcode, 
		bc_status,
		bc_is_check_yn,
		bc_del_yn,
		bc_del_yn AS origin_del_yn,
		checked_by,
		bc_memo,
		DATE_FORMAT(checked_at, '%m/%d') AS checked_at,
		DATE_FORMAT(rentaled_at, '%m/%d') AS rentaled_at,
		DATE_FORMAT(released_at, '%m/%d') AS released_at,
		DATE_FORMAT(deleted_at, '%m/%d') AS deleted_at,
		checked_at AS checked_at_full,
    (SELECT count(*) FROM g5_cart_barcode_log 
      WHERE bch_barcode = bc.bc_barcode 
      AND it_id = bc.it_id AND io_id = bc.io_id) AS history_cnt
  FROM g5_cart_barcode bc
  WHERE 
    it_id = '{$it_id}'
    AND io_id = '{$io_id}'
    AND bc_status != '출고' AND bc_del_yn = 'N'
  ORDER BY
    bc_id DESC
";

$result = sql_query($sql);

$row = get_stock_item_info($it_id, $io_id);

$option = '';
$option_br = '';
if ($row['io_type']) {
  $opt = explode(chr(30), $row['io_id']);
  if ($opt[0] && $opt[1])
    $option .= $opt[0] . ' : ' . $opt[1];
} else {
  $subj = explode(',', $row['it_option_subject']);
  $opt = explode(chr(30), $row['io_id']);
  for ($k = 0; $k < count($subj); $k++) {
    if ($subj[$k] && $opt[$k]) {
      $option .= $option_br . $subj[$k] . ' : ' . $opt[$k];
      $option_br = ' / ';
    }
  }
}

$full_it_name = $row['it_name'];
if ($option) {
  $full_it_name .= " ({$option})";
}

while ($row = sql_fetch_array($result)) {
  $data[] = [
    $full_it_name,
    $row['bc_barcode'],
    $row['checked_at'] ?: '미확인',
  ];
}


// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load(G5_DATA_PATH.'/itemstock_barcode_list_form.xlsx');
$sheet = $excel->getActiveSheet();

$last_row = count($data) + 1;
if ($last_row < 2) $last_row = 2;

// 열 높이
for ($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}

// 가운데 정렬
$sheet->getStyle('A:C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_MyColumnValueBinder(['A', 'B', 'C']));

$sheet->fromArray($data,NULL,'A2');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"상품_바코드_리스트.xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
