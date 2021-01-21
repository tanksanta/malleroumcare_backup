<?php

	include_once('./_common.php');
	include_once(G5_LIB_PATH.'/mailer.lib.php');

	$stoIdList = implode(",", $_POST["stoIdList"]);

	$sql = "
		UPDATE g5_shop_order SET
			stoId = '{$stoIdList}'
		WHERE od_id = '{$_POST["od_id"]}'
	";	

	sql_query($sql);

?>