<?php

	include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	include_once('api.config.php');

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Authorization, Content-Type,Accept, Origin');
	header("Content-Type: application/json");


	if(!$_POST){
		$result["msg"] = "fail";
		echo json_encode($result);
		return false;
	}

	$member = sql_fetch("
		SELECT *
		FROM g5_member
		WHERE mb_id = '{$_POST["mb_id"]}'
	");

	if($member){
		sql_query("
			UPDATE g5_member SET
				mb_entConAcc01 = '{$_POST["entConAcco1"]}',
				mb_entConAcc02 = '{$_POST["entConAcco2"]}'
			WHERE mb_id = '{$_POST["mb_id"]}'
		");
		
		$result["msg"] = "success";
		echo json_encode($result);
	} else {
		$result["msg"] = "존재하지 않는 회원입니다.";
		echo json_encode($result);
	}

?>