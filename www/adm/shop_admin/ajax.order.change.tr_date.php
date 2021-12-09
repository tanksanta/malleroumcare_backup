<?php
$sub_menu = '400460';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check)
    json_response(400, $auth_check);

$od_id = get_search_string($_POST['od_id']);
$tr_date = get_search_string($_POST['tr_date']);

// 업데이트
$sql = "
    UPDATE
        g5_shop_order
    SET
        tr_date = '$tr_date'
    WHERE
        od_id = '$od_id'
";

$result = sql_query($sql);
if(!$result)
    json_response(400, 'DB 오류가 발생하여 업데이트에 실패했습니다.');

json_response(200, 'OK');
