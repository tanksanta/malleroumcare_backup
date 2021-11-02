<?php

	include_once('./_common.php');
	include_once(G5_LIB_PATH.'/mailer.lib.php');

	$sql = "
		DELETE FROM g5_shop_order
		WHERE od_id = '{$_POST["od_id"]}'
	";	

	sql_query($sql);

?>