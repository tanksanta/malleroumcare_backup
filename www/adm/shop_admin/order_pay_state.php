<?php
$sub_menu = '400400';
include_once('./_common.php');

if ($ret_od_id) {
    $od_ids = explode('|', $ret_od_id);
    
    $where = array();
    foreach ($od_ids as $od_id) {
        $where[] = " od_id = '{$od_id}' ";
    }
    $sql_search = 'where ' . implode(' or ', $where);
}

if ($to_state == 'notPaid') {
    $state = 0;
} else if ($to_state == 'paid') {
    $state = 1;
} else {
    alert("요청이 올바르지 않습니다.");
}

// od_pay_state 0 : 미결제, 1 : 결제완료, 2 : 결제후 출고

$sql = "update {$g5['g5_shop_order_table']}
        set od_pay_state = '{$state}'
        {$sql_search}
        ";

sql_query($sql);

goto_url($_SERVER['HTTP_REFERER']);

?>