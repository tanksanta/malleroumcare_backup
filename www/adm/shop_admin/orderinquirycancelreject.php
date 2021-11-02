<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");

if (!$od['od_id']) {
    alert("존재하는 주문이 아닙니다.");
}

$sql = "select *
        from g5_shop_order_cancel_request
        where od_id = '{$od['od_id']}' and approved = 0";
$cancel_request_row = sql_fetch($sql);

if(!$cancel_request_row['od_id']) {
  alert('취소요청이 존재하지 않습니다.');
}

sql_query(" DELETE FROM g5_shop_order_cancel_request WHERE od_id = '{$od['od_id']}' AND approved = 0 ");

// 주문 취소 관련 필드 빈칸으로 초기화
$sql = "update {$g5['g5_shop_order_table']}
        set
            od_cancel_reason = '',
            od_cancel_memo = ''
        where
            od_id = '{$od['od_id']}'
        ";
sql_query($sql);

set_order_admin_log($od_id, '취소 요청 관리자 거절');

goto_url($_SERVER['HTTP_REFERER']);
?>
