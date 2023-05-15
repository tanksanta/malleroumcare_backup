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
    /* // 파일명 :  www/thema/eroumcare_new/main/_Main_partner.php */
    /* // 파일 설명 : 이용자 상황에 따른 메인 메뉴(파트너전용) (리뉴얼) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 


    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
    // SQL 처리 부분
    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==


    // 메인 배너가져오기
    $_Banner = sql_query("  SELECT
                                bn_id, bn_url, bn_alt
                            FROM
                                g5_shop_banner
                            WHERE
                                bn_position IN ('파트너')
                                AND (bn_begin_time<=NOW() AND bn_end_time>=NOW()) 
                                OR (bn_end_time = '' OR bn_end_time = '0000-00-00 00:00:00')
                            ORDER BY bn_order, bn_id DESC
    ");
  
    // 주문건수 
    $_Cnt_Order_Main = sql_fetch("   SELECT
                                    COUNT(ct_id) as cnt_Total,
                                    COUNT(CASE WHEN c.ct_status='출고준비' THEN 1 END) AS cnt_Shipped,
                                    COUNT(CASE WHEN c.ct_status='완료' THEN 1 END) AS cnt_Delivered,
                                    COUNT(CASE WHEN c.ct_is_direct_delivery='2' THEN 1 END) AS cnt_Install
                                FROM
                                    g5_shop_cart c
                                LEFT JOIN
                                    g5_shop_order o ON c.od_id = o.od_id
                                WHERE
                                    c.ct_is_direct_delivery IN(1, 2)
                                    AND c.ct_direct_delivery_partner = '" . $member['mb_id'] . "'
                                    AND c.ct_status IN ('준비', '출고준비', '배송', '완료')
                                    AND o.od_del_yn = 'N'
                                    -- AND o.od_time >= DATE(NOW() - INTERVAL 12 MONTH)
                                    AND o.od_time BETWEEN '" . date('Y-m-') . "01' AND '" . date('Y-m-d') . "'
    ");
    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
    // SQL 처리 부분 종료
    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
?>


            <section class="thkc_section">
                <!-- 01.대시보드 -->
                <div class="dash_bWrap margin30">
                    <ul class="dashList">
                        <li class="dashTitle">주문 현황 (당월) <img src="img/new_common/thkc_ico_arrow_next.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">주문 건수 &nbsp;<span class="d_n"><a href="/shop/partner_orderinquiry_list.php"><?=number_format($_Cnt_Order_Main['cnt_Total']);?></a></span>&nbsp;<span class="d_unit">건</span></li>
                        <li class="dash_line"><img src="img/new_common/thkc_line_gnb.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">출고 완료 &nbsp;<span class="d_n"><a href="/shop/partner_orderinquiry_list.php?ct_status[]=배송&fr_date=<?=date('Y-m-')?>01&to_date=<?=date('Y-m-d')?>"><?=number_format($_Cnt_Order_Main['cnt_Shipped']);?></a></span>&nbsp;<span class="d_unit">건</span></li>
                        <li class="dash_line"><img src="img/new_common/thkc_line_gnb.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">배송 완료 &nbsp;<span class="d_n"><a href="/shop/partner_orderinquiry_list.php?ct_status[]=완료&fr_date=<?=date('Y-m-')?>01&to_date=<?=date('Y-m-d')?>"><?=number_format($_Cnt_Order_Main['cnt_Delivered']);?></a></span>&nbsp;<span class="d_unit"></a></span>&nbsp;<span class="d_unit">건</span></li>
                        <li class="dash_line"><img src="img/new_common/thkc_line_gnb.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">설치 일정 &nbsp;<span class="d_n"><a href="<?=G5_URL;?>/shop/schedule/index.php" onclick="return showSchdule(this.href);" target="_blank"><?=number_format($_Cnt_Order_Main['cnt_Install']);?></a></span>&nbsp;<span class="d_unit">건</span></li>
                    </ul>
                </div>


                <?php if( is_object($_Banner) && $_Banner && ($_Banner->num_rows > 0) ) { ?>
                <!-- 03.프로모션 슬라이딩 배너 -->
                <div class="eventWrap">
                    <!-- Swiper -->
                    <div class="swiper eventSwiper">
                        <div class="swiper-wrapper">
                            <?php
                                // 23.03.06 : 서원 - 메인 롤링배너 데이터 출력. 
                                foreach($_Banner as $key => $row) {
                            ?>
                            <div class="swiper-slide img_r">
                                <?php 
                                    // 23.03.06 : 서원 - 관리자가 업로드 한 배너에 링크 정보가 없을 경우 블랭크(about:blank#blocked) 처리 오류 차단용
                                    $url_ck = false;
                                    if( str_replace("https://","",str_replace("http://", "", $row['bn_url'])) ) { $url_ck = true; }
                                ?>
                                <?php if( $url_ck ) { ?><a href="<?=G5_SHOP_URL;?>/bannerhit.php?bn_id=<?=$row['bn_id']?>"<?=$row['bn_new_win']?" target='_blank'":""?>><?php } ?>
                                    <img src="<?=G5_DATA_URL;?>/banner/<?=$row['bn_id']?>" alt="<?=$row['bn_id']?>_<?=$row['bn_alt']?>">
                                <?php if( $url_ck ) { ?></a><?php } ?>
                            </div>
                            <?php } ?>
                        </div>                       
                    </div>

                    <div class="wrap_swiper_navi">
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>

                </div>
                <?php } ?>

                
                <!-- 08.이로움만의 서비스 -->
                <div class="serviceWrap">
                    <div class="e_TitleWrap e_TitleWrap_p">
                        <div class="e_Title">이로움에서는 <span class="f_bold700">가능합니다.</span></div>
                    </div>
                    <!-- pc -->
                    <div class="svWrap">
                        <div class="svBox svBg01">
                            <h3>안정적 판매 경로 확대</h3>
                            <div class="sv_img"><img src="img/new_main_parther/thkc_banner_parther01.png" alt="제품 홍보 마케팅 서비스"></div>
                            <div class="sv_con">매출 걱정을 덜어드리기 위해 안정적인 판매 경로를 제공해 드립니다.</div>
                        </div>
                        <div class="svBox svBg02">
                            <h3>제품 홍보 마케팅 서비스</h3>
                            <div class="sv_img"><img src="img/new_main_parther/thkc_banner_parther02.png" alt="제품 홍보 마케팅 서비스"></div>
                            <div class="sv_con">제품 생산만 하시면 제품, 홍보, 마케팅은 이로움에서 제공합니다.</div>
                        </div>
                        <div class="svBox svBg03">
                            <h3>MRO 서비스</h3>
                            <div class="sv_img"><img src="img/new_main_parther/thkc_banner_parther03.png" alt="MRO 서비스"></div>
                            <div class="sv_con">재고 및 물류 비용을 낮출 수 있도록 MRO 서비스를 시작합니다.</div>
                        </div>
                    </div>
                    <!-- 모바일 복사(이로움 서비스) -->
                    <div class="moble_svWrap">
                        <div class="swiper mySwiper_service">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="svBox svBg01">
                                        <h3>안정적 판매 경로 확대</h3>
                                        <div class="sv_img"><img src="img/new_main_parther/thkc_banner_parther01.png" alt="안정적 판매 경로 확대">
                                        </div>
                                        <div class="sv_con">매출 걱정을 덜어드리기 위해 안정적인 판매 경로를 제공해 드립니다.</div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="svBox svBg02">
                                        <h3>제품 홍보 마케팅 서비스</h3>
                                        <div class="sv_img"><img src="img/new_main_parther/thkc_banner_parther02.png" alt="제품 홍보 마케팅 서비스">
                                        </div>
                                        <div class="sv_con">제품 생산만 하시면 제품, 홍보, 마케팅은 이로움에서 제공합니다.</div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="svBox svBg03">
                                        <h3>MRO 서비스</h3>
                                        <div class="sv_img"><img src="img/new_main_parther/thkc_banner_parther03.png" alt="MRO 서비스">
                                        </div>
                                        <div class="sv_con">재고 및 물류 비용을 낮출 수 있도록 MRO 서비스를 시작합니다.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 09,10.공지사항/고객센터 Wrap -->
                <div class="boardWrap">
                    <!-- 09.공지사항 -->
                    <div class="board_titleWrap">
                        <div class="board_title">
                            <span class="e_Title">공지사항</span>
                            <span class="b_more f_bold700" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=notice'">+</span>
                        </div>
                        <hr class="board_bar">
                        <div class="noti_conWrap">
                            <?=latest('new_notice_main', 'notice', 5, 34);?>
                        </div>
                    </div>
                    <!-- 10.고객센터 -->
                    <div class="board_titleWrap">
                        <div class="board_title">
                            <span class="e_Title">고객센터</span>
                        </div>
                        <hr class="board_bar">
                        <div class="cs_conWrap">                            
                            <div class="m_csWrap">
                                <div class="m_csBoardWrap m_csBoard_part">
                                    <ul class="m_cs" onclick="location.href='<?=G5_BBS_URL?>/board.php?bo_table=faq'"><!--자주하는 질문-->
                                        <li class="m_iconBg"><img src="img/new_common/thkc_ico_faq.svg" alt="FAQ">
                                        </li>
                                        <li class="m_csTitleWrap" onclick="location.href='<?=G5_BBS_URL?>/board.php?bo_table=faq'">
                                            <h3>자주하는 질문</h3>
                                            <span>궁금한 사항을 확인하실 수 있습니다.</span>
                                        </li>
                                    </ul>
                                    <ul class="m_cs" onclick="location.href='<?=G5_BBS_URL?>/qalist.php'"><!--1:1문의-->
                                        <li class="m_iconBg"><img src="img/new_common/thkc_ico_11.svg" alt="1:1문의">
                                        <li>
                                        <li class="m_csTitleWrap" onclick="location.href='<?=G5_BBS_URL?>/qalist.php'">
                                            <h3>1:1문의</h3>
                                            <span>운영시간 내에 순차적으로 답변해 드립니다.</span>
                                        </li>
                                    </ul>
                                </div>                                
                            </div>                          

                        </div>
                    </div>
                    <!-- 11.고객센터 정보 (모바일에서만)-->
                    <div class="cs_infoWrap">
                        <div class="cs_phone">
                            <!-- <img src="img/new_common/thkc_logo_mark.svg" alt="logo"> -->
                            <span class="f_s16">이로움 고객센터</span>
                            <span class="phone">1533-5088</span>
                        </div>
                        <div class="f_s14">
                            <ul class="cs_m_info">
                                <li class="cs_infoTitle">운영시간</li>
                                <li>[평일] 08:30~17:30 (점심시간 12시~13시) /<br>
                                    [주말/공휴일] 휴무</li>
                            </ul>
                            <ul class="cs_m_info">
                                <li><span class="cs_infoTitle">이메일</span> <a
                                        href="mailto:cs@thkc.co.kr">cs@thkc.co.kr</a></li>
                                <li class="null"> / </li>
                                <li><span class="cs_infoTitle">팩스</span> 02-861-9084</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </section>


            <script src="<?=G5_JS_URL;?>/detectmobilebrowser.js"></script>
            <script type="text/javascript">
            function showSchdule(url) {
                let opt = "width=1360,height=780,left=0,top=10";
                let _url = url;
                if (jQuery.browser.mobile) {
                    opt = "";
                    _url = _url.replace("index.php", "m_index.php");
                }
                window.open(_url, "win_schedule", opt);
                return false;
            }
            </script>