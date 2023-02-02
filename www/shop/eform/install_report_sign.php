<?php
include_once('./_common.php');

if($member['mb_type'] !== 'partner')
    alert('먼저 로그인하세요.');

$od_id = get_search_string($_GET['od_id']);

$sql = "
    SELECT * FROM
        partner_install_report
    WHERE
        od_id = '$od_id' and
        mb_id = '{$member['mb_id']}'
";
$report = sql_fetch($sql);

if(!$report)
    alert('유효하지 않은 요청입니다.');

if($report['ir_file_url'])
    alert('이미 작성된 결과보고서입니다.');

$sql = "
    SELECT
        o.*,
        m.mb_name,
        mb_giup_btel
    FROM
        g5_shop_order o
    LEFT JOIN
        g5_member m ON o.mb_id = m.mb_id
    WHERE
        od_id = '$od_id'
";
$od = sql_fetch($sql);

if(!$od)
    alert('주문이 존재하지 않습니다.');

$sql = "
    SELECT * FROM
        g5_shop_cart
    WHERE
        od_id = '$od_id' and
        ct_direct_delivery_partner = '{$member['mb_id']}' and
        ct_status IN('준비', '출고준비', '배송', '완료')
    ORDER BY
        ct_id ASC
";
$result = sql_query($sql);

$total_qty = 0;
$carts = [];
while($ct = sql_fetch_array($result)) {
    $ct['it_name'] .= $ct['ct_option'] && $ct['ct_option'] != $ct['it_name'] ? " ({$ct['ct_option']})" : '';

    // 바코드 정보 가져오기
    $sto_id = [];

    foreach(array_filter(explode('|', $ct['stoId'])) as $id) {
        $sto_id[] = $id;
    }

    $stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
        'stoId' => implode('|', $sto_id)
    ));

    $barcodes = [];
    if($stock_result['data']) {
      foreach($stock_result['data'] as $data) {
        $barcodes[] = $data['prodBarNum'];
      }
    }

    $ct['barcode'] = $barcodes;

    $total_qty += $ct['ct_qty'];

    $carts[] = $ct;
}

// 문서명 (사업소명_주문번호_설치결과보고서)
$title = "{$od['mb_name']}_{$od_id}_설치결과보고서";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1.0,user-scalable=1,width=device-width">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="css/default.css">
    <link rel="stylesheet" href="css/signeform.css?v=06042032">
    <link rel="stylesheet" href="css/install_report.css">
    <script src="<?=G5_JS_URL?>/signature_pad.umd.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body>
<div class="sign-eform-head flexbox justify">
    <h1 id="eformTitle" class="flex"><?=$title?></h1>
    <button id="btnCloseSign">나가기</button>
</div>
<div class="sign-eform-body">
    <?php
    include_once('./document/install_report.php');
    ?>
</div>
<div class="sign-eform-foot" style="height: 70px;">
    <div class="menu-wrap">
        <button id="btnNext" class="primary">완료</button>
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
    var pos = {
        sign_ir_1: {
            top: 6,
            left: -35,
            width: 120,
            height: 50
        },
    };

    var state = {
        sign_ir_1: '',
    };

    $('#btnNext').click(function(e) {
        e.preventDefault();
        if(!state.sign_ir_1) {
            return alert('서명을 완료해주세요.');
        }

        $(this).text('진행 중...');
        $(this).prop('disabled', true);
        $.post('./ajax.install_report.sign.php', {
            state: JSON.stringify(state),
            od_id: '<?=$od_id?>'
        }, 'json')
        .done(function() {
            $.post('/shop/schedule/ajax.update_schedule_status.php', {
              od_id: '<?=$od_id?>',
              status: '완료'
            }, 'json')
            .done(function() {
              alert('설치확인서 작성이 완료되었습니다.');
              history.back();
            })
            .fail(function($xhr) {
              $(this).text('완료');
              $(this).prop('disabled', false);
              var data = $xhr.responseJSON;
              alert(data && data.message);
            });
        })
        .fail(function($xhr) {
            $(this).text('완료');
            $(this).prop('disabled', false);
            var data = $xhr.responseJSON;
            alert(data && data.message);
        });
    });

    $('#btnCloseSign').click(function(e) {
        e.preventDefault();
        history.back();
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

    $(document).on('click', '.btn-sign, .img-sign', function(e) {
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

      signaturePad.minWidth = 0.75 / dpiRatio;
      signaturePad.maxWidth = 0.75 / dpiRatio;
    }

    function resizeHandler() {
      resizeModal();
      resizeSignBack(origWidth, origHeight);
      resizeCanvas();
    }

    function toResizedDataURL(canvas, origWidth, origHeight) {
      var resizedCanvas = document.createElement('canvas');
      var resizedContext = resizedCanvas.getContext('2d');

      resizedCanvas.width = origWidth * 5;
      resizedCanvas.height = origHeight * 5;

      var $signBack = $('#sign-back');
      var ratio = Math.max(window.devicePixelRatio || 1, 1);

      resizedContext.drawImage(canvas,
        $signBack.css('left').replace(/[^-\d\.]/g, '') * ratio, ($signBack.css('top').replace(/[^-\d\.]/g, '') - 70) * ratio,
        $signBack.width() * ratio, $signBack.height() * ratio,
        0, 0,
        origWidth * 5, origHeight * 5
      );

      return resizedCanvas.toDataURL();
    }

    function repaint() {
      // 서명
      $('.td_sign').each(function() {
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
          $wrap.html('<img class="img-sign" data-id="'+id+'" src="'+imageURL+'" height="'+pos[id].height+'" alt="수급자 서명">');
        }
      });
    }

    repaint();
});
</script>
</body>
</html>