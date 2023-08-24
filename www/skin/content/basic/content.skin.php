<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$content_skin_url.'/style.css">', 0);
?>
<?php
if($co['co_id'] == "privacy" || $co['co_id'] == "provision"){?>
<style>
	.popup_box2 {
		display: none;
		position: fixed;
		width: 100%;
		height: 100%;
		left: 0;
		top: 0;
		z-index: 9999;
		background: rgba(0, 0, 0, 0.5);			
	}
	.popup_box_con {
		padding:20px;
		position: relative;
		background: #ffffff;
		z-index: 99999;
		border-radius:10px;
	}

</style>
<?php
	if($member["mb_id"] == ""){
		echo "<div style='padding:0px 10% 0px 10%;'>";
	}
	$sql = "select * from {$g5['content_table']} where co_id like '".$co['co_id']."_%' order by co_id DESC";
	$result = sql_query($sql);

	echo '<div class="ctt_admin"><select name="" id="co_id" class="input-sm" style="width:150px;">
	<option value="">변경 이력 보기</option>';
	while($row = sql_fetch_array($result)){
		$text = str_replace("privacy_","",$row["co_id"]);
		$text = str_replace("provision_","",$text);
		$text2 = substr($text,0,4)."-".substr($text,4,2)."-".substr($text,6,2)." 이전 내용";
		echo '<option value="'.$row["co_id"].'">'.$text2.'</option>';
	}
	echo '</select> <input type="button" value="보기" onclick="return show_hist()" class="btn btn-black btn-sm"></div>';
}?>

<article id="ctt" class="ctt_<?php echo $co_id; ?>">
    <header>
        <h1><?php echo $g5['title']; ?></h1>
    </header>

    <div id="ctt_con">
        <?php echo $str; ?>
    </div>

</article>
<div id="popup_box2" class="popup_box2 list_box">    
	<div id="" class="popup_box_con" style="height:700px;margin-top:-350px;width:60%;margin-left:-30%;left:50%;top:50%; ">	
	
	<header>	
        <h3 id="co_subject" style="margin-top:0px;float:left"></h3>
	<div style="text-align:right;margin-top:-37px;width:100%;float:right;">
		<button type="button" class="btn btn-black btn-sm btn_close">닫기</button>
	</div>
    </header>
    <div id="ctt_con2" style="height:630px;overflow:auto;padding:10px 15px 0px 0px;margin-right:-18px"></div>	
	</div>	
</div>
<?php
if($co['co_id'] == "privacy" || $co['co_id'] == "provision"){
	if($member["mb_id"] == ""){
		echo "</div>";
	}
	?>
<script>
	function show_hist(){
		$("#co_subject").text("");
		$("#ctt_con2").html("");
		var id = $("#co_id").val();
		if(id == ""){
			alert("변경 이력을 선택해 주세요.");
			$("#co_id").focus();
			return false;
		}
		$.ajax({
		  method: 'POST',
		  url: './ajax.content_info.php',
		  data: {
			co_id: id
		  },
		}).done(function (data) {
		  // return false;
		  if (data.msg != "") {
			alert(data.msg);
		  }
		  if (data.result === 'success') {
			if(data.co_subject != ""){
				$("#co_subject").text(data.co_subject);
				$("#ctt_con2").html(data.co_content);
			}
			
		  }
		});			
		$('body').addClass('modal-open');
		$('#popup_box2').show();
	}
	$('.btn_close').click(function() {			
		$('body').removeClass('modal-open');
		$('#popup_box2').hide();
		$("#co_subject").text("");
		$("#ctt_con2").html("");
	});
</script>
<?php }?>
