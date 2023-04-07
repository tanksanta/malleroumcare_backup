<?php
$sub_menu = '400800';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'd');

$type = $_GET['type'];


if($type == "user"){ // 쿠폰관리 > 회원별로 보기 > 개별 삭제
    $sql = " delete from g5_shop_coupon_member where cp_no = '{$_GET['cp_no']}' and  mb_id = '{$_GET['mb_id']}' ";
    sql_query($sql);

    alert("삭제되었습니다.");
} else { // 쿠폰관리 > 그룹으로 보기 > 선택 삭제
    check_admin_token();

    $count = count($_POST['chk']);
    if(!$count)
        alert('선택삭제 하실 항목을 하나이상 선택해 주세요.');

    for ($i=0; $i<$count; $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        // alert(json_encode($_POST));

        $cp_id = preg_replace('/[^a-z0-9_\-]/i', '', $_POST['cp_id'][$k]);

        $cp = sql_fetch(" select * from  {$g5['g5_shop_coupon_table']} where cp_id = '{$cp_id}' ");
        if(!$cp || !$cp['cp_no'])
          alert('존재하지 않는 쿠폰입니다.');

        $sql = " delete from {$g5['g5_shop_coupon_table']} where cp_id = '{$cp_id}' ";
        sql_query($sql);

        $sql = " delete from g5_shop_coupon_member where cp_no = '{$cp['cp_no']}' ";
        sql_query($sql);
    }
}

goto_url('./couponlist.php?'.$qstr);
?>
