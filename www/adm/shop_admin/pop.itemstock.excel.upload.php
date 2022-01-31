<?php
// $sub_menu = '400620';
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

// auth_check($auth[$sub_menu], "w");

$title = '재고 엑셀 일괄 업로드';
include_once('./pop.head.php');
?>

<style>
  #item_stock_excel_upload_form {
    font-size: 15px;
    padding-top: 10px;
  }

  #item_stock_excel_upload_form .title {
    width: 120px;
    font-weight: bold;
  }

  #item_stock_excel_upload_form .content {
    width: calc(100% - 120px);
  }

  #item_stock_excel_upload_form ul {
    list-style: none;
    padding: 0;
  }

  #item_stock_excel_upload_form li {
    /*border-bottom: 1px solid #ececec;*/
    padding: 8px 0;
    height: 41px;
  }

  #item_stock_excel_upload_form li,
  #item_stock_excel_upload_form .content {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
  }

  #item_stock_excel_upload_form .noti {
    margin-top: 50px;
  }

  #item_stock_excel_upload_form .noti .title {
    font-weight: bold;
    color: #FF5C01;
  }

  #item_stock_excel_upload_form .noti * {
    margin: 0;
    padding: 0;
  }

  #item_stock_excel_upload_form .noti li {
    height: 30px;
  }

  #item_stock_excel_upload_form .noti li {
    list-style: ;
  }

</style>

<div style="padding-bottom: 60px">
  <div id="pop_order_add" class="admin_popup admin_popup_padding">
    <h4 class="h4_header"><?php echo $title; ?></h4>

    <form id="item_stock_excel_upload_form" class="flex-wrap" enctype='multipart/form-data'>
      <ul>
        <li>
          <div class="title">내용</div>
          <div class="content">
            <input type="text" name="ws_memo" style="width: 100%;">
          </div>
        </li>
        <li>
          <div class="title">파일업로드</div>
          <div class="content">
            <input type="file" name="datafile" id="datafile">
          </div>
        </li>
      </ul>

      <div class="noti">
        <p class="title">* 알림</p>
        <ul>
          <li>- 업로드 파일은 보유한 재고 '엑셀다운로드' 후 숫자만 수정 후 등록해주세요.</li>
          <li>- 수량이 수정 되면 내용으로 입력한 정보로 자동으로 + / - 기록됩니다.</li>
          <li>- 한번 기록된 내용은 취소가 불가능합니다.</li>
        </ul>
      </div>
    </form>

    <div id="popup_buttom">
      <div class="addoptionbuttons">
        <a href='#' class="closeBtn">
          취소
        </a>
        <input type="button" class="submitInput" onclick="submitForm(event)" value="확인" />
      </div>
    </div>
  </div>
</div>

<script>
var LOADING = false;


$(function () {
  $(document).on("click", ".closeBtn", function (e) {
    e.preventDefault();

    $('#popup_excel_upload', parent.document).hide();
    $('#hd', parent.document).css('z-index', 10);
  });
})

function submitForm(e) {
  e.preventDefault();

  if (LOADING == true) {
    return;
  }

  LOADING = true;

  var formData = new FormData(document.getElementById("item_stock_excel_upload_form"));

  $.ajax({
    url: 'ajax.itemstock.excel.upload.php',
    type: 'POST',
    data: formData,
    cache: false,
    processData: false,
    contentType: false,
    dataType: 'json',
    async: true,
  })
  .done(function() {
    alert('업로드가 완료되었습니다.');
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
  .always(function() {
    LOADING = false;
  });
}
</script>

</body>
</html>