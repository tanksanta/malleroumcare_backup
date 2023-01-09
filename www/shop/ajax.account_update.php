<?php
include_once('./_common.php');
header('Content-type: application/json');
if(!$member['mb_id']){
    $response["msg"] = "먼저 로그인 부터 해주세요.";
	$response["ok"] = "err";
	echo json_encode($response);
	exit();
}

$mb_account = clean_xss_tags($_POST['mb_account']);

$sql = "
    UPDATE g5_member
    SET mb_account = '$mb_account'
    WHERE mb_id = '{$member['mb_id']}';
";

sql_query($sql);
$response["ok"] = "ok";
$response["msg"] = "사업자 계좌정보가 수정 되었습니다.";
echo json_encode($response);
