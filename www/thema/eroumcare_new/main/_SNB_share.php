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
    /* // 파일명 :  www/thema/eroumcare_new/main/_SNB_footer.php */
    /* // 파일 설명 : 왼쪽 서브 네이게이션 하단 공통 부분 (리뉴얼) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

?>                    
                    
                    <!-- 원격지원 서비스 -->
                    <div class="remoteWrap">
                        <img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_lnb_remote.svg" alt="원격지원 서비스">
                        <div>
                            <h4>원격지원 서비스</h4>
                            <p class="rem_con">전문상담원이 PC화면을 보면서 문제를 해결합니다.</p>
                        </div>
                    </div>
                    <div class="btn_remote" onclick="window.open('https://www.988.co.kr/thkc');">원격지원 서비스 시작</div>


                    <!-- pc다운로드 바로가기 -->
                    <div class="downlodeWrap">
                        <ul>
                            <li><a href="javascript:void(0);" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_카달로그.pdf');">이로움 카달로그 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt="이로움 카달로그"></a></li>
                            <li><a href="javascript:void(0);" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_통장사본.jpg');">통장사본 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt="통장사본"></a></li>
                            <li><a href="javascript:void(0);" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_사업자등록증.pdf');">사업자 등록증 <img src="<?=G5_IMG_URL;?>/new_common/thkc_btn_download.svg" alt="사업자 등록증"></a></li>
                        </ul>
                    </div>

                    <?php if( $member['mb_type'] != "partner" ) { ?>
                    <!-- Mobile 다운로드 바로가기 -->
                    <div class="simpleTabe c_down">
                        <table>
                            <tr>
                                <td class="br br_c" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_카달로그.pdf');"><p class="simTitle">카달로그<br><span class="f_bold700">다운로드</span></p></td>
                                <td class="bb br_c" onclick="window.open('https://forms.gle/5zr5u4aFX4vbjrdT9');"><p class="simTitle">카달로그<br><span class="f_bold700">신청하기</span></p></td>
                            </tr>
                            <tr>
                                <td class="bt br_c" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_통장사본.jpg');"><p class="simTitle">통장사본<br><span class="f_bold700">다운로드</span></p></td>
                                <td class="bl br_c" onclick="window.open('<?=G5_DATA_URL;?>/file/THKC(eroumcare)_사업자등록증.pdf');"><p class="simTitle">사업자등록증<br><span class="f_bold700">신청하기</span></p></td>
                            </tr>
                        </table>
                    </div>
                    <?php } ?>

                    <!-- Mobile 로그아웃 -->
                    <div class="m_logout"><a href="<?=G5_BBS_URL;?>/logout.php">로그아웃</a></div>