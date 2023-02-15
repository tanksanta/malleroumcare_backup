<?php
include_once('./_common.php');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Resources/Css/certNumber.css">
	<script src="/Resources/Scripts/jquery-3.5.1.min.js"></script>
    <title>사업소별 공인인증서 비밀번호</title>
    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="visual">
        <div class="visualWrapLogin">
            <!-- headerWrap -->
            <div class="headerWrap">
                <div class="headerTitle">                   
                    장기요양기관번호
                </div>
                <div class="headerCancel">
                    <a href="javascript:parent.$('#cert_ent_num_popup_box').trigger('click');"><img src="/Resources/Images/btn_cencel.png" alt="취소"></a>
                </div>             
            </div>
            <hr class="hrType">
            <!-- ContertsWrap -->
            <div class="contentsWrap">               
                <!-- pwTitle -->
                <div class="pwTitle">장기요양기관번호</div>
                <div class="pwDesc">사업소의 장기요양기관번호를<br>입력해주십시오.</div>
                <!-- password -->
                <div class="pw">
                    <input type="text" class="inType" id="ent_num" placeholder="장기요양기관번호(11자리) 입력" maxlength="11" numberOnly> 
                    <button class="okType" onClick="ent_num_chk()">확인</button>
                </div>
                   
            </div>
        </div>
    </div>
    
</body>
<script>
	function ent_num_chk(){
		if($('#ent_num').val() == ""){
			alert("장기요양기관번호를 입력해주세요.");
			$('#ent_num').focus();
			return;
		}
		if($('#ent_num').val().length != 11){
			alert("장기요양기관번호는 숫자만 11자리를 입력해 주세요.");
			$('#ent_num').focus();
			return;
		}
		var params = {
				  mode      : 'ent_num'
				, ent_num       : $('#ent_num').val()
			}
			$.ajax({
				type : "POST",            // HTTP method type(GET, POST) 형식이다.
				url : "/ajax.tilko.php",      // 컨트롤러에서 대기중인 URL 주소이다.
				data : params, 
				dataType: 'json',// Json 형식의 데이터이다.
				success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
					parent.pwd_insert();
					parent.$('#cert_ent_num_popup_box').trigger('click');
				  },
				error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
					alert(XMLHttpRequest['responseJSON']['message']);
					$('#ent_num').focus();
				}
			});
	}

	function checkNumber(event) {
	  $(this).val( $(this).val().replace(/[^0-9]/gi,"") );
	}
	$( document ).ready(function() {
		$('#ent_num').focus();
		$(document).on("keyup", "input[numberOnly]", function() {$(this).val( $(this).val().replace(/[^0-9]/gi,"") );})
	});
</script>
</html>