<?php
include_once ('./_common.php');

if (!$member['mb_id']) json_response(500, '로그인이 필요합니다.');

if (!$_POST['fingerprint']) json_response(500, '유효하지않은 요청입니다.');

$mb_id = $member['mb_id'];
$type = $_POST['type'];
$fingerprint = $_POST['fingerprint'];

$row = sql_fetch(" select *
from device_security 
where mb_id = '{$mb_id}' AND fingerprint = '{$fingerprint}' ", false);

if ($type == 'check')
{
    if (!$row['status'])
    {
        json_response(400, '기기등록이 필요합니다.', 'N'); //등록된적 없음
        // json_response(500, '기기등록이 필요합니다.');   
    }
    else {
      if ($row['status'] == 'W')  
      {
          json_response(400, '보안알림 이메일을 통한 본인확인이 필요합니다.', 'W'); //기기 거절로 인한 재승인 대기중
      }
      else if ($row['status'] == 'D')  
      {
          json_response(400, '기기등록 재요청', 'D');
      }
    }
}
else if ($type == 'regist')
{
    if (!$_POST['password']) json_response(400, '비밀번호를 입력하세요.');

    $password = get_encrypt_string($_POST['password']);

    $sql = " select mb_password from {$g5['member_table']} where mb_id = '{$mb_id}' ";
    $row_mb = sql_fetch($sql);
    if ($row_mb['mb_password'])
    {
        if ($password === $row_mb['mb_password'])
        {
          if (!$row['status'])
          {
            $sql = " insert into device_security
                set mb_id   = '{$mb_id}',
                fingerprint = '{$fingerprint}',
                status = 'A' ";
            $result = sql_query($sql, false);
            if (!$result)
            {
                json_response(400, '기기등록오류');
            }
            else {
              $insert_id = sql_insert_id();
              json_response(200, 'OK', array('type' => 'insert', 'insert_id' => $insert_id));
            }
          }
          else
          {
            if (strcmp($row['status'], 'W') == 0 || strcmp($row['status'], 'A') == 0)
            {
            	sql_query("UPDATE device_security SET status = 'A', updated_at = CURRENT_TIMESTAMP WHERE id = '{$row['id']}' LIMIT 1;");
            	json_response(200, 'OK', array('type' => 'update', 'insert_id' => $row['id']));
            }
            else {
            	sql_query("UPDATE device_security SET status = 'W', updated_at = CURRENT_TIMESTAMP WHERE id = '{$row['id']}' LIMIT 1;");
            	json_response(200, 'OK', array('type' => 'update', 'insert_id' => $row['id']));
            }
          }
        }
        else{
          json_response(400, '비밀번호가 틀립니다.');
        }
    }
    else{
      json_response(400, '일치하는 회원정보가 없습니다.');
    }
}

json_response(200, 'OK');
?>
