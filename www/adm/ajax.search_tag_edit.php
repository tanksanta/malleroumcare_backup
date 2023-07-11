<?php
include_once('./_common.php');
//auth_check($auth[$sub_menu], "r");
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

$link = str_replace("https://","",$_POST["link"]);
$link = str_replace("www.","",$link);

if($_POST["st_id"]){//수정
    $sql = "
        update g5_search_tag
		set 
		st_text = '".$_POST["st_text"]."',
		type = '".$_POST["type"]."',
		link = '".(($_POST["type"] == '2')?$link:"")."',
		memo = '".$_POST["memo"]."',
		fr_date = '".$_POST["fr_date2"]."',
		to_date = '".$_POST["to_date2"]."',
		order_num = '".$_POST["order_num"]."',
		useYN = '".$_POST["useYN"]."',
		reg_date = now()
        where
        st_id = '".$_POST["st_id"]."'
    ";
    $result = sql_query($sql);
		
    if(!$result)
        json_response(500, '서버 오류로 수정에 실패했습니다.');

	json_response(200, 'OK');
	exit;
}else{//등록
	$sql = "
		insert into g5_search_tag
		(st_text,type,link,memo,fr_date,to_date,order_num,useYN,reg_date)
		values
		('".$_POST["st_text"]."','".$_POST["type"]."','".(($_POST["type"] == '2')?$link:"")."','".$_POST["memo"]."','".$_POST["fr_date2"]."','".$_POST["to_date2"]."','".$_POST["order_num"]."','".$_POST["useYN"]."',now())
	";
	$result = sql_query($sql);
		
    if(!$result)
        json_response(500, '서버 오류로 등록에 실패했습니다.');

	json_response(200, 'OK');
}
//	json_response(400, '선택한 검색태그가 없습니다.');
//	exit;
//}

?>