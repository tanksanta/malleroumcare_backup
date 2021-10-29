<?php
include_once('./_common.php');

header('Content-type: application/json');

if($member['mb_type'] !== 'default') {
  echo json_encode([]);
  exit;
}

$keyword = str_replace(' ', '', trim($keyword));

$sql = "
  SELECT
    it_id,
    it_name,
    it_model,
    it_price,
    it_cust_price,
    REPLACE(a.it_name, ' ', '') as it_name_no_space,
    ca_id,
    it_img1 as it_img
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
";

$result = sql_query($sql);

$rows = [];
while ( $row = sql_fetch_array($result) ) {

  $gubun = $cate_gubun_table[substr($row["ca_id"], 0, 2)];
  $gubun_text = '판매';
  if($gubun == '01') $gubun_text = '대여';
  else if($gubun == '02') $gubun_text = '비급여';

  $row['gubun'] = $gubun_text;

  $rows[] = $row;
}

echo json_encode($rows);
