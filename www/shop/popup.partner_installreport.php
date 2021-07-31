<?php
include_once("./_common.php");

if(!$is_samhwa_partner) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$od_id = get_search_string($_GET['od_id']);
if(!$od_id) {
  alert('정상적인 접근이 아닙니다.');
}
$check_result = sql_fetch("
  SELECT od_id FROM {$g5['g5_shop_cart_table']}
  WHERE od_id = '{$od_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
  LIMIT 1
");
if(!$check_result['od_id'])
  alert('존재하지 않는 주문입니다.');

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE od_id = '{$od_id}' and mb_id = '{$member['mb_id']}'
");

$photos = [];
if($report && $report['od_id']) {
  // 이미 작성된 설치결과보고서가 있다면

  // 설치사진 가져오기
  $photo_result = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}' and mb_id = '{$member['mb_id']}'
    ORDER BY ip_id ASC
  ");
  while($row = sql_fetch_array($photo_result)) {
    $photos[] = $row;
  }
} else {
  // 설치결과보고서 INSERT
  $insert_result = sql_query("
    INSERT INTO partner_install_report
    SET od_id = '{$od_id}', mb_id = '{$member['mb_id']}',
    ir_issue = '', ir_created_at = NOW(), ir_updated_at = NOW()
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
    #table_ir tr { border-bottom: 1px solid #ccc; }
    #table_ir th, #table_ir td, .issue_wrap { padding: 15px; text-align: left; vertical-align: top; }
    .section_head { font-weight: 700; padding: 5px 0 0 5px; }
    #txt_issue { width: 100%; margin-top: 15px; border: 1px solid #d7d7d7; border-radius: 3px; padding: 6px; resize: vertical; }

    .list_file { padding-top: 10px; }
    .list_file li { padding: 5px; vertical-align: middle; }
    .btn_remove { margin-left: 10px; width: 25px; height: 25px; background-color: #000; border-radius: 3px; color: #fff; font-size: 15px; }

    #popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }
  </style>
</head>
<body>
  <div id="popupHeaderTopWrap">
    <div class="title">설치결과등록</div>
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
      <tr>
        <th><div class="section_head">설치 확인서 등록</div></th>
        <td>
          <form id="form_file_cert">
            <input type="hidden" name="type" value="cert">
            <input type="hidden" name="od_id" value="<?=$od_id?>">
            <input type="hidden" name="m" value="u">
            <label for="file_cert" class="label_file">
              파일찾기
              <input type="file" name="file_cert" id="file_cert" class="ipt_file">
            </label>
          </form>
          <ul id="list_file_cert" class="list_file">
            <?php if($report['ir_cert_url']) { ?>
            <li>
              <?=$report['ir_cert_name']?>
              <button class="btn_remove" data-type="cert">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>
      <tr>
        <th><div class="section_head">설치 사진 등록</div></th>
        <td>
          <form id="form_file_photo">
            <input type="hidden" name="type" value="photo">
            <input type="hidden" name="od_id" value="<?=$od_id?>">
            <input type="hidden" name="m" value="u">
            <label for="file_photo" class="label_file">
              파일찾기
              <input type="file" name="file_photo[]" id="file_photo" class="ipt_file" multiple>
            </label>
          </form>
          <ul id="list_file_photo" class="list_file">
            <?php foreach($photos as $photo) { ?>
            <li>
              <?=$photo['ip_photo_name'] ?>
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
  <div class="issue_wrap">
    <div class="section_head">이슈사항 작성</div>
    <textarea name="" id="txt_issue" rows="7"></textarea>
  </div>

  <div id="popupFooterBtnWrap">
    <button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button type="button" class="cancelbtn" onclick="closePopup();">취소</button>
  </div>

  <script type="text/javascript">
    // 팝업 닫기
    function closePopup() {
      $("#popupProdDeliveryInfoBox", parent.document).hide();
      $("#popupProdDeliveryInfoBox", parent.document).find("iframe").remove();
    }
    $(function() {
      $("#popupCloseBtn").click(function(e) {
        e.preventDefault();
        
        closePopup();
      });

      $('#prodBarNumSaveBtn').click(function() {
        $('#form_partner_installreport').submit();
      });

      // 설치 확인서 업로드
      $('#file_cert').on('change', function() {
        $('#form_file_cert').submit();
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
          $('#list_file_cert').html('\
            <li>\
              ' + result.data + '\
              <button class="btn_remove" data-type="cert">\
                <i class="fa fa-times" aria-hidden="true"></i>\
              </button>\
            </li>\
          ');
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        });
      });

      // 설치 사진 업로드
      $('#file_photo').on('change', function() {
        $('#form_file_photo').submit();
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
        });
      });

      // 삭제버튼
      $(document).on('click', '.btn_remove', function() {
        if(!confirm('정말 파일을 삭제하시겠습니까?')) return;

        var type = $(this).data('type');

        if(type === 'cert') {
          // 설치확인서
          $.post('ajax.partner_installphoto.php', {
            od_id: '<?=$od_id?>',
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
            od_id: '<?=$od_id?>',
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
