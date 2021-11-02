<?php

	include_once "./_common.php";

	$imgList = sql_fetch("SELECT it_img_3d FROM g5_shop_item WHERE it_id = '{$_GET["it_id"]}'")["it_img_3d"];
	$imgList = json_decode($imgList, true);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<title>상품 3D 뷰어</title>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script>
		jQuery.browser = {};
		(function () {
			jQuery.browser.msie = false;
			jQuery.browser.version = 0;
			if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
				jQuery.browser.msie = true;
				jQuery.browser.version = RegExp.$1;
			}
		})();
	</script>
	<script src="../js/j360.js"></script>

	<link rel="shortcut icon" href="/thema/eroumcare/assets/img/top_logo_icon.ico">

	<style>
		* { margin: 0; padding: 0; position: relative; box-sizing: border-box; }
		html, body { width: 100%; height: 100%; float: left; overflow: hidden; }
		#imgListWrap { width: 100%; height: 100%; float: left; display: table; table-layout: fixed; }
		#imgList { width: 100%; display: table-cell; vertical-align: middle; }
		#view_overlay { width: 100%; height: 100%; float: left; }

		#thisPopupCloseBtn { position: fixed; z-index: 100; top: 40px; right: 20px; }

		img { max-width: 100%; }

		.touch_and_move {
				animation: fadein 3s;	-moz-animation: fadein 3s; /* Firefox */-webkit-animation: fadein 3s; /* Safari and Chrome */	-o-animation: fadein 3s; /* Opera */
				position: fixed; position: absolute; width: 90%; height: 50px; line-height: 50px; z-index: 10; bottom: 100px; right:5%; border-radius: 10px; color : #fff; background-color: #4d4d4d; text-align: center; font-weight: bold; font-size: 20px;
				opacity:0;
		}
		@keyframes fadein {
			from {opacity:1;}
				to {opacity:0;}
		}

		}
	</style>
</head>

<body>
	<a href="#" id="thisPopupCloseBtn"><img src="<?=THEMA_URL?>/assets/img/btn_top_menu_x.png"></a>

	<div id="imgListWrap">
		<div id="imgList">
		<?php if($imgList){ ?>
			<?php foreach($imgList as $data){ ?>
				<img src="/data/item/<?=$data?>">
			<?php } ?>
		<?php } ?>
		</div>
			<span class="touch_and_move">화면 터치 후 좌우로 이동해보세요</span>
	</div>

	<?php if($imgList){ ?>
		<script type="text/javascript">
			$(function(){
				$("#imgList").j360();
			})
		</script>
	<?php } ?>

	<script type="text/javascript">
		$(function(){

			$("#thisPopupCloseBtn").click(function(e){
				e.preventDefault();

				$("#item3dViewBox", parent.document).hide();
			});

		});
	</script>



</body>
</html>
