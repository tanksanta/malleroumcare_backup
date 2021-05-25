<?php
include_once("./_common.php");

if(!$is_member) {
  alert('먼저 로그인하세요.');
}

$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '$od_id'";
if($is_member && !$is_admin)
    $sql .= " AND mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['mb_id']) {
  alert('계약서를 작성할 권한이 없습니다.');
}

$eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");
$items = sql_query("SELECT * FROM `eform_document_item` WHERE dc_id = UNHEX('{$eform["uuid"]}')");

$buy = [];
$rent = [];
while($item = sql_fetch_array($items)) {
  if($item['gubun'] == '00') array_push($buy, $item); // 판매 재고
  else array_push($rent, $item); // 대여 재고
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $eform['dc_subject']; ?></title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/signeform.css">
  <link rel="stylesheet" href="css/thk001.css">
  <link rel="stylesheet" href="css/thk002.css">
  <link rel="stylesheet" href="css/thk003.css">
  <script src="<?=G5_JS_URL?>/signature_pad.umd.js"></script>
  <script src="<?=G5_JS_URL?>/jquery-1.11.3.min.js"></script>
</head>
<body>
  <div class="sign-eform-head flexbox justify">
    <h1 id="eformTitle" class="flex"><?=$eform['dc_subject']?></h1>
    <button id="btnCloseSign">나가기</button>
  </div>
  <div class="sign-eform-body">
    <?php
    include_once('./document/thk001_1.php');
    include_once('./document/thk001_2.php');
    include_once('./document/thk002.php');
    include_once('./document/thk003.php');
    ?>
  </div>
  <div class="sign-eform-foot">
    <div class="desc">3건 중 0건 완료되었습니다.</div>
    <div class="menu-wrap">
      <button id="btnPrev">이전단계</button>
      <button id="btnNext">다음단계</button>
    </div>
  </div>
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
  $(function() {
    $('#btnCloseSign').click(function(e) {
      e.preventDefault();
      window.close();
    });

    function closeSignPopup() {
      $('body').removeClass('modal-open');
      var $popUp = $('#popup-sign');
      resizeHandler();
      $popUp.data('id', '');
      $popUp.css({display: 'none'});
    }

    $('#btn-sign-cancel').click(function(e) {
      e.preventDefault();

      closeSignPopup();
    });

    $('#btn-sign-submit').click(function(e) {
      e.preventDefault();
      var id = $('#popup-sign').data('id');

      if(id) {
        if (signaturePad.isEmpty()) {
          return alert("서명을 입력해주세요.");
        } else {
          var dataURL = toResizedDataURL(canvas, origWidth, origHeight);
          state[id] = dataURL;
          repaint();
        }
      }

      closeSignPopup();
    });

    $(document).on('click', '.btn-sign', function(e) {
      e.preventDefault();
      var id = $(this).data('id');

      $('body').addClass('modal-open');
      var $popUp = $('#popup-sign');
      $popUp.data('id', id);
      $popUp.css({display: 'table'});
      origWidth = pos[id].width;
      origHeight = pos[id].height;
      resizeHandler();
    });

    var origWidth = 120;
    var origHeight = 40;

    var wrapper = document.getElementById('sign-pad');
    var canvas = wrapper.querySelector('canvas');

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

    function resizeHandler() {
      resizeModal();
      resizeSignBack(origWidth, origHeight);
      resizeCanvas();
    }

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

    var pos = {
      sign_001_1: {
        top: -5,
        left: 15,
        width: 120,
        height: 40
      },
      seal_001_1: {
        top: -5,
        left: 15,
        width: 120,
        height: 40
      },
      seal_002_1: {
        top: 5,
        left: 60,
        width: 120,
        height: 40
      },
      sign_002_1: {
        top: -3,
        left: 60,
        width: 120,
        height: 40
      },
      sign_003_1: {
        top: -4,
        left: 50,
        width: 120,
        height: 40
      }
    };

    var state = {
      chk_001_1: false,
      sign_001_1: '',
      seal_001_1: '<?=htmlspecialchars($eform['dc_signUrl'])?>',
      seal_002_1: '<?=htmlspecialchars($eform['dc_signUrl'])?>',
      sign_002_1: '',
      chk_003_1: false,
      chk_003_2: false,
      chk_003_3: false,
      sign_003_1: '',
    };

    function repaint() {
      // 직인
      $('.seal-form').each(function() {
        var id = $(this).data('id');
        if($('#'+id).length === 0) $(this).append('<div id="'+id+'" class="seal-wrap"></div>')
        
        var $wrap = $('#'+id);
        $wrap.css({
          top: pos[id].top,
          left: pos[id].left,
          width: pos[id].width,
          height: pos[id].height
        });

        var imageURL = state[id];
        $wrap.html('<img src="'+imageURL+'" height="'+pos[id].height+'" alt="사업소 직인">');
      });

      // 서명
      $('.sign-form').each(function() {
        var id = $(this).data('id');
        if($('#'+id).length === 0) $(this).append('<div id="'+id+'" class="sign-wrap"></div>');

        var $wrap = $('#'+id);
        $wrap.css({
          top: pos[id].top,
          left: pos[id].left,
          width: pos[id].width,
          height: pos[id].height
        });

        // 서명이 비어있다면
        if(!state[id]) {
          $wrap.html('<button class="btn-sign" data-id="'+id+'">서명하기</button>');
        } else {
          var imageURL = state[id];
          $wrap.html('<img src="'+imageURL+'" height="'+pos[id].height+'" alt="수급자 서명">');
        }
      });
    }

    repaint();

    window.onresize = resizeHandler;
  });
  </script>
</body>
</html>
