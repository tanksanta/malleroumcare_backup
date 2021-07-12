<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = "주문 내역 추가";

$od_id = get_uniqid();
$so_nb = get_uniqid_so_nb();
$od_pwd = $member['mb_password'];
$od_status = '작성';

$sql = " insert {$g5['g5_shop_order_table']}
            set od_id             = '$od_id',
                mb_id             = '',
                od_pwd            = '',
                od_time           = '".G5_TIME_YMDHIS."',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '월 마감 정산',
                od_status         = '{$od_status}',
                od_memo           = '',
                od_shop_memo      = '',
                od_mod_history    = '',
                od_cash           = '0',
                od_cash_no        = '',
                od_cash_info      = '',
                od_writer         = '{$member['mb_id']}',
                od_add_admin      = '1',
                so_nb             = '{$so_nb}'
                ";
$result = sql_query($sql, false);

set_order_admin_log($od_id, '주문서 관리자 등록');
?>
<script>
    alert('생성되었습니다. 생성된 주문은 반드시 회원을 선택해주세요.');
    location.href="./samhwa_orderform.php?od_id=<?php echo $od_id; ?>";
</script>