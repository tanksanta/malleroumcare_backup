<?php
include_once('./_common.php');
//auth_check($auth[$sub_menu], "r");
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

if($_POST["st_id"]){//검색태그 확인
    $sql = "
        update
        g5_search_tag
        set
        c_count = c_count+1
        where
        st_id = '".$_POST["st_id"]."'
    ";
    $result = sql_query($sql);
	if(!$result)
       json_response(500, '서버 오류로 '.$_POST["st_id"].' 적용에 실패했습니다.');

	json_response(200, "OK");
	exit;
}else{
	json_response(200, '선택한 검색태그가 없습니다.');
	exit;
}

?>