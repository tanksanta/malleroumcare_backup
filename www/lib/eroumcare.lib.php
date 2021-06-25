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

// 시스템DB dtm형식을 timestamp로 변환
// ex) dtm: 20210411223341
function dtmtotime($dtm) {
  $Y = substr($dtm, 0, 4);
  $m = substr($dtm, 4, 2);
  $d = substr($dtm, 6, 2);
  $H = substr($dtm, 8, 2);
  $i = substr($dtm, 10, 2);
  $s = substr($dtm, 12, 2);

  return strtotime("$Y-$m-$d $H:$i:$s");
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
					a.ct_delivery_price,
					a.ct_is_direct_delivery
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
						a.stoId, 
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
						a.ct_edi_result,
						a.ct_is_direct_delivery,
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

$pen_type_cd = array(
	'00' => '일반 15%',
	'01' => '감경 9%',
	'02' => '감경 6%',
	'03' => '의료 6%',
	'04' => '기초 0%',
);

function get_carts_by_recipient($recipient) {
	global $member;
	global $g5;

	$sql = "SELECT COUNT(*) AS cnt FROM (
				SELECT *
				from {$g5['g5_shop_cart_table']} a
				where a.ct_pen_id = '$recipient' 
					and a.mb_id = '{$member['mb_id']}'
					and a.ct_direct = '0'
					and a.ct_status = '쇼핑' 
				GROUP BY a.it_id 
				order by a.ct_id 
			) b";
	$result = sql_fetch($sql);

	return $result['cnt'] ?: 0;
}

function get_memos_by_recipient($penId) {

	$result = sql_query("
		SELECT * FROM `recipient_memo`
		WHERE penId = '$penId'
		ORDER BY me_id desc
	");

	$res = [];
	while($row = sql_fetch_array($result)) {
		$res[] = $row;
	}

	return $res;
}

// 수급자별 욕구사정기록지
function get_recs_by_recipient($penId) {
	global $member;

	$result = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_REC_LIST, array(
		'usrId' => $member['mb_id'],
		'entId' => $member['mb_entId'],
		'penId' => $penId
	));

	$res = [];
	if($result['errorYN'] == 'N' && $result['data']) {
		$res = $result['data'];
	}

	return $res;
}

// 수급자별 취급 상품
function get_items_by_recipient($penId) {
	$result = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_ITEM_LIST, array(
		'penId' => $penId
	));

	$res = [];
	if($result['errorYN'] == 'N' && $result['data']) {
		$res = $result['data'];
	}

	return $res;
}

$recipient_input_regex = array(
	'penJumin' => '/([1-9][0-9]{5})-?([1-9][0-9]{6})/',
	'penBirth' => '/([1-9][0-9]{3})-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
	'penLtmNum' => '/L?([0-9]{10})/',
	'penRecGraCd' => '/(0[0-5])/',
	'penExpiStDtm' => '/([1-9][0-9]{3})-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
	'penExpiEdDtm' => '/([1-9][0-9]{3})-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
	'penTypeCd' => '/(0[0-4])/',
	'penGender' => '/(남|여)/',
	'penConNum' => '/([0-9]{3})-?([0-9]{4}|[0-9]{3})-?([0-9]{4})/',
	'penConPnum' => '/([0-9]{3}|[0-9]{2})-?([0-9]{4}|[0-9]{3})-?([0-9]{4})/',
	'penProBirth' => '/([1-9][0-9]{3})-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
	'penProRel' => '/(0[0-9]|1[0-1])/',
	'penProConNum' => '/([0-9]{3})-?([0-9]{4}|[0-9]{3})-?([0-9]{4})/',
	'penProConPnum' => '/([0-9]{3}|[0-9]{2})-?([0-9]{4}|[0-9]{3})-?([0-9]{4})/'
);

function recipient_preg_match($data, $key) {
	global $recipient_input_regex;

	$matches = [];
	preg_match($recipient_input_regex[$key], $data[$key], $matches);

	return $matches;
}

// 수급자 등록시 필드 정규화
function normalize_recipient_input($data) {
	$penBirth = recipient_preg_match($data, 'penBirth');
	$data['penBirth'] = $penBirth[1].'-'.$penBirth[2].'-'.$penBirth[3];

	$penLtmNum = recipient_preg_match($data, 'penLtmNum');
	$data['penLtmNum'] = 'L'.$penLtmNum[1];

	$penRecGraCd = recipient_preg_match($data, 'penRecGraCd');
	$data['penRecGraCd'] = $penRecGraCd[1];

	$penTypeCd = recipient_preg_match($data, 'penTypeCd');
	$data['penTypeCd'] = $penTypeCd[1];

	$penGender = recipient_preg_match($data, 'penGender');
	$data['penGender'] = $penGender[1];

	$penExpiStDtm = recipient_preg_match($data, 'penExpiStDtm');
	if($penExpiStDtm)
		$data['penExpiStDtm'] = $penExpiStDtm[1].'-'.$penExpiStDtm[2].'-'.$penExpiStDtm[3];
	
	$penExpiEdDtm = recipient_preg_match($data, 'penExpiEdDtm');
	if($penExpiEdDtm)
		$data['penExpiEdDtm'] = $penExpiEdDtm[1].'-'.$penExpiEdDtm[2].'-'.$penExpiEdDtm[3];

	$penJumin = recipient_preg_match($data, 'penJumin');
	if($penJumin)
		$data['penJumin'] = $penJumin[1].$penJumin[2];

	$penConNum = recipient_preg_match($data, 'penConNum');
	if($penConNum)
		$data['penConNum'] = $penConNum[1].'-'.$penConNum[2].'-'.$penConNum[3];

	$penConPnum = recipient_preg_match($data, 'penConPnum');
	if($penConPnum)
		$data['penConPnum'] = $penConPnum[1].'-'.$penConPnum[2].'-'.$penConPnum[3];
	
	$penProBirth = recipient_preg_match($data, 'penProBirth');
	if($penProBirth)
		$data['penProBirth'] = $penProBirth[1].'-'.$penProBirth[2].'-'.$penProBirth[3];

	$penProRel = recipient_preg_match($data, 'penProRel');
	if($penProRel)
		$data['penProRel'] = $penProRel[1];

	$penProConNum = recipient_preg_match($data, 'penProConNum');
	if($penProConNum)
		$data['penProConNum'] = $penProConNum[1].'-'.$penProConNum[2].'-'.$penProConNum[3];

	$penProConPnum = recipient_preg_match($data, 'penProConPnum');
	if($penProConPnum)
		$data['penProConPnum'] = $penProConPnum[1].'-'.$penProConPnum[2].'-'.$penProConPnum[3];
	
	return $data;
}

// 수급자 등록시 필드 무결성 체크
function valid_recipient_input($data) {

	if(!$data['penNm']) {
		return '수급자명을 입력해주세요.';
	}
	if(!recipient_preg_match($data, 'penBirth')) {
		return '생년월일을 확인해주세요.';
	}
	if(!recipient_preg_match($data, 'penLtmNum')) {
		return '장기요양번호를 확인해주세요.';
	}
	if(!recipient_preg_match($data, 'penRecGraCd')) {
		return '장기요양등급을 확인해주세요.';
	}
	if(!recipient_preg_match($data, 'penTypeCd')) {
		return '본인부담율을 확인해주세요.';
	}
	# 기초수급자는 주민등록번호 입력 필수
	if($data['penTypeCd'] == '04') {
		if(!recipient_preg_match($data, 'penJumin')) {
			return '주민등록번호를 확인해주세요.';
		}
	}
	if(!recipient_preg_match($data, 'penGender')) {
		return '성별을 확인해주세요.';
	}
	if($data['penExpiStDtm']) {
		if(!recipient_preg_match($data, 'penExpiStDtm')) {
			return '유효기간(시작일)을 확인해주세요.';
		}
		if(!recipient_preg_match($data, 'penExpiEdDtm')) {
			return '유효기간(종료일)을 확인해주세요.';
		}
	}
	if($data['penConNum'] && !recipient_preg_match($data, 'penConNum')) {
		return '휴대폰번호를 확인해주세요.';
	}
	if($data['penConPnum'] && !recipient_preg_match($data, 'penConPnum')) {
		return '일반전화번호를 확인해주세요.';
	}
	return false;
}

// 보호자 관계
$pen_pro_rel_cd = array(
	'00' => '처',
	'01' => '남편',
	'02' => '자',
	'03' => '자부',
	'04' => '사위',
	'05' => '형제',
	'06' => '자매',
	'07' => '손',
	'08' => '배우자 형제자매',
	'09' => '외손',
	'10' => '부모',
	'11' => '' // 직접입력
);

$pen_cnm_type_cd = array(
	'00' => '수급자',
	'01' => '보호자'
);

$pen_rec_type_cd = array(
	'00' => '방문',
	'01' => '유선'
);

// 보호자 관계 출력
function get_pen_pro_rel($penProTypeCd, $penProRel) {
	global $pen_pro_rel_cd;

	switch($penProTypeCd) {
		case '00': // 보호자없음
			return '없음';
		case '02': // 요양보호사
			return '요양보호사';
		default:
			return $pen_pro_rel_cd[$penProRel];
	}
}

$sale_product_table = array(
	'ITM2021010800001' => '경사로(실내용)',
	'ITM2020092200020' => '욕창예방매트리스',
	'ITM2020092200011' => '요실금팬티',
	'ITM2020092200010' => '자세변환용구',
	'ITM2020092200009' => '욕창예방방석',
	'ITM2020092200008' => '지팡이',
	'ITM2020092200007' => '간이변기',
	'ITM2020092200006' => '미끄럼방지용품(매트)',
	'ITM2020092200005' => '미끄럼방지용품(양말)',
	'ITM2020092200004' => '안전손잡이',
	'ITM2020092200003' => '성인용보행기',
	'ITM2020092200002' => '목욕의자',
	'ITM2020092200001' => '이동변기'
);

$rental_product_table = array(
	'ITM2020092200019' => '욕창예방매트리스',
	'ITM2020092200018' => '경사로(실외용)',
	'ITM2020092200017' => '배회감지기',
	'ITM2020092200016' => '목욕리프트',
	'ITM2020092200015' => '이동욕조',
	'ITM2020092200014' => '수동침대',
	'ITM2020092200013' => '전동침대',
	'ITM2020092200012' => '수동휠체어'
);

$sale_product_cate_table = array(
	'ITM2021010800001' => '10d0',
	'ITM2020092200020' => '1010',
	'ITM2020092200011' => '1020',
	'ITM2020092200010' => '1030',
	'ITM2020092200009' => '1040',
	'ITM2020092200008' => '1050',
	'ITM2020092200007' => '1060',
	'ITM2020092200006' => '1080',
	'ITM2020092200005' => '1070',
	'ITM2020092200004' => '1090',
	'ITM2020092200003' => '10a0',
	'ITM2020092200002' => '10b0',
	'ITM2020092200001' => '10c0'
);

$rental_product_cate_table = array(
	'ITM2020092200019' => '2010',
	'ITM2020092200018' => '2020',
	'ITM2020092200017' => '2030',
	'ITM2020092200016' => '2040',
	'ITM2020092200015' => '2050',
	'ITM2020092200014' => '2060',
	'ITM2020092200013' => '2070',
	'ITM2020092200012' => '2080'
);

$cate_gubun_table = array(
	'10' => '00', /* 판매 */
	'20' => '01', /* 대여 */
	'70' => '02' /* 비급여 */
);

$recipient_link_state = array(
	'wait' => '대기',
	'request' => '요청',
	'link' => '연결',
	'done' => '등록',
);

function get_recipient($penId) {
	global $member;

	$result = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
		'usrId' => $member['mb_id'],
		'entId' => $member['mb_entId'],
		'penId' => $penId
	));

	$res = null;
	if($result['errorYN'] == 'N' && $result['data'])
		$res = $result['data'][0];

	return $res;
}

// 비급여 상품 체크
function is_benefit_item($item) {
    if (substr($item["ca_id"], 0, 2) == '70') {
        return true;
    } else {
        return false;
    }
}

// 상품분류별 내구연한 - 수급자 주문 별 구매가능 개수 체크
function get_pen_order_limit($penId, $od_id) {
  global $g5;

  $limit = [];
  $result = sql_query("
    SELECT ca_id, ca_name, ca_limit_month, ca_limit_num
    FROM {$g5['g5_shop_category_table']}
    WHERE ca_use_limit = 1
  ");
  while($row = sql_fetch_array($result)) {
    $limit[$row['ca_id']] = array(
      'ca_name' => $row['ca_name'],
      'month' => $row['ca_limit_month'],
      'num' => $row['ca_limit_num']
    );
  }
  
  if(!$limit)
    return [];
  
  $result = sql_query("
    select count(*) as cnt, x.ca_id, y.ca_name
    from (
      select ca_id
      from {$g5['g5_shop_cart_table']} a
      left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
      where a.od_id = '{$od_id}'
      and a.ct_select = '1'
      group by a.it_id
    ) x
    left join {$g5['g5_shop_category_table']} y on x.ca_id = y.ca_id
    group by x.ca_id
  ");
  
  $res = [];
  while($row = sql_fetch_array($result)) {
    $lm = $limit[$row['ca_id']];
  
    if($lm) {
      $cur_cnt = sql_fetch("
        SELECT COUNT(*) as cnt
        FROM `eform_document` d
        LEFT JOIN `eform_document_item` i ON d.dc_id = i.dc_id
        LEFT JOIN `{$g5['g5_shop_item_table']}` x ON i.it_code = x.ProdPayCode
        LEFT JOIN `{$g5['g5_shop_category_table']}` y ON x.ca_id = y.ca_id
        WHERE penId = '{$penId}'
        AND (d.dc_datetime BETWEEN DATE_SUB(NOW(), INTERVAL {$lm['month']} MONTH) AND NOW())
        AND y.ca_id = '{$row['ca_id']}'
      ")['cnt'];
  
      if($cur_cnt + $row['cnt'] > $lm['num']) { // 구매 가능한 수량 넘으면
        // 주문에서 해당 카테고리 아이템들 가져오기
        $item_query = sql_query("
        select b.ProdPayCode
        from {$g5['g5_shop_cart_table']} a
        left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
        where a.od_id = '{$od_id}'
        and a.ct_select = '1' 
        and b.ca_id = '{$row['ca_id']}'
        group by a.it_id
        ");

        $od_items = []; // 주문에 있는 해당 카테고리의 아이템들
        while($item = sql_fetch_array($item_query)) {
          $od_items[] = $item['ProdPayCode'];
        }

        $res[] = array(
          'ca_name' => $row['ca_name'],
          'ca_id' => $row['ca_id'],
          'month' => $lm['month'],
          'limit' => $lm['num'],
          'current' => $cur_cnt,
          'cnt' => $row['cnt'],
          'od_items' => $od_items
        );
      }
    }
  }

  return $res;
}

// 주소(string)로 경위도 가져오는 함수
function get_lat_lng_by_address($address) {
  $result = kakao_api_call('https://dapi.kakao.com/v2/local/search/address.json', array(
    'query' => $address,
    'page' => 1,
    'size' => 1
  ));

  $res = [];
  if($result['meta']['total_count'] > 0) {
    $res['lat'] = $result['documents'][0]['y'];
    $res['lng'] = $result['documents'][0]['x'];
  }

  return $res;
}

// 수급자연결관리 - 수급자연결에 등록된 수급자(rl_id)와 사업소 회원(mb_id) 연결상태 가져오는 함수
function get_recipient_link($rl_id, $mb_id) {

  if(!$rl_id || !$mb_id) return;

  return sql_fetch("
    SELECT * FROM recipient_link_rel
    WHERE rl_id = '$rl_id'
    AND mb_id = '$mb_id'
  ");
}

// 수급자연결관리 - 사업소 회원(mb_id)로 연결된 수급자들 가져오는 함수
function get_recipient_links($mb_id) {
  $res = [];
  if(!$mb_id) return $res;

  $result = sql_query("
    SELECT * FROM recipient_link_rel r
    LEFT JOIN recipient_link l ON r.rl_id = l.rl_id
    WHERE mb_id = '$mb_id'
    AND status <> 'done'
    AND (
      ( rl_state = 'link' and rl_ent_mb_id = '$mb_id' )
      OR
      ( rl_state = 'wait' or rl_state = 'request' )
      )
    ORDER BY r.updated_at desc
  ");

  while($row = sql_fetch_array($result)) {
    $res[] = $row;
  }

  return $res;
}

function send_notification($registration_ids, $notification)
{
	$registration_ids = array_values($registration_ids);

	$url = 'https://fcm.googleapis.com/fcm/send';
	$fields = array(
		'registration_ids' => $registration_ids,
		'notification' => $notification
	);

	$headers = array(
		'Authorization:key =' . GOOGLE_API_KEY,
		'Content-Type: application/json'
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	if ($result === FALSE) {
		return false;
	}
	curl_close($ch);
	return $result;
}

function send_notification_link($registration_ids, $notification, $link = '')
{
	$registration_ids = array_values($registration_ids);

	$url = 'https://fcm.googleapis.com/fcm/send';
	$fields = array(
		'registration_ids' => $registration_ids, // token ids
		'notification' => $notification, // 제목, 내용
		'data' => array(
			'link' => $link // 링크
		)
	);

	$headers = array(
		'Authorization:key =' . GOOGLE_API_KEY,
		'Content-Type: application/json'
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	if ($result === FALSE) {
		return false;
	}
	curl_close($ch);
	return $result;
}

function add_fcmtoken($fcm_token) {
  global $member;

  if (!$fcm_token || $fcm_token == 'null') {
    return false;
  }

  $sql = "SELECT * FROM g5_firebase WHERE fcm_token = '{$fcm_token}'";
  $mb_fcm = sql_fetch($sql);

  if ($mb_fcm['fcm_id'] && $member['mb_id']) {
		$sql = "UPDATE g5_firebase SET mb_id = '{$member['mb_id']}' WHERE fcm_token = '{$fcm_token}'";
		return sql_query($sql);
  }

	$sql = " insert into `g5_firebase`
		set fcm_token = '$fcm_token' 
	";

	if($member['mb_id']) {
		$sql .= ", mb_id = '{$member['mb_id']}'";
	}
	return sql_query($sql);
}

function cancel_notification($uid) {
  if (!$uid) return false;
  $sql = "UPDATE g5_firebase_push SET fp_state = 3 WHERE fp_uid = '{$uid}' AND fp_state = 0";
  return sql_query($sql);
}

function add_notification($ids, $mb_ids= array(), $title, $body, $link='', $date='', $uid='') {
  if (!$ids) {
    $ids = array();
  }
  if (!$mb_ids) {
    $mb_ids = array();
  }
  if (!is_array($ids)) {
    $ids = array($ids);
  }
  if (!is_array($mb_ids)) {
    $mb_ids = array($mb_ids);
  }
  if (!count($ids) && !count($mb_ids)) {
    return false;
  }

  $json_ids = json_encode($ids);
  $json_mb_ids = json_encode($mb_ids);

  $sql = "INSERT INTO g5_firebase_push SET 
    fp_ids = '{$json_ids}',
    fp_mb_ids = '{$json_mb_ids}',
    fp_title = '{$title}',
    fp_body = '{$body}'
  ";

  if ($link) {
    $sql .= ", fp_link = '{$link}'";
  }

  if ($date) {
    $sql .= ", fp_date = '{$date}'";
  }

  if ($uid) {
    $sql .= ", fp_uid = '{$uid}'";
  }

  return sql_query($sql);
}

function get_token_by_id($mb_id) {

  if (!$mb_id) return array();

  $sql = "SELECT fcm_token FROM g5_firebase WHERE mb_id = '{$mb_id}'";
  $result = sql_query($sql);

  $tokens = array();

  while ($row = sql_fetch_array($result)) {
    $tokens[] = $row['fcm_token'];
  }

  return $tokens;
}

function get_ent_id_by_od_id($od_id) {
    global $g5;
    
    $sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE od_id = '$od_id' ";
    $od = sql_fetch($sql);
    
    $sql = "SELECT * FROM {$g5['member_table']} WHERE mb_id = '{$od['mb_id']}' ";
    $mb = sql_fetch($sql);
    
    return $mb['mb_entId'];
}