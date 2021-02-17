<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['ivhead']) && $wset['ivhead']) {
	add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ivhead'].'.css" media="screen">', 0);
	$head_class = 'list-head';
} else {
	$head_class = (isset($wset['ivcolor']) && $wset['ivcolor']) ? 'tr-head border-'.$wset['ivcolor'] : 'tr-head border-black';
}

// 헤더 출력
if($header_skin)
	include_once('./header.php');

// echo $_SERVER['HTTP_REFERER'];

// if(strpos($_SERVER['HTTP_REFERER'], 'orderform') !== false) {
// }

	$prodListCnt = 0;
	$prodList = [];

	if($od["ordId"]){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_URL, "https://eroumcare.com/api/pen/pen5000/pen5000/selectPen5000.do?ordId={$od["ordId"]}&uuid={$od["uuid"]}");
		$res = curl_exec($ch);
		$result = json_decode($res, true);
		$result = $result["data"];

		if($result){
			$ordZip = [];
			$ordZip[0] = substr($result[0]["ordZip"], 0, 3);
			$ordZip[1] = substr($result[0]["ordZip"], 3, 2);

			sql_query("
				UPDATE {$g5["g5_shop_order_table"]} SET
					  mb_id = '{$result[0]["usrId"]}'
					, od_penId = '{$result[0]["penId"]}'
					, od_delivery_text = '{$result[0]["ordWayNum"]}'
					, od_delivery_company = '{$result[0]["delSerCd"]}'
					, od_b_name = '{$result[0]["ordNm"]}'
					, od_b_tel = '{$result[0]["ordCont"]}'
					, od_memo = '{$result[0]["ordMeno"]}'
					, od_b_zip1 = '{$ordZip[0]}'
					, od_b_zip2 = '{$ordZip[1]}'
					, od_b_addr1 = '{$result[0]["ordAddr"]}'
					, od_b_addr2 = '{$result[0]["ordAddrDtl"]}'
					, payMehCd = '{$result[0]["payMehCd"]}'
					, eformYn = '{$result[0]["eformYn"]}'
					, staOrdCd = '{$result[0]["staOrdCd"]}'
				WHERE od_id = '{$od["od_id"]}'
			");
			$od = sql_fetch("SELECT * FROM {$g5["g5_shop_order_table"]} WHERE od_id = '{$od["od_id"]}'");
			
			foreach($result as $data){
				$thisProductData = [];
				
				$thisProductData["prodId"] = $data["prodId"];
				$thisProductData["prodColor"] = $data["prodColor"];
				$thisProductData["prodBarNum"] = $data["prodBarNum"];
				$thisProductData["penStaSeq"] = $data["penStaSeq"];
				array_unshift($prodList, $thisProductData);
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

?>

<script type="text/javascript">
// 주문 완료인경우
if (document.referrer.indexOf("shop/orderform.php") >= 0) {

	// 네이버
	if (!wcs_add) var wcs_add={};
	wcs_add["wa"] = "<?php echo NAVER_WCS_WA; ?>";
	if (!_nasa) var _nasa={};
	_nasa["cnv"] = wcs.cnv("1","<?php echo $tot_price; ?>");
	wcs_do(_nasa);

	// 다음
	//<![CDATA[
	var DaumConversionDctSv="type=P,orderID=<?php echo $od_id; ?>,amount=<?php echo $tot_price; ?>";
	var DaumConversionAccountID="<?php echo DAUM_CONVERSION_ACCOUNT_ID; ?>";
	if(typeof DaumConversionScriptLoaded=="undefined"&&location.protocol!="file:"){
		var DaumConversionScriptLoaded=true;
		document.write(unescape("%3Cscript%20type%3D%22text/javas"+"cript%22%20src%3D%22"+(location.protocol=="https:"?"https":"http")+"%3A//t1.daumcdn.net/cssjs/common/cts/vr200/dcts.js%22%3E%3C/script%3E"));
	}
	//]]>

}
</script>

<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">상태설명</h4>
			</div>
			<div class="modal-body">
				<ul>
				<li>주문 : 주문이 접수되었습니다.</li>
				<li>입금 : 입금(결제)이 완료 되었습니다.</li>
				<li>준비 : 상품 준비 중입니다.</li>
				<li>배송 : 상품 배송 중입니다.</li>
				<li>완료 : 상품 배송이 완료 되었습니다.</li>
				</ul>
				<br>
				<p class="text-center">
					<button type="button" class="btn btn-black btn-sm" data-dismiss="modal">닫기</button>
				</p>
			</div>
		</div>
	</div>
</div>


<div class="well well-sm">
	<span class="print-hide cursor pull-right hidden-xs" data-toggle="modal" data-target="#statusModal">
		<i class="fa fa-info-circle"></i> 상태설명
	</span>
	  주문번호 : <strong><?php echo $od_id; ?></strong>
</div>

<style>
	.delivery-info { margin:0px; padding:0px; padding-left:15px; line-height:22px; white-space:nowrap; }
	.orderInfoTopBtnWrap { width: 100%; float: left; text-align: right; margin-bottom: 10px; }
</style>

<div class="orderInfoTopBtnWrap">
	<button type="button" id="prodBarNumSaveBtn" class="btn btn-blue btn-sm">바코드저장</button>
</div>

<div class="table-responsive">
	<table class="div-table table bsk-tbl bg-white">
	<tbody>
	<tr class="<?php echo $head_class;?>">
		<th scope="col"><span>이미지</span></th>
		<th scope="col"><span>상품명 / 옵션명</span></th>
		<th scope="col"><span>수량</span></th>
		<th scope="col"><span>판매가</span></th>
		<th scope="col"><span>소계</span></th>
		<th scope="col"><span>상태</span></th>
		<th scope="col"><span>바코드</span></th>
		<!--<th scope="col"><span class="last">배송/이용정보</span></th>-->
	</tr>
	<?php for($i=0; $i < count($item); $i++) { $prodMemo = ""; ?>
		<?php for($k=0; $k < count($item[$i]['opt']); $k++) { $prodMemo = ($prodMemo) ? $prodMemo : $item[$i]["prodMemo"]; ?>
			<?php if($k == 0) { ?>
				<tr<?php echo ($i == 0) ? ' class="tr-line"' : '';?>>
					<td class="text-center" style="vertical-align: middle;" rowspan="<?php echo ($item[$i]['rowspan'] + 1); ?>">
						<div class="item-img">
							<img src="/data/item/<?=$item[$i]['thumbnail']?>" onerror="this.src = '/shop/img/no_image.gif';" style="width: 50px; height: 50px;">
							<div class="item-type"><?php echo $item[$i]['pt_it']; ?></div>
						</div>
					</td>
					<td colspan="6">
						<a href="./item.php?it_id=<?php echo $item[$i]['it_id']; ?>">
							<strong><?php echo $item[$i]['it_name']; ?></strong>
							<?php if($item[$i]["prodSupYn"] == "N"){ ?>
								<b style="position: relative; display: inline-block; width: 50px; height: 20px; line-height: 20px; top: -1px; border-radius: 5px; text-align: center; color: #FFF; font-size: 11px; background-color: #DC3333;">비유통</b>
							<?php } ?>
						</a>
					</td>
					<!--
					<td rowspan="<?php echo $item[$i]['rowspan']; ?>">
						<ul class="delivery-info">
							<?php if($item[$i]['seller']) { // 판매자?>
								<li><b><?php echo $item[$i]['seller'];?></b></li>
							<?php } ?>
							<li>
								<?php echo $item[$i]['ct_send_cost'];?>배송
								<?php if($item[$i]['sendcost']) { // 개별배송비 ?>
									(<?php echo number_format($item[$i]['sendcost']);?>원)
								<?php } ?>
							</li>
							<?php if ($item[$i]['is_delivery']) { // 배송가능 ?>

								<?php if($item[$i]['de_company'] && $item[$i]['de_invoice']) { ?>
									<li>
										<?php echo $item[$i]['de_company'];?>
										<?php echo $item[$i]['de_invoice'];?>
									</li>
									<?php if($item[$i]['de_check']) { ?>
										<li>
											<?php echo str_replace("문의전화: ", "", $item[$i]['de_check']);?>
										</li>
									<?php } ?>
								<?php } ?>
								<?php if($item[$i]['de_confirm']) { //수령확인 ?>
									<li>
										<a href="<?php echo $item[$i]['de_confirm'];?>" class="delivery-confirm">
											<span class="orangered">수령확인</span>
										</a>
									</li>
								<?php } ?>

							<?php } else { //배송불가 - 컨텐츠 ?>

								<?php if($list[$i]['use_date']) { ?>
									<li>최종일시 : <?php echo $list[$i]['use_date'];?></li>
								<?php } ?>
								<?php if($list[$i]['use_file']) { ?>
									<li>최종자료 : <?php echo $list[$i]['use_file'];?></li>
								<?php } ?>
								<?php if($list[$i]['use_cnt']) { ?>
									<li>이용횟수 : <?php echo number_format($list[$i]['use_cnt']);?>회</li>
								<?php } ?>

							<?php } ?>
						</ul>
					</td>
					-->
				</tr>
			<?php } ?>
			<tr>
				<td style="vertical-align: middle;"><?php echo $item[$i]['opt'][$k]['ct_option']; ?></td>
				<td class="text-center" style="vertical-align: middle;"><?php echo number_format($item[$i]['opt'][$k]['ct_qty']); ?></td>
				<td class="text-right" style="vertical-align: middle;"><?php echo number_format($item[$i]['opt'][$k]['opt_price']); ?></td>
				<td class="text-right" style="vertical-align: middle;"><?php echo number_format($item[$i]['opt'][$k]['sell_price']); ?></td>
				<td class="text-center" style="vertical-align: middle;">
					<?php
						$ct_status = get_step($item[$i]['opt'][$k]['ct_status']);
						echo $ct_status['name'];
					?>
				</td>
				<td style="width: 120px; vertical-align: middle;">
				<?php for($ii = 0; $ii < $item[$i]["opt"][$k]["ct_qty"]; $ii++){ ?>
					<?php if($od["staOrdCd"] == "03"){ ?>
						<?=($ii) ? "<br>" : ""?>
						<span><?=$prodList[$prodListCnt]["prodBarNum"]?></span>
					<?php } else { ?>
						<input type="text" class="form-control input-sm prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoIdDataList[$prodListCnt]?>" style="margin-bottom: 5px;" value="<?=$prodList[$prodListCnt]["prodBarNum"]?>" <?=($item[$i]["ct_stock_qty"] > $ii) ? "readonly" : ""?>>
					<?php } ?>
				<?php $prodListCnt++; } ?>
				</td>
			</tr>
		<?php } ?>
			<tr>
				<td colspan="6">
					<b>요청사항 : </b>
					<?=$prodMemo?>
				</td>
			</tr>
	<?php } ?>
	</tbody>
	</table>
</div>

<div class="well">
	<div class="row">
		<div class="col-xs-6">주문총액</div>
		<div class="col-xs-6 text-right">
			<strong><?=number_format($tot_price - $od["od_send_cost"])?> 원</strong>
		</div>
		<?php if($od['od_cart_coupon'] > 0) { ?>
			<div class="col-xs-6">개별상품 쿠폰할인</div>
			<div class="col-xs-6 text-right">
				<strong><?php echo number_format($od['od_cart_coupon']); ?> 원</strong>
			</div>
		<?php } ?>
		<?php if($od['od_coupon'] > 0) { ?>
			<div class="col-xs-6">주문금액 쿠폰할인</div>
			<div class="col-xs-6 text-right">
				<strong><?php echo number_format($od['od_coupon']); ?> 원</strong>
			</div>
		<?php } ?>

		<?php if ($od['od_send_cost'] > 0) { ?>
			<div class="col-xs-6">배송비</div>
			<div class="col-xs-6 text-right">
				<strong><?php echo number_format($od['od_send_cost']); ?> 원</strong>
			</div>
		<?php } ?>

		<?php if($od['od_send_coupon'] > 0) { ?>
			<div class="col-xs-6">배송비 쿠폰할인</div>
			<div class="col-xs-6 text-right">
				<strong><?php echo number_format($od['od_send_coupon']); ?> 원</strong>
			</div>
		<?php } ?>

		<?php if ($od['od_send_cost2'] > 0) { ?>
			<div class="col-xs-6">추가배송비</div>
			<div class="col-xs-6 text-right">
				<strong><?php echo number_format($od['od_send_cost2']); ?> 원</strong>
			</div>
		<?php } ?>

		<?php if ($od['od_cancel_price'] > 0) { ?>
			<div class="col-xs-6">취소금액</div>
			<div class="col-xs-6 text-right">
				<strong><?php echo number_format($od['od_cancel_price']); ?> 원</strong>
			</div>
		<?php } ?>

		<?php if ($od['od_cart_discount'] > 0) { ?>
			<div class="col-xs-6">할인금액</div>
			<div class="col-xs-6 text-right">
				<strong>- <?php echo number_format($od['od_cart_discount']); ?> 원</strong>
			</div>
		<?php } ?>

		<?php if ($od['od_cart_discount2'] > 0) { ?>
			<div class="col-xs-6">추가할인금액</div>
			<div class="col-xs-6 text-right">
				<strong>- <?php echo number_format($od['od_cart_discount2']); ?> 원</strong>
			</div>
		<?php } ?>

		<div class="col-xs-6 red"> <b>합계금액</b></div>
		<div class="col-xs-6 text-right red od_tot_price">
			<strong class="print_price"><?php echo number_format($tot_price); ?> 원</strong>
		</div>

		<?php if ($tot_point > 0) { ?>
		<div class="col-xs-6"> 포인트</div>
		<div class="col-xs-6 text-right">
			<strong><?php echo number_format($tot_point); ?> 점</strong>
		</div>
		<?php } ?>
	</div>
</div>

<div class="panel panel-success">
	<div class="panel-heading"><strong>  결제정보</strong></div>
	<div class="table-responsive">
		<table class="div-table table bsk-tbl bg-white">
		<col width="120">
		<tbody>
		<tr>
			<th scope="row">주문번호</th>
			<td><?php echo $od_id; ?></td>
		</tr>
		<tr>
			<th scope="row">주문일시</th>
			<td><?php echo $od['od_time']; ?></td>
		</tr>
		<tr>
			<th scope="row">결제방식</th>
			<td><?php echo ($easy_pay_name ? $easy_pay_name.'('.$od['od_settle_case'].')' : check_pay_name_replace($od['od_settle_case']) ); ?></td>
		</tr>
		<tr>
			<th scope="row">결제상태</th>
			<td><?php echo $pay_status['fullname']; ?></td>
		</tr>
		<tr class="active">
			<th scope="row">결제금액</th>
			<td><?php echo $od_receipt_price; ?></td>
		</tr>
		<?php if($od['od_receipt_price'] > 0) {	?>
			<tr>
				<th scope="row">결제일시</th>
				<td><?php echo $od['od_receipt_time']; ?></td>
			</tr>
		<?php } ?>
		<?php if($app_no_subj) { // 승인번호, 휴대폰번호, 거래번호 ?>
			<tr>
				<th scope="row"><?php echo $app_no_subj; ?></th>
				<td><?php echo $app_no; ?></td>
			</tr>
		<?php } ?>
		<?php if($disp_bank) { // 계좌정보 ?>
			<tr>
				<th scope="row">입금자명</th>
				<td><?php echo get_text($od['od_deposit_name']); ?></td>
			</tr>
			<tr>
				<th scope="row">입금계좌</th>
				<td><?php echo get_text($od['od_bank_account']); ?></td>
			</tr>
		<?php } ?>
		<?php if($disp_receipt_href) { ?>
			<tr>
				<th scope="row">영수증</th>
				<td><a <?php echo $disp_receipt_href;?>>영수증 출력</a></td>
			</tr>
		<?php } ?>
		<?php if ($od['od_receipt_point'] > 0) { ?>
			<tr>
				<th scope="row">포인트사용</th>
				<td><?php echo display_point($od['od_receipt_point']); ?></td>
			</tr>
		<?php } ?>
		<?php if ($od['od_refund_price'] > 0) { ?>
			<tr>
				<th scope="row">환불 금액</th>
				<td><?php echo display_price($od['od_refund_price']); ?></td>
			</tr>
		<?php } ?>
		<?php if($taxsave_href) { ?>
			<tr>
				<th scope="row">현금영수증</th>
				<td>
					<a <?php echo $taxsave_href;?> class="btn btn-black btn-xs">
						<?php echo ($taxsave_confirm) ? '현금영수증 확인하기' : '현금영수증을 발급하시려면 클릭하십시오.';?>
					</a>
				</td>
			</tr>
		<?php } ?>
		<?php
		if ($typereceipt['od_id']) {
		?>
			<tr>
					<th scope="row">매출증빙</th>
					<td>
						<?php echo $typereceipt['name']; ?>
						<?php echo $typereceipt['ot_btel'] ? '( ' . $typereceipt['ot_btel'] : ''; ?>
						<?php echo $typereceipt['ot_tax_email'] ? ' / ' . $typereceipt['ot_tax_email'] : ''; ?>
						<?php echo $typereceipt['ot_btel'] ? ')': ''; ?>
					</td>
			</tr>
		<?php } ?>
		</tbody>
		</table>
	</div>
</div>

<?php if($is_orderform) { ?>
	<div class="panel panel-default">
		<div class="panel-heading"><strong> 주문하신 분</strong></div>
		<div class="table-responsive">
			<table class="div-table table bsk-tbl bg-white">
			<col width="120">
			<tbody>
			<tr>
				<th scope="row">이 름</th>
				<td><?php echo get_text($od['od_name']); ?></td>
			</tr>
			<tr>
				<th scope="row">전화번호</th>
				<td><?php echo get_text($od['od_tel']); ?></td>
			</tr>
			<tr>
				<th scope="row">핸드폰</th>
				<td><?php echo get_text($od['od_hp']); ?></td>
			</tr>
			<tr>
				<th scope="row">주 소</th>
				<td><?php echo get_text(sprintf("(%s%s)", $od['od_zip1'], $od['od_zip2']).' '.print_address($od['od_addr1'], $od['od_addr2'], $od['od_addr3'], $od['od_addr_jibeon'])); ?></td>
			</tr>
			<tr>
				<th scope="row">E-mail</th>
				<td><?php echo get_text($od['od_email']); ?></td>
			</tr>
			</tbody>
			</table>
		</div>
	</div>
	<!-- 수급자 입력 시작 -->

	<div class="point_box">
		<div class="top_area">
			<p>수급자 정보</p>
			<p>주문 시 수급자정보를 입력하셨습니다.</p>

		</div>
		<!-- 수급자 정보가 있으면 아래 내용이 보여집니다. -->
		<div class="point_desc_info">

			<ul>
				<?php if($od['od_penId']){ ?>
				<li>
					<p>수급자</p>
					<p><?php echo get_text($od['od_penNm']); ?></p>
				</li>
				<li>
					<p>인정등급</p>
					<p><?php echo get_text($od['od_penTypeNm']); ?></p>
				</li>
				<li>
					<p>유효기간</p>
					<p><?php echo get_text($od['od_penExpiDtm']); ?></p>
				</li>
				<li>
					<p>적용기간</p>
					<p><?php echo get_text($od['od_penAppEdDtm']); ?></p>
				</li>
				<li>
					<p>전화번호</p>
					<p><?php echo get_text($od['od_penConPnum']); ?></p>
				</li>
				<li>
					<p>휴대전화</p>
					<p><?php echo get_text($od['od_penConPnum']); ?></p>
				</li>
				<li>
					<p>주소</p>
					<p><?php echo get_text($od['od_penAddr']); ?></p>
				</li>
				<?php }else{ ?>
				수급자 정보가 없습니다.
				<?php } ?>
			</ul>
		</div>
	</div>


	<!-- 수급자 입력 끝 -->

	<div class="panel panel-default">
		<div class="panel-heading"><strong>  받으시는 분</strong></div>
		<div class="table-responsive">
			<table class="div-table table bsk-tbl bg-white">
			<col width="120">
			<tbody>
			<tr>
				<th scope="row">이 름</th>
				<td><?php echo get_text($od['od_b_name']); ?></td>
			</tr>
			<tr>
				<th scope="row">전화번호</th>
				<td><?php echo get_text($od['od_b_tel']); ?></td>
			</tr>
			<tr>
				<th scope="row">핸드폰</th>
				<td><?php echo get_text($od['od_b_hp']); ?></td>
			</tr>
			<tr>
				<th scope="row">주 소</th>
				<td><?php echo get_text(sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon'])); ?></td>
			</tr>
			<?php if ($default['de_hope_date_use']) { // 희망배송일을 사용한다면 ?>
				<tr>
					<th scope="row">희망배송일</th>
					<td><?php echo substr($od['od_hope_date'],0,10).' ('.get_yoil($od['od_hope_date']).')' ;?></td>
				</tr>
			<?php } ?>

			<?php if ($od['od_memo']) { ?>
				<tr>
					<th scope="row">전하실 말씀</th>
					<td><?php echo conv_content($od['od_memo'], 0); ?></td>
				</tr>
			<?php } ?>
			</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading"><strong><i class="fa fa-truck fa-lg"></i> 배송정보</strong></div>
		<div class="table-responsive">
			<table class="div-table table bsk-tbl bg-white">
			<col width="120">
			<tbody>
			<?php if ($od['od_delivery_text'] || $od['od_delivery_price'] || $od['od_delivery_company']) {?>
				<?php if($od['od_delivery_company']) { ?>
				<tr>
					<th scope="row">배송회사</th>
					<td>
						<?php
						// echo $od['od_delivery_company'];
						$delivery = get_delivery_company_step($od['od_delivery_company']);
						echo $delivery['name'] ? $delivery['name'] : '';
						?>
						<?php echo get_delivery_inquiry($od['od_delivery_company'], $od['od_invoice'], 'dvr_link'); ?>
					</td>
				</tr>
				<?php } ?>
				<?php if($od['od_delivery_place']) { ?>
					<tr>
						<th scope="row">영업소</th>
						<td><?php echo $od['od_delivery_place']; ?></td>
					</tr>
				<?php } ?>
				<?php if($od['od_delivery_tel']) { ?>
					<tr>
						<th scope="row">전화번호</th>
						<td><?php echo $od['od_delivery_tel']; ?></td>
					</tr>
				<?php } ?>
				<?php if($od['od_delivery_text']) { ?>
				<tr>
					<th scope="row">운송장번호</th>
					<td>
						<?php echo $od['od_delivery_text']; ?>
						<?php if($od['od_delivery_company'] == "ilogen") { ?>
							<a href="https://www.ilogen.com/web/personal/trace/<?php echo $od['od_delivery_text']; ?>" target="_blank" class="btn_delivery">배송조회</a>
						<?php } elseif ($od['od_delivery_company'] == "cjlogistics") { ?>
							<a href="https://www.doortodoor.co.kr/parcel/doortodoor.do?fsp_action=PARC_ACT_002&fsp_cmd=retrieveInvNoACT&invc_no=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery">배송조회</a>
						<?php } elseif ($od['od_delivery_company'] == "kdexp") { ?>
							<a href="https://kdexp.com/basicNewDelivery.kd?barcode=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery">배송조회</a>
						<?php } elseif ($od['od_delivery_company'] == "ds3211") { ?>
							<a href="http://home.daesinlogistics.co.kr/daesin/jsp/d_freight_chase/d_general_process2.jsp?billno1=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery">배송조회</a>
						<?php } elseif ($od['od_delivery_company'] == "hdexp") { ?>
							<a href="http://www.deliverytracking.kr/?dummy=one&deliverytype=hdexp&keyword=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery">배송조회</a>
						<?php } elseif ($od['od_delivery_company'] == "lotteglogis") { ?>
							<a href="http://www.deliverytracking.kr/?dummy=one&deliverytype=lotteglogis&keyword=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery">배송조회</a>
						<?php } elseif ($od['od_delivery_company'] == "chunilps") { ?>
							<a href="http://www.cyber1001.co.kr/kor/taekbae/HTrace.jsp?transNo=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery">배송조회</a>
						<?php } ?>

					</td>
				</tr>
				<?php } ?>
				<?php if($od['od_delivery_price']) { ?>
				<!--
				<tr>
					<th scope="row">운임비</th>
					<td>
						<?php echo number_format($od['od_delivery_price']); ?>원
					</td>
				</tr>
				-->
				<?php } ?>
				<!--
				<tr>
					<th scope="row">배송일시</th>
					<td><?php echo $od['od_invoice_time']; ?></td>
				</tr>
				-->
			<?php }	else { ?>
				<tr>
					<td>아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</td>
				</tr>
			<?php }	?>
			</tbody>
			</table>
		</div>
	</div>
<?php } ?>

<div class="panel panel-primary">
	<div class="panel-heading"><strong><i class="fa fa-money fa-lg"></i> 결제합계</strong></div>
	<div class="table-responsive">
		<table class="div-table table bsk-tbl bg-white">
		<col width="120">
		<tbody>
		<tr>
			<th scope="row">총구매액</th>
			<td class="text-right"><strong><?php echo display_price($tot_price); ?></strong></td>
		</tr>
		<?php if ($misu_price > 0) { ?>
			<tr class="active">
				<th scope="row">미결제액</th>
				<td class="text-right"><strong><?php echo display_price($misu_price);?></strong></td>
			</tr>
		<?php } ?>
		<tr>
			<th scope="row" id="alrdy">결제금액</th>
			<td class="text-right"><strong><?php echo $wanbul; ?></strong></td>
		</tr>
		</tbody>
		</table>
	</div>
</div>

<?php if ($cancel_price == 0) { // 취소한 내역이 없다면 ?>
    <?php
    $type = 0;
    if ($custom_cancel)
        $type = 1;
    if ($pay_complete_cancel || $preparation_cancel)
        $type = 2;

    $btn_name = "주문 취소하기";
    $action_url = "./orderinquirycancel.php";
    $to = "";

    if ($pay_complete_cancel2 || $preparation_cancel) {
        $action_url = "./orderinquirycancelrequest.php";
        $btn_name = "취소 요청하기";
        $to = "cancel";
    }

    if ($shipped_cancel) {
        $action_url = "./orderinquirycancelrequest.php";
        $btn_name = "반품 요청하기";
        $to = "return";
    }

    $sql = "select *
            from g5_shop_order_cancel_request
            where od_id = '{$od['od_id']}' and approved = 0";

    $cancel_request_row = sql_fetch($sql);

    ?>
	<?php if (($custom_cancel || $pay_complete_cancel || $pay_complete_cancel2 || $preparation_cancel || $shipped_cancel) && !$cancel_request_row['od_id']) { ?>
		<div class="print-hide text-center">
			<button id="cancel_btn" type="button" data-toggle="collapse" href="#sod_fin_cancelfrm" aria-expanded="false" aria-controls="sod_fin_cancelfrm" class="btn btn-black btn-sm"><?php echo $btn_name ?></button>
		</div>

		<div class="h15"></div>

		<div id="sod_fin_cancelfrm" class="collapse">
			<div class="well">
				<form class="form" role="form" method="post" action="<?php echo $action_url ?>" onsubmit="return fcancel_check(this);">
				<input type="hidden" name="od_id"  value="<?php echo $od['od_id']; ?>">
				<input type="hidden" name="token"  value="<?php echo $token; ?>">
                <input type="hidden" name="type" value="<?php echo $type ?>">
                <input type="hidden" name="to" value="<?php echo $to ?>">
					<div class="input-group input-group-sm">
                        <!--<span class="input-group-addon">사유</span>-->
						<select name="request_reason_type" class="form-control" style="display: table-cell; width: 100px; margin-right: 10px;">
                            <option value="단순변심">단순변심</option>
                            <option value="제품파손">제품파손</option>
                            <option value="제품하자">제품하자</option>
                            <option value="오주문">오주문</option>
                            <option value="오배송">오배송</option>
                            <option value="A/S">A/S</option>
                            <option value="기타">기타</option>
                        </select>
						<input type="text" name="cancel_memo" id="cancel_memo" required class="form-control input-sm" size="40" maxlength="100" style="width: calc(100% - 110px); float: none;">
						<span class="input-group-btn">
							<button type="submit" class="btn btn-black btn-sm">확인</button>
						</span>
					</div>
				</form>
			</div>
		</div>
	<?php } ?>
    <?php if ($cancel_request_row['od_id']) {?>
        <div class="well text-center">
            <p><?php echo mb_substr($cancel_request_row['request_status'], 0, 2); ?> 요청되었습니다. 관리자의 승인을 기다리고 있습니다.</p>
            <p style="margin: 0">[<?php echo $cancel_request_row['request_reason_type'] ?>] <?php echo $cancel_request_row['request_reason'] ?></p>
        </div>
    <?php } ?>
<?php } else { ?>
	<div class="well text-center">주문 취소, 반품, 품절된 내역이 있습니다.</div>
<?php } ?>

<?php if ($is_account_test) { ?>
	<div class="alert alert-danger">
		관리자가 가상계좌 테스트를 한 경우에만 보입니다.
	</div>

	<form class="form" role="form" method="post" action="http://devadmin.kcp.co.kr/Modules/Noti/TEST_Vcnt_Noti_Proc.jsp" target="_blank">
		<div class="panel panel-default">
			<div class="panel-heading"><strong><i class="fa fa-cog fa-lg"></i> 모의입금처리</strong></div>
			<div class="table-responsive">
				<table class="div-table table bsk-tbl bg-white">
				<col width="120">
				<tbody>
				<tr>
					<th scope="col"><label for="e_trade_no">KCP 거래번호</label></th>
					<td><input type="text" name="e_trade_no" value="<?php echo $od['od_tno']; ?>" class="form-control input-sm"></td>
				</tr>
				<tr>
					<th scope="col"><label for="deposit_no">입금계좌</label></th>
					<td><input type="text" name="deposit_no" value="<?php echo $deposit_no; ?>" class="form-control input-sm"></td>
				</tr>
				<tr>
					<th scope="col"><label for="req_name">입금자명</label></th>
					<td><input type="text" name="req_name" value="<?php echo $od['od_deposit_name']; ?>" class="form-control input-sm"></td>
				</tr>
				<tr>
					<th scope="col"><label for="noti_url">입금통보 URL</label></th>
					<td><input type="text" name="noti_url" value="<?php echo G5_SHOP_URL; ?>/settle_kcp_common.php" class="form-control input-sm"></td>
				</tr>
				</tbody>
				</table>
			</div>
		</div>
		<div id="sod_fin_test" class="text-center">
			<input type="submit" value="입금통보 테스트" class="btn btn-color btn-sm">
		</div>
	</form>
<?php } ?>

<div id="send_statementBox">
	<div>

		<iframe src="<?php echo G5_URL; ?>/shop/pop.statement.php?&od_id=<?=$_GET["od_id"]?>"></iframe>

	</div>
</div>

<style>

	#send_statementBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
	#send_statementBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
	#send_statementBox iframe { position: relative; width: 730px; height: 800px; border: 0; background-color: #FFF; left: 50%; margin-left: -365px; }

	@media (max-width : 750px){
		#send_statementBox iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
	}
</style>

<p class="print-hide text-center">
	<a class="btn btn-color btn-sm" href="./orderinquiry.php"><i class="fa fa-bars"></i> 목록으로</a>
	<button type="button" id="send_statement" class="btn btn-blue btn-sm"><i class="fa fa-print"></i> 거래명세서출력</button>
	<button type="button" onclick="apms_print();" class="btn btn-black btn-sm"><i class="fa fa-print"></i> 프린트</button>
	<?php if($setup_href) { ?>
		<a class="btn btn-color btn-sm win_memo" href="<?php echo $setup_href;?>">
			<i class="fa fa-cogs"></i> 스킨설정
		</a>
	<?php } ?>
</p>

<script>
function fcancel_check(f) {
    var btn_text = $('#cancel_btn').text();
    var strArray = btn_text.split('하기');

    if(!confirm(strArray[0] + " 하시겠습니까?"))
        return false;

    var memo = f.cancel_memo.value;
    if(memo == "") {
        alert("사유를 입력해 주십시오.");
        return false;
    }

    return true;
}

$(function(){
	$(".delivery-confirm").click(function(){
		if(confirm("상품을 수령하셨습니까?\n\n확인시 배송완료 처리가됩니다.")) {
			return true;
		}
		return false;
	});

	// 거래명세서 출력
	$("#send_statementBox").hide();
	$("#send_statementBox").css("opacity", 1);
	$("#send_statement").click(function() {
		$("#send_statementBox").show();
	});
	
	/* 바코드저장 */
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
	
	$("#prodBarNumSaveBtn").click(function(){
		var ordId = "<?=$od["ordId"]?>";
		var eformYn = "<?=$od["eformYn"]?>";

		if(ordId){
			var productList = <?=($prodList) ? json_encode($prodList) : "[]"?>;
			$.each(productList, function(key, value){
				var prodBarNumItem = $(".prodBarNumItem_" + value.penStaSeq);
				var prodBarNum = "";
				
				for(var i = 0; i < prodBarNumItem.length; i++){
					if("<?=$od["od_status"]?>" == "완료"){
						if(!$(prodBarNumItem[i]).val()){
							alert("바코드를 입력해주시길 바랍니다.");
							return false;
						}
					}
					prodBarNum += (prodBarNum) ? "," : "";
					prodBarNum += $(prodBarNumItem[i]).val();
				}
				
				productList[key]["prodBarNum"] = prodBarNum;
			});
			
			var sendData = {
				ordId : "<?=$od["ordId"]?>",
				delGbnCd : "",
				ordWayNum : "",
				delSerCd : "",
				ordNm : $("#od_b_name").val(),
				ordCont : $("#od_b_hp").val(),
				ordMeno : $("#od_memo").val(),
				ordZip : $("#od_b_zip").val(),
				ordAddr : $("#od_b_addr1").val(),
				ordAddrDtl : $("#od_b_addr2").val(),
				eformYn : eformYn,
				staOrdCd : "<?=$od["staOrdCd"]?>",
				prods : productList
			}

			$.ajax({
				url : "https://eroumcare.com/api/pen/pen5000/pen5000/updatePen5000.do",
				type : "POST",
				dataType : "json",
				contentType : "application/json; charset=utf-8;",
				data : JSON.stringify(sendData),
				success : function(result){
					if(result.errorYN == "N"){
						alert("저장이 완료되었습니다.");
					} else {
						alert(result.message);
					}
				}
			});
		} else {
			var delYn = "Y";
			var changeStatus = true;
			if("<?=$od["od_status"]?>" == "완료"){
				delYn = "N";
				$.each(stoldList, function(key, value){
					if(!$("." + value.stoId).val()){
						changeStatus = false;
						alert("바코드를 입력해주시길 바랍니다.");
						return false;
					}
				});
			}
			
			$.each(stoldList, function(key, value){
				var sendData = {
					usrId : "<?=$od["mb_id"]?>",
					prods : [
						{
							stoId : value.stoId,
							prodColor : value.prodColor,
							prodBarNum : ($("." + value.stoId).val()) ? $("." + value.stoId).val() : "",
							prodManuDate : value.prodManuDate,
							stateCd : value.stateCd,
							stoMemo : (value.stoMemo) ? value.stoMemo : "",
							delYn : delYn
						}
					]
				}

				$.ajax({
					url : "https://eroumcare.com/api/pro/pro2000/pro2000/updatePro2000ProdInfoAjaxByShop.do",
					type : "POST",
					dataType : "json",
					async : false,
					contentType : "application/json; charset=utf-8;",
					data : JSON.stringify(sendData),
					success : function(result){
						if(result.errorYN == "Y"){
							alert(result.message);
							return false;
						}
					}
				});
			});
			
			alert("저장이 완료되었습니다.");
		}
	});
	
});
</script>
