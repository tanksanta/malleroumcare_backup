<?php
$sub_menu = '400300';
include_once('./_common.php');
?>
<link rel="stylesheet" href="<?=G5_URL?>/skin/admin/new/css/admin.css">

<style>
	.excelBtn { background-color: #333; color: #FFF; font-weight: bold; padding: 5px 15px; display: inline-block; }
</style>

<div class="new_win">
    <h1>임시회원 일괄등록</h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 임시회원을 일괄등록할 수 있습니다.<br>
            형식은 <strong>임시회원 등록용 엑셀파일</strong>을 다운로드하여 임시회원 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 임시회원가 일괄등록됩니다.
        </p>

        <p>
            <a href="<?php echo G5_ADMIN_URL; ?>/file/temp_member.xlsx" class="excelBtn">임시회원 등록용 엑셀파일 다운로드</a>
        </p>
    </div>

    <form name="fitemexcel" method="post" action="./temp_member_excel_update.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="win_btn btn_confirm">
        <input type="submit" value="임시회원 엑셀파일 등록" class="btn_submit btn">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

    </form>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>