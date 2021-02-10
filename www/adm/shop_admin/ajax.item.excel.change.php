<?php

	include_once("./_common.php");

	sql_query("
		UPDATE g5_shop_item SET
			it_id = '{$_POST["prodId"]}'
		WHERE it_id = '{$_POST["it_id"]}'
	");

	sql_query("
		UPDATE g5_shop_item_option SET
			it_id = '{$_POST["prodId"]}'
		WHERE it_id = '{$_POST["it_id"]}'
	");

?>