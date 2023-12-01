<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if(!$member['mb_id']){
  alert('회원만 이용 가능합니다.',G5_BBS_URL.'/login.php');
}

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

//include_once(THEMA_PATH.'/side/list-cate-side.php');

# 210606 위시리스트
$wishlist = [];

# 210204 재고조회
$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];

$prodsSendData = [];

for($i = 0; $i < $list_cnt; $i++) {
  $wishlist[$list[$i]['it_id']] = false;
  $stockQtyList[$list[$i]["it_id"]] = 0;

  foreach($list[$i]["optionList"] as $optionData) {
    $prodColor = $prodSize = $prodOption = '';
    $prodOptions = [];

    $io_subjects = explode(',', $list[$i]['it_option_subject']);
    $io_ids = explode(chr(30), $optionData);
    for($io_idx = 0; $io_idx < count($io_subjects); $io_idx++) {
      switch($io_subjects[$io_idx]) {
        case '색상':
          $prodColor = $io_ids[$io_idx];
          break;
        case '사이즈':
          $prodSize = $io_ids[$io_idx];
          break;
        default:
          $prodOptions[] = $io_ids[$io_idx];
          break;
      }
    }
    
    if ($prodOptions && count($prodOptions)) {
      $prodOption = implode('|', $prodOptions);
    }
    
    $prodsData = array(
      'prodId' => $list[$i]["it_id"],
      'prodColor' => $prodColor,
      'prodSize' => $prodSize,
      'prodOption' => $prodOption
    );
    array_push($prodsSendData, $prodsData);
  }

  if(!$list[$i]["optionList"]) {
    $prodsData = array(
      'prodId' => $list[$i]["it_id"],
      'prodColor' => '',
      'prodSize' => '',
      'prodOption' => ''
    );
    array_push($prodsSendData, $prodsData);
  }
}

$sendData["prods"] = $prodsSendData;

$it_keys = array_keys($wishlist);
$wish_result = sql_query("SELECT it_id from {$g5['g5_shop_wish_table']} where mb_id = {$member['mb_id']} and it_id in ('"
.implode("', '", $it_keys).
"')");

while($wish_row = sql_fetch_array($wish_result)) {
  $wishlist[$wish_row['it_id']] = true;
}
?>
<style type="text/css">
	.textFitted {line-height:13px;}
</style>
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

<div class="productListWrap" style="margin-top: 30px;">
  <ul>
    <style media="screen">
    .img_3d {position: absolute; width: 50px; height: 40px; margin-top:5px; line-height: 38px; z-index: 10; top: 0; right: 5px; cursor: pointer; border-radius: 10px; background-color: #FFF; border: 1px solid #E2E2E2; text-align: center; font-weight: bold; font-size: 13px; }
    </style>
    <?php if(!$list_cnt) { ?>
    <style>
    .no_content{
      width:100%; height:100px; text-align:center;margin-top:50px;
    }
    </style>
    <div class="no_content">
      내용이 없습니다
    </div>
    <?php } ?>
    <?php for($i=0; $i < $list_cnt; $i++) { ?>
    <?php
	//예약상품 품절표시 
	  $soldout_ck = false;
	  if($list[$i]["pt_end"] > 0){
		$sql2 = "SELECT COUNT(a.od_id) as buy_count FROM `g5_shop_order` AS a 
		INNER JOIN `g5_shop_cart` AS b ON a.od_id = b.od_id AND b.it_id='".$list[$i]["it_id"]."' AND b.ct_status NOT IN ('주문무효','취소')";
		$row2 = sql_fetch($sql2);
		$soldout_ck = ($list[$i]["it_stock_qty"] > $row2["buy_count"])? false : true;
	  }
      $img = apms_it_thumbnail($list[$i], 400, 400, false, true);

      if(!$img["src"] && $list[$i]["it_img1"]){
        $img["src"] = G5_DATA_URL."/item/{$list[$i]["it_img1"]}";
        $img["org"] = G5_DATA_URL."/item/{$list[$i]["it_img1"]}";
      }

      if(!$img["src"]){
        $img["src"] = G5_URL."/shop/img/no_image.gif";
      }

    ?>
    <?php
      $add_height="";
      if($is_admin=="super") {
        $add_height='style="height: 500px;"';
      }
    ?>
    <li class="<?=$list[$i]["it_id"]?>" data-ca="<?=substr($list[$i]["ca_id"], 0, 2)?>" >
      <?php 
      // 우선순위 조정
      if ($is_admin=="super" && $sort == 'custom') {
        $sql_custom_index = "select *
                            from g5_shop_item_custom_index
                            where it_id = '{$list[$i]['it_id']}' and ca_id = '{$ca_id}'";
        $row = sql_fetch($sql_custom_index);
        $custom_index = "<div class='custom-index'>
                        <span>우선순위</span><input data-item-id='{$list[$i]['it_id']}' type='text' style='border: 1px solid #999; float: right; text-align: center;' value='{$row['custom_index']}' oninput='this.value = this.value.replace(/[^0-9.]/g, \"\").replace(/(\..*)\./g, \"$1\");'>
                        </div>";
        echo $custom_index;
      }
      ?>
      <a class="it_link" href="<?=$list[$i]["href"]?>">
        <?php if($list[$i]["prodSupYn"] == "N") { ?>
        <p class="sup">비유통 상품</p>
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
          <div class="item-expected-warehousing-date box" style="height:12%;width:99.4%;top:105.5%;position:absolute;"><?php echo $list[$i]["it_expected_warehousing_date"];?></div>
          <?php } ?>
		  <?php if($list[$i]["it_expected_warehousing_date"] == "" && $soldout_ck){ ?>
					<div class="item-expected-warehousing-date box" style="height:12%;width:99.4%;top:105.5%;position:absolute;">재고 소진으로 판매 종료</div> 
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
              <span>사이즈</span>
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
            <?php if ($ca_id != '70') { // 비급여인 경우 급여가 숨김 ?>
            <?=number_format($list[$i]["it_cust_price"])?>원 <span class="txt_color_green">급여가</span>
            <?php } ?>
          </p>
        <?php
        }
        ?>
        <p class="price">
        <?php
        if ($member["mb_id"]) {
          if ($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) {
            if($ca_id == '70')
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

<div class="list-btn">
  <?php if ($is_admin=="super" && $sort == 'custom') { ?>
  <div style="float: right">
    <button type="button" style="background: #333; color: #fff; padding: 5px 15px;" onclick="submitCustomIndex('<?php echo $ca_id ?>')">우선순위 저장</button>
  </div>
  <?php } ?>
  <div class="list-page list-paging">
    <ul class="pagination pagination-sm en">
      <?php echo apms_paging(5, $page, $total_page, $list_page); ?>
    </ul>
    <div class="clearfix"></div>
  </div>
  <div class="clearfix"></div>
</div>
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
<script type="text/javascript">
// Wishlist
function apms_wishlist(it_id, $this) {
  if(!it_id) {
    alert("코드가 올바르지 않습니다.");
    return false;
  }

  if($this.hasClass('active')) {
    $.ajax({
      url : "./wishupdate.php?w=r&it_id="+encodeURIComponent(it_id),
      type : "GET",
      success : function(result){
        $this.prop('disabled', false);
        $this.removeClass('active');
        /*if(confirm("위시리스트에 등록되었습니다.\n\n바로 확인하시겠습니까?")){
          window.location.href = "./wishlist.php";
        }*/
      }
    });
  } else {
    $.ajax({
      url : "./wishupdate.php",
      type : "POST",
      data : {
        it_id : it_id
      },
      success : function(result){
        $this.prop('disabled', false);
        $this.addClass('active');
        /*if(confirm("위시리스트에 등록되었습니다.\n\n바로 확인하시겠습니까?")){
          window.location.href = "./wishlist.php";
        }*/
      }
    });
  }
  return false;
}

  $(function(){
    $('.btn_wishlist').click(function (e) {
      e.stopPropagation();
      e.preventDefault();
      var it_id = $(this).data('id');
      $(this).prop('disabled', true);
      apms_wishlist(it_id, $(this));
      return false;
    });

  <?php if($member["mb_id"] && $_COOKIE["viewType"] != "basic" && $_COOKIE['SHOW_MY_STOCK'] != 'OFF'){ ?>
    var sendData = <?=json_encode($sendData, JSON_UNESCAPED_UNICODE)?>;

    $.ajax({
      url : "/apiEroum/stock/selectList.php",
      type : "POST",
      async : false,
      data : sendData,
      success : function(result){
                console.log(result);
        $.each(result, function(it_id, cnt){
          var label = "내 보유 재고";
          if($("." + it_id).attr("data-ca") == "20"){
            label = "내 대여 재고";
          }

          $("." + it_id).find(".it_link .img_wrap").append('<p class="cnt"><span>' + label + ' : ' + cnt + '개</span></p>');
        });
      }
    });
  <?php } ?>

  })
</script>

<?php if ($is_admin=="super") { ?>
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
