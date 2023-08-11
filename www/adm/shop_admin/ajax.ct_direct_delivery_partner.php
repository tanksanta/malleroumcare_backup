<?php
$sub_menu = '400400';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check)
    json_response(400, $auth_check);

if($_POST['ct_id'] && $_POST['ct_direct_delivery_partner']) {

    if($_POST['ct_direct_delivery_partner'] == '미지정') {
        $ct_direct_delivery_partner = '';//미지정이 아닌 위탁 해제
		$ct_is_direct_delivery = "0";//위탁 해제
    } else {
        $ct_direct_delivery_partner = $_POST['ct_direct_delivery_partner'];
		$ct_is_direct_delivery = "1";//위탁 설정
    }

    if(is_array($_POST['ct_id'])) {
        foreach($_POST['ct_id'] as $ct_id) {
            $sql = "
                update
                    g5_shop_cart
                set
                    ct_is_direct_delivery = '$ct_is_direct_delivery',
                    ct_direct_delivery_partner = '$ct_direct_delivery_partner'
                where
                    ct_id = '$ct_id'
            ";
            $result = sql_query($sql);

            if(!$result)
                json_response(500, '서버 오류로 위탁 적용에 실패했습니다.');
        }
    }
}

json_response(200, 'OK');
