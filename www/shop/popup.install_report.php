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

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE od_id = '{$od_id}'
");

$photos = [];
if($report && $report['od_id']) {
  // 이미 작성된 설치결과보고서가 있다면

  // 설치사진 가져오기
  $photo_result = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    ORDER BY ip_id ASC
  ");
  while($row = sql_fetch_array($photo_result)) {
    $photos[] = $row;
  }
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
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; position: relative; }
    html, body { width: 100%; min-width: 100%; float: left; margin: 0 !important; padding: 0; font-family: "Noto Sans KR", sans-serif; font-size: 13px; }
    body { padding-top: 60px; }

    #popupHeaderTopWrap { position: fixed; width: 100%; height: 60px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
    #popupHeaderTopWrap > div { height: 100%; line-height: 60px; }
    #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
    #popupHeaderTopWrap > .close { float: right; }
    #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

    #table_ir { width: 100%; }
    #table_ir .ipt_file { display: none; }
    #table_ir .label_file { display: inline-block; border: 1px solid #d7d7d7; color: #666; border-radius: 3px; padding: 10px 30px; cursor: pointer; }
    #table_ir .tr_content { border-bottom: 1px solid #ccc; }
    #table_ir .tr_head { border: none; }
    #table_ir th, #table_ir td, .issue_wrap { padding: 15px; text-align: left; vertical-align: top; }
    #table_ir .tr_head th, #table_ir .tr_head td { padding-bottom: 0; }
    #table_ir .tr_content th, #table_ir .tr_content td { padding-top: 0; }
    .section_head { font-weight: 700; padding: 5px 0 0 5px; }
    #txt_issue { width: 100%; margin-top: 15px; border: 1px solid #d7d7d7; border-radius: 3px; padding: 6px; resize: vertical; }

    .list_file { padding-top: 10px; }
    .list_file li { padding: 5px; vertical-align: middle; }
    .list_file li a.view_image { display: block; width: 100px; min-width: 100px; max-width: 100px; height: 100px; border: 1px solid #ddd; margin-bottom: 3px; }
    .list_file li a.view_image img { width: 100%; height: 100%; }
    .btn_remove { margin-left: 10px; width: 25px; height: 25px; background-color: #000; border-radius: 3px; color: #fff; font-size: 15px; }

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

  <table id="table_ir">
    <colgroup>
      <col style="width: 150px;">
      <col>
    </colgroup>
    <tbody>
      <tr class="tr_head">
        <th colspan="2"><div class="section_head">설치 확인서</div></th>
      </tr>
      <tr class="tr_content">
        <td colspan="2">
          <ul id="list_file_cert" class="list_file">
            <?php if($report['ir_cert_url']) { ?>
            <li>
              <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$report['ir_cert_url']}"))?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" onerror="this.src='/shop/img/no_image.gif';">
              </a>
              <?=$report['ir_cert_name']?>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>
      <tr class="tr_head">
        <th colspan="2"><div class="section_head">설치 사진</div></th>
      </tr>
      <tr class="tr_content">
        <td colspan="2">
          <ul id="list_file_photo" class="list_file">
            <?php foreach($photos as $photo) { ?>
            <li>
              <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$photo['ip_photo_url']}"))?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" onerror="this.src='/shop/img/no_image.gif';">
              </a>
              <?=$photo['ip_photo_name']?>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>
    </tbody>
  </table>
  <div class="issue_wrap">
    <div class="section_head">이슈사항</div>
    <textarea name="ir_issue" id="txt_issue" rows="7" readonly><?=$report['ir_issue']?></textarea>
  </div>

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
