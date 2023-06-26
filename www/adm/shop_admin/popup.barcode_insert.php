<?php
$sub_menu = '400405';

include_once("./_common.php");
auth_check($auth[$sub_menu], "w");
$ct_id = "'".str_replace(",","','",$_REQUEST["barcode_ct_id"])."'";
$ct_id_count = count(explode(",",$_REQUEST["barcode_ct_id"]));
$sql_common = "
  FROM
    {$g5['g5_shop_cart_table']} c
  LEFT JOIN
    {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
  LEFT JOIN
    {$g5['g5_shop_order_table']} o ON c.od_id = o.od_id
  LEFT JOIN
    {$g5['member_table']} m ON c.mb_id = m.mb_id
  LEFT JOIN 
    {$g5['member_table']} m2 ON c.ct_direct_delivery_partner = m2.mb_id
  LEFT JOIN
    partner_install_report pir ON c.od_id = pir.od_id
  LEFT JOIN
    g5_shop_order_cancel_request ocr ON c.od_id = ocr.od_id
";
$sql  = "
  select *, o.od_id as od_id, c.ct_id as ct_id, c.mb_id as mb_id,m2.mb_name AS partner_name, (od_cart_coupon + od_coupon + od_send_coupon) as couponprice,
  TIMEDIFF(it_deadline,DATE_FORMAT(NOW(), '%H:%i:%s')) AS time_dead, c.stoId AS c_stoId, c.it_id 
  $sql_common
  where c.ct_id in ($ct_id)
  order by c.ct_id DESC
";
//echo $sql;
$result = sql_query($sql);


?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>입고 현황</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="/js/barcode_utils.js"></script>
  <link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
  <link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/flex.css">
  <link rel="stylesheet" href="/skin/admin/new/css/admin.css?ver=211222">
<link rel="stylesheet" href="/skin/admin/new/css/admin.css">
<link rel="stylesheet" href="/adm/css/samhwa_admin.css">
<link rel="stylesheet" href="/css/apms.css">
<link rel="stylesheet" href="/css/level/basic.css">
<link rel="stylesheet" href="/thema/eroumcare/assets/css/samhwa.css">

  <style>

	.bg0 {background:#fff}
	.bg1 {background:#f2f5f9}
	.bg1 td {border-color:#e9e9e9}
	
.new_form2 {
    border: 1px solid #ddd;
    box-sizing: border-box;
	margin: 20px 0px;
	height:470px;
	overflow-y:auto;overflow-x:hidden;
	width:100%;	
 }
 .new_form3 {
    border: 1px solid #ddd;
    box-sizing: border-box;
	margin: 10px ;
	padding: 5px ;
 }
 .parent{
	display: flex;
 }
 ul{list-style: none;
	margin: 0;
    padding: 0;
	width:100%;
}
li {
	padding:2px;
}
.newbutton {
    border: 0;
    font-size: 12px;
    height: 33px;
    padding: 0 10px;
    cursor: pointer;
    outline: none;
    box-sizing: border-box;
    border: 1px solid #ddd;
}
.wrap_bg {
	background-color:#e1f0ff;
}

#toast {
    position: fixed;
    top: 50%;
    left: 50%;
    padding: 15px 20px;
    transform: translate(-50%, 10px);
    border-radius: 3px;
    overflow: hidden;
    font-size: .8rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity .5s, visibility .5s, transform .5s;
    background: #111111;
    color: #fff;
    z-index: 10000;
}

#toast.reveal {
    opacity: 1;
    visibility: visible;
}

@media (max-width: 991px){
	html, body {
		max-width: 100%;
		overflow: visible;
	}
}
  </style>
</head>

<body style="margin-bottom:15px;">
<form method="post" action="">
	<input type="hidden" name="ct_delivery_company" id="ct_delivery_company">
	<input type="hidden" name="ct_delivery_num" id="ct_delivery_num">
	<input type="hidden" name="ct_id" id="ct_id">
</form>
<!-- 고정 상단 -->
<div style="padding: 10px 15px;">
	<div class="" id="" class="" style="float:left;width:64%;height:350px;margin-right:1%;">
		<span style="float:left;margin-left:5px;font-weight:bold;">주문 정보</span><span style="float:right;margin-right:5px;font-weight:bold;">총 <?=number_format($ct_id_count)?>건</span>
		<div id="" class="new_form2">
<?php $i = 0;
	while ($row = sql_fetch_array($result)) { 
		$stock_list = "";
		$row['ct_barcode_insert'] = ($row['ct_barcode_insert'] == "" )? 0 :$row['ct_barcode_insert'];
		if(substr($row["ca_id"],0,2) == "70"){
			$barcode_qty = "비급여";
		}else{
			$barcode_qty = ($row['ct_barcode_insert']>=$row["ct_qty"])?$row['ct_barcode_insert']."/".$row["ct_qty"]:"<font color='red'>".$row['ct_barcode_insert']."/".$row["ct_qty"]."</font>";
		}
		$sto_imsi = $row['c_stoId'];
		$stoIdDataList = explode(',',$sto_imsi);
		$stoIdDataList = array_filter($stoIdDataList);
		$stoIdData = implode("|", $stoIdDataList);
		$res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
		'stoId' => $stoIdData
		));
		$result_again = $res['data'];
		if ($result_again && count($result_again)) {
		  $j = 0 ;
		  foreach ($result_again as $stock) {
			$stock_list .= (($j != 0 && $stock['prodBarNum']!="")?",":"").$stock['prodBarNum'];
			$j++;
		  }
		}

		$stock_list = ($stock_list == "")?"우측 바코드 정보에 바코드를 등록 바랍니다.":$stock_list;
		$ct_direct_delivery_partner_name = ($row['partner_name'] == "")?"미등록": $row['partner_name'];//파트너
		
		$sql = "SELECT ct_delivery_company FROM `g5_shop_order` o
				LEFT JOIN g5_shop_cart c ON o.od_id = c.od_id
				WHERE c.it_id = '".$row["it_id"]."' and c.ct_delivery_num != ''
				ORDER BY o.od_time DESC
				LIMIT 1";
		$row22 = sql_fetch($sql);
		$row["ct_delivery_company"] = ($row["ct_delivery_num"]=="")? $row22["ct_delivery_company"]:$row["ct_delivery_company"];
		?>
			<div id="wrap<?=$i?>" class="new_form3">
				<div class="parent" onclick="show_hide('<?=$i?>','<?=$ct_direct_delivery_partner_name?>')">
				<ul >
					<li class="parent">
						<div class="" style="width:70%"><?=$row["it_name"].(($row["ct_option"] != $row["it_name"])?" [".$row["ct_option"]."]":"")?></div>
						<div class="" style="width:20%">바코드/수량</div>
						<div class="" style="width:10%"><span id="barcode_qty<?=$i?>"><?=$barcode_qty?></span>
						<input type="hidden" id="gubun<?=$i?>" value="<?=$barcode_qty?>">
						<input type="hidden" id="od_id<?=$i?>" value="<?=$row['od_id']?>">
						<input type="hidden" id="ct_id<?=$i?>" value="<?=$row['ct_id']?>">
						<input type="hidden" id="mb_id<?=$i?>" value="<?=$row['mb_id']?>">
						<input type="hidden" id="ct_qty<?=$i?>" value="<?=$row['ct_qty']?>">
						<input type="hidden" id="entId<?=$i?>" value="<?=get_ent_id_by_od_id($row['od_id'])?>">
						<input type="hidden" id="stoId<?=$i?>" value='<?=$row['c_stoId']?>'>
						<input type="hidden" id="ct_status<?=$i?>" value='<?=$row["ct_status"]?>'>
						
						</div>
					</li>
					<li class="parent">
						<div class="" style="width:70%"><?=$row["od_b_name"];//수령인 ?></div>
						<div class="" style="width:30%"><?=$row['od_b_tel'];//수령인 연락처 ?></div>
					</li>
					<li class="parent">
						<div class="" style="width:70%"><?=$row["od_b_addr1"].(($row["od_b_addr2"]!="")?" ".$row["od_b_addr2"]:"").(($row["od_b_addr3"]!="")?" ".$row["od_b_addr3"]:"");//배송주소 ?></div>
						<div class="" style="width:20%">송장번호 입력</div>
						<div class="" id="delivery_num_yn_<?=$i?>" style="width:10%"><?=($row['ct_combine_ct_id']||$row['ct_delivery_num'])?"Y":"<font color='red'>N</font>";//배송정보 ?></div>
					</li>
					
				</ul>
				<input type="button" id="btn<?=$i?>" value="▼"  class="newbutton" style="color:#888888;margin-top:15px;margin-right:2px;">
				</div>
				<div id="down_arear<?=$i?>" style="display:none;">
				<ul >
					<li><textarea name="barcode<?=$i?>" id="barcode<?=$i?>" rows="" cols="" class="frm_input" style="width:99%;min-height:50px;background-color:#efefef;line-height:14px;resize: none;" readonly><?=$stock_list?></textarea></li>
					<li><select name="ct_delivery_company_<?=$i?>" id="ct_delivery_company_<?=$i?>" class="frm_input" style="width:28%;">
              <?php foreach($delivery_companys as $data){ ?>
              <option value="<?=$data["val"]?>" <?=($row["ct_delivery_company"] == $data["val"]) ? "selected" : ""?>><?=$data["name"]?></option>
              <?php } ?>
            </select> <input type="text" name="ct_delivery_num_<?=$i?>" id="ct_delivery_num_<?=$i?>" class="frm_input" style="width:50%;padding-left:5px;" value="<?=$row["ct_delivery_num"]?>" placeholder="송장번호 입력"> <input type="button" value="저장" onclick="save_delivery_info('<?=$row["ct_id"]?>','<?=$i?>')" class="btn" style=" background-color:#ff9900;color:#ffffff;font-weight:bold;height:26px;border-radius:3px;width:18.3%;cursor:pointer;"></li>
				</ul>
				</div>
			</div>
<?php $i++;}?>
			
		</div>
	</div>
	<div class="" id="" class="" style="float:left;width:35%;height:400px;position:relative;">
		<span style="float:left;margin-left:5px;font-weight:bold;">바코드 정보</span>
		<div id="cover" class="new_form2" style="position:absolute;top:0px;right:0px;width:100%;height:470px;background-color:#efefef">
			<div style="position:relative;left:-65px;width:150px;height:20px;margin-left:50%;margin-top:65%;">
				주문 선택 시 활성화 됩니다.
			</div>
		</div>
		<div id="" class="new_form2">
			<div id="partner_name" style="margin:10px;">
				파트너명 : 
			</div>
			<div class="parent" style="margin:0px 10px;">				
				<div id="" class="" style="width:50%;left:0px;margin-left:0px;">
					바코드 입력
				</div>
				<div id="" class="" style="width:50%;right:0px;text-align:right;">
					<button type="button" onClick="copy_text('barcode');" class="btn" style="border-radius:3px;width:25px;padding:0px 3px;"><img src="/img/copy.png" width="14" border="0" title="바코드 복사" ></button>&nbsp;<button type="button" onClick="barcode_del();" class="btn" style="border-radius:3px;width:25px;padding:0px 3px;" id="del_btn"><img src="/img/trash.png" width="20"border="0" title="바코드 삭제"></button>
				</div>
			</div>
			<div class="new_form3 parent" style="margin-top:3px;height:41%;overflow-y:auto;overflow-x:hidden;padding:0px">
				<div id="" class="new_form3" style="width:50px;text-align:center;margin:0px;height:139693px;">
					<?php for($i=0;$i<10000;$i++){
						echo (($i!=0)?"<br>":"").($i+1);
				}?>
				</div>
				<textarea name="" rows="" cols="" name="barcode" id="barcode" style="padding:5px 5px;height:139682px;resize: none;" onClick="text_remove(this.value)"></textarea>
			</div>
			<div id="" class="" style="margin-left:10px;">
				<input type="button" value="오류검사" onclick="error_check();" class="btn" style=" background-color:#555555;color:#ffffff;font-weight:bold;height:26px;border-radius:3px;width:31.6%;cursor:pointer;">
				<input type="button" value="재고검사" onclick="stock_check();" class="btn" style=" background-color:#555555;color:#ffffff;font-weight:bold;height:26px;border-radius:3px;width:31.6%;cursor:pointer;">
				<input type="button" value="등록" onclick="barcode_save();" class="btn" style=" background-color:#ff9900;color:#ffffff;font-weight:bold;height:26px;border-radius:3px;width:31.6%;cursor:pointer;">
				<input type="hidden" name="od_id" id="od_id" value="">
				<input type="hidden" name="od_mb_id" id="od_mb_id" value="">
				<input type="hidden" name="od_entId" id="od_entId" value="">
				<input type="hidden" id="ct_qty" value="">
				<input type="hidden" id="error_chk" value="">
				<input type="hidden" id="stoId" value=''>
				<input type="hidden" id="n" value=''>
			</div>
			<div id="" class="" style="margin:10px;">
				총 바코드 <span id="total_count">30</span>개(정상 <font color="blue"><span id="count1">20</span>개</font>, 오류 <font color="red"><span id="count2">10</span>개</font>, 재고 존재 <font color="red"><span id="count3">0</span>개</font>)
			</div>
			<div id="" class="parent" style="margin:15px 10px 5px 10px;">
				<div id="" class="" style="width:50%;left:0px;margin-left:0px;">
					오류 내역
				</div>
				<div class="" style="width:50%;right:0px;text-align:right;">
					<button type="button" onClick="copy_text('error_list');" class="btn" style="border-radius:3px;width:25px;padding:0px 3px;"><img src="/img/copy.png" width="14" border="0" title="오류 내역 복사"></button>
				</div>
			</div>
			<div class="" style="margin:0px 10px;">
				<input type="text" name="" id="error_list" class="frm_input" style="width:100%" disabled>
			</div>
			<div id="" class="parent" style="margin:10px 10px 5px 10px;">
				<div id="" class="" style="width:50%;left:0px;margin-left:0px;">
					재고 존재 내역
				</div>
				<div id="" class="" style="width:50%;right:0px;text-align:right;">
					<button type="button" onClick="copy_text('stock_list');" class="btn" style="border-radius:3px;width:25px;padding:0px 3px;"><img src="/img/copy.png" width="14" border="0" title="재고 존재 내역 복사"></button>
				</div>
			</div>
			<div  class="" style="margin:0px 10px;">
				<input type="text" name="" id="stock_list" class="frm_input" style="width:100%" disabled>
			</div>
		</div>
		
	</div>
</div>
<div id="toast"></div>
<script>
	function text_remove(a){
		if(a.includes('엔터') == true){
			$("#barcode").val("");
		}
	}
	var barcode_org = "";
	function show_hide(n,p,c){
		for(var i=0;i<<?=count(explode(",",$_REQUEST["barcode_ct_id"]))?>;i++ ){
			if(i != n){
				$("#wrap"+i).removeClass('wrap_bg');
				$("#down_arear"+i).hide();
				$("#btn"+i).val("▼");
			}
		}
		if($("#btn"+n).val() == "▼"){
			$("#btn"+n).val("▲");
			$("#down_arear"+n).show();
			$("#wrap"+n).addClass('wrap_bg');
			$("#partner_name").text("파트너명 : "+p);
			$("#od_id").val($("#od_id"+n).val());
			$("#od_mb_id").val($("#mb_id"+n).val());
			$("#od_entId").val($("#entId"+n).val());
			$("#ct_qty").val($("#ct_qty"+n).val());
			$("#ct_id").val($("#ct_id"+n).val());
			$("#stoId").val($("#stoId"+n).val());
			
			$("#n").val(n);
			if($("#barcode"+n).val() != "우측 바코드 정보에 바코드를 등록 바랍니다."){
				if($("#gubun"+n).val() != "비급여"){					
					$("#barcode").val($("#barcode"+n).val().replace(/,/g,'\n'));
					$("#barcode").attr("disabled",false);
					var bar_arr = $("#barcode").val().split("\n");
					$("#total_count").text(bar_arr.length);
				}else{
					$("#total_count").text("0");
					$("#barcode").val("비급여 상품은 바코드 입력불가");
					$("#barcode").attr("disabled",true);
				}
			}else{
				if($("#gubun"+n).val() != "비급여"){
					var txt = '“엔터”로구분하여12자리숫자입력\n연속되는숫자사이에“-”, “~”입력(예시)\n123456789100\n123456789110\n123456789012-19,30,40';
					$("#barcode").attr("disabled",false);
				}else{
					var txt = '비급여 상품은 바코드 입력불가';
					$("#barcode").attr("disabled",true);
				}
				$("#barcode").val(txt);
				$("#total_count").text("0");
			}
			if($("#ct_status"+n).val() == "배송"){
				$("#barcode").attr("disabled",true);
				$("#del_btn").attr("disabled",true);
			}
			barcode_org = txt;
			$("#cover").css("display","none");
		}else{
			$("#cover").css("display","block");
			$("#wrap"+n).removeClass('wrap_bg');
			$("#down_arear"+n).hide();
			$("#btn"+n).val("▼");
			$("#partner_name").text("파트너명 : ");
			$("#barcode").val("");
			$("#ct_qty").val("");
			$("#ct_id").val("");
			$("#stoId").val("");
			$("#od_id").val("");
			$("#od_mb_id").val("");
			$("#od_entId").val("");	
			$("#total_count").text("0");
			$("#n").val("");
		}
		$("#error_chk").val("");
		$("#error_list").val("");
		$("#stock_list").val("");		
		$("#count1").text("0");
		$("#count2").text("0");
		$("#count3").text("0");
	}
	function partner_id_send(partner_id){
		parent.partner_id(partner_id); 
	}
	
	function save_delivery_info(c,i){
		/*if($("#ct_delivery_num_"+i).val() == ""){
			alert("송장번호를 입력해 주세요.");
			$("#ct_delivery_num_"+i).focus();
			return;
		}*/
		$("#ct_id").val(c);//ct_id 값
		$("#ct_delivery_company").val($("#ct_delivery_company_"+i).val());//택배사
		$("#ct_delivery_num").val($("#ct_delivery_num_"+i).val());//송장번호
		var params = {
            ct_id : $('#ct_id').val()
		    , ct_delivery_company : $("#ct_delivery_company").val()
            , ct_delivery_num : $("#ct_delivery_num").val()        }
		// ajax 통신
        $.ajax({
            type : "POST",            // HTTP method type(GET, POST) 형식이다.
            url : "./ajax.delivery_info_edit.php",      // 컨트롤러에서 대기중인 URL 주소이다.
            data : params,            // Json 형식의 데이터이다.
			dataType: "json",
            success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
                // 응답코드 > 0000
                if(res == true){
					toast('저장 되었습니다.');
					//alert("저장 되었습니다.");
					var delivery_num_yn = ($("#ct_delivery_num_"+i).val() != "")? 'Y': "<font color='red'>N</font>";
					$("#delivery_num_yn_"+i).html(delivery_num_yn);
					/*for(var j = 0; j<<?=$ct_id_count?>;j++ ){
						if($("#ct_delivery_num_"+j).val() == ""){
							$("#ct_delivery_company_"+j).val($("#ct_delivery_company").val());
						}
					}*/
				}else{
					toast('배송정보 저장에 실패 했습니다.\n다시 시도해 주세요.');
					//alert("배송정보 저장에 실패 했습니다.\n다시 시도해 주세요.");
				}
            },
            error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
                alert("통신 실패.");
            }
        });
	}
	function copy_text(a){//텍스트 복사
		const $textarea = document.createElement("textarea");

		// body 요소에 존재해야 복사가 진행됨
		document.body.appendChild($textarea);
		
		// 복사할 특정 텍스트를 임시의 textarea에 넣어주고 모두 셀렉션 상태
		$textarea.value = $("#"+a).val().replace(/\n/g,',');
		$textarea.select();
		  
		// 복사 후 textarea 지우기
		document.execCommand('copy');
		document.body.removeChild($textarea);
		toast('복사 완료');
	}
	
	function barcode_del(){//바코드 텍스트 삭제
		if(confirm("정말 삭제 하겠습니까?")){
			$("#barcode").val("");
			toast('삭제 완료\n등록 하셔야 저장됩니다.');
		}
	}
	function barcode_disabled(){
		if($("#barcode").attr('disabled')){
			if($("#ct_status0").val() == "배송"){
				alert("출고완료 상품은 기능이 제한 됩니다.");
			}else{
				alert("비급여 상품입니다.");
			}
			return false;
		}else{
			return true;
		}
	}
	
	function error_check(){//오류검사
		if(barcode_disabled()==false){
			return false;
		}
		$("#error_list").val("");
		var barcode_insert = $("#barcode").val();
		barcode_insert = $.trim(barcode_insert);
		barcode_insert = barcode_insert.replace(/ /g,'\n');
		barcode_insert = barcode_insert.replace(/,|\/|\||\.|\t/g,'\n');
		barcode_insert = barcode_insert.replace(/-|~/g,'\n-\n');
		var barcode_error = "";//에러바코드
		var barcodeArr = "";//정상바코드
		var pre_barcode = "";//이전바코드
		var fron_barcode = "";//앞바코드
		var back_barcode = "";//뒤바코드
		var bar_arr = barcode_insert.split("\n");
		var j = 0;//바코드 에러 카운트용
		var k = 0;//바코드 저장용   
		var h2 = "";
		for(var i=0;i<bar_arr.length;i++){
			if (bar_arr[i].length === 12) {
				if(isNaN(bar_arr[i])) {//숫자 아닌경우
					barcode_error +=((j!=0)?",":"")+bar_arr[i];
					j++;
				}else{
					barcodeArr += ((k!=0)?"\n":"")+bar_arr[i];
					k++;
				}
			}else{
				if(i==0 && (bar_arr[i].length > 12 || isNaN(bar_arr[i]))){
					barcode_error +=((j!=0)?",":"")+bar_arr[i];
					j++;
				}else if(bar_arr[i] == "-"){//구분자처리,4이하일 때
					pre_barcode = bar_arr[i-1];
				}else if(pre_barcode.length === 12 && bar_arr[i-1] == "-"){//연속바코드 처리
					fron_barcode = pre_barcode.substring(0,(12-bar_arr[i].length));
					back_barcode = pre_barcode.slice(-bar_arr[i].length);
					for(var h = parseInt(back_barcode)+1;h<parseInt(bar_arr[i])+1; h++){
						h2 = "";
						if(back_barcode.length > String(h).length){							
							for(var t=0;t<back_barcode.length-String(h).length;t++){
								h2 += "0";
							}
						}
						if(isNaN(fron_barcode+h)) {//숫자 아닌경우
							barcode_error +=((j!=0)?",":"")+fron_barcode+h2+h;
							j++;
						}else{
							barcodeArr += ((k!=0)?"\n":"")+fron_barcode+h2+h;
							k++;
						}
					}
				}else if(bar_arr[i].length < 5 && bar_arr[i] != "-"){//4이하일 때
					if(bar_arr[i-1].length === 12){
						bar_arr[i] = bar_arr[i-1].substring(0,(12-bar_arr[i].length))+bar_arr[i];
					}else{
						bar_arr[i] = pre_barcode.substring(0,(12-bar_arr[i].length))+bar_arr[i];
					}
					if(isNaN(bar_arr[i])) {//숫자 아닌경우
						barcode_error +=((j!=0)?",":"")+bar_arr[i];
						j++;
					}else{
						barcodeArr += ((k!=0)?"\n":"")+bar_arr[i];
						k++;
					}
				}else{//바코드 에러
					barcode_error +=((j!=0)?",":"")+bar_arr[i];
					j++;
				}
			}
		}
		var barcode_arr = barcodeArr.split("\n");
		var barcode_arr2 = [...new Set(barcode_arr)];
		var barcodeArr2 = barcode_arr2.join('\n');
		var count2 = 0;
		$("#error_chk").val("ok");
		$("#barcode").val(barcodeArr2);
		var bar_arr = $("#barcode").val().split("\n");
		
		$("#error_list").val(barcode_error);
		if($("#error_list").val() != ""){
			var error_arr = $("#error_list").val().split(",");
			count2 = error_arr.length;
		}
		$("#count2").text(count2);//오류
		$("#count1").text(bar_arr.length);//정상
		$("#total_count").text(bar_arr.length+count2);//총바코드
		barcode_org = $("#barcode").val();
		//stock_check();//재고검사		
	}

	function stock_check(){//재고검사
		if(barcode_disabled()==false){
			return false;
		}
		$("#stock_list").val("");
		var barcodeArr = [];
		var bar_arr = $("#barcode").val().split("\n");
        for(var i=0;i<bar_arr.length;i++){

			if (bar_arr[i].length === 12) {
			  barcodeArr.push({
				ct_id: $("#ct_id").val(),
				index: i,
				barcode: bar_arr[i],
			  });
			}
		}
      if (barcodeArr.length > 0) {

        $.ajax({
          url: './ajax.barcode_validate_bulk2.php',
          type: 'POST',
          data: {
            ct_id: $("#ct_id").val(),
            barcodeArr: barcodeArr,
          },
          dataType: 'json',
          async: false,
        })
        .done(function(result) {
          //console.log(result.data);
          var activeCount = 0;
		  var stock_list ="";

          result.data.barcodeArr.forEach(function (_this) {
			if (_this.status != '미보유재고' && _this.status != '미등록재고') {
			  stock_list += ","+barcodeArr[_this.index]['barcode'];
            }
          });
          if (stock_list != "") {
            $("#stock_list").val(stock_list.replace(',',''));
			var stock_arr = $("#stock_list").val().split(",");
			activeCount = stock_arr.length;
          }
		  
		  $("#count3").text(activeCount);//재고존재
        })
        .fail(function($xhr) {
          // msgResult = 'error'
          var data = $xhr.responseJSON;
          console.warn(data && data.message);
          // alert('바코드 재고 확인 도중 오류가 발생했습니다. 관리자에게 문의해주세요.');
        })
      }	  
	}
	var loading_barnumsave = false;
	function barcode_save(){//등록
		if(barcode_disabled()==false){
			return false;
		}
		if((barcode_org != $("#barcode").val() || $("#error_chk").val() != "ok") && $("#barcode").val() != ""){
			if(barcode_org != $("#barcode").val()){
				alert("바코드에 변경 사항이 있습니다. 검사 실행 후 등록바랍니다.");//오류검사 후 변경 사항 발생 시
			}else{
				alert("검사 실행 후 등록바랍니다.");
			}
			return false;
		}
		if($("#error_list").val() != ""){
			alert("오류 수정 후 재검사가 필요합니다.");
			return false;
		}
		if($("#stock_list").val() != ""){
			//alert("상품관리 재고에 등록된 바코드입니다.\n확인 후 다시 저장바랍니다.");
			//return false;
		}
		var bar_arr = $("#barcode").val().split("\n");
		if($("#ct_qty").val() < bar_arr.length){
			alert("주문 수량보다 바코드가 더 많이 입력되었습니다.\n확인 후 다시 저장바랍니다.");
			return false;
		}

		var barcode_arr = [];
		var isDuplicated = false;
		var isNotNumber = false;
		var barcode_arr2 = $("#barcode").val().split("\n");  
		var temp_arr = [];
		for(var i = 0 ;i< barcode_arr2.length; i++){
			var barcode = barcode_arr2[i];
            if (barcode != "") {
                temp_arr.push(barcode_arr2[i])
            }
            if(isNaN(barcode)) {
				isNotNumber = true;
            }
		}
		barcode_arr.push(temp_arr);

		if(isNotNumber) {
			alert('입력하신 바코드 중 숫자가 아닌 값이 있습니다.');
			return false;
		}

		barcode_arr.forEach(function(arr) {
			if (isDuplicate(arr)) {
				isDuplicated = true;
			}
		});

		if (isDuplicated) {
		    alert("입력하신 바코드 중 중복 값이 있습니다.");
		    return false;
		}
		  
		need_reload = true;

		var changeStatus = true;
		var insertBarCnt = 0;

		if(loading_barnumsave) return;
		loading_barnumsave = true;      

		var prodsList = {};
		var flag = false;
		var stoldList = {};
		$.ajax({
            type : "POST",            // HTTP method type(GET, POST) 형식이다.
            url : "./ajax.stock_list2.php",      // 컨트롤러에서 대기중인 URL 주소이다.
            async:false,
			data : {
            stoId: $("#stoId").val(),
            ct_id: $("#ct_id").val(),
          },            // Json 형식의 데이터이다.
			dataType: "json",
            success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
				stoldList = res;
				
            },
            error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
                alert("통신 실패.");
            }
        });
		var prodBarNum = "";
		$.each(stoldList, function(key, value) {			
			if(barcode_arr2[key] != "" && barcode_arr2[key] != undefined){
				if(barcode_arr2[key].length !=12){ flag =true;}	
				prodBarNum = barcode_arr2[key];
			}else{
				prodBarNum = "";
			}
			prodsList[key] = {
				stoId : value.stoId,
				prodId : value.prodId,
				prodBarNum : prodBarNum,//($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
			}
			if(prodBarNum != ""){
				insertBarCnt++;
			}			
		});
		//alert(JSON.stringify(prodsList));
		
		if (flag && $("#barcode").val() != "") {
			alert('바코드는 12자리를 입력해주세요.');
			loading_barnumsave = false;
			return false;
		}

		var pass = {};
		

		var sendData = {
			usrId : $("#od_mb_id").val(),
			prods : prodsList,
			entId : $("#od_entId").val(),
			pass: pass,
		}
		
		$.ajax({
        url : "./samhwa_orderform_stock_update.php",
        type : "POST",
        async : false,
        data : sendData,
        success : function(result){
          result = JSON.parse(result);
          if(result.errorYN == "Y") {
            alert(result.message);
          } else {
            
            //cart 기준 barcode insert update
            $.ajax({
              url : "<?=G5_SHOP_URL?>/ajax.ct_barcode_insert.php",
              type : "POST",
              async : false,
              data : {
                od_id : $("#od_id").val(),
                cnt : insertBarCnt
              },
              success : function(){
                  var sendData_barcode = {
                      mb_id : "<?=$member["mb_id"]?>",
                      od_id : $("#od_id").val(),
                      type : "1",
                      prods : prodsList
                  }
                  $.ajax({
                      url : "./ajax.barcode_log.php",
                      type : "POST",
                      async : false,
                      data : sendData_barcode,
                      success : function(result){
                          console.log(result);
                      }
                  });
                }
            });


            loading_barnumsave = false;
            
            // 미재고 바코드 처리
            var toApproveBarcodeArr = [];
			var orgBarcodeArr = [];
			var org_barcode_arr = $("#barcode"+$("#n").val()).val().split(",");  
			var ct_id = $("#ct_id").val();
            for(var k=0;k<barcode_arr2.length;k++){                
                var barcode = barcode_arr2[k];
                toApproveBarcodeArr.push({ ct_id: ct_id, barcode: barcode });
            }
			for(var h=0;h<org_barcode_arr.length;h++){
                orgBarcodeArr.push(org_barcode_arr[h]);
            }
			//alert(JSON.stringify(orgBarcodeArr));		

            if (toApproveBarcodeArr.length > 0) {
              $.ajax({
                url: '/shop/ajax.ct_barcode_insert_not_approved2.php',
                type: 'POST',
                data: {
                  toApproveBarcodeArr: toApproveBarcodeArr,
				  orgBarcodeArr: orgBarcodeArr
                },
                dataType: 'json',
                async: false
              })
              .done(function(result) {
              })
              .fail(function($xhr) {
                var data = $xhr.responseJSON;
                alert(data && data.message);
              })
            }

			if($("#barcode").val() != ""){
				if($("#ct_qty").val() == barcode_arr2.length){//수량과 바코드 수와 같으면
					$("#barcode_qty"+$("#n").val()).html(barcode_arr2.length+"/"+$("#ct_qty").val());
				}else{//다르면
					$("#barcode_qty"+$("#n").val()).html("<font color='red'>"+barcode_arr2.length+"/"+$("#ct_qty").val()+"</font>");
				}
				$("#barcode"+$("#n").val()).val($("#barcode").val().replace(/\n/g,','));
			}else{
				$("#barcode_qty"+$("#n").val()).html("<font color='red'>0/"+$("#ct_qty").val()+"</font>");
				$("#barcode"+$("#n").val()).val("우측 바코드 정보에 바코드를 등록 바랍니다.");
			}
			stock_check();
			toast('등록 되었습니다.');         
          }
        },
        error: function() {
          loading_barnumsave = false;
        }
      });
	}

	let removeToast;

	function toast(string) {//토스트 팝업
		const toast = document.getElementById("toast");

		toast.classList.contains("reveal") ?
			(clearTimeout(removeToast), removeToast = setTimeout(function () {
				document.getElementById("toast").classList.remove("reveal")
			}, 1000)) :
			removeToast = setTimeout(function () {
				document.getElementById("toast").classList.remove("reveal")
			}, 1500)
		toast.classList.add("reveal"),
			toast.innerText = string
	}
</script>
</body>
