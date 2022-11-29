<?php
include_once('./_common.php');

$od_id = get_search_string($_POST['od_id']);
$od = sql_fetch("
  SELECT
    o.*
  FROM
    {$g5['g5_shop_order_table']} o
  WHERE
    od_id = '{$od_id}' and
    mb_id = '{$member['mb_id']}'
");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

$sql = "
  SELECT
    count(*) as cnt
  FROM
      g5_shop_cart
  WHERE
      od_id = '$od_id' and
      mb_id = '{$member['mb_id']}' and
      ct_status not in ('취소', '주문무효', '준비')
";
$row = sql_fetch($sql);
if($row['cnt'] > 0)
    alert('상품준비 단계가 아닌 상품이 있어서 주문서 수정이 불가능합니다.');

$mb = get_member($od['mb_id']);

$send_data = [
    'usrId' => $mb['mb_id'],
    'entId' => $mb['mb_entId'],
    'penOrdId' => $od["ordId"],
    'uuid' => $od['uuid'],
    'penId' => $od["od_penId"],
    'ordNm' => $od["od_b_name"],
    'ordCont' => ($od["od_b_hp"]) ? $od["od_b_hp"] : $od["od_b_tel"],
    'ordMemo' => $od["od_memo"],
    'ordZip' => $od["od_b_zip1"] . $od["od_b_zip2"],
    'ordAddr' => $od["od_b_addr1"],
    'ordAddrDtl' => $od["od_b_addr2"],
    'regUsrId' => $member["mb_id"],
    'regUsrIp' => $_SERVER["REMOTE_ADDR"],
    'finPayment' => '0',
    'payMehCd' => '0',
    'eformType' => '00',
    'returnUrl' => 'NULL',
];

$ct_id_arr = $_POST['ct_id'];
$deleted_arr = $_POST['deleted'];
$it_id_arr = $_POST['it_id'];
$io_id_arr = $_POST['io_id'];
$ct_qty_arr = $_POST['ct_qty'];
$io_type_arr = $_POST['io_type'];
$prodMemo_arr = $_POST['prodMemo'];

$delete_requests = [];

for($i = 0; $i < count($it_id_arr); $i++) {
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $io_id = clean_xss_tags($io_id_arr[$i]);
    $ct_qty = clean_xss_tags($ct_qty_arr[$i]);
    $prodMemo = clean_xss_tags($prodMemo_arr[$i]);
    $ct_id = clean_xss_tags($ct_id_arr[$i]);
    $io_type = 0;
    if($ct_id) {
        $ct = sql_fetch("SELECT * FROM g5_shop_cart WHERE ct_id = '$ct_id' and mb_id = '{$member['mb_id']}'");
        $io_type = $ct['io_type'];
    } else {
        $ct = null;
    }

    $it = sql_fetch(" SELECT * FROM g5_shop_item WHERE it_id = '$it_id' ");
    if(!$it)
        continue;
    
    $io_price = 0;
    if($io_id) {
        $result = sql_fetch(" SELECT io_price FROM g5_shop_item_option WHERE it_id = '$it_id' and io_id = '$io_id' ");
        $io_price = $result['io_price'] ?: 0;
    }

    // 우수 사업소 할인가 적용
    $ct_price = $it['it_price'];
    if($mb['mb_level'] == 4 && $it['it_price_dealer2']) {
        $ct_price = $it['it_price_dealer2'];
    }

    // 사업소별 판매가
    $entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$it['it_id']}' and mb_id = '{$member['mb_id']}' ");
    $it['entprice'] = $entprice['it_price'];

    if($it['entprice'] > 0)
        $ct_price = $it['entprice'];

    // 묶음할인
    $ct_discount = 0;
    $ct_sale_qty = 0;

    for($tmp_i = 0; $tmp_i < count($it_id_arr); $tmp_i++) {
        if($deleted_arr[$i] == '1') continue;
        if($it_id_arr[$tmp_i] !== $it_id) continue;
        if($io_type_arr[$tmp_i] == '1') continue;

        $ct_sale_qty += $ct_qty_arr[$tmp_i];
    }

    $itSaleCntList = [$it["it_sale_cnt"], $it["it_sale_cnt_02"], $it["it_sale_cnt_03"], $it["it_sale_cnt_04"], $it["it_sale_cnt_05"]];
    $itSalePriceList = [$it["it_sale_percent"], $it["it_sale_percent_02"], $it["it_sale_percent_03"], $it["it_sale_percent_04"], $it["it_sale_percent_05"]];
    //우수사업소고 우수사업소 할인가가 있으면 적용
    if($member['mb_level']=="4" && $it['it_sale_percent_great']) {
        $itSalePriceList = [$it["it_sale_percent_great"], $it["it_sale_percent_great_02"], $it["it_sale_percent_great_03"], $it["it_sale_percent_great_04"], $it["it_sale_percent_great_05"]];
    }
    $itSaleCnt = 0;

    if (!$io_type && !$it['entprice']) {
        for($saleCnt = 0; $saleCnt < count($itSaleCntList); $saleCnt++) {
            if($itSaleCntList[$saleCnt] <= $ct_sale_qty) {
                if($itSaleCnt < $itSaleCntList[$saleCnt]) {
                    $ct_discount = $itSalePriceList[$saleCnt] * $ct_qty;
                    $ct_discount = ($ct_price * $ct_qty) - $ct_discount;
                    $itSaleCnt = $itSaleCntList[$saleCnt];
                }
            }
        }
    }

    // 임시조치: 할인금액 마이너스면 0으로 초기화
    if($ct_discount < 0) $ct_discount = 0;

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

    if($ct_id) {
        if(!$ct)
            continue;

        $sto_ids = array_filter(explode('|', $ct['stoId'])); // 재고 ID 배열
        $deleted = $deleted_arr[$i];

        if($deleted == '1') {
            // 삭제
            if($od['od_penId']) {
                // 수급자 주문이면
                $prods = [];
                foreach($sto_ids as $sto_id) {
                    $prods[] = [
                        'stoId' => $sto_id,
                        'prodId' => $it_id,
                        'flag' => 'delete'
                    ];
                }

                $data = $send_data;
                $data['penId'] = $od['od_penId'];
                $data['prods'] = $prods;

                $delete_requests[] = $data;
            } else {
                // 재고주문이면

                // 시스템 재고 삭제
                $result = api_post_call(EROUMCARE_API_STOCK_DELETE_MULTI, [
                    'stoId' => $sto_ids
                ]);

                if($result['errorYN'] !== 'N')
                    alert($result['message']);
            }

            $sql = "
                DELETE FROM
                    g5_shop_cart
                WHERE
                    od_id = '$od_id' and
                    ct_id = '$ct_id'
            ";
            sql_query($sql, true);
            set_order_admin_log($od_id, "상품삭제: {$ct['it_name']}({$ct['ct_option']})");
        } else {
            $prodColor = $prodSize = $prodOption = '';
            $prodOptions = [];
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
                $io_value = sql_real_escape_string(strip_tags($io_value));

                for ($io_idx = 0; $io_idx < count($it_option_subjects); $io_idx++) {
                    switch ($it_option_subjects[$io_idx]) {
                        case '색상':
                            $prodColor = $io_ids[$io_idx];
                            break;
                        case '사이즈':
                            $prodSize = $io_ids[$io_idx];
                            break;
                            default:
                        $prodOptions[] = $io_ids[$io_idx];
                            break;
                    }
                }
                if ($prodOptions && count($prodOptions)) {
                    $prodOption = implode('|', $prodOptions);
                }
            }
            $io_value = $io_value ? $io_value : addslashes($it['it_name']);

            $sql = "
                UPDATE
                    g5_shop_cart
                SET
                    io_id = '$io_id',
                    ct_option = '$io_value',
                    ct_price = '$ct_price',
                    ct_discount = '$ct_discount',
                    io_price =  '$io_price',
                    ct_delivery_cnt = '$ct_delivery_cnt',
                    ct_delivery_price = '$ct_delivery_price',
                    prodMemo = '$prodMemo'
                WHERE
                    od_id = '$od_id' and
                    ct_id = '$ct_id'
            ";
            sql_query($sql, true);

            // 옵션이 변한 경우 : 시스템 재고 옵션 변경해야함
            if( ($ct['io_id'] != $io_id) ) {
                $prods = [];
                foreach($sto_ids as $sto_id) {
                    $prods[] = [
                        'stoId' => $sto_id,
                        'prodId' => $it_id,
                        'prodColor' => $prodColor,
                        'prodSize' => $prodSize,
                        'prodOption' => $prodOption
                    ];
                }

                $result = api_post_call(EROUMCARE_API_STOCK_UPDATE, [
                    'usrId' => $mb['mb_id'],
                    'entId' => $mb['mb_entId'],
                    'prods' => $prods
                ]);

                set_order_admin_log($od_id, "상품변경: {$ct['it_name']}({$ct['ct_option']}) -> {$it['it_name']}({$io_value})");
            }

            // 수량이 줄어든 경우 : 시스템 재고 삭제 해야함
            if($ct['ct_qty'] > $ct_qty) {
                $del_num = $ct['ct_qty'] - $ct_qty;

                $del_sto_ids = [];
                $prods = [];
                for($x = 1; $x <= $del_num; $x++) {
                    $idx = count($sto_ids) - $x;
                    $del_sto_ids[] = $sto_ids[$idx];
                    $prods[] = [
                        'stoId' => $sto_ids[$idx],
                        'prodId' => $it_id,
                        'flag' => 'delete'
                    ];
                }

                if($od['od_penId']) {
                    // 수급자 주문
                    $data = $send_data;
                    $data['penId'] = $od['od_penId'];
                    $data['prods'] = $prods;
    
                    $result = api_post_call(EROUMCARE_API_ORDER_EDIT, $data);
    
                    if($result['errorYN'] !== 'N')
                        alert($result['message']);
                } else {
                    // 재고 주문
                    // 시스템 재고 삭제
                    $result = api_post_call(EROUMCARE_API_STOCK_DELETE_MULTI, [
                        'stoId' => $del_sto_ids
                    ]);

                    if($result['errorYN'] !== 'N')
                        alert($result['message']);
                }

                $sto_ids = array_slice($sto_ids, 0, count($sto_ids) - $del_num);
                $sto_id = '';
                foreach($sto_ids as $stoId) {
                    $sto_id .= $stoId . '|';
                }
                $sql = "
                    UPDATE g5_shop_cart
                    SET 
                        stoId = '$sto_id',
                        ct_qty = '$ct_qty' 
                    WHERE ct_id = '$ct_id'
                ";
                sql_query($sql, true);
                set_order_admin_log($od_id, "상품수량변경: {$it['it_name']}($io_value) {$ct['ct_qty']}개 -> {$ct_qty}개");
            }

            // 수량이 늘어난 경우 : 시스템 재고 추가 해야함
            else if($ct['ct_qty'] < $ct_qty) {
                $add_num = $ct_qty - $ct['ct_qty'];

                $prods = [];
                for($x = 0; $x < $add_num; $x++) {
                    $prods[] = [
                        'prodId' => $it_id,
                        'prodColor' => $prodColor,
                        'prodSize' => $prodSize,
                        'prodOption' => $prodOption,
                        'prodBarNum' => '',
                        'prodManuDate' => date("Y-m-d"),
                        'stoMemo' => $prodMemo,
                        'ct_id' => $ct_id,
                        'flag' => 'insert'
                    ];
                }

                // 추가
                if($od['od_penId']) {
                    // 수급자 주문일때
                    $data = $send_data;
                    $data['penId'] = $od['od_penId'];
                    $data['prods'] = $prods;
                    $result = api_post_call(EROUMCARE_API_ORDER_EDIT, $data);

                    if($result['errorYN'] !== 'N')
                        alert($result['message']);
                    
                    $sto_id = '';
                    foreach($result['data']['stockList'] as $data) {
                        $sto_id .= $data['stoId'] . '|';
                    }

                    $sql = "
                        UPDATE g5_shop_cart
                        SET stoId = CONCAT(stoId,'$sto_id')
                        WHERE ct_id = '$ct_id'
                    ";
                    sql_query($sql, true);
                } else {
                    // 재고주문
                    $data = $send_data;
                    $data['prods'] = $prods;
                    $result = api_post_call(EROUMCARE_API_STOCK_INSERT, $data);

                    if($result['errorYN'] !== 'N')
                        alert($result['message']);
                    
                    $sto_id = '';
                    foreach($result['data'] as $data) {
                        $sto_id .= $data['stoId'] . '|';
                    }

                    $sql = "
                        UPDATE g5_shop_cart
                        SET 
                            stoId = CONCAT(stoId,'$sto_id'),
                            ct_qty = '$ct_qty'
                        WHERE ct_id = '$ct_id'
                    ";
                    sql_query($sql, true);
                }
                set_order_admin_log($od_id, "상품수량변경: {$it['it_name']}($io_value) {$ct['ct_qty']}개 -> {$ct_qty}개");
            }
        }
    } else {
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
            $opt_list[$row['io_type']][$row['io_id']]['price_partner'] = $row['io_price_partner'];
            $opt_list[$row['io_type']][$row['io_id']]['price_dealer'] = $row['io_price_dealer'];
            $opt_list[$row['io_type']][$row['io_id']]['price_dealer2'] = $row['io_price_dealer2'];
            $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
            $opt_list[$row['io_type']][$row['io_id']]['io_thezone'] = $row['io_thezone'];

            // 선택옵션 개수
            if(!$row['io_type'])
                $lst_count++;
        }
        
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
            pt_msg1,
            pt_msg2,
            pt_msg3,
            ct_history,
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
            ordLendStrDtm,
            ordLendEndDtm,
            prodSupYn,
            ct_pen_id,
            ct_warehouse
            )
        VALUES ";

        $ct_select = 1;
        $ct_select_time = G5_TIME_YMDHIS;
        $sw_direct = 0;

        $prodColor = $prodSize = $prodOption = '';
        $prodOptions = [];
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
            $io_value = sql_real_escape_string(strip_tags($io_value));

            for ($io_idx = 0; $io_idx < count($it_option_subjects); $io_idx++) {
                switch ($it_option_subjects[$io_idx]) {
                    case '색상':
                        $prodColor = $io_ids[$io_idx];
                        break;
                    case '사이즈':
                        $prodSize = $io_ids[$io_idx];
                        break;
                        default:
                    $prodOptions[] = $io_ids[$io_idx];
                        break;
                }
            }
            if ($prodOptions && count($prodOptions)) {
                $prodOption = implode('|', $prodOptions);
            }
        }
        $io_value = $io_value ? $io_value : addslashes($it['it_name']);

        $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];
        $io_value = sql_real_escape_string(strip_tags($io_value));
        $remote_addr = get_real_client_ip();
        $point = 0;

        // 대여기간
        $sqlOrdLendStrDtm = 'NULL';
        $sqlOrdLendEndDtm = 'NULL';
        if ($ordLendStartDtm && $ordLendEndDtm) {
            $sqlOrdLendStrDtm = "'{$ordLendStartDtm}'";
            $sqlOrdLendEndDtm = "'{$ordLendEndDtm}'";
        }

        // 수급자 여부
        $sql_ct_pen_id = 'NULL';
        if($od['od_penId']) {
            $sql_ct_pen_id = "'{$od['od_penId']}'";
        }

        // 출하창고
        $ct_warehouse = '검단창고';
        if($it['it_default_warehouse']) {
            $ct_warehouse = $it['it_default_warehouse'];
        }

        $uid = uuidv4();

        $insert_sql = $sql . "
        (
          '$od_id',
          '{$od['mb_id']}',
          '{$it['it_id']}',
          '".addslashes($it['it_name'])."',
          '{$it['it_sc_type']}',
          '{$it['it_sc_method']}',
          '{$it['it_sc_price']}',
          '{$it['it_sc_minimum']}',
          '{$it['it_sc_qty']}',
          '작성',
          '$ct_price',
          '$point',
          '0',
          '0',
          '$io_value',
          '$ct_qty',
          '{$it['it_notax']}',
          '$io_id',
          '$io_type',
          '$io_price',
          '".G5_TIME_YMDHIS."',
          '$remote_addr',
          '$ct_send_cost',
          '$sw_direct',
          '$ct_select',
          '$ct_select_time',
          '{$it['pt_it']}',
          '',
          '',
          '',
          '',
          '$ct_discount',
          '0',
          '$uid',
          '$io_thezone',
          '$ct_delivery_cnt',
          '{$_POST['it_delivery_price'][$i]}',
          '$ct_delivery_company',
          '{$it['it_is_direct_delivery']}',
          '{$it['it_direct_delivery_partner']}',
          '{$it['it_direct_delivery_price']}',
          '$prodMemo',
          $sqlOrdLendStrDtm,
          $sqlOrdLendEndDtm,
          '{$it['prodSupYn']}',
          $sql_ct_pen_id,
          '$ct_warehouse'
        )";
        sql_query($insert_sql, true);
        $ct_id = sql_insert_id();

        $prods = [];
        for($x = 0; $x < $ct_qty; $x++) {
            $prods[] = [
                'prodId' => $it_id,
                'prodColor' => $prodColor,
                'prodSize' => $prodSize,
                'prodOption' => $prodOption,
                'prodBarNum' => '',
                'prodManuDate' => date("Y-m-d"),
                'stoMemo' => $prodMemo,
                'ct_id' => $ct_id,
                'flag' => 'insert'
            ];
        }

        // 추가
        if($od['od_penId']) {
            // 수급자 주문일때
            $data = $send_data;
            $data['penId'] = $od['od_penId'];
            $data['prods'] = $prods;
            $result = api_post_call(EROUMCARE_API_ORDER_EDIT, $data);

            if($result['errorYN'] !== 'N')
                die($result['message']);
                //alert('시스템 주문 수정 중 오류가 발생했습니다.');
            
            $sto_id = '';
            if(!$result['data']['stockList']) {
                print_r2($result);
                exit;
            }

            foreach($result['data']['stockList'] as $data) {
                $sto_id .= $data['stoId'] . '|';
            }

            $sql = "
                UPDATE g5_shop_cart
                SET ct_status = '준비', stoId = '$sto_id'
                WHERE ct_id = '$ct_id'
            ";
            sql_query($sql, true);
        } else {
            // 재고주문
            $data = $send_data;
            $data['prods'] = $prods;
            $result = api_post_call(EROUMCARE_API_STOCK_INSERT, $data);

            if($result['errorYN'] !== 'N')
                die($result['message']);
                //alert('시스템에 재고 추가 중 오류가 발생했습니다.');
            
            $sto_id = '';
            foreach($result['data'] as $data) {
                $sto_id .= $data['stoId'] . '|';
            }

            $sql = "
                UPDATE g5_shop_cart
                SET ct_status = '준비', stoId = '$sto_id'
                WHERE ct_id = '$ct_id'
            ";
            sql_query($sql, true);
        }

        set_order_admin_log($od_id, "상품추가: {$it['it_name']}($io_value) {$ct_qty}개");
    }
}

// 수급자 주문은 삭제를 나중에하도록 수정 (추가할거 있으면 추가부터 하도록)
foreach($delete_requests as $data) {
    $result = api_post_call(EROUMCARE_API_ORDER_EDIT, $data);

    if($result['errorYN'] !== 'N')
        alert($result['message']);
}

// 상품수 수정
$sql = " select COUNT(distinct it_id, ct_uid) as cart_count, count(*) as delivery_count
            from {$g5['g5_shop_cart_table']} where od_id = '$od_id'  ";
$row = sql_fetch($sql);
sql_query("update {$g5['g5_shop_order_table']} set od_cart_count = '{$row['cart_count']}', od_delivery_total = '{$row['delivery_count']}' where od_id = '$od_id' ");


// 배송정보 수정
$od_b_name = clean_xss_tags($_POST['od_b_name']);
$od_b_tel = clean_xss_tags($_POST['od_b_tel']);
$od_b_hp = clean_xss_tags($_POST['od_b_hp']);
$od_b_zip = preg_replace('/[^0-9]/', '', $_POST['od_b_zip']);
$od_b_zip1 = substr($od_b_zip, 0, 3);
$od_b_zip2 = substr($od_b_zip, 3);
$od_b_addr_jibeon = clean_xss_tags($_POST['od_b_addr_jibeon']);
$od_b_addr1 = clean_xss_tags($_POST['od_b_addr1']);
$od_b_addr2 = clean_xss_tags($_POST['od_b_addr2']);

// 배송비
$_tmp_delivery_price = 0;
if( is_array($_POST['it_delivery_price']) && COUNT($_POST['it_delivery_price']) ) {
  foreach($_POST['it_delivery_price'] as $key => $val) {
    $_tmp_delivery_price += $val;
  }
}
$od_send_cost = $_tmp_delivery_price;

sql_query("
    UPDATE
        g5_shop_order
    SET
        od_b_name = '$od_b_name',
        od_b_tel = '$od_b_tel',
        od_b_zip1 = '$od_b_zip1',
        od_b_zip2 = '$od_b_zip2',
        od_b_addr_jibeon = '$od_b_addr_jibeon',
        od_b_addr1 = '$od_b_addr1',
        od_b_addr2 = '$od_b_addr2',
        od_send_cost = '$od_send_cost'
    WHERE
        od_id = '$od_id'
");


$uid = md5($od['od_id'].$od['od_time'].$od['od_ip']);
set_session('ss_orderview_uid', $uid);
goto_url(G5_SHOP_URL.'/orderinquiryview.php?od_id='.$od_id.'&amp;uid='.$uid);
