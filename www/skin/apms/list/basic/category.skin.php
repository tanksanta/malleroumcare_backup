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
$next_category_depth = $depth == 2 ? 4 : 6;
$sql = "SELECT * FROM g5_shop_category where ( length(ca_id) = '$next_category_depth' and ca_id like '".substr($ca_id, 0, ($next_category_depth -2))."%' ) ORDER BY ca_order, ca_id ASC";

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

// print_r2($next_category);
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
	<?php foreach($next_category as $cate) { ?>
		<a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $cate['ca_id']; ?>" class="<?php echo $cate['ca_id'] == $ca_id ? 'on' : ''; ?>"><?php echo $cate['ca_name']; ?>(<?php echo $cate['cnt']; ?>)</a>
	<?php } ?>
</div>