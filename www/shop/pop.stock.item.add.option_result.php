<?php
include_once('./_common.php');

$it_id = $it_id[0];

$productList = [];
$od_prodBarNum_total = 0;

$bi = 0;

for ($i=0; $i < count($io_type[$it_id]); $i++) {
  
  // 상품정보
  $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
  $it = sql_fetch($sql);

  # 옵션값 가져오기
  $prodColor = $prodSize = $prodOption = '';
  $prodOptions = [];

  if ($io_id[$it_id]) { // 옵션값이 있으면
    $io_subjects = explode(',', $it['it_option_subject']);
    $io_ids = explode(chr(30), $io_id[$it_id][$i]);

    for ($io_idx = 0; $io_idx < count($io_subjects); $io_idx++) {
      switch ($io_subjects[$io_idx]) {
        case '색상':
          $prodColor = $io_ids[$io_idx];
          break;
        case '사이즈':
          $prodSize = $io_ids[$io_idx];
          break;
        default:
          $prodOptions[] = $io_ids[$io_idx];
          break;
      }
    }
  }

  if ($prodOptions && count($prodOptions)) {
    $prodOption = implode('|', $prodOptions);
  }
  # 상품목록
  for ($ii = 0; $ii < $ct_qty[$it_id][$i]; $ii++) {
    $thisProductData = [];
    $thisProductData["prodId"] = $it['it_id'];
    $thisProductData["prodColor"] = $prodColor;
    $thisProductData["prodSize"] = $prodSize;
    $thisProductData["prodOption"] = $prodOption;
    $thisProductData["prodBarNum"] = $barcode[$it_id][$bi++];
    $thisProductData["prodManuDate"] = date("Y-m-d");
    $thisProductData["stoMemo"] = "";
    $thisProductData["ct_id"] = "";
    array_push($productList, $thisProductData);
    $od_prodBarNum_total++;
  }
}

$mb = get_member($member['mb_id']);

$stoIdList = [];
$sendData = [];
$sendData["usrId"] = $mb["mb_id"];
$sendData["entId"] = $mb["mb_entId"];
$prodsSendData = [];
$prodsData = [];
foreach ($productList as $key => $value) {
  $prodsData["prodId"] = $value["prodId"];
  $prodsData["prodColor"] = $value["prodColor"];
  $prodsData["prodSize"] = $value["prodSize"];
  $prodsData["prodOption"] = $value["prodOption"];
  $prodsData["prodManuDate"] = $value["prodManuDate"];
  $prodsData["prodBarNum"] = $value["prodBarNum"];
  $prodsData["stoMemo"] = $value["stoMemo"];
  $prodsData["ct_id"] = $value["ct_id"];
  array_push($prodsSendData, $prodsData);
}
$sendData["prods"] = $prodsSendData;

$res = get_eroumcare(EROUMCARE_API_STOCK_INSERT, $sendData);

if ($res['errorYN'] === 'Y') {
  alert($res['message']);
}

$result_again = $res['data'];
$new_sto_ids = array_map(function($data) {
  return array(
    'stoId' => $data['stoId'],
    'prodBarNum' => $data['prodBarNum'],
    'prodId' => $data['prodId'],
    'stateCd' => '01',
  );
}, $result_again);


$api_data = array(
  'usrId' => $mb["mb_id"],
  'entId' => $mb["mb_entId"],
  'prods' => $new_sto_ids,
);
$api_result = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $api_data);

if ($api_result['errorYN'] === 'Y') {
  alert($api_result['message']);
}

?>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $title; ?></title>
</head>
<body>
<script>
window.parent.$('#add_sales_inventory_popup').hide();
alert('추가되었습니다.');
window.parent.location.reload();
</script>
</body>
</html>