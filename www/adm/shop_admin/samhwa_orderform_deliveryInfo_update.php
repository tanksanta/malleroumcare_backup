<?php

	include_once("./_common.php");

	$ct_id_list = $_POST["ct_id"];
	$od_delivery_insert = 0;

	foreach($ct_id_list as $ct_id){
		$ct_delivery_company = $_POST["ct_delivery_company_{$ct_id}"];
		$ct_delivery_num = $_POST["ct_delivery_num_{$ct_id}"];
		$ct_delivery_cnt = $_POST["ct_delivery_cnt_{$ct_id}"];
		$ct_delivery_price = $_POST["ct_delivery_price_{$ct_id}"];
		
		if($ct_delivery_num){
			$od_delivery_insert++;
		}
		
		if($update_type == "popup"){
			sql_query("
				UPDATE g5_shop_cart SET
					ct_delivery_company = '{$ct_delivery_company}',
					ct_delivery_num = '{$ct_delivery_num}'
				WHERE ct_id = '{$ct_id}'
			");
		} else {
			sql_query("
				UPDATE g5_shop_cart SET
					ct_delivery_company = '{$ct_delivery_company}',
					ct_delivery_num = '{$ct_delivery_num}',
					ct_delivery_cnt = '{$ct_delivery_cnt}',
					ct_delivery_price = '{$ct_delivery_price}'
				WHERE ct_id = '{$ct_id}'
			");
		}
	}

	sql_query("
		UPDATE g5_shop_order SET
			od_delivery_insert = '{$od_delivery_insert}'
		WHERE od_id = '{$_POST["od_id"]}'
	");

?>
