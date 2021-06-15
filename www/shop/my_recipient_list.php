<?php

	include_once("./_common.php");
	include_once("./_head.php");

	if(!$is_member){
		alert("접근 권한이 없습니다.");
		exit;
	}

	// $sql_common = " FROM pen1000 ";

	// $sql_search = "	WHERE 
	// 		ENT_ID = '{$member['mb_entId']}' 
	// 	AND ENT_USR_ID = '{$member['mb_id']}'
	// 	AND DEL_YN = 'N' ";

	// $sql_order = " ORDER BY PEN_ID DESC ";

	// $sql = "SELECT count(*) as cnt
	// 	{$sql_common}
	// 	{$sql_search}
	// 	{$sql_order} 
	// ";

	// $row = sql_fetch($sql, false, $g5['sys_connect_db']);
	// $total_count = $row['cnt'];
	// $rows = $config['cf_page_rows'];
	// $total_page  = ceil($total_count / $rows);
	// if ($page < 1) $page = 1;
	// $from_record = ($page - 1) * $rows;
	
	// $sql = "SELECT * 
	// 	{$sql_common}
	// 	{$sql_search}
	// 	{$sql_order} 
	// ";

	// $query = sql_query($sql . " LIMIT {$from_record}, {$rows} ", false, $g5['sys_connect_db']);

	// $list = [];
	// while($data = sql_fetch_array($query)) {
	// 	$list[] = $data;
	// }

	$rows = 15;
	$page = $_GET["page"] ?? 1;

	$send_data = [];
	$send_data["usrId"] = $member["mb_id"];
	$send_data["entId"] = $member["mb_entId"];
	$send_data["pageNum"] = $page;
	$send_data["pageSize"] = $rows;
	if ($sel_field === 'penNm') {
		$send_data['penNm'] = $search;
	}
	if ($sel_field === 'penLtmNum') {
		$send_data['penLtmNum'] = $search;
	}
	if ($sel_field === 'penProNm') {
		$send_data['penProNm'] = $search;
	}

	$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);

	$list = [];
	if($res["data"]){
		$list = $res["data"];
	}


	# 페이징
	$total_count = $res["total"];
	$pageNum = $page; # 페이지 번호
	$listCnt = $rows; # 리스트 갯수 default 15

	$b_pageNum_listCnt = 5; # 한 블록에 보여줄 페이지 갯수 5개
	$block = ceil($pageNum/$b_pageNum_listCnt); # 총 블록 갯수 구하기
	$b_start_page = ( ($block - 1) * $b_pageNum_listCnt ) + 1; # 블록 시작 페이지
	$b_end_page = $b_start_page + $b_pageNum_listCnt - 1;  # 블록 종료 페이지
	$total_page = ceil( $total_count / $listCnt ); # 총 페이지
	// 총 페이지 보다 블럭 수가 만을경우 블록의 마지막 페이지를 총 페이지로 변경
	if ($b_end_page > $total_page){
		$b_end_page = $total_page;
	}
	$total_block = ceil($total_page/$b_pageNum_listCnt);

?>
<script>
   function excelform(url){
       var opt = "width=600,height=450,left=10,top=10";
        window.open(url, "win_excel", opt);
        return false;
    }
</script>

<!-- 수급자 일괄등록  -->

	<!-- 210204 수급자목록 -->
	<div id="myRecipientListWrap">
		<div class="titleWrap" style="margin-bottom:10px;">
			수급자 목록
		</div>

		<form id="form_search" method="get">
			<div class="search_box">
				<select name="sel_field" id="sel_field">
					<option value="penNm"<?php if($sel_field == 'penNm' || $sel_field == 'all') echo ' selected'; ?>>수급자명</option>
					<option value="penProNm"<?php if($sel_field == 'penProNm') echo ' selected'; ?>>보호자명</option>
					<option value="penLtmNum"<?php if($sel_field == 'penLtmNum') echo ' selected'; ?>>장기요양번호</option>
				</select>
				<div class="input_search">
						<input name="search" id="search" value="<?=$search?>" type="text">
						<button id="btn_search" type="submit"></button>
				</div>
			</div>
			<div class="r_btn_area pc">
				<a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">수급자 등록</a>
				<a href="./recipientexcel.php" onclick="return excelform(this.href);" target="_blank" class="btn eroumcare_btn2" title="수급자일괄등록">수급자일괄등록</a>
			</div>
		</form>

		<div class="list_box pc">
			<div class="table_box">	
				<table >
					<tr>
						<th>No.</th>
						<th>수급자 정보</th>
						<th>장기요양정보</th>
						<th>1년사용</th>
						<th>장바구니</th>
						<th>비고</th>
					</tr>
					<?php $i = -1; ?>
					<?php foreach($list as $data){ ?>
					<?php $i++; ?>
					<tr>
						<td>
							<?php echo $total_count - (($page - 1) * $rows) - $i; ?>
						</td>
						<td>
							<a href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId']; ?>'>
								<?php echo $data['penNm']; ?>
								(<?php echo substr($data['penBirth'], 2, 2); ?>년생/<?php echo $data['penGender']; ?>)
								<br/>
								<?php if ($data['penProNm']) { ?>
									보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProConNum'] ? '/' . $data['penProConNum'] : ''; ?>)
								<?php } ?>
							</a>
						</td>
						<td>
							<?php if ($data["penLtmNum"]) { ?>
								<?php echo $data["penLtmNum"]; ?>
								(<?php echo $data["penRecGraNm"]; ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
								<br/>
								<?php echo $data['penExpiDtm']; ?>
							<?php }else{ ?>
								예비수급자
							<?php } ?>
						</td>
						<td style="text-align:center;">
							<?php

							// 유효기간
							$exp_date = substr($data['penExpiStDtm'], 4, 4);
							$exp_now = date('m') . date('d');
							$exp_year = intval($exp_date) < intval($exp_now) ? intval(date('Y')) : intval(date('Y')) - 1; // 지금날짜보다 크면 올해, 작으면 작년

							$exp_start = date('Y-m-d', strtotime($exp_year . $exp_date));
							$exp_end = date('Y-m-d', strtotime('+ 1 years', strtotime($exp_start)));

							// $count = sql_fetch("SELECT COUNT(*) AS cnt FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2')")['cnt'];

							// 계약건수, 금액
							$contract = sql_fetch("SELECT count(*) as cnt, SUM(it_price) as sum_it_price from eform_document_item edi where edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
							// 판매 건수
							$contract_sell = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '00' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
							// 대여 건수
							$contract_borrow = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '01' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
							
							?>
							<span class="<?php echo $contract['sum_it_price'] > 1400000 ? 'red' : ''; ?>"><?php echo number_format($contract['sum_it_price']); ?>원</span>
							<br/>
							계약 <?php echo $contract['cnt']; ?>건, 판매 <?php echo $contract_sell['cnt']; ?>건, 대여 <?php echo $contract_borrow['cnt']; ?>건
						</td>
						<td style="text-align:center;">
							<?php
								$cart_count = get_carts_by_recipient($data['penId']);
								echo $cart_count . '개';
							?>
							<br/>
							<?php if ($data["penLtmNum"]) { ?>
							<a href="<?php echo G5_SHOP_URL; ?>/connect_recipient.php?pen_id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="추가하기">추가하기</a>
							<?php } ?>
						</td>
						<td style="text-align:center;">
							<?php if ($data['recYn'] === 'N') { ?>
								욕구사정기록지 미작성<br/>
								<a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="작성하기">작성하기</a>
							<?php } ?>
						</td>
					</tr>
					<?php } ?>
					</table>
			</div>
		</div>

		<?php if(!$list){ ?>
        <style>
            .no_content{
                width:100%; height:100px; text-align:center;margin-top:150px;
            }
        </style>
        <div class="no_content">
            내용이 없습니다
        </div>
        <?php } ?>

		<?php if($list){ ?>
			<div class="list_box mobile">
				<ul class="li_box">
					<?php foreach ($list as $data) { ?>
						<li>
							<div class="info">
								<a href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId']; ?>'>
									<b>
									<?php echo $data['penNm']; ?>
									(<?php echo substr($data['penBirth'], 2, 2); ?>년생/<?php echo $data['penGender']; ?>)
									</b>
									<?php if ($data['penProNm']) { ?>
										<span class="li_box_protector">
										* 보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProTypeCd'] == '00' ? '/없음' : ''; ?><?php echo $data['penProTypeCd'] == '01' ? '/일반보호자' : ''; ?><?php echo $data['penProTypeCd'] == '02' ? '/요양보호사' : ''; ?>)
										</span>
									<?php } ?>
									<p>
										<?php if ($data["penLtmNum"]) { ?>
											<b>
												<?php echo $data["penLtmNum"]; ?>
												(<?php echo $data["penRecGraNm"]; ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
											</b>
										<?php }else{ ?>
											예비수급자
										<?php } ?>
									</p>
									<p>
										<b>
											1년사용: 
											<?php
											// 유효기간
											$exp_date = substr($data['penExpiStDtm'], 4, 4);
											$exp_now = date('m') . date('d');
											$exp_year = intval($exp_date) < intval($exp_now) ? intval(date('Y')) : intval(date('Y')) - 1; // 지금날짜보다 크면 올해, 작으면 작년

											$exp_start = date('Y-m-d', strtotime($exp_year . $exp_date));
											$exp_end = date('Y-m-d', strtotime('+ 1 years', strtotime($exp_start)));

											// $count = sql_fetch("SELECT COUNT(*) AS cnt FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2')")['cnt'];

											// 계약건수, 금액
											$contract = sql_fetch("SELECT count(*) as cnt, SUM(it_price) as sum_it_price from eform_document_item edi where edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
											// 판매 건수
											$contract_sell = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '00' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
											// 대여 건수
											$contract_borrow = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '01' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");

											?>
											<span class="<?php echo $contract['sum_it_price'] > 1400000 ? 'red' : ''; ?>"><?php echo number_format($contract['sum_it_price']); ?>원</span>
										</b>
										<span style="font-size:0.9em;">
											계약 <?php echo $contract['cnt']; ?>건, 판매 <?php echo $contract_sell['cnt']; ?>건, 대여 <?php echo $contract_borrow['cnt']; ?>건
										</span>
									</p>
								</a>
								<?php if ($data['recYn'] === 'N') { ?>
									<a href="#" class="btn eroumcare_btn2" style="margin-top:10px;" title="작성하기">욕구사정기록지 작성</a>
								<?php } ?>
							</div>
							<?php if ($data["penLtmNum"]) { ?>
							<a href="<?php echo G5_SHOP_URL; ?>/connect_recipient.php?pen_id=<?php echo $data['penId']; ?>" class="li_box_right_btn" title="추가하기">
								장바구니
								<br/>
								<b><?php echo get_carts_by_recipient($data['penId']) . '개'; ?></b>
							</a>
							<?php } ?>
						</li>
					<?php } ?>
				</ul>
			</div>
		<?php } ?>

		<!--
		<div class="itemWrap">
		<?php if($list){ ?>
			<?php foreach($list as $data){ ?>
				<ul class="item">
					<li class="btnWrap">
						<button type="button" class="updateBtn" data-id="<?=$data["penId"]?>">수정</button><button type="button" class="delBtn" data-id="<?=$data["penId"]?>">삭제</button>
					</li>
					<li class="info">
						<p class="labelName">수급자명</p>
						<p class="value"><?=($data["penNm"]) ? $data["penNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">장기요양인정번호</p>
						<p class="value"><?=($data["penLtmNum"]) ? $data["penLtmNum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">본인부담금율</p>
						<p class="value"><?=($data["penTypeNm"]) ? $data["penTypeNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">등급</p>
						<p class="value"><?=($data["penRecGraNm"]) ? $data["penRecGraNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">유효기간</p>
						<p class="value"><?=($data["penExpiDtm"]) ? $data["penExpiDtm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">휴대폰</p>
						<p class="value"><?=($data["penConNum"]) ? $data["penConNum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">일반전화</p>
						<p class="value"><?=($data["penConPnum"]) ? $data["penConPnum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">보호자명</p>
						<p class="value"><?=($data["penProNm"]) ? $data["penProNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">보호자연락처</p>
						<p class="value"><?=($data["penProConNum"]) ? $data["penProConNum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">담당자</p>
						<p class="value"><?=($data["usrNm"]) ? $data["usrNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">처리현황</p>
						<p class="value"><?=($data["appCdNm"]) ? $data["appCdNm"] : "-"?></p>
					</li>
				</ul>
			<?php } ?>
		<?php } ?>
		</div>
		-->

		<div class="list-page list-paging">
			<ul class="pagination pagination-sm en">
				<li></li>
			<?php if($block > 1){ ?>
				<li><a href="?page=<?=($b_start_page-1)?>&sel_field=<?php echo $sel_field; ?>&search=<?php echo $search; ?>"><i class="fa fa-angle-left"></i></a></li>
			<?php } ?>

			<?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?>
				<li class="<?=($j == $pageNum) ? "active" : ""?>">
					<a href="?page=<?=$j?>&sel_field=<?php echo $sel_field; ?>&search=<?php echo $search; ?>"><?=$j?></a>
				</li>
			<?php } ?>
			<?php if($block < $total_block){ ?>
				<li><a href="?page=<?=($b_end_page+1)?>&sel_field=<?php echo $sel_field; ?>&search=<?php echo $search; ?>"><i class="fa fa-angle-right"></i></a></li>
			<?php } ?>
				<li></li>
			</ul>
		</div>
	</div>

	<script type="text/javascript">
		$(function(){

			$("#myRecipientListWrap > .moreBtnWrap > button").click(function(){
				var page = Number($(this).attr("data-page")) + 1;
				var sendData = {
					usrId : "<?=$member["mb_id"]?>",
					entId : "<?=$member["mb_entId"]?>",
					pageNum : page,
					pageSize : "<?=$rows?>"
				}

				$.ajax({
					url : "./ajax.my.recipient.list.php",
					type : "POST",
					async : false,
					data : sendData,
					success : function(result){
						result = JSON.parse(result);
						if(result.errorYN == "Y"){
							alert(result.message);
						} else {
							var list = result.data;

							if(!list.length){
								alert("데이터가 존재하지 않습니다.");
							} else {
								$.each(list, function(key, data){
									var html = '';

									html += '<ul class="item">';
									html += '<li class="btnWrap">';
									html += '<button type="button" class="updateBtn" data-id="' + data.penId + '">수정</button>';
									html += '<button type="button" class="delBtn" data-id="' + data.penId + '">삭제</button>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">수급자명</p>';
									html += '<p class="value">' + ((data.penNm) ? data.penNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">장기요양인정번호</p>';
									html += '<p class="value">' + ((data.penLtmNum) ? data.penLtmNum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">본인부담금율</p>';
									html += '<p class="value">' + ((data.penTypeNm) ? data.penTypeNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">등급</p>';
									html += '<p class="value">' + ((data.penRecGraNm) ? data.penRecGraNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">유효기간</p>';
									html += '<p class="value">' + ((data.penExpiDtm) ? data.penExpiDtm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">휴대폰</p>';
									html += '<p class="value">' + ((data.penConNum) ? data.penConNum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">일반전화</p>';
									html += '<p class="value">' + ((data.penConPnum) ? data.penConPnum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">보호자명</p>';
									html += '<p class="value">' + ((data.penProNm) ? data.penProNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">보호자연락처</p>';
									html += '<p class="value">' + ((data.penProConNum) ? data.penProConNum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">담당자</p>';
									html += '<p class="value">' + ((data.usrNm) ? data.usrNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">처리현황</p>';
									html += '<p class="value">' + ((data.appCdNm) ? data.appCdNm : "-") + '</p>';
									html += '</li>';
									html += '</ul>';

									$("#myRecipientListWrap > .itemWrap").append(html);
								});

								$("#myRecipientListWrap > .moreBtnWrap > button").attr("data-page", page);
							}
						}
					}
				});
			});

			$(document).on("click", "#myRecipientListWrap > .itemWrap > .item > li.btnWrap > .updateBtn", function(){
				var id = $(this).attr("data-id");

				window.location.href = "./my_recipient_update.php?id=" + id;
			});

			$(document).on("click", "#myRecipientListWrap > .itemWrap > .item > li.btnWrap > .delBtn", function(){
				var id = $(this).attr("data-id");

				if(confirm("해당 데이터를 삭제하시겠습니까?")){
					$.ajax({
						url : "./ajax.my.recipient.delete.php",
						type : "POST",
						async : false,
						data : {
							id : id
						},
						success : function(result){
							result = JSON.parse(result);

							if(result.errorYN == "Y"){
								alert(result.message);
							} else {
								window.location.reload();
							}
						}
					});
				}
			});

		})
	</script>

<?php include_once("./_tail.php"); ?>
