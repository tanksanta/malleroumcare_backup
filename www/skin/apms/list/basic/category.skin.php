<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$btn3 = (isset($wset['btn3']) && $wset['btn3']) ? $wset['btn3'] : 'black';

$sql = "SELECT * FROM g5_shop_category WHERE ca_id = '$ca_id' and ca_use='1'";
$cate_result = sql_fetch($sql);

$depth = strlen($ca_id);
$pca_id = substr($ca_id, 0, 2);

$sql = "SELECT * FROM g5_shop_category WHERE ca_id = '$pca_id' and ca_use='1'";
$one_cate_result = sql_fetch($sql);

if ( $depth >= 4 ) {
  $sql = "SELECT * FROM g5_shop_category WHERE ca_id = '".substr($ca_id, 0, 4)."' and ca_use='1'";
  $two_cate_result = sql_fetch($sql);
}

$sql = "SELECT * FROM g5_shop_category where ( length(ca_id) = 4 and ca_id like '"
    . substr($ca_id, 0, 2)
    . "%' ) and ca_use='1' ORDER BY ca_order, ca_id ASC";
$res3 = sql_query($sql);

$new_count = sql_fetch("select count(*) as cnt from g5_shop_item where it_10_subj = 'new'");//신규고시 상품 카운트, 0일 경우 신규고시 버튼 hidden 처리

$ca_sub_name_table = [];
$categories = [];
$sub_categories = [];
while( $row3 = sql_fetch_array($res3) ) {
  // 해당 분류에 속한 상품의 수
  /*$sql1 = " select COUNT(*) as cnt from {$g5['g5_shop_item_table']}
    where ( ca_id LIKE '{$row3['ca_id']}%'
    or ca_id2 LIKE '{$row3['ca_id']}%'
    or ca_id3 LIKE '{$row3['ca_id']}%' ) 
    and it_use = '1'
    ";
  // echo $sql1 . '<br>';
  $row1 = sql_fetch($sql1);
  $row3['cnt'] = $row1['cnt'];*/

  // 3 depth 서브 카테고리 조회
  $sql = " SELECT * FROM g5_shop_category WHERE ( length(ca_id) = 6 and ca_id like '{$row3['ca_id']}%' ) and ca_use='1' ORDER BY ca_order, ca_id ASC ";
  $sub_result = sql_query($sql);
  while($sub = sql_fetch_array($sub_result)) {
    if(!$sub_categories[$row3['ca_id']])
      $sub_categories[$row3['ca_id']] = [];
    $sub_categories[$row3['ca_id']][] = $sub;

    $ca_sub_name_table[substr($sub['ca_id'], 2)] = $row3['ca_name'].'('.$sub['ca_name'].')';
  }

  $ca_sub_name_table[substr($row3['ca_id'], 2)] = $row3['ca_name'];
  $categories[] = $row3;
}

/*$prodCount = sql_fetch("
  select Y.Y, N.N from
  (select count(*) AS Y from {$g5['g5_shop_item_table']} where it_use = '1' and prodSupYn = 'Y' and (ca_id LIKE '{$ca_id}%' or ca_id2 LIKE '{$ca_id}%' or ca_id3 LIKE '{$ca_id}%')) Y,
  (select count(*) AS N from {$g5['g5_shop_item_table']} where it_use = '1' and prodSupYn = 'N' and (ca_id LIKE '{$ca_id}%' or ca_id2 LIKE '{$ca_id}%' or ca_id3 LIKE '{$ca_id}%')) N
");*/

function make_ca_sub_url($ca_sub) {
  $ca_sub_url = "";
  foreach($ca_sub as $val) {
    $ca_sub_url .= '&ca_sub%5B%5D='.$val;
  }
  return $ca_sub_url;
}

function make_it_type_url($it_type) {
  $it_type_url = "";
  foreach($it_type as $val) {
    $it_type_url .= '&it_type%5B%5D='.$val;
  }
  return $it_type_url;
}
$ca_url = G5_SHOP_URL.'/list.php?ca_id='.$ca_id;
$ca_sub_url = make_ca_sub_url($ca_sub);
$it_type_url = make_it_type_url($it_type);
$sort_url = "";
if($sort) $sort_url .= "&sort=$sort";
if($sortodr) $sort_url .= "&sortodr=$sortodr";
$sup_url = "";
if($prodSupYn) $sup_url .= "&prodSupYn=$prodSupYn";
if($prodRentalYn) $sup_url .= "&prodRentalYn=$prodRentalYn";
if($prodNewYn) $sup_url .= "&prodNewYn=$prodNewYn";
$q_url = "";
if($q) $q_url .= "&q=$q";

// 페이지 주소 수정
$list_page = $_SERVER['SCRIPT_NAME'].'?ca_id='.$ca_id.$ca_sub_url.$sort_url.$sup_url.$q_url.$it_type_url.'&page=';
// 상품 정렬 주소 수정
$list_sort_href = './list.php?ca_id='.$ca_id.$ca_sub_url.$sup_url.$q_url.$it_type_url.'&sort=';

// 비급여 체크
$isBenefit = (substr($ca_id, 0, 2) == '70' || substr($ca_id, 0, 2) == '80') ? true : false;
?>
<div class="sub_section_tit">
  <a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $one_cate_result['ca_id']; ?>">
    <?php echo $one_cate_result['ca_name']; ?>
  </a>
  <span>
    <a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $two_cate_result['ca_id']; ?>">
      <?php echo $two_cate_result['ca_name']; ?>
    </a>
  </span>
  <div class="toggle_stock_wrap">
    My 보유재고 표시
    <button type="button" id="btn_toggle_stock" class="<?=($_COOKIE['SHOW_MY_STOCK'] !== 'OFF') ? 'active' : ''?>"><?=($_COOKIE['SHOW_MY_STOCK'] !== 'OFF') ? 'ON' : 'OFF'?></button>
  </div>
</div>
<script>
$(function() {
  // My 보유재고 표시
  $('#btn_toggle_stock').click(function() {
    var show = $(this).hasClass('active');
    if(show) {
      $.cookie('SHOW_MY_STOCK', 'OFF', { expires: 365 });
      window.location.reload();
    } else {      
      $.removeCookie('SHOW_MY_STOCK');
      $.cookie('SHOW_MY_STOCK', 'ON', { expires: 365 });
      window.location.reload();
    }
  });
});
</script>
<div class="cate_wrap">
  <ul>
    <li>
      <div class="cate_head">검색어</div>
      <div class="cate_body">
        <form class="form_cate" action="/shop/list.php" method="get">
          <input type="hidden" name="ca_id" value="<?=$ca_id?>">
          <?php if($sort) echo "<input type='hidden' name='sort' value='$sort'>"; ?> 
          <?php if($sortodr) echo "<input type='hidden' name='sortodr' value='$sortodr'>"; ?> 
          <?php if($prodSupYn) echo "<input type='hidden' name='prodSupYn' value='$prodSupYn'>"; ?> 
		  <?php if($prodRentalYn) echo "<input type='hidden' name='prodRentalYn' value='$prodRentalYn'>"; ?> 
		  <?php if($prodNewYn) echo "<input type='hidden' name='prodNewYn' value='$prodNewYn'>"; ?> 
          <input type="text" name="q" value="<?=($q ? $q : '')?>" class="input_search" maxlength="30">
          <input type="submit" class="input_submit" value="검색">
        </form>
      </div>
    </li>
    <li>
      <div class="cate_head">상품분류</div>
      <div class="cate_body">
        <?php
        foreach($categories as $cate) {
          $ca_sub_name = null;
          foreach($ca_sub as $sub) {
            if(strlen($sub) == 4 && substr($sub, 0, 2) == substr($cate['ca_id'], 2)) {
              $ca_sub_name = $ca_sub_name_table[$sub];
            }
          }
        ?>
        <div class="cate">
          <a href="<?php echo $ca_url.(in_array(substr($cate['ca_id'], 2), $ca_sub) ? '' : '&ca_sub%5B%5D='.substr($cate['ca_id'], 2)).$sort_url.$sup_url.$it_type_url ;?>"
            class="<?php if(in_array(substr($cate['ca_id'], 2), $ca_sub) || $ca_sub_name) echo 'active'; ?>">
            <?php
            if($ca_sub_name)
              echo $ca_sub_name;
            else
              echo $cate['ca_name'];
            ?>
          </a>
          <?php if($sub_categories[$cate['ca_id']]) { ?>
          <div class="cate_sub">
            <?php foreach($sub_categories[$cate['ca_id']] as $sub) { ?>
            <a href="<?php echo $ca_url.(in_array(substr($sub['ca_id'], 2), $ca_sub) ? '&ca_sub%5B%5D='.substr($sub['ca_id'], 2, 2) : '&ca_sub%5B%5D='.substr($sub['ca_id'], 2)).$sort_url.$sup_url.$it_type_url ;?>"
              class="<?php if(in_array(substr($sub['ca_id'], 2), $ca_sub)) echo 'active'; ?>">
              <?php echo $sub['ca_name']; ?>
            </a>
            <?php } ?>
          </div>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
    </li>
    <?php if (!$isBenefit) { ?>
    <li>
      <div class="cate_head">유통구분</div>
      <div class="cate_body">
        <a href="<?=$ca_url.$ca_sub_url.$sort_url?>&prodSupYn=<?=(($prodRentalYn != "Y" && $prodNewYn != "Y") ? ($prodSupYn == 'N' ? 'Y' : ($prodSupYn == 'all' ? 'Y' : 'all')): "Y").$q_url.$it_type_url?>"
        class="<?php if(in_array($prodSupYn, array('Y'))) echo 'active'; ?>">유통품목</a>
        <a href="<?=$ca_url.$ca_sub_url.$sort_url?>&prodSupYn=<?=(($prodRentalYn != "Y" && $prodNewYn != "Y") ? ($prodSupYn == 'Y' ? 'N' : ($prodSupYn == 'all' ? 'N' : 'all')): "N").$q_url.$it_type_url?>"
        class="<?php if(in_array($prodSupYn, array('N'))) echo 'active'; ?>">비유통품목</a>
		<?php if($ca_id == "20"){?>
		<a href="<?=$ca_url.$ca_sub_url.$sort_url?>&prodRentalYn=<?=(($prodRentalYn == 'N' || $prodRentalYn == '') ? 'Y' : 'N').$q_url.$it_type_url?>"
        class="<?php if(in_array($prodRentalYn, array('Y'))) echo 'active'; ?>">렌탈품목</a>
		<?php }?>
		<?php if($ca_id == "10" && $new_count["cnt"]>0){?>
		<a href="<?=$ca_url.$ca_sub_url.$sort_url?>&prodNewYn=<?=(($prodNewYn == 'N' || $prodNewYn == '') ? 'Y' : 'N').$q_url.$it_type_url?>"
        class="<?php if(in_array($prodNewYn, array('Y'))) echo 'active'; ?>">신규고시</a>
		<?php }?>
      </div>
    </li>
    <?php } ?>
    <li>
			<div class="cate_head">기타설정</div>
			<div class="cate_body">
        <?php
        for($i = 1; $i <= 13; $i++) {
          if($default['de_it_type'.$i.'_name']) {
            echo '<a href="'.$ca_url.$ca_sub_url.$sort_url.$sup_url.$q_url.(in_array($i, $it_type) ? make_it_type_url(array_diff($it_type, [$i])) : '&it_type%5B%5D='.$i).'"'.(in_array($i, $it_type) ? ' class="active"' : '').'>';
            echo $default['de_it_type'.$i.'_name'];
            echo '</a>';
          }
        }
        ?>
			</div>
		</li>
    <!--<li>
      <div class="cate_head">기타</div>
      <div class="cate_body"></div>
    </li>-->
  </ul>
</div>
<?php if($q || $ca_sub || $prodSupYn || $prodRentalYn) { ?>
<div class="cate_selected">
  <div class="selected_head">
    <a href="<?=G5_SHOP_URL.'/list.php?ca_id='.$ca_id?>">전체해제</a>
  </div>
  <div class="selected_body">
    <?php if($q) { ?>
      <a href="<?=$ca_url.$ca_sub_url.$sort_url.$sup_url?>"><?=$q?> <i class="fa fa-times" aria-hidden="true"></i></a>
    <?php } ?>
        <?php if (!$isBenefit) { ?>
            <?php if(in_array($prodSupYn, array('Y'))) { ?>
                <a href="<?=$ca_url.$ca_sub_url.$sort_url?><?=($prodSupYn == 'Y' ? '&prodSupYn=all' : '').$q_url?>">유통품목 <i class="fa fa-times" aria-hidden="true"></i></a>
            <?php } ?>
            <?php if(in_array($prodSupYn, array('N'))) { ?>
                <a href="<?=$ca_url.$ca_sub_url.$sort_url?><?=($prodSupYn == 'N' ? '&prodSupYn=all' : '').$q_url?>">비유통품목 <i class="fa fa-times" aria-hidden="true"></i></a>
            <?php } ?>
        <?php } ?>
	<?php if(substr($cate['ca_id'], 2) == "20"){?>
			<?php if(in_array($prodRentalYn, array('Y'))) { ?>
                <a href="<?=$ca_url.$ca_sub_url.$sort_url?><?=($prodRentalYn == 'Y' ? '&prodRentalYn=N' : '').$q_url?>">렌탈품목 <i class="fa fa-times" aria-hidden="true"></i></a>
            <?php } ?>
	<?php }?>
    <?php foreach($ca_sub as $sub) { ?>
    <a href="<?=$ca_url.make_ca_sub_url(array_diff($ca_sub, [$sub])).$sort_url.$sup_url.$q_url?>"><?=$ca_sub_name_table[$sub]?> <i class="fa fa-times" aria-hidden="true"></i></a>
    <?php } ?>
    <?php foreach($it_type as $type) { ?>
    <a href="<?=$ca_url.$ca_sub_url.$sort_url.$sup_url.$q_url.make_it_type_url(array_diff($it_type, [$type]))?>"><?=$default['de_it_type'.$type.'_name']?> <i class="fa fa-times" aria-hidden="true"></i></a>
    <?php } ?>
  </div>
</div>
<?php } ?>
