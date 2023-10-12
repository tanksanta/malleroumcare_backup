<?php
include_once("./_common.php");

# 회원검사
if(!$member["mb_id"])
  json_response(500, '접근 권한이 없습니다.');

if(!$_POST["id"])
  json_response(500, '정상적이지 않은 접근입니다.');

$last_month = date("Y-m",strtotime("-1 month",time()));//지난달

//대여계약 급여제공기록 이력  
	$sql = "SELECT * FROM `eform_rent_hist` 
	WHERE entId='{$member['mb_entId']}' 
	AND penId='{$_POST['id']}' 
	AND !(substr(reg_date,1,7)<'".date("Y-m")."' AND substr(it_end_date,1,7)<'".date("Y-m")."')  ORDER BY reg_date DESC";
	$result = sql_query($sql);
	$count_01 = sql_num_rows($result);
	$html = "<table>";	
	$html .= "	<colgroup><col width='15%'><col width='30%'><col width='30%'><col width='12%'><col width='13%'></colgroup>";
	$html .= "	<tr style='position: sticky;top: 0px;border: 1px solid #ddd !important;'><th>생성날짜</th><th>상품명</th><th>계약기간</th><th>확인방법</th><th>재생성</th></tr>";
	
	if($count_01 == 0){
		$html .= "	<tr>";
		$html .= "		<td colspan='5' row='10'>";
		$html .= "대여계약 급여제공기록 이력이 없습니다.";
		$html .= "		</td>";
		$html .= "	</tr>";
	}else{		
		while($row = sql_fetch_array($result)){
			$penRecTypeCd = ($row['penRecTypeCd'] == "02")?"방문":"유선";
			$row_count = count(explode(",",$row['it_ids']));
			$rowspan = ($row_count == 1)? "" :" rowspan='{$row_count}'";
			$sql2 = "SELECT it_name,it_date FROM eform_document_item WHERE it_id IN ({$row['it_ids']})";
			$result2 = sql_query($sql2);
			$i = 0;
			while($row2 = sql_fetch_array($result2)){
				$it_name[$i] = $row2['it_name'];
				$it_date[$i] = $row2['it_date'];
				$i++;
			}
			$html .= "	<tr>";
			$html .= "		<td{$rowspan}>{$row['confirm_date']}</td>";	
			$html .= "		<td>{$it_name[0]}</td>";
			$html .= "		<td>{$it_date[0]}</td>";
			$html .= "		<td{$rowspan}>{$penRecTypeCd}</td>";
			$html .= "		<td{$rowspan}><a href='javascript:downloadExcel(\"m\",\"{$row['rh_id']}\");'>다운로드</a></td>";
			$html .= "	</tr>";
			if($row_count != 1){	
				for($j = 1; $j < $row_count; $j++){
					$html .="<tr>";
					$html .= "		<td>{$it_name[$j]}</td>";
					$html .= "		<td>{$it_date[$j]}</td>";
					$html .="</tr>";
				}
			}			
		}			
	}
	$html .= "</table>";
	

$rows["html"] = $html;

header('Content-type: application/json');
echo json_encode($rows);
