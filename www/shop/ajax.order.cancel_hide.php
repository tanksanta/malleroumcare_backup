<?php
include_once("./_common.php");

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

$od_id = get_search_string($_POST['od_id']);

if(!$od_id)
  json_response(400, '유효하지 않은 요청입니다.');

$result = sql_query("
  UPDATE
    {$g5['g5_shop_order_table']}
  SET
    od_hide_control = '0'
  WHERE
    od_id = '{$od_id}' and
    mb_id = '{$member['mb_id']}'
");

if(!$result)
  json_response(500, 'DB 서버 오류 발생');

set_order_admin_log($od_id, "사업소 - 주문숨김 취소");
json_response(200, 'OK');
?>
