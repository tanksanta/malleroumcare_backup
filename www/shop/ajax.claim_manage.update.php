$<?php
include_once('./_common.php');

if (!$is_member || !$member['mb_id'])
  json_response(400, '먼저 로그인 하세요.');

$json_params = file_get_contents("php://input");
$post = [];
if (strlen($json_params) > 0 && is_valid_json($json_params))
  $post = json_decode($json_params, true);

if(!$post || !$post['cl_id'])
  json_response(400, '잘못된 요청입니다.');

$keys = [
  'penRecGraCd', 'penRecGraNm',
  'penTypeCd', 'penTypeNm',
  'start_date',
  'total_price', 'total_price_pen', 'total_price_ent'
];

$set = [];
foreach($keys as $key) {
  if(isset($post[$key]) && $post[$key]) {
    $val = get_search_string($post[$key]);
    $set[] = " cl_{$key} = '{$val}' ";
  }
}

$cl_id = get_search_string($post['cl_id']);
$set = implode(', ', $set);

$result = sql_query("
  UPDATE
    `claim_management`
  SET
    {$set}
  WHERE
    cl_id = '{$cl_id}' AND
    mb_id = '{$member['mb_id']}'
");

if(!$result) {
  json_response(500, '서버 DB 오류로 인해 내용을 변경할 수 없습니다.');
}
json_response(200, 'OK');
?>
