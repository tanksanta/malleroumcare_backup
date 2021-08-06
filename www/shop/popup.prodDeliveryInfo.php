<?php

	include_once("./_common.php");

	$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$deliveryTotalCnt = 0;

	$carts = get_carts_by_od_id($od_id, 'Y');

?>
	
<!DOCTYPE html>
<html lang="ko">
	<head>
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<title>배송정보 조회</title>
	<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
	<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
	<link rel="shortcut icon" href="<?php echo THEMA_URL; ?>/assets/img/top_logo_icon.ico">
	<link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
	<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
	
	<style>

		* { margin: 0; padding: 0; box-sizing: border-box; position: relative; }
		html, body { width: 100%; min-width: 100%; float: left; margin: 0 !important; padding: 0; font-family: "Noto Sans KR", sans-serif; font-size: 13px; }
		body { padding-top: 60px; }
		
		#popupHeaderTopWrap { position: fixed; width: 100%; height: 60px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
		#popupHeaderTopWrap > div { height: 100%; line-height: 60px; }
		#popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
		#popupHeaderTopWrap > .close { float: right; }
		#popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

		#popupMemberProdDeliveryListWrap { width: 100%; float: left; padding: 20px; }
		
		#popupMemberProdDeliveryListWrap > .itemBox { width: 100%; float: left; border: 1px solid #DDD; padding: 10px; margin-bottom: 10px; display: table; table-layout: fixed; }
		#popupMemberProdDeliveryListWrap > .itemBox:last-of-type { margin-bottom: 0; }
		#popupMemberProdDeliveryListWrap > .itemBox > li { display: table-cell; vertical-align: middle; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.img { width: 85px; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.img > p { width: 100%; padding-bottom: 100%; float: left; border: 1px solid #E5E5E5; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.img > p > img { position: absolute; width: 100%; height: 100%; left: 0; top: 0; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.info { padding-left: 15px; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.info > p { width: 100%; float: left; margin: -1px 0; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.info > p.name { font-size: 18px; font-weight: bold; color: #000; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 5px; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.info > p.labelVal { font-size: 13px; color: #333; display: table; table-layout: fixed; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.info > p.labelVal > * { display: table-cell; vertical-align: top; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.info > p.labelVal > b { width: 60px; }
		#popupMemberProdDeliveryListWrap > .itemBox > li.logenbtn {
			width:80px;
		}
		#popupMemberProdDeliveryListWrap > .itemBox > li.logenbtn a {
			margin-bottom: 6px;
			display: block;
			vertical-align: top;
			border: 1px solid #ddd;
			background: #fff;
			padding: 3px 0;
			border-radius: 5px;
			font-size: 14px;
			text-align: center;
			color:#000;
		}

	</style>
</head>

<body>
	<div id="popupHeaderTopWrap">
		<div class="title">배송정보</div>
		<div class="close">
			<a href="#" id="popupCloseBtn">
				&times;
			</a>
		</div>
	</div>
	
	<div id="popupMemberProdDeliveryListWrap">
	<?php 
		for($i = 0; $i < count($carts); $i++){ 
			$options = $carts[$i]["options"];

			for($k = 0; $k < count($options); $k++){
	?>
		<ul class="itemBox">
			<li class="img">
				<p>
					<img src="/data/item/<?=$carts[$i]['it_img1']?>" onerror="this.src = '/shop/img/no_image.gif';">
				</p>
			</li>
			
			<li class="info">
				<p class="name">
					<?=stripslashes($carts[$i]["it_name"])?>
					<?php if($carts[$i]["it_name"] != $options[$k]["ct_option"]){ ?>
						(<?=$options[$k]["ct_option"]?>)
					<?php } ?>
				</p>
				<?php if ($options[$k]['ct_combine_ct_id']) { ?>
					<?php
					// 합포 상품 찾기
					foreach($carts as $c) {
						foreach($c['options'] as $o) {
							if($options[$k]['ct_combine_ct_id'] === $o['ct_id']) {
								echo stripslashes($c["it_name"]);
								if($c["it_name"] != $o["ct_option"]){
									echo '(' . $o["ct_option"] . ')';
								}
								echo ' 상품과 같이 배송 됩니다.';
							}
						}
					}
					?>
				<?php }else { ?>
					<p class="labelVal num">
						<b>
						<?php foreach($delivery_companys as $data){ ?>
							<?=($options[$k]["ct_delivery_company"] == $data["val"]) ? $data["name"] : ""?>
						<?php } ?>
						</b>
						<span>
							<?=($options[$k]["ct_delivery_num"]) ? $options[$k]["ct_delivery_num"] : "-"?>
						</span>
					</p>
					<p class="labelVal cnt">
						<b>박스</b>
						<span><?=number_format($options[$k]["ct_delivery_cnt"])?>개</span>
					</p>
				<?php } ?>
			</li>
			<?php if ($options[$k]["ct_delivery_company"] === 'ilogen' && $options[$k]["ct_delivery_num"]) { ?>
			<li class="logenbtn">
				<a href="https://www.ilogen.com/web/personal/trace/<?php echo $options[$k]["ct_delivery_num"]; ?>?open_safari=1" target="_blank">
					배송조회
				</a>
			</li>
			<?php } ?>
		</ul>
		<?php } ?>
	<?php } ?>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$("#popupCloseBtn").click(function(e){
				e.preventDefault();
				
				$("#popupProdDeliveryInfoBox", parent.document).hide();
				$("#popupProdDeliveryInfoBox", parent.document).find("iframe").remove();
			});
			
		})
	</script>
</body>
</html>