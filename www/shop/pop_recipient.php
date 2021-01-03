<?php
include_once('./_common.php');
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
	<div class="btn_area"><a href="#"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu_x.png" alt="" /></a></div>
	
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
	<ul>
		<li>
			<table>
				<tr>
					<td>수급자명</td>
					<td>홍길동</td>
				</tr>
				<tr>
					<td>본인부담금율</td>
					<td>기초 0%</td>
				</tr>
				<tr>
					<td>유효기간 만료일</td>
					<td>2020-02-02</td>
				</tr>
				<tr>
					<td>적용구간 만료일</td>
					<td>2020-02-02</td>
				</tr>
				<tr>
					<td>대여기간 만료일</td>
					<td>2020-02-02</td>
				</tr>
			</table>
			<a href="#">선택</a>
		</li>
		<li>
			<table>
				<tr>
					<td>수급자명</td>
					<td>홍길동</td>
				</tr>
				<tr>
					<td>본인부담금율</td>
					<td>기초 0%</td>
				</tr>
				<tr>
					<td>유효기간 만료일</td>
					<td>2020-02-02</td>
				</tr>
				<tr>
					<td>적용구간 만료일</td>
					<td>2020-02-02</td>
				</tr>
				<tr>
					<td>대여기간 만료일</td>
					<td>2020-02-02</td>
				</tr>
			</table>
			<a href="#">선택</a>
		</li>
	</ul>
</div>

</body>
</html>