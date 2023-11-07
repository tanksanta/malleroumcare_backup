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
    /* // 파일명 : www\shop\popup.eroumon_members_testresult.php */
    /* // 파일 설명 : 해당 페이지는 이로움On에서 상담 신청시 '인정등급상담'일 경우 이로움ON에서 제공된 데이터를 CURL을 통해 HTML 코드 전체를 받아와서 화면에 뿌려 준다. */
    /*                 본 화면의 Care쪽에서 임의 수정할 수 없으며, ON에서 메일에 발송되는 기본 폼이 적용 되는것으로 변경이 필요 할 경우 이로운ON쪽 메일링 폼을 변경 해줘야 한다. */
    /*                 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once("./_common.php");

    if(!$is_member) { alert("먼저 로그인하세요."); }
    
    $view_err = false;

    if(strpos($_SERVER['REQUEST_METHOD'], 'POST') === false) { $view_err = true; }
    if( !$_POST['RECIPIENTS_NO'] || ($_POST['RECIPIENTS_NO'] === "undefined") ) { $view_err = true; }

?>

    <!-- 고정 상단 -->
    <div id="popupHeaderTopWrap">
        <div class="title">인정등급 예상 테스트 결과</div>
        <div class="close"> <a href="javascript:void(0);" onclick="setClose();" > &times; </a> </div>
    </div>
    
    <div style="height:20px;"></div>

<?php

    /*
        
    // 23.10.23 :  서원
    // 해당 페이지는 이로움ON에서 발생한 상담 신청 처리프로세스 과정엥 발생하는 단발적인 처리 프로세스로 아래 내용을 하드코딩을 처리 한다.
    // 수급자 또는 예비수급자의 인정등급 테스트 결과를 확인하기 위한 페이로 아래 이로움ON에서 제공한하는 URL에 특정 $_POST['RECIPIENTS_NO'] 값을 던져 회신되는 html 코드를 모두 읽어 본 페이지에 출력 한다.

    */

    $apiKey = eroumAPI_Key;//"f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856";f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856
    $ch = curl_init(); // 리소스 초기화
    curl_setopt($ch, CURLOPT_URL, eroum_HOST . "/test/result.html?recipientsNo=".$_POST['RECIPIENTS_NO']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ssl 접근시 필요
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//ssl 접근시 필요
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 최초 연결 시도 2초 이내 불가시 연결 취소
    curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'eroumAPI_Key:'.$apiKey ));
 
    $_result = curl_exec($ch); // 데이터 요청 후 수신
    curl_close($ch);  // 리소스 해제

    if( $_result === "인증되지 않은 접근" ) { $view_err = true; }
    else if( $_result === "테스트 항목이 모두 완료되지 않음" ) { $view_err = true; }
    else if( $_result === "결과 가져오기 실패" ) { $view_err = true; }
    else {  }

    /* 23.11.07 : 서원 - 이로움ON 에서 가져오는 데이터에 에러가 없다면 인정등급테스트 메일링코드 출력. // 문제가 있다면 에러 화면 출력! */
    if( $view_err ) { 
        echo("
            <div style=\"width:100%; margin:25% 0px; text-align:center; font-family:'Noto Sans KR',sans-serif;\">
                <img src='/img/warn1.png' alt='경고'>
                <p>죄송합니다.</p>
                <p><span style=\"font-weight:900;\">일시적 오류</span>가 발생했습니다.</p>
                <p>잠시후 다시 시도해 주세요.</p>
            </div>
        ");
    } else {        
        echo( $_result );
    }
?>

    <div style="height:30px;"></div>

    <!-- 고정 하단 -->
    <div id="popupFooterBtnWrap">
        <a href="javascript:void(0);" class="btn btn_close" onclick="setClose();" > 닫 기 </a>
    </div>

    <script>

        // 팝업창 닫기.
        function setClose() { 
            parent.$('.Popup_TestResult').hide();
            parent.$('body').removeClass('modal-open');
        }

    </script>

    <style>
        
        /* 고정 상단 */
        #popupHeaderTopWrap { position: fixed; width: 100%;  height:30px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
        #popupHeaderTopWrap:after { display: block; content: ''; clear: both; }
        #popupHeaderTopWrap > div { height: 100%; line-height: 22px; }
        #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 16px; line-height: 28px; }
        #popupHeaderTopWrap > .close { float: right; padding-right: 32px; }
        #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 30px; top: -2px; text-decoration: none; }

        /* 고정 하단 */
        #popupFooterBtnWrap { position: fixed; left: 0px; width: 100%; height: 50px; background-color: #fff; bottom: 0px; z-index: 10; text-align:center; }
        #popupFooterBtnWrap > a { color: #fff; -webkit-text-stroke: medium; line-height: 22px; text-decoration: none; padding: 10px 5px; }

        .btn_close{ margin-top: 4px; align-items: center; border-radius: 0.5rem; border: 0px; background-color: #333;  display: inline-flex; font-weight: 500; justify-content: center; line-height: 1; padding: 1.25rem 0.125rem; width: 100px; --tw-shadow: 0px 0.154em 0.154em #00000027; --tw-shadow-colored: 0px 0.154em 0.154em var(--tw-shadow-color); box-shadow: var(--tw-ring-offset-shadow,0 0 #0000),var(--tw-ring-shadow,0 0 #0000),var(--tw-shadow); }

    </style>