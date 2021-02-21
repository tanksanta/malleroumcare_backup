<?php

	include_once("./_common.php");
	if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["pageNum"] = 1;
	$sendData["pageSize"] = 9999;
	$sendData["appCd"] = "01";

	if($_GET["penNm"]){
		$sendData["penNm"] = $_GET["penNm"];
	}

	if($_GET["penTypeCd"]){
		$sendData["penTypeCd"] = $_GET["penTypeCd"];
	}

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/recipient/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$list = [];
	if($res["data"]){
		$list = $res["data"];
	}

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
	html, body { width: 100%; height: 100%; float: left; background-color: #FFF; padding: 0; }
	.pop_top_area { width: 100%; left: 0; top: 0; }
	.pop_top_area .btn_area a { top: 15px; right: 15px; }
	.pop_list { z-index: 2; position: relative; }
	.pop_top_area .search_area button{background:#666;color:#fff;font-size:14px;line-height: 36px;height: 36px;display:inline-block;text-align: center;width:50px;}
body, input, textarea, select, button, table {
    font-size: 12px;
}
</style>

<body>
<div class="pop_top_area">
	<p>수급자 정보</p>
	<div class="btn_area"><a href="#" id="thisPopupCloseBtn" attr-a="onclick : attr-a"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu_x.png" alt="" /></a></div>
	<form class="search_area" method="get">
		<select name="penTypeCd">
			<option>수급자구분</option>
			<option value="00" <?=($_GET["penTypeCd"] == "00") ? "selected" : ""?>>일반 15%</option>
			<option value="01" <?=($_GET["penTypeCd"] == "01") ? "selected" : ""?>>감경 9%</option>
			<option value="02" <?=($_GET["penTypeCd"] == "02") ? "selected" : ""?>>감경 6%</option>
			<option value="03" <?=($_GET["penTypeCd"] == "03") ? "selected" : ""?>>의료 6%</option>
			<option value="04" <?=($_GET["penTypeCd"] == "04") ? "selected" : ""?>>기초 0%</option>
		</select>
		<input type="text" name="penNm" value="<?=$_GET["penNm"]?>">
		<button type="submit">검색</button>
	</form>
</div>
<div class="pop_list">
	<ul id="recipient_list">
	<?php if($list){ ?>
		<?php foreach($list as $data){ ?>
		<?php
			$recipient = $data["rn"]."|".$data["penId"]."|".$data["entId"]."|".$data["penNm"]."|".$data["penLtmNum"]."|".$data["penRecGraCd"]."|".$data["penRecGraNm"]."|".$data["penTypeCd"]."|".$data["penTypeNm"]."|".$data["penExpiStDtm"]."|".$data["penExpiEdDtm"]."|".$data["penExpiDtm"]."|".$data["penExpiRemDay"]."|".$data["penGender"]."|".$data["penGenderNm"]."|".$data["penBirth"]."|".$data["penAge"]."|".$data["penAppEdDtm"]."|".$data["penAddr"]."|".$data["penAddrDtl"]."|".$data["penConNum"]."|".$data["penConPnum"]."|".$data["penProNm"]."|".$data["usrId"]."|".$data["appCd"]."|".$data["appCdNm"]."|".$data["caCenYn"]."|".$data["regDtm"]."|".$data["regDt"]."|".$data["ordLendEndDtm"]."|".$data["ordLendRemDay"]."|".$data["usrNm"]."|".$data["penAppRemDay"]."|800,000원";
		?>
			<li>
			<table>
				<tr>
					<td>수급자명</td>
					<td><?=($data["penNm"]) ? $data["penNm"] : "-"?></td>
				</tr>
				<tr>
					<td>본인부담금율</td>
					<td><?=($data["penTypeNm"]) ? $data["penTypeNm"] : "-"?></td>
				</tr>
				<tr>
					<td>유효기간 만료일</td>
					<td><?=($data["penExpiDtm"]) ? $data["penExpiDtm"] : "-"?></td>
				</tr>
				<tr>
					<td>적용구간 만료일</td>
					<td><?=($data["penAppEdDtm"]) ? $data["penAppEdDtm"] : "-"?></td>
				</tr>
				<tr>
					<td>대여기간 만료일</td>
					<td><?=($data["regDt"]) ? $data["regDt"] : "-"?></td>
				</tr>
			</table>
			<a href="#" class="sel_address" data-target="<?=$recipient?>" title="선택">선택</a>
			</li>
		<?php } ?>
	<?php } ?>
	</ul>
</div>
</body>
</html>

<script>
	$(function() {

		$(".sel_address").on("click", function() {
			var penId = $(this).data("target");
			parent.selected_recipient(penId);
			$("#order_recipientBox", parent.document).hide();
		});

		$("#thisPopupCloseBtn").click(function(e){
			e.preventDefault();
			
			$("#order_recipientBox", parent.document).hide();
		});

	});
</script>
