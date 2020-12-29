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

        $it_name = get_text($rowx['it_name']);
        $img = get_it_image($tv_it_id, $tv_div['img_width'], $tv_div['img_height'], $tv_it_id, '', $it_name);
        $it_price = get_price($rowx);
        $print_price = is_int($it_price) ? number_format($it_price) : $it_price;

        if ($tv_tot_count == 0) echo '<div id="stv_ul" class="today-slick">'.PHP_EOL;
        echo '<div class="c'.$k.'">'.PHP_EOL;
        echo '<div class="prd_img">';
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
                prevArrow:"<button type='button' class='today-slick-prev'><i class='fa fa-angle-left' aria-hidden='true'></i></button>",
                nextArrow:"<button type='button' class='today-slick-next'><i class='fa fa-angle-right' aria-hidden='true'></i></button>"
            });
        })
    </script>
    <?php } else { // 오늘 본 상품이 없을 때 ?>
        <p class="li_empty">없음</p>
    <?php } ?>
</div>

<script src="<?php echo G5_JS_URL ?>/scroll_oldie.js"></script>
<!-- } 오늘 본 상품 끝 -->