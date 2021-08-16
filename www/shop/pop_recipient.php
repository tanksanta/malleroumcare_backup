<?php
	include_once("./_common.php");
	if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

  $ca_id_arr = array_filter(explode('|', $_GET['ca_id']));

	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["appCd"] = "01";

	if($_GET["penNm"]){
		$sendData["penNm"] = $_GET["penNm"];
	}

	if($_GET["penTypeCd"]&&$_GET["penTypeCd"]!=="수급자구분"){
		$sendData["penTypeCd"] = $_GET["penTypeCd"];
	}

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/recipient/selectList");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$res = json_decode($res, true);
	curl_close($oCurl);

	$list = [];
	foreach($res['data'] as $data) {
		if(!$data['penExpiDtm']) {
			continue;
		}

		// 유효기간 만료일
		$expired_dtm = substr($data['penExpiDtm'], -10);

		if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
			continue;
		}

		$list[] = $data;
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

.empty_list {
	margin-top: 250px;
    text-align: center;
    font-size: 1.2em;
}
</style>

<body>
<div class="pop_top_area">
	<p>수급자 정보</p>
	<div class="btn_area"><a href="#none" id="thisPopupCloseBtn" attr-a="onclick : attr-a"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu_x.png" alt="" /></a></div>
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
	<?php if($list) { ?>
		<?php
    foreach($list as $data) {
      $warning = [];
      if(is_array($ca_id_arr)) {
        foreach($ca_id_arr as $ca_id) {
          $limit = get_pen_category_limit($data["penLtmNum"], $ca_id);
          if($limit) {
            $cur = intval($limit['num']) - intval($limit['current']);
            if($cur <= 0) {
              // 구매불가능
              $warning_text = "\"{$limit['ca_name']}\" 구매가능 개수가 초과되었습니다.";
              if(!in_array($warning_text, $warning))
                $warning[] = $warning_text;
            }
          }
        }
      }
      $grade_year_info = get_recipient_grade_per_year($data['penId']);
			$recipient = $data["rn"]."|".$data["penId"]."|".$data["entId"]."|".$data["penNm"]."|".$data["penLtmNum"]."|".$data["penRecGraCd"]."|".$data["penRecGraNm"]."|".$data["penTypeCd"]."|".$data["penTypeNm"]."|".$data["penExpiStDtm"]."|".$data["penExpiEdDtm"]."|".$data["penExpiDtm"]."|".$data["penExpiRemDay"]."|".$data["penGender"]."|".$data["penGenderNm"]."|".$data["penBirth"]."|".$data["penAge"]."|".$data["penAppEdDtm"]."|".$data["penAddr"]."|".$data["penAddrDtl"]."|".$data["penConNum"]."|".$data["penConPnum"]."|".$data["penProNm"]."|".$data["usrId"]."|".$data["appCd"]."|".$data["appCdNm"]."|".$data["caCenYn"]."|".$data["regDtm"]."|".$data["regDt"]."|".$data["ordLendEndDtm"]."|".$data["ordLendRemDay"]."|".$data["usrNm"]."|".$data["penAppRemDay"]."|800,000원";
		?>
			<li>
			<table>
				<tr>
					<td>수급자명</td>
					<td><?=($data["penNm"]) ? $data["penNm"] : "-"?></td>
				</tr>
                <tr>
					<td>장기요양번호</td>
					<td><?=($data["penNm"]) ? $data["penLtmNum"] : "-"?></td>
				</tr>

				<tr>
					<td>본인부담금율</td>
					<td><?=($data["penTypeNm"]) ? $data["penTypeNm"] : "-"?></td>
				</tr>
				<tr>
					<td>유효기간 만료일</td>
					<td><?=($data["penExpiDtm"]) ? $data["penExpiDtm"] : "-"?></td>
				</tr>
				<!--
				<tr>
					<td>적용구간 만료일</td>
					<td><?=($data["penAppEdDtm"]) ? $data["penAppEdDtm"] : "-"?></td>
				</tr>
				<tr>
					<td>대여기간 만료일</td>
					<td><?=($data["regDt"]) ? $data["regDt"] : "-"?></td>
				</tr>
				-->
				<tr>
					<td>생년월일</td>
					<td><?php echo $data["penBirth"] ? get_text($data["penBirth"]) : "-"; ?></td>
				</tr>
        <tr>
					<td>연 사용금액</td>
					<td><?php echo number_format($grade_year_info['sum_price']); ?></td>
				</tr>
        <?php foreach($warning as $warning_text) { ?>
        <tr>
					<td colspan="2" style="color: red"><?=$warning_text?></td>
        </tr>
        <?php } ?>
			</table>
      <?php if($warning) { ?>
      <div class="warning">구매가능초과</div>
      <?php } else if($grade_year_info['sum_price'] > 1600000) { ?>
      <div class="warning">사용금액초과</div>
      <?php } else { ?>
			<a href="#" class="sel_address" data-target="<?=$recipient?>" title="선택">선택</a>
      <?php } ?>
			</li>
		<?php } ?>
	<?php } else { ?>
		<div class="empty_list">
			수급자가 없습니다.
		</div>
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
            parent.$('#mask').css({'width':'0px','height':'0px'});
		});

	});
</script>
