<?php
include_once("./_common.php");

if(!$is_member) {
  alert('먼저 로그인하세요.');
}

$dc_id = get_search_string($_GET['dc_id']);
if($dc_id) {
  $eform = sql_fetch("
    SELECT HEX(`dc_id`) as uuid, e.*
    FROM `eform_document` as e
    WHERE dc_id = UNHEX('$dc_id') and entId = '{$member['mb_entId']}' and dc_status = '11' ");
  if(!$eform['uuid']) {
    die('계약서를 확인할 수 없습니다.');
  }
} else {
  $sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '$od_id'";
  if($is_member && !$is_admin)
      $sql .= " AND mb_id = '{$member['mb_id']}' ";
  $od = sql_fetch($sql);
  if(!$od['mb_id']) {
    alert('계약서를 작성할 권한이 없습니다.');
  }

  $eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");

  if($eform['dc_status'] != '1') {
    alert('이미 작성된 계약서입니다.');
  }
}

$items = sql_query("SELECT * FROM `eform_document_item` WHERE dc_id = UNHEX('{$eform["uuid"]}')");

$buy = [];
$rent = [];
while($item = sql_fetch_array($items)) {
  if($item['gubun'] == '00') array_push($buy, $item); // 판매 재고
  else if ($item['gubun'] == '01') array_push($rent, $item); // 대여 재고
}

// 기초수급자,의료수급자 체크
$is_gicho = ($eform['penTypeCd'] == '04' || $eform['penTypeCd'] == '03')? true:false;

// 계약서 로그 작성
$log = '전자계약서의 내용을 확인했습니다.';

$ip = $_SERVER['REMOTE_ADDR'];
$browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

sql_query("INSERT INTO `eform_document_log` SET
`dc_id` = UNHEX('{$eform["uuid"]}'),
`dl_log` = '$log',
`dl_ip` = '$ip',
`dl_browser` = '$browser',
`dl_datetime` = '$datetime'
");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="initial-scale=1.0,user-scalable=1,width=device-width">
  <title><?php echo $eform['dc_subject']; ?></title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/signeform.css?v=06042032">
  <link rel="stylesheet" href="css/thk101.css">
  <link rel="stylesheet" href="css/thk102.css">
  <link rel="stylesheet" href="css/thk001.css">
  <link rel="stylesheet" href="css/thk002.css">
  <link rel="stylesheet" href="css/thk003.css">
  <script src="<?=G5_JS_URL?>/signature_pad.umd.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body>
  <div class="sign-eform-head flexbox justify">
    <h1 id="eformTitle" class="flex"><?=$eform['dc_subject']?></h1>
    <button id="btnCloseSign">나가기</button>
  </div>
  <div class="sign-eform-body">
    <?php
    if($is_gicho) {
      include_once('./document/thk101.php');
      include_once('./document/thk102.php');
    }
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

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

  <script>
	var confirmYN = function (param){
		return new Promise(function(resolve, reject) {
			setTimeout(function() {
				Swal.fire({
		  			title: "수급자에게 '계약서 작성 완료' 안내 메시지를 보내시겠습니까?",
					//text: "삭제하시면 다시 복구시킬 수 없습니다.",
		  			//icon: 'warning',
		  			showCancelButton: true,
		 			confirmButtonColor: '#3085d6',
		  			cancelButtonColor: '#d33',
		  			confirmButtonText: '예',
		  			cancelButtonText: '아니오'
				}).then((result) => {
		  			if (result.value) {
         			   //"보내기" 버튼을 눌렀을 때  
		 			 	resolve(true);
					}
		 			else
					  	resolve(false);
				}) 
			}, 100);
		});
	}
  $(function() {
	var isGicho = <?=($is_gicho ? 'true' : 'false')?>;
    <?php if($dc_id) { ?>
    var od_url = '/shop/electronic_manage.php';
    <?php } else { ?>
    var od_url = '<?=G5_SHOP_URL?>/orderinquiryview.php?od_id=<?=$od_id?>&uid=<?=md5($od_id.$od['od_time'].$od['od_ip'])?>';
    <?php } ?>

    var currentStage = 1;
    var totalStage = isGicho ? 5 : 3;

    var pos = {
      sign_101_1: {
        top: -5,
        left: 10,
        width: 120,
        height: 40
      },
      sign_101_2: {
        top: -5,
        left: 10,
        width: 120,
        height: 40
      },
      sign_101_3: {
        top: -5,
        left: 10,
        width: 120,
        height: 40
      },
      sign_001_1: {
        top: -5,
        left: 10,
        width: 120,
        height: 40
      },
      seal_001_1: {
        top: -5,
        left: 10,
        width: 120,
        height: 40
      },
      seal_002_1: {
        top: 5,
        left: 40,
        width: 120,
        height: 40
      },
      sign_002_1: {
        top: -3,
        left: 40,
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
    if(isGicho) {
      $.extend(state, {
        sign_101_1: '',
        sign_101_2: '',
        sign_101_3: ''
      });
    }

    $('#btnPrev').click(function(e) {
      e.preventDefault();

      if(currentStage > 1) {
        currentStage--;
        repaint();
        scrollToTop();
      } else {
        alert('첫 번째 단계입니다.');
      }
    });

    $('#btnNext').click(function(e) {
      e.preventDefault();

      var todos = getTodos();
	  var smsFlag = true;
      
	  if(todos.current < todos.total) {
        return alert('현재 단계에서 모든 입력을 완료해주세요.');
      }

      if(currentStage < totalStage) {
        currentStage++;
        repaint();
        scrollToTop();
      } else {
        // 마지막 단계 작성완료
		console.log(smsFlag);
		confirmYN().then(function(text){
			smsFlag = text;	
       		if(!confirm('계약서 작성을 완료하시겠습니까?')) return;
		
			$(this).text('진행 중...');
     	    $(this).prop('disabled', true);
				
        	$.post('./ajax.eform.sign.php', {
      	    state: JSON.stringify(state),
      	    uuid: '<?=$eform["uuid"]?>',
			sms:smsFlag, }, 'json'
        	)
        	.done(function(data) {
          		// 작성 완료
          		alert('계약서 작성이 완료되었습니다.');
          		location.href = '/shop/electronic_manage.php';
        	})
        	.fail(function($xhr) {
          		$(this).text('완료');
          		$(this).prop('disabled', false);
          		var data = $xhr.responseJSON;
          		alert(data && data.message);
          		location.href = '/shop/electronic_manage.php';
        	});
		  }, function (error) {
			console.error(error)
          	alert(error);
		});
      }
    });

    $('#btnCloseSign').click(function(e) {
      e.preventDefault();
      location.href = od_url;
    });

    function closeSignPopup() {
      var todos = getTodos();
	  if(todos.current == todos.total) {
        $('#btnNext').addClass('primary');
      }
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

    $(document).on('click', '.chk-form', function(e) {
      //e.preventDefault();

      var id = $(this).attr('id').split('_');
      var tail = id.pop();
      var isN = tail === 'n';
      id = id.join('_');
      if(isN) {
        state[id] = false;
      } else {
        if ($('#'+id+'_n').length > 0) {
          console.log(tail);
          state[id] = true;
        }
        else if (tail == 'all') {
          state['chk_003_1'] = true;
          state['chk_003_2'] = true;
          state['chk_003_3'] = true;
        }
        else {
          state[id] = !state[id];
        }
      }

      repaint();
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

    // 입력완료 체크
    function getTodos() {
      var currentTodos = 0;
      var totalTodos = 0;
      for(var id in state) {
        var key = id.split('_')
        // 사업소 직인은 이미 입력되어있으니까
        if(getCurrentDocument() === key[1] && key[0] !== 'seal') {
          totalTodos++;
          if(state[id]) currentTodos++;
        }
      }

      var todos = {
        current: currentTodos,
        total: totalTodos
      }

      return todos;
    }

    function scrollToTop() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }

    function getCurrentDocument() {
      var documentlist = ['001', '002', '003'];
      if(isGicho) documentlist.unshift('101', '102');
      return documentlist[currentStage - 1];
    }

    function repaint() {
      // 현재 단계만 보여주기
	  $('.a4').css({ display: 'none' });
      var currentDocument = getCurrentDocument();
      if(currentDocument === '001') $('#thk001_1, #thk001_2').css({ display: 'block' });
      else $('#thk' + currentDocument).css({ display: 'block' });

      // 마지막 단계면 작성완료 버튼으로 변경
      if(currentStage === totalStage) {
        $('#btnNext').removeClass('primary').text('완료');
      } else {
        $('#btnNext').removeClass('primary').text('다음단계');
      }

      // 직인
      $('.seal-form').each(function() {
        var id = $(this).data('id');
        if($('#'+id).length === 0) $(this).append('<div id="'+id+'" class="seal-wrap"></div>')
        
        var $wrap = $('#'+id);
        $wrap.css({
          top: pos[id].top,
          left: pos[id].left,
          /*width: pos[id].width,
          height: pos[id].height*/
          maxWidth: 130,
          maxHeight: 130
        });

        if(id == 'seal_002_1') {
          $wrap.css({
            transform: 'translateY(-50%)'
          });
        }

        var imageURL = state[id];
        $wrap.html('<img src="'+imageURL+'" alt="사업소 직인">');
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
          $wrap.html('<img class="img-sign" data-id="'+id+'" src="'+imageURL+'" height="'+pos[id].height+'" alt="수급자 서명">');
        }
      });

      // 체크박스
      $('.chk-form').each(function() {
        var id = $(this).attr('id').split('_');
        var YorN = id.pop();
        var reverse = YorN === 'n';
        id = id.join('_');

        var $chk = $('#'+id+'_'+YorN);
        $chk.prop('checked', reverse ? !state[id] : state[id]);

        if (state['chk_003_1'] && state['chk_003_2'] && state['chk_003_3']) {
          $('#chk_003_all_label').hide();
        }
        else {
          $('#chk_003_all_label').show();
          $('#chk_003_all').prop('checked', false);
        }

      });

      // 입력 상태
      var todos = getTodos();
		if(todos.current == todos.total && currentStage !== totalStage) {
        $('#btnNext').addClass('primary');
      }
      $('.sign-eform-foot .desc').text(todos.total + '건 중 ' + todos.current + '건 완료되었습니다.');

      if(todos.current === todos.total) {

      } else {

      }
    }

    repaint();

    window.onresize = resizeHandler;
  });
  </script>
</body>
</html>
