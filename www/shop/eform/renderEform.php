<?php
include_once("./_common.php");

$dc_id = get_search_string($_GET['dc_id']);
$preview = get_search_string($_GET['preview']);
$zoom = get_search_string($_GET['zoom']);
if($dc_id) {
  if($preview || $download) {
    $timestamp = time();
    $entId = $member['mb_entId'];
  } else {
    $timestamp = intval($_GET['timestamp']);
  }
  $datetime = date('Y-m-d H:i:s', $timestamp);

  $uuid = $dc_id;

  $eform = sql_fetch("
    SELECT HEX(`dc_id`) as uuid, e.*
    FROM `eform_document` as e
    WHERE dc_id = UNHEX('$dc_id') and entId = '$entId' and dc_status in ('10', '11') ");
  if(!$eform['uuid']) {
    die('계약서를 확인할 수 없습니다.');
  }

  if($preview && !$eform['penNm'])
    $eform['penNm'] = '이로움';
} else {
  $timestamp = intval($_GET['timestamp']);
  $datetime = date('Y-m-d H:i:s', $timestamp);

  $eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");

  if($eform['uuid'] !== $uuid || $eform['entId'] !== $entId || $eform['penId'] !== $penId) {
    alert('계약서를 확인할 수 없습니다.');
  }
}

$items = sql_query("SELECT * FROM `eform_document_item` WHERE dc_id = UNHEX('{$eform["uuid"]}')");

$buy = [];
$rent = [];
while($item = sql_fetch_array($items)) {
  if($item['gubun'] == '00') array_push($buy, $item); // 판매 재고
  else if ($item['gubun'] == '01') array_push($rent, $item); // 대여 재고
}

// 서명 정보 가져오기
$contents = sql_query("SELECT * FROM `eform_document_content` WHERE dc_id = UNHEX('{$eform["uuid"]}')");
$state = array();
while($ct = sql_fetch_array($contents)) {
  $id = $ct['ct_id'];
  $val = $ct['ct_content'];
  $state[$id] = $val;
}

// 기초수급자,의료수급자 체크
$is_gicho = ($eform['penTypeCd'] == '04' || $eform['penTypeCd'] == '03')? true:false;

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=1240, initial-scale=1.0">
  <title><?php echo $eform['dc_subject']; ?></title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/signeform.css">
  <link rel="stylesheet" href="css/thk101.css">
  <link rel="stylesheet" href="css/thk102.css">
  <link rel="stylesheet" href="css/thk001.css">
  <link rel="stylesheet" href="css/thk002.css">
  <link rel="stylesheet" href="css/thk003.css">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.7.2/bluebird.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
    <script src="https://unpkg.com/html2canvas@1.0.0-rc.5/dist/html2canvas.js"></script>
  <?php if($preview) echo "<script src='https://unpkg.com/panzoom@9.4.2/dist/panzoom.min.js'></script>"; ?>
  <script src="<?=G5_JS_URL?>/jquery-1.11.3.min.js"></script>
  <style>
    <?php if($download) { ?>
    @page {
      size:210mm 297mm;
      margin: 0;
    }
    @media print {
      html, body {
        margin: 15mm 0;
        padding: 0;
      }
    }
    <?php } ?>
    body.render-eform {
      background-color: #fff;
    }

    .render-eform-body .a4 {
      width: 1240px;
      margin: 0 auto;
      background-color: #fff;
      page-break-inside: avoid;
    }

    .render-eform-body .a4 + .a4 {
      page-break-before: always;
    }
  </style>
</head>
<body class="render-eform" <?php if($preview) echo 'style="width: 1240px;"'; ?>>
<div id="" style="width:100%;text-align:center;padding:10px;position:fixed;top:0px;left:0px;z-index:99999999;">
	<input type="button" value="PDF저장" id="savePdf" style="margin-left:1200px;cursor:pointer;">
</div>
  <div class="render-eform-body wrap" id="content">
    <?php
    if($is_gicho) {
		echo "<div class='pdf_page'>";
      include_once('./document/thk101.php');
		echo "</div><div class='pdf_page'>";
      include_once('./document/thk102.php');
		echo "</div>";
    }
	echo "<div class='pdf_page'>";
    include_once('./document/thk001_1.php');
	echo "</div><div class='pdf_page'>";
    include_once('./document/thk001_2.php');
	echo "</div><div class='pdf_page'>";
    include_once('./document/thk002.php');
	echo "</div><div class='pdf_page'>";
    $is_render = "Y";
    include_once('./document/thk003.php');
	echo "</div>";
    ?>
  </div>
  <script>
  $(function() {
    var isGicho = <?=($is_gicho ? 'true' : 'false')?>;

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
      chk_001_1: <?=$state['chk_001_1'] ?: 'false'?>,
      sign_001_1: '<?=htmlspecialchars($state['sign_001_1'])?>',
      seal_001_1: '<?=htmlspecialchars($state['seal_001_1'])?>',
      seal_002_1: '<?=htmlspecialchars($state['seal_002_1'])?>',
      sign_002_1: '<?=htmlspecialchars($state['sign_002_1'])?>',
      chk_003_1: <?=$state['chk_003_1'] ?: 'false'?>,
      chk_003_2: <?=$state['chk_003_2'] ?: 'false'?>,
      chk_003_3: <?=$state['chk_003_3'] ?: 'false'?>,
      sign_003_1: '<?=htmlspecialchars($state['sign_003_1'])?>',
    };
    if(isGicho) {
      $.extend(state, {
        sign_101_1: '<?=htmlspecialchars($state['sign_101_1'])?>',
        sign_101_2: '<?=htmlspecialchars($state['sign_101_2'])?>',
        sign_101_3: '<?=htmlspecialchars($state['sign_101_3'])?>'
      });
    }


    function repaint() {
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
        if(imageURL)
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
          $wrap.html('');
        } else {
          var imageURL = state[id];
          $wrap.html('<img src="'+imageURL+'" height="'+pos[id].height+'" alt="수급자 서명">');
        }
      });

      // 체크박스
      <?php if($download) { ?>
      $('.chk-form').prop('disabled', true).addClass('done');
      <?php } else { ?>
      $('.chk-form').each(function() {
        var id = $(this).attr('id').split('_');
        var YorN = id.pop();
        var reverse = YorN === 'n';
        id = id.join('_');

        var $chk = $('#'+id+'_'+YorN);
        $chk.prop('checked', reverse ? !state[id] : state[id]);
        $chk.prop('disabled', true);
        $chk.addClass('done');
      });
      <?php } ?>
    }

    repaint();

	$("#savePdf").click(function() { // pdf저장 button id
		
    // setTImeout을 하는 이유는 html2canvas를 불러오는게 너무 빨라서 앞의 js가 먹혀도 반영되지 않은 것처럼 보임
    // 따라서 0.1 초 지연 발생 시킴
      setTimeout(function() {
		$(".thk101").css("padding","200px 0");
		$(".thk102").css("padding","200px 0");
		$(".thk001").css("padding","200px 0");
		$(".thk002").css("padding","200px 0");
		$(".thk003").css("padding","200px 0");
		createPdf();
      }, 100);
  });
  });

  <?php if($preview) { ?>
  var zoomInstance = panzoom(document.body, {
    maxZoom: 1,
    minZoom: 0.4,
    initialZoom: 0.4,
    beforeWheel: function(e) {
      // allow wheel-zoom only if altKey is down. Otherwise - ignore
      var shouldIgnore = !e.altKey;
      return shouldIgnore;
    }
  });

  var zoomIn = false;
  function toggleZoom() {
    zoomIn = !zoomIn;
    if(zoomIn) {
      zoomInstance.smoothZoom(0, 0, 2.5);
    } else {
      zoomInstance.smoothZoom(0, 0, 0.4);
    }
  }

  function zoomStep(step) {
    switch(step) {
      case 0:
      case 1:
        zoomInstance.smoothZoom(0, 0, 1.582);
        break;
      case 2:
        zoomInstance.smoothZoom(0, 0, 0.4);
        break;
    }
  }
  <?php } ?>
  </script>
<script language = "javascript">

var renderedImg = new Array;

var contWidth = 240, // 너비(mm) (a4에 맞춤)
    padding = -15; //상하좌우 여백(mm)

function createPdf() { //이미지를 pdf로 만들기


  var lists = document.querySelectorAll(".pdf_page"),
      deferreds = [],
      doc = new jsPDF("p", "mm", "a4"),
      listsLeng = lists.length;

  for (var i = 0; i < listsLeng; i++) { // pdf_page 적용된 태그 개수만큼 이미지 생성
    var deferred = $.Deferred();
    deferreds.push(deferred.promise());
    generateCanvas(i, doc, deferred, lists[i]);
  }

  $.when.apply($, deferreds).then(function () { // 이미지 렌더링이 끝난 후
    var sorted = renderedImg.sort(function(a,b){return a.num < b.num ? -1 : 1;}), // 순서대로 정렬
        curHeight = padding, //위 여백 (이미지가 들어가기 시작할 y축)
        sortedLeng = sorted.length;

    for (var i = 0; i < sortedLeng; i++) {
      var sortedHeight = sorted[i].height, //이미지 높이
          sortedImage = sorted[i].image; //이미지

      if( curHeight + sortedHeight > 297 - padding * 2 ){ // a4 높이에 맞게 남은 공간이 이미지높이보다 작을 경우 페이지 추가
        doc.addPage(); // 페이지를 추가함
        curHeight = padding; // 이미지가 들어갈 y축을 초기 여백값으로 초기화
        doc.addImage(sortedImage, 'jpeg', padding , curHeight, contWidth, sortedHeight); //이미지 넣기
        curHeight += sortedHeight; // y축 = 여백 + 새로 들어간 이미지 높이
      } else { // 페이지에 남은 공간보다 이미지가 작으면 페이지 추가하지 않음
        doc.addImage(sortedImage, 'jpeg', padding , curHeight, contWidth, sortedHeight); //이미지 넣기
        curHeight += sortedHeight; // y축 = 기존y축 + 새로들어간 이미지 높이
      }
    }
    doc.save('<?php echo $eform['dc_subject'];?>.pdf'); //pdf 저장

    curHeight = padding; //y축 초기화
    renderedImg = new Array; //이미지 배열 초기화

  });	
        $(".thk101").css("padding","0 128px");
		$(".thk102").css("padding","0 128px");
		$(".thk001").css("padding","0 128px");
		$(".thk002").css("padding","0 128px");
		$(".thk003").css("padding","0 128px");
}

function generateCanvas(i, doc, deferred, curList){ //페이지를 이미지로 만들기
  var pdfWidth = $(curList).outerWidth() * 0.2645, //px -> mm로 변환
      pdfHeight = $(curList).outerHeight() * 0.2645,
      heightCalc = contWidth * pdfHeight / pdfWidth; //비율에 맞게 높이 조절
  html2canvas( curList ).then(
    function (canvas) {
      var img = canvas.toDataURL('image/jpeg', 1.0); //이미지 형식 지정
      renderedImg.push({num:i, image:img, height:heightCalc}); //renderedImg 배열에 이미지 데이터 저장(뒤죽박죽 방지)     
      deferred.resolve(); //결과 보내기
    }
  );
}
 
</script>

</body>
</html>
