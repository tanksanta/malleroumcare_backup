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

$pen_rec_gra_cd = array(
  '00' => '등급외',
  '01' => '1등급',
  '02' => '2등급',
  '03' => '3등급',
  '04' => '4등급',
  '05' => '5등급'
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
  if($penBirth)
	  $data['penBirth'] = $penBirth[1].'-'.$penBirth[2].'-'.$penBirth[3];

	$penLtmNum = recipient_preg_match($data, 'penLtmNum');
  if($penLtmNum)
	  $data['penLtmNum'] = 'L'.$penLtmNum[1];

	$penRecGraCd = recipient_preg_match($data, 'penRecGraCd');
  if($penRecGraCd)
	  $data['penRecGraCd'] = $penRecGraCd[1];

	$penTypeCd = recipient_preg_match($data, 'penTypeCd');
  if($penTypeCd)
	  $data['penTypeCd'] = $penTypeCd[1];

	$penGender = recipient_preg_match($data, 'penGender');
  if($penGender)
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
function valid_recipient_input($data, $is_spare = false) {

	if(!$data['penNm']) {
		return '수급자명을 입력해주세요.';
	}

  if(!$is_spare) {
    if(!recipient_preg_match($data, 'penBirth')) {
      return '생년월일을 확인해주세요.';
    }
    if(!recipient_preg_match($data, 'penGender')) {
      return '성별을 확인해주세요.';
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

// 상품분류별 내구연한 - 카테고리 별 구매가능 개수 체크
function get_pen_category_limit($penId, $ca_id) {
  global $g5;

  $limit = sql_fetch("
    SELECT
      ca_use_limit,
      ca_name,
      ca_limit_month as month,
      ca_limit_num as num
    FROM
      {$g5['g5_shop_category_table']}
    WHERE
      ca_id = '$ca_id'
  ");

  if(!$limit['ca_use_limit'])
    return null;
  
  $cur_cnt = sql_fetch("
    SELECT
      COUNT(*) as cnt
    FROM
      `eform_document` d
      LEFT JOIN
        `eform_document_item` i ON d.dc_id = i.dc_id
      LEFT JOIN
        `{$g5['g5_shop_item_table']}` x ON i.it_code = x.ProdPayCode
      LEFT JOIN `{$g5['g5_shop_category_table']}` y ON x.ca_id = y.ca_id
    WHERE
      penId = '{$penId}' AND
      (d.dc_datetime BETWEEN DATE_SUB(NOW(), INTERVAL {$limit['month']} MONTH) AND NOW()) AND
      y.ca_id = '{$ca_id}'
  ")['cnt'];

  $limit['current'] = $cur_cnt;

  return $limit;
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
      ( (rl_state = 'wait' or rl_state = 'request') AND status = 'request' )
      )
    ORDER BY r.updated_at desc
  ");

  while($row = sql_fetch_array($result)) {
    $res[] = $row;
  }

  return $res;
}

// 수급자연결관리 - 연결기간(3일) 지난 수급자 연결해제
function recipient_link_clean() {
  global $member;

  $timestamp = time();
  $datetime = date('Y-m-d H:i:s', $timestamp);

  sql_query("
    UPDATE `recipient_link` l
    LEFT JOIN `recipient_link_rel` r
    ON l.rl_id = r.rl_id SET
    l.rl_state = 'wait',
    l.rl_ent_mb_id = '',
    l.rl_updated_at = '$datetime',
    r.status = 'wait',
    r.updated_at = '$datetime'
    WHERE l.rl_state = 'link'
    AND l.rl_ent_mb_id = '{$member['mb_id']}'
    AND r.mb_id = '{$member['mb_id']}'
    AND r.status = 'link'
    AND r.updated_at < DATE_SUB(NOW(), INTERVAL 3 DAY)
  ");
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
		$sql = "UPDATE g5_firebase SET mb_id = '{$member['mb_id']}', updated_at = now() WHERE fcm_token = '{$fcm_token}'";
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

function is_pen_order($od_id) {
    global $g5;
    
    $sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE od_id = '$od_id' ";
    $od = sql_fetch($sql);
    
    $result = $od['od_penId'];
    
    if (empty($result)) {
        return false;
    } else {
        return true;
    }
}

function get_stock($prodId, $prodBarNum = '') {
  global $member;

  $result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, array(
    'entId' => $member['mb_entId'],
    'usrId' => $member['mb_id'],
    'prodId' => $prodId,
    'prodBarNum' => $prodBarNum
  ));

  $res = [];
  if($result['errorYN'] == 'N' && $result['data']) {
    $res = $result['data'];
  }

  return $res;
}

// 대여 내구연한: 판매가능기간 지났으면 재고상태 05(기타)로 변경하기
function expired_rental_item_clean($prodId) {
  global $g5, $member;

  $item = sql_fetch(" SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = '$prodId' ");

  // 대여 내구연한 사용안하면 무시
  if(!$item['it_rental_use_persisting_year'] || !$item['it_rental_expiry_year']) return;

  $result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, array(
    'entId' => $member['mb_entId'],
    'usrId' => $member['mb_id'],
    'prodId' => $prodId,
    'stateCd' => ['01']
  ));
  
  if($result['errorYN'] == 'N' && $result['data']) {
    $stock_update = [];
    foreach($result['data'] as $stock) {
      $inital_contract_time = strtotime($stock['initialContractDate']);

      // 최초계약일 없으면 무시
      if(!$inital_contract_time || $inital_contract_time < 0) continue;

      // 사용가능햇수 지났으면 추가로 연장대여햇수동안 할인된 금액 적용
      if(time() >= ( $inital_contract_time + ( $item['it_rental_expiry_year'] * 365 * 24 * 60 * 60 ) )) {
        $stock_update[] = array(
          'stoId' => $stock['stoId'],
          'prodBarNum' => $stock['prodBarNum'],
          'customRentalPrice' => $item['it_rental_persisting_price']
        );
      }

      // 사용가능햇수+연장대여햇수 지나면 판매불가능 설정
      /*if(time() >= ( $inital_contract_time + ( ($item['it_rental_expiry_year'] + $item['it_rental_persisting_year']) * 365 * 24 * 60 * 60 ) )) {
        $stock_update[] = array(
          'stoId' => $stock['stoId'],
          'prodBarNum' => $stock['prodBarNum'],
          'stateCd' => '05' // 05(기타)
        );
      }*/
    }

    api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
      'entId' => $member['mb_entId'],
      'usrId' => $member['mb_id'],
      'prods' => $stock_update,
    ));
  }
}

function ct_manager_update($ct_id,$ct_manager){

    $sql = "SELECT * FROM `g5_shop_cart` WHERE ct_id = '{$ct_id}'";
    $cart = sql_fetch($sql);

    $ct_it_name = $cart['it_name'];
    $ct_option = ($cart["ct_option"] == $cart['it_name']) ? "" : "(".$cart['ct_option'].")";
    $ct_it_name = $ct_it_name.$ct_option;

    // 주문자 정보
    $order_member = get_member($cart['mb_id']);
    $giup_name = $order_member['mb_giup_bname'] ? "[" . $order_member['mb_giup_bname'] . "] " : "";

    // add_notification(
    //     array(),
    //     $ct_manager,
    //     '신규 출고 담당자로 지정된 상품이 있습니다.',
    //     $giup_name . $ct_it_name . " * " . $cart['ct_qty'] . "개",
    //     // 'http://naver.com',
    // );

    $sql_ct = "UPDATE `g5_shop_cart` SET `ct_manager`='".$ct_manager."' where `ct_id` = '".$ct_id."'";
    sql_query($sql_ct);
}

function get_recipient_grade($pen_id) {
	if (!$pen_id) {
		return false;
	}

	$sql = "SELECT * FROM recipient_grade_log WHERE pen_id = '{$pen_id}' AND del_yn = 'N'
		ORDER BY seq DESC LIMIT 1
	";
	return sql_fetch($sql);
}

function get_recipient_grade_per_year($pen_id) {
  global $member;

	if (!$pen_id) {
		return false;
	}

	// 등급정보
	$grade = get_recipient_grade($pen_id);

	// 등급정보가 없으면 유효기간 시작일로 설정
	if (!$grade) {
		$send_data = [];
		$send_data['usrId'] = $member['mb_id'];
		$send_data['entId'] = $member['mb_entId'];
		$send_data['penId'] = $pen_id;

		$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);

		$exp_date = substr($res['data'][0]['penExpiStDtm'], 4, 4);
	}

	$exp_date = $grade['pen_gra_apply_month'] . $grade['pen_gra_apply_day'];
	$exp_now = date('m') . date('d');
	$exp_year = intval($exp_date) < intval($exp_now) ? intval(date('Y')) : intval(date('Y')) - 1; // 지금날짜보다 크면 올해, 작으면 작년

	$exp_start = date('Y-m-d', strtotime($exp_year . $exp_date));
	$exp_end = date('Y-m-d', strtotime('+ 1 years', strtotime($exp_start)));

	// 계약건수, 금액
	$contract = sql_fetch("SELECT count(*) as cnt, SUM(it_price) as sum_it_price from eform_document_item edi where edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$pen_id}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
	// 판매 건수
	$contract_sell = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '00' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$pen_id}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
	// 대여 건수
	$contract_borrow = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '01' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$pen_id}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");

	return array(
		'count' => $contract['cnt'],
		'sum_price'	=> $contract['sum_it_price'],
		'sell_count' => $contract_sell['cnt'],
		'borrow_count' => $contract_borrow['cnt'],
	);
}

// 사업소별 미수금 구하는 함수
// fr_date 값이 있을 경우 해당 일 까지의 이월잔액
// total_price_only: true - 총 구매액, false - 총 미수금
function get_outstanding_balance($mb_id, $fr_date = null, $total_price_only = false) {
  $where_date = '';
  $where_ledger_date = '';
  if($fr_date) {
    $where_date = " and od_time < '{$fr_date} 00:00:00' ";
    $where_ledger_date = " and lc_created_at < '{$fr_date} 00:00:00' ";
  }

  # 매출
  $sql_order = "
    SELECT
      (c.ct_qty - c.ct_stock_qty) as ct_qty,
      (
        (
          (c.ct_qty - c.ct_stock_qty) *
          CASE
            WHEN c.io_type = 0
            THEN c.ct_price + c.io_price
            ELSE c.io_price
          END - c.ct_discount
        ) / (c.ct_qty - c.ct_stock_qty)
      ) as price_d,
      0 as deposit
    FROM
      g5_shop_order o
    LEFT JOIN
      g5_shop_cart c ON o.od_id = c.od_id
    WHERE
      c.ct_status = '완료' and
      c.ct_qty - c.ct_stock_qty > 0
  ";

  # 배송비
  $sql_send_cost = "
    SELECT
      1 as ct_qty,
      o.od_send_cost as price_d,
      0 as deposit
    FROM
      g5_shop_order o
    LEFT JOIN
      g5_shop_cart c ON o.od_id = c.od_id
    WHERE
      c.ct_status = '완료' and
      c.ct_qty - c.ct_stock_qty > 0 and
      o.od_send_cost > 0
  ";

  # 매출할인
  $sql_sales_discount = "
    SELECT
      1 as ct_qty,
      (-o.od_sales_discount) as price_d,
      0 as deposit
    FROM
      g5_shop_order o
    LEFT JOIN
      g5_shop_cart c ON o.od_id = c.od_id
    WHERE
      c.ct_status = '완료' and
      c.ct_qty - c.ct_stock_qty > 0 and
      o.od_sales_discount > 0
  ";

  # 입금/출금
  $sql_ledger = "
    SELECT
      1 as ct_qty,
      (
        CASE
          WHEN lc_type = 2
          THEN lc_amount
          ELSE 0
        END
      ) as price_d,
      (
        CASE
          WHEN lc_type = 1
          THEN lc_amount
          ELSE 0
        END
      ) as deposit
    FROM
      ledger_content l
    WHERE
      mb_id = '{$mb_id}'
  ";

  // 사업소 id로 검색
  $sql_search = " and o.mb_id = '{$mb_id}' ";

  $sql = "
    SELECT
      sum(price_d * ct_qty) as total_price,
      sum(deposit) as total_deposit
    FROM
    (
      ({$sql_order} {$sql_search} {$where_date})
      UNION ALL
      ({$sql_send_cost} {$sql_search} {$where_date} GROUP BY o.od_id)
      UNION ALL
      ({$sql_sales_discount} {$sql_search} {$where_date} GROUP BY o.od_id)
      UNION ALL
      ({$sql_ledger} {$where_ledger_date})
    ) u
  ";

  $result = sql_fetch($sql);

  $total_price = $result['total_price'] ?: 0;
  $total_deposit = $result['total_deposit'] ?: 0;

  if($total_price_only)
    return $total_price;
  else
    return $total_price - $total_deposit;
}

function get_tutorials() {
	global $g5, $member;
	$completed = false;
	
	if (!$member['mb_id']) {
		return false;
	}

	$tutorials = array(
		'step' => array(),
	);

	$sql = "SELECT * FROM tutorial WHERE mb_id = '{$member['mb_id']}' ORDER BY created_at ASC";
	$result = sql_query($sql);

	$completed_count = 0;

	while($row = sql_fetch_array($result)) {
		$tutorials['step'][] = $row;

		if ($row['t_state'] == 1) {
			$completed_count++;
		}
		if ($row['t_type'] === '' && $row['t_state'] == 1) {
			$completed = true;
		}
	}

	// 튜토리얼 진행 안한경우 추가
	if (!count($tutorials['step'])) {
		set_tutorial();
		return get_tutorials();
	}

	$tutorials['completed'] = $completed;
	$tutorials['completed_count'] = $completed_count;

	return $tutorials;
}

function get_tutorial($type) {
	global $g5, $member;

	$sql = "SELECT * FROM tutorial WHERE mb_id = '{$member['mb_id']}' and t_type = '{$type}'";
	return sql_fetch($sql);
}

function set_tutorial($type = 'recipient_add', $state = 0, $data = null) {
	global $g5, $member;

	$sql = "REPLACE INTO tutorial SET
		mb_id = '{$member['mb_id']}',
		t_type = '{$type}',
		t_state = '{$state}',
		updated_at = now()
	";

	if ($data) {
		$sql .= ", t_data = '{$data}'";
	}

	return sql_query($sql);
}
