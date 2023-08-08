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
    /* //  * Program Name : EROUMCARE Platform! = Renewal Ver:1.0 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
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
    /* // 파일 작성 일자 : 23.08.02 */
    /* // 파일 작성자 : 박서원 */
    /* // 파일명 : /www/shop/forms_eroumon_matchingservice.php */
    /* // 파일 설명 : 해당 파일은 기존 이로움에 있는 사업소에 대하여 수급자 매칭 여부를 확인하고자 하는 설문지 폼 양식. */
    /* //             본 파일에서 수집되는 정보는 1회성(?) 데이터이며, 설문지에 대한 데이터는 중요하지 않지만, 강제 하고 싶다는 마케팅의 요청에 의해 개발된 파일. */
    /* //             해당 파일에서 수집되는 데이터는 데이터베이스(DB)를 사용하지 않으며, XLSX(엑셀)파일을 저장소로 사용함. */
    
    /* // 최종 수정 일자 : 23.08.02 */
    /* // 최종 수정 내용 : 설문및 매칭 서비스 신청 확인 UI를 위해 파일 생성. */


    /* 
        본 파일은 1회선 데이터 수집을 위한 파일로써~ 이미지는 바이너리 값으로, 아이콘은 svg 코드 값으로 변환하여 1개의 파일에서 모두 관리되도록 처리 합니다.
        또한 별도의 js파일 및 css파일을 추가 하지 않으며, 본 파일 내부에서 처리가 되도록 구성 합니다.
    */
    

    include_once('./_common.php');
    include_once("./_head.php");
    include_once(G5_LIB_PATH.'/PHPExcel.php');

    if(!$member['mb_id'])
        alert('먼저 로그인하세요.',G5_URL.'/bbs/login.php');

    // 폴더 존재 확인
    $_dir_path = G5_DATA_PATH . '/eroumon_matchingservice/';
    if (!is_dir($_dir_path)) { mkdir($_dir_path, 0777, true); }

    // 파일 경로
    $_fileName = 'eroumon_matchingservice.xlsx';

    // 파일 경로
    $_Old_fileName = 'eroumon_matchingservice_' . date('ymd') . '.xlsx';


    // == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- ==
    $result = "";
    $searchWord = $member['mb_id'];

    if( (file_exists($_dir_path.$_fileName)) && (!file_exists($_dir_path.$_Old_fileName)) ) {
        // 일자별 파일 백업
        copy( $_dir_path.$_fileName , $_dir_path.$_Old_fileName ); // 기존 파일 백업.
    } else {

        if( file_exists($_dir_path.$_fileName) && $searchWord ) {

            // 엑셀 파일 로드
            $excel = PHPExcel_IOFactory::load( $_dir_path.$_fileName );

            // 첫 번째 시트 선택 (인덱스 0부터 시작)
            $sheet = $excel->getSheet(0);

            // 최대 행 번호 구하기
            $highestRow = $sheet->getHighestRow();

            // 엑셀 데이터 검색
            for ($row = 2; $row <= $highestRow; $row++) {
                // 현재 행 데이터 가져오기
                $rowData = $sheet->rangeToArray('A' . $row . ':M' . $row, NULL, TRUE, FALSE)[0];

                // 현재 행 데이터에서 단어 검색
                foreach ($rowData as $cellValue) {
                    if (strpos($cellValue, $searchWord) !== false) {
                        // 검색 결과를 배열에 추가 (현재 행의 데이터 전체를 저장)
                        $result = $rowData;
                        break; // 한 행에서 최초로 검색 단어를 발견하면 다음 행으로 넘어갑니다.
                    }
                }
            }
            
        } else {
            if(!file_exists($_dir_path.$_fileName)) {
                $newHeaders = array('일련번호', '회원ID', '사업소코드', '회원명', '신청서사업자', '신청서대표', '신청서휴대전화', '매칭동의일시', '문항1', '문항2', '문항3', '문항4', '문항5');
            
                // 새로운 PHPExcel 객체 생성
                $excel = new PHPExcel();
                
                // 새로운 시트 선택
                $sheet = $excel->getActiveSheet();
                
                // 새로운 헤더 추가
                $sheet->fromArray(array($newHeaders), NULL, 'A1');
    
                // 엑셀 파일 저장
                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $writer->save($_dir_path.$_fileName);
            }
        }

    }
    // == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- ==


    // 해당 페이지에서 REQUEST 이벤트가 발생하고, POST일 경우 질문지 데이터 업데이트를 위한 코드 동작.
    if( ($_SERVER['REQUEST_URI'] === $_SERVER['PHP_SELF']) && ($_SERVER['REQUEST_METHOD']==="POST") && (!$result && !is_array($result)) ) {

        // 기존 엑셀 파일 로드
        $excel = PHPExcel_IOFactory::load($_dir_path.$_fileName);

        // 기존 시트 선택
        $sheet = $excel->getActiveSheet();

        // 기존 시트 마지막 행 번호 계산
        $lastRow = $sheet->getHighestRow();

        // 기존 데이터의 2번째 행 번호
        $existingRow = 2;

        // 기존 데이터를 한 행씩 뒤로 이동시키기
        for ($row = $lastRow; $row >= $existingRow; $row--) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $sheet->getHighestColumn() . $row, NULL, TRUE, FALSE);
            $sheet->fromArray($rowData, NULL, 'A' . ($row + 1));
        }

        // 새로 추가할 데이터
        $newRows = array(
            array(
                ($lastRow),
                " ".$member['mb_id'],
                " ".$member['mb_giup_bnum']?$member['mb_giup_bnum']:"",
                " ".$member['mb_giup_bname']?$member['mb_giup_bname']:"",
                " ".$_POST['Q_bnum'],
                " ".$_POST['Q_bnm'],
                " ".$_POST['Q_hp'],
                date("Y-m-d H:i:s"),
                $_POST['Q1'],
                $_POST['Q2'],
                $_POST['Q3'],
                $_POST['Q4'],
                $_POST['Q5']        
            )
        );

        // 2번째 행에 새로운 데이터 추가
        $sheet->fromArray($newRows, NULL, 'A' . $existingRow);

        // 엑셀 파일 저장
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save($_dir_path.$_fileName);

    }


    //phpinfo();
?>

<style>
    .f_bold500 { font-weight: 500; }
    .matchingExcel { display: flex; justify-content: end; }
    .matchingExcel a { border-radius: 7px; background: #2FA46C; color: #fff; font-size: 14px; padding: 5px 10px; }
    .matchingTitle { margin: 50px 0 40px 0; }
    .matchingTitle h3 { font-size: 35px; font-weight: 400; text-align: center; }
    .matchingTop { border-radius: 20px; padding: 10px; background: linear-gradient(90deg, #7802E4 0%, #5078DD 100%); display: flex; align-items: center; color: #fff; }
    .matchingTop .matchingImg { width: 55%; padding: 15px 50px; }
    .matchingTop .titleSummary { width: 45%; }
    .matchingTop .titleSummary h2 { width: 100%; padding-bottom: 10px; }
    .matchingTop .titleSummary ul li { font-size: 16px; font-weight: 200; line-height: 160%; display: flex; }
    .matchingTop .titleSummary ul li svg { padding-right: 7px; }
    .matchingTop .titleSummary ul li span { margin-left: 3px; }
    .matchingkeyInput { display: flex; justify-content: center; align-items: center; flex-direction: column; padding: 70px 0; }
    .matchingkeyInput h4 { font-weight: 300; padding-bottom: 5px; padding-left: 30px; }
    .matchingkeyInput ul li { display: flex; justify-content: flex-end; font-size: 20px; padding: 3px 0; }
    .matchingkeyInput ul li p { padding-right: 20px; display: flex; align-items: center; }
    .matchingkeyInput ul li b { color: #5270DD; padding: 0 0 0 5px; font-weight: 400; }
    input.thkc_inputM::placeholder { color: #999 !important; opacity: 0.5; }
    input.thkc_inputM[type="text"], input.thkc_inputM[type="number"], input.thkc_inputM { width: 400px; height: 45px; border: 1px solid #DDDDDD; border-radius: 5px; padding: 0 15px; box-sizing: border-box; vertical-align: top; font-size: 16px; color: #494949; outline: none; }
    .matchingQuest { background: #F9F9F9; padding: 10px 0 50px 0; }
    .matchingQuest .QuestTitleWrap { display: flex; flex-direction: column; justify-content: center; padding: 35px 0; }
    .matchingQuest .QuestTitleWrap p { text-align: center; }
    .matchingQuest .QuestTitleWrap .mQ_title01, .matchingResult .mQ_title01 { font-size: 35px; padding: 20px 0 10px 0; line-height:100%; }
    .matchingQuest .QuestTitleWrap .mQ_title02, .matchingResult .mQ_title02 { font-weight: 700; color: #5173DD; }
    .matchingQuest br, .matchingTitle br { display: none; }
    .matchingQuest .QuestTitleWrap .mQ_title03 { color: #666; font-size: 14px; }
    .QuestBoxWrap { padding: 30px; }
    .QuestBoxWrap p { font-size: 18px; padding: 10px 0; font-weight: 500; }
    .QuestBoxWrap p>span { color: #5270DD; }
    .QuestBoxWrap p>b { font-size: 12px; font-weight: 400; border-radius: 5px; border: 1px solid #7304FF; background: #fff; padding: 2px 5px; }
    .QuestBoxWrap ul { background: #fff; padding: 20px 30px; border-radius: 5px; border: 1px solid #E7E7E7; background: #FFF; box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.10); }
    .QuestBoxWrap .Questdf { display: flex; justify-content: space-between; }
    .QuestBoxWrap ul li { padding: 5px 0; display: flex; align-items: center; }
    .QuestBoxWrap ul li span { padding: 0 0 0 10px; }
    .QuestBoxWrap input[type="radio"], input[type="checkBox"], .matchingAgree input[type="radio"], input[type="checkBox"] { width: 20px; height: 20px; }
    .matchingPolicy { margin: 70px 0; }
    .matchingPolicy>p { text-align: center; padding: 0 0 30px 0; color: #666; }
    .matchingPolicy h1 { font-size: 35px; font-weight: 400; padding: 5px 0; text-align: center; }
    .matchingPolicy .policyWrqp { border-radius: 5px; border: 1px solid #E7E7E7; background: #F7F7F7; padding: 30px 20px; font-size: 14px; }
    .matchingPolicy .policyWrqp .p_text { color: #666; }
    .matchingPolicy .policyWrqp .p_text p { margin: 0; }
    .matchingPolicy .policyWrqp .agreeForm { width: 100%; height: 200px; overflow: auto; padding: 0 20px; }
    .matchingPolicy .matchingAgree { display: flex; justify-content: center; }
    .matchingPolicy .matchingAgree>ul { display: flex; justify-content: space-between; padding: 20px 0 50px 0; width: 30%; }
    .matchingPolicy .matchingAgree ul>li { display: flex; align-items: center; gap: 7px; }
    .matchingPolicy .matchingAgree ul>li>label { margin: 0px; }
    .matchingBtnWrap { display: flex; justify-content: center; padding: 10px 0; }
    .matchingBtnWrap a { display: flex; align-items: center; gap: 20px; border-radius: 50px; background: #694AE4; padding: 20px 70px; color: #FFF; font-size: 24px; font-weight: 500; }
    .matchingBtnWrap a:link, .matchingBtnWrap a:visited {	color: #FFF; }
    .matchingBtnWrap a:hover { background: #7D00E5; }
    .matchingBtnWrap p, .matchingQuest p { margin: 0; }
    .matchingBtnWrap #goHomeButton, .matchingBtnWrap #goBackButton { padding: 15px 70px; }
    .QuestBoxWrap label { margin: 0; }

    .matchingResult .mQ_title01 { font-size: 25px; text-align: center; padding: 20px 0 70px 0; }
    .matchingResult { border-radius: 20px; background: #F9F9F9; padding: 60px 0; display: flex; flex-direction: column; align-items: center; }

    /********** css responsive **********/
    @media (max-width: 1399px) {}
    @media (max-width: 1199px) {}
    @media (max-width: 991px) {}
    @media (max-width: 767px) {
        .matchingTitle { padding: 0px 0 0 0; }
        .matchingTitle h3 { font-size: 30px; }
        .matchingTop { display: flex; flex-direction: column; padding: 30px 0; }
        .matchingTop .matchingImg { width: 100%; padding: none; margin: 0 0 20px 0; }
        .matchingTop .matchingImg img { width: 100%; }
        .matchingTop .titleSummary { width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .matchingTop .titleSummary h2 { font-size: 20px; width: 100%; text-align: center; }
        .matchingTop .titleSummary ul li { font-size: 14px; }
        .matchingkeyInput h4 { font-size: 18px; padding-bottom: 10px; }
        .matchingkeyInput ul { width: 100%; }
        .matchingkeyInput ul>li { display: flex; flex-direction: column; padding: 10px 0 0 0; font-size: 18px; }
        .matchingkeyInput ul>li>div { line-height: 10%; }
        .matchingkeyInput ul>li>input { display: flex; flex-direction: column; }
        .QuestBoxWrap ul li { padding: 5px 20px; }
        .QuestBoxWrap .Questdf { display: flex; flex-direction: column; }
        .QuestBoxWrap { padding: 20px 0px; }
        .matchingQuest { background: #fff; padding: 10px 0 50px 0; }
        .matchingPolicy .matchingAgree { padding: 0 0 0 20px; }
        .matchingPolicy .matchingAgree>ul { display: block; padding: 30px 0 50px 0; width: 100%; }
        .matchingBtnWrap a { gap: 10px; padding: 15px 25px; font-size: 20px; }
        .matchingBtnWrap a img { width: 70%; }
        .matchingPolicy .policyWrqp { background: #F7F7F7; padding: 30px 10px; font-size: 12px; }
        .matchingPolicy .policyWrqp .agreeForm { padding: 0 5px; }
        .matchingTitle br { display: block; }
        .matchingPolicy { margin: 20px 0; }
        input.thkc_inputM[type="text"], input.thkc_inputM[type="number"], input.thkc_inputM { width: 100%; }
    }
    @media (max-width: 600px) { 
        .QuestBoxWrap ul { padding: 20px 0px; }
        .matchingQuest br { display: block; }
        .matchingResult .mQ_title01 { font-size: 20px; letter-spacing: -0.05em; }
    }
    @media (max-width: 360px) {
        .matchingBtnWrap a { font-size: 16px; }
    }
</style>

<script>
$(function() {


    // checkbox 요소 선택
    const checkboxes = document.querySelectorAll('input[name="matchingservice"]');

    // 체크박스 클릭 이벤트 리스너 추가
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function() {
            // 선택된 checkbox의 개수 세기
            const numChecked = document.querySelectorAll('input[name="matchingservice"]:checked').length;

            // 하나 이상의 checkbox가 선택된 경우 다른 checkbox들의 선택 해제
            if (numChecked > 1) { checkboxes.forEach(checkbox => { if (checkbox.checked && checkbox !== this) { checkbox.checked = false; } }); }
        });
    });


    // a 태그 클릭 이벤트 (폼 서브밋)
    $('#submitLink').on('click', function (e) {
        e.preventDefault();

        if( !$("#Q_bnum").val() || !$("#Q_bnm").val() || !$("#Q_hp").val() ) { alert("사업자번호, 대표자성명, 휴대번화번호를 모두 입력하시기 바랍니다."); return; }
        
        if( !$('input[name="Q1"]:checked').val() ) { alert("응답하지 않은 문항이 있습니다. Q1"); return; }
        if( !$('input[name="Q2"]:checked').val() ) { alert("응답하지 않은 문항이 있습니다. Q2"); return; }
        if( !$('input[name="Q4"]:checked').val() ) { alert("응답하지 않은 문항이 있습니다. Q4"); return; }

        // 체크된 체크 박스들의 값을 확인하고, 결과를 표시할 변수 초기화
        var ckQ3_vals = [];
        var ckQ5_vals = [];

        // class가 Q3, Q5인 체크 박스들 선택
        var Q3_Val = $('.Q3');
        var Q5_Val = $('.Q5');

        // 체크 박스들을 순회하면서 체크된 값을 확인
        Q3_Val.each(function () { if ($(this).is(':checked')) { ckQ3_vals.push($(this).val()); } });   
        Q5_Val.each(function () { if ($(this).is(':checked')) { ckQ5_vals.push($(this).val()); } });    

        $("#Q3").val( ckQ3_vals );
        $("#Q5").val( ckQ5_vals );
        
        if( !$('input[name="Q3"]').val() ) { alert("응답하지 않은 문항이 있습니다. Q3"); return; }
        if( !$('input[name="Q5"]').val() ) { alert("응답하지 않은 문항이 있습니다. Q5"); return; }
        if( $('input[name="matchingservice"]:checked').val() !== "yes" ) { alert("서비스 이용약관에 동의하셔야 신청 가능합니다."); return; }

        $('#form_matchingservice').submit();
    });
    
    $('#goBackButton').on('click', function (e) {
        e.preventDefault();
        window.history.back(); // 브라우저 뒤로가기 기능 수행
    });

    $('#goHomeButton').on('click', function (e) {
        e.preventDefault();
        window.location.href = '<?=G5_URL?>'; // 메인화면의 URL로 이동
    });


    // 숫자만 입력!!
    $('.numOnly').on('keyup blur', function() {
        var num = $(this).val();
        num.trim();
        this.value = only_num(num);
    });


});
</script>

    <form class="form" role="form" name="form_matchingservice" id="form_matchingservice" action="<?=$_SERVER['PHP_SELF'];?>" method="post">

        <!-- 예비수급자 & 보호자  매칭 서비스 신청서 start -->                   
        <div class="matchingTitle">
            <?php if( $member['mb_level']>=9 ) { ?>
            <!-- 관리자 페이지에서만 display --><div class="matchingExcel"><a href='/data/eroumon_matchingservice/<?=$_fileName?>?ver=<?=date('hms')?>'>엑셀 다운로드</a></div> 
            <?php } ?>
            <h3>예비수급자 & 보호자 <br><span class="f_bold700">매칭 서비스 신청서</span></h3>
        </div>

        <?php if( ($_SERVER['REQUEST_URI'] === $_SERVER['PHP_SELF']) && ($_SERVER['REQUEST_METHOD']==="POST") ) { ?>

        <div class="matchingResult"> 
            <svg xmlns="http://www.w3.org/2000/svg" width="108" height="108" viewBox="0 0 108 108" fill="none">
                <circle cx="54" cy="54" r="54" fill="#5173DD" fill-opacity="0.1"></circle>
                <circle cx="54.0001" cy="54.0001" r="35.0714" fill="white" stroke="#5173DD" stroke-width="7"></circle>
                <path d="M38.4717 51.6254L35.9936 49.1537L31.0503 54.1099L33.5283 56.5815L38.4717 51.6254ZM49.9326 68L47.4609 70.4781L49.9326 72.9434L52.4043 70.4781L49.9326 68ZM33.5283 56.5815L47.4609 70.4781L52.4043 65.5219L38.4717 51.6254L33.5283 56.5815ZM52.4043 70.4781L78.4717 44.4781L73.5283 39.5219L47.4609 65.5219L52.4043 70.4781Z" fill="#5173DD"></path>
            </svg>     
                <p class="mQ_title01"><span class="mQ_title02">매칭 서비스 </span>신청이 완료되었습니다.</p>
            
            <div class="matchingBtnWrap">
                <a href="#" id="goHomeButton"><p>확인</p></a>                                          
            </div>                      
        </div><br /><br />
        <?php } else if( $result && is_array($result) ) { ?>            

            <div class="matchingResult"> 
            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="128" viewBox="0 0 100 128" fill="none">
                <rect y="0.0551758" width="100" height="127.523" rx="10" fill="#E9ECF7"></rect>
                <rect x="12.5" y="13.5" width="75" height="103.052" rx="2.5" fill="white" stroke="#5173DD" stroke-width="5"></rect>
                <rect x="25" y="56" width="50.2199" height="8.36999" rx="2" fill="#E9ECF7"></rect>
                <path d="M49.717 50.4663C56.7404 50.4663 62.434 44.7656 62.434 37.7334C62.434 30.7012 56.7404 25.0005 49.717 25.0005C42.6936 25.0005 37 30.7012 37 37.7334C37 44.7656 42.6936 50.4663 49.717 50.4663Z" fill="#E9ECF7"></path>
                <path d="M62.4661 37.6646C62.4661 30.4042 56.3199 24.5736 48.9215 25.0245C42.5975 25.4145 37.4433 30.5235 37.0295 36.8104C36.7881 40.4791 38.1224 43.8294 40.4196 46.2805C40.7592 46.6466 41.2764 46.8005 41.7672 46.6811L41.8123 46.6705C42.2314 46.5723 42.5842 46.2805 42.7566 45.8879C43.5922 44.0178 45.1679 42.5402 47.1124 41.832C47.6005 41.6542 47.6641 40.9964 47.2105 40.7391C45.29 39.6461 44.1493 37.3463 44.847 34.874C45.3138 33.2213 46.6534 31.9109 48.322 31.4652C51.7387 30.5501 54.829 33.0913 54.829 36.3409C54.829 38.211 53.8051 39.8477 52.2851 40.7231C51.8182 40.9911 51.8766 41.6569 52.378 41.8399C54.3171 42.5482 55.8822 44.0204 56.7098 45.8906C56.8822 46.2858 57.2324 46.5723 57.6489 46.6731L57.6886 46.6837C58.1847 46.8031 58.702 46.6439 59.0468 46.2779C61.1637 44.0178 62.4635 40.9964 62.4635 37.6672" fill="#0031C8"></path>
                <path d="M25.0005 80.7891H75.5367" stroke="#E9ECF7" stroke-width="2.5" stroke-linecap="round"></path>
                <path d="M25.0005 88.1284H75.5367" stroke="#E9ECF7" stroke-width="2.5" stroke-linecap="round"></path>
                <path d="M25.0005 95.4683H75.5367" stroke="#E9ECF7" stroke-width="2.5" stroke-linecap="round"></path>
                <path d="M25.0005 102.808H50.2686" stroke="#E9ECF7" stroke-width="2.5" stroke-linecap="round"></path>
                </svg>    
                <p class="mQ_title01"><span class="mQ_title02">매칭 서비스</span>를 이미 신청하셨습니다.</p>
            
            <div class="matchingBtnWrap">
                <a href="#" id="goBackButton"><p>확인</p></a>                                          
            </div>                      
        </div><br /><br />
        <?php } else { ?>
        
        <div class="matchingTop">
            <div class="matchingImg">
                <?php
                    // 이미지 바이너리 파일
                    $imageData = ("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");
                ?>
                <img src="data:image/png;base64,<?=$imageData?>" alt="매칭이미지">
            </div>                        
            <div class="titleSummary">
                <h2>이로움 매칭 서비스</h2>
                <ul>
                    <li><svg width="18" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 4.33333L7 9.66667L15 1" stroke="#FFD600" stroke-width="2" stroke-linecap="round"/></svg>장기요양인정등급 <span class="f_bold700">상담 신청자 정보 제공</span></li>
                    <li><svg width="18" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 4.33333L7 9.66667L15 1" stroke="#FFD600" stroke-width="2" stroke-linecap="round"/></svg>상담 신청자 상담 <span class="f_bold700">신청정보 및 진행이력 관리</span></li>
                    <li><svg width="18" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 4.33333L7 9.66667L15 1" stroke="#FFD600" stroke-width="2" stroke-linecap="round"/></svg>상담 신청자 매칭 시 <span class="f_bold700">상담 접수/거부 확인</span></li>
                    <li><svg width="18" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 4.33333L7 9.66667L15 1" stroke="#FFD600" stroke-width="2" stroke-linecap="round"/></svg>이로움 서비스 내 <span class="f_bold700">이용자 상호명 노출</span></li>
                </ul>
            </div>
        </div>

        <div class="matchingkeyInput">
            <h4>상담을 위한 <span class="f_bold700">간단 정보</span>를 입력해주세요!</h4>
            <ul>
                <li><p>사업자번호<b>*</b></p><input class="thkc_inputM numOnly" id="Q_bnum" name="Q_bnum" maxlength="12" placeholder="숫자만 입력" value="" type="text" autocomplete="off" /></li>
                <li><p>대표자 성명 <b>*</b></p><input class="thkc_inputM " id="Q_bnm" name="Q_bnm" placeholder="홍길동" value="" type="text" autocomplete="off" /></li>
                <li><p>휴대전화번호 <b>*</b></p><input class="thkc_inputM" id="Q_hp" name="Q_hp" placeholder="010-1111-2222 " maxlength="14" value="" type="text" autocomplete="off"></li>
            </ul>
        </div>

        <div class="matchingQuest">
            <div class="QuestTitleWrap">
                <p class="f_s14 f_bold700">1분만에 작성하면 끝!</p>
                <p class="mQ_title01">매칭 서비스 <br><span class="mQ_title02">고객 설문지</span></p>
                <p class="mQ_title03">수급자 매칭 서비스 품질 향상 목적으로 <br>아래 장기요양기관 운영 관련 정보를 수집하고 있습니다.</p>
                    <p class="mQ_title03">내용 확인 후 체크하여 주시기 바랍니다.</p>
            </div>
            <div class="QuestBoxWrap">
                <p><span>Q1.</span> 월 평균 복지용구 청구하는 수급자 수는 어느 정도입니까?</p>
                <ul>
                    <li><input id="Q1_1" name="Q1" value="A" type="radio" /><label for="Q1_1" class="thkc_blind"><span>0~50명</span></label></li>
                    <li><input id="Q1_2" name="Q1" value="B" type="radio" /><label for="Q1_2" class="thkc_blind"><span>51~100명</span></label></li>
                    <li><input id="Q1_3" name="Q1" value="C" type="radio" /><label for="Q1_3" class="thkc_blind"><span>101~200명</span></label></li>
                    <li><input id="Q1_4" name="Q1" value="D" type="radio" /><label for="Q1_4" class="thkc_blind"><span>201~300명</span></label></li>
                    <li><input id="Q1_5" name="Q1" value="E" type="radio" /><label for="Q1_5" class="thkc_blind"><span>301명 이상</span></label></li>
                </ul>
            </div>
            <div class="QuestBoxWrap">
                <p><span>Q2.</span> 월 평균 복지용구 총 매입액은 어느 구간에 해당하십니까?</p>               
                <ul class="Questdf">
                    <div>
                        <li><input id="Q2_1" name="Q2" value="A" type="radio" /><label for="Q2_1" class="thkc_blind"><span>100만원 이하</span></label></li>
                        <li><input id="Q2_2" name="Q2" value="B" type="radio" /><label for="Q2_2" class="thkc_blind"><span>100~300만원 이하</span></label></li>
                        <li><input id="Q2_3" name="Q2" value="C" type="radio" /><label for="Q2_3" class="thkc_blind"><span>300~500만원 이하</span></label></li>
                    </div>
                    <div>
                        <li><input id="Q2_4" name="Q2" value="D" type="radio" /><label for="Q2_4" class="thkc_blind"><span>500 ~1,000만원 이하</span></label></li> 
                        <li><input id="Q2_5" name="Q2" value="E" type="radio" /><label for="Q2_5" class="thkc_blind"><span>1,000~1,500만원 이하</span></label></li>
                        <li><input id="Q2_6" name="Q2" value="F" type="radio" /><label for="Q2_6" class="thkc_blind"><span>1,500~2,000만원 이하</span></label></li>
                    </div>
                    <div>
                        <li><input id="Q2_7" name="Q2" value="G" type="radio" /><label for="Q2_7" class="thkc_blind"><span>2,000~3,000만원 이하</span></label></li>
                        <li><input id="Q2_8" name="Q2" value="H" type="radio" /><label for="Q2_8" class="thkc_blind"><span>3,000~4,000만원 이하</span></label></li>
                        <li><input id="Q2_9" name="Q2" value="I" type="radio" /><label for="Q2_9" class="thkc_blind"><span>4,000만원 이상</span></label></li>
                    </div>
                </ul>
            </div>
            <div class="QuestBoxWrap">
                <p><span>Q3.</span> 이로움 또는 케어맥스코리아에 주문 시 어떤 방식을 주로 사용하십니까? <b>복수응답 가능</b></p>
                <ul>                    
                    <input id="Q3" name="Q3" type="hidden">
                    <li><input id="Q3_1" name="Q3_1" value="A" type="checkbox" class="Q3" /><label for="Q3_1" class="thkc_blind"><span>온라인 주문</span></label></li>
                    <li><input id="Q3_2" name="Q3_2" value="B" type="checkbox" class="Q3" /><label for="Q3_2" class="thkc_blind"><span>전화 주문</span></label></li>
                    <li><input id="Q3_3" name="Q3_3" value="C" type="checkbox" class="Q3" /><label for="Q3_3" class="thkc_blind"><span>문자 또는 카카오톡 주문</span></label></li>
                    <li><input id="Q3_4" name="Q3_4" value="D" type="checkbox" class="Q3" /><label for="Q3_4" class="thkc_blind"><span>팩스 주문</span></label></li>
                </ul>
            </div>
            <div class="QuestBoxWrap">
                <p><span>Q4.</span> 이로움 & 케어맥스코리아와 거래하는 비중은 어느 정도입니까?</p>
                <ul>
                    <li><input id="Q4_1" name="Q4" value="A" type="radio" /><label for="Q4_1" class="thkc_blind"><span>10% 이하</span></label></li>
                    <li><input id="Q4_2" name="Q4" value="B" type="radio" /><label for="Q4_2" class="thkc_blind"><span>10~30%</span></label></li>
                    <li><input id="Q4_3" name="Q4" value="C" type="radio" /><label for="Q4_3" class="thkc_blind"><span> 30~50%</span></label></li>
                    <li><input id="Q4_4" name="Q4" value="D" type="radio" /><label for="Q4_4" class="thkc_blind"><span>50~70%</span></label></li>
                    <li><input id="Q4_5" name="Q4" value="E" type="radio" /><label for="Q4_5" class="thkc_blind"><span>70~90%</span></label></li>
                    <li><input id="Q4_6" name="Q4" value="F" type="radio" /><label for="Q4_6" class="thkc_blind"><span>90% 이상</span></label></li>
                </ul>
            </div>
            <div class="QuestBoxWrap">
                <p><span>Q5.</span> 전동침대, 안전손잡이 등 설치 서비스는 어떤 방식을 주로 사용하십니까? <b>복수응답 가능</b> </p>
                <ul>
                    <input id="Q5" name="Q5" type="hidden">
                    <li><input id="Q5_1" name="Q5_1" value="A" type="checkbox" class="Q5" /><label for="Q5_1" class="thkc_blind"><span>직접 설치 운영</span></label></li>
                    <li><input id="Q5_2" name="Q5_2" value="B" type="checkbox" class="Q5" /><label for="Q5_2" class="thkc_blind"><span>자체(주변 업체) 위탁</span></label></li>
                    <li><input id="Q5_3" name="Q5_3" value="C" type="checkbox" class="Q5" /><label for="Q5_3" class="thkc_blind"><span>유통사 위탁</span></label></li>
                    <li><input id="Q5_4" name="Q5_4" value="D" type="checkbox" class="Q5" /><label for="Q5_4" class="thkc_blind"><span>제조사 위탁</span></label></li>
                </ul>
            </div>
        </div>
        <div class="matchingPolicy">
            <h1>서비스 이용약관</h1>
            <p>서비스 이용 주요사항을 전부 확인하였으며 서비스 이용 신청에 동의 하세요.</p>

            <div class="policyWrqp">
                <div action="" class="agreeForm">
                    <div class="p_text">
                        <h4>제1조 (서비스 이용 시간)</h4>
                        <p>① 예비수급자 매칭 서비스는 이용자의 업무 시간에 제공함을 원칙으로 합니다.</p>
                        <p>② 이용시간의 변경은 이용자가 접근하기 용이한 전자적 장치를 통하여 사전에 공지합니다.</p>
                        <br>
                        <h4>제2조 (서비스의 내용)</h4>
                        <p>이용자에게 제공하는 ”서비스“의 내용은 다음 각 호와 같습니다.</p>
                            <p>1. “플랫폼” 장기요양인정등급 테스트 상담 신청자 정보 제공</p>
                            <p>2. “플랫폼” 상담 신청자 매칭 시 상담 접수/거부 선택</p>
                            <p>3.  “플랫폼” 상담 신청자 상담 완료 시 맴버스 페이지 이력 확인</p>
                            <p>4. “플랫폼” 이로움 맴버스 페이지 내 이용자 상호명 노출</p>
                        <br>
                        <h4>제3조 (서비스 이용 의무사항)</h4>
                        <p>① 이용자는 자신의 개인정보, 계정정보(아이디, 비밀번호, 결제비밀번호) 등을 제3자에게 제공하지 아니하며, 해당 정보가 유출되지 않도록 관리하여야 합니다.</p>
                        <p>② 이용자는 상담신청자 매칭 시 영업일 기준 2일 내 상담 접수 / 거부 의사를 밝혀야 합니다.</p>
                            <p>③ 이용자는 상담신청자 상담 완료 시 맴버스 페이지에 로그인하고 상담 완료 처리해야 합니다.</p>
                        <br>
                        <h4>제4조 (개인(신용)정보의 보호)</h4>
                        <p>① 이로움 플랫폼 서비스는 개인정보의 보호 및 처리와 관련하여『개인정보보호법』, 정보통신망 이용촉진 및 정보보호 등에 관한 법률』및 각 그 하위 법령에 정하는 사항을 준수하며, 개인정보의 보호를 위하여 노력합니다.</p>
                        <p>② 이로움 서비스는 개인정보의 수집, 이용, 제공, 보호, 위탁 등에 관한 제반 사항의 구체적인 내용을 개인정보처리방침을 통하여 규정하며, 개인정보처리방침은 이로움 플랫폼 웹사이트를 통하여 게시합니다.</p>
                        <p>③ 이용자가 전달받은 상담 신청자 개인(신용)정보의 보유 및 관리책임은 이용자에 있으며, 이용자는 이로움 플랫폼 웹사이트의 개인정보보호 규정을 준수합니다.</p>
                    </div>
                </div>
            </div>
            <div class="matchingAgree">
                <ul>
                    <li><input id="matchingservice.Y" name="matchingservice" value="yes" type="checkbox" /><label for="matchingservice.Y" class="f_bold500">동의합니다</label></li>
                    <li><input id="matchingservice.N" name="matchingservice" value="no" type="checkbox" /><label for="matchingservice.N">동의하지 않습니다.</label></li>
                </ul>
            </div> 
            <div class="matchingBtnWrap">
                <a href="#" id="submitLink">
                    <p>매칭 서비스 신청하기</p>
                    <p><svg width="10" height="18" viewBox="0 0 13 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L11 11L1 21" stroke="white" stroke-width="2" stroke-linecap="round"/></svg></p>
                </a>                    
            </div>                  
        </div>
        
        <?php } ?>                    
        <!-- 예비수급자 & 보호자  매칭 서비스 신청서 end -->
        
    </form>

<?php 
    include_once("./_tail.php");
?>
