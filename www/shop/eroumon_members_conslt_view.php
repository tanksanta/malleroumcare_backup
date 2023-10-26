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
    /* //  * Program Name : EROUMCARE Platform! = EroumON_Order Ver:0.1 */
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

    // 해당 페이지에서 REQUEST 이벤트가 발생하고, POST일 경우 질문지 데이터 업데이트를 위한 코드 동작.
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
            
            if( ($sql_result['CONSLT_STTUS']!=$_MCR_STTUS_CD) && ($sql_result['CONSLT_NO']===$_MC_cON) && ($sql_result['BPLC_CONSLT_NO']===$_MCR_cON)  ) {
                // 프로시저 : CALL `PROC_EROUMCARE_CONSLT_UPDATE`('모드', 상담신청NO, 상담배정NO, '변결될상태값', '완료또는 거부시 사유또는 내용', '배정 당시 사업소아이디');                        
                $sql = (" CALL `PROC_EROUMCARE_CONSLT_UPDATE`('BPLC', {$_MC_cON}, {$_MCR_cON}, '{$_MCR_STTUS_CD}', '{$_MCR_TEXT}', '{$_MCR_ID}'); ");
                $sql_result = "";
                $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
//======================CS04:상담거절, CS06:상담완료 시 알림톡 발송===============================================================================================
				if($_MCR_STTUS_CD == "CS04" || $_MCR_STTUS_CD == "CS06"){
					if($_MCR_STTUS_CD == "CS06"){//상담완료 시 알림톡
						$alimtalk_contents = $sql_result['MBR_NM']."님, 상담이 완료되었습니다.\n상담한 장기요양기관이 마음에 드실 경우 이로움ON에서 상담기관 추천하기 및 관심설정이 가능합니다.\n\n다른 장기요양기관과의 재상담을 원하실 경우\n\n상담 내역 관리에서 재상담 신청이 가능한 점 참고 부탁드립니다.";
						$result2 = send_alim_talk2('CONSLT_REQUEST_'.$sql_result['MBR_TELNO'], $sql_result['MBR_TELNO'], 'ON_00001', $alimtalk_contents, array(
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
						$alimtalk_contents = $sql_result['MBR_NM']."님, 요청하신 1:1 상담이 취소되었습니다.\n\n◼︎ 상담 취소일 : ".date("Y-m-d")."\n\n상담을 원하시는 경우 이로움ON에서 다시 상담을 요청해 주세요.";
						$result2 = send_alim_talk2('CONSLT_CANCEL_'.$sql_result['MBR_TELNO'], $sql_result['MBR_TELNO'], 'ON_00002', $alimtalk_contents, array(
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
    if( $sql_result['CONSLT_STTUS']==="CS03" || $sql_result['CONSLT_STTUS']==="CS04" || $sql_result['CONSLT_STTUS']==="CS09" ) { $_conslt_st = true; }

    if( !is_array($sql_result) ) { alert("[이로움ON] 1:1 상담 정보를 찾을 수 없습니다.", G5_SHOP_URL . "/eroumon_members_conslt_list.php".(($qstr)?"?".$qstr:"")); }

?>


    <section class="wrap">
        <div class="sub_section_tit"><?=$g5['title']?></div>
        <button type="button" id="view_link" class="top_view_style" Onclick="window.open('https://eroum.co.kr/members/login','_blank'); ">이로움ON 맴버스<br />바로가기</button>
        <button type="button" id="view_list" class="top_view_style" Onclick="location.href = '<?=G5_SHOP_URL?>/eroumon_members_conslt_list.php?<?=$qstr?>'; ">목록</button>
    </section>

    <form class="form" role="form" name="form_eroumon_conslt" id="form_eroumon_conslt" action="<?=$_SERVER['PHP_SELF'];?>?consltID=<?=$_consltID?>" method="post">

        <input type="hidden" name="MC_cON" value="<?=$sql_result['CONSLT_NO']?>">
        <input type="hidden" name="MCR_cON" value="<?=$sql_result['BPLC_CONSLT_NO']?>">
        <input type="hidden" name="MCR_ID" value="<?=$sql_result['BPLC_ID']?>">
        <input type="hidden" name="MCR_STTUS_CD" value="">
        <input type="hidden" name="MCR_TEXT" value="">
        <input type="hidden" name="_qstr" value="<?=$qstr?>">

        <section class="wrap"><div class="sub_section_tit" style="font-size: 20px;">상담 대상자 정보</div></section>

        <div class="list_box">
            <table id="table_list">
            <thead>
                <tr>
                    <th>성명</th><td><?=(!$_conslt_st)?$sql_result['MBR_NM']:Masking_Name($sql_result['MBR_NM']);?></td><th>성별</th><td><?=(!$_conslt_st)?$sql_result['Hangeul_GENDER']:"-"?></td>
                </tr><tr>
                    <th>연락처</th><td><?=(!$_conslt_st)?$sql_result['MBR_TELNO']:"-";?></td><th>생년월일</th><td><?=(!$_conslt_st)?substr($sql_result['BRDT'],0,4)."/".substr($sql_result['BRDT'],4,2)."/".substr($sql_result['BRDT'],6,2):"-";?></td>
                </tr><tr>
                    <th>거주지 주소</th><td colspan='3'><?=(!$_conslt_st)?"(".$sql_result['ZIP'].")".$sql_result['ADDR']."<br/>".$sql_result['DADDR']:"-";?></td>
                </tr><tr>
                    <th>상담 신청일</th><td><?=$sql_result['MC_REG_DT']?></td><th>상담진행상태</th><td><?=$sql_result['Hangeul_CONSLT_STTUS']?></td>
                </tr><tr>
                    <th>상담 배정 일시</th><td colspan='3'><?=$sql_result['MCR_REG_DT']?></td>
                </tr>
            </thead>
            <tbody>
            </tbody>
            </table>
        </div>
        
        <?php if( (($sql_result['CONSLT_STTUS']=="CS03") || ($sql_result['CONSLT_STTUS']=="CS04") || ($sql_result['CONSLT_STTUS']=="CS09")) && ($sql_result['CANCL_RESN']) ) {?>

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

        <?php } else if( ($sql_result['CONSLT_STTUS']=="CS05") || ($sql_result['CONSLT_STTUS']=="CS06") ) { ?>
        
        <section class="wrap"><div class="sub_section_tit" style="font-size: 20px;">상담 내용(맴버스관리자메모)</div></section>
        <div class="list_box">
            <table id="table_list">
            <tbody>
                <tr>
                    <th>상담진행상태</th>
                    <td><?=$sql_result['Hangeul_CONSLT_STTUS']?></td>
                </tr>
                <tr style="height:120px;">
                    <th>상담내용</th>
                    <td><textarea id="consltDtls" name="consltDtls" class="CONSLT_DTLS " title="메모" cols="30" rows="5"><?=$sql_result['CONSLT_DTLS']?></textarea></td>
                </tr>      
            </tbody>
            </table>
        </div>    

        <?php } ?>

        <div class="list_box text-right">
            <?php if( ($sql_result['CONSLT_STTUS']=="CS02") || ($sql_result['CONSLT_STTUS']=="CS08") ) { ?>
            <button type="button" id="STTUS_CO5" class="btn-list btn-success btn-lg" data-sttus="CS05">상담수락</button>
            <button type="button" id="STTUS_CO4" class="btn-list btn-danger btn-lg" data-sttus="CS04">상담거부</button>
            <?php } ?>


            <?php if( ($sql_result['CONSLT_STTUS']=="CS01") || ($sql_result['CONSLT_STTUS']=="CS05") || ($sql_result['CONSLT_STTUS']=="CS07") ) { ?>
            <button type="button" id="STTUS_CO3" class="btn-list btn-danger btn-lg" data-sttus="CS03">상담취소</button>
            <?php } ?>
            
            <?php if( ($sql_result['CONSLT_STTUS']=="CS05") ) { ?>
            <button type="submit" id="STTUS_CO6" class="btn-list btn-info btn-lg" data-sttus="CS06">상담완료</button>
            <?php } ?>
        </div>

    </form>

    <div id="Cancel_popup_box">
        <div class="visual">
        <div class="visalWrap">

            <!-- title -->
            <div class="headerTitle">
                <h5>상담 취소 사유 입력</h5>
                <div class="popup_box_close" onclick="$('body').removeClass('modal-open'); $('#Cancel_popup_box').hide();"><i class="fa fa-times"></i></div>
            </div>

            <!-- contents -->
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


    .top_view_style { position: absolute; color: #333; font-weight: normal; font-size: 14px; line-height: 20px; height: 60px; padding: 5px 36px; border-radius: 3px; vertical-align: middle; background-color: #000; cursor: pointer; }
    #view_list { background-color: #fff; color: #000; border: 1px solid #000; right: 175px; top: 8px; }
    #view_link { color: #fff; border: none; right: 0px; top: 8px; }


    .list_box #table_list th { width: 160px; text-align:right; padding-right:10px; }
    .list_box #table_list td { border-top: 1px solid #ddd; }
    .list_box .btn-list { border-radius: 5px; margin : 20px 3px; padding : 10px 24px; }


    .CONSLT_DTLS { border-radius: 0.5rem; width:100%; padding:10px; resize: none; line-height: normal; height: 150px; }

</style>


<?php
    
    @include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
    include_once('./_tail.php');
?>
