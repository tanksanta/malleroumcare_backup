<?php
include_once('./_common.php');

if (!$before_ct_id || !$after_ct_id) {
  json_response(400, '오류입니다.');
}

sql_query("DELETE FROM partner_install_report WHERE ct_id = '{$after_ct_id}'");

sql_query("UPDATE
  partner_install_report pir
SET 
  pir.ct_id = '{$after_ct_id}',
  pir.it_id = ( SELECT c.it_id FROM g5_shop_cart as c INNER JOIN g5_shop_item as i ON c.it_id = i.it_id WHERE c.ct_id = '{$after_ct_id}' )
WHERE
  ct_id = '{$before_ct_id}'
");

json_response(200, 'OK');