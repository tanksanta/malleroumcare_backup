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
    /* //  * (주)티에이치케이컴퍼 & 이로움Care & 이로움ON - [ THKcompany & EroumCare & EroumON ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! & EroumON 1:1 Matching Service Ver:1.0 */
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
    /* // 파일명 : /www/shop/eroumon_members_conslt_view.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 발생한 고객(맴버)의 상담관련 업무를 처리하는 뷰 페이지. */
    /*                회원이 인정등급 테스트를 진행하고 1:1 상담 신청을 했을때, THKC에서 배정해준 사업소에 대한 수락,거부,완료 처리를 할 수있는 페이지. */
    /*                */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    /* !!중요!! */
    /*
        해당 페이지의 내용을 제공하기위해서는 data폴더내에 있는 dbconfig.도메인.php에 관련된 정보를 반드시 기입해야하며,
        해당 파일은 서버의 민감정보가 포함됨에 따라 Git에서 관리하지 않으며, 별도 관리함으로 수기 입력을 해야 한다.
        
        <code>
        if( $_SERVER['HTTP_HOST']==="eroumcare.com" || $_SERVER['HTTP_HOST']=="www.eroumcare.com" ) {
            define('EROUMON_HOST', '상용(운영) 호스트명+포트');
            define('EROUMON_USER', '상용(운영) DB 아이디');
            define('EROUMON_PASSWORD', '상용(운영) DB 비밀번호');
            define('EROUMON_DB', '상용(운영) 사용 할 DB ');
            define('EROUMON_SET_MODE', true);
        } else {
            define('EROUMON_HOST', '테스트 호스트명+포트');
            define('EROUMON_USER', '테스트 DB 아이디');
            define('EROUMON_PASSWORD', '테스트 DB 비밀번호');
            define('EROUMON_DB', '테스트 사용 할 DB ');
            define('EROUMON_SET_MODE', true);
        }
        </code>

    */

    /*
        // 이로움ON 상태값 == == == == == == == == == == == == == == == == == ==
        MC_ST = CS01 : 접수
        MC_ST = CS02 : 배정
        MC_ST = CS03 : 상담자 취소
        MC_ST = CS04 : 사업소 취소
        MC_ST = CS05 : 진행
        MC_ST = CS06 : 완료
        MC_ST = CS07 : 재접수
        MC_ST = CS08 : 재배정
        MC_ST = CS09 : THKC 취소
        
        // 이로움Care 상태값 == == == == == == == == == == == == == == == == == ==
        MCR_ST = CS01 : 상담 신청 접수
        MCR_ST = CS02 : 상담 신청 접수
        MCR_ST = CS03 : 상담 취소(고객)
        MCR_ST = CS04 : 상담 취소(사업소)
        MCR_ST = CS05 : 상담 진행 중
        MCR_ST = CS06 : 상담 완료
        MCR_ST = CS07 : 상담 신청 접수
        MCR_ST = CS08 : 상담 신청 접수
        MCR_ST = CS09 : 상담 취소(THKC)
    */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');

    if(!$member['mb_id'])
        alert('먼저 로그인하세요.',G5_URL.'/bbs/login.php');

    @include_once(G5_LIB_PATH.'/apms.thema.lib.php');
    @include_once($order_skin_path.'/config.skin.php');

    $g5['title'] = '고객상담 상세보기';

    include_once('./_head.php');

    // 테스트용... 사업자 번호 임의수정
    //$member['mb_giup_bnum'] = '123-45-67892';
    //$member['mb_giup_bnum'] = '111-22-33333';

    $qstr = "";
    if( $_SERVER['REQUEST_METHOD']==="GET" ) {
        // 페이징 되는 주소 파라미터
        $qstr = ("fr_date={$fr_date}&to_date=".substr($to_date,0,10)."&srchConsltSttus={$srchConsltSttus}&sel_field={$sel_field}&page={$page}&list_num={$list_num}");
    } else if( $_SERVER['REQUEST_METHOD']==="POST" ) {
        // POST로 넘겨 받은 view진입전 페이지 값 처리.
        $qstr = $_qstr;
    }

    $_consltID = clean_xss_attributes( clean_xss_tags( get_search_string( $_GET['consltID'] ) ) );
    if( !$_consltID ) {
        alert("[이로움ON] 1:1 상담 정보를 찾을 수 없습니다. (consltID)", G5_SHOP_URL . "/eroumon_members_conslt_list.php".(($qstr)?"?".$qstr:""));
    }

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // POST 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    // 해당 페이지에서 REQUEST 이벤트가 발생하고, POST일 경우 데이터 업데이트를 위한 코드 동작.
    if( ( $_SERVER['REQUEST_URI'] === ($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) ) && ($_SERVER['REQUEST_METHOD']==="POST") ) {

        // POST값으로 넘겨 받은 데이터에 대한 SQL인젝션 쿼리 부분 처리.
        $_MC_cON = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['MC_cON'] ) ) );
        $_MCR_cON = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['MCR_cON'] ) ) );
        $_MCR_ID = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['MCR_ID'] ) ) );
        $_MCR_STTUS_CD = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['MCR_STTUS_CD'] ) ) );
        $_MCR_TEXT = clean_xss_attributes( clean_xss_tags( $_POST['MCR_TEXT'] ) );

        if( $eroumon_connect_db ) { 
            
            // 프로시저 : CALL `PROC_EROUMCARE_CONSLT`('모드','회원사업자번호', '검색시작일', '검색종료일','페이지포인터시작','리스트수량','검색조건');
            $sql = (" CALL `PROC_EROUMCARE_CONSLT`('view','{$member['mb_giup_bnum']}','','','','','{$_consltID}'); ");
            $sql_result = "";
            $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
            
            if( ($sql_result['MCR_ST']!=$_MCR_STTUS_CD) && ($sql_result['CONSLT_NO']===$_MC_cON) && ($sql_result['BPLC_CONSLT_NO']===$_MCR_cON)  ) {

                $RGTR = $sql_result['RGTR']; // 상담신청 회원명
				$MBR_TELNO = $sql_result['MBR_TELNO'];//수급자 연락처

                // 프로시저 : CALL `PROC_EROUMCARE_CONSLT_UPDATE`('모드', 상담신청NO, 상담배정NO, '변결될상태값', '완료또는 거부시 사유또는 내용', '배정 당시 사업소아이디');                        
                $sql = (" CALL `PROC_EROUMCARE_CONSLT_UPDATE`('BPLC', {$_MC_cON}, {$_MCR_cON}, '{$_MCR_STTUS_CD}', '{$_MCR_TEXT}', '{$_MCR_ID}'); ");
                $sql_result = "";
                $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
                

                //======================CS04:상담거절, CS06:상담완료 시 알림톡 발송===============================================================================================
				if($_MCR_STTUS_CD == "CS04" || $_MCR_STTUS_CD == "CS06"){
					if($_MCR_STTUS_CD == "CS06"){//상담완료 시 알림톡
						$alimtalk_contents = $RGTR."님, 상담이 완료되었습니다.\n상담한 장기요양기관이 마음에 드실 경우 이로움ON에서 상담기관 추천하기 및 관심설정이 가능합니다.\n\n다른 장기요양기관과의 재상담을 원하실 경우\n\n상담 내역 관리에서 재상담 신청이 가능한 점 참고 부탁드립니다.";
						$result2 = send_alim_talk2('CONSLT_REQUEST_'.$MBR_TELNO, $MBR_TELNO, 'ON_00001', $alimtalk_contents, array(
							'button' => [
							  array(
								'name' => '◼︎ 상담내역 바로가기',
								'type' => 'WL',
								'url_mobile' => 'https://eroum.co.kr/membership/conslt/appl/list',
								'url_pc' => 'https://eroum.co.kr/membership/conslt/appl/list'
							  )
							]
						  ),'','1:1상담 진행 완료','2');//내용은 템플릿과 동일 해야 함 
					}else{//상담거절 시 알림톡
						$alimtalk_contents = $RGTR."님, 요청하신 1:1 상담이 취소되었습니다.\n\n◼︎ 상담 취소일 : ".date("Y-m-d")."\n\n상담을 원하시는 경우 이로움ON에서 다시 상담을 요청해 주세요.";
						$result2 = send_alim_talk2('CONSLT_CANCEL_'.$MBR_TELNO, $MBR_TELNO, 'ON_00002', $alimtalk_contents, array(
							'button' => [
							  array(
								'name' => '◼︎ 요양정보 간편조회',
								'type' => 'WL',
								'url_mobile' => 'https://eroum.co.kr/main/recipter/list',
								'url_pc' => 'https://eroum.co.kr/main/recipter/list'
							  ),
							  array(
								'name' => '◼︎ 인정 등급 예상 테스트',
								'type' => 'WL',
								'url_mobile' => 'https://eroum.co.kr/main/cntnts/test',
								'url_pc' => 'https://eroum.co.kr/main/cntnts/test'
							  )
							]
						  ),'','상담 취소 안내','2');//내용은 템플릿과 동일 해야 함 
					}
				}
                //======================CS04:상담거절, CS06:상담완료 시 알림톡 발송===============================================================================================

            }
        }
    }

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // POST 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==


    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    if( $eroumon_connect_db ) {
        
        $sql = (" CALL `PROC_EROUMCARE_CONSLT`('view','{$member['mb_giup_bnum']}','','','','','{$_consltID}'); ");
        $sql_result = "";
        $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);

    }
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==


    $_conslt_st = false;
    if( $sql_result['CUR_CONSLT_RESULT_NO'] !== $sql_result['BPLC_CONSLT_NO'] ) { $_conslt_st = true; }
    else if( $sql_result['MCR_ST']==="CS01" 
                || $sql_result['MCR_ST']==="CS02" 
                || $sql_result['MCR_ST']==="CS03" 
                || $sql_result['MCR_ST']==="CS04" 
                || $sql_result['MCR_ST']==="CS07" 
                || $sql_result['MCR_ST']==="CS08" 
                || $sql_result['MCR_ST']==="CS09" ) { $_conslt_st = true; }
    else if( $sql_result['MCR_ST']==="CS06" ) {

         // 23.11.01 : 서원 - 상담완료 산태에서 상담신청건의 상태값이 재신청일 일 경우 마스킹 처리
         if($sql_result['MC_ST']==="CS07") { $_conslt_st = true; }

        // 상담완료 이후 48시간 초과시 화면 마스킹
        $currentTime = strtotime($sql_result['CONSLT_DT']); // 현재 시간을 타임스탬프로 변환
        $futureTime = $currentTime + (48 * 3600); // 48시간 후의 타임스탬프 계산 (48시간 * 3600초)    
        //if( $futureTime < strtotime( date('Y-m-d H:i:s') ) ) { $_conslt_st = true; }
    }

    if( !is_array($sql_result) ) { alert("[이로움ON] 1:1 상담 정보를 찾을 수 없습니다.", G5_SHOP_URL . "/eroumon_members_conslt_list.php".(($qstr)?"?".$qstr:"")); }

?>

    <section class="wrap">
        <div class="sub_section_tit"><?=$g5['title']?></div>
        <?php
        /*
        <button type="button" id="view_link" class="top_view_style" Onclick="window.open('https://eroum.co.kr/members/login','_blank'); ">이로움ON 맴버스<br />바로가기</button>
        <button type="button" id="view_list" class="top_view_style" Onclick="location.href = '<?=G5_SHOP_URL?>/eroumon_members_conslt_list.php?<?=$qstr?>'; ">목록</button>
        */
        ?>
    </section>

    <form class="form" role="form" name="form_eroumon_conslt" id="form_eroumon_conslt" action="<?=$_SERVER['PHP_SELF'];?>?consltID=<?=$_consltID?>" method="post">

        <input type="hidden" name="MC_cON" value="<?=$sql_result['CONSLT_NO']?>">
        <input type="hidden" name="MCR_cON" value="<?=$sql_result['BPLC_CONSLT_NO']?>">
        <input type="hidden" name="MCR_ID" value="<?=$sql_result['BPLC_ID']?>">
        <input type="hidden" name="MCR_STTUS_CD" value="">
        <input type="hidden" name="MCR_TEXT" value="">
        <input type="hidden" name="_qstr" value="<?=$qstr?>">

        <section class="wrap"><div class="sub_section_tit" style="font-size: 20px;">상담정보</div></section>

        <div class="list_box">
            <table id="table_list">
                <colgroup>
						<col width="20%"/>
						<col width="30%"/>
						<col width="20%"/>
						<col width="30%"/>
                </colgroup>
                <tr>
                    <th>수급자 성명</th><td><?=( !$_conslt_st || ($sql_result['MCR_ST']==="CS02") || ($sql_result['MCR_ST']==="CS08") )?$sql_result['MBR_NM']:Masking_Name($sql_result['MBR_NM']);?></td>
                    <th>수급자와의 관계</th><td><?=(!$_conslt_st)?$sql_result['Hangeul_RELATION_CD']:"-"?></td>
                </tr><tr>
                    <th>ON회원아이디</th><td><?=(!$_conslt_st)?$sql_result['REG_ID']:"-"?></td>
                    <th>상담유형</th><td><?=(!$_conslt_st || ($sql_result['MCR_ST']==="CS02") || ($sql_result['MCR_ST']==="CS08") )?$sql_result['Hangeul_PREV_PATH']:"-"?></td>
                </tr><tr>
                    <th>성별</th><td><?=(!$_conslt_st)?$sql_result['Hangeul_GENDER']:"-"?></td>
                    <th>상담유형 상세</th>
                    <td>
                        <?php if( (!$_conslt_st) && ($sql_result['PREV_PATH'] == "test") ) { ?>
                            <a href="javascript:void(0);" id="TestResult" data-rno="<?=$sql_result['RECIPIENTS_NO']?>" class="link_btn">상세보기</a>
                        <?php } else if( (!$_conslt_st) && ($sql_result['PREV_PATH'] == "simpleSearch") ) { ?>
                            <a href="javascript:void(0);" id="simpleSearchResult" data-pennum="<?=$sql_result['RCPER_RCOGN_NO']?>" data-pennm="<?=$sql_result['MBR_NM']?>" class="link_btn">상세보기</a>
                        <?php } else { echo("-"); } ?>
                    </td>
                </tr><tr>
                    <th>생년월일</th><td><?=(!$_conslt_st)?$sql_result['BRDT']:"-";?></td>
                    <th>요양인정번호</th><td><?php if( !$_conslt_st ) { ?><?=($sql_result['RCPER_RCOGN_NO'])?"있음":"없음";?><?php } else { echo("-"); } ?></td>
                </tr><tr>
                    <th>상담받을 연락처</th><td><?=(!$_conslt_st)?$sql_result['MBR_TELNO']:"-";?></td>
                    <th>상담신청일시</th><td><?=$sql_result['MC_REG_DT']?></td>
                </tr><tr>
                    <th>실거주지 주소</th><td colspan="3"><?=(!$_conslt_st)?$sql_result['ZIP']." ".$sql_result['ADDR']." ".$sql_result['DADDR']:"-";?></td>
                </tr><tr>
                    <th>상담진행상태</th><td colspan="3"><?=$sql_result['Hangeul_CONSLT_STTUS']?></td>
                </tr>
            </table>
        </div>

        <?php if( ($sql_result['CUR_CONSLT_RESULT_NO'] !== $sql_result['BPLC_CONSLT_NO']) || ($sql_result['MC_ST']==="CS07") ) { ?>
            <span>※ 수급자의 요청으로 인해 정보가 삭제 되었습니다.</span>
        <?php } else if( $sql_result['MCR_ST']==="CS06" ) { ?>
            <!-- span>※ 상담완료 처리 이후 48시간까지 상담정보를 확인할수 있습니다.</span -->
        <?php } ?>

        <div style="height:20px;"></div>

        <?php if( (($sql_result['MCR_ST']=="CS03") || ($sql_result['MCR_ST']=="CS04") || ($sql_result['MCR_ST']=="CS09")) && ($sql_result['CANCL_RESN']) ) {?>

        <section class="wrap"><div class="sub_section_tit" style="font-size: 20px;">상담 취소 사유</div></section>
        <div class="list_box">
            <table id="table_list">
            <tbody>
                <tr style="height:120px;">
                    <th>상담 취소 사유</th>
                    <td><?=nl2br( $sql_result['CANCL_RESN'] );?></td>
                </tr>       
            </tbody>
            </table>
        </div>

        <?php } else if( ($sql_result['MCR_ST']=="CS05") || ($sql_result['MCR_ST']=="CS06") ) { ?>
        
        <section class="wrap"><div class="sub_section_tit" style="font-size: 20px;">상담 내용(맴버스관리자메모)</div></section>
        <div class="list_box">
            <table id="table_list">
                <colgroup>
                    <col width="20%"/>
                    <col width="80%"/>
                <c/olgroup>
            <tbody>
                <tr>
                    <th>상담진행상태</th>
                    <td>
                        <?=$sql_result['Hangeul_CONSLT_STTUS']?>
                        <?php if( $sql_result['MCR_ST']=="CS06" ) { /* echo(" ( " . $sql_result['CONSLT_DT']) . ")";*/ } ?>
                    </td>
                </tr>
                <tr style="height:120px;">
                    <th>상담내용</th>
                    <td>
                    <?php if( $sql_result['MCR_ST']=="CS06" ) { ?>
                        <?=nl2br( $sql_result['CONSLT_DTLS'] );?>
                    <?php } else { ?>
                        <textarea id="consltDtls" name="consltDtls" class="CONSLT_DTLS " title="메모" cols="30" rows="5"><?=$sql_result['CONSLT_DTLS']?></textarea>
                    <?php } ?>
                    </td>
                </tr>      
            </tbody>
            </table>
        </div>    

        <?php } ?>

        <div class="list_box text-right">
            <?php if( ($sql_result['MCR_ST']=="CS02") || ($sql_result['MCR_ST']=="CS08") ) { ?>
            <button type="button" id="STTUS_CO5" class="btn-list btn-success btn-lg" data-sttus="CS05">상담수락</button>
            <button type="button" id="STTUS_CO4" class="btn-list btn-danger btn-lg" data-sttus="CS04">상담거부</button>
            <?php } ?>


            <?php if( ($sql_result['MCR_ST']=="CS01") || ($sql_result['MCR_ST']=="CS05") || ($sql_result['MCR_ST']=="CS07") ) { ?>
            <?php
                /*
                    // 23.10.25 : 서원  - 기획팀(박은정차장) 요청으로 해당 기능 버튼 숨김 처리함. !!!!!!!!!!!!!
                    <button type="button" id="STTUS_CO3" class="btn-list btn-danger btn-lg" data-sttus="CS03">상담취소</button>
                */
            ?>
            <?php } ?>
            
            <?php if( ($sql_result['MCR_ST']=="CS05") ) { ?>
            <button type="submit" id="STTUS_CO6" class="btn-list btn-success btn-lg" data-sttus="CS06">상담완료</button>
            <?php } ?>
            
            <button type="button" id="LIST" class="btn-list btn-info btn-lg" Onclick="location.href = '<?=G5_SHOP_URL?>/eroumon_members_conslt_list.php?<?=$qstr?>'; ">목록</button>
        </div>

    </form>

    <div id="Cancel_popup_box">
        <div class="visual">
        <div class="visalWrap">

            <div class="headerTitle">
                <h5>상담 취소 사유 입력</h5>
                <div class="popup_box_close" onclick="$('body').removeClass('modal-open'); $('#Cancel_popup_box').hide();"><i class="fa fa-times"></i></div>
            </div>

            <div class="contentsWrap">
                <label>상담 취소 사유를 입력해 주세요</label>
                <textarea id="CANCL_RESN" title="메모" cols="30" rows="5"></textarea>

                <div style="margin: 20px 0px;">
                    <input type="bottom" value="취소" onclick="$('body').removeClass('modal-open'); $('#Cancel_popup_box').hide();" class="btn_submit" id="cancel" style="text-align:center; color: #000; background: #fff; border: 1px solid #000; width: 30%;" >
                    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                    <input type="bottom" value="저장하기" onclick="" class="btn_submit" id="STTUS_CO3_save" data-sttus="" style="text-align:center; background: #000; width: 30%;" >
                </div>
            </div>


        </div>
        </div>
    </div>

    <div style="padding: 100px 0px;"></div>


    <!-- 231020 인정등급 예상 결과테스트 모달 -->
    <div id="popupTestResultBox" class="Popup_TestResult"><div></div></div>
    <!-- 231020 인정등급 예상 결과테스트 모달 -->

    <!-- 231025 수급자 조회용 모달 -->
    <div id="popupsimpleSearchBox" class="Popup_simpleSearch"><div></div></div>
    <!-- 231025 수급자 조회용 모달 -->


    <script>
        
        $('#STTUS_CO3').on('click', function (e) {
            e.preventDefault();
            $('#STTUS_CO3_save').data('sttus', $(this).data('sttus')); 
            $('body').addClass('modal-open');
            $('#Cancel_popup_box').show();
            return;
        });

        $('#STTUS_CO3_save').on('click', function (e) {
            e.preventDefault();
            $('input[name="MCR_TEXT"]').val( $('#Cancel_popup_box #CANCL_RESN').val() );
            if( !$('input[name="MCR_TEXT"]').val() ) { alert("상담 취소 사유를 입력해주세요."); return false; }
            Click_Submit( $(this).data('sttus') );
            return;
        });

        $('#STTUS_CO4').on('click', function (e) {
            e.preventDefault();        
            if( confirm("상담 신청을 거부 하시겠습니까?")) { Click_Submit( $(this).data('sttus') ); }
            return;
        });

        $('#STTUS_CO5').on('click', function (e) {
            e.preventDefault();
            if( confirm("상담 신청을 수락 하시겠습니까?")) { Click_Submit( $(this).data('sttus') ); }
            return;
        });

        $('#STTUS_CO6').on('click', function (e) {
            e.preventDefault();        
            $('input[name="MCR_TEXT"]').val( $('#consltDtls').val() );
            Click_Submit( $(this).data('sttus') );
            return;
        });

        $('#TestResult').on('click', function (e) {
            e.preventDefault();
            $(".Popup_TestResult > div").html("");
            $(".Popup_TestResult > div").append("<iframe></iframe>");

            var iframeDocument = $('.Popup_TestResult iframe')[0].contentDocument;
            var form = $('<form action="/shop/popup.eroumon_members_testresult.php" method="post"></form>');
            form.append('<input type="hidden" name="RECIPIENTS_NO" value="' + $(this).data('rno') + '">'); // POST 데이터 추가
            iframeDocument.body.appendChild(form[0]);
            form[0].submit();

            $(".Popup_TestResult iframe").load(function(){
                $('body').addClass('modal-open');
                $(".Popup_TestResult").show();
            });
            return;
        });


        $('#simpleSearchResult').on('click', function (e) {
            e.preventDefault();
            $(".Popup_simpleSearch > div").html("");
            $(".Popup_simpleSearch > div").append("<iframe></iframe>");

            var iframeDocument = $('.Popup_simpleSearch iframe')[0].contentDocument;
            var form = $('<form action="/shop/popup.eroumon_members_simplesearch.php" method="post"></form>');
            form.append('<input type="hidden" name="penNum" value="' + $(this).data('pennum') + '">'); // POST 데이터 추가
            form.append('<input type="hidden" name="penNm" value="' + $(this).data('pennm') + '">'); // POST 데이터 추가
            iframeDocument.body.appendChild(form[0]);
            form[0].submit();

            $(".Popup_simpleSearch iframe").load(function(){
                $('body').addClass('modal-open');
                $(".Popup_simpleSearch").show();
            });
            return;
        });

        function Click_Submit(sttus) {
            $('input[name="MCR_STTUS_CD"]').val( sttus );
            $('#form_eroumon_conslt').submit();
            return;
        }

    </script>


    <style>
        /* 팝업 */
        #Cancel_popup_box { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:999; background: rgba(0, 0, 0, 0.8); }
        #Cancel_popup_box .visual { width:400px; height:250px; max-height: 80%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; }
        #Cancel_popup_box .visual .headerTitle { background: #111111; padding: 2px 15px; color:white; display: flex; justify-content: space-between; }
        #Cancel_popup_box .visual .headerTitle h5 { font-size: 20px; }
        #Cancel_popup_box .visual .headerTitle .cancel i { color: white; }
        #Cancel_popup_box .visual .headerTitle .cancel i:hover { color: #93D500; }
        #Cancel_popup_box .visual .contentsWrap { text-align:center; padding: 15px; background-color: white; }
        #Cancel_popup_box .visual .contentsWrap label { width:95%; text-align: left; }
        #Cancel_popup_box .visual .contentsWrap textarea { width:95%; border-radius: 0.5rem; resize: none; line-height: normal; padding:10px; }
        #Cancel_popup_box .popup_box_close { position:absolute; top:5px; right: 15px; color: white; font-size: 2.5em; cursor:pointer; }


        /* 인정등급 예상 결과테스트 모달 */
        .Popup_TestResult { display: none; position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); table-layout: fixed; }
        .Popup_TestResult > div {  width: 850px; height: 100%; margin: auto; vertical-align: middle; }
        .Popup_TestResult iframe { position: relative; width: 100%; height: 750px; border: 0; background-color: #FFF; top:5%; }
        @media (max-width : 750px){
            .Popup_TestResult > div { padding:0%; }
            .Popup_TestResult iframe { width: 100%; height: 95%; left: 0; margin-left: 0; }
        }

        /* 수급자 조회용 모달 */
        .Popup_simpleSearch { display: none; position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); table-layout: fixed; }
        .Popup_simpleSearch > div {  width: 700px; height: 100%; margin: auto; vertical-align: middle; }
        .Popup_simpleSearch iframe { position: relative; width: 100%; height: 800px; border: 0; background-color: #FFF; top:5%; }
        @media (max-width : 750px){
            .Popup_simpleSearch > div { padding:0%; }
            .Popup_simpleSearch iframe { width: 100%; height: 95%; left: 0; margin-left: 0; }
        }

        .top_view_style { position: absolute; color: #333; font-weight: normal; font-size: 14px; line-height: 20px; height: 60px; padding: 5px 36px; border-radius: 3px; vertical-align: middle; background-color: #000; cursor: pointer; }
        #view_list { background-color: #fff; color: #000; border: 1px solid #000; right: 175px; top: 8px; }
        #view_link { color: #fff; border: none; right: 0px; top: 8px; }

        .list_box #table_list { font-weight:500; }
        .list_box #table_list th { border: 1px solid #dad9d5; background-color: #faf7f5;  font-weight:500;  width: 160px; text-align:right; padding-right:10px; }
        .list_box #table_list td { border: 1px solid #dad9d5;  font-weight:500; }
        .list_box .btn-list { border-radius: 4px; padding: 10px 24px; font-size:16px; font-weight:700; }

        .list_box .btn-list.btn-success { background-color: #3c80b7; }
        .list_box .btn-list.bnt-danger { background-color: #d9534f; }
        .list_box #LIST { background-color: #7f7f7f; }

        .link_btn{ align-items: center; border-radius: 8px; border: solid 1px #999; display: inline-flex; font-weight: 500; justify-content: center; line-height: 1; padding: 8px 15px; --tw-shadow: 0px 0.154em 0.154em #00000027; --tw-shadow-colored: 0px 0.154em 0.154em var(--tw-shadow-color); box-shadow: 0px 2px 4px 0px rgba(0,0,0,0.10); }

        .CONSLT_DTLS { border-radius: 0.5rem; width:100%; padding:10px; resize: none; line-height: normal; height: 150px; }

    </style>




<?php
    @include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
    include_once('./_tail.php');
?>
