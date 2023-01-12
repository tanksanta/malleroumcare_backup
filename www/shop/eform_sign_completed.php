<?php
include_once('./_common.php');
//while(true){
//	$sql = "SELECT dc_status FROM `eform_document` WHERE dc_id=UNHEX('".$_REQUESt["dc_id"]."')";
//	$row=sql_fetch($sql);
//	if($row["dc_status"] == "3" || $row["dc_status"] == "5"){
//		break;
//	}
//}
?>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<body style="background:#111111;margin-top:0px;">
	<div style="width:100%;text-align:center;vertical-align:middle;margin-top:10%;">
		<img src="../adm/shop_admin/img/ajax-loading.gif" style="width:30%" class="img-responsive"><br>
    <b><font color="#ffffff" style="font-size:1.5rem;">처리중입니다.<bR>잠시만 기다려주세요...</font></b>
	</div>
</body>
</html>

<script>
	setTimeout(completed, 11000);	
	function completed(){
		opener.location.reload();
		window.close();
	}
</script>
