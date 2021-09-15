<?php
$sub_menu = "100200";
include_once('./_common.php');

if ($is_admin != 'super')
  json_response(400, '최고관리자만 접근 가능합니다.');

if(!($mb_id = $_POST['mb_id']))
  json_response(400, '회원아이디를 입력해주세요.');

$mb = get_member($mb_id);
if (!$mb['mb_id'])
  json_response(400, '존재하는 회원아이디가 아닙니다.');

$auths = $_POST['auths'];

if(!is_array($auths) || !$auths)
  json_response(400, '권한을 선택해주세요.');

foreach($auths as $auth) {
  $au_menu = $auth['au_menu'];
  $au_auth = $auth['au_auth'];

  if(!$au_menu || !$au_auth)
    json_response(400, '유효하지 않은 요청입니다.', [$au_menu, $au_auth]);
  
  $sql = "
    insert into
      {$g5['auth_table']}
    set
      mb_id = '{$mb_id}',
      au_menu = '{$au_menu}',
      au_auth = '{$au_auth}'
  ";

  $result = sql_query($sql, false);
  if(!$result) {
    $sql = "
      update
        {$g5['auth_table']}
      set
        au_auth = '{$au_auth}'
      where
        mb_id   = '{$mb_id}' and
        au_menu = '{$au_menu}'
    ";
    sql_query($sql);
  }
}

json_response(200, 'OK');
?>
