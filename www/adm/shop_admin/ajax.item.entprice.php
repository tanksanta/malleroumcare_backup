<?php
$sub_menu = '400300';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check)
    json_response(400, $auth_check);

$it_id = get_search_string($_POST['it_id']);
$mb_id = get_search_string($_POST['mb_id']);
$it_price = get_search_string($_POST['it_price']);
$it_price = (int) preg_replace("/[^\d]/","", $it_price);

$count = sql_fetch(" select count(*) as cnt from g5_shop_item_entprice where it_id = '$it_id' and mb_id = '$mb_id' ");

if($count['cnt']) {
    // 업데이트
    $sql = "
        UPDATE
            g5_shop_item_entprice
        SET
            it_price = '$it_price',
            updated_by = '{$member['mb_id']}',
            updated_at = NOW()
        WHERE
            it_id = '$it_id' and
            mb_id = '$mb_id'
    ";

    $result = sql_query($sql);
    if(!$result)
        json_response(400, 'DB 오류가 발생하여 업데이트에 실패했습니다.');
} else {
    // 새로 작성
    $sql = "
        INSERT INTO
            g5_shop_item_entprice
        SET
            it_id = '$it_id',
            mb_id = '$mb_id',
            it_price = '$it_price',
            created_by = '{$member['mb_id']}',
            created_at = NOW(),
            updated_by = '{$member['mb_id']}',
            updated_at = NOW()
    ";
    
    $result = sql_query($sql);
    if(!$result)
        json_response(400, 'DB 오류가 발생하여 작성에 실패했습니다.');
}

$count = sql_fetch(" select count(*) as cnt from g5_shop_item_entprice where it_id = '$it_id' and it_price > 0 ");

json_response(200, 'OK', $count['cnt']);
