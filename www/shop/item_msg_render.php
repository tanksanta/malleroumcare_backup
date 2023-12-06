<?php
if (!defined('_GNUBOARD_')) exit;

$list = [];
$total = 0;
foreach($items as $item) {
  $it = sql_fetch(" SELECT * FROM g5_shop_item WHERE it_id = '{$item['it_id']}' ");
  $gubun = $cate_gubun_table[substr($it["ca_id"], 0, 2)];
  if($gubun == '01') $gubun = '대여';
  else if($gubun == '02') $gubun = '비급여';
  else if($gubun == '03') $gubun = '보장구';
  else $gubun = '판매';

  $it['gubun'] = $gubun;

  $list[] = $it;

  if($gubun != '비급여' && $gubun != '보장구')
    $total += $it['it_cust_price'];
}

$rec_arr = explode(',', trim($ms['ms_rec']));
$recs = [];
foreach($rec_arr as $wr_id) {
  if(trim($wr_id) == '') continue;
  $sql = " select * from g5_write_info where wr_id = '$wr_id' ";
  $rec = sql_fetch($sql);

  $recs[] = $rec;
}
?>
<div class="im_wr">
  <div class="im_hd">
    <img src="<?=THEMA_URL?>/assets/img/footer_logo.png">
  </div>
  <div class="im_msg_wr">
    <p class="pen_nm"><?php echo $ms['ms_pen_nm']; ?>님</p>
    <p><?php echo $ms['mb_entNm']; ?> 사업소에서 자료가 전송되었습니다.</p>
    <?php if($ms['ms_ent_tel']) { ?>
    <a href="tel:<?php echo $ms['ms_ent_tel'] ?>" class="btn_im_tel">
      <i class="fa fa-phone" aria-hidden="true"></i>
      <?php echo $ms['ms_ent_tel']; ?> 전화연결
    </a>
    <?php } ?>
  </div>
  <div class="im_list_wr">
    <div class="im_tab_hd">요약정보</div>
    <?php foreach($list as $it) { ?>
    <div class="im_sum_row im_flex">
      <div>
      <p class="im_sum_name"><?="{$it['it_name']} ({$it['gubun']})"?></p>
      <?php if($it['gubun'] != '비급여' && $it['gubun'] != '보장구') { ?>
      <p class="personal-price">
        ※ 본인부담금 <span>15%(<?=number_format($it["it_cust_price"] * 0.15)?>원)</span>, <span>9%(<?=number_format($it["it_cust_price"] * 0.09)?>원)</span>, <span>6%(<?=number_format($it["it_cust_price"] * 0.06)?>원)</span>
      </p>
      <?php } ?>
      </div>
      <div class="im_sum_price"><?=number_format($it['it_cust_price'])?>원</div>
    </div>
    <?php } ?>
    <div class="im_sum_total">
      <p class="total_price"><?=number_format($total)?>원</p>
      <?php if($total > 0) { ?>
      <p class="personal_price">
        ※ 본인부담금 <span>15%(<?=number_format($total * 0.15)?>원)</span>, <span>9%(<?=number_format($total * 0.09)?>원)</span>, <span>6%(<?=number_format($total * 0.06)?>원)</span>
      </p>
      <?php } ?>
    </div>

    <div class="im_tab_hd">품목정보</div>
    <?php foreach($list as $it) { ?>
    <div class="im_item">
      <div class="im_flex">
        <img src="/data/item/<?=$it['it_img1']?>" alt="<?=$it['it_name']?>" onerror="this.src='/img/no_img.png';">
        <div class="im_info">
          <?php if ($show_expedted_label === 'Y') { ?>
            <p class="it_expected_warehousing_date"><?=$it['it_expected_warehousing_date']?></p>
          <?php } ?>
          <p class="it_name">
            <?php echo "{$it['it_name']} ({$it['gubun']})"; ?>
          </p>
          <p class="it_price">
            급여가 : <?php echo number_format($it["it_cust_price"]); ?>원
          </p>
          <?php if($it['gubun'] != '비급여'  && $it['gubun'] != '보장구') { ?>
          <p class="personal-price">
            ※ 본인부담금 <span>15%(<?=number_format($it["it_cust_price"] * 0.15)?>원)</span>, <span>9%(<?=number_format($it["it_cust_price"] * 0.09)?>원)</span>, <span>6%(<?=number_format($it["it_cust_price"] * 0.06)?>원)</span>
          </p>
          <?php } ?>
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
      <div class="im_btn_more">상세정보 펼쳐보기&nbsp;&nbsp;<i class="fa-solid fa-chevron-down"></i></div>
    </div>
    <?php } ?>
    <?php if($recs) { ?>
    <div class="im_tab_hd">추천정보</div>
      <?php foreach($recs as $rec) { ?>
      <div class="im_wr_content">
        <div class="head"><?=$rec['wr_subject']?></div>
        <?=$rec['wr_content']?>
      </div>
      <?php } ?>
    <?php } ?>
  </div>
</div>

<script>
$(document).on('click', '.im_btn_more', function() {
  $(this).closest('.im_item').addClass('active');
  $(this).html('상세정보 접기&nbsp;&nbsp;<i class="fa-solid fa-chevron-up"></i>').removeClass('im_btn_more').addClass('im_btn_less');
});
$(document).on('click', '.im_btn_less', function() {
  $(this).closest('.im_item').removeClass('active');
  $(this).html('상세정보 펼쳐보기&nbsp;&nbsp;<i class="fa-solid fa-chevron-down"></i>').removeClass('im_btn_less').addClass('im_btn_more');
});
</script>
