<?php
include_once('./_common.php');
//auth_check($auth[$sub_menu], "r");
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

if($_POST["st_id"] && $_POST["useYN"]){//검색태그 확인
	$useYN_text = ($_POST["useYN"] =="Y")?"사용":"미사용";
	if(is_array($_POST['st_id'])) {
        foreach($_POST['st_id'] as $st_id) {
            $sql = "
                update
                    g5_search_tag
                set
                    useYN = '".$_POST["useYN"]."'
                where
                    st_id = '".$st_id."'
            ";
            $result = sql_query($sql);
		
            if(!$result)
                json_response(500, '서버 오류로 '.$useYN_text.' 적용에 실패했습니다.');
        }
    }
	json_response(200, 'OK');
	exit;
}else{
	json_response(400, '선택한 검색태그가 없습니다.');
	exit;
}

?>