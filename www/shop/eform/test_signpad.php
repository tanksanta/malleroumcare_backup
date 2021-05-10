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
</head>
<body>
  <style>
    #popup-sign {
      /* display: none; Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 999; /* Sit on top */
      padding-top: 100px; /* Location of the box */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: rgb(0,0,0); /* Fallback color */
      background-color: rgba(0,0,0,0.6); /* Black w/ opacity */
    }
    #sign-pad {
      background-color: #fff;
      width: 100%;
      height: 100%;
    }
    #sign-pad canvas {
      width:100%;
      height: 100%;
    }
    .popup-modal {
      background-color: #fff;
      margin: 0 auto;
      width: 100%;
      height: 100%;
      max-width: 700px;
      max-height: 460px;
    }
    .popup-modal .head-wrap {
      padding: 16px;
      text-align: center;
      font-size: 18px;
      color: #fff;
      background-color: #5884cc;
    }
    .popup-modal .bottom-wrap {

    }
    .popup-modal .bottom-wrap button {
      display: inline-block;
      margin: 0;
      padding: 16px;
      border:none;
      text-align: center;
      vertical-align: middle;
      font-size: 16px;
      color: #fff;
      cursor: pointer;
    }
    #btn-sign-cancel {
      width: 20%;
      background-color: #9c9c9c;
    }
    #btn-sign-submit {
      width: 80%;
      background-color: #5681ca;
    }
  </style>
  <div id="popup-sign">
    <div class="popup-modal">
      <div class="head-wrap">서명하기</div>
      <div id="sign-pad">
        <canvas></canvas>
        <div class="sign-back"></div>
      </div>
      <div class="bottom-wrap">
        <button id="btn-sign-cancel">취소</button><button id="btn-sign-submit">확인</button>
      </div>
    </div>
  </div>
  <script>
    var wrapper = document.getElementById('sign-pad');
    var canvas = wrapper.querySelector('canvas');
    var signaturePad = new SignaturePad(canvas, {
      minWidth: 4,
      maxWidth: 6
    });

    function resizeCanvas() {
      var ratio =  Math.max(window.devicePixelRatio || 1, 1);

      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);

      signaturePad.clear();
    }

    window.onresize = resizeCanvas;
    resizeCanvas();
  </script>
</body>
</html>