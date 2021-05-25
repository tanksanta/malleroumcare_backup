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
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/signeform.css">
  <script src="<?=G5_JS_URL?>/jquery-1.11.3.min.js"></script>
</head>
<body>
  <div id="popup-sign">
    <div class="popup-modal-wrap">
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
  </div>
  <script>
  // 테스트 서명 비율 120x40
  var origWidth = 120;
  var origHeight = 40;

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

  function calcSize(size, a, b) {
    var ratio = a / b;
    return size / ratio;
  }

  function resizeModal() {
    var margin = 20;

    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var modalWidth = 700 + margin;
    var modalHeight = 600 + margin;

    if(windowWidth > modalWidth) {
      if(windowHeight > modalHeight) {
        modalWidth = modalWidth;
        modalHeight = modalHeight;
      } else {
        modalWidth = calcSize(modalWidth, modalHeight, windowHeight);
        modalHeight = windowHeight;
      }
    } else {
      if(windowHeight > modalHeight) {
        modalHeight = calcSize(modalHeight, modalWidth, windowWidth);
        modalWidth = windowWidth;
      } else {
        if(windowWidth < windowHeight) {
          modalHeight = calcSize(modalHeight, modalWidth, windowWidth);
          modalWidth = windowWidth;
        } else {
          modalWidth = calcSize(modalWidth, modalHeight, windowHeight);
          modalHeight = modalHeight;
        }
      }
    }

    modalWidth -= margin;
    modalHeight -= margin;

    $('.popup-modal').css({
      width: modalWidth,
      height: modalHeight
    });

    $('#sign-pad canvas').css({
      top: 70,
      left: 0,
      width: modalWidth,
      height: modalHeight - 70 - 55
    });
  }

  function resizeHandler() {
    resizeModal();
    resizeSignBack(origWidth, origHeight);
    resizeCanvas();
  }

  function resizeCanvas() {
    var ratio = Math.max(window.devicePixelRatio || 1, 1);

    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);

    signaturePad.clear();
  }

  function resizeSignBack(origWidth, origHeight) {
    var margin = 20;
    var canvasWidth = $('#sign-pad canvas').width() - margin;
    var canvasHegiht = $('#sign-pad canvas').height();
    var dpiRatio = origWidth / canvasWidth;
    var newHeight = origHeight / dpiRatio;

    $('#sign-back').css({
      top: (canvasHegiht / 2) - (newHeight / 2) + 70,
      left: margin / 2,
      width: canvasWidth,
      height: newHeight
    });

    /*$('#sign-pad canvas').css({
      top: (canvasHegiht / 2) - (newHeight / 2) + 70,
      left: margin / 2,
      width: canvasWidth,
      height: newHeight
    });*/

    signaturePad.minWidth = 0.75 / dpiRatio;
    signaturePad.maxWidth = 0.75 / dpiRatio;
  }

  window.onresize =resizeHandler;
  resizeHandler();

  function toResizedDataURL(canvas, origWidth, origHeight) {
    var canvasWidth = canvas.width;
    var canvasHeight = canvas.height;

    var dpiRatio = origWidth / canvasWidth;

    var resizedCanvas = document.createElement('canvas');
    var resizedContext = resizedCanvas.getContext('2d');

    resizedCanvas.width = origWidth * 5;
    resizedCanvas.height = origHeight * 5;

    var $signBack = $('#sign-back');

    resizedContext.drawImage(canvas,
      $signBack.css('left').replace(/[^-\d\.]/g, ''), $signBack.css('top').replace(/[^-\d\.]/g, '') - 70,
      $signBack.width(), $signBack.height(),
      0, 0,
      origWidth * 5, origHeight * 5
    );
    return resizedCanvas.toDataURL();
  }


  var btnSignSubmit = document.getElementById('btn-sign-submit');
  btnSignSubmit.addEventListener("click", function (event) {
    event.preventDefault();
    if (signaturePad.isEmpty()) {
      alert("Please provide a signature first.");
    } else {
      var dataURL = toResizedDataURL(canvas, origWidth, origHeight);
      download(dataURL, "signature.png");
    }
  });

  </script>
</body>
</html>
