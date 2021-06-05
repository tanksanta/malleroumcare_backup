<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$btn3 = (isset($wset['btn3']) && $wset['btn3']) ? $wset['btn3'] : 'black';

$sql = "SELECT * FROM g5_shop_category WHERE ca_id = '$ca_id'";
$cate_result = sql_fetch($sql);

$depth = strlen($ca_id);
$pca_id = substr($ca_id, 0, 2);

$sql = "SELECT * FROM g5_shop_category WHERE ca_id = '$pca_id'";
$one_cate_result = sql_fetch($sql);

if ( $depth >= 4 ) {
	$sql = "SELECT * FROM g5_shop_category WHERE ca_id = '".substr($ca_id, 0, 4)."'";
	$two_cate_result = sql_fetch($sql);
}

$next_category = array();
$sql = "SELECT * FROM g5_shop_category where ( length(ca_id) = 4 and ca_id like '"
		. substr($ca_id, 0, 2)
		. "%' ) ORDER BY ca_order, ca_id ASC";

$res3 = sql_query($sql);
while( $row3 = sql_fetch_array($res3) ) {
	// 해당 분류에 속한 상품의 수
	$sql1 = " select COUNT(*) as cnt from {$g5['g5_shop_item_table']}
		where ( ca_id LIKE '{$row3['ca_id']}%'
		or ca_id2 LIKE '{$row3['ca_id']}%'
		or ca_id3 LIKE '{$row3['ca_id']}%' ) 
		and it_use = '1'
		";
	// echo $sql1 . '<br>';
	$row1 = sql_fetch($sql1);
	$row3['cnt'] = $row1['cnt'];

	$next_category[] = $row3;
}

$prodCount = sql_fetch("
	select Y.Y, N.N from
	(select count(*) AS Y from {$g5['g5_shop_item_table']} where it_use = '1' and prodSupYn = 'Y' and (ca_id LIKE '{$ca_id}%' or ca_id2 LIKE '{$ca_id}%' or ca_id3 LIKE '{$ca_id}%')) Y,
	(select count(*) AS N from {$g5['g5_shop_item_table']} where it_use = '1' and prodSupYn = 'N' and (ca_id LIKE '{$ca_id}%' or ca_id2 LIKE '{$ca_id}%' or ca_id3 LIKE '{$ca_id}%')) N
");

function make_ca_sub_url($ca_sub) {
	$ca_sub_url = "";
	foreach($ca_sub as $val) {
		$ca_sub_url .= '&ca_sub%5B%5D='.$val;
	}
	return $ca_sub_url;
}

$ca_url = G5_SHOP_URL.'/list.php?ca_id='.$ca_id;
$ca_sub_url = make_ca_sub_url($ca_sub);
$sort_url = "";
if($sort) $sort_url .= "&sort=$sort";
if($sortodr) $sort_url .= "&sortodr=$sortodr";
$sup_url = "";
if($prodSupYn) $sup_url .= "&prodSupYn=$prodSupYn";
// print_r2($next_category);

// 페이지 주소 수정
$list_page = $_SERVER['SCRIPT_NAME'].'?ca_id='.$ca_id.$ca_sub_url.$sort_url.$sup_url.'&page=';
$ca_sub_name_table = array();
?>
<div id="samhwa-list-banner">
<?php
	$bimg_str = "";
	$bimg = G5_DATA_PATH."/category/{$cate_result['ca_id']}";
	if (file_exists($bimg)) {
		// echo '<img src="'.G5_DATA_URL.'/category/'.$cate_result['ca_id'].'">';
	}
?>
</div>
<div class="sub_section_tit">
	<a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $one_cate_result['ca_id']; ?>">
		<?php echo $one_cate_result['ca_name']; ?>
	</a>
	<span>
		<a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $two_cate_result['ca_id']; ?>">
			<?php echo $two_cate_result['ca_name']; ?>
		</a>
	</span>
</div>
<div class="cate_wrap">
	<ul>
		<?php if($next_category) { ?>
		<li>
			<div class="cate_head">상품분류</div>
			<div class="cate_body">
				<?php foreach($next_category as $cate) {
					$ca_sub_name_table[substr($cate['ca_id'], 2)] = $cate['ca_name'];
				?>
				<a href="<?php echo $ca_url.$ca_sub_url.(in_array(substr($cate['ca_id'], 2), $ca_sub) ? '' : '&ca_sub%5B%5D='.substr($cate['ca_id'], 2)).$sort_url.$sup_url ;?>"
					class="<?php if(in_array(substr($cate['ca_id'], 2), $ca_sub)) echo 'active'; ?>">
					<?php echo $cate['ca_name']; ?>(<?php echo $cate['cnt']; ?>)
				</a>
				<?php } ?>
			</div>
		</li>
		<?php } ?>
		<li>
			<div class="cate_head">유통여부</div>
			<div class="cate_body">
				<a href="<?=$ca_url.$ca_sub_url.$sort_url?>&prodSupYn=<?=($prodSupYn == 'N' ? 'all' : 'Y')?>"
				class="<?php if(in_array($prodSupYn, array('Y', 'all'))) echo 'active'; ?>">유통품목(<?=$prodCount['Y']?>)</a>
        <a href="<?=$ca_url.$ca_sub_url.$sort_url?>&prodSupYn=<?=($prodSupYn == 'Y' ? 'all' : 'N')?>"
				class="<?php if(in_array($prodSupYn, array('N', 'all'))) echo 'active'; ?>">비유통품목(<?=$prodCount['N']?>)</a>
			</div>
		</li>
		<!--<li>
			<div class="cate_head">기타</div>
			<div class="cate_body"></div>
		</li>-->
	</ul>
</div>
<?php if($ca_sub || $prodSupYn) { ?>
<div class="cate_selected">
	<div class="selected_head">
		<a href="<?=G5_SHOP_URL.'/list.php?ca_id='.$ca_id?>">전체해제</a>
	</div>
	<div class="selected_body">
		<?php if(in_array($prodSupYn, array('Y', 'all'))) { ?>
			<a href="<?=$ca_url.$ca_sub_url.$sort_url?><?=($prodSupYn == 'all' ? '&prodSupYn=N' : '')?>">유통품목 <i class="fa fa-times" aria-hidden="true"></i></a>
		<?php } ?>
		<?php if(in_array($prodSupYn, array('N', 'all'))) { ?>
			<a href="<?=$ca_url.$ca_sub_url.$sort_url?><?=($prodSupYn == 'all' ? '&prodSupYn=Y' : '')?>">비유통품목 <i class="fa fa-times" aria-hidden="true"></i></a>
		<?php } ?>
		<?php foreach($ca_sub as $sub) { ?>
		<a href="<?=$ca_url.make_ca_sub_url(array_diff($ca_sub, [$sub])).$sort_url.$sub_url?>"><?=$ca_sub_name_table[$sub]?> <i class="fa fa-times" aria-hidden="true"></i></a>
		<?php } ?>
	</div>
</div>
<?php } ?>
