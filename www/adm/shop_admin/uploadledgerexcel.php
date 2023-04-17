<?php
$sub_menu = '400300';
include_once('../common.php');
?>
<link rel="stylesheet" href="<?=G5_URL?>/skin/admin/new/css/admin.css">

<style>
	.excelBtn { background-color: #333; color: #FFF; font-weight: bold; padding: 5px 15px; display: inline-block; }
</style>

<div class="new_win">
    <h1>수금등록 일괄 업로드</h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 수금등록 일괄 업로드 할 수 있습니다.<br>
            형식은 <strong>수금등록 일괄 업로드 양식</strong>을 다운로드하여 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 수금정보가 일괄등록됩니다.<br>
            엑셀파일을 저장하실 때는 <strong>Excel 97 - 2003 통합문서 (*.xls)</strong> 로 저장하셔야 합니다.
        </p>

        <!-- <p>
            <a href="<?php echo G5_SHOP_URL; ?>/recipientExcel_new.xlsx" class="excelBtn">수급자등록용 엑셀파일 다운로드</a>
        </p> -->
    </div>

    <form name="fitemexcel" method="post" action="./uploadledgerexcelfile.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="win_btn btn_confirm">
        <input type="submit" value="수금등록 일괄 업로드" class="btn_submit btn">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

    </form>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>