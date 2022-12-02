<?php
include_once('./_common.php');

header('Content-type: application/json');

if($_POST["it_id"] != ""){//이벤트 상품 구매 조회
	$is_buy = 0;
	$sql = "SELECT COUNT(a.od_id) as buy_count FROM `g5_shop_order` AS a 
	INNER JOIN `g5_shop_cart` AS b ON a.od_id = b.od_id AND b.it_id='".$_POST["it_id"]."' AND b.ct_status NOT IN ('주문무효','취소')
	WHERE a.mb_id = '".$member['mb_id']."'";
	$row = sql_fetch($sql);
	if($row["buy_count"] >0 ){// 구매이력 있음
		$is_buy = 1; 
	}
	echo json_encode($is_buy);
	exit;
}

$keyword = str_replace(' ', '', trim($keyword));

$eform = $_GET['eform'];

/*
$prodsupyn_sql = " AND a.prodSupYn = 'Y' ";
if($eform) {
  // 계약서의 경우 비유통 상품도 검색 가능
  $prodsupyn_sql = '';
}
*/
$nonReimbursement = $eform!=1 ?" OR a.ca_id LIKE '70%'":"";
$prodsupyn_sql = $eform!=1 ?" AND a.prodSupYn = 'Y' ":"";

$sql = "
  SELECT
    it_id,
    it_name,
    it_model,
    it_price,
    it_price_dealer2,
    it_cust_price,
    it_rental_price,
    REPLACE(a.it_name, ' ', '') as it_name_no_space,
    ca_id,
    ( select ca_name from g5_shop_category where ca_id = left(a.ca_id, 4) ) as ca_name,
    it_img1 as it_img,
    it_delivery_cnt,
    it_buy_min_qty,
    it_buy_max_qty,
    it_buy_inc_qty,
    it_sc_type,
    it_sc_qty,
    it_sc_price,
    it_sc_minimum,
    it_even_odd,
    it_even_odd_price,
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
    it_sale_percent_great_05,
    it_type1,
    it_type2,
    it_type3,
    it_type4,
    it_type5,
    it_type6,
    it_type7,
    it_type8,
    it_type9,
    it_type10,
    it_expected_warehousing_date
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
      a.ca_id LIKE '20%'
      {$nonReimbursement}
    )
    AND a.it_id NOT IN ('PRO2021072200013', 'PRO2021072200012') -- 체험상품 제외
    AND a.it_name NOT LIKE 'test%' -- 테스트상품 제외
    AND a.it_use = 1 -- 판매상품
    {$prodsupyn_sql}
";

$result = sql_query($sql);

$rows = [];
while ( $row = sql_fetch_array($result) ) {
  $option_sql = "SELECT *
    FROM
      {$g5['g5_shop_item_option_table']}
    WHERE
      it_id = '{$row['it_id']}'
      AND io_type = 0 -- 선택 옵션
      AND io_use = 1 -- 사용중 옵션
    ORDER BY
      io_no ASC
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

  // 우수사업소 가격
  if($member['mb_level'] == 4 && $row['it_price_dealer2']) {
    $row['it_price'] = $row['it_price_dealer2'];
  }
  unset($row['it_price_dealer2']);

  // 사업소별 판매가
  $entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$row['it_id']}' and mb_id = '{$member['mb_id']}' ");
  if($entprice['it_price']) {
    $row['it_sale_cnt'] = 0;
    $row['it_sale_cnt_02'] = 0;
    $row['it_sale_cnt_03'] = 0;
    $row['it_sale_cnt_04'] = 0;
    $row['it_sale_cnt_05'] = 0;
    $row['it_price'] = $entprice['it_price'];
  }

  $rows[] = $row;
}

echo json_encode($rows);
