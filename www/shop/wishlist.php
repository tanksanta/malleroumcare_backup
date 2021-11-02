<?php
include_once('./_common.php');

if (!$is_member)
  goto_url(G5_BBS_URL."/login.php?url=".urlencode(G5_SHOP_URL.'/wishlist.php'));
// 테마에 wishlist.php 있으면 include

if(defined('G5_THEME_SHOP_PATH')) {
  $theme_wishlist_file = G5_THEME_SHOP_PATH.'/wishlist.php';
  if(is_file($theme_wishlist_file)) {
    include_once($theme_wishlist_file);
    return;
    unset($theme_wishlist_file);
  }
}

$g5['title'] = "위시리스트";
include_once('./_head.php');
?>
<style>
/* 페이징 */
.pg-wrap {margin: 90px 0 0;}
.pg-wrap div {text-align: center;font-size: 0;}
.pg-wrap div a {display: inline-block;padding: 0 13px; vertical-align: middle;font-size: 14px;color:#999;}
.pg-wrap div a.on {color:#333}
</style>
<!-- 위시리스트 시작 { -->
<div id="sod_ws">
  <form name="fwishlist" method="post" action="./cartupdate.php">
    <input type="hidden" name="act"       value="multi">
    <input type="hidden" name="sw_direct" value="">
    <input type="hidden" name="prog"      value="wish">
    <div class="wishlist-skin">
      <table class="div-table table bg-white">
        <tbody style="text-align:center; line-height:50px;">
          <tr class="bg-black">
            <th class="text-center" scope="col" width="60">No.</th>
            <th class="text-center" scope="col">이미지</th>
            <th class="text-center" scope="col">상품명</th>
            <th class="text-center" scope="col">보관일시</th>
            <th class="text-center" scope="col">삭제</th>
          </tr>
          <?php
          //토탈카운트
          $sql2  = " select count(*) as count from `{$g5['g5_shop_wish_table']}` where `mb_id` = '{$member['mb_id']}'";
          $count = sql_fetch($sql2);
          $totalCnt = $count['count']; //토탈카운트
          if($_GET['page']){$page=$_GET['page'];}else{$page=1;}


          $listCnt = 5; # 리스트 갯수 default 10
          $b_pageNum_listCnt = 5; # 한 블록에 보여줄 페이지 갯수 5개
          $load=($page-1)*$listCnt; //5개씩
          $sql  = " select a.wi_id, a.wi_time, b.* from {$g5['g5_shop_wish_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id ) ";
          $sql .= " where a.mb_id = '{$member['mb_id']}' order by a.wi_id desc limit {$load}, 5";
          
          # 페이징
          $pageNum = $page; # 페이지 번호

          $block = ceil($pageNum/$b_pageNum_listCnt); # 총 블록 갯수 구하기
          $b_start_page = ( ($block - 1) * $b_pageNum_listCnt ) + 1; # 블록 시작 페이지 
          $b_end_page = $b_start_page + $b_pageNum_listCnt - 1;  # 블록 종료 페이지
          $total_page = ceil( $totalCnt / $listCnt ); # 총 페이지
          // 총 페이지 보다 블럭 수가 만을경우 블록의 마지막 페이지를 총 페이지로 변경
          if ($b_end_page > $total_page){ 
              $b_end_page = $total_page;
          }
          
          $total_block = ceil($total_page/$b_pageNum_listCnt);
          $result = sql_query($sql);
          for ($i=0; $row = sql_fetch_array($result); $i++) {
              $number = $totalCnt-(($page-1)*5)-$i; //넘버링
              $out_cd = '';
              $sql = " select count(*) as count from {$g5['g5_shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' ";
              $tmp = sql_fetch($sql);
              if($tmp['cnt'])
                  $out_cd = 'no';
              $it_price = get_price($row);
              if ($row['it_tel_inq']) $out_cd = 'tel_inq';
              $image = get_it_image($row['it_id'],100,100);
              $root_url=G5_URL;
              $thumb = "<img src='{$root_url}/data/item/{$row['it_img1']}' style='width:50px; height:50px;'>";

          ?>
          <tr>
            <td class="text-center" style="text-align:center; line-height:50px;">
              <?=$number?>
              <?php if(is_soldout($row['it_id'])) { // 품절검사 ?>
              품절
              <?php } else { //품절이 아니면 체크할수 있도록한다 ?>
              <!--<label for="chk_it_id_<?php echo $i; ?>" class="sound_only"><?php echo $row['it_name']; ?></label>
              <input style="margin-bottom:8px;"type="checkbox" name="chk_it_id[<?php echo $i; ?>]" value="1" id="chk_it_id_<?php echo $i; ?>" onclick="out_cd_check(this, '<?php echo $out_cd; ?>');">-->
              <?php } ?>
              <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
              <input type="hidden" name="io_type[<?php echo $row['it_id']; ?>][0]" value="0">
              <input type="hidden" name="io_id[<?php echo $row['it_id']; ?>][0]" value="">
              <input type="hidden" name="io_value[<?php echo $row['it_id']; ?>][0]" value="<?php echo $row['it_name']; ?>">
              <input type="hidden"   name="ct_qty[<?php echo $row['it_id']; ?>][0]" value="1">
            </td>
            <td class="text-center">
              <a href="./item.php?it_id=<?php echo $row['it_id']; ?>">
                <?php echo $thumb; ?>
              </a>
            </td>
            <td style="text-align:center; line-height:50px;"><a href="./item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo stripslashes($row['it_name']); ?></a></td>
            <td style="text-align:center; line-height:50px;" class="text-center"><?php echo $row['wi_time']; ?></td>
            <td style="text-align:center; line-height:50px;" class="text-center"><a href="./wishupdate.php?w=d&amp;wi_id=<?php echo $row['wi_id']; ?>" class="wish_del"><i class="fa fa-trash" aria-hidden="true"></i><span class="sound_only">삭제</span></a></td>
          </tr>
          <?php
          }
          if ($i == 0)
            echo '<tr><td colspan="5" class="text-center text-muted" height="150">보관함이 비었습니다.</td></tr>';
          ?>
        </tbody>
      </table>
    </div>
    <!--<p class="text-center">
      <button type="submit" class="btn btn-black btn-sm" onclick="return fwishlist_check(document.fwishlist,'');">장바구니 담기</button>
      <button type="submit" class="btn btn-color btn-sm" onclick="return fwishlist_check(document.fwishlist,'direct_buy');">주문하기</button>
    </p>-->
  </form>
  <!-- 페이징 -->
  <div class="pg-wrap">
    <div id="numbering_zone1">
      <?php if($pageNum >$b_pageNum_listCnt){ ?><a href="<?=G5_SHOP_URL?>/wishlist.php?page=1"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a><?php } ?>
      <?php if($block > 1){ ?><a href="<?=G5_SHOP_URL?>/wishlist.php?page=<?=($b_start_page-1)?>"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a><?php } ?>
      <?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?><a href="<?=G5_SHOP_URL?>/wishlist.php?page=<?=$j?>"><?=$j?></a><?php } ?>
      <?php if($block < $total_block){ ?><a href="<?=G5_SHOP_URL?>/wishlist.php?page=<?=($b_end_page+1)?>"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a><?php } ?>
      <?php if($block < $total_block){ ?><a href="<?=G5_SHOP_URL?>/wishlist.php?page=<?=$total_page?>"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a><?php } ?>
    </div>
  </div>
</div>

<script>
function out_cd_check(fld, out_cd)
{
  if (out_cd == 'no') {
    alert("옵션이 있는 상품입니다.\n\n상품을 클릭하여 상품페이지에서 옵션을 선택한 후 주문하십시오.");
    fld.checked = false;
    return;
  }

  if (out_cd == 'tel_inq') {
    alert("이 상품은 전화로 문의해 주십시오.\n\n장바구니에 담아 구입하실 수 없습니다.");
    fld.checked = false;
    return;
  }
}

function fwishlist_check(f, act)
{
  var k = 0;
  var length = f.elements.length;

  for(i=0; i<length; i++) {
    if (f.elements[i].checked) {
      k++;
    }
  }

  if(k == 0)
  {
    alert("상품을 하나 이상 체크 하십시오");
    return false;
  }

  if (act == "direct_buy")
  {
    f.sw_direct.value = 1;
  }
  else
  {
    f.sw_direct.value = 0;
  }

  return true;
}
</script>
<!-- } 위시리스트 끝 -->

<?php
include_once('./_tail.php');
?>
