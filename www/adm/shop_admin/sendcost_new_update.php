<?php
$sub_menu = '400750';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

check_admin_token();

$w = $_POST['w'];

if($w == 'd') {
    $count = count($_POST['chk']);
    if(!$count)
        alert('삭제하실 항목을 하나이상 선택해 주십시오.');

    for($i=0; $i<$count; $i++) {
        $k = $_POST['chk'][$i];

        $sc_id = (int) $_POST['sc_id'][$k];
        sql_query(" delete from g5_shop_sendcost_new where sc_id = '$sc_id' ");
    }
} else {
    $sc_address = trim(strip_tags(clean_xss_attributes($_POST['sc_address'])));
    $sc_price = preg_replace('/[^0-9]/', '', $_POST['sc_price']);

    if(!$sc_address)
        alert('지역명을 입력해 주십시오.');
    if(!$sc_price)
        alert('추가배송비를 입력해 주십시오.');

    $sql = " insert into g5_shop_sendcost_new
                  ( sc_address, sc_price )
                values
                  ( '$sc_address', '$sc_price' ) ";
    sql_query($sql);
}

goto_url('./sendcost_new_list.php?page='.$page);
?>
