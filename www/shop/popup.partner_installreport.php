<?php
include_once("./_common.php");

if(!$is_samhwa_partner && !$is_admin) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$ct_id = get_search_string($_GET['ct_id']);
if(!$ct_id) {
  alert('정상적인 접근이 아닙니다.');
}
if (!$is_admin) {
  $check_member = "and ct_direct_delivery_partner = '{$member['mb_id']}'";
}
$check_result = sql_fetch("
  SELECT ct_id, mb_id, ct_direct_delivery_partner, od_id, it_id FROM {$g5['g5_shop_cart_table']}
  WHERE ct_id = '{$ct_id}' {$check_member}
  LIMIT 1
");
// if(!$check_result['ct_id'])
//   alert('존재하지 않는 주문입니다.');

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE ct_id = '{$ct_id}'
");

$photos = [];
if($report && $report['ct_id']) {
  // 이미 작성된 설치결과보고서가 있다면

  // 설치사진 가져오기
  $photo_result = sql_query("
    SELECT * FROM partner_install_photo
    WHERE ct_id = '{$ct_id}'
    ORDER BY ip_id ASC
  ");
  while($row = sql_fetch_array($photo_result)) {
    $photos[] = $row;
  }
} else {
  // 설치결과보고서 INSERT
  $insert_result = sql_query("
    INSERT INTO partner_install_report
    SET
      od_id = '{$check_result['od_id']}',
      it_id = '{$check_result['it_id']}',
      ct_id = '{$ct_id}',
      mb_id = '{$check_result['ct_direct_delivery_partner']}',
      ir_issue = '',
      ir_created_at = NOW(),
      ir_updated_at = NOW()
  ");
  $report = array(
    'ir_cert_name' => '',
    'ir_cert_url' => ''
  );
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>설치결과등록</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="shortcut icon" href="<?php echo THEMA_URL; ?>/assets/img/top_logo_icon.ico">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <script src="<?php echo G5_JS_URL ?>/common.js?v=1"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; position: relative; }
    html, body { width: 100%; min-width: 100%; float: left; margin: 0 !important; padding: 0; font-family: "Noto Sans KR", sans-serif; font-size: 13px; }
    body { padding: 60px 0; }

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

    #popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }

    input[type="checkbox"], label {
      vertical-align: middle;
    }
    label {
      margin-right: 10px;
    }
  </style>
</head>
<body>
  <div id="popupHeaderTopWrap">
    <div class="title">설치결과등록 <?php echo !$check_result['ct_id'] ? '(미매칭)' : ''; ?></div>
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
      <tr class="tr_content">
        <th>
          <div class="section_head" style="padding-top:15px;">이슈사항</div>
        </th>
        <td colspan="2">
          <form id="form_partner_installreport2">
            <div style="padding: 15px 10px 0 10px;">
              <input type="checkbox" name="ir_is_issue_1" <?php echo $report['ir_is_issue_1']?'checked':''; ?> value="1" id="ir_is_issue_1">
              <label for="ir_is_issue_1">상품변경</label>
              <input type="checkbox" name="ir_is_issue_2" <?php echo $report['ir_is_issue_2']?'checked':''; ?> value="1" id="ir_is_issue_2">
              <label for="ir_is_issue_2">상품추가</label>
              <input type="checkbox" name="ir_is_issue_3" <?php echo $report['ir_is_issue_3']?'checked':''; ?> value="1" id="ir_is_issue_3">
              <label for="ir_is_issue_3">미설치</label>
            </div>
          </form>
        </td>
      </tr>
      <tr class="tr_head">
        <th><div class="section_head">설치 확인서 등록</div></th>
        <td>
          <form id="form_file_cert">
            <input type="hidden" name="type" value="cert">
            <input type="hidden" name="ct_id" value="<?=$ct_id?>">
            <input type="hidden" name="m" value="u">
            <label for="file_cert" class="label_file">
              파일찾기
              <input type="file" name="file_cert" id="file_cert" class="ipt_file" accept="image/*">
            </label>
          </form>
        </td>
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
              <button class="btn_remove" data-type="cert">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>
      <tr class="tr_head">
        <th><div class="section_head">설치 사진 등록</div></th>
        <td>
          <form id="form_file_photo">
            <input type="hidden" name="type" value="photo">
            <input type="hidden" name="ct_id" value="<?=$ct_id?>">
            <input type="hidden" name="m" value="u">
            <label for="file_photo" class="label_file">
              파일찾기
              <input type="file" name="file_photo[]" id="file_photo" class="ipt_file" accept="image/*" multiple>
            </label>
          </form>
        </td>
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
              <button class="btn_remove" data-type="photo" data-id="<?=$photo['ip_id']?>">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>
    </tbody>
  </table>
  <form id="form_partner_installreport">
    <input type="hidden" name="ct_id" value="<?=$ct_id?>">
    <div class="issue_wrap">
      <div class="section_head">이슈사항 작성</div>
      <textarea name="ir_issue" id="txt_issue" rows="7"><?=$report['ir_issue']?></textarea>
    </div>
  </form>

  <div id="popupFooterBtnWrap">
    <button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button type="button" class="cancelbtn" onclick="closePopup();">취소</button>
  </div>

  <script type="text/javascript">
    // 팝업 닫기
    function closePopup() {
      try {
        $('#hd', parent.document).css('z-index', 10);
      } catch (e) {}
      $("body", parent.document).removeClass('modal-open');
      $("#popup_box", parent.document).hide();
      $("#popup_box", parent.document).find("iframe").remove();
    }
    $(function() {
      $("#popupCloseBtn").click(function(e) {
        e.preventDefault();

        closePopup();
      });

      $('#prodBarNumSaveBtn').click(function() {
        $('#form_partner_installreport').submit();
      });

      // 설치 결과 등록
      $('#form_partner_installreport').on('submit', function(e) {
        e.preventDefault();

        var data = $.extend(
          $('#form_partner_installreport2').serializeObject(), 
          $(this).serializeObject(),
        );

        $.post('ajax.partner_installreport.php', data, 'json')
        .done(function() {
          alert('저장이 완료되었습니다.');
          parent.window.location.reload();
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        });
      });

      // 설치 확인서 업로드
      $('#file_cert').on('change', function() {
        if($(this).val()) {
          $('#form_file_cert').submit();
        }
      });
      $('#form_file_cert').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
          url: 'ajax.partner_installphoto.php',
          type: 'POST',
          data: new FormData(this),
          cache: false,
          processData: false,
          contentType: false,
          dataType: 'json'
        })
        .done(function(result) {
          var photo = result.data;
          $('#list_file_cert').html('\
            <li>\
              <a href="/bbs/view_image.php?open_safari=1&fn=' + encodeURIComponent('/data/partner/img/' + photo['url']) + '" target="_blank" class="view_image">\
                <img src="' + ('/data/partner/img/' + photo['url']) + '" onerror="this.src=\'/shop/img/no_image.gif\';">\
              </a>\
              ' + photo.name + '\
              <button class="btn_remove" data-type="cert">\
                <i class="fa fa-times" aria-hidden="true"></i>\
              </button>\
            </li>\
          ');
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        })
        .always(function() {
          $('#file_cert').val('');
        });
      });

      // 설치 사진 업로드
      $('#file_photo').on('change', function() {
        if($(this).val()) {
          $('#form_file_photo').submit();
        }
      });
      $('#form_file_photo').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
          url: 'ajax.partner_installphoto.php',
          type: 'POST',
          data: new FormData(this),
          cache: false,
          processData: false,
          contentType: false,
          dataType: 'json'
        })
        .done(function(result) {
          var photos = result.data;
          var list_photo_html = '';
          for(var i = 0; i < photos.length; i++) {
            var photo = photos[i];
            list_photo_html += '\
              <li>\
                <a href="/bbs/view_image.php?open_safari=1&fn=' + encodeURIComponent('/data/partner/img/' + photo['ip_photo_url']) + '" target="_blank" class="view_image">\
                  <img src="' + ('/data/partner/img/' + photo['ip_photo_url']) + '" onerror="this.src=\'/shop/img/no_image.gif\';">\
                </a>\
                ' + photo['ip_photo_name'] + '\
                <button class="btn_remove" data-type="photo" data-id="' + photo['ip_id'] + '">\
                  <i class="fa fa-times" aria-hidden="true"></i>\
                </button>\
              </li>\
            ';
            $('#list_file_photo').html(list_photo_html);
          }
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        })
        .always(function() {
          $('#form_file_photo').val('');
        });
      });

      // 삭제버튼
      $(document).on('click', '.btn_remove', function() {
        if(!confirm('정말 파일을 삭제하시겠습니까?')) return;

        var type = $(this).data('type');

        if(type === 'cert') {
          // 설치확인서
          $.post('ajax.partner_installphoto.php', {
            ct_id: '<?=$ct_id?>',
            type: 'cert',
            m: 'd'
          }, 'json')
          .done(function() {
            $('#list_file_cert').empty();
          })
          .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
          });
        }

        else if(type === 'photo') {
          var $li = $(this).closest('li');

          // 설치사진
          var ip_id = $(this).data('id');
          $.post('ajax.partner_installphoto.php', {
            ct_id: '<?=$ct_id?>',
            type: 'photo',
            m: 'd',
            ip_id: ip_id
          }, 'json')
          .done(function(result) {
            $li.remove();
          })
          .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
          });
        }
      });

      $('#form_partner_installreport').on('submit', function(e) {
        e.preventDefault();
      });
    });
  </script>
</body>
</html>
