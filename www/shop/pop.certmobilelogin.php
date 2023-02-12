<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Resources/Css/certLogin_m.css">
	<script src="/Resources/Scripts/jquery-3.5.1.min.js"></script>
    <title>사업소별 공인인증서 비밀번호</title>
    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
<script>
	function pwd_ch(){
		if($('#cert_pwd').val() == ""){
			alert("사업소 공인인증서 비밀번호를 입력해 주세요.");
			$('#cert_pwd').focus();
		}else{
			parent.cert_pwd($('#cert_pwd').val());
			parent.$('#cert_popup_box').trigger('click');
		}
	}
	$( document ).ready(function() {
		$('#cert_pwd').focus();
	});
</script>

</head>
<body>
    <div class="visual">
        <div class="visualWrapLogin">
            <!-- headerWrap -->
            <div class="headerCancel">
                    <a href="javascript:parent.$('#cert_popup_box').trigger('click');"><img src="/Resources/Images/btn_cencel.png" alt="취소"></a>
             </div>
            <!-- ContertsWrap -->
            <div class="contentsWrap">               
                <!-- pwTitle -->
                <div class="pwTitle">사업소 공인인증서 비밀번호</div>
                <div class="pwDesc">요양정보조회를 이용하시려면<br>
                    사업소 공인인증서 비밀번호를 입력하셔야합니다.</div>
                <!-- password -->
                <div class="pw">
                    <input type="password" class="inType" id="cert_pwd" placeholder="사업소 공인인증서 비밀번호 입력">                                       
                </div>               
            </div>
             <!-- button -->
             <div class="btnWrap">
                <div class="btn celType" onClick="parent.$('#cert_popup_box').trigger('click');">취소</div><div class="btn okType" onClick="pwd_ch();">확인</div>
            </div>
        </div>
    </div>
    
</body>
</html>