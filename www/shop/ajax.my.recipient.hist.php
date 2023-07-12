<?php
include_once('./_common.php');

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');

$data = $_POST ?: [];

// error_log(var_export(json_encode($_POST),1));
error_log(var_export("catch : ".$member['mb_entId'].",".$_POST,1));

if($data['penLtmNum'] != null){
	error_log(var_export("oh... im in;",1));
	error_log(var_export($data['penLtmNum'].",".$member["mb_entId"],1));
	$check_data = "select * from pen_purchase_hist where ENT_ID = '".$member["mb_entId"]."' and PEN_LTM_NUM = 'L".$data['penLtmNum']."';";
	$check_res = sql_query($check_data);
	if(sql_num_rows($check_res) != 0){
		$update_data = "UPDATE pen_purchase_hist set SYNC_GOVERN = '1' where ENT_ID = '".$member["mb_entId"]."' and PEN_LTM_NUM = 'L".$data['penLtmNum']."';";
        sql_query($update_data);

		//json_response(200, 'OK', array(
		//	'msg' => "contract update"
		//));
	}
}

$list_data = $data['data']['recipientContractDetail']['Result'];
$list_this_hist = $data['data']['recipientContractHistory']['Result']['ds_result'];

if($list_data['ds_ctrHistTotalList'] == null || $list_data['ds_ctrHistTotalList'] == ''){
	json_response(200, 'OK', array(
		'sql' => "",
		'rem_amount' => 1600000,
	));
}

$keys = ['PAST_YM', 'ENT_ID', 'PEN_NM', 'PEN_LTM_NUM', 'PEN_REC_GRA_NM', 
'PEN_EXPI_ST_DTM', 'PEN_EXPI_ED_DTM', 'PEN_TYPE_NM', 'PEN_TYPE_CD', 'PROD_PAY_CODE', 'PROD_BAR_NUM',
'ITEM_NM', 'PROD_NM', 'ORD_STATUS', 'ORD_DTM', 'ORD_STR_DTM', 'ORD_END_DTM', 
'TOTAL_PRICE', 'MONTH_PRICE', 'REG_USR_ID', 'REG_USR_IP',
'MODIFY_USR_ID', 'MODIFY_USR_IP', 'PEN_BUDGET', 'SYNC_GOVERN'];

$list_detail = $list_data['ds_welToolTgtList'][0];
$list_hist = $list_data['ds_ctrHistTotalList'];
$insert_list = [];

/*
$mapping_list_total = [];
if($list_hist){
	for($i=0; $i<sizeof($list_hist);$i++){
		if($mapping_list_total[$list_hist[$i]['BCD_NO']]){
			array_push($mapping_list_total[$list_hist[$i]['BCD_NO']], $list_hist[$i]);
		}else {
			$mapping_list_total[$list_hist[$i]['BCD_NO']] = [];
			array_push($mapping_list_total[$list_hist[$i]['BCD_NO']], $list_hist[$i]);
		}		
	}
}
*/
$mapping_list_this = [];
$CNCL_YN = [];
if($list_this_hist){
	for($i=0; $i<sizeof($list_this_hist);$i++){
		$CNCL_YN[$list_this_hist[$i]['BCD_NO']] = $list_this_hist[$i]['CNCL_YN'];
		if($mapping_list_this[$list_this_hist[$i]['BCD_NO']]){
			$mapping_item = [];
			$mapping_item['BCD_NO'] = $list_this_hist[$i]['BCD_NO'];
			$mapping_item['CNCL_YN'] = $list_this_hist[$i]['CNCL_YN'];
			$mapping_item['POF_FR_DT'] = $list_this_hist[$i]['POF_FR_DT'];
			$mapping_item['TOT_AMT'] = $list_this_hist[$i]['TOT_AMT'];
			$mapping_item['WLR_MTHD_CD'] = $list_this_hist[$i]['WLR_MTHD_CD'];
			array_push($mapping_list_this[$list_this_hist[$i]['BCD_NO']], $list_this_hist[$i]);
		}else {
			$mapping_list_this[$list_this_hist[$i]['BCD_NO']] = [];
			array_push($mapping_list_this[$list_this_hist[$i]['BCD_NO']], $list_this_hist[$i]);
		}		
	}
}
/*
json_response(400, 'OK', array(
	'msg' => json_encode($mapping_list_total)
));

$result_mapping = [];
if(sizeof(array_keys($mapping_list_this))> 0){
	for($i=0; $i<sizeof(array_keys($mapping_list_this));$i++){
		error_log(var_export($i." value length : ".sizeof(array_values($mapping_list_this)[$i]),1));
		if(sizeof(array_values($mapping_list_this)[$i])>1){
			$result_mapping[array_keys($mapping_list_this)[$i]] = array_values($mapping_list_this)[$i];
		}
	}
}
*/

$result_mapping = [];
if(sizeof(array_keys($mapping_list_this))> 0){
	for($i=0; $i<sizeof(array_keys($mapping_list_this));$i++){
		error_log(var_export($i." value length : ".sizeof(array_values($mapping_list_this)[$i]),1));
		if(sizeof(array_values($mapping_list_this)[$i])>1){
			$list_tmp = [];
			for($ind=0;$ind<sizeof(array_values($mapping_list_this)[$i]);$ind++){
				$_key = array_values($mapping_list_this)[$i][$ind]['BCD_NO']."/".array_values($mapping_list_this)[$i][$ind]['POF_FR_DT'];
				$list_tmp[$_key] = array_values($mapping_list_this)[$i][$ind]['TOT_AMT'];
			}
			$result_mapping[array_keys($mapping_list_this)[$i]] = $list_tmp;
		}
	}
}

/*
json_response(400, 'OK', array(
	'msg' => json_encode($result_mapping)
));
*/

for($i = 0; $i < sizeof($list_hist); $i++){
	$insert_list[$i]['PEN_EXPI_ST_DTM'] = '';
	$insert_list[$i]['PEN_EXPI_ED_DTM'] = '';
	$insert_list[$i]['PEN_BUDGET'] = '';

	if(sizeof($list_data['ds_toolPayLmtList'])>0){
		for($ind = 0; $ind < sizeof($list_data['ds_toolPayLmtList']); $ind++){
			if(strtotime($list_data['ds_toolPayLmtList'][$ind]['APDT_FR_DT']) <= strtotime($list_hist[$i]['POF_FR_DT'])){
				$insert_list[$i]['PEN_EXPI_ST_DTM'] = $list_data['ds_toolPayLmtList'][$ind]['APDT_FR_DT'];
				$insert_list[$i]['PEN_EXPI_ED_DTM'] = $list_data['ds_toolPayLmtList'][$ind]['APDT_TO_DT'];
				$insert_list[$i]['PEN_BUDGET'] = $list_data['ds_toolPayLmtList'][$ind]['REMN_AMT'];
				break;
			}
		}
	}

	$insert_list[$i]['PAST_YM'] = substr($list_hist[$i]['POF_FR_DT'],0,4)."-".substr($list_hist[$i]['POF_FR_DT'],4,2);
	$insert_list[$i]['ENT_ID'] = $member["mb_entId"];
	$insert_list[$i]['PEN_NM'] = $list_detail['FNM'];
	$insert_list[$i]['PEN_LTM_NUM'] = $list_detail['LTC_MGMT_NO'];
	$insert_list[$i]['PEN_REC_GRA_NM'] = $list_detail['LTC_RCGT_GRADE_CD'].'등급';
	$insert_list[$i]['PEN_TYPE_NM'] = $list_detail['REDUCE_NM'];
	if($list_detail['REDUCE_NM'] == '일반'){
		$insert_list[$i]['PEN_TYPE_CD'] = '15%';
	} else if ($list_detail['REDUCE_NM'] == '기초'){
		$insert_list[$i]['PEN_TYPE_CD'] = '0%';
	} else if($list_detail['REDUCE_NM'] == '의료'){
		$insert_list[$i]['PEN_TYPE_CD'] = '6%';
	} else {
		$insert_list[$i]['PEN_TYPE_CD'] = rtrim(explode('(', $list_detail['SBA_CD'])[1],')');
	}
	// $insert_list[$i]['PEN_TYPE_CD'] = $list_detail['SBA_CD'] == '일반'?'15%':$list_detail['SBA_CD'] == '기초'?'0%':$list_detail['SBA_CD'] == '의료'?'6%':substr($list_detail['SBA_CD'],4,2);
	$insert_list[$i]['PROD_PAY_CODE'] = $list_hist[$i]['WLR_MTHD_CD'];
	$insert_list[$i]['PROD_BAR_NUM'] = $list_hist[$i]['BCD_NO'];
	$insert_list[$i]['ITEM_NM'] = $list_hist[$i]['WIM_ITM_CD_NM'];
	$insert_list[$i]['PROD_NM'] = $list_hist[$i]['PRDCT_NM'];
	$insert_list[$i]['ORD_STATUS'] = $list_hist[$i]['WEL_PAY_STL_NM'];
	$insert_list[$i]['ORD_DTM'] = $list_hist[$i]['POF_FR_DT'];
	$insert_list[$i]['ORD_STR_DTM'] = $list_hist[$i]['POF_FR_DT']?:$list_hist[$i]['CTR_FR_DT'];
	$insert_list[$i]['ORD_END_DTM'] = $list_hist[$i]['POF_TO_DT']?:$list_hist[$i]['CTR_TO_DT'];

	$insert_list[$i]['TOTAL_PRICE'] = $list_hist[$i]['TOT_AMT'];	
	$t_key = strval($list_hist[$i]['BCD_NO']);		
	if($result_mapping[$t_key]){
		$tmp_key = $list_hist[$i]['BCD_NO']."/".$list_hist[$i]['PERIOD'];
		for($idx=0;$idx<sizeof(array_keys($result_mapping[$t_key]));$idx++){
			if(array_keys($result_mapping[$t_key])[$idx] == $tmp_key){
				error_log(var_export("*******key : ".array_keys($result_mapping[$t_key])[$idx],1));
				error_log(var_export("*******tmp : ".$tmp_key,1));
				error_log(var_export("*******val : ".array_values($result_mapping[$t_key])[$idx],1));
				$insert_list[$i]['TOTAL_PRICE'] = array_values($result_mapping[$t_key])[$idx];
				break;
			}
		}
	}

	$insert_list[$i]['MONTH_PRICE'] = $list_hist[$i]['MECH_AMT'];
	$insert_list[$i]['REG_USR_ID'] = $member["mb_id"];
	$insert_list[$i]['REG_USR_IP'] = $_SERVER['REMOTE_ADDR'];
	$insert_list[$i]['MODIFY_USR_ID'] = $member["mb_id"];
	$insert_list[$i]['MODIFY_USR_IP'] = $_SERVER['REMOTE_ADDR'];
	$insert_list[$i]['CNCL_YN'] = ($CNCL_YN[$list_hist[$i]['BCD_NO']] != "")?$CNCL_YN[$list_hist[$i]['BCD_NO']]:"정상";
	if($data['status']){
		$insert_list[$i]['SYNC_GOVERN'] = 1;
	} else {
		$insert_list[$i]['SYNC_GOVERN'] = 0;
	}	
}

/*
$sql_test = "INSERT INTO pen_purchase_hist (PAST_YM, ENT_ID, PEN_NM, PEN_LTM_NUM, PEN_REC_GRA_NM, 
PEN_EXPI_ST_DTM, PEN_EXPI_ED_DTM, PEN_TYPE_NM, PEN_TYPE_CD, PROD_PAY_CODE, 
ITEM_NM, PROD_NM, ORD_STATUS, ORD_DTM, ORD_STR_DTM, ORD_END_DTM, 
TOTAL_PRICE, MONTH_PRICE, REG_USR_ID, REG_USR_IP,
MODIFY_USR_ID, MODIFY_USR_IP, PEN_BUDGET, SYNC_GOVERN) VALUES 
('2022-07', 'dev', '홍길동', '0295849304', '3등급', 
'2021-09-01', '2022-08-31', '일반', '15%', '1', 
'ITEM_NM', 'PROD_NM', '대여', '2021-08-01', '2021-08-01', '2021-07-31', 
'150000', '12500', 'dev', '112.223.445.553',
'dev', '112.223.445.553', '1295880', '0');";

sql_query($sql_test);

$sql = "INSERT INTO pen_purchase_hist (PAST_YM, ENT_ID, PEN_NM, PEN_LTM_NUM, PEN_REC_GRA_NM, 
PEN_EXPI_ST_DTM, PEN_EXPI_ED_DTM, PEN_TYPE_NM, PEN_TYPE_CD, PROD_PAY_CODE, 
ITEM_NM, PROD_NM, ORD_STATUS, ORD_DTM, ORD_STR_DTM, ORD_END_DTM, 
TOTAL_PRICE, MONTH_PRICE, REG_USR_ID, REG_USR_IP,
MODIFY_USR_ID, MODIFY_USR_IP, PEN_BUDGET, SYNC_GOVERN) VALUES ";
*/
$query = "SHOW COLUMNS FROM pen_purchase_hist WHERE `Field` = 'CNCL_YN';";//업데이트멤버 없을 시 추가
	$wzres = sql_fetch( $query );
	if(!$wzres['Field']) {
		sql_query("ALTER TABLE `pen_purchase_hist`
		ADD `CNCL_YN` varchar(10) NULL DEFAULT '정상' COMMENT '계약상태' AFTER PEN_BUDGET", true);
	}

$sql = "INSERT INTO pen_purchase_hist (PEN_EXPI_ST_DTM, PEN_EXPI_ED_DTM, PEN_BUDGET, PAST_YM, ENT_ID, PEN_NM, PEN_LTM_NUM, PEN_REC_GRA_NM, PEN_TYPE_NM, PEN_TYPE_CD, PROD_PAY_CODE, PROD_BAR_NUM, ITEM_NM, PROD_NM, ORD_STATUS, ORD_DTM, ORD_STR_DTM, ORD_END_DTM, TOTAL_PRICE, MONTH_PRICE, REG_USR_ID, REG_USR_IP, MODIFY_USR_ID, MODIFY_USR_IP, SYNC_GOVERN,CNCL_YN) VALUES ";

for($idx = 0; $idx < sizeof($insert_list); $idx++){
	$sql = $sql."('".$insert_list[$idx]['PEN_EXPI_ST_DTM']."','".$insert_list[$idx]['PEN_EXPI_ED_DTM']."','"
	.$insert_list[$idx]['PEN_BUDGET']."','".$insert_list[$idx]['PAST_YM']."','".$insert_list[$idx]['ENT_ID']."','"
	.$insert_list[$idx]['PEN_NM']."','".$insert_list[$idx]['PEN_LTM_NUM']."','".$insert_list[$idx]['PEN_REC_GRA_NM']."','"
	.$insert_list[$idx]['PEN_TYPE_NM']."','".$insert_list[$idx]['PEN_TYPE_CD']."','".$insert_list[$idx]['PROD_PAY_CODE']."','".$insert_list[$idx]['PROD_BAR_NUM']."','"
	.$insert_list[$idx]['ITEM_NM']."','".$insert_list[$idx]['PROD_NM']."','".$insert_list[$idx]['ORD_STATUS']."','"
	.$insert_list[$idx]['ORD_DTM']."','".$insert_list[$idx]['ORD_STR_DTM']."','".$insert_list[$idx]['ORD_END_DTM']."','"
	.$insert_list[$idx]['TOTAL_PRICE']."','".$insert_list[$idx]['MONTH_PRICE']."','".$insert_list[$idx]['REG_USR_ID']."','"
	.$insert_list[$idx]['REG_USR_IP']."','".$insert_list[$idx]['MODIFY_USR_ID']."','".$insert_list[$idx]['MODIFY_USR_IP']."','"
	.$insert_list[$idx]['SYNC_GOVERN']."','"
	.$insert_list[$idx]['CNCL_YN']."')";
}
$sql_del = "delete from pen_purchase_hist where ENT_ID = '".$member["mb_entId"]."' and PEN_LTM_NUM = '".$insert_list[0]['PEN_LTM_NUM']."';";
sql_query($sql_del);

$sql = str_replace(')(', '),(', $sql);
$sql = $sql.';';

sql_query($sql);

//***** 틸코블렛 API 조회 값중 대여 상품이 있는 경우 잔여 금액을 계산하지 못하고 잘못된 값을 반환하는 사례가 발생하여 아래 계약건에 대한 금액 합산 로직을 추가하였음 - 정한진 차장 2023.06.13
/*
$pen_budget = $list_data['ds_toolPayLmtList'][$ind]['REMN_AMT'];//잔여금액 초기화
$sql_b = "SELECT COUNT(*) cnt FROM pen_purchase_hist 
WHERE ENT_ID = '".$member["mb_entId"]."' AND PEN_NM = '".$list_detail['FNM']."' AND PEN_LTM_NUM = '".$list_detail['LTC_MGMT_NO']."' 
AND (CURRENT_TIMESTAMP BETWEEN PEN_EXPI_ST_DTM AND PEN_EXPI_ED_DTM) AND (PEN_BUDGET = '1600000' OR ORD_STATUS='대여');";
$row_b = sql_fetch($sql_b);
$count = $row_b["cnt"];

if($count > 0){//잔여금액 점검 시작
*/	
	$sql_b2 = "SELECT SUM(TOTAL_PRICE) total_price1	
	FROM pen_purchase_hist
	WHERE ENT_ID = '".$member["mb_entId"]."' AND PEN_NM = '".$list_detail['FNM']."' AND PEN_LTM_NUM = '".$list_detail['LTC_MGMT_NO']."' 
	AND (CNCL_YN = '변경' OR CNCL_YN = '정상')
	AND ('".date("Ymd")."' BETWEEN PEN_EXPI_ST_DTM AND PEN_EXPI_ED_DTM) ;";
	$row = sql_fetch($sql_b2);
	$total_price1 = (!$row["total_price1"])? 0 : $row["total_price1"];
	$pen_budget = 1600000-$total_price1;
	
	$sql_u = "UPDATE pen_purchase_hist set PEN_BUDGET='".$pen_budget."'
	where ENT_ID = '".$member["mb_entId"]."' AND PEN_NM = '".$list_detail['FNM']."' AND PEN_LTM_NUM = '".$list_detail['LTC_MGMT_NO']."' 
	AND ('".date("Y-m-d")."' BETWEEN PEN_EXPI_ST_DTM AND PEN_EXPI_ED_DTM)";
	sql_query($sql_u);//잔여금액 업데이트

//}
$sql_m = "select count(id) cnt from macro_request where mb_id='{$member['mb_id']}' and recipient_name='".$list_detail['FNM']."' and recipient_num='".str_replace("L","",$list_detail['LTC_MGMT_NO'])."'";
$row_m = sql_fetch($sql_m);
$count = $row_m["cnt"];
if($count>0){
	$sql_mu = "update macro_request set rem_amount='".$pen_budget."' where mb_id='{$member['mb_id']}' and recipient_name='".$list_detail['FNM']."' and recipient_num='".str_replace("L","",$list_detail['LTC_MGMT_NO'])."'";
	sql_query($sql_mu);
}

json_response(200, 'OK', array(
  'sql' => $sql_mu,
  'rem_amount' => $pen_budget,
));
?>
