<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>
<!-- 상품진열 10 시작 { -->
<?php
for ($i=1; $row=sql_fetch_array($result); $i++) {
    if ($this->list_mod >= 2) { // 1줄 이미지 : 2개 이상
        if ($i%$this->list_mod == 0) $sct_last = 'sct_last'; // 줄 마지막
        else if ($i%$this->list_mod == 1) $sct_last = 'sct_clear'; // 줄 첫번째
        else $sct_last = '';
    } else { // 1줄 이미지 : 1개
        $sct_last = 'sct_clear';
    }

    if ($i == 1) {
        if ($this->css) {
            echo "<ul class=\"{$this->css}\">\n";
        } else {
            echo "";
        }
    }

    //echo "<li class=\"{$sct_last}\" style=\"width:{$this->img_width}px\">\n";
    echo "<div class=\"item_one\">\n";

    if ($this->view_it_youtube_link && $row['it_youtube_link']) {
        echo '<div class="icon_vod"><img src="'.THEMA_URL.'/assets/img/icon_vod.png" alt=""></div>';
    }

    if ($this->href) {
        echo "<a href=\"{$this->href}{$row['it_id']}\">\n";
    }

    
    if ($this->view_it_img) {
        echo '<div class="img_area">';
        //echo get_it_image($row['it_id'], $this->img_width, $this->img_height, '', '', stripslashes($row['it_name']))."\n";
        echo get_it_image($row['it_id'], 155, 0, '', '', stripslashes($row['it_name']))."\n";
        echo '</div>';
    }

    
    if ($this->view_sns) {
        $sns_top = $this->img_height + 10;
        $sns_url  = G5_SHOP_URL.'/item.php?it_id='.$row['it_id'];
        $sns_title = get_text($row['it_name']).' | '.get_text($config['cf_title']);
        echo "<div class=\"sct_sns\">";
        echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/facebook.png');
        echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/twitter.png');
        echo get_sns_share_link('googleplus', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/gplus.png');
        echo "</div>\n";
    }

    echo '<div class="desc">';
    
    
    if ($this->view_it_name) {
        echo "<p>".stripslashes($row['it_name'])."</p>\n";
    }



    if ($this->view_it_model && $row['it_model']) {
        echo "<p class=\"item_tit\"> ".stripslashes($row['it_model'])."</p>\n";
    }

    echo '
								
    ';

    if ($this->view_it_price) {
        echo '<div class="pay">'.show_samhwa_price($row, THEMA_KEY).'</div>';
    }

    //if ($this->view_it_icon) {
    //    if ( $row['it_type1']) {
    //        echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type1_color'].';color:'.$default['de_it_type1_color'].';">'.$default['de_it_type1_name'].'</div>';
    //    }
    //    if ( $row['it_type2']) {
    //        echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type2_color'].';color:'.$default['de_it_type2_color'].';">'.$default['de_it_type2_name'].'</div>';
    //    }
    //    if ( $row['it_type3']) {
    //        echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type3_color'].';color:'.$default['de_it_type3_color'].';">'.$default['de_it_type3_name'].'</div>';
    //    }
    //    if ( $row['it_type4']) {
    //        echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type4_color'].';color:'.$default['de_it_type4_color'].';">'.$default['de_it_type4_name'].'</div>';
    //    }
    //    if ( $row['it_type5']) {
    //        echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type5_color'].';color:'.$default['de_it_type5_color'].';">'.$default['de_it_type5_name'].'</div>';
    //    }
    //}

    echo '</div>';

    if ($this->href) {
        echo "</a>\n";
    }

    if ($this->view_it_youtube_link && $row['it_youtube_link']) {
        echo '
        <div class="vod_area">
            <div class="vod_tit">동영상보기</div>
            <div class="btn_close"> <button><img src="'.THEMA_URL.'/assets/img/btn_top_menu_x.png"></button></div>
            <iframe width="100%" height="280" src="'.$row['it_youtube_link'].'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen="" style="height: 56.25px;"></iframe>
            <a href="'.$row['it_youtube_link'].'">상세보기 <img src="'.THEMA_URL.'/assets/img/btn_arrow_icon.png" alt=""> </a>
        </div>
        ';
    }
    /*

    if ($this->view_it_id) {
        echo "<div class=\"sct_id\">&lt;".stripslashes($row['it_id'])."&gt;</div>\n";
    }

    if ($this->href) {
        echo "<div class=\"sct_txt\"><a href=\"{$this->href}{$row['it_id']}\">\n";
    }

    if ($this->view_it_name) {
        echo stripslashes($row['it_name'])."\n";
    }

    if ($this->href) {
        echo "</a></div>\n";
    }

    if ($this->view_it_basic && $row['it_basic']) {
        echo "<div class=\"sct_basic\">".stripslashes($row['it_basic'])."</div>\n";
    }

    if ($this->view_it_cust_price || $this->view_it_price) {

        echo "<div class=\"sct_cost\">\n";

        if ($this->view_it_cust_price && $row['it_cust_price']) {
            echo "<span class=\"sct_discount\">".display_price($row['it_cust_price'])."</span>\n";
        }

        if ($this->view_it_price) {
            echo display_price(get_price($row), $row['it_tel_inq'])."\n";
        }

        echo "</div>\n";

    }

    if ($this->view_it_icon) {
        echo "<div class=\"sct_icon\">".item_icon($row)."</div>\n";
    }

    */

    
    echo "</div>\n";
}

if ($i > 1) echo "";

if($i == 1) echo "<p class=\"sct_noitem\">등록된 상품이 없습니다.</p>\n";
?>

<!-- } 상품진열 10 끝 -->