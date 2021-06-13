<?php
include_once("./_common.php");
include_once('./eform/lib/eform.lib.php');

if(!$member["mb_id"])
  json_response(400, '접근 권한이 없습니다.');

if(!$_POST["id"])
  json_response(400, "정상적이지 않은 접근입니다.");

if(!$_POST["memo"])
  json_response(400, '메모를 입력해주세요.');

// 존재하는 수급자인지 체크
$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId'],
  'penId' => $_POST['id']
));

if(!$res || $res['errorYn'] == 'Y')
json_response(500, '서버 오류로 수급자 정보를 불러올 수 없습니다.');

$pen = $res['data'][0];
if(!$pen)
  json_response(500, '수급자 정보가 존재하지 않습니다.');

$datetime = date('Y-m-d H:i:s');

if($_POST['me_id']) {
  // update
  sql_query("UPDATE `recipient_memo` SET
    memo = '$memo',
    me_updated_at = '$datetime'
    WHERE penId = '{$pen['penId']}'
  ");
} else {
  // insert
  sql_query("INSERT INTO `recipient_memo` SET
    penId = '{$pen['penId']}',
    memo = '$memo',
    me_created_at = '$datetime',
    me_updated_at = '$datetime'  
  ");
}

json_response(200, 'OK');
?>
