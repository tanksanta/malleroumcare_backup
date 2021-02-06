<?php

	include_once("./_common.php");
	include_once("./_head.php");

	# 회원검사
	if(!$member["mb_id"]){
		alert("접근 권한이 없습니다.");
		return false;
	}

	# 첫번째 페이지 수급자 목록
	$sendLength = 10;

	$sendData = "?usrId={$member["mb_id"]}";
	$sendData .= "&start=1";
	$sendData .= "&length={$sendLength}";
	$sendData .= "&draw=1";

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/pen/pen2000/pen2000/selectPen2000ListAjaxByShop.do{$sendData}");
	curl_setopt($oCurl, CURLOPT_POST, 0);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$res = curl_exec($oCurl);
	$list = json_decode($res, true);
	curl_close($oCurl);
	
	if($list["data"]){
		$list = $list["data"];
	}

?>

	<!-- 210204 수급자목록 -->
	<div id="myRecipientListWrap">
		<div class="titleWrap">수급자 목록<a href="./my.recipient.write.php" title="수급자 등록">수급자 등록</a></div>
		
		<div class="itemWrap">
		<?php if($list){ ?>
			<?php foreach($list as $data){ ?>
				<ul class="item">
					<li class="btnWrap">
						<button type="button" class="updateBtn" data-id="<?=$data["penId"]?>">수정</button>
						<button type="button" class="delBtn" data-id="<?=$data["penId"]?>">삭제</button>
					</li>
					<li class="info">
						<p class="labelName">수급자명</p>
						<p class="value"><?=($data["penNm"]) ? $data["penNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">장기요양인정번호</p>
						<p class="value"><?=($data["penLtmNum"]) ? $data["penLtmNum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">본인부담금율</p>
						<p class="value"><?=($data["penTypeNm"]) ? $data["penTypeNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">등급</p>
						<p class="value"><?=($data["penRecGraNm"]) ? $data["penRecGraNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">유효기간</p>
						<p class="value"><?=($data["penExpiDtm"]) ? $data["penExpiDtm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">휴대전화</p>
						<p class="value"><?=($data["penConNum"]) ? $data["penConNum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">일반전화</p>
						<p class="value"><?=($data["penConPnum"]) ? $data["penConPnum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">보호자명</p>
						<p class="value"><?=($data["penProNm"]) ? $data["penProNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">보호자연락처</p>
						<p class="value"><?=($data["penGenderNm"]) ? $data["penGenderNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">담당자</p>
						<p class="value"><?=($data["usrNm"]) ? $data["usrNm"] : "-"?></p>
					</li>
				</ul>
			<?php } ?>
		<?php } ?>
		</div>
		
		<div class="moreBtnWrap">
			<button type="button" data-page="1"><i class="fa fa-plus-circle"></i>더보기</button>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$("#myRecipientListWrap > .moreBtnWrap > button").click(function(){
				var page = Number($(this).attr("data-page")) + 1;
				var sendData = "";
				
				sendData += "?usrId=<?=$member["mb_id"]?>";
				sendData += "&start=" + page;
				sendData += "&length=<?=$sendLength?>";
				
				$.ajax({
					url : "https://eroumcare.com/pen/pen2000/pen2000/selectPen2000ListAjaxByShop.do" + sendData,
					type : "GET",
					success : function(result){
						$("#myRecipientListWrap > .moreBtnWrap > button").attr("data-page", page);
					}
				});
			});
			
		})
	</script>

<?php include_once("./_tail.php"); ?>