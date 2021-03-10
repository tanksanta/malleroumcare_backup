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
</script>
    <button onclick="window.webkit.messageHandlers.openBarcode.postMessage('3');">확인</button>
    <input type="text" id="barcodeNum">
</body>
</html>
