<?php

	include_once("./_common.php");

	sql_query("
		UPDATE g5_shop_order SET
			od_prodBarNum_insert = '{$_POST["cnt"]}'
		WHERE od_id = '{$_POST["od_id"]}'
	");

?>