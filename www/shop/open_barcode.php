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

<div id="barcode-selector">
  <input type="hidden" id="scanner-count" value="0" />
  <div class="barcode-selector-content">
	  <i id="barcode-selector-close" class="fa fa-times"></i>
    <h4>스캔 방법을 선택하세요.</h4>
    <ul>
      <li class="orange" id="barcode-scanner-opener">바코드 스캔</li>
      <li id="pda-scanner-opener">PDA 스캔</li>
    </ul>
    <p>
      PDA스캔은 PDA기기 이용자만 선택해주세요.
    </p>
  </div>
</div>

<style>
#barcode-selector {
  display: none;
  position: fixed;
  background-color: rgba(0, 0, 0, 0.5);
  width: 100%;
  height: 100%;
  z-index: 9999;
  left: 0;
  top: 0;
}

#barcode-selector-close {
  position: absolute;
  color: black;
  top: 15px;
  right: 15px;
  font-size: 20px;
  cursor: pointer;
}

.barcode-selector-content {
  margin: auto;
  color: black;
  text-align: center;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  position: absolute;
  background: white;
  padding: 30px 30px 20px 30px;
  width: 80%;
}

.barcode-selector-content ul {
  display:-webkit-box;
  display:-ms-flexbox;
  display:flex;
  -webkit-box-pack: justify;
  -ms-flex-pack: justify;
  justify-content: space-between;
  margin: 15px 0;
}
.barcode-selector-content ul li {
  width: 48%;
  border-radius: 5px;
  color: white;
  background-color: #5f5f5f;
  line-height:45px;
  cursor:pointer;
}
.barcode-selector-content ul li.orange {
  background-color:#ee8102;
}
.barcode-selector-content p {
  font-size: 0.8em;
}

.toast {
  width: 98% !important;
  left: 0 !important;
  margin: 5px auto 0 auto !important;
  right: 0;
  text-align:center;
  font-weight:bold;
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

      if(isNaN(barcode)) {
        $.toast('\'' + barcode + '\'는 숫자 이외의 문자가 포함되어있습니다. <br/> 다시스캔해주세요.', {
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
      check_option();

      if (!findEmptyBarcodeInput()) {
        closeWebBarcode();
        $.toast('바코드 입력이 완료되었습니다.', {
          duration: 5000,
          type: 'success'
        });
        return;
      } else {
        // 자동 추가 버튼 추가
        $(target).closest('li').find('.barcode_add').show();
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

  $(document).on('touchstart, click', '#barcode-selector, #barcode-selector-close', function(e) {
    if ($(e.target).is('.barcode-selector-content')) {
      return;
    }
    $('#barcode-selector').fadeOut();
  });

  $(document).on('touchstart, click', '#pda-scanner-opener', function(e) {
    var cnt = $('#scanner-count').val();
    $('#barcode-selector').hide();
    setTimeout(() => {
      openWebBarcode(cnt);
    }, 500);
  });

  $(document).on('touchstart, click', '#barcode-scanner-opener', function (e) {
    var cnt = $('#scanner-count').val();
    $('#barcode-selector').hide();
    try {
      switch (device) {
        case "android":
          /* android */
          window.EroummallApp.openBarcode("" + cnt + "");
          break;
        case "ios":
          /* ios */
          window.webkit.messageHandlers.openBarcode.postMessage("" + cnt + "");
          break;
        default:
          throw new Error();
          break;
      }
    } catch (e) {
      alert('오류가 발생하였습니다.\n' + e);
    }
  });

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