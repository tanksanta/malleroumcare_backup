<?php

	if(!defined("_GNUBOARD_")) exit;

?>
	
	<link rel="stylesheet" href="<?php echo G5_URL; ?>/css/swiper.min.css">
	<script src="<?php echo G5_URL; ?>/js/swiper.min.js"></script>

	<!-- 메인 상단 슬라이드 -->
	<div id="mainTopSlidePCWrap">
		<div class="listWrap">
		<?php foreach($head_category as $cate) { ?>
			<ul>
				<li class="mainMenu">
					<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo $cate['ca_name']; ?><i class="fa fa-angle-right"></i></a>
				</li>
				<?php foreach($cate['sub'] as $i=>$sub) { ?>
					<li><a href='<?php echo G5_SHOP_URL . '/list.php?ca_id='.substr($sub['ca_id'], 0, 2).'&ca_sub%5B%5D='.substr($sub['ca_id'], 2, 2); ?>' class='cate_02 <?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub['ca_name']; ?></a></li>
					<?php if (!empty($sub['sub'])) { ?>
						<?php foreach($sub['sub'] as $sub2) { ?>
							<li><a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub2['ca_id']; ?>' class='cate_03 <?php echo $sub2['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub2['ca_name']; ?></a></li>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</ul>
		<?php } ?>
		</div>
		
		<div class="viewWrap swiper-container">
			<ul style="width: 100%;" class="swiper-wrapper">
				<li style="width: 100%;" class="swiper-slide">
					<a href="/bbs/board.php?bo_table=notice&wr_id=28">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_11.png" alt="">
					</a>
				</li>
				<li style="width: 100%;" class="swiper-slide">
					<a href="/shop/item.php?it_id=PRO2021022500198">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_10.png" alt="">
					</a>
				</li>
				<li style="width: 100%;" class="swiper-slide">
					<a href="/shop/item.php?it_id=PRO2021071500001&ca_id=70&page=1&sort=custom&page=1">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_09.png" alt="">
					</a>
				</li>
				<li style="width: 100%;" class="swiper-slide">

					<a href="<?php echo G5_URL; ?>/bbs/board.php?bo_table=notice&wr_id=23">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_07.jpg" alt="">
					</a>
				</li>
				<li style="width: 100%;" class="swiper-slide">
					<a href="<?php echo G5_URL; ?>/bbs/content.php?co_id=guide">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_08.png" alt="">
					</a>
				</li><!-- 
				<li style="width: 33.33%;" class="swiper-slide">
					<a href="/bbs/board.php?bo_table=notice&wr_id=11">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_01.jpg" alt="">
					</a>
				</li>
				<li style="width: 33.33%;" class="swiper-slide">
					<a href="/bbs/board.php?bo_table=notice&wr_id=11">
						<img src="<?=THEMA_URL?>/assets/img/main_banner_02.jpg" alt="">
					</a>
				</li> -->
			</ul>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			var swiper = new Swiper(".swiper-container", {
				slidesPerView : "auto",
				autoplay : {
					delay : 5000,
				},
			});
			
		})
	</script>
	
	<!-- 메인 추천 카테고리 -->
	<div id="mainBestCategoryWrap">
		<div class="title">
			카테고리별 추천상품
		</div>
		
		<ul class="list">
			<li><a href="#" data-no="1020" title="요실금팬티">요실금팬티</a></li>
			<li><a href="#" data-no="1030" title="자세변환용구">자세변환용구</a></li>
			<li><a href="#" data-no="1040" title="욕창예방방석">욕창예방방석</a></li>
			<li><a href="#" data-no="1050" title="지팡이">지팡이</a></li>
			<li><a href="#" data-no="1060" title="간이변기">간이변기</a></li>
			<li><a href="#" data-no="1080" title="미끄럼방지매트">미끄럼방지매트</a></li>
			<li><a href="#" data-no="1090" title="안전손잡이">안전손잡이</a></li>
			<li><a href="#" data-no="10b0" title="목욕의자">목욕의자</a></li>
			<li><a href="#" data-no="10c0" title="이동변기">이동변기</a></li>
		</ul>
		
		<div class="productListWrap">
			<ul>
			</ul>
		</div>
	</div>

	<script>
	// 첫번째 클릭
	$(document).ready(function(){
		setTimeout(function() {
			$('#mainBestCategoryWrap>ul.list').children('li:first-child').children('a').first()[0].click()
		}, 500);
	});
	</script>
	
	<!-- 메인 추천 제품 -->
	<div id="mainBestProductWrap">
		<div class="title">
			신규 추천상품
		</div>
		
		<div class="productListWrap">
			<ul>
			<?php
				$productSQL = sql_query("
					SELECT *
					FROM g5_shop_item
					WHERE ca_id2 = '30'
					ORDER BY it_id DESC
				");
				
				$optionProductList = [];
				for($i = 0; $row = sql_fetch_array($productSQL); $i++){
					$thisOptionList = [];

					# 210204 옵션
					$thisOptionSQL = sql_query("
						SELECT io_id
						FROM g5_shop_item_option
						WHERE it_id = '{$row["it_id"]}'
					");
					for($ii = 0; $subRow = sql_fetch_array($thisOptionSQL); $ii++){
						array_push($thisOptionList, $subRow["io_id"]);
					}
					
					$optionProductList[$row["it_id"]] = $thisOptionList;
				}
							 
				# 210204 재고조회
				$sendData = [];
				$sendData["usrId"] = $member["mb_id"];
				$sendData["entId"] = $member["mb_entId"];

				$prodsSendData = [];
				if($optionProductList){
					foreach($optionProductList as $it_id => $data){
						$stockQtyList[$it_id] = 0;

						if($data){
							foreach($data as $optionData){
								$prodsData = [];
								$prodsData["prodId"] = $it_id;
								$prodsData["prodColor"] = explode(chr(30), $optionData)[0];
								$prodsData["prodSize"] = explode(chr(30), $optionData)[1];

								array_push($prodsSendData, $prodsData);
							}
						} else {
							$prodsData = [];
							$prodsData["prodId"] = $it_id;
							$prodsData["prodColor"] = "";
							$prodsData["prodSize"] = "";

							array_push($prodsSendData, $prodsData);
						}
					}
				}

				$sendData["prods"] = $prodsSendData;

				$productSQL = sql_query("
					SELECT *
					FROM g5_shop_item
					WHERE ca_id2 = '30'
					ORDER BY it_id DESC
				");
							 
				for($i = 0; $row = sql_fetch_array($productSQL); $i++){
					$img = apms_it_thumbnail($row, 400, 400, false, true);

					if(!$img["src"] && $row["it_img1"]){
						$img["src"] = G5_DATA_URL."/item/{$row["it_img1"]}";
						$img["org"] = G5_DATA_URL."/item/{$row["it_img1"]}";
					}

					if(!$img["src"]){
						$img["src"] = G5_URL."/shop/img/no_image.gif";
					}
			?>
				<li class="<?=$row["it_id"]?>" data-ca="<?=substr($row["ca_id"], 0, 2)?>">
					<a href="/shop/item.php?it_id=<?=$row["it_id"]?>">
					<?php if($row["prodSupYn"] == "N"){ ?>
						<p class="sup">비유통 상품</p>
					<?php } ?>
						<p class="img">
						<?php if($img["src"]){ ?>
							<img src="<?=$img["src"]?>" alt="<?=$row["it_name"]?>_상품이미지">
							<?php if(json_decode($row["it_img_3d"], true)){ ?>
							<span class="img_3d">
								<img src="<?=G5_IMG_URL?>/item3dviewVisual.jpg">
							</span>
							<?php } ?>
						<?php } ?>
						</p>
						<p class="name"><?=$row["it_name"]?></p>
					<?php if($row["it_model"]){ ?>
						<p class="info"><?=$row["it_model"]?></p>
					<?php } ?>
					<?php if($member["mb_id"]){ ?>
						<?php if($member["mb_level"] == "3"){ ?>
							<?php if($_COOKIE["viewType"] != "basic"){ ?>
								<p class="discount"><?=number_format($row["it_cust_price"])?>원</p>
							<?php } ?>
							<p class="price"><?=($_COOKIE["viewType"] == "basic") ? number_format($row["it_cust_price"]) : number_format($row["it_price"])?>원</p>
						<?php } else { ?>
							<p class="price"><?=number_format($row["it_price"])?>원</p>
						<?php } ?>
					<?php } else { ?>
						<p class="price"><?=number_format($row["it_cust_price"])?>원</p>
					<?php } ?>
					</a>
				</li>
			<?php } ?>
			</ul>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			var sendData = <?=json_encode($sendData, JSON_UNESCAPED_UNICODE)?>;
			
			function stockCntSetting(){
			<?php if($member["mb_id"]){ ?>
				$.ajax({
					url : "/apiEroum/stock/selectList.php",
					type : "POST",
					async : false,
					data : sendData,
					success : function(result){
						$.each(result, function(it_id, cnt){
							var label = "재고 보유";
							if($("." + it_id).attr("data-ca") == "20"){
								label = "보유 대여 재고";
							}

							$("." + it_id).find("a > .cnt").remove();
							$("." + it_id).find("a").append('<p class="cnt"><span>' + label + '</span><span class="right">' + cnt + '개</span></p>');
						});
					}
				});
			<?php } ?>
			}
			
			stockCntSetting();
			
			function bestItemSetting(){
				var no = $("#mainBestCategoryWrap > .list > li > a.active").attr("data-no");
				
				$.ajax({
					url : "/shop/ajax.main.best.item.php",
					type : "POST",
					async : false,
					data : {
						no : no
					},
					success : function(result){
						result = JSON.parse(result);
						sendData = result.sendData;
						var html = "";

						$("#mainBestCategoryWrap > .productListWrap > ul > li").remove();
						
						if(result.data){
							$.each(result.data, function(it_id, row){
								html += '<li class="' + it_id + '" data-ca="' + row.ca_id + '">';
								html += '<a href="/shop/item.php?it_id=' + it_id + '">';
								if(row.prodSupYn == "N"){
									html += '<p class="sup">비유통 상품</p>';
								}
								html += '<p class="img">';
								if(row.img){
									html += '<img src="' + row.img + '" alt="' + row.it_name + '_상품이미지">';
									if(row.it_img_3d){
										html += '<span class="img_3d"><img src="<?=G5_IMG_URL?>/item3dviewVisual.jpg"></span>';
									}
								}
								html += '</p>';
								html += '<p class="name">' + row.it_name + '</p>';
								if(row.it_model){
									html += '<p class="info">' + row.it_model + '</p>';
								}
								if(row.it_price_discount){
									html += '<p class="discount">' + number_format(row.it_price_discount) + '원</p>';
								}
								html += '<p class="price">' + number_format(row.it_price) + '원</p>';
								html += '</a>';
								html += '</li>';
							});
							
							$("#mainBestCategoryWrap > .productListWrap > ul").html(html);
							stockCntSetting();
						}
					}
				});
			}
			
			$("#mainBestCategoryWrap > .list > li > a").click(function(e){
				e.preventDefault();
				if($(this).hasClass("active")){
					return false;
				}
				
				$("#mainBestCategoryWrap > .list > li > a").removeClass("active");
				$(this).addClass("active");
				bestItemSetting();
			});
			
		})
	</script>
	
	<!-- 메인 배너 -->
	<div id="mainBannerWrap">
		<div class="listWrap">
			<div class="slick">
				<div class="item">
					<a href="<?php echo G5_URL; ?>/bbs/content.php?co_id=guide">
						<img src="<?=THEMA_URL?>/assets/img/main_footer_banner.png" alt="" class="pc_layout">
						<img src="<?=THEMA_URL?>/assets/img/main_footer_banner_mo.png" alt="" class="mo_layout">
					</a>
				</div>
			</div>
		</div>
	</div>
	
	<!-- 메인 최근게시글 -->
	<div id="mainBoardListWrap">
  
  
  <div class="board">
    <div class="title">
      <span>공지사항</span> 
      <a href="/bbs/board.php?bo_table=notice"  ><img src="<?=THEMA_URL?>/assets/img/btn_board_more.png" alt="" /></a>
    </div>
    <?php  echo latest('list_main', 'notice', 5, 25); ?>
  </div>
  
  <div class="board">
    <div class="title">
      <span>자주하는 질문</span>
      <a href="/bbs/board.php?bo_table=faq"  ><img src="<?=THEMA_URL?>/assets/img/btn_board_more.png" alt="" /></a>
    </div>
    <?php  echo latest('list_main', 'faq', 5, 25); ?>
  </div>
  <div class="customer">
    <div class="title">
      <span>이로움 고객만족센터</span>
    </div>
    
    <ul class="info">
      <li class="call">
        <img src="<?=THEMA_URL?>/assets/img/mainCallIcon.png" alt="">
        <p class="call_info">
          <?php 
          $manager_hp="";
          $manager_name="";
          if($member['mb_manager']) {
            $sql_m ='select * from `g5_member` where `mb_id` = "'.$member['mb_manager'].'"';
            $result_m = sql_fetch($sql_m);
            $manager_hp = $result_m['mb_hp'];
            $manager_name = $result_m['mb_name'];
          }
          if($manager_hp) {
          ?>
          <span class="Label"><?=$manager_name?> </span>
          <span class="value" ><?=$manager_hp?></span>
          <?php } else { ?>
          <span class="Label">주문안내</span>
          <span class="value">032-562-6608</span>
          <?php } ?>
        </p>
        <p>
          <span class="Label">시스템안내</span>
          <span class="value">02-830-1301~2</span>
        </p>
      </li>
      <li class="etc">
        <p>
          <span>운영시간</span>
          <span class="line"></span>
          <span>월~금 09:00~18:00 (점심시간 12시~13시)</span>
        </p>
        <p>
          <span>Email</span>
          <span class="line"></span>
          <span><?php echo $default['de_admin_info_email']; ?></span>
        </p>
        <p>
          <span>Fax</span>
          <span class="line"></span>
          <span><?php echo $default['de_admin_company_fax']; ?></span>
        </p>
      </li>
    </ul>
  </div>
</div>

<!--
<div class="eroumcare-popup">
	<i class="fa fa-close fa-lg eroumcare-popup-close close-eroumcare-popup"></i>
	<div class="eroumcare-popup-content">
		<h3>
			수급자 신규등록
		</h3>
		<p>
			수급자 등록 체험을 위해<br/>체험용 수급자를 등록해보세요.
		</p>
		<div class="eroumcare-popup-buttons">
			<a href="#" class="active">
				수급자 주문하기
			</a>
			<a href="#" class="close-eroumcare-popup">
				다음에
			</a>
		</div>
	</div>
</div>
-->

<?php 
$t_recipient_add = get_tutorial('recipient_add');
if ($t_recipient_add['t_state'] == '0') { 
?>
	<script>
		show_eroumcare_popup({
			title: '수급자 신규등록',
			content: '수급자 등록 체험을 위해<br/>체험용 수급자를 등록해보세요.',
			activeBtn: {
				href: '/shop/my_recipient_write.php?tutorial=true',
				text: '수급자 등록하기',
			},
			hideBtn: {
				text: '다음에',
			}
		});
	</script>
<?php } ?>


<?php
$t_recipient_order = get_tutorial('recipient_order');
if ($t_recipient_order['t_state'] == '0') {
?>
<script>
show_eroumcare_popup({
  title: '수급자 주문하기',
  content: '수급자 주문을 체험하시겠습니까?<br/>판매품목 1개, 대여품목1개<br/>선택되어 주문을 체험할 수 있습니다.',
  activeBtn: {
    text: '주문체험하기',
    href: '/shop/tutorial_order.php'
  },
  hideBtn: {
    text: '다음에',
  }
});

</script>
<?php
} 
?>

<?php
$t_document = get_tutorial('document');
if ($t_document['t_state'] == '0') {
	
	$t_sql = "SELECT e.dc_status FROM tutorial as t INNER JOIN eform_document as e ON t.t_data = e.od_id
	WHERE 
		t.mb_id = '{$member['mb_id']}' AND
		t.t_type = 'recipient_order'
	";
	$t_result = sql_fetch($t_sql);

	if ($t_result['dc_status'] == '2' || $t_result['dc_status'] == '3') {
?>
	<script>
	show_eroumcare_popup({
		title: '전자문서 확인',
		content: '작성한 전자 계약서를<br/>확인하시겠습니까?',
		activeBtn: {
			text: '전자계약서확인',
			href: '/shop/electronic_manage.php'
		},
		hideBtn: {
			text: '다음에',
		}
	});
	</script>
	<?php } ?>
<?php } ?>

<?php
$t_claim = get_tutorial('claim');
if ($t_claim['t_state'] == '0') {
?>
<script>
  show_eroumcare_popup({
    title: '청구내역 확인',
    content: '수급자 주문 후 누적된 청구내역을<br/>확인 하시겠습니까?',
    activeBtn: {
      text: '청구내역 확인',
      href: '/shop/claim_manage.php'
    },
    hideBtn: {
      text: '다음에',
    }
  });
</script>
<?php } 
?>