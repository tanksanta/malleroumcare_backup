<?php

	include_once("./_common.php");

	$g5["title"] = "주문 내역 바코드 수정";
	// include_once(G5_ADMIN_PATH."/admin.head.php");

	$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$deliveryTotalCnt = 0;

	if (!$od['od_id']) {
		alert("해당 주문번호로 주문서가 존재하지 않습니다.");
	} else {
		if($od["ordId"]){
			$sendData = [];
			$sendData["penOrdId"] = $od["ordId"];
			$sendData["uuid"] = $od["uuid"];

			$oCurl = curl_init();
			curl_setopt($oCurl, CURLOPT_PORT, 9001);
			curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/order/selectList");
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$res = curl_exec($oCurl);
			curl_close($oCurl);

			$result = json_decode($res, true);
			$result = $result["data"];

			if($result){
				foreach($result as $data){
					$thisProductData = [];

					$thisProductData["prodId"] = $data["prodId"];
					$thisProductData["prodColor"] = $data["prodColor"];
					$thisProductData["stoId"] = $data["stoId"];
					$thisProductData["prodBarNum"] = $data["prodBarNum"];
					$thisProductData["penStaSeq"] = $data["penStaSeq"];
					array_push($prodList, $thisProductData);
				}
			}
		} else {
			$stoIdData = $od["stoId"];
			$stoIdData = explode(",", $stoIdData);
			$stoIdDataList = [];
			foreach($stoIdData as $data){
				array_push($stoIdDataList, $data);
			}
			$stoIdData = implode("|", $stoIdDataList);
		}
	}

	// 상품목록
	$sql = " select a.ct_id,
					a.it_id,
					a.it_name,
					a.cp_price,
					a.ct_notax,
					a.ct_send_cost,
					a.ct_sendcost,
					a.it_sc_type,
					a.pt_it,
					a.pt_id,
					b.ca_id,
					b.ca_id2,
					b.ca_id3,
					b.pt_msg1,
					b.pt_msg2,
					b.pt_msg3,
					a.ct_status,
					b.it_model,
					b.it_outsourcing_use,
					b.it_outsourcing_company,
					b.it_outsourcing_manager,
					b.it_outsourcing_email,
					b.it_outsourcing_option,
					b.it_outsourcing_option2,
					b.it_outsourcing_option3,
					b.it_outsourcing_option4,
					b.it_outsourcing_option5,
					a.pt_old_name,
					a.pt_old_opt,
					a.ct_uid,
					a.prodMemo,
					a.prodSupYn,
					a.ct_qty,
					a.ct_stock_qty,
					b.it_img1
			  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
			  group by a.it_id, a.ct_uid
			  order by a.ct_id ";

	$result = sql_query($sql);

	$carts = array();
	$cate_counts = array();

	for($i=0; $row=sql_fetch_array($result); $i++) {

		$cate_counts[$row['ct_status']] += 1;

		// 상품의 옵션정보
		$sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_stock_qty, ct_barcode, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, ct_sendcost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount, ct_uid
						, ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn
					from {$g5['g5_shop_cart_table']} MT
					where od_id = '{$od['od_id']}'
						and it_id = '{$row['it_id']}'
						and ct_uid = '{$row['ct_uid']}'
					order by io_type asc, ct_id asc ";
		$res = sql_query($sql);

		$row['options_span'] = sql_num_rows($res);

		$row['options'] = array();
		for($k=0; $opt=sql_fetch_array($res); $k++) {

			$opt_price = 0;

			if($opt['io_type'])
				$opt_price = $opt['io_price'];
			else
				$opt_price = $opt['ct_price'] + $opt['io_price'];

			$opt["opt_price"] = $opt_price;

			// 소계
			$opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
			$opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

			if($opt["prodSupYn"] == "Y"){
				$opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
			}

			$row['options'][] = $opt;
		}


		// 합계금액 계산
		$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
						SUM(ct_qty) as qty,
						SUM(ct_discount) as discount,
						SUM(ct_send_cost) as sendcost
					from {$g5['g5_shop_cart_table']}
					where it_id = '{$row['it_id']}'
						and od_id = '{$od['od_id']}'
						and ct_uid = '{$row['ct_uid']}'";
		$sum = sql_fetch($sql);

		$row['sum'] = $sum;

		$carts[] = $row;
	}

?>
<!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Document</title>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
 </head>
 <body>
	

<style>
    @font-face { font-family: 'NanumBarunGothic';
    src: url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.eot');
    src: url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.eot') format('embedded-opentype'),
    url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.woff') format('woff');}
    body { margin:0px; padding:0px; font-family: 'NanumBarunGothic', 'serif';}
    ul{list-style:none;}
    a { text-decoration:none } 
    .section1{ position:relative; width:100%; padding:0px;}
    .head{ position:relative; width:100%; background-color:#333333;height:60px; line-height:60px;}
    .head .p1{  float:left; margin-left:20px; color: #f1f1f1;font-size:30px; }
    .head .xbtn{ float:right;  margin-right:20px;color: #f1f1f1; font-size:40px; line-height:60px;}

    .naming_box{position:relative; width:100%; color: #333333; font-size:19px;height:70px; border-bottom: 1px solid #dddddd;}
    .naming_box .sp1{ position:relative; left:20px; line-height:70px;}
    .imfomation_box{ margin:0px;width:100%;position:relative; padding:0px;display:block; width:100%; height:auto;}
    .imfomation_box a .li_box{width:100%;  height:70px; text-align:center; border-bottom: 1px solid #dddddd;}
    .imfomation_box a .li_box .li_box_line1{width: 100%; margin:auto; float:left;color:#000;}
    .imfomation_box a .li_box .li_box_line1 .p1{height:100%;  margin:auto; float:left; color:#000;line-height:70px;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span1{ flex: 1; font-size:22px; margin-left:20px; float:left;}



    /* @media screen and (max-width: 4000px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:35px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:30px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:30px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:330px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{width:500px;float:left; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
    }
    @media screen and (max-width: 1200px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:30px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:25px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:25px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:270px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{width:auto;}
    }

    @media screen and (max-width: 1000px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:30px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:25px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:25px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:270px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    }

    @media screen and (max-width: 900px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:28px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:23px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:23px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

    }

    @media screen and (max-width: 630px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:23px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:20px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:20px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

    }


    @media screen and (max-width: 500px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:20px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:15px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:15px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{
            width:170px;float:left;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }
    }
    .li_box.type1{display:block;}
    .li_box.type1{display:none;} */
}
 </style>



<section class="section1">

    <div class="head">
        <b class="p1">바코드입력</b>
        <a href="javascript:history.back();"><span class="xbtn">&times;</span></a>
    </div>

    <div class="naming_box">
        <span class="sp1">
            <span class="sp1_1">[재고]<?=$od_id?></span>
            <span class="sp1_2">(배송:<?=count($carts)+1?>개)</span>
        </span>
    </div>

    <ul class="imfomation_box" id="imfomation_box">
<?php 
        for($i = 0; $i < count($carts); $i++){ 
            $options = $carts[$i]["options"];
            
            for($k = 0; $k < count($options); $k++){
?>
                <a href="">
                    <li class="li_box">
                        <div class="li_box_line1">
                            <p class="p1">
                                <span class="span1">
                                    <span class="span1_1">상품명(옵션명)</span>
                                </span>
                            </p>
                        </div>
                    </li>
                </a>
<?php
            }   
        }
?>
    <?php 
        for($i = 0; $i < count($carts); $i++){ 
            $options = $carts[$i]["options"];
            
            for($k = 0; $k < count($options); $k++){
    ?>
						<tr>
							<td>
								<?=stripslashes($carts[$i]["it_name"])?>
								<?php if($carts[$i]["it_name"] != $options[$k]["ct_option"]){ ?>
									(<?=$options[$k]["ct_option"]?>)
								<?php } ?>
							</td>
							<td>
								<ul>
									<li>
										<input type="text" class="frm_input" style="width: 302px;">
										<button type="button" style="width: 35px; height: 24px; background-color: #3366CC; color: #FFF;" class="barNumCustomSubmitBtn">적용</button>
										<button type="button" style="width: 35px; height: 24px; background-color: #999; color: #FFF;" class="barNumGuideOpenBtn">방법</button>
										<div class="barNumGuideBox">
											<div class="title">바코드 일괄 등록 방법 <button type="button" class="closeBtn">X</button></div>
											<p>
												공통된 문자/숫자를 앞에 부여 후 반복되는 숫자를 입력합니다.<br><br>
												예시) 010101^3,4,5-10- 010101은 공동문자/숫자입니다.<br><br>
												- ^이후는 자동으로 입력하기 위한 내용입니다.<br>
												-    “숫자 입력 후 콤마(,)”를 입력하면 독립 숫자가 입력됩니다.<br>
												- 5-10이라고 입력하면5부터10까지 순차적으로 입력됩니다.<br>
												- 00-20으로 시작 숫자가00인 경우2자리 숫자로 입력됩니다
											</p>
										</div>
									</li>
								<?php for($b = 0; $b< $options[$k]["ct_qty"]; $b++){ ?>
									<li style="padding-top: 5px;">
										<input type="text" value="<?=$prodList[$prodListCnt]["prodBarNum"]?>" class="frm_input required prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoIdDataList[$prodListCnt]?>">
									</li>
								<?php $prodListCnt++; } ?>
								</ul>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
    </ul>
    <input type="hidden" value="1" id="page">
</section>


	<div id="prodBarNumFormWrap">
		
		<div class="titleWrap">
			바코드 정보입력
		</div>
		
		<div class="tableWrap">
			<table>
				<colgroup>
					<col width="">
					<col width="400px">
				</colgroup>
				
				<thead>
					<tr>
						<th>상품(옵션)</th>
						<th>바코드</th>
					</tr>
				</thead>
				
				<tbody>
				<?php 
					for($i = 0; $i < count($carts); $i++){ 
						$options = $carts[$i]["options"];
						
						for($k = 0; $k < count($options); $k++){
				?>
						<tr>
							<td>
								<?=stripslashes($carts[$i]["it_name"])?>
								<?php if($carts[$i]["it_name"] != $options[$k]["ct_option"]){ ?>
									(<?=$options[$k]["ct_option"]?>)
								<?php } ?>
							</td>
							<td>
								<ul>
									<li>
										<input type="text" class="frm_input" style="width: 302px;">
										<button type="button" style="width: 35px; height: 24px; background-color: #3366CC; color: #FFF;" class="barNumCustomSubmitBtn">적용</button>
										<button type="button" style="width: 35px; height: 24px; background-color: #999; color: #FFF;" class="barNumGuideOpenBtn">방법</button>
										<div class="barNumGuideBox">
											<div class="title">바코드 일괄 등록 방법 <button type="button" class="closeBtn">X</button></div>
											<p>
												공통된 문자/숫자를 앞에 부여 후 반복되는 숫자를 입력합니다.<br><br>
												예시) 010101^3,4,5-10- 010101은 공동문자/숫자입니다.<br><br>
												- ^이후는 자동으로 입력하기 위한 내용입니다.<br>
												-    “숫자 입력 후 콤마(,)”를 입력하면 독립 숫자가 입력됩니다.<br>
												- 5-10이라고 입력하면5부터10까지 순차적으로 입력됩니다.<br>
												- 00-20으로 시작 숫자가00인 경우2자리 숫자로 입력됩니다
											</p>
										</div>
									</li>
								<?php for($b = 0; $b< $options[$k]["ct_qty"]; $b++){ ?>
									<li style="padding-top: 5px;">
										<input type="text" value="<?=$prodList[$prodListCnt]["prodBarNum"]?>" class="frm_input required prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoIdDataList[$prodListCnt]?>">
									</li>
								<?php $prodListCnt++; } ?>
								</ul>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
				</tbody>
			</table>
		</div>
		
	</div>
	
	<div id="prodBarNumBtnWrap">
		<button type="button" class="main" id="prodBarNumSaveBtn">저장</button>
        <?php if($_GET['new']){ ?>
		<button type="button" onclick="history.back();">취소</button>
        <?php }else{ ?>
            <button type="button" onclick="window.close();">취소</button>
        <?php }?>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$(".barNumCustomSubmitBtn").click(function(){
				var val = $(this).closest("li").find("input").val();
				var target = $(this).closest("ul").find("li");
				var barList = [];

				if(val.indexOf("^") == -1){
					alert("내용을 입력해주시길 바랍니다.");
					return false;
				}

				for(var i = 0; i < target.length; i++){
					if(i > 0){
						if($(target[i]).find("input").val()){
							if(!confirm("이미 등록된 바코드가 있습니다.\n무시하고 적용하시겠습니까?")){
								return false;
							} else {
								break;
							}
						}
					}
				}

				if(val){
					val = val.split("^");
					var first = val[0];
					var secList = val[1].split(",");
					for(var i = 0; i < secList.length; i++){
						if(secList[i].indexOf("-") == -1){
							barList.push(first + secList[i]);
						} else {
							var secData = secList[i].split("-");
							var secData0Len = secData[0].length;
							secData[0] = Number(secData[0]);
							secData[1] = Number(secData[1]);

							for(var ii = secData[0]; ii < (secData[1] + 1); ii++){
								var barData = ii;
								if(String(barData).length < secData0Len){
									var iiiCnt = secData0Len - String(barData).length;
									for(var iii = 0; iii < iiiCnt; iii++){
										barData = "0" + barData;
									}
								}

								barList.push(first + barData);
							}
						}
					}

					for(var i = 0; i < target.length; i++){
						if(i > 0){
							$(target[i]).find("input").val(barList[i - 1]);
						}
					}
				}
			});

			$(".barNumGuideBox .closeBtn").click(function(){
				$(this).closest(".barNumGuideBox").hide();
			});

			$(".barNumGuideOpenBtn").click(function(){
				$(this).next().toggle();
			});

			var stoldList = [];
			var stoIdData = "<?=$stoIdData?>";
			if(stoIdData){
				var sendData = {
					stoId : stoIdData
				}

				$.ajax({
					url : "https://eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do",
					type : "POST",
					dataType : "json",
					contentType : "application/json; charset=utf-8;",
					data : JSON.stringify(sendData),
					success : function(res){
						$.each(res.data, function(key, value){
							$("." + value.stoId).val(value.prodBarNum);
						});

						if(res.data){
							stoldList = res.data;
						}
					}
				});
			}
			
			$("#prodBarNumSaveBtn").click(function() {
				var ordId = "<?=$od["ordId"]?>";
				var changeStatus = true;
				var insertBarCnt = 0;

				if(ordId){
					var productList = <?=($prodList) ? json_encode($prodList) : "[]"?>;
					$.each(productList, function(key, value){
						var prodBarNumItem = $(".prodBarNumItem_" + value.penStaSeq);
						var prodBarNum = "";

						for(var i = 0; i < prodBarNumItem.length; i++){
							prodBarNum += (prodBarNum) ? "," : "";
							prodBarNum += $(prodBarNumItem[i]).val();
							
							if($(prodBarNumItem[i]).val()){
								insertBarCnt++;
							}
						}

						productList[key]["prodBarNum"] = prodBarNum;
					});

					var sendData = {
						usrId : "<?=$od["mb_id"]?>",
						penOrdId : "<?=$od["ordId"]?>",
						delGbnCd : "",
						ordWayNum : "",
						delSerCd : "",
						ordNm : $("#od_b_name").val(),
						ordCont : $("#od_b_hp").val(),
						ordMeno : $("#od_memo").val(),
						ordZip : $("#od_b_zip").val(),
						ordAddr : $("#od_b_addr1").val(),
						ordAddrDtl : $("#od_b_addr2").val(),
						eformYn : "<?=$od["eformYn"]?>",
						staOrdCd : "<?=$od["staOrdCd"]?>",
						lgsStoId : "",
						prods : productList
					}

					$.ajax({
						url : "./samhwa_orderform_order_update.php",
						type : "POST",
						async : false,
						data : sendData,
						success : function(result){
							result = JSON.parse(result);
							if(result.errorYN == "Y"){
								alert(result.message);
							} else {
								alert("저장이 완료되었습니다.");
								
								$.ajax({
									url : "/shop/ajax.order.prodBarNum.cnt.php",
									type : "POST",
									async : false,
									data : {
										od_id : "<?=$od_id?>",
										cnt : insertBarCnt
									}
								});
								opener.location.reload();
								window.close();
							}
						}
					});
				} else {
					var prodsList = {};

					$.each(stoldList, function(key, value){
						prodsList[key] = {
							stoId : value.stoId,
							prodColor : value.prodColor,
							prodSize : value.prodSize,
							prodBarNum : ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
							prodManuDate : value.prodManuDate,
							stateCd : value.stateCd,
							stoMemo : (value.stoMemo) ? value.stoMemo : ""
						}
						
						if($("." + value.stoId).val()){
							insertBarCnt++;
						}
					});

					var sendData = {
						usrId : "<?=$od["mb_id"]?>",
						prods : prodsList
					}

					$.ajax({
						url : "./samhwa_orderform_stock_update.php",
						type : "POST",
						async : false,
						data : sendData,
						success : function(result){
							result = JSON.parse(result);
							if(result.errorYN == "Y"){
								alert(result.message);
							} else {
								alert("저장이 완료되었습니다.");
								
								$.ajax({
									url : "/shop/ajax.order.prodBarNum.cnt.php",
									type : "POST",
									async : false,
									data : {
										od_id : "<?=$od_id?>",
										cnt : insertBarCnt
									}
								});
                                
                                <?php if($_GET['new']){ ?>
                                    history.back();
                                <?php }else{ ?>
                                    opener.location.reload();
                                    window.close();
                                <?php }?>
							}
						}
					});
				}
			});
			
		})
	</script>
 </body>