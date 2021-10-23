<?php
include_once('./_common.php');

$keyword = str_replace(' ', '', trim($keyword));

$sql = "SELECT 
  it_id as id,
  it_id,
  it_name,
  it_model,
  it_price,
  REPLACE(a.it_name, ' ', '') as it_name_no_space,
  ca_id,
  it_sale_cnt,
  it_sale_cnt_02,
  it_sale_cnt_03,
  it_sale_cnt_04,
  it_sale_cnt_05,
  it_sale_percent,
  it_sale_percent_02,
  it_sale_percent_03,
  it_sale_percent_04,
  it_sale_percent_05,
  it_sale_percent_great,
  it_sale_percent_great_02,
  it_sale_percent_great_03,
  it_sale_percent_great_04,
  it_sale_percent_great_05
  FROM
    {$g5['g5_shop_item_table']} a
  WHERE
      (
        a.it_model like '%$keyword%' OR 
        a.it_name like '%$keyword%' OR 
        a.it_id like '%$keyword%' OR 
        a.pt_id like '%$keyword%' OR
        REPLACE(a.it_name, ' ', '') LIKE '%$keyword%'
      )
      AND
      (
        a.ca_id LIKE '10%' OR
        a.ca_id LIKE '20%' OR
        a.ca_id LIKE '70%'
      )
      AND it_id <> 'PRO2021022500102' -- 21.10.23. 533-900은 리스트에 안뜨게 수정
";

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

  $gubun = $cate_gubun_table[substr($row["ca_id"], 0, 2)];
  $gubun_text = '판매';
  if($gubun == '01') $gubun_text = '대여';
  else if($gubun == '02') $gubun_text = '비급여';

  $row['gubun'] = $gubun_text;

  $rows[] = $row;
}

header('Content-type: application/json');
echo json_encode($rows);
?>