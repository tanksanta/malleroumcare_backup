<?php
include_once("./_common.php");

// $barcode = preg_replace("/[^a-z0-9]/i", "", $barcode);
$barcode = preg_replace("/[^0-9]/i", "", $barcode); //바코드는 숫자로만 되어있음. 211102

if (!$it_id || !$barcode) {
	json_response(400, '잘못된 요청입니다.');
}

// $sql = "SELECT c.*, i.prodPayCode as prod_pay_code FROM g5_shop_cart AS c
// INNER JOIN g5_shop_item AS i ON c.od_id = i.od_id
// WHERE ct_id = '{$ct_id}'
// ";
$sql = "SELECT i.prodPayCode as prod_pay_code FROM g5_shop_item as i
WHERE i.it_id = '{$it_id}'
";
$item = sql_fetch($sql);


if (!$item['prod_pay_code']) {
	json_response(500, '상품의 제품코드가 입력되지 않았습니다.');
}

$converted_barcode = $barcode;

if (strlen($barcode) > 13) {
	if (strpos($barcode, $item['prod_pay_code']) === false) {
		json_response(500, '상품과 바코드의 제품코드가 잘못되었습니다.');
	}

	$converted_barcode = explode($item['prod_pay_code'], $barcode)[1];
}

$return_data = array(
	'original_barcode' => $barcode,
	'converted_barcode' => $converted_barcode,
);

json_response(200, 'OK', $return_data);