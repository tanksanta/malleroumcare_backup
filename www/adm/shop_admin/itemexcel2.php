<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '엑셀파일로 상품 일괄 수정';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>
            상품코드(구 더존코드)를 기반으로 엑셀일괄 수정합니다<br/>
            엑셀파일은 <strong>Excel 97 - 2003 통합문서 (*.xls)</strong> 로 확장자가 정해져있어야 합니다.
        </p>
    </div>

    <form name="fitemexcel" method="post" action="./itemexcelupdate2.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="win_btn btn_confirm">
        <input type="submit" value="상품 엑셀파일 수정" class="btn_submit btn">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

    </form>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>