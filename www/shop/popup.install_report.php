<?php
include_once("./_common.php");

if(!$is_member) {
  alert("먼저 로그인하세요.");
}

$od_id = get_search_string($_GET['od_id']);
if(!$od_id) {
  alert('정상적인 접근이 아닙니다.');
}
$check_result = sql_fetch("
  SELECT ct_id FROM {$g5['g5_shop_cart_table']}
  WHERE od_id = '{$od_id}' and mb_id = '{$member['mb_id']}'
  LIMIT 1
");
if(!$check_result['ct_id'])
  alert('존재하지 않는 주문입니다.');

# 설치결과보고서
$reports = [];
$report_result = sql_query("
    SELECT * FROM partner_install_report
    WHERE od_id = '$od_id'
");
while($report = sql_fetch_array($report_result)) {

  $report_mb = get_member($report['mb_id']);
  $report['member'] = $report_mb;

  $report['issue'] = [];
  if($report['ir_is_issue_1'])
    $report['issue'][] = '상품변경';
  if($report['ir_is_issue_2'])
    $report['issue'][] = '상품추가';
  if($report['ir_is_issue_3'])
    $report['issue'][] = '미설치';

  // 설치사진 가져오기
  $photo_result1 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '설치사진'
    ORDER BY ip_id ASC
  ");
  $report['photo'] = [];
  while($photo = sql_fetch_array($photo_result1)) {
    $report['photo'][] = $photo;
  }

  // 실물바코드사진(필수) 가져오기
  $photo_result2 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '실물바코드사진'
    ORDER BY ip_id ASC
  ");
  $report['photo2'] = [];
  while($photo = sql_fetch_array($photo_result2)) {
    $report['photo2'][] = $photo;
  }

  // 설치ㆍ회수ㆍ소독확인서(필수) 가져오기
  $photo_result3 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '설치ㆍ회수ㆍ소독확인서'
    ORDER BY ip_id ASC
  ");
  $report['photo3'] = [];
  while($photo = sql_fetch_array($photo_result3)) {
    $report['photo3'][] = $photo;
  }

  // 추가사진(선택) 가져오기
  $photo_result4 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '추가사진'
    ORDER BY ip_id ASC
  ");
  $report['photo4'] = [];
  while($photo = sql_fetch_array($photo_result4)) {
    $report['photo4'][] = $photo;
  }

  $reports[] = $report;
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>설치결과</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="shortcut icon" href="<?php echo THEMA_URL; ?>/assets/img/top_logo_icon.ico">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/magnific-popup.css">
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <script src="<?php echo G5_JS_URL ?>/jquery.magnific-popup.js"></script>
  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    position: relative;
  }

  html,
  body {
    width: 100%;
    min-width: 100%;
    float: left;
    margin: 0 !important;
    padding: 0;
    font-family: "Noto Sans KR", sans-serif;
    font-size: 13px;
  }

  body {
    padding-top: 60px;
  }

  #popupHeaderTopWrap {
    position: fixed;
    width: 100%;
    height: 60px;
    left: 0;
    top: 0;
    z-index: 10;
    background-color: #333;
    padding: 0 20px;
  }

  #popupHeaderTopWrap>div {
    height: 100%;
    line-height: 60px;
  }

  #popupHeaderTopWrap>.title {
    float: left;
    font-weight: bold;
    color: #FFF;
    font-size: 22px;
  }

  #popupHeaderTopWrap>.close {
    float: right;
  }

  #popupHeaderTopWrap>.close>a {
    color: #FFF;
    font-size: 40px;
    top: -2px;
  }

  .install-report {
    border-top: none;
    padding: 18px;
    background: #fff;
    margin-bottom: 18px;
  }

  .install-report .row {
    margin-left: 0;
    margin-right: 0;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
  }

  .install-report .row:before,
  .install-report .row:after {
    display: none;
    content: none;
  }

  .install-report .top-wrap {
    -webkit-box-lines: multiple;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
  }

  .install-report .top-wrap>span {
    font-size: 16px;
  }

  .install-report .mid-wrap {
    text-align: center;
    padding: 20px 0;
    margin-top: -18px;
    border-bottom: 1px solid #ddd;
  }

  .install-report .mid-wrap .btn_ir_download {
    display: inline-block;
    border-radius: 3px;
    margin: 5px;
    padding: 10px 20px;
    text-align: center;
    font-weight: bold;
    background: #fff;
    border: 1px solid #999;
    color: #333;
  }

  .install-report .mid-wrap .issue {
    color: #ee8102;
  }

  .install-report .row.no-gutter {
    margin: 0
  }

  .install-report .report-img-wrap {
    -webkit-box-lines: multiple;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin: 10px 0px;
    border: 1px solid #d7d7d7;
    padding: 4px;
    width: 100%;
  }

  .install-report .report-img-wrap .col {
    padding: 5px;
  }

  .install-report .report-img {
    width: 100px;
    min-width: 100px;
    max-width: 100px;
    height: 100px;
    border: 1px solid #ddd;
  }

  .install-report img {
    width: 100%;
    height: 100%;
  }

  .install-report .issue-wrap {
    width: 100%;
    margin: 10px 0px;
    border: 1px solid #d7d7d7;
    padding: 4px;
    min-height: 120px;
  }

  .install-report .report-title-wrap {
    font-size: 14px;
    font-weight: bold;
    margin-top: 10px;
    height: 24px;
    display: flex;
    align-items: center;
  }
  </style>
</head>

<body>
  <div id="popupHeaderTopWrap">
    <div class="title">설치결과</div>
    <div class="close">
      <a href="#" id="popupCloseBtn">
        &times;
      </a>
    </div>
  </div>

  <?php foreach($reports as $report) { ?>
  <div class="install-report">
    <?php if($report) { ?>
    <div class="mid-wrap">
      <?php if($report['ir_file_url']) { ?>
      <a href="<?=G5_SHOP_URL."/eform/install_report_download.php?od_id={$od_id}"?>" class="btn_ir_download">결과보고서
        다운로드</a>
      <?php } ?>
      <?php if($report['issue']) { ?>
      <div class="issue">
        이슈사항 (<?php echo implode(', ', $report['issue']); ?>)
      </div>
      <?php } ?>
    </div>
    <?php if($report['ir_cert_url']) { ?>
    <div class="row report-img-wrap">
      <div class="col">
        <div class="report-img">
          <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$report['ir_cert_url']}"))?>"
            target="_blank" class="view_image">
            <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
              onerror="this.src='/shop/img/no_image.gif';">
          </a>
        </div>
      </div>
    </div>
    <?php } ?>

    <?php if ($report['photo']) { ?>
    <div class="row report-title-wrap">
      설치 사진 등록
    </div>
    <div class="row report-img-wrap">
      <?php foreach($report['photo'] as $photo) { ?>
      <div class="col">
        <div class="report-img">
          <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$photo['ip_photo_url']}"))?>"
            target="_blank" class="view_image">
            <img src="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>"
              onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
          </a>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>

    <?php if ($report['photo2']) { ?>
    <div class="row report-title-wrap">
      실물바코드사진
    </div>
    <div class="row report-img-wrap">
      <?php foreach($report['photo2'] as $photo) { ?>
      <div class="col">
        <div class="report-img">
          <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$photo['ip_photo_url']}"))?>"
            target="_blank" class="view_image">
            <img src="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>"
              onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
          </a>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>

    <?php if ($report['photo3']) { ?>
    <div class="row report-title-wrap">
      설치ㆍ회수ㆍ소독확인서
    </div>
    <div class="row report-img-wrap">
      <?php foreach($report['photo3'] as $photo) { ?>
      <div class="col">
        <div class="report-img">
          <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$photo['ip_photo_url']}"))?>"
            target="_blank" class="view_image">
            <img src="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>"
              onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
          </a>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>

    <?php if ($report['photo4']) { ?>
    <div class="row report-title-wrap">
      추가사진(선택) 상품변경 혹은 특이사항 발생 시
    </div>
    <div class="row report-img-wrap">
      <?php foreach($report['photo4'] as $photo) { ?>
      <div class="col">
        <div class="report-img">
          <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$photo['ip_photo_url']}"))?>"
            target="_blank" class="view_image">
            <img src="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>"
              onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
          </a>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>

    <?php if ($report['ir_issue']) { ?>
    <div class="row report-title-wrap">
      이슈사항
    </div>
    <div class="row issue-wrap">
      <p class="issue">
        <?=nl2br($report['ir_issue'])?>
      </p>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  </div>
  <?php } ?>

  <script type="text/javascript">
  // 팝업 닫기
  function closePopup() {
    $("body", parent.document).removeClass('modal-open');
    $("#popup_box", parent.document).hide();
    $("#popup_box", parent.document).find("iframe").remove();
  }
  $(function() {
    $("#popupCloseBtn").click(function(e) {
      e.preventDefault();

      closePopup();
    });
  });
  </script>
</body>

</html>