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
    /* // 파일명 :  www/thema/eroumcare_new/main/_SNB_default.php */
    /* // 파일 설명 : 왼쪽 서브 네이게이션(왼쪽 사이드 메뉴) - 일반(기본) (리뉴얼) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 


    $mobile_agent = '/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/';
    $_isMobile = false;

    // preg_match() 함수를 이용해 모바일 기기로 접속하였는지 확인
    if (preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])) {
        $_isMobile = true;
    }


    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    // 대금결제 관련 SQL 처리 부분 시작
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    $_sql_bl = $_sql_bl_history = "";
    // 23.03.27 : 서원 - 모바일이 아니며, Lv가 3/4인 경우만 해당 됨.
    if( (!$_isMobile) && (($member['mb_level']==3) || ($member['mb_level']==4)) ) {

        // 23.01.09 : 서원 - 관리자 설정값 확인.
        //                    해당 값을 가져와서 대금 결제 버튼 활성화에 대한 조건 체크
        $_billing = json_decode( $default['de_paymenet_billing_OnOff'], TRUE );
        if( ($_billing['OnOff'] == "Y") && ( ((int)$_billing['start_dt']<=date('d')) && ((int)$_billing['end_dt']>=date('d')) ) ) {

            // 23.01.09 : 서원 - 해당 사업소에 대금 결제건이 있는지 확인.
            $_sql = ("  SELECT COUNT(bl_id) as cnt, price_total
                        FROM payment_billing_list
                        WHERE mb_id = '" . $member['mb_id'] . "'
                            AND mb_thezone = '" . $member['mb_thezone'] . "'
                            AND billing_yn = 'Y'
                            AND YEAR(create_dt) = YEAR(CURRENT_DATE()) 
                            AND MONTH(create_dt) = MONTH(CURRENT_DATE())
                            AND ( pay_confirm_id IS NULL OR pay_confirm_id = '' )
                            AND ( pay_confirm_receipt_id IS NULL OR pay_confirm_receipt_id = '' )
            ");
            $_sql_bl = sql_fetch($_sql);

            if( ($_sql_bl['cnt'] > 0) && ($_sql_bl['price_total']>0) ) { 
                /* 해당 로그인 사업소에 경제가 미결제 청구금액이 있을 경우 버튼 출력. */
                
                // 23.01.10 : 서원 - 해당 사업소에 결제 이력이 있는지 확인.
                $_sql = ("  SELECT COUNT(bl_id) as cnt
                            FROM payment_billing_list
                            WHERE mb_id = '" . $member['mb_id'] . "'
                                AND mb_thezone = '" . $member['mb_thezone'] . "'
                                AND billing_yn = 'Y'
                                AND ( pay_confirm_id IS NOT NULL OR pay_confirm_id <> '' )
                                AND ( pay_confirm_receipt_id IS NOT NULL OR pay_confirm_receipt_id <> '' )
                            ORDER BY pay_confirm_dt DESC
                ");
                $_sql_bl_history = sql_fetch($_sql);

            }
        }

    }
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    // 대금결제 관련 SQL 처리 부분 종료
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =


    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    // 쿠폰관련 SQL 처리 부분 시작
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
    $cp_count = 0;
    $cp_info=array();
    $res = sql_query(" SELECT `cp_id`, `cp_method`, `cp_minimum`
                FROM `g5_shop_coupon` sCP
                LEFT JOIN `g5_shop_coupon_member` sCPM ON sCP.cp_no = sCPM.cp_no
                WHERE
                    ( sCP.mb_id IN ( '" . $member['mb_id'] . "', '전체회원' ) OR sCPM.mb_id = '" . $member['mb_id'] . "' )
                    AND `cp_start` <= '" . G5_TIME_YMD . "' AND `cp_end` >= '" . G5_TIME_YMD . "'
                GROUP BY sCP.cp_no
    ");
    for($k=0; $cp=sql_fetch_array($res); $k++) {
        if(!is_used_coupon($member['mb_id'], $cp['cp_id'])) { $cp_count++;
            $cp_info[$cp['cp_id']]['cp_method'] = $cp['cp_method'];
            $cp_info[$cp['cp_id']]['cp_minimum'] = $cp['cp_minimum'];
        }
    }
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    // 쿠폰관련 SQL 처리 부분 종료
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

?>


    <style>
        /* 온라인결제 팝업 */
        #OnlineBilling_popup { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:99990; background:rgba(229, 229, 229, 0.5); }
        #OnlineBilling_popup iframe { width:600px; height:580px; max-height: 80%; position:absolute; top: 50%; left: 50%; transform:translate(-50%, -50%); background:white; }
        .OnlineBilling_popup_close { position:absolute; top:15px; right: 15px; color: #000; font-size: 2.5em; cursor:pointer; }

        /* PG사 팝업 최상단 */
        body.bootpay-open .bootpay-payment-background { z-index: 99991; }
		#loading {
		  display: none;
		  background-color: rgba(0,0,0,0.7);
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 100%;
		  height: 100%;
		  z-index : 9999999999999999 !important;
		}

		#loading > div {
		  position: relative;
		  top: 50%;
		  left: 50%;
		  transform: translate(-50%, -50%);
		  text-align: center;
		  
		}

		#loading img {
		  top: 50%;
		  left: 50%;
		  margin-left : -75px; 
		  width: 150px;
		  position: relative;
		}

		#loading p {
		  color: #fff;
		  position: relative;
		  top: -25px;
		}
    </style>
	<!--로딩 중 -->
	<div id="loading" style="display: none">
	  <div>
		<img src="/img/loading_apple.gif" class="img-responsive" >
		<p style="margin-top:40px;font-size:30px;line-height:40px;">정보를 불러오고 있습니다.<br>잠시만 기다려주세요.</p>
	  </div>
	</div>
<script>
	function loading_onoff(a){
		if(a == "on" ){
			$('body').css('overflow-y', 'hidden');
			$('#loading').show();
		}else{
			$('body').css('overflow-y', 'scroll');
			$('#loading').hide(); 
		}
	}
	window.onpageshow = function(event){
		if(event.persisted || (window.performance && window.performance.navigation.type == 2)){
			loading_onoff('off');
		}
	}

</script>
    <!-- ## 메인 전체 영역 (왼쪽메뉴, gap,  컨텐츠) ## -->
    <div id="thkc_mainWrap" class="thkc_container">

        <!-- ** PC 왼쪽 메뉴 "w302px" ** -->
        <div id="thkc_asideWrap">
            <aside class="thkc_aside">
                <!-- 회원정보 -->
                <div id="memberWrap">
                    
                    <?php if($member['mb_level'] >= 9) { ?>
                    <p class="admPDA_menu"><a href="/shop/release_orderlist.php" class="btn_orderlist">관리자 주문내역 관리</a></p>
                    <?php if(check_auth($member['mb_id'], '400480', 'w')) { ?>
                    <p class="admPDA_menu"><a href="/shop/release_purchaseorderlist.php" class="btn_orderlist purchaseorderlist">관리자 구매발주 관리</a></p>
                    <?php }
                    if(check_auth($member['mb_id'], '400480', 'w')) { ?>
                    <p class="admPDA_menu"><a href="/shop/release_stocklist.php" class="btn_orderlist stocklist">보유재고 관리</a></p>
                    <?php } } ?>

                    <div class="thkc_memberTitle">
                        <p class="memTitle"><!-- 회원이름 -->
                            <?=$member["mb_name"]?><span class="dir"> 님</span>
                            <?php if($member['admin'] || $is_samhwa_admin) { ?>
                            <span class="admin">( <a href="<?php echo G5_ADMIN_URL;?>/">관리메뉴</a> )</span>
                            <?php } ?>
                        </p>
                        <p class="member_modi">
                            <a href="#" onclick="location.href='<?=G5_BBS_URL?>/member_confirm.php?url=member_info_newform.php'">회원정보 수정</a>
                        </p>
                    </div>


                    <!-- 쿠폰, 장바구니, 간편메뉴, 고객센터 -->
                    <div class="thkc_leftMenuWrap">

                        <!-- 쿠폰, 장바구니, (대금결제 <> 주문보기) -->
                        <div class="memInfoWrap">
                            

                            <!-- 쿠폰 시작 -->
                            <?php if($cp_count > 0) { ?>
                            <div class="mem_shipWrap f_s14">
                                <a href="#" onclick="window.open('<?=$at_href['coupon']?>','couponViewPopup','width=600,height=400,scrollbars=yes')">
                                    <p>쿠폰</p>
                                    <p><span class="mInfo"><?=$cp_count?></span><span class="mUnit">장</span></p>
                                </a>
                            </div>
                            <?php } ?>
                            <!-- 쿠폰 종료 -->


                            <!-- 장바구니 시작 -->
                            <?php if(get_boxcart_datas_count() > 0) { ?>
                            <div class="mem_shipWrap f_s14">
                                <a href="/shop/cart.php">
                                    <p><?=($_SESSION['recipient']['penId']=="")?"사업소":$_SESSION['recipient']['penNm']."님";?> 장바구니</p>
                                    <p><span class="mInfo"><?=get_boxcart_datas_count(); ?></span><span class="mUnit">건</span></p>
                                </a>
                            </div>
                            <?php } ?>
                            <!-- 장바구니 종료 -->


                            
                            <?php
                                // 23.04.12 : 서원 - 대금 결제 부분 사이드 메뉴부분에서 팝업 기능과 버튼 분리 (버튼 시작 부분)
                                if( (!$_isMobile) && (($member['mb_level']==3) || ($member['mb_level']==4)) ) { $_billing = json_decode( $default['de_paymenet_billing_OnOff'], TRUE );
                                    if( ($_billing['OnOff'] == "Y") && ( ((int)$_billing['start_dt']<=date('d')) && ((int)$_billing['end_dt']>=date('d')) ) ) {
                            ?>

                            <!-- 대금결제 시작 -->
                            <div class="thkc_btnBoxWrap">
                                <?php   if( is_array($_sql_bl) && ($_sql_bl['cnt'] > 0) && ($_sql_bl['price_total']>0) ) { /* 해당 로그인 사업소에 경제가 미결제 청구금액이 있을 경우 버튼 출력. */ ?>
                                    <a href='#' class='event_noti btn_OnlineBilling' onClick=''><p>대금 결제하기</p>
                                        <span><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_arrow_next.svg" alt="아이콘"></span>
                                    </a>
                                <?php
                                        } else { 
                                            if( is_array($_sql_bl_history) && $_sql_bl_history['cnt'] > 0 ) {
                                ?>
                                    <a href='#' class='event_noti btn_OnlineBilling' onClick=''><p>결제 내역 확인하기</p>
                                        <span><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_arrow_next.svg" alt="아이콘"></span>
                                    </a>
                                <?php 
                                            } 
                                        }
                                ?>
                            </div>
                            <!-- 대금결제 종료 -->

                            <?php // 23.04.12 : 서원 - 대금 결제 부분 사이드 메뉴부분에서 팝업 기능과 버튼 분리 (버튼 종료 부분)
                                    }
                                }
                            ?>

                        </div>



                        <!-- 간편메뉴 -->
                        <div class="simpleTabe">
                            <table>
                                <tr>
                                    <td class="br btn_simple">
                                        <a href="#none" onclick="location.href='/shop/check_my_ltcare_info.php'">
                                            <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_easy01.svg" alt="간편조회">
                                            <p class="simTitle">간편<span class="f_bold700">조회</span></p>
                                        </a>
                                    </td>
                                    <td class="bb btn_simple">
                                        <a href="#none" onclick="location.href='/shop/item_msg_list.php'">
                                            <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_easy02.svg" alt="간편제안">
                                            <p class="simTitle">간편<span class="f_bold700">제안</span></p>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bt btn_simple">
                                        <a href="#none" onclick="location.href='/shop/simple_order.php'">
                                            <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_easy03.svg" alt="간편주문">
                                            <p class="simTitle">간편<span class="f_bold700">주문</span></p>
                                        </a>
                                    </td>
                                    <td class="bl btn_simple">
                                        <a href="#none" onclick="location.href='/shop/simple_eform.php'">
                                            <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_easy04.svg" alt="간편계약">
                                            <p class="simTitle">간편<span class="f_bold700">계약</span></p>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- 이로움 고객센터 전화번호 -->
                        <div class="left_csWrap">
                            <div class="csTitle">이로움 고객센터</div>
                            <div class="csCall">1533-5088</div>
                        </div>

                    </div>
                    <!-- 쿠폰, 장바구니, 간편메뉴, 고객센터 end -->


                    <!-- 사업소 운영관리 -->
                    <div class="mgeWrap">
                        <h3 class="mgTitle y_bar">사업소 운영관리</h3>
                        <hr>
                        <div class="office_menu">
                            <ul>

                                <?php
                                    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  
                                    // 23.03.07 : 서원 - 이로움ON 에서 발생한 주문 정보에 대한 페이지 링크
                                    //                    관리자 및 특정 사업소 아이디 하드 코딩으로 접근.
                                    //                    추후 해당 부분 제거 필요 또는 특정 사업소 추가시 아이디 추가 필요.
                                    // H/C 파일 - \www\thema\eroumcare\shop.head.php
                                    //          - \www\skin\apms\order\new_basic\orderinquiry.skin.php
                                    //          - \www\shop\electronic_manage_new.php
                                    if( 
                                        ($member['mb_level'] >= 9 ) || ($member['mb_id'] == "ariamart") || ($member['mb_id'] == "hula1202")
                                    ) {
                                    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==
                                ?>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage08.svg" alt="복지용구 신청관리"><a href="/shop/eroumon_order_list.php">복지용구 신청관리</a></li>
                                <?php } ?>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage01.svg" alt="주문/배송 관리"><a href="/shop/orderinquiry.php">주문/배송 관리</a></li>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage02.svg" alt="수급자 관리"><a href="/shop/my_recipient_list.php" onclick="loading_onoff('on')">수급자 관리</a></li>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage03.svg" alt="계약서 관리"><a href="/shop/electronic_manage_new.php">계약서 관리</a></li>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage04.svg" alt="청구 내역 관리"><a href="/shop/claim_manage.php">청구 내역 관리</a></li>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage05.svg" alt="보유 급여상품 관리"><a href="/shop/sales_Inventory.php">보유 급여상품 관리</a></li>
                            </ul>
                        </div>
                    </div>


                    <!-- 공지사항 -->
                    <div class="noticeWrap">
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


                    <!-- 사업소 운영지원 -->
                    <div class="mgeWrap">
                        <h3 class="mgTitle y_bar">사업소 운영지원</h3>
                        <hr>
                        <div class="office_menu">
                            <ul>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage06.svg" alt="사업소 운영 자료실"><a href="<?=G5_BBS_URL;?>/board.php?bo_table=care_files">사업소 운영 자료실</a></li>
                                <li><img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_manage07.svg" alt="복지용구 뉴스"><a href="<?=G5_BBS_URL;?>/board.php?bo_table=care_news">복지용구 뉴스</a></li>
                            </ul>
                        </div>
                    </div>


                    <!-- 이로움 고객센터 -->
                    <div class="mgeWrap">
                        <h3 class="mgTitle y_bar">이로움 고객센터</h3>
                        <hr>
                        <div class="office_menu">
                            <ul>
                                <li><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_faq.svg" alt="자주하는 질문"><a href="<?=G5_BBS_URL;?>/board.php?bo_table=faq">자주하는 질문</a></li>
                                <li><img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_11.svg" alt="1:1문의"><a href="<?=G5_BBS_URL;?>/qalist.php">1:1문의</a></li>
                            </ul>
                        </div>
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
        

        
        <?php
            // 23.04.12 : 서원 - 대금 결제 부분 사이드 메뉴부분에서 팝업 기능과 버튼 분리 (팝업창관련 시작 부분)
            if( (!$_isMobile) && (($member['mb_level']==3) || ($member['mb_level']==4)) ) { $_billing = json_decode( $default['de_paymenet_billing_OnOff'], TRUE );
                if( ($_billing['OnOff'] == "Y") && ( ((int)$_billing['start_dt']<=date('d')) && ((int)$_billing['end_dt']>=date('d')) ) ) {
        ?>
        <!-- 대금결제 팝업관련 시작 -->
        <!-- 온라인결제 팝업창 스크립트 -->
        <script src="<?=G5_JS_URL ?>/payment_eroum.js?ver=<?=APMS_SVER; ?>" type="application/javascript"></script>                            
        <!-- 온라인결제 팝업 -->
        <div id="OnlineBilling_popup"><div class="OnlineBilling_popup_close"><i class="fa fa-times"></i></div><iframe id="OnlineBilling_iframe"></iframe></div>
        <!-- 대금결제 팝업관련 종료 -->
        <?php
            // 23.04.12 : 서원 - 대금 결제 부분 사이드 메뉴부분에서 팝업 기능과 버튼 분리 (팝업창관련 종료 부분)
                }
            }
        ?>


        <!--  ** gap w38px ** -->
        <div id="thkc_gap"></div>

        <!--  ** 메인 컨텐츠  "w1060px" ** -->
        <div id="thkc_conWrap">

            <div class="at-container"> <!-- at-container start -->
                <div class="at-content"> <!-- at-content start -->
