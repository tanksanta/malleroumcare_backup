<?php

	include_once("./_common.php");

	$sql = "
		UPDATE g5_shop_order SET
			  od_status = '{$_POST["od_status"]}'
			, staOrdCd = '{$_POST["staOrdCd"]}'
		WHERE od_id = '{$_POST["od_id"]}'
	";
	sql_fetch($sql);

?>