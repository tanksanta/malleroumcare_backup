<?php
include_once('./_common.php');

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
        'penType' => $row[3],
        'penNm' => $row[4],
        'penLtmNum' => $row[$i == 0 ? 7 : 5],
        'penRecGraNm' => $row[$i == 0 ? 9 : 6],
        'start_date' => date('Y-m-d', strtotime($row[$i == 0 ? 10 : 7])),
        'total_price' => intval(preg_replace("/[^0-9]/", "", $row[$i == 0 ? 12 : 8])),
        'total_price_pen' => intval(preg_replace("/[^0-9]/", "", $row[$i == 0 ? 13 : 9])),
        'total_price_ent' => intval(preg_replace("/[^0-9]/", "", $row[$i == 0 ? 16 : 10]))
      );
      $pen['penRate'] = intval($pen['total_price_pen'] == 0 ? 0 : round($pen['total_price_pen'] * 100 / $pen['total_price']));
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