<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- <script type="text/javascript" src="./barcode_test.js"></script>
    <script>
        YOUR_HANDLER_NAME = 'openBarcode';
    </script> -->
</head>
<body>
<script  src="//code.jquery.com/jquery-latest.min.js"></script>
<script type="text/javascript">
    function sendBarcode(text) {
//        alert('(Native)returnMessage() :\n' + text);
		$("#barcodeNum").val(text);
    }
	
	/* 기종체크 */
	var deviceUserAgent = navigator.userAgent.toLowerCase();
	var device;
	
	if(deviceUserAgent.indexOf("android") > -1){
		/* android */
		device = "android";
	}

	if(deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1){
		/* ios */
		device = "ios";
	}
	
	$(function(){
		
		/* 열기 */
		$("#btn210316open").click(function(){
			switch(device){
				case "android" :
					/* android */
					alert("android");
					window.EroummallApp.openBarcode("3");
					break;
				case "ios" :
					/* ios */
					alert("ios");
					window.webkit.messageHandlers.openBarcode.postMessage("3");
					break;
			}
		});
		
		/* 닫기 */
		$("#btn210316close").click(function(){
			switch(device){
				case "android" :
					/* android */
					alert("android");
					window.EroummallApp.closeBarcode("");
					break;
				case "ios" :
					/* ios */
					alert("ios");
					window.webkit.messageHandlers.closeBarcode.postMessage("");
					break;
			}
		});
		
	})
</script>
    <button type="button" id="btn210316open">210316 OPEN</button>
    <button type="button" id="btn210316close">210316 CLOSE</button>
    <input type="text" id="barcodeNum">
</body>
</html>
