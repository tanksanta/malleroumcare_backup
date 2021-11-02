<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

if($member['mb_type'] !== 'default')
  json_response(400, '사업소 회원만 이용할 수 있습니다.');

$selected_month = $_POST['selected_month'];

if(!$selected_month)
  json_response(400, '요청이 유효하지 않습니다.');

function parse_pen_type($penType, $penRate) {
  $penTypeCd = '';

  switch($penRate) {
    case 14:
    case 15:
    case 16:
      if($penType === '일반')
        $penTypeCd = '00'; // 일반 15%
      break;
    case 8:
    case 9:
    case 10:
      if($penType === '일반')
        $penTypeCd = '01'; // 감경 9%
      break;
    case 5:
    case 6:
    case 7:
      if($penType === '일반')
        $penTypeCd = '02'; // 감경 6%
      else if($penType === '의료')
        $penTypeCd = '03'; // 의료 6%
      break;
    case 0:
      if($penType === '의료')
        $penTypeCd = '04'; // 기초 0%
      break;
  }

  return $penTypeCd;
}

$pen_arr = [];

if($file = $_FILES['nhisfile']['tmp_name']) {
  include_once('../lib/Excel/reader.php');

  $reader = new Spreadsheet_Excel_Reader();
  $reader->setOutputEncoding('UTF-8');
  $reader->read($file);

  $sheet_year = $reader->sheets[0]['cells'][2][17];
  $sheet_month = $reader->sheets[0]['cells'][2][20];
  if($selected_month != "{$sheet_year}-{$sheet_month}-01")
    json_response(400, '현재 선택한 달('.date('Y년 m월', strtotime($selected_month)).')의 건보자료가 아닙니다.');

  $sheets_count = count($reader->sheets);

  for($i = 0; $i < $sheets_count; $i++) {
    $sheet = $reader->sheets[$i];
    $num_rows = $sheet['numRows'];

    // 값이 비어있으면 넘김
    if($sheet['cells'][$i == 0 ? 6 : 2][3] === '') continue;

    for($r = ($i == 0 ? 6 : 2); $r < $num_rows; $r++) {
      $row = $sheet['cells'][$r];
      
      $pen = array(
        'penType' => trim($row[3]),
        'penNm' => trim($row[4]),
        'penLtmNum' => trim($row[$i == 0 ? 7 : 5]),
        'penRecGraNm' => trim($row[$i == 0 ? 9 : 6]),
        'start_date' => date('Y-m-d', strtotime($row[$i == 0 ? 10 : 7])),
        'total_price' => intval(preg_replace("/[^0-9]/", "", $row[$i == 0 ? 12 : 8])),
        'total_price_pen' => intval(preg_replace("/[^0-9]/", "", $row[$i == 0 ? 13 : 9])),
        'total_price_ent' => intval(preg_replace("/[^0-9]/", "", $row[$i == 0 ? 16 : 10]))
      );
      $pen['penRate'] = intval($pen['total_price_pen'] == 0 ? 0 : round($pen['total_price_pen'] * 100 / $pen['total_price']));
      $pen['penTypeCd'] = parse_pen_type($pen['penType'], $pen['penRate']);
      $pen['penTypeNm'] = $pen_type_cd[$pen['penTypeCd']];
      $pen_arr[] = $pen;
    }
  }
} else {
  json_response('400', '파일을 선택해주세요.');
}

if(!$pen_arr)
  json_response(400, '공단파일에 수급자가 없습니다.');

$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

sql_query("
  INSERT INTO
    `claim_nhis_upload`
  SET
    mb_id = '{$member['mb_id']}',
    selected_month = '$selected_month',
    created_at = '$datetime',
    updated_at = '$datetime'
");
$cu_id = sql_insert_id();

if(!$cu_id)
  json_response(500, '업로드에 실패했습니다.');

$values = [];
foreach($pen_arr as $pen) {
  $val = array(
    $cu_id,
    $pen['penType'],
    $pen['penNm'],
    $pen['penLtmNum'],
    $pen['penRecGraNm'],
    $pen['penRate'],
    $pen['penTypeCd'],
    $pen['penTypeNm'],
    $pen['start_date'],
    $pen['total_price'],
    $pen['total_price_pen'],
    $pen['total_price_ent']
  );
  $values[] = "('".implode("', '", $val)."')";
}

$result = sql_query(
  "
  insert into `claim_nhis_content`
    (cu_id, penType, penNm, penLtmNum, penRecGraNm, penRate, penTypeCd, penTypeNm, start_date, total_price, total_price_pen, total_price_ent)
  values
  ".implode(', ', $values)
);

if(!$result) {
  sql_query("DELETE FROM `claim_nhis_upload` WHERE cu_id = '$cu_id'");

  json_response(500, '업로드에 실패했습니다.');
}

json_response(200, 'OK');
?>
