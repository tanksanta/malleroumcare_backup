<?php

include_once("./_common.php");

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');

// collect Get Data
$id = $_POST['id']; // 관리자 id
$menu_a = $_POST['menu']; // 변경,삭제,조회 될 페이지 ID가 array 방식으로 들어옴(하나의 아이템만 들어오더라도 array 방식으로 들어오도록 통일
$status = $_POST['status']; // 조회, 등록, 삭제의 상태를 나타내기 위한 attr (s:권한조회,w:권한등록,d:권한삭제,eu:최초진입메뉴등록,ed:최초진입메뉴등록해제)
$auth = $_POST['auth']?:"";

if($status == "s") { // 관리자 권한 상세정보 가져오기
  $auth_menu = [];
  for($i=0; $i < count($menu_a); $i++){
    $auth_menu[str_replace(" ", "", array_keys($menu_a)[$i])] = array('page_name'=> array_values($menu_a)[$i], 'reg'=>'n', 'au_auth'=>'','entry_menu'=>'');
  }

  $sql = "select * from g5_auth where mb_id = '{$id}';";
  $result = sql_query($sql);
  if(!$result) json_response(500, "관리자 권한 조회에 실패하였습니다. 다시 시도해주세요.");

  for ($i=0; $row=sql_fetch_array($result); $i++) {
    $auth_menu[$row['au_menu']]['reg'] = 'y';
    $au_auth = [];
     if($row['au_auth']) {
       if(strpos($row['au_auth'],"r")!==false) $au_auth[] = "읽기";
       if(strpos($row['au_auth'],"w")!==false) $au_auth[] = "쓰기";
       if(strpos($row['au_auth'],"d")!==false) $au_auth[] = "삭제";

       $auth_menu[$row['au_menu']]['au_auth'] =  implode(",", $au_auth);
     }
     else $auth_menu[$row['au_menu']]['au_auth'] = $row['au_auth'];

     $auth_menu[$row['au_menu']]['entry_menu'] = $row['entry_menu']?:'';
  }

  json_response(200, $auth_menu);
}
else if($status == "c") { // 관리자 권한 상세정보 가져오기 -> auth에 기존 관리자, id에 권한을 가져올 관리자 id 들어있음
  $auth_menu = [];
  for($i=0; $i < count($menu_a); $i++){
    $auth_menu[str_replace(" ", "", array_keys($menu_a)[$i])] = array('page_name'=> array_values($menu_a)[$i], 'reg'=>'n', 'au_auth'=>'','entry_menu'=>'');
  }

  $sql_reset = "DELETE FROM g5_auth WHERE mb_id = '{$auth}'";
  $result_reset = sql_query($sql_reset);
  if(!$result_reset) json_response(500, "관리자 권한 복사에 실패하였습니다. 다시 시도해주세요.");

  $sql = "select * from g5_auth where mb_id = '{$id}';";
  $result = sql_query($sql);

  for ($i=0; $row=sql_fetch_array($result); $i++) {
     $auth_menu[$row['au_menu']]['reg'] = 'y';
    $au_auth = [];
     if($row['au_auth']) {
       if(strpos($row['au_auth'],"r")!==false) $au_auth[] = "읽기";
       if(strpos($row['au_auth'],"w")!==false) $au_auth[] = "쓰기";
       if(strpos($row['au_auth'],"d")!==false) $au_auth[] = "삭제";

       $auth_menu[$row['au_menu']]['au_auth'] =  implode(",", $au_auth);
     }
     else $auth_menu[$row['au_menu']]['au_auth'] = $row['au_auth'];

     $auth_menu[$row['au_menu']]['entry_menu'] = $row['entry_menu']?:'';

     if($row['entry_menu']) $sql_i = "INSERT INTO g5_auth (mb_id, au_menu, au_auth, entry_menu) VALUES ('{$auth}', '{$row['au_menu']}', '{$row['au_auth']}', '{$row['entry_menu']}')";
     else $sql_i = "INSERT INTO g5_auth (mb_id, au_menu, au_auth, entry_menu) VALUES ('{$auth}', '{$row['au_menu']}', '{$row['au_auth']}', null)";
      $result_i = sql_query($sql_i);
      if(!$result_i) json_response(500, "관리자 권한 복사에 실패하였습니다. 다시 시도해주세요.");
  }

  json_response(200, $auth_menu);
}
else if($status == "w") { // 관리자 권한 등록
  $sql = "INSERT INTO g5_auth (mb_id, au_menu, au_auth, entry_menu) VALUES ('{$id}', '{$menu_a[0]}', '{$auth}', null)";
  $result = sql_query($sql);
  if(!$result) json_response(500, "관리자 권한 등록에 실패하였습니다. 다시 시도해주세요.");
}
else if($status == "d") { // 관리자 권한 삭제
  $sql = "DELETE FROM g5_auth WHERE mb_id = '{$id}' and au_menu = '{$menu_a[0]}'";
  $result = sql_query($sql);
  if(!$result) json_response(500, "관리자 권한 삭제에 실패하였습니다. 다시 시도해주세요.");
}
else if($status == "eu") { // 관리자 최초 진입 메뉴 등록
  $entry_link = "";
  $menu_cd = "menu".substr(str_replace(' ', '', $menu_a[0]), 0, 3);
  foreach($menu[$menu_cd] as $value) {
    if(in_array(str_replace(' ', '', $menu_a[0]), $value)) {
      $entry_link = $value[2];
    }
    else continue;
  }
  $sql_ed = "UPDATE g5_auth SET entry_menu = null, entry_link = null WHERE mb_id = '{$id}'";
  $result_ed = sql_query($sql_ed);
  if(!$result_ed) json_response(500, "관리자 최초 진입 메뉴 등록에 실패하였습니다. 다시 시도해주세요.");
  $sql_eu = "UPDATE g5_auth SET entry_menu = 'y', entry_link = '{$entry_link}' WHERE mb_id = '{$id}' and au_menu = '{$menu_a[0]}'";
  $result_eu = sql_query($sql_eu);
  if(!$result_eu) json_response(500, "관리자 최초 진입 메뉴 등록에 실패하였습니다. 다시 시도해주세요.");
}
else if($status == "ed") { // 관리자 최초 진입 메뉴 등록 해제
  $sql_ed = "UPDATE g5_auth SET entry_menu = null, entry_link = null WHERE mb_id = '{$id}'";
  $result_ed = sql_query($sql_ed);
  if(!$result_ed) json_response(500, "관리자 최초 진입 메뉴 등록 해제에 실패하였습니다. 다시 시도해주세요.");
}
?>
