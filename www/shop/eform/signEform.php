<?php
include_once("./_common.php");

$eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");
$items = sql_query("SELECT * FROM `eform_document_item` WHERE dc_id = UNHEX('{$eform["uuid"]}')");

$buy = [];
$rent = [];
while($item = sql_fetch_array($items)) {
  if($item['gubun'] == '00') array_push($buy, $item); // 판매 재고
  else array_push($rent, $item); // 대여 재고
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $eform['dc_subject']; ?></title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/signeform.css">
  <link rel="stylesheet" href="css/thk001.css">
  <link rel="stylesheet" href="css/thk002.css">
  <link rel="stylesheet" href="css/thk003.css">
  <script src="<?=G5_JS_URL?>/jquery-1.11.3.min.js"></script>
</head>
<body>
  <div class="sign-eform-head flexbox justify">
    <h1 id="eformTitle" class="flex"><?=$eform['dc_subject']?></h1>
    <button id="btnCloseSign">나가기</button>
  </div>
  <div class="sign-eform-body">
    <?php
    include_once('./document/thk001_1.php');
    include_once('./document/thk001_2.php');
    include_once('./document/thk002.php');
    include_once('./document/thk003.php');
    ?>
  </div>
  <div class="sign-eform-foot">
    <div class="desc">3건 중 0건 완료되었습니다.</div>
    <div class="menu-wrap">
      <button id="btnPrev">이전단계</button>
      <button id="btnNext">다음단계</button>
    </div>
  </div>
  <script>
  $(function() {
    $('#btnCloseSign').click(function(e) {
      e.preventDefault();
      window.close();
    });
  });
  </script>
</body>
</html>
