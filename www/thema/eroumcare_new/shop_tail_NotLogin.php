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
  /* // 파일명 : thema\eroumcare_new\shop_tail_NotLogin.php */
  /* // 파일 설명 : FNB부분이며, 로그인 하지 않았을때 표현되는 하단 부분 */
  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

?>

<?php

  if(!defined("_GNUBOARD_")) exit;

?>

    <!--  ## 하단 footer 영역 ## -->
    <footer id="thkc_footerWrap" class="thkc_footer">
        <!-- footer 메뉴 바 -->
        <div class="f_menuBgWrap">
            <div class="f_menuWrap thkc_container_02">
                <div class="f_logoWrap"><!--footer 로고-->
                    <div class="f_logo"><img src="<?=G5_IMG_URL;?>/new_common/thkc_logo_footer.svg" alt="logo"></div>
                    <ul class="l_f_menu f_menu"><!--footer 메뉴-->
                        <li><a href="<?=G5_BBS_URL;?>/content.php?co_id=company">회사소개</a></li>
                        <li><a href="<?=G5_BBS_URL;?>/content.php?co_id=provision">이용약관</a></li>
                        <li><a href="<?=G5_BBS_URL;?>/content.php?co_id=privacy">개인정보처리방침</a></li>
                    </ul>
                </div>
                <div class="f_Fsns"><!--패밀리 사이트-->
                    <select name="sitelist" id="" class="f_site" onchange= "javascript:go_url(this.options[this.selectedIndex].value);" >
                        <option value selected>Family Site</option>
                        <option value="http://www.thkc.co.kr/">회사 홈페이지</option>
                        <option value="https://eroum.co.kr/">이로움ON</a></option>
						<option value="https://eroum.co.kr/market/">이로움ON 마켓</a></option>
						<option value="https://www.seniortalktalk.com/">시니어톡톡</a></option>
						<option value="https://pro.seniortalktalk.com/">시니어톡톡PRO</a></option>
                    </select>
                    <ul class="f_sns"><!--snsn 버튼-->
                        <li class="btn_sns"><a href="https://pf.kakao.com/_tewXxj" target="_blank"><img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_sns_talk.svg" alt=""></a></li>
                        <li class="btn_sns"><a href="https://www.youtube.com/@e-roum9433" target="_blank"><img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_sns_youtube.svg" alt=""></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- footer 인포 바 -->
        <div class="f_infoBgWrap">
            <div class="f_info_landing thkc_container_02">
                <div class="f_Fsns_d"> <!-- 패밀리 사이트 복제  -->
                    <div class="m_f_Fsns"></div>
                </div>
                <div class="f_info "><!--footer 정보-->
                    <ul>
                        <li>
                            (주)티에이치케이컴퍼니 ㅣ 대표 : 신종호 ㅣ 사업자등록번호 : 617-86-14330 <br class="f_br">
                            <span class="bnt_license"> <a href="javascript:;" onclick="window.open('https://www.ftc.go.kr/bizCommPop.do?wrkr_no=6178614330','communicationViewPopup','width=750,height=700,scrollbars=yes')">사업자정보확인</a></span><br>
                            통신판매신고번호 : 2016-부산금정-0114 | 개인정보보호관리자 : 신종호
                        </li>
                        <li>
                            주소 : 부산광역시 금정구 중앙대로 1815, 5층(구서동, 가루라빌딩)<br>
                            사무소 : 서울시 금천구 서부샛길 606 대성디폴리스 B동 1401호<br>
                            기업부설연구소 : 경상남도 김해시 주촌면 소망길 88 메디컬실용화센터 504호<br>
                            물류센터 : 인천광역시 서구 이든1로 21
                        </li>
                    </ul>
                    <ul>
                        <li>
                            본 쇼핑몰의 콘텐츠 및 모든 정보(UI,UX)는 ㈜티에이치케이컴퍼니에 있으며, 어떠한 이유에서도 무단복제, 도용, 캡처, 스크래핑, 배포 등 저작물 사용 시에는
                            저작권법(제97조5항)에 의해 보호받는 저작물이므로 이를 위반 시에는 법적 처벌을 받을 수 있습니다.
                        </li>
                        <li>
                            Copyright ⓒEroumcare All rights reserved.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <!--  // 하단 footer 영역 end// -->
    <!-- ** Top버튼 ** -->
    <div id="thkc_pageTop">
        <div class="btn_top"><img src="<?=G5_IMG_URL;?>/new_common/thkc_bnt_arrow_top.svg" alt=""></div>
    </div>


    <!-- aos.js (애니메이션 효과) -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>        AOS.init();    </script>

    <!-- 부트스트랩 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    
    <!-- js script -->
    <script src="<?=G5_JS_URL;?>/new_js/thkc_script.js"></script>
