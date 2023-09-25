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
    /* // 파일명 : /www/shop/eroumon_members_conslt_list.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 발생한 고객(맴버)의 상담관련 업무를 처리하는 리스트 페이지. */
    /*                회원이 인정등급 테스트를 진행하고 1:1 상담 신청을 했을때, THKC에서 배정해준 사업소에 해당 리스트가 출력되는 페이지. */
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

    $g5['title'] = '고객상담 신청관리';

    include_once('./_head.php');
 
    // 테스트용... 사업자 번호 임의수정
    //$member['mb_giup_bnum'] = '123-45-67892';
    //$member['mb_giup_bnum'] = '111-22-33333';

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    if( $eroumon_connect_db ) { 

        // 날짜검색
        if ($fr_date && $to_date) {
            $to_date = $to_date . ' 23:59:59';
        } else {        
            $fr_date = date("Y-m-d",strtotime("-90 day", time()));
            $to_date = date("Y-m-d H:i:s",strtotime("0 day", time()));
        }

        $_Search = "";
        $search = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['search'] ) ) );

        // 상태값 선택에 따른 SQL 쿼리문.
        if( $srchConsltSttus=="CS02" ) $_Search = "AND (MCR.CONSLT_STTUS=''CS02'')";
        else if( $srchConsltSttus=="CS05" ) $_Search = "AND (MCR.CONSLT_STTUS=''CS05'')";
        else if( $srchConsltSttus=="CANCEL" ) $_Search = "AND ((MCR.CONSLT_STTUS=''CS03'') OR (MCR.CONSLT_STTUS=''CS04'') OR (MCR.CONSLT_STTUS=''CS09''))";
        else if( $srchConsltSttus=="CS06" ) $_Search = "AND (MCR.CONSLT_STTUS=''CS06'')";
        
        // 검색에 따른 (전체, 이름, 연락처) SQL 쿼리문 - 해당 쿼리는 LIKE를 기반으로 한다.
        if( $sel_field=="NM" ) $_Search .= "AND (MC.MBR_NM LIKE ''%{$search}%'')";
        else if( $sel_field=="TELNO" ) $_Search .= "AND (MC.MBR_TELNO LIKE ''%{$search}%'')";
        else if( $sel_field=="all" && $search ) $_Search .= "AND ((MC.MBR_NM LIKE ''%{$search}%'') OR (MC.MBR_TELNO LIKE ''%{$search}%''))";

        // 페이지 진입에 따른 조건 기준으로 검색된 검색 개수.
        $sql = (" CALL `PROC_EROUMCARE_CONSLT`('cnt','{$member['mb_giup_bnum']}', '{$fr_date}', '{$to_date}', NULL, NULL, '{$_Search}'); ");
        $sql_result = "";
        $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
        //var_dump($sql_result);

        $total_count = $sql_result['cnt'];
        $rows = ($list_num)?$list_num:$config['cf_page_rows'];
        $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
        if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
        $from_record = ($page - 1) * $rows; // 시작 열을 구함
        
        // 리스트용 데이터 호출
        // 프로시저 : CALL `PROC_EROUMCARE_CONSLT`('모드','회원사업자번호', '검색시작일', '검색종료일','페이지포인터시작','리스트수량','검색조건');
        $sql = (" CALL `PROC_EROUMCARE_CONSLT`('list','{$member['mb_giup_bnum']}', '{$fr_date}', '{$to_date}','{$from_record}','{$rows}','{$_Search}'); ");
        $sql_result = "";
        $sql_result = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);

    } else { $page = $total_count = 0; }

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 페이지 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    // 페이징 되는 주소 파라미터
    $qstr = ("fr_date={$fr_date}&to_date=".substr($to_date,0,10)."&srchConsltSttus={$srchConsltSttus}&sel_field={$sel_field}&page={$page}&list_num={$list_num}");

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 페이지 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

?>


    <section class="wrap">
        <div class="sub_section_tit"><?=$g5['title']?></div>
        <button type="button" class="" id="view_link" Onclick="window.open('https://eroum.co.kr/members/login','_blank'); ">이로움ON 맴버스<br />바로가기</button>
    </section>


    <form name="shop_order_list" id="shop_order_list" method="get" onsubmit="return shop_order_list_submit_function(this);">
    <div class="new_form" style="min-width: 99%; margin: 0px;">

        <table class="new_form_table">
            <tr>
                <td style="width:160px; height: 45px; text-align:right;"><strong>검색기간</strong></td>
                <td style="padding: 10px 25px;" class="sch_last">

                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('전체');">전체</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('오늘');">오늘</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('어제');">어제</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('이번주');">일주일</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('이번달');">이번달</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('지난달');">지난달</button>
                    <input type="text" id="fr_date"  name="fr_date" value="<?=$fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
                    <input type="text" id="to_date"  name="to_date" value="<?=substr($to_date,0,10); ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">

                </td>
            </tr>
            <tr>
                <td style="width:160px; height: 45px; text-align:right;"><strong>상담진행상태</strong></td>
                <td style="padding: 10px 25px;">
                    <select name="srchConsltSttus" id="srchConsltSttus" class="form-control w-84">
                        <option value="">선택</option>
                        <option value="CS02"<?=($srchConsltSttus=="CS02")?" selected":""?>>상담 신청 접수</option>
                        <option value="CS05"<?=($srchConsltSttus=="CS05")?" selected":""?>>상담 진행 중</option>
                        <option value="CANCEL"<?=($srchConsltSttus=="CANCEL")?" selected":""?>>상담 취소</option>
                        <option value="CS06"<?=($srchConsltSttus=="CS06")?" selected":""?>>상담 완료</option>
                    </select>
                </td>
            </tr>  
            <tr>
                <td style="width:160px; height: 45px; text-align:right;"><strong>검색어</strong></td>
                <td style="padding: 10px 25px;">
                    <select name="sel_field" id="sel_field" class="inupt_s">
                        <option value="all" <?=get_selected($sel_field, 'all'); ?>>전체</option>
                        <option value="NM" <?=get_selected($sel_field, 'NM'); ?>>성명</option>
                        <option value="TELNO" <?=get_selected($sel_field, 'TELNO'); ?>>연락처</option>
                    </select>&nbsp;
                    <input type="text" id="search"  name="search" value="<?=$search; ?>" class="frm_input" size="30" maxlength="25" autocomplete="off" style="width:250px;">&nbsp;
                    <input type="submit" value="검색" class="btn_submit" id="_submit" style="width:80px; height:35px; padding: 0px; background: #333;">
                </td>
            </tr>   
          
        </table>

    </div>

    <div style="height:50px; padding: 30px 0px;"> 
        <select name="list_num" id="bn_position" style="width:120px; float:right;" onchange="submit();">
            <option value="" <?=($list_num=="")?"selected":""?>> 시스템 기본 보기 </option>
            <option value="50" <?=($list_num=="50")?"selected":""?>> 50씩 보기 </option>
            <option value="100" <?=($list_num=="100")?"selected":""?>> 100씩 보기 </option>
            <option value="200" <?=($list_num=="200")?"selected":""?>> 200씩 보기 </option>
            <option value="500" <?=($list_num=="500")?"selected":""?>> 500씩 보기 </option>
            <option value="1000" <?=($list_num=="1000")?"selected":""?>> 1000씩 보기 </option>
        </select>

        <div style="width:250px; height:30px; font-size:12px; float:left;" >
            검색 개수 : <span id="list_cnt"><?=number_format($total_count);?> 건</span>
        </div>
    </div>

    </form>

    <div class="list_box">
        <table id="table_list">
        <thead>
            <tr>
                <th style="width:">번호</th>
                <th style="width: 100px;">상담진행상태</th>
                <th style="width: 100px;">성명</th>
                <th style="width: ;">성별</th>
                <th style="width: ;">연락처</th>
                <th style="width: ;">만나이</th>
                <th style="width: ;">생년월일</th>
                <th style="width: ;">거주지주소</th>
                <th style="width: 150px;">상담배정일<br/>(상담신청일)</th>
            </tr>
        </thead>
        <tbody>
        <?php

            for($i=0; $row=sql_fetch_array($sql_result); $i++) {
                $bg = 'bg'.($i%2);
                //var_dump($row);
                
                $birthday = new DateTime($row['BRDT']); // 생년월일
                $age = $birthday->diff( new DateTime(date('ymd')) )->y; // 만나이 계산

                $_conslt_st = false;
                if( $row['CONSLT_STTUS']==="CS03" || $row['CONSLT_STTUS']==="CS04" || $row['CONSLT_STTUS']==="CS09" ) { $_conslt_st = true; }
        ?>    
            <tr class="<?=$bg?>" >

                <td style="text-align: center;"><?=($total_count - (($page - 1) * $page_rows) - $i);?></td>
                <td style="text-align: center;">
                    <a href="./eroumon_members_conslt_view.php?consltID=<?=$row['BPLC_CONSLT_NO'];?>&<?=$qstr?>">
                        <?php if( $_conslt_st ) { ?><span style="color:red;"><?php } ?><?=$row['Hangeul_CONSLT_STTUS']?><?php if( $_conslt_st ) { ?></span><?php } ?>
                    </a>
                </td>
                <td style="text-align: center;"><a href="./eroumon_members_conslt_view.php?consltID=<?=$row['BPLC_CONSLT_NO'];?>&<?=$qstr?>" class="link_btn"><?=(!$_conslt_st)?$row['MBR_NM']:Masking_Name($row['MBR_NM']);?></a></td>
                <td style="text-align: center;"><?=(!$_conslt_st)?$row['Hangeul_GENDER']:"-";?></td>
                <td style="text-align: center;"><?=(!$_conslt_st)?$row['MBR_TELNO']:"-";?></td>
                <td style="text-align: center;"><?=(!$_conslt_st)?$age."세":"-";?></td>
                <td style="text-align: center;"><?=(!$_conslt_st)?substr($row['BRDT'],0,4)."/".substr($row['BRDT'],4,2)."/".substr($row['BRDT'],6,2):"-";?></td>
                <td style="text-align: center; font-size:13px;"><?=(!$_conslt_st)?"(".$row['ZIP'].")".$row['ADDR']."<br/>".$row['DADDR']:"-";?></td>
                <td style="text-align: center;"><?=$row['MCR_REG_DT']."<br/><span style='font-size:12px;'>(".$row['MC_REG_DT']?>)</span></td>

            </tr>
        <?php } ?>
        <?php if ($i == 0) echo ("<tr><td colspan='9' class='empty_table'>자료가 없습니다.</td></tr>"); ?>
        </tbody>
        </table>
    </div>

    <div style="height:50px; padding: 10px 0px;"> 
        <button type="button" class="btn-primary btn-excel">엑셀 다운로드</button>
    </div>

    <?=$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr);?>
    
    <div style="padding: 100px 0px;"></div>

    <script>

        $(function() {
            $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
        });


        $('.btn-excel').on('click', function (e) {

            $.ajax({
                url: './eroumon_members_conslt_excel.php',
                type: "POST",
                xhrFields: { 
                    responseType: "blob" // 응답 데이터 타입을 Blob으로 설정
                 },
                data: { 
                    "PUTtype" : "Excel",
                    "fr_date" : "<?=$fr_date;?>",
                    "to_date" : "<?=substr($to_date,0,10);?>",
                    "srchConsltSttus" : "<?=$srchConsltSttus;?>",
                    "sel_field" : "<?=$sel_field;?>",
                    "search": "<?=$search?>"
                },
                success:function(data) {
                    // 응답으로 받은 Blob 데이터를 파일로 다운로드
                    var blob = new Blob([data]);
                    var link = document.createElement("a");
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "이로움ON_고객상담리스트_<?=date('Y-m-d');?>.xlsx"; // 다운로드할 파일 이름 설정
                    document.body.appendChild(link);
                    link.style.display = "none";
                    link.click();
                    document.body.removeChild(link);
                 },
                error:function() { alert('문제발생!!'); }
            });

        });


        function set_date(today)
        {
            <?php
                $date_term = date('w', G5_SERVER_TIME);
                $week_term = $date_term + 7;
                $last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
            ?>
            if (today == "오늘") {
                document.getElementById("fr_date").value = "<?=G5_TIME_YMD; ?>";
                document.getElementById("to_date").value = "<?=G5_TIME_YMD; ?>";
            } else if (today == "내일") {
                document.getElementById("fr_date").value = "<?=date('Y-m-d', G5_SERVER_TIME + 86400); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-d', G5_SERVER_TIME + 86400); ?>";
            } else if (today == "어제") {
                document.getElementById("fr_date").value = "<?=date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
            } else if (today == "이번주") {
                document.getElementById("fr_date").value = "<?=date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-d', G5_SERVER_TIME); ?>";
            } else if (today == "이번달") {
                document.getElementById("fr_date").value = "<?=date('Y-m-01', G5_SERVER_TIME); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-d', G5_SERVER_TIME); ?>";
            } else if (today == "지난주") {
                document.getElementById("fr_date").value = "<?=date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
            } else if (today == "지난달") {
                document.getElementById("fr_date").value = "<?=date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
            } else if (today == "전체") {
                document.getElementById("fr_date").value = "";
                document.getElementById("to_date").value = "";
            } else if (today == "일주일") {
                document.getElementById("fr_date").value = "<?=date('Y-m-d', strtotime('-7 days', G5_SERVER_TIME)); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-d', G5_SERVER_TIME); ?>";
            } else if (today == "3개월") {
                document.getElementById("fr_date").value = "<?=date('Y-m-d', strtotime('-3 month', G5_SERVER_TIME)); ?>";
                document.getElementById("to_date").value = "<?=date('Y-m-d', G5_SERVER_TIME); ?>";
            }
        }

    </script>


    <style>

        .new_form { border:1px solid #ddd; box-sizing: border-box; margin:30px 20px 30px 20px; background-color: #f8f8fa; min-width:1400px; }
        .new_form .submit { position:relative; height:50px; }
        .new_form .submit button[type="submit"] { background-color: #009845; height:32px; width:80px; line-height: 32px; color:white; border:0; display:block; margin:15px auto; cursor:pointer; }
        .new_form .submit .buttons { position:absolute; top:6px; right:15px; }
        .new_form .submit .buttons button { background:none !important; padding-left:0px; width: auto !important; margin-right: 10px; border:0; }

        .new_form_table { padding:10px 20px; width:100%; margin: 0 auto; color:#666666; box-sizing: border-box; background-color: #f8f8fa; }
        .new_form_table th { width:150px; text-align:left; border-bottom:1px solid #e1e2e2; padding:12px 20px; }
        .new_form_table tr:last-child th { border-bottom:0; }
        .new_form_table tr:last-child td { border-bottom:0; }
        .new_form_table td { border:0; padding:2px 0; border-bottom:1px solid #e1e2e2; line-height:30px; }
        .new_form_table td.date { font-size:0px; }
        .new_form_table td.date .sch_last { display:inline-block; font-size:13px; margin-left:10px; vertical-align: middle; }
        .new_form_table td select { font-size: 12px; color: #555; appearance: none; -webkit-appearance: none; -moz-appearance: none; height: 24px !important; padding: 2px 25px 0px 3px; background: #ffffff url('/adm/shop_admin/img/admin_select_n.gif') no-repeat right 8px center; border:1px solid #dbdde2; border-radius: 0px; width: 100px; height: 33px !important; padding: 0px 13px !important; }
        .new_form_table td select::-ms-expand {display:none}
        .new_form_table td div.date { display:inline-block; border:1px solid #ddd; background-color:white; }
        .new_form_table td input[type="text"] { display:inline-block; border:1px solid #ddd; background-color:white !important; height: 33px; width: 100px; text-align: left; font-size: 13px; padding:0 10px; box-sizing:border-box; }
        .new_form_table td div.date input { border: 0px !important; border-right: 1px solid #ddd !important; outline: none; padding: 8px 10px; margin: 0; }
        .new_form_table td div.date input:hover { border: 0px !important; border-right: 1px solid #ddd !important; }
        .new_form_table td div.date img { cursor:pointer; vertical-align: middle; padding: 0 10px; }
        .new_form_table td .newbtn { height: 33px; border: 1px solid #ddd; display: inline-block; vertical-align: middle; line-height:33px; cursor:pointer; box-sizing: border-box; }
        .new_form_table td .newbtn input,
        .new_form_table td .newbutton { border: 0; font-size: 12px; height: 33px; padding: 0 10px; cursor: pointer; outline: none; box-sizing: border-box; border:1px solid #ddd; }
        .new_form_table td .newbtn:hover, .new_form_table td .newbutton:hover { border:1px solid #0c9846; }
        .new_form_table td .mul { font-size: 12px; margin: 0 5px; height: 33px; line-height: 33px; display: inline-block; vertical-align: middle;}

        .new_form_table td input[type=checkbox],
        .new_form_table td input[type=radio]{ display:none; }
        .new_form_table td input[type=checkbox] + label,
        .new_form_table td input[type=radio] + label { display: inline-block; cursor: pointer; line-height: 21px; padding-left: 27px; background: url('/adm/shop_admin/img/checkbox.png') left/21px no-repeat; margin-right:10px; height:21px; }
        .new_form_table td input[type=radio] + label { background: url('/adm/shop_admin/img/radio.png') left/21px no-repeat; }
        .new_form_table td input[type=checkbox]:checked + label { background-image: url('/adm/shop_admin/img/checkbox_checked.png'); }
        .new_form_table td input[type=radio]:checked + label { background-image: url('/adm/shop_admin/img/radio_checked.png'); }

        .new_form_table td #search_keyword { width: 200px; height: 33px; padding: 0 15px; box-sizing: border-box;}
        .new_form_table td .search_type_text { display:none !important; }
        .new_form_table td .search_keyworld_msg { margin-left:15px; font-size:12px; letter-spacing: -1px; }
        .new_form_table td .select { display:inline-block; height:33px; background-color: #fff; box-sizing: border-box; padding:5px 10px; width:150px; position:relative; border:1px solid #a7a8aa; line-height: 20px; }
        .new_form_table td .select:after { content:"▼"; display:block; position:absolute; top:5px; right:10px; }
        .new_form_table td .select:hover .selectbox_multi { display:block; }
        .new_form_table td .select .selectbox_multi { display: none; position: absolute; top: -1px; left: -1px; z-index: 99; width: 150px; }
        .new_form_table td .select .selectbox_multi .cont { width:100%; }
        .new_form_table td .select .selectbox_multi .cont .list { height:130px; }
        .new_form_table td .linear { border-left: 1px solid #ddd; margin-left: 15px; display: inline-block; padding-left: 15px; height: 31px; box-sizing: border-box; vertical-align: middle; line-height: 31px; }
        .new_form_table td .linear>span { margin-right:15px; }

        tr td { padding: 2px 10px; position: relative; }        
        td #popup-area { width:30px; }
        td .popup_tooltip { display: none; animation: tooltipAni 1s; transition: opacity 0.5s; position: absolute; top: 5px; left: -150px; background: #fff; border:1px solid #dbdde2; width:250px; padding: 10px; font-weight: normal; font-size: 14px; line-height: 24px; }
        td:hover .popup_tooltip { display: block; }

        #view_link { position: absolute; color: #333; font-weight: normal; font-size: 14px; line-height: 20px; height: 60px; padding: 5px 36px; border-radius: 3px; vertical-align: middle; background-color: #000; color: #fff; border: none; cursor: pointer; right: 0px; top: 8px; }      
        .link_btn{ align-items: center; border-radius: 0.5rem; border: solid 1px #C1C1C1; display: inline-flex; font-weight: 700; justify-content: center; line-height: 1; padding: 1.25rem 0.125rem; width: 100%; --tw-shadow: 0px 0.154em 0.154em #00000027; --tw-shadow-colored: 0px 0.154em 0.154em var(--tw-shadow-color); box-shadow: var(--tw-ring-offset-shadow,0 0 #0000),var(--tw-ring-shadow,0 0 #0000),var(--tw-shadow); }
        .list_box #table_list td { padding:5px; }
        .btn-excel { float:right; border-radius: 0.5rem; background: #000; padding: 5px 10px; }

    </style>


<?php
    
    @include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
    include_once('./_tail.php');
?>