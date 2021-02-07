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

	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];
	$sendData["pageNum"] = 1;
	$sendData["pageSize"] = $sendLength;

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

	<!-- 210204 수급자목록 -->
	<div id="myRecipientListWrap">
		<div class="titleWrap">수급자 목록<a href="./my.recipient.write.php" title="수급자 등록">수급자 등록</a></div>
		
		<div class="itemWrap">
		<?php if($list){ ?>
			<?php foreach($list as $data){ ?>
				<ul class="item">
					<li class="btnWrap">
						<button type="button" class="updateBtn" data-id="<?=$data["penId"]?>">수정</button><button type="button" class="delBtn" data-id="<?=$data["penId"]?>">삭제</button>
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
						<p class="value"><?=($data["penProConNum"]) ? $data["penProConNum"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">담당자</p>
						<p class="value"><?=($data["usrNm"]) ? $data["usrNm"] : "-"?></p>
					</li>
					<li class="info">
						<p class="labelName">처리현황</p>
						<p class="value"><?=($data["appCdNm"]) ? $data["appCdNm"] : "-"?></p>
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
				var sendData = {
					usrId : "<?=$member["mb_id"]?>",
					entId : "<?=$member["mb_entId"]?>",
					pageNum : page,
					pageSize : "<?=$sendLength?>"
				}
				
				$.ajax({
					url : "./ajax.my.recipient.list.php",
					type : "POST",
					async : false,
					data : sendData,
					success : function(result){
						result = JSON.parse(result);
						if(result.errorYN == "Y"){
							alert(result.message);
						} else {
							var list = result.data;
							
							if(!list.length){
								alert("데이터가 존재하지 않습니다.");
							} else {
								$.each(list, function(key, data){
									var html = '';
									
									html += '<ul class="item">';
									html += '<li class="btnWrap">';
									html += '<button type="button" class="updateBtn" data-id="' + data.penId + '">수정</button>';
									html += '<button type="button" class="delBtn" data-id="' + data.penId + '">삭제</button>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">수급자명</p>';
									html += '<p class="value">' + ((data.penNm) ? data.penNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">장기요양인정번호</p>';
									html += '<p class="value">' + ((data.penLtmNum) ? data.penLtmNum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">본인부담금율</p>';
									html += '<p class="value">' + ((data.penTypeNm) ? data.penTypeNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">등급</p>';
									html += '<p class="value">' + ((data.penRecGraNm) ? data.penRecGraNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">유효기간</p>';
									html += '<p class="value">' + ((data.penExpiDtm) ? data.penExpiDtm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">휴대전화</p>';
									html += '<p class="value">' + ((data.penConNum) ? data.penConNum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">일반전화</p>';
									html += '<p class="value">' + ((data.penConPnum) ? data.penConPnum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">보호자명</p>';
									html += '<p class="value">' + ((data.penProNm) ? data.penProNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">보호자연락처</p>';
									html += '<p class="value">' + ((data.penProConNum) ? data.penProConNum : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">담당자</p>';
									html += '<p class="value">' + ((data.usrNm) ? data.usrNm : "-") + '</p>';
									html += '</li>';
									html += '<li class="info">';
									html += '<p class="labelName">처리현황</p>';
									html += '<p class="value">' + ((data.appCdNm) ? data.appCdNm : "-") + '</p>';
									html += '</li>';
									html += '</ul>';
									
									$("#myRecipientListWrap > .itemWrap").append(html);
								});
								
								$("#myRecipientListWrap > .moreBtnWrap > button").attr("data-page", page);
							}
						}
					}
				});
			});
			
			$(document).on("click", "#myRecipientListWrap > .itemWrap > .item > li.btnWrap > .updateBtn", function(){
				var id = $(this).attr("data-id");
				
				window.location.href = "./my.recipient.update.php?id=" + id;
			});
			
			$(document).on("click", "#myRecipientListWrap > .itemWrap > .item > li.btnWrap > .delBtn", function(){
				var id = $(this).attr("data-id");
				
				if(confirm("해당 데이터를 삭제하시겠습니까?")){
					$.ajax({
						url : "./ajax.my.recipient.delete.php",
						type : "POST",
						async : false,
						data : {
							id : id
						},
						success : function(result){
							result = JSON.parse(result);
							
							if(result.errorYN == "Y"){
								alert(result.message);
							} else {
								window.location.reload();
							}
						}
					});
				}
			});
			
		})
	</script>

<?php include_once("./_tail.php"); ?>