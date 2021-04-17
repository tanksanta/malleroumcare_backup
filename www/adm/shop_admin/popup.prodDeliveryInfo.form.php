<?php

	include_once("./_common.php");

	$g5["title"] = "주문 내역 바코드 수정";
	include_once(G5_ADMIN_PATH."/admin.head.php");

	$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$deliveryTotalCnt = 0;

	$carts = get_carts_by_od_id($od_id, 'Y');

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

		.combine {
			display:none;
		}
		.combine.active {
			display:table-cell;
		}
		.ct_combine_ct_id {
			width:100%;
			text-align-last:center;
		}
		
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
					<col width="80px">
				</colgroup>
				
				<thead>
					<tr>
						<th>상품(옵션)</th>
						<th>분류</th>
						<th>송장번호</th>
						<th>박스수량</th>
						<th>배송비</th>
						<th>합포여부</th>
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
							<td class="combine combine_n <?php if(!$carts[$i]['ct_combine_ct_id']) echo ' active ';?>">
								<select class="frm_input" name="ct_delivery_company_<?=$carts[$i]["ct_id"]?>">
								<?php foreach($delivery_companys as $data){ ?>
									<option value="<?=$data["val"]?>" <?=($carts[$i]["ct_delivery_company"] == $data["val"]) ? "selected" : ""?>><?=$data["name"]?></option>
								<?php } ?>
								</select>
							</td>
							<td class="combine combine_n <?php if(!$carts[$i]['ct_combine_ct_id']) echo ' active ';?>">
								<input type="text" value="<?=$carts[$i]["ct_delivery_num"]?>" class="frm_input" name="ct_delivery_num_<?=$carts[$i]["ct_id"]?>">
							</td>
							<td class="combine combine_n <?php if(!$carts[$i]['ct_combine_ct_id']) echo ' active ';?>">
								<select class="frm_input ct_delivery_cnt" name="ct_delivery_cnt_<?=$carts[$i]["ct_id"]?>">
								<?php for($ii = 1; $ii < 21; $ii++){ ?>
									<option value="<?=$ii?>" <?=($carts[$i]["ct_delivery_cnt"] == $ii) ? "selected" : ""?>><?=$ii?></option>
								<?php } ?>
								</select>
							</td>
							<td class="combine combine_n <?php if(!$carts[$i]['ct_combine_ct_id']) echo ' active ';?>">
								<input type="text" value="<?=$carts[$i]["ct_delivery_price"]?>" class="frm_input ct_delivery_price" name="ct_delivery_price_<?=$carts[$i]["ct_id"]?>" style="width: 100px;">
								<span>원</span>
							</td>
							<td class="combine combine_y <?php if($carts[$i]['ct_combine_ct_id']) echo ' active ';?>" colspan="4">
								<select name="ct_combine_ct_id_<?php echo $carts[$i]["ct_id"]; ?>" class="ct_combine_ct_id">
									<?php
									foreach($carts as $c) { 
										if ($c['ct_id'] === $carts[$i]['ct_id']) continue;
									?>
										<option value="<?php echo $c['ct_id']; ?>"><?php echo stripslashes($c["it_name"]); ?>
									<?php } ?>
								</select>
							</td>
							<td>
								<label><input type="checkbox" name="ct_combine_<?php echo $carts[$i]["ct_id"]; ?>" class="chk_ct_combine" value="1" <?php if($carts[$i]['ct_combine_ct_id']) echo ' checked';?>> 합포</label>
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

			$('.chk_ct_combine').click(function() {
				var parent = $(this).closest('tr');

				if ($(this).is(":checked")) {
					$(parent).find('.combine_y').addClass('active');
					$(parent).find('.combine_n').removeClass('active');
					return;
				}

				$(parent).find('.combine_n').addClass('active');
				$(parent).find('.combine_y').removeClass('active');
			})
			
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