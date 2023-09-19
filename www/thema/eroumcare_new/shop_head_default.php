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
    /* // 파일명 : thema\eroumcare_new\shop_head_default.php */
    /* // 파일 설명 : GNB 부분이며, 로그인되었을때 보이는 공통 부분. */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

    $is_approved = false;
    if($member['mb_id']) { $is_approved = true; }


    add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/bs3/css/bootstrap.min.css?ver='.APMS_SVER.'" type="text/css">',0);
    add_stylesheet('<link rel="stylesheet" href="'.COLORSET_URL.'/colorset.css?ver='.APMS_SVER.'" type="text/css">',0);
    
?>

    <!-- style -->
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/new_css/thkc_common.css?ver=<?=APMS_SVER;?>">
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/new_css/thkc_gnb_style.css?ver=<?=APMS_SVER;?>">
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/new_css/thkc_lnb_style.css?ver=<?=APMS_SVER;?>">

    <?php if($member['mb_type'] != "partner" ) { ?>
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/new_css/thkc_main_eroum.css?ver=<?=APMS_SVER;?>">
    <?php } else { ?>
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/new_css/thkc_main_partner.css?ver=<?=APMS_SVER;?>">
    <?php } ?>

    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/new_css/thkc_footer_style.css?ver=<?=APMS_SVER;?>">


    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700&display=swap" rel="stylesheet">

    <!-- font swesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- swiper_ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.2.4/swiper-bundle.css" integrity="sha512-303pOWiYlJMbneUN488MYlBISx7PqX8Lo/lllysH56eKO8nWIMEMGRHvkZzfXYrHj4j4j5NtBuWmgPnkLlzFCg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.2.4/swiper-bundle.min.js" integrity="sha512-Hvn3pvXhhG39kmZ8ue3K8hw8obT4rfLXHE5n+IWNCMkR6oV3cfkQNUQqVvX3fNJO/JtFeo/MfLmqp5bqAT+8qg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Swiper JS -->
    <!-- swiper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/9.0.0/swiper-bundle.min.js" integrity="sha512-U0YYmuLwX0Z1X7dX4z45TWvkn0f8cDXPzLL0NvlgGmGs0ugchpFAO7K+7uXBcCrjVDq5A0wAnISCcf/XhSNYiA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>-->


    <!-- ## 상단 메뉴 영역 ## -->
    <div id="thkc_headerWrap">
    
    <?php if( ($member['mb_type'] != "partner" ) ) { ?>
    <?php if($_COOKIE["viewType"] == "adm") { ?>

        <!-- 최상단 띠 배너 (스와이퍼 슬라이드) -->
        <div id="topBannerWrap">
            <div class="topBanner">
                <div class="topBanner_swiper_navi">
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
                
                <div class="swiper mySwiper_band">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide f_topBanner" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=notice&wr_id=152'">성장하는 사업소의 비결 모두 <b>"이로움"</b>에 있습니다. </div>
                        <div class="swiper-slide f_topBanner" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=notice&wr_id=147'">복잡한 계약절차, 이로움 <b>"간편계약"</b> 으로 해결하세요!</div>
                        <div class="swiper-slide f_topBanner" onclick="location.href='<?=G5_BBS_URL;?>/board.php?bo_table=notice&wr_id=141'">클릭만으로 간편하게 수급자 케어, 이로움 <b>"요양정보조회"</b></div>
                    </div>
                </div>
            </div>
        </div>

    <?php } else { if(($member["mb_level"] =="3" || $member["mb_level"] =="4")) { ?>
        <div class="topBanner_mode"> "급여가 안내"모드 실행 중입니다 </div>
    <?php } } ?>
    <?php } ?>
        

        <!-- GNB -->
        <header class="thkc_container">
			<!-- 메인메뉴 스와이프 좌/우 화살표 Start-->
	            <div class="mainMenu_swiper_navi main_swiper_navi">
	                <div class="swiper-button-next"></div>
	                <div class="swiper-button-prev"></div>
	            </div>
            <!-- 메인메뉴 스와이프 좌/우 화살표 End-->
            <nav class="thkc_nav">
                <div class="menuWrap">

                    <!-- 상단로고 -->
                    <h1 class="logo"><a href="<?=G5_URL;?>"><img src="<?=G5_IMG_URL;?>/new_common/thkc_logo_gnb.svg" alt="로고"></a></h1>
                    <div class="menuWrap02<?=(($member['mb_type']=="partner" )?(" main_menu_partner"):(""))?>">
						<div class="swiper mySwiper_menu">
                        <!-- 상단 대 메뉴 -->
  						<ul class="main_menu  swiper-wrapper">
                            <li class="swiper-slide swiper-slide_m"><a href="/shop/list.php?ca_id=10">급여상품(판매)</a></li>
                            <li class="swiper-slide swiper-slide_m" id="first_swiper"><a href="/shop/list.php?ca_id=20">급여상품(대여)</a></li>
                            <li class="swiper-slide swiper-slide_m"><a href="/shop/list.php?ca_id=70">비급여상품</a></li>
							<li class="swiper-slide swiper-slide_m" id="last_swiper"><a href="/shop/list.php?ca_id=80">장애인보장구</a></li>
                        </ul>
						<div class="spWrap"><div class="swiper-pagination"></div></div>
						</div>

                        <!-- 모바일 토글버튼 -->
                        <div class="toggle">
                            <div class="bar1"></div>
                            <div class="bar2"></div>
                            <div class="bar3"></div>
                        </div>
                    </div>
                </div>
                <div class="menuWrap menu_b">                 
                    <?php
                        // 23.03.06 : 서원 - 맴버 타입이 파트너일 경우 해당 메뉴는 나타내지 않는다.
                        if( $member['mb_type'] != "partner" ) {
                    ?>
                    <!-- 상단 이벤트 메뉴 -->
                    <div class="line_gnb">
                        <img src="<?=G5_IMG_URL;?>/new_common/thkc_line_gnb.svg" alt="로고">
                    </div>
                    <ul class="sub_menu f_col3 f_s14">
                        <li><a href="<?=G5_BBS_URL;?>/board.php?bo_table=event">이벤트</a></li>
                        <li><a href="<?=G5_BBS_URL;?>/board.php?bo_table=sample">샘플신청</a></li>
                        <li><a href="<?=G5_BBS_URL;?>/board.php?bo_table=rental">렌탈신청</a></li>
                    </ul>
                    <?php } ?>

                </div>
                <div class="searchWrap">

                    <!-- 검색 -->
                    <div class="thkc_search">
                        <form name="Qsearch" id="Qsearch" method="get" onKeypress="javascript:if(event.keyCode==13) {search_submit('Qsearch',$('#Qsearch .search_url').val());}">
                              <input type="hidden" class="search_url" name="url" value="<?=((IS_YC)?($at_href['isearch']):($at_href['search']));?>"> 
                              <input type="text" class="in_search ipt_search" name="stx" value="<?php echo get_text($stx); ?>" placeholder="상품을 검색하세요" onKeypress="javascript:if(event.keyCode==13) {search_submit('Qsearch',$('#Qsearch .search_url').val());}">
                              <div class="icon_serch"><a href="#"><img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_search.svg" alt="검색" onclick="search_submit('Qsearch',$('#Qsearch .search_url').val());"></a></div>
                        </form>
                    </div>

                    <!-- 로그아웃 -->
                    <div class="logout"><a href="<?=G5_BBS_URL;?>/logout.php">로그아웃</a></div>
                </div>
            </nav>
        </header>

        <!-- GNB 하단 라인 pc -->
        <div id="topLine"></div>

        <!-- GNB 하단 라인 모바일 -->
        <div id="topLine_<?=(($member['mb_type']=="partner" )?("partner_"):(""))?>m"></div>

    </div>
    <!-- // 상단 메뉴 영역 // end-->

    <script type="text/javascript">
        function search_submit(id, url) {

            if($('#'+id+' .ipt_search').val().length < 2) {
                alert("검색어는 두글자 이상 입력하십시오.");
                $('#'+id+' .ipt_search').select();
                $('#'+id+' .ipt_search').focus();
                return false;
            }
            
            $("#"+id).attr("action", url );
            $("#"+id).submit();

            return true;
        }
		var swiper = new Swiper(".menuWrap02 .mySwiper_menu", {
  slidesPerView: 1,
      spaceBetween: 5,
      //pagination: {
      //  el: ".swiper-pagination",
      //  clickable: true,
        //dynamicBullets: true,//추가 또는 삭제 할지도....        
      //},
      //slidesPerView: "auto",
      //autoplay: {
      //  delay: 4000,
      //  disableOnInteraction: false,
      //},
	  navigation: {   // 버튼
          nextEl: ".mainMenu_swiper_navi .swiper-button-next",
          prevEl: ".mainMenu_swiper_navi .swiper-button-prev",
        },
      breakpoints: {
        240: {
          slidesPerView: 2,
          spaceBetween: 1,
        },
        378: {
          slidesPerView: 3,
          spaceBetween: 1,
        },
        540: {
          slidesPerView: 4,
          spaceBetween: 1,
        }
      },
  
});
    </script>

    <?php

      // == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == --
      // LeftMenu 부분 시작
      // == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == --

      // 23.02.22 : 서원 - 맴버이며, 로그인되어 있는 아이디가 있을 경우
      if( $member && is_array($member) && $member['mb_id'] ) {

        // SNB ( Side Navigation Bar ) : Side
        // 23.03.06 :  서원 - 맴버의 타입이 파트너 인지 일반인지 확인 하여 사이트 메뉴를 타입별로 보여 준다.
        if( $member['mb_type'] == "partner" ) {

          // 23.03.06 : 서원 - 파트너일 경우
          include_once("main/_SNB_partner.php");

        } else {
          
          // 23.03.06 : 서원 - 사업소등 파트너 이외~
          include_once("main/_SNB_default.php"); 

        }

      }

      // == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == --
      // LeftMenu 부분 종료
      // == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == -- == --

    ?>
