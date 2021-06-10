<?php
include_once('./_common.php');
include_once('./eform/lib/eform.lib.php');

if (!$is_member || !$member['mb_id']) json_response(400, '먼저 로그인 하세요.');

$json_params = file_get_contents("php://input");
$post = [];
if (strlen($json_params) > 0 && is_valid_json($json_params))
  $post = json_decode($json_params, true);

if(!$post || !array_keys_exists([
  'penId', 'penNm', 'penLtmNum', 'penRecGraCd', 'penRecGraNm', 'penTypeCd', 'penTypeNm',
  'start_date', 'total_price', 'total_price_pen', 'total_price_ent', 'selected_month'
], $post)) json_response(400, '잘못된 요청입니다.'.json_encode($post));

$check = sql_fetch("SELECT cl_id FROM `claim_management` WHERE
  penId = '{$post['penId']}' AND
  penNm = '{$post['penNm']}' AND
  penLtmNum = '{$post['penLtmNum']}' AND
  penRecGraCd = '{$post['penRecGraCd']}' AND
  penTypeCd = '{$post['penTypeCd']}' AND
  selected_month = '{$post['selected_month']}'
");
if($check['cl_id']) {
  // 이미 생성되어있는 변경지점이면 값을 새로 업데이트
  sql_query("UPDATE `claim_management` SET
    total_price = '{$post['total_price']}',
    total_price_pen = '{$post['total_price_pen']}',
    total_price_ent = '{$post['total_price_ent']}'
    WHERE cl_id = '{$check['cl_id']}'
  ");
  json_response(200, $check['cl_id']);
}

$result = sql_query("INSERT INTO `claim_management` SET
  mb_id = '{$member['mb_id']}',
  penId = '{$post['penId']}',
  penNm = '{$post['penNm']}',
  penLtmNum = '{$post['penLtmNum']}',
  penRecGraCd = '{$post['penRecGraCd']}',
  penRecGraNm = '{$post['penRecGraNm']}',
  penTypeCd = '{$post['penTypeCd']}',
  penTypeNm = '{$post['penTypeNm']}',
  start_date = '{$post['start_date']}',
  total_price = '{$post['total_price']}',
  total_price_pen = '{$post['total_price_pen']}',
  total_price_ent = '{$post['total_price_ent']}',
  selected_month = '{$post['selected_month']}'
");
$cl_id = sql_insert_id();

if(!$result) {
  json_response(500, '서버 DB 오류로 인해 내용을 변경할 수 없습니다.');
}
json_response(200, $cl_id);
?>
