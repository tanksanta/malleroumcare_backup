<?php
$sub_menu = '400400';
include_once('./_common.php');
auth_check($auth[$sub_menu], "w");

$od_id = get_search_string($_POST['od_id']);
$ct_id_arr = $_POST['ct_id'];
$io_type_arr = $_POST['io_type'];
$delete_arr = $_POST['delete'];
$it_id_arr = $_POST['it_id'];
$io_id_arr = $_POST['io_id'];
$qty_arr = $_POST['qty'];
$it_price_arr = $_POST['it_price'];
$memo_arr = $_POST['memo'];

$od = sql_fetch(" select * from g5_shop_order where od_id = '$od_id' ");
if(!$od['od_id'])
    alert('존재하지 않는 주문입니다.');

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

for($i = 0; $i < count($ct_id_arr); $i++) {
    $ct_id = clean_xss_tags($ct_id_arr[$i]);
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $io_id = clean_xss_tags(trim($io_id_arr[$i]));
    $qty = clean_xss_tags($qty_arr[$i]);
    $qty = (int) preg_replace("/[^\d]/","", $qty);
    $it_price = clean_xss_tags($it_price_arr[$i]);
    $it_price = (int) preg_replace("/[^\d]/","", $it_price);
    $memo = clean_xss_tags($memo_arr[$i]);

    if(!$it_id)
        continue;

    $it = sql_fetch(" SELECT * FROM g5_shop_item WHERE it_id = '$it_id' ");

    $io_type = 0;
    if($ct_id) {
        // 수정 or 삭제
        $ct = sql_fetch(" select * from g5_shop_cart where od_id = '$od_id' and ct_id = '$ct_id' ");
        if(!$ct['ct_id'])
            continue;
        $sto_ids = array_filter(explode('|', $ct['stoId'])); // 재고 ID 배열
        
        $delete = $delete_arr[$i];
        $io_type = clean_xss_tags($io_type_arr[$i]);

        if($delete) {
            // 삭제

            if($od['od_penId']) {
                // 수급자주문이면
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

                $result = api_post_call(EROUMCARE_API_ORDER_EDIT, $data);

                if($result['errorYN'] !== 'N')
                    die($result['message']);
                    //alert('시스템 주문 수정 중 오류가 발생했습니다.');

            } else if($io_type != '1') {
                // 재고주문이면

                // 시스템 재고 삭제
                $result = api_post_call(EROUMCARE_API_STOCK_DELETE_MULTI, [
                    'stoId' => $sto_ids
                ]);

                if($result['errorYN'] !== 'N')
                    die($result['message']);
                    //alert('시스템에서 재고 삭제를 실패했습니다.');
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
            // 수정
            if($io_type != '1') {
                $it_price_orig = $ct['ct_price'] + $ct['io_price'];
                $price_sql = " ct_price = '$it_price', io_price = '0', ";
            } else {
                $it_price_orig = $ct['io_price'];
                $price_sql = " ct_price = '0', io_price = '$it_price', ";
            }
            $total_price_orig = $it_price_orig * ($ct["ct_qty"] - $ct["ct_stock_qty"]) - $ct['ct_discount'];
            // 단가 역산
            $it_price_orig = $total_price_orig ? round($total_price_orig / ($ct["ct_qty"] - $ct["ct_stock_qty"])) : 0;

            if( ($ct['it_id'] == $it_id ) && ( $ct['io_id'] == $io_id ) && ( $ct['ct_qty'] == $qty ) && ($it_price_orig == $it_price) ) {
                // 변경사항이 없으면 continue
                if($ct['prodMemo'] != $memo) {
                    // 요청사항만 변경된경우 요청사항만 반영
                    $sql = " UPDATE g5_shop_cart SET prodMemo = '$memo' WHERE od_id = '$od_id' and ct_id = '$ct_id' ";
                    sql_query($sql, true);
                    set_order_admin_log($od_id, "요청사항수정: {$ct['it_name']}({$ct['ct_option']}) - '{$ct['prodMemo']}' -> '{$memo}'");
                }
                continue;
            }

            $prodColor = $prodSize = $prodOption = '';
            $prodOptions = [];
            $io_value = '';
            if ($io_id) {
                $io_row = sql_fetch("select * from g5_shop_item_option where it_id = '{$it_id}' and io_id = '{$io_id}'");

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
                    it_id = '$it_id',
                    it_name = '{$it['it_name']}',
                    io_id = '$io_id',
                    ct_option = '$io_value',
                    $price_sql
                    ct_discount = '0',
                    ct_qty = '$qty',
                    prodMemo = '$memo',
                    io_thezone = '{$io_row['io_thezone']}'
                WHERE
                    od_id = '$od_id' and
                    ct_id = '$ct_id'
            ";
            sql_query($sql, true);

            if($io_type != '1') {

                // 상품 or 옵션이 변한 경우 : 시스템 재고 옵션 변경해야함
                if( ($ct['it_id'] != $it_id ) || ($ct['io_id'] != $io_id) ) {
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
                if($ct['ct_qty'] > $qty) {
                    $del_num = $ct['ct_qty'] - $qty;

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
                            die($result['message']);
                            //alert('시스템에서 주문 수정을 실패했습니다.');
                    } else {
                        // 재고 주문
                        // 시스템 재고 삭제
                        $result = api_post_call(EROUMCARE_API_STOCK_DELETE_MULTI, [
                            'stoId' => $del_sto_ids
                        ]);

                        if($result['errorYN'] !== 'N')
                            die($result['message']);
                            //alert('시스템에서 재고 삭제를 실패했습니다.');
                    }

                    $sto_ids = array_slice($sto_ids, 0, count($sto_ids) - $del_num);
                    $sto_id = '';
                    foreach($sto_ids as $stoId) {
                        $sto_id .= $stoId . '|';
                    }
                    $sql = "
                        UPDATE g5_shop_cart
                        SET stoId = '$sto_id'
                        WHERE ct_id = '$ct_id'
                    ";
                    sql_query($sql, true);
                    set_order_admin_log($od_id, "상품수량변경: {$it['it_name']}($io_value) {$ct['ct_qty']}개 -> {$qty}개");
                }

                // 수량이 늘어난 경우 : 시스템 재고 추가 해야함
                else if($ct['ct_qty'] < $qty) {
                    $add_num = $qty - $ct['ct_qty'];

                    $prods = [];
                    for($x = 0; $x < $add_num; $x++) {
                        $prods[] = [
                            'prodId' => $it_id,
                            'prodColor' => $prodColor,
                            'prodSize' => $prodSize,
                            'prodOption' => $prodOption,
                            'prodBarNum' => '',
                            'prodManuDate' => date("Y-m-d"),
                            'stoMemo' => $memo,
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
                            die($result['message']);
                            //alert('시스템에 재고 추가 중 오류가 발생했습니다.');
                        
                        $sto_id = '';
                        foreach($result['data'] as $data) {
                            $sto_id .= $data['stoId'] . '|';
                        }

                        $sql = "
                            UPDATE g5_shop_cart
                            SET stoId = CONCAT(stoId,'$sto_id')
                            WHERE ct_id = '$ct_id'
                        ";
                        sql_query($sql, true);
                    }
                    set_order_admin_log($od_id, "상품수량변경: {$it['it_name']}($io_value) {$ct['ct_qty']}개 -> {$qty}개");
                }
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

        $io_price = 0;
        $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];
        $io_value = sql_real_escape_string(strip_tags($io_value));
        $remote_addr = get_real_client_ip();
        $ct_discount = 0;
        $point = 0;

        if($it['it_delivery_min_cnt']) {
          //박스 개수 큰것 +작은것 - >ceil
          $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($qty / $it['it_delivery_cnt']) : 0;
          //큰박스 floor 한 가격을 담음
          $ct_delivery_bigbox = $it['it_delivery_cnt'] ? floor($qty / $it['it_delivery_cnt']) : 0;
          $ct_delivery_price = $it['it_delivery_cnt'] ? ($ct_delivery_bigbox * $it['it_delivery_price']) : 0;
          //나머지
          $remainder = $qty % $it['it_delivery_cnt'];
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
          $ct_delivery_cnt = $it['it_delivery_cnt'] ? ceil($qty / $it['it_delivery_cnt']) : 0;
          $ct_delivery_price = $ct_delivery_cnt * $it['it_delivery_price'];
        }
        $ct_delivery_company = 'ilogen';

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

        // 비유통상품 가격
        if($it['prodSupYn'] == 'N') {
            $it_price = 0;
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
          '{$it_price}',
          '$point',
          '0',
          '0',
          '$io_value',
          '$qty',
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
          '$ct_delivery_price',
          '$ct_delivery_company',
          '{$it['it_is_direct_delivery']}',
          '{$it['it_direct_delivery_partner']}',
          '{$it['it_direct_delivery_price']}',
          '$memo',
          $sqlOrdLendStrDtm,
          $sqlOrdLendEndDtm,
          '{$it['prodSupYn']}',
          $sql_ct_pen_id,
          '$ct_warehouse'
        )";
        sql_query($insert_sql, true);
        $ct_id = sql_insert_id();

        $prods = [];
        for($x = 0; $x < $qty; $x++) {
            $prods[] = [
                'prodId' => $it_id,
                'prodColor' => $prodColor,
                'prodSize' => $prodSize,
                'prodOption' => $prodOption,
                'prodBarNum' => '',
                'prodManuDate' => date("Y-m-d"),
                'stoMemo' => $memo,
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

        set_order_admin_log($od_id, "상품추가: {$it['it_name']}($io_value) {$qty}개");
    }
}

// 상품수 수정
$sql = " select COUNT(distinct it_id, ct_uid) as cart_count, count(*) as delivery_count
            from {$g5['g5_shop_cart_table']} where od_id = '$od_id'  ";
$row = sql_fetch($sql);




// ================================================================================================
// 23.02.02 : 서원 - 관리자 주문에 대한 배송비 정책 적용 부분 시작
// ================================================================================================

// 배송비 합계
$_sum_delivery_cost = 0;
// 상품 가격 합계
$_sum_it_price = 0;
// 배송정책 타입0 수량
$_sum_sc_type0 = 0;


// 23.02.02 : 서원 - POST받은 상품 정보에서 배송비 합산을 위한 계산 Loop
foreach ( $_POST['it_id'] as $key => $val ) {

    // 삭제된 상품의 경우 배송비 계산 하지 않음.
    if( $_POST['delete'][$key] == 1 ) continue; 
    if( !$val ) continue; // 상품 아이디 값이 없을 경우 continue


    // 배송비 정책 라이브러리 조회
    $_result = "";
    $_result = get_item_delivery_cost( $val, $_POST['qty'][$key], (int)preg_replace("/[^\d]/","", $_POST['it_price'][$key]) );


    // 23.02.02 : 서원 - 배송비 타입이 0,1,2,3에 속할 경우 전체 금액의 무료 배송을 결정 하기 위해 별도 계산.
    if( $_result['sc_type'] == 0 || $_result['sc_type'] == 1 || $_result['sc_type'] == 2 || $_result['sc_type'] == 3 ) {
    // 배송비 정책 타입이 '0'0일 경우 해당 상품 카운트 합산
    if( $_result['sc_type'] == 0 ) { $_sum_sc_type0 += 1; }
    // 쇼핑몰 기본 배송비정책에 의한 금액 산정을 위한 합산
    $_sum_it_price += ( $_POST['qty'][$key] * (int)preg_replace("/[^\d]/","", $_POST['it_price'][$key]) );
    } else {
    // 위 4가지 조건 배송비 정책 타입 이외 모두 배송비 합산.
    $_sum_delivery_cost += $_result['cost'];
    }

// Loop 종료
}


// 쇼핑몰 기본 배송비 정책 가져와서 Array 처리
$send_cost_limit =  explode(';', $default['de_send_cost_limit'] );
$send_cost_list = explode(';', $default['de_send_cost_list'] );

// 상품중 배송비 정책 타입이 '0'이상이고, 금액이 발생되었을 경우 기본 정책 정책 루틴 적용. 
if( ($_sum_sc_type0 > 0) && ($_sum_it_price > 0) ) {
  for( $i=0; $i < COUNT($send_cost_limit); $i++) {
    if($_sum_it_price < $send_cost_limit[$i]) { 
      $_sum_delivery_cost += $send_cost_list[$i]; break;
    }
  }
}

// ================================================================================================
// 23.02.02 : 서원 - 관리자 주문에 대한 배송비 정책 적용 부분 종료
// ================================================================================================



$od_send_cost = $_sum_delivery_cost;
sql_query(" UPDATE {$g5['g5_shop_order_table']} 
            SET od_cart_count = '{$row['cart_count']}', 
                od_delivery_total = '{$row['delivery_count']}',
                od_send_cost = '{$od_send_cost}' 
            where od_id = '$od_id'
        ");


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>주문서 수정</title>
<script type="text/javascript" src="<?php echo G5_JS_URL ?>/datetime_components/jquery.min.js"></script>
</head>
<script>  
$(function() {
  alert('완료되었습니다.');
  try{
    setTimeout(function() {
      $('#popup_order_add', parent.document).hide();
      $('#hd').css('z-index', 10);
      parent.location.reload();
    }, 500);
  }catch(e){ 
    window.close();
  }
});
</script>