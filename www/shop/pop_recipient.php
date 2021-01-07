<?php
include_once('./_common.php');
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가


$url = "https://eroumcare.com/pen/pen2000/pen2000/selectPen2000ListAjaxByShop.do?usrId=" . $_SESSION['ss_mb_id'] . "&start=1&length=500&draw=1";
$curl = curl_init();
$timeout = 5; // 0으로 하면 시간제한이 없다.
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
$result =  curl_exec($curl);
$data = json_decode($result, true)['data'];
curl_close($curl);
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
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<script src="<?php echo G5_JS_URL;?>/common.js"></script>
</head>
<style>
body, input, textarea, select, button, table {
    font-size: 12px;
}
</style>


<head>

<body>

<div class="pop_top_area">
	<p>수급자 정보</p>
	<div class="btn_area"><a href="javascript:void(0);" onclick="self.close();" attr-a="onclick : attr-a"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu_x.png" alt="" /></a></div>
	<div class="search_area">
		<select name="" id="">
			<option>수급자구분</option>
			<option>일반 15%</option>
			<option>감경 9%</option>
			<option>감경 6%</option>
			<option>의료 6%</option>
			<option>기초 0%</option>
		</select>
		<input type="text" name="some_name" value="" id="some_name"/>
		<a href="#">검색</a>
	</div>
</div>
<div class="pop_list">
	<ul id="recipient_list">
		<?php 
		for ($i=0; $i<count($data); $i++) { 
			echo '<li>
				<table>
					<tr>
						<td>수급자명</td>
						<td>' . $data[$i]['penNm'] . '</td>
					</tr>
					<tr>
						<td>본인부담금율</td>
						<td>' . $data[$i]['penTypeNm'] . '</td>
					</tr>
					<tr>
						<td>유효기간 만료일</td>
						<td>' . $data[$i]['penExpiDtm'] . '</td>
					</tr>
					<tr>
						<td>적용구간 만료일</td>
						<td>' . $data[$i]['penAppEdDtm'] . '</td>
					</tr>
					<tr>
						<td>대여기간 만료일</td>
						<td>' . $data[$i]['regDt'] . '</td>
					</tr>
				</table>
				<a href="#" class="sel_address" title="선택">선택</a>
				</li>';
			}
		?>
	</ul>
</div>

</body>
</html>

<script>
$(function() {
    $(".sel_address").on("click", function() {
		if ($data.empty == false) {
			window.opener.selected_recipient($data);
		}
		
        window.close();
    });

});
</script>
