<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//자동높이조절
apms_script('imagesloaded');
apms_script('height');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$list_skin_url.'/style.css" media="screen">', 0);

// 버튼컬러
$btn1 = (isset($wset['btn1']) && $wset['btn1']) ? $wset['btn1'] : 'black';
$btn2 = (isset($wset['btn2']) && $wset['btn2']) ? $wset['btn2'] : 'color';

// 썸네일
//$thumb_w = (isset($wset['thumb_w']) && $wset['thumb_w'] > 0) ? $wset['thumb_w'] : 400;
$thumb_h = (isset($wset['thumb_h']) && $wset['thumb_h'] > 0) ? $wset['thumb_h'] : 540;
$thumb_w = 310;
$thumb_h = 400;
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

include_once($list_skin_path.'/category.skin.php');

include_once(THEMA_PATH.'/side/list-cate-side.php');
?>

<div id="sort-wrapper">
    <div class="dropdown">
        <a id="sortLabel" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-block">
            상품정렬
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="sortLabel">
            <li><a <?php echo ($sort == 'custom' ) ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>custom">추천순</a></li>
            <li><a <?php echo ($sort == 'it_price' && $sortodr == 'desc') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_price&amp;sortodr=desc">높은가격순</a></li>
            <li><a <?php echo ($sort == 'it_price' && $sortodr == 'asc') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_price&amp;sortodr=asc">낮은가격순</a></li>
            <li><a <?php echo ($sort == 'it_sum_qty') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_sum_qty&amp;sortodr=desc">판매많은순</a></li>
            <li><a <?php echo ($sort == 'it_use_avg') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_use_avg&amp;sortodr=desc">평점높은순</a></li>
            <li><a <?php echo ($sort == 'it_use_cnt') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_use_cnt&amp;sortodr=desc">후기많은순</a></li>
            <li><a <?php echo ($sort == 'it_update_time') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_update_time&amp;sortodr=desc">최근등록순</a></li>
        </ul>
    </div>
</div>
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
	
	if ($list[$i]['href']) {
        echo "</a>\n";
	}

	echo '<div class="desc">';
	
	if ($list[$i]['href']) {
        echo "<a href=\"{$list[$i]['href']}\">\n";
	}
	
	
    if ($list[$i]['it_name']) {
		$supInfo = "<b style='position: relative; display: inline-block; width: 50px; height: 20px; line-height: 20px; top: -1px; border-radius: 5px; text-align: center; color: #FFF; font-size: 11px; background-color: #".(($list[$i]["prodSupYn"] == "Y") ? "3366CC" : "DC3333")."; margin-left: 10px;'>".(($list[$i]["prodSupYn"] == "Y") ? "유통" : "비유통")."</b>";
		
        echo "<p>".stripslashes($list[$i]['it_name'])." {$supInfo}</p>\n";
	}
	
	if ($list[$i]['href']) {
        echo "</a>\n";
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
<!--
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
<div class="list-wrap">
	<?php
	// 리스트
	for ($i=0; $i < $list_cnt; $i++) {

		// DC
		$cur_price = $dc_per = '';
		if($list[$i]['it_cust_price'] > 0 && $list[$i]['it_price'] > 0) {
			$cur_price = '<strike>&nbsp;'.number_format($list[$i]['it_cust_price']).'&nbsp;</strike>';
			$dc_per = round((($list[$i]['it_cust_price'] - $list[$i]['it_price']) / $list[$i]['it_cust_price']) * 100);
		}

		// 라벨
		$item_label = '';
		if($dc_per || $list[$i]['it_type5']) {
			$item_label = '<div class="label-cap bg-red">DC</div>';	
		} else if($list[$i]['it_type3'] || $list[$i]['pt_num'] >= (G5_SERVER_TIME - ($new_item * 3600))) {
			$item_label = '<div class="label-cap bg-'.$wset['new'].'">New</div>';
		}

		// 아이콘
		$item_icon = item_icon($list[$i]);
		$item_icon = ($item_icon) ? '<div class="label-tack">'.$item_icon.'</div>' : '';

		// 이미지
		// $img = apms_it_thumbnail($list[$i], $thumb_w, $thumb_h, false, true);
		$img = apms_it_thumbnail($list[$i], 400, 400, false, true);

		// print_r2($list[$i]);
		if ( !$img['src'] && $list[$i]['it_img1'] ) {
			$img['src'] = G5_DATA_URL . '/item/' . $list[$i]['it_img1'];
			$img['org'] = G5_DATA_URL . '/item/' . $list[$i]['it_img1'];
		}
		// print_r2($img);


	?>
		<div class="item-row">
			<div class="item-list">
				<div class="item-image">
					<a href="<?php echo $list[$i]['href'];?>">
						<div class="img-wrap">
							<?php echo $shadow_in;?>
							<?php echo $item_label;?>
							<?php echo $item_icon;?>
							<div class="img-item">
								<img src="<?php echo $img['src'];?>" alt="<?php echo $img['alt'];?>">
							</div>
						</div>
					</a>
					<?php echo $shadow_out;?>
				</div>
				<div class="item-content">
					<?php if($wset['star']) { ?>
						<div class="item-star">
							<?php echo apms_get_star($list[$i]['it_use_avg'], $wset['star']); //평균별점 ?>
						</div>
					<?php } ?>
					<div class="item-name">
						<a href="<?php echo $list[$i]['href'];?>">
							<b><?php echo $list[$i]['it_name'];?></b>
							<div class="item-text">
								<?php echo ($list[$i]['it_basic']) ? $list[$i]['it_basic'] : apms_cut_text($list[$i]['it_explan'], 120); ?>
							</div>
							<div class="item-model">
								<?php echo str_replace(';', '<br/>', $list[$i]['it_model']); ?>
							</div>
						</a>
					</div>
					<div class="item-bar"></div>
					<div class="item-price en">
						<?php if($list[$i]['it_tel_inq']) { ?>
							<b>Call</b>
						<?php } else { ?>
							<?php echo show_samhwa_price($list[$i], THEMA_KEY);?>
						<?php } ?>
					</div>
					<div class="item-tags">
						<?php echo show_samhwa_it_tags($list[$i]); ?>
					</div>
					<div class="item-details en">
						<?php if($wset['cmt'] && $list[$i]['pt_comment']) { ?>
							<span class="item-sp red">
								<i class="fa fa-comment"></i> 
								<?php echo number_format($list[$i]['pt_comment']);?>
							</span>
						<?php } ?>
						<?php if($wset['buy'] && $list[$i]['it_sum_qty']) { ?>
							<span class="item-sp blue">
								<i class="fa fa-shopping-cart"></i>
								<?php echo number_format($list[$i]['it_sum_qty']);?>
							</span>
						<?php } ?>
						<?php if($wset['hit'] && $list[$i]['it_hit']) { ?>
							<span class="item-sp gray">
								<i class="fa fa-eye"></i> 
								<?php echo number_format($list[$i]['it_hit']);?>
							</span>
						<?php } ?>
						<?php if($list[$i]['it_point']) { ?>
							<span class="item-sp green">
								<i class="fa fa-gift"></i> 
								<?php echo ($list[$i]['it_point_type'] == 2) ? $list[$i]['it_point'].'%' : number_format(get_item_point($list[$i]));?>
							</span>
						<?php } ?>
						<?php if($dc_per) { ?>
							<span class="item-sp orangered">
								<i class="fa fa-bolt"></i> 
								<?php echo $dc_per;?>% DC
							</span>
						<?php } ?>
					</div>
				</div>
				<?php if($wset['sns']) { ?>
					<div class="item-sns">
						<?php 
							$sns_url  = G5_SHOP_URL.'/item.php?it_id='.$list[$i]['it_id'];
							$sns_title = get_text($list[$i]['it_name']);
							$sns_img = $list_skin_url.'/img';
							echo  get_sns_share_link('facebook', $sns_url, $sns_title, $sns_img.'/sns_fb.png').' ';
							echo  get_sns_share_link('twitter', $sns_url, $sns_title, $sns_img.'/sns_twt.png').' ';
							echo  get_sns_share_link('googleplus', $sns_url, $sns_title, $sns_img.'/sns_goo.png').' ';
							echo  get_sns_share_link('kakaostory', $sns_url, $sns_title, $sns_img.'/sns_kakaostory.png').' ';
							echo  get_sns_share_link('kakaotalk', $sns_url, $sns_title, $sns_img.'/sns_kakao.png').' ';
							echo  get_sns_share_link('naverband', $sns_url, $sns_title, $sns_img.'/sns_naverband.png').' ';
						?>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } // end for ?>
	<?php if(!$list_cnt) { ?>
		<div class="list-none">등록된 상품이 없습니다.</div>
	<?php } ?>
	<div class="clearfix"></div>
</div>
<script>
$(document).ready(function(){
	$('.list-wrap').imagesLoaded(function(){
		$('.list-wrap .item-content').matchHeight();
	});
});
</script>
<style>
</style>
-->
<div class="list-btn">
	<div class="list-page list-paging">
		<ul class="pagination pagination-sm en">
			<?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
		</ul>
		<div class="clearfix"></div>
	</div>
	<div class="">
        <?php if ($is_admin && $sort == 'custom') { ?>
        <div style="float: right">
            <button type="button" style="background: #333; color: #fff; padding: 5px 15px;" onclick="submitCustomIndex('<?php echo $ca_id ?>')">우선순위 저장</button>
        </div>
        <?php } ?>
		<div class="btn-group">
			<?php if ($is_event) { ?>
				<a class="btn btn-<?php echo $btn2;?> btn-sm" href="./event.php"><i class="fa fa-gift"></i> 이벤트</a>
			<?php } ?>
			<?php if ($write_href) { ?>
				<a class="btn btn-<?php echo $btn1;?> btn-sm" href="<?php echo $write_href;?>"><i class="fa fa-upload"></i><span class="hidden-xs"> 등록</span></a>
			<?php } ?>
			<?php if ($admin_href) { ?>
				<a class="btn btn-<?php echo $btn1;?> btn-sm" href="<?php echo $admin_href;?>"><i class="fa fa-th-large"></i><span class="hidden-xs"> 관리</span></a>
			<?php } ?>
			<?php if ($config_href) { ?>
				<a class="btn btn-<?php echo $btn1;?> btn-sm" href="<?php echo $config_href;?>"><i class="fa fa-cog"></i><span class="hidden-xs"> 설정</span></a>
			<?php } ?>
			<?php if($setup_href) { ?>
				<a class="btn btn-<?php echo $btn1;?> btn-sm win_memo" href="<?php echo $setup_href;?>"><i class="fa fa-cogs"></i><span class="hidden-xs"> 스킨설정</span></a>
			<?php } ?>
			<!-- <?php if ($rss_href) { ?>
				<a class="btn btn-<?php echo $btn2;?> btn-sm" title="카테고리 RSS 구독하기" href="<?php echo $rss_href;?>" target="_blank"><i class="fa fa-rss fa-lg"></i></a>
			<?php } ?> -->
		</div>
	</div>
	<div class="clearfix"></div>
</div>

<?php if ($is_admin) { ?>
<script>
    function submitCustomIndex(ca_id) {
        // var notEmptyInputs = $('.custom-index input').filter(function() {
        //     return this.value.length !== 0;
        // });

        var inputs = $('.custom-index input');
        
        var customIndexObj = {}; // (item-id : custom-index)
        var tempKey;
        
        if (inputs.length != 0) {
            console.log(inputs)
            inputs.each(function (i, v) {
                tempKey = $(v).data('item-id');
                customIndexObj[tempKey] = $(v).val();
            });

            $.ajax({
                type: "POST",
                url: "/adm/shop_admin/ajax.item.customindex.php",
                cache: false,
                async: false,
                data: {
                    ca_id : ca_id,
                    customIndexObj : customIndexObj
                },
                dataType: "json",
                success: function(data) {
                    alert(data);
                    location.reload();
                },
                error: function () {
                    alert("요청 중 에러가 발생했습니다.");
                }
            });
        }
    }
</script>
<?php } ?>