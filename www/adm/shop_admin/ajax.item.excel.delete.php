<?php

	include_once("./_common.php");

	sql_query("
		DELETE FROM g5_shop_item
		WHERE it_id = '{$_POST["it_id"]}'
	");

	sql_query("
		DELETE FROM g5_shop_item_option
		WHERE it_id = '{$_POST["it_id"]}'
	");

?>