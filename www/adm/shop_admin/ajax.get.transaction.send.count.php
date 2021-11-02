<?php
$sub_menu = "400470";
include_once('./_common.php');

if ($is_admin != 'super')
  json_response(400, '최고관리자만 접근 가능합니다.');

$sql = "select count(*) as cnt from g5_transaction where email IS NOT NULL OR fax IS NOT NULL;";
$sql_result = sql_fetch($sql);
$send_cnt = $sql_result['cnt'];

$sql = "
  select count(*) as cnt from (
    (select ct_id, ct_status, od_id as cart_od_id 
      from g5_shop_cart X 
      left join g5_shop_item Y On Y.it_id = X.it_id
    ) B 
    inner join g5_shop_order A ON B.cart_od_id = A.od_id
  ) where ct_status = '배송' and od_del_yn = 'N';
";
$sql_result = sql_fetch($sql);
$transaction_cnt = $sql_result['cnt'];
// echo $sql;
json_response(200, 'OK', $transaction_cnt - $send_cnt);
?>
