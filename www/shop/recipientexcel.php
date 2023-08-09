<?php
$sub_menu = '400300';
include_once('../common.php');
?>
<link rel="stylesheet" href="<?=G5_URL;?>/skin/admin/new/css/admin.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<style>
	.excelBtn { background-color: #333; color: #FFF; font-weight: bold; padding: 5px 15px; display: inline-block; }
</style>

<div class="new_win">
    <h1>수급자일괄등록</h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 수급자를 일괄등록할 수 있습니다.<br>
            형식은 <strong>수급자 일괄등록 엑셀파일</strong>을 다운로드하여 수급자 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 수급자가 일괄등록됩니다.
        </p>

        <p>
            <!--<a href="<?php echo G5_SHOP_URL; ?>/recipientExcel_new.xlsx" class="excelBtn">수급자등록용 엑셀파일 다운로드</a>-->
            <a href="<?php echo G5_SHOP_URL; ?>/수급자일괄등록 샘플양식.xlsx" class="excelBtn">수급자등록용 엑셀파일 다운로드</a>
        </p>
    </div>

    <form name="fitemexcel" id="fitemexcel" method="post" action="./recipientexcelupdate_new.php" enctype="MULTIPART/FORM-DATA" autocomplete="off" onsubmit="chk_file();">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="win_btn btn_confirm">
        <input type="button" value="수급자 엑셀파일 등록" class="btn_submit btn"  onClick="chk_file();">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

    </form>

</div>
<script>
	function chk_file(){
		var fileVal = $("#excelfile").val();

		if( fileVal != "" ){
			var ext = fileVal.split('.').pop().toLowerCase(); //확장자분리
			//아래 확장자가 있는지 체크
			if($.inArray(ext, ['xlsx']) == -1){
			  alert('xlsx 파일만 업로드 할수 있습니다.');
			  return false;
			}
		}else{
			alert('업로드할 파일을 선택해 주세요.');
			return false;
		}
		document.fitemexcel.submit();
	}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>
