<?php
include_once('./_common.php');

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

if($file = $_FILES['file']['tmp_name']) {
  include_once('../lib/Excel/reader.php');

  $reader = new Spreadsheet_Excel_Reader();
  $reader->setOutputEncoding('UTF-8');
  $reader->read($file);

  $sheets_count = count($reader->sheets);

  $pen_arr = [];
  for($i = 0; $i < $sheets_count; $i++) {
    $sheet = $reader->sheets[$i];
    $num_rows = $sheet['numRows'];

    // 값이 비어있으면 넘김
    if($row[3] === '') continue;

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
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>테스트</title>
</head>
<body>
  <form method="post" enctype="MULTIPART/FORM-DATA">
    <input type="file" name="file" id="file">
    <input type="submit" value="전송">
  </form>
  <?php
  print_r2($pen_arr);
  ?>
</body>
</html>