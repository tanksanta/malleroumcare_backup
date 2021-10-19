<?php
if (!defined('_GNUBOARD_')) exit;

foreach($items as $item) {
  $it = sql_fetch(" SELECT * FROM g5_shop_item WHERE it_id = '{$item['it_id']}' ");
  $gubun = $cate_gubun_table[substr($it["ca_id"], 0, 2)];
  $gubun_text = '판매';
  if($gubun == '01') $gubun_text = '대여';
  else if($gubun == '02') $gubun_text = '비급여';
?>
<div class="im_item">
  <div class="im_flex">
    <img src="/data/item/<?=$it['it_img1']?>" alt="<?=$it['it_name']?>" onerror="this.src='/img/no_img.png';">
    <div class="im_info">
      <p class="it_name">
        <?php echo "{$it['it_name']} ({$gubun_text})"; ?>
      </p>
      <p class="it_price">
        급여가 : <?php echo number_format($it["it_cust_price"]); ?>원
      </p>
      <p class="personal-price">
        ※ 본인부담금 <span>15%(<?=number_format($it["it_cust_price"] * 0.15)?>원)</span>, <span>9%(<?=number_format($it["it_cust_price"] * 0.09)?>원)</span>, <span>6%(<?=number_format($it["it_cust_price"] * 0.06)?>원)</span>
      </p>
      <ul class="it_detail">
        <?php if(trim($it["prodSym"])) { ?>
        <li>- 재질 : <?=$it["prodSym"]?></li>
        <?php } ?>
        <?php if(trim($it["prodSizeDetail"])) { ?>
        <li>- 사이즈 : <?=$it["prodSizeDetail"]?></li>
        <?php } ?>
        <?php if(trim($it["prodWeig"])) { ?>
        <li>- 중량 : <?=$it["prodWeig"]?></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="it_explan">
    <?php echo apms_explan($it['it_explan']); ?>
  </div>
  <button class="im_btn_more">상세정보 펼쳐보기 ▼</button>
</div>
<?php
}
?>
<script>
$('.im_btn_more').click(function() {
  $(this).closest('.im_item').addClass('active');
  $(this).remove();
});
</script>
