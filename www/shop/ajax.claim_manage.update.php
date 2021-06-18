<?php
include_once('./_common.php');

if (!$is_member || !$member['mb_id']) json_response(400, '먼저 로그인 하세요.');
if (!$cl_id) json_response(400, '잘못된 요청입니다.');

$json_params = file_get_contents("php://input");
$post = [];
if (strlen($json_params) > 0 && is_valid_json($json_params))
  $post = json_decode($json_params, true);

if(!$post || !array_keys_exists([
  'penId', 'penNm', 'penLtmNum', 'penRecGraCd', 'penRecGraNm', 'penTypeCd', 'penTypeNm',
  'start_date', 'total_price', 'total_price_pen', 'total_price_ent', 'selected_month'
], $post)) json_response(400, '잘못된 요청입니다.'.json_encode($post));

$result = sql_query("UPDATE `claim_management` SET
cl_status = '1',
start_date = '{$post['start_date']}',
total_price = '{$post['total_price']}',
total_price_pen = '{$post['total_price_pen']}',
total_price_ent = '{$post['total_price_ent']}'
WHERE cl_id = '$cl_id' AND
mb_id = '{$member['mb_id']}'
");

if(!$result) {
  json_response(500, '서버 DB 오류로 인해 내용을 변경할 수 없습니다.');
}
json_response(200, 'OK');
?>
