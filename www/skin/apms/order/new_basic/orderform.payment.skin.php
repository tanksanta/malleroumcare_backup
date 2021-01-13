<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<!-- <?php if (!$default['de_card_point']) { ?>
	<div class="well" id="sod_frm_pt_alert">
		<strong>무통장입금</strong> 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.
	</div>
<?php } ?> -->

<section id="sod_frm_pay" class="order-payment">
	<div class="panel panel-default">
		<div class="panel-heading"><strong> 결제정보</strong></div>
		<div class="panel-body">
			<?php if($oc_cnt > 0) { ?>
				<div class="form-group">
					<label class="col-sm-2 control-label"><b>주문할인금액</b></label>
					<label class="col-sm-2 control-label">
						<span id="od_cp_price">0</span>원
					</label>
					<div class="col-sm-7">
						<input type="hidden" name="od_cp_id" value="">
						<div class="btn-group">
							<button type="button" id="od_coupon_btn" class="btn btn-black btn-sm">쿠폰적용</button>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if($sc_cnt > 0) { ?>
				<div class="form-group">
					<label class="col-sm-2 control-label"><b>배송할인금액</b></label>
					<label class="col-sm-2 control-label">
						<span id="sc_cp_price">0</span>원
					</label>
					<div class="col-sm-7">
						<input type="hidden" name="sc_cp_id" value="">
						<div class="btn-group">
							<button type="button" id="sc_coupon_btn" class="btn btn-black btn-sm">쿠폰적용</button>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="form-group">
				<label class="col-sm-2 control-label"><b>총주문금액</b></label>
				<label class="col-sm-2 control-label">
					<b><span id="od_tot_price"><?php echo number_format($tot_price); ?></span></b>원
				</label>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><b>추가배송비</b></label>
				<label class="col-sm-2 control-label">
					<span id="od_send_cost2">0</span>원
				</label>
				<div class="col-sm-7">
					<label class="control-label text-muted font-12">지역에 따라 추가되는 도선료 등의 배송비입니다.</label>
				</div>
			</div>

			<?php if($is_none) { ?>
				<div class="alert alert-danger text-center">
					<?php if($default['as_point']) { ?>
						<b>보유하신 포인트가 부족합니다.</b>
					<?php } else { ?>
						<b>결제할 방법이 없습니다.</b> 운영자에게 알려주시면 감사하겠습니다.
					<?php } ?>
				</div>
			<?php } else { ?>
				<div class="form-group">
					<label class="col-sm-2 control-label"><b>결제방법</b></label>
					<div class="col-sm-10 radio-line">
						<?php if($is_po) { ?>
							 <label><input type="radio" id="od_settle_point" name="od_settle_case" value="포인트"> 포인트결제</label>
						<?php } ?>

						<?php if($is_mu) { ?>
							<label><input type="radio" id="od_settle_bank" name="od_settle_case" value="무통장"> 무통장입금</label>
						<?php } ?>

						<?php if($is_vbank) { ?>
							<label><input type="radio" id="od_settle_vbank" name="od_settle_case" value="가상계좌"> <?php echo $escrow_title;?>가상계좌</label>
						<?php } ?>

						<?php if($is_iche) { ?>
							<label><input type="radio" id="od_settle_iche" name="od_settle_case" value="계좌이체"> <?php echo $escrow_title;?>계좌이체</label>
						<?php } ?>

						<?php if($is_hp) { ?>
							<label><input type="radio" id="od_settle_hp" name="od_settle_case" value="휴대폰"> 휴대폰</label>
						<?php } ?>

						<?php if($is_card) { ?>
							<label><input type="radio" id="od_settle_card" name="od_settle_case" value="신용카드"> 신용카드</label>
						<?php } ?>

						<?php if($is_easy_pay) { ?>
							<label><input type="radio" id="od_settle_easy_pay" name="od_settle_case" value="간편결제"> <span class="<?php echo $pg_easy_pay_name;?>"><?php echo $pg_easy_pay_name;?></span></label>
						<?php } ?>

						<?php if($is_kakaopay) { ?>
							 <label><input type="radio" id="od_settle_kakaopay" name="od_settle_case" value="KAKAOPAY"> <span class="kakaopay_icon">KAKAOPAY</span></label>
						<?php } ?>

						<?php if($is_samsung_pay) { ?>
							<label><input type="radio" id="od_settle_samsung_pay" data-case="samsungpay" name="od_settle_case" value="삼성페이"> <span class="samsung_pay">삼성페이</span></label>
						<?php } ?>

						<?php if($is_inicis_lpay) { ?>
							<label><input type="radio" id="od_settle_inicislpay" data-case="lpay" name="od_settle_case" value="lpay"> <span class="inicis_lpay">L.pay</span></label>
						<?php } ?>

					</div>
				</div>

				<?php if($is_point) { ?>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="od_temp_point"><b>사용 포인트</b></label>
						<div class="col-sm-2">
							<input type="hidden" name="max_temp_point" value="<?php echo $temp_point;?>">
							<div class="input-group">
								<input type="text" name="od_temp_point" value="0" id="od_temp_point" class="frm_input form-control input-sm" size="10">
								<span class="input-group-addon">점</span>
							</div>
						</div>
						<div class="col-sm-7 font-12">
							<span id="sod_frm_pt">
								보유포인트(<?php echo display_point($member['mb_point']);?>)중 <strong id="use_max_point">최대 <?php echo display_point($temp_point);?></strong>까지 사용 가능 
								(<?php echo $point_unit;?>점 단위로 입력)
							</span>
						</div>
					</div>
				<?php } ?>

				<?php if($is_mu) { ?>
					<div id="settle_bank" style="display:none">
						<div class="form-group">
							<label class="col-sm-2 control-label" for="od_bank_account"><b>입금할 계좌</b></label>
							<div class="col-sm-4">
								<select name="od_bank_account" id="od_bank_account" class="form-control input-sm">
									<option value="">선택하십시오.</option>
									<?php echo $bank_account; ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="od_deposit_name"><b>입금자명</b></label>
							<div class="col-sm-2">
								<input type="text" name="od_deposit_name" id="od_deposit_name" class="form-control input-sm" size="10" maxlength="20">
							</div>
						</div>
					</div>
				<?php } ?>

				<?php
				$sql = "SELECT * FROM g5_member_giup_manager WHERE mb_id = '{$member['mb_id']}'";
				$result = sql_query($sql);
				$managers = array();
				while( $m_row = sql_fetch_array($result) ) {
					$managers[] = $m_row;
				}
				?>

				<?php if($mb_giup && count($managers) ) { ?>
					<style>
					#mb_giup_manager {
						width: 150px;
						border: 1px solid #ddd;
						background-color: #fff;
						border-radius: 0;
						height: 30px;
						line-height: 30px;
					}
					</style>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="mb_giup_manager"><b>담당자</b></label>
						<div class="col-sm-2">
							<select name="mb_giup_manager" id="mb_giup_manager">
								<?php
								foreach($managers as $manager) { 
								?>
									<option value="<?php echo $manager['mm_no']; ?>"><?php echo $manager['mm_name']; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				<?php } ?>


				<style>
					#typereceipt2_view {
						display:none;
					}
					#typereceipt1_view {
						display:none;
					}
					#typereceipt2_view {

					}
					#typereceipt1_view ul,
					#typereceipt2_view ul {
						margin:0;
						padding:0;
					}
					#typereceipt1_view ul li,
					#typereceipt2_view ul li {
						margin:0;
						list-style:none;
						padding:0;
					}
					#typereceipt1_view input[readonly], 
					#typereceipt1_view input[readonly="readonly"] {
						color:#909090;
					}
				</style>

				<div class="form-group typereceipt-form">
					<label class="col-sm-2 control-label"><b>매출증빙</b></label>
					<div class="col-sm-10 radio-line">
						<input type="radio" name="ot_typereceipt" id="typereceipt0" value="0" checked="checked"> <label for="typereceipt0">발급안함</label>
						<input type="radio" name="ot_typereceipt" id="typereceipt2" value="31"> <label for="typereceipt2">현금영수증 </label>
						<input type="radio" name="ot_typereceipt" id="typereceipt1" value="11"> <label for="typereceipt1" id="typereceipt1_label">세금계산서 </label>
						<div id="typereceipt2_view">
							<ul id="cash_container" class="typereceiptlay">
								<li>
									<input type="radio" name="typereceipt_cuse" class="typereceipt_cuse" id="cuse0" value="1" checked="checked"> <label for="cuse0">개인 소득공제</label>
									<input type="radio" name="typereceipt_cuse" class="typereceipt_cuse" id="cuse1" value="2"> <label for="cuse1">사업자 지출증빙</label>
								</li>
								<li class="personallay">
									<input type="text" name="p_typereceipt_btel" class="line number basic_input" maxlength="13" title="휴대폰번호('-' 없이 입력)" placeholder="휴대폰번호('-' 없이 입력)">
								</li>
								<li class="businesslay" style="display:none;">
									<input type="text" name="p_typereceipt_bnum" class="line number basic_input" maxlength="12" title="사업자번호('-' 없이 입력)" placeholder="사업자번호('-' 없이 입력)">
								</li>
								<li>
									<input type="text" name="p_typereceipt_email" class="line basic_input" title="이메일주소" placeholder="이메일주소">
								</li>
							</ul>
						</div>
						<div id="typereceipt1_view">
						<ul id="tax_container" class="typereceiptlay">
						<table>
							<tbody>
								<tr>
									<th scope="row">
										<label for="typereceipt_bname">기업명</label>
									</th>
									<td colspan="3">
										<input type="text" name="typereceipt_bname" value="<?php echo $member['mb_giup_bname'] ?>" id="typereceipt_bname" class="frm_input" size="30" maxlength="20">
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="typereceipt_boss_name">대표자명</label>
									</th>
									<td colspan="3">
										<input type="text" name="typereceipt_boss_name" value="<?php echo $member['mb_giup_boss_name'] ?>" id="typereceipt_boss_name" class="frm_input" size="30" maxlength="20">
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="typereceipt_btel">연락처</label>
									</th>
									<td colspan="3">
										<input type="text" name="typereceipt_btel" value="<?php echo $member['mb_giup_btel'] ?>" id="typereceipt_btel" class="frm_input" size="30" maxlength="20">
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="typereceipt_bnum">사업자번호</label>
									</th>
									<td colspan="3">
										<input type="text" name="typereceipt_bnum" value="<?php echo $member['mb_giup_bnum'] ?>" id="typereceipt_bnum" class="frm_input" size="30" maxlength="12" <?php echo $member['mb_giup_bnum'] ? ' readonly ' : ''; ?>>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="ot_location_zip">사업장소재지</label>
									</th>
									<td colspan="3">
										<label for="ot_location_zip" class="sound_only">우편번호</label>
										<input type="text" name="ot_location_zip" value="<?php echo get_text($member['mb_giup_zip1']).get_text($member['mb_giup_zip2']); ?>" id="ot_location_zip" required class="frm_input required" size="17" readonly>
										<button type="button" class="shbtn" onclick="win_zip('forderform', 'ot_location_zip', 'ot_location_addr1', 'ot_location_addr2', 'ot_location_addr3', 'ot_location_jibeon');">주소 검색</button><br>
										<input type="text" name="ot_location_addr1" value="<?php echo get_text($member['mb_giup_addr1']); ?>" id="ot_location_addr1" required class="frm_input required" size="30" placeholder="기본주소" readonly><br/>
										<input type="text" name="ot_location_addr2" value="<?php echo get_text($member['mb_giup_addr2']); ?>" id="ot_location_addr2" class="frm_input" size="30" placeholder="상세주소"><br/>
										<input type="text" name="ot_location_addr3" value="<?php echo get_text($member['mb_giup_addr3']); ?>" id="ot_location_addr3" class="frm_input" size="30" placeholder="지번주소" readonly style="display:none">
										<input type="hidden" name="ot_location_jibeon" value="<?php echo get_text($member['mb_giup_addr_jibeon']); ?>">
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="typereceipt_buptae">업태</label>
									</th>
									<td>
										<input type="text" name="typereceipt_buptae" value="<?php echo $member['mb_giup_buptae'] ?>" id="typereceipt_buptae" class="frm_input" size="30" maxlength="20">
									</td>
									<th scope="row">
										<label for="typereceipt_bupjong">업종</label>
									</th>
									<td>
										<input type="text" name="typereceipt_bupjong" value="<?php echo $member['mb_giup_bupjong'] ?>" id="typereceipt_bupjong" class="frm_input" size="30" maxlength="20">
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="typereceipt_tax_email">이메일</label>
									</th>
									<td colspan="3">
										<input type="text" name="typereceipt_email" value="<?php echo $member['mb_giup_tax_email'] ?>" id="typereceipt_email" class="frm_input" size="30" maxlength="20">
									</td>
								</tr>
								<?php
								$sql = "SELECT * FROM g5_member_giup_manager WHERE mb_id = '{$member['mb_id']}'";
								$result = sql_query($sql);
								$managers = array();
								while( $m_row = sql_fetch_array($result) ) {
									$managers[] = $m_row;
								}
								if (!count($managers)) {
									array_push($managers, array());
								}
								?>
								<tr>
									<th scope="row">
										<label for="typereceipt_manager_name">담당자명</label>
									</th>
									<td colspan="3">
										<input type="text" name="typereceipt_manager_name" value="<?php echo $managers[0]['mm_name'] ?>" id="typereceipt_manager_name" class="frm_input" size="30" maxlength="20">
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<script>
				$(function() {
					function no_login() {
						<?php if ( !$member['mb_id'] ) { ?>
						//$('.typereceipt-form').hide();
						//$('#typereceipt1_view').hide();
						//$('#typereceipt2_view').hide();
						$('#typereceipt1').hide();
						$('#typereceipt1_label').hide();
						<?php } ?>
					}

					$('#typereceipt2').click(function() {
						if ( $(this).is(':checked') ) {
							$('#typereceipt2_view').show();
							$('#typereceipt1_view').hide();
						}
						no_login();
					});
					$('#typereceipt1').click(function() {
						if ( $(this).is(':checked') ) {
							$('#typereceipt1_view').show();
							$('#typereceipt2_view').hide();
						}
						no_login();
					});
					$('#typereceipt0').click(function() {
						if ( $(this).is(':checked') ) {
							$('#typereceipt1_view').hide();
							$('#typereceipt2_view').hide();
						}
						// no_login();
					});

					$('.typereceipt_cuse').click(function() {
						var val = $(this).val();

						if ( val == 1 ) {
							$('.personallay').show();
							$('.businesslay').hide();
						}else{
							$('.personallay').hide();
							$('.businesslay').show();
						}
						no_login();
					});

					$('input[name="od_settle_case"]').click(function() {
						var val = $(this).val();

						if (val === '신용카드') {
							$('#typereceipt0').click();
							$('.typereceipt-form').hide();
							$('#typereceipt1_view').hide();
							$('#typereceipt2_view').hide();
						}else{
							$('.typereceipt-form').show();
						}
						no_login();
					});

					// 사용자 화면에서 비회원일때와 회원인경우 신용카드를 선택한 경우 매출증빙을 선택 못하도록 안보이도록 설정
					no_login();
					$('#typereceipt0').click();

					$('input[name="typereceipt_bnum"], input[name="p_typereceipt_bnum"]').on('keyup', function(){
						var num = $(this).val();
						num.trim();
						this.value = auto_saup_hypen(num) ;
					});
				});
				</script>

			<?php } ?>
		</div>
	</div>
</section>
