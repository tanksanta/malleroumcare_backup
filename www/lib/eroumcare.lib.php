<?php 
function get_eroumcare($api_url, $data) {
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, $api_url);
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);
    
	return $res;
}

function get_eroumcare2($api_url, $data) {
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_URL, $api_url);
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);
    
	return $res;
}

function get_carts_by_od_id($od_id, $delivery_yn = null) {

	// 유통 비유통 구분시
	if ($delivery_yn) {
		// $delivery_where = "AND a.ct_delivery_yn = '". $delivery_yn ."'";
		$delivery_where = "AND a.prodSupYn = 'Y'";
	}

	global $g5;
	
	// 상품목록
	$sql = " select a.ct_id,
					a.it_id,
					a.it_name,
					a.cp_price,
					a.ct_notax,
					a.ct_send_cost,
					a.ct_sendcost,
					a.it_sc_type,
					a.pt_it,
					a.pt_id,
					b.ca_id,
					b.ca_id2,
					b.ca_id3,
					b.pt_msg1,
					b.pt_msg2,
					b.pt_msg3,
					a.ct_status,
					b.it_model,
					b.it_outsourcing_use,
					b.it_outsourcing_company,
					b.it_outsourcing_manager,
					b.it_outsourcing_email,
					b.it_outsourcing_option,
					b.it_outsourcing_option2,
					b.it_outsourcing_option3,
					b.it_outsourcing_option4,
					b.it_outsourcing_option5,
					a.pt_old_name,
					a.pt_old_opt,
					a.ct_uid,
					a.prodMemo,
					a.prodSupYn,
					a.ct_qty,
					a.ct_stock_qty,
					b.it_img1,
					a.ct_delivery_company,
					a.ct_delivery_num,
					a.ct_combine_ct_id,
					b.it_delivery_cnt,
					b.it_delivery_price,
					a.ct_delivery_cnt,
					a.ct_delivery_price
			  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
			  $delivery_where
			  group by a.it_id, a.ct_uid
			  order by a.ct_id ";

	$result = sql_query($sql);

	$carts = array();
	$cate_counts = array();

	$od_cart_count = 0;
	for($i=0; $row=sql_fetch_array($result); $i++) {

		$cate_counts[$row['ct_status']] += 1;

		// 상품의 옵션정보
		$sql = " select a.ct_id,
						a.mb_id, 
						a.it_id, 
						a.ct_price, 
						a.ct_point, 
						a.ct_qty, 
						a.ct_stock_qty, 
						a.ct_barcode, 
						a.ct_option, 
						a.ct_status, 
						a.cp_price, 
						a.ct_stock_use, 
						a.ct_point_use, 
						a.ct_send_cost, 
						a.ct_sendcost, 
						a.io_type, 
						a.io_price, 
						a.pt_msg1, 
						a.pt_msg2, 
						a.pt_msg3, 
						a.ct_discount, 
						a.ct_uid, 
						a.ct_combine_ct_id,
						b.it_delivery_cnt,
						b.it_delivery_price,
						a.ct_delivery_cnt,
						a.ct_delivery_price,
						a.it_name,
						a.ct_delivery_company,
						a.ct_delivery_num,
						( SELECT prodSupYn FROM g5_shop_item WHERE it_id = a.it_id ) AS prodSupYn,
						prodMemo
					from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
					where a.od_id = '{$od_id}'
						and a.it_id = '{$row['it_id']}'
						and a.ct_uid = '{$row['ct_uid']}'
					order by a.io_type asc, a.ct_id asc ";
		$res = sql_query($sql);

		$row['options_span'] = sql_num_rows($res);

		$row['options'] = array();
		for($k=0; $opt=sql_fetch_array($res); $k++) {

			$opt_price = 0;

			if($opt['io_type'])
				$opt_price = $opt['io_price'];
			else
				$opt_price = $opt['ct_price'] + $opt['io_price'];

			$opt["opt_price"] = $opt_price;
			$od_cart_count += $opt["ct_qty"];

			// 소계
			$opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
			$opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

			if($opt["prodSupYn"] == "Y"){
				$opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
			}

			$row['options'][] = $opt;
		}


		// 합계금액 계산
		$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
						SUM(ct_qty) as qty,
						SUM(ct_discount) as discount,
						SUM(ct_send_cost) as sendcost
					from {$g5['g5_shop_cart_table']}
					where it_id = '{$row['it_id']}'
						and od_id = '{$od_id}'
						and ct_uid = '{$row['ct_uid']}'";
		$sum = sql_fetch($sql);

		$row['sum'] = $sum;

		$carts[] = $row;
	}

	return $carts;
}