<?php
include_once('./_common.php');
/*
$mobile_agent = "/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/";

//if(preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])){
$mobile_send = "";
if($mobile_send == "ok"){
	$mobile_yn = "Mobile";
	$getCert = "";
}else{
	$mobile_yn = "Pc";
	$getCert = ", getCert";

}*/
if($_REQUEST["option"] == "1"){//공인인증서 등록
	$b_img_url = "https://".$_SERVER['HTTP_HOST']."/Resources/Images/cert_img_title01.png";
}elseif($_REQUEST["option"] == "2"){//공인인증서로 로그인
	$b_img_url = "https://".$_SERVER['HTTP_HOST']."/Resources/Images/cert_img_title02.png";
}

?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>
	공인인증서 복사
</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
<link href="/Resources/Css/style.css" rel="stylesheet" />
	
	<!-- script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script -->
	<script src="/Resources/Scripts/jquery-3.5.1.min.js"></script>
	<script src="/Resources/Scripts/TilkoSignWebSocket.min.js"></script>
	<script type="text/javascript">
		const apiKey = "a55aaf2f84a0477da82bb4572f97babf";
		const hubUrl = "https://cert.tilko.net/";
		const apiHost = "https://api.tilko.net/";

		let _tksReq	= new TilkoSignRequest("general", "ping", getCert);


		function callScheme(api_key, hubServerUrl, bannerUrl, clientType) {
			api_key = "82543e5b2d0c4fccb565437933519ba3";
			try {
				var siteUrl = window.location.host;

				_tksReq.Add("apiKey=" + api_key);
				_tksReq.Add("hubServerUrl=" + hubServerUrl);
				_tksReq.Add("bannerUrl=" + bannerUrl);
                _tksReq.Add("siteUrl=" + siteUrl);
                _tksReq.Add("clientType=" + clientType);
				
				// TilkoSign 설치여부 확인
				_tksReq.Send();

				var _scheme		= api_key + "|" + hubServerUrl + "|" + bannerUrl + "|" + siteUrl + "|" + clientType;
				_scheme			= _scheme.hexEncode();
				location.href	= "tilkosign://" + _scheme;
			}
			catch (e) {
				alert(e);
			}
		}


        function getCert(obj) {
			if(obj.PriKey != "" && obj.PubKey != "" && obj.Pwd != ""){
				//alert(atob(obj.PubKey));
				var params = {
						  PriKey      : obj.PriKey
						, PubKey       : obj.PubKey
						, Pwd       : obj.Pwd
						, Expire       : obj.Expire
						, Name       : obj.Name
						, Issuer       : obj.Issuer
						, Purpose       : obj.Purpose
				}
				$.ajax({
					type : "POST",            // HTTP method type(GET, POST) 형식이다.
					url : "/ajax.tilko.php",      // 컨트롤러에서 대기중인 URL 주소이다.
					data : params, 
					dataType: 'json',// Json 형식의 데이터이다.
					success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
					<?php if($_REQUEST["option"] == "2"){//로그인 후 부모창 조회 실행?>
						parent.$("#btn_submit").trigger("click");
					<?php }else{?>// 응답코드 > 0000
						parent.$('#cert_guide_popup_box').trigger('click')
					<?php }?>

					<?php 
						// 23.11.07 : 서원 - 사업소 '회원정보수정' 페이지에서 인증서 등록을 진행하고, 정상 처리되었을 경우 상위 부모창을 새로고침 함.
						if( strpos($_SERVER['HTTP_REFERER'], "/bbs/member_info_newform.php") !== false ) { 
							echo("parent.location.reload();");
						}					
					?>

					  },
					error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
						alert("통신 실패.")
					}
				});
			}else{
				alert("인증서 등록에 실패 하였습니다. 다시 한번 인증서 등록을 진행해 주세요.\n계속해서 인증서 등록에 실패 할 경우 시스템 관리자에게 문의 해 주시기 바랍니다.");
			}
			console.log(obj);
            //callApi(obj);
		}


        // RSA 공개키(Public Key) 조회 함수
        function getPublicKey(callback) {
            const uri = apiHost + "/api/Auth/GetPublicKey?APIkey=" + apiKey;
            const params = {};

            return $.ajax({
                type: "GET",
                url: uri,
                data: params,
                contentType: "application/json",
                success: function (data) {
                    console.log(data);
                    return data.PublicKey;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error(textStatus);
                    console.error(jqXHR);
                }
            });
        }


        async function callApi(cert) {
            //alert(encodeURI(cert.PubKey));
			// AES Key를 RSA Public Key로 암호화
            const rsaPublicKey = await getPublicKey().then(function (data) {
                // AES Secret Key 및 IV 생성
                const aesKey = CryptoJS.enc.Utf8.parse('1234567890123456');                  // 16 바이트 랜덤값 사용 가능
                const aesIv = CryptoJS.enc.Hex.parse("00000000000000000000000000000000");   // 고정값

                const rsaPublicKey = data.PublicKey;

                const aesCipherKey = rsaEncrypt(rsaPublicKey, aesKey, "pkcs1");

                const uri = apiHost + "/api/v1.0/Gov/AA090UserJuminCheckResApp";

                const headers = {
                    "Content-Type": "application/json",
                    "API-KEY": apiKey,
                    "ENC-KEY": aesCipherKey
                };
				
                const params = {
                    "CertFile": aesEncryptCert(aesKey, aesIv, cert.PubKey),
                    "KeyFile": aesEncryptCert(aesKey, aesIv, cert.PriKey),
                    "CertPassword": aesEncrypt(aesKey, aesIv, CryptoJS.enc.Base64.parse(cert.Pwd).toString(CryptoJS.enc.Utf8)),
                    "PersonName": "홍길동",
                    "IdentityNumber": aesEncrypt(aesKey, aesIv, "8801011234567"),
                    "PublishDate": "20200101",
                };

                $.ajax({
                    type: "POST",
                    url: uri,
                    headers: headers,
                    data: JSON.stringify(params),
                    contentType: "application/json",
                    success: function (data) {
                        console.log(data);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error(textStatus);
                        console.error(jqXHR);
                    }
                });
            });
        }
	$(document).ready(function () {
		callScheme('', 'https://cert.tilko.net/', '<?=$b_img_url?>', 'Pc');
	});
    </script>
</head>
<body>
	<form method="post" action="./" id="form1">
<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="6Jq3CuqmxBFNcUNDXLt6evIKDAc7vJEf7bUy8l92gqNuFnvwVtcJtq6/JIiRjt2eO7Z74HBA+Sesd4nsaEYIPRbuzNN5QiTyArcecOKaKbw=" />
<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="CA0B0334" />
	<iframe id="_ifdownload" style="display:none;"></iframe>
	</form>
</body>
</html>

</html>
