<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once('./_common.php');
?>

<link rel="stylesheet" type="text/css" href="<?php echo G5_URL; ?>/css/jquery.toast.min.css" />
<script type="text/javascript" src="<?php echo G5_URL; ?>/js/jquery.toast.min.js"></script>

<div id="web-barcode">
	<i id="web-barcode-close" class="fa fa-times"></i>
  <div class="web-barcode-content">
    <div id="web-barcode-loading"></div>
    <div>
      바코드 스캔 대기중..<br />
    </div>
    <input type="text" id="web-barcode-input" />
  </div>
</div>

<style>
.toast {
  width: 98% !important;
  left: 0 !important;
  margin: 5px auto 0 auto !important;
  right: 0;
  text-align:center;
  font-weight:bold;
}

.barcode_add {
  display:none !important;
}

#web-barcode {
  display: none;
  position: fixed;
  background-color: rgba(0, 0, 0, 0.5);
  width: 100%;
  height: 100%;
  z-index: 9999;
  left: 0;
  top: 0;
}

#web-barcode-input {
  width:1px;
  height:1px;
  border:none;
}

#web-barcode-close {
  position: absolute;
  color: white;
  top: 15px;
  left: 20px;
  font-size: 40px;
  cursor: pointer;
}

.web-barcode-content {
  margin: auto;
  color: white;
  text-align: center;
}


#web-barcode-loading {
  display: block;
  width: 50px;
  height: 50px;
  border: 3px solid rgba(255,255,255,.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: web-barcode-loading-spin 1s ease-in-out infinite;
  -webkit-animation: web-barcode-loading-spin 1s ease-in-out infinite;
  margin:0 auto 10px auto;
}

@keyframes web-barcode-loading-spin {
  to { -webkit-transform: rotate(360deg); }
}
@-webkit-keyframes web-barcode-loading-spin {
  to { -webkit-transform: rotate(360deg); }
}
</style>

<script>
var isOpenWebBarcode = false;
var barcodeInputFocusInterval;

function barcodeInputFocus() {
  if (!isOpenWebBarcode) {
    clearInterval(barcodeInputFocusInterval);
    return;
  }
  $('#web-barcode-input').focus();
  $('#web-barcode-input').click();
}

function openWebBarcode(cnt) {
  if (!sendBarcodeTargetList || !sendBarcodeTargetList.length) {
    alert('잘못된 요청입니다.');
    return;
  }

  $('#web-barcode').css('display', 'flex');
  $('#web-barcode-input').focus();
  isOpenWebBarcode = true;
  $('#web-barcode-input').val('');
  barcodeInputFocusInterval = setInterval(barcodeInputFocus, 1000);
}

function closeWebBarcode() {
  isOpenWebBarcode = false;
  clearInterval(barcodeInputFocusInterval)
  $('#web-barcode').hide();
  $('#web-barcode-input').val('');
}

function findEmptyBarcodeInput() {
  for(var i=0; i<sendBarcodeTargetList.length; i++) {
    var node = $(".frm_input_" + sendBarcodeTargetList[i]);
    var val = node.val();

    if (!val || val.length < 12) {
      return node;
    }
  }
  
  return null;
}

function isDuplicateBarcode(barcode) {
  for(var i=0; i<sendBarcodeTargetList.length; i++) {
    if ($(".frm_input_" + sendBarcodeTargetList[i]).val() === barcode) {
      return true;
    }
  }
  return false;
}

function receiveBarcode(tempBarcode) {
  setTimeout(function() {
    var scannedBarcode = tempBarcode || $('#web-barcode-input').val();
    $('#web-barcode-input').val('');

    if (!scannedBarcode) return;
    if (String(scannedBarcode).length < 3) {
      alert('키보드 사용은 불가능합니다.');
      return;
    }

    // 바코드 정상 여부 체크
    $.post('/shop/ajax.check_barcode.php', {
      it_id: cur_it_id,
      barcode: scannedBarcode,
    }, 'json')
    .done(function(data) {
      var barcode = String(data.data.converted_barcode);
      var target = findEmptyBarcodeInput();

      if (barcode.length !== 12) {
        $.toast('\'' + barcode + '\'는 잘못된 바코드입니다. <br/> (12글자 아님)', {
          duration: 3000,
          type: 'danger'
        });
        return;
      }

      if (isDuplicateBarcode(barcode)) {
        $.toast('\'' + barcode + '\'는 중복된 바코드입니다. <br/> 다시스캔해주세요.', {
          duration: 3000,
          type: 'danger'
        });
        return;
      }

      target.val(barcode);
      $.toast('\'' + barcode + '\'가 등록되었습니다.', {
        duration: 2000,
        type: 'info'
      });
      notallLengthCheck();

      if (!findEmptyBarcodeInput()) {
        closeWebBarcode();
        $.toast('바코드 입력이 완료되었습니다.', {
          duration: 5000,
          type: 'success'
        });
        return;
      }
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      $.toast(data && data.message, {
        duration: 3000,
        type: 'danger'
      });
    });

  }, 100);
}

$(function(){

  $(document).on('touchstart, click', '#web-barcode-close', function(e) {
    alert('바코드스캔을 종료합니다');
    closeWebBarcode();
  });

  $(document).on('keydown', '#web-barcode-input', function(e) {
    var isScanner = e.key === 'Unidentified' || e.key === 'TVNetwork';

    if (e.key) {
      e.preventDefault();
    }

    if (!isScanner) {
      return;
    }

    receiveBarcode();
  });
});

</script>