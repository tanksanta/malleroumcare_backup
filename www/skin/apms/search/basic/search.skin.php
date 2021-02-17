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
	$stockQtyList = [];
	if($member["mb_id"]){
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
		
		# 재고조회
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, 9001);
		curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectList");
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$res = curl_exec($oCurl);
		$stockCntList = json_decode($res, true);
		curl_close($oCurl);
		
		if($stockCntList["data"]){
			foreach($stockCntList["data"] as $data){
				$stockQtyList[$data["prodId"]] += $data["quantity"];
			}
		}
	}

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
</style>

<div class="productListWrap" style="margin-top: 30px;">
	<ul>
	<?php for($i=0; $i < $list_cnt; $i++){ ?>
	<?php
										  
		$img = apms_it_thumbnail($list[$i], 400, 400, false, true);

		if(!$img["src"] && $list[$i]["it_img1"]){
			$img["src"] = G5_DATA_URL."/item/{$list[$i]["it_img1"]}";
			$img["org"] = G5_DATA_URL."/item/{$list[$i]["it_img1"]}";
		}

		if(!$img["src"]){
			$img["src"] = G5_URL."/shop/img/no_image.gif";
		}
										  
	?>
		<li>
			<a href="<?=$list[$i]["href"]?>">
			<?php if($list[$i]["prodSupYn"] == "N"){ ?>
				<p class="sup">비유통 상품</p>
			<?php } ?>
				<p class="img">
				<?php if($img["src"]){ ?>
					<img src="<?=$img["src"]?>" alt="<?=$list[$i]["it_name"]?>_상품이미지">
				<?php } ?>
				</p>
				<p class="name"><?=$list[$i]["it_name"]?></p>
			<?php if($list[$i]["it_model"]){ ?>
				<p class="info"><?=$list[$i]["it_model"]?></p>
			<?php } ?>
			<?php if($member["mb_id"]){ ?>
				<?php if($member["mb_level"] == "3"){ ?>
					<p class="price"><?=($_COOKIE["viewType"] == "basic") ? number_format($list[$i]["it_cust_price"]) : number_format($list[$i]["it_price"])?>원</p>
				<?php } else { ?>
					<p class="price"><?=number_format($list[$i]["it_price"])?>원</p>
				<?php } ?>
			<?php } else { ?>
				<p class="price"><?=number_format($list[$i]["it_cust_price"])?>원</p>
			<?php } ?>
			
			<?php if($stockQtyList[$list[$i]["it_id"]]){ ?>
				<p class="cnt">
					<span>재고 보유</span>
					<span class="right"><?=$stockQtyList[$list[$i]["it_id"]]?>개</span>
				</p>
			<?php } ?>
			</a>
		</li>
	<?php } ?>
	</ul>
</div>

<script>
$(document).ready(function(){
	$('.list-wrap').imagesLoaded(function(){
		$('.list-wrap .item-content').matchHeight();
	});
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
