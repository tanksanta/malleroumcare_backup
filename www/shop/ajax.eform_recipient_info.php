<?php
include_once("./_common.php");

# 회원검사
if(!$member["mb_id"])
  json_response(500, '접근 권한이 없습니다.');

if(!$_POST["penId"])
  json_response(500, '정상적이지 않은 접근입니다.');


$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId'],
  'penId' => $_POST['penId']
));

if(!$res || $res['errorYN'] == 'Y')
  json_response(500, '서버 오류로 수급자 정보를 불러올 수 없습니다.');

$pen = $res['data'][0];
if(!$pen)
  json_response(500, '수급자 정보가 존재하지 않습니다.');

$alarm_count = 0;//알람 여부
$lastupdate_sub = "";
$penExpiDtm_sub = "";
$penAppDtm_sub = "";

//수급자 정보
$update_date = ($pen['modifyDtm'] != "")? $pen['modifyDtm']:$pen['regDtm'];//최근조회일시 없을경우 등록일시로
$lastupdate = substr($update_date,0,4).'-'.substr($update_date,4,2).'-'.substr($update_date,6,2); //마지막 업데이트일 
$penExpiDtm = $pen['penExpiDtm'];//인정유효기간

$penAppEdDtm = substr($pen['penAppEdDtm'],0,4).'-'.substr($pen['penAppEdDtm'],4,2).'-'.substr($pen['penAppEdDtm'],6,2);//적용기간 마지막날
$penAppStDtm = date('Y-m-d',strtotime($penAppEdDtm." -1 year"));//적용기간 첫날
$penAppStDtm = date('Y-m-d',strtotime($penAppStDtm." +1 day"));//적용기간 첫날

while($penAppStDtm." 00:00:00" > date("Y-m-d H:i:s")){//마지막날이 1년 더 길 때
	$penAppStDtm = date('Y-m-d',strtotime($penAppStDtm." -1 year"));
	$penAppEdDtm = date('Y-m-d',strtotime($penAppEdDtm." -1 year"));	
}
$penAppDtm = $penAppStDtm." ~ ".$penAppEdDtm;
if(date('Y-m-d',strtotime($lastupdate."+30 day")) < date("Y-m-d")){//마지막 업데이트일 30일 점검
	$alarm_count++;
	$lastupdate_sub = " <a href='my_recipient_view.php?id=".$_POST['penId']."' target='_blank'><font style='color:red;font-weight:bold;'>[업데이트 필요]</font></a>";
}
if(substr($penExpiDtm,13,10) < date("Y-m-d")){//인정유효기간 만료 점검
	$alarm_count++;
	$penExpiDtm_sub = " <a href='my_recipient_view.php?id=".$_POST['penId']."' target='_blank'><font style='color:red;font-weight:bold;'>[기간만료]</font></a>";
}
if(substr($penExpiDtm,13,10) < date("Y-m-d")){//인정유효기간 만료 점검
	$alarm_count++;
	$penAppDtm_sub = " <a href='my_recipient_view.php?id=".$_POST['penId']."' target='_blank'><font style='color:red;font-weight:bold;'>[기간만료]</font></a>";
}
//상품정보
$item_list2 = array();
$it_gubun = array();
$it_gubun["00"] = array();
$it_gubun["01"] = array();
$item_ids = "";

if($_POST["it_id"]){
	for($i = 0; $i < count($_POST["it_id"]);$i++){//상품ID 배열	
		if($item_list2[$_POST["it_id"][$i]]["qty"] == 0 || $item_list2[$_POST["it_id"][$i]]["qty"] == ""){
			$item_list2["it_id"][] = $_POST["it_id"][$i];
			$item_list2["it_gubun"][] = $_POST["it_gubun"][$i];
			if($_POST["it_gubun"][$i] == "대여"){
				$it_gubun["00"][] = $_POST["it_id"][$i];//대여
			}else{
				$it_gubun["01"][] = $_POST["it_id"][$i];//판매
			}
			$item_list2[$_POST["it_id"][$i]]["qty"] = $_POST["it_qty"][$i];//상품 수량
			$item_list2[$_POST["it_id"][$i]]["gubun"] = $_POST["it_gubun"][$i];//상품 판매,대여 구분
			$item_ids .=($item_ids == "")? "'".$_POST["it_id"][$i]."'":",'".$_POST["it_id"][$i]."'";
		}else{
			$item_list2[$_POST["it_id"][$i]]["qty"] += $_POST["it_qty"][$i];
		}
	}

	if(count($_POST["it_id"])>0){
		$sql_i = "select it_id,it_thezone,ProdPayCode,it_name from g5_shop_item where it_id in (".$item_ids.")";
		$result_i = sql_query($sql_i);
		while($row_i = sql_fetch_array($result_i)){
			$item_list2[$row_i["it_id"]]["it_name"] = $row_i["it_name"];//상품명
			$item_list2[$row_i["it_id"]]["it_thezone"] = $row_i["it_thezone"];//더존코드
			$item_list2[$row_i["it_id"]]["ProdPayCode"] = $row_i["ProdPayCode"];//품목코드			
			switch($row_i["it_thezone"]){
				case "ITM2020092200001" : $item_list2[$row_i["it_id"]]["itemNm"] = "이동변기";break;
				case "ITM2020092200002" : $item_list2[$row_i["it_id"]]["itemNm"] = "목욕의자";break;
				case "ITM2020092200003" : $item_list2[$row_i["it_id"]]["itemNm"] = "성인용보행기";break;
				case "ITM2020092200004" : $item_list2[$row_i["it_id"]]["itemNm"] = "안전손잡이";break;
				case "ITM2020092200006" : $item_list2[$row_i["it_id"]]["itemNm"] = "미끄럼방지용품_양말";break;//wmds는 ITM2020092200005가 양말
				case "ITM2020092200005" : $item_list2[$row_i["it_id"]]["itemNm"] = "미끄럼방지용품_매트/방지액";break;//wmds는 ITM2020092200006가 매트
				case "ITM2020092200007" : $item_list2[$row_i["it_id"]]["itemNm"] = "간이변기";break;
				case "ITM2020092200008" : $item_list2[$row_i["it_id"]]["itemNm"] = "지팡이";break;
				case "ITM2020092200009" : $item_list2[$row_i["it_id"]]["itemNm"] = "욕창예방방석";break;
				case "ITM2020092200010" : $item_list2[$row_i["it_id"]]["itemNm"] = "자세변환용구";break;
				case "ITM2020092200011" : $item_list2[$row_i["it_id"]]["itemNm"] = "요실금팬티";break;
				case "ITM2020092200012" : $item_list2[$row_i["it_id"]]["itemNm"] = "수동휠체어";break;
				case "ITM2020092200013" : $item_list2[$row_i["it_id"]]["itemNm"] = "전동침대";break;
				case "ITM2020092200014" : $item_list2[$row_i["it_id"]]["itemNm"] = "수동침대";break;
				case "ITM2020092200015" : $item_list2[$row_i["it_id"]]["itemNm"] = "이동욕조";break;
				case "ITM2020092200016" : $item_list2[$row_i["it_id"]]["itemNm"] = "목욕리프트";break;
				case "ITM2020092200017" : $item_list2[$row_i["it_id"]]["itemNm"] = "배회감지기";break;
				case "ITM2020092200018" : $item_list2[$row_i["it_id"]]["itemNm"] = "경사로(실외용)";break;
				case "ITM2020092200019" : $item_list2[$row_i["it_id"]]["itemNm"] = "욕창예방매트리스";break;//대여
				case "ITM2020092200020" : $item_list2[$row_i["it_id"]]["itemNm"] = "욕창예방매트리스";break;//판매
				case "ITM2021010800001" : $item_list2[$row_i["it_id"]]["itemNm"] = "경사로(실내용)";break;
			}
			$item_nms .= ($item_nms == "")?"'".$item_list2[$row_i["it_id"]]["itemNm"]."'":",'".$item_list2[$row_i["it_id"]]["itemNm"]."'";
		}
	}
}


$res_items = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECT_ITEM_LIST, array(
    'penId' => $_POST['penId']
));

$ym = "";//미끄럼방지용품 PROC_CD 가 있을경우
$sysnon = "";//미끄럼방지용품 시스템미등록 이 있을경우

// $sql_chk_period = "select ENT_ID, PEN_NM,PEN_LTM_NUM,ORD_DTM,ITEM_NM,count(item_NM) as cnt from pen_purchase_hist
// where ITEM_NM not in ('안전손잡이','미끄럼 방지용품','간이변기','자세변환용구','요실금팬티','') and curdate() < DATE_ADD(ORD_DTM, INTERVAL 5 YEAR)
// and ent_id = 'ENT2022052400001' and PEN_LTM_NUM = 'L0011602141' group by ITEM_NM;";

$sql = "select * from pen_purchase_hist where ENT_ID = '".$member['mb_entId']."' and PEN_NM = '".$res['data'][0]['penNm']."' and PEN_LTM_NUM  = '".$res['data'][0]['penLtmNum']."' and ('".date("Y-m-d")."' between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) order by ORD_END_DTM DESC;";
$ct_result = sql_query($sql);
$ct_list = [];
$ct_count = [];//계약완료건
$ct_count2 = [];//판매,대여건
while ($res_item = sql_fetch_array($ct_result)) {
    $res_item['ITEM_NM'] = str_replace(' ','',$res_item['ITEM_NM']);
    $item_nm = $res_item['ITEM_NM'];
	if(str_replace(" ","",$res_item["ITEM_NM"]) == "미끄럼방지용품" && $res_item["PROC_CD"] != ""){
		$ym = "1";
		
		$sql = "select ca_id,ca_id2 from g5_shop_item where ProdPayCode='".$res_item["PROC_CD"]."' order by it_id DESC limit 1";
		$row = sql_fetch($sql);
		
		if(substr($row["ca_id"],0,4) == "1070" || substr($row["ca_id2"],0,4) == "1070"){//미끄럼방지양말
			$res_item['ITEM_NM'] = "미끄럼방지용품_양말";
			
		}elseif(substr($row["ca_id"],0,4) == "1080" || substr($row["ca_id2"],0,4) == "1080"){//미끄럼방지매트
			$res_item['ITEM_NM'] = "미끄럼방지용품_매트/방지액";
		}else{//시스템 미등록
			$res_item['ITEM_NM'] = "미끄럼방지용품_시스템미등록";
			$sysnon = "1";
		}
		$item_nm = "미끄럼방지용품";		
	}
	
	$ct_list[] = $res_item;
    $paycode = $res_item['PROD_PAY_CODE']==1?'1':'0';

    if($res_item['CNCL_YN'] =="정상"){		
		if($ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode]){
			$ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] += 1;
			$where_proc_cd = ($res_item['PROC_CD'] != "")? " and PROC_CD='".$res_item['PROC_CD']."'":"";
			$sql2 = "select count('past_id') as cnt from pen_purchase_hist where ENT_ID = '".$member['mb_entId']."' and PEN_NM = '".$res['data'][0]['penNm']."' and PEN_LTM_NUM  = '".$res['data'][0]['penLtmNum']."' and ('".date("Y-m-d")."' between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) and replace(ITEM_NM,' ','')='".$item_nm."' and CNCL_YN='정상' and PROD_BAR_NUM='".$res_item['PROD_BAR_NUM']."'".$where_proc_cd;
			if($res_item['ORD_STATUS'] == "대여"){
				$sql2 .= " and ('".date("Y-m-d")."' between ORD_STR_DTM and ORD_END_DTM) and ORD_STATUS='".$res_item['ORD_STATUS']."' ;";
			}
			$bar_row = sql_fetch($sql2);
			if($bar_row["cnt"] == 1){
				$ct_count2[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] += 1;
			}
		}else {
			$ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] = 1;
			$ct_count2[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] = 1;
		}
	}elseif($res_item['CNCL_YN'] =="변경"){
		//$sql2 = "select count('past_id') as cnt from pen_purchase_hist where ENT_ID = '".$member['mb_entId']."' and PEN_NM = '".$res['data'][0]['penNm']."' and PEN_LTM_NUM  = '".$res['data'][0]['penLtmNum']."' and ('".date("Y-m-d")."' between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) and replace(ITEM_NM,' ','')='".$res_item['ITEM_NM']."' and CNCL_YN='정상';";
		//$cncl_row = sql_fetch($sql2);
		//if($cncl_row['cnt'] == '0'){//정상 카운트가 없을 경우 변경을 정상 카운트로 처리
			//if($ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode]){
				$ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] += 1;
				$ct_count2[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] += 1;
			//} else {
			//	$ct_count[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] = 1;
			//	$ct_count2[str_replace(' ','',$res_item['ITEM_NM']).'0'.$paycode] = 1;
			//}
		//}
	}
}


if($res_items['data']){
	for($i=0;$i<count($res_items['data']);$i++){//WMDS 카테고리명 변경 시를 대비해서 itemId로 카테고리명 매칭 작업
		switch($res_items['data'][$i]["itemId"]){
			case "ITM2020092200001" : $res_items['data'][$i]["itemNm"] = "이동변기";break;
			case "ITM2020092200002" : $res_items['data'][$i]["itemNm"] = "목욕의자";break;
			case "ITM2020092200003" : $res_items['data'][$i]["itemNm"] = "성인용보행기";break;
			case "ITM2020092200004" : $res_items['data'][$i]["itemNm"] = "안전손잡이";break;
			case "ITM2020092200005" : $res_items['data'][$i]["itemNm"] = "미끄럼방지용품_양말";break;
			case "ITM2020092200006" : $res_items['data'][$i]["itemNm"] = "미끄럼방지용품_매트/방지액";break;
			case "ITM2020092200007" : $res_items['data'][$i]["itemNm"] = "간이변기";break;
			case "ITM2020092200008" : $res_items['data'][$i]["itemNm"] = "지팡이";break;
			case "ITM2020092200009" : $res_items['data'][$i]["itemNm"] = "욕창예방방석";break;
			case "ITM2020092200010" : $res_items['data'][$i]["itemNm"] = "자세변환용구";break;
			case "ITM2020092200011" : $res_items['data'][$i]["itemNm"] = "요실금팬티";break;
			case "ITM2020092200012" : $res_items['data'][$i]["itemNm"] = "수동휠체어";break;
			case "ITM2020092200013" : $res_items['data'][$i]["itemNm"] = "전동침대";break;
			case "ITM2020092200014" : $res_items['data'][$i]["itemNm"] = "수동침대";break;
			case "ITM2020092200015" : $res_items['data'][$i]["itemNm"] = "이동욕조";break;
			case "ITM2020092200016" : $res_items['data'][$i]["itemNm"] = "목욕리프트";break;
			case "ITM2020092200017" : $res_items['data'][$i]["itemNm"] = "배회감지기";break;
			case "ITM2020092200018" : $res_items['data'][$i]["itemNm"] = "경사로(실외용)";break;
			case "ITM2020092200019" : $res_items['data'][$i]["itemNm"] = "욕창예방매트리스";break;
			case "ITM2020092200020" : $res_items['data'][$i]["itemNm"] = "욕창예방매트리스";break;
			case "ITM2021010800001" : $res_items['data'][$i]["itemNm"] = "경사로(실내용)";break;
		}
	}
}
if($sysnon == "1"){
	$key_list = ['이동변기01'=>'1','목욕의자01'=>'1','안전손잡이01'=>'10','미끄럼방지용품_양말01'=>'6','미끄럼방지용품_매트/방지액01'=>'5','미끄럼방지용품_시스템미등록01'=>'5','간이변기01'=>'2',
	'지팡이01'=>'1','욕창예방매트리스01'=>'1','욕창예방방석01'=>'1','자세변환용구01'=>'5','성인용보행기01'=>'2','요실금팬티01'=>'4','경사로(실내용)01'=>'6','수동휠체어00'=>'1',
	'전동침대00'=>'1','욕창예방매트리스00'=>'1','이동욕조00'=>'1','목욕리프트00'=>'1','배회감지기00'=>'1','경사로(실외용)00'=>'1','수동침대00'=>'1'];
	$res_items['data'][$i]["itemNm"] = "미끄럼방지용품_시스템미등록";
	$res_items['data'][$i]["gubun"] = "00";
}else{
	$key_list = ['이동변기01'=>'1','목욕의자01'=>'1','안전손잡이01'=>'10','미끄럼방지용품_양말01'=>'6','미끄럼방지용품_매트/방지액01'=>'5','간이변기01'=>'2',
	'지팡이01'=>'1','욕창예방매트리스01'=>'1','욕창예방방석01'=>'1','자세변환용구01'=>'5','성인용보행기01'=>'2','요실금팬티01'=>'4','경사로(실내용)01'=>'6','수동휠체어00'=>'1',
	'전동침대00'=>'1','욕창예방매트리스00'=>'1','이동욕조00'=>'1','목욕리프트00'=>'1','배회감지기00'=>'1','경사로(실외용)00'=>'1','수동침대00'=>'1'];
}

if($res_items['data'])
    $penToolList = $res_items["data"];

$used_period = ['이동변기01'=>'5','목욕의자01'=>'5','성인용보행기01'=>'5','지팡이01'=>'2','욕창예방매트리스01'=>'3',
'욕창예방방석01'=>'3','경사로(실내용)01'=>'2'
];

/*
$used_period = ['이동변기01'=>'5','목욕의자01'=>'5','성인용보행기01'=>'5','지팡이01'=>'2','욕창예방매트리스01'=>'3',
'욕창예방방석01'=>'3','경사로(실내용)01'=>'2','전동침대00'=>'10','수동침대00'=>'10','수동휠체어00'=>'5',
'욕창예방매트리스00'=>'3','경사로(실외용)00'=>'8','이동욕조00'=>'5','배회감지기00'=>'5','목욕리프트00'=>'3'
];
*/


$item_list = [];
for($ind = 0; $ind < count($penToolList) ; $ind++ ){
    $gubun = $penToolList[$ind]['gubun'] == '00'?'01':'00';
    $item_list[] = $penToolList[$ind]['itemNm'].$gubun;
}

for ($idx = 0; $idx < count($key_list) ; $idx++ ){
    if(in_array(array_keys($key_list)[$idx], $item_list)){
        $penToolRefCnt[array_keys($key_list)[$idx]] = array_values($key_list)[$idx];
    } else {
        $penToolRefCnt[array_keys($key_list)[$idx]] = -1;
    }
}

if(substr($_POST['penLtmNum'],0,2)=='LL'){
    $_POST['penLtmNum'] = substr($_POST['penLtmNum'], 1);
}
/*
$arr_hist = [];//사용 내역 전체 
$sql_hist = "select * from pen_purchase_hist where ent_id = '{$member['mb_entId']}' and PEN_LTM_NUM = 'L{$_POST['penLtmNum']}' and ORD_STATUS='판매' and CNCL_YN='정상'";
if($item_nms != ""){
	$sql_hist .= " and ITEM_NM IN (".$item_nms.")";
}
$res_hist = sql_query($sql_hist);
while ($hist_result = sql_fetch_array($res_hist)) {
    $arr_hist[] = $hist_result;

}
*/
$arr_period = [];
$sql_period = "
(select PEN_NM, PROD_PAY_CODE, replace(ITEM_NM,' ','') as ITEM_NM, PROD_BAR_NUM, PROD_NM, ORD_DTM, PEN_EXPI_ST_DTM, PEN_EXPI_ED_DTM,CNCL_YN from pen_purchase_hist where ent_id = '{$member['mb_entId']}' and PEN_LTM_NUM = 'L{$_POST['penLtmNum']}' 
and ITEM_NM in ('성인용보행기','경사로(실내용)') order by ITEM_NM desc)
UNION
(select PEN_NM, PROD_PAY_CODE, replace(ITEM_NM,' ','') as ITEM_NM, PROD_BAR_NUM, PROD_NM, max(ORD_DTM) as ORD_DTM, PEN_EXPI_ST_DTM, PEN_EXPI_ED_DTM,CNCL_YN from pen_purchase_hist where ent_id = '{$member['mb_entId']}' and PEN_LTM_NUM = 'L{$_POST['penLtmNum']}'
and ITEM_NM not in ('안전손잡이','미끄럼 방지용품','간이변기','자세변환용구','요실금팬티','성인용보행기','경사로(실내용)') AND PROD_PAY_CODE = 1 group by ITEM_NM order by ITEM_NM desc);
";

$period_result = sql_query($sql_period);
while ($res_item_period = sql_fetch_array($period_result)) {
    if($res_item_period['PROD_PAY_CODE'] == '1'){
        $res_item_period['items'] = $res_item_period['ITEM_NM'].'01';
        $res_item_period['period'] = $used_period[$res_item_period['items']];
    } else {
        $res_item_period['items'] = $res_item_period['ITEM_NM'].'00';
        $res_item_period['period'] = $used_period[$res_item_period['items']];
    }
    $arr_period[] = $res_item_period;
}

$prod_period = $arr_period;
$cnt_period = array();//전체 사용 중 사용연안중에 있는 제품 구하기
        for($i = 0; $i < count($prod_period); $i++){
            $deadline_date = $prod_period[$i]['ORD_DTM'];
            $deadline_date = date("Y-m-d",strtotime($prod_period[$i]['ORD_DTM']." -1 days"));
            $deadline_date = date("Y-m-d",strtotime($deadline_date." +".$prod_period[$i]['period']." years"));

            $paycode = $prod_period[$i]['PROD_PAY_CODE'] == '1' ?'01':'00';
            if($deadline_date > Date("Y-m-d")){
                $cnt_period[$prod_period[$i]['ITEM_NM'].$paycode] = $cnt_period[$prod_period[$i]['ITEM_NM'].$paycode]==null? 1: $cnt_period[$prod_period[$i]['ITEM_NM'].$paycode]+1;
            }
        }



/*
$arr_category = [];
$sql_cate = "select ca_id, if(ca_id='10d0','경사로(실내용)',if(ca_id='2020','경사로(실외용)',ca_name)) as ca_name from g5_shop_category where char_length(ca_id) = 4 and left(ca_id,2) in ('10','20');";
$cate_result = sql_query($sql_cate);
while ($res_cate = sql_fetch_array($cate_result)) {
    $arr_category[$res_cate['ca_name']] = $res_cate['ca_id'];
}
*/
$penToolRefCnt;//급여가능, 불가 판별 (-1 : 급여불가 품목)

$rows["lastupdate"] = $lastupdate.$lastupdate_sub;//마지막 업데이트일
$rows["penExpiDtm"] = $penExpiDtm.$penExpiDtm_sub;//인정유효기간
$rows["penAppDtm"] = $penAppDtm.$penAppDtm_sub;//적용기간


$html .= '판매계약<br>';
$html .= '			<table>';
$html .= '			<colgroup><col width="55%"><col width="15%"><col width="30%"></colgroup>';
$html .= '			<tr><th>품목명(상품명)</th><th>수량</th><th>계약가능</th></tr>';
$it_gubun["00"] = (is_array($it_gubun["00"]))?$it_gubun["00"]:array();
$it_gubun["01"] = (is_array($it_gubun["01"]))?$it_gubun["01"]:array();

if(count($it_gubun["01"]) == 0){
	$html .= '	<tr><td colspan="3">계약 품목이 없습니다.</td></tr>';
}else{
	for($j = 0; $j<count($it_gubun["01"]);$j++){//판매
		if($penToolRefCnt[$item_list2[$it_gubun["01"][$j]]["itemNm"].'01'] == -1){//계약가능
			$abled_count_01 = '<font style="color:red;font-weight:bold;">급여불가</font>';
			$alarm_count++;
		}else{
			$item_period = ($cnt_period[$item_list2[$it_gubun["01"][$j]]["itemNm"].'01'] == null)? 0: $cnt_period[$item_list2[$it_gubun["01"][$j]]["itemNm"].'01'];
			$cnt2 = ($ct_count2[$item_list2[$it_gubun["01"][$j]]["itemNm"].'01'] == null)? 0: $ct_count2[$item_list2[$it_gubun["01"][$j]]["itemNm"].'01'];
			$item_period = ($item_period==0)?0:$item_period-$cnt2;
			$sale_count = $penToolRefCnt[$item_list2[$it_gubun["01"][$j]]["itemNm"].'01']-$cnt2-$item_period;

			if($item_list2[$it_gubun["01"][$j]]["itemNm"] == "욕창예방매트리스" ){
				$duplication = 0;
				for($k = 0; $k<count($it_gubun["00"]);$k++){
					$duplication = ($item_list2[$it_gubun["00"][$k]]["itemNm"] == "욕창예방매트리스")?"1":"";
				}

				if($ct_count2['욕창예방매트리스00'] > 0){
					$abled_count_01 = '<font style="color:red;font-weight:bold;">0 개<br>계약한도초과</font>';
					$alarm_count++;
				}elseif($duplication == 1 ){

						$abled_count_01 = '<font style="color:red;font-weight:bold;">'.($sale_count).' 개<br>'.(($sale_count==0)?'계약한도초과':'판매&대여품목').'</font>';

					$alarm_count++;
				}

			}elseif(($sale_count-$item_list2[$it_gubun["01"][$j]]["qty"])<0){//수량초과
				$abled_count_01 = '<font style="color:red;font-weight:bold;">'.($sale_count).' 개<br>계약한도초과</font>';
				$alarm_count++;
			}else{
				$abled_count_01 = ($sale_count).' 개';
			}
		}
		if($sysnon == "1" && ($item_list2[$it_gubun["01"][$j]]["itemNm"] == "미끄럼방지용품_매트/방지액" || $item_list2[$it_gubun["01"][$j]]["itemNm"] == "미끄럼방지용품_양말")){
			$html .= '	<tr><td><a href="my_recipient_view.php?id='.$_POST['penId'].'" target="_blank" style="color:red;font-weight:bold;">'.$item_list2[$it_gubun["01"][$j]]["itemNm"].'<br>('.$item_list2[$it_gubun["01"][$j]]["it_name"].')</a></td><td><a href="my_recipient_view.php?id='.$_POST['penId'].'" target="_blank" style="color:red;font-weight:bold;">'.$item_list2[$it_gubun["01"][$j]]["qty"].' 개</a></td><td><a href="my_recipient_view.php?id='.$_POST['penId'].'" target="_blank" style="color:red;font-weight:bold;">미등록 제품<br>계약이력 확인</a></td></tr>';
			$alarm_count++;
		}else{
			$html .= '	<tr><td>'.$item_list2[$it_gubun["01"][$j]]["itemNm"].'<br>('.$item_list2[$it_gubun["01"][$j]]["it_name"].')</td><td>'.$item_list2[$it_gubun["01"][$j]]["qty"].' 개</td><td>'.$abled_count_01.'</td></tr>';
		}
	}
}

$html .= '	</table><br>대여계약<br>';
$html .= '			<table>';
$html .= '			<colgroup><col width=55%"><col width="15%"><col width="30%"></colgroup>';
$html .= '			<tr><th>품목명(상품명)</th><th>수량</th><th>계약가능</th></tr>';

if(count($it_gubun["00"]) == 0){
	$html .= '	<tr><td colspan="3">계약 품목이 없습니다.</td></tr>';
}else{
	for($k = 0; $k<count($it_gubun["00"]);$k++){//대여
		if($penToolRefCnt[$item_list2[$it_gubun["00"][$k]]["itemNm"].'00'] == -1){//계약가능
			$abled_count_00 = '<font style="color:red;font-weight:bold;">급여불가</font>';
			$alarm_count++;
		}else{
			$item_period = ($cnt_period[$item_list2[$it_gubun["00"][$k]]["itemNm"].'00'] == null)? 0: $cnt_period[$item_list2[$it_gubun["00"][$k]]["itemNm"].'00'];
			$cnt2 = ($ct_count2[$item_list2[$it_gubun["00"][$k]]["itemNm"].'00'] == null)? 0: $ct_count2[$item_list2[$it_gubun["00"][$k]]["itemNm"].'00'];
			$item_period = ($item_period==0)?0:$item_period-$cnt2;
				
			$rent_count = (($penToolRefCnt[$item_list2[$it_gubun["00"][$k]]["itemNm"].'00']-$cnt2-$item_period))<0?0:($penToolRefCnt[$item_list2[$it_gubun["00"][$k]]["itemNm"].'00']-$cnt2-$item_period);
			
			if($item_list2[$it_gubun["00"][$k]]["itemNm"] == "욕창예방매트리스" ){
				$duplication = 0;
				for($j = 0; $j<count($it_gubun["01"]);$j++){
					$duplication = ($item_list2[$it_gubun["01"][$j]]["itemNm"] == "욕창예방매트리스")?"1":"";
				}

				if($ct_count2['욕창예방매트리스01'] > 0){
					$abled_count_00 = '<font style="color:red;font-weight:bold;">0 개<br>계약한도초과</font>';
					$alarm_count++;
				}elseif($duplication == 1 ){
					$abled_count_00 = '<font style="color:red;font-weight:bold;">'.($rent_count).' 개<br>'.(($rent_count == 0)?'계약한도초과':'판매&대여품목').'</font>';
					$alarm_count++;
				}

			}elseif(($rent_count-$item_list2[$it_gubun["00"][$k]]["qty"])<0){//수량초과
				$abled_count_00 = '<font style="color:red;font-weight:bold;">'.($rent_count).' 개<br>계약한도초과</font>';
				$alarm_count++;
			}else{
				$abled_count_00 = ($rent_count).' 개';
			}
		}

		$html .= '	<tr><td>'.$item_list2[$it_gubun["00"][$k]]["itemNm"].'<br>('.$item_list2[$it_gubun["00"][$k]]["it_name"].')</td><td>'.$item_list2[$it_gubun["00"][$k]]["qty"].' 개</td><td>'.$abled_count_00.'</td></tr>';
	}
}

$html .= '	</table>';

for($h = 0; $h<count($cnt_period);$h++){
	//$test .= implode( '/',$cnt_period[$h])."<br>";
}
//$html .= $sql_period;
$rows["alarm_count"] = $alarm_count;//알람 여부
$rows["html"] = $html;//$html;
//$rows["test"] = $pen['penAppEdDtm'];//$html;

header('Content-type: application/json');
echo json_encode($rows);
