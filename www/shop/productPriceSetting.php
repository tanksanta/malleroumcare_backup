<?php

	include_once("./_common.php");

	$result = sql_query("
		SELECT it_id, it_price, it_cust_price FROM g5_shop_item
	");

	for($i = 0; $row = sql_fetch_array($result); $i++){
		echo "UPDATE pro1100 SET PROD_OFL_PRICE = '{$row["it_price"]}', PROD_SUP_PRICE = '{$row["it_cust_price"]}' WHERE PROD_ID = '{$row["it_id"]}';<br>";
	}

?>