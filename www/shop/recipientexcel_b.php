<?php
$sub_menu = '400300';
include_once('../common.php');
?>
<link rel="stylesheet" href="<?=G5_URL;?>/skin/admin/new/css/admin.css">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>

<style>
	.excelBtn { background-color: #333; color: #FFF; font-weight: bold; padding: 5px 15px; display: inline-block; }
</style>

<div class="new_win">
    <h1>B사 수급자일괄등록</h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 B사 수급자를 일괄등록할 수 있습니다.
        </p>
        <!--
        <p>
            <a href="<?php echo G5_SHOP_URL; ?>/recipientExcel_b.xlsx" class="excelBtn">수급자등록용 엑셀파일 다운로드</a>
        </p>
        -->
    </div>

    <form name="fitemexcel" id="form_upload" method="post" action="./recipientexcelupdate_b.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="win_btn btn_confirm">
        <input type="submit" value="B사 수급자 엑셀파일 등록" class="btn_submit btn">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

    </form>

</div>

<script>
$('#form_upload').on('submit', function(e) {
  e.preventDefault();
  var fd = new FormData(document.getElementById("form_upload"));
  window.opener.excelPost($(this).attr('action'), fd);
  window.close();
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>