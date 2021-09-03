<?php
include_once('./_common.php');

if (!$is_member || !$member['mb_id'])
  json_response(400, '먼저 로그인 하세요.');

if($member['mb_type'] !== 'default')
  json_response(400, '사업소 회원만 이용할 수 있습니다.');

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

if($post['penRecGraNm']) {
  $pen_rec_gra_nm = array_flip($pen_rec_gra_cd);

  if(!isset($pen_rec_gra_nm[$post['penRecGraNm']]))
    json_response(400, '잘못된 요청입니다.');

  $post['penRecGraCd'] = $pen_rec_gra_nm[$post['penRecGraNm']];
}

if($post['penTypeNm']) {
  $pen_type_nm = array_flip($pen_type_cd);

  if(!isset($pen_type_nm[$post['penTypeNm']]))
    json_response(400, '잘못된 요청입니다.');

  $post['penTypeCd'] = $pen_type_nm[$post['penTypeNm']];
}

$set = [" cl_status = '1' "];
foreach($keys as $key) {
  if(isset($post[$key]) && $post[$key]) {
    $val = sql_real_escape_string($post[$key]);
    $set[] = " cl_{$key} = '{$val}' ";
  }
}

$cl_id = sql_real_escape_string($post['cl_id']);
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
