<?php
include_once('./_common.php');

$od_id = get_search_string($_POST['od_id']);
$od = sql_fetch("
  SELECT
    o.*
  FROM
    {$g5['g5_shop_order_table']} o
  WHERE
    od_id = '{$od_id}'
");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

$mb = get_member($od['mb_id']);

$manager_mb_id = get_session('ss_manager_mb_id');
$manager_log_text = '';
if($manager_mb_id) {
  $manager = get_member($manager_mb_id);
  $manager_log_text = "({$manager['mb_name']}) ";
}

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

/*
$cart_result = sql_query("
  SELECT
    c.*,
    i.ca_id,
    i.it_img1,
    i.it_cust_price
  FROM
    {$g5['g5_shop_cart_table']} c
  LEFT JOIN
    {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
  WHERE
    od_id = '{$od_id}' and
    ct_direct_delivery_partner = '{$member['mb_id']}' and
    ct_status IN('출고준비', '배송', '완료', '취소', '주문무효')
  ORDER BY
    ct_id ASC
");

$carts = [];
while($row = sql_fetch_array($cart_result)) {
  $carts[] = $row;
}

if(!$carts)
  alert('존재하지 않는 주문입니다.');
*/

$ct_id_arr = $_POST['ct_id'];
$deleted_arr = $_POST['deleted'];
$it_id_arr = $_POST['it_id'];
$io_id_arr = $_POST['io_id'];
$ct_qty_arr = $_POST['ct_qty'];
$prodMemo_arr = $_POST['prodMemo'];

$delete_requests = [];

for($i = 0; $i < count($it_id_arr); $i++) {
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $io_id = clean_xss_tags($io_id_arr[$i]);
    $ct_qty = clean_xss_tags($ct_qty_arr[$i]);
    $prodMemo = clean_xss_tags($prodMemo_arr[$i]);

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

    $ct_id = clean_xss_tags($ct_id_arr[$i]);
    if($ct_id) {
        $ct = get_partner_cart_item($ct_id);
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
            set_order_admin_log($od_id, "{$manager_log_text}상품삭제: {$ct['it_name']}({$ct['ct_option']})");
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
                    io_price =  '$io_price',
                    ct_qty = '$ct_qty',
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

                set_order_admin_log($od_id, "{$manager_log_text}상품변경: {$ct['it_name']}({$ct['ct_option']}) -> {$it['it_name']}({$io_value})");
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
                    SET stoId = '$sto_id'
                    WHERE ct_id = '$ct_id'
                ";
                sql_query($sql, true);
                set_order_admin_log($od_id, "{$manager_log_text}상품수량변경: {$it['it_name']}($io_value) {$ct['ct_qty']}개 -> {$ct_qty}개");
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
                        SET stoId = CONCAT(stoId,'$sto_id')
                        WHERE ct_id = '$ct_id'
                    ";
                    sql_query($sql, true);
                }
                set_order_admin_log($od_id, "{$manager_log_text}상품수량변경: {$it['it_name']}($io_value) {$ct['ct_qty']}개 -> {$ct_qty}개");
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
        $ct_discount = 0;
        $point = 0;

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
		
		if($it['it_direct_delivery_partner'] != ""){//직배송 파트너가 있을 경우 파트너 계정에 설정되어 있는 출하창고 등록
			$partner = get_member($it['it_direct_delivery_partner']);
			$ct_warehouse = ($partner["mb_partner_default_warehouse"] != "" )? $partner["mb_partner_default_warehouse"] : $ct_warehouse;
		}
		
		$ct_warehouse = ($member["mb_partner_default_warehouse"] != "" )? $member["mb_partner_default_warehouse"] : $ct_warehouse;//파트너의 등록 된 출하 창고가 있다면 등록 된 출하 창고로 지정


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
          '$ct_delivery_price',
          '$ct_delivery_company',
          '2',
          '{$member['mb_id']}',
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
                SET ct_status = '출고준비', stoId = '$sto_id'
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
                SET ct_status = '출고준비', stoId = '$sto_id'
                WHERE ct_id = '$ct_id'
            ";
            sql_query($sql, true);
        }

        set_order_admin_log($od_id, "{$manager_log_text}상품추가: {$it['it_name']}($io_value) {$ct_qty}개");
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

set_partner_order_edit($od_id, 1);
goto_url('partner_orderinquiry_view.php?od_id=' . $od_id);
