<?php

	include_once("./_common.php");

	$g5["title"] = "주문 내역 바코드 수정";
	include_once(G5_ADMIN_PATH."/admin.head.php");

	$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$deliveryTotalCnt = 0;

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
					a.ct_delivery_company,
					a.ct_delivery_num,
					a.ct_delivery_cnt,
					a.ct_delivery_price,
					b.it_delivery_cnt,
					b.it_delivery_price,
					b.it_img1
			  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
			  AND a.ct_delivery_yn = 'Y'
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

	<style>
		
		#hd, #text_size, .page_title, #ft { display: none; }
		html, body { width: 100%; height: 100%; min-width: 100%; float: left; margin: 0 !important; padding: 0 !important; overflow: hidden; }
		#tbl_wrap { top: 0; min-height: 100%; }
		#wrapper { min-height: 100%; padding: 0; border: 0; }
		
		.barNumGuideBox { position: absolute; width: 380px; border: 1px solid #DDD; background-color: #FFF; text-align: left; padding: 15px 20px; display: none; margin-left: 35px; margin-top: 5px; right: 10px; }
		.barNumGuideBox > .title { width: 100%; font-weight: bold; margin-bottom: 15px; position: relative; }
		.barNumGuideBox > .title > button { float: right; }
		.barNumGuideBox > p { width: 100%; padding: 0; }
		
		#container { position: absolute; width: 100%; height: 100%; left: 0; top: 0; }
		#prodBarNumFormWrap { width: 100%; height: calc(100% - 60px); float: left; overflow: auto; }
		
		#prodBarNumFormWrap > .titleWrap { width: 100%; float: left; font-weight: bold; font-size: 21px; padding: 20px; }
		
		#prodBarNumFormWrap > .tableWrap { width: 100%; float: left; }
		#prodBarNumFormWrap > .tableWrap > table { width: 100%; float: left; table-layout: fixed; }
		#prodBarNumFormWrap > .tableWrap > table thead > tr > th { border-top: 1px solid #3366CC; border-bottom: 1px solid #3366CC; padding: 10px 0; font-weight: bold; font-size: 13px; }
		#prodBarNumFormWrap > .tableWrap > table tbody > tr > td { border-left: 0; border-right: 0; padding: 10px; vertical-align: top; }
		#prodBarNumFormWrap > .tableWrap > table tbody > tr:last-of-type > td { border-bottom: 0; }
		
		#prodBarNumBtnWrap { width: 100%; height: 60px; float: left; background-color: #F1F1F1; padding: 10px; }
		#prodBarNumBtnWrap > button { width: 100px; height: 40px; line-height: 28px; float: left; font-size: 13px; font-weight: bold; color: #FFF; background-color: #333; margin-left: 5px; }
		#prodBarNumBtnWrap > button:first-of-type { margin-left: 0; }
		#prodBarNumBtnWrap > button.main { width: calc(100% - 105px); background-color: #3366CC; }
		
		.frm_input { width: 100%; font-size: 13px !important; padding: 0 5px; }
		
	</style>
	
	<form id="prodBarNumFormWrap">
		<input type="hidden" name="od_id" value="<?=$od["od_id"]?>">
		
		<div class="titleWrap">
			배송정보입력
		</div>
		
		<div class="tableWrap">
			<table>
				<colgroup>
					<col width="">
					<col width="15%">
					<col width="20%">
					<col width="10%">
					<col width="150px">
				</colgroup>
				
				<thead>
					<tr>
						<th>상품(옵션)</th>
						<th>택배사</th>
						<th>송장번호</th>
						<th>박스수량</th>
						<th>배송비</th>
					</tr>
				</thead>
				
				<tbody>
				<?php 
					for($i = 0; $i < count($carts); $i++){ 
						$options = $carts[$i]["options"];

						for($k = 0; $k < count($options); $k++){
				?>
						<tr data-price="<?=$carts[$i]["it_delivery_price"]?>" data-cnt="<?=$carts[$i]["it_delivery_cnt"]?>">
							<td>
								<input type="hidden" name="ct_id[]" value="<?=$carts[$i]["ct_id"]?>">
								<?=stripslashes($carts[$i]["it_name"])?>
								<?php if($carts[$i]["it_name"] != $options[$k]["ct_option"]){ ?>
									(<?=$options[$k]["ct_option"]?>)
								<?php } ?>
							</td>
							<td>
								<select class="frm_input" name="ct_delivery_company_<?=$carts[$i]["ct_id"]?>">
								<?php foreach($delivery_companys as $data){ ?>
									<option value="<?=$data["val"]?>" <?=($carts[$i]["ct_delivery_company"] == $data["val"]) ? "selected" : ""?>><?=$data["name"]?></option>
								<?php } ?>
								</select>
							</td>
							<td><input type="text" value="<?=$carts[$i]["ct_delivery_num"]?>" class="frm_input" name="ct_delivery_num_<?=$carts[$i]["ct_id"]?>"></td>
							<td>
								<select class="frm_input ct_delivery_cnt" name="ct_delivery_cnt_<?=$carts[$i]["ct_id"]?>">
								<?php for($ii = 1; $ii < 21; $ii++){ ?>
									<option value="<?=$ii?>" <?=($carts[$i]["ct_delivery_cnt"] == $ii) ? "selected" : ""?>><?=$ii?></option>
								<?php } ?>
								</select>
							</td>
							<td>
								<input type="text" value="<?=$carts[$i]["ct_delivery_price"]?>" class="frm_input ct_delivery_price" name="ct_delivery_price_<?=$carts[$i]["ct_id"]?>" style="width: 100px;">
								<span>원</span>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
				</tbody>
			</table>
		</div>
		
	</form>
	
	<div id="prodBarNumBtnWrap">
		<button type="button" class="main" id="prodBarNumSaveBtn">저장</button>
		<button type="button" onclick="window.close();">취소</button>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$(".ct_delivery_cnt").change(function(){
//				var parent = $(this).closest("tr");
//				
//				var price = Number($(parent).attr("data-price"));
//				var cnt = Number($(parent).attr("data-cnt"));
//				
//				var val = $(this).val();
//				
//				if(cnt){
//					var tmpCnt = Math.floor(val / cnt);
//					
//					if(tmpCnt < (val / cnt)){
//						tmpCnt += 1;
//					}
//					
//					$(parent).find(".ct_delivery_price").val(tmpCnt * price);
//				}
			});
			
			$("#prodBarNumSaveBtn").click(function() {
				var ordId = "<?=$od["ordId"]?>";
				var changeStatus = true;
				var insertBarCnt = 0;

				$.ajax({
					url : "./samhwa_orderform_deliveryInfo_update.php",
					type : "POST",
					async : false,
					data : $("#prodBarNumFormWrap").serialize(),
					success : function(result){
						alert("저장이 완료되었습니다.");

						opener.location.reload();
						window.close();
					}
				});
			});
			
		})
	</script>

<?php include_once(G5_ADMIN_PATH."/admin.tail.php"); ?>