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
    var canvasHegiht = canvas.offsetHeight;
    var dpiRatio = origWidth / canvasWidth;
    var newHeight = origHeight / dpiRatio;

    signBack.style.top = ((canvasHegiht / 2) - (newHeight / 2)) + 'px';
    signBack.style.width = canvasWidth + 'px';
    signBack.style.height = newHeight + 'px';

    signaturePad.minWidth = 1.25 / dpiRatio;
    signaturePad.maxWidth = 1.25 / dpiRatio;
  }

  window.onresize =resizeCanvas;
  resizeCanvas();

  // //////////////////////////////////////
  // TEST PURPOSE
  // //////////////////////////////////////
  function toResizedDataURL(canvas, dpiRatio) {
    var resizedCanvas = document.createElement('canvas');
    var resizedContext = resizedCanvas.getContext('2d');

    resizedCanvas.width = canvas.width * dpiRatio;
    resizedCanvas.height = canvas.height * dpiRatio;

    resizedContext.drawImage(canvas, 0, 0, resizedCanvas.width, resizedCanvas.height);
    return resizedCanvas.toDataURL();
  }

  function download(dataURL, filename) {
    if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
      window.open(dataURL);
    } else {
      var blob = dataURLToBlob(dataURL);
      var url = window.URL.createObjectURL(blob);

      var a = document.createElement("a");
      a.style = "display: none";
      a.href = url;
      a.download = filename;

      document.body.appendChild(a);
      a.click();

      window.URL.revokeObjectURL(url);
    }
  }

  // One could simply use Canvas#toBlob method instead, but it's just to show
  // that it can be done using result of SignaturePad#toDataURL.
  function dataURLToBlob(dataURL) {
    // Code taken from https://github.com/ebidel/filer.js
    var parts = dataURL.split(';base64,');
    var contentType = parts[0].split(":")[1];
    var raw = window.atob(parts[1]);
    var rawLength = raw.length;
    var uInt8Array = new Uint8Array(rawLength);

    for (var i = 0; i < rawLength; ++i) {
      uInt8Array[i] = raw.charCodeAt(i);
    }

    return new Blob([uInt8Array], { type: contentType });
  }

  var btnSignSubmit = document.getElementById('btn-sign-submit');
  btnSignSubmit.addEventListener("click", function (event) {
    event.preventDefault();
    if (signaturePad.isEmpty()) {
      alert("Please provide a signature first.");
    } else {
      var origWidth = 200;
      var canvasWidth = canvas.offsetWidth;
      var dpiRatio = origWidth / canvasWidth;
      var dataURL = toResizedDataURL(canvas, dpiRatio);
      download(dataURL, "signature.png");
    }
  });
  // //////////////////////////////////////
  // TEST PURPOSE 끝
  // //////////////////////////////////////
  </script>
</body>
</html>
