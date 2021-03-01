<?php

	include_once('../common.php');

	$sql = sql_query("SELECT it_id FROM g5_shop_item");
	for($i = 0; $row = sql_fetch_array($sql); $i++){
		echo "{$row["it_id"]}<br>";
	}

?>