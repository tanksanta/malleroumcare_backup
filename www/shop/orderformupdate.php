<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

$od_receipt_bank = '';

//이니시스 lpay 요청으로 왔다면 $default['de_pg_service'] 값을 이니시스로 변경합니다.
if( $od_settle_case == 'lpay' ){
    $default['de_pg_service'] = 'inicis';
}

//if(($od_settle_case != '무통장' && $od_settle_case != '포인트' && $od_settle_case != 'KAKAOPAY') && $default['de_pg_service'] == 'lg' && !$_POST['LGD_PAYKEY'])
//	alert('결제등록 요청 후 주문해 주십시오.');

// 장바구니가 비어있는가?
if (get_session("ss_direct"))
    $tmp_cart_id = get_session('ss_cart_direct');
else
    $tmp_cart_id = get_session('ss_cart_id');

if (get_cart_count($tmp_cart_id) == 0)// 장바구니에 담기
    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SHOP_URL.'/cart.php');

$it_ids = array();
$productList = [];
$postProdBarNumCnt = 0;
$deliveryTotalCnt = 0;

$od_prodBarNum_insert = 0;
$od_prodBarNum_total = 0;

$od_delivery_insert = 0;
$od_delivery_total = 0;

$error = "";
// 장바구니 상품 재고 검사
$sql = " select MT.it_id,
                MT.ct_qty,
                MT.it_name,
                MT.io_id,
                MT.io_type,
                MT.ct_option,
                MT.ct_qty,
                MT.ct_id,
				( SELECT it_time FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_time,
				( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn,
				( SELECT ProdPayCode FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodPayCode,
				( SELECT it_delivery_cnt FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_cnt,
				( SELECT it_delivery_price FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_price
           from {$g5['g5_shop_cart_table']} MT
          where od_id = '$tmp_cart_id'
            and ct_select = '1' ";

$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{

	# 상품목록
	for($ii = 0; $ii < $row["ct_qty"]; $ii++){
		if($_POST["penId"]){
			$thisProductData = [];
			$thisProductData["prodId"] = $row["it_id"];
			$thisProductData["prodColor"] = explode(chr(30), $row["io_id"])[0];
			$thisProductData["prodSize"] = explode(chr(30), $row["io_id"])[1];
			$thisProductData["prodBarNum"] = $_POST["prodBarNum_{$postProdBarNumCnt}"];
			$thisProductData["penStaSeq"] = "".(count($productList) + 1)."";
			$thisProductData["prodPayCode"] = $row["prodPayCode"];
			$thisProductData["itemNm"] = explode(chr(30), $row["io_id"])[0]." / ".explode(chr(30), $row["io_id"])[1];
			$thisProductData["ordLendStrDtm"] = date("Y-m-d", strtotime($_POST["ordLendStartDtm_{$row["ct_id"]}"]));
			$thisProductData["ordLendEndDtm"] = date("Y-m-d", strtotime($_POST["ordLendEndDtm_{$row["ct_id"]}"]));

			array_push($productList, $thisProductData);
		} else {
			$thisProductData = [];
			$thisProductData["prodId"] = $row["it_id"];
			$thisProductData["prodColor"] = explode(chr(30), $row["io_id"])[0];
			$thisProductData["prodSize"] = explode(chr(30), $row["io_id"])[1];
			$thisProductData["prodBarNum"] = $_POST["prodBarNum_{$postProdBarNumCnt}"];
			$thisProductData["prodManuDate"] = date("Y-m-d");
			$thisProductData["stoMemo"] = $_POST["od_memo"];

			array_push($productList, $thisProductData);
		}

		$od_prodBarNum_total++;
		if($_POST["prodBarNum_{$postProdBarNumCnt}"]){
			$od_prodBarNum_insert++;
		}

		$postProdBarNumCnt++;
	}

	# 요청사항 저장
	sql_query("
		UPDATE {$g5["g5_shop_cart_table"]} SET
			prodMemo = '{$_POST["prodMemo_{$row["ct_id"]}"]}'
		WHERE ct_id = '{$row["ct_id"]}'
	");

	# 대여기간저장
	if($_POST["penId"]){
		sql_query("
			UPDATE {$g5["g5_shop_cart_table"]} SET
				ordLendStrDtm = '{$_POST["ordLendStartDtm_{$row["ct_id"]}"]}',
				ordLendEndDtm = '{$_POST["ordLendEndDtm_{$row["ct_id"]}"]}'
			WHERE ct_id = '{$row["ct_id"]}'
		");
	}

	# 비유통상품 금액저장
	if($row["prodSupYn"] == "N"){
//		sql_query("
//			UPDATE {$g5["g5_shop_cart_table"]} SET
//				ct_price = 0
//			WHERE ct_id = '{$row["ct_id"]}'
//		");
	}

	# 재고사용수량 저장
	if($_POST["penId"]){
		sql_query("
			UPDATE {$g5["g5_shop_cart_table"]} SET
				ct_stock_qty = '{$_POST["it_option_stock_cnt_{$row["ct_id"]}"]}'
			WHERE ct_id = '{$row["ct_id"]}'
		");

		if($row["prodSupYn"] == "Y"){
			$deliveryTotalCnt += $row["ct_qty"] - $_POST["it_option_stock_cnt_{$row["ct_id"]}"];
		}
	}

	# 배송가능 설정
	if($row["prodSupYn"] == "Y"){
		if(($row["ct_qty"] - $_POST["it_option_stock_cnt_{$row["ct_id"]}"]) > 0){
			$od_delivery_total++;

			$tmpQty = $row["ct_qty"] - $_POST["it_option_stock_cnt_{$row["ct_id"]}"];
            if($row[$i]["it_delivery_cnt"]){
			    $tmpCnt = floor($tmpQty / $row["it_delivery_cnt"]);
                if($tmpCnt < ($tmpQty / $row[$i]["it_delivery_cnt"])){
                    $tmpCnt += 1;
                }
            }

			$tmpPrice = $tmpCnt * $row["it_delivery_price"];

			sql_query("
				UPDATE {$g5["g5_shop_cart_table"]} SET
					ct_delivery_yn = 'Y',
					ct_delivery_cnt = '{$tmpCnt}',
					ct_delivery_price = '{$tmpPrice}'
				WHERE ct_id = '{$row["ct_id"]}'
			");
		}
	}

    // 상품에 대한 현재고수량
    if($row["io_id"]) {
        $it_stock_qty = (int)get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
    } else {
        $it_stock_qty = (int)get_it_stock_qty($row['it_id']);
    }
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row['ct_qty'] > $it_stock_qty)
        $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";

    if (!in_array($row['it_id'], $it_ids)) {
        $it_ids[] = $row['it_id'];
    }
}

if($i == 0)
    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SHOP_URL.'/cart.php');

if ($error != "")
{
    $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
    alert($error, $page_return_url);
}

$i_price     = (int)$_POST['od_price'] - (int)$_POST['od_discount'];
$i_send_cost  = (int)$_POST['od_send_cost'];
$i_send_cost2  = (int)$_POST['od_send_cost2'];
$i_send_coupon  = abs((int)$_POST['od_send_coupon']);
$i_temp_point = (int)$_POST['od_temp_point'];


// 주문금액이 상이함
$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as od_price,
              COUNT(distinct it_id) as cart_count,
			  SUM(ct_discount) as od_discount,
			  ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn
            from {$g5['g5_shop_cart_table']} MT where od_id = '$tmp_cart_id' and ct_select = '1' ";
$row = sql_fetch($sql);
// $tot_ct_price = ($row["prodSupYn"] == "Y") ? $row['od_price'] : 0;
$tot_ct_price = $row['od_price'];

$tot_ct_discount = ($row["od_discount"]) ? $row["od_discount"] : 0;
$cart_count = $row['cart_count'];
$tot_od_price = $tot_ct_price;

//20210329 토탈 가격이 일정금액 이상 넘으면 배송비 무료
$sql_d = "SELECT `de_send_conditional` FROM `g5_shop_default`";
$result_d = sql_fetch($sql_d);
if($tot_ct_price >=$result_d['de_send_conditional']){

}

// 쿠폰금액계산
$tot_cp_price = 0;
if($is_member) {
    // 상품쿠폰
    $tot_it_cp_price = $tot_od_cp_price = 0;
    $it_cp_cnt = count($_POST['cp_id']);
    $arr_it_cp_prc = array();
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = $_POST['cp_id'][$i];
        $it_id = $_POST['it_id'][$i];
        $sql = " select cp_id, cp_method, cp_target, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '$cid'
                      and mb_id IN ( '{$member['mb_id']}', '전체회원' )
                      and cp_start <= '".G5_TIME_YMD."'
                      and cp_end >= '".G5_TIME_YMD."'
                      and cp_method IN ( 0, 1 ) ";
        $cp = sql_fetch($sql);
        if(!$cp['cp_id'])
            continue;

        // 사용한 쿠폰인지
        if(is_used_coupon($member['mb_id'], $cp['cp_id']))
            continue;

        // 분류할인인지
        if($cp['cp_method']) {
            $sql2 = " select it_id, ca_id, ca_id2, ca_id3
                        from {$g5['g5_shop_item_table']}
                        where it_id = '$it_id' ";
            $row2 = sql_fetch($sql2);

            if(!$row2['it_id'])
                continue;

            if($row2['ca_id'] != $cp['cp_target'] && $row2['ca_id2'] != $cp['cp_target'] && $row2['ca_id3'] != $cp['cp_target'])
                continue;
        } else {
            if($cp['cp_target'] != $it_id)
                continue;
        }

        // 상품금액
        $sql = " select SUM( IF(io_type = '1', io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_price
                    from {$g5['g5_shop_cart_table']}
                    where od_id = '$tmp_cart_id'
                      and it_id = '$it_id'
                      and ct_select = '1' ";
        $ct = sql_fetch($sql);
        $item_price = $ct['sum_price'];

        if($cp['cp_minimum'] > $item_price)
            continue;

        $dc = 0;
        if($cp['cp_type']) {
            $dc = floor(($item_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
        } else {
            $dc = $cp['cp_price'];
        }

        if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
            $dc = $cp['cp_maximum'];

        if($item_price < $dc)
            continue;

        $tot_it_cp_price += $dc;
        $arr_it_cp_prc[$it_id] = $dc;
    }

    $tot_od_price -= $tot_it_cp_price;

    // 주문쿠폰
    if($_POST['od_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$_POST['od_cp_id']}'
                      and mb_id IN ( '{$member['mb_id']}', '전체회원' )
                      and cp_start <= '".G5_TIME_YMD."'
                      and cp_end >= '".G5_TIME_YMD."'
                      and cp_method = '2' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($member['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($tot_od_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            if($tot_od_price < $dc)
                die('Order coupon error.');

            $tot_od_cp_price = $dc;
            $tot_od_price -= $tot_od_cp_price;
        }
    }

    $tot_cp_price = $tot_it_cp_price + $tot_od_cp_price;
}

if ((int)($row['od_price'] - $row['od_discount'] - $tot_cp_price) !== $i_price) {
//    die("Error.");
}

// 배송비가 상이함
$send_cost = get_sendcost($tmp_cart_id, 1, 1);

if ( $od_delivery_type != 'delivery1' ) {
    $send_cost = 0;
}

$tot_sc_cp_price = 0;
if($is_member && $send_cost > 0) {
    // 배송쿠폰
    if($_POST['sc_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$_POST['sc_cp_id']}'
                      and mb_id IN ( '{$member['mb_id']}', '전체회원' )
                      and cp_start <= '".G5_TIME_YMD."'
                      and cp_end >= '".G5_TIME_YMD."'
                      and cp_method = '3' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($member['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($send_cost * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            if($dc > $send_cost)
                $dc = $send_cost;

            $tot_sc_cp_price = $dc;
        }
    }
}

if ((int)($send_cost - $tot_sc_cp_price) !== (int)($i_send_cost - $i_send_coupon)) {
//    die("Error..");
}

// 추가배송비가 상이함
$od_b_zip   = preg_replace('/[^0-9]/', '', $od_b_zip);
$od_b_zip1  = substr($od_b_zip, 0, 3);
$od_b_zip2  = substr($od_b_zip, 3);
$zipcode = $od_b_zip;
$sql = " select sc_id, sc_price from {$g5['g5_shop_sendcost_table']} where sc_zip1 <= '$zipcode' and sc_zip2 >= '$zipcode' ";
$tmp = sql_fetch($sql);
if(!$tmp['sc_id'])
    $send_cost2 = 0;
else {

    $total_item_sc_price = 0;

    $it_sc_add_sendcost = 'it_sc_add_sendcost';
    if ($member['mb_type'] == 'partner') {
        $it_sc_add_sendcost = 'it_sc_add_sendcost_partner';
    }

    if($it_ids) {
        foreach($it_ids as $it_id) {
            $sql = "SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = {$it_id}";
            $result = sql_fetch($sql);

            if ($result[$it_sc_add_sendcost] > -1) { // 추가배송비가 설정되어 있는 경우
                $total_item_sc_price += $result[$it_sc_add_sendcost];
            } else { // 없는경우 기본 관리자에 있는걸 가져온다.
                $total_item_sc_price += $tmp['sc_price'];
            }

        }

    }

    if ($total_item_sc_price) {
        $send_cost2 = $total_item_sc_price;
    }else{
        $send_cost2 = (int)$tmp['sc_price'];
    }
}


if ( $od_delivery_type != 'delivery1' ) {
    $send_cost2 = 0;
}

if($send_cost2 !== $i_send_cost2)
    die("관리자에게 문의하세요. Error...1: " . $send_cost2 . '/' . $i_send_cost2);

// 결제포인트가 상이함
// 회원이면서 포인트사용이면
$temp_point = 0;
if ($is_member && $config['cf_use_point'])
{
    if($member['mb_point'] >= $default['de_settle_min_point']) {
        $temp_point = (int)$default['de_settle_max_point'];

        if($temp_point > (int)$tot_od_price)
            $temp_point = (int)$tot_od_price;

        if($temp_point > (int)$member['mb_point'])
            $temp_point = (int)$member['mb_point'];

        $point_unit = (int)$default['de_settle_point_unit'];
        $temp_point = (int)((int)($temp_point / $point_unit) * $point_unit);
    }
}

if ($od_settle_case == "포인트")
{
	$temp_order_point = $i_price + $i_send_cost + $i_send_cost2 - $i_send_coupon;
	if($temp_order_point != $i_temp_point)
        alert('결제하실 금액과 포인트가 일치하지 않습니다.', $page_return_url);

} else {
	if (($i_temp_point > (int)$temp_point || $i_temp_point < 0) && $config['cf_use_point'])
		die("Error....");
}

if ($od_temp_point)
{
    if ($member['mb_point'] < $od_temp_point)
        alert('회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.', $page_return_url);
}

$i_price = $i_price + $i_send_cost + $i_send_cost2 - $i_temp_point - $i_send_coupon;
$order_price = $tot_od_price + $send_cost + $send_cost2 - $tot_sc_cp_price - $od_temp_point;

$od_status = '주문';
$od_tno    = '';
if ($od_settle_case == "무통장" || $od_settle_case == "포인트")
{
    $od_receipt_point   = $i_temp_point;
    $od_receipt_price   = 0;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0) {
        $od_status      = '입금';
        $od_receipt_time = G5_TIME_YMDHIS;
    }
}
else if ($od_settle_case == "계좌이체")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_SHOP_PATH.'/inicis/inistdpay_result.php';
            break;
		default:
            include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
            $bank_name  = iconv("cp949", "utf-8", $bank_name);
            break;
    }

    $od_tno             = $tno;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $od_settle_case;
    $od_deposit_name    = $od_name;
    $od_bank_account    = $bank_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "가상계좌")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_SHOP_PATH.'/inicis/inistdpay_result.php';
			$od_app_no = $app_no;
            break;
		default:
            include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
            $bankname   = iconv("cp949", "utf-8", $bankname);
            $depositor  = iconv("cp949", "utf-8", $depositor);
            break;
    }

    $od_receipt_point   = $i_temp_point;
    $od_tno             = $tno;
	$od_receipt_price   = 0;
    $od_bank_account    = $bankname.' '.$account;
    $od_deposit_name    = $depositor;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
}
else if ($od_settle_case == "휴대폰")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_SHOP_PATH.'/inicis/inistdpay_result.php';
            break;
		default:
            include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
            break;
    }

    $od_tno             = $tno;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $commid . ($commid ? ' ' : '').$mobile_no;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "신용카드")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_SHOP_PATH.'/inicis/inistdpay_result.php';
            break;
		default:
            include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
            $card_name  = iconv("cp949", "utf-8", $card_name);
            break;
    }

    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $card_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "간편결제" || ($od_settle_case == "lpay" && $default['de_pg_service'] === 'inicis') )
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_SHOP_PATH.'/inicis/inistdpay_result.php';
            break;
		default:
            include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
            $card_name  = iconv("cp949", "utf-8", $card_name);
            break;
    }

    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $card_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "KAKAOPAY")
{
    include G5_SHOP_PATH.'/kakaopay/kakaopay_result.php';

    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $card_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "월 마감 정산")
{
	$od_status = "준비";
	$od_misu = 0;
}
else
{
    die("od_settle_case Error!!!");
}

$od_pg = $default['de_pg_service'];
if($od_settle_case == 'KAKAOPAY')
    $od_pg = 'KAKAOPAY';

if($od_settle_case == '신용카드')
    $od_receipt_bank    = '99609';


// 주문금액과 결제금액이 일치하는지 체크
if($tno) {
    if((int)$order_price !== (int)$pg_price) {
        $cancel_msg = '결제금액 불일치';
        switch($od_pg) {
            case 'lg':
                include G5_SHOP_PATH.'/lg/xpay_cancel.php';
                break;
            case 'inicis':
                include G5_SHOP_PATH.'/inicis/inipay_cancel.php';
                break;
            case 'KAKAOPAY':
                $_REQUEST['TID']               = $tno;
                $_REQUEST['Amt']               = $amount;
                $_REQUEST['CancelMsg']         = $cancel_msg;
                $_REQUEST['PartialCancelCode'] = 0;
                include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
                break;
			default:
                include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
                break;
        }

        die("Receipt Amount Error");
    }
}
if ( $od_status == '입금' ) {
    $od_pay_state = '1';
}else{
    $od_pay_state = '0';
}

if ($is_member)
    $od_pwd = $member['mb_password'];
else
    $od_pwd = get_encrypt_string($_POST['od_pwd']);

// 주문번호를 얻는다.
$od_id = get_session('ss_order_id');
$so_nb = get_uniqid_so_nb();

$od_escrow = 0;
if($escw_yn == 'Y')
    $od_escrow = 1;

// 복합과세 금액
$od_tax_mny = round($i_price / 1.1);
$od_vat_mny = $i_price - $od_tax_mny;
$od_free_mny = 0;
if($default['de_tax_flag_use']) {
    $od_tax_mny = (int)$_POST['comm_tax_mny'];
    $od_vat_mny = (int)$_POST['comm_vat_mny'];
    $od_free_mny = (int)$_POST['comm_free_mny'];
}

$od_email         = get_email_address($od_email);
$od_name          = clean_xss_tags($od_name);
$od_tel           = clean_xss_tags($od_tel);
$od_hp            = clean_xss_tags($od_hp);
$od_zip           = preg_replace('/[^0-9]/', '', $od_zip);
$od_zip1          = substr($od_zip, 0, 3);
$od_zip2          = substr($od_zip, 3);
$od_addr1         = clean_xss_tags($od_addr1);
$od_addr2         = clean_xss_tags($od_addr2);
$od_addr3         = clean_xss_tags($od_addr3);
$od_addr_jibeon   = preg_match("/^(N|R)$/", $od_addr_jibeon) ? $od_addr_jibeon : '';

//수급자정보
$od_penId			= clean_xss_tags($penId);
$od_penNm			= clean_xss_tags($penNm);
$od_penTypeNm		= clean_xss_tags($penTypeNm);
$od_penExpiDtm		= clean_xss_tags($penExpiDtm);
$od_penAppEdDtm		= clean_xss_tags($penAppEdDtm);
$od_penConPnum		= clean_xss_tags($penConPnum);
$od_penConNum		= clean_xss_tags($penConNum);
$od_penzip			= preg_replace('/[^0-9]/', '', $penzip);
$od_penzip1			= substr($penzip, 0, 3);
$od_penzip2			= substr($penzip, 3);
$od_penAddr			= clean_xss_tags($penAddr);

$od_b_name        = clean_xss_tags($od_b_name);
$od_b_hp          = clean_xss_tags($od_b_hp);
$od_b_tel         = clean_xss_tags($od_b_tel) ? clean_xss_tags($od_b_tel) : $od_b_hp;
$od_b_addr1       = clean_xss_tags($od_b_addr1);
$od_b_addr2       = clean_xss_tags($od_b_addr2);
$od_b_addr3       = clean_xss_tags($od_b_addr3);
$od_b_addr_jibeon = preg_match("/^(N|R)$/", $od_b_addr_jibeon) ? $od_b_addr_jibeon : '';
$od_memo          = clean_xss_tags($od_memo);
$od_delivery_type = clean_xss_tags($od_delivery_type);
$od_deposit_name  = clean_xss_tags($od_deposit_name);
$od_tax_flag      = $default['de_tax_flag_use'];
$mb_giup_manager  = (int)$mb_giup_manager;

$od_sales_manager = '1205';
if ($member['mb_type'] == 'default') {
    $od_sales_manager = '1205';
}
if (is_dealer()) {
    $od_sales_manager = '1202';
}
if ($member['mb_type'] == 'partner') {
    $od_sales_manager = '1201';
}
if ($od['od_settle_case'] == '네이버페이') {
    $od_sales_manager = '1204';
}

// 주문서에 입력
$od_receipt_time = ($od_receipt_time) ? $od_receipt_time : "0000-00-00 00:00:00";
$od_hope_date = ($od_hope_date) ? $od_hope_date : "0000-00-00 00:00:00";

# 배송비설정
if(!$od_delivery_total){
	$od_send_cost = 0;
	$od_send_cost2 = 0;
}

//보유재고 discount = 0 
if($_POST["od_stock_insert_yn"]){
    $tot_ct_discount=0;
}


$sql = " insert {$g5['g5_shop_order_table']}
            set od_id             = '$od_id',
                mb_id             = '{$member['mb_id']}',
                od_pwd            = '$od_pwd',
                od_name           = '$od_name',
                od_email          = '$od_email',
                od_tel            = '$od_tel',
                od_hp             = '$od_hp',
                od_zip1           = '$od_zip1',
                od_zip2           = '$od_zip2',
                od_addr1          = '$od_addr1',
                od_addr2          = '$od_addr2',
                od_addr3          = '$od_addr3',
                od_addr_jibeon    = '$od_addr_jibeon',

                od_penId			= '$od_penId',
                od_penNm			= '$od_penNm',
                od_penTypeNm		= '$od_penTypeNm',
                od_penExpiDtm		= '$od_penExpiDtm',
                od_penAppEdDtm		= '$od_penAppEdDtm',
                od_penConPnum		= '$od_penConPnum',
                od_penConNum		= '$od_penConNum',
                od_penzip1          = '$od_penzip1',
                od_penzip2          = '$od_penzip2',
                od_penAddr			= '$od_penAddr',

                od_b_name         = '$od_b_name',
                od_b_tel          = '$od_b_tel',
                od_b_hp           = '$od_b_hp',
                od_b_zip1         = '$od_b_zip1',
                od_b_zip2         = '$od_b_zip2',
                od_b_addr1        = '$od_b_addr1',
                od_b_addr2        = '$od_b_addr2',
                od_b_addr3        = '$od_b_addr3',
                od_b_addr_jibeon  = '$od_b_addr_jibeon',
                od_deposit_name   = '$od_deposit_name',
                od_memo           = '$od_memo',
                od_cart_count     = '$cart_count',
                od_cart_price     = '$tot_ct_price',
				od_cart_discount = '$tot_ct_discount',
                od_cart_coupon    = '$tot_it_cp_price',
                od_send_cost      = '$od_send_cost',
                od_send_coupon    = '$tot_sc_cp_price',
                od_send_cost2     = '$od_send_cost2',
                od_coupon         = '$tot_od_cp_price',
                od_receipt_price  = '$od_receipt_price',
                od_receipt_point  = '$od_receipt_point',
                od_bank_account   = '$od_bank_account',
                od_receipt_time   = '$od_receipt_time',
                od_misu           = '$od_misu',
                od_pg             = '$od_pg',
                od_tno            = '$od_tno',
                od_app_no         = '$od_app_no',
                od_escrow         = '$od_escrow',
                od_tax_flag       = '$od_tax_flag',
                od_tax_mny        = '$od_tax_mny',
                od_vat_mny        = '$od_vat_mny',
                od_free_mny       = '$od_free_mny',
                od_status         = '$od_status',
                od_shop_memo      = '',
                od_hope_date      = '$od_hope_date',
                od_time           = '".G5_TIME_YMDHIS."',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '$od_settle_case',
                od_test           = '{$default['de_card_test']}',
                od_giup_manager   = '$mb_giup_manager',
                od_pay_state      = '$od_pay_state',
                od_delivery_type  = '$od_delivery_type',
                so_nb             = '{$so_nb}',
                od_receipt_bank_no = '{$od_tno}',
                od_sales_manager  = '{$od_sales_manager}',
                od_receipt_bank   = '{$od_receipt_bank}',
                staOrdCd   = '00',

				od_mod_history = '',
				od_next_status = '',
				od_cash = 0,
				od_cash_no = '',
				od_cash_info = '',
				od_pay_memo = '',
				od_naver_PaymentMeans = '',
				od_naver_PaymentCoreType = '',
				stoId = '',

				od_prodBarNum_insert = '{$od_prodBarNum_insert}',
				od_prodBarNum_total = '{$od_prodBarNum_total}',

				od_delivery_insert = '{$od_delivery_insert}',
				od_delivery_total = '{$od_delivery_total}'
                ";
$result = sql_query($sql, false);

// 주문정보 입력 오류시 결제 취소
if(!$result) {
    if($tno) {
        $cancel_msg = '주문정보 입력 오류';
        switch($od_pg) {
            case 'lg':
                include G5_SHOP_PATH.'/lg/xpay_cancel.php';
                break;
            case 'inicis':
                include G5_SHOP_PATH.'/inicis/inipay_cancel.php';
                break;
            case 'KAKAOPAY':
                $_REQUEST['TID']               = $tno;
                $_REQUEST['Amt']               = $amount;
                $_REQUEST['CancelMsg']         = $cancel_msg;
                $_REQUEST['PartialCancelCode'] = 0;
                include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
                break;
			default:
                include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
                break;
        }
    }

    // 관리자에게 오류 알림 메일발송
    $error = 'order';
    include G5_SHOP_PATH.'/ordererrormail.php';

    die('<p>고객님의 주문 정보를 처리하는 중 오류가 발생해서 주문이 완료되지 않았습니다.</p><p>'.strtoupper($od_pg).'를 이용한 전자결제(신용카드, 계좌이체, 가상계좌 등)은 자동 취소되었습니다.');
}

// 매출증빙 분류
if ($od_settle_case == "신용카드") {
    $ot_typereceipt_cate = '17'; // 카드 17
} else {
    $ot_typereceipt_cate = '31'; // 현금 31
}

if ($ot_typereceipt == '11') { // 세금계산서 선택시
    $ot_typereceipt_cate = '11'; // 세금계산서 11
}

// 매출증빙
if ( $ot_typereceipt ) {
    // 현금영수증
    if ( $ot_typereceipt == 31 ) {

        $ot_typereceipt_cate = $ot_typereceipt_cate ? $ot_typereceipt_cate : $ot_typereceipt;

        $sql = " insert g5_shop_order_typereceipt
            set od_id               = '$od_id',
                ot_typereceipt_cate = '$ot_typereceipt_cate',
                ot_typereceipt      = '$ot_typereceipt',
                ot_typereceipt_cuse = '$typereceipt_cuse',
                ot_btel             = '$p_typereceipt_btel',
                ot_bnum             = '$p_typereceipt_bnum',
                ot_tax_email        = '$p_typereceipt_email'
        ";
        sql_query($sql);
    }
    // 세금계산서
    if ( $ot_typereceipt == 11 ) {

        $ot_location_zip1 = preg_replace('/[^0-9]/', '', substr($_POST['ot_location_zip'], 0, 3));
        $ot_location_zip2 = preg_replace('/[^0-9]/', '', substr($_POST['ot_location_zip'], 3));

        $ot_typereceipt_cate = $ot_typereceipt_cate ? $ot_typereceipt_cate : $ot_typereceipt;

        $sql = " insert g5_shop_order_typereceipt
            set od_id               = '$od_id',
                ot_typereceipt_cate = '$ot_typereceipt_cate',
                ot_typereceipt      = '$ot_typereceipt',
                ot_typereceipt_cuse = '0',
                ot_bname = '{$typereceipt_bname}',
                ot_boss_name = '{$typereceipt_boss_name}',
                ot_btel = '{$typereceipt_btel}',
                ot_bnum = '{$typereceipt_bnum}',
                ot_buptae = '{$typereceipt_buptae}',
                ot_bupjong = '{$typereceipt_bupjong}',
                ot_tax_email = '{$typereceipt_email}',
                ot_manager_name = '{$typereceipt_manager_name}',
                ot_location_zip1 = '{$ot_location_zip1}',
                ot_location_zip2 = '{$ot_location_zip2}',
                ot_location_addr1 = '{$ot_location_addr1}',
                ot_location_addr2 = '{$ot_location_addr2}',
                ot_location_addr3 = '{$ot_location_addr3}',
                ot_location_jibeon = '{$ot_location_jibeon}'
        ";
        sql_query($sql);

        // 회원정보 변경된 내역 등록
        $sql = "update g5_member
            set mb_giup_bname = '{$typereceipt_bname}',
                mb_giup_boss_name = '{$typereceipt_boss_name}',
                mb_giup_btel = '{$typereceipt_btel}',
                mb_giup_bnum = '{$typereceipt_bnum}',
                mb_giup_buptae = '{$typereceipt_buptae}',
                mb_giup_bupjong = '{$typereceipt_bupjong}',
                mb_giup_tax_email = '{$typereceipt_email}'
            WHERE mb_id = '{$member['mb_id']}'
        ";
        sql_query($sql);
    }
}else{
    if ($ot_typereceipt_cate) {
        $sql = " insert g5_shop_order_typereceipt
                set od_id               = '{$od_id}',
                    ot_typereceipt_cate = '{$ot_typereceipt_cate}'
            ";
        sql_query($sql);
    }
}

// 장바구니 상태변경
// 신용카드로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$cart_status = $od_status;
$sql_card_point = "";
if ($od_receipt_price > 0 && !$default['de_card_point']) {
    $sql_card_point = " , ct_point = '0' ";
}
$sql = "update {$g5['g5_shop_cart_table']}
           set od_id = '$od_id',
               ct_status = '$cart_status'
               $sql_card_point
         where od_id = '$tmp_cart_id'
           and ct_select = '1' ";
$result = sql_query($sql, false);

// 택배 선불이 아니면 카트에 있는 배송비 0으로 만들어주기
if ( $od_delivery_type != 'delivery1' ) {
    $sql = "UPDATE {$g5['g5_shop_cart_table']} SET ct_sendcost = 0 WHERE od_id = '$od_id'";
    sql_query($sql);
}

// 주문정보 입력 오류시 결제 취소
if(!$result) {
    if($tno) {
        $cancel_msg = '주문상태 변경 오류';
        switch($od_pg) {
            case 'lg':
                include G5_SHOP_PATH.'/lg/xpay_cancel.php';
                break;
            case 'inicis':
                include G5_SHOP_PATH.'/inicis/inipay_cancel.php';
                break;
            case 'KAKAOPAY':
                $_REQUEST['TID']               = $tno;
                $_REQUEST['Amt']               = $amount;
                $_REQUEST['CancelMsg']         = $cancel_msg;
                $_REQUEST['PartialCancelCode'] = 0;
                include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
                break;
			default:
                include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
                break;
        }
    }

    // 관리자에게 오류 알림 메일발송
    $error = 'status';
    include G5_SHOP_PATH.'/ordererrormail.php';

    // 주문삭제
    sql_query(" delete from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");

    die('<p>고객님의 주문 정보를 처리하는 중 오류가 발생해서 주문이 완료되지 않았습니다.</p><p>'.strtoupper($od_pg).'를 이용한 전자결제(신용카드, 계좌이체, 가상계좌 등)은 자동 취소되었습니다.');
}

// 회원이면서 포인트를 사용했다면 테이블에 사용을 추가
if ($is_member && $od_receipt_point)
    insert_point($member['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제");

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))) . "&nbsp;";

// 쿠폰사용내역기록
if($is_member) {
    $it_cp_cnt = count($_POST['cp_id']);
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = $_POST['cp_id'][$i];
        $cp_it_id = $_POST['it_id'][$i];
        $cp_prc = (int)$arr_it_cp_prc[$cp_it_id];

        if(trim($cid)) {
            $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                        set cp_id       = '$cid',
                            mb_id       = '{$member['mb_id']}',
                            od_id       = '$od_id',
                            cp_price    = '$cp_prc',
                            cl_datetime = '".G5_TIME_YMDHIS."' ";
            sql_query($sql);
        }

        // 쿠폰사용금액 cart에 기록
        $cp_prc = (int)$arr_it_cp_prc[$cp_it_id];
        $sql = " update {$g5['g5_shop_cart_table']}
                    set cp_price = '$cp_prc'
                    where od_id = '$od_id'
                      and it_id = '$cp_it_id'
                      and ct_select = '1'
                    order by ct_id asc
                    limit 1 ";
        sql_query($sql);
    }

    if($_POST['od_cp_id']) {
        $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                    set cp_id       = '{$_POST['od_cp_id']}',
                        mb_id       = '{$member['mb_id']}',
                        od_id       = '$od_id',
                        cp_price    = '$tot_od_cp_price',
                        cl_datetime = '".G5_TIME_YMDHIS."' ";
        sql_query($sql);
    }

    if($_POST['sc_cp_id']) {
        $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                    set cp_id       = '{$_POST['sc_cp_id']}',
                        mb_id       = '{$member['mb_id']}',
                        od_id       = '$od_id',
                        cp_price    = '$tot_sc_cp_price',
                        cl_datetime = '".G5_TIME_YMDHIS."' ";
        sql_query($sql);
    }
}

// APMS : 주문처리 - 2014.07.21
apms_order($od_id, $od_status, $member['mb_recommend']);

// 쿠폰업데이트
apms_coupon_update($member['mb_id']);

// include_once(G5_SHOP_PATH.'/ordermail1.inc.php');
// include_once(G5_SHOP_PATH.'/ordermail2.inc.php');

// SMS BEGIN --------------------------------------------------------
// 주문고객과 쇼핑몰관리자에게 SMS 전송
if($config['cf_sms_use'] && ($default['de_sms_use2'] || $default['de_sms_use3'])) {
    $is_sms_send = false;

    // 충전식일 경우 잔액이 있는지 체크
    if($config['cf_icode_id'] && $config['cf_icode_pw']) {
        $userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);

        if($userinfo['code'] == 0) {
            if($userinfo['payment'] == 'C') { // 정액제
                $is_sms_send = true;
            } else {
                $minimum_coin = 100;
                if(defined('G5_ICODE_COIN'))
                    $minimum_coin = intval(G5_ICODE_COIN);

                if((int)$userinfo['coin'] >= $minimum_coin)
                    $is_sms_send = true;
            }
        }
    }

    if($is_sms_send) {
        $sms_contents = array($default['de_sms_cont2'], $default['de_sms_cont3']);
        $recv_numbers = array($od_hp, $default['de_sms_hp']);
        $send_numbers = array($default['de_admin_company_tel'], $default['de_admin_company_tel']);

        $sms_count = 0;
        $sms_messages = array();

        for($s=0; $s<count($sms_contents); $s++) {
            $sms_content = $sms_contents[$s];
            $recv_number = preg_replace("/[^0-9]/", "", $recv_numbers[$s]);
            $send_number = preg_replace("/[^0-9]/", "", $send_numbers[$s]);

            $sms_content = str_replace("{이름}", $od_name, $sms_content);
            $sms_content = str_replace("{보낸분}", $od_name, $sms_content);
            $sms_content = str_replace("{받는분}", $od_b_name, $sms_content);
            $sms_content = str_replace("{주문번호}", $od_id, $sms_content);
            $sms_content = str_replace("{주문금액}", number_format($tot_ct_price - $tot_ct_discount + $od_send_cost + $od_send_cost2), $sms_content);
            $sms_content = str_replace("{회원아이디}", $member['mb_id'], $sms_content);
            $sms_content = str_replace("{회사명}", $default['de_admin_company_name'], $sms_content);

            $idx = 'de_sms_use'.($s + 2);

            if($default[$idx] && $recv_number) {
                $sms_messages[] = array('recv' => $recv_number, 'send' => $send_number, 'cont' => $sms_content);
                $sms_count++;
            }
        }

        // 무통장 입금 때 고객에게 계좌정보 보냄
        if($od_settle_case == '무통장' && $default['de_sms_use2'] && $od_misu > 0) {
            $sms_content = $od_name."님의 입금계좌입니다.\n금액:".number_format($od_misu)."원\n계좌:".$od_bank_account."\n".$default['de_admin_company_name'];

            $recv_number = preg_replace("/[^0-9]/", "", $od_hp);
            $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']);

            $sms_messages[] = array('recv' => $recv_number, 'send' => $send_number, 'cont' => $sms_content);
            $sms_count++;
        }

        // SMS 전송
        if($sms_count > 0) {
            if($config['cf_sms_type'] == 'LMS') {
                include_once(G5_LIB_PATH.'/icode.lms.lib.php');

                $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

                // SMS 모듈 클래스 생성
                if($port_setting !== false) {
                    $SMS = new LMS;
                    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                    for($s=0; $s<count($sms_messages); $s++) {
                        $strDest     = array();
                        $strDest[]   = $sms_messages[$s]['recv'];
                        $strCallBack = $sms_messages[$s]['send'];
                        $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                        $strSubject  = '';
                        $strURL      = '';
                        $strData     = iconv_euckr($sms_messages[$s]['cont']);
                        $strDate     = '';
                        $nCount      = count($strDest);

                        $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                        $SMS->Send();
                        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
                    }
                }
            } else {
                include_once(G5_LIB_PATH.'/icode.sms.lib.php');

                $SMS = new SMS; // SMS 연결
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);

                for($s=0; $s<count($sms_messages); $s++) {
                    $recv_number = $sms_messages[$s]['recv'];
                    $send_number = $sms_messages[$s]['send'];
                    $sms_content = iconv_euckr($sms_messages[$s]['cont']);

                    $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], $sms_content, "");
                }

                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }
        }
    }
}
// SMS END   --------------------------------------------------------

// orderview 에서 사용하기 위해 session에 넣고
$uid = md5($od_id.G5_TIME_YMDHIS.$REMOTE_ADDR);
set_session('ss_orderview_uid', $uid);

// 주문 정보 임시 데이터 삭제
if($od_pg == 'inicis') {
    $sql = " delete from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' and dt_pg = '$od_pg' ";
    sql_query($sql);
}

// 주문번호제거
set_session('ss_order_id', '');

// 기존자료 세션에서 제거
if (get_session('ss_direct'))
    set_session('ss_cart_direct', '');

// 배송지처리 - 받는사람이 있으면 처리
if($is_member && $od_b_name) {
    $sql = " select * from {$g5['g5_shop_order_address_table']}
                where mb_id = '{$member['mb_id']}'
                  and ad_name = '$od_b_name'
                  and ad_tel = '$od_b_tel'
                  and ad_hp = '$od_b_hp'
                  and ad_zip1 = '$od_b_zip1'
                  and ad_zip2 = '$od_b_zip2'
                  and ad_addr1 = '$od_b_addr1'
                  and ad_addr2 = '$od_b_addr2'
                  and ad_addr3 = '$od_b_addr3' ";
    $row = sql_fetch($sql);

    // 기본배송지 체크
    if($ad_default) {
        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ad_default = '0'
                    where mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }

    $ad_subject = clean_xss_tags($ad_subject);

    if($row['ad_id']){
        $sql = " update {$g5['g5_shop_order_address_table']}
                      set ad_default = '$ad_default',
                          ad_subject = '$ad_subject',
                          ad_jibeon  = '$od_b_addr_jibeon'
                    where mb_id = '{$member['mb_id']}'
                      and ad_id = '{$row['ad_id']}' ";
    } else {
        $sql = " insert into {$g5['g5_shop_order_address_table']}
                    set mb_id       = '{$member['mb_id']}',
                        ad_subject  = '$ad_subject',
                        ad_default  = '$ad_default',
                        ad_name     = '$od_b_name',
                        ad_tel      = '$od_b_tel',
                        ad_hp       = '$od_b_hp',
                        ad_zip1     = '$od_b_zip1',
                        ad_zip2     = '$od_b_zip2',
                        ad_addr1    = '$od_b_addr1',
                        ad_addr2    = '$od_b_addr2',
                        ad_addr3    = '$od_b_addr3',
                        ad_jibeon   = '$od_b_addr_jibeon' ";
    }

    sql_query($sql);
}

// Push - 최고관리자에게 보냄 ---------------------------------------
	$mb_list = $config['cf_admin'].','.$config['as_admin'];
	$push = array(
		'use'=>'od',
		'flag'=>'new',
		'od_name'=>$od_name,
		'od_id'=>$od_id,
		'od_amount'=>($tot_ct_price - $tot_ct_discount + $od_send_cost + $od_send_cost2),
		'od_status'=>$od_status,
		'od_memo'=>$od_memo);
	apms_push($mb_list, $od_id, $od_id, G5_URL, $push);
// ------------------------------------------------------------------

	# 주문신청
	if($_POST["penId"]){

		sql_query("
			UPDATE g5_shop_order SET
				od_del_yn = 'Y'
			WHERE od_id = '{$od_id}'
		");

		$_SESSION["productList{$od_id}"] = $productList;
		$_SESSION["deliveryTotalCnt{$od_id}"] = $deliveryTotalCnt;

		$sendData = [];
		$sendData["usrId"] = $member["mb_id"];

		$sendData["penId"] = $_POST["penId"];
		$sendData["delGbnCd"] = "";
		$sendData["ordWayNum"] = "";
		$sendData["delSerCd"] = "";
		$sendData["ordNm"] = $_POST["od_b_name"];
        $sendData["ordCont"] = ($_POST["od_b_hp"]) ? $_POST["od_b_hp"] : $_POST["od_b_tel"];
		$sendData["ordMeno"] = $_POST["od_memo"];
		$sendData["ordZip"] = $_POST["od_b_zip"];
		$sendData["ordAddr"] = $_POST["od_b_addr1"];
		$sendData["ordAddrDtl"] = $_POST["od_b_addr2"];
		$sendData["finPayment"] = "{$order_price}";
		$sendData["payMehCd"] = "0";
		$sendData["regUsrId"] = $member["mb_id"];
		$sendData["regUsrIp"] = $_SERVER["REMOTE_ADDR"];
		$sendData["prods"] = $productList;
		$sendData["documentId"] = ($_POST["penTypeCd"] == "04") ? "THK101_THK102_THK001_THK002_THK003" : "THK001_THK002_THK003";
		$sendData["eformType"] = ($_POST["penTypeCd"] == "04") ? "21" : "00";
		$sendData["conAcco1"] = $_POST["entConAcc01"];
		$sendData["conAcco2"] = $_POST["entConAcc02"];
		$sendData["returnUrl"] = G5_SHOP_URL."/orderinquiryview.php?result=Y&od_id={$od_id}&uid={$uid}&documentId={$sendData["documentId"]}";

		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, 9901);
		curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/order/insert");
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$res = curl_exec($oCurl);
		$res = json_decode($res, true);
		curl_close($oCurl);

//		echo json_encode($sendData, JSON_UNESCAPED_UNICODE);
//		return false;
		if($res["errorYN"] == "Y"){
            sql_query("
            DELETE FROM g5_shop_order
            WHERE od_id = '{$od_id}'
            ");
            alert($res["message"],G5_URL);
		} else {
			$_SESSION["uuid{$od_id}"] = $res["data"]["uuid"];
			$_SESSION["penOrdId{$od_id}"] = $res["data"]["penOrdId"];

			goto_url(G5_SHOP_URL."/orderformupdateReturn.php?uuid={$res["data"]["uuid"]}&ordId={$res["data"]["penOrdId"]}&od_id={$od_id}&documentId={$sendData["documentId"]}");
		}
	}

	# 재고신청
	if(!$_POST["penId"]){
		$stoIdList = [];

        $sendData = [];
        $sendData["usrId"] = $member["mb_id"];
        $sendData["entId"] = $member["mb_entId"];
        $prodsSendData = [];
        $prodsData = [];
        foreach($productList as $key => $value){
            $prodsData["prodId"] = $value["prodId"];
            $prodsData["prodColor"] = $value["prodColor"];
            $prodsData["prodSize"] = $value["prodSize"];
            $prodsData["prodManuDate"] = $value["prodManuDate"];
            $prodsData["prodBarNum"] = $value["prodBarNum"];
            $prodsData["stoMemo"] = $value["stoMemo"];
            $prodsData["ct_id"] = $value["ct_id"];
            array_push($prodsSendData, $prodsData);
        }

        $sendData["prods"] = $prodsSendData;
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, 9901);
        curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/insert");
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $res = curl_exec($oCurl);
        $res = json_decode($res, true);
        curl_close($oCurl);

        if($res["errorYN"] == "N"){
            for($k=0; $k<count($res['data']);$k++){
                array_push($stoIdList, $res['data'][$k]["stoId"]);
                $sql_ct = "update `g5_shop_cart` set `stoId` = CONCAT(`stoId`,'".$res['data'][$k]["stoId"]."|') where `ct_id` ='".$res['data'][$k]["ct_id"]."'";
                sql_query($sql_ct);
            }
        } else {
            sql_query("
            DELETE FROM g5_shop_order
            WHERE od_id = '{$od_id}'
            ");
            // echo print_r($res);
            // return false;
            // alert('시스템 오류, 주문이 불가능합니다.',G5_URL);
            alert($res["message"],G5_URL);

        }

		$stoIdList = implode(",", $stoIdList);
		sql_query("
			UPDATE g5_shop_order SET
				stoId = '{$stoIdList}'
			WHERE od_id = '{$od_id}'
		");

		$stoIdList = explode(",", $stoIdList);

		# 210224 보유재고등록요청
		if($_POST["od_stock_insert_yn"]){
			$sendData = [];
			$sendData["usrId"] = $member["mb_id"];
			$sendData["entId"] = $member["mb_entId"];

			$prodsSendData = [];

			foreach($stoIdList as $stoId){
				$prodsData = [];
				$prodsData["stoId"] = $stoId;
				$prodsData["stateCd"] = "01";
				array_push($prodsSendData, $prodsData);
			}

			$sendData["prods"] = $prodsSendData;

			$oCurl = curl_init();
			curl_setopt($oCurl, CURLOPT_PORT, 9901);
			curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/update");
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$res = curl_exec($oCurl);
			$res = json_decode($res, true);
			curl_close($oCurl);

			if($res["errorYN"] == "N"){
				sql_query("
					UPDATE g5_shop_order SET
						  od_delivery_yn = 'N'
						, od_stock_insert_yn = 'Y'
						, staOrdCd = '01'
						, od_status = '완료'
					WHERE od_id = '{$od_id}'
				");

				sql_query("
					UPDATE g5_shop_cart SET
						  ct_price = '0'
					WHERE od_id = '{$od_id}'
				");
			} else {
                sql_query("
                DELETE FROM g5_shop_order
                WHERE od_id = '{$od_id}'
                ");
                // alert('시스템 오류, 보유재고 등록이 불가능합니다.',G5_URL);
                alert($res["message"],G5_URL);

			}
		}

		goto_url(G5_SHOP_URL."/orderinquiryview.php?result=Y&od_id={$od_id}&amp;uid={$uid}");
	}

?>

<html>
    <head>
        <title>주문정보 기록</title>
        <script  src="//code.jquery.com/jquery-latest.min.js"></script>
        <script>
            // 결제 중 새로고침 방지 샘플 스크립트 (중복결제 방지)
            function noRefresh()
            {
                /* CTRL + N키 막음. */
                if ((event.keyCode == 78) && (event.ctrlKey == true))
                {
                    event.keyCode = 0;
                    return false;
                }
                /* F5 번키 막음. */
                if(event.keyCode == 116)
                {
                    event.keyCode = 0;
                    return false;
                }
            }

            document.onkeydown = noRefresh ;
        </script>
    </head>
</html>
