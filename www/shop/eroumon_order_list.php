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
    /* // 파일명 : /www/shop/eroumon_order_list.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 발생한 주문건을 DB받은 후 해당 화면에서 사업소가 리스트로 확인 가능한 페이지 */
    /*                */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');

    if(!$member['mb_id'])
        alert('먼저 로그인하세요.',G5_URL.'/bbs/login.php');

    @include_once(G5_LIB_PATH.'/apms.thema.lib.php');
    @include_once($order_skin_path.'/config.skin.php');

    $g5['title'] = '수급자 주문관리';

    include_once('./_head.php');


    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    $sql_common = ("    FROM g5_shop_order_api OD    ");

    // 날짜검색
    if ($fr_date && $to_date) {
        $where[] = "(OD.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
    } else {        
        $where[] = "(OD.od_time between '".date("Y-m-d",strtotime("-90 day", time()))."' and '".date("Y-m-d",strtotime("+1 day", time()))."') ";
    }

    $where[] = "(OD.mb_id = '".$member['mb_id']."') ";

    if( $search && $sel_field == "all" ) { 
        $where[] = "(OD.order_send_id   LIKE '%".$search."%')
                    OR (OD.od_b_name       LIKE '%".$search."%')
                    OR (OD.od_b_hp         LIKE '%".$search."%')
                    OR (OD.od_penNm        LIKE '%".$search."%')
                    OR (OD.od_penLtmNum    LIKE '%".$search."%')
                    OR (CT.it_name         LIKE '%".$search."%') ";
    }
    else if( $search && $sel_field == "1" ) $where[] = "(OD.order_send_id   LIKE '%".$search."%') ";
    else if( $search && $sel_field == "2" ) $where[] = "(OD.od_b_name       LIKE '%".$search."%') ";
    else if( $search && $sel_field == "3" ) $where[] = "(OD.od_b_hp         LIKE '%".$search."%') ";
    else if( $search && $sel_field == "4" ) $where[] = "(OD.od_penNm        LIKE '%".$search."%') ";
    else if( $search && $sel_field == "5" ) $where[] = "(OD.od_penLtmNum    LIKE '%".$search."%') ";
    else if( $search && $sel_field == "6" ) $where[] = "(CT.it_name         LIKE '%".$search."%') ";

    if( $status ) {
        $i = 0;
        $qwhere = "";
        foreach ($status as $key => $val) {
            if( $val == "전체" ) break;
            $i++;
            $qwhere .= "'{$val}'";
            if( $i < count($status) ) $qwhere .= ", ";
        }
        if($qwhere) $where[] = "od_status IN ({$qwhere})";
    }

    if ($where) {
        if ($sql_search) {
            $sql_search .= " AND ";
        }else{
            $sql_search .= " WHERE ";
        }

        $sql_search .= implode(' and ', $where);
    }
    

    $sql = (" SELECT count(OD.order_send_id) as cnt
                {$sql_common}
                LEFT JOIN g5_shop_cart_api CT ON CT.order_send_id IS NULL
                {$sql_search}
    ");
    $row = sql_fetch($sql);


    $total_count = $row['cnt'];
    $rows = ($list_num)?$list_num:$config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함    


    $sql = (" SELECT OD.*, CT.it_name
                {$sql_common}
                LEFT JOIN g5_shop_cart_api CT ON CT.order_send_id=OD.order_send_id
                {$sql_search}
                GROUP BY OD.order_send_id
                ORDER BY OD.od_time DESC
                LIMIT {$from_record}, {$rows}
    ");
    $result = sql_query($sql);    

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==




    
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 페이지 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    $qstatus = "";
    if( $status ) {
        foreach ($status as $key => $val) {
            if( $val == "전체" ) {
                $status = ['전체'];
                break;
            }
            $qstatus .= "&amp;status[]={$val}";
        }
    }

    // 페이징 되는 주소 파라미터
    $qstr = ("sel_field={$sel_field}&amp;search={$search}&amp;fr_date={$fr_date}&amp;to_date={$to_date}").$qstatus;

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 페이지 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

?>


    <section class="wrap">
        <div class="sub_section_tit">수급자 주문관리</div>
        <button type="button" class="" id="view_link" Onclick="window.open('https://eroum.co.kr/members/login','_blank'); ">이로움ON 맴버스<br />바로가기</button>
    </section>


    <form name="shop_order_list" id="shop_order_list" method="get" onsubmit="return shop_order_list_submit_function(this);">
    <div class="new_form" style="min-width: 99%; margin: 0px;">

        <table style="background-color: #f8f8fa;" class="new_form_table">
            <tr>
                <td style="width:160px; height: 45px; text-align:center;"><strong>검색조건</strong></td>
                <td style="padding: 10px 25px;">

                    <input type="checkbox" name="status[]" id="전체" value="전체"<?=option_array_checked('전체', $status);?>/>&nbsp;<label for="전체" style="vertical-align:-3px;">전체</label>
                    <input type="checkbox" name="status[]" id="주문승인대기" value="승인대기"<?=option_array_checked('승인대기', $status);?>/>&nbsp;<label for="주문승인대기" style="vertical-align:-3px;">주문 승인대기</label>
                    <input type="checkbox" name="status[]" id="주문승인완료" value="승인완료"<?=option_array_checked('승인완료', $status);?>/>&nbsp;<label for="주문승인완료" style="vertical-align:-3px;">주문 승인완료</label>
                    <input type="checkbox" name="status[]" id="결제완료" value="결제완료"<?=option_array_checked('결제완료', $status);?>/>&nbsp;<label for="결제완료" style="vertical-align:-3px;">결제완료</label>
                    <input type="checkbox" name="status[]" id="주문완료" value="주문완료"<?=option_array_checked('주문완료', $status);?>/>&nbsp;<label for="주문완료" style="vertical-align:-3px;">주문완료</label>
                    <input type="checkbox" name="status[]" id="출고완료" value="출고완료"<?=option_array_checked('출고완료', $status);?>/>&nbsp;<label for="출고완료" style="vertical-align:-3px;">출고완료</label>
                    <input type="checkbox" name="status[]" id="주문취소" value="주문취소"<?=option_array_checked('주문취소', $status);?>/>&nbsp;<label for="주문취소" style="vertical-align:-3px;">주문취소</label>
                    <input type="checkbox" name="status[]" id="계약서작성완료" value="작성완료"<?=option_array_checked('작성완료', $status);?>/>&nbsp;<label for="계약서작성완료" style="vertical-align:-3px;">계약서 작성완료</label>
                    <input type="checkbox" name="status[]" id="계약서서명완료" value="서명완료"<?=option_array_checked('서명완료', $status);?>/>&nbsp;<label for="계약서서명완료" style="vertical-align:-3px;">계약서 서명완료</label>

                </td>
            </tr>
            <tr>
                <td style="width:160px; height: 45px; text-align:center;"><strong>검색기간</strong></td>
                <td style="padding: 10px 25px;" class="sch_last">

                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('전체');">전체</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('오늘');">오늘</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('어제');">어제</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('이번주');">일주일</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('이번달');">이번달</button>
                    <button type="button" class="select_date newbutton" onclick="javascript:set_date('지난달');">지난달</button>
                    <input type="text" id="fr_date"  name="fr_date" value="<?=$fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
                    <input type="text" id="to_date"  name="to_date" value="<?=$to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">

                </td>
            </tr>
            <tr>
                <td style="width:160px; height: 45px; text-align:center;"><strong>검색어</strong></td>
                <td style="padding: 10px 25px;">
                    <select name="sel_field" id="sel_field" class="inupt_s">
                        <option value="all" <?=get_selected($sel_field, 'all'); ?>>전체</option>
                        <option value="1" <?=get_selected($sel_field, '1'); ?>>주문번호</option>
                        <option value="2" <?=get_selected($sel_field, '2'); ?>>주문자명</option>
                        <option value="3" <?=get_selected($sel_field, '3'); ?>>주문자연락처</option>
                        <option value="4" <?=get_selected($sel_field, '4'); ?>>수급자명</option>
                        <option value="5" <?=get_selected($sel_field, '5'); ?>>요양인정번호</option>
                        <option value="6" <?=get_selected($sel_field, '6'); ?>>상품명</option>
                    </select>&nbsp;
                    <input type="text" id="search"  name="search" value="<?=$search; ?>" class="frm_input" size="30" maxlength="25" autocomplete="off" style="width:250px;">&nbsp;
                    <input type="submit" value="검색" class="btn_submit" id="_submit" style="width:80px; height:35px; padding: 0px; background: #333;">
                </td>
            </tr>            
        </table>

    </div>
    </form>


    <div style="height:50px; padding: 30px 0px;"> 
    총 <?=number_format($total_count);?>건
    </div>


    <div class="list_box">
        <table id="table_list">
        <thead>
            <tr>
                <th style="width: 17%;">주문번호</th>
                <th style="width: 15%;">주문자</th>
                <th style="width: 15%;">수급자</th>
                <th style="width: 30%;">상품</th>
                <th style="width: 10%;">주문일</th>
                <th style="width: 13%;">상태</th>
            </tr>
        </thead>
        <tbody>
        <?php

            for($i=0; $row=sql_fetch_array($result); $i++) {
                $bg = 'bg'.($i%2);
          
        ?>    
            <tr data-odid="" class="<?=$bg?>">  
                <td style="text-align: center;">
                    <a href="./eroumon_order_view.php?order_send_id=<?=$row['order_send_id'];?>">
                        <u><?=$row['order_send_id'];?></u>
                    </a>
                </td>
                <td>
                    <p><?=$row['od_b_name'];?></p>
                    <p><?=$row['od_b_hp'];?></p>
                </td>
                <td>
                    <p><?=$row['od_penNm'];?></p>
                    <p>L<?=$row['od_penLtmNum'];?></p>
                </td>
                <td>
                    <a href="./eroumon_order_view.php?order_send_id=<?=$row['order_send_id'];?>">
                        <u><?=$row['it_name'];?><?=(($row['od_cart_count']>1)?"외 ".($row['od_cart_count']-1)."종":"");?></u>
                    </a>
                </td>
                <td style="text-align: center;">
                    <?=substr($row['od_time'],0,10);?>
                </td>
                <td style="text-align: center;">
                    <?=$row['od_status'];?>
                    <?php if($row['od_status'] == "출고완료") { ?>
                        <img src="/img/warn4.png" id="popup-area" alt="tooltip">
                        <div id="popup" class="popup_tooltip">출고완료된 상품 정보로 수급자와 계약을 진행하실 수 있습니다.</div>
                    <?php } ?>
                </td>
            </tr>
        <?php
            }
        ?>
        <?php if ($i == 0) echo ("<tr><td colspan='6' class='empty_table'>자료가 없습니다.</td></tr>"); ?>
        </tbody>
        </table>
    </div>

    <?php
        $pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
        echo $pagelist;
    ?>

    <script>

    $(function() {  

        $("#fr_date, #to_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            showButtonPanel: true,
            yearRange: "c-99:c+99",
            maxDate: "+0d"
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
            document.getElementById("fr_date").value = "<?php echo G5_TIME_YMD; ?>";
            document.getElementById("to_date").value = "<?php echo G5_TIME_YMD; ?>";
        } else if (today == "내일") {
            document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME + 86400); ?>";
            document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME + 86400); ?>";
        } else if (today == "어제") {
            document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
            document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
        } else if (today == "이번주") {
            document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
            document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
        } else if (today == "이번달") {
            document.getElementById("fr_date").value = "<?php echo date('Y-m-01', G5_SERVER_TIME); ?>";
            document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
        } else if (today == "지난주") {
            document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
            document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
        } else if (today == "지난달") {
            document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
            document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
        } else if (today == "전체") {
            document.getElementById("fr_date").value = "";
            document.getElementById("to_date").value = "";
        } else if (today == "일주일") {
            document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-7 days', G5_SERVER_TIME)); ?>";
            document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
        } else if (today == "3개월") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-3 month', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
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

    #view_link {
                    position: absolute;
                    color: #333;
                    font-weight: normal;
                    font-size: 14px;
                    line-height: 20px;
                    height: 60px;
                    padding: 5px 36px;
                    border-radius: 3px;
                    vertical-align: middle;
                    background-color: #000;
                    color: #fff;
                    border: none;
                    cursor: pointer;
                    right: 0px;
                    top: 8px;
        }

        tr td {
            padding: 2px 10px;
            position: relative;
        }
        
        td #popup-area {
            width:30px;
        }

        td .popup_tooltip {
            display: none;
            animation: tooltipAni 1s;
            transition: opacity 0.5s;
            position: absolute;
            top: 5px;
            left: -150px;
            background: #fff;
            border:1px solid #dbdde2;
            width:250px;
            padding: 10px 10px;
            font-weight: normal;
            font-size: 14px;
            line-height: 24px;

        }

        td:hover .popup_tooltip { display: block; }

        @keyframes tooltipAni {
            0% { opacity: 0; }
            80 { opacity: 0; }
            100% { opacity: 1; }
        }

</style>


<?php
    
    @include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
    include_once('./_tail.php');
?>
