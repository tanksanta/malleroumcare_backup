<?php
$sub_menu = '400490';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '수급자 주문상세';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 검색처리
$select = array();
$where = array();

$order_send_id = isset($_GET['order_send_id']) ? get_search_string($_GET['order_send_id']) : '';
$mb_id = isset($_GET['mb_id']) ? get_search_string($_GET['mb_id']) : '';

$select[] = ' M.mb_giup_bnum ';
$select[] = ' M.mb_entId ';
//$select[] = ' I.it_name ';
//$select[] = ' COUNT(E.dc_id) as it_count ';
//$select[] = ' I2.t_price ';
$sql_join = ' LEFT outer JOIN `g5_member` M ON O.mb_id = M.mb_id ';

$where[] = " O.order_send_id = '".$order_send_id."'";
$where[] = " O.mb_id = '".$mb_id."'"; 

// select 배열 처리
$select[] = "O.*";
$sql_select = implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}

$sql_from = " FROM `g5_shop_order_api` O";
$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where );
//echo "SELECT " . $sql_select . $sql_from . $sql_join . $sql_where ;

$row=sql_fetch_array($result);

$od_status = $row["od_status"];

	switch($row["relation_code"]){
			case "0":
			$relation_code = "본인";
			break;
			case "1": 
			$relation_code = "가족";
			break;
			case "2":
			$relation_code= "친족";
			break;
			case "3":
			$relation_code = "기타";
			break;
			default : $relation_code = "본인";
			break;
		}
//진행상태 네비게이션 
$btn_class1 = "btn3";
$btn_class2 = $btn_class3 = $btn_class4 = $btn_class5 = $btn_class6 = $btn_class7 = $btn_class8 = "btn2";
switch($od_status){
	case "주문처리" : $btn_class2 = "btn3"; break;
	case "결제완료" : $btn_class2 = $btn_class3 = "btn3"; break;
	case "주문완료" : $btn_class2 = $btn_class3 = $btn_class4 = "btn3"; break;
	case "출고완료" : $btn_class2 = $btn_class3 = $btn_class4 = $btn_class5 = "btn3"; break;
	case "작성완료" : $btn_class2 = $btn_class3 = $btn_class4 = $btn_class5 = $btn_class6 = "btn3"; break;
	case "서명완료" : $btn_class2 = $btn_class3 = $btn_class4 = $btn_class5 = $btn_class6 = $btn_class7 = "btn3"; break;
	case "주문취소" : $btn_class1 = "btn2";$btn_class8 = "btn3"; break;
}

			$od_penLtmNum = $row["od_penLtmNum"];
			$od_penLtmNum = str_replace("L","",$od_penLtmNum);  //수급자 요양인정번호
			$od_penNm = $row["od_penNm"]; //수급자 이름
			$od_penTypeNm = $row["od_penTypeNm"]; //본인부담율
			$od_penRecGraNm = $row["od_penRecGraNm"]; //본인부담율
			if($od_penTypeNm == "" && $od_penRecGraNm == "" ){
			//wmds 에서 조회 후 없을 경우 틸코 API를 통해 공단 조회
			//wmds 에 있을 경우 정상 처리, 없을 경우 공단 조회 - 1.5에서 이미 조회 했을 것으로 간주 공단 조회 안함
			//공단 조회 시 있을 경우 등록 안내, 없을 경우 에러 처리
				$page = 1;
				//$ca_id_arr = array_filter(explode('|', $_GET['ca_id']));


				$send_data = [];
				$send_data["penNm"] = $od_penNm;
				$send_data["penLtmNum"] = "L".$od_penLtmNum;//수급자번호에 L 제거 저장
				$send_data["usrId"] = $row["mb_id"];
				$send_data["entId"] = $row["mb_entId"];
				$send_data["pageNum"] = $page;
				$send_data["pageSize"] = 1;
				$send_data["appCd"] = "01";

				$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
				$list = [];
				foreach($res['data'] as $data) {
				  $checklist = ['penRecGraCd', 'penTypeCd', 'penExpiDtm', 'penBirth'];
				  $is_incomplete = false;
				  foreach($checklist as $check) {
					if(!$data[$check])
					  $is_incomplete = true;
				  }
				  if(!in_array($data['penGender'], ['남', '여']))
					$is_incomplete = true;
				  if($data['penTypeCd'] == '04' && !$data['penJumin'])
					$is_incomplete = true;
				  if($data['penExpiDtm']) {
					// 유효기간 만료일 지난 수급자는 유효기간 입력 후 주문하게 함
					$expired_dtm = substr($data['penExpiDtm'], -10);
					if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
					  $data['penExpiDtm'] = '';
					  $is_incomplete = true;
					}
				  }
				 
				  $data['incomplete'] = $is_incomplete;
				  $list[] = $data;
				  $od_penId = $data["penId"];
				  $od_penRecGraNm = $data["penRecGraNm"];
				  $od_penTypeNm = $data["penTypeNm"];
				  $od_penExpiDtm = $data["penExpiDtm"];
				  $od_penAppEdDtm = $data["penAppEdDtm"];
				  $od_penGender = ($data["penGender"] != "")?$data["penGender"] : $od_penGender;
				}
				$sql = "update `g5_shop_order_api` set od_penId='".$od_penId."',od_penTypeNm='".$od_penTypeNm."',od_penRecGraNm='".$od_penRecGraNm."',od_penExpiDtm='".$od_penExpiDtm."',od_penAppEdDtm='".$od_penAppEdDtm."',od_penGender='".$od_penGender."' where order_send_id = '".$order_send_id."' and mb_id = '".$mb_id."'";
				sql_query($sql);
			}

?>
<style type="text/css">
	.btn3 {
		background:#333333;border-radius: 5px;border-bottom:2px solid #cccccc;border-right:2px solid #cccccc;color:#ffffff; width:130px; font-weight:bold;
	}
	.btn2 {
		background:#efefef;border-radius: 5px;border-bottom:2px solid #cccccc;border-right:2px solid #cccccc; width:130px;font-weight:bold;
	}
</style>
<div class="local_desc01 local_desc" style="border:#fff;border-bottom:2px solid #dddddd;background:#fff;font-size:14px;">
    <b>진행 상태</b>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;padding-bottom:0px;">
<input type="button" class="btn <?=$btn_class1?>" value="주문승인대기"> →
<input type="button" class="btn <?=$btn_class2?>" value="주문승인완료"> →
<input type="button" class="btn <?=$btn_class3?>" value="결제완료"> →
<input type="button" class="btn <?=$btn_class4?>" value="주문완료"> →
<input type="button" class="btn <?=$btn_class5?>" value="출고완료"> →
<input type="button" class="btn <?=$btn_class6?>" value="계약서 서명요청"> →
<input type="button" class="btn <?=$btn_class7?>" value="계약서 서명완료">&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" class="btn <?=$btn_class8?>" value="주문취소">
</div>

<div class="local_desc01 local_desc" style="border:#fff;border-bottom:2px solid #dddddd;background:#fff;font-size:14px;">
    <b>수급자 주문 정보</b>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">주문번호</div>
	<div style="display:inline; left:150px;position:absolute;"><?=$row["order_send_id"]; ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">주문일자</div>
	<div style="display:inline; left:650px;position:absolute;"><?=$row["od_time"]; ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">주문 사업소</div>
	<div style="display:inline; left:150px;position:absolute;"><?=$row["od_name"]; ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">사업자등록번호</div>
	<div style="display:inline; left:650px;position:absolute;"><?=$row["mb_giup_bnum"]; ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">수급자이름</div>
	<div style="display:inline; left:150px;position:absolute;"><?=(mb_substr($row["od_penNm"],0,1)."*".mb_substr($row["od_penNm"],-1)); ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">장기요양번호</div>
	<div style="display:inline; left:650px;position:absolute;"><?="L".(substr(str_replace("L","",$row["od_penLtmNum"]),0,2)."******".substr(str_replace("L","",$row["od_penLtmNum"]),8,2)); ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;width:100px;">본인부담금율</div>
	<div style="display:inline; left:150px;position:absolute;"><?=$od_penTypeNm; ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">인정등급</div>
	<div style="display:inline; left:650px;position:absolute;"><?=$od_penRecGraNm; ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;width:100px;">수급자전화번호</div>
	<div style="display:inline; left:150px;position:absolute;"><?=substr($row["od_penConPnum"],0,6)."****"; ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">수급자성별</div>
	<div style="display:inline; left:650px;position:absolute;"><?=($row["od_penGender"]!="")?$row["od_penGender"]."자":""; ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">구매자ID</div>
	<div style="display:inline; left:150px;position:absolute;"><?=$row["od_b_id"]; ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">구매자이름</div>
	<div style="display:inline; left:650px;position:absolute;"><?=(mb_substr($row["od_b_name"],0,1)."*".mb_substr($row["od_b_name"],-1)); ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">구매자연락처</div>
	<div style="display:inline; left:150px;position:absolute;"><?=substr($row["od_b_tel"],0,6)."****"; ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">수급자와관계</div>
	<div style="display:inline; left:650px;position:absolute;"><?=$relation_code; ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">수령인명</div>
	<div style="display:inline; left:150px;position:absolute;"><?=($row["od_b_name2"] != "")?(mb_substr($row["od_b_name2"],0,1)."*".mb_substr($row["od_b_name2"],-1)): ""; ?></div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">수령인전화번호</div>
	<div style="display:inline; left:650px;position:absolute;"><?=substr($row["od_b_hp"],0,6)."****"; ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">배송주소</div>
	<div style="display:inline; left:150px;position:absolute;"><?=($row["od_b_addr1"] != "")?mb_substr($row["od_b_addr1"],0,6)."*************":"";//.$row["penAddrDtl"]; ?></div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">배송요청사항</div>
	<div style="display:inline; left:150px;position:absolute;"><?=$row["od_memo"]; ?></div>
</div>


<div class="local_desc01 local_desc" style="border:#fff;border-bottom:2px solid #dddddd;background:#fff;font-size:14px;">
    <b>수급자 주문 상품 정보</b>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;padding:0px;">
    <table style="position:relative;text-align:center;margin-bottom:10px;" cellpadding="5">
    <colgroup>
				  <col width="3%"/>
				  <col width="10%"/>
				  <col width="13%"/>
				  <col width="10%"/>
				  <col width="19%"/>
				  <col width="19%"/>
				  <col width="5%"/>
				  <col width="5%"/>
				  <col width="16%"/>
				</colgroup>
	<tr style="background:#333333;font-weight:bold; color:#ffffff;">
		<td>No.</td>
		<td>급여코드</td>
		<td>급여품목</td>		
		<td>상품ID</td>
		<td>상품명</td>
		<td>옵션명</td>
		<td>수량</td>
		<td>상태</td>
		<td>반려사유</td>
    </tr>
<?php 
$sql = "SELECT o.*,c.ca_name FROM `g5_shop_cart_api` o
left outer join g5_shop_item i on o.ProdPayCode = i.ProdPayCode
left outer join g5_shop_category c on substring(i.ca_id,1,4) = c.ca_id
WHERE o.mb_id='".$mb_id."' and o.order_send_id='".$order_send_id."'
GROUP BY o.ct_id";
$result = sql_query($sql);
$num = 1;
while($row2=sql_fetch_array($result)){
?>    
	<tr <?php if($row2["ct_status"] == "반려"){?>style="color:red;"<?php }?>>
		<td><?=$num?></td>
		<td><?=$row2["ProdPayCode"]?></td>
		<td><?=$row2["ca_name"]; ?></td>
		<td><?=$row2["it_id"];?></td>
		<td><?=$row2["it_name"];?></td>
		<td><?=str_replace(chr(30),"",$row2["io_id"]);?></td>
		<td><?=$row2["ct_qty"];?></td>
		<td><?=($row2["ct_status"] == "")?"접수":$row2["ct_status"];?></td>
		<td><?=$row2["ct_memo"];?></td>
    </tr>
<?php $num++;}?>
    </table>
</div>
<div class="local_desc01 local_desc" style="border:#fff;border-bottom:2px solid #dddddd;background:#fff;font-size:14px;">
    <b>이로움 주문 정보 </b>
</div>
<?php if($od_status != "승인대기" && $od_status != "승인완료" && $od_status != "결재완료"  && $od_status != "주문취소" && $row["od_sync_odid"]!=""){?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">주문번호</div>
	<div style="display:inline; left:150px;position:absolute;"><?=$row["od_sync_odid"]?></div>
	<div style="display:inline;font-weight:bold; left:300px;position:absolute;margin-top:-5px;"><?php if(1){?><a href="samhwa_orderform.php?od_id=<?=$row["od_sync_odid"]?>&sub_menu=400400" class="btn" style="background:#cccccc;" target="_blank">상세보기</a> <a href="javascript:;" class="btn" id="prodBarNumCntBtn" style="background:#cccccc;">바코드보기</a> <a href="javascript:;" class="btn" id="deliveryCntBtn" style="background:#cccccc;">송장내역</a><?php }?></div>	
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;padding:0px;">
    <table style="position:relative;text-align:center;margin-bottom:10px;" cellpadding="5">
    <tr style="background:#333333;font-weight:bold; color:#ffffff;">
		<td>No.</td>
		<td>상품명</td>
		<td>옵션명</td>		
		<td>수량</td>
		<td>상태</td>
		<td>출고일자</td>
    </tr>
<?php 
$sql = "SELECT * FROM `g5_shop_cart` WHERE od_id='".$row["od_sync_odid"]."'";
$result = sql_query($sql);
$num = 1;
while($row3=sql_fetch_array($result)){
	switch($row3["ct_status"]){
		case "주문": $ct_status = "주문접수"; break;
		case "준비": $ct_status = "상품준비"; break;
		case "출고준비": $ct_status = "출고준비"; break;
		case "배송": $ct_status = "출고완료"; break;
		case "완료": $ct_status = "배송완료"; break;
		case "취소": $ct_status = "주문취소"; break;
		case "무효": $ct_status = "주문무효"; break;
		default: $ct_status = "주문접수"; break;
	}
?>    
	<tr>
		<td><?=$num?></td>
		<td><?=$row3["it_name"]?></td>
		<td><?=$row3["io_id"]; ?></td>
		<td><?=$row3["ct_qty"];?></td>
		<td><?=$ct_status;?></td>
		<td><?=$row3["ct_ex_date"];?></td>
    </tr>
<?php $num++;}?>
    </table>
</div>
<?php }else{?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    주문정보가 없습니다.	
</div>
<?php }?>
<div class="local_desc01 local_desc" style="border:#fff;border-bottom:2px solid #dddddd;background:#fff;font-size:14px;">
    <b>계약서 정보 </b>
</div>
<?php if($od_status == "서명요청" || $od_status == "서명완료" || $od_status == "작성완료"){
	$eform = sql_fetch("SELECT dc_id, hex(dc_id) as uuid FROM `eform_document` WHERE od_id = '{$row['od_sync_odid']}'");
	if($eform['dc_id']!= ""){//계약서 있음
	?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">계약서번호</div>
	<div style="display:inline; left:150px;position:absolute;"><?=$eform['uuid']?></div>
	<div style="display:inline;font-weight:bold; left:400px;position:absolute;margin-top:-5px;"><a href="javascript:;" class="btn" style="background:#cccccc;" onclick="mds_download('<?=$eform['uuid']?>',1);">상세보기</a></div>	
</div>
<?php }else{?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    계약정보가 없습니다.	
</div>
<?php } 
}else{?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    계약정보가 없습니다.	
</div>
<?php }?>
<div class="local_desc01 local_desc" style="border:#fff;border-bottom:2px solid #dddddd;background:#fff;font-size:14px;">
    <b>이벤트 로그 </b>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;font-weight:bold;">기간</div>
	<div style="display:inline; left:200px;position:absolute;font-weight:bold;">타입</div>
	<div style="display:inline;font-weight:bold; left:500px;position:absolute;">내용</div>
</div>
<?php
$sql = "SELECT * FROM `g5_shop_api_log` WHERE mb_id='".$mb_id."' and order_send_id='".$order_send_id."' order by log_time DESC,log_id DESC";
$result = sql_query($sql);
while($row4=sql_fetch_array($result)){
	switch($row4["log_type"]){
		case "1" : $type = "수신"; break;
		case "2" : $type = "송신"; break;
		case "3" : $type = "이벤트처리"; break;
		default : $type = "이벤트처리"; break;
	}
?>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:10px;margin-top:-10px;">
    <div style="display:inline;"><?=$row4["log_time"]?></div>
	<div style="display:inline; left:200px;position:absolute;"><?=$type?></div>
	<div style="display:inline; left:500px;position:absolute;"><?=$row4["log_cont"]?></div>
</div>
<?php }?>

<div class="btn_fixed_top" style="text-align:right;padding-right:15px !important;">
    <a href="./pen_orderlist.php?<?=explode("?",$_SERVER["REQUEST_URI"])[1]?>" class="btn" style="background:#cccccc;">수급자 주문목록보기</a>
</div>

<script>
$(function() {
    $(document).on("click", "#deliveryCntBtn", function(e){
    e.preventDefault();
    
    var popupWidth = 1200;
    var popupHeight = 700;

    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY= (window.screen.height / 2) - (popupHeight / 2);
    
    //아래로하면 cart기준으로 바꿈(상품하나씩)
    window.open("./popup.prodDeliveryInfo.form.php?od_id=<?=$row['od_sync_odid']?>&show_release_ready_only=N", "배송정보", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
  });
  
  $(document).on("click", "#prodBarNumCntBtn", function(e) {
    e.preventDefault();
    var popupWidth = 800;
    var popupHeight = 700;
    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY= (window.screen.height / 2) - (popupHeight / 2);
    // var id = $(this).attr("data-id");
    // window.open("./popup.prodBarNum.form.php?od_id=" + id, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
    //popup.prodBarNum.form_3.php 으로하면 cart 기준으로 바뀜 (상품하나씩)
    window.open("./popup.prodBarNum.form.php?no_refresh=1&orderlist=1&prodId=&od_id=<?=$row['od_sync_odid']?>&stock_insert=&option=", "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
  });
});

// 계약서,감사추적인증서 보기 
	function mds_download(dc_id,gubun) {//1:계약서,2:감사추적인증서
 		$.post('/shop/ajax.eform_mds_api.php', {
			dc_id:dc_id,
			gubun:gubun,
			div:'view_doc'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				return false;				
			}
			if(data.url != "url생성실패"){				
				window.open(data.url, "PopupDoc", "width=1000,height=1000");
			}else{
				alert(data.url);//url 생성실패 알림
			}
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		});	
	}

function view_doc(url){//계약서 보기
	window.open(url, "PopupDoc", "width=1000,height=1000");
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
