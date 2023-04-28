<?php if($is_orderable) echo '<script src="'.$item_skin_url.'/shop.js"></script>'.PHP_EOL; ?>
<style>
.item-head{}
.it_opt_del{right:20px;}
.list-group-item .row{width:100%;}
.it_option{ max-width:100%; margin-left:0% ;left: 0%;}
@media screen and (max-width: 500px){
    .it_option{ max-width:90%;}
}
</style>
	<div style="width:500px; margin:50px auto;">
			<form name="fitem" method="post" action="./cartupdate.php" class="form item-form" role="form" onsubmit="return fitem_submit(this);">
			<input type="hidden" name="it_id[]" value="<?php echo $it_id; ?>">
			<input type="hidden" name="it_msg1[]" value="<?php echo $it['pt_msg1']; ?>">
			<input type="hidden" name="it_msg2[]" value="<?php echo $it['pt_msg2']; ?>">
			<input type="hidden" name="it_msg3[]" value="<?php echo $it['pt_msg3']; ?>">
			<input type="hidden" name="sw_direct" value="1">
			<input type="hidden" name="url">
			<input type="hidden" id="it_buy_min_qty" value="<?php echo $it['it_buy_min_qty']; ?>">
			<input type="hidden" id="it_buy_max_qty" value="<?php echo $it['it_buy_max_qty']; ?>">
			<input type="hidden" id="it_buy_inc_qty" value="<?php echo $it['it_buy_inc_qty']; ?>">

            <script>
                var sc_price_info;
                $(function () {
                    sc_price_info = $('.sc_price_info').html();
                })
                $('#ct_sc_method_sel').change(function () {
                    if ($(this).val().includes('quick')) {
                        $('.sc_price_info').html("* 담당자와 상담 후 선택해 주시기 바랍니다. (고객센터 : 02-2267-8080)");
                    } else {
                        $('.sc_price_info').html(sc_price_info);
                    }
                });
            </script>
			<div id="item_option" style="top:50%;left:50%;">
				<?php if($option_item) { ?>
					<table class="div-table table samhwa-item-option-table">
					<col width="120">
					<tbody>
					<?php echo $option_item; // 선택옵션	?>
					</tbody>
					</table>
				<?php }	?>

				<?php if($supply_item) { ?>
					<p><b>추가옵션</b></p>
					<table class="div-table table samhwa-item-option-table">
					<col width="120">
					<tbody>
					<?php echo $supply_item; // 추가옵션 ?>
					</tbody>
					</table>
				<?php }	?>
				<style>
				.col-sm-7 .it_opt_prc{display:none;} 
				.col-sm-5 .input-group-btn{ top:8px;left:10px;}
				</style>
				<?php if ($is_orderable) { ?>
					<div id="it_sel_option">
						<?php
						if(!$option_item) {
							if(!$it['it_buy_min_qty'])
								$it['it_buy_min_qty'] = 1;
						?>
							<ul id="it_opt_added" class="list-group">
								<li class="it_opt_list list-group-item <?php echo !$option_item && !$supply_item ? 'alone ' : ''; ?>">
									<input type="hidden" name="io_type[<?php echo $it_id; ?>][]" value="0">
									<input type="hidden" name="io_id[<?php echo $it_id; ?>][]" value="">
									<input type="hidden" name="io_value[<?php echo $it_id; ?>][]" value="<?php echo $it['it_name']; ?>">
									<input type="hidden" class="io_price" value="0">
									<input type="hidden" class="io_stock" value="<?php echo $it['it_stock_qty']; ?>">
									<div class="row">
										<div class="col-sm-5">
											<div class="input-group">
												<label for="ct_qty_<?php echo $i; ?>" class="sound_only">수량</label>
												<div class="input-group-btn">
													<button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus-circle fa-lg"></i><span class="sound_only">감소</span></button>
												</div>
												<input type="text" name="ct_qty[<?php echo $it_id; ?>][]" value="<?php echo $it['it_buy_min_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="form-control input-sm" size="5">
												<div class="input-group-btn">
													<button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus-circle fa-lg"></i><span class="sound_only">증가</span></button>
												</div>
											</div>
										</div>
									</div>
								</li>
							</ul>
							<script>
							$(function() {
								price_calculate();
							});
							</script>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
			<div class="popup-btn">
				<button type="submit" >확인</button>
				<button type="button" class="p-cls-btn" onclick="popup01_hide()">취소</button>
			</div>
			</form>
		</div>



<script>
// BS3
$(function() {
	$("select.it_option").addClass("form-control input-sm");
	$("select.it_supply").addClass("form-control input-sm");
});


// 바로구매, 장바구니 폼 전송
function fitem_submit(f) {

	f.action = "./cartupdate.php";
	f.target = "";


	if($(".it_opt_list").size() < 1) {
		alert("선택옵션을 선택해 주십시오.");
		return false;
	}
	var val, io_type, result = true;
	var sum_qty = 0;
	var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
	var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
	var $el_type = $("input[name^=io_type]");

	$("input[name^=ct_qty]").each(function(index) {
		val = $(this).val();

		if(val.length < 1) {
			alert("수량을 입력해 주십시오.");
			result = false;
			return false;
		}

		if(val.replace(/[0-9]/g, "").length > 0) {
			alert("수량은 숫자로 입력해 주십시오.");
			result = false;
			return false;
		}

		if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
			alert("수량은 1이상 입력해 주십시오.");
			result = false;
			return false;
		}

		io_type = $el_type.eq(index).val();
		if(io_type == "0")
			sum_qty += parseInt(val);
	});

	if(!result) {
		return false;
	}

	if(min_qty > 0 && sum_qty < min_qty) {
		alert("선택옵션 개수 총합 "+number_format(String(min_qty))+"개 이상 주문해 주십시오.");
		return false;
	}

	if(max_qty > 0 && sum_qty > max_qty) {
		alert("선택옵션 개수 총합 "+number_format(String(max_qty))+"개 이하로 주문해 주십시오.");
		return false;
	}

	if (document.pressed == "장바구니") {
		$.post("./itemcart.php", $(f).serialize(), function(error) {
			if(error != "OK") {
				alert(error.replace(/\\n/g, "\n"));
				return false;
			} else {
				if(!confirm("장바구니에 담겼습니다.\n\n확인을 원하시면 '아니오'를 선택하세요")) {
					document.location.href = "./cart.php";
				}
			}
		});
		return false;
	} else {
		return true;
	}
}
</script>

