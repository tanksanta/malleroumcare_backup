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
   
	//var_dump($res);
	//alert(print_r(curl_getinfo($oCurl)));
	//console.log(curl_errno($oCurl));
	//echo curl_error($oCurl);
    
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

function get_carts_by_od_id($od_id, $delivery_yn = null, $where = null, $order_by = "a.ct_id") {

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
          b.it_delivery_company,
					a.ct_delivery_cnt,
					a.ct_delivery_box_type,
					a.ct_delivery_price,
					a.ct_is_direct_delivery,
          a.ct_send_direct_delivery,
          a.ct_send_direct_delivery_fax,
          a.ct_send_direct_delivery_email
			  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
			  $delivery_where
        $where
			  group by a.it_id, a.ct_uid
			  order by $order_by";

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
            a.io_id,
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
            b.it_delivery_company,
						a.ct_delivery_cnt,
						a.ct_delivery_box_type,
						a.ct_delivery_price,
						a.it_name,
						a.ct_delivery_company,
						a.ct_delivery_num,
						a.ct_edi_result,
						a.ct_is_direct_delivery,
            a.ct_direct_delivery_partner,
            a.ct_direct_delivery_price,
            a.ct_warehouse,
						b.prodSupYn,
            b.it_taxInfo,
						prodMemo,
            b.ca_id,
            a.ct_barcode_insert,
            b.it_price
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
  '05' => '5등급',
  '06' => '6등급'
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

  $penId = get_search_string($penId);

  $result = sql_query("
    SELECT * FROM
    (
      (
        SELECT
          rs_id as recId,
          'simple' as type,
          total_review,
          created_at,
          updated_at
        FROM
          recipient_rec_simple
        WHERE
          penId = '{$penId}' and mb_id = '{$member['mb_id']}'
      )
      UNION ALL
      (
        SELECT
          rd_id as recId,
          'detail' as type,
          total_review,
          created_at,
          updated_at
        FROM
          recipient_rec_detail
        WHERE
          penId = '{$penId}' and mb_id = '{$member['mb_id']}'
      )
    ) u
    ORDER BY
      created_at DESC
  ");

  $res = [];
  while($row = sql_fetch_array($result)) {
    $res[] = $row;
  }

	return $res;
}

// 보호자 가져오기
function get_pros_by_recipient($penId) {
  global $member;

  $penId = clean_xss_tags($penId);

  $sql = "
    SELECT * FROM
      recipient_protector
    WHERE
      mb_id = '{$member['mb_id']}' and
      penId = '$penId'
  ";

  $result = sql_query($sql);

  $pros = [];
  while($row = sql_fetch_array($result)) {
    $pros[] = $row;
  }

  return $pros;
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
	// 'penJumin' => '/([1-9][0-9]{5})-?([1-9][0-9]{6})/',
  'penJumin' => '/([1-9][0-9]{5})/',
	'penBirth' => '/([1-9][0-9]{3})-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
	'penLtmNum' => '/L?([0-9]{10})/',
	'penRecGraCd' => '/(0[0-6])/',
	'penExpiStDtm' => '/([1-9][0-9]{3})-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
	'penExpiEdDtm' => '/([1-9][0-9]{3})-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
	'penTypeCd' => '/(0[0-4])/',
	'penGender' => '/(남|여|미지정)/',
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
// $is_spare = true: 예비수급자, false: 수급자
// $valid_exist_input_only = true: 값이 존재할때만 무결성 체크함(값이 비어있으면 무결성 체크 통과), false: 값이 비어있으면 무결성 체크결과 오류
function valid_recipient_input($data, $is_spare = false, $valid_exist_input_only = false) {
  global $exist_input_only;
  $exist_input_only = $valid_exist_input_only;

	if(!$data['penNm']) {
		return '수급자명을 입력해주세요.';
	}

  // $valid_exist_input_only = true 인 경우 값이 비어있으면 무결성 체크 통과시킴
  if (!function_exists('_recipient_preg_match')) {
    function _recipient_preg_match($data, $key) {
      global $exist_input_only;

      if($exist_input_only && !$data[$key])
        return true;
      
      return recipient_preg_match($data, $key);
    }
  }

  if(!$is_spare) {
    if(!_recipient_preg_match($data, 'penBirth')) {
      return '생년월일을 확인해주세요.';
    }
    if(!_recipient_preg_match($data, 'penGender')) {
      return '성별을 확인해주세요.';
    }
    if(!recipient_preg_match($data, 'penLtmNum')) { // 장기요양번호는 필수값 ($valid_exist_input_only 여부 상관없이)
      return '장기요양번호를 확인해주세요.';
    }
    if(!_recipient_preg_match($data, 'penRecGraCd')) {
      return '장기요양등급을 확인해주세요.';
    }
    if(!_recipient_preg_match($data, 'penTypeCd')) {
      return '본인부담율을 확인해주세요.';
    }
    # 기초수급자는 주민등록번호 입력 필수
    if($data['penTypeCd'] == '04') {
      if(!_recipient_preg_match($data, 'penJumin')) {
        return '주민등록번호를 확인해주세요.';
      }
    }
  }
	if($data['penExpiStDtm']) {
		if(!_recipient_preg_match($data, 'penExpiStDtm')) {
			return '유효기간(시작일)을 확인해주세요.';
		}
		if(!_recipient_preg_match($data, 'penExpiEdDtm')) {
			return '유효기간(종료일)을 확인해주세요.';
		}
	}
	if($data['penConNum'] && !_recipient_preg_match($data, 'penConNum')) {
		return '휴대폰번호를 확인해주세요.';
	}
	if($data['penConPnum'] && !_recipient_preg_match($data, 'penConPnum')) {
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

// 수급자 활동 알림 - 알림 개수 가져오는 함수
function get_recipient_noti_count() {
  global $member;
  
  $cnt_sql = "
    SELECT
      COUNT(*) as cnt
    FROM
      recipient_noti
    WHERE
      mb_id = '{$member['mb_id']}' and
      rn_checked_yn = 'N'
  ";

  $cnt_result = sql_fetch($cnt_sql);

  return $cnt_result ? $cnt_result['cnt'] : 0;
}

// 상품분류별 내구연한 - 수급자 활동 알림 (10일 전 알림)
function category_limit_noti() {
  global $member;

  if(!$member['mb_id'] || !$member['mb_entId'])
    return;

  // 판매일경우 판매일+내구연한개월수, 대여일경우 대여종료일
  // 이 값이 현재일-내구연한개월수 보다 같거나 크면 구매가능개수에서 차감해야함
  $end_date = "
    CASE
      WHEN i.gubun = '01'
      THEN
        STR_TO_DATE(SUBSTRING_INDEX(i.it_date, '-', -3), '%Y-%m-%d')
      ELSE
        DATE_ADD(STR_TO_DATE(SUBSTRING_INDEX(i.it_date, '-', -3), '%Y-%m-%d'), INTERVAL y.ca_limit_month MONTH)
    END
  ";
  $eform_sql = "
    SELECT
      HEX(d.dc_id) as uuid,
      d.penNm,
      d.penLtmNum,
      y.ca_id,
      y.ca_name,
      $end_date as end_date,
      count(*) as qty
    FROM
      eform_document d
    LEFT JOIN
      eform_document_item i ON d.dc_id = i.dc_id
    LEFT JOIN
      g5_shop_item x ON x.it_id = (
        select it_id
        from g5_shop_item
        where
          ProdPayCode = i.it_code and
          (
            ( i.gubun = '00' and ca_id like '10%' ) or
            ( i.gubun = '01' and ca_id like '20%' )
          )
        limit 1
      )
    LEFT JOIN
      g5_shop_category y ON x.ca_id = y.ca_id
    WHERE
      d.entId = '{$member['mb_entId']}' and
      y.ca_use_limit = 1 and
      DATEDIFF($end_date, NOW()) BETWEEN 0 AND 10
    GROUP BY
      d.dc_id
  ";
  $eform_result = sql_query($eform_sql);
  while($row = sql_fetch_array($eform_result)) {
    $check_result = sql_fetch(" SELECT rn_id FROM recipient_noti WHERE rn_type = 'eform' and dc_id = UNHEX('{$row['uuid']}') ");
    if($check_result && $check_result['rn_id']) {
      // 이미 알림에 등록되어있으면 건너뜀
      continue;
    }

    // 수급자알림 테이블에 등록
    sql_query("
      INSERT INTO
        recipient_noti
      SET
        rn_type = 'eform',
        dc_id = UNHEX('{$row['uuid']}'),
        mb_id = '{$member['mb_id']}',
        penNm = '{$row['penNm']}',
        penLtmNum = '{$row['penLtmNum']}',
        ca_id = '{$row['ca_id']}',
        ca_name = '{$row['ca_name']}',
        qty = '{$row['qty']}',
        end_date = '{$row['end_date']}',
        rn_created_at = NOW(),
        rn_updated_at = NOW()
    ");
  }

  $end_date = "
    CASE
      WHEN sd_gubun = '01'
      THEN
        sd_rent_date
      ELSE
        DATE_ADD(sd_sale_date, INTERVAL y.ca_limit_month MONTH)
    END
  ";
  $upload_sql = "
    SELECT
      sd_id,
      sd_pen_nm as penNm,
      sd_pen_ltm_num as penLtmNum,
      y.ca_id,
      y.ca_name,
      $end_date as end_date,
      count(*) as qty
    FROM
      stock_data_upload d
    LEFT JOIN
      g5_shop_item x ON sd_it_code = x.ProdPayCode and
      (
        ( sd_gubun = '00' and x.ca_id like '10%' ) or
        ( sd_gubun = '01' and x.ca_id like '20%' )
      )
    LEFT JOIN
      g5_shop_category y ON x.ca_id = y.ca_id
    WHERE
      d.mb_id = '{$member['mb_id']}' and
      sd_status = 1 and
      y.ca_use_limit = 1 and
      DATEDIFF($end_date, NOW()) BETWEEN 0 AND 10
    GROUP BY
      sd_id
  ";
  $upload_result = sql_query($upload_sql);
  while($row = sql_fetch_array($upload_result)) {
    $check_result = sql_fetch(" SELECT rn_id FROM recipient_noti WHERE rn_type = 'upload' and sd_id = '{$row['sd_id']}' ");
    if($check_result && $check_result['rn_id']) {
      // 이미 알림에 등록되어있으면 건너뜀
      continue;
    }

    // 수급자알림 테이블에 등록
    sql_query("
      INSERT INTO
        recipient_noti
      SET
        rn_type = 'upload',
        sd_id = '{$row['sd_id']}',
        mb_id = '{$member['mb_id']}',
        penNm = '{$row['penNm']}',
        penLtmNum = '{$row['penLtmNum']}',
        ca_id = '{$row['ca_id']}',
        ca_name = '{$row['ca_name']}',
        qty = '{$row['qty']}',
        end_date = '{$row['end_date']}',
        rn_created_at = NOW(),
        rn_updated_at = NOW()
    ");
  }
}

// 상품분류별 내구연한 - 카테고리 별 구매가능 개수 체크
function get_pen_category_limit($penLtmNum, $ca_id) {
  global $g5, $member;

  if(!$member['mb_id'] || !$member['mb_entId'])
    return null;

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
  
  // 판매일경우 판매일+내구연한개월수, 대여일경우 대여종료일
  // 이 값이 현재일-내구연한개월수 보다 같거나 크면 구매가능개수에서 차감해야함
  $end_date = "
    CASE
      WHEN i.gubun = '01'
      THEN
        STR_TO_DATE(SUBSTRING_INDEX(i.it_date, '-', -3), '%Y-%m-%d')
      ELSE
        DATE_ADD(STR_TO_DATE(SUBSTRING_INDEX(i.it_date, '-', -3), '%Y-%m-%d'), INTERVAL y.ca_limit_month MONTH)
    END
  ";
  $eform_result = sql_fetch("
    SELECT
      COUNT(*) as current
    FROM
      eform_document d
    LEFT JOIN
      eform_document_item i ON d.dc_id = i.dc_id
    LEFT JOIN
      g5_shop_item x ON x.it_id = (
        select it_id
        from g5_shop_item
        where
          ProdPayCode = i.it_code and
          (
            ( i.gubun = '00' and ca_id like '10%' ) or
            ( i.gubun = '01' and ca_id like '20%' )
          )
        limit 1
      )
    LEFT JOIN
      g5_shop_category y ON x.ca_id = y.ca_id
    WHERE
      d.dc_status in ('2', '3') and
      d.entId = '{$member['mb_entId']}' and
      d.penLtmNum = '{$penLtmNum}' and
      y.ca_id = '{$ca_id}' and
      $end_date >= DATE_SUB(NOW(), INTERVAL y.ca_limit_month MONTH)
  ");

  $end_date = "
  CASE
    WHEN sd_gubun = '01'
    THEN
      sd_rent_date
    ELSE
      DATE_ADD(sd_sale_date, INTERVAL y.ca_limit_month MONTH)
  END
  ";
  $upload_result = sql_fetch("
    SELECT
      count(*) as current
    FROM
      stock_data_upload d
    LEFT JOIN
      g5_shop_item x ON sd_it_code = x.ProdPayCode and
      (
        ( sd_gubun = '00' and x.ca_id like '10%' ) or
        ( sd_gubun = '01' and x.ca_id like '20%' )
      )
    LEFT JOIN
      g5_shop_category y ON x.ca_id = y.ca_id
    WHERE
      d.mb_id = '{$member['mb_id']}' and
      sd_status = 1 and
      d.sd_pen_ltm_num = '{$penLtmNum}' and
      y.ca_id = '{$ca_id}' and
      $end_date >= DATE_SUB(NOW(), INTERVAL y.ca_limit_month MONTH)
  ");

  $eform_current = $eform_result ? $eform_result['current'] : 0;
  $upload_current = $upload_result ? $upload_result['current'] : 0;

  $limit['current'] = $eform_current + $upload_current;

  return $limit;
}

// 상품분류별 내구연한 - 수급자 주문 별 구매가능 개수 체크
function get_pen_order_limit($penId, $od_id) {
  global $g5;

  $pen = get_recipient($penId);
  if(!$pen) {
    return [];
  }

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
    $limit = get_pen_category_limit($pen['penLtmNum'], $row['ca_id']);
  
    if($limit) {
  
      if($limit['current'] + $row['cnt'] > $limit['num']) { // 구매 가능한 수량 넘으면
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
          'month' => $limit['month'],
          'limit' => $limit['num'],
          'current' => $limit['current'],
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
		$sql = "UPDATE g5_firebase SET mb_id = '{$member['mb_id']}', login_yn = 1, updated_at = now() WHERE fcm_token = '{$fcm_token}'";
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

function get_token_by_ob_id_and_ct_id($mb_id) {

  if (!$mb_id) return array();

  $sql = "SELECT fcm_token FROM g5_firebase WHERE mb_id = '{$mb_id}' AND login_yn = 1";
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
// + 대여중인상품(02) 대여기간 종료되면 자동으로 대여가능(01)로 변경하기
function expired_rental_item_clean($prodId) {
  global $g5, $member;

  $item = sql_fetch(" SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = '$prodId' ");

  /* 
  * 대여중인상품(02) 대여기간 종료되면 자동으로 대여가능(01)로 변경하기
  */
  $result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, array(
    'entId' => $member['mb_entId'],
    'usrId' => $member['mb_id'],
    'prodId' => $prodId,
    'stateCd' => ['02']
  ));
  if($result['errorYN'] == 'N' && $result['data']) {
    $stock_update = [];
    $now = time();
    foreach($result['data'] as $stock) {
      $end_time = strtotime($stock['ordLendEndDtm']);

      if(!$stock['ordLendEndDtm'] || !$end_time) continue;

      // 대여기간이 지났으면
      if($now >= $end_time) {
        $stock_update[] = array(
          'stoId' => $stock['stoId'],
          'prodBarNum' => $stock['prodBarNum'],
          'stateCd' => '01'
        );
      }
    }
    api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
      'entId' => $member['mb_entId'],
      'usrId' => $member['mb_id'],
      'prods' => $stock_update,
    ));
  }
  
  /* 
  * 대여 내구연한: 판매가능기간 지났으면 재고상태 05(기타)로 변경하기
  */
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

function ct_warehouse_update($ct_id, $ct_warehouse){

    $sql = "SELECT * FROM `g5_shop_cart` WHERE ct_id = '{$ct_id}'";
    $cart = sql_fetch($sql);

    $sql_ct = "UPDATE `g5_shop_cart` SET `ct_warehouse`='".$ct_warehouse."' where `ct_id` = '".$ct_id."'";
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

function get_recipient_grade_per_year($pen_id, $penExpiStDtm = null) {
  global $member;

	if (!$pen_id) {
		return false;
	}

	// 등급정보
	$grade = get_recipient_grade($pen_id);

	// 등급정보가 없으면 유효기간 시작일로 설정
	if (!$grade) {
    if(!$penExpiStDtm) {
      $send_data = [];
      $send_data['usrId'] = $member['mb_id'];
      $send_data['entId'] = $member['mb_entId'];
      $send_data['penId'] = $pen_id;

      $res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
      $penExpiStDtm = $res['data'][0]['penExpiStDtm'];
    }

		$exp_date = substr($penExpiStDtm, 4, 4);
	} else {
    $exp_date = $grade['pen_gra_apply_month'] . $grade['pen_gra_apply_day'];
  }

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

//수급자 판매 건수 가져오기
function get_recipient_contract_sell($pen_id) {
  global $member;

	if (!$pen_id) {
		return false;
	}

	// 판매 건수
	$contract_sell = sql_fetch("SELECT count(*) as cnt from g5_shop_order where od_penId = '{$pen_id}' and od_del_yn = 'N';");

	return array(
		'sell_count' => $contract_sell['cnt']
	);
}

// 사업소별 미수금 구하는 함수
// fr_date 값이 있을 경우 해당 일 까지의 이월잔액
// total_price_only: true - 총 구매액, false - 총 미수금
// current_month_only: true - 이번달 미수금 내역만 가져오기
function get_outstanding_balance($mb_id, $fr_date = null, $total_price_only = false, $current_month_only = false) {
  $where_date = '';
  $where_ledger_date = '';
  if($fr_date) {
    $where_date = " and od_time < '{$fr_date} 00:00:00' ";
    $where_ledger_date = " and lc_created_at < '{$fr_date} 00:00:00' ";
  }

  if($current_month_only) {
    $where_date = ' AND YEAR(od_time) = YEAR(CURRENT_DATE()) AND MONTH(od_time) = MONTH(CURRENT_DATE()) ';
  	$where_ledger_date = ' AND YEAR(lc_created_at) = YEAR(CURRENT_DATE()) AND MONTH(lc_created_at) = MONTH(CURRENT_DATE()) ';
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
      (o.od_send_cost + o.od_send_cost2) as price_d,
      0 as deposit
    FROM
      g5_shop_order o
    LEFT JOIN
      g5_shop_cart c ON o.od_id = c.od_id
    WHERE
      c.ct_status = '완료' and
      c.ct_qty - c.ct_stock_qty > 0 and
      o.od_send_cost + o.od_send_cost2 > 0
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

  # 쿠폰할인
  $coupon_price = "(o.od_cart_coupon + o.od_coupon + o.od_send_coupon)";
  $sql_sales_coupon = "
    SELECT
      1 as ct_qty,
      (-$coupon_price) as price_d,
      0 as deposit
    FROM
      g5_shop_order o
    LEFT JOIN
      g5_shop_cart c ON o.od_id = c.od_id
    WHERE
      c.ct_status = '완료' and
      c.ct_qty - c.ct_stock_qty > 0 and
      $coupon_price > 0
  ";

  # 포인트결제
  $sql_sales_point = "
    SELECT
      1 as ct_qty,
      (-o.od_receipt_point) as price_d,
      0 as deposit
    FROM
      g5_shop_order o
    LEFT JOIN
      g5_shop_cart c ON o.od_id = c.od_id
    WHERE
      c.ct_status = '완료' and
      c.ct_qty - c.ct_stock_qty > 0 and
      o.od_receipt_point > 0
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
      ({$sql_sales_coupon} {$sql_search} {$where_date} GROUP BY o.od_id)
      UNION ALL
      ({$sql_sales_point} {$sql_search} {$where_date} GROUP BY o.od_id)
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

function get_tutorials($all = false) {
	global $g5, $member;
	$completed = false;

  return false;
	
	if (!$member['mb_id']) {
		return false;
	}

	$tutorials = array(
		'step' => array(),
	);

  $t_type_sql = "";

  if (!$all) {
    $t_type_sql = " AND t_type IN (
      'recipient_add',
      'recipient_order',
      'document',
      'claim'
    ) ";
  }

	$sql = "SELECT * FROM tutorial
  WHERE
    mb_id = '{$member['mb_id']}'
    {$t_type_sql}
  ORDER BY created_at ASC";
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
		// set_tutorial();
		// return get_tutorials();
    return false;
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

	$sql = "SELECT * FROM tutorial WHERE mb_id = '{$member['mb_id']}' AND t_type = '{$type}'";
	$result = sql_fetch($sql);

	$sql = '';

	if ($result['t_id']) {
		$sql .= "UPDATE tutorial SET
			mb_id = '{$member['mb_id']}',
			t_type = '{$type}',
			t_state = '{$state}',
			updated_at = now()
		";
		if ($data) {
			$sql .= ", t_data = '{$data}'";
		}
		$sql .= " WHERE t_id = '{$result['t_id']}' ";
		
	} else {
    $sql .= "INSERT INTO tutorial SET
			mb_id = '{$member['mb_id']}',
			t_type = '{$type}',
			t_state = '{$state}'
		";
		if ($data) {
			$sql .= ", t_data = '{$data}'";
		}
  }

	return sql_query($sql);
}

// 파트너 회원 목록 가져오기
function get_partner_members($partner_type = null) {
  $partner_type_where = '';

  if ($partner_type) {
    $partner_type_where = " and mb_partner_type like '%{$partner_type}%'";
  }

  $sql = "
    SELECT
      *
    FROM
      g5_member
    WHERE
      mb_type = 'partner' and
      mb_partner_auth = 1 and
      mb_partner_date >= NOW()
      {$partner_type_where}
  ";

  $result = sql_query($sql);

  $ret = [];

  if(!$result)
    return $ret;

  while($row = sql_fetch_array($result)) {
    $ret[] = $row;
  }

  return $ret;
}

// 파트너 거래처원장
function get_partner_ledger($mb_id, $fr_date = '', $to_date = '', $sel_field = '', $search = '', $sql_search = '', $contain_purchase = true) {
  $where_order = $where_ledger = '';

  # 기간
  if($fr_date && $to_date) {
    $where_order = " and (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
    $where_ledger = " and (pl_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  }

  # 주문내역
  $sql_order = "
    SELECT
     'g5_shop' as table_type,
      od_time,
      c.od_id,
      mb_entNm,
      mb_partner_type,
      it_name,
      ct_option,
      ct_qty,
      ct_direct_delivery_price as price_d,
      ROUND (
         ct_direct_delivery_price / 1.1
      ) * ct_qty as price_d_p,
      ROUND (
        ct_direct_delivery_price / 1.1 / 10
      ) * ct_qty as price_d_s,
      0 as deposit,
      od_b_name,
      ct_id,
      ct_warehouse
    FROM
      g5_shop_cart c
    LEFT JOIN
      g5_shop_order o ON c.od_id = o.od_id
    LEFT JOIN
      g5_member m ON c.mb_id = m.mb_id
    WHERE
      ct_status = '완료' and
      od_del_yn = 'N' and
      ct_is_direct_delivery IN(1, 2) and
      ct_direct_delivery_partner = '{$mb_id}'
      {$sql_search}
      {$where_order}
  ";

  $sql_purchase_order = "
    SELECT
      'purchase' as table_type,
      od_time,
      c.od_id,
      mb_entNm,
      mb_partner_type,
      it_name,
      ct_option,
      ct_qty,
      IF(io_type = 1, io_price, (ct_price + io_price)) AS price_d,
      ROUND (
         IF(io_type = 1, io_price, (ct_price + io_price)) / 1.1
      ) * ct_qty as price_d_p,
      ROUND (
        IF(io_type = 1, io_price, (ct_price + io_price)) / 1.1 / 10
      ) * ct_qty as price_d_s,
      0 as deposit,
      od_b_name,
      ct_id,
      ct_warehouse
    FROM
      purchase_cart c
    LEFT JOIN
      purchase_order o ON c.od_id = o.od_id
    LEFT JOIN
      g5_member m ON c.mb_id = m.mb_id
    WHERE
      ct_status = '입고완료' and
      od_del_yn = 'N' and
      ct_supply_partner = '{$mb_id}'
      {$sql_search}
      {$where_order}
  ";

  # 입금/출금
  $sql_ledger = "
    SELECT
      'partner_ledger' as table_type,
      pl_created_at as od_time,
      '' as od_id,
      m.mb_entNm,
      m.mb_partner_type,
      (
        CASE
          WHEN pl_type = 1
          THEN '입금'
          WHEN pl_type = 2
          THEN '환수'
        END
      ) as it_name,
      pl_memo as ct_option,
      1 as ct_qty,
      (
        CASE
          WHEN pl_type = 2
          THEN pl_amount
          ELSE 0
        END
      ) as price_d,
      0 as price_d_p,
      0 as price_d_s,
      (
        CASE
          WHEN pl_type = 1
          THEN pl_amount
          ELSE 0
        END
      ) as deposit,
      '' as od_b_name,
      '' as ct_id,
      '' as ct_warehouse
    FROM
      partner_ledger l
    LEFT JOIN
      g5_member m ON l.mb_id = m.mb_id
    WHERE
      l.mb_id = '{$mb_id}'
      {$where_ledger}
  ";

  $sql_common = "
  FROM
    (
      ({$sql_order} {$where_order})
      UNION ALL
      ({$sql_ledger} {$where_ledger})
    ) u
  ";

  if ($contain_purchase) {
    $sql_common = "
  FROM
    (
      ({$sql_order} {$where_order})
      UNION ALL
      ({$sql_purchase_order} {$where_order})
      UNION ALL
      ({$sql_ledger} {$where_ledger})
    ) u
  ";
  }

  # 구매액 합계 계산
  $total_result = sql_fetch("SELECT sum(price_d * ct_qty) as total_price, count(*) as cnt {$sql_common}", true);
  $total_price = $total_result['total_price'];

  $result = sql_query("
    SELECT
      u.*,
      (price_d * ct_qty) as sales
    {$sql_common}
    ORDER BY
      od_time asc,
      od_id asc
  ");

  # 이월잔액
  $carried_balance = get_partner_outstanding_balance($mb_id, $fr_date, false, false, $contain_purchase);

  $ledger = [];
  $balance = $carried_balance;
  while($row = sql_fetch_array($result)) {
    $balance += ($row['price_d'] * $row['ct_qty']);
    $balance -= ($row['deposit']);
    $row['balance'] = $balance;
    $ledger[] = $row;
  }

  # 검색어
  $sel_field = in_array($sel_field, ['mb_entNm', 'it_name', 'od_id']) ? $sel_field : '';
  $search = get_search_string($search);
  if($sel_field && $search) {
    // 검색결과 필터링
    $ledger = array_values(array_filter($ledger, function($v) {
      global $sel_field, $search;
      $pattern = '/.*'.preg_quote($search).'.*/i';
      return preg_match($pattern, $v[$sel_field]);
    }));
  }

  return array(
    'total_price' => $total_price,
    'carried_balance' => $carried_balance,
    'ledger' => $ledger
  );
}

// 파트너 거래처원장 - 미수금 합계
// fr_date 값이 있을 경우 해당 일 까지의 이월잔액
// total_price_only: true - 총 구매액, false - 총 미수금
function get_partner_outstanding_balance($mb_id, $fr_date = null, $total_price_only = false, $current_month_only = false, $contain_purchase = true) {
  global $g5;

  $where_date = '';
  $where_ledger_date = '';
  if($fr_date) {
    $where_date = " and od_time < '{$fr_date} 00:00:00' ";
    $where_ledger_date = " and pl_created_at < '{$fr_date} 00:00:00' ";
  }

  if($current_month_only) {
    $where_date = ' AND YEAR(od_time) = YEAR(CURRENT_DATE()) AND MONTH(od_time) = MONTH(CURRENT_DATE()) ';
  	$where_ledger_date = ' AND YEAR(pl_created_at) = YEAR(CURRENT_DATE()) AND MONTH(pl_created_at) = MONTH(CURRENT_DATE()) ';
  }

  # 주문내역
  $sql_order = "
    SELECT
      ct_qty,
      ct_direct_delivery_price as price_d,
      0 as deposit
    FROM
      {$g5['g5_shop_cart_table']} c
    LEFT JOIN
      {$g5['g5_shop_order_table']} o ON c.od_id = o.od_id
    WHERE
      ct_status = '완료' and
      od_del_yn = 'N' and
      ct_is_direct_delivery IN(1, 2) and
      ct_direct_delivery_partner = '{$mb_id}'
  ";

  # 발주내역
  $sql_purchase_order = "
    SELECT
      ct_qty,
      IF(io_type = 1, io_price, (ct_price + io_price)) as price_d,
      0 as deposit
    FROM
      purchase_cart c
    LEFT JOIN
      purchase_order o ON c.od_id = o.od_id
    WHERE
      ct_status = '입고완료' and
      od_del_yn = 'N' and
      ct_supply_partner = '{$mb_id}'
  ";

  # 입금/출금
  $sql_ledger = "
    SELECT
      1 as ct_qty,
      (
        CASE
          WHEN pl_type = 2
          THEN pl_amount
          ELSE 0
        END
      ) as price_d,
      (
        CASE
          WHEN pl_type = 1
          THEN pl_amount
          ELSE 0
        END
      ) as deposit
    FROM
      partner_ledger l
    WHERE
      mb_id = '{$mb_id}'
  ";

  $sql = "
    SELECT
      sum(ct_qty * price_d) as total_price,
      sum(deposit) as total_deposit
    FROM
    (
      ({$sql_order} {$where_date})
      UNION ALL
      ({$sql_ledger} {$where_ledger_date})
    ) u
  ";

  if ($contain_purchase) {
    $sql = "
    SELECT
      sum(ct_qty * price_d) as total_price,
      sum(deposit) as total_deposit
    FROM
    (
      ({$sql_order} {$where_date})
      UNION ALL
      ({$sql_purchase_order} {$where_date})  
      UNION ALL
      ({$sql_ledger} {$where_ledger_date})
    ) u
  ";
  }

  $result = sql_fetch($sql);

  $total_price = $result['total_price'] ?: 0;
  $total_deposit = $result['total_deposit'] ?: 0;

  if($total_price_only)
    return $total_price;
  else
    return $total_price - $total_deposit;
}

// 비즈톡 API 요청 함수
function biztalk_api_call($url, $data = null, $token  = null) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, BIZTALK_API_HOST.$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  if($data) {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
  }
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  $header = [
    'Accept: application/json',
    'Content-Type: application/json'
  ];
  if($token) $header[] = 'bt-token: '.$token;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

  $res = curl_exec($ch);
  curl_close($ch);

  return json_decode($res, true);
}

// 비즈톡 토큰 발급
function get_biztalk_token() {
  $result = biztalk_api_call('/v2/auth/getToken', array(
    'bsid' => BIZTALK_API_BS_ID,
    'passwd' => BIZTALK_API_BS_PWD
  ));

  return $result['token'] ?: null;
}

// 비즈톡 알림톡 전송
function send_alim_talk($msgIdx, $recipient, $tmpltCode, $message, $attach = null, $token = null) {
  if(!$token)
    $token = get_biztalk_token();

  if(!$token) return null;

  $data = array(
    'msgIdx' => $msgIdx,
    'countryCode' => '82',
    'recipient' => $recipient,
    'senderKey' => BIZTALK_API_SENDER_KEY,
    'resMethod' => 'PUSH',
    'tmpltCode' => $tmpltCode,
    'message' => $message
  );

  if($attach) $data['attach'] = $attach;

  $result = biztalk_api_call('/v2/kko/sendAlimTalk', $data, $token);

  return $result;
}

// 비즈톡 전송 결과 확인
function get_biztalk_result() {
  $token = get_biztalk_token();

  if(!$token) return null;

  $result = biztalk_api_call('/v2/kko/getResultAll', null, $token);

  return $result;
}

// 수급자 연결아이디 가져오기 (penId)
function get_pen_ent_by_pen_id($penId, $ent_mb_id = null) {
  global $member;

  if(!$penId)
    return null;

  if(!$ent_mb_id)
    $ent_mb_id = $member['mb_id'];

  $result = sql_fetch(" SELECT * FROM recipient_ent WHERE ent_mb_id = '{$ent_mb_id}' AND penId = '{$penId}' ");
  if($result['pen_mb_id'])
    return $result;

  return null;
}

// 수급자 연결아이디 가져오기 (pen_mb_id)
function get_pen_ent_by_pen_mb_id($pen_mb_id, $ent_mb_id = null) {
  global $member;

  $pen_mb_id = get_search_string($pen_mb_id);

  if(!$pen_mb_id)
    return null;

  if(!$ent_mb_id) {
    $result = sql_query(" SELECT * FROM recipient_ent WHERE pen_mb_id = '{$pen_mb_id}' ");
    $data = [];
    while($row = sql_fetch_array($result)) {
      $data[] = $row;
    }
    return $data;
  }

  $ent_mb_id = get_search_string($ent_mb_id);

  $result = sql_fetch(" SELECT * FROM recipient_ent WHERE ent_mb_id = '{$ent_mb_id}' AND pen_mb_id = '{$pen_mb_id}' ");
  if($result['pen_mb_id'])
    return $result;

  return null;
}

function calc_order_price($od_id)
{
  global $g5;

  $sql = "SELECT * FROM {$g5['g5_shop_cart_table']} where od_id = {$od_id}";
  $od = sql_fetch($sql);

  $sql = " select C.it_id,
              C.ct_qty,
              C.it_name,
              C.io_id,
              C.io_type,
              C.ct_option,
              C.ct_qty,
              C.ct_id,
              I.it_time,
              I.prodSupYn,
              I.ProdPayCode as prodPayCode,
              I.it_delivery_cnt,
              I.it_delivery_price,
              I.it_option_subject
        from {$g5['g5_shop_cart_table']} C
        left join {$g5['g5_shop_item_table']} I on C.it_id = I.it_id
        where od_id = '$od_id'
        and ct_select = '1' ";
    
  $result = sql_query($sql);
  $carts = [];
  $it_ids = [];
  for ($i=0; $row=sql_fetch_array($result); $i++) {
    $carts[] = $row;

    if (!in_array($row['it_id'], $it_ids)) {
      $it_ids[] = $row['it_id'];
    }
  }

  $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as od_price,
    COUNT(distinct it_id) as cart_count,
    SUM(ct_discount) as od_discount,
    ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn
    from {$g5['g5_shop_cart_table']} MT where od_id = '$od_id' and ct_select = '1' ";
  $row = sql_fetch($sql);

  $od_member = get_member($od['mb_id']);

  $tot_ct_price = $row['od_price'];
  $tot_ct_discount = ($row["od_discount"]) ? $row["od_discount"] : 0;
  $cart_count = $row['cart_count'];
  $tot_od_price = $tot_ct_price;

  $send_cost = get_sendcost($od_id, 1, 1);

  $zipcode = $od['od_b_zip1'] . $od['od_b_zip2'];
  $sql = " select sc_id, sc_price from {$g5['g5_shop_sendcost_table']} where sc_zip1 <= '$zipcode' and sc_zip2 >= '$zipcode' ";
  $tmp = sql_fetch($sql);
  if (!$tmp['sc_id']) {
    $send_cost2 = 0;
  } else {

    $total_item_sc_price = 0;

    $it_sc_add_sendcost = 'it_sc_add_sendcost';
    if ($od_member['mb_type'] == 'partner') {
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

  if ( $od['od_delivery_type'] != 'delivery1' ) {
    $send_cost = 0;
    $send_cost2 = 0;
  }

  $order_price = $tot_od_price + $send_cost + $send_cost2 - $od['od_send_coupon'] - $od['od_receipt_point'];

  return $order_price;
}

function eroumcare_ajax_paging($function_name, $write_pages, $cur_page, $total_page, $url, $add='', $first='<i class="fa fa-angle-double-left"></i>', $prev='<i class="fa fa-angle-left"></i>', $next='<i class="fa fa-angle-right"></i>', $last='<i class="fa fa-angle-double-right"></i>') {

	$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
	//$url = preg_replace('#(&amp;)?page=[0-9]*#', '', $url);
	//$url .= substr($url, -1) === '?' ? 'page=' : '&amp;page=';

	if(!$cur_page) $cur_page = 1;
	if(!$total_page) $total_page = 1;

	$ajax = ($css) ? ' class="'.$css.'"' : ''; // Ajax용 클래스

	$str = '';
	if($first) {
		if ($cur_page < 2) {
			$str .= '<li class="disabled"><a>'.$first.'</a></li>';
		} else {
			$str .= '<li><a href="javascript:;" onclick="'.$function_name.'(\''.$url.'1'.$add.'\', \'1\');">'.$first.'</a></li>';
		}
	}

	$start_page = (((int)(($cur_page - 1 ) / $write_pages)) * $write_pages) + 1;
	$end_page = $start_page + $write_pages - 1;

	if ($end_page >= $total_page) { 
		$end_page = $total_page;
	}

	if ($start_page > 1) { 
		$str .= '<li><a href="javascript:;" onclick="'.$function_name.'(\''.$url.($start_page-1).$add.'\', \'1\');">'.$prev.'</a></li>';
	} else {
		$str .= '<li class="disabled"><a>'.$prev.'</a></li>'; 
	}

	if ($total_page > 0){
		for ($k=$start_page;$k<=$end_page;$k++){
			if ($cur_page != $k) {
				$str .= '<li><a href="javascript:;" onclick="'.$function_name.'(\''.$url.$k.$add.'\', \'1\');">'.$k.'</a></li>';
			} else {
				$str .= '<li class="active"><a>'.$k.'</a></li>';
			}
		}
	}

	if ($total_page > $end_page) {
		$str .= '<li><a href="javascript:;" onclick="'.$function_name.'(\''.$url.($end_page+1).$add.'\', \'1\');">'.$next.'</a></li>';
	} else {
		$str .= '<li class="disabled"><a>'.$next.'</a></li>';
	}

	if($last) {
		if ($cur_page < $total_page) {
			$str .= '<li><a href="javascript:;" onclick="'.$function_name.'(\''.$url.($total_page).$add.'\', \'1\');">'.$last.'</a></li>';
		} else {
			$str .= '<li class="disabled"><a>'.$last.'</a></li>';
		}
	}

	return $str;
}

function get_custom_ct_status_text($ct_status) {
  $ct_status_text = '';

  switch($ct_status) {
    case '보유재고등록': $ct_status_text="보유재고등록"; break;
    case '재고소진': $ct_status_text="재고소진"; break;
    case '작성': $ct_status_text="작성"; break;
    case '주문무효': $ct_status_text="주문무효"; break;
    case '취소': $ct_status_text="주문취소"; break;
    case '주문': $ct_status_text="주문접수"; break;
    case '입금': $ct_status_text="입금완료"; break;
    case '준비': $ct_status_text="상품준비"; break;
    case '출고준비': $ct_status_text="출고준비"; break;
    case '배송': $ct_status_text="출고완료"; break;
    case '완료': $ct_status_text="배송완료"; break;
  }

  return $ct_status_text ?: $ct_status;
}

function get_average_sales_qty($it_id, $month = 3) {
 $sql = "
  SELECT SUM(ct_qty) as total_qty
  FROM g5_shop_cart
  WHERE 
    it_id = '{$it_id}' AND
    (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL {$month} MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
	  ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
	";

 $result = sql_fetch($sql);

 return (int) round($result['total_qty'] / $month);
}

function check_auth($mb_id, $menu, $auth) {
  global $is_admin;

  if ($is_admin == 'super') {
    return true;
  }

  $sql = "
    SELECT * FROM g5_auth WHERE mb_id = '{$mb_id}' AND au_menu = '{$menu}' AND au_auth LIKE '%{$auth}%'
  ";

  $row = sql_fetch($sql);

  if ($row) {
    return true;
  } else {
    return false;
  }
}


function get_manage_stock_count($type) {
  $common_ct_status = "('주문', '입금', '준비', '출고준비', '배송', '완료')";

  // 안전재고
  if ($type == 1) {
    $where = " AND (sum_ws_qty <= safe_min_stock_qty) AND sum_ws_qty != 0 AND safe_min_stock_qty != 0 ";
  }

  // 최대재고
  if ($type == 2) {
    $where = " AND (sum_ws_qty > safe_min_stock_qty AND sum_ws_qty <= safe_max_stock_qty) AND sum_ws_qty != 0 AND safe_min_stock_qty != 0 ";
  }

  // 악성재고
  if ($type == 3) {
    $where = " AND (sum_ws_qty > safe_min_stock_qty) AND sum_ws_qty != 0 AND safe_min_stock_qty != 0 ";
  }

  // 1 안전재고, 2 최대재고, 3 악성재고
  $sql = "
  SELECT count(*) AS cnt
  FROM 
    (SELECT
      it.it_id,
      it.it_name,
      io.io_id, 
      ws.ws_option, 
      IFNULL(sum(ws.ws_qty), '0') AS sum_ws_qty,
      CASE
        WHEN io.io_stock_manage_min_qty IS NOT NULL AND io.io_stock_manage_min_qty > 0
          THEN io.io_stock_manage_min_qty 
        WHEN it.it_stock_manage_min_qty IS NOT NULL AND it.it_stock_manage_min_qty > 0
          THEN it.it_stock_manage_min_qty
        ELSE
          IFNULL(ROUND((SELECT sum(ct_qty) FROM g5_shop_cart
                WHERE (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL 3 MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
                  ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
                AND ct_status IN {$common_ct_status}
                AND it_id = it.it_id AND io_id = IFNULL(io.io_id, '')) / 3 * 0.5), 0)
      END AS safe_min_stock_qty,
      CASE
        WHEN io.io_stock_manage_max_qty IS NOT NULL AND io.io_stock_manage_max_qty > 0
          THEN io.io_stock_manage_max_qty 
        WHEN it.it_stock_manage_max_qty IS NOT NULL AND it.it_stock_manage_max_qty > 0
          THEN it.it_stock_manage_max_qty
        ELSE
          IFNULL(ROUND((SELECT sum(ct_qty) FROM g5_shop_cart
                WHERE (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL 3 MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
                  ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
                AND ct_status IN {$common_ct_status}
                AND it_id = it.it_id AND io_id = IFNULL(io.io_id, '')) / 3 * 1.5), 0)
      END AS safe_max_stock_qty
    FROM 
      g5_shop_item it
      LEFT JOIN g5_shop_item_option AS io ON it.it_id = io.it_id AND (io.io_type = '0' AND io.io_use = '1')
      LEFT JOIN warehouse_stock AS ws ON (it.it_id = ws.it_id AND IFNULL(io.io_id, '') = ws.io_id) AND (ws.ws_del_yn = 'N')
    GROUP BY it.it_id, io.io_id
    ORDER BY NULL
    ) AS t
  WHERE 1 {$where}
  ";

  return sql_fetch($sql)['cnt'];
}

function get_purchase_order_by_it_id($it_id, $ct_status = '발주완료') {
  $sql = "
    SELECT *
    FROM purchase_cart
    WHERE it_id = '{$it_id}' AND ct_status = '{$ct_status}'
  ";

  $result = sql_query($sql);

  $array = [];

  while ($row = sql_fetch_array($result)) {
    $array[] = $row;
  }

  return $array;
}

function count_item_option($it_id) {
  $sql = "
    SELECT count(*) AS cnt
    FROM g5_shop_item it
    LEFT JOIN g5_shop_item_option io ON it.it_id = io.it_id
    WHERE it.it_id = '{$it_id}' AND io.io_type = 0
  ";

  return sql_fetch($sql)['cnt'];
}

function replace_querystring($qstr, $key, $value) {
  $qstr_arr = [];

  $qstr_conv = str_replace('&amp;' , '&', $qstr);
  parse_str($qstr_conv, $qstr_arr);
  $qstr_arr[$key] = $value;

  return http_build_query($qstr_arr);
}

function convert_item_option_to_text($it_option_subject, $io_id) {
  if (!$io_id) {
    return '';
  }

  $io_value = '';

  $it_option_subjects = explode(',', $it_option_subject);
  $io_ids = explode(chr(30), $io_id);

  for ($g = 0; $g < count($io_ids); $g++) {
    if ($g > 0) {
      $io_value .= ' / ';
    }
    $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
  }

  return sql_real_escape_string(strip_tags($io_value));
}

function get_stock_item_info($it_id, $io_id) {
  $where = "WHERE it_id = '{$it_id}' ";

  if ($io_id) {
    $where .= " AND io_id = '{$io_id}' ";
  }

  $use_warehouse_where_sql = get_use_warehouse_where_sql();
  $sql = "
    SELECT
     T.*
    FROM
    (SELECT
      (SELECT 
        IFNULL(sum(ws_qty) - sum(ws_scheduled_qty), 0) 
      FROM warehouse_stock 
      WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND ws_del_yn = 'N' {$use_warehouse_where_sql}) AS sum_ws_qty,
      (SELECT count(*)
        FROM g5_cart_barcode
        WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND bc_del_yn = 'N' AND ct_id = '0') AS sum_barcode_qty,
      (SELECT count(*)
        FROM g5_cart_barcode
        WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND bc_del_yn = 'N' AND ct_id = '0' AND checked_at IS NOT NULL) AS sum_checked_barcode_qty,
      a.*,
      b.io_type,
      b.io_id
    FROM
      (SELECT
        it_id,
        it_name,
        it_use,
        it_option_subject,
        ProdPayCode
      FROM g5_shop_item i) AS a
    LEFT JOIN (SELECT * from g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS b ON (a.it_id = b.it_id)) AS T 
    {$where}
  ";

  return sql_fetch($sql);
}

function get_use_warehouse_where_sql($use_and = true) {
  $sql = "SELECT * FROM warehouse WHERE wh_use_yn = 'Y'";
  $result = sql_query($sql);
  $wh_name_list = [];
  $where = ' ';

  if ($use_and) {
    $where = ' AND';
  }

  while ($row = sql_fetch_array($result)) {
    $wh_name_list[] = $row['wh_name'];
  }

  if (count($wh_name_list) > 0) {
    $where .= " wh_name in (";

    for ($i = 0; $i < count($wh_name_list); $i++) {
      $where .= "'{$wh_name_list[$i]}'";
      if ($i + 1 != count($wh_name_list)) {
        $where .= ", ";
      }
    }

    $where .= ") ";
  }

  return $where;
}
// 공단에서 받아온 데이터
// 20220926 황현지 수정
function get_rentalitem_deadline2($entId) {
	$sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE ent_id='{$entId}' AND ord_status='대여';";
	$result = sql_query($sql);
	
	$list_rental_dealine = array();
	$list_LTM = array();
	$timetoday = date('y-m-d');
	$today = new DateTime($timetoday);
  $list_Ltn_Num = array();

	while ($row = sql_fetch_array($result)) {

		$thatday = new DateTime(substr($row['ORD_END_DTM'],0,10));

    $date_dif = date_diff($today,$thatday)->days;
    if($today > $thatday) { $date_dif = $date_dif*(-1);} // 오늘을 기준으로 전후 30일을 모두 계산해오는 것을 막음
		if ($date_dif < 30 && $date_dif > 0){
      if(count($list_LTM) > 0){
        if(array_search($row['PEN_LTM_NUM'], $list_Ltn_Num) === false){
          $count_on_deadline++;
          $list_LTM[] = array('penLtmNum'=>$row['PEN_LTM_NUM'], 'total_price'=>$row['TOTAL_PRICE']); 
          $list_Ltn_Num[] = $row['PEN_LTM_NUM'];
        } else {
          $temp_index = count($list_LTM) - array_search($row['PEN_LTM_NUM'], $list_Ltn_Num);
          $list_LTM[$temp_index]['total_price'] = $list_LTM[$temp_index]['total_price']+$row['TOTAL_PRICE']; 
        }
      } else{
        $count_on_deadline++;
        $list_LTM[] = array('penLtmNum'=>$row['PEN_LTM_NUM'], 'total_price'=>$row['TOTAL_PRICE']); 
        $list_Ltn_Num[] = $row['PEN_LTM_NUM'];
      }
		}
		$thatday = null;
	}
	//print_r($list_LTM);

	return $list_LTM;
}

// 기존 데이터 오류 발생
function get_rentalitem_deadline($entId) {
	$sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE ent_id='{$entId}' AND ord_status='대여';";
	$result = sql_query($sql);
	
	$list_rental_dealine = array();
	$list_LTM = array();
	$timetoday = date('y-m-d');
	$today = new DateTime($timetoday);

	while ($row = sql_fetch_array($result)) {

		$thatday = new DateTime(substr($row['ORD_END_DTM'],0,10));
		if (date_diff($today,$thatday)->days < 30)
		{
			$count_on_deadline++;
			$list_LTM[] = array('penLtmNum'=>$row['PEN_LTM_NUM'], 'total_price'=>$row['TOTAL_PRICE']);
		}
		$thatday = null;
	}
	//print_r($list_LTM);

	return $list_LTM;
}


// 우리샵에서 판매/대여한 상품 
function get_contract_info($entId, $recipient_pl)
{
	//echo $entId;
	$sql = "
		SELECT 
			d.dc_id,
			d.dc_subject,
			d.dc_status,
			d.od_id,
			d.penNm,
			d.penLtmNum,
			d.penExpiDtm,
			d.dc_datetime,
			d.dc_sign_datetime,
			i.it_id,
			i.ca_name,
			i.it_name,
			i.it_code,
			i.it_barcode,
			i.it_qty,
			i.it_price 
		FROM 
			eform_document d 
		LEFT JOIN 
			eform_document_item i 
			ON d.dc_id = i.dc_id
    	WHERE
      		d.entId = '{$entId}'"; 

	$result = sql_query($sql);
	
	//$tmp = explode("-",$timeAppdate);

	$recipient_list = array();
	$idx = 0;
	$timetoday = date('y-m-d');
	$today = new DateTime($timetoday);
	while ($row = sql_fetch_array($result)) {
	  echo $row['penExpiDtm']."<br/>";
	  $timeExp = substr($row['penExpiDtm'],13,10);
	  $tmp = explode("-",$timeExp);
	  if (checkdate($tmp[1],$tmp[2],$tmp[0]) == false)
		continue;
	  $to = new DateTime($timeExp);
	  $timeAppdate= $tmp[0].$tmp[1].$tmp[2];
	
	  $timeBuy= substr($row['dc_datetime'],0,10);
	  $tmp = explode("-",$timeBuy);
	  if (checkdate($tmp[1],$tmp[2],$tmp[0]) == false)
		continue;
	  $from = new DateTime($timeBuy);
	  $timePurchase= $tmp[0].$tmp[1].$tmp[2];

	  //echo (int)$timeAppdate." : ".(int)$timePurchase."<br/>";

	  //echo date_diff($to,$from)->days."<br/>";
	  if (((int)$timePurchase < (int)$timeAppdate) && fmod((date_diff($to,$from)->days),365) < 365)  
	  {
		$recipient_pl[$row['penLtmNum']]["contract"]++;;
		$recipient_pl[$row['penLtmNum']]["transaction_amt"] += (int)$row["it_price"];
		if ((strcmp( $row['ca_name'], '수동휠체어') == 0) ||
			(strcmp( $row['ca_name'], '전동침대') == 0) ||
			(strcmp( $row['ca_name'], '수동침대') == 0) ||
			(strcmp( $row['ca_name'], '욕창예방 매트리스') == 0) ||
			(strcmp( $row['ca_name'], '이동욕조') == 0) ||
			(strcmp( $row['ca_name'], '목욕리프트') == 0) ||
			(strcmp( $row['ca_name'], '배회감지기') == 0) ||
			(strcmp( $row['ca_name'], '경사로(실외용)') == 0) )
		{
				$recipient_pl[$row['penLtmNum']]["rental"]++;;

		}
		else
		{
			$recipient_pl[$row['penLtmNum']]["purchase"]++;;
		}

	  }
	  $from = null;
	  $to = null;

	}
	//print_r($recipient_list);
	print_r($recipient_pl);

	return $recipient_pl;

}


function get_totalamt_pention($ltmNum) {


	//$sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE pen_ltm_num='{$ltmNum}' AND PEN_EXPI_ED_DTM >= CURDATE();";
	$sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE pen_ltm_num='{$ltmNum}' AND (CURDATE() between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM);";
	$result = sql_query($sql);

	//if($result->num_rows == 0);
	//	return -1; // no purchase history within the period

	$row = sql_fetch_array($result);
		
//	if ($row['PEN_BUDGET'] == null )
//		echo "<br/> pen Budget = ".$row['PEN_BUDGET'];


	return $row;
}

function get_updated_date_recipient($ltmNum) {

	//$sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE pen_ltm_num='{$ltmNum}' AND PEN_EXPI_ED_DTM >= CURDATE();";
	$sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE pen_ltm_num='{$ltmNum}' AND (CURDATE() between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM);";
	$result = sql_query($sql);

	//if($result->num_rows == 0);
	//	return -1; // no purchase history within the period

	$row = sql_fetch_array($result);
		
//	if ($row['PEN_BUDGET'] == null )
//		echo "<br/> pen Budget = ".$row['PEN_BUDGET'];

	return $row['MODIFY_DTM'];
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-02
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-14
 * 설명 : 특정 설치파트너 소속의 매니저 목록 조회
 * @param string $partner_mb_id
 * @param string $mb_type
 * @return mixed 
 */
function get_partner_member_list_by_partner_mb_id($partner_mb_id, $mb_type) {
  $sql = "SELECT * FROM `g5_member` WHERE mb_id = '$partner_mb_id';";
  $members_str = '{"members":{"all":"전체",';
  $mb_type = "";
  $result = sql_query($sql);

  while ($res_item = sql_fetch_array($result)) {
      $mb_type = $res_item['mb_type'];
  }

  $sql = "SELECT * FROM `g5_member` WHERE mb_manager = '$partner_mb_id';";
  $result = sql_query($sql);

  while ($res_item = sql_fetch_array($result)) {
      $members_str.= '"'.$res_item['mb_id'].'":"'.$res_item['mb_name'].'",';
  }
  $members_str.= "}";
  $members_str = str_replace(',}', '}', $members_str);
  $members_str.= ',"mb_type":"'.$mb_type.'"}';
  return json_decode($members_str);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-14
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-23
 * 설명 : 설치파트너 목록 조회
 * @param string $mb_type
 * @return mixed 
 */
function get_partner_list($mb_type) {
  $sql = "SELECT DISTINCT g5_member.mb_id, g5_member.mb_name
  FROM partner_inst_sts
  JOIN g5_member ON partner_inst_sts.partner_mb_id = g5_member.mb_id
  WHERE g5_member.mb_type = 'partner';";

  $result = sql_query($sql);
  $members_str = '{"members":{"all":"전체",';

  while ($res_item = sql_fetch_array($result)) {
    $members_str.= '"'.$res_item['mb_id'].'":"'.$res_item['mb_name'].'",';
  }

  $members_str.= "}";
  $members_str = str_replace(',}', '}', $members_str);
  $members_str.= ',"mb_type":"'.$mb_type.'"}';
  return json_decode($members_str);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-02
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-28
 * 설명 : 특정 사업소의 수급자 목록 조회(주문 일정이 있는 수급자에 한정하여)
 * @param string $ent_md_id : 사업소 mb_id
 * @return mixed
 */
function get_partner_member_list_by_ent_mb_id_and_partner_mb_id($ent_md_id) {
  $sql = "SELECT * FROM g5_member WHERE mb_id = '$ent_md_id';";
  $result = sql_fetch($sql);
  $mb_type = $result['mb_type'];
  
  $manager_str = '{"members":{"all":"전체",';

  $sql = "SELECT DISTINCT od_b_name, od_b_hp FROM partner_inst_sts WHERE od_mb_id = '$ent_md_id' AND status != '주문' AND status != '주문무효';";
  $result = sql_query($sql);
  while ($res_item = sql_fetch_array($result)) {
    $manager_str.= '"'.$res_item['od_b_name'].'":"'.$res_item['od_b_name'].'",';
  }
  $manager_str.= "}";
  $manager_str = str_replace(',}', '}', $manager_str);
  $manager_str.= ',"mb_type":"'.$mb_type.'"}';
  return json_decode($manager_str);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-07
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-28
 * 설명 : 설치파트너 매니저 설치 일정 생성 여부 확인
 * @param integer $od_id
 * @return boolean 
 */
function exit_partner_install_schedule($od_id) {
  $sql = "SELECT id FROM partner_inst_sts WHERE od_id = $od_id;";
  $result = sql_query($sql);
  $sql = "SELECT ct_id FROM g5_shop_cart WHERE od_id = $od_id AND (ct_status = '출고준비' OR ct_status = '완료');";
  $result_cart = sql_query($sql);
  return mysqli_num_rows($result) == mysqli_num_rows($result_cart) && mysqli_num_rows($result) > 0;
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-09
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-09
 * 설명 : 설치파트너 매니저 설치 일정 중복 확인
 * @param string $partner_manager_mb_id
 * @param string $delivery_date 포맷 : YYYY-MM-DD
 * @param string $delivery_datetime 포맷 : hh:mm
 * @return boolean 
 */
function duplicate_partner_install_schedule($partner_manager_mb_id, $delivery_date, $delivery_datetime) {
  $sql = "SELECT 
  id 
  FROM `partner_inst_sts` 
  WHERE partner_manager_mb_id = '$partner_manager_mb_id' 
  AND delivery_date = '$delivery_date' 
  AND delivery_datetime = '$delivery_datetime';";
  $result = sql_query($sql);
  return mysqli_num_rows($result) == 0;
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-21
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-23
 * 설명 : 설치파트너 매니저 설치 불가일 중복 확인
 * @param string $partner_manager_mb_id
 * @param string $delivery_date 포맷 : YYYY-MM-DD
 * @return boolean 
 */
function duplicate_partner_deny_schedule($partner_manager_mb_id, $delivery_date) {
  $sql = "SELECT 
  id 
  FROM `partner_manager_deny_schedule` 
  WHERE partner_manager_mb_id = '$partner_manager_mb_id' 
  AND deny_date = '$delivery_date';";
  $result = sql_query($sql);
  return mysqli_num_rows($result) == 0;
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-02
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-12-27
 * 설명 : 설치파트너 매니저 설치 일정 생성
 * @param integer $od_id
 * @return boolean
 */
function create_partner_install_schedule($od_id) {
  $sql = "SELECT
    ct.ct_status, 
    ct.ct_id,
    ct.it_name,
    ct.prodMemo, 
    ct.ct_is_direct_delivery, 
    od.od_id, 
    od.od_b_hp, 
    od.od_b_name,
    od.od_b_addr1, 
    od.od_b_addr2, 
    mb.mb_id, 
    mb.mb_entNm
  FROM
  g5_shop_cart AS ct
  LEFT JOIN g5_shop_order AS od ON ct.od_id = od.od_id
  LEFT JOIN g5_member AS mb ON mb.mb_id = od.mb_id
  WHERE od.od_id = $od_id AND 
  ct.ct_is_direct_delivery = 2 AND 
  (ct.ct_status = '준비' OR ct.ct_status = '출고준비' OR ct.ct_status = '완료' OR ct.ct_status = '배송');";
  $cart_result = sql_query($sql);
  if (mysqli_num_rows($cart_result) < 1) return false;

  if (strlen($delivery_datetime) <= 2) {
    $delivery_datetime .= ":00";
  }

  $sql = "INSERT INTO `partner_inst_sts` 
  (
    status, 
    ct_id, 
    it_name, 
    od_id, 
    od_mb_id,
    od_mb_ent_name, 
    od_b_name, 
    od_b_hp, 
    od_b_addr1, 
    od_b_addr2, 
    prodMemo
  ) VALUES ";
  while ($cart = sql_fetch_array($cart_result)) {
    if ($cart["ct_is_direct_delivery"] == '2') {
      if ($cart["ct_status"] == '완료') {
        $sql = $sql."('완료',"
        ."'".$cart["ct_id"]."',"
        ."'".$cart["it_name"]."',"
        ."'".$cart["od_id"]."',"
        ."'".$cart["mb_id"]."',"
        ."'".$cart["mb_entNm"]."',"
        ."'".$cart["od_b_name"]."',"
        ."'".$cart["od_b_hp"]."',"
        ."'".$cart["od_b_addr1"]."',"
        ."'".$cart["od_b_addr2"]."',"
        ."'".$cart["prodMemo"]."'),";
      } else {
        $sql = $sql."('준비',"
        ."'".$cart["ct_id"]."',"
        ."'".$cart["it_name"]."',"
        ."'".$cart["od_id"]."',"
        ."'".$cart["mb_id"]."',"
        ."'".$cart["mb_entNm"]."',"
        ."'".$cart["od_b_name"]."',"
        ."'".$cart["od_b_hp"]."',"
        ."'".$cart["od_b_addr1"]."',"
        ."'".$cart["od_b_addr2"]."',"
        ."'".$cart["prodMemo"]."'),";
      }
    } else {
      return true;
    }
  }
  $sql = substr($sql, 0, -1).";";
  return sql_query($sql);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-02
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-21
 * 설명 : 설치파트너 매니저 설치 일정 상태 수정
 * @param integer $ct_id
 * @param string $status 출고준비|출고완료|취소|주문무효|완료
 * @return boolean 
 */
function update_partner_install_schedule_status_by_ct_id($ct_id, $status) {
  $sql = "UPDATE `partner_inst_sts` SET status = '$status' WHERE ct_id = $ct_id";
  return sql_query($sql);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-12-16
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-12-26
 * 설명 : 설치파트너 매니저 설치 일정 상태 수정
 * @param integer[] $od_id
 * @param string $status 출고준비|출고완료|취소|주문무효|완료
 * @return boolean
 */
function update_partner_install_schedule_status_by_od_id($od_id, $status) {
  if ($status == '완료') {
    $sql = "UPDATE `partner_inst_sts` SET status = '완료' WHERE od_id = $od_id";
  } else {
    $sql = "UPDATE `partner_inst_sts` SET status = '준비' WHERE od_id = $od_id";
  }
  return sql_query($sql);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-21
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-12-26
 * 설명 : 설치파트너 매니저 설치 일정 상태 수정
 * @param integer[] $ct_id
 * @param string $status 출고준비|출고완료|취소|주문무효|완료
 * @return boolean
 */
function update_partner_install_schedule_status_by_ct_id_array($ct_id, $status) {
  if ($status == '완료') {
    $sql = "UPDATE `partner_inst_sts` SET status = '완료' WHERE ct_id IN (".join(",", $ct_id).");";
  } else {
    $sql = "UPDATE `partner_inst_sts` SET status = '준비' WHERE ct_id IN (".join(",", $ct_id).");";
  }
  return sql_query($sql);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-07
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-07
 * 설명 : 설치파트너 매니저 설치 일정 날짜 수정
 * @param integer $od_id
 * @param integer $ct_id
 * @param string $delivery_date 포맷 : YYYY-MM-DD
 * @param string $delivery_datetime 포맷 : hh:mm
 * @return boolean 
 */
function update_partner_install_schedule_delivery_date_and_delivery_datetime_by_ct_id($ct_id, $delivery_date, $delivery_datetime) {
  if (strlen($delivery_datetime) <= 2) {
    $delivery_datetime .= ":00";
  }
  $sql = "UPDATE `partner_inst_sts` 
  SET delivery_date = '$delivery_date', delivery_datetime = '$delivery_datetime' 
  WHERE ct_id = $ct_id";
  return sql_query($sql);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-07
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-07
 * 설명 : 설치파트너 매니저 일정 담당자 지정
 * @param integer $od_id
 * @param string $partner_manager_mb_id
 * @return boolean 
 */
function update_partner_install_schedule_partner_by_ct_id($ct_id, $partner_manager_mb_id) {
  $sql = "SELECT mb_id, mb_name, mb_manager FROM g5_member WHERE mb_id = '$partner_manager_mb_id';";
  $partner = sql_fetch($sql);
  if ($partner == null) return false;
  
  $sql = "UPDATE `partner_inst_sts` SET partner_mb_id = '".$partner["mb_manager"]."', partner_manager_mb_id = '".$partner["mb_id"]."', partner_manager_mb_name = '".$partner["mb_name"]."' WHERE ct_id = $ct_id;";
  return sql_query($sql);
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-28
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-12-31
 * 설명 : 일정 수정 사항 및 삭제 내역 체크
 * @param string $mb_id
 * @param string $member
 * @return mixed
 */
function validate_schedule($mb_id, $member) {
  # 개수 체크
  $sql = "SELECT DISTINCT od_id FROM `partner_inst_sts`;";
  $result = sql_query($sql);
  while ($item = sql_fetch_array($result)) {
    $sql = "SELECT 
    s.od_id, 
    group_concat(s.ct_id ORDER BY s.ct_id ASC) AS `ct_concat` 
    FROM `partner_inst_sts` AS `s` 
    LEFT JOIN `g5_shop_cart` AS `ct` ON ct.od_id = s.od_id 
    WHERE s.od_id = '".$item['od_id']."' 
    GROUP BY od_id;";
    $compare_a = sql_fetch($sql);
  
    $sql = "SELECT 
    ct.od_id, 
    group_concat(ct.ct_id ORDER BY ct.ct_id ASC) AS `ct_concat` 
    FROM `g5_shop_cart` AS ct
    WHERE ct.od_id = '".$item['od_id']."' 
    GROUP BY od_id;";
    $compare_b = sql_fetch($sql);
    if ($compare_a['ct_concat'] != $compare_b['ct_concat']) {
      $sql = "DELETE FROM `partner_inst_sts` WHERE od_id = ".$item['od_id'].";";
      sql_query($sql);
      create_partner_install_schedule($item['od_id']);
    }
  }
  
  # 관리자 계정
    $sql = "SELECT 
  `s`.id AS `s_id`, 
  
  `s`.status AS `s_status`, 
  `s`.delivery_date AS `s_delivery_date`, 
  `s`.delivery_datetime AS `s_delivery_datetime`, 
  `s`.prodMemo AS `s_prodMemo`, 
  `s`.ct_id AS `s_ct_id`, 
  `s`.it_name AS `s_it_name`, 

  `s`.od_id AS `s_od_id`, 
  `s`.od_mb_id AS `s_od_mb_id`, 
  `s`.od_mb_ent_name AS `s_od_mb_ent_name`, 
  `s`.od_b_name AS `s_od_b_name`, 
  `s`.od_b_hp AS `s_od_b_hp`, 
  `s`.od_b_addr1 AS `s_od_b_addr1`, 
  `s`.od_b_addr2 AS `s_od_b_addr2`, 

  `s`.partner_mb_id AS `s_partner_mb_id`, 
  
  `s`.partner_manager_mb_id AS `s_partner_manager_mb_id`, 
  `s`.partner_manager_mb_name AS `s_partner_manager_mb_name`, 

  `ct`.ct_status AS `ct_status`, 
  DATE_FORMAT(`ct`.ct_direct_delivery_date, '%Y-%m-%d') AS `ct_delivery_date`, 
  DATE_FORMAT(`ct`.ct_direct_delivery_date, '%H:%i') AS `ct_delivery_datetime`, 
  `ct`.prodMemo AS `ct_prodMemo`, 
  `ct`.ct_id AS `ct_ct_id`, 
  `ct`.it_name AS `ct_it_name`, 
  
  `od`.od_id AS `od_od_id`, 
  `od_mb`.mb_id AS `od_od_mb_id`, 
  `od_mb`.mb_entNm AS `od_od_mb_ent_name`, 
  `od`.od_b_hp AS `od_od_b_hp`, 
  `od`.od_b_name AS `od_od_b_name`, 
  `od`.od_b_addr1 AS `od_od_b_addr1`, 
  `od`.od_b_addr2 AS `od_od_b_addr2`, 

  `p_mb`.mb_id AS `p_mb_partner_mb_id`, 

  `m_mb`.mb_id AS `m_mb_partner_manager_mb_id`, 
  `m_mb`.mb_name AS `m_mb_partner_manager_mb_name` 
  FROM `partner_inst_sts` AS `s` 
  LEFT JOIN `g5_shop_cart` AS `ct` ON ct.ct_id = s.ct_id
  LEFT JOIN `g5_shop_order` AS `od` ON od.od_id = s.od_id
  LEFT JOIN `g5_member` AS `od_mb` ON od_mb.mb_id = od.mb_id
  LEFT JOIN `g5_member` AS `p_mb` ON p_mb.mb_id = ct.ct_direct_delivery_partner
  LEFT JOIN `g5_member` AS `m_mb` ON m_mb.mb_id = od.od_partner_manager
  WHERE `ct`.ct_is_direct_delivery = 2;";
  $result = sql_query($sql);
  while ($item = sql_fetch_array($result)) {
    if ($item["s_status"] != '완료' && $item["ct_status"] == '완료' && $item["s_status"] != $item["ct_status"]) {
      $sql = "UPDATE `partner_inst_sts` SET status = '완료' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    } else if (($item["ct_status"] == "준비" || $item["ct_status"] == "출고준비" || $item["ct_status"] == "배송") && $item["s_status"] != "준비") {
      $sql = "UPDATE `partner_inst_sts` SET status = '준비' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_delivery_date"] != $item["ct_delivery_date"]) {
      $sql = "UPDATE `partner_inst_sts` SET delivery_date = '".$item['ct_delivery_date']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_delivery_datetime"] != $item["ct_delivery_datetime"]) {
      $sql = "UPDATE `partner_inst_sts` SET delivery_datetime = '".$item['ct_delivery_datetime']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_prodMemo"] != $item["ct_prodMemo"]) {
      $sql = "UPDATE `partner_inst_sts` SET prodMemo = '".$item['ct_prodMemo']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_ct_id"] != $item["ct_ct_id"]) {
      $sql = "UPDATE `partner_inst_sts` SET ct_id = '".$item['ct_ct_id']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_it_name"] != $item["ct_it_name"]) {
      $sql = "UPDATE `partner_inst_sts` SET it_name = '".$item['ct_it_name']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_od_id"] != $item["od_od_id"]) {
      $sql = "UPDATE `partner_inst_sts` SET od_id = '".$item['od_od_id']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_od_mb_id"] != $item["od_od_mb_id"]) {
      $sql = "UPDATE `partner_inst_sts` SET od_mb_id = '".$item['od_od_mb_id']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_od_mb_ent_name"] != $item["od_od_mb_ent_name"]) {
      $sql = "UPDATE `partner_inst_sts` SET od_mb_ent_name = '".$item['od_od_mb_ent_name']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_od_b_hp"] != $item["od_od_b_hp"]) {
      $sql = "UPDATE `partner_inst_sts` SET od_b_hp = '".$item['od_od_b_hp']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_od_b_name"] != $item["od_od_b_name"]) {
      $sql = "UPDATE `partner_inst_sts` SET od_b_name = '".$item['od_od_b_name']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_od_b_addr1"] != $item["od_od_b_addr1"]) {
      $sql = "UPDATE `partner_inst_sts` SET od_b_addr1 = '".$item['od_od_b_addr1']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_od_b_addr2"] != $item["od_od_b_addr2"]) {
      $sql = "UPDATE `partner_inst_sts` SET od_b_addr2 = '".$item['od_od_b_addr2']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_partner_mb_id"] != $item["p_mb_partner_mb_id"] || $item["s_partner_mb_id"] == null) {
      $sql = "UPDATE `partner_inst_sts` SET partner_mb_id = '".$item['p_mb_partner_mb_id']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_partner_manager_mb_id"] != $item["m_mb_partner_manager_mb_id"] || $item["s_partner_manager_mb_id"] == null) {
      $sql = "UPDATE `partner_inst_sts` SET partner_manager_mb_id = '".$item['m_mb_partner_manager_mb_id']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
    if ($item["s_partner_manager_mb_name"] != $item["m_mb_partner_manager_mb_name"] || $item["s_partner_manager_mb_name"] == null) {
      $sql = "UPDATE `partner_inst_sts` SET partner_manager_mb_name = '".$item['m_mb_partner_manager_mb_name']."' WHERE id = ".$item['s_id'].";";
      sql_query($sql);
    }
  }
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-14
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-12-30
 * 설명 : 사업소 기준으로 설치파트너 매니저 일정 
 * @param string $member
 * @return mixed
 */
function get_partner_schedule_by_mb_id($member) {
  $od_mb_id = $member['mb_id'];
  $sql = "SELECT 
    s.status, 
    s.delivery_date, 
    s.delivery_datetime, 
    s.od_id, 
    ct.ct_qty, 
    s.it_name, 
    s.partner_manager_mb_id, 
    s.partner_manager_mb_name, 
    mb.mb_hp, 
    s.od_mb_id, 
    s.od_b_name, 
    s.od_b_hp, 
    s.od_b_addr1, 
    s.od_b_addr2, 
    s.prodMemo
  FROM `partner_inst_sts` AS s 
  LEFT JOIN `g5_shop_cart` AS ct ON ct.ct_id = s.ct_id 
  LEFT JOIN `g5_member` AS mb ON mb.mb_id = s.partner_mb_id 
  WHERE od_mb_id = '$od_mb_id' 
  AND delivery_date != '' 
  AND delivery_datetime != '' 
  AND (status = '준비' OR status = '완료');";

  $result = sql_query($sql);
  $return_list = [];
  while ($res_item = sql_fetch_array($result)) {
    array_push($return_list, array(
      'status' => $res_item['status'],
      'delivery_date' => $res_item['delivery_date'],
      'delivery_datetime' => $res_item['delivery_datetime'],
      'od_id' => $res_item['od_id'],
      'ct_qty' => $res_item['ct_qty'],
      'it_name' => $res_item['it_name'],
      'partner_mb_id' => '',
      'partner_hp' => $res_item['mb_hp'],
      'partner_manager_mb_id' => $res_item['partner_manager_mb_id'],
      'partner_manager_mb_name' => $res_item['partner_manager_mb_name'],
      'od_mb_id' => $res_item['od_mb_id'],
      'od_b_name' => $res_item['od_b_name'],
      'od_b_hp' => $res_item['od_b_hp'],
      'od_b_addr1' => $res_item['od_b_addr1'],
      'od_b_addr2' => $res_item['od_b_addr2'],
      'prodMemo' => $res_item['prodMemo'],
      'type' => 'schedule',
  ));
  }
  $sql = "SELECT 
    ds.deny_date, 
    ds.partner_mb_id, 
    m.mb_id, 
    m.mb_name
  FROM `partner_manager_deny_schedule` AS ds
  LEFT JOIN g5_member AS m ON m.mb_id = ds.partner_manager_mb_id
  WHERE ds.partner_mb_id = '$partner_mb_id';";
  $result = sql_query($sql);
  while ($res_item = sql_fetch_array($result)) {
    array_push($return_list, array(
      'status' => '',
      'delivery_date' => $res_item['deny_date'],
      'delivery_datetime' => '',
      'od_id' => '',
      'it_name' => '',
      'partner_mb_id' => $res_item['partner_mb_id'],
      'partner_hp' => '',
      'partner_manager_mb_id' => $res_item['mb_id'],
      'partner_manager_mb_name' => $res_item['mb_name'],
      'od_mb_id' => '',
      'od_b_name' => '',
      'od_b_hp' => '',
      'od_b_addr1' => '',
      'od_b_addr2' => '',
      'prodMemo' => '',
      'type' => 'deny_schedule',
  ));
  }
  return $return_list;
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-02
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-12-30
 * 설명 : 설치파트너 & 설치파트너 매니저 & 관리자 계정으로 설치 일정 조회
 * @param string $member
 * @return mixed
 */
function get_partner_schedule_by_partner_mb_id($member) {
  $mb_id = $member['mb_id'];
  $mb_type = $member['mb_type'];
  $mb_level = $member['mb_level'];
  if ($mb_level >= 9 && $mb_type === 'default') {
    // 관리자 계정
    $sql = "SELECT 
      status, 
      s.delivery_date, 
      s.delivery_datetime, 
      s.od_id, 
      ct.ct_qty, 
      s.it_name, 
      m.mb_manager AS 'partner_mb_id', 
      s.partner_manager_mb_id, 
      s.partner_manager_mb_name, 
      mb.mb_hp, 
      s.od_mb_id, 
      s.od_mb_ent_name, 
      s.od_b_name, 
      s.od_b_hp, 
      s.od_b_addr1, 
      s.od_b_addr2, 
      s.prodMemo
    FROM `partner_inst_sts` AS s
    LEFT JOIN `g5_member` AS m ON m.mb_id = s.partner_manager_mb_id
    INNER JOIN `g5_shop_cart` AS ct ON ct.ct_id = s.ct_id 
    LEFT JOIN `g5_member` AS mb ON mb.mb_id = s.partner_mb_id 
    WHERE delivery_date != '' 
    AND delivery_datetime != '' 
    AND (status = '준비' OR status = '완료');";
  } else if($mb_type == 'manager') {
    // 설치파트너 매니저 계정
    $sql = "SELECT 
      status, 
      s.delivery_date, 
      s.delivery_datetime, 
      s.od_id, 
      ct.ct_qty, 
      s.it_name, 
      m.mb_manager AS 'partner_mb_id', 
      s.partner_manager_mb_id, 
      s.partner_manager_mb_name, 
      mb.mb_hp, 
      s.od_mb_id, 
      s.od_mb_ent_name, 
      s.od_b_name, 
      s.od_b_hp, 
      s.od_b_addr1, 
      s.od_b_addr2, 
      s.prodMemo
    FROM `partner_inst_sts` as s
    LEFT JOIN `g5_member` AS m ON m.mb_id = s.partner_manager_mb_id
    INNER JOIN `g5_shop_cart` AS ct ON ct.ct_id = s.ct_id 
    LEFT JOIN `g5_member` AS mb ON mb.mb_id = s.partner_mb_id 
    WHERE partner_manager_mb_id = '$mb_id' 
    AND delivery_date != '' 
    AND delivery_datetime != '' 
    AND (status = '준비' OR status = '완료');";
  } else {
    // 설치파트너 계정
    $sql = "SELECT 
      status, 
      s.delivery_date, 
      s.delivery_datetime, 
      s.od_id, 
      ct.ct_qty, 
      s.it_name, 
      m.mb_manager AS 'partner_mb_id', 
      s.partner_manager_mb_id, 
      s.partner_manager_mb_name, 
      mb.mb_hp, 
      s.od_mb_id, 
      s.od_mb_ent_name, 
      s.od_b_name, 
      s.od_b_hp, 
      s.od_b_addr1, 
      s.od_b_addr2, 
      s.prodMemo
    FROM `partner_inst_sts` as s
    LEFT JOIN `g5_member` AS m ON m.mb_id = s.partner_manager_mb_id
    INNER JOIN `g5_shop_cart` AS ct ON ct.ct_id = s.ct_id 
    LEFT JOIN `g5_member` AS mb ON mb.mb_id = s.partner_mb_id 
    WHERE partner_mb_id = '$mb_id' 
    AND delivery_date != '' 
    AND delivery_datetime != '' 
    AND (status = '준비' OR status = '완료');";
  }
  $result = sql_query($sql);
  $return_list = [];
  while ($res_item = sql_fetch_array($result)) {
    array_push($return_list, array(
      'status' => $res_item['status'],
      'delivery_date' => $res_item['delivery_date'],
      'delivery_datetime' => $res_item['delivery_datetime'],
      'od_id' => $res_item['od_id'],
      'ct_qty' => $res_item['ct_qty'],
      'it_name' => $res_item['it_name'],
      'partner_mb_id' => $res_item['partner_mb_id'],
      'partner_hp' => $res_item['mb_hp'],
      'partner_manager_mb_id' => $res_item['partner_manager_mb_id'],
      'partner_manager_mb_name' => $res_item['partner_manager_mb_name'],
      'od_mb_id' => $res_item['od_mb_id'],
      'od_b_name' => $res_item['od_b_name'],
      'od_b_hp' => $res_item['od_b_hp'],
      'od_b_addr1' => $res_item['od_b_addr1'],
      'od_b_addr2' => $res_item['od_b_addr2'],
      'prodMemo' => $res_item['prodMemo'],
      'type' => 'schedule',
   ));
  }
  $sql = "SELECT 
    ds.deny_date,
    ds.partner_mb_id, 
    m.mb_id,
    m.mb_name
  FROM `partner_manager_deny_schedule` AS ds
  LEFT JOIN g5_member AS m ON m.mb_id = ds.partner_manager_mb_id
  WHERE ds.partner_mb_id = '$partner_mb_id';";
  $result = sql_query($sql);
  while ($res_item = sql_fetch_array($result)) {
    array_push($return_list, array(
      'status' => '',
      'delivery_date' => $res_item['deny_date'],
      'delivery_datetime' => '',
      'od_id' => '',
      'ct_qty' => '',
      'it_name' => '',
      'partner_mb_id' => $res_item['partner_mb_id'],
      'partner_mb_hp' => '', 
      'partner_manager_mb_id' => $res_item['mb_id'],
      'partner_manager_mb_name' => $res_item['mb_name'],
      'od_mb_id' => '',
      'od_b_name' => '',
      'od_b_hp' => '',
      'od_b_addr1' => '',
      'od_b_addr2' => '',
      'prodMemo' => '',
      'type' => 'deny_schedule',
   ));
  }
  return $return_list;
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-02
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-21
 * 설명 : 설치파트너 매니저 설치 불가 일정 일괄 생성
 * @param string $partner_mb_id : 설치파트너 mb_id
 * @param string $partner_manager_mb_id : 설치파트너 매니저 mb_id
 * @param string[] $schedules : 설치 불가 일정 array 예시: ["2022-10-01", "2022-10-23"]
 * @return boolean|mixed 
 */
function bulk_partner_deny_schedule($partner_mb_id, $partner_manager_mb_id, $schedules) {
  if (count($schedules) > 0) {
    $result_schedules = $schedules;
    foreach($schedules as $schedule) {
      global $t_schedule;
      $t_schedule = $schedule;
      $sql = "SELECT deny_date FROM `partner_manager_deny_schedule` WHERE partner_manager_mb_id = '$partner_manager_mb_id' AND deny_date = '$schedule';";
      $count = mysqli_num_rows(sql_query($sql));
      if ($count != 0) {
        $result_schedules = array_filter($result_schedules, function($value) {
          global $t_schedule;
          return $value != $t_schedule;
        });
      }
    }
    if (count($result_schedules) > 0) {
      $sql = "INSERT INTO `partner_manager_deny_schedule` (partner_mb_id, partner_manager_mb_id, deny_date) VALUES ";
      foreach($result_schedules as $schedule) {
        $sql = $sql."('$partner_mb_id', '$partner_manager_mb_id', '$schedule'),";
      }
      $sql = substr($sql, 0, -1).";";
      return sql_query($sql);
    } else {
      return false;
    }
  } else {
    return false;
  }
}

/**
 * 작성자 : 임근석
 * 작성일자 : 2022-11-21
 * 마지막 수정자 : 임근석
 * 마지막 수정일자 : 2022-11-21
 * 설명 : 설치파트너 매니저 설치 불가 일정 삭제
 * @param string $partner_mb_id : 설치파트너 mb_id
 * @param string $partner_manager_mb_id : 설치파트너 매니저 mb_id
 * @param string $deny_date : 설치 불가 일정
 * @return boolean 
 */
function delete_partner_deny_schedule($partner_mb_id, $partner_manager_mb_id, $deny_date) {
  $sql = "SELECT id FROM `partner_manager_deny_schedule` WHERE partner_mb_id = '$partner_mb_id' AND partner_manager_mb_id = '$partner_manager_mb_id' AND deny_date = '$deny_date';";
  $count = mysqli_num_rows(sql_query($sql));
  if ($count > 0) {
    $sql = "DELETE FROM `partner_manager_deny_schedule` WHERE partner_mb_id = '$partner_mb_id' AND partner_manager_mb_id = '$partner_manager_mb_id' AND deny_date = '$deny_date';";
    return sql_query($sql);
  } else {
    return false;
  }
}




/*
 *
 * 작성자 : 박서원
 * 작성일자 : 2022-12-06
 * 마지막 수정자 : 박서원
 * 마지막 수정일자 : 2022-12-07
 * 설명 : 각상품별 배송비 타입에 따른 계산 방식 단일화
 * @param string $it_id : 아이템 아이디
 * @param string $qty : 수량
 * @param string $_price : 상품가격
 * @return int
 *
 */
function get_item_delivery_cost( $it_id, $qty, $_price=0 ) {

  if( !$it_id || !$qty ) return 0;  
  
  global $default;  

  $_result = array();
  $_cost = 0;
  $_cost_limit = explode(";",$default['de_send_cost_limit']);
  $_cost_list = explode(";",$default['de_send_cost_list']);  

  $_item = sql_fetch("
    SELECT it_price, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, it_even_odd, it_even_odd_price
    FROM g5_shop_item
    WHERE `it_id` = '" . $it_id . "';
  ");

  if( $_price==0 ) {
    $_price = $_item['it_price'] * $qty;
  }

  if( $_item['it_sc_type'] == 1 ) {
    // (기본) 무료 배송
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

  }  
  else if( $_item['it_sc_type'] == 2 ) {
    // (기본) 조건부 무료 배송(X) X - X - X - X - X - X - X - X - X
    // 22.12.07 : 서원 - 플랫폼팀 요청에 의해 해당 기능 사용중지 처리
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    if( (int)$_price < (int)$_item['it_sc_minimum'] ) {
      $_cost = $_item['it_sc_price'];
    } else { 
      $_cost = 0;
    }
  }  
  else if( $_item['it_sc_type'] == 3 ) {
    // (기본) 유료 배송(X) X - X - X - X - X - X - X - X - X - X
    // 22.12.07 : 서원 - 플랫폼팀 요청에 의해 해당 기능 사용중지 처리
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    $_cost = ( $_item['it_sc_price'] );
  }    
  else if( $_item['it_sc_type'] == 4 ) {
    // (별도) 수량별 유료 배송
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    $_cost = ( $_item['it_sc_price'] * ceil($qty / $_item['it_sc_qty']) ) ;
  }   
  else if( $_item['it_sc_type'] == 5 ) {
    // (별도) 홀수/짝수 배송
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    if( ($_item['it_even_odd'] == 0) && ($qty > 0) && (($qty % 2) === 1) ) {
      // 홀수
      $_cost = $_item['it_even_odd_price'];
    } else if( ($_item['it_even_odd'] == 1) && ($qty > 0) && (($qty % 2) === 0) ) {
      // 짝수
      $_cost = $_item['it_even_odd_price'];
    }
  }
  else if( $_item['it_sc_type'] == 6 ) {
    // (별도) 포장수량 무료 배송
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    if( $qty % $_item['it_sc_qty'] ) {
      $_cost = $_item['it_sc_price'];
    }
  }
  else {
    // (기본) 쇼핑몰 기본 설정
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    foreach($_cost_limit as $key => $val) {
      if( (int)$_price < (int)$_cost_limit[$key] ) { 
         $_cost = $_cost_list[$key];
        break;
      }
    }

  }
  
  $_result = array(
    "it_id" => $it_id,
    "cost" => (int)$_cost
  );

  return $_result;
}


/*
 *
 * 작성자 : 박서원
 * 작성일자 : 2022-12-15
 * 마지막 수정자 : 박서원
 * 마지막 수정일자 : 2022-12-16
 * 설명 : 암호화/복호화에 필요한 기본 키값
 * @return string : 32바이트 키값
 *
 */
function security_key() {
  $_result ="";
  $_key = 'EroumCare_security!KEY'; 
  // ** 추후 회원 개별 키값으로 변경 필요.
  // ** 소스에서 키값이 유출되더라도 회원 개별 키값으로 복구 불가능하게 적용 해야함.

  // 256 bit 키를 만들기 위해서 비밀번호를 해시해서 첫 32바이트를 사용합니다.
  $_result = substr(hash('sha256', $_key, true), 0, 32);
  return $_result;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2022-12-15
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2022-12-16
* 설명 : 암호화/복호화에 필요한 기본 Initial Vector
* @return string : 128 bit(16 byte)
*
*/
function security_iv() {
  $_result ="";

  // ** 추후 벡터 정보의 경우 바이너리파일에서 읽어 사용하는 방식으로 변경 필요.

  // Initial Vector(IV)는 128 bit(16 byte)입니다.
  $_result = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
  return $_result;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2022-12-15
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2022-12-16
* 설명 : 암호화
* @param string $msg : 메시지
* @param string $key : 암호화 키값
* @param string $Iv : Initial Vector
* @return string : encryption
* 사용 : $result = encryption_AES256( 평문텍스트, security_key(), security_iv() );
*
*/
function encryption_AES256( $msg, $key, $Iv ){
  $_result ="";

  // 암호화
  $_result = base64_encode(openssl_encrypt($msg, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $Iv));    
  return $_result;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2022-12-15
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2022-12-16
* 설명 : 복호화
* @param string $msg : 메시지
* @param string $key : 암호화 키값
* @param string $Iv : Initial Vector
* @return string : decryption
* 사용 : $result = decryption_AES256( 암호화된텍스트, security_key(), security_iv() );
* 
*/
function decryption_AES256( $msg, $key, $Iv ){
  $_result ="";

  // 복호화
  $_result = openssl_decrypt(base64_decode($msg), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $Iv);    
  return $_result;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2022-12-20
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2022-12-20
* 설명 : 연락처 마스킹 처리
* @param string $str : 연락처
* @return string : 010-****-1234 / 02***1234
* 사용 : $result = Masking_Tel( "010-1234-5678" );
* 
*/
function Masking_Tel($str){ 
  $_result = "";
  $_array = explode("-", $str);

  if( (COUNT($_array)>1) && is_array( $_array ) ) {
    
    $strlen = mb_strlen($_array[1], 'utf-8');
    switch($strlen){
      case 3: $_result = $_array[0]."-***-".$_array[2]; break;
      case 4: $_result = $_array[0]."-****-".$_array[2]; break;
      case 0: $_result =''; break;
    }

  } else {

    $strlen = mb_strlen($str, 'utf-8');
    switch($strlen){
      case 9: $_result = mb_substr($str,0,2)."***".mb_substr($str,5,4); break;
      case 10: $_result = mb_substr($str,0,3)."***".mb_substr($str,6,4); break;
      case 11: $_result = mb_substr($str,0,3)."****".mb_substr($str,7,4); break;
      case 0: $_result =''; break;
    }

  }

  return $_result;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2022-12-20
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2022-12-20
* 설명 : 문자열 전체 마스킹
* @param string $str : 문자열
* @return string : *************************
* 사용 : $result = Masking_All( "이로움장기요양기관통합관리시스템" );
* 
*/
// 문자열 전체 마스킹
function Masking_All($str){ 
  $_result = preg_replace('/(.*?)/', '*', $str);
  return $_result;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2022-12-20
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2022-12-20
* 설명 : 이름 마스킹
* @param string $str : 문자열
* @return string : *************************
* 사용 : $result = Masking_Name( "홍길동" );
* 
*/
function Masking_Name($str) {
  $str = trim($str);
  $strlen = mb_strlen($str, 'utf-8');
  $_result = $str;

  if ($strlen <= 2) {
    // 한두 글자면 그냥 뒤에 별표 붙여서 내보낸다.
    $_result = mb_substr($str, 0, 1, 'utf-8') . '*';
  }
  else if ($strlen >= 3) {
    // 3으로 나눠서 앞뒤.
    $leave_strlen = floor($strlen/3); // 남겨 둘 길이
    $asterisk_strlen = $strlen - ($leave_strlen * 2);
    $offset = $leave_strlen + $asterisk_strlen;
    $head = mb_substr($str, 0, $leave_strlen, 'utf-8');
    $tail = mb_substr($str, $offset, $leave_strlen, 'utf-8');
    $_result = $head . implode('', array_fill(0, $asterisk_strlen, '*')) . $tail;
  }

  return $_result;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2022-12-20
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2022-12-20
* 설명 : 문자열 커스텀 마스킹
* @param string $str : 문자열
* @return string : 이로움장기요양기관통*****템
* 사용 : $result = Masking_Custom( "이로움장기요양기관통합관리시스템", 10 , 5 );
* 
*/
function Masking_Custom($str, $start, $len) {
  $_result = "";
  $strlen = mb_strlen($str, 'utf-8');

  $head = mb_substr($str, 0, $start, 'utf-8');  
  $tail = mb_substr($str, ($start+$len), ($strlen-($start+$len)), 'utf-8');
  
  $masking = mb_substr($str, $start, $len, 'utf-8');  
  $strlen = mb_strlen($masking, 'utf-8');
  $masking = implode('', array_fill(0, $strlen, '*'));

  $_result = $head . $masking . $tail;
  
  return $_result;
}