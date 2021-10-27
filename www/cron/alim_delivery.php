<?php
include_once('./_common.php');

/*
 * 출고완료 상품 알림톡 보내기
 * 매일 오후 12시, 오후 6시 두번 보냄
 * ct_alim: 0 = 알림톡 미전송, 1 = 알림톡 전송완료
 */

$key_check = "2xBkK#4fKR9hPp=x+J9dDWr9fxR5Nt*2^e@D-!AL";
$key = $_POST['key'];

// 키 인증
if($key !== $key_check)
    json_response(400, '인증에 실패했습니다.');

$sql = "
    select c.*, count(*) as cnt from g5_shop_cart c
    where ct_status = '배송' and ct_alim = 0
    group by od_id
";
$result = sql_query($sql);

while($ct = sql_fetch_array($result)) {
    $mb = get_member($ct['mb_id']);
    $od = get_order($ct['od_id']);

    $it_name_txt = $ct['it_name'];
    if($ct['cnt'] > 1)
        $it_name_txt .= ' 외 ' . ($ct['cnt'] - 1) . '건';

    // 주문조회 주소 생성
    $uid = md5($od['od_id'].$od['od_time'].$od['od_ip']);
    $url = "https://eroumcare.com/shop/orderinquiryview.php?od_id={$od['od_id']}&uid={$uid}";

    $token = get_biztalk_token();
    if(!$token)
        json_response(500, '비즈톡 토큰 발급 오류');

    send_alim_talk(
        'OD_DELIVERY_'.$od['od_id'],
        $mb["mb_hp"],
        'ent_order_delivery',
        "[출고완료 안내]\n{$mb['mb_entNm']}님, 주문하신 상품이 출고완료 되었습니다.\n\n택배물품의 경우 택배사 사정에따라 2~3일 소요됩니다.\n■ 주문일시 : ".date('Y/m/d H:i', strtotime($od['od_time']))."\n■ 주문번호 : {$od['od_id']}\n■ 주문내역 : {$it_name_txt}\n■ 배송지 : {$od['od_b_addr1']} {$od['od_b_addr2']} {$od['od_b_addr3']} {$od['od_b_addr_jibeon']}",
        [
            'button' => [
                [
                    'name' => '바코드/송장 확인',
                    'type' => 'WL',
                    'url_mobile' => $url
                ]
            ]
        ],
        $token
    );

    $sql = "
        update g5_shop_cart
        set ct_alim = 1
        where ct_status = '배송' and ct_alim = 0 and od_id = '{$od['od_id']}'
    ";
    sql_query($sql);
}

json_response(200, 'OK');
