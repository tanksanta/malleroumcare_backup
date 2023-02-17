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
    /* //  * Program Name : EROUMCARE Platform! = OnlineBilling Ver:0.1 */
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
    /* // 파일명 : /www/adm/shop_admin/shop_order_list.php */
    /* // 파일 설명 :   주문리스트 Order 테이블 정보(관리자화면) */
    /*                  기존 주문내역은 cart 테이블의 데이터를 기준으로 정확한 주문서 건수에대한 집계 및 정보 확인이 불가능함에 따라 해당 페이지 신규 추가 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '400404';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '주문서 리스트';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$colspan = 14;

if(!$fr_date){ $fr_date = date("Y-m-d", strtotime(date("Y", time() )."-".date("m", time() )."-".date("d", time() )." -".date("w", time() )." day"));  }
if(!$to_date){ $to_date = date("Y-m-d", strtotime($fr_date." +6 day")); }

// 페이징 되는 주소 파라미터
$qstr = ("list_num={$list_num}&amp;od_status={$od_status}&amp;od_admin_yn={$od_admin_yn}&amp;fr_date={$fr_date}&amp;to_date={$to_date}&amp;mb_name={$mb_name}&amp;");

?>


<style>
    .modal-popup {
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        z-index: 999;
        background-color: rgba(0, 0, 0, 0.6);
        display:none;
    }

    .modal-popup > div {
        width: 1000px;
        max-width: 80%;
        height: 80%;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .modal-popup > div iframe {
        width:100%;
        height:100%;
        border: 0;
        background-color: #FFF;
    }

    /* 로딩 팝업 */
    #loading_excel { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
    #loading_excel .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    #loading_excel .loading_modal p { padding: 0; font-size: 16px; }
    #loading_excel .loading_modal img { display: block; margin: 20px auto; }
    #loading_excel .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }
</style>


<form name="shop_order_list" id="shop_order_list" method="get" onsubmit="return shop_order_list_submit_function(this);">
<div class="new_form">

    <table style="background-color: #f8f8fa;" class="new_form_table">
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>검색조건</strong></td>
            <td style="padding: 10px 25px;">

                <div style="float:left; margin:0px; padding-right:20px; padding-bottom:0px;">
                    <strong>상태</strong><br />
                    <select name="od_status" id="bn_position" style="width:120px;">
                        <option value="" <?=($od_status=="")?"selected":""?>> 전체 </option>
                        <option value="receipt" <?=($od_status=="receipt")?"selected":""?>> 주문접수 </option>
                        <option value="progress" <?=($od_status=="progress")?"selected":""?>> 배송진행 </option>
                        <option value="completed" <?=($od_status=="completed")?"selected":""?>> 배송완료 </option>
                        <option value="cancel" <?=($od_status=="cancel")?"selected":""?>> 주문취소 </option>
                    </select>
                </div>

                <div style="float:left; margin:0px; padding-right:20px; padding-bottom:0px;">
                    <strong>관리자주문</strong><br />

                    <select name="od_admin_yn" id="bn_position" style="width:120px;">
                        <option value="" <?=($od_admin_yn=="")?"selected":""?>> 전체 </option>
                        <option value="Y" <?=($od_admin_yn=="Y")?"selected":""?>> 관리자 </option>
                        <option value="N" <?=($od_admin_yn=="N")?"selected":""?>> 사업소 </option>
                    </select>
                </div>

            </td>
        </tr>
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>검색기간</strong></td>
            <td style="padding: 10px 25px;" class="sch_last">
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
                사업소명 <input type="text" id="mb_name"  name="mb_name" value="<?=$mb_name; ?>" class="frm_input" size="30" maxlength="25" autocomplete="off" style="width:250px;">
                &nbsp; <input type="submit" value="검색" class="btn_submit" id="onlinebilling_submit" style="width:50px; height:25px;">
            </td>
        </tr>            
    </table>

</div>

<div style="padding: 0px 20px; height:30px; margin-bottom: 0px;">
    <select name="list_num" id="bn_position" style="width:120px; float:right;" onchange="submit();">
        <option value="" <?=($list_num=="")?"selected":""?>> 시스템 기본 보기 </option>
        <option value="50" <?=($list_num=="50")?"selected":""?>> 50씩 보기 </option>
        <option value="100" <?=($list_num=="100")?"selected":""?>> 100씩 보기 </option>
        <option value="200" <?=($list_num=="200")?"selected":""?>> 200씩 보기 </option>
        <option value="500" <?=($list_num=="500")?"selected":""?>> 500씩 보기 </option>
        <option value="1000" <?=($list_num=="1000")?"selected":""?>> 1000씩 보기 </option>
    </select>

    <div style="width:250px; height:30px; font-size:12px; float:left;" >
        검색 개수 : <span id="list_cnt"></span>
    </div>
</div>

</form>


<div class="tbl_wrap tbl_head01">
    <table>
    <thead>
    <tr>
        <th scope="col" style="width:125px;">주문일자</th>
        <th scope="col" style="">사업소명</th>
        <th scope="col" style="">수령인</th>
        <th scope="col" style="">품목명</th>
        <th scope="col" style="width:40px;">품목수</th>
        <th scope="col" style="width:45px;">총수량</th>
        <th scope="col" style="width:80px;">총금액</th>
        <th scope="col" style="width:60px;">배송비</th>
        <th scope="col" style="width:60px;">쿠폰할인</th>
        <th scope="col" style="width:80px;">총액</th>
        <th scope="col" style="width:150px;">주문요청사항</th>
        <th scope="col" style="width:60px;">관리자주문</th>
        <th scope="col" style="width:60px;">상태</th>
    </tr>
    </thead>
    <tbody>
<?php

    $sql_common = (" 
        FROM (

            SELECT 
                A.od_id ,
                date_format(A.od_time, '%Y-%m-%d %H:%i:%s') as '주문일시',
                A.od_name as '사업소명',
                C.mb_id as '사업소아이디',
                (CASE when A.od_name=A.od_b_name then null else A.od_b_name end) as '받는이',
                B.상품명들 as '상품명들',
                B.품목수 as '품목수',
                B.개수 as '총수량',
                B.총금액-B.할인금액 as '주문금액',
                A.od_send_cost+A.od_send_cost2 as '배송비',
                A.od_coupon*-1 as '쿠폰할인',
                B.총금액-B.할인금액+A.od_send_cost+A.od_send_cost2-A.od_coupon  as '총액',
                (CASE A.od_memo	WHEN '' THEN NULL ELSE CONCAT(LEFT(A.od_memo,10),'...') END) as '주문요청사항',
                (CASE A.od_add_admin When '' then NULL else '관리자' end) as '관리자',
                (CASE WHEN B.품목수 is null then '주문취소' WHEN B.준비수=B.품목수 then '주문접수' WHEN B.품목수=B.완료수 then '배송완료' else '배송진행' END) as '상태'
        
            FROM g5_shop_order A
            LEFT JOIN 
            (
                SELECT
                    od_id ,
                    concat(it_name) as '품목명',
                    group_concat(it_name) as '상품명들',
                    sum(ct_price*ct_qty) as '총금액',
                    sum(ct_discount) as '할인금액',
                    count(ct_status) as '품목수',
                    sum(Ct_qty) as '개수',
                    count(CASE when ct_status = '완료' then 1 end ) as '완료수',
                    count(CASE when ct_status = '배송' then 1 end )as '배송수',
                    count(CASE when ct_status = '준비' then 1 end ) as '준비수'
                FROM g5_shop_cart
                WHERE ct_status in('준비','배송','완료')
                GROUP by od_id 
            )
            B on A.od_id=B.od_id
            LEFT JOIN g5_member C on C.mb_id=A.mb_id
            WHERE (A.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' )
        
        ) D
        ");

    // 날짜검색
    if ($fr_date && $to_date) {
        $where[] = "(`주문일시` between '$fr_date 00:00:00' and '$to_date 23:59:59' )";
    }


    if($od_admin_yn=="Y"){ $where[] = "`관리자` = '관리자' "; }
    else if($od_admin_yn=="N"){ $where[] = "`관리자` IS NULL "; } 
    else{ }


    if( $od_status =="receipt" ) { $where[] = "`상태` = '주문접수' "; }
    else if( $od_status =="progress" ) { $where[] = "`상태` = '배송진행' "; }
    else if( $od_status =="completed" ) { $where[] = "`상태` = '배송완료' "; }
    else if( $od_status =="cancel" ) { $where[] = "`상태` = '주문취소' "; }
    else { }

    if($mb_name){
        $where[] = "`사업소명` LIKE '%" . $mb_name . "%' ";
    }

    if ($where) {
        if ($sql_search) {
            $sql_search .= " AND ";
        }else{
            $sql_search .= " WHERE ";
        }

        $sql_search .= implode(' and ', $where);
    }

    $sql = " SELECT count(od_id) as cnt
                {$sql_common}
                {$sql_search} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = ($list_num)?$list_num:$config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $sql = " SELECT *
                {$sql_common}
                {$sql_search}
                ORDER BY `주문일시` DESC
                LIMIT {$from_record}, {$rows} ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>
    <tr data-od-id="<?=$row['od_id']?>" class="<?=$bg?>">
        <td style="text-align:center;"><?=$row['주문일시']?></td>
        <td style="text-align:center;" data-mb-id="<?=$row['사업소아이디']?>" class="open_member_pop">
            <a href="#"> <?=$row['사업소명']?> </a>
        </td>
        <td style="text-align:center;"><?=$row['받는이']?></td>
        <td style="text-align:center;" class="open_order_pop">
            <a href="#"> <?=$row['상품명들']?> </a>
        </td>
        <td style="text-align:center;"><?=number_format($row['품목수'])?></td>
        <td style="text-align:center;"><?=number_format($row['총수량'])?></td>
        <td style="text-align:right;"><?=number_format($row['주문금액'])?></td>
        <td style="text-align:right;"><?=number_format($row['배송비'])?></td>
        <td style="text-align:right;"><?=number_format($row['쿠폰할인'])?></td>
        <td style="text-align:right;"><?=number_format($row['총액'])?></td>
        <td style="text-align:center;"><?=$row['주문요청사항']?></td>
        <td style="text-align:center;"><?=$row['관리자']?></td>
        <td style="text-align:center;"><?=$row['상태']?></td>
    </tr>    
    <?php } ?>
    <?php if ($i == 0) echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>'; ?>
    </tbody>
    </table>
</div>


<?php
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
echo $pagelist;
?>

<div id="popup_order_add" class="modal-popup"> <div> </div> </div>

<div id="loading_excel" style="display: none;">
<div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
</div>
</div>

<div class="btn_fixed_top">
    <a href="#" id="order_add" class="btn btn_01">주문서 추가</a>
    <input type="button" value="엑셀다운로드" onclick="Download_Excel()" class="btn btn_02">
</div>

<script src="<?=G5_JS_URL;?>/jquery.fileDownload.js"></script>

<script>
    
    $(function(){
        $("#list_cnt").html('<?=number_format($total_count);?>건');


        $("#sch_sort").change(function(){ // select #sch_sort의 옵션이 바뀔때
            if($(this).val()=="vi_date"){ // 해당 value 값이 vi_date이면
                $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
            }else{ // 아니라면
                $("#sch_word").datepicker("destroy"); // datepicker 미실행
            }
        });


        if($("#sch_sort option:selected").val()=="vi_date"){ // select #sch_sort 의 옵션중 selected 된것의 값이 vi_date라면
            $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
        }


        $(document).on("click", "#order_add", function (e) {
            e.preventDefault();

            $("#popup_order_add > div").html("<iframe src='./pop.order.add.php'></iframe>");
            $("#popup_order_add iframe").load(function(){
                $("#popup_order_add").show();
                $('#hd').css('z-index', 3);
                $('#popup_order_add iframe').contents().find('.mb_id_flexdatalist').focus();
            });

        });


        $(document).on("click", ".open_member_pop", function (e) {

            e.preventDefault();

            var mb_id = $(this).attr("data-mb-id");
            // console.log(mb_id);

            if (!mb_id) { alert('비회원입니다.'); return; }
            window.open( g5_admin_url + '/member_form.php?sst=&sod=&sfl=&stx=&page=&w=u&mb_id=' + mb_id, '_blank' );

        });
        

        $(document).on("click", ".open_order_pop", function (e) {
            
            e.preventDefault();

            var od_id =  $(this).parent().attr("data-od-id");
            // console.log(od_id);

            window.open( g5_admin_url + '/shop_admin/samhwa_orderform.php?od_id=' + od_id, '_blank' );
        });


    });


    $(document).ready(function() {

        $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    });

    

    function shop_order_list_submit_function(f)
    {
        return true;
    }

    function Download_Excel() {
        if (!confirm("엑셀 파일을 다운로드 하시겠습니까?")) { return; }
        
        $('#loading_excel').show();

        var queryString = "<?=urldecode($_SERVER['QUERY_STRING']);?>";
        excel_downloader = $.fileDownload('/adm/shop_admin/ajax.shop_order_list_Excel.php', {
                    httpMethod: "POST",
                    data: { mode_set:"ExcelDown", data:queryString }
                })
                .always(function() { $('#loading_excel').hide(); });
    }    
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
