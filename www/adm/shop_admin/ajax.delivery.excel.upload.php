<?php
include_once('./_common.php');

$sub_menu = '400400';
$auth_check = auth_check($auth[$sub_menu], "w", true);
if($auth_check)
  json_response(400, $auth_check);

$file = $_FILES['datafile']['tmp_name'];
if(!$file)
  json_response(400, '파일을 선택해주세요.');

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel5');
$excel = $reader->load($file);
$sheet = $excel->getActiveSheet();

$num_rows = $sheet->getHighestRow();

for($i = 2; $i <= $num_rows; $i++) {
  $od_id = sql_real_escape_string(trim($sheet->getCell('A'.$i)->getValue()));
  $ct_id = sql_real_escape_string(trim($sheet->getCell('M'.$i)->getValue()));
  $ct_delivery_company = sql_real_escape_string(trim($sheet->getCell('N'.$i)->getValue()));
  $ct_delivery_num = sql_real_escape_string(trim($sheet->getCell('O'.$i)->getValue()));

  if(!$ct_delivery_company || !$ct_delivery_num)
    continue;

  $msg = '';
  if(!$ct_id)
    $msg = '카트ID 값이 유효하지 않습니다.';

  $flag = false;
  foreach($delivery_companys as $company) {
    if($company["name"] == $ct_delivery_company){
      $ct_delivery_company = $company["val"];
      $flag = true;
      break;
    }
  }
  if(!$flag)
    $msg = '택배사를 정확하게 입력해주세요.';

  $ct = sql_fetch(" SELECT * FROM g5_shop_cart WHERE ct_id = '{$ct_id}' and od_id = '{$od_id}' ");
  if(!$ct['ct_id'])
    $msg = '해당 상품이 존재하지 않습니다.';

  if($msg)
    json_response(400, "({$i}열) {$msg}");
  
  if($ct['ct_combine_ct_id']) {
    // 합포 체크된 상품이면 합포 대상 상품의 배송정보를 변경함
    $update_ct_id = $ct['ct_combine_ct_id'];
  } else {
    $update_ct_id = $ct_id;
  }

  // 배송정보 입력
  $result = sql_query("
    UPDATE
      g5_shop_cart
    SET
      ct_delivery_company = '{$ct_delivery_company}',
      ct_delivery_num = '{$ct_delivery_num}',
      ct_edi_result = '0'
    WHERE
      ct_id = '{$update_ct_id}'
  ");

  // 배송로그 입력
  sql_query("
    INSERT INTO
      g5_delivery_log
    SET
      od_id = '{$od_id}',
      ct_id = '{$ct_id}',
      mb_id = '{$member['mb_id']}',
      d_content = '',
      ct_combine_ct_id = '{$ct['ct_combine_ct_id']}',
      ct_delivery_company = '{$ct_delivery_company}',
      ct_delivery_num = '{$ct_delivery_num}',
      ct_delivery_cnt = '{$ct['ct_delivery_cnt']}',
      ct_delivery_price = '{$ct['ct_delivery_price']}',
      ct_edi_result = '0',
      ct_is_direct_delivery = '{$ct['ct_is_direct_delivery']}',
      d_date = NOW()
  ");

  // 배송개수 업데이트
  $cnt = sql_fetch(" SELECT count(*) as cnt FROM g5_shop_cart WHERE od_id ='{$od_id}' and ct_delivery_num <> '' and ct_delivery_num is not null ");
  $cnt = $cnt['cnt'] ?: 0;
  sql_query("
    UPDATE g5_shop_order SET
      od_delivery_insert = '{$cnt}'
    WHERE od_id = '{$od_id}'
  ");
}
?>
