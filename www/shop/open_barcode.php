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
    <div>바코드 스캔 대기중..<br /></div>
    <input type="text" id="web-barcode-input"/>
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
    <p> PDA스캔은 PDA기기 이용자만 선택해주세요. </p>
  </div>
</div>


<style>
#barcode-selector { display: none; position: fixed; background-color: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; z-index: 9999; left: 0; top: 0; }
#barcode-selector-close { position: absolute; color: black; top: 15px; right: 15px; font-size: 20px; cursor: pointer; }

.barcode-selector-content { margin: auto; color: black; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); position: absolute; background: white; padding: 30px 30px 20px 30px; width: 80%; }
.barcode-selector-content ul { display:-webkit-box; display:-ms-flexbox; display:flex; -webkit-box-pack: justify; -ms-flex-pack: justify; justify-content: space-between; margin: 15px 0; }
.barcode-selector-content ul li { width: 48%; border-radius: 5px; color: white; background-color: #5f5f5f; line-height:45px; cursor:pointer; }
.barcode-selector-content ul li.orange { background-color:#ee8102; }
.barcode-selector-content p { font-size: 0.8em; }

#web-barcode { display: none; position: fixed; background-color: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; z-index: 9999; left: 0; top: 0; }
#web-barcode-input { width:0.1px; height:0.1px; font-size:20px; border:none; }
#web-barcode-close { position: absolute; color: white; top: 15px; left: 20px; font-size: 40px; cursor: pointer; }
.web-barcode-content { margin: auto; color: white; text-align: center;}
#web-barcode-loading { display: block; width: 50px; height: 50px; border: 3px solid rgba(255,255,255,.3); border-radius: 50%; border-top-color: #fff; animation: web-barcode-loading-spin 1s ease-in-out infinite; -webkit-animation: web-barcode-loading-spin 1s ease-in-out infinite; margin:0 auto 10px auto; }

.toast { width: 98% !important; left: 0 !important; margin: 5px auto 0 auto !important; right: 0; text-align:center; font-weight:bold; }

@keyframes web-barcode-loading-spin { to { -webkit-transform: rotate(360deg); } }
@-webkit-keyframes web-barcode-loading-spin { to { -webkit-transform: rotate(360deg); } }
</style>


<script>
var isOpenWebBarcode = false;
var barcodeInputFocusInterval;

/* 기종체크 */
var deviceUserAgent = navigator.userAgent.toLowerCase();
var device;

if(deviceUserAgent.indexOf("android") > -1) {
  /* android */
  device = "android";
} else if(deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1) {
  /* ios */
  device = "ios";
}


$(function(){


  $(document).on('keyup', '#web-barcode-input', function(e) {
    var isScanner = e.key === 'Unidentified' || e.key === 'TVNetwork';

    if (e.key) { e.preventDefault(); }
    if (!isScanner) { return; }
    
    receiveBarcode();

  });

  
  $("#web-barcode-input").focusout(function(e) {
    $('#web-barcode-input').attr("readonly",true);
    $('#web-barcode-input').focus();
    setTimeout(function(){ $('#web-barcode-input').attr("readonly",false); }, 80);
  });

  $(document).on('touchstart, click', '#web-barcode-close', function(e) {
    //alert('바코드스캔을 종료합니다');
    closeWebBarcode();
  });

  $(document).on('touchstart, click', '#pda-scanner-opener', function(e) {
    var cnt = $('#scanner-count').val();
    $('#barcode-selector').hide();
      openWebBarcode(cnt);
  });

/*

  $(document).on('touchstart, click', '#barcode-selector, #barcode-selector-close', function(e) {
    if ($(e.target).is('.barcode-selector-content')) {
      return;
    }
    $('#barcode-selector').fadeOut();
  });

  
  $(document).on('touchstart, click', '#barcode-scanner-opener', function (e) {
    var cnt = $('#scanner-count').val();
    $('#barcode-selector').hide();
    try {
      switch (device) {
        case "android": // android
          window.EroummallApp.openBarcode("" + cnt + "");
          break;
        case "ios": // ios
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
  
  */

});


// PDA버전 바코드 창 활성화
function openWebBarcode(cnt) {
  if (!sendBarcodeTargetList || !sendBarcodeTargetList.length) {
    alert('잘못된 요청입니다.'); return;
  }

  $('#web-barcode').css('display', 'flex');
  $('#web-barcode-input').attr("readonly",true);
  $('#web-barcode-input').focus();
  setTimeout(function(){ $('#web-barcode-input').attr("readonly",false); }, 80);

  isOpenWebBarcode = true;
  $('#web-barcode-input').val('');

}

// 입력될 바코드 위치 필드 검색
function findEmptyBarcodeInput() {
  //alert( $('html').scrollTop() );
  for(var i=0; i<sendBarcodeTargetList.length; i++) {
    var node = $(".frm_input_" + sendBarcodeTargetList[i]);
    var val = node.val();

    if (!val || val.length < 12) {
      // 바코드가 정상적으로 입려되었을 경우 화면상 스크롤을 움직여 입력된 내용을 확인할 수 있게 처리.
      $('html').scrollTop( (node.offset().top - 400) );
      return node;
    }
  }
 
  return null;
}

// 동일 바코드 검색
function isDuplicateBarcode(barcode) {
  for(var i=0; i<sendBarcodeTargetList.length; i++) {
    if ($(".frm_input_" + sendBarcodeTargetList[i]).val() === barcode) {
      return true;
    }
  }
  return false;
}

// 바코드창 닫기
function closeWebBarcode() {
  isOpenWebBarcode = false;
  //clearInterval(barcodeInputFocusInterval)
  $('#web-barcode').hide();
  $('#web-barcode-input').val('');
}


function receiveBarcode(tempBarcode) {
  //setTimeout(function() {
    var scannedBarcode = tempBarcode || $('#web-barcode-input').val();
    $('#web-barcode-input').val('');

    if (!scannedBarcode) return;
    else if (String(scannedBarcode).length < 7) {
      alert('키보드 사용은 불가능합니다.');
      return;
    }

    var barcode =  scannedBarcode.replace(cur_pdcode,""); // 제품 코드 제거된 바코드 추출
        barcode =  barcode.replace("-",""); // 추출된 바코드에서 '하이픈' 제거

    var target = findEmptyBarcodeInput();

    // cur_pdcode 글로벌 변수로 상위에 무조건 정의 되어 있어야 한다.
    // cur_pdcode 해당 변수는 상품의 급여제품 코드로 스캔된 앞 12자리와 화면상 선택된 제품의 급여제품 코드가 동일해야 한다.
    if( cur_pdcode === scannedBarcode.substr(0,12) ) {

      if (barcode.length !== 12) {
        $.toast('\'' + barcode + '\'는 잘못된 바코드입니다. <br/> (12글자 아님)', { duration: 3000, type: 'danger' });
        return;
      } 
      else if (isDuplicateBarcode(barcode)) {
        $.toast('\'' + barcode + '\'는 중복된 바코드입니다. <br/> 다시스캔해주세요.', { duration: 3000, type: 'danger' });
        return;
      }
      else if(isNaN(barcode)) {
        $.toast('\'' + barcode + '\'는 숫자 이외의 문자가 포함되어있습니다. <br/> 다시스캔해주세요.', { duration: 3000, type: 'danger' });
        return;
      }

      target.val(barcode);
      $.toast('\'' + barcode + '\'가 등록되었습니다.', { duration: 2000, type: 'info' });

      notallLengthCheck();
      check_option();

    } else {

      if( !barcode ) {     
        $.toast('상품의 제품코드가 입력되지 않았습니다.', { duration: 5000, type: 'success' });
        return;
      }
      else if(barcode.length > 13) {

        if( !(cur_pdcode === scannedBarcode.substr(0,12)) ) {
          $.toast('상품과 바코드의 제품코드가 잘못되었습니다.', { duration: 5000, type: 'success' });
          return;
        }

      }
      else {
        $.toast('바코드가 정상적으로 인식되지 않았습니다.', { duration: 5000, type: 'success' });
        return;
      }

    }

    if( !findEmptyBarcodeInput() ) {
      closeWebBarcode();
      $.toast('바코드 입력이 완료되었습니다.', { duration: 5000, type: 'success' });
      return;
    } else {
      // 자동 추가 버튼 추가
      $(target).closest('li').find('.barcode_add').show();
    }

    <?php 
    /* 23.01.17 : 서원 - 주석전용 */
    // 기존 ajax를 통한 바코드 상품의 유무를 체크 하였으나, 불필요한 쿼리문으로 판단됨.
    // 이미 리스트의 상품 데이터에서 해당 제품을 스캔 하려고하며, 상품의 정보를 모두 알고 있는 상태에서 다시 해당 상품의 급여코드를 검색 하는건 의미가 없음.
    // 단, 급여코드와 다르거나 바코드의 스캔 데이터를 검증하기 위한 코드는 필요함.
    ?>

    /*
    // 바코드 정상 여부 체크
    $.post('/shop/ajax.check_barcode.php', { it_id: cur_it_id, barcode: scannedBarcode }, 'json')
    .done(function(data) {
      var barcode = String(data.data.converted_barcode);
      var target = findEmptyBarcodeInput();


    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      $.toast(data && data.message, { duration: 3000, type: 'danger' });
    });
    */

  //}, 80);

}


/*
function barcodeInputFocus() {
  if (!isOpenWebBarcode) {
    //clearInterval(barcodeInputFocusInterval);
    return;
  }

  $('#web-barcode-input').attr("readonly",true);
  $('#web-barcode-input').focus();
  setTimeout(function(){ $('#web-barcode-input').attr("readonly",false);}, 25);

  //$('#web-barcode-input').click();
}

*/

// 앱이 아닌 경우 바코드버튼 숨김
if(!window.EroummallApp && !(window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.openBarcode )) {
  $('.nativePopupOpenBtn').hide();
}
</script>