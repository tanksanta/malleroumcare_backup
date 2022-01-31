<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$ws_id = get_search_string($_POST['ws_id']);

if(!$ws_id) alert('유효하지 않은 요청입니다.');

$sql = "
    update
        warehouse_stock
    set
        ws_del_yn = 'Y',
        ws_del_by = '{$member['mb_id']}',
        ws_updated_at = NOW()
    where
        ws_id = {$ws_id}
";

$result = sql_query($sql);

if(!$result)
    alert('DB 오류로 삭제할 수 없습니다.');

alert('완료되었습니다.');
