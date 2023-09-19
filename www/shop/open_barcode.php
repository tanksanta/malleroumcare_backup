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
  #web-barcode-close { position: absolute; color: white; top: 15px; left: 20px; font-size: 60px; cursor: pointer; }
  #web-barcode-loading { display: block; width: 50px; height: 50px; border: 3px solid rgba(255,255,255,.3); border-radius: 50%; border-top-color: #fff; animation: web-barcode-loading-spin 1s ease-in-out infinite; -webkit-animation: web-barcode-loading-spin 1s ease-in-out infinite; margin:0 auto 10px auto; }

  .web-barcode-content { margin: auto; color: white; text-align: center;}
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
        openWebBarcode( $('#scanner-count').val() );
    });

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
      if ( (barcode.length === 12) && $(".frm_input_" + sendBarcodeTargetList[i]).val() === barcode ) { return true; }
      else if ( (barcode.length === 8) && $(".frm_input_" + sendBarcodeTargetList[i]).val() === '0000' + barcode ) { return true; }
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


  // 에러메시지
  function msg_error(barcode,len) {

    if (barcode.length !== len) { $.toast('\'' + barcode + '\'는 잘못된 바코드입니다. <br/> (' + len + '글자 아님)', { duration: 3000, type: 'danger' }); return false; } 
    else if ((barcode.length === 12) && isDuplicateBarcode(barcode)) { $.toast('급여제품<br/>\'' + barcode + '\'는 중복된 바코드입니다.<br/>다시스캔해주세요.', { duration: 3000, type: 'danger' }); return false; }
    else if ((barcode.length === 8) && isDuplicateBarcode(barcode)) { $.toast('장애인보조기기<br/> \'' + barcode + '\'는 중복된 바코드입니다.<br/>다시스캔해주세요.', { duration: 3000, type: 'danger' }); return false; }
    else if (isNaN(barcode)) { $.toast('\'' + barcode + '\'는 숫자 이외의 문자가 포함되어있습니다. <br/> 다시스캔해주세요.', { duration: 3000, type: 'danger' }); return false; }
    
    return true;
  }


  // 바코드 input박스 입력 처리
  function target_value_insert(barcode) {
    var target = findEmptyBarcodeInput();

    target.val(barcode);
    $.toast('\'' + barcode + '\'가 등록되었습니다.', { duration: 2000, type: 'info' });

    notallLengthCheck();
    check_option();

  }


  function receiveBarcode(tempBarcode) {
    //setTimeout(function() {

    var scannedBarcode = tempBarcode || $('#web-barcode-input').val();
        scannedBarcode = scannedBarcode.replace("-", "");

    $('#web-barcode-input').val('');

    // 바코드 데이터가 없을 경우 리턴.
    if (!scannedBarcode) return;
    // 수동 입력 부분에 대한 제한.
    else if (String(scannedBarcode).length < 7) { alert('키보드 사용은 불가능합니다.'); return; }
    // 장기요양보험 - 급여제품(판매,대여) 바코드
    else if( (String(scannedBarcode).length === 24) && (cur_pdcode === scannedBarcode.substr(0,12)) ) {
      
      // 제품 코드 제거된 바코드 추출 && 추출된 바코드에서 '하이픈' 제거
      var barcode = scannedBarcode.replace(cur_pdcode, "").replace("-", ""); // 제품 코드 제거된 바코드 추출 && 추출된 바코드에서 '하이픈' 제거
      if ( !msg_error(barcode, 12) ) { return; }
      
      // PDA에서 읽혀진 바코드에 문제가 없다면 input에 넣는다. ( 장기요양보섬 - 급여제품 바코드 )
      target_value_insert(barcode);

    }
    // 국민전강보험 - 장애인 보조기기 바코드
    else if( cur_it_use_short_barcode && (String(scannedBarcode).length === 25) && (cur_adpcode === scannedBarcode.substr(0,17)) ) { 
      
      // 제품 코드 제거된 바코드 추출 && 추출된 바코드에서 '하이픈' 제거
      var barcode =  scannedBarcode.replace(cur_adpcode,"").replace("-", ""); 
      if ( !msg_error(barcode, 8) ) { return; }
      
      // PDA에서 읽혀진 바코드에 문제가 없다면 input에 넣기. ( 국민건강보험공단 - 장애인 보조기기 바코드 )
      // 해당 바코드는 제품관련 바코드 부분을 제외하고 8자리 바코드로 운영됨에 따라 시스템에서 12인식을 바코드를 모두 변경 할수 없어,
      // 8자리 앞에 '0000' 숫자0(영)을 4개 붙녀 12자리로 만들어 바코드를 input에 넣는다.
      target_value_insert('0000'+barcode);

    }
    // 24자리 25자리 바코드를 읽었지만 기타로 분류되어 기타 바코드 인식 오류 발생할 경우.
    else {
      
      // PAD에서 읽은 바코드 값을 표현한다.
      // 바코드값에 오류가 발생했을때 PDA 기계가 어떻게 읽었는지 확인 하기 위한 용도.
      $.toast(scannedBarcode, { duration: 5000, type: 'success' });
      
      // 장기요양보험 - 급여제품(판매,대여) 바코드
      if( String(scannedBarcode).length === 24 ) {
        // 제품 코드 제거된 바코드 추출 && 추출된 바코드에서 '하이픈' 제거
        var barcode =  scannedBarcode.replace(cur_pdcode,"").replace("-", "");
        $.toast('실제 상품과 바코드의 제품코드를 확인해주세요.', { duration: 5000, type: 'danger' }); return;
      }
      
      // 국민전강보험 - 장애인 보조기기 바코드
      else if( String(scannedBarcode).length === 25 ) {
        // 제품 코드 제거된 바코드 추출 && 추출된 바코드에서 '하이픈' 제거
        var barcode =  scannedBarcode.replace(cur_adpcode,"").replace("-", ""); 
        $.toast('실제 상품과 바코드의 제품코드를 확인해주세요.<br/><br/>상품 상세정보의 장애인보조기기 옵션<br/>활성화 여부를 확인해주세요.', { duration: 8000, type: 'danger' }); return;
      }

      $.toast('바코드가 정상적으로 인식되지 않았습니다.', { duration: 3000, type: 'danger' }); return;

    }


    var target = findEmptyBarcodeInput();
    if( !findEmptyBarcodeInput() ) {
      closeWebBarcode();
      $.toast('바코드 입력이 완료되었습니다.', { duration: 5000, type: 'success' }); return;
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


  // 앱이 아닌 경우 바코드버튼 숨김
  if(!window.EroummallApp && !(window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.openBarcode )) { $('.nativePopupOpenBtn').hide(); }

</script>