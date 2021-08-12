<?php
include_once('./_common.php');

if (!$od_id) {
  alert('잘못된 접근입니다.');
}


$result = sql_fetch("SELECT count(*) as cnt FROM {$g5['g5_shop_cart_table']} WHERE od_id = '{$od_id}' AND ct_status != '준비'");
if ($result['cnt']) {
  alert('주문상태가 상품준비 상태가 아닌 상품이 있습니다. 관리자에게 문의해주세요.');
}

$od_b_zip1 = preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 0, 3));
$od_b_zip2 = preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 3));


$sql = " update {$g5['g5_shop_order_table']}
                set 
                    od_b_name = '$od_b_name',
                    od_b_tel = '$od_b_tel',
                    od_b_hp = '$od_b_hp',
                    od_b_zip1 = '$od_b_zip1',
                    od_b_zip2 = '$od_b_zip2',
                    od_b_addr1 = '$od_b_addr1',
                    od_b_addr2 = '$od_b_addr2',
                    od_b_addr3 = '$od_b_addr3',
                    od_b_addr_jibeon = '$od_b_addr_jibeon'
                    ";
$sql .= " where od_id = '$od_id' ";
sql_query($sql);

alert('변경이 완료되었습니다.', G5_SHOP_URL . '/orderinquiryview.php?od_id=' . $od_id );