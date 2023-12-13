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
    /* //  * (주)티에이치케이컴퍼 & 이로움Care & 이로움ON - [ THKcompany & EroumCare & EroumON ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! & EroumON 1:1 Matching Service Ver:1.0 */
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
    
    /* // 최종 수정 일자 : 23.08.22 */
    /* 
       // 최종 수정 내용 : 설문및 매칭 서비스 신청 확인 UI를 위해 파일 생성. 
                           23.08.22 - 매칭 설문 및 신청 완료 후 화면의 '수급자 매칭 서비스 관련 사업소 고객 대상 Q&A' 이미지 추가
    */
    
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    /* 
        본 파일은 1회선 데이터 수집을 위한 파일로써~ 이미지는 바이너리 값으로, 아이콘은 svg 코드 값으로 변환하여 1개의 파일에서 모두 관리되도록 처리 합니다.
        또한 별도의 js파일 및 css파일을 추가 하지 않으며, 본 파일 내부에서 처리가 되도록 구성 합니다.
    */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    
    /*
    
        추가 수정 내용 ( 23.11.17 ~ )
         - 기존 마케팅에서 요구했던 매칭 신청에 대한 정보를 파일 정보로 저장하던 방식을 DB 저장 방식으로 변경.
         - DB 방식으로 변경됨에 따라 이로움ON BPLC 테이블의 사업소 정보 중 신청시 매칭 담당자에 대한 정보를 같이 변경 해줌.

        해당 페이지의 구조적 프로세스가 변경됨에 따라 
            "www\sql\이로움Care_매칭서비스신청_관리자메뉴추가.sql"
        위 파일 경로에 있는 SQL을 1회 실행 후 정상 동작이 된다.
          
    */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');
    include_once("./_head.php");
    include_once(G5_LIB_PATH.'/PHPExcel.php');

    if(!$member['mb_id'])
        alert('먼저 로그인하세요.',G5_URL.'/bbs/login.php');


    // 매칭 여부에 대한 상태값
    $_matching = "N";
    if( ($member['mb_giup_matching']=="Y") && ($member['mb_matching_forms']) ) {
        //$_matching = "Y";
    }
   

    // 해당 페이지에서 REQUEST 이벤트가 발생하고, POST일 경우 질문지 데이터 업데이트를 위한 코드 동작.
    if( ($_SERVER['REQUEST_URI'] === $_SERVER['PHP_SELF']) && ($_SERVER['REQUEST_METHOD']==="POST") && ($_matching=="N") ) {
            
        $_referee_cd = "";
        if( !$member['mb_referee_cd'] ) {
            while(1) { // 무한루프 
            // ( 사업소 추천코드의 중복을 막기 위해 임의 생성된 코드가 있는지 확인하고, 존재하지 않을때까지 while 처리 한다. )

                $_recd = range(0, 9); // 0부터 9까지의 배열 생성
                shuffle($_recd); // 배열 섞기            
                $uniqueNum = implode('', array_slice($_recd, 0, 6)); // 배열의 처음 6개 요소를 사용하여 문자열 생성

                $sql = " SELECT COUNT(mb_id) FROM g5_member WHERE mb_referee_cd='" . $uniqueNum . ".";
                $unique_cnt = sql_fetch($sql);

                if( $unique_cnt == 0 ) {                
                    $_referee_cd = $uniqueNum;
                    break; // 반복문을 종료하고 while문 밖으로 나감
                }

            }
        } else { $_referee_cd = $member['mb_referee_cd']; }

        // POST값으로 넘겨 받은 데이터에 대한 SQL인젝션 쿼리 부분 처리.
        $_MM_nm = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['MM_nm'] ) ) );
        $_MM_hp = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['MM_hp'] ) ) );
        $_MM_email = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['MM_email'] ) ) );

        $_form = [ "Q1" => $_POST['Q1'], "Q2" => $_POST['Q2'], "Q3" => $_POST['Q3'], "Q4" => $_POST['Q4'], "Q5" => $_POST['Q5'] ];

        sql_query(" UPDATE g5_member
                    SET mb_giup_matching = 'Y'
                    ,mb_matching_manager_nm = '" . $_MM_nm . "'
                    ,mb_matching_manager_tel = '" . $_MM_hp . "'
                    ,mb_matching_manager_mail = '" . $_MM_email . "'
                    ,mb_matching_dt = NOW()
                    ,mb_matching_forms = '" . json_encode($_form) . "'
                    ,mb_referee_cd = '" . $_referee_cd . "'
                    WHERE mb_id = '{$member['mb_id']}';        
        ");

        $_matchingINFO = [
            "mb_giup_matching" => 'Y'
            ,"mb_matching_manager_nm" => $_MM_nm
            ,"mb_matching_manager_tel" => $_MM_hp
            ,"mb_matching_manager_mail" => $_MM_email
            ,"mb_id" => $member['mb_id']
            ,"mb_giup_bnum" => $member['mb_giup_bnum']
        ];

        // 23.11.20 - 서원 : 프로시저 CALL `PROC_EROUMCARE_BPLC`('모드','이로움ON 회원 데이터');
        //                    사업소의 매칭 담당자 정보는 사업소ID와 사업자번호가 이로움Care와 이로움ON이 동일해야 변경됨.
        $sql = (" CALL `PROC_EROUMCARE_BPLC`('UPDATE_matching','".json_encode($_matchingINFO, JSON_UNESCAPED_UNICODE)."'); ");
        $sql_result = "";
        $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);

        $_matching = "Y";
    }
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
    .matchingPolicy .policyWrqp .agreeForm { width: 100%; padding: 0 20px; }
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

            if( !$("#MM_nm").val() || !$("#MM_hp").val() || !$("#MM_email").val() ) { alert("매칭 담당자의 성명, 휴대폰번호,이메일을 모두 입력하시기 바랍니다."); return; }
            
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


        // Input 요소 참조
        const phoneNumberInput = $('#MM_hp');

        // 입력 내용이 변경될 때마다 실행되는 이벤트 핸들러
        phoneNumberInput.on('input', function () {
            // 입력된 숫자만 남기고 모든 문자 및 하이픈 제거
            const cleanedPhoneNumber = phoneNumberInput.val().replace(/[^0-9]/g, '');

            // 하이픈을 추가한 포맷으로 변경
            const formattedPhoneNumber = formatPhoneNumber(cleanedPhoneNumber);

            // 입력 필드에 포맷된 번호 설정
            phoneNumberInput.val(formattedPhoneNumber);
        });

    });


    // 휴대폰 번호에 하이픈을 추가하는 함수
    function formatPhoneNumber(phoneNumber) {
        const phoneNumberLength = phoneNumber.length;
        if (phoneNumberLength < 4) {
            return phoneNumber;
        } else if (phoneNumberLength < 7) {
            return `${phoneNumber.slice(0, 3)}-${phoneNumber.slice(3)}`;
        } else {
            return `${phoneNumber.slice(0, 3)}-${phoneNumber.slice(3, 7)}-${phoneNumber.slice(7, 11)}`;
        }
    }
</script>

    <form class="form" role="form" name="form_matchingservice" id="form_matchingservice" action="<?=$_SERVER['PHP_SELF'];?>" method="post">

        <!-- 예비수급자 & 보호자  매칭 서비스 신청서 start -->                   
        <div class="matchingTitle">
            <?php if( ($_matching=="N") && ($member['mb_matching_forms']) ) { ?>
                <h3>매칭 서비스 안내</h3>
            <?php } else { ?>
                <h3>예비수급자 & 보호자 <br><span class="f_bold700">매칭 서비스 신청서</span></h3>
            <?php } ?>
        </div>

        <?php /* if( ($_SERVER['REQUEST_URI'] === $_SERVER['PHP_SELF']) && ($_SERVER['REQUEST_METHOD']==="POST") ) { ?>
        <!--
        <div class="matchingResult"> 
            <svg xmlns="http://www.w3.org/2000/svg" width="108" height="108" viewBox="0 0 108 108" fill="none">
                <circle cx="54" cy="54" r="54" fill="#5173DD" fill-opacity="0.1"></circle>
                <circle cx="54.0001" cy="54.0001" r="35.0714" fill="white" stroke="#5173DD" stroke-width="7"></circle>
                <path d="M38.4717 51.6254L35.9936 49.1537L31.0503 54.1099L33.5283 56.5815L38.4717 51.6254ZM49.9326 68L47.4609 70.4781L49.9326 72.9434L52.4043 70.4781L49.9326 68ZM33.5283 56.5815L47.4609 70.4781L52.4043 65.5219L38.4717 51.6254L33.5283 56.5815ZM52.4043 70.4781L78.4717 44.4781L73.5283 39.5219L47.4609 65.5219L52.4043 70.4781Z" fill="#5173DD"></path>
            </svg>
            <p class="mQ_title01"><span class="mQ_title02">매칭 서비스 </span>신청이 완료되었습니다.</p>
            <p class="mQ_title02">상담 배정이 완료되면 수급자 상담관리에서 상담 신청자 목록을 확인하실 수 있습니다.</p>
            <div class="matchingBtnWrap"><a href="javascript:void(0);" id="goHomeButton"><p>확인</p></a></div>
        </div><br />
        <div style="text-align:center;"><img src="<?=G5_IMG_URL;?>/eroumon_matchingservice_qna.png" alt="매칭이미지" style="width: 90%;"></div><br />
        -->
        <?php } else */ if( $_matching == "Y" ) { ?>            

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
            <div class="mQ_title01">
                <span class="mQ_title02">매칭 서비스 </span>신청이 완료되었습니다.
                <br><br><br>
                <p>상담 배정이 완료되면 수급자 상담관리에서</p>
                <p>상담 신청자 목록을 확인하실 수 있습니다.</p>
            </div>
            <div class="matchingBtnWrap"><a href="javascript:void(0);" id="goHomeButton"><p>확인</p></a></div>
        </div><br />
        <div style="text-align:center;"><img src="<?=G5_IMG_URL;?>/eroumon_matchingservice_qna.png" alt="매칭이미지" style="width: 90%;"></div><br />

        <?php } else if( ($_matching == "N") && ($member['mb_matching_forms']) ) { ?>

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
            <br /> <br />
            <div class="mQ_title01">
            <p>기존에 신청하신 이력이 있습니다.</p>
            <p>이로움 <span class="mQ_title02">고객센터 1533-5088</span>로</p>
            <p>연락주시면 자세히 안내 도와드리겠습니다.</p>
            </div>
            <div class="matchingBtnWrap"><a href="javascript:void(0);" id="goHomeButton"><p>확인</p></a></div>

        </div><br />

        <?php } else { ?>
        
        <div class="matchingTop">
            <div class="matchingImg"> 
                <img src="<?=G5_IMG_URL;?>/eroumon_matchingservice_form_top.png" alt="매칭이미지">
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
            <h4>매칭 서비스<span class="f_bold700"> 상담 담당자 정보</span>를 입력해주세요.</h4>
            <ul>
                <li><p>매칭 담당자 성명 <b>*</b></p><input class="thkc_inputM " id="MM_nm" name="MM_nm" placeholder="홍길동" value="" maxlength="10" type="text" autocomplete="off" /></li>
                <li><p>매칭 담당자 휴대폰번호 <b>*</b></p><input class="thkc_inputM" id="MM_hp" name="MM_hp" placeholder="010-1111-2222" maxlength="16" value="" type="text" autocomplete="off"></li>
                <li><p>매칭 담당자 이메일<b>*</b></p><input class="thkc_inputM" id="MM_email" name="MM_email" placeholder="eroumcare@thkc.co.kr" maxlength="200" value="" type="text" autocomplete="off"></li>
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
                <a href="javascript:void(0);" id="submitLink">
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
