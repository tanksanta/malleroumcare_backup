<?php
include_once('./_common.php');
?>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<body style="background:#111111;margin-top:0px;">
	<div style="width:100%;text-align:center;vertical-align:middle;margin-top:10%;">
		<img src="../adm/shop_admin/img/ajax-loading.gif" style="width:30%" class="img-responsive"><br>
    <b><font color="#ffffff" style="font-size:1.5rem;">처리중입니다.<bR>잠시만 기다려주세요...</font></b>
	</div>
</body>
</html>

<script>
	$(function(){
		$.ajaxSetup({
			async:true
			});
		$.post('ajax.eform_mds_api.php', {
			div : "completed_doc"
			,dc_id : '<?=$_REQUEST['documentId']?>'
		}, 'json')
		.done(function(data) {
			//alert(JSON.stringify(data));
			if(data.api_stat != "1"){
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				return false;				
			}			
			if(data.url != "url생성실패"){
				completed(data.rent);
			}else{
				alert(res.url);//url 생성실패 알림
			}
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		});	
	});
	
		
	function completed(rent){
		if(rent == 1){//대여계약 급여제공기록 계약 시
			$('body',opener.document).removeClass('modal-open');
			$('#popup_box8',opener.document).hide();
			opener.rent_efrom_hist_open()
		}else{
			opener.location.reload();
		}
		window.close();
	}
</script>
