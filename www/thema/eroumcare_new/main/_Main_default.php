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
    /* // 파일명 :  www/thema/eroumcare_new/main/_Main_default.php */
    /* // 파일 설명 : 이용자 상황에 따른 메인 메뉴(일반-사업소,관리자 등) (리뉴얼) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

    include_once(G5_LIB_PATH.'/popular.lib.php');

    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
    // SQL 처리 부분 시작
    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==

    // 최근 주문내역 건수
        // 상품준비 건수   cnt_productpreparation = 0 ;
        // 출고준비 건수   cnt_Preparingshipment = 0 ;
        // 출고완료 건수   cnt_Shipmentcompleted = 0 ;
        // 설치일정 건수   cnt_Installationschedule = 0 ;
    $_OrderCnt = sql_fetch("   SELECT

                                    COUNT(CASE WHEN c.ct_status='준비' THEN 1 END) AS cnt_productpreparation,
                                    COUNT(CASE WHEN c.ct_status='출고준비' THEN 1 END) AS cnt_Preparingshipment,
                                    COUNT(CASE WHEN c.ct_status='배송' THEN 1 END) AS cnt_Shipmentcompleted,
                                    -- COUNT(CASE WHEN c.ct_status NOT IN ('완료') AND c.ct_is_direct_delivery='2' THEN 1 END) AS cnt_Installationschedule
                                    ( SELECT COUNT(ct_id) FROM g5_shop_cart WHERE c.ct_is_direct_delivery='2' AND ct_time BETWEEN '" . date('Y-m-d') . "' AND '" . date('Y-m-t') . "') AS cnt_Installationschedule
    
                                FROM
                                    g5_shop_cart c

                                LEFT JOIN
                                    g5_shop_order o ON c.od_id = o.od_id

                                WHERE
                                    c.mb_id = '" . $member['mb_id'] . "' 
                                    AND c.ct_status IN ('준비', '출고준비', '배송', '완료')
                                    AND o.od_del_yn = 'N'
                                    -- AND o.od_time >= DATE(NOW() - INTERVAL 12 MONTH)
                                    -- AND o.od_time BETWEEN '" . date('Y-m-') . "01' AND '" . date('Y-m-t') . "'
                                    -- 주석: 기존 월단위 또는 기간별 검색조건에서 마케팅 요청으로 'g5_shop_cart'테이블과 'g5_shop_order'테이블을 전체 풀 검색으로 변경.
                                    --        기간 검색이 빠짐으로 인한 메인페이지 접속시 속도 저하 발생 예상됨.
                        ");


    // 메인 배너가져오기
    $_Banner = sql_query("  SELECT
                                bn_id, bn_url, bn_alt, bn_new_win
                            FROM
                                g5_shop_banner
                            WHERE
                                bn_position IN ('사업소')
                                AND bn_status = 'Y'
                                AND (bn_begin_time<=NOW() AND bn_end_time>=NOW()) 
                                OR (bn_end_time = '' OR bn_end_time = '0000-00-00 00:00:00')
                            ORDER BY bn_order, bn_id DESC
                        ");
    

    // 추천상품( SQ값 기준 최종 마지막 데이터 1개를 가져와 뿌려줌. )
    $_recommended = sql_query(" SELECT RC.recommended_url, RC.sq, 
                                       IT.*,
                                       ( CASE WHEN RC.product1=IT.it_id THEN 'product1' WHEN RC.product2=IT.it_id THEN 'product2' WHEN RC.product3=IT.it_id THEN 'product3' END ) AS prod
                                FROM g5_shop_recommended RC
                                    LEFT JOIN g5_shop_item IT ON it_id IN ( RC.product1, RC.product2, RC.product3 )
                                WHERE sq = (SELECT MAX(sq) FROM g5_shop_recommended) AND (it_id <>'')
                                ORDER BY prod ASC 
    ");

    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
    // SQL 처리 부분 종료
    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
?>

            <section class="thkc_section">

                <!-- 01.대시보드 -->
                <div class="dash_bWrap">
                    <ul class="dashList">
                        <li class="dashTitle">주문 현황<img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_arrow_next.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">
                            상품준비 &nbsp;
                            <span class="d_n"><a href="<?=G5_URL;?>/shop/orderinquiry.php?ct_status=준비"><?=number_format($_OrderCnt['cnt_productpreparation']);?></a></span>&nbsp;
                            <span class="d_unit">건</span>
                        </li>
                        <li class="dash_line"><img src="<?=G5_IMG_URL;?>/new_common/thkc_line_gnb.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">
                            출고준비 &nbsp;
                            <span class="d_n"><a href="<?=G5_URL;?>/shop/orderinquiry.php?ct_status=출고준비"><?=number_format($_OrderCnt['cnt_Preparingshipment']);?></a></span>&nbsp;
                            <span class="d_unit">건</span>
                        </li>
                        <li class="dash_line"><img src="<?=G5_IMG_URL;?>/new_common/thkc_line_gnb.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">
                            출고완료 &nbsp;
                            <span class="d_n"><a href="<?=G5_URL;?>/shop/orderinquiry.php?ct_status=배송"><?=number_format($_OrderCnt['cnt_Shipmentcompleted']);?></a></span>&nbsp;
                            <span class="d_unit">건</span>
                        </li>
                        <li class="dash_line"><img src="<?=G5_IMG_URL;?>/new_common/thkc_line_gnb.svg" alt="아이콘"></li>
                        <li class="dash_sTitle">
                            설치일정 &nbsp;
                            <span class="d_n"><a href="<?=G5_URL;?>/shop/schedule/index.php" onclick="return showSchdule(this.href);" target="_blank"><?=number_format($_OrderCnt['cnt_Installationschedule']);?></a></span>&nbsp;
                            <span class="d_unit">건</span>
                        </li>
                    </ul>
                </div>


                <!-- 02.공지사항 -->
                <div class="top_noticeWrap">
                    <ul class="top_notice">
                        <?php echo latest("new_notice_main_top", 'notice', 5, 100); ?>
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


                <!-- 04.메인 검색 영역  -->
                <div class="m_searchWrap">
                    <div class="ms_Title">찾고 계신 복지용구를 검색 해 보세요!</div>
                    <div class="m_search">
                        <form name="Qmsearch" id="Qmsearch" method="get" onKeypress="javascript:if(event.keyCode==13) {search_submit('Qmsearch',$('#Qmsearch .search_url').val());}">
                            <input type="hidden" class="search_url" name="url" value="<?=((IS_YC)?($at_href['isearch']):($at_href['search']));?>"> 
                            <input class="m_in_search ipt_search" type="text" name="stx" placeholder="상품명 / 급여코드 / 상품설명 검색" onKeypress="javascript:if(event.keyCode==13) {search_submit('Qmsearch',$('#Qmsearch .search_url').val());}">
                            <div class="m_icon_serch">
                                <a href="#"><img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_search.svg" alt="검색" onclick="search_submit('Qmsearch',$('#Qmsearch .search_url').val());"></a>
                            </div>
                        </form>
                    </div>                        

                    <!-- 해시태그 시작 -->
                    <?=popular('basic', 5, 7);  // ('스킨명', 태그개수, 태그 통계기간[day단위] ) ?>
                    <!-- 해시태그 종료 -->

                </div>



                <!-- 05.이로움 가이드 --> 
                <div class="guideWrap">
                    <div class="e_TitleWrap">
                        <div class="e_Title">이로움 <span class="f_bold700">가이드</span></div>
                        <div class="e_Con">이로움 서비스를 알기 쉽게 안내해 드립니다.</div>
                    </div>
                    <div class="guideBoxWrap">
                        <ul class="g_BoxWrap" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_가이드_복지용구.pdf');">
                            <li class="g_Box"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_guide_01.png" alt="가이드"></li>
                            <li class="guideTitle">사업소 가이드 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt=""></li>
                        </ul>
                        <ul class="g_BoxWrap" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_가이드_의료기상.pdf');">
                            <li class="g_Box"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_guide_02.png" alt="가이드"></li>
                            <li class="guideTitle">의료기상 가이드 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt=""></li>
                        </ul>
                        <ul class="g_BoxWrap" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_가이드_재가센터.pdf');">
                            <li class="g_Box"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_guide_03.png" alt="가이드"></li>
                            <li class="guideTitle">재가센터 가이드 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt=""></li>
                        </ul>
                    </div>
                    <!-- ## 고객가이드 다운로드 파일은 모바일 슬라이드에도 링크 업데이트 해야함 ## -->

                    <!-- 05.이로움 가이드 모바일 복제 (스와이프, 슬라이드)-->
                    <div class="moble_guideBoxWrap">
                        <div class="swiper mySwiper_guide">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <ul class="g_BoxWrap" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_가이드_복지용구.pdf');">
                                        <li class="g_Box">
                                            <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_guide_01.png" alt="">
                                        </li>
                                        <li class="guideTitle">사업소 가이드 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt="가이드"></li>
                                    </ul>
                                </div>
                                <div class="swiper-slide">
                                    <ul class="g_BoxWrap" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_가이드_의료기상.pdf');">
                                        <li class="g_Box">
                                            <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_guide_02.png" alt="">
                                        </li>
                                        <li class="guideTitle">의료기상 가이드 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt="가이드"></li>
                                    </ul>
                                </div>
                                <div class="swiper-slide">
                                    <ul class="g_BoxWrap" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_가이드_재가센터.pdf');">
                                        <li class="g_Box">
                                            <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_guide_03.png" alt="">
                                        </li>
                                        <li class="guideTitle">재가센터 가이드 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt="가이드"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- 06.안전손잡이 -->
                <div class="banner01_BarWrap" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=faq&wr_id=6'">
                    <div class="banner01_img">
                        <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_safe.png" alt="안전손잡이">
                    </div>
                    <div class="b_titleWrap">
                        <h4 class="banner01Title">안전손잡이 설치 안내</h4>
                        <span class="banner01Con">
                            이로움만의 편리한 설치 서비스를 받아보세요!
                        </span>
                    </div>
                </div>


                <?php if( $_recommended && ($_recommended->num_rows>0) ) { ?>
                <!-- 07.이로움 추천제품 -->
                <div class="pdWrap">
                    <div class="e_TitleWrap e_TitleWrap02">
                        <div class="e_Title">이로움 <span class="f_bold700">추천상품</span></div>
                        <?php
                            $_tmpRow = sql_fetch_array($_recommended);
                            mysqli_data_seek($_recommended, 0); 
                            if( $_recommended && ($_recommended->num_rows>0) && $_tmpRow && $_tmpRow['recommended_url'] ) {
                        ?>
                        <div class="btn_pdMore" onclick="location.href='<?=$_tmpRow['recommended_url']?>'">상세보기</div>
                        <?php } ?>
                    </div>
                    <div class="pdBoxWrap">

                    <!-- 추천상품 시작 { -->
                    <?php 
                        if( $_recommended && ($_recommended->num_rows>0) ) { 
                            for($i=0; $_trow=sql_fetch_array($_recommended); $i++) {
                            
                                $_tag = explode(",", $_trow['pt_tag']);
                            
                                $_tag_txt = "";
                                if( $_tag && (COUNT($_tag)>0) ) { 
                                    foreach ($_tag as $key => $val) { $_tag_txt .= "<span class='tag'>#".stripslashes($val)."</span>\n"; } 
                                }
                            
                                $_price = "";
                                if($member["mb_id"]) {
                                    if($_COOKIE["viewType"] == "basic" || in_array($member['mb_type'], ['partner', 'normal'])) {
                                        $_price = number_format($_trow["it_cust_price"]);
                                    } else {
                                        $_entprice = sql_fetch(" SELECT it_price FROM g5_shop_item_entprice WHERE it_id='" . $_trow["it_id"] . "' AND mb_id='" . $member['mb_id'] . "' ");
                            
                                        if( ($_entprice) && ($_entprice['it_price']) ) {
                                            // 사업소별 지정 가격
                                            $_price = number_format($_entprice['it_price']);
                                        }else if($member["mb_level"] == "3") {
                                            //사업소 가격
                                            $_price = number_format($_trow["it_price"]);
                                        } else if($member["mb_level"] == "4") {
                                            //우수 사업소 가격
                                            $_price = ($_trow["it_price_dealer2"]) ? number_format($_trow["it_price_dealer2"]) : number_format($_trow["it_price"]);
                                        } else {
                                            $_price = number_format($_trow["it_price"]);
                                        }
                                    }
                                }                                

                                echo("
                                <!-- 상품(" . $_trow['it_id'] . ") 시작 -->
                                <div class='pd_BoxWrap' onclick='location.href=\"" . G5_SHOP_URL . "/recommendedhit.php?pr_id=" . $_trow['sq'] . "&product=" . $_trow['it_id'] . "\"'>
                                    <div class='pd_Box'>" . get_it_image($_trow['it_id'], '240', '240', '', '', stripslashes($_trow['it_name'])) . "</div>
                                    <div class='pd_Title'>
                                        <h2>" . stripslashes($_trow['it_name']) . "</h2>
                                        <div class='pd_Tag'>" . $_tag_txt . "</div>
                                    </div>
                                    <hr>
                                    <div class='price_BoxWrap'>
                                        <div class='priceWrap'>
                                            <h4 class='allPrice'>급여가</h4> <span class='Price_01'>" . number_format($_trow['it_cust_price']) . "</span>&nbsp;<span
                                                class='Price_unit'>원</span>
                                        </div>
                                        <div class='priceWrap'>
                                            <h4 class='salePrice'>판매가</h4> <span class='Price_02'>" . $_price . "</span>&nbsp;<span
                                                class='Price_unit'>원</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- 상품(" . $_trow['it_id'] . ") 종료 -->
                            ");

                            }                        
                        }
                    ?>
                    <!-- } 추천상품 끝 -->


                    </div>
                </div>
                <?php } ?>


                <!-- 08.이로움만의 서비스 -->
                <div class="serviceWrap">
                    <div class="e_TitleWrap">
                        <div class="e_Title">이로움만의 <span class="f_bold700">서비스</span></div>
                    </div>

                    <!-- PC 화면 -->
                    <div class="svWrap">
                        <div class="svBox svBg01" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=event'">
                            <h3>이벤트</h3>
                            <div class="sv_img"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_service_01.png" alt="이벤트"></div>
                            <div class="sv_con">이로움에서만 만나볼 수 있는 다양하고 실속있는 이벤트</div>
                        </div>
                        <div class="svBox svBg02" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=care_files'">
                            <h3>사업소 운영 자료실</h3>
                            <div class="sv_img"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_service_02.png" alt="레탈 프로그램"></div>
                            <div class="sv_con">공단 평가 자료, 복지용구 컨텐츠 등 사업소 운영에 필요한 자료 모음집</div>
                        </div>
                        <div class="svBox svBg03" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=care_news'">
                            <h3>복지용구 뉴스</h3>
                            <div class="sv_img"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_service_03.png" alt="비급여 샘플 제공"></div>
                            <div class="sv_con">공단 공지사항, 급여가 변동, 전국 수급자 추이, 복지용구 판매 데이터 등 유용한 업계 소식 제공</div>
                        </div>
                    </div>

                    <!-- 모바일 화면 -->
                    <div class="moble_svWrap">
                        <div class="swiper mySwiper_service">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="svBox svBg01" onclick="location.href='/bbs/board.php?bo_table=event'">
                                        <h3>이벤트</h3>
                                        <div class="sv_img"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_service_01.png" alt="이벤트">
                                        </div>
                                        <div class="sv_con">이로움에서만 만나볼 수 있는 다양하고 실속있는 이벤트</div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="svBox svBg02" onclick="location.href='/bbs/board.php?bo_table=care_files'">
                                        <h3>사업소 운영 자료실</h3>
                                        <div class="sv_img"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_service_02.png" alt="레탈 프로그램"></div>
                                        <div class="sv_con">공단 평가 자료, 복지용구 컨텐츠 등 사업소 운영에 필요한 자료 모음집</div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="svBox svBg03" onclick="location.href='/bbs/board.php?bo_table=care_news'">
                                        <h3>복지용구 뉴스</h3>
                                        <div class="sv_img"><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_img_service_03.png" alt="비급여 샘플 제공"></div>
                                        <div class="sv_con">공단 공지사항, 급여가 변동, 전국 수급자 추이, 복지용구 판매 데이터 등 유용한 업계 소식 제공</div>
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
                                        <li class="m_iconBg"><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_faq.svg" alt="FAQ">
                                        </li>
                                        <li class="m_csTitleWrap" onclick="location.href='<?=G5_BBS_URL?>/board.php?bo_table=faq'">
                                            <h3>자주하는 질문</h3>
                                            <span>궁금한 사항을 확인하실 수 있습니다.</span>
                                        </li>
                                    </ul>
                                    <ul class="m_cs" onclick="location.href='<?=G5_BBS_URL?>/qalist.php'"><!--1:1문의-->
                                        <li class="m_iconBg"><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_11.svg" alt="1:1문의">
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
                            <img src="<?=G5_IMG_URL;?>/new_common/thkc_logo_mark.svg" alt="logo">
            <span class="phone">1533-5088</span>
                        </div>
                        <div class="">
                            <ul class="cs_m_info">
                                <li class="cs_infoTitle">운영시간</li>
                                <li>[평일] 08:30~17:30 (점심시간 12시~13시) /<br> [주말/공휴일] 휴무</li>
                            </ul>
                            <ul class="cs_m_info">
                                <li><span class="cs_infoTitle">이메일</span> <a href="mailto:cs@thkc.co.kr">cs@thkc.co.kr</a></li>
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