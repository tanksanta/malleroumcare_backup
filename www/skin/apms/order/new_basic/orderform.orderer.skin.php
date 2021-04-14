<?php
if(!$member["mb_id"]){alert('로그인을 해주세요',G5_BBS_URL.'/login.php?url=%2F');};
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//수급자 정보 필드추가
sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
					ADD `od_penId` varchar(20) NOT NULL DEFAULT '' AFTER `od_addr_jibeon`,
                    ADD `od_penNm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penId`,
                    ADD `od_penTypeNm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penNm`,
                    ADD `od_penExpiDtm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penTypeNm`,
                    ADD `od_penAppEdDtm` varchar(20) NOT NULL DEFAULT '' AFTER `od_penExpiDtm`,
                    ADD `od_penConPnum` varchar(20) NOT NULL DEFAULT '' AFTER `od_penAppEdDtm`,
                    ADD `od_penConNum` varchar(20) NOT NULL DEFAULT '' AFTER `od_penConPnum`,
					ADD `od_penzip1` char(3) NOT NULL DEFAULT '' AFTER `od_penConNum`,
                    ADD `od_penzip2` char(3) NOT NULL DEFAULT '' AFTER `od_penzip1`
                    ADD `od_penAddr` varchar(100) NOT NULL DEFAULT '' AFTER `od_penzip2` ", false);

	# 210223 수급자여부
	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["pageNum"] = 1;
	$sendData["pageSize"] = 1;
	$sendData["appCd"] = "01";

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/recipient/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$recipientTotalCnt = $res["total"];


//쇼핑몰에서 설정한 일정한 금액 이상이 넘을경우 배송비 무료
$sql_d = "SELECT `de_send_conditional` FROM `g5_shop_default`";
$result_d = sql_fetch($sql_d);
if($tot_sell_price >=$result_d['de_send_conditional']){
    $send_cost=0;
}
$tot_price=$tot_sell_price+$send_cost-$tot_sell_discount;
?>


<!-- // 20200306성훈추가 재고바코드 배열빼기 -->
<script>
var renew_num="";
var renew_array=[];
var renew_array2=[];
var array_box=[];
</script>
<!-- // 20200306성훈추가 재고바코드 배열빼기 -->

<script>
    $(function() {
        $('#od_tel').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
        $('#od_hp').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
        $('#od_b_tel').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
        $('#od_b_hp').on('keyup', function(){
            var num = $(this).val();
            num.trim();
            this.value = auto_phone_hypen(num) ;
        });
    });
</script>


	<section class="tab-wrap tab-2 on">
		<div class="detail-price pc_none tablet_block">
			<h5 class="icon-tti order_recipientInfoBox" style="display: none;">
				수급자 정보
				<a href="#" class="order_recipient"><img src="<?=$SKIN_URL?>/image/icon_23.png" alt=""> 내 수급자 조회</a>
			</h5>
			<div class="all-info all-info2 order_recipientInfoBox" style="display: none;">
				<ul>
					<li>
						<div>
							<b>수급자명</b>
							<span class="penNm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>인정등급</b>
							<span class="penTypeNm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>장기요양번호</b>
							<span class="penLtmNum_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>유효기간</b>
							<span class="penExpiDtm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>적용기간</b>
							<span class="penAppEdDtm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>전화번호</b>
							<span class="penConPnum_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>휴대폰</b>
							<span class="penConNum_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>주소</b>
							<span class="penAddr_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
				</ul>
			</div>
		</div>

		<div class="detail-wrap">
			<h4>상품 정보</h4>
			<div class="info-wrap">
				<div class="table-list2">
					<ul class="head">
						<li class="pro">상품(옵션)</li>
						<li class="num">수량</li>
						<li class="pro-price">단가</li>
						<li class="price">총금액</li>
						<li class="delivery-price" style="width: 20%;">배송비</li>
						<li class="barcode" style="display: none;">바코드</li>
					</ul>

					<?php for($i=0; $i < count($item); $i++) { ?>
						<div class="list item" data-code="<?=$item[$i]["it_id"]?>" data-sup="<?=$item[$i]["prodSupYn"]?>" data-ca="<?=(substr($item[$i]["ca_id"], 0, 2))?>">
							<ul class="cb">
								<li class="pro">
									<div class="img"><img src="/data/item/<?=$item[$i]['thumbnail']?>" onerror="this.src = '/shop/img/no_image.gif';"></div>
									<div class="pro-info">
										<div class="pro-icon">
											<i class="icon01"><?=($item[$i]["prodSupYn"] == "N") ? "비유통" : "유통"?></i>

										<?php if(substr($item[$i]["ca_id"], 0, 2) == 10){ ?>
											<i class="icon03">판매</i>
										<?php } ?>

										<?php if(substr($item[$i]["ca_id"], 0, 2) == 20){ ?>
											<i class="icon02">대여</i>
										<?php } ?>
										</div>
										<div class="name">
											<input type="hidden" name="it_id[<?php echo $i; ?>]"    value="<?php echo $item[$i]['hidden_it_id']; ?>" class="it_id_class">
											<input type="hidden" name="it_name[<?php echo $i; ?>]"  value="<?php echo $item[$i]['hidden_it_name']; ?>">
											<input type="hidden" name="it_price[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_sell_price']; ?>">
											<input type="hidden" name="it_discount[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_sell_discount']; ?>">
											<input type="hidden" name="cp_id[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_cp_id']; ?>">
											<input type="hidden" name="cp_price[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_cp_price']; ?>">
											<input type="hidden" name="ct_price[<?php echo $i; ?>]" value="<?php echo $item[$i]['ct_price']; ?>">
											<input type="hidden" name="it_qty[<?php echo $i; ?>]" value="<?php echo $item[$i]['qty']; ?>">
											<?php for($ii = 0; $ii < count($item[$i]["it_optionList"]); $ii++){ ?>
												<input type="hidden" class="it_option_stock_cnt" name="it_option_stock_cnt_<?=$item[$i]["it_optionList"][$ii]["id"]?>" value="0">
											<?php } ?>
											<?php if($default['de_tax_flag_use']) { ?>
												<input type="hidden" name="it_notax[<?php echo $i; ?>]" value="<?php echo $item[$i]['hidden_it_notax']; ?>">
											<?php } ?>
											<?php echo $item[$i]['it_name']; ?>
										</div>
										<?php if($item[$i]['it_options']) { ?>
											<div class="text"><?php echo $item[$i]['it_options'];?></div>
										<?php } ?>

										<?php
											//소계 토탈 - 디스카운트
											$pirce_v = str_replace(',','',$item[$i]['total_price']);
										?>
										<!--모바일용-->
										<div class="info_pc_none">
											<div>
												<p><?php echo $item[$i]['qty']; ?>개</p>
											</div>
											<div>
												<p><?php echo $item[$i]['ct_price']; ?></p>
											</div>
											<div>
												<p class="price_print"><?php echo number_format($pirce_v) ; ?></p>
											</div>
										</div>
									</div>
								</li>
								<li class="num m_none">
									<p><?php echo $item[$i]['qty']; ?>개</p>
								</li>
								<li class="pro-price m_none">
									<p><?php echo $item[$i]['ct_price']; ?></p>
								</li>
								<li class="price m_none">
									<p class="price_print"><?php echo number_format($pirce_v) ; ?></p>
								</li>
								<li class="delivery-price m_none" style="width: 20%;">
									<p><?php echo $item[$i]['ct_send_cost']; ?></p>
								</li>
								<li class="barcode barList" style="display: none;">
								<?php
									for($ii = 0; $ii < count($item[$i]["it_optionList"]); $ii++){
										for($iii = 0; $iii < $item[$i]["it_optionList"][$ii]["qty"]; $iii++){
								?>
										<?php if($optionCntList[$item[$i]["it_id"]][$ii] > $iii){ ?>
											<input type="hidden" placeholder="바코드" maxlength="12" oninput="maxLengthCheck(this)" class="prodStockBarBox<?=$ii?> prodBarSelectBox prodBarSelectBox<?=$ii?>" style="margin-bottom: 5px;" data-code="<?=$ii?>" data-this-code="<?=$iii?>" data-name="<?=$postProdBarNumCnt?>" name="prodBarNum_<?=$postProdBarNumCnt?>">
										<?php } else { ?>
										<?php
											if(substr($item[$i]["ca_id"], 0, 2) == 20){
												$itemPenIdStatus = false;
											}
										?>
											<input type="hidden" placeholder="바코드" maxlength="12" oninput="maxLengthCheck(this)"class="hidden barcode_input prodStockBarBox<?=$ii?>" value="" style="margin-bottom: 5px;" data-code="<?=$ii?>" data-this-code="<?=$iii?>" data-name="<?=$postProdBarNumCnt?>"  name="prodBarNum_<?=$postProdBarNumCnt?>">
										<?php } ?>
								<?php
										$postProdBarNumCnt++; }
									}
								?>
								</li>
							</ul>
							<div class="list-btm">
								<?php if(substr($item[$i]["ca_id"], 0, 2) == 20){ ?>
								<div class="stock_insert_none order_none" style="display: none;">
									<span class="btm-tti">대여금액(월)</span>
									<span><?=number_format($item[$i]["it_rental_price"])?>원</span>
								</div>
								<div class="stock_insert_none order_none" style="display: none;">
									<span class="btm-tti">대여기간</span>
									<span class="list-day">
										<input type="text" class="ordLendDtmInput ordLendStartDtm dateonly" style="margin-right: 6px;" name="ordLendStartDtm_<?=$item[$i]["ct_id"]?>" data-default="<?=date("Y-m-d")?>" value="<?=date("Y-m-d")?>"> ~ <input type="text" class="ordLendDtmInput ordLendEndDtm dateonly" style="margin-left: 6px;" name="ordLendEndDtm_<?=$item[$i]["ct_id"]?>" data-default="<?=date("Y-m-d", strtotime("+ 364 days"))?>" readonly value="<?=date("Y-m-d", strtotime("+ 364 days"))?>">
										<ul>
											<li><a href="#" class="dateBtn" data-month="6">6개월</a></li>
											<li><a href="#" class="dateBtn" data-month="12">1년</a></li>
											<li><a href="#" class="dateBtn" data-month="24">2년</a></li>
										</ul>
									</span>
								</div>
								<?php } ?>
								<div>
									<span class="btm-tti">요청사항</span>
									<span class="list-textarea">
										<textarea name="prodMemo_<?=$item[$i]["ct_id"]?>" placeholder="추가 구매 사항이나 상품관련 요청사항을 입력하세요." ></textarea>
									</span>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>

			<div class="order-info">
				<div class="top">
					<h5>받으시는 분</h5>
					<?php if($is_member){ ?>
					<div class="add-ac">
						<p>배송지 선택</p>
						<ul>
							<li class="ad_sel_addr" id="ad_sel_addr_same" data-value="same">주문자와 동일</li>
							<li class="ad_sel_addr" id="ad_sel_addr_recipient" data-value="recipient" style="display: none;">수급자와 동일</li>
							<li class="ad_sel_addr" id="od_sel_addr_new" data-value="new">신규 배송지</li>
							<?php
							if ($member['mb_id']) {
								$sql = "SELECT count(*) as cnt from {$g5['g5_shop_order_address_table']} where mb_id = '{$member['mb_id']}' ";
								$result = sql_fetch($sql);
								if ($result['cnt']) { 
							?>
									<li class="ad_sel_addr" id="order_address">배송지 목록</li>
								<?php } ?>
							<?php } ?>
						</ul>
					</div>
					<?php } ?>
				</div>
				<div class="table-list3">
					<ul>
						<li>
							<strong>이름</strong>
							<div>
								<input class="w-240" type="text" id="od_b_name" name="od_b_name" value="<?=$member['mb_name']?>">
							</div>
						</li>
						<li>
							<strong>전화번호</strong>
							<div>
								<input class="w-240" type="text" id="od_b_tel" name="od_b_tel" value="<?=$member['mb_tel']?>">
							</div>
						</li>
						<li>
							<strong>핸드폰</strong>
							<div>
								<input class="w-240" type="text" id="od_b_hp" name="od_b_hp" value="<?=$member['mb_hp']?>">
							</div>
						</li>
						<li class="addr">
							<strong>주소</strong>
							<div>
								<div>
									<input type="text"  class="w-70"name="od_b_zip" id="od_b_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2'] ?>" required readonly>
									<button type="button" onclick="win_zip('forderform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">우편번호</button>
									<input type="hidden" name="od_b_addr_jibeon" value="<?=$member['mb_hp']?>">
								</div>
								<div>
									<input type="text" name="od_b_addr1" id="od_b_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" required readonly style="width: 100%;">
								 </div>
								 <div>
									<input type="text" name="od_b_addr2" id="od_b_addr2" value="<?php echo get_text($member['mb_addr2']).get_text($member['mb_addr_jibeon']) ?>" style="width: 100%;">
								 </div>
							</div>
						</li>
						<li>
							<strong>배송요청사항</strong>
							<input type="text" class="w-all" name="od_memo" id="od_memo">
                        <select name="od_delivery_type" id="od_delivery_type" style="display: none;">
                            <?php
                            foreach($delivery_types as $type) {
                                // if ( $type['user-order'] != true ) continue;
                                if ( !$default['de_delivery_type_' . $type['val']] ) continue;
                            ?>
                                <option value="<?php echo $type['val']; ?>" <?php echo $type['val'] == $od['od_delivery_type'] ? 'selected' : ''; ?> data-type="<?php echo $type['type']; ?>"><?php echo $type['name']; ?></option>
                            <?php } ?>
                        </select>
						</li>
					</ul>
				</div>

				<div class="top">
					<h5>매출증빙</h5>
					<div class="check-ac">
						<span class="check">
							<input type="radio" id="typereceipt1" name="ot_typereceipt" value="11">
							<label for="typereceipt1">
								<span class="check_on"></span>
							</label>
							<b>세금계산서</b>
						</span>
						<span class="check">
							<input type="radio" id="typereceipt2" name="ot_typereceipt" value="31">
							<label for="typereceipt2">
								<span class="check_on"></span>
							</label>
							<b>현금영수증</b>
						</span>
						<span class="check">
							<input type="radio" id="typereceipt0" name="ot_typereceipt" value="0">
							<label for="typereceipt0">
								<span class="check_on"></span>
							</label>
							<b>발급 안함</b>
						</span>
					</div>
				</div>
				<div class="table-list3 table-list4" id="typereceipt1_view">
				<?php
					# 결제정보
					$sendData_entInfo = [];
					$sendData_entInfo["usrId"] = $member["mb_id"];

					$oCurl = curl_init();
					curl_setopt($oCurl, CURLOPT_PORT, 9901);
					curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/ent/account");
					curl_setopt($oCurl, CURLOPT_POST, 1);
					curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData_entInfo, JSON_UNESCAPED_UNICODE));
					curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
					$res = curl_exec($oCurl);
					curl_close($oCurl);
					$entInfo=json_decode($res,true);
				?>
					<ul>
						<li>
							<div class="list-con">
								<strong>기업명</strong>
								<div>
									<input type="text" name="typereceipt_bname" value="<?php echo $entInfo['data']['entNm'] ?>" id="typereceipt_bname" maxlength="20">
								</div>
							</div>
							<div class="list-con">
								<strong>대표자명</strong>
								<div>
									<input type="text" name="typereceipt_boss_name" value="<?php echo $entInfo['data']['entCeoNm'] ?>" id="typereceipt_boss_name" maxlength="20">
								</div>
							</div>
						</li>
						<li>
							<div class="list-con">
								<strong>사업자번호</strong>
								<div>
									<input type="text" name="typereceipt_bnum" value="<?php echo $member['mb_giup_bnum'] ?>" id="typereceipt_bnum" maxlength="12" <?php echo $member['mb_giup_bnum'] ? ' readonly ' : ''; ?>>
								</div>
							</div>
							<div class="list-con list-tel">
								<strong>연락처</strong>
								<div>
									<input type="text" name="typereceipt_btel" value="<?php echo $entInfo['data']['entPnum'] ?>" id="typereceipt_btel" maxlength="20" style="margin-left: 0;">
								</div>
							</div>
						</li>
						<li class="addr">
							<strong>주소</strong>
							<div>
								<div>
									<input type="text"  class="w-70"name="ot_location_zip" value="<?php echo get_text($member['mb_giup_zip1']).get_text($member['mb_giup_zip2']); ?>" id="ot_location_zip" required readonly>
									<button type="button" onclick="win_zip('forderform', 'ot_location_zip', 'ot_location_addr1', 'ot_location_addr2', 'ot_location_addr3', 'ot_location_jibeon');">우편번호</button>
									<input type="hidden" name="ot_location_jibeon" value="">
								</div>
								<div>
									<input type="text" name="ot_location_addr1" value="<?php echo get_text($member['mb_giup_addr1']); ?>" id="ot_location_addr1" required readonly>
								 </div>
								 <div>
									<input type="text" name="ot_location_addr2" value="<?php echo get_text($member['mb_giup_addr2']); ?>" id="ot_location_addr2">
								 </div>
							</div>
						</li>
						<li>
							<div class="list-con">
								<strong>업태</strong>
								<div>
									<input type="text" name="typereceipt_buptae" value="<?php echo $entInfo['data']['entBusiCondition'] ?>" id="typereceipt_buptae" maxlength="20">
								</div>
							</div>
							<div class="list-con">
								<strong>업종</strong>
								<div>
									<input type="text" name="typereceipt_bupjong" value="<?php echo $entInfo['data']['entBusiType'] ?>" id="typereceipt_bupjong" maxlength="20">
								</div>
							</div>
						</li>
						<li class="em">
							<div class="list-con">
								<strong>이메일</strong>
								<div>
									<input type="text" name="typereceipt_email" value="<?php echo $entInfo['data']['entMail'] ?>" id="typereceipt_email" maxlength="20">
								</div>
							</div>
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
							<div class="list-con">
								<strong>담당자명</strong>
								<div>
									<input type="text" name="typereceipt_manager_name" value="<?php echo $entInfo['data']['entTaxCharger'] ?>" id="typereceipt_manager_name" maxlength="20">
								</div>
							</div>
						</li>

					</ul>
				</div>

				<div class="table-list3 table-list4" id="typereceipt2_view">
					<ul>
						<li>
							<input type="radio" name="typereceipt_cuse" class="typereceipt_cuse" id="cuse0" value="1" checked> <label for="cuse0">개인 소득공제</label>
							<input type="radio" name="typereceipt_cuse" class="typereceipt_cuse" id="cuse1" value="2"> <label for="cuse1">사업자 지출증빙</label>
						</li>
						<li>
							<div class="list-con personallay">
								<strong>휴대폰번호</strong>
								<div>
									<input type="text" name="p_typereceipt_btel" class="number" maxlength="13" title="휴대폰번호('-' 없이 입력)" placeholder="휴대폰번호('-' 없이 입력)">
								</div>
							</div>
							<div class="list-con businesslay" style="display: none;">
								<strong>휴대폰번호</strong>
								<div>
									<input type="text" name="p_typereceipt_bnum" class="number" maxlength="12" title="사업자번호('-' 없이 입력)" placeholder="사업자번호('-' 없이 입력)">
								</div>
							</div>
							<div class="list-con list-tel">
								<strong>이메일</strong>
								<div>
									<input type="text" name="p_typereceipt_email" title="이메일주소">
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="detail-price">
			<h5 class="icon-tti order_recipientInfoBox m_none tablet_none" style="display: none;">
				수급자 정보
				<a href="#" class="order_recipient"><img src="<?=$SKIN_URL?>/image/icon_23.png" alt=""> 내 수급자 조회</a>
			</h5>
			<div class="all-info all-info2 order_recipientInfoBox m_none tablet_none" style="display: none;">
				<ul>
					<li>
						<div>
							<b>수급자명</b>
							<span class="penNm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>인정등급</b>
							<span class="penTypeNm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>장기요양번호</b>
							<span class="penLtmNum_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>유효기간</b>
							<span class="penExpiDtm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>적용기간</b>
							<span class="penAppEdDtm_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>전화번호</b>
							<span class="penConPnum_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>휴대폰</b>
							<span class="penConNum_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
					<li>
						<div>
							<b>주소</b>
							<span class="penAddr_print" style="color: #CCC;">수급자를 선택해주세요.</span>
						</div>
					</li>
				</ul>
			</div>
			<h5>결제정보</h5>
			<div class="all-info">
				<ul>
					<li>
						<div>
							<b>주문금액</b>
							<span id="printTotalCellPrice"><?php echo number_format($tot_sell_price); ?>원</span>
						</div>
					</li>
					<li>
						<div class="stock_insert_none">
							<b>할인금액
								<i class="coupon-icon" id="od_coupon_btn">쿠폰</i>
							</b>
							<span><span id="od_cp_price"><?php echo number_format($tot_sell_discount); ?></span>원</span>
						</div>
<!--
						<div class="coupon-on">
							<b>쿠폰적용</b>
							<span>2000원</span>
						</div>
-->
					</li>
					<li>
						<div>
							<b>배송비</b>
							<span class="delivery_cost_display"><?php echo number_format($send_cost); ?>원</span>
						</div>
					</li>
				</ul>
				<div class="all-info-price od_tot_price">
					<b>합계금액</b>
					<span id="ct_tot_price" class="print_price"><?php echo number_format($tot_price); ?> 원</span>
				</div>
			</div>
			<h5 class="stock_insert_none">결제방법</h5>
			<div class="payment-tab stock_insert_none">
				<ul>
					<li class="on">
						<a href="#" data-for="od_settle_pay_end">
							<img src="<?=$SKIN_URL?>/image/icon_21.png" alt="">
							월 마감정산
						</a>
					</li>
					<li>
						<a href="#" data-for="od_settle_bank">
							<img src="<?=$SKIN_URL?>/image/icon_22.png" alt="">
							무통장입금
						</a>
					</li>
				</ul>
			</div>

			<div id="settle_bank" style="display: none;">
				<h5>입금할 계좌</h5>
				<select name="od_bank_account" id="od_bank_account" style="width: 100%; height: 30px; border: 1px solid #DDD; padding: 5px; margin-top: -25px;">
					<option value="">선택하십시오.</option>
					<?php
                          if ($default['de_bank_use']) {
                            // 은행계좌를 배열로 만든후
                            $str = explode("\n", trim($default['de_bank_account']));
                            if (count($str) <= 1)
                            {
                                $bank_account = '<option value="'.$str[0].'">'.$str[0].PHP_EOL.'</option> ';
                            }
                            else
                            {
                                $bank_account .= '<option value="">선택하십시오.</option>';
                                for ($i=0; $i<count($str); $i++)
                                {
                                    //$str[$i] = str_replace("\r", "", $str[$i]);
                                    $str[$i] = trim($str[$i]);
                                    $bank_account .= '<option value="'.$str[$i].'">'.$str[$i].'</option>'.PHP_EOL;
                                }
                            }
                        }
                        ?>
					<?php echo $bank_account; ?>
				</select>

				<h5>입금자명</h5>
				<input type="text" name="od_deposit_name" id="od_deposit_name" maxlength="20" style="width: 100%; height: 30px; border: 1px solid #DDD; padding: 5px; margin-top: -25px; margin-bottom: 40px;">
			</div>

			<div class="text" style="display: none;">
				<span>- 보유재고 등록 선택 시 상품배송이 되지 않습니다. </span>
				<span>- 보유 재고 등록시 바코드 정보를 모두 입력해야 등록이 가능합니다. </span>
			</div>

			<div class="pay-btn">
				<button type="button" id="forderform_check_btn" onclick="forderform_check(this.form);">상품 주문하기</button>
				<a href="javascript:history.go(-1);">취소</a>
			</div>
		</div>

	</section>



<?php if(!$is_orderform) { //주문서가 필요없는 주문일 때 ?>
    <section id="sod_frm_orderer" style="margin-bottom:0px;">
        <div class="panel panel-default">
            <div class="panel-heading"><strong> 결제하시는 분</strong></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label"><b>아이디</b></label>
                    <label class="col-sm-3 control-label" style="text-align:left;">
                        <b><?php echo $member['mb_id'];?></b>
                    </label>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>이름</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_name" value="<?php echo get_text($member['mb_name']); ?>" id="od_name" required class="form-control input-sm" maxlength="20">
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_tel"><b>연락처</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_tel" value="<?php echo ($member['mb_hp']) ? get_text($member['mb_hp']) : get_text($member['mb_tel']); ?>" id="od_tel" required class="form-control input-sm" maxlength="13">
                        <span class="fa fa-phone form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="od_memo"><b>메모</b></label>
                    <div class="col-sm-8">
                        <textarea name="od_memo" rows=3 id="od_memo" class="form-control input-sm"></textarea>
                    </div>
                </div>
                <input type="hidden" name="od_email" value="<?php echo $member['mb_email']; ?>">
                <input type="hidden" name="od_hp" value="<?php echo get_text($member['mb_hp']); ?>">
                <input type="hidden" name="od_b_name" value="<?php echo get_text($member['mb_name']); ?>">
                <input type="hidden" name="od_b_tel" value="<?php echo get_text($member['mb_tel']); ?>">
                <input type="hidden" name="od_b_hp" value="<?php echo get_text($member['mb_hp']); ?>">
                <input type="hidden" name="od_b_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2']; ?>">
                <input type="hidden" name="od_b_addr1" value="<?php echo get_text($member['mb_addr1']); ?>">
                <input type="hidden" name="od_b_addr2" value="<?php echo get_text($member['mb_addr2']); ?>">
                <input type="hidden" name="od_b_addr3" value="<?php echo get_text($member['mb_addr3']); ?>">
                <input type="hidden" name="od_b_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']); ?>">

            </div>
        </div>
    </section>

<?php } else { ?>



    <!-- 비회원주문 -->
    <?php if($is_guest_order) { // 비회원 주문일 때 ?>
    <!-- 주문하시는 분 입력 시작 { -->
    <section id="sod_frm_agree" style="margin-bottom:0px;">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong> 개인정보처리방침안내</strong>
            </div>
            <div class="panel-body">
                비회원으로 주문하시는 경우 포인트는 지급하지 않습니다.
            </div>
            <table class="table">
                <colgroup>
                    <col width="30%">
                    <col width="30%">
                </colgroup>
                <thead>
                <tr>
                    <th>목적</th>
                    <th>항목</th>
                    <th>보유기간</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>이용자 식별 및 본인 확인</td>
                    <td>이름, 비밀번호</td>
                    <td>5년(전자상거래등에서의 소비자보호에 관한 법률)</td>
                </tr>
                <tr>
                    <td>배송 및 CS대응을 위한 이용자 식별</td>
                    <td>주소, 연락처(이메일, 휴대전화번호)</td>
                    <td>5년(전자상거래등에서의 소비자보호에 관한 법률)</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="row row-15">
            <div class="consent_wrap">
                <p>*비회원으로 주문시 경우 개인정보 동의 필수</p>
                <div class="btn_consent">
                    <div data-toggle="buttons">
                        <label class="btn btn-green btn-sm btn-block">
                            <input type="checkbox" name="agree" value="1" id="agree" autocomplete="off">
                            <i class="fa fa-check"></i>
                            개인정보처리방침안내에 동의합니다.
                        </label>
                    </div>
                    <div class="h10"></div>
                </div>
            </div>
            <div class="login_wrap">
                <p>*로그인 후 주문 시 회원 혜택 제공</p>
                <div class="btn_login">
                    <a href="<?php echo $order_login_url;?>" class="btn btn-lightgray btn-sm btn-block">
                        <i class="fa fa-sign-in"></i>
                        로그인/회원가입
                    </a>
                    <div class="h10"></div>
                </div>
            </div>
        </div>
        <div class="h10"></div>
    </section>
    <?php } ?>
    <!-- 비회원주문 -->





    <!-- 주문하시는 분 입력 시작 { -->
    <section id="sod_frm_orderer" style="margin-bottom:0px; display: none;">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>  주문하시는 분</strong></div>
            <div class="panel-body">
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_name"><b>이름</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_name" value="<?php echo get_text($member['mb_name']); ?>" id="od_name" required class="form-control input-sm" maxlength="20">
                        <span class="fa fa-check form-control-feedback"></span>
                    </div>
                </div>
                <?php if (!$is_member) { // 비회원이면 ?>
                    <div class="form-group has-feedback">
                        <label class="col-sm-2 control-label" for="od_pwd"><b>비밀번호</b><strong class="sound_only">필수</strong></label>
                        <div class="col-sm-3">
                            <input type="password" name="od_pwd" id="od_pwd" required class="form-control input-sm" maxlength="20">
                            <span class="fa fa-lock form-control-feedback"></span>
                        </div>
                        <div class="col-sm-7">
                            <span class="help-block">영,숫자 3~20자 (주문서 조회시 필요)</span>
                        </div>
                    </div>
                <?php } ?>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_tel"><b>전화번호</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_tel" value="<?php echo get_text($member['mb_tel']); ?>" id="od_tel" required class="form-control input-sm" maxlength="13">
                        <span class="fa fa-phone form-control-feedback"></span>
                    </div>
                </div>
                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_hp"><b>핸드폰</b></label>
                    <div class="col-sm-3">
                        <input type="text" name="od_hp" value="<?php echo get_text($member['mb_hp']); ?>" id="od_hp" class="form-control input-sm" maxlength="13">
                        <span class="fa fa-mobile form-control-feedback"></span>
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label"><b>주소</b><strong class="sound_only">필수</strong></label>
                    <div class="col-sm-8">
                        <label for="od_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                        <label>
                            <input type="text" name="od_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2'] ?>" id="od_zip" required class="form-control input-sm" size="6" maxlength="6">
                        </label>
                        <label>
                            <button type="button" class="btn btn-black btn-sm" style="margin-top:0px;" onclick="win_zip('forderform', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon');">주소 검색</button>
                        </label>

                        <div class="addr-line">
                            <label class="sound_only" for="od_addr1">기본주소<strong class="sound_only"> 필수</strong></label>
                            <input type="text" name="od_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" id="od_addr1" required class="form-control input-sm" size="60" placeholder="기본주소">
                        </div>

                        <div class="addr-line">
                            <label class="sound_only" for="od_addr2">상세주소</label>
                            <input type="text" name="od_addr2" value="<?php echo get_text($member['mb_addr2']) ?>" id="od_addr2" class="form-control input-sm" size="50" placeholder="상세주소">
                        </div>

                        <label class="sound_only" for="od_addr3">참고항목</label>
                        <input type="text" name="od_addr3" value="<?php echo get_text($member['mb_addr3']) ?>" id="od_addr3" class="form-control input-sm" size="50" readonly="readonly" placeholder="참고항목">
                        <input type="hidden" name="od_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']) ?>">
                    </div>
                </div>


                <div class="form-group has-feedback">
                    <label class="col-sm-2 control-label" for="od_email"><b>E-mail</b><strong class="sound_only"> 필수</strong></label>
                    <div class="col-sm-5">
                        <input type="text" name="od_email" value="<?php echo $member['mb_email']; ?>" id="od_email" required class="form-control input-sm email" size="35" maxlength="100">
                        <span class="fa fa-envelope form-control-feedback"></span>
                    </div>
                </div>


                <?php if ($default['de_hope_date_use']) { // 배송희망일 사용 ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="od_hope_date"><b>희망배송일</b></label>
                        <!--
                            <div class="col-sm-3">
                                <select name="od_hope_date" id="od_hope_date" class="form-control input-sm>
                                    <option value="">선택하십시오.</option>
                                    <?php
                                        for ($i=0; $i<7; $i++) {
                                            $sdate = date("Y-m-d", time()+86400*($default['de_hope_date_after']+$i));
                                            echo '<option value="'.$sdate.'">'.$sdate.' ('.get_yoil($sdate).')</option>'.PHP_EOL;
                                        }
                                    ?>
                                </select>
                            </div>
                        -->
                        <div class="col-sm-7">
                            <span class="form-inline">
                                <input type="text" name="od_hope_date" value="" id="od_hope_date" required class="form-control input-sm" size="11" maxlength="10" readonly="readonly">
                            </span>
                            이후로 배송 바랍니다.
                        </div>
                    </div>
                <?php }  ?>


            </div>
        </div>
    </section>
    <!-- } 주문하시는 분 입력 끝 -->


    <!-- 수급자 정보 iframe창 -->
	<?php if($itemPenIdStatus){ ?>
	<div id="order_recipientBox">
		<div>
			<iframe src="<?php echo G5_SHOP_URL;?>/pop_recipient.php"></iframe>
		</div>
	</div>
	<div id="order_submitCheckBox">
		<div>
			<div>
				<div class="title">기타 협약사항1</div>
				<textarea class="form-control input-sm" name="entConAcc01" id="entConAcc01"><?=$member["mb_entConAcc01"]?></textarea>

				<div class="title">기타 협약사항2</div>
				<textarea class="form-control input-sm" name="entConAcc02" id="entConAcc02"><?=$member["mb_entConAcc02"]?></textarea>

				<button type="button" onclick="forderform_check(this.form);">수급자 주문하기</button>
				<button type="button" onclick="order_submitCheckBox_hide();">취소</button>
			</div>
		</div>
	</div>
	<?php } ?>
    <style>
		#order_recipientBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
		#order_recipientBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
		#order_recipientBox iframe { position: relative; width: 700px; height: 500px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; }

		#order_submitCheckBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
		#order_submitCheckBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
		#order_submitCheckBox > div > div { position: relative; width: 700px; height: 500px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; padding: 30px; text-align: center; overflow: auto; }
		#order_submitCheckBox > div > div > .title { width: 100%; float: left; font-size: 16px; font-weight: bold; margin-bottom: 5px; text-align: left; }
		#order_submitCheckBox > div > div > textarea { height: 100px; margin-bottom: 20px; resize: vertical; }
		#order_submitCheckBox > div > div > button { width: 150px; height: 45px; background-color: #333; border: 0; color: #FFF; border-radius: 0; font-size: 18px; font-weight: bold; }

		#order_recipient { background-color: #333 !important; color: #FFF !important; }
		#recipient_del { background-color: #DC3333 !important; color: #FFF !important; }

		.panel .top_area{position:relative;}
		/*.panel .top_area p:first-child{font-weight:bold;color:#ed9947;}*/
		.panel .top_area p:nth-child(2){font-size:12px;color:#999;margin:0;}
		.panel .top_area a{position:absolute;top:5px; right:0px;border:1px solid #ddd;padding: 10px 15px;display:inline-block;text-align:center;}
		.panel .top_area a:hover{background: #f5f5f5;color:#333;}

		#sod_frm_stock_status { margin-bottom: 50px; }
		#sod_frm_stock_status input[type="checkbox"] { vertical-align: middle; margin-right: 5px; margin-top: 0; top: -2px; position: relative; }
		#sod_frm_stock_status label { cursor: pointer; }

		@media (max-width : 750px){
			#order_recipientBox iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
			#order_submitCheckBox > div > div { width: 100%; height: 100%; left: 0; margin-left: 0; }
			#order_recipient { height: 30px; line-height: 28px; font-size: 12px; padding: 0 10px; border: 1px solid #999 !important; background-color: #999 !important; top: 0; right: 0; }
			#recipient_del { height: 30px; line-height: 28px; font-size: 12px; padding: 0 10px; border: 1px solid #DC3333 !important; background-color: rgba(0, 0, 0, 0) !important; top: 0; right: 0; color: #DC3333 !important; margin-right: 100px !important; }
		}
	</style>
    <!-- 수급자 정보 iframe창 -->

	<script>

		// 수급자 선택 함수-
		function selected_recipient($penId) {

			<?php $re = sql_fetch(" select * from {$g5['recipient_table']} where penId = '$penId' ");  ?>
			// document.getElementById("penNm").value=$re['penNm'];
			// document.getElementById("penExpiDtm").value=$re['penExpiDtm'];
			// document.getElementById("penAppEdDtm").value=$re['penAppEdDtm'];
			// document.getElementById("penConNum").value=$re['penConNum'];
			// document.getElementById("penAddr").value=$re['penAddr'];
			// document.getElementById("penTypeNm").value=$re['penTypeNm'];
			// document.getElementById("penMoney").value=$re['penMoney'];

            // 수급자 정보 iframe 에서 넘긴값 받기
			var recipient = $penId.split("|");
			var list = {
				"rn":recipient[0],
				"penId":recipient[1],			//PENID_19210105000002
				"entId":recipient[2],			//ENT2020070900001
				"penNm":recipient[3],			//심재성
				"penLtmNum":recipient[4],		//L2147483647
				"penRecGraCd":recipient[5],		//01
				"penRecGraNm":recipient[6],		//1등급
				"penTypeCd":recipient[7],		//00
				"penTypeNm":recipient[8],		//일반 15%
				"penExpiStDtm":recipient[9],	//2020-01-01
				"penExpiEdDtm":recipient[10],	//2020-12-31
				"penExpiDtm":recipient[11],		//2020-01-01 ~ 2020-12-31
				"penExpiRemDay":recipient[12],	//375
				"penGender":recipient[13],		//00
				"penGenderNm":recipient[14],	//남
				"penBirth":recipient[15],		//19800101
				"penAge":recipient[16],			//41
				"penAppEdDtm":recipient[17],	//2020-12-31
				"penAddr":recipient[18],		//부산 금정구 부산대학로 63번길 2
				"penAddrDtl":recipient[19],		//10
				"penConNum":recipient[20],		//010-2631-3284
				"penConPnum":recipient[21],		//051-780-8157
				"penProNm":recipient[22],
				"usrId":recipient[23],			//1000203000
				"appCd":recipient[24],			//01
				"appCdNm":recipient[25],		//등록완료
				"caCenYn":recipient[26],		//N
				"regDtm":recipient[27],			//1609811513000
				"regDt":recipient[28],			//2021.01.05
				"ordLendEndDtm":recipient[29],
				"ordLendRemDay":recipient[30],
				"usrNm":recipient[31],			//홍길동
				"penAppRemDay":recipient[32],	//-10
				"penMoney":recipient[33]		//800,000원
			};

            //수급자 정보 컨트롤
			$('#Yrecipient').removeClass('none');
			$('#Yrecipient').addClass('block');

			$('#Nrecipient').removeClass('block');
			$('#Nrecipient').addClass('none');

            //수급자 정보 동기화
			document.getElementById("penId").value=list['penId'];				//penId
			document.getElementById("penNm").value=list['penNm'];				//수급자명
			document.getElementById("penTypeNm").value=list['penTypeNm'];		//인정등급
			document.getElementById("penExpiDtm").value=list['penExpiDtm'];		//유효기간
			document.getElementById("penAppEdDtm").value=list['penAppEdDtm'];	//적용기간
			document.getElementById("penConNum").value=list['penConNum'];		//휴대전화
			document.getElementById("penConPnum").value=list['penConPnum'];		//전화번호
			document.getElementById("penAddr").value=list['penAddr'];			//주소
			document.getElementById("penTypeCd").value=list['penTypeCd'];			//주소
			///*document.getElementById("penMoney").value=list['penMoney'];			//한도금액*/

			$(".penNm_print").text(list['penNm']);				//수급자명
			$(".penTypeNm_print").text(list['penTypeNm']);				//수급자명
			$(".penLtmNum_print").text(list['penLtmNum']);				//장기요양번호
			$(".penExpiDtm_print").text(list['penExpiDtm']);				//유효기간
			$(".penAppEdDtm_print").text(list['penAppEdDtm']);				//적용기간
			$(".penConNum_print").text(list['penConNum']);				//휴대전화
			$(".penConPnum_print").text(list['penConPnum']);				//전화번호
			$(".penAddr_print").text(list['penAddr']);				//주소
			$(".tab-2 .detail-price .all-info.all-info2 ul li span").css("color", "");

			document.getElementById("od_coupon_btn").style.display="none";			//한도금액*/
            $("#od_cp_price").text(0);


			var optionCntList = <?=json_encode($optionCntList)?>;
			var optionBarList = <?=json_encode($optionBarList)?>;
			var prodItemList = $(".table-list2 .list.item");

			$('.open_input_barcode').remove();

			$.each(prodItemList, function(key, itemDom){
				var code = $(itemDom).attr("data-code");
				var itemList = $(itemDom).find(".pro > .pro-info > .text li");
				var discountCnt = 0;
				var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
				var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));
				var change_discount = 0;
				$(itemDom).find(".barList").find("input").attr("type", "text");

				$.each(itemList, function(subKey, subDom){
//					if($(itemDom).attr("data-sup") == "Y"){
						var dataBarCnt = Number($(subDom).attr("data-bar-cnt"));
						var dataStockCnt = Number($(subDom).attr("data-stock-cnt"));
						var optionCnt = (dataStockCnt <= dataBarCnt) ? dataStockCnt : dataBarCnt;
						var html = "";
						var display = $(itemDom).attr("data-ca");
						display = (display == "20") ? "none" : "block";

						for(var i = 0; i < optionCnt; i++){
							html += "<option value='" + (i + 1) + "'>" + (i + 1) + "개</option>";
						}

						optionCnt = (optionCnt) ? optionCnt : 0;

						$(subDom).closest(".item").find(".recipientBox").remove();
						$(subDom).css("position", "relative");
						if(html){
							$(subDom).closest(".item").find(".list-btm").prepend("<div id='renew_num_v' class='check-ac recipientBox' style='display: " + display + ";' data-code='" + subKey + "'><label><input type='radio' name='" + code + "Sup" + subKey + "' data-type='new'> 신규주문</label><label><input type='radio' name='" + code + "Sup" + subKey + "' data-type='use' checked> 재고소진 </label> <select>" + html + "</select></div>");
						} else {
							$(subDom).closest(".item").find(".list-btm").prepend("<div id='renew_num_v'class='check-ac recipientBox' style='display: none;' data-code='" + subKey + "'><input type='radio' name='" + code + "Sup" + subKey + "' data-type='new' checked> 신규주문</label></div>");
						}

						$(subDom).closest(".item").find(".list-btm").find(".recipientBox select").val(optionCnt);

						var item = $(itemDom).find(".prodBarSelectBox" + subKey);

						//20210306성훈추가 - 바코드허용개수
						renew_num = $(itemDom).find(".prodBarSelectBox" + subKey).length;

						for(var i = 0; i < item.length; i++){
							var name = $(item[i]).attr("name");
							var dataCode = $(item[i]).attr("data-code");
							var dataThisCode = $(item[i]).attr("data-this-code");
							var dataName = $(item[i]).attr("data-name");
							//20210306 성훈수정(아래줄 id 추가)
                            var html = '<select id="prodBarSelectBox_renew'+i+'" class="prodBarSelectBox prodBarSelectBox' + subKey + '" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '"><option value="">재고 바코드</option>';
							optionBarList[code][subKey].sort();
							$.each(optionBarList[code][subKey], function(key, value){
								html += '<option value="' + value + '">' + value + '</option>';
							});
							html += '</select>';

							$(item[i]).after(html);

							$(item[i]).remove();
						}

                        
						//20210306 성훈 추가
						renew_array = optionBarList[code][subKey];
						renew_array2 = optionBarList[code][subKey];


						discountCnt += optionCnt;

						var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
						var stockCntItemCnt = Number($(subDom).closest(".item").find(".list-btm").find(".recipientBox select").val());
						stockCntItemCnt = (stockCntItemCnt) ? stockCntItemCnt : 0;
						$(stockCntItem[subKey]).val(stockCntItemCnt);

						//할인율 계산
						sendData_discount=[];
                        //it_id, 주문수량 - 재고수량
                        sendData_discount = {
                            "it_id" : $(itemDom).data('code'),
                            "ct_sale_qty" : dataBarCnt-dataStockCnt
                        };
                        $.ajax({
                            url : "./ajax.change_discount.php",
                            type : "POST",
                            async : false,
                            data : sendData_discount,
                            success : function(result){
                                change_discount=change_discount+parseInt(result);
                            }
                        });
//					}
				});

				//전체개수 - 재고 코드개수 : 가격넣기
				$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
				$("input[name='it_discount[" + key + "]']").val(change_discount);
                $("input[name='od_discount']").val(change_discount);
				$(itemDom).find(".price_print").text(number_format((cnt - discountCnt) * price));
                

				var has_barcode_text = $(itemDom).find('.barcode.barList').find('input[type="text"]').length;
				var has_barcode_button = $(itemDom).find('.barcode.barList').find('.open_input_barcode').length;
				var it_id = $(itemDom).data('code');

				if (has_barcode_text && !has_barcode_button) {
					$(itemDom).find('.barcode.barList').append('<a class="prodBarNumCntBtn open_input_barcode" data-id="' + it_id + '">바코드 (0/' + has_barcode_text + ')</a>');
				}
				

			});

			var it_price = $("input[name^=it_price]");
			var it_discount = $("input[name^=it_discount]");
			var totalPrice = 0;



            
            //배송비조회
            var send_price =0;
            var discount_prie =0;
            $.each(it_price, function(key, dom){
                if($(dom).closest(".list.item").attr("data-sup") == "Y"){
                    var send_price2 =0;
                    sendData_v=[];
                    sendData_v = {
                        "it_id" : $(dom).closest(".list.item").attr("data-code"),
                        "cart_id" :'<?=$s_cart_id?>'
                    };
                    $.ajax({
                            url : "./ajax.stock.send_price.php",
                            type : "POST",
                            async : false,
                            data : sendData_v,
                            success : function(result){
                                send_price2=result;
                            }
                        });
                        totalPrice = totalPrice + parseInt($(it_price[key]).val());
                        discount_prie = discount_prie + parseInt($(it_discount[key]).val());
                        if(totalPrice > 0){  send_price = send_price+parseInt(send_price2); }
                        if(totalPrice >= <?=$result_d['de_send_conditional'] ?>){  send_price = 0; }
                }
            });
			if(!totalPrice){
				$("input[name='od_send_cost']").val(0);
				$(".delivery_cost_display").text("0 원");
			} else {
                $("input[name='od_send_cost']").val($("input[name='od_send_cost_org']").val());
                $("input[name='od_send_cost']").val(send_price);
				$(".delivery_cost_display").text(number_format(send_price) + " 원");
			};
			$("input[name=od_price]").val(totalPrice);
			$("#od_cp_price").text(number_format(discount_prie));
			$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");

			calculate_order_price();
			$("#ad_sel_addr_recipient").show();

			$(".stockCntStatusDom").show();
			$("#sod_frm_taker").show();
			$("#sod_frm_pay").show();
			$(".ordLendFrm").show();

			var item = $(".ordLendDtmInput");
			for(var i = 0; i < item.length; i++){
				$(item[i]).val($(item[i]).attr("data-default"));
			}

			$("#od_stock_insert_yn").prop("checked", false);
			$("#sod_frm_stock_status").hide();
			$(".barList input").val("");
			$('#ad_sel_addr_recipient').click();

		}


        
		$(function() {
			function recipientDelete(){
				$(".recipientBox").remove();

				$('#Yrecipient').removeClass('block');
				$('#Yrecipient').addClass('none');

				$('#Nrecipient').removeClass('none');
				$('#Nrecipient').addClass('block');

				$('#penId').val('');
				$('#penNm').val('');
				$('#penTypeNm').val('');
				$('#penExpiDtm').val('');
				$('#penAppEdDtm').val('');
				$('#penConPnum').val('');
				$('#penConNum').val('');
				$('#penAddr').val('');
				$('#penMoney').val('');

				$("#ad_sel_addr_recipient").hide();

				var optionCntList = <?=json_encode($optionCntList)?>;
				var optionBarList = <?=json_encode($optionBarList)?>;
				var prodItemList = $(".table-list2 .list.item");

				$.each(prodItemList, function(key, itemDom){
					var code = $(itemDom).attr("data-code");            //아이템 넘버
					var itemList = $(itemDom).find(".pro > .pro-info > .text li");        //바코드개수
					var discountCnt = 0;
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

					$(itemDom).find(".barList").find("input").attr("type", "hidden");   //개수만큼 넣기
					
					var input_count = 0;

					$('.open_input_barcode').remove();

					$.each(itemList, function(subKey, subDom){
							var item = $(itemDom).find(".prodBarSelectBox" + subKey);  //셀력트박스 찾기
							var parent = $(this).closest(".list.item");
							var it_id = $(parent).attr("data-code");

							for(var i = 0; i < item.length; i++){
								var name = $(item[i]).attr("name");
								var dataCode = $(item[i]).attr("data-code");
								var dataThisCode = $(item[i]).attr("data-this-code");
								var dataName = $(item[i]).attr("data-name");

								$(item[i]).after('<input type="hidden" class="prodBarSelectBox hidden barcode_input prodBarSelectBox' + subKey + '" value="" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '">');
								input_count++;
								
								if (i === item.length - 1 && input_count) {
									$(item[i]).after('<a class="prodBarNumCntBtn open_input_barcode" data-id="' + it_id + '">바코드 (0/' + input_count + ')</a>');
								}
								$(item[i]).remove();
							}

							var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
							$(stockCntItem[subKey]).val(0);
					});

					$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
					$(itemDom).find(".price_print").text(number_format((cnt - discountCnt) * price));
				});

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;

				$.each(it_price, function(key, dom){
					if($(dom).closest(".list.item").attr("data-sup") == "Y"){
						totalPrice =  totalPrice + parseInt($(it_price[key]).val());
					}
				});

                //here

				if(!totalPrice){
					$("input[name='od_send_cost']").val(0);
					$(".delivery_cost_display").text("0 원");
				} else {
					$("input[name='od_send_cost']").val($("input[name='od_send_cost_org']").val());
					$(".delivery_cost_display").text(number_format($("input[name='od_send_cost_org']").val()) + " 원");
				}
				$("input[name=od_price]").val(totalPrice);
				$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
				calculate_order_price();

				$(".pay-btn button").text("상품 주문하기");
				$(".stockCntStatusDom").hide();
				$(".ordLendFrm").hide();
				$(".ordLendDtmInput").val("");

				$("#od_stock_insert_yn").prop("checked", false);
				$("#sod_frm_stock_status").show();
				$(".barList input").val("");
			};



			function od_stock_insert_yn(){
				var status = $("#od_stock_insert_yn").prop("checked");

				var prodItemList = $(".table-list2 .list.item");
				$(".barList input").val("");

				if(status){
					$("#sod_frm_taker").hide();
					$("#sod_frm_pay").hide();
					$(".barList input[type='hidden']").attr("type", "number");
				} else {
					$("#sod_frm_taker").show();
					$("#sod_frm_pay").show();
					$(".barList input[type='text']").attr("type", "hidden");
				}

				$.each(prodItemList, function(key, itemDom){
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

					if(status){
						price = 0;
						$("input[name='it_discount[" + key + "]']").val(price);
					} else {
						$("input[name='it_discount[" + key + "]']").val(0);
					}

					$("input[name='it_price[" + key + "]']").val(price);
					$(itemDom).find(".price_print").text(number_format(price));
					
					// 보유재고 바코드					
					var has_barcode_text = $(itemDom).find('.barcode.barList').find('.barcode_input').length;
					var has_barcode_button = $(itemDom).find('.barcode.barList').find('.open_input_barcode').length;
					var it_id = $(itemDom).data('code');

					if (has_barcode_text && !has_barcode_button) {
						$(itemDom).find('.barcode.barList').append('<a class="prodBarNumCntBtn open_input_barcode" data-id="' + it_id + '">바코드 (0/' + has_barcode_text + ')</a>');
					}
				});

				if(status){
					$("input[name='od_send_cost']").val(0);
					$(".delivery_cost_display").text("0 원");
				} else {
					$("input[name='od_send_cost']").val($("input[name='od_send_cost_org']").val());
					$(".delivery_cost_display").text(number_format($("input[name='od_send_cost_org']").val()) + " 원");
				}

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;

				$.each(it_price, function(key, dom){
					if($(dom).closest(".list.item").attr("data-sup") == "Y"){
                        totalPrice =  totalPrice + parseInt($(it_price[key]).val());
					}
				});

				$("input[name=od_price]").val(totalPrice);
				$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
                
				calculate_order_price();
			}


		//상품주문,수급자주문,보유재고등록 탭
		$('.detail-tab li').on('click',function(){
			switch($(this).attr("data-type")){
				case "order_pen" :
                <?php if(!$itemPenIdStatus){ ?>
                    alert("대여상품은 재고 확보 후 수급자 계약이 가능합니다.");
                    return false;
                <?php } ?>
            }
			
			if($(this).hasClass("on")){
				return false;
			}
			$('#ad_sel_addr_same').click();
			let thisIndex = $(this).index();
			$(this).addClass('on');
			$(this).siblings('li').removeClass('on');
			$('.tab-wrap').eq(thisIndex).addClass('on');

			$("#od_stock_insert_yn").prop("checked", false);
			$("#forderform_check_btn").text("상품 주문하기");

			$(".tab-2 .table-list2 .delivery-price").css("width", "20%");
			$(".tab-2 .table-list2 .barcode").hide();

			$(".order-info").show();
			$(".stock_insert_none").show();
			$(".order_none").show();
			$(".order_recipientInfoBox").hide();

			$("#penId").val("");
			$(".tab-2 .detail-price .all-info.all-info2 ul li span").text("수급자를 선택해주세요.");
			$(".tab-2 .detail-price .all-info.all-info2 ul li span").css("color", "#CCC");
			$(".order-info .top .add-ac p").text("배송지 선택");
			recipientDelete();

			switch($(this).attr("data-type")){
				case "stock_insert" :
					$("input[name=od_discount]").val("0");
					$("#od_stock_insert_yn").prop("checked", true);
					$("#forderform_check_btn").text("보유재고 등록");
					$(".tab-2 .table-list2 .delivery-price").css("width", "10%");
					$(".tab-2 .table-list2 .barcode").show();
					$(".order-info").hide();
					$(".stock_insert_none").hide();
					od_stock_insert_yn();
					break;
				case "order_pen" :
					$("input[name=od_discount]").val($("input[name=org_discount]").val());
					$(".tab-2 .table-list2 .delivery-price").css("width", "10%");
					$(".tab-2 .table-list2 .barcode").show();
					$(".order_recipientInfoBox").show();
                    $("#order_recipientBox").show();
					break;
				case "order" :
                    discount_for_order();
					$("input[name=od_discount]").val($("input[name=org_discount]").val());
					$(".order_none").hide();
					break;
			}
		});

		//결제방법 탭
		$('.payment-tab ul li > a').on('click',function(e){
			e.preventDefault();
		});
		$('.payment-tab ul li').on('click',function(e){
			e.preventDefault();

			var target = $(this).find("a").attr("data-for");

			$("input[name='od_settle_case']").prop("checked", false);
			$("#" + target).prop("checked", true);

			$('.payment-tab ul li').removeClass("on");
			$('.payment-tab ul li > a[data-for="' + target + '"]').closest("li").addClass("on");

			$("#settle_bank").hide();
			switch(target){
				case "od_settle_bank" :
					$("#settle_bank").show();
					break;
			}
		});
        
		//배송지선택
		$('.add-ac').find('p').on('click',function(){
			$(this).siblings('ul').stop().toggle();

			$('.add-ac').find('ul li').on('click',function(){
				let textValue = $(this).text();
				$(this).parents('ul').siblings('p').text(textValue);
				$(this).parents('ul').stop().hide();
			});
		});
		//재고 개수 선택
		$('.num-select').find('p').on('click',function(){
			$(this).siblings('ul').stop().toggleClass('on');

			$('.num-select').find('ul li').on('click',function(){
				let textValue = $(this).text();
				$(this).parents('ul').siblings('p').text(textValue);
				$(this).parents('ul').removeClass('on');
			});
		});
		});
	</script>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<?php if ($goods_count) $goods .= ' 외 '.$goods_count.'건'; ?>
	<script type="text/javascript">

            //상품주문 클릭시 할인금액 계산
            function discount_for_order(){
            var prodItemList = $(".table-list2 .list.item");      //아이템정보2
            var change_discount=0;
            $.each(prodItemList, function(key, itemDom){
                var code = $(itemDom).attr("data-code");
                var itemList = $(itemDom).find(".pro > .pro-info > .text li");

                $.each(itemList, function(subKey, subDom){
                    var dataBarCnt = Number($(subDom).attr("data-bar-cnt"));
                    //할인율 계산
                    sendData_discount=[];
                    //it_id, 주문수량 - 재고수량
                    sendData_discount = {
                        "it_id" : code,
                        "ct_sale_qty" : dataBarCnt
                    };
                    $.ajax({
                        url : "./ajax.change_discount.php",
                        type : "POST",
                        async : false,
                        data : sendData_discount,
                        success : function(result){
                            //console.log(sendData_discount);
                            //console.log(result);
                            change_discount=change_discount+parseInt(result);
                        }
                    });
                    
                });
            });
            $("input[name='od_discount']").val(change_discount);
            $("#od_cp_price").text(number_format(change_discount));
        }

		$(function(){
            //데이터 피커
			$.datepicker.setDefaults({
				dateFormat : 'yy-mm-dd',
				prevText: '이전달',
				nextText: '다음달',
				monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
				monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
				dayNames: ["일", "월", "화", "수", "목", "금", "토"],
				dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
				dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
				showMonthAfterYear: true,
				changeMonth: true,
				changeYear: true
			});

			$(".dateonly").datepicker({
				onSelect : function(dateText){
					if($(this).hasClass("ordLendStartDtm")){
						var dateList = $(this).val().split("-");
						var date = new Date(dateList[0], dateList[1], dateList[2]);

						date.setDate(date.getDate() + 364);

						var year = date.getFullYear();
						var month = date.getMonth();
						var day = date.getDate();

						month = (month < 10) ? "0" + month : month;
						day = (day < 10) ? "0" + day : day;

						$(this).closest(".list-day").find(".ordLendEndDtm").val(year + "-" + month + "-" + day);
					}
				}
			});

			$(".dateBtn").click(function(e){
				e.preventDefault();

				if(!$(this).closest(".list-day").find(".ordLendStartDtm").val()){
					$(this).closest(".list-day").find(".ordLendStartDtm").val($(this).closest(".list-day").find(".ordLendStartDtm").attr("data-default"));
				}

				var month = Number($(this).attr("data-month"));
				var dateList = $(this).closest(".list-day").find(".ordLendStartDtm").val().split("-");
				var date = new Date(dateList[0], dateList[1], dateList[2]);

				date.setMonth(date.getMonth() + month);
				date.setDate(date.getDate() - 1);

				var year = date.getFullYear();
				var month = date.getMonth();
				var day = date.getDate();

				month = (month < 10) ? "0" + month : month;
				day = (day < 10) ? "0" + day : day;

				$(this).closest(".list-day").find(".ordLendEndDtm").val(year + "-" + month + "-" + day);
			});


			var optionCntList = <?=json_encode($optionCntList)?>; //아이템정보
			var optionBarList = <?=json_encode($optionBarList)?>; //바코드저오
			var prodItemList = $(".table-list2 .list.item");      //아이템정보2
			$.each(prodItemList, function(key, itemDom){
				var code = $(itemDom).attr("data-code");
				var itemList = $(itemDom).find(".pro > .pro-info > .text li");

				$.each(itemList, function(subKey, subDom){
					var html = optionCntList[code][subKey];
					$(subDom).attr("data-bar-cnt", $(itemDom).find(".prodStockBarBox" + subKey).length);
					$(subDom).attr("data-stock-cnt", html);
					if(html){
						$(subDom).append(" <span class='stockCntStatusDom' style='opacity: 0.7; display: none; font-size: 11px;'>(보유 재고 : " + html + "개)</span>");
					}
				});
			});



            //재고바코드 셀렉트 박스
			$(document).on("change", ".prodBarSelectBox", function(){
				var code = $(this).closest(".list.item").find(".recipientBox").attr("data-code");
				var it_id = $(this).closest(".list.item").attr("data-code");

				var selected = [];
				var parent;

				$(this).closest(".list.item").find('.prodBarSelectBox').each(function(i, item) {
					selected.push($(item).val());
					parent = this;
				}).promise().done(function() {
					$(parent).closest(".list.item").find('.prodBarSelectBox').each(function(i, item) {
						$(item).find('option').show();
						$(item).find('option').css({"visibility":"visible"}); // ie에서 option show, hidden 이슈 있어서 대체

						// 선택된값 숨기기
						$(item).find('option').each(function(ii, sub_item) {
							if ( $(sub_item).val() && selected.indexOf($(sub_item).val()) > -1 ) {
								$(sub_item).hide();
								$(sub_item).css({"visibility":"hidden"});
							}
						});
					});
				});
			});


            //바코드입력 함수
			$(document).on("click", ".open_input_barcode", function(){
				var it_id = $(this).data('id');
				var barcode_nodes = $(this).closest('.barList').find('.barcode_input');
				var barcodes = [];
				var is_mobile = navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1;

				$(barcode_nodes).each(function(i, item) {
					barcodes.push($(item).val());
				});

				window.name = "barcode_parent";
				var url = "./popup.order_barcode_form.php";
				var open_win;

				if(is_mobile) {
					$('#barcode_popup_iframe').show();
				} else {
					open_win = window.open("", "barcode_child", "width=683, height=800, resizable = no, scrollbars = no");
				}

				$('#barcode_popup_form').attr('target', is_mobile ? 'barcode_popup_iframe' : 'barcode_child');

				$('#barcode_popup_form').attr('action', url);
				$('#barcode_popup_form').attr('method', 'post');
				$('#barcode_popup_form input[name="it_id"]').val(it_id);
				$('#barcode_popup_form input[name="barcodes"]').val(barcodes.join('|'));
				$('#barcode_popup_form').submit();
			});


            //재고바코드 셀렉트 박스
			$(document).on("change", ".prodBarSelectBox", function(){
                var this_a=this;
                var this_v = $(this).val();
                var flag=false;
				if($(this).val()){
					var code = $(this).attr("data-code");
					var item = $(this).closest("li").find(".prodBarSelectBox" + code);

                    var sendData2=[];
                    var prodsData = [];
                    var prodsSendData = [];

                    var it_id_class = $(this).closest("li");
					prodsData["prodId"] = it_id_class.attr('data-code');
                    //console.log(prodsData["prodId"]);
                    sendData2 = {
                        usrId : "<?=$member["mb_id"]?>",
                        prodId : prodsData["prodId"]
                    };
						if($("#od_stock_insert_yn").prop("checked")){
							$.ajax({
								url : "./ajax.stock.selectbarnumlist.php",
								type : "POST",
								async : false,
								data : sendData2,
								success : function(result){
									result = JSON.parse(result);
									//console.log(result.data[0].prodBarNumList);

									for(var i =0; i < result.data[0].prodBarNumList.length; i ++){
										if(result.data[0].prodBarNumList[i] == this_v){
											alert("이미 등록된 바코드입니다.");
											$(this_a).val("");
											flag=true;
											return false;
										}
									}

								}
							});
						}
                    if(flag){ return false;}
					for(var i = 0; i < item.length; i++){
						if($(this).attr("data-this-code") != $(item[i]).attr("data-this-code")){
							if($(this).val() == $(item[i]).val()){
								alert("바코드는 중복선택하실 수 없습니다.");
								$(this).val("");
								return false;
							}
						}
					}
				}
			});


            //수급자 -> select 박스 클릭시 돌아가는 함수
			$(document).on("change", ".recipientBox select", function(){
				if($(this).parent(".recipientBox").find("input[type='radio']:checked").attr("data-type") != "use"){
					return false;
				}

				var code = $(this).closest(".recipientBox").attr("data-code");
				var val = $(this).val();
				var item = $(this).closest(".list.item").find(".prodBarSelectBox" + code);
				var it_id = $(this).closest(".list.item").attr("data-code");

				var input_count = 0;
                var select_count = 0;
                var change_discount=0;

				$('.open_input_barcode').remove();


                // 신규, 재고소진
				for(var i = 0; i < item.length; i++){
					var name = $(item[i]).attr("name");
					var dataCode = $(item[i]).attr("data-code");
					var dataThisCode = $(item[i]).attr("data-this-code");
					var dataName = $(item[i]).attr("data-name");
					var html = "";
					if(i < val){
                        select_count++;
                        var html = '<select id="prodBarSelectBox_renew'+i+'" class="prodBarSelectBox prodBarSelectBox' + code + '" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '"><option value="">재고 바코드</option>';
							optionBarList[it_id][code].sort();
							$.each(optionBarList[it_id][code], function(key, value){
								html += '<option value="' + value + '">' + value + '</option>';
							});
						html += '</select>';
					} else {
						input_count++;
						html += '<input type="text" class="prodBarSelectBox' + code + ' hidden barcode_input" value="" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '">';
					}

					if (i === item.length - 1 && input_count) {
						html += '<a class="prodBarNumCntBtn open_input_barcode" data-id="' + it_id + '">바코드 (0/' + input_count + ')</a>';
					}
					$(item[i]).after(html);
					$(item[i]).remove();
				}

                //전체 아이템 
				$.each(prodItemList, function(key, itemDom){
                    
					var code = $(itemDom).attr("data-code");
					var itemList = $(itemDom).find(".pro > .pro-info > .text li");
					var discountCnt = 0;
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));
                    //아이템 개당 계산
					$.each(itemList, function(subKey, subDom){
                        var dataBarCnt = Number($(subDom).attr("data-bar-cnt"));
					    var select_num = $(itemDom).find(".prodBarSelectBox"); //select 개수

						if($(itemDom).attr("data-sup") == "Y"){
							var checkedType = $(subDom).closest(".item").find(".list-btm").find(".recipientBox input[type='radio']:checked").attr("data-type");

							if(checkedType == "use"){
								discountCnt += Number($(subDom).closest(".item").find(".list-btm").find(".recipientBox select").val());
							}
                            //할인율 계산
                            sendData_discount=[];
                            //it_id, 주문수량 - 재고수량
                            sendData_discount = {
                                "it_id" : code,
                                "ct_sale_qty" : dataBarCnt -select_num.length
                            };
                            $.ajax({
                                url : "./ajax.change_discount.php",
                                type : "POST",
                                async : false,
                                data : sendData_discount,
                                success : function(result){
                                    //console.log(sendData_discount);
                                    //console.log(result);
                                    change_discount=change_discount+parseInt(result);
                                }
                            });
						}

						var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
						if(checkedType == "use"){
							var stockCntItemCnt = Number($(subDom).closest(".item").find(".list-btm").find(".recipientBox select").val());
							stockCntItemCnt = (stockCntItemCnt) ? stockCntItemCnt : 0;
							$(stockCntItem[subKey]).val(stockCntItemCnt);
						} else {
							$(stockCntItem[subKey]).val(0);
						}
                        });
                    $("input[name='it_discount[" + key + "]']").val(change_discount);
					$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
					$(itemDom).find(".price_print").text(number_format((cnt - discountCnt) * price));
				});

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;
                //배송비조회, 할인율계산
                var send_price =0;
                $.each(it_price, function(key, dom){

                    if($(dom).closest(".list.item").attr("data-sup") == "Y"){
                        var send_price2 =0;
                        sendData_v=[];
                        sendData_v = {
                            "it_id" : $(dom).closest(".list.item").attr("data-code"),
                            "cart_id" :'<?=$s_cart_id?>'
                        };
                        $.ajax({
                                url : "./ajax.stock.send_price.php",
                                type : "POST",
                                async : false,
                                data : sendData_v,
                                success : function(result){
                                    send_price2=result;
                                }
                            });

                            totalPrice =  totalPrice + parseInt($(it_price[key]).val());

                            if(totalPrice > 0){  send_price = send_price+parseInt(send_price2); }
                            if(totalPrice >= <?=$result_d['de_send_conditional'] ?>){  send_price = 0; }

                    }
                });
                    $("input[name='od_discount']").val(change_discount);
                    $("#od_cp_price").text(number_format(change_discount));
                    $("input[name='od_send_cost']").val(send_price);
                    $(".delivery_cost_display").text(number_format(send_price)+" 원");
                    $("input[name=od_price]").val(totalPrice);
                    $("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
				calculate_order_price();
			});



            //수급자신청 재고소진 or 신규주문 
			$(document).on("change", ".recipientBox input[type='radio']", function(){
				var code = $(this).closest(".recipientBox").attr("data-code");
				var parent = $(this).closest(".list.item");
				var type = $(this).attr("data-type");
				var item = $(parent).find(".prodBarSelectBox" + code);
				var it_id = $(parent).attr("data-code");

				var input_count = 0;
                var change_discount=0;

				$('.open_input_barcode').remove();

                var od_send_cost_org = $("input[name^=od_send_cost_org]")
				switch(type){
					case "new" :
						for(var i = 0; i < item.length; i++){
							var name = $(item[i]).attr("name");
							var dataCode = $(item[i]).attr("data-code");
							var dataThisCode = $(item[i]).attr("data-this-code");
							var dataName = $(item[i]).attr("data-name");

							$(item[i]).after('<input type="text" class="prodBarSelectBox' + code + ' hidden barcode_input" value="" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '">');
							input_count++;

							if (i === item.length - 1 && input_count) {
								$(item[i]).after('<a class="prodBarNumCntBtn open_input_barcode" data-id="' + it_id + '">바코드 (0/' + input_count + ')</a>');
							}

							$(item[i]).remove();
						}
				        $("input[name=od_send_cost]").val($("input[name=od_send_cost_org]").val());
						break;
					case "use" :
						for(var i = 0; i < item.length; i++){
							var name = $(item[i]).attr("name");
							var dataCode = $(item[i]).attr("data-code");
							var dataThisCode = $(item[i]).attr("data-this-code");
							var dataName = $(item[i]).attr("data-name");

							//20210306 성훈수정(아래줄 id 추가)
							var html = '<select id="prodBarSelectBox_renew'+i+'" class="prodBarSelectBox prodBarSelectBox' + code + '" style="margin-bottom: 5px;" data-code="' + dataCode + '" data-this-code="' + dataThisCode + '" data-name="' + dataName + '" name="' + name + '"><option value="">재고 바코드</option>';
							optionBarList[it_id][code].sort();
							$.each(optionBarList[it_id][code], function(key, value){
								html += '<option value="' + value + '">' + value + '</option>';
							});
							html += '</select>';

							$(item[i]).after(html);

							$(item[i]).remove();
						}
				        $("input[name=od_send_cost]").val(0);
						$(this).closest(".recipientBox").find("select").val(item.length);
						break;
				}

				$.each(prodItemList, function(key, itemDom){
					var code = $(itemDom).attr("data-code");
					var itemList = $(itemDom).find(".pro > .pro-info > .text li");
					var discountCnt = 0;
					var price = Number($("input[name='ct_price[" + key + "]']").val().replace(/,/gi, ""));
					var cnt = Number($("input[name='it_qty[" + key + "]']").val().replace(/,/gi, ""));

					$.each(itemList, function(subKey, subDom){
                        var dataBarCnt = Number($(subDom).attr("data-bar-cnt"));
						if($(itemDom).attr("data-sup") == "Y"){
							var checkedType = $(subDom).closest(".item").find(".list-btm").find(".recipientBox input[type='radio']:checked").attr("data-type");

							if(checkedType == "use"){
								discountCnt += Number($(subDom).closest(".item").find(".list-btm").find(".recipientBox select").val());
							}
						}

						var stockCntItem = $(itemDom).find(".it_option_stock_cnt");
						if(checkedType == "use"){
							$(stockCntItem[subKey]).val(Number($(subDom).closest(".item").find(".list-btm").find(".recipientBox select").val()));
						} else {
							$(stockCntItem[subKey]).val(0);
						}

                        //할인율 계산
                        sendData_discount=[];
                        //it_id, 주문수량 - 재고수량
                        sendData_discount = {
                            "it_id" : code,
                            "ct_sale_qty" : dataBarCnt
                        };
                        $.ajax({
                            url : "./ajax.change_discount.php",
                            type : "POST",
                            async : false,
                            data : sendData_discount,
                            success : function(result){
                                //console.log(sendData_discount);
                                //console.log(result);
                                change_discount=change_discount+parseInt(result);
                            }
                        });

					});
                    
					$("input[name='it_price[" + key + "]']").val((cnt - discountCnt) * price);
					$(itemDom).find(".price_print").text(number_format((cnt - discountCnt) * price));
				});

				var it_price = $("input[name^=it_price]");
				var it_discount = $("input[name^=it_discount]");
				var totalPrice = 0;
                //배송비 조회
                var send_price =0;
				$.each(it_price, function(key, dom){
					if($(dom).closest(".list.item").attr("data-sup") == "Y"){
                        var send_price2 =0;
                        sendData_v=[];
                        sendData_v = {
                            "it_id" : $(dom).closest(".list.item").attr("data-code"),
                            "cart_id" :'<?=$s_cart_id?>'
                        };
                        $.ajax({
								url : "./ajax.stock.send_price.php",
								type : "POST",
								async : false,
								data : sendData_v,
								success : function(result){
                                    send_price2=result;
								}
							});
                            totalPrice += $(it_price[key]).val() - $(it_discount[key]).val();
                            if(totalPrice > 0){  send_price = send_price+parseInt(send_price2); }
                            if(totalPrice >= <?=$result_d['de_send_conditional'] ?>){  send_price = 0; }
					}
				});

                $("input[name='od_discount']").val(change_discount);
                $("#od_cp_price").text(number_format(change_discount));
                $("input[name='od_send_cost']").val(send_price);
                $(".delivery_cost_display").text(number_format($("input[name=od_send_cost]").val())+" 원");
				$("input[name=od_price]").val(totalPrice);
				$("#printTotalCellPrice").text(number_format(totalPrice) + " 원");
				calculate_order_price();
			});
		})
	</script>

	<script type="text/javascript">
		$(function() {
			// 수급자목록
				$("#order_submitCheckBox").hide();
				$("#order_submitCheckBox").css("opacity", 1);

				$("#order_recipientBox").hide();
				$("#order_recipientBox").css("opacity", 1);
			$(".order_recipient").click(function(e){
				e.preventDefault();

				<?php if($itemPenIdStatus){ ?>
					$("#order_recipientBox").show();
				<?php } else { ?>
					alert("대여상품은 재고 확보 후 수급자 계약이 가능합니다.");
				<?php } ?>
			});

			$('#od_delivery_type').change(function() {
				var val = $(this).val();

				if ( val === 'quick2' ) {
					$('.quick_explain').show();
				}else{
					$('.quick_explain').hide();
				}
			});
		});
	</script>

    <?php if ($ct_sc_method_sel) { ?>
    <script>
        $(window).load(function () {
            $('#od_delivery_type').val('<?php echo $ct_sc_method_sel ?>');
            $('#od_delivery_type').trigger('change');
        })

    </script>
    <?php } ?>

<?php } ?>

<script>
<?php if($_GET['penId_r']){ //보유재고 관리에서 넘어오면 실행 ?>
        $(document).ready(function() {
            $('#c_recipient').click();
            $("#order_recipientBox").hide();
            selected_recipient('<?=$_GET['penId_r']?>');
            $('.prodBarSelectBox0 option[value="<?=$_GET['barcode_r']?>"]').attr('selected', 'selected');
            $('#ad_sel_addr_same').click();
        });
<?php } ?>
</script>