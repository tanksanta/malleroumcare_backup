<?php
include_once("./_common.php");

$dc_id = get_search_string($_GET['dc_id']);
$preview = get_search_string($_GET['preview']);
$download = get_search_string($_GET['download']);
$zoom = get_search_string($_GET['zoom']);
if($dc_id) {
  if($preview || $download) {
    $timestamp = time();
    $entId = (get_search_string($_GET['entId']) == "")?$member['mb_entId'] : get_search_string($_GET['entId']);
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

//대리인 관계
switch($eform['contract_sign_relation']){
	case "0": $contract_sign_relation = "본인"; break;
	case "1": $contract_sign_relation = "가족"; break;
	case "2": $contract_sign_relation = "친족"; break;
	case "3": $contract_sign_relation = "기타"; break;
	default:$contract_sign_relation = "본인"; break;
}

//신청인 관계
switch($eform['applicantRelation']){
	case "0": $applicantRelation = "본인"; break;
	case "1": $applicantRelation = "가족"; break;
	case "2": $applicantRelation = "친족"; break;
	case "3": $applicantRelation = "기타"; break;
	case "4": $applicantRelation = "대리인"; break;
	case "5": $applicantRelation = ""; break;
	default:$applicantRelation = "본인"; break;
}
//신청인 항목
if($eform['applicantRelation'] == "0"){//본인으로 되있을 경우
		$applicantNm = $eform['penNm'];//신청인명
		$applicantBirth = $eform['penBirth'];//신청인생년월일
		$applicantRelation = "본인";//수급자와의 관계
		$applicantAddr = "(".$eform['penZip'].") ".$eform['penAddr']." ".$eform['penAddrDtl'];//신청인 주소
		$applicantTel = $eform['penConNum'];//신청인 전화번호
}elseif($eform['applicantRelation'] == "4"){//대리인으로 되있을 경우
	$applicantNm = $eform['contract_sign_name'];//신청인명
		$applicantBirth = "";//신청인생년월일 공란으로 유지
		$applicantRelation = $contract_sign_relation;//수급자와의 관계
		$applicantAddr = $eform['contract_addr'];//신청인 주소
		$applicantTel = $eform['contract_tel'];//신청인 전화번호
}else{//신청이 별도로 있을 경우
	$applicantNm = $eform['applicantNm'];//신청인명
	$applicantBirth = $eform['applicantBirth'];//신청인생년월일
	$applicantRelation = $applicantRelation;//수급자와의 관계
	$applicantAddr = $eform['applicantAddr'];//신청인 주소
	$applicantTel = $eform['applicantTel'];//신청인 전화번호
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
<?php if($_GET['entId'] == ""){?>
<div id="pdf_save_bt" style="width:100%;text-align:center;padding:10px;position:fixed;top:0px;left:0px;z-index:99999999;">
	<form name="WkhtmlPdfForm" id="WkhtmlPdfForm" action="./pdf_down.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
		<input type="hidden" name="uuid" value="<?=$dc_id?>">
		<input type="submit" value="PDF저장" style="margin-left:1200px;cursor:pointer;">
	</form>	
</div>
<?php }?>
  <div class="render-eform-body wrap" id="content">
    <?php
    if($is_gicho) {
      include_once('./document/thk101_new.php');
      include_once('./document/thk102_new.php');
    }
    include_once('./document/thk001_1_new.php');
    include_once('./document/thk001_2_new.php');
    include_once('./document/thk002_new.php');
    $is_render = "Y";
    include_once('./document/thk003_new.php');
    ?>
  </div>
  <script>
  $(function() {
    if ( self !== top ) {
		$("#pdf_save_bt").css("display","none");
	}
	
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


</body>
</html>
