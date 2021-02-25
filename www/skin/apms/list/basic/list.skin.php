<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

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

include_once(THEMA_PATH.'/side/list-cate-side.php');

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

<div id="sort-wrapper">
    <div class="dropdown">
        <a id="sortLabel" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-block">
            상품정렬
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="sortLabel">
            <li><a <?php echo ($sort == 'custom' ) ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>custom">추천순</a></li>
            <li><a <?php echo ($sort == 'it_price' && $sortodr == 'desc') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_price&amp;sortodr=desc&prodSupYn=<?=$_COOKIE["prodSupYn"]?>">높은가격순</a></li>
            <li><a <?php echo ($sort == 'it_price' && $sortodr == 'asc') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_price&amp;sortodr=asc&prodSupYn=<?=$_COOKIE["prodSupYn"]?>">낮은가격순</a></li>
            <li><a <?php echo ($sort == 'it_sum_qty') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_sum_qty&amp;sortodr=desc&prodSupYn=<?=$_COOKIE["prodSupYn"]?>">판매많은순</a></li>
            <li><a <?php echo ($sort == 'it_use_avg') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_use_avg&amp;sortodr=desc&prodSupYn=<?=$_COOKIE["prodSupYn"]?>">평점높은순</a></li>
            <li><a <?php echo ($sort == 'it_use_cnt') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_use_cnt&amp;sortodr=desc&prodSupYn=<?=$_COOKIE["prodSupYn"]?>">후기많은순</a></li>
            <li><a <?php echo ($sort == 'it_update_time') ? 'class="on" ' : '';?>href="<?php echo $list_sort_href; ?>it_update_time&amp;sortodr=desc&prodSupYn=<?=$_COOKIE["prodSupYn"]?>">최근등록순</a></li>
        </ul>
    </div>
    
    <?php
		$prodSupLabel = "유통구분";
				   
		if(!$_COOKIE["prodSupYn"]){
			$prodSupLabel = "유통구분";
		}

		if($_GET["prodSupYn"]){
			switch($_GET["prodSupYn"]){
				case "Y" :
					$prodSupLabel = "유통구분";
					break;
				case "N" :
					$prodSupLabel = "비유통구분";
					break;
				case "all" :
					$prodSupLabel = "유통+비유통구분";
					break;
			}
		} else {
			switch($_COOKIE["prodSupYn"]){
				case "Y" :
					$prodSupLabel = "유통구분";
					break;
				case "N" :
					$prodSupLabel = "비유통구분";
					break;
				case "all" :
					$prodSupLabel = "유통+비유통구분";
					break;
			}
		}
	?>
   	<div class="dropdown" style="margin-right: 10px;">
        <a id="prodSupLabel" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-block">
            <?=$prodSupLabel?>
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="prodSupLabel">
            <li><a href="<?="{$list_sort_href}"?><?=$_GET["sort"]?>&prodSupYn=Y">유통구분</a></li>
            <li><a href="<?="{$list_sort_href}"?><?=$_GET["sort"]?>&prodSupYn=N">비유통구분</a></li>
            <li><a href="<?="{$list_sort_href}"?><?=$_GET["sort"]?>&prodSupYn=all">유통+비유통구분</a></li>
        </ul>
    </div>
</div>

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
		<li class="<?=$list[$i]["it_id"]?>">
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
					<?php if($_COOKIE["viewType"] != "basic"){ ?>
						<p class="discount"><?=number_format($list[$i]["it_cust_price"])?>원</p>
					<?php } ?>
					<p class="price"><?=($_COOKIE["viewType"] == "basic") ? number_format($list[$i]["it_cust_price"]) : number_format($list[$i]["it_price"])?>원</p>
				<?php } else { ?>
					<p class="price"><?=number_format($list[$i]["it_price"])?>원</p>
				<?php } ?>
			<?php } else { ?>
				<p class="price"><?=number_format($list[$i]["it_cust_price"])?>원</p>
			<?php } ?>
			</a>
		</li>
	<?php } ?>
	</ul>
</div>
<div class="list-btn">
	<div class="list-page list-paging">
		<ul class="pagination pagination-sm en">
			<?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
		</ul>
		<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div>
</div>

<script type="text/javascript">
	$(function(){

	<?php if($member["mb_id"]){ ?>
		var sendData = <?=json_encode($sendData, JSON_UNESCAPED_UNICODE)?>;

		$.ajax({
			url : "/apiEroum/stock/selectList.php",
			type : "POST",
			async : false,
			data : sendData,
			success : function(result){
				$.each(result, function(it_id, cnt){
					$("." + it_id).find("a").append('<p class="cnt"><span>재고 보유</span><span class="right">' + cnt + '개</span></p>');
				});
			}
		});
	<?php } ?>

	})
</script>

<?php if ($is_admin) { ?>
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