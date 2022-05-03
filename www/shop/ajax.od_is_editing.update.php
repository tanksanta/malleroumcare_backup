<?php
include_once('./_common.php');

if($is_member)
    sql_query(" UPDATE g5_shop_order SET od_is_editing = 0 WHERE mb_id = '{$member['mb_id']}' and od_is_editing = 1 ");

json_response(200, 'OK');
