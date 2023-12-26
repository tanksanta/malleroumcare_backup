<?php
include_once('./_common.php');

/*
 * 출고완료후 3일 지나면 배송완료 처리
 * ct_status: 배송 = 출고완료, 완료 = 배송완료
 * ct_ex_date(예상출고일)
 */
$API_DATA_GO_KR_KEY = "3Wr%2Bh6X4HjuV%2FExQPkWsLGfXE%2Bx%2B0%2F%2FCycRS4kKOVfK9rS0M4Ln8dhoOT6Xx3EiRZYNUJgkDot7y8jMHynsVMg%3D%3D";

$index = 0;
$date_index = 0;
$day_offs = [];

// 공휴일 및 주말 제외 3일
while($date_index <= 3) {
    $time_index = strtotime("-{$index} days");
    $year_month = date('Y-m', $time_index);

    if (!$day_offs[$year_month]) {
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, 80);
        curl_setopt($oCurl, CURLOPT_URL, "http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo?serviceKey={$API_DATA_GO_KR_KEY}&solYear=" . date('Y', $time_index) . "&solMonth=" . date('m', $time_index));
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $res = curl_exec($oCurl);
        $xml = simplexml_load_string($res);
        $day_offs[$year_month] = json_decode(json_encode($xml->body->items), TRUE);
    }

    $is_holiday = false;
    foreach ($day_offs[$year_month]['item'] as $holiday) {
        if ($holiday['locdate'] == date('Ymd', $time_index)) {
            $is_holiday = true;
            break;
        }
    }
    if (in_array(date('w' , $time_index), [0, 6])) { // 6 토요일, 0 일요일
        $is_holiday = true;
    }
    if ($is_holiday) {
        $index++;
        continue;
    }

    $index++;
    $date_index++;
}
$sub_day = date('Y-m-d', $time_index);

$where = "
    c.ct_status = '배송'
    AND c.ct_ex_date <= '{$sub_day}'
    AND ocr.od_id IS NULL
";

$query = sql_query("SELECT *, c.od_id, c.mb_id FROM g5_shop_cart as c
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

    $it_name = $result['it_name'];
    if($result['it_name'] !== $result['ct_option']){
      $it_name = $it_name."(".$result['ct_option'].")";
    }

    if($result['io_type'])
        $opt_price = $result['io_price'];
    else
        $opt_price = $result['ct_price'] + $result['io_price'];

    $result["opt_price"] = $opt_price;

    // 소계
    $result['ct_price_stotal'] = $opt_price * $result['ct_qty'] - $result['ct_discount'];
    if($result["prodSupYn"] == "Y") {
        $result["ct_price_stotal"] -= ($result["ct_stock_qty"] * $opt_price);
    }

    $point_receiver = get_member($result['mb_id']);
    $point = (int)($result["ct_price_stotal"] / 100 * $default['de_it_grade' . $point_receiver['mb_grade'] . '_discount']);
    $point_content = "주문({$result['od_id']}) {$it_name} 상품 배송완료 포인트 적립 ({$default['de_it_grade' . $point_receiver['mb_grade'] . '_name']} / {$default['de_it_grade' . $point_receiver['mb_grade'] . '_discount']}%)";

    insert_point($point_receiver['mb_id'], $point, $point_content, 'order_completed', $result['ct_id'], $point_receiver['mb_id']);

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

sql_query("UPDATE g5_shop_item AS it, (SELECT COUNT(it_id)AS cnt,it_id,it_name FROM g5_shop_cart WHERE ct_status = '완료' GROUP BY it_id ) AS sale
SET it.it_sum_qty = sale.cnt WHERE it.it_id = sale.it_id"); //제품별 판매 수량 업데이트

if(!$count) {
    json_response(200, $sub_day . '까지 배송완료 처리할 내용이 없습니다.');
}

json_response(201, $sub_day . '까지 OK');
