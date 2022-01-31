<?php
$sub_menu = '400620';
include_once("./_common.php");

$auth_check = auth_check($auth[$sub_menu], "w", true);
if($auth_check)
  json_response(400, $auth_check);

// 업로드 파일 확인
$file = $_FILES['datafile']['tmp_name'];
if (!$file) {
  json_response(400, '파일을 선택해주세요.');
}

// 현재 데이터 불러오기
$origin_data_arr = [];
$origin_data_map = [];

$sql = "
  SELECT it.it_id, it.it_name, io.io_id, io.io_standard, it.it_option_subject, ws.ws_option, IFNULL(ws.ws_qty, '0') AS ws_qty
  FROM g5_shop_item it
  LEFT JOIN (SELECT * FROM g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS io ON it.it_id = io.it_id
  LEFT JOIN (SELECT it_id, io_id, ws_option, (SUM(ws_qty) - SUM(ws_scheduled_qty)) AS ws_qty FROM warehouse_stock GROUP BY it_id, io_id) AS ws ON (io.it_id = ws.it_id AND io.io_id = ws.io_id)
  ORDER BY it.it_name ASC
";

$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {
  $io_value = '';
  if ($row['io_id'] && $row['it_option_subject']) {
    $it_option_subjects = explode(',', $row['it_option_subject']);
    $io_ids = explode(chr(30), $row['io_id']);

    for ($g = 0; $g < count($io_ids); $g++) {
      if ($g > 0) {
        $io_value .= ' / ';
      }
      $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
    }
  }

  $row['io_value'] = $io_value;

  $origin_data_arr[] = [
    $row['it_id'],
    $row['io_id'],
    $row['it_name'],
    $row['io_value'],
//    $row['ws_qty'],
  ];

  $origin_data_map[] = [
    'it_id' => $row['it_id'],
    'io_id' => $row['io_id'],
    'it_name' => $row['it_name'],
    'io_value' => $row['io_value'],
    'ws_qty' => $row['ws_qty'],
  ];
}

// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load($file);
$sheet = $excel->getActiveSheet();

$last_col = $sheet->getHighestDataColumn();
$num_cols = PHPExcel_Cell::columnIndexFromString($last_col);
$num_rows = $sheet->getHighestDataRow();


// 컬럼 수 검증
if ($num_cols != 5) {
  json_response(400, '가로 컬럼 수가 맞지 않습니다 (기본: 5개)');
}

// 업로드 엑셀 데이터 처리
$upload_data_arr = [];
$upload_data_map = [];
for ($idx = 2; $idx <= $num_rows; $idx++) { // 세로 반복
  $it_id = sql_real_escape_string(trim($sheet->getCell('A'.$idx)->getValue()));
  $io_id = sql_real_escape_string(trim($sheet->getCell('B'.$idx)->getValue()));
  $it_name = sql_real_escape_string(trim($sheet->getCell('C'.$idx)->getValue()));
  $io_value = sql_real_escape_string(trim($sheet->getCell('D'.$idx)->getValue()));
  $ws_qty = sql_real_escape_string(trim($sheet->getCell('E'.$idx)->getValue()));

  $upload_data_arr[] = [
    $it_id,
    $io_id,
    $it_name,
    $io_value,
//    $ws_qty,
  ];

  $upload_data_map[] = [
    'it_id' => $it_id,
    'io_id' => $io_id,
    'it_name' => $it_name,
    'io_value' => $io_value,
    'ws_qty' => $ws_qty,
  ];
}

// DB 데이터와 비교 (상품코드, 옵션코드, 상품명, 옵션명)
if (count($origin_data_arr) != count($upload_data_arr)) {
  json_response(400, '데이터 갯수가 맞지 않습니다');
}
for ($j = 0; $j < count($origin_data_arr); $j++) {
  if (count(array_diff($origin_data_arr[$j], $upload_data_arr[$j])) > 0) {
    json_response(400, '상품코드, 옵션코드, 상품명, 옵션명이 변경되어 업로드가 불가능합니다.다운 받은 엑셀파일에서 수량만 변경 후 다시 업로드해주세요.');
  }
}

// DB 수정
$ws_memo = clean_xss_tags($ws_memo);

for ($j = 0; $j < count($upload_data_map); $j++) {
  $it_id = $upload_data_map[$j]['it_id'];
  $io_id = $upload_data_map[$j]['io_id'];
  $it_name = $upload_data_map[$j]['it_name'];
  $io_value = $upload_data_map[$j]['io_value'];
  $ws_qty = intval($upload_data_map[$j]['ws_qty']) - intval($origin_data_map[$j]['ws_qty']);

  if ( $ws_qty != 0 && (intval($upload_data_map[$j]['ws_qty']) != intval($origin_data_map[$j]['ws_qty'])) ) {
    $sql = "
    INSERT INTO warehouse_stock
    SET
      it_id = '{$it_id}',
      io_id = '{$io_id}',
      io_type = '0',
      it_name = '{$it_name}',
      ws_option = '{$io_value}',
      ws_qty = '{$ws_qty}',
      ws_scheduled_qty = '0',
      mb_id = '{$member['mb_id']}',
      ws_memo = '{$ws_memo}',
      wh_name = '{$wh_name}',
      od_id = '0',
      ct_id = '0',
      inserted_from = 'stock_edit_excel',
      ws_created_at = NOW(),
      ws_updated_at = NOW()
  ";
    sql_query($sql);
  }
}


json_response(200, 'OK');
?>
