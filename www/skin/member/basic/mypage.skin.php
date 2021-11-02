<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

?>

<div class="mypage-skin vertical-spacer">
	<!-- <div class="panel panel-default view-author">
		<div class="panel-heading">
			<h3 class="panel-title">My Profile</h3>
		</div>
		<div class="panel-body">
			<div class="pull-left text-center auth-photo">
				<div class="img-photo">
					<?php echo ($member['photo']) ? '<img src="'.$member['photo'].'" alt="">' : '<i class="fa fa-user"></i>'; ?>
				</div>
				<div class="btn-group" style="margin-top:-30px;white-space:nowrap;">
					<button type="button" class="btn btn-color btn-sm" onclick="apms_like('<?php echo $member['mb_id'];?>', 'like', 'it_like'); return false;" title="Like">
						<i class="fa fa-thumbs-up"></i> <span id="it_like"><?php echo number_format($member['liked']) ?></span>
					</button>
					<button type="button" class="btn btn-color btn-sm" onclick="apms_like('<?php echo $member['mb_id'];?>', 'follow', 'it_follow'); return false;" title="Follow">
						<i class="fa fa-users"></i> <span id="it_follow"><?php echo $member['followed']; ?></span>
					</button>
				</div>
			</div>
			<div class="auth-info">
				<div class="en font-14" style="margin-bottom:6px;">
					<span class="pull-right font-12">Lv.<?php echo $member['level'];?></span>
					<b><?php echo $member['name']; ?></b> &nbsp;<span class="text-muted en font-12"><?php echo $member['grade'];?></span>
				</div>
				<div class="div-progress progress progress-striped no-margin">
					<div class="progress-bar progress-bar-exp" role="progressbar" aria-valuenow="<?php echo round($member['exp_per']);?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($member['exp_per']);?>%;">
						<span class="sr-only"><?php echo number_format($member['exp']);?> (<?php echo $member['exp_per'];?>%)</span>
					</div>
				</div>
				<p style="margin-top:6px;">
					<?php echo ($mb_signature) ? $mb_signature : '등록된 서명이 없습니다.'; ?>
				</p>
			</div>
			<div class="clearfix"></div>
		</div>
	</div> -->

	<div class="row">
		<div class="col-sm-7">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">내 정보</h3>
				</div>
				<ul class="list-group">
					<li class="list-group-item">
						<?php echo samhwa_xp_icon(); ?>
						<?php echo get_text($member['mb_name']); ?>
					</li>
					<!-- 포인트 기능 삭제
					<li class="list-group-item">
						<a href="<?php echo $at_href['point'];?>" target="_blank" class="win_point">
							<span class="pull-right"><?php echo number_format($member['mb_point']); ?>점</span>
							<?php echo AS_MP;?>
						</a>
					</li> -->
					<?php if(IS_YC) { ?>
						<li class="list-group-item">
							<a href="<?php echo $at_href['coupon'];?>" target="_blank" class="win_point">
								<span class="pull-right"><?php echo number_format($cp_count); ?></span>
								보유쿠폰
							</a>
						</li>
					<?php } ?>
					<li class="list-group-item">
						<span class="pull-right"><?php echo ($member['mb_tel'] ? $member['mb_tel'] : '미등록'); ?></span>
						연락처
					</li>
					<li class="list-group-item">
						<span class="pull-right"><?php echo ($member['mb_email'] ? $member['mb_email'] : '미등록'); ?></span>
						E-Mail
					</li>
					<!-- <li class="list-group-item">
						<span class="pull-right"><?php echo $member['mb_today_login']; ?></span>
						최종접속일
					</li>
					<li class="list-group-item">
						<span class="pull-right"><?php echo $member['mb_datetime']; ?></span>
						회원가입일
					</li> -->
					<?php if($member['mb_addr1']) { ?>
						<li class="list-group-item">
							<?php echo sprintf("(%s-%s)", $member['mb_zip1'], $member['mb_zip2']).' '.print_address($member['mb_addr1'], $member['mb_addr2'], $member['mb_addr3'], $member['mb_addr_jibeon']); ?>
						</li>
					<?php } ?>
				</ul>
				<?php if($member['mb_profile']) { ?>
					<div class="panel-body">
						<?php echo conv_content($member['mb_profile'],0);?>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="col-sm-5">
			<div class="row">
				<?php if ($is_admin == 'super') { ?>
					<div class="col-xs-6">
						<div class="form-group">
							<a href="<?php echo G5_ADMIN_URL; ?>" class="btn btn-lightgray btn-sm btn-block">관리자</a>
						</div>
					</div>
				<?php } ?>
				<?php if (IS_YC && ($is_admin == 'super' || IS_PARTNER)) { ?>
					<div class="col-xs-6">
						<div class="form-group">
							<a href="<?php echo $at_href['myshop'];?>" class="btn btn-lightgray btn-sm btn-block">
								마이샵
							</a>		
						</div>
					</div>
				<?php } ?>
				<!-- <div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['response'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_memo">
							내글반응
							<?php if ($member['response']) echo '('.number_format($member['response']).')'; ?>
						</a>		
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['memo'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_memo">
							쪽지함
							<?php if ($member['memo']) echo '('.number_format($member['memo']).')'; ?>
						</a>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['follow'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_memo">
							팔로우
						</a>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['scrap'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_scrap">
							스크랩
						</a>
					</div>
				</div> -->
				<?php if(IS_YC) { ?>
					<div class="col-xs-6">
						<div class="form-group">
							<a href="<?php echo $at_href['coupon'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_point">
								마이쿠폰
							</a>
						</div>
					</div>
					<!-- <div class="col-xs-6">
						<div class="form-group">
							<a href="<?php echo $at_href['shopping'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_memo">
								쇼핑리스트
							</a>
						</div>
					</div> -->
					<div class="col-xs-6">
						<div class="form-group">
							<a href="<?php echo $at_href['wishlist'];?>" class="btn btn-lightgray btn-sm btn-block">
								취급상품
							</a>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<a href="<?php echo G5_URL; ?>/shop/orderinquiry.php" class="btn btn-lightgray btn-sm btn-block">
								주문내역
							</a>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<a href="<?php echo G5_URL; ?>/shop/cart.php" class="btn btn-lightgray btn-sm btn-block">
								장바구니
							</a>
						</div>
					</div>
				<?php } ?>
				<!-- <div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['mypost'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_memo">
							내글관리
						</a>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['myphoto'];?>" target="_blank" class="btn btn-lightgray btn-sm btn-block win_memo">
							사진등록
						</a>
					</div>
				</div> -->
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo G5_URL; ?>/shop/my_ledger_list.php" class="btn btn-lightgray btn-sm btn-block">
							거래처원장
						</a>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo G5_URL; ?>/bbs/qalist.php" class="btn btn-lightgray btn-sm btn-block">
							1:1문의 게시판
						</a>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo G5_URL; ?>/shop/my_data_upload.php" class="btn btn-lightgray btn-sm btn-block">
							과거공단자료 업로드
						</a>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['edit'];?>" class="btn btn-lightgray btn-sm btn-block">
							정보수정
						</a>
					</div>
				</div>
				<?php 
				$tutorials = get_tutorials(); 
				if ($tutorials['completed_count'] >= 4) {
				?>
				<div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo G5_URL; ?>/shop/tutorial_reset.php" class="btn btn-lightgray btn-sm btn-block">
							서비스 다시체험
						</a>
					</div>
				</div>
				<?php } ?>
				<!-- <div class="col-xs-6">
					<div class="form-group">
						<?php if ( $member['mb_type'] == 'partner' ) { ?>
						<a href="/shop/partner_excel.php" class="btn btn-lightgray btn-sm btn-block">제품상세페이지 다운로드</a>
						<?php } ?>
					</div>
				</div> -->
				<!-- <div class="col-xs-6">
					<div class="form-group">
						<a href="<?php echo $at_href['leave'];?>" class="btn btn-lightgray btn-sm btn-block leave-me">
							탈퇴하기
						</a>
					</div>
				</div> -->
			</div>
		</div>
	</div>

	<?php if(IS_YC) { // 영카트 ?>
		<br>
		<!-- 최근 주문내역 시작 { -->
		<!-- <section>
			<h4>최근 주문내역</h4>
			<?php
				// 최근 주문내역
				$sql = " select o.*, i.it_model, i.it_name, c.ct_qty from {$g5['g5_shop_order_table']} as o 
					LEFT JOIN g5_shop_cart as c ON o.od_id = c.od_id
					LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
					where o.mb_id = '{$member['mb_id']}' AND o.od_del_yn = 'N' order by o.od_id desc limit 0, 5 ";
			    $result = sql_query($sql);
			?>
			<div class="table-responsive">
				<table class="table mypage-tbl">			
				<thead>
				<tr>
					<th scope="col">주문서번호</th>
					<th scope="col">상품정보</th>
					<th scope="col">수령인</th>
					<th scope="col">배송정보</th>
					<th scope="col">주문일시</th>
					<th scope="col">상품수</th>
					<th scope="col">주문금액</th>
					<th scope="col">입금액</th>
					<th scope="col">미입금액</th>
					<th scope="col">상태</th>
				</tr>
				</thead>
			    <tbody>
			    <?php 
				for ($i=0; $row=sql_fetch_array($result); $i++) {
			        $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
		
					// switch($row['od_status']) {
					// 	case '주문' : $od_status = '입금확인중'; break;
					// 	case '입금' : $od_status = '입금완료'; break;
					// 	case '준비' : $od_status = '상품준비중'; break;
					// 	case '배송' : $od_status = '상품배송'; break;
					// 	case '완료' : $od_status = '배송완료'; break;
					// 	default		: $od_status = '주문취소'; break;
					// }
					$od_status = get_step($row['od_status']);
			    ?>
					<tr>
						<td>
							<input type="hidden" name="ct_id[<?php echo $i; ?>]" value="<?php echo $row['ct_id']; ?>">
							<a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>"><?php echo $row['od_id']; ?></a>
						</td>
						<td><?php echo $row['it_model']; ?><br> <?php echo $row['it_name'] ? '(' . $row['it_name'] . ')' : ''; ?></td>
						<td><?php echo $row['od_b_name']; ?></td>
						<td><?php echo show_delivery_info($row); ?></td>
						<td><?php echo substr($row['od_time'],2,14); ?> (<?php echo get_yoil($row['od_time']); ?>)</td>
						<td><?php echo $row['ct_qty']; ?></td>
						<td><?php echo display_price($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?></td>
						<td><?php echo display_price($row['od_receipt_price']); ?></td>
						<td><?php echo display_price($row['od_misu']); ?></td>
						<td><?php echo $od_status['name']; ?></td>
					</tr>
			    <?php } ?>
				<?php if ($i == 0) { ?>
					<tr><td colspan="10" class="empty_table">주문 내역이 없습니다.</td></tr>
				<?php } ?>
			    </tbody>
			    </table>
			</div>
			<p class="text-right">
				<a href="<?php echo G5_URL; ?>/shop/orderinquiry.php"><i class="fa fa-arrow-right"></i> 주문내역 더보기</a>
			</p>
		</section> -->
		<!-- } 최근 주문내역 끝 -->

        <!-- <section>
			<h4>과거 주문내역</h4>

			<?php
				// 최근 주문내역
			    $sql = "SELECT `fo`.*, `foi`.`goods_count` FROM `fm_order` AS `fo` 
                            LEFT JOIN (SELECT `order_seq`, COUNT(`order_seq`) AS `goods_count` FROM `fm_order_item` GROUP BY `order_seq`) AS `foi`
                                ON `foi`.`order_seq` = `fo`.`order_seq`
                            LEFT JOIN `fm_member` AS `fm`
                                ON `fm`.`member_seq` = `fo`.`member_seq`
                            where `fm`.`userid` = '{$member['mb_id']}' 
                            ORDER BY `fo`.`order_seq` DESC LIMIT 0, 5 ";
			    $result = sql_query($sql);
			?>
			<div class="table-responsive">
				<table class="table mypage-tbl">			
				<thead>
				<tr>
					<th scope="col">주문서번호</th>
					<th scope="col">주문일시</th>
					<th scope="col">상품수</th>
					<th scope="col">주문금액</th>
					<th scope="col">배송비</th>
					<th scope="col">결제여부</th>
				</tr>
				</thead>
			    <tbody>
			    <?php for ($i=0; $row=sql_fetch_array($result); $i++) { ?>
					<tr>
						<td>
							<input type="hidden" name="ct_id[<?php echo $i; ?>]" value="<?php echo $row['ct_id']; ?>">
							<a href="<?php echo G5_SHOP_URL; ?>/pastorderinquiryview.php?seq=<?php echo $row['order_seq']; ?>"><?php echo $row['order_seq']; ?></a>
						</td>
						<td><?php echo substr($row['regist_date'],2,14); ?> (<?php echo get_yoil($row['regist_date']); ?>)</td>
						<td><?php echo $row['goods_count']; ?></td>
						<td><?php echo display_price($row['settleprice']); ?></td>
						<td><?php echo display_price($row['shipping_cost']); ?></td>
						<td><?php echo ($row["deposit_yn"] == "y")?"<b>결제</b>":"미결제"; ?></td>
					</tr>
			    <?php } ?>
				<?php if ($i == 0) { ?>
					<tr><td colspan="7" class="empty_table">주문 내역이 없습니다.</td></tr>
				<?php } ?>
			    </tbody>
			    </table>
			</div>
			<p class="text-right">
				<a href="<?php echo G5_URL; ?>/shop/pastorderinquiry.php"><i class="fa fa-arrow-right"></i> 과거주문내역 더보기</a>
			</p>
		</section> -->
	<?php } ?>
</div>