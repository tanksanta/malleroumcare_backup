<?php

	include_once("./_common.php");

	$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$deliveryTotalCnt = 0;

	// 상품목록
	$sql = " select a.ct_id,
					a.it_id,
					a.it_name,
					a.cp_price,
					a.ct_notax,
					a.ct_send_cost,
					a.ct_sendcost,
					a.it_sc_type,
					a.pt_it,
					a.pt_id,
					b.ca_id,
					b.ca_id2,
					b.ca_id3,
					b.pt_msg1,
					b.pt_msg2,
					b.pt_msg3,
					a.ct_status,
					b.it_model,
					b.it_outsourcing_use,
					b.it_outsourcing_company,
					b.it_outsourcing_manager,
					b.it_outsourcing_email,
					b.it_outsourcing_option,
					b.it_outsourcing_option2,
					b.it_outsourcing_option3,
					b.it_outsourcing_option4,
					b.it_outsourcing_option5,
					a.pt_old_name,
					a.pt_old_opt,
					a.ct_uid,
					a.prodMemo,
					a.prodSupYn,
					a.ct_qty,
					a.ct_stock_qty,
					a.ct_delivery_company,
					a.ct_delivery_num,
					a.ct_delivery_cnt,
					a.ct_delivery_price,
					b.it_delivery_cnt,
					b.it_delivery_price,
					b.it_img1
			  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
			  AND a.ct_delivery_yn = 'Y'
			  AND a.prodSupYn = 'Y'
			  group by a.it_id, a.ct_uid
			  order by a.ct_id ";

	$result = sql_query($sql);

	$carts = array();
	$cate_counts = array();

	for($i=0; $row=sql_fetch_array($result); $i++) {

		$cate_counts[$row['ct_status']] += 1;

		// 상품의 옵션정보
		$sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_stock_qty, ct_barcode, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, ct_sendcost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount, ct_uid
						, ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn
					from {$g5['g5_shop_cart_table']} MT
					where od_id = '{$od['od_id']}'
						and it_id = '{$row['it_id']}'
						and ct_uid = '{$row['ct_uid']}'
					order by io_type asc, ct_id asc ";
		$res = sql_query($sql);

		$row['options_span'] = sql_num_rows($res);

		$row['options'] = array();
		for($k=0; $opt=sql_fetch_array($res); $k++) {

			$opt_price = 0;

			if($opt['io_type'])
				$opt_price = $opt['io_price'];
			else
				$opt_price = $opt['ct_price'] + $opt['io_price'];

			$opt["opt_price"] = $opt_price;

			// 소계
			$opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
			$opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

			if($opt["prodSupYn"] == "Y"){
				$opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
			}

			$row['options'][] = $opt;
		}


		// 합계금액 계산
		$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
						SUM(ct_qty) as qty,
						SUM(ct_discount) as discount,
						SUM(ct_send_cost) as sendcost
					from {$g5['g5_shop_cart_table']}
					where it_id = '{$row['it_id']}'
						and od_id = '{$od['od_id']}'
						and ct_uid = '{$row['ct_uid']}'";
		$sum = sql_fetch($sql);

		$row['sum'] = $sum;

		$carts[] = $row;
	}

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
				<p class="labelVal num">
					<b>
					<?php foreach($delivery_companys as $data){ ?>
						<?=($carts[$i]["ct_delivery_company"] == $data["val"]) ? $data["name"] : ""?>
					<?php } ?>
					</b>
					<span>
						<?=($carts[$i]["ct_delivery_num"]) ? $carts[$i]["ct_delivery_num"] : "-"?>
					</span>
				</p>
				<p class="labelVal cnt">
					<b>박스</b>
					<span><?=number_format($carts[$i]["ct_delivery_cnt"])?>개</span>
				</p>
				<p class="labelVal price">
					<b>배송비</b>
					<span><?=number_format($carts[$i]["ct_delivery_price"])?>원</span>
				</p>
			</li>
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