<?php
include_once('./_common.php');

// 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
if ($token && get_session("ss_token") == $token) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
    set_session("ss_token", "");
    alert("로그아웃 상태입니다.", G5_SHOP_URL);
}

$od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' and mb_id = '{$member['mb_id']}' ");

if (!$od['od_id']) {
    alert("존재하는 주문이 아닙니다.");
}

if (!($to == "cancel" || $to == "return")) {
    alert("올바른 요청이 아닙니다.");
}

// 준비 상태가 아닌 주문이 있는 경우 취소요청할 수 없음
$status_check = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_cart_table']} where od_id = '{$od_id}' and ct_status <> '준비' ");
if($status_check['cnt'] > 0)
  alert('주문이 취소 가능한 상태가 아닙니다.');

if ($to == "cancel")
    $status = "취소 대기중";
if ($to == "return")
    $status = "반품 대기중";

$time =  date('Y-m-d H:i:s', time());
$sql = "insert into g5_shop_order_cancel_request
        set
            od_id = '{$od['od_id']}',
            mb_id = '{$member['mb_id']}',
            request_type = '{$to}',
            request_status = '{$status}',
            request_reason_type = '{$request_reason_type}',
            request_reason = '{$cancel_memo}',
            requested_at = '{$time}'
        ";
sql_query($sql);

$sql = "update {$g5['g5_shop_order_table']}
        set
            od_cancel_reason = '{$request_reason_type}',
            od_cancel_memo = '{$cancel_memo}'
        where
            od_id = '{$od['od_id']}'
        ";
sql_query($sql);

// 관리자 배송 기록에 남게
$type_text = '취소';
if($to == 'return') $type_text = '반품';
set_order_admin_log($od['od_id'], "{$type_text} 요청 ({$request_reason_type} - {$cancel_memo})");

goto_url(G5_SHOP_URL."/orderinquiryview.php?od_id=$od_id&amp;uid=$uid");
?>
