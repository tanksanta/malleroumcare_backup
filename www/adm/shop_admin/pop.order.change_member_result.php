<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$mb = get_member($mb_id);
//print_r2($mb);
if (!$mb['mb_id']) {
    alert("해당 회원이 존재하지 않습니다.");
}

$mb['mb_tel'] = $mb['mb_tel'] ? $mb['mb_tel'] : $mb['mb_hp'];

$sql = " update {$g5['g5_shop_order_table']} set 
            mb_id = '{$mb['mb_id']}',
            od_name = '{$mb['mb_name']}',
            od_email = '{$mb['mb_email']}',
            od_tel = '{$mb['mb_tel']}',
            od_hp = '{$mb['mb_hp']}',
            od_zip1 = '{$mb['mb_zip1']}',
            od_zip2 = '{$mb['mb_zip2']}',
            od_addr1 = '{$mb['mb_addr1']}',
            od_addr2 = '{$mb['mb_addr2']}',
            od_addr3 = '{$mb['mb_addr3']}',
            od_addr_jibeon = '{$mb['mb_addr_jibeon']}',
            od_b_name = '{$mb['mb_name']}',
            od_b_tel = '{$mb['mb_tel']}',
            od_b_hp = '{$mb['mb_hp']}',
            od_b_zip1 = '{$mb['mb_zip1']}',
            od_b_zip2 = '{$mb['mb_zip2']}',
            od_b_addr1 = '{$mb['mb_addr1']}',
            od_b_addr2 = '{$mb['mb_addr2']}',
            od_b_addr3 = '{$mb['mb_addr3']}',
            od_b_addr_jibeon = '{$mb['mb_addr_jibeon']}'
            where od_id = '{$od_id}' ";
sql_query($sql);

$sql = " update {$g5['g5_shop_cart_table']} set mb_id = '{$mb['mb_id']}' where od_id = '{$od_id}' ";
sql_query($sql);

set_order_admin_log($od['od_id'], '주문서 회원 ' . $mb['mb_id'] . ' 로 변경');

?>
<html>
<head>
<title>주문 내역 수정 > 삼화쇼핑몰</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css">
</head>
<script>

try{

    alert('완료되었습니다.');
    window.opener.document.location.href=window.opener.document.URL;
    window.close();

}catch(e){ 
    // alert('팝업창을 다시 열어주세요');
    window.close();
}

</script>