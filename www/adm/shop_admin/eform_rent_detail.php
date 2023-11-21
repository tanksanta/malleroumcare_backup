<?php
$sub_menu = '500900';
include_once('./_common.php');
//모두싸인 연동=====================================================================
$API_Key64 = base64_encode(G5_MDS_ID.":".G5_MDS_KEY); //API 접속 base64 인코딩 키
//$client = new \GuzzleHttp\Client();
//===============================================================================
auth_check($auth[$sub_menu], "r");

$g5['title'] = '대여계약 급여제공기록 상세';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 검색처리
$select = array();
$where = array();

$uuid = isset($_GET['dc_id']) ? get_search_string($_GET['dc_id']) : '';



// select 배열 처리
$select[] = "E.*";
$select[] = "ef.penNm";
$select[] = "ef.penRecGraNm";
$select[] = "ef.penTypeNm";
$select[] = "ef.penLtmNum";
$select[] = "ef.penExpiDtm";
$select[] = "ef.penJumin";
$select[] = "ef.penAddr";
$select[] = "ef.penConNum";
$select[] = "mb.mb_id";
$select[] = "mb.mb_giup_boss_name as entCeoNm";
$select[] = "mb.sealFile as dc_signUrl";
$sql_select = implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}
$sql_join .=" LEFT OUTER JOIN `eform_document` AS ef ON E.penId = ef.penId AND ef.dc_id =(
	SELECT dc_id FROM`eform_document` WHERE penId = E.penId ORDER BY dc_datetime DESC LIMIT 1
) ";
$sql_join .=" LEFT OUTER JOIN `g5_member` AS mb ON mb_entId=E.entId ";
$sql_from = " FROM `eform_rent_hist` E";
$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . " and E.rh_id = '".$uuid."'");
//echo "SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . " and HEX(E.dc_id) = '".$uuid."'";

$row=sql_fetch_array($result);

$rh_status = $row["rh_status"];
$dc_sign_request_datetime = $row["dc_sign_request_datetime"];
$dc_sign_send_datetime = $row["dc_sign_send_datetime"];
$dc_subject = $row["dc_subject"];

switch($row['contract_sign_relation']){
	case '1':
	$contract_sign_relation_con = "가족";
	break;
	case '2':
	$contract_sign_relation_con = "친족";
	break;
	case '3':
	$contract_sign_relation_con = "기타(".$_POST['contract_sign_relation_nm'].")";
	break;
	default:
	$contract_sign_relation_con = "본인";
	break;
}

$it_id = explode(",",$row['it_ids']);
$row_count = count($it_id);
$it_date = explode(",",$row['it_dates']);

for($ii = 0; $ii < $row_count; $ii++ ){
	$it_dates2[$it_id[$ii]] = $it_date[$ii];
}

function calc_rental_price($str_date, $end_date, $price,$penTypeCd) {
    $rental_price = 0;
	$price22 = array();
    $str_time = strtotime($str_date);
    $end_time = strtotime($end_date);

    $year1 = date('Y', $str_time);
    $year2 = date('Y', $end_time);

    $month1 = date('m', $str_time);
    $month2 = date('m', $end_time);

	$day1 = date('d', $str_time);
    $day2 = date('d', $end_time);

    $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

    // 중간달 계산
    if($diff > 1) {
        $rental_price1 += ( $price * ($diff - 1) );
    }

    
    if($diff == 0){ //년,월 차이 없이 일만 차이 있을 경우
		$rental_price2 += (int) (round(
			$price * (
				($end_date-$str_date+1)
				/
				( date('t', $end_time)*10 )
			))*10
		) ;
	}else{// 마지막 달 계산 
		$rental_price2 += (int) (round(
			$price * (
				date('j', $end_time)
				/
				( date('t', $end_time) * 10 )
			)
		)) * 10;
	}

    if($diff > 0) {
        // 첫째 달 계산
        $rental_price3 += (int) (round(
            $price * (
                ( date('t', $str_time) - date('j', $str_time) + 1 )
                /
                ( date('t', $str_time) * 10 )
            )
        )) * 10;
    }

	$rental_price = $rental_price1+$rental_price2+$rental_price3;
    $price22["calc_rental_price"] = $rental_price;//$rental_price;
	if($diff == 0){//단기계약
		$price22["calc_pen_price"] = calc_pen_price(($penTypeCd), ($rental_price2),2);
	}else{//일반계약
		$price22["calc_pen_price"] = calc_pen_price(($penTypeCd), $rental_price1+$rental_price2+$rental_price3,1);
	}

	return $price22;
}

function calc_pen_price($penTypeCd, $price,$round_floor) {
	switch($penTypeCd) {
        case '00':
            $rate = 15;
            break;
        case '01':
            $rate = 9;
            break;
        case '02':
        case '03':
            $rate = 6;
            break;
        case '04':
            return 0;
        default:
            $rate = 15;
            break;
    }
	
	if($round_floor == 2){
		$pen_price =  (int)round(
			$price * ($rate / 100)/10
		) * 10;
	}else{
		$pen_price =  (int)floor(
			$price * ($rate/ 100)/10
		)* 10 ;
	}

    return $pen_price;
}
?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding:10px 0px;">
	<b>급여제공기록지 정보</b><br><br>
	<table style="display:inline;position:relative;text-align:center;" cellpadding="5">
    <tr style="background:#333333;font-weight:bold;color:#ffffff;">
		<td width="200">생성일자</td>

    </tr>
    <tr>	
		<td><?=substr($row["reg_date"],0,16); ?></td>
    </tr>
    </table>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding:10px 0px;">
    <b>수급자(확인자) 정보</b><br><br>
	<table style="display:inline;position:relative;text-align:center;" cellpadding="5">
    <tr style="background:#333333;text-align:center;font-weight:bold;color:#ffffff;">
		<td width="110">수급자명</td>
		<td width="110">장기요양인번호</td>	
		<td width="110">수급자와의 관계</td>	
		<td width="110">생년월일</td>		
		<td width="110">인정등급</td>
		<td width="120">본인부담율</td>
		<td width="180">장기요양인정 유효기간</td>
		<td width="130">전화번호</td>		
		<td width="200">주소</td>
		<td width="100">확인방법</td>
    </tr>
    <tr>
		<td><?=mb_substr($row["penNm"],0,1)."**";//.mb_substr($row["penNm"],-1)); ?></td>
		<td><?=substr($row["penLtmNum"],0,4)."********";//.substr($row["penLtmNum"],7,4)); ?></td>	
		<td><?=$contract_sign_relation_con; ?></td>
		<td><?=substr($row["penJumin"],0,3)."***"; ?></td>		
		<td><?=$row["penRecGraNm"]; ?></td>
		<td><?=$row["penTypeNm"]; ?></td>		
		<td><?=$row["penExpiDtm"]; ?></td>
		<td><?=($row["penConNum"] != "")?substr($row["penConNum"],0,5)."********":""; ?></td>
		<td><?=($row["penAddr"] != "")?mb_substr($row["penAddr"],0,6)."*************":"";//.$row["penAddrDtl"]; ?></td>
		<td><?=($row["penRecTypeCd"]=="02")?"방문":"유선"; ?></td>
    </tr>
    </table>
</div>

<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding:10px 0px;">
	<b>사업소 정보</b><br><br>
	<table style="display:inline;position:relative;text-align:center;" cellpadding="5">
    <tr style="background:#333333;text-align:center;font-weight:bold;color:#ffffff;">
		<td width="250">기관명</td>
		<td width="200">기관번호</td>
		<td width="110">대표자</td>
		<td width="110">서명(이미지)</td>
    </tr>
    <tr>
		<td><?=$row["entNm"]?></td>
		<td><?=$row["entNum"]?></td>
		<td><?=$row["entCeoNm"]?></td>
		<td><img src="<?="/data/file/member/stamp/".$row["dc_signUrl"]; ?>" height="25px;"></td>
    </tr>
    </table>
</div>

<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding:10px 0px;">
	<b>상품 정보</b><br><br>
	<table style="display:inline;position:relative;text-align:center;" cellpadding="5">
    <tr style="background:#333333;text-align:center;font-weight:bold;color:#ffffff;">
		<td width="50">No.</td>
		<td width="450">품목명(제품명)</td>
		<td width="350">장기요양급여바코드</td>
		<td width="200">전체대여계약기간</td>
		<td width="200">급여제공기록지 대여계약기간</td>
		<td width="150">급여가</td>
		<td width="150">본인부담금</td>
		<td width="150">공단부담금</td>
    </tr>
<?php 
$sql = "SELECT * FROM `eform_document_item` WHERE it_id in (".$row["it_ids"].")";
$result = sql_query($sql);
$num = 1;
$total_price = 0;
$total_price_pen = 0;
$total_price_ent = 0;
while($row2=sql_fetch_array($result)){
	$it_rental_price[$row2["it_id"]] = $row2["it_rental_price"];//기준급여비용
	$price = calc_rental_price(str_replace("-","",substr($it_dates2[$row2["it_id"]],0,10)), str_replace("-","",substr($it_dates2[$row2["it_id"]],11,10)), $it_rental_price[$row2["it_id"]],$pen['penTypeCd']);
	$it_price = $price["calc_rental_price"];//대여가(추가)
	$it_price_pen = $price["calc_pen_price"];//본인부담금(추가)
	$it_price_ent = $it_price - $it_price_pen;//공단부담금
?>    
	<tr>
		<td><?=$num?></td>
		<td><?=$row2["it_name"]."(".$row2["ca_name"].")"?></td>
		<td><?=$row2["it_code"]."-".$row2["it_barcode"]; ?></td>
		<td><?=$row2["it_date"];?></td>
		<td><?=$it_dates2[$row2["it_id"]];?></td>

		<td><?=number_format($it_price);?></td>
		<td><?=number_format($it_price_pen);?></td>
		<td><?=number_format($it_price_ent);?></td>
    </tr>
<?php 
	$total_price += $it_price;
	$total_price_pen += $it_price_pen;
	$total_price_ent += $it_price_ent;
	$num++;}?>
	<tr>
		<td colspan="5">합계</td>		
		<td><?=number_format($total_price);?></td>
		<td><?=number_format($total_price_pen);?></td>
		<td><?=number_format($total_price_ent);?></td>
    </tr>
    </table>
</div>
<?php

if($dc_sign_send_datetime != "0000-00-00 00:00:00"){
	$api_url = 'https://api.modusign.co.kr/documents/'.$row["doc_id"];
	$type = "GET";
	$data = "";
	$arrResponse = get_modusign($API_Key64,$api_url,$type,$data);

	switch ($arrResponse["status"]){//문서상태
		case "ON_GOING" : $status = "서명 대기중"; $div = "sign"; break; 
		case "ABORTED" : 
			if($arrResponse["abort"]["type"] == "REJECTION")$status = "서명 거절"; 
			if($arrResponse["abort"]["type"] == "SIGNING_CANCELLED")$status = "서명 취소"; 
			if($arrResponse["abort"]["type"] == "REQUEST_CANCELLATION")$status = "서명요청 취소"; 		
		break; 
		case "COMPLETED" : $status = "서명 완료"; $resend = '<input type="button" value="계약서 재전송" onclick="resend_doc(\''.$arrResponse["id"].'\',\'01071534117\')">'; break; 
	}
	$view_bt = '<input class="btn" style="cursor:pointer;" type="button" value="계약서보기" onclick="view_doc(\''.$arrResponse["file"]["downloadUrl"].'\')">';
	$view_bt2 = ($arrResponse["auditTrail"]["downloadUrl"]!="")?'<input class="btn" style="cursor:pointer;" type="button" value="감사추적인증서보기" onclick="view_doc(\''.$arrResponse["auditTrail"]["downloadUrl"].'\')">':'';

	$p_signedAts[$arrResponse["signings"][0]["participantId"]] = $arrResponse["signings"][0]["signedAt"];
	if($arrResponse["status"] != "ABORTED"){
		$p_stataus = ($p_signedAts[$arrResponse["participants"][0]["id"]] != "")? "서명완료":"서명 대기중";
		$p_signedAt = ($p_signedAts[$arrResponse["participants"][0]["id"]] != "")? date("Y-m-d H:i:s",strtotime($p_signedAts[$arrResponse["participants"][0]["id"]])):"-";
	}else{
		$p_stataus = $status;
		$p_signedAt = ($arrResponse["abort"]["abortedAt"] != "")? date("Y-m-d H:i:s",strtotime($arrResponse["abort"]["abortedAt"])):"-";
	}
?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding:10px 0px;">
	<b>전자서명(모두싸인) 정보</b><br><br>
	<table style="display:inline;position:relative;text-align:center;" cellpadding="5" >
    <tr style="background:#333333;text-align:center;font-weight:bold;color:#ffffff;">
		<td width="450">문서이름</td>
		<td width="200">서명요청일자</td>
		<td width="150">서명방법</td>
		<td width="200">서명완료(거절) 일자</td>
		<td width="150">상태</td>
    </tr>
	<tr>
		<td><?=$arrResponse["title"]?></td>
		<td><?=date("Y-m-d H:i:s",strtotime($arrResponse["createdAt"]));?></td>
		<td><?=($arrResponse["participants"][$i]["signingMethod"]["type"] == "SECURE_LINK")?"웹페이지":"카카오톡"; ?></td>
		<td><?=$p_signedAt;?></td>
		<td><?=$status?></td>
		<!--td><?=$view_bt;?></td>
		<td><?=$view_bt2;?></td-->		
    </tr>
    </table>
	
</div>
<?php }?>
<div class="btn_fixed_top" style="text-align:right;padding-right:15px !important;">
    <a href="./eform_rent.php?<?=explode("?",$_SERVER["REQUEST_URI"])[1]?>" class="btn" style="background:#333333;color:#ffffff;">목록으로 돌아가기</a><?php /*<a href="javascript:dc_view('<?=$rh_status?>','<?=$dc_sign_request_datetime?>');"  class="btn" style="background:#333333;color:#ffffff">계약서보기</a><a href="/shop/eform/downloadReceipt.php?dc_id=<?=$uuid?>" class="btn" style="background:#333333;color:#ffffff">급여비용명세서보기</a>*/?>
</div>

<script>
$(function() {
    	
});

function dc_view(stat,request_datet){
	var d_url;
	if(stat == 2 || stat == 3){//서명완료
		if(request_datet == ""){//기존계약서
			d_url = "/shop/eform/downloadEform.php?dc_id=<?=$uuid?>";
		}else{//모두싸인계약서
			view_doc('<?=$uuid?>');
		}
	}else{//계약서 생성만
		if(request_datet == ""){//기존계약서
			d_url = "/shop/eform/renderEform.php?download=1&dc_id=<?=$uuid?>&entId=<?=$entId?>";
		}else{//모두싸인계약서에서 완료 안된 상태
			view_doc('<?=$uuid?>');
		}
	}
	if(d_url != ""){//기존계약서보기
		window.open(d_url);
	}
}

function view_doc(url){//계약서 보기
	window.open(url, "PopupDoc", "width=1000,height=1000");
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
