<?php
$sub_menu = '400400';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], "w", true);
if($auth_check)
    json_response(400, $auth_check);

$ct_id_arr = $_POST['ct_id'];
if(!$ct_id_arr)
    json_response(400, '합포할 주문을 선택해주세요.');

$combine_orders = [];

foreach($ct_id_arr as $ct_id) {
    $ct = sql_fetch(" select * from g5_shop_cart where ct_id = '$ct_id' ");
    if(!$ct['ct_id'])
        continue;
    
    $combine_orders += [ $ct['od_id'] ];
}

foreach($combine_orders as $od_id) {
    $carts_result = sql_query("
        select ct_id, ct_status, ct_delivery_cnt, ct_delivery_price, ct_combine_ct_id
        from g5_shop_cart
        where od_id = '$od_id' and ct_status not in ('취소', '주문무효')
        and ct_is_direct_delivery = 0
        order by ct_id asc
    ");

    $greatest = 0;
    $target = null;
    $carts = [];
    while($cart = sql_fetch_array($carts_result)) {
        // 이미 수동으로 합포 적용한 상품이 있으면 continue
        if($cart['ct_combine_ct_id']) continue 2;

        // 가장 박스수량이 많은 상품을 찾아 합포 대상으로 설정
        if($cart['ct_delivery_cnt'] > $greatest) {
            $greatest = $cart['ct_delivery_cnt'];
            $target = $cart['ct_id'];
        }

        $carts[$cart['ct_id']] = $cart;
    }

    try {
        $packed = get_packed_boxes($od_id);

        $boxes = $packed['joinPacked'];
        if(!$boxes) continue; // 합포대상이 없으면 continue;

        // 합포 대상에 합포 적용
        foreach($boxes as $box) {
            foreach($box['items'] as $ct_id => $item) {
                $box_qty = $carts[$ct_id]['ct_delivery_cnt'];
                $price = $carts[$ct_id]['ct_delivery_price'];

                if($box_qty > 1 || $ct_id == $target) {
                    // 박스수량이 여러개인 경우 마지막 한 박스만 합포. 나머지 박스들은 완포임.

                    $unit_price = (int) ($price / $box_qty);

                    // 합포될 배송박스의 수량 및 가격을 뺀다
                    $box_qty -= 1;
                    $price = $unit_price * $box_qty;

                    if($ct_id == $target) {
                        // 박스가 합포 대상이면 합포박스의 수량 및 배송비를 더함
                        $box_qty += 1;
                        $price += $box['price'];
                    }

                    sql_query("
                        update g5_shop_cart
                        set ct_delivery_cnt = '$box_qty', ct_delivery_price = '$price',
                        ct_is_auto_combined = 1
                        where ct_id = '$ct_id'
                    ");
                } else {
                    // 나머지 모두 합포인 상품들은 합포 체크
                    sql_query("
                        update g5_shop_cart
                        set ct_combine_ct_id = '$target',
                        ct_is_auto_combined = 1
                        where ct_id = '$ct_id'
                    ");
                }
            }
        }

    } catch(Exception $e) {
        // 합포 오류 발생
        json_response($e->getCode(), $e->getMessage());
    }
}

json_response(200, 'OK');
