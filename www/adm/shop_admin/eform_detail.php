<?php
$sub_menu = '500900';
include_once('./_common.php');
//모두싸인 연동=====================================================================
$API_Key64 = base64_encode(G5_MDS_ID.":".G5_MDS_KEY); //API 접속 base64 인코딩 키
$client = new \GuzzleHttp\Client();
//===============================================================================
auth_check($auth[$sub_menu], "r");

$g5['title'] = '계약서상세보기';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 검색처리
$select = array();
$where = array();

$uuid = isset($_GET['dc_id']) ? get_search_string($_GET['dc_id']) : '';

//$select[] = ' m.mb_id ';
$select[] = ' I.it_name ';
$select[] = ' COUNT(E.dc_id) as it_count ';
$select[] = ' I2.t_price ';
$sql_join = ' LEFT JOIN `eform_document_item` I ON E.dc_id = I.dc_id 
LEFT JOIN (SELECT dc_id, it_name,it_qty,it_price, SUM(it_qty*it_price) AS t_price FROM `eform_document_item` GROUP BY dc_id) I2 ON E.dc_id = I2.dc_id ';

// select 배열 처리
$select[] = "E.*";
$sql_select = "HEX(E.dc_id) as uuid, ".implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}

$sql_from = " FROM `eform_document` E";
$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . " and HEX(E.dc_id) = '".$uuid."'");
//echo "SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . " and HEX(E.dc_id) = '".$uuid."'";

$row=sql_fetch_array($result);

$dc_status = $row["dc_status"];
$dc_sign_request_datetime = $row["dc_sign_request_datetime"];
$dc_subject = $row["dc_subject"];
?>
<div class="local_desc01 local_desc" style="background:#eee">
    <b>계약서 정보</b>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:0px;">
    <div style="float:left;margin-top:15px;">계약서 정보</div>
	<table style="display:inline;left:100px;position:relative;text-align:center;top:-10px;" cellpadding="5">
    <tr style="background:#eeeeee;font-weight:bold;">
		<td width="110">서류확인방법</td>
		<td width="160">계약일자</td>		
		<td width="160">서류 생성일자</td>
		<td width="160">서류 완료일자</td>
    </tr>
    <tr>
		<td><?=($row["penRecTypeCd"] == "00" || $row["penRecTypeCd"] == "02" || $row["penRecTypeCd"] == "")?"방문":"유선";?></td>
		<td><?=$row["do_date"]?></td>		
		<td><?=$row["dc_datetime"]; ?></td>
		<td><?=$dc_sign_datetime = ($row["dc_sign_datetime"] == "0000-00-00 00:00:00")?"-":$row["dc_sign_datetime"];?></td>
    </tr>
    </table>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:0px;">
    <div style="float:left;margin-top:15px;">수급자 정보</div>
	<table style="display:inline;left:100px;position:relative;text-align:center;top:-10px;" cellpadding="5">
    <tr style="background:#eeeeee;text-align:center;font-weight:bold;">
		<td width="110">요양인정번호</td>
		<td width="110">성명</td>	
		<td width="110">생년월일</td>		
		<td width="110">인정등급</td>
		<td width="120">본인부담금율</td>
		<td width="180">유효기간</td>
		<td width="130">전화번호</td>		
		<td width="200">주소</td>
    </tr>
    <tr>
		<td><?=substr($row["penLtmNum"],0,4)."********";//.substr($row["penLtmNum"],7,4)); ?></td>
		<td><?=mb_substr($row["penNm"],0,1)."**";//.mb_substr($row["penNm"],-1)); ?></td>
		<td><?=substr($row["penJumin"],0,3)."***"; ?></td>		
		<td><?=$row["penRecGraNm"]; ?></td>
		<td><?=$row["penTypeNm"]; ?></td>		
		<td><?=$row["penExpiDtm"]; ?></td>
		<td><?=substr($row["penConNum"],0,5)."********"; ?></td>
		<td><?=mb_substr($row["penAddr"],0,6)."*************";//.$row["penAddrDtl"]; ?></td>
    </tr>
    </table>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:0px;">
    <div style="float:left;margin-top:5px;">장기요양/제가서비스<br>신청인 정보</div>
	<table style="display:inline;left:52px;position:relative;text-align:center;top:-10px;" cellpadding="5">
    <tr style="background:#eeeeee;text-align:center;font-weight:bold;">
		<td width="110">사용여부</td>
		<td width="110">성명</td>
		<td width="110">생년월일</td>
		<td width="110">수급자와 관계</td>
		<td width="130">전화번호</td>
		<td width="220">주소</td>
    </tr>
    <tr>
		<td><?=($row["applicantNm"] !="")?"사용": "사용안함";?></td>
		<td><?=($row["applicantNm"])? mb_substr($row["applicantNm"],0,1)."*".mb_substr($row["applicantNm"],-1): "&nbsp;";?></td>
		<td><?=($row["applicantBirth"])?substr($row["applicantBirth"],0,3)."***" : "&nbsp";?></td>
		<td><?=($row["applicantRelation"])?$row["applicantRelation"] : "&nbsp";?></td>
		<td><?=($row["applicantTel"])?substr($row["applicantTel"],0,5)."********" : "&nbsp"; ?></td>
		<td><?=($row["applicantAddr"])?mb_substr($row["applicantAddr"],0,6)."*************" : "&nbsp";?></td>
    </tr>
    </table>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:0px;">
    <div style="float:left;margin-top:15px;">대리인 정보</div>
	<table style="display:inline;left:100px;position:relative;text-align:center;top:-10px;" cellpadding="5">
    <tr style="background:#eeeeee;text-align:center;font-weight:bold;">
		<td width="110">사용여부</td>
		<td width="110">성명</td>
		<td width="110">수급자와 관계</td>
		<td width="110">전화번호</td>
    </tr>
    <tr>
		<td><?=($row["contract_sign_type"] == "1")?"사용": "사용안함";?></td>
		<td><?=($row["contract_sign_type"] == "1")?mb_substr($row["contract_sign_name"],0,1)."*".mb_substr($row["contract_sign_name"],-1): "&nbsp;";?></td>
		<td><?php if($row["contract_sign_relation"] == 1){$relation_text = "가족";}elseif($row["contract_sign_relation"] == 2){$relation_text = "친족";}elseif($row["contract_sign_relation"] == 3){$relation_text = "기타";} //
		echo ($row["contract_sign_type"] == "1")? $relation_text: "&nbsp;";?></td>
		<td><?=($row["contract_sign_type"] == "1")?substr($row["contract_tel"],0,5)."********":"&nbsp;";?></td>

    </tr>
    </table>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:0px;">
    <div style="float:left;margin-top:15px;">사업소 정보</div>
	<table style="display:inline;left:100px;position:relative;text-align:center;top:-10px;" cellpadding="5">
    <tr style="background:#eeeeee;text-align:center;font-weight:bold;">
		<td width="250">기관명</td>
		<td width="200">기관번호</td>
		<td width="110">대표자</td>
		<td width="110">서명(이미지)</td>
    </tr>
    <tr>
		<td><?=$row["entNm"]?></td>
		<td><?=$row["entNum"]?></td>
		<td><?=$row["entCeoNm"]?></td>
		<td><img src="<?=$row["dc_signUrl"]; ?>" height="25px;"></td>
    </tr>
    </table>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:0px;">
    <div style="float:left;margin-top:25px;">계약서 특이사항</div>
	<table style="display:inline;left:75px;position:relative;top:-10px;text-align:center;">
	<tr>
		<td style="background:#eeeeee;text-align:center;font-weight:bold;padding:5px;width:110px;height:40px;">사용여부</td>
		<td rowspan="2"style="width:960px;height:80px;"><div style="position:relative;top:0px;left:0px;width:100%;height:80px;overflow:auto;table-layout:fixed;"><?=$row["entConAcc01"]?>
		</div></td>
    </tr>
	<tr>
		<td><?=($row["entConAcc01"] != "")?"사용":"사용안함";?></td>
    </tr>
    </table>
</div>
<div class="local_desc01 local_desc" style="background:#eee">
    <b>상품 정보</b>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding:0px;">
    <table style="position:relative;text-align:center;margin-bottom:10px;" cellpadding="5">
    <tr style="background:#eeeeee;font-weight:bold;">
		<td>No.</td>
		<td>품목명</td>
		<td>품목코드</td>
		<td>제품명</td>
		<td>바코드</td>
		<td>구분</td>
		<td>계약일</td>
		<td>급여가</td>
		<td>본인부담금</td>
		<td>공단부담금</td>
    </tr>
<?php 
$sql = "SELECT * FROM `eform_document_item` WHERE HEX(dc_id)='".$uuid."'";
$result = sql_query($sql);
$num = 1;
$total_price = 0;
$total_price_pen = 0;
$total_price_ent = 0;
while($row=sql_fetch_array($result)){
?>    
	<tr>
		<td><?=$num?></td>
		<td><?=$row["it_name"]?></td>
		<td><?=$row["it_code"]; ?></td>
		<td><?=$row["ca_name"];?></td>
		<td><?=$row["it_barcode"];?></td>
		<td><?=($row["gubun"] == "00")?"판매":"대여";?></td>
		<td><?=$row["it_date"];?></td>
		<td><?=number_format($row["it_price"]);?></td>
		<td><?=number_format($row["it_price_pen"]);?></td>
		<td><?=number_format($row["it_price_ent"]);?></td>
    </tr>
<?php 
	$total_price += $row["it_price"];
	$total_price_pen += $row["it_price_pen"];
	$total_price_ent += $row["it_price_ent"];
	$num++;}?>
	<tr>
		<td colspan="7">합계</td>		
		<td><?=number_format($total_price);?></td>
		<td><?=number_format($total_price_pen);?></td>
		<td><?=number_format($total_price_ent);?></td>
    </tr>
    </table>
</div>
<?php if($row["dc_sign_send_datetime"] != "0000-00-00 00:00:00"){
	$response = $client->request('GET', 'https://api.modusign.co.kr/documents?offset=0&limit=1&metadatas=%7B%22dc_id%22%3A%22'.strtolower($uuid).'%22%7D', [
	  'headers' => [
		'accept' => 'application/json',
		'authorization' => 'Basic '.$API_Key64,
	  ],
	]);

	$arrResponse = json_decode($response->getBody(),true);
	switch ($arrResponse["documents"][0]["status"]){//문서상태
		case "ON_GOING" : $status = "서명 대기중"; $div = "sign"; break; 
		case "ABORTED" : 
			if($arrResponse["documents"][0]["abort"]["type"] == "REJECTION")$status = "서명 거절"; 
			if($arrResponse["documents"][0]["abort"]["type"] == "SIGNING_CANCELLED")$status = "서명 취소"; 
			if($arrResponse["documents"][0]["abort"]["type"] == "REQUEST_CANCELLATION")$status = "요청 취소"; 		
		break; 
		case "COMPLETED" : $status = "서명 완료"; $resend = '<input type="button" value="계약서 재전송" onclick="resend_doc(\''.$arrResponse["documents"][0]["id"].'\',\'01071534117\')">'; break; 
	}
	$view_bt = '<input class="btn" style="cursor:pointer;" type="button" value="계약서보기" onclick="view_doc(\''.$arrResponse["documents"][0]["file"]["downloadUrl"].'\')">';
	$view_bt2 = ($arrResponse["documents"][0]["auditTrail"]["downloadUrl"]!="")?'<input class="btn" style="cursor:pointer;" type="button" value="감사추적인증서보기" onclick="view_doc(\''.$arrResponse["documents"][0]["auditTrail"]["downloadUrl"].'\')">':'';
?>
<div class="local_desc01 local_desc" style="background:#eee">
    <b>계약서명 정보 (모두싸인)</b>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding:0px;">
    <table style="position:relative;text-align:center;margin-bottom:10px;" cellpadding="5">
    <tr style="background:#eeeeee;font-weight:bold;">
		<td>문서이름</td>
		<td>서명생성일</td>
		<td>상태</td>
		<!--td>계약서보기</td>
		<td>감사추적인증서</td-->
    </tr>
	<tr>
		<td><?=$arrResponse["documents"][0]["title"]?></td>
		<td><?=date("Y-m-d H:i:s",strtotime($arrResponse["documents"][0]["createdAt"]));?></td>
		<td><?=$status?></td>
		<!--td><?=$view_bt;?></td>
		<td><?=$view_bt2;?></td-->		
    </tr>
    </table>
	<table style="relative;text-align:center;margin-bottom:10px;" cellpadding="5">
    <tr style="background:#eeeeee;font-weight:bold;">
		<td>No.</td>
		<td>구분</td>
		<td>서명방법</td>
		<td>전화번호</td>
		<td>진행상태</td>
		<td>서명완료일자</td>
    </tr>
<?php 
$participants_count = count($arrResponse["documents"][0]["participants"]);

for($i=0;$i<$participants_count;$i++){
?>    
	<tr>
		<td><?=$i+1?></td>
		<td><?=$arrResponse["documents"][0]["participants"][$i]["name"]?></td>
		<td><?=($arrResponse["documents"][0]["participants"][$i]["signingMethod"]["type"] == "SECURE_LINK")?"웹페이지":"카카오톡"; ?></td>
		<td><?=$arrResponse["documents"][0]["participants"][$i]["signingMethod"]["value"] ?></td>
		<td><?=($arrResponse["documents"][0]["signings"][$i]["signedAt"] != "")? "서명완료":"서명 대기중";?></td>
		<td><?=($arrResponse["documents"][0]["signings"][$i]["signedAt"] != "")? date("Y-m-d H:i:s",strtotime($arrResponse["documents"][0]["signings"][$i]["signedAt"])):"-"; ?></td>
    </tr>
<?php 
	$total_price += $row["it_price"];
	$total_price_pen += $row["it_price_pen"];
	$num++;}?>
    </table>
</div>
<?php }?>
<div class="btn_fixed_top" style="text-align:right;padding-right:15px !important;">
    <a href="./eform.php?<?=explode("?",$_SERVER["REQUEST_URI"])[1]?>" class="btn" style="background:#cccccc;">계약서목록보기</a><?php /*<a href="javascript:dc_view('<?=$dc_status?>','<?=$dc_sign_request_datetime?>');"  class="btn" style="background:#333333;color:#ffffff">계약서보기</a><a href="/shop/eform/downloadReceipt.php?dc_id=<?=$uuid?>" class="btn" style="background:#333333;color:#ffffff">급여비용명세서보기</a>*/?>
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
