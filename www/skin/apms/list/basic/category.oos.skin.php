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

$sql = "SELECT * FROM g5_shop_category where ( length(ca_id) = 4 and ca_id like '"
    . substr($ca_id, 0, 2)
    . "%' ) ORDER BY ca_order, ca_id ASC";
$res3 = sql_query($sql);

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
  $sql = " SELECT * FROM g5_shop_category WHERE ( length(ca_id) = 6 and ca_id like '{$row3['ca_id']}%' ) ORDER BY ca_order, ca_id ASC ";
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

$ca_url = G5_SHOP_URL.'/list_oos.php?ca_id='.$ca_id;
$ca_sub_url = make_ca_sub_url($ca_sub);
$it_type_url = make_it_type_url($it_type);
$sort_url = "";
if($sort) $sort_url .= "&sort=$sort";
if($sortodr) $sort_url .= "&sortodr=$sortodr";
$sup_url = "";
if($prodSupYn) $sup_url .= "&prodSupYn=$prodSupYn";
$q_url = "";
if($q) $q_url .= "&q=$q";

// 페이지 주소 수정
$list_page = $_SERVER['SCRIPT_NAME'].'?ca_id='.$ca_id.$ca_sub_url.$sort_url.$sup_url.$q_url.$it_type_url.'&page=';
// 상품 정렬 주소 수정
$list_sort_href = './list_oos.php?ca_id='.$ca_id.$ca_sub_url.$sup_url.$q_url.$it_type_url.'&sort=';

// 비급여 체크
$isBenefit = substr($ca_id, 0, 2) == '70' ? true : false;
?>
<div class="sub_section_tit">
  <a href="#">
    품절상품
  </a>
  <span>
    <a href="<?php echo G5_SHOP_URL; ?>/list_oos.php?ca_id=<?php echo $two_cate_result['ca_id']; ?>">
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
        <form class="form_cate" action="/shop/list_oos.php" method="get">
          <input type="hidden" name="ca_id" value="<?=$ca_id?>">
          <?php if($sort) echo "<input type='hidden' name='sort' value='$sort'>"; ?> 
          <?php if($sortodr) echo "<input type='hidden' name='sortodr' value='$sortodr'>"; ?> 
          <?php if($prodSupYn) echo "<input type='hidden' name='prodSupYn' value='$prodSupYn'>"; ?> 
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
          <a href="<?php echo $ca_url.(in_array(substr($cate['ca_id'], 2), $ca_sub) ? '' : '&ca_sub%5B%5D='.substr($cate['ca_id'], 2)).$sort_url.$sup_url ;?>"
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
            <a href="<?php echo $ca_url.(in_array(substr($sub['ca_id'], 2), $ca_sub) ? '&ca_sub%5B%5D='.substr($sub['ca_id'], 2, 2) : '&ca_sub%5B%5D='.substr($sub['ca_id'], 2)).$sort_url.$sup_url ;?>"
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
  </ul>
</div>