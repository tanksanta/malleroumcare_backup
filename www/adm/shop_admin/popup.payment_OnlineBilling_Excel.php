<?php
    /* // */
    /* // */
    /* // */
    /* // */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* //  *  */
    /* //  *  */
    /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! = OnlineBilling Ver:0.1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2022 THKC Co,Ltd.  All rights reserved. */
    /* //  *  */
    /* //  *  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // */
    /* // */
    /* // */
    /* // */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 : /www/adm/shop_admin/popup.payment_OnlineBilling_Excel.php */
    /* // 파일 설명 :   온라인 결제(관리자화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  대금 청구서 엑셀 파일 업로드를 위한 업로드 파일 경로 확인 페이지 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500150';
include_once("./_common.php");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>대금 청구 엑셀파일 업로드</title>
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_reset.css">
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_style.css">
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?=G5_URL?>/skin/admin/new/css/admin.css">
  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="<?=G5_JS_URL;?>/jquery.fileDownload.js"></script>



<style> 
	.excelBtn { background-color: #333; color: #FFF; font-weight: bold; padding: 5px 15px; display: inline-block; }
    #excelfile_upload p { font-weight: bold; }
    #excelfile { width: 100%; }

    /* 로딩 팝업 */
    #loading_excel { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
    #loading_excel .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    #loading_excel .loading_modal p { padding: 0; font-size: 16px; }
    #loading_excel .loading_modal img { display: block; margin: 20px auto; }
    #loading_excel .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }    
</style>

<script>
    function execl_submit_function()
    {
        $('#loading_excel').show();
        
        var form = document.getElementById("form_fitemexcel");

        form.method = "post";
        form.action = "/adm/shop_admin/popup.payment_OnlineBilling_ExcelUpload.php";
        form.enctype = "MULTIPART/FORM-DATA";

        form.submit();
        
        return true;
    }
</script>

<div class="new_win">
    <h1>온라인 결제용 대금 청구서 업로드</h1>

    <div class="local_desc01 local_desc">
        <p style="font-weight: bold; font-size: 16px; color: navy;";>## 주의사항 ##</p>
        <li>* 청구 일자가 <u>[<?=date("m", mktime(0, 0, 0, date("m")-1, 1))?>월]</u>이 아닌 데이터는 청구 업로드가 불가능 합니다.</li>
        <li>* EROUMCARE에 가입되어 있는 사업소만 청구 등록이 가능 합니다.</li>
        <li>* 청구용 엑셀파일은 동일한 파일명으로 1회만 업로드 가능 합니다.</li>
        <li>* 대금 결제 청구는 당월 기준 사업소당 1개의 청구만 가능 합니다. (동시 여러개 불가!!)</li>
        <li>* 기존 청구건을 변경 하고자하는 경우 기존 청구내역에서 '청구취소' 처리 후 가능합니다.</li>
        <li>* 수수료는 '수수료설정'에 입력된 값을 기본으로 파일 업로드시 일괄 적용 됩니다.</li>
        <li>* 소수점 자리 수수료의 경우 무조건 올림 처리되어 정산 됩니다.</li>
        <li>* 결제금액이 '0원'이하의 경우 등록되더라도 시스템에서 자동취소 처리 됩니다.</li>
    </div>

    <form id="form_fitemexcel" name="fitemexcel" autocomplete="off" onsubmit="return false;">

    <div id="excelfile_upload">
        <p for="excelfile">업로드용 엑셀 파일을 선택하세요.</p>
        <input type="file" name="excelfile" id="excelfile" accept=".xlsx">
    </div>

    <div class="win_btn btn_confirm">
        <input type="submit" value="<?=date("m", mktime(0, 0, 0, date("m")-1, 1));?>월 대금 청구서 엑셀파일 등록" class="btn_submit btn" Onclick="execl_submit_function();">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

    </form>

</div>

<!-- 로딩 -->
<div id="loading_excel" style="display: none;">
<div class="loading_modal">
    <p>엑셀파일 업로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
</div>
</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>