<?php

	# 공용파일 추출
	include_once("../_common.php");

	# 변수검사
	if(!$_POST){
		echo "값이 존재하지 않습니다.";
		return false;
	}

	echo $_SERVER["HTTP_REFERER"];

?>