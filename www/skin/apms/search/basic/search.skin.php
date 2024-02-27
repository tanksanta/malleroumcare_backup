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

	# 210204 재고조회
	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];

	$prodsSendData = [];

	for($i = 0; $i < $list_cnt; $i++){
		$stockQtyList[$list[$i]["it_id"]] = 0;

		if($list[$i]["optionList"]){
			foreach($list[$i]["optionList"] as $optionData){
				$prodsData = [];
				$prodsData["prodId"] = $list[$i]["it_id"];
				$prodsData["prodColor"] = explode(chr(30), $optionData)[0];
				$prodsData["prodSize"] = explode(chr(30), $optionData)[1];

				array_push($prodsSendData, $prodsData);
			}
		} else {
			$prodsData = [];
			$prodsData["prodId"] = $list[$i]["it_id"];
			$prodsData["prodColor"] = "";
			$prodsData["prodSize"] = "";

			array_push($prodsSendData, $prodsData);
		}
	}

	$sendData["prods"] = $prodsSendData;

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
	.textFitted {line-height:13px;}
</style>

<div class="productListWrap" style="margin-top: 30px;">
	<ul>
	<?php for($i=0; $i < $list_cnt; $i++){ ?>
	<?php
  $img = apms_it_thumbnail($list[$i], 400, 400, false, true);

  if(!$img["src"] && $list[$i]["it_img1"]) {
    $img["src"] = G5_DATA_URL."/item/{$list[$i]["it_img1"]}";
    $img["org"] = G5_DATA_URL."/item/{$list[$i]["it_img1"]}";
  }

  if(!$img["src"]) {
    $img["src"] = G5_URL."/shop/img/no_image.gif";
  }
//예약상품 품절표시 
  $soldout_ck = false;
  if($list[$i]["pt_end"] > 0){
	$sql2 = "SELECT COUNT(a.od_id) as buy_count FROM `g5_shop_order` AS a 
	INNER JOIN `g5_shop_cart` AS b ON a.od_id = b.od_id AND b.it_id='".$list[$i]["it_id"]."' AND b.ct_status NOT IN ('주문무효','취소')";
	$row2 = sql_fetch($sql2);
	$soldout_ck = ($list[$i]["it_stock_qty"] > $row2["buy_count"])? false : true;
  }
  $gubun = $cate_gubun_table[substr($list[$i]["ca_id"], 0, 2)];
  $gubun_text = '판매';
  if($gubun == '01') $gubun_text = '대여';
  else if($gubun == '02') $gubun_text = '비급여';
  else if($gubun == '03') $gubun_text = '보장구';
	?>
		<li class="<?=$list[$i]["it_id"]?>" data-ca="<?=substr($list[$i]["ca_id"], 0, 2)?>">
			<a class="it_link" href="<?=$list[$i]["href"]?>">
        <?php if($list[$i]["prodSupYn"] == "N") { ?>
          <p class="sup">비유통 상품</p>
        <?php } ?>
		<?php if($list[$i]["it_10_subj"] == "new") { ?>
        <p class="sup" style="left: 15px;background-color: #4568E3;">신규고시</p>
        <?php } ?>
        <div class="img_wrap">
          <p class="img">
            <?php if($img["src"]){ ?>
            <img src="<?=$img["src"]?>" alt="<?=$list[$i]["it_name"]?>_상품이미지">
            <?php } ?>
          </p>
          <?php if(json_decode($list[$i]["it_img_3d"], true)) { ?>
          <div class="img_3d">
            <img src="<?=G5_IMG_URL?>/item3dviewVisual.jpg">
          </div>
          <?php } ?>
          <?php if($member["mb_id"]) { /* ?>
          <button class="btn_wishlist <?=($wishlist[$list[$i]['it_id']] ? 'active' : '')?>" data-id="<?=$list[$i]['it_id']?>"><i class="fa fa-star" aria-hidden="true"></i></button>
          <?php */ } ?>
          <?php if($list[$i]["it_expected_warehousing_date"] !== "") { ?>
          <div class="item-expected-warehousing-date box"  style="height:12%;width:99.4%;top:105.5%;position:absolute;"><?php echo $list[$i]["it_expected_warehousing_date"];?></div>
          <?php } ?>
		  <?php if($list[$i]["it_expected_warehousing_date"] == "" && $soldout_ck){ ?>
					<div class="item-expected-warehousing-date box"  style="height:12%;width:99.4%;top:105.5%;position:absolute;">재고 소진으로 판매 종료</div>
			<?php } ?>
        </div>
        <p class="name"><?=$list[$i]["it_name"]?></p>
        <?php if($list[$i]["it_model"]) { ?>
        <p class="info"><?=$list[$i]["it_model"]?></p>
        <?php } ?>
        <ul class="detailInfo">
          <?php if(trim($list[$i]["prodSym"])) { ?>
          <li>
            <span class="infoLabel">
              <span>·</span>
              <span>재질</span>
            </span>
            <span class="info">: <?=$list[$i]["prodSym"]?></span>
          </li>
          <?php } ?>
          <?php if(trim($list[$i]["prodSizeDetail"])) { ?>
          <li>
            <span class="infoLabel">
              <span>·</span>
              <span>사이즈 </span>
            </span>
            <span class="info">: <?=$list[$i]["prodSizeDetail"]?></span>
          </li>
          <?php } ?>
          <?php if(trim($list[$i]["prodWeig"])) { ?>
          <li>
            <span class="infoLabel">
              <span>·</span>
              <span>중량</span>
            </span>
            <span class="info">: <?=$list[$i]["prodWeig"]?></span>
          </li>
          <?php } ?>
        </ul>
        <?php
        if ($_COOKIE["viewType"] !== "basic" && !in_array($member['mb_type'], ['partner', 'normal'])) {
        ?>
          <p class="discount">
            <?php if (substr($list[$i]["ca_id"],0,2) != '70') { // 비급여인 경우 급여가 숨김 ?>
            <?=number_format($list[$i]["it_cust_price"])?>원 <span class="txt_color_green">급여가</span>
            <?php } ?>
          </p>
        <?php } ?>
        <p class="price">
        <?php
        if ($member["mb_id"]) {
          if ($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) {
            if(substr($list[$i]["ca_id"],0,2) == '70')
                echo number_format($list[$i]["it_cust_price"])."원";
            else
                echo number_format($list[$i]["it_cust_price"])."원 <span class='txt_color_green'>급여가</span>";
          } else {
            if ($list[$i]["entprice"]) {
              echo number_format($list[$i]["entprice"])."원";
              if (!is_benefit_item($list[$i])) {
                  echo "<span class='txt_color_orange'>판매가</span>";
              }
            } else if ($member["mb_level"] == "3") {
              //사업소 가격
              echo number_format($list[$i]["it_price"])."원";
              if (!is_benefit_item($list[$i])) {
                  echo "<span class='txt_color_orange'>판매가</span>";
              }
            } else if ($member["mb_level"] == "4") {
              //우수 사업소 가격
              echo ($list[$i]["it_price_dealer2"]) ? number_format($list[$i]["it_price_dealer2"])."원" : number_format($list[$i]["it_price"])."원 (사업소 판매가)";
            } else {
              echo number_format($list[$i]["it_price"])."원";
              if (!is_benefit_item($list[$i])) {
                echo "<span class='txt_color_orange'>판매가</span>";
              }
            }
          }
        } else {
          echo number_format($list[$i]["it_cust_price"]).'원';
          if (!is_benefit_item($list[$i])) {
            echo "<span class='txt_color_orange'>판매가</span>";
          }
        }
        ?>
        </p>
			<div class="it_type_box">
        <?php if($list[$i]['it_type1']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type1_color']?>; color:<?=$default['de_it_type1_color']?>;"><?=$default['de_it_type1_name']?></p><?php } ?>
        <?php if($list[$i]['it_type2']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type2_color']?>; color:<?=$default['de_it_type2_color']?>;"><?=$default['de_it_type2_name']?></p><?php } ?>
        <?php if($list[$i]['it_type3']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type3_color']?>; color:<?=$default['de_it_type3_color']?>;"><?=$default['de_it_type3_name']?></p><?php } ?>
        <?php if($list[$i]['it_type4']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type4_color']?>; color:<?=$default['de_it_type4_color']?>;"><?=$default['de_it_type4_name']?></p><?php } ?>
        <?php if($list[$i]['it_type5']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type5_color']?>; color:<?=$default['de_it_type5_color']?>;"><?=$default['de_it_type5_name']?></p><?php } ?>
		<?php if($list[$i]['it_type6']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type6_color']?>; color:<?=$default['de_it_type6_color']?>;"><?=$default['de_it_type6_name']?></p><?php } ?>
        <?php if($list[$i]['it_type7']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type7_color']?>; color:<?=$default['de_it_type7_color']?>;"><?=$default['de_it_type7_name']?></p><?php } ?>
        <?php if($list[$i]['it_type8']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type8_color']?>; color:<?=$default['de_it_type8_color']?>;"><?=$default['de_it_type8_name']?></p><?php } ?>
        <?php if($list[$i]['it_type9']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type9_color']?>; color:<?=$default['de_it_type9_color']?>;"><?=$default['de_it_type9_name']?></p><?php } ?>
        <?php if($list[$i]['it_type10'] || $soldout_ck){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type10_color']?>; color:<?=$default['de_it_type10_color']?>;"><?=$default['de_it_type10_name']?></p><?php } ?>
        <?php if($list[$i]['it_type11']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type11_color']?>; color:<?=$default['de_it_type11_color']?>;"><?=substr($list[$i]['it_deadline'],0,5)." ".$default['de_it_type11_name']?></p><?php } ?>
		<?php if($list[$i]['it_type12']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type12_color']?>; color:<?=$default['de_it_type12_color']?>;"><?=$default['de_it_type12_name']?></p><?php } ?>
		<?php if($list[$i]['it_type13']){ ?><p class="p_box" style="border:1px solid <?=$default['de_it_type13_color']?>; color:<?=$default['de_it_type13_color']?>;"><?=$default['de_it_type13_name']?></p><?php } ?>
			</div>
        <?php
        $tag_list = apms_get_text($list[$i]['pt_tag']);
        if($tag_list) {
        ?>
        <p class="tag">
          <?php
          $tag = explode(",", $tag_list);
          foreach($tag as $val) {
            echo '<span class="hash-tag" style="display:none;">#'.$val.'</span>';
          }
          ?>
        </p>
        <?php
        }
        ?>
      </a>
		</li>
	<?php } ?>
	</ul>
</div>

<script type="text/javascript">
	$(function(){

	<?php if($member["mb_id"] && $_COOKIE["viewType"] != "basic" && $_COOKIE['SHOW_MY_STOCK'] != 'OFF'){ ?>
		var sendData = <?=json_encode($sendData, JSON_UNESCAPED_UNICODE)?>;

		$.ajax({
			url : "/apiEroum/stock/selectList.php",
			type : "POST",
			async : false,
			data : sendData,
			success : function(result){
				$.each(result, function(it_id, cnt){
					var label = "재고 보유";
					if($("." + it_id).attr("data-ca") == "20"){
						label = "보유 대여 재고";
					}

					$("." + it_id).find("a").append('<p class="cnt"><span>' + label + '</span><span class="right">' + cnt + '개</span></p>');
				});
			}
		});
	<?php } ?>

	})
</script>
<script src="/js/textFit.js"></script>
<script>
$(document).ready(function(){
	if($(window).width() > 1397){
			$(".box").css({"height":"13%","top":"105.5%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:9, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 1198){
			$(".box").css({"height":"13%","top":"106%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:7, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});				
		}else if($(window).width() > 960){
			$(".box").css({"height":"14%","top":"106%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:8, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});				
		}else if($(window).width() > 800){
			$(".box").css({"height":"8%","top":"103.5%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:12, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 700){
			$(".box").css({"height":"9%","top":"104%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:9, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 600){
			$(".box").css({"height":"12%","top":"106%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:9, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 450){
			$(".box").css({"height":"14%","top":"106.5%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:8, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 350){
			$(".box").css({"height":"15%","top":"107%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:7, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});
		}else{
			$(".box").css({"height":"16%","top":"108%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:6, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});
		}	
	window.addEventListener("resize", function() {
		//alert($(window).width());
		if($(window).width() > 1397){
			$(".box").css({"height":"13%","top":"105.5%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:9, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 1198){
			$(".box").css({"height":"13%","top":"106%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:7, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});				
		}else if($(window).width() > 960){
			$(".box").css({"height":"14%","top":"106%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:8, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});				
		}else if($(window).width() > 800){
			$(".box").css({"height":"8%","top":"103.5%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:12, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 700){
			$(".box").css({"height":"9%","top":"104%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:9, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 600){
			$(".box").css({"height":"12%","top":"106%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:9, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 450){
			$(".box").css({"height":"14%","top":"106.5%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:8, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});		
		}else if($(window).width() > 350){
			$(".box").css({"height":"15%","top":"107%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:7, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});
		}else{
			$(".box").css({"height":"16%","top":"108%"});
			textFit(document.getElementsByClassName('box'), {minFontSize:6, maxFontSize:17,alignHoriz: true, alignVert: true, multiLine: true});
		}	
	})
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
