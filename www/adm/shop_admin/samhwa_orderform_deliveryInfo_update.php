<?php

include_once("./_common.php");

$sub_menu = '400400';
$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check) {
  die("권한이 없습니다.");
}

$ct_id_list = $_POST["ct_id"];
$od_delivery_insert = 0;

$direct_delivery_date = '';

foreach($ct_id_list as $ct_id) {
  $ct_delivery_company = $_POST["ct_delivery_company_{$ct_id}"];
  $ct_delivery_num = $_POST["ct_delivery_num_{$ct_id}"];
  $ct_delivery_cnt = $_POST["ct_delivery_cnt_{$ct_id}"];
  $ct_delivery_price = $_POST["ct_delivery_price_{$ct_id}"];
  $ct_delivery_combine = $_POST["ct_combine_{$ct_id}"];
  $ct_delivery_combine_ct_id = (int)$_POST["ct_combine_ct_id_{$ct_id}"];



  $ct_is_direct_delivery = (int)$_POST["ct_is_direct_delivery_{$ct_id}"] ?: 0;
  $ct_is_direct_delivery_sub = (int)$_POST["ct_is_direct_delivery_sub_{$ct_id}"] ?: 0;
  if($ct_is_direct_delivery && $ct_is_direct_delivery_sub) {
    $ct_is_direct_delivery = $ct_is_direct_delivery_sub;
    $ct_direct_delivery_partner = get_search_string($_POST["ct_direct_delivery_partner_{$ct_id}"]);
    $ct_direct_delivery_price = (int)$_POST["ct_direct_delivery_price_{$ct_id}"] ?: 0;
    $direct_delivery_date = ' , ct_direct_delivery_date = NOW() ';
  } else {
    $ct_direct_delivery_partner = '';
    $ct_direct_delivery_price = 0;
  }
  
  
  if($ct_delivery_num||$ct_delivery_combine){
    $od_delivery_insert++;
  }

  if ($ct_delivery_combine) {
    $combine_where = "ct_combine_ct_id = '{$ct_delivery_combine_ct_id}',";
  } else {
    $combine_where = "ct_combine_ct_id = NULL,";
  }
  
  if($update_type == "popup") {
    sql_query("
      UPDATE g5_shop_cart SET
        $combine_where
        ct_delivery_company = '{$ct_delivery_company}',
        ct_delivery_num = '{$ct_delivery_num}',
        ct_edi_result = 0
      WHERE ct_id = '{$ct_id}'
    ");
  } else {
    sql_query("
      UPDATE g5_shop_cart SET
        $combine_where
        ct_delivery_company = '{$ct_delivery_company}',
        ct_delivery_num = '{$ct_delivery_num}',
        ct_delivery_cnt = '{$ct_delivery_cnt}',
        ct_delivery_price = '{$ct_delivery_price}',
        ct_edi_result = 0,
        ct_is_direct_delivery = '{$ct_is_direct_delivery}',
        ct_direct_delivery_partner = '{$ct_direct_delivery_partner}',
        ct_direct_delivery_price = '{$ct_direct_delivery_price}'
        {$direct_delivery_date}
      WHERE ct_id = '{$ct_id}'
    ");
  }

  //배송 로그
  $od_id=$_POST["od_id"];
  $mb_id=$member["mb_id"];
  $data=date("Y-m-d H:i:s");
  if ($ct_delivery_combine) {
    $combine_where2 = "ct_combine_ct_id = '{$ct_delivery_combine_ct_id}',";
  } else {
    $combine_where2 = "ct_combine_ct_id = '',";
  }

  $sql = " insert into `g5_delivery_log`
  set od_id = '{$od_id}',
      ct_id = '{$ct_id}',
      mb_id = '{$mb_id}',
      d_content = '',
      $combine_where2
      ct_delivery_company = '{$ct_delivery_company}',
      ct_delivery_num = '{$ct_delivery_num}',
      ct_delivery_cnt = '{$ct_delivery_cnt}',
      ct_delivery_price = '{$ct_delivery_price}',
      ct_edi_result = '0',
      ct_is_direct_delivery = '{$ct_is_direct_delivery}',
      d_date = '{$data}'
  ";
  sql_query($sql);
}

sql_query("
  UPDATE g5_shop_order SET
    od_delivery_insert = '{$od_delivery_insert}'
  WHERE od_id = '{$_POST["od_id"]}'
");

?>
