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
  /* // 파일명 : thema\eroumcare_new\shop_tail_default.php */
  /* // 파일 설명 : FNB부분 이며, 로그인 하였을때  공통으로 나오는 부분. */
  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

?>

          </div> <!-- at-content end -->
        </div> <!-- at-container end -->

      </div>



    </div>
    <!-- // 메인 전체 영역 (왼쪽메뉴, 컨텐츠) end// -->   

        <?php
            // 23.03.06 : 서원 - 맴버이며, 로그인되어 있는 아이디가 있며, 맴버타입이 파트너가 아닌 경우
            if( $member && is_array($member) && $member['mb_id'] && ( $member['mb_type'] != "partner") ) {        
        ?>

        <!--  ** 이로움 톡톡 배너 외 Right ** -->
        <div id="bannerRight">
            <!-- 카달로그 배너 -->
            <div class="banner_catalogWrap">
                <div class="c_apply" onclick="window.open('https://forms.gle/5zr5u4aFX4vbjrdT9');">
                    <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_catalog_app.svg" alt="">
                    <span>카달로그<br><b>신청하기</b></span>
                </div>
                <div class="c_downloade" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_카달로그.pdf');">
                    <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_catalog_down.svg" alt="">
                    <span>카달로그<br><b>다운로드</b></span>
                </div>
            </div>
            <!-- 이로움 톡톡 배너 -->
            <div class="banner_eroum" onclick="window.open('https://pf.kakao.com/_tewXxj/chat');">
                <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_btn_banner_talk.png" alt="">
            </div>              
            <!-- 이로움 톡톡 삭제 -->
            <div class="banner_del">
                <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_btn_x_delete.svg" alt="">
            </div>
        </div>
         <!-- ** 이로움 톡톡 배너 외 복제 ** -->
         <div id="bannerRightClone">
            <div class="m_banner_eroum">         
            </div>
        </div>

        <?php
            }
        ?>



    <!--  ## 하단 footer 영역 ## -->
    <footer id="thkc_footerWrap" class="thkc_footer">
        <!-- footer 메뉴 바 -->
        <div class="f_menuBgWrap">
            <div class="f_menuWrap thkc_container">
                <div class="f_logoWrap"><!--footer 로고-->
                    <div class="f_logo"><img src="<?=G5_IMG_URL;?>/new_common/thkc_logo_footer.svg" alt="logo"></div>
                    <ul class="f_menu"><!--footer 메뉴-->
                        <li><a href="<?=G5_BBS_URL;?>/content.php?co_id=company">회사소개</a></li>
                        <li><a href="<?=G5_BBS_URL;?>/content.php?co_id=provision">이용약관</a></li>
                        <li><a href="<?=G5_BBS_URL;?>/content.php?co_id=privacy">개인정보처리방침</a></li>
                    </ul>
                </div>
                <div class="f_Fsns"><!--패밀리 사이트-->
                    <select name="sitelist" id="" class="f_site" onchange= "javascript:go_url(this.options[this.selectedIndex].value);" >
                        <option value selected>Family Site</option>
                        <option value="http://www.thkc.co.kr/">회사 홈페이지</option>
                        <option value="https://eroum.co.kr/market">이로움 마켓</a></option>
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
            <div class="f_infoWrap thkc_container">
                <div class="f_Fsns_d"> <!-- 패밀리 사이트 복제  -->
                    <div class="m_f_Fsns"></div>
                </div>
                <div class="f_info"><!--footer 정보-->
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
                        <li>
                            본 쇼핑몰의 콘텐츠 및 모든 정보(UI,UX)는 ㈜티에이치케이컴퍼니에 있으며, 어떠한 이유에서도 무단복제, 도용, 캡처, 스크래핑, 배포 등 저작물 사용 시에는
                            저작권법(제97조5항)에 의해 보호받는 저작물이므로 이를 위반 시에는 법적 처벌을 받을 수 있습니다.
                        </li>
                        <li>
                            Copyright ⓒEroumcare All righs reserved.
                        </li>
                    </ul>
                </div>
                <div class="f_cs"><!--footer 고객센터-->
                    <ul>
                        <li class="f_cs_title">
                            <div>
                                <h4>이로움 고객센터</h4>
                            </div>
                            <div class="phone">1533-5088</div>
                        </li>
                        <li>운영시간 <div class="f_cs_data">[평일] 08:30~17:30 (점심시간 12시~13시)<br>[주말/공휴일] 휴무</div>
                        </li>
                        <li>기타연락 <div class="f_cs_data">[이메일] <a href="mailto:cs@thkc.co.kr">cs@thkc.co.kr</a><br>[팩스]
                                02-861-9084</div>
                        </li>
                    </ul>
                </div>
                <div class="f_remoteWrap"><!--footer 원격지원-->
                    <div class="f_remote">
                        <div><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_footer_remote.svg" alt=""></div>
                        <div>
                            <h4>원격지원 서비스</h4>
                            <div class="rem_con">전문상담원이 PC화면을 보면서 문제를 해결합니다.</div>
                        </div>
                    </div>
                    <div class="btn_remote" onclick="window.open('https://www.988.co.kr/thkc');">
                        <div>원격지원 서비스 시작</div>
                        <div><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_arrow_next.svg" alt=""></div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--  // 하단 footer 영역 end// -->
    
     <!-- ** Top버튼 ** -->
     <div id="thkc_pageTop">           
        <div class="btn_top"><img src="<?=G5_IMG_URL;?>/new_common/thkc_bnt_arrow_top.svg" alt=""></div>
    </div>


    
    <script src="<?=G5_JS_URL;?>/new_js/thkc_script.js"></script>
    


<script>

  <?php if($member['mb_id']) { ?>
  try {
    if (navigator.userAgent.indexOf("Android") > - 1) {
      window.EroummallApp.requestToken("");
    } else if (navigator.userAgent.indexOf("iPhone") > - 1) {
      window.webkit.messageHandlers.requestToken.postMessage("");
    }
  } catch(ex) {
    // do nothing
  }
  <?php } ?>

  // APP에서 키값을 위해 임의 호출됨.
  function pushKey(token) {
    $.post('/api/register_token.php', { token: token }, 'json').done(function(data) { });
  }
  
</script>


    <!-- <style>
        #thkc_conWrap .at-container .at-content .tab-content .item-explan iframe {
            width: 100%; height: 250px;
        }
    </style> -->