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
    /* // 파일명 :  www/thema/eroumcare_new/main/_SNB_partner.php */
    /* // 파일 설명 : 왼쪽 서브 네이게이션(왼쪽 사이드 메뉴) - 파트너 전용 (리뉴얼) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
    // SQL 처리 부분 시작
    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==

    $_Cnt_Purchase = sql_fetch("    SELECT 
                                        count(*) as cnt
                                    FROM 
                                        purchase_cart ct
                                    LEFT JOIN 
                                        purchase_order od ON ct.od_id = od.od_id 
                                    WHERE 
                                        ct.ct_supply_partner = '" . $member['mb_id'] . "'
                                        AND (ct.ct_delivery_num is null or ct.ct_delivery_num = '') 
                                        AND (od.od_partner_manager is null or od.od_partner_manager = '')
                                        AND ct.ct_status IN ('발주완료')
    ");

    $_Cnt_Order = sql_fetch("   SELECT
                                    COUNT(CASE WHEN c.ct_status='출고준비' THEN 1 END) AS cnt_Shipped
                                FROM
                                    g5_shop_cart c
                                LEFT JOIN
                                    g5_shop_order o ON c.od_id = o.od_id
                                WHERE
                                    c.ct_is_direct_delivery IN(1, 2)
                                    AND c.ct_direct_delivery_partner = '" . $member['mb_id'] . "'
                                    AND (c.ct_delivery_num IS NULL OR c.ct_delivery_num = '')
                                    AND (o.od_partner_manager IS NULL OR o.od_partner_manager = '')
                                    AND c.ct_status IN ('출고준비')
                                    AND o.od_del_yn = 'N'
    ");

    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
    // SQL 처리 부분 종료
    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==

?>
    <!-- ## 메인 전체 영역 (왼쪽메뉴, gap,  컨텐츠) ## -->
    <div id="thkc_mainWrap" class="thkc_container">

        <!-- ** PC 왼쪽 메뉴 "w302px" ** -->
        <div id="thkc_asideWrap">
            <aside class="thkc_aside">
                <!-- 회원정보 -->
                <div id="memberWrap">

                    <p class="memTitle"><!-- 회원이름 -->
                        <?=$member["mb_name"]?><span class="dir"> 님</span>
                    </p>
                    <p class="member_modi">
                        <a href="#" onclick="location.href='<?=G5_BBS_URL?>/member_confirm.php?url=member_info_newform.php'">회원정보 수정</a>
                    </p>
                    
                    <!-- 간편메뉴 -->
                    <div class="fastTabe">
                        <table>
                            <tr>                              
                                <td class="bb" onclick="location.href='/shop/partner_orderinquiry_list.php'">
                                    <span class="fast_img"><img src="<?=G5_IMG_URL;?>/new_main_parther/thkc_ico_parther01.svg" alt="주문내역"></span> <div class="simTitle btn_simple"><span class="f_bold700">주문</span>내역</div>
                                    
                                        <?php if( $_Cnt_Order && ($_Cnt_Order['cnt_Shipped']>0) ) { ?>
                                    <div class="icon_report Cnt_Order"><?=$_Cnt_Order['cnt_Shipped']?></div>
                                    <?php } ?>
                                </td>                                
                            </tr>
                            <tr>                              
                                <td class="bb" onclick="location.href='/shop/partner_purchaseorderinquiry_list.php'">
                                    <span class="fast_img"><img src="<?=G5_IMG_URL;?>/new_main_parther/thkc_ico_parther02.svg" alt="주문내역"></span> <div class="simTitle btn_simple"><span class="f_bold700">발주</span>내역</div>
                                    <?php if( $_Cnt_Purchase && ($_Cnt_Purchase['cnt']>0) ) { ?>
                                    <div class="icon_report Cnt_Purchase"><?=$_Cnt_Purchase['cnt']?></div>
                                    <?php } ?>
                                </td>
                            </tr> 
                            <tr>                              
                                <td onclick="location.href='/shop/partner_ledger_list.php'">
                                    <span class="fast_img"><img src="<?=G5_IMG_URL;?>/new_main_parther/thkc_ico_parther03.svg" alt="주문내역"></span> <div class="simTitle btn_simple"><span class="f_bold700">거래처</span>원장</div>
                                </td>
                            </tr>                             
                        </table>
                        
                    </div>


                    <!-- 이로움 고객센터 전화번호 -->
                    <div class="left_csWrap">
                        <div class="csTitle">이로움 고객센터</div>
                        <div class="csCall">1533-5088</div>
                    </div>
                    

                    <!-- 공지사항 -->
                    <div class="noticeWrap left_partTop">
                        <h3 class="mgTitle y_bar_02">공지사항
                            <a href="<?=G5_BBS_URL;?>/board.php?bo_table=notice">
                            <div class="more_01"><span class="b_more f_bold700" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=notice'">+</span></div>                                
                            </a>
                        </h3>
                        
                    </div>
                    <hr>
                    <div class="notice_menu">
                        <?=latest('new_notice_left', 'notice', 5, 20);?>
                    </div>
                                    
                    <?php
                        // 23.02.22 : 서원 - SNB ( Side Navigation Bar ) : Side 에서 사용하는 하단 공통 부분.
                        include_once("_SNB_share.php");
                    ?>

                </div>
            </aside>
        </div>

        <!-- ** mobile 왼쪽 aside 복제 **-->
        <div class="overlay"></div>
        <div class="asideClone">
            <div class="mobileAside">
                <div class="m_toggle">
                    <div class="bar1"></div>
                    <div class="bar2"></div>
                    <div class="bar3"></div>
                </div>
                <!-- 모바일 토글 아이콘-->
                <div class="toggleClone">
                </div>
            </div>
        </div>
        
        <!--  ** gap w38px ** -->
        <div id="thkc_gap"></div>
        
        <!--  ** 메인 컨텐츠  "w1060px" ** -->
        <div id="thkc_conWrap">

            <div class="at-container"> <!-- at-container start -->
                <div class="at-content"> <!-- at-content start -->