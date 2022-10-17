<?php
include_once('./_common.php');

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');

$data = $_POST;
$data['entId'] = $member["mb_entId"];
$data['usrId'] = $member["mb_id"];
// $data['delYn'] = 'N';
// echo 'console.log("'.$data.'")';

// $data = normalize_recipient_input($data);

$res = api_post_call(EROUMCARE_API_RECIPIENT_UPDATE, $data);

if ($data['isSpare'] == 'Y') 
  $res = api_post_call(EROUMCARE_API_SPARE_RECIPIENT_UPDATE, $data);

if(!$res || $res['errorYN'] != 'N')
  json_response(500, $res['message'] ?: '시스템서버가 응답하지 않습니다.');

if($data['penLtmNum'] != null || $data['penLtmNum'] != ''){
  $delete_macro = "DELETE FROM macro_request WHERE mb_id = '".$member['mb_id']."' AND recipient_num = '".str_replace('L', '', $data['penLtmNum'])."';";
  $delete_hist = "DELETE FROM pen_purchase_hist WHERE ENT_ID = '".$member['mb_entId']."' AND PEN_LTM_NUM = '".$data['penLtmNum']."';";
  
  sql_query($delete_macro);
  sql_query($delete_hist);
}

json_response(200, 'OK', $res);
?>
