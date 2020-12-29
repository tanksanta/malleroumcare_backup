<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//자동높이조절
apms_script('imagesloaded');
apms_script('height');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 버튼컬러
$btn1 = (isset($wset['btn1']) && $wset['btn1']) ? $wset['btn1'] : 'black';
$btn2 = (isset($wset['btn2']) && $wset['btn2']) ? $wset['btn2'] : 'color';

// 헤더 출력
if($header_skin)
	include_once('./header.php');

// 썸네일
$thumb_w = (isset($wset['thumb_w']) && $wset['thumb_w'] > 0) ? $wset['thumb_w'] : 400;
$thumb_h = (isset($wset['thumb_h']) && $wset['thumb_h'] > 0) ? $wset['thumb_h'] : 540;
$img_h = apms_img_height($thumb_w, $thumb_h, '135');

$wset['line'] = (isset($wset['line']) && $wset['line'] > 0) ? $wset['line'] : 2;
$line_height = 20 * $wset['line'];

// 간격
$gap_right = (isset($wset['gap']) && ($wset['gap'] > 0 || $wset['gap'] == "0")) ? $wset['gap'] : 15;
$minus_right = ($gap_right > 0) ? '-'.$gap_right : 0;

$gap_bottom = (isset($wset['gapb']) && ($wset['gapb'] > 0 || $wset['gapb'] == "0")) ? $wset['gapb'] : 30;
$minus_bottom = ($gap_bottom > 0) ? '-'.$gap_bottom : 0;

// 가로수
$item = (isset($wset['item']) && $wset['item'] > 0) ? $wset['item'] : 4;

// 반응형
if(_RESPONSIVE_) {
	$lg = (isset($wset['lg']) && $wset['lg'] > 0) ? $wset['lg'] : 3;
	$md = (isset($wset['md']) && $wset['md'] > 0) ? $wset['md'] : 3;
	$sm = (isset($wset['sm']) && $wset['sm'] > 0) ? $wset['sm'] : 2;
	$xs = (isset($wset['xs']) && $wset['xs'] > 0) ? $wset['xs'] : 2;
}

// 새상품
$is_new = (isset($wset['new']) && $wset['new']) ? $wset['new'] : 'red'; 
$new_item = ($wset['newtime']) ? $wset['newtime'] : 24;

// DC
$is_dc = (isset($wset['dc']) && $wset['dc']) ? $wset['dc'] : 'orangered'; 

// 그림자
$shadow_in = '';
$shadow_out = (isset($wset['shadow']) && $wset['shadow']) ? apms_shadow($wset['shadow']) : '';
if($shadow_out && isset($wset['inshadow']) && $wset['inshadow']) {
	$shadow_in = '<div class="in-shadow">'.$shadow_out.'</div>';
	$shadow_out = '';	
}

$list_cnt = count($list);

include_once($skin_path.'/search.skin.form.php');

?>
<style>
	.list-wrap { margin-right:<?php echo $minus_right;?>px; margin-bottom:<?php echo $minus_bottom;?>px; }
	.list-wrap .item-row { width:<?php echo apms_img_width($item);?>%; }
	.list-wrap .item-list { margin-right:<?php echo $gap_right;?>px; margin-bottom:<?php echo $gap_bottom;?>px; }
	.list-wrap .item-name { height:<?php echo $line_height;?>px; }
	.list-wrap .img-wrap { padding-bottom:<?php echo $img_h;?>%; }
	<?php if(_RESPONSIVE_) { // 반응형일 때만 작동 ?>
		<?php if($lg) { ?>
		@media (max-width:1199px) { 
			.responsive .list-wrap .item-row { width:<?php echo apms_img_width($lg);?>%; } 
		}
		<?php } ?>
		<?php if($md) { ?>
		@media (max-width:991px) { 
			.responsive .list-wrap .item-row { width:<?php echo apms_img_width($md);?>%; } 
		}
		<?php } ?>
		<?php if($sm) { ?>
		@media (max-width:767px) { 
			.responsive .list-wrap .item-row { width:<?php echo apms_img_width($sm);?>%; } 
		}
		<?php } ?>
		<?php if($xs) { ?>
		@media (max-width:480px) { 
			.responsive .list-wrap .item-row { width:<?php echo apms_img_width($xs);?>%; } 
		}
		<?php } ?>
	<?php } ?>
</style>
<div class="item_list_wrap">
	<ul> 
<?php
// 리스트
for ($i=0; $i < $list_cnt; $i++) {
	echo '<li>';

	if ($list[$i]['it_youtube_link']) {
        echo '<div class="icon_vod"><img src="'.THEMA_URL.'/assets/img/icon_vod.png" alt=""></div>';
    }

    if ($list[$i]['href']) {
        echo "<a href=\"{$list[$i]['href']}\">\n";
	}

	$img = apms_it_thumbnail($list[$i], 400, 400, false, true);

	// print_r2($list[$i]);
	if ( !$img['src'] && $list[$i]['it_img1'] ) {
		$img['src'] = G5_DATA_URL . '/item/' . $list[$i]['it_img1'];
		$img['org'] = G5_DATA_URL . '/item/' . $list[$i]['it_img1'];
	}

	if ( !$img['src'] ) {
		$img['src'] = G5_URL . '/shop/img/no_image.gif';
	}
	
    if ($img['src']) {
        echo '<div class="img_area">';
        echo '<img src="'.$img['src'].'" alt="'.$img['alt'].'" style="display: block;">';
		echo '</div>';
	}

	echo '<div class="desc">';
	
    if ($list[$i]['it_name']) {
        echo "<p>".stripslashes($list[$i]['it_name'])."</p>\n";
	}

	
    if ($list[$i]['it_model']) {
        echo "<p class=\"item_tit\">  ".stripslashes($list[$i]['it_model'])."</p>\n";
	}
	
	
    echo '
								 
    ';

	
    if ($list[$i]['it_price']) {
        echo '<div class="pay">'.show_samhwa_price($list[$i], THEMA_KEY).'</div>';
	}
	
	if ($list[$i]['it_price']) {
        if ( $list[$i]['it_type1']) {
            echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type1_color'].';color:'.$default['de_it_type1_color'].';">'.$default['de_it_type1_name'].'</div>';
        }
        if ( $list[$i]['it_type2']) {
            echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type2_color'].';color:'.$default['de_it_type2_color'].';">'.$default['de_it_type2_name'].'</div>';
        }
        if ( $list[$i]['it_type3']) {
            echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type3_color'].';color:'.$default['de_it_type3_color'].';">'.$default['de_it_type3_name'].'</div>';
        }
        if ( $list[$i]['it_type4']) {
            echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type4_color'].';color:'.$default['de_it_type4_color'].';">'.$default['de_it_type4_name'].'</div>';
        }
        if ( $list[$i]['it_type5']) {
            echo '<div class="item_tag" style="border:1px solid '.$default['de_it_type5_color'].';color:'.$default['de_it_type5_color'].';">'.$default['de_it_type5_name'].'</div>';
        }
	}
	
    echo '</div>';

	if ($list[$i]['href']) {
        echo "</a>\n";
	}
 
	// 우선순위 조정
    if ($is_admin && $sort == 'custom') {
        $sql_custom_index = "select *
                          from g5_shop_item_custom_index
                          where it_id = '{$list[$i]['it_id']}' and ca_id = '{$ca_id}'";
        $row = sql_fetch($sql_custom_index);
        $custom_index = "<div class='custom-index'>
                            <span>우선순위</span><input data-item-id='{$list[$i]['it_id']}' type='text' style='border: 1px solid #999; float: right; text-align: center;' value='{$row['custom_index']}' oninput='this.value = this.value.replace(/[^0-9.]/g, \"\").replace(/(\..*)\./g, \"$1\");'>
                         </div>";
        echo $custom_index;
    }
    
    if ($list[$i]['it_youtube_link']) {
        echo '
        <div class="vod_area">
            <div class="vod_tit">동영상보기</div>
            <div class="btn_close"> <button><img src="'.THEMA_URL.'/assets/img/btn_top_menu_x.png"></button></div>
            <iframe width="100%" height="280" src="'.$list[$i]['it_youtube_link'].'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen="" style="height: 56.25px;"></iframe>
            <a href="'.$list[$i]['it_youtube_link'].'">상세보기 <img src="'.THEMA_URL.'/assets/img/btn_arrow_icon.png" alt=""> </a>
        </div>
        ';
    }

	echo '</li>';
}
?>
	</ul>
</div>

<script>
$(document).ready(function(){
	$('.list-wrap').imagesLoaded(function(){
		$('.list-wrap .item-content').matchHeight();
	});
});
</script>

<div class="list-page text-center">
	<ul class="pagination en">
		<?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
	</ul>
	<div class="clearfix"></div>
</div>

<?php if ($is_admin || $setup_href) { ?>
	<div class="text-center">
		<?php if($is_admin) { ?>
			<a class="btn btn-<?php echo $btn1;?> btn-sm" href="<?php echo G5_ADMIN_URL;?>/apms_admin/apms.admin.php?ap=thema"><i class="fa fa-cog"></i> 설정</a>
		<?php } ?>
		<?php if($setup_href) { ?>
			<a class="btn btn-<?php echo $btn2;?> btn-sm win_memo" href="<?php echo $setup_href;?>"><i class="fa fa-cogs"></i> 스킨설정</a>
		<?php } ?>
		<div class="h30"></div>
	</div>
<?php } ?>
