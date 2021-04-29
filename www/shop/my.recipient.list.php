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
	$sendData["pageNum"] = ($_GET["page"]) ? $_GET["page"] : 1;
	$sendData["pageSize"] = $sendLength;

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
	if($res["data"]){
		$list = $res["data"];
	}

	# 페이징
	$totalCnt = $res["total"];
	$pageNum = $sendData["pageNum"]; # 페이지 번호
	$listCnt = $sendLength; # 리스트 갯수 default 15

	$b_pageNum_listCnt = 5; # 한 블록에 보여줄 페이지 갯수 5개
	$block = ceil($pageNum/$b_pageNum_listCnt); # 총 블록 갯수 구하기
	$b_start_page = ( ($block - 1) * $b_pageNum_listCnt ) + 1; # 블록 시작 페이지
	$b_end_page = $b_start_page + $b_pageNum_listCnt - 1;  # 블록 종료 페이지
	$total_page = ceil( $totalCnt / $listCnt ); # 총 페이지
	// 총 페이지 보다 블럭 수가 만을경우 블록의 마지막 페이지를 총 페이지로 변경
	if ($b_end_page > $total_page){
		$b_end_page = $total_page;
	}
	$total_block = ceil($total_page/$b_pageNum_listCnt);

?>
<style>
    .a2{
        position: absolute; width: 100px; height: 35px; line-height: 35px; font-size: 12px; color: #FFF;
        text-align: center; top: 50%; margin-top: -17.5px; right: 0;
    }

</style>
<script>
   function excelform(url){
       var opt = "width=600,height=450,left=10,top=10";
        window.open(url, "win_excel", opt);
        return false;
    }
</script>

<!-- 수급자 일괄등록  -->

	<!-- 210204 수급자목록 -->
	<div id="myRecipientListWrap">
		<div class="titleWrap">
			수급자 목록
			<a href="./my.recipient.write.php" class="a1" title="수급자 등록">수급자 등록</a>
			<a href="./recipientexcel.php" onclick="return excelform(this.href);" target="_blank" class="a2" title="수급자일괄등록">수급자일괄등록</a>
		</div>
        <?php if(!$list){ ?>
        <style>
            .no_content{
                width:100%; height:100px; text-align:center;margin-top:150px;
            }
        </style>
        <div class="no_content">
            내용이 없습니다
        </div>
        <?php } ?>
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
						<p class="labelName">휴대폰</p>
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

		<div class="list-page list-paging">
			<ul class="pagination pagination-sm en">
				<li></li>
			<?php if($block > 1){ ?>
				<li><a href="?page=<?=($b_start_page-1)?>"><i class="fa fa-angle-left"></i></a></li>
			<?php } ?>

			<?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?>
				<li class="<?=($j == $pageNum) ? "active" : ""?>">
					<a href="?page=<?=$j?>"><?=$j?></a>
				</li>
			<?php } ?>
			<?php if($block < $total_block){ ?>
				<li><a href="?page=<?=($b_end_page+1)?>"><i class="fa fa-angle-right"></i></a></li>
			<?php } ?>
				<li></li>
			</ul>
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
									html += '<p class="labelName">휴대폰</p>';
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
