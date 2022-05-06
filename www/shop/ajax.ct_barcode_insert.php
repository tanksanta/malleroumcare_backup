<?php


include_once("./_common.php");

if($_POST['od_id']) {
  $od_id = $_POST['od_id'];
  $sqlv = "SELECT
    ct_id,
    stoId,
    io_type
  FROM `g5_shop_cart`
  WHERE
    `od_id` ='".$od_id."'
  ";
  $result = sql_query($sqlv);

  while($row = sql_fetch_array($result)) {
    $count_a = 0;
    $stoIdDataList = explode('|',$row['stoId']);
    $stoIdDataList = array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);
    $sendData["stoId"] = $stoIdData;
    $res = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
    $result_again = $res['data'];

    for($k=0; $k < count($result_again); $k++) {
      if($result_again[$k]['prodBarNum']) {
        $count_a++;
      }
    }

    $sql = "SELECT c.*, i.ca_id FROM
      {$g5['g5_shop_cart_table']} as c
      INNER JOIN {$g5['g5_shop_item_table']} as i ON c.it_id = i.it_id
    where `ct_id` = '{$row['ct_id']}' ";
    $ct = sql_fetch($sql);
    $gubun = $cate_gubun_table[substr($ct['ca_id'], 0, 2)];

    // 비급여 제품이 아니고 추가옵션 아닌것만
    if ($gubun != '02' && $row['io_type'] == 0) {
      sql_query('UPDATE `g5_shop_cart` SET `ct_barcode_insert`="'.$count_a.'" where `ct_id` = "'.$row['ct_id'].'"');
    }
  }
  echo "success";
} else {
  echo "fail";
}



?>