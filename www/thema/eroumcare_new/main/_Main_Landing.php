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
    /* // 파일명 :  \www\thema\eroumcare_new\main\_Main_Landing.php */
    /* // 파일 설명 : (리뉴얼) - 랜딩 페이지 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    if(!defined("_GNUBOARD_")) exit;

?>


    <div id="thkc_landing">
        <!-- login -->
        <nav class="container-sm d-flex flex-column align-items-center pb-5 mb_70 pt-3 pt-md-5">
            <div class="land_title_01 pb-1 pb-md-2">이로움 플랫폼을 만나면 <span class="f_bold">관리가 쉬워진다!</span></div>
            <h1 class="land_title_02">장기요양기관 <br class="thkc_land_br">
                <spna class="f_bold700">통합관리 플랫폼</spna>
            </h1>
            <div class="d-flex justify-content-center gap-2 gap-sm-3">
                <a href="<?=G5_URL;?>/bbs/login.php" class="text-white">
                    <div class="land_btn thkc_bg01"> 로그인</div>
                </a>
                <a href="<?=G5_URL;?>/bbs/register.php" class="link-dark">
                    <div class="land_btn border border-dark">회원가입</div>
                </a>
            </div>
        </nav>

        <!-- visual -->
        <section class="l_MainWrap" data-aos="fade-up" data-aos-duration="3000">
            <div class="l_MainImg">
                <img src="<?=G5_IMG_URL;?>/new_landing/thkc_lanMain.png" class="img-fluid l-hover" alt="메인이미지">
            </div>
            <div class="l_MainTitle_01 l_MainAny" data-aos="fade-up">
                <span class="l_MainTitle">쉽고, 편리합니다</span>
                <p class="l_MainTitle02">자동화 관리 서비스</p>
            </div>
            <div class="l_MainTitle_02 l_MainAny02">
                <span class="l_MainTitle">혜택을 나눕니다</span>
                <p class="l_MainTitle02">비용 절감, 업무 효율 극대화</p>
            </div>
            <div class="l_MainTitle_03 l_MainAny02">
                <span class="l_MainTitle">새롭습니다</span>
                <p class="l_MainTitle02">업계 최초, 국내 유일</p>
            </div>
            <div class="l_MainTitle_04 l_MainAny">
                <span class="l_MainTitle">사람을 먼저 생각합니다</span>
            </div>
        </section>

        <!-- sec01 이로움엔 있다 -->
        <section class="container-sm text-center py-5 mt-5">
            <div class="bg-primary-sm mb_70" data-aos="fade-up" data-aos-duration="2000">
                <h1 class="fw-light display-5 p-3">이로움엔 <span class="fw-bold thkc_mark">있습니다!</span></h1>
                <h4 class="land_title_03 f_bold100">이로움 플랫폼만 제공하는 <span class="f_bold700">통합관리 시스템</span></h4>
                <h5 class="land_title_04 f_bold100">업계 최초, 국내 유일한 플랫폼은 이유가 있습니다.</h5>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4 py-5 px-3">
                <div class="col" data-aos="flip-left" data-aos-easing="ease-out-cubic" data-aos-duration="2000">
                    <div class="card thkc_card01">
                        <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_be01.svg" class="card-img-top img-fluid p-3 p-lg-4"
                            alt="card-grid-image">
                        <div class="card-body px-3 px-md-4">
                            <h4 class="card-title fw-normal "><span class="fw-bold">통합관리</span> 서비스 제공</h4>
                            <p class="card-text pb-4 land_pb">전국 1,100개 사업소가 사용하는 복지용구 전문 플랫폼입니다.</p>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="flip-left" data-aos-easing="ease-out-cubic" data-aos-duration="2000">
                    <div class="card thkc_card01">
                        <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_be02.svg" class="card-img-top img-fluid p-3 p-lg-4"
                            alt="card-grid-image">
                        <div class="card-body px-3 px-md-4">
                            <h4 class="card-title">최다 <span class="fw-bold">복지용구</span> 보유</h4>
                            <p class="card-text pb-4 land_pb">774개 복지용품을 보유하고 있어 한번에 주문이 가능합니다.</p>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="flip-left" data-aos-easing="ease-out-cubic" data-aos-duration="2000">
                    <div class="card thkc_card01">
                        <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_be03.svg" class="card-img-top img-fluid p-3 p-lg-4"
                            alt="card-grid-image">
                        <div class="card-body px-3 px-md-1">
                            <h4 class="card-title">다양한 <span class="fw-bold">마케팅 활동</span></h4>
                            <p class="card-text pb-4 land_pb">이로움 가입 회원들에게 이벤트 및 프로모션 활동을 통해 다양한 혜택을 무료로 제공하고 있습니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- sec02 이로움은 된다 -->
        <section class="bg-light">
            <div class="container-sm py-lg-5 py-0">
                <div class="row py-5">
                    <div class="col-lg-6 col-12 order-2 order-lg-1">
                        <!-- 4개 카드 -->
                        <div class="row row-cols-1 row-cols-md-2 g-4 m-1 m-md-0">
                            <div class="col">
                                <div class="card card_become" data-aos="flip-left" data-aos-easing="ease-out-cubic" data-aos-duration="2000">
                                    <div class="card-body">
                                        <h4 class="card-title">간편<span class="fw-bold">조회</span></h4>
                                        <p class="card-text text-lineH">번거로운 수급자 조회를 빠르고 편하게 바꿨습니다.</p>
                                    </div>
                                    <div class="col-6 col-md-6 offset-6">
                                        <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_become01.svg" class="card-img-top"
                                            alt="간편조회">
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card_become" data-aos="flip-left" data-aos-easing="ease-out-cubic" data-aos-duration="2000">
                                    <div class="card-body">
                                        <h4 class="card-title">간편<span class="fw-bold">제안</span></h4>
                                        <p class="card-text text-lineH">수급자별 맞춤 상품을 쉽고 편하게 전달할 수 있습니다.</p>
                                    </div>
                                    <div class="col-6 col-md-6 offset-6">
                                        <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_become02.svg" class="card-img-top"
                                            alt="간편제안">
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card_become" data-aos="flip-left" data-aos-easing="ease-out-cubic" data-aos-duration="2000">
                                    <div class="card-body">
                                        <h4 class="card-title">간편<span class="fw-bold">주문</span></h4>
                                        <p class="card-text text-lineH">자동 바코드 기능으로 쉽게 주문하실 수 있습니다.</p>
                                    </div>
                                    <div class="col-6 col-md-6 offset-6">
                                        <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_become03.svg" class="card-img-top"
                                            alt="간편주문">
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card_become" data-aos="flip-left" data-aos-easing="ease-out-cubic" data-aos-duration="2000">
                                    <div class="card-body">
                                        <h4 class="card-title">간편<span class="fw-bold">계약</span></h4>
                                        <p class="card-text text-lineH">건강보험공단 양식으로 전자계약이 가능합니다.</p>
                                    </div>
                                    <div class="col-6 col-md-6 offset-6">
                                        <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_become04.svg" class="card-img-top"
                                            alt="간편계약">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12 order-1 order-lg-2 d-flex flex-column justify-content-center ">
                        <div class="p-0 px-sm-5 py-5 text-center text-sm-start" data-aos="fade-up"
                            data-aos-duration="2000">
                            <h1 class="fw-light display-5">이로움은 <span class="fw-bold thkc_mark">됩니다.</span></h1>
                            <h4 class="land_title_03 f_bold100">이로움 플랫폼을 <span class="f_bold400">사용해야 되는 이유</span></h4>
                            <h5 class="land_title_04 fw-light">수급자 관리가 쉽고, 편하게 됩니다.</h5>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- sec03 이로움만 한다 -->
        <section class="container-sm py-lg-5 gy-5">
            <div class="row py-5">
                <!-- 타이틀 -->
                <div class="col-lg-6 col-12 d-flex flex-column justify-content-center ">
                    <div class="p-0 px-sm-5 py-5 text-center text-sm-start" data-aos="fade-up" data-aos-duration="2000">
                        <h1 class="fw-light display-5">이로움만 <span class="fw-bold thkc_mark">합니다.</span></h1>
                        <h4 class="land_title_03 f_bold100">모든 참여자에게 <span class="f_bold400">혜택을 나눕니다.</span></h4>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <!-- 4개 카드 -->
                    <div class="card mb-3 card_do land_bg01 mb-4" style="max-width: 540px;" data-aos="flip-up"
                        data-aos-duration="2000">
                        <div class="row g-0 d-flex align-items-center">
                            <div class="col-4">
                                <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_do01.svg" class="img-fluid rounded-start"
                                    alt="복지용구 사업소">
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <p class="card-text">효율적인 시스템 운영 관리와<br class="thkc_land_br02"> 비용 절감이 필요한</p>
                                    <h4 class="card-title">복지용구 <span class="fw-bold">사업소</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3 card_do land_bg02 mb-4" style="max-width: 540px;" data-aos="flip-up"
                        data-aos-duration="2000">
                        <div class="row g-0 align-items-center">
                            <div class="col-4">
                                <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_do02.svg" class="img-fluid rounded-start"
                                    alt="복지용구 공급업체">
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <p class="card-text">한정된 마케팅과 영업 활동으로<br class="thkc_land_br02">
                                        안정적인 판매처가 필요한</p>
                                    <h4 class="card-title">복지용구 <span class="fw-bold">공급업체</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3 card_do land_bg03" style="max-width: 540px;" data-aos="flip-up"
                        data-aos-duration="2000">
                        <div class="row g-0 align-items-center">
                            <div class="col-4">
                                <img src="<?=G5_IMG_URL;?>/new_landing/thkc_banner_do03.svg" class="img-fluid rounded-start"
                                    alt="제휴사 공식 파트너사">
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <p class="card-text text-letterS">설치, 소독, 렌탈, 카드결제 등<br class="thkc_land_br02">
                                        핵심서비스 연결을 통해 서비스 확대가 필요한</p>
                                    <h4 class="card-title l_title_do01"><span class="fw-bold">제휴사 & 공식 파트너사</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- sec04 이로움 슬로건 -->
        <section class="thkc_land_contact">
            <div class="container-sm">
                <div class="row">
                    <!-- div.col-md-6 -->
                    <div class="col-md-6 p-5 order-1 order-md-2">
                        <div class="land_bimg_p"></div>
                    </div>
                    <!-- div.col-md-6 -->
                    <div class="col-md-6 d-flex flex-column justify-content-center text-md-center text-start px-5">
                        <h4 class="pb-2 f_bold100 fs-5 fs-md-4" data-aos="fade-down"
                            data-aos-duration="2000"><span class="l_MainTitleB_02 ">모든 어르신과 보호자, 장기요양 기관 등 <br
                                    class="thkc_land_br02">
                                모든 사용자가 혜택을 누릴 수 있는 유일한</span>
                            <h1 class="f_bold400" data-aos="fade-up" data-aos-duration="2000"><span
                                    class="l_MainTitleB_01">시니어 라이프 케어 플랫폼</span><br>
                                <span class="f_bold700">“이로움”</span>
                            </h1>
                        </h4>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- sec05 고객지원 -->
        <section class="thkc_land_section py-5" data-aos="fade-up" data-aos-duration="2000">
            <div class="cs_infoWrap">
                <div class="cs_phone">
                    <img src="<?=G5_IMG_URL;?>/new_common/thkc_logo_mark.svg" alt="logo">
                    <h4 class="f_bold100 py-1">이로움 고객센터</h4>
                    <span class="phone">1533-5088</span>
                </div>
                <div class="f_s14">
                    <ul class="cs_m_info">
                        <li class="cs_infoTitle">운영시간</li>
                        <li>[평일] 08:30~17:30 <br class="thkc_land_br">(점심시간 12시~13시) /<br class="thkc_land_br"> [주말/공휴일]
                            휴무</li>
                    </ul>
                    <ul class="cs_m_info">
                        <li><span class="cs_infoTitle">이메일</span>
                            <a href="mailto:cs@thkc.co.kr">cs@thkc.co.kr</a>
                        </li>
                        <li class="null"> / </li>
                        <li><span class="cs_infoTitle">팩스</span> 02-861-9084</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>