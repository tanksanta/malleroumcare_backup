<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$price = $_POST['price'];

foreach($price as $it_id => $entprice) {
    $it_id = get_search_string($it_id);
    foreach($entprice as $mb_id => $it_price) {
        $mb_id = get_search_string($mb_id);
        $it_price = preg_replace('/[^0-9]/', '', $it_price);

        $result = sql_fetch(" select * from g5_shop_item_entprice where it_id = '{$it_id}' and mb_id = '{$mb_id}' ");

        // 가격이 없으면 continue
        if($result['mb_id'] && !$it_price) {
            $sql = "
                update
                    g5_shop_item_entprice
                set
                    it_price = NULL,
                    updated_by = '{$member['mb_id']}',
                    updated_at = NOW()
                WHERE
                    it_id = '$it_id' and
                    mb_id = '$mb_id'
            ";

            sql_query($sql);
            continue;
        }



        if($result['mb_id']) {
            $sql = "
                update
                    g5_shop_item_entprice
                set
                    it_price = '$it_price',
                    updated_by = '{$member['mb_id']}',
                    updated_at = NOW()
                WHERE
                    it_id = '$it_id' and
                    mb_id = '$mb_id'
            ";

            sql_query($sql);
        } else {
            $sql = "
                insert into
                    g5_shop_item_entprice
                set
                    it_id = '$it_id',
                    mb_id = '$mb_id',
                    it_price = '$it_price',
                    created_by = '{$member['mb_id']}',
                    created_at = NOW(),
                    updated_by = '{$member['mb_id']}',
                    updated_at = NOW()
            ";

            sql_query($sql);
        }
    }
}

alert('완료되었습니다.', 'itemprice.php');
