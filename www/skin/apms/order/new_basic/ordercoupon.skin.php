<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 쿠폰 선택 시작 { -->
<div id="od_coupon_frm">
    <?php if($is_coupon) { ?>
		<div class="table-responsive">
			<table class="div-table table">
			<thead>
			<tr class="active">
				<th class="text-center" scope="col">쿠폰명</th>
				<th class="text-center" scope="col">적용대상</th>
				<th class="text-center" scope="col">할인금액</th>
				<th class="text-center" scope="col">적용</th>
			</tr>
			</thead>
			<tbody>
			<?php for($i=0; $i < count($list); $i++) { 
				if($list[$i]['cp_method'] == 0){//개별상품
					$cp_target_name = $list[$i]['it_name'];
				}elseif($list[$i]['cp_method'] == 1){//카테고리
					$cp_target_name = $list[$i]['ca_name']." 전체";
				}else{
					$cp_target_name = '';
				}
				?>
				<tr>
					<td>
						<input type="hidden" name="o_cp_id[]" value="<?php echo $list[$i]['cp_id']; ?>">
						<input type="hidden" name="o_cp_prc[]" value="<?php echo $list[$i]['dc']; ?>">
						<input type="hidden" name="o_cp_subj[]" value="<?php echo $list[$i]['cp_subject']; ?>">
						<input type="hidden" name="o_cp_method[]" value="<?php echo $list[$i]['cp_method']; ?>">
						<input type="hidden" name="o_cp_target[]" value="<?php echo $list[$i]['cp_target']; ?>">
						<?php echo get_text($list[$i]['cp_subject']); ?>
					</td>
					<td class="text-center"><?php echo $cp_target_name; ?></td>
					<td class="text-right"><?php echo number_format($list[$i]['dc']); ?></td>
					<td class="text-center">
					<?php if($list[$i]['cp_method'] == "3" && $_POST['send_cost'] == '0'){//배송비
							echo "사용불가";
						}elseif($list[$i]['cp_method'] == "0" && !(strpos($_POST['it_ids'],$list[$i]['cp_target']) !== false)){//개별상품
							echo "사용불가";
						}elseif($list[$i]['cp_method'] == "1" && !(strpos($_POST['ca_ids'],",".$list[$i]['cp_target']) !== false)){//카테고리
							echo "사용불가";
						}else{?>						
						<button type="button" class="od_cp_apply btn btn-black btn-xs">적용</button>
						<?php }?>
					</td>
				</tr>
			<?php }	?>
			</tbody>
			</table>
		</div>
	<?php } else { ?>
		<p class="text-center">사용할 수 있는 쿠폰이 없습니다.</p>
    <?php } ?>

	<br>

    <div class="text-center">
        <button type="button" id="od_coupon_close" class="btn btn-black btn-sm">닫기</button>
    </div>
</div>
<!-- } 쿠폰 선택 끝 -->
