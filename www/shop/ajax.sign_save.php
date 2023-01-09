<?php
include_once("./_common.php");

if(!$is_member) {
  $response["msg"] ='먼저 로그인 하세요.';
  $response["ok"] = "err";
}
header('Content-type: application/json');
$state = json_decode(stripslashes($_POST['img_data']), true);
if(!$state) {
  json_response(400, '잘못된 요청입니다.');
}else{
// 서명일 경우 서명 이미지 저장
$signdir = G5_DATA_PATH.'/file/member/stamp';

	$encoded_image = explode(",", $state)[1];
    $decoded_image = base64_decode($encoded_image);

    $filename = $member["mb_id"]."_".time().".png";
    file_put_contents("$signdir/$filename", $decoded_image);
  }

  sql_query("update g5_member set sealFile='".$filename."' where mb_id='".$member["mb_id"]."'");

	$response["sealFile"] = $filename;
	$response["msg"] = "날인 정보를 업로드 했습니다.";
	$response["ok"] = "ok";
	//json_response(200, 'OK');
	echo json_encode($response);
?>