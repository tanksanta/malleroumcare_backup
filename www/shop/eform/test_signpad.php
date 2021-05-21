<?php
include_once('./_common.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="<?php echo G5_JS_URL ?>/signature_pad.umd.js"></script>
  <link rel="stylesheet" href="css/signeform.css">
</head>
<body>
  <div id="popup-sign">
    <div class="popup-modal">
      <div class="head-wrap">서명하기</div>
      <div id="sign-pad">
        <canvas></canvas>
        <div id="sign-back">이곳에 사인해주세요.</div>
      </div>
      <div class="bottom-wrap">
        <button id="btn-sign-submit">확인</button><button id="btn-sign-cancel">취소</button>
      </div>
    </div>
  </div>
  <script>
    var wrapper = document.getElementById('sign-pad');
    var canvas = wrapper.querySelector('canvas');
    var signBack = document.getElementById('sign-back');

    var signaturePad = new SignaturePad(canvas, {
      backgroundColor: 'transparent',
      minDistance: 5,
      throttle: 3,
      minWidth: 4,
      maxWidth: 4
    });

    function resizeCanvas() {
      var ratio = Math.max(window.devicePixelRatio || 1, 1);

      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext('2d').scale(ratio, ratio);

      signaturePad.clear();
      // 테스트 서명 비율 200x75
      resizeSignBack(200, 75);
    }

    function resizeSignBack(origWidth, origHeight) {
      var canvasWidth = canvas.offsetWidth;
      var dpiRatio = canvasWidth / origWidth;
      var newHeight = origHeight * dpiRatio;

      signBack.style.width = canvasWidth + 'px';
      signBack.style.height = newHeight + 'px';
    }

    window.onresize =resizeCanvas;
    resizeCanvas();
  </script>
</body>
</html>
