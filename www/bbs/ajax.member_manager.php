<?php
$sub_menu = "200100";
include_once('./_common.php');

$w = $_POST['w'];
$mm_id = get_search_string($_POST['mm_id']);
$mm_pw = trim($_POST['mm_pw']);
$mm_name = get_search_string($_POST['mm_name']);
$mm_tel = get_search_string($_POST['mm_tel']);
$mm_email = sql_real_escape_string($_POST['mm_email']);
$mm_memo = sql_real_escape_string($_POST['mm_memo']);
$manager_auth_order = ($_POST['manager_auth_order'])?$_POST['manager_auth_order']:"0";
$mb_viewType = ($_POST['mb_viewType'])?$_POST['mb_viewType']:"0";

$mb = get_member($member['mb_id']);
if(!$mb['mb_id'])
  json_response(400, '존재하지않는 사업소 회원입니다.');

$mb_id = $mb['mb_id'];

if(!$w) {
  // 담당자 등록
  if(!$mm_id)
    json_response(400, '아이디를 입력해주세요.');
  if(preg_match("/[^0-9a-z_]+/i", $mm_id))
    json_response(400, '회원아이디는 영문자, 숫자, _ 만 입력하세요.');
  if(strlen($mm_id) < 3)
    json_response(400, '회원아이디는 최소 3글자 이상 입력하세요.');
  $sql = " select count(*) as cnt from `{$g5['member_table']}` where mb_id = '$mm_id' ";
  $row = sql_fetch($sql);
  if ($row['cnt'])
    json_response(400, '이미 사용중인 회원아이디 입니다.');

  if(!$mm_pw)
    json_response(400, '비밀번호를 입력해주세요.');

  if(!$mm_name)
    json_response(400, '이름을 입력해주세요.');
  if(!$mm_tel)
    json_response(400, '연락처를 입력해주세요.');
  $sql = "
    INSERT INTO
      {$g5["member_table"]}
    SET
      mb_id = '{$mm_id}',
      mb_level = 3,
      mb_password = '".get_encrypt_string($mm_pw)."',
      mb_type = 'manager',
      mb_name = '{$mm_name}',
	  mb_nick = '{$mm_name}',
	  mb_tel = '{$mm_tel}',
      mb_email = '{$mm_email}',
      mb_memo = '{$mm_memo}',
      mb_manager = '{$mb_id}',";
if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출
	$sql .= "
	  manager_auth_order = '{$manager_auth_order}',
	  mb_viewType = '{$mb_viewType}',";
}
    $sql .= "  mb_datetime = '".G5_TIME_YMDHIS."'
  ";

  $result = sql_query($sql, true);
  if(!$result)
    json_response(500, 'DB 오류로 담당자 등록에 실패했습니다.');
}

else if($w === 'u') {
  // 담당자 수정
  $sql_password = "";
  if($mm_pw)
    $sql_password = " , mb_password = '".get_encrypt_string($mm_pw)."' ";
  
  $sql = "
    UPDATE
      {$g5["member_table"]}
    SET
      mb_name = '{$mm_name}',
      mb_nick = '{$mm_name}',
	  mb_tel = '{$mm_tel}',
      mb_email = '{$mm_email}',";
if($member["mb_type"] == "default" && $_SESSION["ss_manager_auth_order"] == ""){//사업소 계정일때만 노출
	$sql .= "
	  manager_auth_order = '{$manager_auth_order}',
	  mb_viewType = '{$mb_viewType}',";
}
    $sql .= "
      mb_memo = '{$mm_memo}'
      {$sql_password}
    WHERE
      mb_id = '{$mm_id}' and
      mb_type = 'manager' and
      mb_manager = '{$mb_id}'
  ";

  $result = sql_query($sql);
  if(!$result)
    json_response(500, 'DB 오류로 담당자 수정에 실패했습니다.');
}

else if($w === 'd') {
  // 담당자 삭제
  $sql = "
    DELETE FROM
      {$g5["member_table"]}
    WHERE
      mb_id = '{$mm_id}' and
      mb_type = 'manager' and
      mb_manager = '{$mb_id}'
  ";

  $result = sql_query($sql);
  if(!$result)
    json_response(500, 'DB 오류로 담당자 삭제에 실패했습니다.');
}

else {
  json_response(400, '잘못된 요청입니다.');
}

json_response(200, 'OK');
?>
