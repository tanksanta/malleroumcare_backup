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
  <script src="./js/signEform.js"></script>
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
      position: relative;
      background-color: #fff;
      width: 100%;
      height: 100%;
    }
    #sign-pad canvas {
      position: absolute;
      z-index: 9999;
      width:100%;
      height: 100%;
    }
    #sign-back {
      position: absolute;
      display: -ms-flexbox;
      display: flex;
      -ms-flex-align: center;
      align-items: center;
      -ms-flex-pack: center;
      justify-content: center;
      text-align: center;
      top: 110px; 
      left: 16px;
      right: 16px;
      height: 240px;
      color: #aaa;
      background-color: #f2f2f2;
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
      padding: 24px 16px 16px 16px;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      color: #000;
      background-color: #fff;
    }
    .popup-modal .bottom-wrap {

    }
    .popup-modal .bottom-wrap button {
      display: inline-block;
      margin: 0;
      padding: 18px 16px;
      border: none;
      text-align: center;
      vertical-align: middle;
      font-size: 16px;
      color: #fff;
      cursor: pointer;
    }
    #btn-sign-submit {
      width: 80%;
      background-color: #f28b08;
    }
    #btn-sign-cancel {
      width: 20%;
      background-color: #7d7d7d;
    }
  </style>
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
    var signaturePad = new SignaturePad(canvas, {
      backgroundColor: 'transparent',
      minDistance: 5,
      throttle: 3,
      minWidth: 4,
      maxWidth: 4
    });

    window.onresize = function() { resizeCanvas(canvas, signaturePad) };
    resizeCanvas();
  </script>
</body>
</html>
