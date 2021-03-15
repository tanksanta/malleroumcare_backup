<?php

	include_once('./_common.php');
	include_once(G5_LIB_PATH.'/mailer.lib.php');

	sql_query("
		UPDATE g5_shop_order SET
			  ordId = '{$_GET["ordId"]}'
			, uuid = '{$_GET["uuid"]}'
		WHERE od_id = '{$_GET["od_id"]}'
	");
	header("Location: http://61.106.19.170:8080/eform/reqClient/requestJspNew?UUID={$_GET["uuid"]}&CONTRACT_NUMBER={$_GET["ordId"]}&EFORM_TYPE=00&DOCUMENT_ID={$_GET["documentId"]}");

?>