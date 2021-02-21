<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$tv_datas = get_view_today_items(true);

$tv_div['top'] = 0;
$tv_div['img_width'] = 140;
$tv_div['img_height'] = 140;
$tv_div['img_length'] = 10; // 한번에 보여줄 이미지 수

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 오늘 본 상품 시작 { -->
<div id="stv" class="op_area">

    <?php if ($tv_datas) { // 오늘 본 상품이 1개라도 있을 때 ?>
    	<a href="#" id="stv_link">
    		<p class="info">
    			<span class="name">상품명</span>
    			<span>구매가능수량 : <span class="stock">0</span>개</span>
    			<span class="price">0원</span>
    		</p>
    		<p></p>
    		
<!--    		<span class="removeBtn" data-target="0">X</span>-->
    	</a>
    <?php
    $tv_tot_count = 0;
    $k = 0;
    $i = 1;
    foreach($tv_datas as $rowx)
    {
        if(!$rowx['it_id'])
            continue;
        
        $tv_it_id = $rowx['it_id'];

        if ($tv_tot_count % $tv_div['img_length'] == 0) $k++;

        $tv_it = sql_fetch("SELECT * FROM g5_shop_item WHERE it_id = '{$tv_it_id}'");
        $img = $tv_it["it_img1"];
		$img = "<img src='/data/item/{$img}' style='width: 128px; height: 128px;'>";
		
		$tv_it_price = 0;
		if($member["mb_id"]){
			if($member["mb_level"] == "3"){
				$tv_it_price = ($_COOKIE["viewType"] == "basic") ? $tv_it["it_cust_price"] : $tv_it["it_price"];
			} else {
				$tv_it_price = $tv_it["it_price"];
			}
		} else {
			$tv_it_price = $tv_it["it_cust_price"];
		}

        if ($tv_tot_count == 0) echo '<div id="stv_ul" class="today-slick">'.PHP_EOL;
        echo "<div class='c{$k}' data-id='{$tv_it["it_id"]}' data-name='{$tv_it["it_name"]}' data-stock='".number_format(get_it_stock_qty($tv_it["it_id"]))."' data-price='".number_format($tv_it_price)."'>".PHP_EOL;
        echo "<div class='prd_img'>";
        echo $img;
        echo '</div>'.PHP_EOL;
        //echo '<div class="prd_name">';
        //echo cut_str($it_name, 10, '').PHP_EOL;
        //echo '</div>';
        //echo '<div class="prd_cost">';
        //echo $print_price.PHP_EOL;
        //echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL;

        $tv_tot_count++;
        $i++;
    }

    if ($tv_tot_count > 0) echo '</div>'.PHP_EOL;
    ?>
    <span id="stv_pg"><span class="today-current-page">1</span>/<span class="total-page"><?php echo $tv_tot_count; ?></span></span>
    <script>
        jQuery(function($) {
            $('.today-slick').on('beforeChange', function(event, slick, currentSlide, nextSlide) {
                $(".today-current-page").html(nextSlide + 1)
            });

            $('.today-slick').slick({
                infinite : true,
                arrows : true,
                prevArrow:"<button type='button' class='today-slick-prev'><i class='fa fa-caret-left' aria-hidden='true'></i></button>",
                nextArrow:"<button type='button' class='today-slick-next'><i class='fa fa-caret-right' aria-hidden='true'></i></button>"
            });
			
			$(".today-slick .slick-slide").mouseenter(function(){
				var id = $(this).attr("data-id");
				var name = $(this).attr("data-name");
				var stock = $(this).attr("data-stock");
				var price = $(this).attr("data-price");
				
				$("#stv_link .name").text(name);
				$("#stv_link .stock").text(stock);
				$("#stv_link .price").text(price + "원");
				$("#stv_link .removeBtn").attr("data-target", $(this).attr("data-slick-index"));
				$("#stv_link").attr("href", "/shop/item.php?it_id=" + id);
				$("#stv_link").css("display", "table");
			});
			
			$("#stv_link").mouseleave(function(){
				$(this).hide();
			});
			
//			$("#stv_link > .removeBtn").click(function(e){
//				var index = Number($(this).attr("data-target")) + 1;
//				e.stopPropagation();
//				
//				$.ajax({
//					url : "/shop/ajax.remove.today.item.php",
//					type : "POST",
//					data : {
//						index : index
//					}
//				});
//				
//				
//				$("#stv_link").hide();
//				$("#stv_pg .total-page").text(Number($("#stv_pg .total-page")) - 1);
//				$("#stv_pg .today-current-page").text(1);
//				$(".today-slick .slick-slide[data-slick-index='" + index + "']").remove();
//				
//				return false;
//			});
        })
    </script>
    <?php } else { // 오늘 본 상품이 없을 때 ?>
    <?php } ?>
</div>

<script src="<?php echo G5_JS_URL ?>/scroll_oldie.js"></script>
<!-- } 오늘 본 상품 끝 -->