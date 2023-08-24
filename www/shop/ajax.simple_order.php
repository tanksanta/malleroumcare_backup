<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
    json_response(400, '먼저 로그인하세요.');

set_cart_id(1);
set_session("ss_direct", 1);
$tmp_cart_id = get_session('ss_cart_direct');
$clean = $_POST['clean'];
if($clean == "ok"){//불필요한 "쇼핑" 데이터 제거
	sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_direct = 1 and ct_status = '쇼핑'", false);
	json_response(200, 'OK', $tmp_cart_id);
	exit;
}

$it_id_arr = $_POST['it_id'];
$io_id_arr = $_POST['io_id'];
$io_type_arr = $_POST['io_type'];
$ct_qty_arr = $_POST['ct_qty'];
$prodMemo_arr = $_POST['prodMemo'];

if(!($it_id_arr && $io_id_arr && $ct_qty_arr)) {
    json_response(400, '주문할 상품을 선택해주세요.');
}

sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_direct = 1 ", false);

$pen_type = $_POST['pen_type'];
$penId = clean_xss_tags($_POST['penId']);

function calc_pen_price($penTypeCd, $price) {
    switch($penTypeCd) {
        case '00':
            $rate = 15;
            break;
        case '01':
            $rate = 9;
            break;
        case '02':
        case '03':
            $rate = 6;
            break;
        case '04':
            return 0;
        default:
            $rate = 15;
            break;
    }

    $pen_price = (int) floor(
        $price * $rate / (100 * 10)
    ) * 10;

    return $pen_price;
}

if ($pen_type == 1) {
    $pen_id = clean_xss_tags($_POST['pen_id']);
    if(!$pen_id)
        json_response(400, '수급자를 선택해주세요.');
    
    $pen = get_recipient($pen_id);

    // 계약서 생성
    $dc_id = sql_fetch("SELECT REPLACE(UUID(),'-','') as uuid")["uuid"];

    $sql = "
        INSERT INTO
            eform_document
        SET
            dc_id = UNHEX('$dc_id'),
            dc_status = '11',
            od_id = '$tmp_cart_id',
            entId = '{$member["mb_entId"]}',
            entNm = '{$member["mb_entNm"]}',
            entCrn = '{$member["mb_giup_bnum"]}',
            entNum = '{$member["mb_ent_num"]}',
            entMail = '{$member["mb_email"]}',
            entCeoNm = '{$member["mb_giup_boss_name"]}',
            entConAcc01 = '{$member['entConAcc01']}',
            entConAcc02 = '{$member['entConAcc02']}',
            penId = '{$pen['penId']}',
            penNm = '{$pen['penNm']}',
            penConNum = '{$pen['penConNum']}',
            penBirth = '{$pen['penBirth']}',
            penLtmNum = '{$pen['penLtmNum']}',
            penRecGraCd = '{$pen['penRecGraCd']}', # 장기요양등급
            penRecGraNm = '{$pen['penRecGraNm']}',
            penRecTypeCd = '{$pen['penRecTypeCd']}', # 수령방법
            penRecTypeTxt = '{$pen['penRecTypeTxt']}',
            penTypeCd = '{$pen['penTypeCd']}', # 본인부담금율
            penTypeNm = '{$pen['penTypeNm']}',
            penExpiDtm = '{$pen['penExpiDtm']}', # 수급자 이용기간
            penJumin = '{$pen['penJumin']}',
            penZip = '{$pen['penZip']}',
            penAddr = '{$pen['penAddr']}',
            penAddrDtl = '{$pen['penAddrDtl']}',
            contract_sign_type = '0',
            contract_sign_name = '',
            contract_sign_relation = '0'
    ";
    $result = sql_query($sql);

    if(!$result)
        json_response(500, 'DB 서버 오류로 계약서를 저장하지 못했습니다.');

    $ip = $_SERVER['REMOTE_ADDR'];
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $timestamp = time();
    $datetime = date('Y-m-d H:i:s', $timestamp);

    // 문서 제목 생성
    $eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$dc_id')");
    $subject = $eform["entNm"]."_".str_replace('-', '', $eform["entCrn"])."_".$eform["penNm"].substr($eform["penLtmNum"], 0, 6)."_".date("Ymd")."_";
    $subject_count_postfix = sql_fetch("SELECT COUNT(`dc_id`) as cnt FROM `eform_document` WHERE `dc_subject` LIKE '{$subject}%'")["cnt"];
    $subject_count_postfix = str_pad($subject_count_postfix + 1, 3, '0', STR_PAD_LEFT); // zerofill
    $subject .= $subject_count_postfix;

    // 계약서 정보 업데이트
    sql_query("
        UPDATE `eform_document` SET
            `dc_subject` = '$subject',
            `dc_datetime` = '$datetime',
            `dc_ip` = '$ip'
        WHERE `dc_id` = UNHEX('$dc_id')
    ");

    // 계약서 로그 작성
    $log = '전자계약서를 생성했습니다.';

    sql_query("INSERT INTO `eform_document_log` SET
        `dc_id` = UNHEX('$dc_id'),
        `dl_log` = '$log',
        `dl_ip` = '$ip',
        `dl_browser` = '$browser',
        `dl_datetime` = '$datetime'
    ");
}

for ($i = 0; $i < count($it_id_arr); $i++) {
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $io_id = clean_xss_tags($io_id_arr[$i]);
    $io_type = clean_xss_tags($io_type_arr[$i]);
    $io_id = preg_replace(G5_OPTION_ID_FILTER, '', $io_id);
    $ct_qty = clean_xss_tags($ct_qty_arr[$i]);
    $prodMemo = clean_xss_tags($prodMemo_arr[$i]);

    if(!$it_id || $ct_qty < 1) continue;

    $it = sql_fetch(" select i.*, (select ca_name from g5_shop_category where ca_id = left(i.ca_id, 4) ) as ca_name from {$g5['g5_shop_item_table']} i where it_id = '{$it_id}' ");
    if(!$it['it_id']) continue;

    $io_value = '';
    if ($io_id) {
      if ($io_type == '0') {
        $it_option_subjects = explode(',', $it['it_option_subject']);
      } else {
        $it_option_subjects = explode(',', $it['it_supply_subject']);
      }
      $io_ids = explode(chr(30), $io_id);
      for($g = 0; $g< count($io_ids); $g++) {
        if ($g > 0) {
          $io_value .= ' / ';
        }
        $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
      }
    }

    if ($it['it_sc_type'] == 1)
        $ct_send_cost = 2; // 무료
    else if ($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
        $ct_send_cost = 1; // 착불
    else
        $ct_send_cost = 0;

    // 옵션정보를 얻어서 배열에 저장
    $opt_list = array();
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
    $result = sql_query($sql);
    $lst_count = 0;
    for ($k = 0; $row = sql_fetch_array($result); $k++) {
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
    if ($lst_count && $io_id == '')
        continue;

    // 구매할 수 없는 옵션은 건너뜀
    if ($io_id && !$opt_list[$io_type][$io_id]['use'])
        continue;
    
    $io_price = samhwa_opt_price($opt_list[$io_type][$io_id], THEMA_KEY);
    $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];

    // 구매가격이 음수인지 체크
    if ($io_type) { // 추가옵션
      if ((int)$io_price < 0)
        json_response(400, '구매금액이 음수인 상품은 구매할 수 없습니다.');
    } else {
      if ((int)$it['it_price'] + (int)$io_price < 0)
        json_response(400, '구매금액이 음수인 상품은 구매할 수 없습니다.');
    }

    $io_value = sql_real_escape_string(strip_tags($io_value));
    $remote_addr = get_real_client_ip();

    if ($it['it_delivery_min_cnt']) {
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

    // 사업소별 판매가
    $entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$it['it_id']}' and mb_id = '{$member['mb_id']}' ");
    $it['entprice'] = $entprice['it_price'];

    if($it['entprice'] > 0)
        $it_price = $it['entprice'];

    // 비유통상품 가격
    if($it['prodSupYn'] == 'N') {
        $it_price = 0;
    }

    // 묶음할인
    $ct_discount = 0;
    $ct_sale_qty = 0;

    for ($tmp_i = 0; $tmp_i < count($it_id_arr); $tmp_i++) {
        if ($it_id_arr[$tmp_i] !== $it_id) continue;

        $ct_sale_qty += $ct_qty_arr[$tmp_i];
    }

    $itSaleCntList = [$it["it_sale_cnt"], $it["it_sale_cnt_02"], $it["it_sale_cnt_03"], $it["it_sale_cnt_04"], $it["it_sale_cnt_05"]];
    $itSalePriceList = [$it["it_sale_percent"], $it["it_sale_percent_02"], $it["it_sale_percent_03"], $it["it_sale_percent_04"], $it["it_sale_percent_05"]];
    //우수사업소고 우수사업소 할인가가 있으면 적용
    if($member['mb_level']=="4"&&$it['it_sale_percent_great']){
        $itSalePriceList = [$it["it_sale_percent_great"], $it["it_sale_percent_great_02"], $it["it_sale_percent_great_03"], $it["it_sale_percent_great_04"], $it["it_sale_percent_great_05"]];
    }
    $itSaleCnt = 0;

    if (!$io_type && !$it['entprice']) {
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
    if ($ct_discount < 0) $ct_discount = 0;
	// 출하창고
    $ct_warehouse = '검단창고';
    if($it['it_default_warehouse']) {
      $ct_warehouse = $it['it_default_warehouse'];
    }
	if($it['it_direct_delivery_partner'] != ""){//직배송 파트너가 있을 경우 파트너 계정에 설정되어 있는 출하창고 등록
		$partner = get_member($it['it_direct_delivery_partner']);
		$ct_warehouse = ($partner["mb_partner_default_warehouse"] != "" )? $partner["mb_partner_default_warehouse"] : $ct_warehouse;
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
        prodSupYn,
        ct_warehouse
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
        '{$it['prodSupYn']}',
        '{$ct_warehouse}'
    )
    ";

    $result = sql_query($insert_sql);
    if(!$result)
        json_response(500, 'DB 오류가 발생하여 주문을 완료하지 못했습니다.');
    
    if($pen_type == 1) {
        $gubun = $cate_gubun_table[substr($it['ca_id'], 0, 2)];
        if($gubun == '00') {
            $it_price = $it['it_cust_price']; // 급여가
            $it_date = date('Y-m-d');
        } else if($gubun == '01') {
            $it_price = $it['it_rental_price']; // 대여가
            $it_date = date('Y-m-d') . '-' . date('Y-m-d');;
        } else {
            continue;
        }
        for($x = 0; $x < $ct_qty; $x++) {
            $it_price_pen = calc_pen_price($pen['penTypeCd'], $it_price);
            $it_price_ent = $it_price - $it_price_pen;
    
            $sql = "
                INSERT INTO eform_document_item SET
                    dc_id = UNHEX('$dc_id'),
                    gubun = '$gubun',
                    ca_name = '{$it['ca_name']}',
                    it_name = '{$it['it_name']}',
                    it_code = '{$it['ProdPayCode']}',
                    it_barcode = '',
                    it_qty = '1',
                    it_date = '$it_date',
                    it_price = '$it_price',
                    it_price_pen = '$it_price_pen',
                    it_price_ent = '$it_price_ent'
            ";
            sql_query($sql);
        }
    }
}

// 새로운 주문번호 생성
$od_id = get_uniqid();
set_session('ss_order_id', $od_id);

json_response(200, 'OK', $tmp_cart_id);
