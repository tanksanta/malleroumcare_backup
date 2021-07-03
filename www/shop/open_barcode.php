<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once('./_common.php');
?>

<div id="web-barcode">
	<i id="web-barcode-close" class="fa fa-times"></i>
  <div class="web-barcode-content">
    <div id="web-barcode-loading"></div>
    <div>
      바코드 스캔 대기중..<br />
    </div>
    <input type="text" id="web-barcode-input" style="width:1px;height:1px;border:none;" />
  </div>
</div>

<style>
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

$(function(){

  $(document).on('touchstart', '#web-barcode-close', function(e) {
    alert('바코드스캔을 종료합니다');
    closeWebBarcode();
    e.preventDefault();
    e.stopPropagation();
  });

  $(document).on('keydown', '#web-barcode-input', function(e) {
    var isScanner = e.key === 'Unidentified' || e.key === 'TVNetwork';

    if (e.key) {
      e.preventDefault();
    }

    if (isScanner) {
      setTimeout(function() {
        var scannedBarcode = $('#web-barcode-input').val();
        $('#web-barcode-input').val('');

        if (!scannedBarcode) return;
        if (scannedBarcode.length < 3) {
          alert('키보드 사용은 불가능합니다.');
          return;
        }

        // 바코드 정상 여부 체크
        $.post('/shop/ajax.check_barcode.php', {
          it_id: cur_it_id,
          barcode: scannedBarcode,
        }, 'json')
        .done(function(data) {
          var sendBarcodeTarget = $(".frm_input_" + sendBarcodeTargetList[0]);
          $(sendBarcodeTarget).val(data.data.converted_barcode);
          sendBarcodeTargetList = sendBarcodeTargetList.slice(1);

          if (!sendBarcodeTargetList.length) { // 완료시
            closeWebBarcode();
          }
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
          closeWebBarcode();
        });

      }, 100)
    }
  });
});

</script>