<?php
include_once("./_common.php");
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<title><?php $title; ?></title>
<link rel="stylesheet" href="/adm/css/popup.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
<link rel="stylesheet" href="<?php echo G5_PLUGIN_URL;?>/jquery-ui/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="<?php echo G5_PLUGIN_URL;?>/jquery-ui/style.css" type="text/css">
<link rel="stylesheet" href="<?php echo G5_JS_URL ?>/font-awesome/css/font-awesome.min.css">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<script src="<?php echo G5_JS_URL;?>/common.js"></script>
</head>

<style>
  .modal-open {
    overflow: hidden;
  }
  html, body { width: 100%; height: 100%; float: left; background-color: #FFF; padding: 0; }
  .pop_top_area { width: 100%; left: 0; top: 0; }
  .pop_top_area .btn_area a { top: 15px; right: 15px; }
  body, input, textarea, select, button, table {
    font-size: 12px;
  }
  .empty_list {
    margin-top: 250px;
    text-align: center;
    font-size: 1.2em;
  }
  .pop_list ul>li a.disabled {
    background: #fff;
    color: #333;
    cursor: default;
	width:100%
	left:50%
	margin-left:-50%
  }
 .pop_list {
    width: 100%;
    max-width: 1190px;
    margin: 60px auto 0;
	z-index: 2;
	text-align:center;
	position: relative; 
}
.pop_list ul>li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    position: relative;
	display: inline-block; 
	width:170px;
	text-align:left;
}


</style>

<body>

<div class="pop_top_area">
  <p>제조사(직배송) 업체</p>
  <div class="btn_area"><a href="#none" id="thisPopupCloseBtn" attr-a="onclick : attr-a"><a href="javascript:history.back();"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu_x.png" alt="" /></a></div>  
</div>
<div class="pop_list">
  <ul id="recipient_list">
  <?php	$sql = "SELECT TRIM(it_maker) AS it_maker2 FROM g5_shop_item 
			WHERE it_maker != '' 
			AND it_maker NOT LIKE '%가격인상%' 
			AND it_maker NOT LIKE '%,%' 
			AND it_maker NOT LIKE '% 김포점%'  
			AND it_maker NOT LIKE '%미키코리아(%'  
			AND it_maker NOT LIKE '%미키코리아메%' 
			AND it_maker NOT LIKE '케어맥스'
			AND it_maker NOT LIKE '(주)케어맥스코리아'
			GROUP BY it_maker2";
		$result = sql_query($sql);
		while($row = sql_fetch_array($result)){
			$it_makers = explode(">",$row["it_maker2"]);
			$it_maker = str_replace(" ","", $it_makers[(count($it_makers)-1)]);
			echo "<li>".$it_maker."</li>";
		}
?>
<li>&nbsp;</li><li>&nbsp;</li><li>&nbsp;</li><li>&nbsp;</li>
  </ul>

</div>
</body>
</html>

<script>


</script>
