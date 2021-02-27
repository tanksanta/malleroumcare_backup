<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '엑셀파일로 배송정보 일괄 업로드';
include_once(G5_PATH.'/head.sub.php');
?>

<style>
	.excelBtn { background-color: #333; color: #FFF; font-weight: bold; padding: 5px 15px; display: inline-block; }
</style>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 배송 정보를 일괄업로드할 수 있습니다.<br>
            형식은 <strong>배송정보일괄업로드용 엑셀파일</strong>을 다운로드하여 상배송품 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 배송정보가 일괄업로드됩니다.<br>
            엑셀파일을 저장하실 때는 <strong>Excel 97 - 2003 통합문서 (*.xls)</strong> 로 저장하셔야 합니다.
        </p>

<!--
        <p>
            <a href="./deliveryexcelform.php" class="excelBtn">배송정보일괄업로드용 엑셀파일 다운로드</a>
        </p>
-->
    </div>

    <form name="fitemexcel" method="post" action="./deliveryexcelupdate.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="win_btn btn_confirm">
        <input type="submit" value="배송정보 엑셀파일 업로드" class="btn_submit btn">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

    </form>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>