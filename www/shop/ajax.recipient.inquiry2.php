<?php

include_once("./_common.php");

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');


$sql = "select * from macro_request where mb_id='{$member['mb_id']}' and recipient_name='{$rn}' and recipient_num='{$id}'";//수급자정보,구매가능 품폭
$row = sql_fetch( $sql );
/*
$obj_purchaseHistory->lendRunway = $row["lendRunway"]; //경사로(실외용)-대여
$obj_purchaseHistory->loiteringDetection = $row["loiteringDetection"]; //배회감지기-대여
$obj_purchaseHistory->bathLift = $row["bathLift"]; //목욕리프트-대여
$obj_purchaseHistory->portableBath = $row["portableBath"]; //이동욕조-대여
$obj_purchaseHistory->lendBedsorePreventionMatriss = $row["lendBedsorePreventionMatriss"]; //욕창예방 매트리스-대여
$obj_purchaseHistory->mBed = $row["mBed"]; //수동침대-대여
$obj_purchaseHistory->eBed = $row["eBed"]; //전동침대-대여
$obj_purchaseHistory->mWheelChair = $row["mWheelChair"]; //수동휠체어-대여
$obj_purchaseHistory->incontinencePanty = $row["incontinencePanty"]; //요실금팬티
$obj_purchaseHistory->movingToilet = $row["movingToilet"]; //이동변기
$obj_purchaseHistory->runway = $row["runway"]; //경사로(실내용)
$obj_purchaseHistory->adultWalker = $row["adultWalker"]; //성인용보행기
$obj_purchaseHistory->bedsorePreventMatriss = $row["bedsorePreventMatriss"]; //욕창예방 매트리스
$obj_purchaseHistory->postureChangeTool = $row["postureChangeTool"]; //자세변환용구
$obj_purchaseHistory->cushionPreventMatriss = $row["cushionPreventMatriss"]; //욕창예방방석
$obj_purchaseHistory->cane = $row["cane"]; //지팡이
$obj_purchaseHistory->simpleToilet = $row["simpleToilet"]; //간이변기
$obj_purchaseHistory->safetyPreventSlivery = $row["safetyPreventSlivery"]; //미끄럼방지용품(매트,방지액)
$obj_purchaseHistory->sliveryPreventSocks = $row["sliveryPreventSocks"]; //미끄럼방지양말
$obj_purchaseHistory->safetyHandGrip = $row["safetyHandGrip"]; //안전손잡이
$obj_purchaseHistory->bathingChair = $row["bathingChair"]; //목욕의자

$arr_ph = (array) $obj_purchaseHistory;
*/
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["REDUCE_NM"] = $row["type"];
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["SBA_CD"] = $row["type"]." ".$row["percent"];
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["LTC_RCGT_GRADE_CD"] = str_replace("등급","",$row["grade"]);
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["RCGT_EDA_DT"] = $row["penExpiDtm"];//인정유효기간
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["applydtm"] = $row["penApplyDtm"];//적용기간
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["REMN_AMT"] = $row["rem_amount"];//잔액
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["USE_AMT"] = (1600000-$row["rem_amount"]);//사용금액
$recipientContractDetail["Result"]["ds_welToolTgtList"][0]["UPDATE"] = ($row["updated_at"] == "")?$row["regdt"]:$row["updated_at"];//업데이트 
//판매
$recipientToolList["Result"]["ds_payPsbl1"] = array();
$recipientToolList["Result"]["ds_payPsbl2"] = array();
if($row["movingToilet"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "이동변기";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "이동변기";}//이동변기
if($row["bathingChair"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "목욕의자";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "목욕의자";}//목욕의자
if($row["safetyHandGrip"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "안전손잡이";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "안전손잡이";}//안전손잡이
if($row["safetyPreventSlivery"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "미끄럼방지용품";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "미끄럼방지용품";}//미끄럼방지용품
if($row["simpleToilet"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "간이변기";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "간이변기";}//간이변기
if($row["cane"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "지팡이";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "지팡이";}//지팡이
if($row["cushionPreventMatriss"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "욕창예방방석";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "욕창예방방석";}//욕창예방방석
if($row["bedsorePreventMatriss"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "욕창예방 매트리스";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "욕창예방 매트리스";}//욕창예방매트리스
if($row["postureChangeTool"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "자세변환용구";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "자세변환용구";}//자세변환용구
if($row["adultWalker"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "성인용보행기";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "성인용보행기";}//성인용보행기
if($row["incontinencePanty"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "요실금팬티";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "요실금팬티";}//요실금팬티
if($row["runway"] > 0){$recipientToolList["Result"]["ds_payPsbl1"][]["WIM_ITM_CD"] = "경사로(실내용)";}else{$recipientToolList["Result"]["ds_payPsbl2"][]["WIM_ITM_CD"] = "경사로(실내용)";}//경사로(실내용)

//대여
$recipientToolList["Result"]["ds_payPsblLnd1"] = array();
$recipientToolList["Result"]["ds_payPsblLnd2"] = array();
if($row["mWheelChair"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "수동휠체어";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "수동휠체어";}//수동휠체어
if($row["eBed"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "전동침대";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "전동침대";}//전동침대
if($row["mBed"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "수동침대";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "수동침대";}//수동침대
if($row["lendBedsorePreventionMatriss"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "욕창예방 매트리스";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "욕창예방 매트리스";}//욕창예방매트리스
if($row["portableBath"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "이동욕조";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "이동욕조";}//이동욕조
if($row["bathLift"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "목욕리프트";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "목욕리프트";}//목욕리프트
if($row["loiteringDetection"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "배회감지기";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "배회감지기";}//배회감지기
if($row["lendRunway"] > 0){$recipientToolList["Result"]["ds_payPsblLnd1"][]["WIM_ITM_CD"] = "경사로(실외용)";}else{$recipientToolList["Result"]["ds_payPsblLnd2"][]["WIM_ITM_CD"] = "경사로(실외용)";}//경사로(실외용)

$recipientContractHistory['Result']['ds_result'] = null;
$PEN_EXPI_ST_DTM = substr($row["penApplyDtm"],0,10);
$PEN_EXPI_ED_DTM = substr($row["penApplyDtm"],13,10);
//$sql2 = "select * from pen_purchase_hist where ENT_ID='{$member['mb_entId']}' and PEN_NM='{$rn}' and PEN_LTM_NUM ='L{$id}' and PEN_EXPI_ST_DTM>='{$PEN_EXPI_ST_DTM}' and PEN_EXPI_ED_DTM<='{$PEN_EXPI_ED_DTM}' order by ORD_END_DTM DESC";//구매한 품목
$sql2 = "select * from pen_purchase_hist where ENT_ID='{$member['mb_entId']}' and PEN_NM='{$rn}' and PEN_LTM_NUM ='L{$id}' and ('".date("Y-m-d")."' between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) order by ORD_END_DTM DESC";//구매한 품목
$result = sql_query($sql2);
$i = 0;
while ($res_item = sql_fetch_array($result)) {
	$recipientContractHistory['Result']['ds_result'][$i]['WLR_MTHD_CD'] = $res_item["ORD_STATUS"];//판매,대여 구분
	$recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'] = str_replace(" ","",$res_item["ITEM_NM"]);//품명명
	$recipientContractHistory['Result']['ds_result'][$i]['MGDS_NM'] = $res_item["PROD_NM"];//제품명
	$recipientContractHistory['Result']['ds_result'][$i]['POF_FR_DT'] = $res_item["ORD_STR_DTM"]."~".$res_item["ORD_END_DTM"];//기간
	$recipientContractHistory['Result']['ds_result'][$i]['TOT_AMT'] = $res_item["TOTAL_PRICE"];//급여가
	$recipientContractHistory['Result']['ds_result'][$i]['CNCL_YN'] = $res_item["CNCL_YN"];//계약상태
	$recipientContractHistory['Result']['ds_result'][$i]['PROD_BAR_NUM'] = $res_item["PROD_BAR_NUM"];//바코드
	$i++;
}

//print_r($arr_ph);
//print_r(json_encode($arr_ph));

//echo ("<br>");
//print_r($response_arr['Result']['Result']);
//print_r($response_arr['Result']['ds_Result']);
//print_r($response_arr['Result']['ds_welToolTgtList']);
//foreach($response_arr['Result']['ds_Result'] as $key ==> $value)
//{
//	echo $key;
//	echo $value";
//echo ("<br>");
//}
// 복지용구급여가능불가능품목조회

return json_response(200, '조회가 완료되었습니다.', array(
  'penId' => $penId,
  'recipientContractDetail' => $recipientContractDetail,
  'recipientToolList' => $recipientToolList,
  'recipientContractHistory' => $recipientContractHistory,
  'recipientPurchaseRecord' => json_encode($arr_ph), 
  'sql' => $sql2
));
?>
