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
    body {
      width: 100%;
      height: 100vh;
      background-color: #bbb;
    }
    #signature-pad {
      background-color: #fff;
      width: 100%;
      height: 100%;
      max-width: 700px;
      max-height: 460px;
    }
    canvas {
      width:100%;
      height: 100%;
    }
  </style>
  <div id="signature-pad">
    <canvas></canvas>
  </div>
  <script>
    var wrapper = document.getElementById('signature-pad');
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