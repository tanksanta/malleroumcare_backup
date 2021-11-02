<?php
include_once('./_common.php');

$ss_ent_mb_id = get_session('ss_ent_mb_id');
$ss_pen_id = get_session('ss_pen_id');
if(!$ss_ent_mb_id || !$ss_pen_id)
  alert('연결된 사업소가 없습니다.');

$tmp_cart_id = preg_replace("/[^0-9]/", "", $ss_pen_id);
set_session('ss_cart_id', $tmp_cart_id);

$g5['title'] = '공급제품 보관함';
include_once('./_head.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_row['order_'.MOBILE_.'skin'];
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_row['order_'.MOBILE_.'skin'];

// 스킨설정
$wset = array();
if($skin_row['order_'.MOBILE_.'set']) {
	$wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
}
$skin_url = $order_skin_url;

add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);
// 목록헤드
if(isset($wset['chead']) && $wset['chead']) {
  add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['chead'].'.css" media="screen">', 0);
  $head_class = 'list-head';
} else {
  $head_class = (isset($wset['ccolor']) && $wset['ccolor']) ? 'tr-head border-'.$wset['ccolor'] : 'tr-head border-black';
}

$sql = " select a.ct_id,
        a.it_id,
        a.it_name,
        a.ct_price,
        a.ct_discount,
        a.ct_point,
        a.ct_qty,
        a.ct_status,
        a.ct_send_cost,
        a.it_sc_type,
        b.it_cust_price,
        b.ca_id,
        b.ca_id2,
        b.ca_id3,
        b.pt_it,
        b.pt_msg1,
        b.pt_msg2,
        b.pt_msg3,
        b.it_model,
        a.prodSupYn,
        b.it_img1
       from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
      where a.od_id = '$tmp_cart_id'
      and a.mb_id = '$ss_ent_mb_id'
      and a.ct_pen_id = '$ss_pen_id' ";
$sql .= " group by a.it_id ";
$sql .= " order by a.ct_id ";
$result = sql_query($sql);

$cart_count = sql_num_rows($result);

$item = array();

for ($i=0; $row=sql_fetch_array($result); $i++)
{
  // 합계금액 계산
  $sql = " select SUM(IF(a.io_type = 1, (a.io_price * ct_qty), ((it_cust_price + a.io_price) * ct_qty))) as price,
          SUM(ct_point * ct_qty) as point,
          SUM(ct_discount) as discount,
          SUM(ct_qty) as qty
        from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
        where a.it_id = '{$row['it_id']}'
          and od_id = '$tmp_cart_id' ";
  $sum = sql_fetch($sql);

  $item[$i] = $row;

  $item[$i]['pt_it'] = apms_pt_it($row['pt_it'],1);

  if ($i==0) { // 계속쇼핑
    $continue_ca_id = $row['ca_id'];
  }

  $item[$i]['it_options'] = print_item_options($row['it_id'], $tmp_cart_id, $row['pt_msg1'], $row['pt_msg2'], $row['pt_msg3']);

  // 배송비
  switch($row['ct_send_cost'])
  {
    case 1:
      $ct_send_cost = '착불';
      break;
    case 2:
      $ct_send_cost = '무료';
      break;
    default:
      $ct_send_cost = '선불';
      break;
  }

  // 조건부무료
  if($row['it_sc_type'] == 2) {
    $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $tmp_cart_id);

    if($sendcost == 0)
      $ct_send_cost = '무료';
  }

  $point      = $sum['point'];
  $sell_price = $sum['price'];

  $item[$i]['ct_send_cost'] = $ct_send_cost;
  $item[$i]['point'] = $point;
  $item[$i]['sell_price'] = $sell_price;
  $item[$i]['sell_discount'] = $sum["discount"];
  $item[$i]['qty'] = $sum['qty'];
  $item[$i]['thumbnail'] = $row['it_img1'];

  $tot_point      += $point;
  $tot_sell_price += $sell_price;
  $tot_sell_discount += $sum["discount"];

} // for 끝

// 배송비 계산
if ($i > 0) {
  $send_cost = get_sendcost_new($tmp_cart_id, 0);
}

// 총계 = 주문상품금액합계 + 배송비
$tot_price = $tot_sell_price - $tot_sell_discount + $send_cost; 
?>

<section class="wrap">
  <div class="sub_section_tit">공급제품 보관함</div>
  <div class="table-responsive">
    <table class="div-table table bsk-tbl bg-white">
      <tbody>
        <tr class="<?php echo $head_class;?>">
          <th scope="col"><span>이미지</span></th>
          <th scope="col"><span>상품명</span></th>
          <th scope="col"><span>총수량</span></th>
          <th scope="col"><span>상품금액</span></th>
          <th scope="col"><span>할인가</span></th>
          <th scope="col"><span>소계</span></th>
          <!-- <th scope="col"><span>포인트</span></th> -->
          <th scope="col"><span class="last">배송비</span></th>
        </tr>
        <?php for($i=0;$i < count($item); $i++) { ?>
        <tr<?php echo ($i == 0) ? ' class="tr-line"' : '';?>>
          <td class="text-center">
            <div class="item-img">
              <img src="/data/item/<?=$item[$i]['thumbnail']?>" onerror="this.src = '/shop/img/no_image.gif';" style="width: 100px; height: 100px;">
              <div class="item-type">
                <?php echo $item[$i]['pt_it']; ?>
              </div>
            </div>
          </td>
          <td>
            <a href="./item.php?it_id=<?php echo $item[$i]['it_id'];?>">
              <b><?php echo stripslashes($item[$i]['it_name']); ?></b>
              <?php if($item[$i]["prodSupYn"] == "N"){ ?>
              <b style="position: relative; display: inline-block; width: 50px; height: 20px; line-height: 20px; top: -1px; border-radius: 5px; text-align: center; color: #FFF; font-size: 11px; background-color: #DC3333;">비유통</b>
              <?php } ?>
            </a>
            <?php if($item[$i]['it_options']) { ?>
            <div class="well well-sm"><?php echo $item[$i]['it_options'];?></div>
            <?php } ?>
          </td>
          <td class="text-center"><?php echo number_format($item[$i]['qty']); ?></td>
          <td class="text-right"><?php echo number_format($item[$i]['it_cust_price']); ?></td>
          <td class="text-right"><?php echo number_format($item[$i]['sell_discount']); ?></td>
          <td class="text-right"><span id="sell_price_<?php echo $i; ?>"><?php echo number_format($item[$i]['sell_price']); ?></span></td>
          <td class="text-center"><?php echo $item[$i]['ct_send_cost']; ?></td>
        </tr>
        <?php } ?>
        <?php if ($i == 0) { ?>
        <tr><td colspan="8" class="text-center text-muted"><p style="padding:50px 0;">장바구니가 비어 있습니다.</p></td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <div class="well bg-white">
    <div class="row">
      <div class="col-xs-6"> 총 상품금액 </div>
      <div class="col-xs-6 text-right">
        <strong id="total_price"><?php echo number_format($tot_price); ?> 원 <!-- / <?php echo number_format($tot_point); ?> 점 --></strong>
      </div>
    </div>
  </div>
</section>
<?php include_once('./_tail.php'); ?>
