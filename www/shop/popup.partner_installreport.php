<?php
include_once("./_common.php");

if(!$is_samhwa_partner) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$od_id = get_search_string($_GET['od_id']);
if(!$od_id) {
  alert('정상적인 접근이 아닙니다.');
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
    #txt_issue { width: 100%; margin-top: 15px; border: 1px solid #d7d7d7; border-radius: 3px; padding: 6px; }

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

  <form id="form_partner_installreport">
    <table id="table_ir">
      <colgroup>
        <col style="width: 150px;">
        <col>
      </colgroup>
      <tbody>
        <tr>
          <th><div class="section_head">설치 확인서 등록</div></th>
          <td>
            <label for="file_cert" class="label_file">
              파일찾기
              <input type="file" name="file_cert" id="file_cert" class="ipt_file">
            </label>
            <ul class="list_file">
              <li>
                abc.jpg
                <button class="btn_remove">
                  <i class="fa fa-times" aria-hidden="true"></i>
                </button>
              </li>
            </ul>
          </td>
        </tr>
        <tr>
          <th><div class="section_head">설치 사진 등록</div></th>
          <td>
            <label for="file_photo" class="label_file">
              파일찾기
              <input type="file" name="file_photo" id="file_photo" class="ipt_file">
            </label>
            <ul class="list_file">
              <li>
                abc.jpg
                <button class="btn_remove">
                  <i class="fa fa-times" aria-hidden="true"></i>
                </button>
              </li>
              <li>
                abc.jpg
                <button class="btn_remove">
                  <i class="fa fa-times" aria-hidden="true"></i>
                </button>
              </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="issue_wrap">
      <div class="section_head">이슈사항 작성</div>
      <textarea name="" id="txt_issue" rows="7"></textarea>
    </div>
  </form>

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

      $('#form_partner_installreport').on('submit', function(e) {
        e.preventDefault();
      });
		});
	</script>
</body>
</html>
