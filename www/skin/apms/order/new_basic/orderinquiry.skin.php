<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['ihead']) && $wset['ihead']) {
	add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ihead'].'.css" media="screen">', 0);
	$head_class = 'list-head';
} else {
	$head_class = (isset($wset['icolor']) && $wset['icolor']) ? 'tr-head border-'.$wset['icolor'] : 'tr-head border-black';
}

// 헤더 출력
if($header_skin)
	include_once('./header.php');

	# 스킨경로	
	$SKIN_URL = G5_SKIN_URL.'/apms/order/'.$skin_name;

?>

<link rel="stylesheet" href="<?=$SKIN_URL?>/css/jquery-ui.min.css">
<link rel="stylesheet" href="<?=$SKIN_URL?>/css/product_order.css">
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
   
<script>
	$( function() {
		//캘린더
		$("#date1").datepicker({
			dateFormat : 'yy-mm-dd',
			prevText: '이전달',
			nextText: '다음달',
			monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			dayNames: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
			showMonthAfterYear: true,
			changeMonth: true,
			changeYear: true,
			showOn: "both",
			buttonImage: "<?=$SKIN_URL?>/image/icon_17.png",
			buttonImageOnly: true,
			buttonText: "Select date"
		});
		$("#date2").datepicker({
			dateFormat : 'yy-mm-dd',
			prevText: '이전달',
			nextText: '다음달',
			monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
			dayNames: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
			dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
			showMonthAfterYear: true,
			changeMonth: true,
			changeYear: true,
			showOn: "both",
			buttonImage: "<?=$SKIN_URL?>/image/icon_17.png",
			buttonImageOnly: true,
			buttonText: "Select date"
		});

		//셀렉트(주문+재고, 전체 상태)
		$('.order-date .list-select .select').find('p').each(function(){
			$(this).on('click',function(){
				$(this).siblings('ul').stop().slideToggle();
				$(this).parent('.select').siblings('.select').find('ul').stop().slideUp();
				$(this).siblings('ul').find('li a ').on('click',function(){
					let textVal = $(this).text();
					$(this).parents('ul').siblings('p').text(textVal);
					$(this).parents('ul').stop().slideUp();

				});
			});
		});
	} );
</script>
  
   <!-- 210326 재고조회팝업 -->
	<div id="popupProdBarNumInfoBox" class="listPopupBoxWrap">
		<div>
		</div>
	</div>
  	<!-- 210326 재고조회팝업 -->
   
   <!-- 210326 배송정보팝업 -->
	<div id="popupProdDeliveryInfoBox" class="listPopupBoxWrap">
		<div>
		</div>
	</div>
   
    <style>
		.listPopupBoxWrap { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
		.listPopupBoxWrap > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
		.listPopupBoxWrap iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

		@media (max-width : 750px){
			.listPopupBoxWrap iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
		}
	</style>
  
	<script type="text/javascript">
		$(function(){

			$(".listPopupBoxWrap").hide();
			$(".listPopupBoxWrap").css("opacity", 1);
			
			$(".popupDeliveryInfoBtn").click(function(e){
				e.preventDefault();
				
				var od = $(this).attr("data-od");
				$("#popupProdDeliveryInfoBox > div").append("<iframe src='/shop/popup.prodDeliveryInfo.php?od_id=" + od + "'>");
				$("#popupProdDeliveryInfoBox iframe").load(function(){
					$("#popupProdDeliveryInfoBox").show();
				});
			});
			
			$(".popupProdBarNumInfoBtn").click(function(e){
				e.preventDefault();
				
				var od = $(this).attr("data-od");
				var it = $(this).attr("data-it");
				var stock = $(this).attr("data-stock");
				$("#popupProdBarNumInfoBox > div").append("<iframe src='/adm/shop_admin/popup.prodBarNum.form_3.php?prodId=" + it + "&od_id=" + od + "&stock_insert=" + stock + "'>");
				$("#popupProdBarNumInfoBox iframe").load(function(){
					$("#popupProdBarNumInfoBox").show();
				});
			});
			
		})
	</script>
   <!-- 210326 배송정보팝업 -->
    
<section id="pro-order" class="wrap order-list">
	<h2 class="tti">주문내역</h2>
	<div class="order-date">
		<div class="list-text">
			<div>
				<span><img src="<?=$SKIN_URL?>/image/icon_13.png" alt="">상품준비 <b><?=$item_wait_count?>건</b></span>
			</div>
			<div>
				<span><img src="<?=$SKIN_URL?>/image/icon_14.png" alt="">배송중 <b><?=$delivery_ing_count?>건</b></span>
			</div>
		</div>
		<form class="date-box cb" style="width: 100%;" method="get">
			<div class="list-date">
				<input type="text" name="s_date" value="<?=$_GET["s_date"]?>" id="date1" /> 
				~ 
				<input type="text" name="e_date" value="<?=$_GET["e_date"]?>" id="date2" /> 
			</div>
			<div class="list-tab">
				<a href="javascript:;" onclick="searchDateSetting('1week');">일주일</a>
				<a href="javascript:;" onclick="searchDateSetting('1month');">이번달</a>
				<a href="javascript:;" onclick="searchDateSetting('3month');">3개월</a>
			</div>
			<div class="list-select">
				<div class="select">
					<input type="hidden" name="od_stock" value="<?=$_GET["od_stock"]?>">
					<p><?=$search_od_stock?></p>
					<ul>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_stock" data-val="">주문+재고</a></li>
					<?php for($i = 0; $i < count($order_stocks); $i++){ ?>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_stock" data-val="<?=$order_stocks[$i]["val"]?>"><?=$order_stocks[$i]["name"]?></a></li>
					<?php } ?>
					</ul>
				</div>
				<div class="select">
					<input type="hidden" name="od_status" value="<?=$_GET["od_status"]?>">
					<p><?=$search_od_status?></p>
					<ul>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_status" data-val="">전체 상태</a></li>
					<?php for($i = 0; $i < count($order_steps); $i++){ ?>
						<li><a href="javscript:;" class="hiddenChange" data-target="od_status" data-val="<?=$order_steps[$i]["val"]?>"><?=$order_steps[$i]["name"]?></a></li>
					<?php } ?>
					</ul>
				</div>
				<button type="submit">검색</button>
			</div>
		</form>
	</div>

	<div class="list-wrap">
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
	<?php for ($i = 0; $i < count($list); $i++){ $row = $list[$i]; ?>
	<?php
		$itemList = [];
        $stock_insert ="1";
		$itemSQL = sql_query("
			SELECT a.*
				, ( SELECT it_img1 FROM {$g5["g5_shop_item_table"]} WHERE it_id = a.it_id ) AS it_img
				, ( SELECT prodSupYn FROM {$g5["g5_shop_item_table"]} WHERE it_id = a.it_id ) AS prodSupYn
			FROM {$g5["g5_shop_cart_table"]} a
			WHERE od_id = '{$row["od_id"]}'
		");
											  
		for($ii = 0; $item = sql_fetch_array($itemSQL); $ii++){
			array_push($itemList, $item);
		}
	?>
		<div class="table-list table-list2">
			<div class="top">
				<span> <i class="m_none">주문번호 :</i> <a href="<?=$row["od_href"]?>"><?=$row["od_id"]?></a> </span>
				<span> <?=display_price($row["od_total_price"])?> </span>
				<span class="m_none"> <?=date("Y.m.d(H:i)", strtotime($row["od_time"]))?></span>
			<?php if($row["recipient_yn"] == "Y"){ ?>
				<span class="btn-pro"> <img src="<?=$SKIN_URL?>/image/icon_15.png" alt=""> 수급자 주문 </span>
			<?php }else if($row["od_stock_insert_yn"] == "Y"){ 
                $stock_insert ="2";
            ?>
				<span class="btn-pro on"> 보유재고등록 </span>
			<?php } else { ?>
				<span class="btn-pro on"> <img src="<?=$SKIN_URL?>/image/icon_16.png" alt=""> 상품 주문 </span>
			<?php } ?>
			</div>

			<div class="info-wrap">
			<?php if($row["recipient_yn"] == "Y"){ ?>
				<div class="info-top">
					<h5>수급자 정보 : <?=$row["od_penNm"]?> (<?=$row["od_penTypeNm"]?>)</h5>
					<a href="javascript:;" style="display: none;">계약서</a>
				</div>
			<?php } ?>
			</div>
			
			<?php foreach($itemList as $item){ 
                // //바코드 개수 구하기
                // $sendData2=[];
                // // $sendData2["stateCd"] =['01','02','08','09'];
                // $sendData2["usrId"] = $member["mb_id"];
                // $sendData2["entId"] = $member["mb_entId"];
                // $sendData2["prodId"] = $item["it_id"];
                // $oCurl = curl_init();
                // curl_setopt($oCurl, CURLOPT_PORT, 9901);
                // curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectDetailList");
                // curl_setopt($oCurl, CURLOPT_POST, 1);
                // curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData2, JSON_UNESCAPED_UNICODE));
                // curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                // curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                // $res = curl_exec($oCurl);
                // $res = json_decode($res, true);
                // curl_close($oCurl);
                // // print_r($res["data"][0]['prodBarNum']);
                // $barcode_c=0;		
                // for($k=0;$k<count($res["data"]); $k++){
                //     if($res["data"][$k]['prodBarNum']) $barcode_c++;
                // }
                // print_r($item['ct_qty']);
            ?>

				<div class="list">
					<ul class="cb">
						<li class="pro">
							<div class="img" style="min-width:100px; min-height:100px;">
							<?php if($item["it_img"]){ ?>
								<a href="<?=$row["od_href"]?>"><img src="/data/item/<?=$item["it_img"]?>" onerror="this.src='/img/no_img.png';"></a>
							<?php } ?>
							</div>
							<div class="pro-info">
							<?php if($row["recipient_yn"] == "Y"){ ?>
								<div class="day">
								<?php if($item["ordLendStrDtm"] && $item["ordLendStrDtm"] != "0000-00-00 00:00:00"){ ?>
									<i>대여</i>
									<?=date("Y.m.d", strtotime($item["ordLendStrDtm"]))?> ~ <?=date("Y.m.d", strtotime($item["ordLendEndDtm"]))?>
								<?php } else { ?>
									<i class="on-order">주문</i>
								<?php } ?>
								</div>
							<?php } ?>
								<div class="name">
									<a href="<?=$row["od_href"]?>">
									<?=$item["it_name"]?> <?=($item["ct_option"] && $item["ct_option"] != $item["it_name"]) ? "({$item["ct_option"]})" : ""?>
									<?=($item["prodSupYn"] == "N") ? "<b>비유통</b>" : ""?>
									</a>
								</div>
								<div>
									<em>수량 : <?=$item["ct_qty"]?></em>
								<?php if($item["ct_stock_qty"]){ ?>
									<em>, 재고소진 : <?=$item["ct_stock_qty"]?></em>
								<?php } ?>
								</div>
								<div class="pc_none">
									<?=($row["od_stock_insert_yn"] == "Y") ? "재고등록완료" : $row["od_status"]?>
								</div>
							</div>
						</li>
						<li class="delivery m_none">
							<p><?=($row["od_stock_insert_yn"] == "Y") ? "재고등록완료" : $row["od_status"]?></p>
						</li>
						<li class="info-btn">
							<div>
                            <?php
                                $sendData = [];
                                $sendData["penOrdId"] = $item["ordId"];
                                $sendData["uuid"] = $item["uuid"];
                                $sendData["it_id"] = $item["it_id"];
                                
                                $oCurl = curl_init();
                                curl_setopt($oCurl, CURLOPT_PORT, 9901);
                                curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/order/selectList");
                                curl_setopt($oCurl, CURLOPT_POST, 1);
                                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
                                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                                $res = curl_exec($oCurl);
                                curl_close($oCurl);
                    
                                $result = json_decode($res, true);
                                $result = $result["data"];
                                // print_r($item);
                            ?>
                            <?php 
                                if($stock_insert=="2"){
                            ?>
                                <a href="#" class="btn-03 btn-0 popupProdBarNumInfoBtn" data-stock="<?=$stock_insert?>" data-od="<?=$row["od_id"]?>" data-it="<?=$item["it_id"]?>"> 
                                <!-- <?=($row["od_prodBarNum_insert"] < $row["od_prodBarNum_total"]) ? "바코드 ({$row["od_prodBarNum_insert"]}/{$item['ct_qty']})" : "바코드 확인"?> -->
                                바코드 확인
                                </a>
							<?php }else{ ?>
                                <?php if($item["prodSupYn"] == "N"){ ?>
                                    <a href="#" class="btn-03 btn-0 popupProdBarNumInfoBtn" data-stock="<?=$stock_insert?>" data-od="<?=$row["od_id"]?>" data-it="<?=$item["it_id"]?>"> 
                                    <!-- <?=($row["od_prodBarNum_insert"] < $row["od_prodBarNum_total"]) ? "바코드 ({$row["od_prodBarNum_insert"]}/{$item['ct_qty']})" : "바코드 확인"?> -->
                                    바코드 확인
                                    </a>
                                <?php } else { ?>
                                    <?php //if($barcode_c>0){ ?>
                                    <a href="#" class="btn-01 btn-0 popupProdBarNumInfoBtn" data-stock="<?=$stock_insert?>" data-od="<?=$row["od_id"]?>" data-it="<?=$item["it_id"]?>"><img src="<?=$SKIN_URL?>/image/icon_02.png" alt=""> 바코드</a>
                                    <?php //}else { ?>
                                        <!-- 아직 바코드가 입력되지 않았습니다. -->
                                    <?php //} ?>
                                <?php } ?>
                            <?php } ?>
							<?php if($row["od_delivery_insert"] && ($item["prodSupYn"] == "Y")){ ?>
								<a href="#" class="btn-02 btn-0 popupDeliveryInfoBtn" data-od="<?=$row["od_id"]?>">배송정보</a>
							<?php } ?>
                            <?php 
                                $sql_v= "SELECT `ca_id` FROM `g5_shop_item` WHERE `it_id` = '".$item["it_id"]."'";
                                $result_v=sql_fetch($sql_v);
                                $str = substr($result_v['ca_id'],0 , 2);
                                if($str=="20"){
                                    $path ="sales_Inventory_datail2.php";
                                }else{
                                    $path ="sales_Inventory_datail.php";
                                }
                            ?>
							<?php if($row["od_status"] == "배송완료"){ ?>
								<a href="<?php echo G5_SHOP_URL; ?>/<?=$path?>?prodId=<?=$item["it_id"]?>&page=&searchtype=&searchtypeText=" class="btn-02 btn-0">재고확인</a>
							<?php } ?>
							<?php if($row["od_status"] == "출고완료"){ ?>
								<a href="#" class="btn-04 btn-0 delivery_ok" data-ct-id="<?php echo $item['ct_id']; ?>" data-od-id="<?php echo $row["od_id"]; ?>">배송완료</a>
							<?php } ?>
							</div>
						</li>
					</ul>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	</div>

</section>

<div class="text-center">
	<ul class="pagination pagination-sm en">
		<?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
	</ul>
</div>

<?php if($setup_href) { ?>
	<p class="text-center">
		<a class="btn btn-color btn-sm win_memo" href="<?php echo $setup_href;?>">
			<i class="fa fa-cogs"></i> 스킨설정
		</a>
	</p>
<?php } ?>

<script type="text/javascript">
    function hide_control(od_id){
        $.ajax({
				method: "POST",
				url: "./ajax.hide_control.php",
				data: {
					od_id: od_id
				}
			}).done(function(data) {
                if(data=="S"){
                    alert('삭제가 완료되었습니다.');
                    window.location.reload(); 
                }
			})
    }

	function searchDateSetting(type){
		switch(type){
			case "1week" :
				$("#date1").val("<?=date("Y-m-d", strtotime("- 7 days"))?>");
				break;
			case "1month" :
				$("#date1").val("<?=date("Y-m-01")?>");
				break;
			case "3month" :
				$("#date1").val("<?=date("Y-m-d", strtotime("- 3 month"))?>");
				break;
		}

		$("#date2").val("<?=date("Y-m-d")?>");
	}
	
	$(function(){
		
		$(".hiddenChange").click(function(){
			var target = $(this).attr("data-target");
			var val = $(this).attr("data-val");
			
			$(this).closest("form").find("input[name='" + target + "']").val(val);
		});

		$('.delivery_ok').click(function(e) {

			e.preventDefault();

			var od_id = $(this).data('od-id');
			var ct_id = $(this).data('ct-id');

			$.ajax({
				method: "POST",
				dataType:"json",
				url: "./ajax.order.delivery.ok.php",
				data: {
					od_id: od_id,
					ct_id: ct_id
				}
			}).done(function(data) {
				console.log(data);
				if ( data.msg ) {
					alert(data.msg);
				}
				if (data.result === 'success') {
					location.reload(true);
				}
			})
		})
		
	})
</script>