<?php
include_once('./_common.php');

$sql = "SELECT it_id as id, it_id, it_name, it_model, it_price 
  FROM
    {$g5['g5_shop_item_table']} a
  WHERE
      (
        a.it_model like '%$keyword%' OR 
        a.it_name like '%$keyword%' OR 
        a.it_id like '%$keyword%' OR 
        a.pt_id like '%$keyword%'
      )
      AND
      (
        a.ca_id LIKE '10%' or
        a.ca_id LIKE '70%'
      )
";
// 대여제품은 선택 불가
// OR a.ca_id LIKE '20%'

$result = sql_query($sql);

$rows = array();
while ( $row = sql_fetch_array($result) ) {
  $option_sql = "SELECT *
    FROM
      {$g5['g5_shop_item_option_table']}
    WHERE
        it_id = '{$row['it_id']}'
        and io_type = 0 -- 선택옵션
  ";
  $option_result = sql_query($option_sql);

  $row['options'] = [];
  while ($option_row = sql_fetch_array($option_result)) {
    $row['options'][] = $option_row;
  }

  $rows[] = $row;
}

header('Content-type: application/json');
echo json_encode($rows);
?>