<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function get_partner_cart_item($ct_id) {
    global $member;

    if(!$ct_id)
        return null;

    $sql = "
        SELECT *
        FROM g5_shop_cart
        WHERE
            ct_id = '$ct_id' and
            ct_is_direct_delivery IN(1, 2) and
            ct_direct_delivery_partner = '{$member['mb_id']}'
    ";

    $result = sql_fetch($sql);

    return $result;
}

function set_partner_order_edit($od_id, $od_partner_edit) {
    $sql = "
        UPDATE g5_shop_order
        SET
            od_partner_edit = '$od_partner_edit'
        WHERE
            od_id = '$od_id'
    ";
    
    $result = sql_query($sql);

    return $result;
}
