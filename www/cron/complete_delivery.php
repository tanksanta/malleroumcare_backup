<?php
include_once('./_common.php');

/*
 * 출고완료후 3일 지나면 배송완료 처리
 * ct_status: 배송 = 출고완료, 완료 = 배송완료
 * ct_ex_date(예상출고일)
 */

$where = "
    c.ct_status = '배송'
    AND c.ct_ex_date <= DATE_SUB(NOW(), INTERVAL 3 DAY)
    AND ocr.od_id IS NULL
";

$query = sql_query("SELECT * FROM g5_shop_cart as c
    LEFT JOIN g5_shop_order_cancel_request ocr ON c.od_id = ocr.od_id -- 취소 요청
    WHERE
        {$where}
");


$count = 0;

while($result = sql_fetch_array($query)) {
    set_order_admin_log($result['od_id'], '상품 [' . $result['ct_option'] . '] 상태 출고완료후 3일 경과로, 배송완료 단계로 자동 변경');
    // sql_query("UPDATE g5_shop_cart SET
    //     ct_status = '완료',
    //     ct_move_date = NOW()
    // WHERE
    //     ct_id = '{$result['ct_id']}'
    // ");

    $count++;
}

sql_query("UPDATE g5_shop_cart as c
    LEFT JOIN g5_shop_order_cancel_request ocr ON c.od_id = ocr.od_id
    SET
        c.ct_status = '완료',
        c.ct_move_date = NOW()
    WHERE
        {$where}
");

if(!$count) {
    json_response(200, '배송완료 처리할 내용이 없습니다.');
}

json_response(201, 'OK');