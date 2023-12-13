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
    /* //  * Program Name : EROUMCARE Platform! = matchingservice Ver:1 */
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
    /* // 파일명 : www\adm\shop_admin\popup.eroumon_form_result.php */
    /* // 파일 설명 : [관리자] 이로움ON 매칭서비스 신청 설문지 작성에 대한 결과 페이지 */
    /*                  사업소에서 최초 매칭 신청시 작성한 설문지 답변에 대한 결과 값을 시각화 해주는 페이지. */
    /*                   */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500060';
include_once("./_common.php");    

$_id = clean_xss_attributes( clean_xss_tags( get_search_string( $_GET['id'] ) ) );

$sql = " SELECT mb_id, mb_giup_bname, mb_matching_forms FROM g5_member WHERE mb_id='" . $_id . "'";
$row = sql_fetch($sql);

$_FormsResult = json_decode( $row['mb_matching_forms'] );
?>


<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FormsResult</title>
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_reset.css">
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_style.css">
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="<?=G5_JS_URL;?>/jquery.fileDownload.js"></script>
</head>
    
<body oncontextmenu="return false" ondragstart="return false" onselectstart="return false">

    <div id="popupHeaderTopWrap">
        <div class="title">[<?=$row['mb_giup_bname']?>] 매칭 상담신청 설문 결과</div>
        <div class="close"> <a href="javascript:void(0);"> × </a> </div>
    </div>


    <?php if( $_FormsResult || !is_null($_FormsResult) ) { ?>

    <div class="QuestBoxWrap">
        <p><span>Q1.</span> 월 평균 복지용구 청구하는 수급자 수는 어느 정도입니까?</p>
        <ul>
            <?php if( $_FormsResult->Q1 === "A" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>0~50명</span></li>
            <?php } else if( $_FormsResult->Q1 === "B" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>51~100명</span></li>
            <?php } else if( $_FormsResult->Q1 === "C" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>101~200명</span></li>
            <?php } else if( $_FormsResult->Q1 === "D" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>201~300명</span></li>
            <?php } else if( $_FormsResult->Q1 === "E" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>301명 이상</span></li>
            <?php } ?>
        </ul>
    </div>
    <div class="QuestBoxWrap">
        <p><span>Q2.</span> 월 평균 복지용구 총 매입액은 어느 구간에 해당하십니까?</p>               
        <ul class="Questdf">

            <?php if( $_FormsResult->Q2 === "A" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>100만원 이하</span></li>
            <?php } else if( $_FormsResult->Q2 === "B" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>100~300만원 이하</span></li>
            <?php } else if( $_FormsResult->Q2 === "C" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>300~500만원 이하</span></li>
            <?php } else if( $_FormsResult->Q2 === "D" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>500 ~1,000만원 이하</span></li> 
            <?php } else if( $_FormsResult->Q2 === "E" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>1,000~1,500만원 이하</span></li>
            <?php } else if( $_FormsResult->Q2 === "F" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>1,500~2,000만원 이하</span></li>
            <?php } else if( $_FormsResult->Q2 === "G" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>2,000~3,000만원 이하</span></li>
            <?php } else if( $_FormsResult->Q2 === "H" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>3,000~4,000만원 이하</span></li>
            <?php } else if( $_FormsResult->Q2 === "I" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>4,000만원 이상</span></li>
            <?php } ?>

        </ul>
    </div>
    <div class="QuestBoxWrap">
        <p><span>Q3.</span> 이로움 또는 케어맥스코리아에 주문 시 어떤 방식을 주로 사용하십니까?</p>
        <ul>
            <?php $_arryQ3 = explode(",", $_FormsResult->Q3); ?>
            <?php if( in_array("A", $_arryQ3) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>온라인 주문</span></li><?php } ?>
            <?php if( in_array("B", $_arryQ3) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>전화 주문</span></li><?php } ?>
            <?php if( in_array("C", $_arryQ3) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>문자 또는 카카오톡 주문</span></li><?php } ?>
            <?php if( in_array("D", $_arryQ3) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>팩스 주문</span></li><?php } ?>
        </ul>
    </div>
    <div class="QuestBoxWrap">
        <p><span>Q4.</span> 이로움 & 케어맥스코리아와 거래하는 비중은 어느 정도입니까?</p>
        <ul>
            <?php if( $_FormsResult->Q4 === "A" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>10% 이하</span></li>
            <?php } else if( $_FormsResult->Q4 === "B" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>10~30%</span></li>
            <?php } else if( $_FormsResult->Q4 === "C" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>30~50%</span></li>
            <?php } else if( $_FormsResult->Q4 === "D" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>50~70%</span></li>
            <?php } else if( $_FormsResult->Q4 === "E" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>70~90%</span></li>
            <?php } else if( $_FormsResult->Q4 === "F" ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>90% 이상</span></li>
            <?php } ?>
        </ul>
    </div>
    <div class="QuestBoxWrap">
        <p><span>Q5.</span> 전동침대, 안전손잡이 등 설치 서비스는 어떤 방식을 주로 사용하십니까?</p>
        <ul>
            <?php $_arryQ5 =  explode(",", $_FormsResult->Q5); ?>
            <?php if( in_array("A", $_arryQ5) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>직접 설치 운영</span></li><?php } ?>
            <?php if( in_array("B", $_arryQ5) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>자체(주변 업체) 위탁</span></li><?php } ?>
            <?php if( in_array("C", $_arryQ5) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>유통사 위탁</span></li><?php } ?>
            <?php if( in_array("D", $_arryQ5) ) { ?><li><img src="<?=G5_IMG_URL;?>/icon_security_check.png"><span>제조사 위탁</span></li><?php } ?>
        </ul>
    </div>

    <?php } else { ?>

    <div style="width:100%; margin:25% 0px; text-align:center; font-family:'Noto Sans KR',sans-serif;">
        <img src='<?=G5_IMG_URL;?>/warn1.png' alt='경고' style="margin: auto;">        
        <div style="height:30px;"></div>
        <p>사업소명 : <?=$row['mb_giup_bname']?></p>
        <div style="height:10px;"></div>
        <p>설문지에 <span style="font-weight:900;">답변한 데이터가 없습니다.</span></p>
        <div style="height:10px;"></div>
        <p>해당 사업소의 매칭상담 신청여부가</p>
        <p>관리자의에 의해 임의 변경되었을 수 있습니다.</p>
    </div>

    <?php } ?>



    <script type="application/javascript">

        $(function() {

            $('.close').click(function() {
                setClose();
            });

            //parent.$("#eroumon_form_result_popup iframe").load(function(){
            <?php
                if( !$_FormsResult || is_null($_FormsResult) ) {
                    echo("
                    setTimeout(function () {
                        //alert('매칭상담 설문지의 데이터가 존재하지 않습니다.');
                        //setClose(); 
                    },250);
                    ");
                }                    
            ?>
            //});

        });

        // 팝업창 닫기.
        function setClose() {
            parent.$('body').removeClass('modal-open');
            parent.$('#eroumon_form_result_popup').hide();
        }

    </script>  

    <style>

        /* 기본 */
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; position: relative; }
        html, body { width: 100%; font-family: "Noto Sans KR", sans-serif; }
        body { padding: 50px 5px}
        a { text-decoration: none; color: inherit; }
        ul, li { list-style: none; }
        button { border: 0; font-family: "Noto Sans KR", sans-serif; cursor: pointer; }
        input { font-family: "Noto Sans KR", sans-serif; }

        /* 고정 상단 */
        #popupHeaderTopWrap { position: fixed; width: 100%; left: 0; top: 0; z-index: 10; background-color: #3d3781; padding: 0 20px; }
        #popupHeaderTopWrap:after { display: block; content: ''; clear: both; }
        #popupHeaderTopWrap > div { height: 100%; line-height: 40px; }
        #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 20px; }
        #popupHeaderTopWrap > .close { float: right; }
        #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 30px; top: 1px; }

        .QuestBoxWrap { padding: 5px 30px; }
        .QuestBoxWrap p { font-size: 16px; padding: 10px 0; font-weight: 500; }
        .QuestBoxWrap p>span { color: #5270DD; }
        .QuestBoxWrap p>b { font-size: 12px; font-weight: 400; border-radius: 5px; border: 1px solid #7304FF; background: #fff; padding: 2px 5px; }
        .QuestBoxWrap ul { background: #fff; padding: 0px 30px; border-radius: 5px; border: 1px solid #E7E7E7; background: #FFF; box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.10); }
        .QuestBoxWrap .Questdf { display: flex; justify-content: space-between; }
        .QuestBoxWrap ul li { font-size: 14px; padding: 5px 0; display: flex; align-items: center; }
        .QuestBoxWrap ul li img { width: 22px; }
        .QuestBoxWrap ul li span { padding: 0 0 0 5px; }

    </style>
  
</body>
    
</html>