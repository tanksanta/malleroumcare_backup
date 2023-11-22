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
	AND ((!(substr(reg_date,1,7)<'".date("Y-m")."' AND substr(it_end_date,1,7)<'".date("Y-m")."') AND dc_sign_send_datetime='0000-00-00 00:00:00') 
	OR (dc_sign_send_datetime != '0000-00-00 00:00:00' AND rh_status != '0'))  ORDER BY reg_date DESC";
	$result = sql_query($sql);
	$count_01 = sql_num_rows($result);
	$html = "<table>";	
	$html .= "	<colgroup><col width='27%'><col width='30%'><col width='21%'><col width='7%'><col width='18%'></colgroup>";
	$html .= "	<tr style='position: sticky;top: -1px;border: 1px solid #ddd !important;'><th>생성날짜</th><th>상품명</th><th>계약기간</th><th>확인<br>방법</th><th>상태정보</th></tr>";
	
	if($count_01 == 0){
		$html .= "	<tr>";
		$html .= "		<td colspan='5' row='10'>";
		$html .= "대여계약 급여제공기록 이력이 없습니다.";
		$html .= "		</td>";
		$html .= "	</tr>";
	}else{		
		while($row = sql_fetch_array($result)){
			$relation_html = "";
			switch($row['contract_sign_relation']){
				case 1 : $relation_html = "가족"; break;
				case 2 : $relation_html = "친족"; break;
				case 3 : $relation_html = "기타-".$row['pen_guardian_nm']."<br>(".$row['contract_sign_relation_nm'].")"; break;
				default : $relation_html = "본인"; break;
			}
			$penRecTypeCd = ($row['penRecTypeCd'] == "02")?"방문":"유선";
			$it_ids = explode(",",$row['it_ids']);
			$row_count = count($it_ids);
			$it_dates = explode(",",$row['it_dates']);
			for($ii = 0; $ii < $row_count; $ii++ ){
				$it_date[$it_ids[$ii]] = $it_dates[$ii];
			}
			$rowspan = ($row_count == 1)? "" :" rowspan='{$row_count}'";
			$eform_date = $row['confirm_date']."(생성)";//생성일
			$eform_date .= ($row['dc_sign_send_datetime'] != "0000-00-00 00:00:00")? "<br>".substr($row['dc_sign_send_datetime'],0,10)."(요청)": "";//서명요청일
			$eform_date .= ($row['dc_sign_datetime'] != "0000-00-00 00:00:00")? "<br>".substr($row['dc_sign_datetime'],0,10)."(완료)" : "";//서명완료일
			$rh_status = "";
			switch($row['rh_status']){
				case "2": case "3" : $rh_status = "<font color=\"blue\">서명완료</font><br><a href='javascript:mds_download(\"{$row['doc_id']}\");'><b>계약서보기</b></a>";break;
				case "4" : $rh_status = "<a href='javascript:open_sign_stat(\"{$relation_html}\",\"{$row['rh_id']}\",\"{$row['doc_id']}\");' title='서명 진행 상황 보기'>서명진행중</a><br><a href='javascript:mds_download(\"{$row['doc_id']}\");'><b>계약서보기</b></a>";break;
				case "5" : $rh_status = "<a href='javascript:open_rejection_view(\"{$row['rh_id']}\",\"{$row['doc_id']}\");'><font color=\"red\" title='서명 거절 사유 보기'>서명거절</font></a><br><a href='javascript:mds_download(\"	{$row['doc_id']}\");'><b>계약서보기</b></a>";break;
				default: $rh_status = "<a href='javascript:open_send_sign(\"{$row['rh_id']}\",\"{$row['contract_sign_relation']}\",\"{$row['contract_sign_relation_nm']}\",\"{$row['pen_guardian_nm']}\");'>서명요청하기</a><br><a href='javascript:downloadExcel(\"m\",\"{$row['rh_id']}\");'><b>다운로드</b></a>";break;
			}

			$sql2 = "SELECT it_name,it_id FROM eform_document_item WHERE it_id IN ({$row['it_ids']})";
			$result2 = sql_query($sql2);
			$i = 0;
			while($row2 = sql_fetch_array($result2)){
				$it_name[$i] = $row2['it_name'];
				$it_id2[$i] = $row2['it_id'];
				$i++;
			}
			$html .= "	<tr>";
			$html .= "		<td{$rowspan}>{$eform_date}</td>";	
			$html .= "		<td>{$it_name[0]}</td>";
			$html .= "		<td>".substr($it_date[$it_id2[0]],0,10)."<br>".substr($it_date[$it_id2[0]],10,11)."</td>";
			$html .= "		<td{$rowspan}>{$penRecTypeCd}</td>";
			$html .= "		<td{$rowspan}>{$rh_status}</td>";
			$html .= "	</tr>";
			if($row_count != 1){	
				for($j = 1; $j < $row_count; $j++){
					$html .="<tr>";
					$html .= "		<td>{$it_name[$j]}</td>";
					$html .= "		<td>".substr($it_date[$it_id2[$j]],0,10)."<br>".substr($it_date[$it_id2[$j]],10,11)."</td>";
					$html .="</tr>";
				}
			}			
		}			
	}
	$html .= "</table>";
	

$rows["html"] = $html;

header('Content-type: application/json');
echo json_encode($rows);
