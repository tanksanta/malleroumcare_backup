<?php

	include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	include_once('api.config.php');

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Authorization, Content-Type,Accept, Origin');

	$member = sql_fetch("
		SELECT *
		FROM g5_member
		WHERE mb_id = '{$_POST["mb_id"]}'
	");

	if($member){
		set_session("ss_mb_id", $member["mb_id"]);
		goto_url("/");
	} else {
		echo "존재하지 않는 회원입니다.";
		return false;
	}

?>