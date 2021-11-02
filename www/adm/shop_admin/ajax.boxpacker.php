<?php
$sub_menu = '400400';
include_once('./_common.php');

if($auth_check = auth_check($auth[$sub_menu], 'r', true))
  json_response(400, $auth_check);

$od_id = get_search_string($od_id);
if(!$od_id)
  json_response(400, '유효하지않은 요청입니다.');

try {
  $data = get_packed_boxes($od_id);
} catch(Exception $e) {
  json_response($e->getCode(), $e->getMessage());
}

json_response(200, 'OK', $data);
