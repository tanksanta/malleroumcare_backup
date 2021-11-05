<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
    json_response(400, '먼저 로그인하세요.');

set_cart_id(1);
set_session("ss_direct", 1);
$tmp_cart_id = get_session('ss_cart_direct');

$it_id_arr = $_POST['it_id'];
$io_id_arr = $_POST['io_id'];
$ct_qty_arr = $_POST['ct_qty'];
$prodMemo_arr = $_POST['prodMemo'];

if(!($it_id_arr && $io_id_arr && $ct_qty_arr)) {
    json_response(400, '주문할 상품을 선택해주세요.');
}

sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_direct = 1 ", false);

for($i = 0; $i < count($it_id_arr); $i++) {
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $io_id = clean_xss_tags($io_id_arr[$i]);
    $io_id = preg_replace(G5_OPTION_ID_FILTER, '', $io_id);
    $ct_qty = clean_xss_tags($ct_qty_arr[$i]);
    $prodMemo = clean_xss_tags($prodMemo_arr[$i]);
    $io_type = 0;

    if(!$it_id || $ct_qty < 1) continue;

    $it = sql_fetch(" select * from {$g5['g5_shop_item_table']} where it_id = '{$it_id}' ");
    if(!$it['it_id']) continue;

    $io_value = '';
    if ($io_id) {
      $it_option_subjects = explode(',', $it['it_option_subject']);
      $io_ids = explode(chr(30), $io_id);
      for($g = 0; $g< count($io_ids); $g++) {
        if ($g > 0) {
          $io_value .= ' / ';
        }
        $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
      }
    }

    if($it['it_sc_type'] == 1)
        $ct_send_cost = 2; // 무료
    else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
        $ct_send_cost = 1; // 착불
    else
        $ct_send_cost = 0;

    // 옵션정보를 얻어서 배열에 저장
    $opt_list = array();
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
    $result = sql_query($sql);
    $lst_count = 0;
    for($k=0; $row=sql_fetch_array($result); $k++) {
        $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
        $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
        $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
        $opt_list[$row['io_type']][$row['io_id']]['io_price'] = $row['io_price'];
        $opt_list[$row['io_type']][$row['io_id']]['io_price_partner'] = $row['io_price_partner'];
        $opt_list[$row['io_type']][$row['io_id']]['io_price_dealer'] = $row['io_price_dealer'];
        $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
        $opt_list[$row['io_type']][$row['io_id']]['io_thezone'] = $row['io_thezone'];

        // 선택옵션 개수
        if(!$row['io_type'])
            $lst_count++;
    }

    // 선택옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
    if($lst_count && $io_id == '')
        continue;
  
    // 구매할 수 없는 옵션은 건너뜀
    if($io_id && !$opt_list[$io_type][$io_id]['use'])
        continue;
    
    $io_price = samhwa_opt_price($opt_list[$io_type][$io_id], THEMA_KEY);
    $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];

    // 구매가격이 음수인지 체크
    if($io_type) {
    if((int)$io_price < 0)
        json_response(400, '구매금액이 음수인 상품은 구매할 수 없습니다.');
    } else {
    if((int)$it['it_price'] + (int)$io_price < 0)
        json_response(400, '구매금액이 음수인 상품은 구매할 수 없습니다.');
    }

    $io_value = sql_real_escape_string(strip_tags($io_value));
    $remote_addr = get_real_client_ip();

    if($it['it_delivery_min_cnt']) {
        //박스 개수 큰것 +작은것 - >ceil
        $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($ct_qty / $it['it_delivery_cnt']) : 0;
        //큰박스 floor 한 가격을 담음
        $ct_delivery_bigbox = $it['it_delivery_cnt'] ? floor($ct_qty / $it['it_delivery_cnt']) : 0;
        $ct_delivery_price = $it['it_delivery_cnt'] ? ($ct_delivery_bigbox * $it['it_delivery_price']) : 0;
        //나머지
        $remainder = $ct_qty % $it['it_delivery_cnt'];
        //나머지가 있으면
        if($remainder) {
            //나머지가 최소수량보다 작으면
            if($remainder <= $it['it_delivery_min_cnt']) {
                //작은 박스 가격 더해줌
                $ct_delivery_price = $ct_delivery_price + $it['it_delivery_min_price'];
            } else {
                //큰 박스 가격 더해줌
                $ct_delivery_price = $ct_delivery_price + $it['it_delivery_price'];
            }
        }
    } else {
        //없으면 큰박스로만 진행
        $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($ct_qty / $it['it_delivery_cnt']) : 0;
        $ct_delivery_price = $ct_delivery_cnt * $it['it_delivery_price'];
    }

    $ct_delivery_company = 'ilogen';
    $io_value = $io_value ? $io_value : addslashes($it['it_name']);

    $it_price = $it['it_price'];
    // 우수사업소 할인
    if($member['mb_level'] == 4 && $it['it_price_dealer2']) {
        $it_price = $it['it_price_dealer2'];
    }

    // 비유통상품 가격
    if($it['prodSupYn'] == 'N') {
        $it_price = 0;
    }

    // 묶음할인
    $ct_discount = 0;
    $ct_sale_qty = 0;

    for($tmp_i = 0; $tmp_i < count($it_id_arr); $tmp_i++) {
        if($it_id_arr[$tmp_i] !== $it_id) continue;

        $ct_sale_qty += $ct_qty_arr[$tmp_i];
    }

    $itSaleCntList = [$it["it_sale_cnt"], $it["it_sale_cnt_02"], $it["it_sale_cnt_03"], $it["it_sale_cnt_04"], $it["it_sale_cnt_05"]];
    $itSalePriceList = [$it["it_sale_percent"], $it["it_sale_percent_02"], $it["it_sale_percent_03"], $it["it_sale_percent_04"], $it["it_sale_percent_05"]];
    //우수사업소고 우수사업소 할인가가 있으면 적용
    if($member['mb_level']=="4"&&$it['it_sale_percent_great']){
        $itSalePriceList = [$it["it_sale_percent_great"], $it["it_sale_percent_great_02"], $it["it_sale_percent_great_03"], $it["it_sale_percent_great_04"], $it["it_sale_percent_great_05"]];
    }
    $itSaleCnt = 0;

    if (!$io_type) {
        for($saleCnt = 0; $saleCnt < count($itSaleCntList); $saleCnt++) {
            if($itSaleCntList[$saleCnt] <= $ct_sale_qty) {
                if($itSaleCnt < $itSaleCntList[$saleCnt]) {
                    $ct_discount = $itSalePriceList[$saleCnt] * $ct_qty;
                    $ct_discount = ($it_price * $ct_qty) - $ct_discount;
                    $itSaleCnt = $itSaleCntList[$saleCnt];
                }
            }
        }
    }

    // 임시조치: 할인금액 마이너스면 0으로 초기화
    if($ct_discount < 0) $ct_discount = 0;


    $sql = " INSERT INTO {$g5['g5_shop_cart_table']}
        ( od_id,
        mb_id,
        it_id,
        it_name,
        it_sc_type,
        it_sc_method,
        it_sc_price,
        it_sc_minimum,
        it_sc_qty,
        ct_status,
        ct_price,
        ct_point,
        ct_point_use,
        ct_stock_use,
        ct_option,
        ct_qty,
        ct_notax,
        io_id,
        io_type,
        io_price,
        ct_time,
        ct_ip,
        ct_send_cost,
        ct_direct,
        ct_select,
        ct_select_time,
        pt_it,
        ct_discount,
        ct_price_type,
        ct_uid,
        io_thezone,
        ct_delivery_cnt,
        ct_delivery_price,
        ct_delivery_company,
        ct_is_direct_delivery,
        ct_direct_delivery_partner,
        ct_direct_delivery_price,
        prodMemo,
        prodSupYn
        )
    VALUES ";

    $uid = uuidv4();
    $ct_time = G5_TIME_YMDHIS;
    
    $insert_sql =  $sql . "
    (
        '$tmp_cart_id',
        '{$member['mb_id']}',
        '$it_id',
        '".addslashes($it['it_name'])."',
        '{$it['it_sc_type']}',
        '{$it['it_sc_method']}',
        '{$it['it_sc_price']}',
        '{$it['it_sc_minimum']}',
        '{$it['it_sc_qty']}',
        '쇼핑',
        '$it_price',
        '0',
        '0',
        '0',
        '$io_value',
        '$ct_qty',
        '{$it['it_notax']}',
        '$io_id',
        '$io_type',
        '$io_price',
        '$ct_time',
        '$remote_addr',
        '$ct_send_cost',
        '1',
        '1',
        '$ct_time',
        '{$it['pt_it']}',
        '$ct_discount',
        '0',
        '$uid',
        '$io_thezone',
        '$ct_delivery_cnt',
        '$ct_delivery_price',
        '$ct_delivery_company',
        '{$it['it_is_direct_delivery']}',
        '{$it['it_direct_delivery_partner']}',
        '{$it['it_direct_delivery_price']}',
        '$prodMemo',
        '{$it['prodSupYn']}'
    )
    ";

    $result = sql_query($insert_sql);
    if(!$result)
        json_response(500, 'DB 오류가 발생하여 주문을 완료하지 못했습니다.');
}

// 새로운 주문번호 생성
$od_id = get_uniqid();
set_session('ss_order_id', $od_id);

json_response(200, 'OK', $tmp_cart_id);
