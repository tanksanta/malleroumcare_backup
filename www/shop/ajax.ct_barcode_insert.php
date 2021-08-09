<?php


include_once("./_common.php");

if($_POST['od_id']) {
  $od_id = $_POST['od_id'];
  $sqlv = "select `ct_id`, `stoId` from `g5_shop_cart` where `od_id` ='".$od_id."'";
  $result = sql_query($sqlv);

  while($row = sql_fetch_array($result)) {
    $count_a = 0;
    $stoIdDataList = explode('|',$row['stoId']);
    $stoIdDataList = array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);
    $sendData["stoId"] = $stoIdData;
    $res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
    $result_again = $res['data'];

    for($k=0; $k < count($result_again); $k++) {
      if($result_again[$k]['prodBarNum']) {
        $count_a++;
      }
    }
    sql_query('UPDATE `g5_shop_cart` SET `ct_barcode_insert`="'.$count_a.'" where `ct_id` = "'.$row['ct_id'].'"');
  }
  echo "success";
} else {
  echo "fail";
}



?>