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
            a.ct_direct_delivery_partner,
            a.ct_direct_delivery_price,
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
function valid_recipient_input($data, $is_spare = false, $b_company = false) {

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
    if(!$b_company && !recipient_preg_match($data, 'penTypeCd')) {
      return '본인부담율을 확인해주세요.';
    }
    # 기초수급자는 주민등록번호 입력 필수
    if(!$b_company && $data['penTypeCd'] == '04') {
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
      g5_shop_item x ON i.it_code = x.ProdPayCode and
      (
        ( i.gubun = '00' and x.ca_id like '10%' ) or
        ( i.gubun = '01' and x.ca_id like '20%' )
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
      g5_shop_item x ON i.it_code = x.ProdPayCode and
      (
        ( i.gubun = '00' and x.ca_id like '10%' ) or
        ( i.gubun = '01' and x.ca_id like '20%' )
      )
    LEFT JOIN
      g5_shop_category y ON x.ca_id = y.ca_id
    WHERE
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
  $where_date = ' and MONTH(od_time) = MONTH(CURRENT_DATE()) ';
  $where_ledger_date = ' and MONTH(pl_created_at) = MONTH(CURRENT_DATE()) ';
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
function get_partner_members() {
  $sql = "
    SELECT
      *
    FROM
      g5_member
    WHERE
      mb_type = 'partner' and
      mb_partner_auth = 1 and
      mb_partner_date >= NOW()
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
function get_partner_ledger($mb_id, $fr_date = '', $to_date = '', $sel_field = '', $search = '') {
  $where_order = $where_ledger = '';

  # 기간
  if($fr_date && $to_date) {
    $where_order = " and (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
    $where_ledger = " and (pl_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  }

  # 주문내역
  $sql_order = "
    SELECT
      od_time,
      c.od_id,
      mb_entNm,
      it_name,
      ct_option,
      ct_qty,
      ct_direct_delivery_price as price_d,
      0 as deposit,
      od_b_name
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
      {$where_order}
  ";

  # 입금/출금
  $sql_ledger = "
    SELECT
      pl_created_at as od_time,
      '' as od_id,
      m.mb_entNm,
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
      (
        CASE
          WHEN pl_type = 1
          THEN pl_amount
          ELSE 0
        END
      ) as deposit,
      '' as od_b_name
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
  $carried_balance = get_partner_outstanding_balance($mb_id, $fr_date);

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
function get_partner_outstanding_balance($mb_id, $fr_date = null, $total_price_only = false) {
  global $g5;

  $where_date = ' and MONTH(od_time) = MONTH(CURRENT_DATE()) ';
  $where_ledger_date = ' and MONTH(pl_created_at) = MONTH(CURRENT_DATE()) ';
  if($fr_date) {
    $where_date = " and od_time < '{$fr_date} 00:00:00' ";
    $where_ledger_date = " and pl_created_at < '{$fr_date} 00:00:00' ";
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
function send_alim_talk($msgIdx, $recipient, $tmpltCode, $message, $attach = null) {
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
