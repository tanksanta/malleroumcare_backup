<?php
include_once("./_common.php");

$dc_id = get_search_string($_GET['dc_id']);
$preview = get_search_string($_GET['preview']);
$zoom = get_search_string($_GET['zoom']);
if($dc_id) {
  if($preview) {
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
    WHERE dc_id = UNHEX('$dc_id') and entId = '$entId' and dc_status = '10' ");
  if(!$eform['uuid']) {
    die('계약서를 확인할 수 없습니다.');
  }
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

// 기초수급자 체크
$is_gicho = $eform['penTypeCd'] == '04';

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
  <div class="render-eform-body">
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
    }

    repaint();

    <?php if($preview) { ?>
      panzoom(document.body, {
        maxZoom: 1,
        minZoom: 0.4,
        initialZoom: 0.4
      });
    <?php } ?>
  });
  </script>
</body>
</html>
