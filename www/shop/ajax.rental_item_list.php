<?php
include_once("./_common.php");

# 회원검사
if(!$member["mb_id"])
  json_response(500, '접근 권한이 없습니다.');

if(!$_POST["id"])
  json_response(500, '정상적이지 않은 접근입니다.');

if(!$_POST["c_month"])
  json_response(500, '생성월 정보가 누락 되었습니다.');

$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId'],
  'penId' => $_POST['id']
));

if(!$res || $res['errorYN'] == 'Y')
  json_response(500, '서버 오류로 수급자 정보를 불러올 수 없습니다.');

$pen = $res['data'][0];
if(!$pen)
  json_response(500, '수급자 정보가 존재하지 않습니다.');

$day_count = date('t', strtotime($_POST["c_month"]."-01"));//월 마지막날
$rows = array();
$sql = "SELECT a.* FROM `eform_document_item` a 
			INNER JOIN `eform_document` AS b 
			ON a.dc_id = b.dc_id 
			AND b.entId='{$member['mb_entId']}' 
			AND b.dc_status='3'
			AND penId='{$pen['penId']}'
			WHERE a.gubun='01'
			AND a.it_rental_price IS NOT NULL
			AND ('".$_POST["c_month"]."-01' BETWEEN CONCAT(SUBSTR(it_date,1,7),'-01') AND CONCAT(SUBSTR(it_date,12,7),'-31') OR '".date("Y-m",strtotime("-1 month",strtotime($_POST["c_month"]."-01")))."-01' BETWEEN CONCAT(SUBSTR(a.it_date,1,7),'-01') AND CONCAT(SUBSTR(a.it_date,12,7),'-31'))
			ORDER BY SUBSTR(a.it_date,1,10) ASC";
	$result = sql_query($sql);
	$count_01 = sql_num_rows($result);
	if($count_01 == 0){
		$html = "진행중인 대여계약이 없습니다.";
	}else{
		$html = "<table>";
		$html .= "	<colgroup><col width='5%'><col width='25%'><col width='25%'><col width='20%'><col width='25%'></colgroup>";
		$html .= "	<tr><th>선택</th><th>상품명</th><th>품목명</th><th>계약기간</th><th>상품바코드</th></tr>";
		while($row = sql_fetch_array($result)){
			$first_date = substr($row['it_date'],0,10);
			$last_date = substr($row['it_date'],11,10);
			$first_date2 = (substr($first_date,0,7) == $_POST["c_month"])?$first_date:$_POST["c_month"]."-01";
			$last_date2 = (substr($last_date,0,7) == $_POST["c_month"])?$last_date: $_POST["c_month"]."-".$day_count;
			$html .= "	<tr>";
			$html .= "		<td><input type='checkbox' name='it_id[]' data-name='{$row['it_name']}' data-date1='{$first_date2}' data-date2='{$last_date2}' value='{$row['it_id']}'></td>";
			$html .= "		<td>{$row['it_name']}</td>";
			$html .= "		<td>{$row['ca_name']}</td>";
			$html .= "		<td>{$first_date} ~ {$last_date}</td>";
			$html .= "		<td>{$row['it_code']}-{$row['it_barcode']}</td>";
			$html .= "	</tr>";
		}		
		$html .= "</table>";
	}

$rows["html"] = $html;

header('Content-type: application/json');
echo json_encode($rows);